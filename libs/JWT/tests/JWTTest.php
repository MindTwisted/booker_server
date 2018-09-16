<?php

require_once 'libs/JWT/JWT.php';

use libs\JWT\JWT;

class JWTTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tokens for tests
     */
    private static $primaryToken;
    private static $secondaryToken;
    private static $tokenWithExp;

    /**
     * Generate new tokens for tests
     */
    public static function setUpBeforeClass()
    {
        self::$primaryToken = JWT::sign(['data' => 'something'], 'secret');
        self::$secondaryToken = JWT::sign(['id' => '2131232'], 'wrong');
        self::$tokenWithExp = JWT::sign(['data' => 'token_with_exp'], 'another', ['expiresIn' => 3]);
    }

    /**
     * Test if token is correctly formatted
     */
    public function testTokenValidFormat()
    {
        $pattern = '/^[\w\-]+\.[\w\-]+\.[\w\-]+$/';

        $this->assertRegExp($pattern, self::$primaryToken);
        $this->assertRegExp($pattern, self::$secondaryToken);
        $this->assertRegExp($pattern, self::$tokenWithExp);
    }

    /**
     * Test verification failure with invalid token or invalid secretKey
     */
    public function testVerifyInvalidToken()
    {
        $tokenVerify1 = JWT::verify('fd3dsf23.cvx332.sds', 'qwert');
        $tokenVerify2 = JWT::verify(self::$primaryToken, 'qwert');
        $tokenVerify3 = JWT::verify(self::$secondaryToken, 'qwert');
        $tokenVerify4 = JWT::verify(self::$tokenWithExp, 'qwert');

        $this->assertFalse($tokenVerify1);
        $this->assertFalse($tokenVerify2);
        $this->assertFalse($tokenVerify3);
        $this->assertFalse($tokenVerify4);
    }

    /**
     * Test verification success with valid token and valid secretKey
     */
    public function testVerifyValidToken()
    {
        $tokenVerify1 = JWT::verify(self::$primaryToken, 'secret');
        $tokenVerify2 = JWT::verify(self::$secondaryToken, 'wrong');
        $tokenVerify3 = JWT::verify(self::$tokenWithExp, 'another');

        $this->assertTrue(!!$tokenVerify1);
        $this->assertTrue(!!$tokenVerify2);
        $this->assertTrue(!!$tokenVerify3);
    }

    /**
     * Test verification returns right payload
     */
    public function testVerifyReturnsRightPayload()
    {
        $tokenVerify1 = JWT::verify(self::$primaryToken, 'secret');
        $tokenVerify2 = JWT::verify(self::$secondaryToken, 'wrong');
        $tokenVerify3 = JWT::verify(self::$tokenWithExp, 'another');

        $this->assertEquals(['data' => 'something'], $tokenVerify1);
        $this->assertEquals(['id' => '2131232'], $tokenVerify2);
        $this->assertEquals(['data' => 'token_with_exp'], $tokenVerify3);
    }

    /**
     * Test verify failure expired token
     */
    public function testVerifyExpiredToken()
    {
        sleep(5);

        $tokenVerify = JWT::verify(self::$tokenWithExp, 'another');

        $this->assertFalse($tokenVerify);
    }
}