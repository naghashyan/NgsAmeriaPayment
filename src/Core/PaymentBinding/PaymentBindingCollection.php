<?php declare(strict_types=1);

namespace Ngs\AmeriaPayment\Core\PaymentBinding;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                     add(PaymentBindingEntity $entity)
 * @method void                     set(string $key, PaymentBindingEntity $entity)
 * @method PaymentBindingEntity[]   getIterator()
 * @method PaymentBindingEntity[]   getElements()
 * @method PaymentBindingEntity|null get(string $key)
 * @method PaymentBindingEntity|null first()
 * @method PaymentBindingEntity|null last()
 */
class PaymentBindingCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return PaymentBindingEntity::class;
    }
}
