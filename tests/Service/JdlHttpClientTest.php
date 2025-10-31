<?php

declare(strict_types=1);

namespace JingdongLdopBundle\Tests\Service;

use JingdongLdopBundle\Entity\JdlAccessToken;
use JingdongLdopBundle\Entity\JdlConfig;
use JingdongLdopBundle\Repository\JdlAccessTokenRepository;
use JingdongLdopBundle\Repository\JdlConfigRepository;
use JingdongLdopBundle\Service\JdlHttpClient;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(JdlHttpClient::class)]
#[RunTestsInSeparateProcesses]
final class JdlHttpClientTest extends AbstractIntegrationTestCase
{
    private TestJdlHttpClient $httpClient;

    private MockObject $clientMock;

    private MockObject $configRepositoryMock;

    private MockObject $loggerMock;

    private MockObject $tokenRepositoryMock;

    private JdlConfig $mockConfig;

    private JdlAccessToken $mockToken;

    protected function onSetUp(): void
    {
        $this->clientMock = $this->createMock(HttpClientInterface::class);

        // 使用具体类 JdlConfigRepository 作为 Mock：
        // 1. 需要模拟配置获取的具体业务逻辑
        // 2. 测试需要验证配置相关的错误处理
        // 3. Repository 提供稳定的业务接口，适合直接模拟
        $this->configRepositoryMock = $this->createMock(JdlConfigRepository::class); // @phpstan-ignore-line
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        // 使用具体类 JdlAccessTokenRepository 作为 Mock：
        // 1. Token 获取逻辑对 HTTP 客户端功能测试至关重要
        // 2. 需要模拟认证失败等异常场景
        // 3. 访问令牌管理有明确的业务边界，适合具体模拟
        $this->tokenRepositoryMock = $this->createMock(JdlAccessTokenRepository::class); // @phpstan-ignore-line

        $this->mockConfig = new JdlConfig();
        $this->mockConfig->setAppKey('test_app_key');
        $this->mockConfig->setAppSecret('test_app_secret');
        $this->mockConfig->setRedirectUri('https://example.com/redirect');
        $this->mockConfig->setCustomerCode('test_customer_code');

        $this->mockToken = new JdlAccessToken();
        $this->mockToken->setAccessToken('test_access_token');
        $this->mockToken->setRefreshToken('test_refresh_token');

        $this->configRepositoryMock->method('getDefaultConfig')
            ->willReturn($this->mockConfig)
        ;

        $this->tokenRepositoryMock->method('find')
            ->with(1)
            ->willReturn($this->mockToken)
        ;

        $this->httpClient = new TestJdlHttpClient(
            $this->clientMock,
            $this->configRepositoryMock,
            $this->loggerMock,
            $this->tokenRepositoryMock
        );

        $this->httpClient->setTestToken('test_token');
    }

    public function testGenerateSignWithValidParamsReturnsCorrectSignature(): void
    {
        $params = [
            'method' => 'test.method',
            'app_key' => 'test_app_key',
            'timestamp' => '2023-01-01 12:00:00',
            'v' => '2.0',
            'sign_method' => 'md5',
            '360buy_param_json' => json_encode(['param1' => 'value1']),
        ];

        // 使用反射来测试私有方法
        $reflectionMethod = new \ReflectionMethod(JdlHttpClient::class, 'generateSign');
        $reflectionMethod->setAccessible(true);

        $sign = $reflectionMethod->invoke($this->httpClient->getHttpClient(), $params, 'test_app_secret');

        // 验证签名是大写的MD5值
        $this->assertIsString($sign);
        $this->assertEquals(32, strlen($sign));
        $this->assertEquals(strtoupper($sign), $sign);
    }

    public function testGetAuthCodeValidResponseReturnsAuthCode(): void
    {
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getInfo')
            ->willReturn(['redirect_url' => 'https://example.com/callback?code=test_auth_code&state=test_state'])
        ;
        $responseMock->method('toArray')
            ->willReturn(['status' => 'ok'])
        ;

        $this->clientMock->method('request')
            ->with(
                'GET',
                'https://open-oauth.jd.com/oauth2/to_login',
                Assert::anything()
            )
            ->willReturn($responseMock)
        ;

        $result = $this->httpClient->getAuthCode();

        $this->assertEquals('test_auth_code', $result);
    }

    public function testGetAuthCodeInvalidResponseThrowsException(): void
    {
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getInfo')
            ->willReturn(['redirect_url' => 'https://example.com/callback?error=invalid_request'])
        ;
        $responseMock->method('toArray')
            ->willReturn(['status' => 'error'])
        ;

        $this->clientMock->method('request')
            ->willReturn($responseMock)
        ;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to get auth code');

        $this->httpClient->getAuthCode();
    }

    public function testRequestValidResponseReturnsResponseArray(): void
    {
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('toArray')
            ->willReturn(['test_key' => 'test_value'])
        ;
        $responseMock->method('getContent')
            ->willReturn('{"test_key":"test_value"}')
        ;

        $this->clientMock->method('request')
            ->with(
                'POST',
                'https://api.jd.com/routerjson',
                Assert::callback(function ($options) {
                    return is_array($options) && isset($options['body']) && is_array($options['body']);
                })
            )
            ->willReturn($responseMock)
        ;

        $result = $this->httpClient->request('test.method', ['param1' => 'value1']);
        $this->assertEquals(['test_key' => 'test_value'], $result);
    }

    public function testRequestTokenExpiredResponseHandlesError(): void
    {
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('toArray')
            ->willReturn([
                'error_response' => [
                    'code' => '19',
                    'zh_desc' => 'Token expired',
                ],
            ])
        ;
        $responseMock->method('getContent')
            ->willReturn('{"error_response":{"code":"19","zh_desc":"Token expired"}}')
        ;

        $this->clientMock->method('request')
            ->willReturn($responseMock)
        ;

        $result = $this->httpClient->request('test.method', ['param1' => 'value1']);
        $this->assertArrayHasKey('error_response', $result);
        $this->assertIsArray($result['error_response']);
        $this->assertEquals('19', $result['error_response']['code']);
    }
}
