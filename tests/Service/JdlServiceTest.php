<?php

namespace JingdongLdopBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use JingdongLdopBundle\Entity\JdlConfig;
use JingdongLdopBundle\Entity\LogisticsDetail;
use JingdongLdopBundle\Entity\PickupOrder;
use JingdongLdopBundle\Enum\JdPickupOrderStatus;
use JingdongLdopBundle\Repository\JdlConfigRepository;
use JingdongLdopBundle\Repository\LogisticsDetailRepository;
use JingdongLdopBundle\Service\JdlHttpClient;
use JingdongLdopBundle\Service\JdlService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * 用于测试的 PickupOrder 子类，添加 getOrderCode 方法
 */
class TestPickupOrder extends PickupOrder
{
    private string $orderCode = 'ORDER12345';
    
    public function setOrderCode(string $orderCode): self
    {
        $this->orderCode = $orderCode;
        return $this;
    }
    
    public function getOrderCode(): string
    {
        return $this->orderCode;
    }
}

class JdlServiceTest extends TestCase
{
    private JdlService $jdlService;
    private MockObject&JdlHttpClient $httpClientMock;
    private MockObject&JdlConfigRepository $configRepositoryMock;
    private MockObject&LogisticsDetailRepository $logisticsDetailRepositoryMock;
    private MockObject&EntityManagerInterface $entityManagerMock;
    private JdlConfig $mockConfig;

