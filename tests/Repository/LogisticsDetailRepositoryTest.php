<?php

declare(strict_types=1);

namespace JingdongLdopBundle\Tests\Repository;

use JingdongLdopBundle\Entity\LogisticsDetail;
use JingdongLdopBundle\Enum\JdLogisticsStatus;
use JingdongLdopBundle\Repository\LogisticsDetailRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(LogisticsDetailRepository::class)]
#[RunTestsInSeparateProcesses]
final class LogisticsDetailRepositoryTest extends AbstractRepositoryTestCase
{
    private LogisticsDetailRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(LogisticsDetailRepository::class);
    }

    public function testSave(): void
    {
        $detail = $this->createLogisticsDetail();

        $this->repository->save($detail);

        $this->assertNotNull($detail->getId());
        $this->assertEquals('JD12345678901234567890', $detail->getWaybillCode());
    }

    public function testSaveWithoutFlush(): void
    {
        $detail = $this->createLogisticsDetail();

        $this->repository->save($detail, false);

        $this->assertNotNull($detail->getId());
    }

    public function testRemove(): void
    {
        $detail = $this->createLogisticsDetail();
        $this->repository->save($detail);
        $id = $detail->getId();

        $this->repository->remove($detail);

        $foundDetail = $this->repository->find($id);
        $this->assertNull($foundDetail);
    }

    private function createLogisticsDetail(string $waybillCode = 'JD12345678901234567890'): LogisticsDetail
    {
        $detail = new LogisticsDetail();
        $detail->setWaybillCode($waybillCode);
        $detail->setCustomerCode('TEST_CUSTOMER');
        $detail->setOrderCode('ORDER_' . uniqid());
        $detail->setOperateTime(new \DateTimeImmutable());
        $detail->setOperateRemark('包裹已发出');
        $detail->setOperateSite('北京分拣中心');
        $detail->setOperateType('发出');
        $detail->setOperateUser('操作员001');
        $detail->setWaybillStatus(JdLogisticsStatus::STATUS_IN_TRANSIT);
        $detail->setNextSite('上海分拣中心');
        $detail->setNextCity('上海市');

        return $detail;
    }

    protected function createNewEntity(): object
    {
        return $this->createLogisticsDetail('JD_' . uniqid());
    }

    protected function getRepository(): LogisticsDetailRepository
    {
        return $this->repository;
    }
}
