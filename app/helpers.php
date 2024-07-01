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
        return Xml2Array::convert($xml)->toArray();
    }
}
