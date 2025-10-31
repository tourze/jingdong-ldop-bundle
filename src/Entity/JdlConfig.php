<?php

declare(strict_types=1);

namespace JingdongLdopBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JingdongLdopBundle\Repository\JdlConfigRepository;
use Symfony\Component\Validator\Constraints as Assert;
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
    #[Assert\Type(type: 'bool')]
    private ?bool $valid = false;

    #[ORM\Column(type: Types::STRING, length: 32, options: ['comment' => '商家编码'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 32)]
    private string $customerCode;    // 商家编码

    #[ORM\Column(type: Types::STRING, length: 32, options: ['comment' => '京东应用的AppKey'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 32)]
    private string $appKey;         // 应用Key

    #[ORM\Column(type: Types::STRING, length: 64, options: ['comment' => '京东应用的AppSecret'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 64)]
    private string $appSecret;      // 应用密钥

    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '京东物流API接口地址'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Assert\Url]
    private string $apiEndpoint = 'https://api.jdl.com';  // API接口地址

    #[ORM\Column(type: Types::STRING, length: 10, options: ['comment' => 'API版本号'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 10)]
    private string $version = '2.0';  // API版本号

    #[ORM\Column(type: Types::STRING, length: 10, options: ['comment' => 'API返回数据格式'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 10)]
    #[Assert\Choice(choices: ['json', 'xml'])]
    private string $format = 'json';  // 返回格式

    #[ORM\Column(type: Types::STRING, length: 10, options: ['comment' => 'API签名方法'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 10)]
    #[Assert\Choice(choices: ['md5', 'sha1', 'sha256'])]
    private string $signMethod = 'md5';  // 签名方法

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '配置备注信息'])]
    #[Assert\Length(max: 65535)]
    private ?string $remark = null;  // 备注信息

    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => 'OAuth2授权回调地址'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Assert\Url]
    private string $redirectUri;

    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): void
    {
        $this->valid = $valid;
    }

    public function getCustomerCode(): string
    {
        return $this->customerCode;
    }

    public function setCustomerCode(string $customerCode): void
    {
        $this->customerCode = $customerCode;
    }

    public function getAppKey(): string
    {
        return $this->appKey;
    }

    public function setAppKey(string $appKey): void
    {
        $this->appKey = $appKey;
    }

    public function getAppSecret(): string
    {
        return $this->appSecret;
    }

    public function setAppSecret(string $appSecret): void
    {
        $this->appSecret = $appSecret;
    }

    public function getApiEndpoint(): string
    {
        return $this->apiEndpoint;
    }

    public function setApiEndpoint(string $apiEndpoint): void
    {
        $this->apiEndpoint = $apiEndpoint;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function setFormat(string $format): void
    {
        $this->format = $format;
    }

    public function getSignMethod(): string
    {
        return $this->signMethod;
    }

    public function setSignMethod(string $signMethod): void
    {
        $this->signMethod = $signMethod;
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): void
    {
        $this->remark = $remark;
    }

    public function getRedirectUri(): string
    {
        return $this->redirectUri;
    }

    public function setRedirectUri(string $redirectUri): void
    {
        $this->redirectUri = $redirectUri;
    }

    public function __toString(): string
    {
        return $this->customerCode;
    }
}
