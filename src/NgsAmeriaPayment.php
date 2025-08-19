<?php declare(strict_types=1);

namespace Ngs\AmeriaPayment;

use Ngs\AmeriaPayment\Checkout\Payment\AmeriaPaymentHandler;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Util\PluginIdProvider;

/**
 * Class NgsAmeriaPayment
 */
class NgsAmeriaPayment extends Plugin
{
    /**
     * @inheritDoc
     */
    public function install(InstallContext $installContext): void
    {
        $this->addPaymentMethod($installContext->getContext());
    }

    /**
     * @inheritDoc
     */
    public function uninstall(UninstallContext $uninstallContext): void
    {
        // Only set the payment method to inactive when uninstalling. Removing the payment method would
        // cause data consistency issues, since the payment method might have been used in several orders
        $this->setPaymentMethodIsActive(false, $uninstallContext->getContext());
    }

    /**
     * @inheritDoc
     */
    public function activate(ActivateContext $activateContext): void
    {
        $this->setPaymentMethodIsActive(true, $activateContext->getContext());

        parent::activate($activateContext);
    }

    /**
     * @inheritDoc
     */
    public function deactivate(DeactivateContext $deactivateContext): void
    {
        $this->setPaymentMethodIsActive(false, $deactivateContext->getContext());

        parent::deactivate($deactivateContext);
    }

    /**
     * @param Context $context
     */
    private function addPaymentMethod(Context $context): void
    {
        $paymentMethodExists = $this->getPaymentMethodId($context);

        // Payment method exists already, no need to continue here
        if ($paymentMethodExists) {
            return;
        }

        /** @var PluginIdProvider $pluginIdProvider */
        $pluginIdProvider = $this->container->get(PluginIdProvider::class);
        $pluginId = $pluginIdProvider->getPluginIdByBaseClass(self::class, $context);

        $paymentData = [
            // payment handler will be selected by the identifier
            'handlerIdentifier' => AmeriaPaymentHandler::class,
            'name' => 'Ameria Payment',
            'description' => 'Ameria Payment',
            'pluginId' => $pluginId,
        ];

        /** @var EntityRepositoryInterface $paymentRepository */
        $paymentRepository = $this->container->get('payment_method.repository');
        $paymentRepository->create([$paymentData], $context);
    }

    /**
     * @param bool $active
     * @param Context $context
     */
    private function setPaymentMethodIsActive(bool $active, Context $context): void
    {
        /** @var EntityRepositoryInterface $paymentRepository */
        $paymentRepository = $this->container->get('payment_method.repository');

        $paymentMethodId = $this->getPaymentMethodId($context);

        // Payment does not even exist, so nothing to (de-)activate here
        if (!$paymentMethodId) {
            return;
        }

        $paymentMethod = [
            'id' => $paymentMethodId,
            'active' => $active,
        ];

        $paymentRepository->update([$paymentMethod], $context);
    }

    /**
     * @param Context $context
     *
     * @return string|null
     */
    private function getPaymentMethodId(Context $context): ?string
    {
        /** @var EntityRepositoryInterface $paymentRepository */
        $paymentRepository = $this->container->get('payment_method.repository');

        // Fetch ID for update
        $paymentCriteria = (new Criteria())->addFilter(new EqualsFilter('handlerIdentifier', AmeriaPaymentHandler::class));

        return $paymentRepository->searchIds($paymentCriteria, $context)->firstId();
    }
}