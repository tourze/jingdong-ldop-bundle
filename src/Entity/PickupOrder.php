<?php

namespace JingdongLdopBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JingdongLdopBundle\Enum\JdPickupOrderStatus;
use JingdongLdopBundle\Repository\PickupOrderRepository;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTimestampBundle\Attribute\CreateTimeColumn;
use Tourze\DoctrineTimestampBundle\Attribute\UpdateTimeColumn;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Attribute\CreatedByColumn;
use Tourze\DoctrineUserBundle\Attribute\UpdatedByColumn;
use Tourze\EasyAdmin\Attribute\Action\Creatable;
use Tourze\EasyAdmin\Attribute\Action\Deletable;
use Tourze\EasyAdmin\Attribute\Action\Editable;
use Tourze\EasyAdmin\Attribute\Column\BoolColumn;
use Tourze\EasyAdmin\Attribute\Column\ExportColumn;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdmin\Attribute\Field\FormField;
use Tourze\EasyAdmin\Attribute\Filter\Filterable;

#[Creatable]
#[Editable]
#[Deletable]
#[ORM\Entity(repositoryClass: PickupOrderRepository::class)]
#[ORM\Table(name: 'jdl_pickup_order')]
#[ORM\Index(columns: ['pick_up_code'], name: 'idx_pick_up_code')]
class PickupOrder
{
    #[Filterable]
    #[IndexColumn]
    #[ListColumn(order: 98, sorter: true)]
    #[ExportColumn]
    #[CreateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '创建时间'])]
    private ?\DateTimeInterface $createTime = null;

    #[UpdateTimeColumn]
    #[ListColumn(order: 99, sorter: true)]
    #[Filterable]
    #[ExportColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '更新时间'])]
    private ?\DateTimeInterface $updateTime = null;

    public function setCreateTime(?\DateTimeInterface $createdAt): void
    {
        $this->createTime = $createdAt;
    }

    public function getCreateTime(): ?\DateTimeInterface
    {
        return $this->createTime;
    }

    public function setUpdateTime(?\DateTimeInterface $updateTime): void
    {
        $this->updateTime = $updateTime;
    }

    public function getUpdateTime(): ?\DateTimeInterface
    {
        return $this->updateTime;
    }

    #[ExportColumn]
    #[ListColumn(order: -1, sorter: true)]
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

    #[BoolColumn]
    #[IndexColumn]
    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    #[ListColumn(order: 97)]
    #[FormField(order: 97)]
    private ?bool $valid = false;

    #[ORM\ManyToOne(targetEntity: JdlConfig::class)]
    #[ORM\JoinColumn(name: 'config_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private ?JdlConfig $config = null; // 京东物流配置（必传）

    #[ORM\Column(type: 'string', length: 50, nullable: false)]
    private string $senderName;    // 寄件人姓名（必传）

    #[ORM\Column(type: 'string', length: 20, nullable: false)]
    private string $senderMobile;  // 寄件人手机号（必传）

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $senderAddress; // 寄件人地址（必传）

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $senderPostcode = null; // 寄件人邮编（非必传）

    #[ORM\Column(type: 'string', length: 50, nullable: false)]
    private string $receiverName;  // 收件人姓名（必传）

