<?php declare(strict_types=1);

namespace Ngs\AmeriaPayment\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;
use Throwable;

/**
 * Class Migration1628764739NgsAmeriaSwPaymentTokenHash
 *
 * Create payment token table to store sw payment token hash
 */
class Migration1628764739NgsAmeriaSwPaymentTokenHash extends MigrationStep
{
    /**
     * @inheritDoc
     */
    public function getCreationTimestamp(): int
    {
        return 1628764739;
    }

    /**
     * @inheritDoc
     */
    public function update(Connection $connection): void
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `ngs_ameria_sw_payment_token_hash` (
                  `hash` VARCHAR (64) COLLATE utf8mb4_unicode_ci NOT NULL,
                  `token` TEXT COLLATE utf8mb4_unicode_ci NOT NULL,
                  PRIMARY KEY (`hash`)
                ) ENGINE = INNODB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;';

        try {
            $connection->executeQuery($sql);
        } catch (Throwable $ex) {
        }
    }

    /**
     * @inheritDoc
     */
    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}