<?php

namespace libs\JWT;

class JWT {

    /**
     * Generate new JWT
     */
    public static function sign($payloadData, $secretKey, $options = [])
    {
        // Create token header as a JSON string
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);

        // Create token payload as a JSON string
        if (isset($options['expiresIn']))
        {
            $payloadData['exp'] = time() + $options['expiresIn'];
        }

        $payload = json_encode($payloadData);

        // Encode Header to Base64Url String
        $base64UrlHeader = str_replace(
            ['+', '/', '='], 
            ['-', '_', ''], 
            base64_encode($header)
        );

        // Encode Payload to Base64Url String
        $base64UrlPayload = str_replace(
            ['+', '/', '='], 
            ['-', '_', ''], 
            base64_encode($payload)
        );

        // Create Signature Hash
        $signature = hash_hmac(
            'sha256', 
            $base64UrlHeader . "." . $base64UrlPayload, 
            $secretKey, 
            true
        );

        // Encode Signature to Base64Url String
        $base64UrlSignature = str_replace(
            ['+', '/', '='], 
            ['-', '_', ''], 
            base64_encode($signature)
        );

        // Create JWT
        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

        return $jwt;
    }

    /**
     * Verify provided JWT
     */
    public static function verify($token, $secretKey)
    {
        if (!preg_match('/^[\w\-]+\.[\w\-]+\.[\w\-]+$/', $token)) 
        {
            return false;
        }

        list($header, $payload, $signature) = explode('.', $token);

        $verifySignature = hash_hmac(
            'sha256',
            $header . "." . $payload, 
            $secretKey, 
            true
        );
        $verifySignature = str_replace(
            ['+', '/', '='], 
            ['-', '_', ''], 
            base64_encode($verifySignature)
        );

        if ($verifySignature !== $signature)
        {
            return false;
        }

        $decodedPayload = json_decode(base64_decode($payload), true);

        if (isset($decodedPayload['exp']))
        {
            if (+$decodedPayload['exp'] < +time())
            {
                return false;
            }

            unset($decodedPayload['exp']);
        }

        return $decodedPayload;
    }
}