<?php declare(strict_types=1);

namespace Ngs\AmeriaPayment\Core\ScheduledTask;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class CaptureFrozenPaymentsTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'ngs.ameria.capture_frozen_payments';
    }

    public static function getDefaultInterval(): int
    {
        return 3600; // hourly
    }
}
