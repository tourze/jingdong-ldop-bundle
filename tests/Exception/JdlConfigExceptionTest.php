<?php

declare(strict_types=1);

namespace JingdongLdopBundle\Tests\Exception;

use JingdongLdopBundle\Exception\JdlConfigException;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(JdlConfigException::class)]
final class JdlConfigExceptionTest extends AbstractExceptionTestCase
{
    public function testInstanceOf(): void
    {
        $exception = new JdlConfigException('Test message');
        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }

    public function testMessage(): void
    {
        $message = '配置错误';
        $exception = new JdlConfigException($message);
        $this->assertSame($message, $exception->getMessage());
    }

    public function testCode(): void
    {
        $code = 500;
        $exception = new JdlConfigException('Test message', $code);
        $this->assertSame($code, $exception->getCode());
    }
}
