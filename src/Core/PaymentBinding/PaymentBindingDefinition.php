<?php declare(strict_types=1);

namespace Ngs\AmeriaPayment\Core\PaymentBinding;

use Shopware\Core\Checkout\Customer\CustomerDefinition;
use Shopware\Core\Checkout\Payment\PaymentMethodDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UuidField;
use Shopware\Core\System\SalesChannel\SalesChannelDefinition;

/**
 * Definition for saved payment bindings
 */
class PaymentBindingDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'ngs_payment_binding';

    /**
     * @inheritDoc
     */
    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    /**
     * @inheritDoc
     */
    public function getCollectionClass(): string
    {
        return PaymentBindingCollection::class;
    }

    /**
     * @inheritDoc
     */
    public function getEntityClass(): string
    {
        return PaymentBindingEntity::class;
    }

    /**
     * @inheritDoc
     */
    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new UuidField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('customer_id', 'customerId', CustomerDefinition::class))->addFlags(new Required()),
            (new FkField('payment_method_id', 'paymentMethodId', PaymentMethodDefinition::class))->addFlags(new Required()),
            new FkField('sales_channel_id', 'salesChannelId', SalesChannelDefinition::class),
            (new StringField('provider', 'provider'))->addFlags(new Required()),
            (new StringField('binding_token', 'bindingToken'))->addFlags(new Required()),
            (new StringField('masked_pan', 'maskedPan'))->addFlags(new Required()),
            (new StringField('card_scheme', 'cardScheme'))->addFlags(new Required()),
            (new IntField('expiry_month', 'expiryMonth'))->addFlags(new Required()),
            (new IntField('expiry_year', 'expiryYear'))->addFlags(new Required()),
            (new BoolField('is_default', 'isDefault'))->addFlags(new Required()),
            new JsonField('meta', 'meta'),
            new CreatedAtField(),
            new UpdatedAtField(),
        ]);
    }
}
