<?php

declare(strict_types=1);

namespace JingdongLdopBundle\Tests\Repository;

use JingdongLdopBundle\Entity\JdlConfig;
use JingdongLdopBundle\Entity\PickupOrder;
use JingdongLdopBundle\Enum\JdPickupOrderStatus;
use JingdongLdopBundle\Repository\JdlConfigRepository;
use JingdongLdopBundle\Repository\PickupOrderRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(PickupOrderRepository::class)]
#[RunTestsInSeparateProcesses]
final class PickupOrderRepositoryTest extends AbstractRepositoryTestCase
{
    private PickupOrderRepository $repository;

    private JdlConfigRepository $configRepository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(PickupOrderRepository::class);
        $this->configRepository = self::getService(JdlConfigRepository::class);
    }

    public function testSave(): void
    {
        $order = $this->createPickupOrder();

        $this->repository->save($order);

        $this->assertNotNull($order->getId());
        $this->assertEquals('张三', $order->getSenderName());
    }

    public function testSaveWithoutFlush(): void
    {
        $order = $this->createPickupOrder();

        $this->repository->save($order, false);

        $this->assertNotNull($order->getId());
    }

    public function testRemove(): void
    {
        $order = $this->createPickupOrder();
        $this->repository->save($order);
        $id = $order->getId();

        $this->repository->remove($order);

        $foundOrder = $this->repository->find($id);
        $this->assertNull($foundOrder);
    }

    private function createPickupOrder(string $senderName = '张三'): PickupOrder
    {
        $config = $this->createConfig();
        $this->configRepository->save($config);

        $order = new PickupOrder();
        $order->setConfig($config);
        $order->setSenderName($senderName);
        $order->setSenderMobile('13800138000');
        $order->setSenderAddress('北京市朝阳区测试地址');
        $order->setSenderPostcode('100000');
        $order->setReceiverName('李四');
        $order->setReceiverMobile('13900139000');
        $order->setReceiverAddress('上海市浦东新区测试地址');
        $order->setReceiverPostcode('200000');
        $order->setWeight(1.5);
        $order->setLength(20.0);
        $order->setWidth(20.0);
        $order->setHeight(20.0);
        $order->setRemark('测试取件');
        $order->setStatus(JdPickupOrderStatus::CREATED->value);
        $order->setValid(true);

        return $order;
    }

    private function createConfig(?string $customerCode = null): JdlConfig
    {
        $uniqueCode = null !== $customerCode ? $customerCode : 'TEST_' . uniqid();

        $config = new JdlConfig();
        $config->setCustomerCode($uniqueCode);
        $config->setAppKey('test_app_key_' . $uniqueCode);
        $config->setAppSecret('test_app_secret_' . $uniqueCode);
        $config->setApiEndpoint('https://api.test.com');
        $config->setVersion('2.0');
        $config->setFormat('json');
        $config->setSignMethod('md5');
        $config->setRedirectUri('https://test.com/callback');
        $config->setValid(true);

        return $config;
    }

    public function testFindOneByAssociationConfigShouldReturnMatchingEntity(): void
    {
        $config = $this->createConfig('ASSOC_FINDONE_TEST');
        $this->configRepository->save($config);

        $order = $this->createPickupOrder('关联查找测试用户');
        $order->setConfig($config);
        $this->repository->save($order);

        $foundOrder = $this->repository->findOneBy(['config' => $config]);

        $this->assertNotNull($foundOrder);
        $this->assertEquals($config->getId(), $foundOrder->getConfig()?->getId());
        $this->assertEquals('关联查找测试用户', $foundOrder->getSenderName());
    }

    public function testCountByAssociationConfigShouldReturnCorrectNumber(): void
    {
        $config = $this->createConfig('COUNT_ASSOC_FINDBY_TEST');
        $this->configRepository->save($config);

        $order = $this->createPickupOrder('关联计数测试用户');
        $order->setConfig($config);
        $this->repository->save($order);

        $count = $this->repository->count(['config' => $config]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    protected function createNewEntity(): object
    {
        return $this->createPickupOrder('Test_' . uniqid());
    }

    protected function getRepository(): PickupOrderRepository
    {
        return $this->repository;
    }
}