    #[ORM\Column(type: 'string', length: 20, nullable: false)]
    private string $receiverMobile; // 收件人手机号（必传）

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $receiverAddress; // 收件人地址（必传）

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $receiverPostcode = null; // 收件人邮编（非必传）

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: false)]
    private float $weight = 0.5;  // 重量(kg)（必传）

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $length = null;  // 长(cm)（非必传）

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $width = null;   // 宽(cm)（非必传）

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $height = null;  // 高(cm)（非必传）

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $remark = null; // 备注信息（非必传）

    #[ORM\Column(type: 'string', length: 32, nullable: false)]
    private string $status = JdPickupOrderStatus::STATUS_CREATED; // 订单状态

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $packageName = null;     // 包裹名称（非必传）

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $packageQuantity = null;    // 包裹数量（非必传）

    #[ORM\Column(type: 'string', length: 32, nullable: true)]
    private ?string $senderProvince = null;  // 寄件人省份（非必传）

    #[ORM\Column(type: 'string', length: 32, nullable: true)]
    private ?string $senderCity = null;      // 寄件人城市（非必传）

    #[ORM\Column(type: 'string', length: 32, nullable: true)]
    private ?string $senderCounty = null;    // 寄件人区县（非必传）

    #[ORM\Column(type: 'string', length: 32, nullable: true)]
    private ?string $receiverProvince = null; // 收件人省份（非必传）

    #[ORM\Column(type: 'string', length: 32, nullable: true)]
    private ?string $receiverCity = null;     // 收件人城市（非必传）

    #[ORM\Column(type: 'string', length: 32, nullable: true)]
    private ?string $receiverCounty = null;   // 收件人区县（非必传）

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $pickupStartTime = null; // 期望取件开始时间（非必传）

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $pickupEndTime = null;   // 期望取件结束时间（非必传）

    #[ORM\Column(type: 'string', length: 32, nullable: true)]
    private ?string $pickUpCode = null;   // 取件单号

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

    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): self
    {
        $this->valid = $valid;

        return $this;
    }

    // Getters and Setters
    public function getConfig(): ?JdlConfig
    {
        return $this->config;
    }

    public function setConfig(?JdlConfig $config): self
    {
        $this->config = $config;

        return $this;
    }

    public function getSenderName(): string
    {
        return $this->senderName;
    }

    public function setSenderName(string $senderName): self
    {
        $this->senderName = $senderName;

        return $this;
    }

    public function getSenderAddress(): string
    {
        return $this->senderAddress;
    }

    public function setSenderAddress(string $senderAddress): self
    {
        $this->senderAddress = $senderAddress;

        return $this;
    }

    public function getSenderPostcode(): ?string
    {
        return $this->senderPostcode;
    }

    public function setSenderPostcode(?string $senderPostcode): self
    {
        $this->senderPostcode = $senderPostcode;

        return $this;
    }

    public function getReceiverName(): string
    {
        return $this->receiverName;
    }

    public function setReceiverName(string $receiverName): self
    {
        $this->receiverName = $receiverName;

        return $this;
    }

    public function getReceiverAddress(): string
    {
        return $this->receiverAddress;
    }

    public function setReceiverAddress(string $receiverAddress): self
    {
        $this->receiverAddress = $receiverAddress;

        return $this;
    }

    public function getReceiverPostcode(): ?string
    {
        return $this->receiverPostcode;
    }

    public function setReceiverPostcode(?string $receiverPostcode): self
    {
        $this->receiverPostcode = $receiverPostcode;

        return $this;
    }

    public function getWeight(): float
    {
        return $this->weight;
    }

    public function setWeight(float $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    public function getLength(): ?float
    {
        return $this->length;
    }

    public function setLength(?float $length): self
    {
        $this->length = $length;

        return $this;
    }

    public function getWidth(): ?float
    {
        return $this->width;
    }

    public function setWidth(?float $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function getHeight(): ?float
    {
        return $this->height;
    }

    public function setHeight(?float $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): self
    {
        $this->remark = $remark;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getSenderMobile(): string
    {
        return $this->senderMobile;
    }

    public function setSenderMobile(string $senderMobile): self
    {
        $this->senderMobile = $senderMobile;

        return $this;
    }

    public function getReceiverMobile(): string
    {
        return $this->receiverMobile;
    }

    public function setReceiverMobile(string $receiverMobile): self
    {
        $this->receiverMobile = $receiverMobile;

        return $this;
    }

    public function getPackageName(): ?string
    {
        return $this->packageName;
    }

    public function setPackageName(?string $packageName): self
    {
        $this->packageName = $packageName;

        return $this;
    }

    public function getPackageQuantity(): ?int
    {
        return $this->packageQuantity;
    }

    public function setPackageQuantity(?int $packageQuantity): self
    {
        $this->packageQuantity = $packageQuantity;

        return $this;
    }

    public function getSenderProvince(): ?string
    {
        return $this->senderProvince;
    }

    public function setSenderProvince(?string $senderProvince): self
    {
        $this->senderProvince = $senderProvince;

        return $this;
    }

    public function getSenderCity(): ?string
    {
        return $this->senderCity;
    }

    public function setSenderCity(?string $senderCity): self
    {
        $this->senderCity = $senderCity;

        return $this;
    }

    public function getSenderCounty(): ?string
    {
        return $this->senderCounty;
    }

    public function setSenderCounty(?string $senderCounty): self
    {
        $this->senderCounty = $senderCounty;

        return $this;
    }

    public function getReceiverProvince(): ?string
    {
        return $this->receiverProvince;
    }

    public function setReceiverProvince(?string $receiverProvince): self
    {
        $this->receiverProvince = $receiverProvince;

        return $this;
    }

    public function getReceiverCity(): ?string
    {
        return $this->receiverCity;
    }

    public function setReceiverCity(?string $receiverCity): self
    {
        $this->receiverCity = $receiverCity;

        return $this;
    }

    public function getReceiverCounty(): ?string
    {
        return $this->receiverCounty;
    }

    public function setReceiverCounty(?string $receiverCounty): self
    {
        $this->receiverCounty = $receiverCounty;

        return $this;
    }

    public function getPickupStartTime(): ?\DateTime
    {
        return $this->pickupStartTime;
    }

    public function setPickupStartTime(?\DateTime $pickupStartTime): self
    {
        $this->pickupStartTime = $pickupStartTime;

        return $this;
    }

    public function getPickupEndTime(): ?\DateTime
    {
        return $this->pickupEndTime;
    }

    public function setPickupEndTime(?\DateTime $pickupEndTime): self
    {
        $this->pickupEndTime = $pickupEndTime;

        return $this;
    }

    public function getPickUpCode(): ?string
    {
        return $this->pickUpCode;
    }

    public function setPickUpCode(?string $pickUpCode): self
    {
        $this->pickUpCode = $pickUpCode;

        return $this;
    }
}
