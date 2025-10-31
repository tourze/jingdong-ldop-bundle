<?php

declare(strict_types=1);

namespace JingdongLdopBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JingdongLdopBundle\Enum\JdPickupOrderStatus;
use JingdongLdopBundle\Repository\PickupOrderRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;

#[ORM\Entity(repositoryClass: PickupOrderRepository::class)]
#[ORM\Table(name: 'jdl_pickup_order', options: ['comment' => '京东物流取件订单表'])]
class PickupOrder implements \Stringable
{
    use TimestampableAware;
    use BlameableAware;
    use SnowflakeKeyAware;

    #[IndexColumn]
    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    #[Assert\Type(type: 'bool')]
    private ?bool $valid = false;

    #[ORM\ManyToOne(targetEntity: JdlConfig::class)]
    #[ORM\JoinColumn(name: 'config_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private ?JdlConfig $config = null; // 京东物流配置（必传）

    #[ORM\Column(type: Types::STRING, length: 50, nullable: false, options: ['comment' => '寄件人姓名'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    private string $senderName;    // 寄件人姓名（必传）

    #[ORM\Column(type: Types::STRING, length: 20, nullable: false, options: ['comment' => '寄件人手机号'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 20)]
    #[Assert\Regex(pattern: '/^1[3-9]\d{9}$/', message: '手机号格式不正确')]
    private string $senderMobile;  // 寄件人手机号（必传）

    #[ORM\Column(type: Types::STRING, length: 255, nullable: false, options: ['comment' => '寄件人地址'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $senderAddress; // 寄件人地址（必传）

    #[ORM\Column(type: Types::STRING, length: 20, nullable: true, options: ['comment' => '寄件人邮编'])]
    #[Assert\Length(max: 20)]
    #[Assert\Regex(pattern: '/^\d{6}$/', message: '邮编格式不正确')]
    private ?string $senderPostcode = null; // 寄件人邮编（非必传）

    #[ORM\Column(type: Types::STRING, length: 50, nullable: false, options: ['comment' => '收件人姓名'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    private string $receiverName;  // 收件人姓名（必传）

    #[ORM\Column(type: Types::STRING, length: 20, nullable: false, options: ['comment' => '收件人手机号'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 20)]
    #[Assert\Regex(pattern: '/^1[3-9]\d{9}$/', message: '手机号格式不正确')]
    private string $receiverMobile; // 收件人手机号（必传）

    #[ORM\Column(type: Types::STRING, length: 255, nullable: false, options: ['comment' => '收件人地址'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $receiverAddress; // 收件人地址（必传）

