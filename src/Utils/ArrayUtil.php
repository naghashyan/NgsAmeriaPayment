<?php declare(strict_types=1);

namespace Ngs\AmeriaPayment\Utils;

/**
 * Class ArrayUtil contains functions working with array
 */
final class ArrayUtil
{
    /**
     * Get query parameters from URL
     *
     * @param string $url
     *
     * @return array
     */
    public static function getUrlQueryParams(string $url): array
    {
        $query = parse_url($url, PHP_URL_QUERY);

        if (!$query) {
            return [];
        }

        parse_str($query, $params);

        return $params;
    }

}