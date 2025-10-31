<?php

declare(strict_types=1);

namespace JingdongLdopBundle\Tests\Exception;

use JingdongLdopBundle\Exception\JingdongLdopBundleException;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(JingdongLdopBundleException::class)]
final class JingdongLdopBundleExceptionTest extends AbstractExceptionTestCase
{
    public function testInstanceOf(): void
    {
        $exception = new JingdongLdopBundleException('Test message');

        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertEquals('Test message', $exception->getMessage());
    }

    public function testMessage(): void
    {
        $exception = new JingdongLdopBundleException('Custom message');

        $this->assertEquals('Custom message', $exception->getMessage());
    }

    public function testCode(): void
    {
        $exception = new JingdongLdopBundleException('Test message', 500);

        $this->assertEquals(500, $exception->getCode());
    }
}
