<?php

namespace libs;

class View
{
    /**
     * Current render type
     */
    private static $renderType = 'json';

    /**
     * Available render types
     */
    private static $availableRenderTypes = ['json', 'html', 'txt', 'xml'];

    /**
     * Available status messages
     */
    private static $status = [
        200 => '200 OK',
        400 => '400 Bad Request',
        401 => '401 Unauthorized',
        403 => '403 Forbidden',
        404 => '404 Not Found',
        405 => '405 Method Not Allowed',
        409 => '409 Conflict',
        422 => '422 Unprocessable Entity',
        500 => '500 Internal Server Error',
    ];

    /**
     * Array to XML convert
     */
    private static function arrayToXml($data, &$xml_data)
    {
        foreach ($data as $key => $value) {
            if (is_numeric($key)) {
                $key = 'item' . $key; //dealing with <0/>..<n/> issues
            }
            if (is_array($value)) {
                $subnode = $xml_data->addChild($key);
                self::arrayToXml($value, $subnode);
            } else {
                $xml_data->addChild("$key", htmlspecialchars("$value"));
            }
        }
    }

    /**
     * JSON response
     */
    private static function json($data, $code)
    {
        // Treat this as json
        header('Content-Type: application/json');

        // Return the encoded json
        echo json_encode([
            'status' => $code < 300, // success or not?
            'message' => $data,
        ]);

        die();
    }

    /**
     * HTML response
     */
    private static function html($data)
    {
        // Treat this as html document
        header("Content-Type: text/plain");

        // Return formatted result
        print_r($data);

        die();
    }

    /**
     * TXT response
     */
    private static function txt($data)
    {
        $textFileName = 'text_response' . time() . '.txt';

        // Treat this as text file
        header("Content-Type: text/plain");
        header("Content-Disposition: attachment; filename=\"$textFileName\"");

        // Return formatted result
        print_r($data);

        die();
    }

    /**
     * XML response
     */
    private static function xml($data)
    {
        if (!is_array($data))
        {
            $data = [$data];
        }
        
        // Treat this as xml
        header("Content-Type: text/xml");

        // Creating object of SimpleXMLElement
        $xml_data = new \SimpleXMLElement('<?xml version="1.0"?><data></data>');

        // Function call to convert array to xml
        self::arrayToXml($data, $xml_data);

        // Return xml result
        echo $xml_data->asXML();

        die();
    }

    /**
     * Set render type
     */
    public static function setRenderType($type)
    {
        self::$renderType = in_array($type, self::$availableRenderTypes) ? $type : 'json';
    }

    /**
     * Render view
     */
    public static function render($data, $code = 200)
    {
        // Clear the old headers
        header_remove();

        // Set the actual code
        http_response_code($code);

        // Ok, validation error, or failure
        header('Status: ' . self::$status[$code]);

        switch (self::$renderType) {
            case 'json':
                self::json($data, $code);
                break;
            case 'html':
                self::html($data, $code);
                break;
            case 'txt':
                self::txt($data, $code);
                break;
            case 'xml':
                self::xml($data, $code);
                break;
        }
    }
}
