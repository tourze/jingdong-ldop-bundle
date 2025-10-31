<?php

declare(strict_types=1);

namespace JingdongLdopBundle\Tests\Enum;

use JingdongLdopBundle\Enum\JdLogisticsStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;

/**
 * @internal
 */
#[CoversClass(JdLogisticsStatus::class)]
final class JdLogisticsStatusTest extends AbstractEnumTestCase
{
    public function testEnumValues(): void
    {
        // 验证具体的业务逻辑
        $this->assertEquals('CREATED', JdLogisticsStatus::STATUS_CREATED->value);
        $this->assertEquals('COLLECTED', JdLogisticsStatus::STATUS_COLLECTED->value);
        $this->assertEquals('IN_TRANSIT', JdLogisticsStatus::STATUS_IN_TRANSIT->value);
        $this->assertEquals('DELIVERING', JdLogisticsStatus::STATUS_DELIVERING->value);
        $this->assertEquals('DELIVERED', JdLogisticsStatus::STATUS_DELIVERED->value);
        $this->assertEquals('REJECTED', JdLogisticsStatus::STATUS_REJECTED->value);
        $this->assertEquals('EXCEPTION', JdLogisticsStatus::STATUS_EXCEPTION->value);
    }

    public function testGetLabel(): void
    {
        // 验证标签方法
        $this->assertEquals('已创建', JdLogisticsStatus::STATUS_CREATED->getLabel());
        $this->assertEquals('已揽收', JdLogisticsStatus::STATUS_COLLECTED->getLabel());
        $this->assertEquals('运输中', JdLogisticsStatus::STATUS_IN_TRANSIT->getLabel());
        $this->assertEquals('派送中', JdLogisticsStatus::STATUS_DELIVERING->getLabel());
        $this->assertEquals('已签收', JdLogisticsStatus::STATUS_DELIVERED->getLabel());
        $this->assertEquals('已拒收', JdLogisticsStatus::STATUS_REJECTED->getLabel());
        $this->assertEquals('异常', JdLogisticsStatus::STATUS_EXCEPTION->getLabel());
    }

    public function testToArray(): void
    {
        $pending = JdLogisticsStatus::STATUS_CREATED->toArray();
        $this->assertIsArray($pending);
        $this->assertEquals(['value' => 'CREATED', 'label' => '已创建'], $pending);

        $collected = JdLogisticsStatus::STATUS_COLLECTED->toArray();
        $this->assertIsArray($collected);
        $this->assertEquals(['value' => 'COLLECTED', 'label' => '已揽收'], $collected);
    }

    public function testGetBadge(): void
    {
        // 验证徽章颜色方法
        $this->assertEquals('secondary', JdLogisticsStatus::STATUS_CREATED->getBadge());
        $this->assertEquals('primary', JdLogisticsStatus::STATUS_COLLECTED->getBadge());
        $this->assertEquals('info', JdLogisticsStatus::STATUS_IN_TRANSIT->getBadge());
        $this->assertEquals('warning', JdLogisticsStatus::STATUS_DELIVERING->getBadge());
        $this->assertEquals('success', JdLogisticsStatus::STATUS_DELIVERED->getBadge());
        $this->assertEquals('danger', JdLogisticsStatus::STATUS_REJECTED->getBadge());
        $this->assertEquals('danger', JdLogisticsStatus::STATUS_EXCEPTION->getBadge());
    }

    public function testSpecificBusinessLogic(): void
    {
        // 测试特定的业务逻辑
        $options = JdLogisticsStatus::genOptions();
        $this->assertIsArray($options);
        $this->assertCount(7, $options);

        // 验证特定状态的选择项
        $createdItem = JdLogisticsStatus::STATUS_CREATED->toSelectItem();
        $this->assertEquals('CREATED', $createdItem['value']);
        $this->assertEquals('已创建', $createdItem['label']);
    }
}
