<?php declare(strict_types=1);

namespace Ngs\AmeriaPayment\Api\Ameria\Http;

use Psr\Http\Client\ClientInterface;

interface ClientFactoryInterface
{
    public function create(): ClientInterface;
}