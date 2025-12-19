<?php

declare(strict_types=1);

namespace JingdongLdopBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use JingdongLdopBundle\Entity\JdlConfig;

final class JdlConfigFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $config = new JdlConfig();
        $config->setCustomerCode('TEST_CUSTOMER_001');
        $config->setAppKey('test_app_key_123456789012345');
        $config->setAppSecret('test_app_secret_123456789012345678901234567890123456789012');
        $config->setApiEndpoint('https://api.jdl.com');
        $config->setVersion('2.0');
        $config->setFormat('json');
        $config->setSignMethod('md5');
        $config->setRedirectUri('https://localhost/jingdong/callback');
        $config->setRemark('测试配置');
        $config->setValid(true);

        $manager->persist($config);
        $this->addReference(self::class . '_config_1', $config);

        $inactiveConfig = new JdlConfig();
        $inactiveConfig->setCustomerCode('TEST_CUSTOMER_002');
        $inactiveConfig->setAppKey('test_app_key_234567890123456');
        $inactiveConfig->setAppSecret('test_app_secret_234567890123456789012345678901234567890123');
        $inactiveConfig->setApiEndpoint('https://api.jdl.com');
        $inactiveConfig->setVersion('2.0');
        $inactiveConfig->setFormat('json');
        $inactiveConfig->setSignMethod('sha256');
        $inactiveConfig->setRedirectUri('https://localhost/jingdong/callback2');
        $inactiveConfig->setRemark('测试配置2');
        $inactiveConfig->setValid(false);

        $manager->persist($inactiveConfig);

        $manager->flush();
    }
}
