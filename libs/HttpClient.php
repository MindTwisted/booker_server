<?php

namespace libs;

class HttpClient
{
    private $curl;
    private $response;

    public function __construct()
    {
        $this->curl = curl_init();
    }

    public function json()
    {
        return json_decode($this->response, true);
    }

    public function get($url)
    {
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);

        $this->response = curl_exec($this->curl);

        curl_close($this->curl);

        return $this;
    }
}