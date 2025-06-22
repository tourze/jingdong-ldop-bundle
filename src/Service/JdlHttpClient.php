<?php

namespace JingdongLdopBundle\Service;

use JingdongLdopBundle\Repository\JdlAccessTokenRepository;
use JingdongLdopBundle\Repository\JdlConfigRepository;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class JdlHttpClient
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
        $config = $this->configRepository->getDefaultConfig();

        $response = $this->client->request('GET', self::AUTH_CODE_URL, [
            'query' => [
                'app_key' => $config->getAppKey(),
                'response_type' => 'code',
                'redirect_uri' => $config->getRedirectUri(),
                'state' => md5(uniqid()),
                'scope' => 'snsapi_base',
            ],
        ]);
        $this->logger->debug('京东授权码请求', [
            'resp' => $response->toArray(),
        ]);

        // 从重定向URL中提取code
        $redirectUrl = $response->getInfo()['redirect_url'] ?? '';
        parse_str(parse_url($redirectUrl, PHP_URL_QUERY), $query);

        if (empty($query['code'])) {
            throw new \Exception('Failed to get auth code');
        }

        return $query['code'];
    }

    /**
     * 发送API请求
     */
    public function request(string $method, array $params = []): array
    {
        $config = $this->configRepository->getDefaultConfig();

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

        // 发送请求
        $response = $this->client->request('POST', self::API_URL, [
            'body' => $requestParams,
        ]);

        // 记录请求日志
        $this->logger->info('京东下单请求', [
            'url' => self::API_URL,
            'request' => $requestParams,
            'response' => $response->getContent(),
        ]);

        $res = $response->toArray();

        // token过期
        if (isset($res['error_response']) && '19' === $res['error_response']['code']) {
            // 刷新token todo
        }

        return $res;
    }

    /**
     * 获取访问令牌
     */
    protected function getAccessToken(): string
    {
        $token = $this->tokenRepository->find(1);
        if (empty($token)) {
            throw new \Exception('Failed to get access token');
        }

        return $token->getAccessToken();
    }

    /**
     * 生成签名
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
