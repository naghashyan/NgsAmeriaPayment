<?php declare(strict_types=1);

namespace Ngs\AmeriaPayment\Components;

use Doctrine\DBAL\Connection;
use Throwable;

/**
 * Class SwPaymentTokenHashManager contains CRUD functionality of ngs_ameria_sw_payment_token_hash table
 */
class SwPaymentTokenHashManager
{
    private Connection $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * insert sw payment token and hash
     *
     * @param string $hash
     * @param string $swPaymentToken
     *
     * @return bool
     */
    public function insert(string $hash, string $swPaymentToken): bool
    {
        $data = [];
        $data['hash'] = $hash;
        $data['token'] = $swPaymentToken;

        try {
            $this->connection->insert('ngs_ameria_sw_payment_token_hash', $data);
        } catch (Throwable $ex) {
            return false;
        }

        return true;
    }

    /**
     * Get token by hash
     *
     * @param string $hash
     *
     * @return string|null
     */
    public function getTokenByHash(string $hash): ?string
    {
        $sql = 'SELECT `token` FROM `ngs_ameria_sw_payment_token_hash` WHERE `hash` = :hash';

        try {
            $res = $this->connection->fetchOne($sql, ['hash' => $hash]);
        } catch (Throwable $ex) {
            return null;
        }

        return $res ?: null;
    }

    /**
     * Delete by hash
     *
     * @param string $hash
     *
     * @return bool
     */
    public function deleteByHash(string $hash): bool
    {
        $data = [];
        $data['hash'] = $hash;

        try {
            $this->connection->delete('ngs_ameria_sw_payment_token_hash', $data);
        } catch (Throwable $ex) {
            return false;
        }

        return true;
    }
}