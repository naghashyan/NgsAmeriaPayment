<?php declare(strict_types=1);

namespace Ngs\AmeriaPayment\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;
use Throwable;

class Migration202407181200AddPaymentBinding extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 202407181200;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `ngs_payment_binding` (
    `id` BINARY(16) NOT NULL,
    `customer_id` BINARY(16) NOT NULL,
    `payment_method_id` BINARY(16) NOT NULL,
    `sales_channel_id` BINARY(16) NULL,
    `provider` VARCHAR(255) NOT NULL,
    `binding_token` VARCHAR(255) NOT NULL,
    `masked_pan` VARCHAR(255) NOT NULL,
    `card_scheme` VARCHAR(255) NOT NULL,
    `expiry_month` INT NOT NULL,
    `expiry_year` INT NOT NULL,
    `is_default` TINYINT(1) NOT NULL DEFAULT 0,
    `meta` JSON NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,
    PRIMARY KEY (`id`),
    KEY `idx.binding.customer` (`customer_id`),
    KEY `idx.binding.payment_method` (`payment_method_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        try {
            $connection->executeStatement($sql);
        } catch (Throwable $e) {
            // ignore
        }
    }

    public function updateDestructive(Connection $connection): void
    {
        // no destructive update
    }
}
