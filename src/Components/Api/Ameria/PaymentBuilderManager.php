<?php declare(strict_types=1);

namespace Ngs\AmeriaPayment\Components\Api\Ameria;

use GuzzleHttp\Exception\RequestException;
use Monolog\Logger;
use Ngs\AmeriaPayment\Api\Ameria\OrderResourceFactory;
use Ngs\AmeriaPayment\Api\Ameria\Request\Struct\InitPayment;
use Ngs\AmeriaPayment\Api\Ameria\Request\Struct\PaymentDetails;
use Ngs\AmeriaPayment\Api\Ameria\Resource\OrderResource;
use Ngs\AmeriaPayment\Api\Ameria\Response\Struct\InitPayment as InitPaymentResponse;
use Ngs\AmeriaPayment\Api\Ameria\Response\Struct\PaymentDetails as PaymentDetailsResponse;
use Ngs\AmeriaPayment\Api\Base\Exception\ApiException;
use Ngs\AmeriaPayment\Components\LanguageEntityManager;
use Ngs\AmeriaPayment\Components\PluginConfig\PluginConfigStruct;
use Psr\Http\Message\ResponseInterface;
use Shopware\Core\Checkout\Payment\Cart\AsyncPaymentTransactionStruct;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Throwable;

/**
 * Class PaymentBuilderManager
 */
class PaymentBuilderManager
{
    private const SUCCESS_CODE_INIT_PAYMENT = 1;
    private const SUCCESS_CODE_PAYMENT_DETAILS = '00';
    private const ORDER_STATUS_APPROVED = 1;
    private const ORDER_STATUS_DEPOSITED = 2;

    private const LANGUAGE_LOCALE_MAPPING = [
        'ru-RU' => self::PAYMENT_LANGUAGE_RU,
        'hy-AM' => self::PAYMENT_LANGUAGE_AM
    ];

    private const PAYMENT_LANGUAGE_EN = 'en';
    private const PAYMENT_LANGUAGE_RU = 'ru';
    private const PAYMENT_LANGUAGE_AM = 'am';

    private const CURRENCY_CODE_AMD = '051';
    private const CURRENCY_CODES = ['AMD' => '051', 'EUR' => '978', 'USD' => '840', 'RUB' => '643'];

    public const NUMBER_RANGE_TYPE = 'ngs_ameria_order';

    private Logger $logger;
    private LanguageEntityManager $languageEntityManager;

    /**
     * @param LanguageEntityManager $languageEntityManager
     * @param Logger $logger
     */
    public function __construct(
        LanguageEntityManager $languageEntityManager,
        Logger                $logger
    )
    {
        $this->logger = $logger;
        $this->languageEntityManager = $languageEntityManager;
    }

    /**
     * Init payment.
     *
     * @param AsyncPaymentTransactionStruct $transactionStruct
     * @param SalesChannelContext $salesChannelContext
     * @param PluginConfigStruct $pluginConfig
     * @param string $returnUrl
     * @param int $orderId
     *
     * @return InitPaymentResponse
     *
     * @throws ApiException
     */
    public function initPayment(AsyncPaymentTransactionStruct $transactionStruct, SalesChannelContext $salesChannelContext, PluginConfigStruct $pluginConfig, string $returnUrl, int $orderId): InitPaymentResponse
    {
        $totalPrice = $transactionStruct->getOrderTransaction()->getAmount()->getTotalPrice();

        if ($pluginConfig->testMode) {
            $amount = 10;
        } else {
            $currencyFactor = $transactionStruct->getOrder()->getCurrencyFactor();

            $amount = $totalPrice / $currencyFactor;
        }

        $requestStruct = new InitPayment();

        $requestStruct->OrderID = $orderId;
        $requestStruct->Amount = $amount;
        $requestStruct->BackURL = $returnUrl;
        $requestStruct->Description = $pluginConfig->description;
        $requestStruct->Currency = $this->getCurrency($salesChannelContext);
        $requestStruct->ClientID = $pluginConfig->clientId;
        $requestStruct->Username = $pluginConfig->username;
        $requestStruct->Password = $pluginConfig->password;

        $orderResource = $this->createOrderResource($pluginConfig->apiUri);

        try {
            $response = $orderResource->initPayment($requestStruct);
        } catch (Throwable $ex) {
            $apiException = $this->handleRequestException($ex);
            $apiException->setRequest($requestStruct->cloneWithoutApiCredentials());

            $this->logger->error('InitPayment.', ['ex' => $apiException->toArray()]);

            throw $apiException;
        }

        $responseJson = (string)$response->getBody();

        $this->logger->info("InitPayment response: $responseJson", ['request' => $requestStruct->toArrayWithoutApiCredentials()]);

        $responseObj = InitPaymentResponse::jsonToResponseStruct($responseJson);

        $responseObj->addRequestToExtension($requestStruct->cloneWithoutApiCredentials());

        $responseObj->paymentURL = $this->getPaymentURL($pluginConfig, $responseObj->PaymentID, $salesChannelContext);

        if ($responseObj->ResponseCode !== self::SUCCESS_CODE_INIT_PAYMENT) {
            throw new ApiException((string)$responseObj->ResponseMessage, 0, null, ['response' => $responseObj]);
        }

        if (!$responseObj->PaymentID) {
            throw new ApiException('Payment id is empty.', 0, null, ['response' => $responseObj]);
        }

        return $responseObj;
    }

