<?php

declare(strict_types=1);

namespace JingdongLdopBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use JingdongLdopBundle\Entity\JdlConfig;
use JingdongLdopBundle\Entity\PickupOrder;
use JingdongLdopBundle\Enum\JdPickupOrderStatus;

final class PickupOrderFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $config = $this->getReference(JdlConfigFixtures::class . '_config_1', JdlConfig::class);

        $order1 = new PickupOrder();
        $order1->setConfig($config);
        $order1->setSenderName('张三');
        $order1->setSenderMobile('13800138001');
        $order1->setSenderAddress('北京市朝阳区建国路123号');
        $order1->setSenderPostcode('100025');
        $order1->setReceiverName('李四');
        $order1->setReceiverMobile('13800138002');
        $order1->setReceiverAddress('上海市浦东新区世纪大道456号');
        $order1->setReceiverPostcode('200120');
        $order1->setWeight(1.5);
        $order1->setLength(30.0);
        $order1->setWidth(20.0);
        $order1->setHeight(10.0);
        $order1->setRemark('测试订单1');
        $order1->setStatus(JdPickupOrderStatus::STATUS_CREATED);
        $order1->setPackageName('数码产品');
        $order1->setPackageQuantity(1);
        $order1->setSenderProvince('北京市');
        $order1->setSenderCity('北京市');
        $order1->setSenderCounty('朝阳区');
        $order1->setReceiverProvince('上海市');
        $order1->setReceiverCity('上海市');
        $order1->setReceiverCounty('浦东新区');
        $order1->setPickupStartTime(new \DateTimeImmutable('+1 day 09:00:00'));
        $order1->setPickupEndTime(new \DateTimeImmutable('+1 day 18:00:00'));
        $order1->setPickUpCode('JDL202501270001');
        $order1->setValid(true);

        $manager->persist($order1);

        $order2 = new PickupOrder();
        $order2->setConfig($config);
        $order2->setSenderName('王五');
        $order2->setSenderMobile('13800138003');
        $order2->setSenderAddress('广州市天河区天河路789号');
        $order2->setReceiverName('赵六');
        $order2->setReceiverMobile('13800138004');
        $order2->setReceiverAddress('深圳市南山区科技园南路321号');
        $order2->setWeight(2.0);
        $order2->setRemark('测试订单2');
        $order2->setStatus(JdPickupOrderStatus::STATUS_SUBMITTED);
        $order2->setPackageName('服装');
        $order2->setPackageQuantity(2);
        $order2->setSenderProvince('广东省');
        $order2->setSenderCity('广州市');
        $order2->setSenderCounty('天河区');
        $order2->setReceiverProvince('广东省');
        $order2->setReceiverCity('深圳市');
        $order2->setReceiverCounty('南山区');
        $order2->setPickUpCode('JDL202501270002');
        $order2->setValid(true);

        $manager->persist($order2);

        $cancelledOrder = new PickupOrder();
        $cancelledOrder->setConfig($config);
        $cancelledOrder->setSenderName('陈七');
        $cancelledOrder->setSenderMobile('13800138005');
        $cancelledOrder->setSenderAddress('成都市锦江区春熙路111号');
        $cancelledOrder->setReceiverName('周八');
        $cancelledOrder->setReceiverMobile('13800138006');
        $cancelledOrder->setReceiverAddress('重庆市渝中区解放碑222号');
        $cancelledOrder->setWeight(0.8);
        $cancelledOrder->setRemark('测试取消订单');
        $cancelledOrder->setStatus(JdPickupOrderStatus::STATUS_CANCELLED);
        $cancelledOrder->setPackageName('文件');
        $cancelledOrder->setPackageQuantity(1);
        $cancelledOrder->setSenderProvince('四川省');
        $cancelledOrder->setSenderCity('成都市');
        $cancelledOrder->setSenderCounty('锦江区');
        $cancelledOrder->setReceiverProvince('重庆市');
        $cancelledOrder->setReceiverCity('重庆市');
        $cancelledOrder->setReceiverCounty('渝中区');
        $cancelledOrder->setValid(false);

        $manager->persist($cancelledOrder);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            JdlConfigFixtures::class,
        ];
    }
}
