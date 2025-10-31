<?php

declare(strict_types=1);

namespace JingdongLdopBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use JingdongLdopBundle\Entity\LogisticsDetail;
use JingdongLdopBundle\Enum\JdLogisticsStatus;

class LogisticsDetailFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $detail1 = new LogisticsDetail();
        $detail1->setWaybillCode('JDL202501270001');
        $detail1->setCustomerCode('TEST_CUSTOMER_001');
        $detail1->setOrderCode('ORDER202501270001');
        $detail1->setOperateTime(new \DateTimeImmutable('2025-01-27 10:00:00'));
        $detail1->setOperateRemark('包裹已从发货仓库发出');
        $detail1->setOperateSite('北京分拣中心');
        $detail1->setOperateType('发货');
        $detail1->setWaybillStatus(JdLogisticsStatus::STATUS_COLLECTED);
        $detail1->setOperateUser('操作员001');
        $detail1->setNextSite('北京转运中心');
        $detail1->setNextCity('北京');

        $manager->persist($detail1);

        $detail2 = new LogisticsDetail();
        $detail2->setWaybillCode('JDL202501270001');
        $detail2->setCustomerCode('TEST_CUSTOMER_001');
        $detail2->setOrderCode('ORDER202501270001');
        $detail2->setOperateTime(new \DateTimeImmutable('2025-01-27 12:30:00'));
        $detail2->setOperateRemark('包裹正在运输途中');
        $detail2->setOperateSite('北京转运中心');
        $detail2->setOperateType('运输');
        $detail2->setWaybillStatus(JdLogisticsStatus::STATUS_IN_TRANSIT);
        $detail2->setOperateUser('操作员002');
        $detail2->setNextSite('上海分拣中心');
        $detail2->setNextCity('上海');

        $manager->persist($detail2);

        $detail3 = new LogisticsDetail();
        $detail3->setWaybillCode('JDL202501270002');
        $detail3->setCustomerCode('TEST_CUSTOMER_001');
        $detail3->setOrderCode('ORDER202501270002');
        $detail3->setOperateTime(new \DateTimeImmutable('2025-01-27 09:00:00'));
        $detail3->setOperateRemark('包裹已成功签收');
        $detail3->setOperateSite('上海配送站');
        $detail3->setOperateType('签收');
        $detail3->setWaybillStatus(JdLogisticsStatus::STATUS_DELIVERED);
        $detail3->setOperateUser('收件人本人');

        $manager->persist($detail3);

        $manager->flush();
    }
}