    /**
     * Get payment details
     *
     * @param PluginConfigStruct $pluginConfig
     * @param string $paymentId
     *
     * @return PaymentDetailsResponse
     *
     * @throws ApiException
     */
    public function getPaymentDetails(PluginConfigStruct $pluginConfig, string $paymentId): PaymentDetailsResponse
    {
        $orderResource = $this->createOrderResource($pluginConfig->apiUri);

        $requestStruct = new PaymentDetails();

        $requestStruct->Username = $pluginConfig->username;
        $requestStruct->Password = $pluginConfig->password;
        $requestStruct->PaymentID = $paymentId;

        try {
            $response = $orderResource->getPaymentDetails($requestStruct);
        } catch (Throwable $ex) {
            $apiException = $this->handleRequestException($ex);
            $apiException->setRequest($requestStruct->cloneWithoutApiCredentials());

            $this->logger->error('PaymentDetails.', ['ex' => $apiException->toArray()]);

            throw $apiException;
        }

        $responseJson = (string)$response->getBody();

        $responseObj = PaymentDetailsResponse::jsonToResponseStruct($responseJson);

        $responseObj->addRequestToExtension($requestStruct->cloneWithoutApiCredentials());

        $this->logger->info("PaymentDetails response $responseJson", ['request' => $requestStruct->toArrayWithoutApiCredentials()]);

        if ($responseObj->ResponseCode !== self::SUCCESS_CODE_PAYMENT_DETAILS) {
            throw new ApiException((string)$responseObj->Description, 0, null, ['response' => $responseObj]);
        }

        $orderStatus = (int)$responseObj->OrderStatus;

        if ($orderStatus !== self::ORDER_STATUS_APPROVED && $orderStatus !== self::ORDER_STATUS_DEPOSITED) {
            throw new ApiException((string)$responseObj->Description, 0, null, ['response' => $responseObj]);
        }

        return $responseObj;
    }

    /**
     * Handle guzzle exception
     *
     * @param Throwable $ex
     *
     * @return ApiException
     */
    private function handleRequestException(Throwable $ex): ApiException
    {
        [$message, $code, $responseBody] = $this->getMessagesFromException($ex);

        return new ApiException($message, $code, $ex, ['responseBody' => $responseBody]);
    }

    /**
     * Get messages from guzzle exception
     *
     * @param Throwable $ex
     *
     * @return array
     */
    private function getMessagesFromException(Throwable $ex): array
    {
        $responseBody = '';
        $message = $ex->getMessage();
        $code = $ex->getCode();

        if ($ex instanceof RequestException) { // get message from guzzle exception
            $response = $ex->getResponse();

            if ($response instanceof ResponseInterface) {
                $responseBody = (string)$response->getBody();
                $message = $response->getReasonPhrase();
                $code = $response->getStatusCode();
            }
        }

        return [$message, $code, $responseBody];
    }


    /**
     * Get currency
     *
     * @param SalesChannelContext $salesChannelContext
     *
     * @return string
     */
    private function getCurrency(SalesChannelContext $salesChannelContext): string
    {
        //at this moment API accept only AMD currency
        return self::CURRENCY_CODE_AMD;
        $currencyIsoCode = $salesChannelContext->getCurrency()->getIsoCode();

        return self::CURRENCY_CODES[$currencyIsoCode] ?? self::CURRENCY_CODE_AMD;
    }

    /**
     * Returns Payment gateway URL
     *
     * @param PluginConfigStruct $pluginConfig
     * @param string $paymentId
     * @param SalesChannelContext $salesChannelContext
     *
     * @return string
     */
    private function getPaymentURL(PluginConfigStruct $pluginConfig, string $paymentId, SalesChannelContext $salesChannelContext): string
    {
        $language = $this->getLanguage($salesChannelContext);

        return "$pluginConfig->apiUri/Payments/Pay?id=$paymentId&lang=$language";
    }

    /**
     * Returns Language
     *
     * @param SalesChannelContext $salesChannelContext
     *
     * @return string
     */
    private function getLanguage(SalesChannelContext $salesChannelContext): string
    {
        $localeCode = $this->languageEntityManager->getLocaleCodeById($salesChannelContext->getSalesChannel()->getLanguageId(), $salesChannelContext->getContext());

        return self::LANGUAGE_LOCALE_MAPPING[$localeCode] ?? self::PAYMENT_LANGUAGE_EN;
    }

    /**
     * @param string $uri
     *
     * @return OrderResource
     */
    private function createOrderResource(string $uri): OrderResource
    {
        $orderResourceFactory = new OrderResourceFactory($uri);

        return $orderResourceFactory->create();
    }
}