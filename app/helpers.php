<?php

use Jackiedo\XmlArray\Xml2Array;

if (!function_exists('array_from_xml')) {
    /**
     * Obtiene un xml remoto y lo retorna como
     * un array
     *
     * @param string $url
     * @author Daniel Lucia <daniellucia84@gmail.com>
     */
    function array_from_xml(string $url): array
    {
        $xml = file_get_contents($url);
        if (!is_valid_xml($xml)) {
            return [];
        }

        return Xml2Array::convert($xml)->toArray();
    }
}

if (!function_exists('is_valid_xml')) {
    /**
     * Comprueba si un xml es válido
     *
     * @param string $xml
     * @return boolean
     */
    function is_valid_xml(string $xml): bool
    {
        libxml_use_internal_errors(true);

        $doc = simplexml_load_string($xml);
        $xml = explode("\n", $xml);

        if ($doc !== false) {
            $errors = libxml_get_errors();

            if (!empty($errors)) {
                return true;
            }

            libxml_clear_errors();
        }

        return false;
    }
}
