<?php declare(strict_types=1);

namespace Ngs\AmeriaPayment\Api\Ameria\Request\Struct;

class ConfirmPayment extends BaseRequestStruct
{
    protected array $apiCredentials = ['Username', 'Password'];

    /** @var string */
    public $PaymentID;

    /** @var string */
    public $Username;

    /** @var string */
    public $Password;

    /** @var float */
    public $Amount;
}
