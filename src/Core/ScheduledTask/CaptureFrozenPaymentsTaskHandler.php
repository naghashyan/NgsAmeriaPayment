<?php declare(strict_types=1);

namespace Ngs\AmeriaPayment\Core\ScheduledTask;

use Ngs\AmeriaPayment\Checkout\Payment\AmeriaPaymentHandler;
use Ngs\AmeriaPayment\Components\PaymentManager;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryStates;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStateHandler;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStates;
use Shopware\Core\Checkout\Order\OrderStates;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextFactory;


class CaptureFrozenPaymentsTaskHandler extends ScheduledTaskHandler
{
    private EntityRepository $orderTransactionRepository;
    private PaymentManager $paymentManager;
    private OrderTransactionStateHandler $transactionStateHandler;
    private SalesChannelContextFactory $salesChannelContextFactory;

    public function __construct(
        EntityRepository $scheduledTaskRepository,
        EntityRepository $orderTransactionRepository,
        PaymentManager $paymentManager,
        OrderTransactionStateHandler $transactionStateHandler,
        SalesChannelContextFactory $salesChannelContextFactory
    ) {
        parent::__construct($scheduledTaskRepository);
        $this->orderTransactionRepository = $orderTransactionRepository;
        $this->paymentManager = $paymentManager;
        $this->transactionStateHandler = $transactionStateHandler;
        $this->salesChannelContextFactory = $salesChannelContextFactory;
    }

    public static function getHandledMessages(): iterable
    {
        return [CaptureFrozenPaymentsTask::class];
    }

    public function run(): void
    {
        $context = Context::createDefaultContext();

        $criteria = new Criteria();
        $criteria->addAssociation('order');
        $criteria->addAssociation('order.deliveries.stateMachineState');
        $criteria->addAssociation('order.stateMachineState');
        $criteria->addAssociation('paymentMethod');
        $criteria->addFilter(new EqualsFilter('stateMachineState.technicalName', OrderTransactionStates::STATE_AUTHORIZED));
        $criteria->addFilter(new EqualsFilter('paymentMethod.handlerIdentifier', AmeriaPaymentHandler::class));

        /** @var OrderTransactionCollection $transactions */
        $transactions = $this->orderTransactionRepository->search($criteria, $context)->getEntities();

        foreach ($transactions as $transaction) {
            $order = $transaction->getOrder();
            if ($order === null) {
                continue;
            }

            $customFields = $transaction->getCustomFields() ?? [];
            $paymentId = $customFields['ngs_ameria_payment_id'] ?? null;
            if (!$paymentId) {
                continue;
            }

            $salesChannelContext = $this->salesChannelContextFactory->create('', $order->getSalesChannelId(), []);

            $orderState = $order->getStateMachineState()?->getTechnicalName();
            if ($orderState === OrderStates::STATE_CANCELLED) {
                $this->paymentManager->cancel($paymentId, $salesChannelContext);
                $this->transactionStateHandler->cancel($transaction->getId(), $context);
                continue;
            }

            $shipped = false;
            foreach ($order->getDeliveries() as $delivery) {
                $deliveryState = $delivery->getStateMachineState()?->getTechnicalName();
                if ($deliveryState === OrderDeliveryStates::STATE_SHIPPED) {
                    $shipped = true;
                    break;
                }
            }

            if (!$shipped) {
                continue;
            }

            $authorizedAmount = $transaction->getAmount()->getTotalPrice();
            $orderPrice = $order->getPrice();
            $captureAmount = $orderPrice ? $orderPrice->getTotalPrice() : $authorizedAmount;
            $captureAmount = min($captureAmount, $authorizedAmount);

            $this->paymentManager->capture($paymentId, $captureAmount, $salesChannelContext);

            if ($captureAmount < $authorizedAmount) {
                $this->paymentManager->cancel($paymentId, $salesChannelContext);
            }

            $this->transactionStateHandler->paid($transaction->getId(), $context);
        }
    }
}
