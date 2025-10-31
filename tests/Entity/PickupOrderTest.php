<?php

declare(strict_types=1);

namespace JingdongLdopBundle\Tests\Entity;

use JingdongLdopBundle\Entity\JdlConfig;
use JingdongLdopBundle\Entity\PickupOrder;
use JingdongLdopBundle\Enum\JdPickupOrderStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(PickupOrder::class)]
final class PickupOrderTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new PickupOrder();
    }

    /** @return iterable<string, array{string, mixed}> */
    public static function propertiesProvider(): iterable
    {
        return [
            'senderName' => ['senderName', '张三'],
            'senderMobile' => ['senderMobile', '13800138000'],
            'senderAddress' => ['senderAddress', '北京市海淀区中关村大街1号'],
            'senderPostcode' => ['senderPostcode', '100080'],
            'receiverName' => ['receiverName', '李四'],
            'receiverMobile' => ['receiverMobile', '13900139000'],
            'receiverAddress' => ['receiverAddress', '上海市浦东新区张江高科技园区'],
            'receiverPostcode' => ['receiverPostcode', '201203'],
            'weight' => ['weight', 2.5],
            'length' => ['length', 30.5],
            'width' => ['width', 20.8],
            'height' => ['height', 15.2],
            'remark' => ['remark', '快递请轻拿轻放，易碎物品'],
            'status' => ['status', JdPickupOrderStatus::STATUS_SUBMITTED],
            'packageName' => ['packageName', '电子产品'],
            'packageQuantity' => ['packageQuantity', 3],
            'senderProvince' => ['senderProvince', '北京市'],
            'senderCity' => ['senderCity', '北京市'],
            'senderCounty' => ['senderCounty', '海淀区'],
            'receiverProvince' => ['receiverProvince', '上海市'],
            'receiverCity' => ['receiverCity', '上海市'],
            'receiverCounty' => ['receiverCounty', '浦东新区'],
            'pickupStartTime' => ['pickupStartTime', new \DateTimeImmutable()],
            'pickupEndTime' => ['pickupEndTime', new \DateTimeImmutable()],
            'pickUpCode' => ['pickUpCode', 'JD12345678'],
            'valid' => ['valid', true],
            'createdBy' => ['createdBy', 'test_user'],
            'updatedBy' => ['updatedBy', 'another_user'],
            'createTime' => ['createTime', new \DateTimeImmutable()],
            'updateTime' => ['updateTime', new \DateTimeImmutable()],
        ];
    }

    public function testObjectStateAfterConstruction(): void
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

    public function testSetConfig(): void
    {
        $order = new PickupOrder();

        $config = new JdlConfig();
        $config->setAppKey('test_app_key');
        $config->setAppSecret('test_app_secret');
        $config->setCustomerCode('test_customer_code');
        $config->setRedirectUri('https://example.com/redirect');

        $order->setConfig($config);
        $this->assertSame($config, $order->getConfig());

        $order->setConfig(null);
        $this->assertNull($order->getConfig());
    }
}
