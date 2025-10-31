<?php

declare(strict_types=1);

namespace JingdongLdopBundle\Tests\Exception;

use JingdongLdopBundle\Exception\JdlAuthException;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(JdlAuthException::class)]
final class JdlAuthExceptionTest extends AbstractExceptionTestCase
{
    public function testInstanceOf(): void
    {
        $exception = new JdlAuthException('Test message');
        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }

    public function testMessage(): void
    {
        $message = '认证失败';
        $exception = new JdlAuthException($message);
        $this->assertSame($message, $exception->getMessage());
    }

    public function testCode(): void
    {
        $code = 401;
        $exception = new JdlAuthException('Test message', $code);
        $this->assertSame($code, $exception->getCode());
    }
}
