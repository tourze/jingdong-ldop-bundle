<?php

namespace JingdongLdopBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use JingdongLdopBundle\Entity\LogisticsDetail;
use JingdongLdopBundle\Entity\PickupOrder;
use JingdongLdopBundle\Enum\JdPickupOrderStatus;
use JingdongLdopBundle\Exception\JdlConfigException;
use JingdongLdopBundle\Exception\JdlApiException;
use JingdongLdopBundle\Repository\JdlConfigRepository;
use JingdongLdopBundle\Repository\LogisticsDetailRepository;

class JdlService
{
    public function __construct(
        private readonly JdlHttpClient $httpClient,
        private readonly JdlConfigRepository $configRepository,
        private readonly LogisticsDetailRepository $logisticsDetailRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * 创建京东物流取件订单
     * 文档：https://jos.jd.com/apilistnewdetail?apiGroupId=64&apiId=15991&apiName=null
     */
    public function createPickupOrder(PickupOrder $pickupOrder): array
    {
        $config = $this->configRepository->getDefaultConfig();
        if (empty($config)) {
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
            'customerCode' => $pickupOrder->getConfig()->getCustomerCode(),
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
            $response = $this->httpClient->request(
                'jingdong.ldop.receive.pickuporder.receive',
                $params
            );

            // 处理响应结果
            if (isset($response['errorMessage'])) {
                throw new JdlApiException($response['errorMessage'], $response['code'] ?? 500);
            }

            $result = $response['jingdong_ldop_receive_pickuporder_receive_responce']['receivepickuporder_result'] ?? [];
            if (isset($result['code']) && '100' !== $result['code']) {
                throw new JdlApiException($response['messsage'] ?? '京东下单失败');
            }

            // 更新订单状态
            $pickupOrder->setStatus(JdPickupOrderStatus::STATUS_SUBMITTED);

            // 设置取件码（如果返回）
            if (!empty($result['pickUpCode'])) {
                $pickupOrder->setPickUpCode($result['pickUpCode']);
                $this->entityManager->persist($pickupOrder);
                $this->entityManager->flush();
            }

            return $result;
        } catch (\Throwable $e) {
            throw new JdlApiException('创建取件订单失败: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * 取消京东物流取件订单
     */
    public function cancelPickupOrder(PickupOrder $pickupOrder, string $cancelReason = '客户取消服务单，终止取件'): array
    {
        $params = [
            'pickupCode' => $pickupOrder->getPickUpCode(),
            'endReason' => 19,                    // 取消原因
            'operateTime' => date('Y-m-d H:i:s'),              // 操作时间
            'endReasonName' => $cancelReason,
            'source' => 'ECLP',
            'customerCode' => $pickupOrder->getConfig()->getCustomerCode(),
        ];

        try {
            $response = $this->httpClient->request(
                'jingdong.ldop.pickup.cancel',
                $params
            );

            // 处理响应结果
            if (isset($response['error_response'])) {
                throw new JdlApiException($response['error_response']['zh_desc'] ?? '取消取件订单失败', $response['error_response']['code'] ?? 500);
            }

            $result = $response['jingdong_ldop_pickup_cancel_responce'] ?? [];

            // 更新订单状态
            if (isset($result['returnType']['statusCode']) && 0 === $result['returnType']['statusCode']) {
                $pickupOrder->setStatus(JdPickupOrderStatus::STATUS_CANCELLED);
                $this->entityManager->persist($pickupOrder);
                $this->entityManager->flush();
            }

            return $result;
        } catch (\Throwable $e) {
            throw new JdlApiException('取消取件订单失败: ' . $e->getMessage(), $e->getCode(), $e);
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
    public function getLogisticsTrace(string $waybillCode, PickupOrder $pickupOrder): array
    {
        $params = [
            'waybillCode' => $waybillCode,                          // 运单号
            'customerCode' => $pickupOrder->getConfig()->getCustomerCode(),  // 商家编码
        ];

        try {
            $response = $this->httpClient->request(
                'jingdong.ldop.receive.trace.get',
                $params
            );

            // 处理响应结果
            if (isset($response['error_response'])) {
                throw new JdlApiException($response['error_response']['zh_desc'] ?? '获取物流信息失败', $response['error_response']['code'] ?? 500);
            }

            $result = $response['jingdong_ldop_receive_trace_get_response'] ?? [];
            if (empty($result) || !isset($result['receiveTraceGetResult']['traceList'])) {
                return [];
            }

            // 转换为 LogisticsDetail 实体数组
            $logisticsDetails = [];
            foreach ($result['receiveTraceGetResult']['traceList'] as $trace) {
                // 检查是否已存在相同的物流信息
                $exists = $this->logisticsDetailRepository->findOneBy([
                    'waybillCode' => $waybillCode,
                    'operateTime' => new \DateTimeImmutable($trace['operateTime']),
                    'operateType' => $trace['operateType'],
                ]);

                if ($exists !== null) {
                    continue; // 跳过已存在的记录
                }

                $detail = new LogisticsDetail();
                $detail->setWaybillCode($waybillCode)
                    ->setCustomerCode($pickupOrder->getConfig()->getCustomerCode())
                    ->setOrderCode($pickupOrder->getId() ?? 'unknown')
                    ->setOperateTime($trace['operateTime'] ?? '')
                    ->setOperateRemark($trace['operateRemark'] ?? '')
                    ->setOperateSite($trace['operatePlace'] ?? '')
                    ->setOperateType($trace['operateType'] ?? '');

                // 设置可选字段
                if (!empty($trace['operator'])) {
                    $detail->setOperateUser($trace['operator']);
                }

                $logisticsDetails[] = $detail;
            }

            return $logisticsDetails;
        } catch (\Throwable $e) {
            throw new JdlApiException('获取物流信息失败: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

}
