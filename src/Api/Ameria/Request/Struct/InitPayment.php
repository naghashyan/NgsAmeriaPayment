<?php declare(strict_types=1);

namespace Ngs\AmeriaPayment\Api\Ameria\Request\Struct;

/**
 * Class InitPayment contains API request params Structure
 */
class InitPayment extends BaseRequestStruct
{
    protected array $apiCredentials = ['Username', 'Password', 'ClientID'];

//    /**
//     * @var int
//     */
//    public $CardHolderID;

    /**
     * @var string
     */
    public $Currency;

    /**
     * @var string
     */
    public $Description;

    /**
     * @var int
     */
    public $OrderID;

    /**
     * @var string
     */
    public $Amount;

//    /**
//     * @var string
//     */
//    public $Opaque;

    /**
     * @var string
     */
    public $BackURL;

    /**
     * @var string
     */
    public $ClientID;

    /**
     * @var string
     */
    public $Username;

    /**
     * @var string
     */
    public $Password;
}