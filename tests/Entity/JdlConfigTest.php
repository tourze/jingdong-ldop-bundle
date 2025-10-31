<?php

declare(strict_types=1);

namespace JingdongLdopBundle\Tests\Entity;

use JingdongLdopBundle\Entity\JdlConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(JdlConfig::class)]
final class JdlConfigTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new JdlConfig();
    }

    /** @return iterable<string, array{string, mixed}> */
    public static function propertiesProvider(): iterable
    {
        return [
            'customerCode' => ['customerCode', 'TEST_CUSTOMER_CODE'],
            'appKey' => ['appKey', 'TEST_APP_KEY'],
            'appSecret' => ['appSecret', 'TEST_APP_SECRET'],
            'apiEndpoint' => ['apiEndpoint', 'https://test-api.jd.com'],
            'version' => ['version', '3.0'],
            'format' => ['format', 'json'],
            'signMethod' => ['signMethod', 'md5'],
            'remark' => ['remark', 'Test remark'],
            'redirectUri' => ['redirectUri', 'https://test-redirect.example.com/callback'],
            'valid' => ['valid', true],
            'createdBy' => ['createdBy', 'test_user'],
            'updatedBy' => ['updatedBy', 'another_user'],
            'createTime' => ['createTime', new \DateTimeImmutable()],
            'updateTime' => ['updateTime', new \DateTimeImmutable()],
        ];
    }

    public function testObjectStateAfterConstruction(): void
    {
        $config = new JdlConfig();

        // 默认值断言
        $this->assertEquals('https://api.jdl.com', $config->getApiEndpoint());
        $this->assertEquals('2.0', $config->getVersion());
        $this->assertEquals('json', $config->getFormat());
        $this->assertEquals('md5', $config->getSignMethod());
        $this->assertFalse($config->isValid());

        // 必需属性应为null
        $this->assertNull($config->getCreateTime());
        $this->assertNull($config->getUpdateTime());
        $this->assertNull($config->getCreatedBy());
        $this->assertNull($config->getUpdatedBy());
    }
}
