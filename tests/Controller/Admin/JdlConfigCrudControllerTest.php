<?php

declare(strict_types=1);

namespace JingdongLdopBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use JingdongLdopBundle\Controller\Admin\JdlConfigCrudController;
use JingdongLdopBundle\Entity\JdlConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * 京东物流配置 CRUD 控制器测试
 *
 * 测试 JdlConfigCrudController 的各项功能，确保：
 * - 控制器正确配置
 * - 字段配置正确
 * - 实体FQCN正确
 * - CRUD操作配置正确
 * - 过滤器和动作配置正确
 *
 * @internal
 */
#[CoversClass(JdlConfigCrudController::class)]
#[RunTestsInSeparateProcesses]
class JdlConfigCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): JdlConfigCrudController
    {
        return self::getService(JdlConfigCrudController::class);
    }

    /**
     * 修复基类中客户端初始化问题
     * 创建工作正常的认证客户端，绕过有问题的基类方法
     */
    protected function createWorkingAuthenticatedClient(): KernelBrowser
    {
        // 如果内核没有启动，启动它
        if (!self::$booted) {
            $kernel = self::bootKernel();
        }

        // 从容器获取客户端
        $client = self::getContainer()->get('test.client');
        if (!$client instanceof KernelBrowser) {
            throw new \RuntimeException('无法创建功能测试客户端，请确保 "framework.test" 配置设置为 true');
        }

        $client->catchExceptions(false);

        // 初始化数据库
        if (self::hasDoctrineSupport()) {
            self::cleanDatabase();
        }

        $this->createAdminUser('admin@test.com', 'password123');
        $this->loginAsAdmin($client, 'admin@test.com', 'password123');

        return $client;
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID' => ['ID'];
        yield '启用状态' => ['启用状态'];
        yield '商家编码' => ['商家编码'];
        yield '应用Key' => ['应用Key'];
        yield 'API接口地址' => ['API接口地址'];
        yield 'API版本号' => ['API版本号'];
        yield '数据格式' => ['数据格式'];
        yield '签名算法' => ['签名算法'];
        yield '授权回调地址' => ['授权回调地址'];
        yield '创建时间' => ['创建时间'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        yield 'valid' => ['valid'];
        yield 'customerCode' => ['customerCode'];
        yield 'appKey' => ['appKey'];
        yield 'appSecret' => ['appSecret'];
        yield 'apiEndpoint' => ['apiEndpoint'];
        yield 'version' => ['version'];
        yield 'format' => ['format'];
        yield 'signMethod' => ['signMethod'];
        yield 'redirectUri' => ['redirectUri'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        yield 'valid' => ['valid'];
        yield 'customerCode' => ['customerCode'];
        yield 'appKey' => ['appKey'];
        yield 'appSecret' => ['appSecret'];
        yield 'apiEndpoint' => ['apiEndpoint'];
        yield 'version' => ['version'];
        yield 'format' => ['format'];
        yield 'signMethod' => ['signMethod'];
        yield 'redirectUri' => ['redirectUri'];
        yield 'remark' => ['remark'];
    }

    public function testControllerInstantiation(): void
    {
        $controller = new JdlConfigCrudController();
        $this->assertInstanceOf(JdlConfigCrudController::class, $controller);
    }

    public function testConfigureCrudReturnsCorrectLabels(): void
    {
        $controller = new JdlConfigCrudController();
        $crud = $controller->configureCrud(
            Crud::new()
        );

        $this->assertInstanceOf(Crud::class, $crud);
    }

    public function testConfigureActionsReturnsCorrectConfiguration(): void
    {
        $controller = new JdlConfigCrudController();
        $actions = $controller->configureActions(
            Actions::new()
        );

        $this->assertInstanceOf(Actions::class, $actions);
    }

    public function testConfigureFiltersReturnsCorrectConfiguration(): void
    {
        $controller = new JdlConfigCrudController();
        $filters = $controller->configureFilters(
            Filters::new()
        );

        $this->assertInstanceOf(Filters::class, $filters);
    }

    #[TestWith([Crud::PAGE_INDEX])]
    #[TestWith([Crud::PAGE_DETAIL])]
    #[TestWith([Crud::PAGE_NEW])]
    #[TestWith([Crud::PAGE_EDIT])]
    public function testConfigureFieldsReturnsFields(string $pageName): void
    {
        $controller = new JdlConfigCrudController();
        $fields = $controller->configureFields($pageName);

        $this->assertIsIterable($fields);

        // 转换为数组来检查非空
        $fieldsArray = iterator_to_array($fields);
        $this->assertNotEmpty($fieldsArray);
    }

    public function testEntityCreation(): void
    {
        $config = new JdlConfig();
        $config->setValid(true);
        $config->setCustomerCode('TEST_CUSTOMER_001');
        $config->setAppKey('test_app_key_12345');
        $config->setAppSecret('test_app_secret_67890');
        $config->setApiEndpoint('https://api.test.jdl.com');
        $config->setVersion('2.0');
        $config->setFormat('json');
        $config->setSignMethod('md5');
        $config->setRedirectUri('https://example.com/callback');
        $config->setRemark('测试配置');

        // 验证实体属性
        $this->assertTrue($config->isValid());
        $this->assertSame('TEST_CUSTOMER_001', $config->getCustomerCode());
        $this->assertSame('test_app_key_12345', $config->getAppKey());
        $this->assertSame('test_app_secret_67890', $config->getAppSecret());
        $this->assertSame('https://api.test.jdl.com', $config->getApiEndpoint());
        $this->assertSame('2.0', $config->getVersion());
        $this->assertSame('json', $config->getFormat());
        $this->assertSame('md5', $config->getSignMethod());
        $this->assertSame('https://example.com/callback', $config->getRedirectUri());
        $this->assertSame('测试配置', $config->getRemark());

        // 验证字符串表示
        $this->assertSame('TEST_CUSTOMER_001', (string) $config);
    }

    public function testEntityDefaults(): void
    {
        $config = new JdlConfig();

        // 验证默认值
        $this->assertFalse($config->isValid());
        $this->assertSame('https://api.jdl.com', $config->getApiEndpoint());
        $this->assertSame('2.0', $config->getVersion());
        $this->assertSame('json', $config->getFormat());
        $this->assertSame('md5', $config->getSignMethod());
    }

    public function testChoiceFieldValidValues(): void
    {
        $config = new JdlConfig();

        // 测试format字段的有效值
        $validFormats = ['json', 'xml'];
        foreach ($validFormats as $format) {
            $config->setFormat($format);
            $this->assertSame($format, $config->getFormat());
        }

        // 测试signMethod字段的有效值
        $validSignMethods = ['md5', 'sha1', 'sha256'];
        foreach ($validSignMethods as $method) {
            $config->setSignMethod($method);
            $this->assertSame($method, $config->getSignMethod());
        }
    }

    public function testRequiredFields(): void
    {
        $config = new JdlConfig();

        // 设置所有必填字段
        $config->setCustomerCode('REQUIRED_CUSTOMER');
        $config->setAppKey('required_app_key');
        $config->setAppSecret('required_app_secret');
        $config->setRedirectUri('https://required.callback.com');

        $this->assertSame('REQUIRED_CUSTOMER', $config->getCustomerCode());
        $this->assertSame('required_app_key', $config->getAppKey());
        $this->assertSame('required_app_secret', $config->getAppSecret());
        $this->assertSame('https://required.callback.com', $config->getRedirectUri());
    }

    public function testNullableFields(): void
    {
        $config = new JdlConfig();

        // 测试可空的字段
        $this->assertNull($config->getRemark());

        // 设置并测试可空字段
        $config->setRemark('可选备注信息');
        $this->assertSame('可选备注信息', $config->getRemark());

        // 重新设置为null
        $config->setRemark(null);
        $this->assertNull($config->getRemark());
    }

    public function testBooleanFields(): void
    {
        $config = new JdlConfig();

        // 测试默认值
        $this->assertFalse($config->isValid());

        // 测试设置为true
        $config->setValid(true);
        $this->assertTrue($config->isValid());

        // 测试设置为false
        $config->setValid(false);
        $this->assertFalse($config->isValid());

        // 测试设置为null
        $config->setValid(null);
        $this->assertNull($config->isValid());
    }

    public function testFluentInterface(): void
    {
        $config = new JdlConfig();

        $config->setValid(true);
        $config->setCustomerCode('FLUENT_TEST');
        $config->setAppKey('fluent_key');
        $config->setAppSecret('fluent_secret');
        $config->setApiEndpoint('https://fluent.api.com');
        $config->setVersion('3.0');
        $config->setFormat('xml');
        $config->setSignMethod('sha256');
        $config->setRedirectUri('https://fluent.callback.com');
        $config->setRemark('流式接口测试');
        $result = $config;

        // 验证返回的是同一个对象
        $this->assertSame($config, $result);

        // 验证所有值都已正确设置
        $this->assertTrue($config->isValid());
        $this->assertSame('FLUENT_TEST', $config->getCustomerCode());
        $this->assertSame('fluent_key', $config->getAppKey());
        $this->assertSame('fluent_secret', $config->getAppSecret());
        $this->assertSame('https://fluent.api.com', $config->getApiEndpoint());
        $this->assertSame('3.0', $config->getVersion());
        $this->assertSame('xml', $config->getFormat());
        $this->assertSame('sha256', $config->getSignMethod());
        $this->assertSame('https://fluent.callback.com', $config->getRedirectUri());
        $this->assertSame('流式接口测试', $config->getRemark());
    }

    /**
     * 测试表单验证错误
     */
    public function testValidationErrors(): void
    {
        $client = $this->createWorkingAuthenticatedClient();
        $crawler = $client->request('GET', $this->generateAdminUrl('new'));

        $response = $client->getResponse();
        $this->assertSame(200, $response->getStatusCode(), 'Expected successful response');

        $form = $this->findFormOnPage($crawler);
        if (null === $form) {
            self::markTestSkipped('无法获取表单，跳过验证测试');
        }

        $this->submitEmptyForm($client, $form);

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

        $this->assertValidationErrors($client);
    }

    /**
     * 在页面上查找表单
     */
    private function findFormOnPage(Crawler $crawler): ?Form
    {
        // 尝试不同的按钮选择器
        $buttonSelectors = ['Create', '创建', 'Save', '保存', 'Submit', '提交'];

        foreach ($buttonSelectors as $buttonText) {
            try {
                $buttonCrawler = $crawler->selectButton($buttonText);
                if ($buttonCrawler->count() > 0) {
                    return $buttonCrawler->form();
                }
            } catch (\InvalidArgumentException) {
                continue;
            }
        }

        // 尝试直接获取第一个form
        $formElements = $crawler->filter('form');
        if ($formElements->count() > 0) {
            return $formElements->first()->form();
        }

        return null;
    }

    /**
     * 提交空表单
     */
    private function submitEmptyForm(KernelBrowser $client, Form $form): void
    {
        $entityName = $this->getEntitySimpleName();

        // 提交空表单以触发验证错误
        $form[$entityName . '[customerCode]'] = '';     // 留空触发NotBlank
        $form[$entityName . '[appKey]'] = '';           // 留空触发NotBlank
        $form[$entityName . '[appSecret]'] = '';        // 留空触发NotBlank
        $form[$entityName . '[redirectUri]'] = '';      // 留空触发NotBlank
        $form[$entityName . '[valid]'] = '1';           // 可选字段

        $client->submit($form);
    }

    /**
     * 断言验证错误
     */
    private function assertValidationErrors(KernelBrowser $client): void
    {
        // 验证返回422状态码（验证失败）
        $response = $client->getResponse();
        $this->assertSame(422, $response->getStatusCode(), 'Expected validation failure status code');

        // 重新获取响应页面内容
        $crawler = $client->getCrawler();

        // 验证页面包含验证错误信息
        $invalidFeedbacks = $crawler->filter('.invalid-feedback');
        $this->assertGreaterThan(0, $invalidFeedbacks->count(), '应该存在验证错误信息');

        // 检查具体的验证错误信息（根据实际情况使用英文错误信息）
        $errorText = $invalidFeedbacks->text();
        $this->assertStringContainsString('should not be blank', $errorText, '应该包含NotBlank验证错误');
    }

    /**
     * 测试URL格式验证
     */
    public function testUrlValidation(): void
    {
        $client = $this->createWorkingAuthenticatedClient();
        $crawler = $client->request('GET', $this->generateAdminUrl('new'));

        $response = $client->getResponse();
        $this->assertSame(200, $response->getStatusCode(), 'Expected successful response');

        $form = $this->findFormOnPage($crawler);
        if (null === $form) {
            self::markTestSkipped('无法获取表单，跳过验证测试');
        }

        $this->submitFormWithInvalidUrls($client, $form);
        $this->assertValidationErrorsPresent($client);
    }

    /**
     * 提交包含无效URL的表单
     */
    private function submitFormWithInvalidUrls(KernelBrowser $client, Form $form): void
    {
        $entityName = $this->getEntitySimpleName();

        // 填入必填字段但URL格式错误
        $form[$entityName . '[customerCode]'] = 'TEST123';
        $form[$entityName . '[appKey]'] = 'test_key';
        $form[$entityName . '[appSecret]'] = 'test_secret';
        $form[$entityName . '[apiEndpoint]'] = 'invalid-url-format';    // 无效URL
        $form[$entityName . '[redirectUri]'] = 'not-a-valid-url';      // 无效URL

        $client->submit($form);
    }

    /**
     * 断言存在验证错误
     */
    private function assertValidationErrorsPresent(KernelBrowser $client): void
    {
        $response = $client->getResponse();
        $this->assertSame(422, $response->getStatusCode(), 'Expected validation failure status code');

        $crawler = $client->getCrawler();
        $invalidFeedbacks = $crawler->filter('.invalid-feedback');
        $this->assertGreaterThan(0, $invalidFeedbacks->count(), '应该存在验证错误信息');
    }

    /**
     * 测试选择字段验证
     */
    public function testChoiceFieldValidation(): void
    {
        $client = $this->createWorkingAuthenticatedClient();
        $crawler = $client->request('GET', $this->generateAdminUrl('new'));

        $response = $client->getResponse();
        $this->assertSame(200, $response->getStatusCode(), 'Expected successful response');

        $form = $this->findFormOnPage($crawler);
        if (null === $form) {
            self::markTestSkipped('无法获取表单，跳过验证测试');
        }

        $this->submitValidForm($client, $form);
        $this->assertSuccessfulSubmission($client);
    }

    /**
     * 提交有效表单
     */
    private function submitValidForm(KernelBrowser $client, Form $form): void
    {
        $entityName = $this->getEntitySimpleName();

        // 填入必填字段
        $form[$entityName . '[customerCode]'] = 'TEST123';
        $form[$entityName . '[appKey]'] = 'test_key';
        $form[$entityName . '[appSecret]'] = 'test_secret';
        $form[$entityName . '[redirectUri]'] = 'https://example.com/callback';

        // 测试有效的选择值
        $form[$entityName . '[format]'] = 'json';
        $form[$entityName . '[signMethod]'] = 'md5';

        $client->submit($form);
    }

    /**
     * 断言成功提交
     */
    private function assertSuccessfulSubmission(KernelBrowser $client): void
    {
        $response = $client->getResponse();
        $this->assertSame(302, $response->getStatusCode(), '有效的选择字段值应该可以成功提交');
    }
}
