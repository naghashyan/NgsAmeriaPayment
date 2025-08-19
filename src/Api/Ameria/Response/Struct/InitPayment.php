<?php declare(strict_types=1);

namespace Ngs\AmeriaPayment\Api\Ameria\Response\Struct;

/**
 * Class InitPayment
 * Response from API wrapped into this object
 */
class InitPayment extends BaseResponseStruct
{
    /**
     * Unique payment ID
     *
     * @var string|null
     */
    public $PaymentID;

    /**
     * Operation response code (successful=1)
     *
     * @var int|null
     */
    public $ResponseCode;

    /**
     * Description of operation response
     *
     * @var string|null
     */
    public $ResponseMessage;

    /**
     * Payment gateway URL. Added dynamically
     *
     * @var string|null
     */
    public $paymentURL;
}