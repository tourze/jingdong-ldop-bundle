<?php

declare(strict_types=1);

namespace JingdongLdopBundle\Tests\Exception;

use JingdongLdopBundle\Exception\JdlApiException;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(JdlApiException::class)]
final class JdlApiExceptionTest extends AbstractExceptionTestCase
{
    public function testInstanceOf(): void
    {
        $exception = new JdlApiException('Test message');
        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }

    public function testMessage(): void
    {
        $message = 'API调用失败';
        $exception = new JdlApiException($message);
        $this->assertSame($message, $exception->getMessage());
    }

    public function testCode(): void
    {
        $code = 500;
        $exception = new JdlApiException('Test message', $code);
        $this->assertSame($code, $exception->getCode());
    }
}
