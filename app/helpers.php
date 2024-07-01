<?php

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
        $xml = simplexml_load_file($url, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xml);
        return json_decode($json, true);
    }
}
