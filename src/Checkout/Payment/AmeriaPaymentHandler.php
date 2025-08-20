<?php declare(strict_types=1);

namespace Ngs\AmeriaPayment\Checkout\Payment;

use Ngs\AmeriaPayment\Api\Base\Exception\ApiException;
use Ngs\AmeriaPayment\Components\PaymentManager;
use Ngs\AmeriaPayment\Components\PluginConfig\PluginConfigStruct;
use Monolog\Logger;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStateHandler;
use Shopware\Core\Checkout\Payment\Cart\AsyncPaymentTransactionStruct;
use Shopware\Core\Checkout\Payment\Cart\PaymentHandler\AsynchronousPaymentHandlerInterface;
use Shopware\Core\Checkout\Payment\Exception\AsyncPaymentFinalizeException;
use Shopware\Core\Checkout\Payment\Exception\AsyncPaymentProcessException;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AmeriaPaymentHandler
 */
class AmeriaPaymentHandler implements AsynchronousPaymentHandlerInterface
{
    private OrderTransactionStateHandler $transactionStateHandler;
    private PaymentManager $paymentManager;
    private Logger $logger;

    /**
     * @param OrderTransactionStateHandler $transactionStateHandler
     * @param PaymentManager $paymentManager
     */
    public function __construct(OrderTransactionStateHandler $transactionStateHandler, PaymentManager $paymentManager, Logger $logger)
    {
        $this->transactionStateHandler = $transactionStateHandler;
        $this->paymentManager = $paymentManager;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function pay(
        AsyncPaymentTransactionStruct $transaction,
        RequestDataBag $dataBag,
        SalesChannelContext $salesChannelContext
    ): RedirectResponse
    {
        $transactionId = $transaction->getOrderTransaction()->getId();

        $this->logger->info('Payment handler pay started', [
            'transactionId' => $transactionId,
        ]);

        try {
            $redirectUrl = $this->paymentManager->initPayment($transaction, $salesChannelContext);
        } catch (ApiException $ex) {
            $this->logger->error('Payment handler pay failed', [
                'transactionId' => $transactionId,
                'ex' => $ex->getMessage(),
            ]);

            throw new AsyncPaymentProcessException(
                $transactionId,
                $ex->getMessage()
            );
        }

        $this->logger->info('Redirecting to external gateway', [
            'transactionId' => $transactionId,
            'redirectUrl' => $redirectUrl,
        ]);

        // Redirect to external gateway
        return new RedirectResponse($redirectUrl);
    }

    /**
     * @inheritDoc
     */
    public function finalize(
        AsyncPaymentTransactionStruct $transaction,
        Request $request,
        SalesChannelContext $salesChannelContext
    ): void
    {
        $transactionId = $transaction->getOrderTransaction()->getId();
        $paymentId = $request->get('paymentID');

        $this->logger->info('Payment handler finalize started', [
            'transactionId' => $transactionId,
            'paymentId' => $paymentId,
        ]);

        try {
            $this->paymentManager->finalize($transaction, $salesChannelContext, $paymentId);
        } catch (ApiException $ex) {
            $this->logger->error('Payment handler finalize failed', [
                'transactionId' => $transactionId,
                'ex' => $ex->getMessage(),
            ]);

            throw new AsyncPaymentFinalizeException(
                $transactionId,
                $ex->getMessage()
            );
        }

        $pluginConfig = $this->paymentManager->getPluginConfig($salesChannelContext->getSalesChannelId());
        if ($pluginConfig->freezePayments) {
            $this->transactionStateHandler->authorize($transactionId, $salesChannelContext->getContext());
            $this->logger->info('Transaction authorized', [
                'transactionId' => $transactionId,
            ]);
        } else {
            $this->transactionStateHandler->paid($transactionId, $salesChannelContext->getContext());
            $this->logger->info('Transaction paid', [
                'transactionId' => $transactionId,
            ]);
        }
    }
}
