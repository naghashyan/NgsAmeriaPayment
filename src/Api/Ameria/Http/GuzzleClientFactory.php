<?php declare(strict_types=1);

namespace Ngs\AmeriaPayment\Api\Ameria\Http;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class GuzzleClientFactory implements ClientFactoryInterface
{
    private array $config;

    /**
     * @param array $config
     * @param string $apiEndpoint
     * @param string $username
     * @param string $password
     * @param string $token
     */
    public function __construct(array $config = [], string $apiEndpoint = '', string $username = '', string $password = '', string $token = '')
    {
        $defaultConfig = [];

        if ($apiEndpoint) {
            $defaultConfig['base_uri'] = $apiEndpoint;
        }

        $defaultConfig[RequestOptions::HTTP_ERRORS] = true;
        $defaultConfig[RequestOptions::TIMEOUT] = 30;
        $defaultConfig[RequestOptions::CONNECT_TIMEOUT] = 10;
        $defaultConfig[RequestOptions::ALLOW_REDIRECTS] = false;

        if ($username && $password) {
            $credentials = base64_encode("$username:$password");
            $defaultConfig[RequestOptions::HEADERS]['Authorization'] = "Basic $credentials";

            if ($token) {
                $defaultConfig[RequestOptions::HEADERS]['Authorization'] .= ",Bearer $token";
            }
        }

        if ($token) {
            $defaultConfig[RequestOptions::HEADERS]['Authorization'] = "Bearer $token";
        }

        $this->config = array_merge($defaultConfig, $config); //overrides default config
    }

    public function create(): Client
    {
        return new Client($this->config);
    }
}