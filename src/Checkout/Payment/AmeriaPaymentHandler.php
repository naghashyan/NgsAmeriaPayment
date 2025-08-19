<?php declare(strict_types=1);

namespace Ngs\AmeriaPayment\Checkout\Payment;

use Ngs\AmeriaPayment\Api\Base\Exception\ApiException;
use Ngs\AmeriaPayment\Components\PaymentManager;
use Ngs\AmeriaPayment\Components\PluginConfig\PluginConfigStruct;
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

    /**
     * @param OrderTransactionStateHandler $transactionStateHandler
     * @param PaymentManager $paymentManager
     */
    public function __construct(OrderTransactionStateHandler $transactionStateHandler, PaymentManager $paymentManager)
    {
        $this->transactionStateHandler = $transactionStateHandler;
        $this->paymentManager = $paymentManager;
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
        try {
            $redirectUrl = $this->paymentManager->initPayment($transaction, $salesChannelContext);
        } catch (ApiException $ex) {
            throw new AsyncPaymentProcessException(
                $transaction->getOrderTransaction()->getId(),
                $ex->getMessage()
            );
        }

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
        try {
            $this->paymentManager->finalize($transaction, $salesChannelContext, $request->get('paymentID'));
        } catch (ApiException $ex) {
            throw new AsyncPaymentFinalizeException(
                $transaction->getOrderTransaction()->getId(),
                $ex->getMessage()
            );
        }

        $transactionId = $transaction->getOrderTransaction()->getId();

        $pluginConfig = $this->paymentManager->getPluginConfig($salesChannelContext->getSalesChannelId());
        if ($pluginConfig->freezePayments) {
            $this->transactionStateHandler->authorize($transactionId, $salesChannelContext->getContext());
        } else {
            $this->transactionStateHandler->paid($transactionId, $salesChannelContext->getContext());
        }
    }
}
