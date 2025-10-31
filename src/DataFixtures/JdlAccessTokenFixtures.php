<?php

declare(strict_types=1);

namespace JingdongLdopBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use JingdongLdopBundle\Entity\JdlAccessToken;

class JdlAccessTokenFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $accessToken = new JdlAccessToken();
        $accessToken->setAccessToken('test_access_token_123456789012');
        $accessToken->setRefreshToken('test_refresh_token_12345678901');
        $accessToken->setScope('test_scope');
        $accessToken->setExpireTime(new \DateTimeImmutable('+1 hour'));

        $manager->persist($accessToken);

        $expiredToken = new JdlAccessToken();
        $expiredToken->setAccessToken('expired_access_token_123456789');
        $expiredToken->setRefreshToken('expired_refresh_token_12345678');
        $expiredToken->setScope('expired_scope');
        $expiredToken->setExpireTime(new \DateTimeImmutable('-1 hour'));

        $manager->persist($expiredToken);

        $manager->flush();
    }
}
