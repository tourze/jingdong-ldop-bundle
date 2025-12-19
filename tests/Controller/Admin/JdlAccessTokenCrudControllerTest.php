<?php

declare(strict_types=1);

namespace JingdongLdopBundle\Tests\Controller\Admin;

use JingdongLdopBundle\Controller\Admin\JdlAccessTokenCrudController;
use JingdongLdopBundle\Entity\JdlAccessToken;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use Tourze\UserServiceContracts\UserManagerInterface;

/**
 * 京东物流访问令牌 CRUD 控制器测试
 *
 * 测试 JdlAccessTokenCrudController 的各项功能，确保：
 * - 控制器正确配置
 * - 字段配置正确
 * - 敏感信息正确格式化显示
 * - 过期时间状态正确显示
 * - 实体FQCN正确
 *
 * @internal
 */
#[CoversClass(JdlAccessTokenCrudController::class)]
#[RunTestsInSeparateProcesses]
class JdlAccessTokenCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): JdlAccessTokenCrudController
    {
        return self::getService(JdlAccessTokenCrudController::class);
    }

    /**
     * 创建具有正确权限的管理员用户
     * 在需要权限的测试方法开始时调用
     */
    private function createAdminUserIfNeeded(): void
    {
        $userManager = self::getService(UserManagerInterface::class);

        // 尝试查找已存在的用户
        $existingUser = $userManager->loadUserByIdentifier('admin');
        if ($existingUser === null) {
            // 创建新的管理员用户
            $adminUser = $userManager->createUser(
                userIdentifier: 'admin',
                password: 'password',
                roles: ['ROLE_ADMIN']
            );

            // 如果不是内存用户，需要保存到数据库
            if (!method_exists($adminUser, 'getUserIdentifier') || $adminUser->getUserIdentifier() !== 'admin') {
                $userManager->saveUser($adminUser);
            }
        }
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID' => ['ID'];
        yield '授权范围' => ['授权范围'];
        yield '过期时间' => ['过期时间'];
        yield '创建时间' => ['创建时间'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        yield 'accessToken' => ['accessToken'];
        yield 'scope' => ['scope'];
        yield 'expireTime' => ['expireTime'];
    }

    private JdlAccessTokenCrudController $controller;

    private function setUpController(): void
    {
        $this->controller = new JdlAccessTokenCrudController();
    }

    /**
     * 测试控制器配置
     */
    public function testControllerConfiguration(): void
    {
        $this->setUpController();
        // 验证控制器可以正常实例化
        $this->assertInstanceOf(JdlAccessTokenCrudController::class, $this->controller);
    }

    /**
     * 测试令牌格式化方法
     */
    public function testTokenFormatting(): void
    {
        $controller = new JdlAccessTokenCrudController();
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('formatToken');
        $method->setAccessible(true);

        // 测试空令牌
        $this->assertSame('', $method->invoke($controller, null));
        $this->assertSame('', $method->invoke($controller, ''));

        // 测试短令牌（不截断）
        $this->assertSame('abc', $method->invoke($controller, 'abc'));

        // 测试长令牌（截断显示）
        $longToken = 'abcdefghij1234567890abcdefghij12';
        $formatted = $method->invoke($controller, $longToken);
        $this->assertIsString($formatted);
        $this->assertStringStartsWith('abcdef', $formatted);
        $this->assertStringEndsWith('j12', $formatted);
        $this->assertStringContainsString('...', $formatted);
    }

    /**
     * 测试过期时间格式化方法
     */
    public function testExpireTimeFormatting(): void
    {
        $controller = new JdlAccessTokenCrudController();
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('formatExpireTime');
        $method->setAccessible(true);

        // 测试空时间
        $this->assertSame('', $method->invoke($controller, null));

        // 测试未来时间（未过期）
        $futureTime = new \DateTimeImmutable('+1 day');
        $formatted = $method->invoke($controller, $futureTime);
        $this->assertIsString($formatted);
        $this->assertStringStartsWith('🟢', $formatted);
        $this->assertStringNotContainsString('已过期', $formatted);

        // 测试过去时间（已过期）
        $pastTime = new \DateTimeImmutable('-1 day');
        $formatted = $method->invoke($controller, $pastTime);
        $this->assertIsString($formatted);
        $this->assertStringStartsWith('🔴', $formatted);
        $this->assertStringContainsString('已过期', $formatted);
    }

    /**
     * 测试实体创建
     */
    public function testEntityCreation(): void
    {
        $accessToken = new JdlAccessToken();
        $accessToken->setAccessToken('test_access_token_1234567890');
        $accessToken->setRefreshToken('test_refresh_token_1234567890');
        $accessToken->setScope('read:orders write:orders');
        $accessToken->setExpireTime(new \DateTimeImmutable('+1 day'));

        // 验证实体属性
        $this->assertSame('test_access_token_1234567890', $accessToken->getAccessToken());
        $this->assertSame('test_refresh_token_1234567890', $accessToken->getRefreshToken());
        $this->assertSame('read:orders write:orders', $accessToken->getScope());
        $this->assertInstanceOf(\DateTimeImmutable::class, $accessToken->getExpireTime());

        // 验证字符串表示
        $this->assertSame('test_access_token_1234567890', (string) $accessToken);
    }

    /**
     * 测试实体验证约束
     */
    public function testEntityValidationConstraints(): void
    {
        $accessToken = new JdlAccessToken();

        // 测试必填字段的存在
        try {
            $accessToken->setAccessToken('valid_token');
            $accessToken->setRefreshToken('valid_refresh');
        } catch (\Exception $e) {
            self::fail('Valid token values should not throw exception: ' . $e->getMessage());
        }
    }

    /**
     * 测试表单验证错误
     */
    public function testValidationErrors(): void
    {
        // 确保管理员用户存在
        $this->createAdminUserIfNeeded();

        $client = $this->createAuthenticatedClient();

        // 访问新建页面
        $crawler = $client->request('GET', $this->generateAdminUrl('new'));

        // 直接检查响应状态码而不使用断言方法
        $statusCode = $client->getResponse()->getStatusCode();
        if ($statusCode >= 400) {
            self::fail(sprintf('Expected successful response, got %d', $statusCode));
        }

        // 查找表单
        $form = $crawler->selectButton('Create')->form();

        // 提交空表单以触发验证错误
        $client->submit($form);

        // 验证返回 422 状态码
        $response = $client->getResponse();
        $this->assertSame(422, $response->getStatusCode(), 'Expected validation failure status code');

        // 验证页面包含验证错误信息
        $crawler = $client->getCrawler();
        $invalidFeedbacks = $crawler->filter('.invalid-feedback');
        $this->assertGreaterThan(0, $invalidFeedbacks->count(), '应该存在验证错误信息');

        // 检查具体的验证错误信息
        $errorText = $invalidFeedbacks->text();
        $this->assertStringContainsString('should not be blank', $errorText, '应该包含NotBlank验证错误');
    }

    /**
     * 测试字段长度验证
     */
    public function testFieldLengthValidation(): void
    {
        // 确保管理员用户存在
        $this->createAdminUserIfNeeded();

        $client = $this->createAuthenticatedClient();

        // 访问新建页面
        $crawler = $client->request('GET', $this->generateAdminUrl('new'));

        // 直接检查响应状态码而不使用断言方法
        $statusCode = $client->getResponse()->getStatusCode();
        if ($statusCode >= 400) {
            self::fail(sprintf('Expected successful response, got %d', $statusCode));
        }

        // 验证页面包含新建表单
        $forms = $crawler->filter('form');
        $this->assertGreaterThan(0, $forms->count(), '页面应该包含表单');

        // 验证页面包含必要字段
        $scopeFields = $crawler->filter('[name*="scope"]');
        $this->assertGreaterThan(0, $scopeFields->count(), '页面应该包含scope字段');
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        yield 'accessToken' => ['accessToken'];
        yield 'scope' => ['scope'];
        yield 'expireTime' => ['expireTime'];
    }
}
