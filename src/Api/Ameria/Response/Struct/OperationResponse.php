<?php declare(strict_types=1);

namespace Ngs\AmeriaPayment\Api\Ameria\Response\Struct;

class OperationResponse extends BaseResponseStruct
{
    /** @var string */
    public $ResponseCode;

    /** @var string|null */
    public $ResponseMessage;

    /** @var string|null */
    public $Opaque;
}
