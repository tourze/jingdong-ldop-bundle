<?php

declare(strict_types=1);

namespace JingdongLdopBundle\Tests\Enum;

use JingdongLdopBundle\Enum\JdPickupOrderStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;

/**
 * @internal
 */
#[CoversClass(JdPickupOrderStatus::class)]
final class JdPickupOrderStatusTest extends AbstractEnumTestCase
{
    public function testPickupOrderStatusConstants(): void
    {
        // 验证枚举常量值
        $this->assertEquals('CREATED', JdPickupOrderStatus::STATUS_CREATED);
        $this->assertEquals('SUBMITTED', JdPickupOrderStatus::STATUS_SUBMITTED);
        $this->assertEquals('UPDATED', JdPickupOrderStatus::STATUS_UPDATED);
        $this->assertEquals('CANCELLED', JdPickupOrderStatus::STATUS_CANCELLED);

        // 验证枚举实例值
        $this->assertEquals('CREATED', JdPickupOrderStatus::CREATED->value);
        $this->assertEquals('SUBMITTED', JdPickupOrderStatus::SUBMITTED->value);
        $this->assertEquals('UPDATED', JdPickupOrderStatus::UPDATED->value);
        $this->assertEquals('CANCELLED', JdPickupOrderStatus::CANCELLED->value);
    }

    public function testToArray(): void
    {
        $created = JdPickupOrderStatus::CREATED->toArray();
        $this->assertIsArray($created);
        $this->assertEquals(['value' => 'CREATED', 'label' => '已创建'], $created);

        $submitted = JdPickupOrderStatus::SUBMITTED->toArray();
        $this->assertIsArray($submitted);
        $this->assertEquals(['value' => 'SUBMITTED', 'label' => '已提交'], $submitted);
    }

    public function testSpecificBusinessLogic(): void
    {
        // 测试特定的业务逻辑
        $options = JdPickupOrderStatus::genOptions();
        $this->assertIsArray($options);
        $this->assertCount(4, $options);

        // 验证特定状态的选择项
        $createdItem = JdPickupOrderStatus::CREATED->toSelectItem();
        $this->assertArrayHasKey('value', $createdItem);
        $this->assertArrayHasKey('label', $createdItem);
        $this->assertEquals('CREATED', $createdItem['value']);
        $this->assertEquals('已创建', $createdItem['label']);
    }
}
