<?php

namespace libs;

class HttpClient
{
    private $response;
    private $code;

    /**
     * Constructor
     */
    public function __construct($response, $code)
    {
        $this->response = $response;
        $this->code = $code;
    }

    /**
     * Convert json response into associative array
     */
    public function jsonToArray()
    {
        return json_decode($this->response, true);
    }

    /**
     * Get response http status code
     */
    public function code()
    {
        return $this->code;
    }

    /**
     * Perform http GET request
     */
    public static function get($url, array $headers = [])
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

        curl_close($ch);

        return new self($response, $code);
    }

    /**
     * Perform http POST request
     */
    public static function post($url, $body, array $headers = [])
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        
        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

        curl_close($ch);

        return new self($response, $code);
    }

    /**
     * Perform http PUT request
     */
    public static function put($url, $body, array $headers = [])
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($body));
        
        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

        curl_close($ch);

        return new self($response, $code);
    }

    /**
     * Perform http DELETE request
     */
    public static function delete($url, $body, array $headers = [])
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($body));
        
        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

        curl_close($ch);

        return new self($response, $code);
    }
}