<?php declare(strict_types=1);

namespace Ngs\AmeriaPayment\Components;

use Shopware\Core\Framework\Context;
use Throwable;

/**
 * Class OrderTransactionEntityManager contains SW order transaction related functionality
 */
class OrderTransactionEntityManager extends BaseEntityManager
{
    /**
     * Update payment id custom field
     *
     * @param string $id
     * @param string|null $paymentId
     * @param int $orderId
     * @param Context $context
     *
     * @return bool
     */
    public function setPaymentIdCustomField(string $id, ?string $paymentId, int $orderId, Context $context): bool
    {
        $data = [];
        $data['id'] = $id;
        $data['customFields']['ngs_ameria_payment_id'] = $paymentId;
        $data['customFields']['ngs_ameria_order_id'] = $orderId;

        return $this->update($data, $context);
    }

    /**
     * Update md order id custom field
     *
     * @param string $id
     * @param string $mdOrderId
     * @param Context $context
     *
     * @return bool
     */
    public function setMdOrderIdCustomField(string $id, string $mdOrderId, Context $context): bool
    {
        $data = [];
        $data['id'] = $id;
        $data['customFields']['ngs_ameria_md_order_id'] = $mdOrderId;

        return $this->update($data, $context);
    }

    /**
     * @param array $data
     * @param Context $context
     *
     * @return bool
     */
    private function update(array $data, Context $context): bool
    {
        try {
            $this->repository->update([$data], $context);
        } catch (Throwable $ex) {
            return false;
        }

        return true;
    }

}