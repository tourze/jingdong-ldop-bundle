<?php

namespace JingdongLdopBundle\Tests\Entity;

use JingdongLdopBundle\Entity\JdlAccessToken;
use PHPUnit\Framework\TestCase;

class JdlAccessTokenTest extends TestCase
{
    private JdlAccessToken $accessToken;

    protected function setUp(): void
    {
        $this->accessToken = new JdlAccessToken();
    }
    
    public function testGetSetAccessToken()
    {
        $value = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9';
        $this->accessToken->setAccessToken($value);
        $this->assertEquals($value, $this->accessToken->getAccessToken());
    }
    
    public function testGetSetRefreshToken()
    {
        $value = 'refresh_token_value';
        $this->accessToken->setRefreshToken($value);
        $this->assertEquals($value, $this->accessToken->getRefreshToken());
    }
    
    public function testGetSetScope()
    {
        $value = 'read write';
        $this->accessToken->setScope($value);
        $this->assertEquals($value, $this->accessToken->getScope());
        
        $this->accessToken->setScope(null);
        $this->assertNull($this->accessToken->getScope());
    }
    
    public function testGetSetExpireTime()
    {
        $now = new \DateTime();
        $this->accessToken->setExpireTime($now);
        $this->assertSame($now, $this->accessToken->getExpireTime());
        
        $this->accessToken->setExpireTime(null);
        $this->assertNull($this->accessToken->getExpireTime());
    }
    
    public function testGetSetCreateTime()
    {
        $now = new \DateTime();
        $this->accessToken->setCreateTime($now);
        $this->assertSame($now, $this->accessToken->getCreateTime());
    }
    
    public function testGetSetUpdateTime()
    {
        $now = new \DateTime();
        $this->accessToken->setUpdateTime($now);
        $this->assertSame($now, $this->accessToken->getUpdateTime());
    }
    
    public function testGetId_initiallyNull()
    {
        $this->assertNull($this->accessToken->getId());
    }
    
    public function testObjectStateAfterConstruction()
    {
        $token = new JdlAccessToken();
        
        // 必需属性应为null
        $this->assertNull($token->getCreateTime());
        $this->assertNull($token->getUpdateTime());
        $this->assertNull($token->getId());
        $this->assertEquals('', $token->getScope());
        $this->assertNull($token->getExpireTime());
    }
    
    public function testFluentInterface()
    {
        $result = $this->accessToken->setAccessToken('test_token')
            ->setRefreshToken('refresh_token')
            ->setScope('read');
            
        $this->assertSame($this->accessToken, $result);
    }
} 