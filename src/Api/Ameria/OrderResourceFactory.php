<?php declare(strict_types=1);

namespace Ngs\AmeriaPayment\Api\Ameria;

use Ngs\AmeriaPayment\Api\Ameria\Resource\OrderResource;
use Ngs\AmeriaPayment\Api\Ameria\Http\GuzzleClientFactory;

class OrderResourceFactory implements ResourceFactoryInterface
{
    protected string $uri;
    protected array $config;

    /**
     * @param string $uri
     * @param array $config
     */
    public function __construct(string $uri, array $config = [])
    {
        $this->uri = $uri;
        $this->config = $config;
    }

    public function create(): OrderResource
    {
        $guzzleClientFactory = new GuzzleClientFactory($this->config);

        $client = $guzzleClientFactory->create();

        return new OrderResource($this->uri, $client);
    }
}