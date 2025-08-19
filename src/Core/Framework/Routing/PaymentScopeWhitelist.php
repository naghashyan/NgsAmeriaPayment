<?php declare(strict_types=1);

namespace Ngs\AmeriaPayment\Core\Framework\Routing;

use Ngs\AmeriaPayment\Core\Checkout\Payment\Controller\PaymentController;
use Shopware\Core\Framework\Routing\RouteScopeWhitelistInterface;

/**
 * Class PaymentScopeWhitelist
 *
 * Adds PaymentController to whitelist to work without scope (RouteScope annotation).
 */
class PaymentScopeWhitelist implements RouteScopeWhitelistInterface
{
    /**
     * @inheritDoc
     */
    public function applies(string $controllerClass): bool
    {
        return $controllerClass === PaymentController::class;
    }
}