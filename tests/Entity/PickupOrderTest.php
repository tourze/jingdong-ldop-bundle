<?php

namespace JingdongLdopBundle\Tests\Entity;

use JingdongLdopBundle\Entity\JdlConfig;
use JingdongLdopBundle\Entity\PickupOrder;
use JingdongLdopBundle\Enum\JdPickupOrderStatus;
use PHPUnit\Framework\TestCase;

class PickupOrderTest extends TestCase
{
    private PickupOrder $pickupOrder;
    private JdlConfig $config;

    protected function setUp(): void
    {
        $this->pickupOrder = new PickupOrder();

        $this->config = new JdlConfig();
        $this->config->setAppKey('test_app_key')
            ->setAppSecret('test_app_secret')
            ->setCustomerCode('test_customer_code')
            ->setRedirectUri('https://example.com/redirect');
    }

    public function testGetSetSenderName()
    {
        $value = '张三';
        $this->pickupOrder->setSenderName($value);
        $this->assertEquals($value, $this->pickupOrder->getSenderName());
    }

    public function testGetSetSenderMobile()
    {
        $value = '13800138000';
        $this->pickupOrder->setSenderMobile($value);
        $this->assertEquals($value, $this->pickupOrder->getSenderMobile());
    }

    public function testGetSetSenderAddress()
    {
        $value = '北京市海淀区中关村大街1号';
        $this->pickupOrder->setSenderAddress($value);
        $this->assertEquals($value, $this->pickupOrder->getSenderAddress());
    }

    public function testGetSetSenderPostcode()
    {
        $value = '100080';
        $this->pickupOrder->setSenderPostcode($value);
        $this->assertEquals($value, $this->pickupOrder->getSenderPostcode());

        $this->pickupOrder->setSenderPostcode(null);
        $this->assertNull($this->pickupOrder->getSenderPostcode());
    }

    public function testGetSetReceiverName()
    {
        $value = '李四';
        $this->pickupOrder->setReceiverName($value);
        $this->assertEquals($value, $this->pickupOrder->getReceiverName());
    }

    public function testGetSetReceiverMobile()
    {
        $value = '13900139000';
        $this->pickupOrder->setReceiverMobile($value);
        $this->assertEquals($value, $this->pickupOrder->getReceiverMobile());
    }

    public function testGetSetReceiverAddress()
    {
        $value = '上海市浦东新区张江高科技园区';
        $this->pickupOrder->setReceiverAddress($value);
        $this->assertEquals($value, $this->pickupOrder->getReceiverAddress());
    }

    public function testGetSetReceiverPostcode()
    {
        $value = '201203';
        $this->pickupOrder->setReceiverPostcode($value);
        $this->assertEquals($value, $this->pickupOrder->getReceiverPostcode());

        $this->pickupOrder->setReceiverPostcode(null);
        $this->assertNull($this->pickupOrder->getReceiverPostcode());
    }

    public function testGetSetWeight()
    {
        $value = 2.5;
        $this->pickupOrder->setWeight($value);
        $this->assertEquals($value, $this->pickupOrder->getWeight());
    }

    public function testGetSetDimensions()
    {
        // 测试长度
        $length = 30.5;
        $this->pickupOrder->setLength($length);
        $this->assertEquals($length, $this->pickupOrder->getLength());

        // 测试宽度
        $width = 20.8;
        $this->pickupOrder->setWidth($width);
        $this->assertEquals($width, $this->pickupOrder->getWidth());

        // 测试高度
        $height = 15.2;
        $this->pickupOrder->setHeight($height);
        $this->assertEquals($height, $this->pickupOrder->getHeight());

        // 测试空值
        $this->pickupOrder->setLength(null);
        $this->assertNull($this->pickupOrder->getLength());

        $this->pickupOrder->setWidth(null);
        $this->assertNull($this->pickupOrder->getWidth());

        $this->pickupOrder->setHeight(null);
        $this->assertNull($this->pickupOrder->getHeight());
    }

    public function testGetSetRemark()
    {
        $value = '快递请轻拿轻放，易碎物品';
        $this->pickupOrder->setRemark($value);
        $this->assertEquals($value, $this->pickupOrder->getRemark());

        $this->pickupOrder->setRemark(null);
        $this->assertNull($this->pickupOrder->getRemark());
    }

    public function testGetSetStatus()
    {
        // 测试默认状态
        $this->assertEquals(JdPickupOrderStatus::STATUS_CREATED, $this->pickupOrder->getStatus());

        // 测试设置状态
        $value = JdPickupOrderStatus::STATUS_SUBMITTED;
        $this->pickupOrder->setStatus($value);
        $this->assertEquals($value, $this->pickupOrder->getStatus());
    }

    public function testGetSetPackageInfo()
    {
        // 测试包裹名称
        $packageName = '电子产品';
        $this->pickupOrder->setPackageName($packageName);
        $this->assertEquals($packageName, $this->pickupOrder->getPackageName());

        // 测试包裹数量
        $packageQuantity = 3;
        $this->pickupOrder->setPackageQuantity($packageQuantity);
        $this->assertEquals($packageQuantity, $this->pickupOrder->getPackageQuantity());

        // 测试空值
        $this->pickupOrder->setPackageName(null);
        $this->assertNull($this->pickupOrder->getPackageName());

        $this->pickupOrder->setPackageQuantity(null);
        $this->assertNull($this->pickupOrder->getPackageQuantity());
    }

