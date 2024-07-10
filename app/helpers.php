<?php

use Illuminate\Support\Facades\Storage;
use Jackiedo\XmlArray\Xml2Array;

if (!function_exists('array_from_xml')) {
    /**
     * Obtiene un xml remoto y lo retorna como
     * un array
     *
     * @param string $url
     * @author Daniel Lucia <daniellucia84@gmail.com>
     */
    function array_from_xml(string $url)
    {

        try {
            $headers = @get_headers($url);

            if ($headers && strpos($headers[0], '200')) {
                $xml = Xml2Array::convert(file_get_contents($url))->toArray();
                $json = json_encode($xml);
                return json_decode($json, true);
            } else {
                return false;
            }

        } catch (\Exception $e) {
            return false;
        }
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


if (!function_exists('array_from_opml')) {
    /**
     * Función que convierte un archivo OPML en un array
     *
     * @param string $file
     * @return array
     */
    function array_from_opml(string $file): array
    {
        $xml = simplexml_load_string(Storage::get($file));
        return json_decode(json_encode($xml), true);
    }
}
