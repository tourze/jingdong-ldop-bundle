<?php

declare(strict_types=1);

namespace JingdongLdopBundle\Tests\Controller\Admin;

use JingdongLdopBundle\Controller\Admin\LogisticsDetailCrudController;
use JingdongLdopBundle\Entity\LogisticsDetail;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Constraint\LogicalOr;
use PHPUnit\Framework\Constraint\StringContains;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use Tourze\UserServiceContracts\UserManagerInterface;

/**
 * 京东物流详情CRUD控制器测试
 * @internal
 */
#[CoversClass(LogisticsDetailCrudController::class)]
#[RunTestsInSeparateProcesses]
final class LogisticsDetailCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): LogisticsDetailCrudController
    {
        return self::getService(LogisticsDetailCrudController::class);
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
        yield '运单号' => ['运单号'];
        yield '商家编码' => ['商家编码'];
        yield '订单号' => ['订单号'];
        yield '运单状态' => ['运单状态'];
        yield '操作时间' => ['操作时间'];
        yield '操作类型' => ['操作类型'];
        yield '操作网点' => ['操作网点'];
        yield '操作人员' => ['操作人员'];
        yield '操作描述' => ['操作描述'];
        yield '创建时间' => ['创建时间'];
        yield '更新时间' => ['更新时间'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        yield 'waybillCode' => ['waybillCode'];
        yield 'customerCode' => ['customerCode'];
        yield 'orderCode' => ['orderCode'];
        yield 'waybillStatus' => ['waybillStatus'];
        yield 'operateTime' => ['operateTime'];
        yield 'operateType' => ['operateType'];
        yield 'operateSite' => ['operateSite'];
        yield 'operateRemark' => ['operateRemark'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        yield 'waybillCode' => ['waybillCode'];
        yield 'customerCode' => ['customerCode'];
        yield 'orderCode' => ['orderCode'];
        yield 'waybillStatus' => ['waybillStatus'];
        yield 'operateTime' => ['operateTime'];
        yield 'operateType' => ['operateType'];
        yield 'operateSite' => ['operateSite'];
        yield 'operateUser' => ['operateUser'];
        yield 'operateRemark' => ['operateRemark'];
        yield 'nextSite' => ['nextSite'];
        yield 'nextCity' => ['nextCity'];
    }

    public function testControllerIsInstantiable(): void
    {
        $controller = new LogisticsDetailCrudController();
        $this->assertInstanceOf(LogisticsDetailCrudController::class, $controller);
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

        // 检查响应状态码
        $response = $client->getResponse();
        $this->assertSame(200, $response->getStatusCode(), 'Expected successful response');

        // 获取表单 - 尝试多种可能的按钮文本
        $form = null;
        $buttonTexts = ['创建', 'Create', '保存', 'Save'];
        foreach ($buttonTexts as $buttonText) {
            try {
                $form = $crawler->selectButton($buttonText)->form();
                break;
            } catch (\InvalidArgumentException $e) {
                // 继续尝试下一个按钮文本
                continue;
            }
        }

        if (null === $form) {
            // 如果都找不到，尝试找表单的提交按钮
            $forms = $crawler->filter('form');
            if ($forms->count() > 0) {
                $form = $forms->first()->form();
            } else {
                Assert::fail('Cannot find form or submit button on the page');
            }
        }

        $entityName = $this->getEntitySimpleName();

        // 提交空表单以触发验证错误
        $form[$entityName . '[waybillCode]'] = '';      // 留空触发NotBlank
        $form[$entityName . '[customerCode]'] = '';     // 留空触发NotBlank
        $form[$entityName . '[orderCode]'] = '';        // 留空触发NotBlank
        $form[$entityName . '[operateRemark]'] = '';    // 留空触发NotBlank
        $form[$entityName . '[operateSite]'] = '';      // 留空触发NotBlank
        $form[$entityName . '[operateType]'] = '';      // 留空触发NotBlank
        // 提供有效的operateTime以避免null值错误，但测试其他字段的NotBlank验证
        $form[$entityName . '[operateTime]'] = '2025-01-27 10:00:00';

        $crawler = $client->submit($form);

        // 验证返回422状态码（验证失败）
        $response = $client->getResponse();
        $this->assertSame(422, $response->getStatusCode(), 'Expected validation failure status code');

        // 验证页面包含验证错误信息
        $invalidFeedbacks = $crawler->filter('.invalid-feedback');
        $this->assertGreaterThan(0, $invalidFeedbacks->count(), '应该存在验证错误信息');

        // 检查具体的验证错误信息
        $errorText = $invalidFeedbacks->text();
        Assert::assertThat(
            $errorText,
            LogicalOr::fromConstraints(
                new StringContains('不能为空'),
                new StringContains('This value should not be blank')
            ),
            '应该包含NotBlank验证错误'
        );
    }

    /**
     * 测试日期时间字段验证
     */
    public function testDateTimeValidation(): void
    {
        // 确保管理员用户存在
        $this->createAdminUserIfNeeded();

        $client = $this->createAuthenticatedClient();

        // 访问新建页面
        $crawler = $client->request('GET', $this->generateAdminUrl('new'));

        // 检查响应状态码
        $response = $client->getResponse();
        $this->assertSame(200, $response->getStatusCode(), 'Expected successful response');

        // 获取表单 - 尝试多种可能的按钮文本
        $form = null;
        $buttonTexts = ['创建', 'Create', '保存', 'Save'];
        foreach ($buttonTexts as $buttonText) {
            try {
                $form = $crawler->selectButton($buttonText)->form();
                break;
            } catch (\InvalidArgumentException $e) {
                // 继续尝试下一个按钮文本
                continue;
            }
        }

        if (null === $form) {
            // 如果都找不到，尝试找表单的提交按钮
            $forms = $crawler->filter('form');
            if ($forms->count() > 0) {
                $form = $forms->first()->form();
            } else {
                Assert::fail('Cannot find form or submit button on the page');
            }
        }

        $entityName = $this->getEntitySimpleName();

        // 填入必填字段
        $form[$entityName . '[waybillCode]'] = 'WB123456789';
        $form[$entityName . '[customerCode]'] = 'CUST001';
        $form[$entityName . '[orderCode]'] = 'ORDER001';
        $form[$entityName . '[operateRemark]'] = '测试操作描述';
        $form[$entityName . '[operateSite]'] = '北京分拣中心';
        $form[$entityName . '[operateType]'] = '分拣';
        // operateTime是必填的DateTimeImmutable字段，故意留空测试错误处理

        // 期望抛出InvalidArgumentException
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('操作时间不能为空');

        $crawler = $client->submit($form);
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

        // 检查响应状态码
        $response = $client->getResponse();
        $this->assertSame(200, $response->getStatusCode(), 'Expected successful response');

        // 获取表单 - 尝试多种可能的按钮文本
        $form = null;
        $buttonTexts = ['创建', 'Create', '保存', 'Save'];
        foreach ($buttonTexts as $buttonText) {
            try {
                $form = $crawler->selectButton($buttonText)->form();
                break;
            } catch (\InvalidArgumentException $e) {
                // 继续尝试下一个按钮文本
                continue;
            }
        }

        if (null === $form) {
            // 如果都找不到，尝试找表单的提交按钮
            $forms = $crawler->filter('form');
            if ($forms->count() > 0) {
                $form = $forms->first()->form();
            } else {
                Assert::fail('Cannot find form or submit button on the page');
            }
        }

        $entityName = $this->getEntitySimpleName();

        // 测试超长字段验证
        $form[$entityName . '[waybillCode]'] = str_repeat('W', 33);     // 超过32字符限制
        $form[$entityName . '[customerCode]'] = str_repeat('C', 33);    // 超过32字符限制
        $form[$entityName . '[orderCode]'] = str_repeat('O', 33);       // 超过32字符限制
        $form[$entityName . '[operateRemark]'] = str_repeat('R', 256);  // 超过255字符限制
        $form[$entityName . '[operateSite]'] = str_repeat('S', 33);     // 超过32字符限制
        $form[$entityName . '[operateType]'] = str_repeat('T', 33);     // 超过32字符限制
        // 提供有效的operateTime以避免null值错误
        $form[$entityName . '[operateTime]'] = '2025-01-27 10:00:00';

        $crawler = $client->submit($form);

        // 验证返回422状态码（验证失败）
        $response = $client->getResponse();
        $this->assertSame(422, $response->getStatusCode(), 'Expected validation failure status code');

        // 验证页面包含长度验证错误信息
        $invalidFeedbacks = $crawler->filter('.invalid-feedback');
        $this->assertGreaterThan(0, $invalidFeedbacks->count(), '应该存在长度验证错误信息');
    }

    /**
     * 重写基类的方法，使用实际的字段配置验证
     *
     * 基类硬编码了 'instanceId', 'email', 'password' 字段验证，
     * 但LogisticsDetail实体没有这些字段，需要使用实际的字段配置
     */
}
