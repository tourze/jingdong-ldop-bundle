<?php

namespace JingdongLdopBundle\Tests\Unit\Exception;

use JingdongLdopBundle\Exception\JdlConfigException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class JdlConfigExceptionTest extends TestCase
{
    public function testInstanceOf()
    {
        $exception = new JdlConfigException('Test message');
        $this->assertInstanceOf(RuntimeException::class, $exception);
    }

    public function testMessage()
    {
        $message = '配置错误';
        $exception = new JdlConfigException($message);
        $this->assertSame($message, $exception->getMessage());
    }

    public function testCode()
    {
        $code = 500;
        $exception = new JdlConfigException('Test message', $code);
        $this->assertSame($code, $exception->getCode());
    }
}