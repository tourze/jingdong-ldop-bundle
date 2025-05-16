<?php

namespace JingdongLdopBundle\Tests\Entity;

use JingdongLdopBundle\Entity\JdlConfig;
use PHPUnit\Framework\TestCase;

class JdlConfigTest extends TestCase
{
    private JdlConfig $jdlConfig;

    protected function setUp(): void
    {
        $this->jdlConfig = new JdlConfig();
    }
    
    public function testGetSetCustomerCode()
    {
        $value = 'TEST_CUSTOMER_CODE';
        $this->jdlConfig->setCustomerCode($value);
        $this->assertEquals($value, $this->jdlConfig->getCustomerCode());
    }
    
    public function testGetSetAppKey()
    {
        $value = 'TEST_APP_KEY';
        $this->jdlConfig->setAppKey($value);
        $this->assertEquals($value, $this->jdlConfig->getAppKey());
    }
    
    public function testGetSetAppSecret()
    {
        $value = 'TEST_APP_SECRET';
        $this->jdlConfig->setAppSecret($value);
        $this->assertEquals($value, $this->jdlConfig->getAppSecret());
    }
    
    public function testGetSetApiEndpoint()
    {
        $value = 'https://test-api.jd.com';
        $this->jdlConfig->setApiEndpoint($value);
        $this->assertEquals($value, $this->jdlConfig->getApiEndpoint());
    }
    
    public function testGetSetVersion()
    {
        $value = '3.0';
        $this->jdlConfig->setVersion($value);
        $this->assertEquals($value, $this->jdlConfig->getVersion());
    }
    
    public function testGetSetFormat()
    {
        $value = 'xml';
        $this->jdlConfig->setFormat($value);
        $this->assertEquals($value, $this->jdlConfig->getFormat());
    }
    
    public function testGetSetSignMethod()
    {
        $value = 'hmac';
        $this->jdlConfig->setSignMethod($value);
        $this->assertEquals($value, $this->jdlConfig->getSignMethod());
    }
    
    public function testGetSetRemark()
    {
        $value = 'Test remark';
        $this->jdlConfig->setRemark($value);
        $this->assertEquals($value, $this->jdlConfig->getRemark());
    }
    
    public function testGetSetRedirectUri()
    {
        $value = 'https://test-redirect.example.com/callback';
        $this->jdlConfig->setRedirectUri($value);
        $this->assertEquals($value, $this->jdlConfig->getRedirectUri());
    }
    
    public function testGetSetValid()
    {
        $this->jdlConfig->setValid(true);
        $this->assertTrue($this->jdlConfig->isValid());
        
        $this->jdlConfig->setValid(false);
        $this->assertFalse($this->jdlConfig->isValid());
        
        $this->jdlConfig->setValid(null);
        $this->assertNull($this->jdlConfig->isValid());
    }
    
    public function testGetSetCreatedBy()
    {
        $value = 'test_user';
        $this->jdlConfig->setCreatedBy($value);
        $this->assertEquals($value, $this->jdlConfig->getCreatedBy());
    }
    
    public function testGetSetUpdatedBy()
    {
        $value = 'another_user';
        $this->jdlConfig->setUpdatedBy($value);
        $this->assertEquals($value, $this->jdlConfig->getUpdatedBy());
    }
    
    public function testGetSetCreateTime()
    {
        $now = new \DateTime();
        $this->jdlConfig->setCreateTime($now);
        $this->assertSame($now, $this->jdlConfig->getCreateTime());
    }
    
    public function testGetSetUpdateTime()
    {
        $now = new \DateTime();
        $this->jdlConfig->setUpdateTime($now);
        $this->assertSame($now, $this->jdlConfig->getUpdateTime());
    }
    
    public function testGetId_initiallyNull()
    {
        $this->assertNull($this->jdlConfig->getId());
    }
    
    public function testObjectStateAfterConstruction()
    {
        $config = new JdlConfig();
        
        // 默认值断言
        $this->assertEquals('https://api.jdl.com', $config->getApiEndpoint());
        $this->assertEquals('2.0', $config->getVersion());
        $this->assertEquals('json', $config->getFormat());
        $this->assertEquals('md5', $config->getSignMethod());
        $this->assertFalse($config->isValid());
        
        // 必需属性应为null
        $this->assertNull($config->getCreateTime());
        $this->assertNull($config->getUpdateTime());
        $this->assertNull($config->getCreatedBy());
        $this->assertNull($config->getUpdatedBy());
    }
    
    public function testFluentInterface()
    {
        $result = $this->jdlConfig->setCustomerCode('TEST_CODE')
            ->setAppKey('KEY')
            ->setAppSecret('SECRET');
            
        $this->assertSame($this->jdlConfig, $result);
    }
} 