<?php declare(strict_types=1);

namespace Ngs\AmeriaPayment\Api\Ameria\Resource;

use GuzzleHttp\RequestOptions;
use Ngs\AmeriaPayment\Api\Ameria\Request\Struct\InitPayment;
use Ngs\AmeriaPayment\Api\Ameria\Request\Struct\PaymentDetails;
use Ngs\AmeriaPayment\Api\Ameria\Request\Struct\ConfirmPayment;
use Ngs\AmeriaPayment\Api\Ameria\Request\Struct\CancelPayment;
use Ngs\AmeriaPayment\Api\Ameria\Request\Struct\MakeBindingPayment;
use Ngs\AmeriaPayment\Api\Ameria\BaseResource;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class OrdersResource is API Resource
 */
class OrderResource extends BaseResource
{
    private string $endpoint = 'api/VPOS';

    /**
     * Init payment.
     *
     * @param InitPayment $data params to send to APi.
     *
     * @return ResponseInterface response on success
     *
     * @throws ClientExceptionInterface
     */
    public function initPayment(InitPayment $data): ResponseInterface
    {
        return $this->client->post("$this->uri/$this->endpoint/InitPayment", [RequestOptions::JSON => $data]);
    }

    /**
     * Get payment details
     *
     * @param PaymentDetails $data params to send to APi.
     *
     * @return ResponseInterface response on success
     *
     * @throws ClientExceptionInterface
     */
    public function getPaymentDetails(PaymentDetails $data): ResponseInterface
    {
        return $this->client->post("$this->uri/$this->endpoint/GetPaymentDetails", [RequestOptions::JSON => $data]);
    }

    public function confirmPayment(ConfirmPayment $data): ResponseInterface
    {
        return $this->client->post("$this->uri/$this->endpoint/ConfirmPayment", [RequestOptions::JSON => $data]);
    }

    public function cancelPayment(CancelPayment $data): ResponseInterface
    {
        return $this->client->post("$this->uri/$this->endpoint/CancelPayment", [RequestOptions::JSON => $data]);
    }

    public function makeBindingPayment(MakeBindingPayment $data): ResponseInterface
    {
        return $this->client->post("$this->uri/$this->endpoint/MakeBindingPayment", [RequestOptions::JSON => $data]);
    }

}