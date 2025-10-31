<?php

declare(strict_types=1);

namespace JingdongLdopBundle\Tests\Entity;

use JingdongLdopBundle\Entity\LogisticsDetail;
use JingdongLdopBundle\Enum\JdLogisticsStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(LogisticsDetail::class)]
final class LogisticsDetailTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new LogisticsDetail();
    }

    /** @return iterable<string, array{string, mixed}> */
    public static function propertiesProvider(): iterable
    {
        return [
            'waybillCode' => ['waybillCode', 'JD12345678'],
            'customerCode' => ['customerCode', 'TEST_CUSTOMER_CODE'],
            'orderCode' => ['orderCode', 'ORDER123456'],
            'operateRemark' => ['operateRemark', '快递已揽收'],
            'operateSite' => ['operateSite', '北京分拣中心'],
            'operateType' => ['operateType', '1'],
            'operateUser' => ['operateUser', '快递员张三'],
            'waybillStatus' => ['waybillStatus', JdLogisticsStatus::STATUS_COLLECTED],
            'nextSite' => ['nextSite', '上海分拣中心'],
            'nextCity' => ['nextCity', '上海'],
            'createdBy' => ['createdBy', 'test_user'],
            'updatedBy' => ['updatedBy', 'another_user'],
            'createTime' => ['createTime', new \DateTimeImmutable()],
            'updateTime' => ['updateTime', new \DateTimeImmutable()],
        ];
    }

    public function testGetSetOperateTimeWithStringInput(): void
    {
        // 测试DateTimeImmutable输入
        $dateString = '2023-01-01 12:00:00';
        $dateTime = new \DateTimeImmutable($dateString);
        $detail = new LogisticsDetail();
        $detail->setOperateTime($dateTime);
        $this->assertInstanceOf(\DateTimeImmutable::class, $detail->getOperateTime());
        $this->assertEquals($dateString, $detail->getOperateTime()->format('Y-m-d H:i:s'));
    }
}
