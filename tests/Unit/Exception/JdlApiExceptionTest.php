<?php

namespace JingdongLdopBundle\Tests\Unit\Exception;

use JingdongLdopBundle\Exception\JdlApiException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class JdlApiExceptionTest extends TestCase
{
    public function testInstanceOf()
    {
        $exception = new JdlApiException('Test message');
        $this->assertInstanceOf(RuntimeException::class, $exception);
    }

    public function testMessage()
    {
        $message = 'API调用失败';
        $exception = new JdlApiException($message);
        $this->assertSame($message, $exception->getMessage());
    }

    public function testCode()
    {
        $code = 500;
        $exception = new JdlApiException('Test message', $code);
        $this->assertSame($code, $exception->getCode());
    }
}