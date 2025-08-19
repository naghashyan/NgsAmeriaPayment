<?php declare(strict_types=1);

namespace Ngs\AmeriaPayment\Api\Base;

/**
 * Class BaseRequestStruct is base class for Request struct classes
 */
class BaseRequestStruct extends BaseStruct
{
    public function __construct()
    {
        $this->unsetProperty('extensions');
    }
}