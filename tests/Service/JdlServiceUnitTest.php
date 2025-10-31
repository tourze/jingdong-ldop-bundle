<?php

declare(strict_types=1);

namespace JingdongLdopBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use JingdongLdopBundle\Entity\JdlConfig;
use JingdongLdopBundle\Entity\LogisticsDetail;
use JingdongLdopBundle\Entity\PickupOrder;
use JingdongLdopBundle\Enum\JdPickupOrderStatus;
use JingdongLdopBundle\Exception\JdlApiException;
use JingdongLdopBundle\Exception\JdlConfigException;
use JingdongLdopBundle\Repository\JdlConfigRepository;
use JingdongLdopBundle\Repository\LogisticsDetailRepository;
use JingdongLdopBundle\Service\JdlHttpClient;
use JingdongLdopBundle\Service\JdlService;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * JdlService 单元测试
 *
 * 使用 Mock 对象测试复杂的业务逻辑
 *
 * @internal
 */
#[CoversClass(JdlService::class)]
#[RunTestsInSeparateProcesses]
final class JdlServiceUnitTest extends AbstractIntegrationTestCase
{
    private JdlService $jdlService;

    private MockObject&JdlHttpClient $httpClientMock;

    private MockObject&JdlConfigRepository $configRepositoryMock;

    private MockObject&LogisticsDetailRepository $logisticsDetailRepositoryMock;

    private MockObject&EntityManagerInterface $entityManagerMock;

    private MockObject&LoggerInterface $loggerMock;

    private JdlConfig $mockConfig;

    protected function onSetUp(): void
    {
        /*
         * 使用具体类 JdlHttpClient 作为 Mock：
         * 1. JdlHttpClient 是核心业务逻辑类，需要完整模拟其行为
         * 2. 测试需要验证与 HTTP 客户端的具体交互方式
         * 3. 这个类的接口相对稳定，不会频繁变化
         */
        $this->httpClientMock = $this->createMock(JdlHttpClient::class); // @phpstan-ignore-line

        /*
         * 使用具体类 JdlConfigRepository 作为 Mock：
         * 1. 配置仓库的行为对业务逻辑测试至关重要
         * 2. 需要模拟特定的配置获取方法
         * 3. Repository 类有明确的业务语义，适合直接模拟
         */
        $this->configRepositoryMock = $this->createMock(JdlConfigRepository::class); // @phpstan-ignore-line

        /*
         * 使用具体类 LogisticsDetailRepository 作为 Mock：
         * 1. 物流详情仓库的查询方法对测试逻辑很重要
         * 2. 需要模拟数据库查询的返回结果
         * 3. 测试需要验证特定的数据持久化行为
         */
        $this->logisticsDetailRepositoryMock = $this->createMock(LogisticsDetailRepository::class); // @phpstan-ignore-line
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->mockConfig = new JdlConfig();
        $this->mockConfig->setAppKey('test_app_key');
        $this->mockConfig->setAppSecret('test_app_secret');
        $this->mockConfig->setRedirectUri('https://example.com/redirect');
        $this->mockConfig->setCustomerCode('test_customer_code');

        $this->configRepositoryMock->method('getDefaultConfig')
            ->willReturn($this->mockConfig)
        ;

        $this->jdlService = new JdlService( // @phpstan-ignore-line
            $this->httpClientMock,
            $this->configRepositoryMock,
            $this->logisticsDetailRepositoryMock,
            $this->entityManagerMock,
            $this->loggerMock
        );
    }

    public function testCreatePickupOrderWithValidParamsReturnsSuccessResult(): void
    {
        $pickupOrder = $this->createPickupOrderMock();

        $successResponse = [
            'jingdong_ldop_receive_pickuporder_receive_responce' => [
                'receivepickuporder_result' => [
                    'code' => '100',
                    'pickUpCode' => 'JD12345678',
                    'message' => '下单成功',
                ],
            ],
        ];

        $this->httpClientMock->method('request')
            ->with(
                'jingdong.ldop.receive.pickuporder.receive',
                Assert::anything()
            )
            ->willReturn($successResponse)
        ;

        $this->entityManagerMock->expects($this->once())
            ->method('persist')
            ->with(Assert::callback(function ($order) {
                return $order instanceof PickupOrder
                    && JdPickupOrderStatus::STATUS_SUBMITTED === $order->getStatus()
                    && 'JD12345678' === $order->getPickUpCode();
            }))
        ;

        $this->entityManagerMock->expects($this->once())
            ->method('flush')
        ;

        $result = $this->jdlService->createPickupOrder($pickupOrder);
        $this->assertEquals('100', $result['code']);
        $this->assertEquals('JD12345678', $result['pickUpCode']);
    }

