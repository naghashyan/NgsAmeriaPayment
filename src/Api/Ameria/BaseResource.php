<?php declare(strict_types=1);

namespace Ngs\AmeriaPayment\Api\Ameria;

use GuzzleHttp\Client;

/**
 * Abstract Class Resource
 */
abstract class BaseResource
{
    protected Client $client;
    protected string $uri;

    /**
     * @param string $uri
     * @param Client $client
     */
    public function __construct(string $uri, Client $client)
    {
        $this->uri = $uri;
        $this->client = $client;
    }

}