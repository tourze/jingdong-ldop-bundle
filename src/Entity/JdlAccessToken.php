<?php

namespace JingdongLdopBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JingdongLdopBundle\Repository\JdlAccessTokenRepository;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;

#[ORM\Entity(repositoryClass: JdlAccessTokenRepository::class)]
#[ORM\Table(name: 'jdl_access_token', options: ['comment' => '京东物流访问令牌表'])]
class JdlAccessToken implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = null;

    #[ORM\Column(type: Types::STRING, length: 32, options: ['comment' => 'access_token'])]
    private string $accessToken;

    #[ORM\Column(type: Types::STRING, length: 32, options: ['comment' => 'refresh_token'])]
    private string $refreshToken;

    #[ORM\Column(type: Types::STRING, length: 64, nullable: true, options: ['comment' => 'scope范围'])]
    private ?string $scope = '';

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '过期时间'])]
    private ?\DateTimeImmutable $expireTime = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(string $refreshToken): self
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }

    public function setScope(?string $scope): self
    {
        $this->scope = $scope;

        return $this;
    }

    public function getExpireTime(): ?\DateTimeImmutable
    {
        return $this->expireTime;
    }

    public function setExpireTime(?\DateTimeImmutable $expireTime): static
    {
        $this->expireTime = $expireTime;

        return $this;
    }

    public function __toString(): string
    {
        return $this->accessToken;
    }
}
