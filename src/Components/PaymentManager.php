<?php declare(strict_types=1);

namespace Ngs\AmeriaPayment\Components;

use Ngs\AmeriaPayment\Api\Ameria\Response\Struct\InitPayment;
use Ngs\AmeriaPayment\Api\Ameria\Response\Struct\PaymentDetails;
use Ngs\AmeriaPayment\Api\Base\Exception\ApiException;
use Ngs\AmeriaPayment\Components\Api\Ameria\PaymentBuilderManager;
use Ngs\AmeriaPayment\Components\PluginConfig\PluginConfigService;
use Ngs\AmeriaPayment\Components\PluginConfig\PluginConfigStruct;
use Ngs\AmeriaPayment\Utils\ArrayUtil;
use Shopware\Core\Checkout\Payment\Cart\AsyncPaymentTransactionStruct;
use Shopware\Core\System\NumberRange\ValueGenerator\NumberRangeValueGeneratorInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class PaymentManager
 */
class PaymentManager
{
    private RouterInterface $router;
    private SwPaymentTokenHashManager $swPaymentTokenHashManager;
    private OrderTransactionEntityManager $orderTransactionEntityManager;
    private PaymentBuilderManager $paymentBuilderManager;
    private NumberRangeValueGeneratorInterface $numberRangeValueGenerator;
    private PluginConfigService $pluginConfigService;

    /**
     * @param RouterInterface $router
     * @param SwPaymentTokenHashManager $swPaymentTokenHashManager
     * @param OrderTransactionEntityManager $orderTransactionEntityManager
     * @param PaymentBuilderManager $paymentBuilderManager
     * @param NumberRangeValueGeneratorInterface $valueGenerator
     * @param PluginConfigService $pluginConfigService
     */
    public function __construct(
        RouterInterface $router,
        SwPaymentTokenHashManager $swPaymentTokenHashManager,
        OrderTransactionEntityManager $orderTransactionEntityManager,
        PaymentBuilderManager $paymentBuilderManager,
        NumberRangeValueGeneratorInterface $valueGenerator,
        PluginConfigService $pluginConfigService
    )
    {
        $this->router = $router;
        $this->swPaymentTokenHashManager = $swPaymentTokenHashManager;
        $this->orderTransactionEntityManager = $orderTransactionEntityManager;
        $this->paymentBuilderManager = $paymentBuilderManager;
        $this->numberRangeValueGenerator = $valueGenerator;
        $this->pluginConfigService = $pluginConfigService;
    }

    public function getPluginConfig(string $salesChannelId): PluginConfigStruct
    {
        return new PluginConfigStruct($this->pluginConfigService, $salesChannelId);
    }

    /**
     * Init payment and return external gateway url
     *
     * @param AsyncPaymentTransactionStruct $transaction
     * @param SalesChannelContext $salesChannelContext
     *
     * @return string
     *
     * @throws ApiException
     */
    public function initPayment(AsyncPaymentTransactionStruct $transaction, SalesChannelContext $salesChannelContext): string
    {
        $pluginConfig = new PluginConfigStruct($this->pluginConfigService, $salesChannelContext->getSalesChannelId());

        $returnUrl = $this->assembleReturnUrl($transaction->getReturnUrl());
        $orderId = (int)$this->numberRangeValueGenerator->getValue(PaymentBuilderManager::NUMBER_RANGE_TYPE, $salesChannelContext->getContext(), null, false);

        try {
            $responseObj = $this->paymentBuilderManager->initPayment($transaction, $salesChannelContext, $pluginConfig, $returnUrl, $orderId);
        } catch (ApiException $ex) {
            $responseObj = $ex->getResponse();

            if ($responseObj instanceof InitPayment) {
                $this->orderTransactionEntityManager->setPaymentIdCustomField($transaction->getOrderTransaction()->getId(), $responseObj->PaymentID, $orderId, $salesChannelContext->getContext());
            }

            throw $ex;
        }

        $this->orderTransactionEntityManager->setPaymentIdCustomField($transaction->getOrderTransaction()->getId(), $responseObj->PaymentID, $orderId, $salesChannelContext->getContext());

        return $responseObj->paymentURL;
    }

    /**
     * Get payment details and finalize payment
     *
     * @param AsyncPaymentTransactionStruct $transaction
     * @param SalesChannelContext $salesChannelContext
     * @param string|null $paymentId
     *
     * @throws ApiException
     */
    public function finalize(AsyncPaymentTransactionStruct $transaction, SalesChannelContext $salesChannelContext, ?string $paymentId): void
    {
        if (!$paymentId) {
            throw new ApiException();
        }

        $pluginConfig = new PluginConfigStruct($this->pluginConfigService, $salesChannelContext->getSalesChannelId());

        try {
            $responseObj = $this->paymentBuilderManager->getPaymentDetails($pluginConfig, $paymentId);
        } catch (ApiException $ex) {
            $responseObj = $ex->getResponse();

            if ($responseObj instanceof PaymentDetails) {
                $this->orderTransactionEntityManager->setMdOrderIdCustomField($transaction->getOrderTransaction()->getId(), $responseObj->MDOrderID, $salesChannelContext->getContext());
            }

            throw $ex;
        }

        $this->orderTransactionEntityManager->setMdOrderIdCustomField($transaction->getOrderTransaction()->getId(), $responseObj->MDOrderID, $salesChannelContext->getContext());
    }

    public function capture(string $paymentId, float $amount, SalesChannelContext $salesChannelContext): void
    {
        $pluginConfig = new PluginConfigStruct($this->pluginConfigService, $salesChannelContext->getSalesChannelId());
        $this->paymentBuilderManager->confirmPayment($pluginConfig, $paymentId, $amount);
    }

    public function cancel(string $paymentId, SalesChannelContext $salesChannelContext): void
    {
        $pluginConfig = new PluginConfigStruct($this->pluginConfigService, $salesChannelContext->getSalesChannelId());
        $this->paymentBuilderManager->cancelPayment($pluginConfig, $paymentId);
    }

    /**
     * Since external payment system truncates the return URL to 500 characters
     * and the SW return URl is too long due to _sw_payment_token request parameter,
     * we use 'ameria finalize transaction' URl as return URl.
     *
     * Hash SW payment token with sha 256 algorithm and store in DB.
     *
     * @param string $returnUrl
     *
     * @return string
     */
    private function assembleReturnUrl(string $returnUrl): string
    {
        $urlQueryParams = ArrayUtil::getUrlQueryParams($returnUrl);

        $swPaymentToken = $urlQueryParams['_sw_payment_token'];
        $hash = hash('sha256', $swPaymentToken);

        $this->swPaymentTokenHashManager->insert($hash, $swPaymentToken);

        $parameters = [];
        $parameters['hash'] = $hash;

        return $this->router->generate('payment.ngs.ameria.finalize.transaction', $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}