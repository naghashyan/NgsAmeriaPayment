<?php declare(strict_types=1);

namespace Ngs\AmeriaPayment\Api\Ameria\Response\Struct;

/**
 * Class PaymentDetailsResponse
 * Response from API wrapped into this object
 */
class PaymentDetails extends BaseResponseStruct
{
    /**
     * Transaction amount
     *
     * @var string
     */
    public $Amount;

    /**
     * Amount blocked on the client’s card
     *
     * @var string
     */
    public $ApprovedAmount;

    /**
     * Transaction authorization code
     *
     * @var string
     */
    public $ApprovalCode;

    /**
     * Masked card number
     *
     * @var string
     */
    public $CardNumber;

    /**
     * Cardholder name
     *
     * @var string
     */
    public $ClientName;

    /**
     * Cardholder email
     *
     * @var string
     */
    public $ClientEmail;

    /**
     * Transaction currency
     *
     * @var string
     */
    public $Currency;

    /**
     * Transaction date
     *
     * @var string
     */
    public $DateTime;

    /**
     * Amount deposited to the merchant’s account
     *
     * @var string
     */
    public $DepositedAmount;

    /**
     * Information about the transaction
     *
     * @var string|null
     */
    public $Description;

    /**
     * Merchant ID
     *
     * @var string
     */
    public $MerchantId;

    /**
     * Value of opaque field of the initial request
     *
     * @var string
     */
    public $Opaque;

    /**
     * Unique ID of the transaction
     *
     * @var int
     */
    public $OrderID;

    /**
     * Payment state
     *
     * @var string
     */
    public $PaymentState;

    /**
     * Payment type
     *
     * 1 - Virtual Arca
     * 3 - Visa, MasterCard, Arca (epay)
     * 5 - Visa, MasterCard, Arca (ipay)
     * 6 - Binding
     *
     * @var int
     */
    public $PaymentType;

    /**
     * Operation response code (successful=00)
     *
     * @var string
     */
    public $ResponseCode;

    /**
     * Unique code of the transaction
     *
     * @var string
     */
    public $rrn;

    /**
     * Merchant’s terminalid
     *
     * @var string
     */
    public $TerminalId;

    /**
     * Transaction description
     *
     * @var string
     */
    public $TrxnDescription;

    /**
     * Status code of the payment
     *
     * @var int|string
     */
    public $OrderStatus;

    /**
     * Amount transferred back to the card
     *
     * @var string
     */
    public $RefundedAmount;

    /**
     * Unique ID for binding transactions
     *
     * @var string
     */
    public $CardHolderID;

    /**
     * Payment system identifier
     *
     * @var string
     */
    public $MDOrderID;

    /**
     * Main code
     *
     * @var string
     */
    public $PrimaryRC;

    /**
     * Card expiration date
     *
     * @var string
     */
    public $ExpDate;

    /**
     * IP address
     *
     * @var string
     */
    public $ProcessingIP;

    /**
     * Binding identifier
     *
     * @var string
     */
    public $BindingID;

    /**
     * Action code
     *
     * @var string
     */
    public $ActionCode;

    /**
     * Exchange rate
     *
     * @var string
     */
    public $ExchangeRate;

}