<?php

declare(strict_types=1);

namespace JingdongLdopBundle\Tests;

use JingdongLdopBundle\JingdongLdopBundle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;

/**
 * @internal
 */
#[CoversClass(JingdongLdopBundle::class)]
#[RunTestsInSeparateProcesses]
final class JingdongLdopBundleTest extends AbstractBundleTestCase
{
}
