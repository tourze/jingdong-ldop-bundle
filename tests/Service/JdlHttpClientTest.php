<?php

namespace JingdongLdopBundle\Tests\Service;

use JingdongLdopBundle\Entity\JdlConfig;
use JingdongLdopBundle\Repository\JdlAccessTokenRepository;
use JingdongLdopBundle\Repository\JdlConfigRepository;
use JingdongLdopBundle\Service\JdlHttpClient;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * 用于测试的 HttpClient 子类，重写 getAccessToken 方法
 */
class TestJdlHttpClient extends JdlHttpClient
{
    private string $testToken = 'test_token';
    
    public function setTestToken(string $token): void
    {
        $this->testToken = $token;
    }
    
    protected function getAccessToken(): string
    {
        return $this->testToken;
    }
}

class JdlHttpClientTest extends TestCase
{
    private TestJdlHttpClient $httpClient;
    private MockObject $clientMock;
    private MockObject $configRepositoryMock;
    private MockObject $loggerMock;
    private MockObject $tokenRepositoryMock;
    private JdlConfig $mockConfig;

    protected function setUp(): void
    {
        $this->clientMock = $this->createMock(HttpClientInterface::class);
        $this->configRepositoryMock = $this->createMock(JdlConfigRepository::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->tokenRepositoryMock = $this->createMock(JdlAccessTokenRepository::class);
        
        $this->mockConfig = new JdlConfig();
        $this->mockConfig->setAppKey('test_app_key');
        $this->mockConfig->setAppSecret('test_app_secret');
        $this->mockConfig->setRedirectUri('https://example.com/redirect');
        $this->mockConfig->setCustomerCode('test_customer_code');
        
        $this->configRepositoryMock->method('getDefaultConfig')
            ->willReturn($this->mockConfig);
            
        $this->httpClient = new TestJdlHttpClient(
            $this->clientMock,
            $this->configRepositoryMock,
            $this->loggerMock,
            $this->tokenRepositoryMock
        );
        
        $this->httpClient->setTestToken('test_token');
    }
    
    public function testGenerateSign_withValidParams_returnsCorrectSignature()
    {
        $params = [
            'method' => 'test.method',
            'app_key' => 'test_app_key',
            'timestamp' => '2023-01-01 12:00:00',
            'v' => '2.0',
            'sign_method' => 'md5',
            '360buy_param_json' => json_encode(['param1' => 'value1'])
        ];
        
        // 使用反射来测试私有方法
        $reflectionMethod = new \ReflectionMethod(JdlHttpClient::class, 'generateSign');
        $reflectionMethod->setAccessible(true);
        
        $sign = $reflectionMethod->invoke($this->httpClient, $params, 'test_app_secret');
        
        // 验证签名是大写的MD5值
        $this->assertIsString($sign);
        $this->assertEquals(32, strlen($sign));
        $this->assertEquals(strtoupper($sign), $sign);
    }
    
    public function testGetAuthCode_validResponse_returnsAuthCode()
    {
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getInfo')
            ->willReturn(['redirect_url' => 'https://example.com/callback?code=test_auth_code&state=test_state']);
        $responseMock->method('toArray')
            ->willReturn(['status' => 'ok']);
            
        $this->clientMock->method('request')
            ->with(
                'GET',
                'https://open-oauth.jd.com/oauth2/to_login',
                $this->anything()
            )
            ->willReturn($responseMock);
            
        $result = $this->invokeMethod($this->httpClient, 'getAuthCode');
        
        $this->assertEquals('test_auth_code', $result);
    }
    
    public function testGetAuthCode_invalidResponse_throwsException()
    {
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getInfo')
            ->willReturn(['redirect_url' => 'https://example.com/callback?error=invalid_request']);
        $responseMock->method('toArray')
            ->willReturn(['status' => 'error']);
            
        $this->clientMock->method('request')
            ->willReturn($responseMock);
            
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to get auth code');
        
        $this->invokeMethod($this->httpClient, 'getAuthCode');
    }
    
    public function testRequest_validResponse_returnsResponseArray()
    {
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('toArray')
            ->willReturn(['test_key' => 'test_value']);
        $responseMock->method('getContent')
            ->willReturn('{"test_key":"test_value"}');
            
        $this->clientMock->method('request')
            ->with(
                'POST',
                'https://api.jd.com/routerjson',
                $this->callback(function ($options) {
                    return is_array($options) && isset($options['body']) && is_array($options['body']);
                })
            )
            ->willReturn($responseMock);
            
        $result = $this->httpClient->request('test.method', ['param1' => 'value1']);
        
        $this->assertIsArray($result);
        $this->assertEquals(['test_key' => 'test_value'], $result);
    }
    
    public function testRequest_tokenExpiredResponse_handlesError()
    {
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('toArray')
            ->willReturn([
                'error_response' => [
                    'code' => '19',
                    'zh_desc' => 'Token expired'
                ]
            ]);
        $responseMock->method('getContent')
            ->willReturn('{"error_response":{"code":"19","zh_desc":"Token expired"}}');
            
        $this->clientMock->method('request')
            ->willReturn($responseMock);
            
        $result = $this->httpClient->request('test.method', ['param1' => 'value1']);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('error_response', $result);
        $this->assertEquals('19', $result['error_response']['code']);
    }
    
    /**
     * 调用对象的私有方法
     */
    private function invokeMethod($object, string $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        
        return $method->invokeArgs($object, $parameters);
    }
} 