<?php declare(strict_types=1);

namespace Ngs\AmeriaPayment\Api\Ameria;

interface ResourceFactoryInterface
{
    public function create(): BaseResource;
}