    #[ORM\Column(type: Types::STRING, length: 20, nullable: true, options: ['comment' => '收件人邮编'])]
    #[Assert\Length(max: 20)]
    #[Assert\Regex(pattern: '/^\d{6}$/', message: '邮编格式不正确')]
    private ?string $receiverPostcode = null; // 收件人邮编（非必传）

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: false, options: ['comment' => '重量(kg)'])]
    #[Assert\NotBlank]
    #[Assert\Type(type: 'numeric')]
    #[Assert\GreaterThan(value: 0, message: '重量必须大于0')]
    private float $weight = 0.5;  // 重量(kg)（必传）

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true, options: ['comment' => '长(cm)'])]
    #[Assert\Type(type: 'numeric')]
    #[Assert\GreaterThan(value: 0, message: '长度必须大于0')]
    private ?float $length = null;  // 长(cm)（非必传）

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true, options: ['comment' => '宽(cm)'])]
    #[Assert\Type(type: 'numeric')]
    #[Assert\GreaterThan(value: 0, message: '宽度必须大于0')]
    private ?float $width = null;   // 宽(cm)（非必传）

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true, options: ['comment' => '高(cm)'])]
    #[Assert\Type(type: 'numeric')]
    #[Assert\GreaterThan(value: 0, message: '高度必须大于0')]
    private ?float $height = null;  // 高(cm)（非必传）

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '备注信息'])]
    #[Assert\Length(max: 65535)]
    private ?string $remark = null; // 备注信息（非必传）

    #[ORM\Column(type: Types::STRING, length: 32, nullable: false, options: ['comment' => '订单状态'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 32)]
    private string $status = JdPickupOrderStatus::STATUS_CREATED; // 订单状态

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true, options: ['comment' => '包裹名称'])]
    #[Assert\Length(max: 50)]
    private ?string $packageName = null;     // 包裹名称（非必传）

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '包裹数量'])]
    #[Assert\Type(type: 'integer')]
    #[Assert\PositiveOrZero]
    private ?int $packageQuantity = null;    // 包裹数量（非必传）

    #[ORM\Column(type: Types::STRING, length: 32, nullable: true, options: ['comment' => '寄件人省份'])]
    #[Assert\Length(max: 32)]
    private ?string $senderProvince = null;  // 寄件人省份（非必传）

    #[ORM\Column(type: Types::STRING, length: 32, nullable: true, options: ['comment' => '寄件人城市'])]
    #[Assert\Length(max: 32)]
    private ?string $senderCity = null;      // 寄件人城市（非必传）

    #[ORM\Column(type: Types::STRING, length: 32, nullable: true, options: ['comment' => '寄件人区县'])]
    #[Assert\Length(max: 32)]
    private ?string $senderCounty = null;    // 寄件人区县（非必传）

    #[ORM\Column(type: Types::STRING, length: 32, nullable: true, options: ['comment' => '收件人省份'])]
    #[Assert\Length(max: 32)]
    private ?string $receiverProvince = null; // 收件人省份（非必传）

    #[ORM\Column(type: Types::STRING, length: 32, nullable: true, options: ['comment' => '收件人城市'])]
    #[Assert\Length(max: 32)]
    private ?string $receiverCity = null;     // 收件人城市（非必传）

    #[ORM\Column(type: Types::STRING, length: 32, nullable: true, options: ['comment' => '收件人区县'])]
    #[Assert\Length(max: 32)]
    private ?string $receiverCounty = null;   // 收件人区县（非必传）

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '期望取件开始时间'])]
    #[Assert\Type(type: \DateTimeImmutable::class)]
    private ?\DateTimeImmutable $pickupStartTime = null; // 期望取件开始时间（非必传）

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '期望取件结束时间'])]
    #[Assert\Type(type: \DateTimeImmutable::class)]
    private ?\DateTimeImmutable $pickupEndTime = null;   // 期望取件结束时间（非必传）

    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 32, nullable: true, options: ['comment' => '取件单号'])]
    #[Assert\Length(max: 32)]
    private ?string $pickUpCode = null;   // 取件单号

    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): void
    {
        $this->valid = $valid;
    }

    // Getters and Setters
    public function getConfig(): ?JdlConfig
    {
        return $this->config;
    }

    public function setConfig(?JdlConfig $config): void
    {
        $this->config = $config;
    }

    public function getSenderName(): string
    {
        return $this->senderName;
    }

    public function setSenderName(string $senderName): void
    {
        $this->senderName = $senderName;
    }

    public function getSenderAddress(): string
    {
        return $this->senderAddress;
    }

    public function setSenderAddress(string $senderAddress): void
    {
        $this->senderAddress = $senderAddress;
    }

    public function getSenderPostcode(): ?string
    {
        return $this->senderPostcode;
    }

    public function setSenderPostcode(?string $senderPostcode): void
    {
        $this->senderPostcode = $senderPostcode;
    }

    public function getReceiverName(): string
    {
        return $this->receiverName;
    }

    public function setReceiverName(string $receiverName): void
    {
        $this->receiverName = $receiverName;
    }

    public function getReceiverAddress(): string
    {
        return $this->receiverAddress;
    }

    public function setReceiverAddress(string $receiverAddress): void
    {
        $this->receiverAddress = $receiverAddress;
    }

    public function getReceiverPostcode(): ?string
    {
        return $this->receiverPostcode;
    }

    public function setReceiverPostcode(?string $receiverPostcode): void
    {
        $this->receiverPostcode = $receiverPostcode;
    }

    public function getWeight(): float
    {
        return $this->weight;
    }

    public function setWeight(?float $weight): void
    {
        $this->weight = $weight ?? 0.5;  // 如果为null，使用默认值0.5
    }

    public function getLength(): ?float
    {
        return $this->length;
    }

    public function setLength(?float $length): void
    {
        $this->length = $length;
    }

    public function getWidth(): ?float
    {
        return $this->width;
    }

    public function setWidth(?float $width): void
    {
        $this->width = $width;
    }

    public function getHeight(): ?float
    {
        return $this->height;
    }

    public function setHeight(?float $height): void
    {
        $this->height = $height;
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): void
    {
        $this->remark = $remark;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getSenderMobile(): string
    {
        return $this->senderMobile;
    }

    public function setSenderMobile(string $senderMobile): void
    {
        $this->senderMobile = $senderMobile;
    }

    public function getReceiverMobile(): string
    {
        return $this->receiverMobile;
    }

    public function setReceiverMobile(string $receiverMobile): void
    {
        $this->receiverMobile = $receiverMobile;
    }

    public function getPackageName(): ?string
    {
        return $this->packageName;
    }

    public function setPackageName(?string $packageName): void
    {
        $this->packageName = $packageName;
    }

    public function getPackageQuantity(): ?int
    {
        return $this->packageQuantity;
    }

    public function setPackageQuantity(?int $packageQuantity): void
    {
        $this->packageQuantity = $packageQuantity;
    }

    public function getSenderProvince(): ?string
    {
        return $this->senderProvince;
    }

    public function setSenderProvince(?string $senderProvince): void
    {
        $this->senderProvince = $senderProvince;
    }

    public function getSenderCity(): ?string
    {
        return $this->senderCity;
    }

    public function setSenderCity(?string $senderCity): void
    {
        $this->senderCity = $senderCity;
    }

    public function getSenderCounty(): ?string
    {
        return $this->senderCounty;
    }

    public function setSenderCounty(?string $senderCounty): void
    {
        $this->senderCounty = $senderCounty;
    }

    public function getReceiverProvince(): ?string
    {
        return $this->receiverProvince;
    }

    public function setReceiverProvince(?string $receiverProvince): void
    {
        $this->receiverProvince = $receiverProvince;
    }

    public function getReceiverCity(): ?string
    {
        return $this->receiverCity;
    }

    public function setReceiverCity(?string $receiverCity): void
    {
        $this->receiverCity = $receiverCity;
    }

    public function getReceiverCounty(): ?string
    {
        return $this->receiverCounty;
    }

    public function setReceiverCounty(?string $receiverCounty): void
    {
        $this->receiverCounty = $receiverCounty;
    }

    public function getPickupStartTime(): ?\DateTimeImmutable
    {
        return $this->pickupStartTime;
    }

    public function setPickupStartTime(?\DateTimeImmutable $pickupStartTime): void
    {
        $this->pickupStartTime = $pickupStartTime;
    }

    public function getPickupEndTime(): ?\DateTimeImmutable
    {
        return $this->pickupEndTime;
    }

    public function setPickupEndTime(?\DateTimeImmutable $pickupEndTime): void
    {
        $this->pickupEndTime = $pickupEndTime;
    }

    public function getPickUpCode(): ?string
    {
        return $this->pickUpCode;
    }

    public function setPickUpCode(?string $pickUpCode): void
    {
        $this->pickUpCode = $pickUpCode;
    }

    public function __toString(): string
    {
        return $this->pickUpCode ?? $this->id ?? '';
    }
}
