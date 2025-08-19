<?php declare(strict_types=1);

namespace Ngs\AmeriaPayment\Api\Ameria\Request\Struct;

/**
 * Class BaseRequestStruct is base class for Request struct classes
 */
class BaseRequestStruct extends \Ngs\AmeriaPayment\Api\Base\BaseRequestStruct
{
    /**
     * Api credentials property names to unset
     *
     * @var array
     */
    protected array $apiCredentials = [];

    /**
     * @return array
     */
    public function toArrayWithoutApiCredentials(): array
    {
        $newInstance = $this->cloneWithoutApiCredentials();

        return $newInstance->toArray();
    }

    /**
     * @return static
     */
    public function cloneWithoutApiCredentials(): self
    {
        $newInstance = clone $this;

        foreach ($newInstance->apiCredentials as $apiCredential) {
            unset($newInstance->$apiCredential);
        }

        unset($newInstance->apiCredentials);

        return $newInstance;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

}