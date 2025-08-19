<?php declare(strict_types=1);

namespace Ngs\AmeriaPayment\Migration;

use DateTime;
use Doctrine\DBAL\Connection;
use Exception;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;
use Throwable;

/**
 * Class Migration1629276664NgsAmeriaOrderIdNumberRange
 *
 * Create ameria order id number range
 */
class Migration1629276664NgsAmeriaOrderIdNumberRange extends MigrationStep
{
    /**
     * @inheritDoc
     */
    public function getCreationTimestamp(): int
    {
        return 1629276664;
    }

    /**
     * @inheritDoc
     */
    public function update(Connection $connection): void
    {
        try {
            $this->createNumberRanges($connection);
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

    /**
     * @param Connection $connection
     *
     * @throws Exception
     */
    private function createNumberRanges(Connection $connection): void
    {
        $definitionNumberRangeTypes = [
            'ngs_ameria_order' => [
                'id' => Uuid::randomHex(),
                'global' => 0,
                'nameDe' => 'Ngs Ameria Bestellung',
                'nameEn' => 'Ngs Ameria Order',
            ]
        ];

        $definitionNumberRanges = [
            'order' => [
                'id' => Uuid::randomHex(),
                'name' => 'Ngs Ameria Orders',
                'nameDe' => 'Ngs Ameria Bestellungen',
                'global' => 1,
                'typeId' => $definitionNumberRangeTypes['ngs_ameria_order']['id'],
                'pattern' => '{n}',
                'start' => 1,
            ]
        ];

        $systemLanguage = Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM);
        $alreadyExistedTypeIds = [];

        foreach ($definitionNumberRangeTypes as $typeName => $numberRangeType) {
            $isExists = $this->isNumberRangeTypeExists($connection, $typeName);

            if ($isExists) {
                $alreadyExistedTypeIds[$numberRangeType['id']] = $numberRangeType['id'];

                continue;
            }

            $connection->insert(
                'number_range_type',
                [
                    'id' => Uuid::fromHexToBytes($numberRangeType['id']),
                    'global' => $numberRangeType['global'],
                    'technical_name' => $typeName,
                    'created_at' => (new DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                ]
            );

            $connection->insert(
                'number_range_type_translation',
                [
                    'number_range_type_id' => Uuid::fromHexToBytes($numberRangeType['id']),
                    'type_name' => $numberRangeType['nameEn'],
                    'language_id' => $systemLanguage,
                    'created_at' => (new DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                ]
            );
        }

        foreach ($definitionNumberRanges as $numberRange) {
            if (isset($alreadyExistedTypeIds[$numberRange['typeId']])) {
                continue;
            }

            $connection->insert(
                'number_range',
                [
                    'id' => Uuid::fromHexToBytes($numberRange['id']),
                    'global' => $numberRange['global'],
                    'type_id' => Uuid::fromHexToBytes($numberRange['typeId']),
                    'pattern' => $numberRange['pattern'],
                    'start' => $numberRange['start'],
                    'created_at' => (new DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                ]
            );

            $connection->insert(
                'number_range_translation',
                [
                    'number_range_id' => Uuid::fromHexToBytes($numberRange['id']),
                    'name' => $numberRange['name'],
                    'language_id' => $systemLanguage,
                    'created_at' => (new DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                ]
            );
        }
    }

    /**
     * Check number range type exists
     *
     * @param Connection $connection
     * @param string $type
     *
     * @return bool
     */
    private function isNumberRangeTypeExists(Connection $connection, string $type): bool
    {
        $sql = 'SELECT
                  LOWER (HEX (`id`))
                FROM
                  `number_range_type`
                WHERE `technical_name` = :type';

        try {
            $id = $connection->fetchOne($sql, ['type' => $type]);
        } catch (Throwable $ex) {
            return false;
        }

        return (bool)$id;
    }
}
