<?php declare(strict_types=1);

namespace Ngs\AmeriaPayment\Core\ScheduledTask;

use Ngs\AmeriaPayment\Components\PaymentManager;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;

class CaptureFrozenPaymentsTaskHandler extends ScheduledTaskHandler
{
    private EntityRepository $orderTransactionRepository;
    private PaymentManager $paymentManager;

    public function __construct(EntityRepository $scheduledTaskRepository, EntityRepository $orderTransactionRepository, PaymentManager $paymentManager)
    {
        parent::__construct($scheduledTaskRepository);
        $this->orderTransactionRepository = $orderTransactionRepository;
        $this->paymentManager = $paymentManager;
    }

    public static function getHandledMessages(): iterable
    {
        return [CaptureFrozenPaymentsTask::class];
    }

    public function run(): void
    {
        $context = Context::createDefaultContext();

        // TODO: Fetch authorized transactions and capture or cancel based on order and delivery status
        // This is a placeholder to demonstrate scheduling logic.
    }
}