    public function testGetSetSenderLocation()
    {
        // 测试寄件人省份
        $province = '北京市';
        $this->pickupOrder->setSenderProvince($province);
        $this->assertEquals($province, $this->pickupOrder->getSenderProvince());

        // 测试寄件人城市
        $city = '北京市';
        $this->pickupOrder->setSenderCity($city);
        $this->assertEquals($city, $this->pickupOrder->getSenderCity());

        // 测试寄件人区县
        $county = '海淀区';
        $this->pickupOrder->setSenderCounty($county);
        $this->assertEquals($county, $this->pickupOrder->getSenderCounty());

        // 测试空值
        $this->pickupOrder->setSenderProvince(null);
        $this->assertNull($this->pickupOrder->getSenderProvince());

        $this->pickupOrder->setSenderCity(null);
        $this->assertNull($this->pickupOrder->getSenderCity());

        $this->pickupOrder->setSenderCounty(null);
        $this->assertNull($this->pickupOrder->getSenderCounty());
    }

    public function testGetSetReceiverLocation()
    {
        // 测试收件人省份
        $province = '上海市';
        $this->pickupOrder->setReceiverProvince($province);
        $this->assertEquals($province, $this->pickupOrder->getReceiverProvince());

        // 测试收件人城市
        $city = '上海市';
        $this->pickupOrder->setReceiverCity($city);
        $this->assertEquals($city, $this->pickupOrder->getReceiverCity());

        // 测试收件人区县
        $county = '浦东新区';
        $this->pickupOrder->setReceiverCounty($county);
        $this->assertEquals($county, $this->pickupOrder->getReceiverCounty());

        // 测试空值
        $this->pickupOrder->setReceiverProvince(null);
        $this->assertNull($this->pickupOrder->getReceiverProvince());

        $this->pickupOrder->setReceiverCity(null);
        $this->assertNull($this->pickupOrder->getReceiverCity());

        $this->pickupOrder->setReceiverCounty(null);
        $this->assertNull($this->pickupOrder->getReceiverCounty());
    }

    public function testGetSetPickupTime()
    {
        // 测试取件开始时间
        $startTime = new \DateTime('2023-01-01 10:00:00');
        $this->pickupOrder->setPickupStartTime($startTime);
        $this->assertSame($startTime, $this->pickupOrder->getPickupStartTime());

        // 测试取件结束时间
        $endTime = new \DateTime('2023-01-01 12:00:00');
        $this->pickupOrder->setPickupEndTime($endTime);
        $this->assertSame($endTime, $this->pickupOrder->getPickupEndTime());

        // 测试空值
        $this->pickupOrder->setPickupStartTime(null);
        $this->assertNull($this->pickupOrder->getPickupStartTime());

        $this->pickupOrder->setPickupEndTime(null);
        $this->assertNull($this->pickupOrder->getPickupEndTime());
    }

    public function testGetSetPickUpCode()
    {
        $value = 'JD12345678';
        $this->pickupOrder->setPickUpCode($value);
        $this->assertEquals($value, $this->pickupOrder->getPickUpCode());

        $this->pickupOrder->setPickUpCode(null);
        $this->assertNull($this->pickupOrder->getPickUpCode());
    }

    public function testGetSetConfig()
    {
        $this->pickupOrder->setConfig($this->config);
        $this->assertSame($this->config, $this->pickupOrder->getConfig());

        $this->pickupOrder->setConfig(null);
        $this->assertNull($this->pickupOrder->getConfig());
    }

    public function testGetSetValid()
    {
        $this->pickupOrder->setValid(true);
        $this->assertTrue($this->pickupOrder->isValid());

        $this->pickupOrder->setValid(false);
        $this->assertFalse($this->pickupOrder->isValid());

        $this->pickupOrder->setValid(null);
        $this->assertNull($this->pickupOrder->isValid());
    }

    public function testGetSetCreatedBy()
    {
        $value = 'test_user';
        $this->pickupOrder->setCreatedBy($value);
        $this->assertEquals($value, $this->pickupOrder->getCreatedBy());
    }

    public function testGetSetUpdatedBy()
    {
        $value = 'another_user';
        $this->pickupOrder->setUpdatedBy($value);
        $this->assertEquals($value, $this->pickupOrder->getUpdatedBy());
    }

    public function testGetSetCreateTime()
    {
        $now = new \DateTimeImmutable();
        $this->pickupOrder->setCreateTime($now);
        $this->assertSame($now, $this->pickupOrder->getCreateTime());
    }

    public function testGetSetUpdateTime()
    {
        $now = new \DateTimeImmutable();
        $this->pickupOrder->setUpdateTime($now);
        $this->assertSame($now, $this->pickupOrder->getUpdateTime());
    }

    public function testGetId_initiallyNull()
    {
        $this->assertNull($this->pickupOrder->getId());
    }

    public function testObjectStateAfterConstruction()
    {
        $order = new PickupOrder();

        // 默认值断言
        $this->assertEquals(JdPickupOrderStatus::STATUS_CREATED, $order->getStatus());
        $this->assertEquals(0.5, $order->getWeight());
        $this->assertFalse($order->isValid());

        // 必需属性应为null
        $this->assertNull($order->getCreateTime());
        $this->assertNull($order->getUpdateTime());
        $this->assertNull($order->getCreatedBy());
        $this->assertNull($order->getUpdatedBy());
        $this->assertNull($order->getConfig());
    }

    public function testFluentInterface()
    {
        $result = $this->pickupOrder->setSenderName('张三')
            ->setSenderMobile('13800138000')
            ->setSenderAddress('北京市海淀区');

        $this->assertSame($this->pickupOrder, $result);
    }
}
