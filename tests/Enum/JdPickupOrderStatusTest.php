<?php

namespace JingdongLdopBundle\Tests\Enum;

use JingdongLdopBundle\Enum\JdPickupOrderStatus;
use PHPUnit\Framework\TestCase;

class JdPickupOrderStatusTest extends TestCase
{
    public function testPickupOrderStatusConstants()
    {
        // 验证常量值
        $this->assertEquals('CREATED', JdPickupOrderStatus::STATUS_CREATED);
        $this->assertEquals('SUBMITTED', JdPickupOrderStatus::STATUS_SUBMITTED);
        $this->assertEquals('UPDATED', JdPickupOrderStatus::STATUS_UPDATED);
        $this->assertEquals('CANCELLED', JdPickupOrderStatus::STATUS_CANCELLED);
    }
} 