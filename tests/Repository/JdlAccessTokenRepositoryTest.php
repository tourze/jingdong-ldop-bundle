<?php

declare(strict_types=1);

namespace JingdongLdopBundle\Tests\Repository;

use JingdongLdopBundle\Entity\JdlAccessToken;
use JingdongLdopBundle\Repository\JdlAccessTokenRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(JdlAccessTokenRepository::class)]
#[RunTestsInSeparateProcesses]
final class JdlAccessTokenRepositoryTest extends AbstractRepositoryTestCase
{
    private JdlAccessTokenRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(JdlAccessTokenRepository::class);
    }

    public function testSave(): void
    {
        $token = new JdlAccessToken();
        $token->setAccessToken('test_access_token_123');
        $token->setRefreshToken('test_refresh_token_456');
        $token->setScope('read write');
        $token->setExpireTime(new \DateTimeImmutable('+1 hour'));

        $this->repository->save($token);

        $this->assertNotNull($token->getId());
        $this->assertEquals('test_access_token_123', $token->getAccessToken());
    }

    public function testSaveWithoutFlush(): void
    {
        $token = new JdlAccessToken();
        $token->setAccessToken('test_access_token_no_flush');
        $token->setRefreshToken('test_refresh_token_no_flush');

        $this->repository->save($token, false);

        $this->assertNotNull($token->getId());
    }

    public function testRemove(): void
    {
        $token = new JdlAccessToken();
        $token->setAccessToken('test_access_token_remove');
        $token->setRefreshToken('test_refresh_token_remove');

        $this->repository->save($token);
        $id = $token->getId();

        $this->repository->remove($token);

        $foundToken = $this->repository->find($id);
        $this->assertNull($foundToken);
    }

    protected function createNewEntity(): object
    {
        $token = new JdlAccessToken();
        $token->setAccessToken('test_' . uniqid());
        $token->setRefreshToken('refresh_' . uniqid());
        $token->setScope('test');
        $token->setExpireTime(new \DateTimeImmutable('+1 hour'));

        return $token;
    }

    protected function getRepository(): JdlAccessTokenRepository
    {
        return $this->repository;
    }
}
