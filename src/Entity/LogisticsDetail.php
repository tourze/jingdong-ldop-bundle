<?php

declare(strict_types=1);

namespace JingdongLdopBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JingdongLdopBundle\Enum\JdLogisticsStatus;
use JingdongLdopBundle\Repository\LogisticsDetailRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;

#[ORM\Entity(repositoryClass: LogisticsDetailRepository::class)]
#[ORM\Table(name: 'jdl_logistics_detail', options: ['comment' => '京东物流详情表'])]
class LogisticsDetail implements \Stringable
{
    use TimestampableAware;
    use BlameableAware;
    use SnowflakeKeyAware;

    #[ORM\Column(type: Types::STRING, length: 32, options: ['comment' => '运单号'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 32)]
    private string $waybillCode;    // 运单号（必传）

    #[ORM\Column(type: Types::STRING, length: 32, options: ['comment' => '商家编码'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 32)]
    private string $customerCode;   // 商家编码（必传）

    #[ORM\Column(type: Types::STRING, length: 32, options: ['comment' => '订单号'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 32)]
    private string $orderCode;      // 订单号（必传）

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '操作时间'])]
    #[Assert\NotBlank]
    #[Assert\Type(type: \DateTimeImmutable::class)]
    private \DateTimeImmutable $operateTime;  // 操作时间（必传）

    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '操作描述'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $operateRemark;  // 操作描述（必传）

    #[ORM\Column(type: Types::STRING, length: 32, options: ['comment' => '操作网点'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 32)]
    private string $operateSite;    // 操作网点（必传）

    #[ORM\Column(type: Types::STRING, length: 32, nullable: true, options: ['comment' => '操作人员'])]
    #[Assert\Length(max: 32)]
    private ?string $operateUser = null;    // 操作人员（非必传）

    #[ORM\Column(type: Types::STRING, length: 32, options: ['comment' => '操作类型'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 32)]
    private string $operateType;    // 操作类型（必传）

    #[ORM\Column(type: Types::STRING, length: 32, enumType: JdLogisticsStatus::class, options: ['comment' => '运单状态'])]
    #[Assert\NotNull]
    #[Assert\Choice(callback: [JdLogisticsStatus::class, 'cases'])]
    private JdLogisticsStatus $waybillStatus;  // 运单状态

    #[ORM\Column(type: Types::STRING, length: 32, nullable: true, options: ['comment' => '下一站网点'])]
    #[Assert\Length(max: 32)]
    private ?string $nextSite = null;  // 下一站网点（非必传）

    #[ORM\Column(type: Types::STRING, length: 32, nullable: true, options: ['comment' => '下一站城市'])]
    #[Assert\Length(max: 32)]
    private ?string $nextCity = null;   // 下一站城市（非必传）

    // Getters and Setters
    public function getWaybillCode(): string
    {
        return $this->waybillCode;
    }

    public function setWaybillCode(string $waybillCode): void
    {
        $this->waybillCode = $waybillCode;
    }

    public function getCustomerCode(): string
    {
        return $this->customerCode;
    }

    public function setCustomerCode(string $customerCode): void
    {
        $this->customerCode = $customerCode;
    }

    public function getOrderCode(): string
    {
        return $this->orderCode;
    }

    public function setOrderCode(string $orderCode): void
    {
        $this->orderCode = $orderCode;
    }

    public function getOperateTime(): \DateTimeImmutable
    {
        return $this->operateTime;
    }

    public function setOperateTime(?\DateTimeImmutable $operateTime): void
    {
        if (null === $operateTime) {
            throw new \InvalidArgumentException('操作时间不能为空');
        }
        $this->operateTime = $operateTime;
    }

    public function getOperateRemark(): string
    {
        return $this->operateRemark;
    }

    public function setOperateRemark(string $operateRemark): void
    {
        $this->operateRemark = $operateRemark;
    }

    public function getOperateSite(): string
    {
        return $this->operateSite;
    }

    public function setOperateSite(string $operateSite): void
    {
        $this->operateSite = $operateSite;
    }

    public function getOperateUser(): ?string
    {
        return $this->operateUser;
    }

    public function setOperateUser(?string $operateUser): void
    {
        $this->operateUser = $operateUser;
    }

    public function getOperateType(): string
    {
        return $this->operateType;
    }

    public function setOperateType(string $operateType): void
    {
        $this->operateType = $operateType;
    }

    public function getWaybillStatus(): JdLogisticsStatus
    {
        return $this->waybillStatus;
    }

    public function setWaybillStatus(JdLogisticsStatus $waybillStatus): void
    {
        $this->waybillStatus = $waybillStatus;
    }

    public function getNextSite(): ?string
    {
        return $this->nextSite;
    }

    public function setNextSite(?string $nextSite): void
    {
        $this->nextSite = $nextSite;
    }

    public function getNextCity(): ?string
    {
        return $this->nextCity;
    }

    public function setNextCity(?string $nextCity): void
    {
        $this->nextCity = $nextCity;
    }

    public function __toString(): string
    {
        return $this->waybillCode;
    }
}