    public function testCreatePickupOrderWithErrorResponseThrowsException(): void
    {
        $pickupOrder = $this->createPickupOrderMock();

        $errorResponse = [
            'errorMessage' => '参数错误',
            'code' => '400',
        ];

        $this->httpClientMock->method('request')
            ->willReturn($errorResponse)
        ;

        $this->expectException(JdlApiException::class);
        $this->expectExceptionMessage('参数错误');

        $this->jdlService->createPickupOrder($pickupOrder);
    }

    public function testCreatePickupOrderNoAvailableConfigThrowsException(): void
    {
        $pickupOrder = $this->createPickupOrderMock();

        /*
         * 重新创建具体类 JdlConfigRepository Mock：
         * 1. 需要模拟 getDefaultConfig 返回 null 的特定场景
         * 2. 验证业务异常处理逻辑的正确性
         * 3. 确保配置缺失时的错误提示准确性
         */
        $configRepositoryMock = $this->createMock(JdlConfigRepository::class); // @phpstan-ignore-line
        $configRepositoryMock->method('getDefaultConfig')
            ->willReturn(null)
        ;

        $jdlService = new JdlService( // @phpstan-ignore-line
            $this->httpClientMock,
            $configRepositoryMock,
            $this->logisticsDetailRepositoryMock,
            $this->entityManagerMock,
            $this->loggerMock
        );

        $this->expectException(JdlConfigException::class);
        $this->expectExceptionMessage('无可用京东配置');

        $jdlService->createPickupOrder($pickupOrder);
    }

    public function testCancelPickupOrderWithValidParamsReturnsSuccessResult(): void
    {
        $pickupOrder = $this->createPickupOrderMock();
        $pickupOrder->setPickUpCode('JD12345678');

        $successResponse = [
            'jingdong_ldop_pickup_cancel_responce' => [
                'returnType' => [
                    'statusCode' => 0,
                    'message' => '取消成功',
                ],
            ],
        ];

        $this->httpClientMock->method('request')
            ->with(
                'jingdong.ldop.pickup.cancel',
                Assert::anything()
            )
            ->willReturn($successResponse)
        ;

        $this->entityManagerMock->expects($this->once())
            ->method('persist')
            ->with(Assert::callback(function ($order) {
                return $order instanceof PickupOrder
                    && JdPickupOrderStatus::STATUS_CANCELLED === $order->getStatus();
            }))
        ;

        $this->entityManagerMock->expects($this->once())
            ->method('flush')
        ;

        $result = $this->jdlService->cancelPickupOrder($pickupOrder);
        $this->assertArrayHasKey('returnType', $result);
        $this->assertIsArray($result['returnType']);
        $this->assertEquals(0, $result['returnType']['statusCode']);
    }

    public function testCancelPickupOrderWithErrorResponseThrowsException(): void
    {
        $pickupOrder = $this->createPickupOrderMock();
        $pickupOrder->setPickUpCode('JD12345678');

        $errorResponse = [
            'error_response' => [
                'code' => '500',
                'zh_desc' => '取消失败',
            ],
        ];

        $this->httpClientMock->method('request')
            ->willReturn($errorResponse)
        ;

        $this->expectException(JdlApiException::class);
        $this->expectExceptionMessage('取消失败');

        $this->jdlService->cancelPickupOrder($pickupOrder);
    }

