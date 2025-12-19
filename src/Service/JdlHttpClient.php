<?php

declare(strict_types=1);

namespace JingdongLdopBundle\Service;

use JingdongLdopBundle\Exception\JdlAuthException;
use JingdongLdopBundle\Repository\JdlAccessTokenRepository;
use JingdongLdopBundle\Repository\JdlConfigRepository;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[WithMonologChannel(channel: 'jingdong_ldop')]
final class JdlHttpClient
{
    // 京东 API 相关地址
    private const API_URL = 'https://api.jd.com/routerjson';

    private const AUTH_CODE_URL = 'https://open-oauth.jd.com/oauth2/to_login';

    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly JdlConfigRepository $configRepository,
        private readonly LoggerInterface $logger,
        private readonly ?JdlAccessTokenRepository $tokenRepository,
    ) {
    }

    /**
     * 获取授权码
     */
    public function getAuthCode(): string
    {
        $startTime = microtime(true);
        $config = $this->configRepository->getDefaultConfig();
        if (null === $config) {
            throw new JdlAuthException('未找到京东配置');
        }

        try {
            $response = $this->client->request('GET', self::AUTH_CODE_URL, [
                'query' => [
                    'app_key' => $config->getAppKey(),
                    'response_type' => 'code',
                    'redirect_uri' => $config->getRedirectUri(),
                    'state' => md5(uniqid()),
                    'scope' => 'snsapi_base',
                ],
            ]);

            $duration = microtime(true) - $startTime;
            $this->logger->info('京东授权码请求', [
                'url' => self::AUTH_CODE_URL,
                'method' => 'GET',
                'duration_ms' => round($duration * 1000, 2),
                'response_code' => $response->getStatusCode(),
                'response_headers' => $response->getHeaders(),
            ]);

            // 从重定向URL中提取code
            $redirectUrl = $response->getInfo()['redirect_url'] ?? '';
            $queryString = parse_url($redirectUrl, PHP_URL_QUERY);
            if (null === $queryString || false === $queryString) {
                throw new JdlAuthException('Failed to parse redirect URL');
            }

            parse_str($queryString, $query);

            if (!isset($query['code']) || '' === $query['code'] || !is_string($query['code'])) {
                throw new JdlAuthException('Failed to get auth code');
            }

            return $query['code'];
        } catch (\Exception $e) {
            $duration = microtime(true) - $startTime;
            $this->logger->error('京东授权码请求失败', [
                'url' => self::AUTH_CODE_URL,
                'method' => 'GET',
                'duration_ms' => round($duration * 1000, 2),
                'error' => $e->getMessage(),
                'exception' => get_class($e),
            ]);
            throw $e;
        }
    }

    /**
     * 发送API请求
     */
    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function request(string $method, array $params = []): array
    {
        $startTime = microtime(true);
        $config = $this->configRepository->getDefaultConfig();
        if (null === $config) {
            throw new JdlAuthException('未找到京东配置');
        }

        // 构建请求参数
        $requestParams = [
            'method' => $method,
            'access_token' => $this->getAccessToken(),
            'app_key' => $config->getAppKey(),
            'timestamp' => date('Y-m-d H:i:s'),
            'v' => '2.0',
            'sign_method' => 'md5',
            '360buy_param_json' => json_encode($params),
        ];

        // 生成签名
        $requestParams['sign'] = $this->generateSign($requestParams, $config->getAppSecret());

        try {
            // 发送请求
            $response = $this->client->request('POST', self::API_URL, [
                'body' => $requestParams,
            ]);

            $duration = microtime(true) - $startTime;
            $responseContent = $response->getContent();

            // 记录请求日志
            $this->logger->info('京东API请求', [
                'url' => self::API_URL,
                'method' => 'POST',
                'api_method' => $method,
                'duration_ms' => round($duration * 1000, 2),
                'request_params' => array_merge($requestParams, ['sign' => '[HIDDEN]']), // 隐藏签名
                'response_code' => $response->getStatusCode(),
                'response_size' => strlen($responseContent),
            ]);

            $res = $response->toArray();

            // token过期
            if (isset($res['error_response']) && '19' === $res['error_response']['code']) {
                $this->logger->warning('京东API Token过期', [
                    'api_method' => $method,
                    'error_code' => $res['error_response']['code'],
                    'error_msg' => $res['error_response']['zh_desc'] ?? '未知错误',
                ]);
                // 刷新token todo
            }

            return $res;
        } catch (\Exception $e) {
            $duration = microtime(true) - $startTime;
            $this->logger->error('京东API请求失败', [
                'url' => self::API_URL,
                'method' => 'POST',
                'api_method' => $method,
                'duration_ms' => round($duration * 1000, 2),
                'error' => $e->getMessage(),
                'exception' => get_class($e),
            ]);
            throw $e;
        }
    }

    /**
     * 获取访问令牌
     */
    protected function getAccessToken(): string
    {
        if (null === $this->tokenRepository) {
            throw new JdlAuthException('Token repository not available');
        }

        $token = $this->tokenRepository->find(1);
        if (null === $token) {
            throw new JdlAuthException('Failed to get access token');
        }

        return $token->getAccessToken();
    }

    /**
     * 生成签名
     */
    /**
     * @param array<string, mixed> $params
     */
    private function generateSign(array $params, string $appSecret): string
    {
        // 按键名升序排序
        ksort($params);

        // 构建签名字符串
        $signStr = $appSecret;
        foreach ($params as $key => $value) {
            if ('sign' !== $key && !is_null($value) && '' !== $value) {
                $signStr .= $key . $value;
            }
        }
        $signStr .= $appSecret;
        // var_dump($signStr);

        // 返回MD5签名
        return strtoupper(md5($signStr));
    }
}
