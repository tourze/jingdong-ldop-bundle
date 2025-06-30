<?php

namespace JingdongLdopBundle\Tests\Unit\Exception;

use JingdongLdopBundle\Exception\JdlAuthException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class JdlAuthExceptionTest extends TestCase
{
    public function testInstanceOf()
    {
        $exception = new JdlAuthException('Test message');
        $this->assertInstanceOf(RuntimeException::class, $exception);
    }

    public function testMessage()
    {
        $message = '认证失败';
        $exception = new JdlAuthException($message);
        $this->assertSame($message, $exception->getMessage());
    }

    public function testCode()
    {
        $code = 401;
        $exception = new JdlAuthException('Test message', $code);
        $this->assertSame($code, $exception->getCode());
    }
}