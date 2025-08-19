<?php declare(strict_types=1);

namespace Ngs\AmeriaPayment\Core\Checkout\Payment\Controller;

use Ngs\AmeriaPayment\Components\SwPaymentTokenHashManager;
use Shopware\Core\Framework\Routing\Exception\MissingRequestParameterException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Shopware\Core\Framework\Routing\Annotation\Since;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class PaymentController
 *
 * Controller is whitelisted to work without scope (RouteScope annotation).
 *
 * @see \Ngs\AmeriaPayment\Core\Framework\Routing\PaymentScopeWhitelist
 */
class PaymentController extends AbstractController
{
    private SwPaymentTokenHashManager $swPaymentTokenHashManager;

    /**
     * @param SwPaymentTokenHashManager $swPaymentTokenHashManager
     */
    public function __construct(
        SwPaymentTokenHashManager $swPaymentTokenHashManager
    )
    {
        $this->swPaymentTokenHashManager = $swPaymentTokenHashManager;
    }

    /**
     * Replace 'hash' with '_sw_payment_token' param in request and redirect to sw 'finalize transaction' controller
     *
     * @Since("6.0.0.0")
     * @Route("/payment/ngs/ameria/finalize-transaction", name="payment.ngs.ameria.finalize.transaction", methods={"GET", "POST"})
     *
     * @param Request $request
     *
     * @return Response
     *
     * @see \Ngs\AmeriaPayment\Components\PaymentManager::assembleReturnUrl
     */
    public function finalizeTransaction(Request $request): Response
    {
        $hash = $request->get('hash');

        if (!$hash) {
            throw new MissingRequestParameterException('hash');
        }

        $paymentToken = $this->swPaymentTokenHashManager->getTokenByHash($hash);

        if (!$paymentToken) {
            throw new MissingRequestParameterException('_sw_payment_token');
        }

        $this->swPaymentTokenHashManager->deleteByHash($hash);

        $requestParams = array_merge($request->query->all(), $request->request->all());

        unset($requestParams['hash']);

        $finishUrl = $this->assembleReturnUrl($requestParams, $paymentToken);

        return new RedirectResponse($finishUrl);
    }

    /**
     * @param array $params
     * @param string $token
     *
     * @return string
     */
    private function assembleReturnUrl(array $params, string $token): string
    {
        $params['_sw_payment_token'] = $token;

        return $this->generateUrl('payment.finalize.transaction', $params, UrlGeneratorInterface::ABSOLUTE_URL);
    }

}