    public function testGetLogisticsTraceWithValidParamsReturnsLogisticsDetails(): void
    {
        $testPickupOrder = new TestPickupOrder();
        $testPickupOrder->setOrderCode('ORDER12345');
        $testPickupOrder->setConfig($this->mockConfig);
        $pickupOrder = $testPickupOrder->getPickupOrder();

        $successResponse = [
            'jingdong_ldop_receive_trace_get_response' => [
                'receiveTraceGetResult' => [
                    'traceList' => [
                        [
                            'operateTime' => '2023-01-01 12:00:00',
                            'operateRemark' => '已揽收',
                            'operatePlace' => '北京分拣中心',
                            'operateType' => '1',
                            'operator' => '快递员',
                        ],
                        [
                            'operateTime' => '2023-01-02 12:00:00',
                            'operateRemark' => '运输中',
                            'operatePlace' => '北京分拣中心',
                            'operateType' => '2',
                        ],
                    ],
                ],
            ],
        ];

        $this->httpClientMock->method('request')
            ->with(
                'jingdong.ldop.receive.trace.get',
                Assert::anything()
            )
            ->willReturn($successResponse)
        ;

        $this->logisticsDetailRepositoryMock->method('findOneBy')
            ->willReturn(null)
        ;

        $result = $this->jdlService->getLogisticsTrace('JD12345678', $pickupOrder);
        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(LogisticsDetail::class, $result);
        $this->assertEquals('JD12345678', $result[0]->getWaybillCode());
        $this->assertEquals('已揽收', $result[0]->getOperateRemark());
    }

    public function testGetLogisticsTraceWithExistingRecordSkipsExistingAndReturnsNew(): void
    {
        $testPickupOrder = new TestPickupOrder();
        $testPickupOrder->setOrderCode('ORDER12345');
        $testPickupOrder->setConfig($this->mockConfig);
        $pickupOrder = $testPickupOrder->getPickupOrder();

        $successResponse = [
            'jingdong_ldop_receive_trace_get_response' => [
                'receiveTraceGetResult' => [
                    'traceList' => [
                        [
                            'operateTime' => '2023-01-01 12:00:00',
                            'operateRemark' => '已揽收',
                            'operatePlace' => '北京分拣中心',
                            'operateType' => '1',
                            'operator' => '快递员',
                        ],
                        [
                            'operateTime' => '2023-01-02 12:00:00',
                            'operateRemark' => '运输中',
                            'operatePlace' => '北京分拣中心',
                            'operateType' => '2',
                        ],
                    ],
                ],
            ],
        ];

        $this->httpClientMock->method('request')
            ->willReturn($successResponse)
        ;

        $this->logisticsDetailRepositoryMock->method('findOneBy')
            ->willReturnCallback(function ($criteria) {
                if ('1' === $criteria['operateType']) {
                    return new LogisticsDetail();
                }

                return null;
            })
        ;

        $result = $this->jdlService->getLogisticsTrace('JD12345678', $pickupOrder);
        $this->assertCount(1, $result);
        $this->assertEquals('运输中', $result[0]->getOperateRemark());
    }

    public function testGetLogisticsTraceWithErrorResponseThrowsException(): void
    {
        $testPickupOrder = new TestPickupOrder();
        $testPickupOrder->setOrderCode('ORDER12345');
        $testPickupOrder->setConfig($this->mockConfig);
        $pickupOrder = $testPickupOrder->getPickupOrder();

        $errorResponse = [
            'error_response' => [
                'code' => '500',
                'zh_desc' => '获取物流信息失败',
            ],
        ];

        $this->httpClientMock->method('request')
            ->willReturn($errorResponse)
        ;

        $this->expectException(JdlApiException::class);
        $this->expectExceptionMessage('获取物流信息失败');

        $this->jdlService->getLogisticsTrace('JD12345678', $pickupOrder);
    }

    public function testGetLogisticsTraceWithEmptyResponseReturnsEmptyArray(): void
    {
        $testPickupOrder = new TestPickupOrder();
        $testPickupOrder->setOrderCode('ORDER12345');
        $testPickupOrder->setConfig($this->mockConfig);
        $pickupOrder = $testPickupOrder->getPickupOrder();

        $emptyResponse = [
            'jingdong_ldop_receive_trace_get_response' => [],
        ];

        $this->httpClientMock->method('request')
            ->willReturn($emptyResponse)
        ;

        $result = $this->jdlService->getLogisticsTrace('JD12345678', $pickupOrder);
        $this->assertEmpty($result);
    }

    private function createPickupOrderMock(): PickupOrder
    {
        $order = new PickupOrder();
        $order->setSenderName('测试发件人');
        $order->setSenderMobile('13800138000');
        $order->setSenderAddress('北京市海淀区');
        $order->setReceiverName('测试收件人');
        $order->setReceiverMobile('13900139000');
        $order->setReceiverAddress('上海市浦东新区');
        $order->setWeight(1.5);
        $order->setConfig($this->mockConfig);

        return $order;
    }
}
