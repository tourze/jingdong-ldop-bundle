<?php

declare(strict_types=1);

namespace JingdongLdopBundle\Tests\Entity;

use JingdongLdopBundle\Entity\JdlAccessToken;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(JdlAccessToken::class)]
final class JdlAccessTokenTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new JdlAccessToken();
    }

    /** @return iterable<string, array{string, mixed}> */
    public static function propertiesProvider(): iterable
    {
        return [
            'accessToken' => ['accessToken', 'test_token'],
            'refreshToken' => ['refreshToken', 'refresh_token_value'],
            'scope' => ['scope', 'read write'],
            'expireTime' => ['expireTime', new \DateTimeImmutable()],
            'createTime' => ['createTime', new \DateTimeImmutable()],
            'updateTime' => ['updateTime', new \DateTimeImmutable()],
        ];
    }

    public function testObjectStateAfterConstruction(): void
    {
        $token = new JdlAccessToken();

        // 必需属性应为null
        $this->assertNull($token->getCreateTime());
        $this->assertNull($token->getUpdateTime());
        $this->assertNull($token->getId());
        $this->assertEquals('', $token->getScope());
        $this->assertNull($token->getExpireTime());
    }
}
