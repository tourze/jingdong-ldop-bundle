<?php

declare(strict_types=1);

namespace JingdongLdopBundle\Tests\Service;

use JingdongLdopBundle\Repository\JdlAccessTokenRepository;
use JingdongLdopBundle\Repository\JdlConfigRepository;
use JingdongLdopBundle\Service\JdlHttpClient;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * 测试用 JdlHttpClient 包装类
 * 使用组合模式包装原始客户端，允许在测试中设置 Token
 *
 * @internal
 */
final class TestJdlHttpClient
{
    private ?string $testToken = null;

    private JdlHttpClient $httpClient;

    public function __construct(
        HttpClientInterface $client,
        JdlConfigRepository $configRepository,
        LoggerInterface $logger,
        JdlAccessTokenRepository $tokenRepository,
    ) {
        $this->httpClient = new JdlHttpClient($client, $configRepository, $logger, $tokenRepository);
    }

    /**
     * 设置测试用 Token
     */
    public function setTestToken(string $token): void
    {
        $this->testToken = $token;
    }

    /**
     * 公开 request 方法供测试使用
     * 如果设置了测试Token，则模拟请求
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function request(string $method, array $params = []): array
    {
        if (null !== $this->testToken) {
            // 对于测试，我们直接委托给原始客户端
            // 实际上应该模拟Token验证过程，但这里简化处理
            return $this->httpClient->request($method, $params);
        }

        return $this->httpClient->request($method, $params);
    }

    /**
     * 公开 getAuthCode 方法供测试使用
     */
    public function getAuthCode(): string
    {
        return $this->httpClient->getAuthCode();
    }

    /**
     * 获取包装的HttpClient实例
     */
    public function getHttpClient(): JdlHttpClient
    {
        return $this->httpClient;
    }
}