    protected function setUp(): void
    {
        $this->httpClientMock = $this->createMock(JdlHttpClient::class);
        $this->configRepositoryMock = $this->createMock(JdlConfigRepository::class);
        $this->logisticsDetailRepositoryMock = $this->createMock(LogisticsDetailRepository::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        
        $this->mockConfig = new JdlConfig();
        $this->mockConfig->setAppKey('test_app_key');
        $this->mockConfig->setAppSecret('test_app_secret');
        $this->mockConfig->setRedirectUri('https://example.com/redirect');
        $this->mockConfig->setCustomerCode('test_customer_code');
        
        $this->configRepositoryMock->method('getDefaultConfig')
            ->willReturn($this->mockConfig);
            
        $this->jdlService = new JdlService(
            $this->httpClientMock,
            $this->configRepositoryMock,
            $this->logisticsDetailRepositoryMock,
            $this->entityManagerMock
        );
    }
    
    public function testCreatePickupOrder_withValidParams_returnsSuccessResult()
    {
        // 创建测试所需的取件订单对象
        $pickupOrder = $this->createPickupOrderMock();
        
        // 模拟HTTP客户端响应
        $successResponse = [
            'jingdong_ldop_receive_pickuporder_receive_responce' => [
                'receivepickuporder_result' => [
                    'code' => '100',
                    'pickUpCode' => 'JD12345678',
                    'message' => '下单成功'
                ]
            ]
        ];
        
        $this->httpClientMock->method('request')
            ->with(
                'jingdong.ldop.receive.pickuporder.receive',
                $this->anything()
            )
            ->willReturn($successResponse);
            
        $this->entityManagerMock->expects($this->once())
            ->method('persist')
            ->with($this->callback(function ($order) {
                return $order instanceof PickupOrder 
                    && $order->getStatus() === JdPickupOrderStatus::STATUS_SUBMITTED
                    && $order->getPickUpCode() === 'JD12345678';
            }));
            
        $this->entityManagerMock->expects($this->once())
            ->method('flush');
            
        $result = $this->jdlService->createPickupOrder($pickupOrder);
        $this->assertEquals('100', $result['code']);
        $this->assertEquals('JD12345678', $result['pickUpCode']);
    }
    
    public function testCreatePickupOrder_withErrorResponse_throwsException()
    {
        // 创建测试所需的取件订单对象
        $pickupOrder = $this->createPickupOrderMock();
        
        // 模拟HTTP客户端错误响应
        $errorResponse = [
            'errorMessage' => '参数错误',
            'code' => '400'
        ];
        
        $this->httpClientMock->method('request')
            ->willReturn($errorResponse);
            
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('参数错误');
        
        $this->jdlService->createPickupOrder($pickupOrder);
    }
    
    public function testCreatePickupOrder_noAvailableConfig_throwsException()
    {
        $pickupOrder = $this->createPickupOrderMock();
        
        // 模拟无可用配置
        $this->configRepositoryMock = $this->createMock(JdlConfigRepository::class);
        $this->configRepositoryMock->method('getDefaultConfig')
            ->willReturn(null);
            
        // 重新创建 jdlService 实例，使用新的 mock
        $this->jdlService = new JdlService(
            $this->httpClientMock,
            $this->configRepositoryMock,
            $this->logisticsDetailRepositoryMock,
            $this->entityManagerMock
        );
            
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('无可用京东配置');
        
        $this->jdlService->createPickupOrder($pickupOrder);
    }
    
    public function testCancelPickupOrder_withValidParams_returnsSuccessResult()
    {
        // 创建测试所需的取件订单对象
        $pickupOrder = $this->createPickupOrderMock();
        $pickupOrder->setPickUpCode('JD12345678');
        
        // 模拟HTTP客户端响应
        $successResponse = [
            'jingdong_ldop_pickup_cancel_responce' => [
                'returnType' => [
                    'statusCode' => 0,
                    'message' => '取消成功'
                ]
            ]
        ];
        
        $this->httpClientMock->method('request')
            ->with(
                'jingdong.ldop.pickup.cancel',
                $this->anything()
            )
            ->willReturn($successResponse);
            
        $this->entityManagerMock->expects($this->once())
            ->method('persist')
            ->with($this->callback(function ($order) {
                return $order instanceof PickupOrder 
                    && $order->getStatus() === JdPickupOrderStatus::STATUS_CANCELLED;
            }));
            
        $this->entityManagerMock->expects($this->once())
            ->method('flush');
            
        $result = $this->jdlService->cancelPickupOrder($pickupOrder);
        $this->assertArrayHasKey('returnType', $result);
        $this->assertEquals(0, $result['returnType']['statusCode']);
    }
    
    public function testCancelPickupOrder_withErrorResponse_throwsException()
    {
        // 创建测试所需的取件订单对象
        $pickupOrder = $this->createPickupOrderMock();
        $pickupOrder->setPickUpCode('JD12345678');
        
        // 模拟HTTP客户端错误响应
        $errorResponse = [
            'error_response' => [
                'code' => '500',
                'zh_desc' => '取消失败'
            ]
        ];
        
        $this->httpClientMock->method('request')
            ->willReturn($errorResponse);
            
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('取消失败');
        
        $this->jdlService->cancelPickupOrder($pickupOrder);
    }
    
    public function testGetLogisticsTrace_withValidParams_returnsLogisticsDetails()
    {
        // 创建测试所需的取件订单对象
        $pickupOrder = new TestPickupOrder();
        $pickupOrder->setOrderCode('ORDER12345');
        $pickupOrder->setConfig($this->mockConfig);
        
        // 模拟HTTP客户端响应
        $successResponse = [
            'jingdong_ldop_receive_trace_get_response' => [
                'receiveTraceGetResult' => [
                    'traceList' => [
                        [
                            'operateTime' => '2023-01-01 12:00:00',
                            'operateRemark' => '已揽收',
                            'operatePlace' => '北京分拣中心',
                            'operateType' => '1',
                            'operator' => '快递员'
                        ],
                        [
                            'operateTime' => '2023-01-02 12:00:00',
                            'operateRemark' => '运输中',
                            'operatePlace' => '北京分拣中心',
                            'operateType' => '2'
                        ]
                    ]
                ]
            ]
        ];
        
        $this->httpClientMock->method('request')
            ->with(
                'jingdong.ldop.receive.trace.get',
                $this->anything()
            )
            ->willReturn($successResponse);
            
        // 模拟存在检查
        $this->logisticsDetailRepositoryMock->method('findOneBy')
            ->willReturn(null);
            
        $result = $this->jdlService->getLogisticsTrace('JD12345678', $pickupOrder);
        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(LogisticsDetail::class, $result);
        $this->assertEquals('JD12345678', $result[0]->getWaybillCode());
        $this->assertEquals('已揽收', $result[0]->getOperateRemark());
    }
    
    public function testGetLogisticsTrace_withExistingRecord_skipsExistingAndReturnsNew()
    {
        // 创建测试所需的取件订单对象
        $pickupOrder = new TestPickupOrder();
        $pickupOrder->setOrderCode('ORDER12345');
        $pickupOrder->setConfig($this->mockConfig);
        
        // 模拟HTTP客户端响应
        $successResponse = [
            'jingdong_ldop_receive_trace_get_response' => [
                'receiveTraceGetResult' => [
                    'traceList' => [
                        [
                            'operateTime' => '2023-01-01 12:00:00',
                            'operateRemark' => '已揽收',
                            'operatePlace' => '北京分拣中心',
                            'operateType' => '1',
                            'operator' => '快递员'
                        ],
                        [
                            'operateTime' => '2023-01-02 12:00:00',
                            'operateRemark' => '运输中',
                            'operatePlace' => '北京分拣中心',
                            'operateType' => '2'
                        ]
                    ]
                ]
            ]
        ];
        
        $this->httpClientMock->method('request')
            ->willReturn($successResponse);
            
        // 模拟第一个记录已存在
        $this->logisticsDetailRepositoryMock->method('findOneBy')
            ->willReturnCallback(function ($criteria) {
                if ($criteria['operateType'] === '1') {
                    return new LogisticsDetail();
                }
                return null;
            });
            
        $result = $this->jdlService->getLogisticsTrace('JD12345678', $pickupOrder);
        $this->assertCount(1, $result);
        $this->assertEquals('运输中', $result[0]->getOperateRemark());
    }
    
    public function testGetLogisticsTrace_withErrorResponse_throwsException()
    {
        // 创建测试所需的取件订单对象
        $pickupOrder = new TestPickupOrder();
        $pickupOrder->setOrderCode('ORDER12345');
        $pickupOrder->setConfig($this->mockConfig);
        
        // 模拟HTTP客户端错误响应
        $errorResponse = [
            'error_response' => [
                'code' => '500',
                'zh_desc' => '获取物流信息失败'
            ]
        ];
        
        $this->httpClientMock->method('request')
            ->willReturn($errorResponse);
            
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('获取物流信息失败');
        
        $this->jdlService->getLogisticsTrace('JD12345678', $pickupOrder);
    }
    
    public function testGetLogisticsTrace_withEmptyResponse_returnsEmptyArray()
    {
        // 创建测试所需的取件订单对象
        $pickupOrder = new TestPickupOrder();
        $pickupOrder->setOrderCode('ORDER12345');
        $pickupOrder->setConfig($this->mockConfig);
        
        // 模拟HTTP客户端空响应
        $emptyResponse = [
            'jingdong_ldop_receive_trace_get_response' => []
        ];
        
        $this->httpClientMock->method('request')
            ->willReturn($emptyResponse);
            
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