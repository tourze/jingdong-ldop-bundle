<?php

namespace JingdongLdopBundle\Tests;

use HttpClientBundle\HttpClientBundle;
use JingdongLdopBundle\JingdongLdopBundle;
use PHPUnit\Framework\TestCase;

class JingdongLdopBundleTest extends TestCase
{
    public function testGetBundleDependencies()
    {
        // 验证依赖是否正确
        $dependencies = JingdongLdopBundle::getBundleDependencies();
        
        $this->assertNotEmpty($dependencies);
        $this->assertArrayHasKey(HttpClientBundle::class, $dependencies);
        $this->assertEquals(['all' => true], $dependencies[HttpClientBundle::class]);
    }
} 