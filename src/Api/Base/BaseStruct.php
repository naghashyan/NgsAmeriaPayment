<?php declare(strict_types=1);

namespace Ngs\AmeriaPayment\Api\Base;

use Shopware\Core\Framework\Struct\Struct;
use Throwable;

/**
 * Class BaseStruct is base class for Request and Response struct classes
 */
class BaseStruct extends Struct
{
    /**
     * Unset object property by property name
     *
     * @param string $name
     */
    protected function unsetProperty(string $name): void
    {
        unset($this->$name);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        try {
            $responseJson = json_encode($this, JSON_THROW_ON_ERROR, 512);
        } catch (Throwable $ex) {
            $responseJson = $ex->getMessage();
        }

        return (string)$responseJson;
    }

}