<?php

declare(strict_types=1);

namespace JingdongLdopBundle\Tests\Service;

use JingdongLdopBundle\Entity\JdlConfig;
use JingdongLdopBundle\Entity\PickupOrder;

/**
 * 测试用 PickupOrder 包装类
 * 使用组合模式包装原始实体，添加了测试需要的 orderCode 属性和相关方法
 *
 * @internal
 */
final class TestPickupOrder
{
    private ?string $orderCode = null;

    private PickupOrder $pickupOrder;

    public function __construct()
    {
        $this->pickupOrder = new PickupOrder();
    }

    /**
     * 获取订单编号
     */
    public function getOrderCode(): ?string
    {
        return $this->orderCode;
    }

    /**
     * 设置订单编号
     */
    public function setOrderCode(?string $orderCode): void
    {
        $this->orderCode = $orderCode;
    }

    /**
     * 获取包装的PickupOrder实体
     */
    public function getPickupOrder(): PickupOrder
    {
        return $this->pickupOrder;
    }

    /**
     * 设置配置方法的便捷包装
     */
    public function setConfig(?JdlConfig $config): void
    {
        $this->pickupOrder->setConfig($config);
    }

    /**
     * 一些常用的委托方法，避免使用魔术方法
     */
    public function setSenderName(string $senderName): void
    {
        $this->pickupOrder->setSenderName($senderName);
    }

    public function setSenderMobile(string $senderMobile): void
    {
        $this->pickupOrder->setSenderMobile($senderMobile);
    }

    public function setSenderAddress(string $senderAddress): void
    {
        $this->pickupOrder->setSenderAddress($senderAddress);
    }

    public function setReceiverName(string $receiverName): void
    {
        $this->pickupOrder->setReceiverName($receiverName);
    }

    public function setReceiverMobile(string $receiverMobile): void
    {
        $this->pickupOrder->setReceiverMobile($receiverMobile);
    }

    public function setReceiverAddress(string $receiverAddress): void
    {
        $this->pickupOrder->setReceiverAddress($receiverAddress);
    }

    public function setWeight(float $weight): void
    {
        $this->pickupOrder->setWeight($weight);
    }

    public function setPickUpCode(?string $pickUpCode): void
    {
        $this->pickupOrder->setPickUpCode($pickUpCode);
    }

    public function getStatus(): string
    {
        return $this->pickupOrder->getStatus();
    }

    public function getPickUpCode(): ?string
    {
        return $this->pickupOrder->getPickUpCode();
    }
}
