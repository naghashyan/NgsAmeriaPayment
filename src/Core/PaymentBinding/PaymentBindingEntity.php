<?php declare(strict_types=1);

namespace Ngs\AmeriaPayment\Core\PaymentBinding;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

/**
 * Entity representing stored payment binding
 */
class PaymentBindingEntity extends Entity
{
    use EntityIdTrait;

    protected string $customerId;
    protected string $paymentMethodId;
    protected ?string $salesChannelId = null;
    protected string $provider;
    protected string $bindingToken;
    protected string $maskedPan;
    protected string $cardScheme;
    protected int $expiryMonth;
    protected int $expiryYear;
    protected bool $isDefault = false;
    protected ?array $meta = null;

    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    public function setCustomerId(string $customerId): void
    {
        $this->customerId = $customerId;
    }

    public function getPaymentMethodId(): string
    {
        return $this->paymentMethodId;
    }

    public function setPaymentMethodId(string $paymentMethodId): void
    {
        $this->paymentMethodId = $paymentMethodId;
    }

    public function getSalesChannelId(): ?string
    {
        return $this->salesChannelId;
    }

    public function setSalesChannelId(?string $salesChannelId): void
    {
        $this->salesChannelId = $salesChannelId;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function setProvider(string $provider): void
    {
        $this->provider = $provider;
    }

    public function getBindingToken(): string
    {
        return $this->bindingToken;
    }

    public function setBindingToken(string $bindingToken): void
    {
        $this->bindingToken = $bindingToken;
    }

    public function getMaskedPan(): string
    {
        return $this->maskedPan;
    }

    public function setMaskedPan(string $maskedPan): void
    {
        $this->maskedPan = $maskedPan;
    }

    public function getCardScheme(): string
    {
        return $this->cardScheme;
    }

    public function setCardScheme(string $cardScheme): void
    {
        $this->cardScheme = $cardScheme;
    }

    public function getExpiryMonth(): int
    {
        return $this->expiryMonth;
    }

    public function setExpiryMonth(int $expiryMonth): void
    {
        $this->expiryMonth = $expiryMonth;
    }

    public function getExpiryYear(): int
    {
        return $this->expiryYear;
    }

    public function setExpiryYear(int $expiryYear): void
    {
        $this->expiryYear = $expiryYear;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(bool $isDefault): void
    {
        $this->isDefault = $isDefault;
    }

    public function getMeta(): ?array
    {
        return $this->meta;
    }

    public function setMeta(?array $meta): void
    {
        $this->meta = $meta;
    }
}
