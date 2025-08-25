<?php declare(strict_types=1);

namespace Ngs\AmeriaPayment\Service;

use Ngs\AmeriaPayment\Core\PaymentBinding\PaymentBindingCollection;
use Ngs\AmeriaPayment\Core\PaymentBinding\PaymentBindingEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use InvalidArgumentException;
use RuntimeException;

class BindingService
{
    private EntityRepository $repository;
    private EncryptionService $encryptionService;

    public function __construct(EntityRepository $repository, EncryptionService $encryptionService)
    {
        $this->repository = $repository;
        $this->encryptionService = $encryptionService;
    }

    public function createBindingFromPSPResponse(OrderTransactionEntity $transaction, CustomerEntity $customer, array $pspData, Context $context): PaymentBindingEntity
    {
        if (empty($pspData['bindingToken'])) {
            throw new InvalidArgumentException('PSP response does not contain binding token');
        }

        $bindingId = Uuid::randomHex();

        $data = [
            'id' => $bindingId,
            'customerId' => $customer->getId(),
            'paymentMethodId' => $transaction->getPaymentMethodId(),
            'salesChannelId' => $transaction->getOrder() ? $transaction->getOrder()->getSalesChannelId() : null,
            'provider' => $pspData['provider'] ?? 'ameria',
            'bindingToken' => $this->encryptionService->encrypt($pspData['bindingToken']),
            'maskedPan' => $pspData['maskedPan'] ?? '',
            'cardScheme' => $pspData['cardScheme'] ?? '',
            'expiryMonth' => (int) ($pspData['expiryMonth'] ?? 0),
            'expiryYear' => (int) ($pspData['expiryYear'] ?? 0),
            'isDefault' => (bool)($pspData['isDefault'] ?? false),
            'meta' => $pspData['meta'] ?? null,
        ];

        $this->repository->create([$data], $context);

        /** @var PaymentBindingEntity|null $created */
        $created = $this->repository->search(new Criteria([$bindingId]), $context)->first();

        if (!$created) {
            throw new RuntimeException('Failed to create payment binding');
        }

        return $created;
    }

    public function listBindings(string $customerId, ?string $paymentMethodId, ?string $salesChannelId, Context $context): PaymentBindingCollection
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('customerId', $customerId));
        if ($paymentMethodId) {
            $criteria->addFilter(new EqualsFilter('paymentMethodId', $paymentMethodId));
        }
        if ($salesChannelId) {
            $criteria->addFilter(new EqualsFilter('salesChannelId', $salesChannelId));
        }

        /** @var PaymentBindingCollection $collection */
        $collection = $this->repository->search($criteria, $context)->getEntities();

        return $collection;
    }

    public function setDefault(string $bindingId, Context $context): void
    {
        /** @var PaymentBindingEntity|null $binding */
        $binding = $this->repository->search(new Criteria([$bindingId]), $context)->first();
        if (!$binding) {
            throw new InvalidArgumentException('Binding not found');
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('customerId', $binding->getCustomerId()));
        $criteria->addFilter(new EqualsFilter('paymentMethodId', $binding->getPaymentMethodId()));
        $criteria->addFilter(new NotFilter(MultiFilter::CONNECTION_AND, [new EqualsFilter('id', $bindingId)]));
        $others = $this->repository->search($criteria, $context)->getEntities();
        $updates = [];
        foreach ($others as $other) {
            $updates[] = ['id' => $other->getId(), 'isDefault' => false];
        }
        $updates[] = ['id' => $bindingId, 'isDefault' => true];
        $this->repository->update($updates, $context);
    }

    public function deleteBinding(string $bindingId, Context $context): void
    {
        $this->repository->delete([['id' => $bindingId]], $context);
    }

    public function getDecryptedToken(PaymentBindingEntity $binding): string
    {
        return $this->encryptionService->decrypt($binding->getBindingToken());
    }
}
