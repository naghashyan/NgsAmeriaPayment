<?php declare(strict_types=1);

namespace Ngs\AmeriaPayment\Api\Ameria\Response\Struct;

use Ngs\AmeriaPayment\Api\Ameria\Request\Struct\BaseRequestStruct;
use Shopware\Core\Framework\Struct\Struct;

/**
 * Class BaseRequestStruct is base class for Request struct classes
 */
class BaseResponseStruct extends \Ngs\AmeriaPayment\Api\Base\BaseResponseStruct
{
    private const REQUEST_EXT_NAME = 'api_request';

    /**
     * @return BaseRequestStruct|null|Struct
     */
    public function getRequestFromExtension(): ?BaseRequestStruct
    {
        return $this->getExtension(self::REQUEST_EXT_NAME);
    }

    /**
     * @param BaseRequestStruct $initPayment
     */
    public function addRequestToExtension(BaseRequestStruct $initPayment): void
    {
        $this->addExtension(self::REQUEST_EXT_NAME, $initPayment);
    }

}