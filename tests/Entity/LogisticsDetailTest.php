<?php

namespace JingdongLdopBundle\Tests\Entity;

use JingdongLdopBundle\Entity\LogisticsDetail;
use JingdongLdopBundle\Enum\JdLogisticsStatus;
use PHPUnit\Framework\TestCase;

class LogisticsDetailTest extends TestCase
{
    private LogisticsDetail $logisticsDetail;

    protected function setUp(): void
    {
        $this->logisticsDetail = new LogisticsDetail();
    }

    public function testGetSetWaybillCode()
    {
        $value = 'JD12345678';
        $this->logisticsDetail->setWaybillCode($value);
        $this->assertEquals($value, $this->logisticsDetail->getWaybillCode());
    }

    public function testGetSetCustomerCode()
    {
        $value = 'TEST_CUSTOMER_CODE';
        $this->logisticsDetail->setCustomerCode($value);
        $this->assertEquals($value, $this->logisticsDetail->getCustomerCode());
    }

    public function testGetSetOrderCode()
    {
        $value = 'ORDER123456';
        $this->logisticsDetail->setOrderCode($value);
        $this->assertEquals($value, $this->logisticsDetail->getOrderCode());
    }

    public function testGetSetOperateTime()
    {
        // 测试字符串日期输入
        $dateString = '2023-01-01 12:00:00';
        $this->logisticsDetail->setOperateTime($dateString);
        $this->assertInstanceOf(\DateTime::class, $this->logisticsDetail->getOperateTime());
        $this->assertEquals($dateString, $this->logisticsDetail->getOperateTime()->format('Y-m-d H:i:s'));
    }

    public function testGetSetOperateRemark()
    {
        $value = '快递已揽收';
        $this->logisticsDetail->setOperateRemark($value);
        $this->assertEquals($value, $this->logisticsDetail->getOperateRemark());
    }

    public function testGetSetOperateSite()
    {
        $value = '北京分拣中心';
        $this->logisticsDetail->setOperateSite($value);
        $this->assertEquals($value, $this->logisticsDetail->getOperateSite());
    }

    public function testGetSetOperateType()
    {
        $value = '1';
        $this->logisticsDetail->setOperateType($value);
        $this->assertEquals($value, $this->logisticsDetail->getOperateType());
    }

    public function testGetSetOperateUser()
    {
        $value = '快递员张三';
        $this->logisticsDetail->setOperateUser($value);
        $this->assertEquals($value, $this->logisticsDetail->getOperateUser());

        $this->logisticsDetail->setOperateUser(null);
        $this->assertNull($this->logisticsDetail->getOperateUser());
    }

    public function testGetSetWaybillStatus()
    {
        $status = JdLogisticsStatus::STATUS_COLLECTED;
        $this->logisticsDetail->setWaybillStatus($status);
        $this->assertSame($status, $this->logisticsDetail->getWaybillStatus());
    }

    public function testGetSetNextSite()
    {
        $value = '上海分拣中心';
        $this->logisticsDetail->setNextSite($value);
        $this->assertEquals($value, $this->logisticsDetail->getNextSite());

        $this->logisticsDetail->setNextSite(null);
        $this->assertNull($this->logisticsDetail->getNextSite());
    }

    public function testGetSetNextCity()
    {
        $value = '上海';
        $this->logisticsDetail->setNextCity($value);
        $this->assertEquals($value, $this->logisticsDetail->getNextCity());

        $this->logisticsDetail->setNextCity(null);
        $this->assertNull($this->logisticsDetail->getNextCity());
    }

    public function testGetSetCreatedBy()
    {
        $value = 'test_user';
        $this->logisticsDetail->setCreatedBy($value);
        $this->assertEquals($value, $this->logisticsDetail->getCreatedBy());
    }

    public function testGetSetUpdatedBy()
    {
        $value = 'another_user';
        $this->logisticsDetail->setUpdatedBy($value);
        $this->assertEquals($value, $this->logisticsDetail->getUpdatedBy());
    }

    public function testGetSetCreateTime()
    {
        $now = new \DateTimeImmutable();
        $this->logisticsDetail->setCreateTime($now);
        $this->assertSame($now, $this->logisticsDetail->getCreateTime());
    }

    public function testGetSetUpdateTime()
    {
        $now = new \DateTimeImmutable();
        $this->logisticsDetail->setUpdateTime($now);
        $this->assertSame($now, $this->logisticsDetail->getUpdateTime());
    }

    public function testGetId_initiallyNull()
    {
        $this->assertNull($this->logisticsDetail->getId());
    }

    public function testFluentInterface()
    {
        $result = $this->logisticsDetail->setWaybillCode('JD12345678')
            ->setCustomerCode('TEST_CUSTOMER_CODE')
            ->setOrderCode('ORDER123456');

        $this->assertSame($this->logisticsDetail, $result);
    }
}
