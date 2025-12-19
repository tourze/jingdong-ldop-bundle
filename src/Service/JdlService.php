<?php

declare(strict_types=1);

namespace JingdongLdopBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use JingdongLdopBundle\Entity\JdlConfig;
use JingdongLdopBundle\Entity\LogisticsDetail;
use JingdongLdopBundle\Entity\PickupOrder;
use JingdongLdopBundle\Enum\JdPickupOrderStatus;
use JingdongLdopBundle\Exception\JdlApiException;
use JingdongLdopBundle\Exception\JdlConfigException;
use JingdongLdopBundle\Repository\JdlConfigRepository;
use JingdongLdopBundle\Repository\LogisticsDetailRepository;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(public: true)]
#[WithMonologChannel(channel: 'jingdong_ldop')]
final readonly class JdlService
{
    public function __construct(
        private JdlHttpClient $httpClient,
        private JdlConfigRepository $configRepository,
        private LogisticsDetailRepository $logisticsDetailRepository,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * 创建京东物流取件订单
     * 文档：https://jos.jd.com/apilistnewdetail?apiGroupId=64&apiId=15991&apiName=null
     */
    /**
     * @return array<string, mixed>
     */
    public function createPickupOrder(PickupOrder $pickupOrder): array
    {
        $startTime = microtime(true);
        $config = $this->configRepository->getDefaultConfig();
        if (null === $config) {
            throw new JdlConfigException('无可用京东配置');
        }
        $pickupOrder->setConfig($config);

        // 构建请求参数
        $params = [
            'orderId' => $pickupOrder->getId(),
            'pickupName' => $pickupOrder->getSenderName(),
            'pickupTel' => $pickupOrder->getSenderMobile(),
            'pickupAddress' => $pickupOrder->getSenderAddress(),
            'customerContract' => $pickupOrder->getReceiverName(),
            'customerTel' => $pickupOrder->getReceiverMobile(),
            'backAddress' => $pickupOrder->getReceiverAddress(),
            'customerCode' => $pickupOrder->getConfig()?->getCustomerCode() ?? $config->getCustomerCode(),
            'weight' => $pickupOrder->getWeight(),
            'volume' => 1,
            'packageCount' => $pickupOrder->getPackageQuantity() ?? 1,
            'pickupStartTime' => $pickupOrder->getPickupStartTime()?->format('Y-m-d H:i:s'),
            'pickupEndTime' => $pickupOrder->getPickupEndTime()?->format('Y-m-d H:i:s'),
            'valueAddService' => '',
            'guaranteeValue' => '',
            'remark' => '上门前请先电话联系',
            'desp' => '使用过的咖啡胶囊',
        ];

        try {
            $this->logger->info('创建京东取件订单开始', [
                'order_id' => $pickupOrder->getId(),
                'customer_code' => $config->getCustomerCode(),
                'sender_name' => $pickupOrder->getSenderName(),
                'receiver_name' => $pickupOrder->getReceiverName(),
            ]);

            $response = $this->httpClient->request(
                'jingdong.ldop.receive.pickuporder.receive',
                $params
            );

            // 处理响应结果
            if (isset($response['errorMessage'])) {
                throw new JdlApiException($response['errorMessage'], (int) ($response['code'] ?? 500));
            }

            $result = $response['jingdong_ldop_receive_pickuporder_receive_responce']['receivepickuporder_result'] ?? [];
            if (isset($result['code']) && '100' !== $result['code']) {
                throw new JdlApiException($response['messsage'] ?? '京东下单失败', (int) ($result['code'] ?? 500));
            }

            // 更新订单状态
            $pickupOrder->setStatus(JdPickupOrderStatus::STATUS_SUBMITTED);

            // 设置取件码（如果返回）
            if (isset($result['pickUpCode']) && '' !== $result['pickUpCode']) {
                $pickupOrder->setPickUpCode($result['pickUpCode']);
                $this->entityManager->persist($pickupOrder);
                $this->entityManager->flush();
            }

            $duration = microtime(true) - $startTime;
            $this->logger->info('创建京东取件订单成功', [
                'order_id' => $pickupOrder->getId(),
                'pickup_code' => $result['pickUpCode'] ?? null,
                'duration_ms' => round($duration * 1000, 2),
                'status' => $pickupOrder->getStatus(),
            ]);

            return $result;
        } catch (\Throwable $e) {
            $duration = microtime(true) - $startTime;
            $this->logger->error('创建京东取件订单失败', [
                'order_id' => $pickupOrder->getId(),
                'duration_ms' => round($duration * 1000, 2),
                'error' => $e->getMessage(),
                'exception' => get_class($e),
            ]);
            throw new JdlApiException('创建取件订单失败: ' . $e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    /**
     * 取消京东物流取件订单
     */
    /**
     * @return array<string, mixed>
     */
    public function cancelPickupOrder(PickupOrder $pickupOrder, string $cancelReason = '客户取消服务单，终止取件'): array
    {
        $startTime = microtime(true);
        $config = $pickupOrder->getConfig();
        if (null === $config) {
            throw new JdlConfigException('订单未关联京东配置');
        }

        $params = [
            'pickupCode' => $pickupOrder->getPickUpCode(),
            'endReason' => 19,                    // 取消原因
            'operateTime' => date('Y-m-d H:i:s'),              // 操作时间
            'endReasonName' => $cancelReason,
            'source' => 'ECLP',
            'customerCode' => $config->getCustomerCode(),
        ];

        try {
            $this->logger->info('取消京东取件订单开始', [
                'order_id' => $pickupOrder->getId(),
                'pickup_code' => $pickupOrder->getPickUpCode(),
                'cancel_reason' => $cancelReason,
                'customer_code' => $config->getCustomerCode(),
            ]);

            $response = $this->httpClient->request(
                'jingdong.ldop.pickup.cancel',
                $params
            );

            // 处理响应结果
            if (isset($response['error_response'])) {
                throw new JdlApiException($response['error_response']['zh_desc'] ?? '取消取件订单失败', (int) ($response['error_response']['code'] ?? 500));
            }

            $result = $response['jingdong_ldop_pickup_cancel_responce'] ?? [];

            // 更新订单状态
            if (isset($result['returnType']['statusCode']) && 0 === $result['returnType']['statusCode']) {
                $pickupOrder->setStatus(JdPickupOrderStatus::STATUS_CANCELLED);
                $this->entityManager->persist($pickupOrder);
                $this->entityManager->flush();
            }

            $duration = microtime(true) - $startTime;
            $this->logger->info('取消京东取件订单成功', [
                'order_id' => $pickupOrder->getId(),
                'pickup_code' => $pickupOrder->getPickUpCode(),
                'duration_ms' => round($duration * 1000, 2),
                'status' => $pickupOrder->getStatus(),
            ]);

            return $result;
        } catch (\Throwable $e) {
            $duration = microtime(true) - $startTime;
            $this->logger->error('取消京东取件订单失败', [
                'order_id' => $pickupOrder->getId(),
                'pickup_code' => $pickupOrder->getPickUpCode(),
                'duration_ms' => round($duration * 1000, 2),
                'error' => $e->getMessage(),
                'exception' => get_class($e),
            ]);
            throw new JdlApiException('取消取件订单失败: ' . $e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    /**
     * 获取物流跟踪信息
     * 接口：jingdong.ldop.receive.trace.get
     *
     * @param string      $waybillCode 运单号
     * @param PickupOrder $pickupOrder 取件订单
     *
     * @return array 返回物流跟踪信息数组
     *
     * @throws \Exception
     */
    /**
     * @return array<LogisticsDetail>
     */
    public function getLogisticsTrace(string $waybillCode, PickupOrder $pickupOrder): array
    {
        $startTime = microtime(true);
        $config = $this->validatePickupOrderConfig($pickupOrder);

        $params = [
            'waybillCode' => $waybillCode,
            'customerCode' => $config->getCustomerCode(),
        ];

        try {
            $this->logTraceStart($waybillCode, $pickupOrder, $config);
            $response = $this->httpClient->request('jingdong.ldop.receive.trace.get', $params);

            $this->validateApiResponse($response);
            $traceList = $this->extractTraceList($response, $waybillCode, $pickupOrder);

            if ([] === $traceList) {
                return [];
            }

            $logisticsDetails = $this->processTraceList($traceList, $waybillCode, $config, $pickupOrder);
            $this->logTraceSuccess($waybillCode, $pickupOrder, $startTime, $traceList, $logisticsDetails);

            return $logisticsDetails;
        } catch (\Throwable $e) {
            $this->logTraceError($waybillCode, $pickupOrder, $startTime, $e);
            throw new JdlApiException('获取物流信息失败: ' . $e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    private function validatePickupOrderConfig(PickupOrder $pickupOrder): JdlConfig
    {
        $config = $pickupOrder->getConfig();
        if (null === $config) {
            throw new JdlConfigException('订单未关联京东配置');
        }

        return $config;
    }

    private function logTraceStart(string $waybillCode, PickupOrder $pickupOrder, JdlConfig $config): void
    {
        $this->logger->info('获取京东物流跟踪信息开始', [
            'waybill_code' => $waybillCode,
            'order_id' => $pickupOrder->getId(),
            'customer_code' => $config->getCustomerCode(),
        ]);
    }

    /**
     * @param array<string, mixed> $response
     */
    private function validateApiResponse(array $response): void
    {
        if (isset($response['error_response'])) {
            throw new JdlApiException($response['error_response']['zh_desc'] ?? '获取物流信息失败', (int) ($response['error_response']['code'] ?? 500));
        }
    }

    /**
     * @param array<string, mixed> $response
     * @return array<mixed>
     */
    private function extractTraceList(array $response, string $waybillCode, PickupOrder $pickupOrder): array
    {
        $result = $response['jingdong_ldop_receive_trace_get_response'] ?? [];
        if ([] === $result || !isset($result['receiveTraceGetResult']['traceList'])) {
            $this->logger->warning('获取京东物流跟踪信息为空', [
                'waybill_code' => $waybillCode,
                'order_id' => $pickupOrder->getId(),
            ]);

            return [];
        }

        return $result['receiveTraceGetResult']['traceList'];
    }

    /**
     * @param array<mixed> $traceList
     * @return array<LogisticsDetail>
     */
    private function processTraceList(array $traceList, string $waybillCode, JdlConfig $config, PickupOrder $pickupOrder): array
    {
        $logisticsDetails = [];
        foreach ($traceList as $trace) {
            if ($this->isTraceExists($waybillCode, $trace)) {
                continue;
            }

            $detail = $this->createLogisticsDetail($trace, $waybillCode, $config, $pickupOrder);
            $logisticsDetails[] = $detail;
        }

        return $logisticsDetails;
    }

    /**
     * @param array<string, mixed> $trace
     */
    private function isTraceExists(string $waybillCode, array $trace): bool
    {
        $exists = $this->logisticsDetailRepository->findOneBy([
            'waybillCode' => $waybillCode,
            'operateTime' => new \DateTimeImmutable($trace['operateTime']),
            'operateType' => $trace['operateType'],
        ]);

        return null !== $exists;
    }

    /**
     * @param array<string, mixed> $trace
     */
    private function createLogisticsDetail(array $trace, string $waybillCode, JdlConfig $config, PickupOrder $pickupOrder): LogisticsDetail
    {
        $detail = new LogisticsDetail();
        $detail->setWaybillCode($waybillCode);
        $detail->setCustomerCode($config->getCustomerCode());
        $detail->setOrderCode($pickupOrder->getId() ?? 'unknown');

        $this->setOperateTime($detail, $trace);

        $detail->setOperateRemark($trace['operateRemark'] ?? '');
        $detail->setOperateSite($trace['operatePlace'] ?? '');
        $detail->setOperateType($trace['operateType'] ?? '');

        if (isset($trace['operator']) && '' !== $trace['operator']) {
            $detail->setOperateUser($trace['operator']);
        }

        return $detail;
    }

    /**
     * @param array<string, mixed> $trace
     */
    private function setOperateTime(LogisticsDetail $detail, array $trace): void
    {
        $operateTimeStr = $trace['operateTime'] ?? '';
        if ('' !== $operateTimeStr) {
            try {
                $operateTime = new \DateTimeImmutable($operateTimeStr);
                $detail->setOperateTime($operateTime);
            } catch (\Exception) {
                $detail->setOperateTime(new \DateTimeImmutable());
            }
        } else {
            $detail->setOperateTime(new \DateTimeImmutable());
        }
    }

    /**
     * @param array<mixed> $traceList
     * @param array<LogisticsDetail> $logisticsDetails
     */
    private function logTraceSuccess(string $waybillCode, PickupOrder $pickupOrder, float $startTime, array $traceList, array $logisticsDetails): void
    {
        $duration = microtime(true) - $startTime;
        $this->logger->info('获取京东物流跟踪信息成功', [
            'waybill_code' => $waybillCode,
            'order_id' => $pickupOrder->getId(),
            'duration_ms' => round($duration * 1000, 2),
            'total_traces' => count($traceList),
            'new_traces' => count($logisticsDetails),
        ]);
    }

    private function logTraceError(string $waybillCode, PickupOrder $pickupOrder, float $startTime, \Throwable $e): void
    {
        $duration = microtime(true) - $startTime;
        $this->logger->error('获取京东物流跟踪信息失败', [
            'waybill_code' => $waybillCode,
            'order_id' => $pickupOrder->getId(),
            'duration_ms' => round($duration * 1000, 2),
            'error' => $e->getMessage(),
            'exception' => get_class($e),
        ]);
    }
}
