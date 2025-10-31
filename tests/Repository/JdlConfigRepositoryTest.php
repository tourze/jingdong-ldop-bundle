<?php

declare(strict_types=1);

namespace JingdongLdopBundle\Tests\Repository;

use JingdongLdopBundle\Entity\JdlConfig;
use JingdongLdopBundle\Repository\JdlConfigRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(JdlConfigRepository::class)]
#[RunTestsInSeparateProcesses]
final class JdlConfigRepositoryTest extends AbstractRepositoryTestCase
{
    private JdlConfigRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(JdlConfigRepository::class);
    }

    public function testFindAllValid(): void
    {
        $result = $this->repository->findAllValid();
        $this->assertIsArray($result);
    }

    public function testGetDefaultConfig(): void
    {
        $config = $this->createConfig('TEST001', true);
        $this->repository->save($config);

        $defaultConfig = $this->repository->getDefaultConfig();
        $this->assertInstanceOf(JdlConfig::class, $defaultConfig);
        $this->assertTrue($defaultConfig->isValid());
    }

    public function testGetDefaultConfigWhenNoValidConfigExists(): void
    {
        $validConfigs = $this->repository->findAllValid();
        foreach ($validConfigs as $validConfig) {
            $validConfig->setValid(false);
            $this->repository->save($validConfig);
        }

        $config = $this->createConfig('TEST002', false);
        $this->repository->save($config);

        $defaultConfig = $this->repository->getDefaultConfig();
        $this->assertNull($defaultConfig);
    }

    public function testSave(): void
    {
        $config = $this->createConfig('SAVE_TEST', true);

        $this->repository->save($config);

        $this->assertNotNull($config->getId());
        $this->assertEquals('SAVE_TEST', $config->getCustomerCode());
    }

    public function testSaveWithoutFlush(): void
    {
        $config = $this->createConfig('SAVE_NO_FLUSH', true);

        $this->repository->save($config, false);

        $this->assertNotNull($config->getId());
    }

    public function testRemove(): void
    {
        $config = $this->createConfig('REMOVE_TEST', true);
        $this->repository->save($config);
        $id = $config->getId();

        $this->repository->remove($config);

        $foundConfig = $this->repository->find($id);
        $this->assertNull($foundConfig);
    }

    private function createConfig(string $customerCode, bool $valid): JdlConfig
    {
        $config = new JdlConfig();
        $config->setCustomerCode($customerCode);
        $config->setAppKey('test_app_key_' . $customerCode);
        $config->setAppSecret('test_app_secret_' . $customerCode);
        $config->setApiEndpoint('https://api.jdl.com');
        $config->setVersion('2.0');
        $config->setFormat('json');
        $config->setSignMethod('md5');
        $config->setRedirectUri('https://example.com/callback');
        $config->setValid($valid);

        return $config;
    }

    protected function createNewEntity(): object
    {
        return $this->createConfig('test_' . uniqid(), true);
    }

    protected function getRepository(): JdlConfigRepository
    {
        return $this->repository;
    }
}
