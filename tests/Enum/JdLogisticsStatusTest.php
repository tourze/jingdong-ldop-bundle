<?php

namespace JingdongLdopBundle\Tests\Enum;

use JingdongLdopBundle\Enum\JdLogisticsStatus;
use PHPUnit\Framework\TestCase;

class JdLogisticsStatusTest extends TestCase
{
    public function testEnumCases()
    {
        $cases = JdLogisticsStatus::cases();
        
        // 验证枚举值数量
        $this->assertCount(7, $cases);
        $this->assertContainsOnlyInstancesOf(JdLogisticsStatus::class, $cases);
        
        // 验证枚举项
        $statusValues = [
            'CREATED', 'COLLECTED', 'IN_TRANSIT', 'DELIVERING', 
            'DELIVERED', 'REJECTED', 'EXCEPTION'
        ];
        
        foreach ($cases as $case) {
            $this->assertContains($case->value, $statusValues);
        }
    }
    
    public function testGetLabel()
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
} 