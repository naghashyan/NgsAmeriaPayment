<?php declare(strict_types=1);

namespace Ngs\AmeriaPayment\Api\Base\Exception;

use Exception;
use Ngs\AmeriaPayment\Api\Base\BaseRequestStruct;
use Ngs\AmeriaPayment\Api\Base\BaseResponseStruct;
use Throwable;

/**
 * Class ApiException used for throwing exceptions in cases when status code is not OK in API.
 * And also used for throwing internal errors.
 */
class ApiException extends Exception
{
    private array $params;

    /**
     * ApiException constructor.
     *
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     * @param array $params Adds extra parameters when throwing exceptions
     */
    public function __construct(string $message = '', int $code = 0, Throwable $previous = null, array $params = [])
    {
        parent::__construct($message, $code, $previous);

        $this->params = $params;
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        try {
            $exStr = json_encode($this->toArray(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        } catch (Throwable $ex) {
            $exStr = $ex->getMessage();
        }

        return (string)$exStr;
    }

    /**
     * Array representation of the exception
     *
     * @return array
     */
    public function toArray(): array
    {
        return ['message' => $this->getMessage(), 'code' => $this->getCode(), 'params' => $this->getParams()];
    }

    /**
     * Returns extra parameters which are added during throwing exceptions
     *
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * Returns extra parameter by key which are added during throwing exceptions
     *
     * @param string $key
     *
     * @return mixed|null
     */
    public function getParam(string $key)
    {
        return $this->params[$key] ?? null;
    }

    /**
     * Set extra parameter by key which are added during throwing exceptions
     *
     * @param string $key
     * @param mixed $value
     */
    public function setParam(string $key, $value): void
    {
        $this->params[$key] = $value;
    }

    /**
     * Set request
     *
     * @param BaseRequestStruct $value
     */
    public function setRequest(BaseRequestStruct $value): void
    {
        $this->setParam('request', $value);
    }

    /**
     * Set response
     *
     * @param BaseResponseStruct $value
     */
    public function setResponse(BaseResponseStruct $value): void
    {
        $this->setParam('request', $value);
    }

    /**
     * @return BaseResponseStruct|null
     */
    public function getResponse(): ?BaseResponseStruct
    {
        return $this->getParam('response');
    }

    /**
     * @return BaseRequestStruct|null
     */
    public function getRequest(): ?BaseRequestStruct
    {
        return $this->getParam('request');
    }

}