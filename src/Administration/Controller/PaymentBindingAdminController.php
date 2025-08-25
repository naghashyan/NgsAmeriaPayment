<?php declare(strict_types=1);

namespace Ngs\AmeriaPayment\Administration\Controller;

use Ngs\AmeriaPayment\Service\BindingService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"api"})
 * @Route(defaults={"_routeScope"={"api"}})
 */
class PaymentBindingAdminController extends AbstractController
{
    private BindingService $bindingService;

    public function __construct(BindingService $bindingService)
    {
        $this->bindingService = $bindingService;
    }

    /**
     * @Route("/api/_action/ngs/payment-binding/{customerId}", name="api.action.ngs.payment-binding.list", methods={"GET"})
     */
    public function list(string $customerId, Context $context): JsonResponse
    {
        $collection = $this->bindingService->listBindings($customerId, null, null, $context);
        $data = [];
        foreach ($collection as $binding) {
            $data[] = [
                'id' => $binding->getId(),
                'maskedPan' => $binding->getMaskedPan(),
                'cardScheme' => $binding->getCardScheme(),
                'expiryMonth' => $binding->getExpiryMonth(),
                'expiryYear' => $binding->getExpiryYear(),
                'isDefault' => $binding->isDefault(),
            ];
        }
        return new JsonResponse($data);
    }

    /**
     * @Route("/api/_action/ngs/payment-binding/{id}", name="api.action.ngs.payment-binding.delete", methods={"DELETE"})
     */
    public function delete(string $id, Context $context): JsonResponse
    {
        $this->bindingService->deleteBinding($id, $context);
        return new JsonResponse(null, 204);
    }
}
