<?php declare(strict_types=1);

namespace Ngs\AmeriaPayment\Api\Ameria\Request\Struct;

class MakeBindingPayment extends BaseRequestStruct
{
    protected array $apiCredentials = ['Username', 'Password', 'ClientID'];

    /** @var string */
    public $ClientID;

    /** @var string */
    public $Username;

    /** @var string */
    public $Password;

    /** @var string */
    public $Currency;

    /** @var string */
    public $Description;

    /** @var int */
    public $OrderID;

    /** @var float */
    public $Amount;

    /** @var string|null */
    public $Opaque;

    /** @var string */
    public $CardHolderID;

    /** @var string */
    public $BackURL;

    /** @var int */
    public $PaymentType;
}
