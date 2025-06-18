<?php

namespace JingdongLdopBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JingdongLdopBundle\Enum\JdLogisticsStatus;
use JingdongLdopBundle\Repository\LogisticsDetailRepository;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Attribute\CreatedByColumn;
use Tourze\DoctrineUserBundle\Attribute\UpdatedByColumn;

#[ORM\Entity(repositoryClass: LogisticsDetailRepository::class)]
#[ORM\Table(name: 'jdl_logistics_detail')]
class LogisticsDetail
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = null;

    #[CreatedByColumn]
    #[ORM\Column(nullable: true, options: ['comment' => '创建人'])]
    private ?string $createdBy = null;

    #[UpdatedByColumn]
    #[ORM\Column(nullable: true, options: ['comment' => '更新人'])]
    private ?string $updatedBy = null;

    #[ORM\Column(type: 'string', length: 32)]
    private string $waybillCode;    // 运单号（必传）

    #[ORM\Column(type: 'string', length: 32)]
    private string $customerCode;   // 商家编码（必传）

    #[ORM\Column(type: 'string', length: 32)]
    private string $orderCode;      // 订单号（必传）

    #[ORM\Column(type: 'datetime')]
    private \DateTime $operateTime;  // 操作时间（必传）

    #[ORM\Column(type: 'string', length: 255)]
    private string $operateRemark;  // 操作描述（必传）

    #[ORM\Column(type: 'string', length: 32)]
    private string $operateSite;    // 操作网点（必传）

    #[ORM\Column(type: 'string', length: 32, nullable: true)]
    private ?string $operateUser = null;    // 操作人员（非必传）

    #[ORM\Column(type: 'string', length: 32)]
    private string $operateType;    // 操作类型（必传）

    #[ORM\Column(type: 'string', length: 32, enumType: JdLogisticsStatus::class)]
    private JdLogisticsStatus $waybillStatus;  // 运单状态

    #[ORM\Column(type: 'string', length: 32, nullable: true)]
    private ?string $nextSite = null;  // 下一站网点（非必传）

    #[ORM\Column(type: 'string', length: 32, nullable: true)]
    private ?string $nextCity = null;   // 下一站城市（非必传）

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setCreatedBy(?string $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    public function setUpdatedBy(?string $updatedBy): self
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    public function getUpdatedBy(): ?string
    {
        return $this->updatedBy;
    }

    // Getters and Setters
    public function getWaybillCode(): string
    {
        return $this->waybillCode;
    }

    public function setWaybillCode(string $waybillCode): self
    {
        $this->waybillCode = $waybillCode;

        return $this;
    }

    public function getCustomerCode(): string
    {
        return $this->customerCode;
    }

    public function setCustomerCode(string $customerCode): self
    {
        $this->customerCode = $customerCode;

        return $this;
    }

    public function getOrderCode(): string
    {
        return $this->orderCode;
    }

    public function setOrderCode(string $orderCode): self
    {
        $this->orderCode = $orderCode;

        return $this;
    }

    public function getOperateTime(): \DateTime
    {
        return $this->operateTime;
    }

    public function setOperateTime(string $operateTime): self
    {
        $this->operateTime = new \DateTime($operateTime);

        return $this;
    }

    public function getOperateRemark(): string
    {
        return $this->operateRemark;
    }

    public function setOperateRemark(string $operateRemark): self
    {
        $this->operateRemark = $operateRemark;

        return $this;
    }

    public function getOperateSite(): string
    {
        return $this->operateSite;
    }

    public function setOperateSite(string $operateSite): self
    {
        $this->operateSite = $operateSite;

        return $this;
    }

    public function getOperateUser(): ?string
    {
        return $this->operateUser;
    }

    public function setOperateUser(?string $operateUser): self
    {
        $this->operateUser = $operateUser;

        return $this;
    }

    public function getOperateType(): string
    {
        return $this->operateType;
    }

    public function setOperateType(string $operateType): self
    {
        $this->operateType = $operateType;

        return $this;
    }

    public function getWaybillStatus(): JdLogisticsStatus
    {
        return $this->waybillStatus;
    }

    public function setWaybillStatus(JdLogisticsStatus $waybillStatus): self
    {
        $this->waybillStatus = $waybillStatus;

        return $this;
    }

    public function getNextSite(): ?string
    {
        return $this->nextSite;
    }

    public function setNextSite(?string $nextSite): self
    {
        $this->nextSite = $nextSite;

        return $this;
    }

    public function getNextCity(): ?string
    {
        return $this->nextCity;
    }

    public function setNextCity(?string $nextCity): self
    {
        $this->nextCity = $nextCity;

        return $this;
    }
}
