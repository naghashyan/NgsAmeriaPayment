<?php declare(strict_types=1);

namespace Ngs\AmeriaPayment\Api\Base;

use JsonException;
use Throwable;

/**
 * Class BaseResponseStruct is base class for Response struct classes
 */
class BaseResponseStruct extends BaseStruct
{
    /**
     * Wrap response json string from API into corresponding response object
     *
     * @param string $responseJson
     * @param string $responseClassName
     *
     * @return static
     */
    public static function jsonToResponseStruct(string $responseJson, string $responseClassName = ''): self
    {
        return self::wrapIntoResponseObject(self::getResponseArr($responseJson), $responseClassName);
    }

    /**
     * Wrap response from API into corresponding response object
     *
     * @param array $responseArr
     * @param string $responseClassName
     *
     * @return static
     */
    public static function wrapIntoResponseObject(array $responseArr, string $responseClassName = ''): self
    {
        $responseClass = static::class . $responseClassName;
        $responseObj = new $responseClass();

        foreach ($responseArr as $key => $value) {
            if (is_array($value) && class_exists($responseClass . ucfirst($key))) {
                if (isset($value[0]) && is_array($value[0])) {
                    $responseObjects = [];

                    foreach ($value as $i => $v) {
                        if (is_array($v)) {
                            $responseObjects[$i] = self::wrapIntoResponseObject($v, $responseClassName . $key);
                        }
                    }

                    $value = $responseObjects;
                } else if (!is_array($responseObj->$key)) {
                    $value = self::wrapIntoResponseObject($value, $responseClassName . $key);
                }
            }

            try {
                $responseObj->$key = $value;
            } catch (Throwable $ex) {
            }
        }

        return $responseObj;
    }

    /**
     * Get response as array from json string
     *
     * @param string $responseJson
     *
     * @return array
     */
    private static function getResponseArr(string $responseJson): array
    {
        if (!$responseJson) {
            return [];
        }

        try {
            $responseArr = json_decode($responseJson, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $ex) {
            return [];
        }

        if (!$responseArr) {
            return [];
        }

        if (!is_array($responseArr)) {
            return [];
        }

        return $responseArr;
    }
}