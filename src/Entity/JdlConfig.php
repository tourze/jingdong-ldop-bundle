<?php

namespace JingdongLdopBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JingdongLdopBundle\Repository\JdlConfigRepository;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;

#[ORM\Entity(repositoryClass: JdlConfigRepository::class)]
#[ORM\Table(name: 'jdl_config', options: ['comment' => '京东物流配置表'])]
#[ORM\UniqueConstraint(name: 'uniq_customer_code', columns: ['customer_code'])]
class JdlConfig implements \Stringable
{
    use TimestampableAware;
    use BlameableAware;
    use SnowflakeKeyAware;


    #[IndexColumn]
    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    private ?bool $valid = false;

    #[ORM\Column(type: Types::STRING, length: 32, options: ['comment' => '商家编码'])]
    private string $customerCode;    // 商家编码

    #[ORM\Column(type: Types::STRING, length: 32, options: ['comment' => '京东应用的AppKey'])]
    private string $appKey;         // 应用Key

    #[ORM\Column(type: Types::STRING, length: 64, options: ['comment' => '京东应用的AppSecret'])]
    private string $appSecret;      // 应用密钥

    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '京东物流API接口地址'])]
    private string $apiEndpoint = 'https://api.jdl.com';  // API接口地址

    #[ORM\Column(type: Types::STRING, length: 10, options: ['comment' => 'API版本号'])]
    private string $version = '2.0';  // API版本号

    #[ORM\Column(type: Types::STRING, length: 10, options: ['comment' => 'API返回数据格式'])]
    private string $format = 'json';  // 返回格式

    #[ORM\Column(type: Types::STRING, length: 10, options: ['comment' => 'API签名方法'])]
    private string $signMethod = 'md5';  // 签名方法

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '配置备注信息'])]
    private ?string $remark = null;  // 备注信息

    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => 'OAuth2授权回调地址'])]
    private string $redirectUri;



    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): self
    {
        $this->valid = $valid;

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

    public function getAppKey(): string
    {
        return $this->appKey;
    }

    public function setAppKey(string $appKey): self
    {
        $this->appKey = $appKey;

        return $this;
    }

    public function getAppSecret(): string
    {
        return $this->appSecret;
    }

    public function setAppSecret(string $appSecret): self
    {
        $this->appSecret = $appSecret;

        return $this;
    }

    public function getApiEndpoint(): string
    {
        return $this->apiEndpoint;
    }

    public function setApiEndpoint(string $apiEndpoint): self
    {
        $this->apiEndpoint = $apiEndpoint;

        return $this;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function setFormat(string $format): self
    {
        $this->format = $format;

        return $this;
    }

    public function getSignMethod(): string
    {
        return $this->signMethod;
    }

    public function setSignMethod(string $signMethod): self
    {
        $this->signMethod = $signMethod;

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

    public function getRedirectUri(): string
    {
        return $this->redirectUri;
    }

    public function setRedirectUri(string $redirectUri): self
    {
        $this->redirectUri = $redirectUri;

        return $this;
    }

    public function __toString(): string
    {
        return $this->customerCode;
    }
}
