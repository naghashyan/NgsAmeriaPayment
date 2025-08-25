<?php declare(strict_types=1);

namespace Ngs\AmeriaPayment\Storefront\Controller;

use Ngs\AmeriaPayment\Service\BindingService;
use Shopware\Core\Checkout\Customer\Exception\CustomerNotLoggedInException;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * @RouteScope(scopes={"store-api"})
 * @Route(defaults={"_routeScope"={"store-api"}})
 */
class PaymentBindingStoreApiController extends AbstractController
{
    private BindingService $bindingService;
    private SessionInterface $session;

    public function __construct(BindingService $bindingService, SessionInterface $session)
    {
        $this->bindingService = $bindingService;
        $this->session = $session;
    }

    /**
     * List bindings for current customer
     *
     * @Route("/store-api/ngs/payment-binding", name="store-api.ngs.payment-binding.list", methods={"GET"})
     */
    public function list(Request $request, SalesChannelContext $context): JsonResponse
    {
        $customer = $context->getCustomer();
        if (!$customer) {
            throw new CustomerNotLoggedInException();
        }
        $paymentMethodId = $request->query->get('paymentMethodId');
        $salesChannelId = $request->query->get('salesChannelId');
        $collection = $this->bindingService->listBindings($customer->getId(), $paymentMethodId, $salesChannelId, $context->getContext());
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
     * Create a new binding from PSP response
     *
     * @Route("/store-api/ngs/payment-binding", name="store-api.ngs.payment-binding.create", methods={"POST"})
     */
    public function create(Request $request, SalesChannelContext $context): JsonResponse
    {
        $customer = $context->getCustomer();
        if (!$customer) {
            throw new CustomerNotLoggedInException();
        }
        $payload = json_decode($request->getContent(), true) ?: [];
        $pspData = $payload['pspData'] ?? [];
        $orderTransaction = new OrderTransactionEntity();
        $orderTransaction->setId($payload['orderTransactionId'] ?? Uuid::randomHex());
        $orderTransaction->setPaymentMethodId($context->getPaymentMethod()->getId());
        $binding = $this->bindingService->createBindingFromPSPResponse($orderTransaction, $customer, $pspData, $context->getContext());
        return new JsonResponse(['id' => $binding->getId()], 201);
    }

    /**
     * Set default binding
     *
     * @Route("/store-api/ngs/payment-binding/{id}/default", name="store-api.ngs.payment-binding.set-default", methods={"PATCH"})
     */
    public function setDefault(string $id, SalesChannelContext $context): JsonResponse
    {
        $customer = $context->getCustomer();
        if (!$customer) {
            throw new CustomerNotLoggedInException();
        }
        $this->bindingService->setDefault($id, $context->getContext());
        return new JsonResponse(null, 204);
    }

    /**
     * Delete binding
     *
     * @Route("/store-api/ngs/payment-binding/{id}", name="store-api.ngs.payment-binding.delete", methods={"DELETE"})
     */
    public function delete(string $id, SalesChannelContext $context): JsonResponse
    {
        $customer = $context->getCustomer();
        if (!$customer) {
            throw new CustomerNotLoggedInException();
        }
        $this->bindingService->deleteBinding($id, $context->getContext());
        return new JsonResponse(null, 204);
    }

    /**
     * Persist selected binding in session
     *
     * @Route("/store-api/ngs/payment-binding/select", name="store-api.ngs.payment-binding.select", methods={"POST"})
     */
    public function select(Request $request, SalesChannelContext $context): JsonResponse
    {
        $customer = $context->getCustomer();
        if (!$customer) {
            throw new CustomerNotLoggedInException();
        }
        $payload = json_decode($request->getContent(), true) ?: [];
        $bindingId = $payload['bindingId'] ?? null;
        if ($bindingId) {
            $this->session->set('ngsBindingSelection', ['bindingId' => $bindingId]);
        }
        return new JsonResponse(['selected' => $bindingId]);
    }

    /**
     * Clear selected binding
     *
     * @Route("/store-api/ngs/payment-binding/select", name="store-api.ngs.payment-binding.unselect", methods={"DELETE"})
     */
    public function unselect(SalesChannelContext $context): JsonResponse
    {
        $customer = $context->getCustomer();
        if (!$customer) {
            throw new CustomerNotLoggedInException();
        }
        $this->session->remove('ngsBindingSelection');
        return new JsonResponse(null, 204);
    }
}
