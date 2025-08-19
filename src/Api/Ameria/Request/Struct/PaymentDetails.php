<?php declare(strict_types=1);

namespace Ngs\AmeriaPayment\Api\Ameria\Request\Struct;

/**
 * Class PaymentDetails contains API request params Structure
 */
class PaymentDetails extends BaseRequestStruct
{
    protected array $apiCredentials = ['Username', 'Password'];

    /**
     * @var string
     */
    public $PaymentID;

    /**
     * @var string
     */
    public $Username;

    /**
     * @var string
     */
    public $Password;
}