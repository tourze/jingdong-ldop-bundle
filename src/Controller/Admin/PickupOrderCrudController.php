<?php

declare(strict_types=1);

namespace JingdongLdopBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use JingdongLdopBundle\Controller\Admin\JdlConfigCrudController;
use JingdongLdopBundle\Entity\JdlConfig;
use JingdongLdopBundle\Entity\PickupOrder;
use JingdongLdopBundle\Enum\JdPickupOrderStatus;

/**
 * 京东物流取件订单管理控制器
 *
 * 提供京东物流取件订单的完整 CRUD 管理功能，包括：
 * - 订单信息的创建、查看、编辑和删除
 * - 寄件人和收件人信息管理
 * - 包裹尺寸和重量信息配置
 * - 取件时间安排
 * - 订单状态跟踪
 */
#[AdminCrud(
    routePath: '/jingdong-ldop/pickup-order',
    routeName: 'jingdong_ldop_pickup_order'
)]
final class PickupOrderCrudController extends AbstractCrudController
{
    /**
     * 指定管理的实体类
     */
    public static function getEntityFqcn(): string
    {
        return PickupOrder::class;
    }

    /**
     * 配置 CRUD 控制器的基本设置
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('京东物流取件订单')
            ->setEntityLabelInPlural('京东物流取件订单')
            ->setPageTitle('index', '京东物流取件订单管理')
            ->setPageTitle('new', '创建京东物流取件订单')
            ->setPageTitle('edit', '编辑京东物流取件订单')
            ->setPageTitle('detail', '京东物流取件订单详情')
            ->setSearchFields(['senderName', 'senderMobile', 'receiverName', 'receiverMobile', 'pickUpCode'])
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->showEntityActionsInlined()
            ->setHelp('index', '管理京东物流取件订单，支持创建新订单、查看订单详情、修改订单信息和跟踪订单状态。')
            ->setHelp('new', '创建新的京东物流取件订单。请确保填写完整的寄件人和收件人信息，以及准确的包裹重量。')
            ->setHelp('edit', '修改京东物流取件订单信息。请注意：已提交的订单某些字段可能无法修改。')
        ;
    }

    /**
     * 配置字段显示
     */
    public function configureFields(string $pageName): iterable
    {
        // 基本信息字段
        yield BooleanField::new('valid', '是否有效')
            ->setHelp('控制此订单是否有效，无效订单将不会被处理')
            ->renderAsSwitch(false)
            ->hideOnIndex()
        ;

        yield AssociationField::new('config', '京东物流配置')
            ->setHelp('选择用于此订单的京东物流配置')
            ->setCrudController(JdlConfigCrudController::class)
            ->setRequired(true)
        ;

        // 订单状态
        yield ChoiceField::new('status', '订单状态')
            ->setChoices([
                '已创建' => JdPickupOrderStatus::STATUS_CREATED,
                '已提交' => JdPickupOrderStatus::STATUS_SUBMITTED,
                '已修改' => JdPickupOrderStatus::STATUS_UPDATED,
                '已取消' => JdPickupOrderStatus::STATUS_CANCELLED,
            ])
            ->setHelp('当前订单的处理状态')
            ->allowMultipleChoices(false)
            ->renderExpanded(false)
            ->renderAsBadges([
                JdPickupOrderStatus::STATUS_CREATED => 'secondary',
                JdPickupOrderStatus::STATUS_SUBMITTED => 'info',
                JdPickupOrderStatus::STATUS_UPDATED => 'warning',
                JdPickupOrderStatus::STATUS_CANCELLED => 'danger',
            ])
        ;

        yield TextField::new('pickUpCode', '取件单号')
            ->setHelp('京东物流系统生成的唯一取件单号')
            ->hideOnForm()
        ;

        // 寄件人信息
        yield TextField::new('senderName', '寄件人姓名')
            ->setHelp('寄件人的真实姓名，必须与身份证件一致')
            ->setRequired(true)
            ->setColumns(6)
        ;

        yield TelephoneField::new('senderMobile', '寄件人手机号')
            ->setHelp('寄件人手机号，格式：1xxxxxxxxx，用于取件联系')
            ->setRequired(true)
            ->setColumns(6)
        ;

        yield TextareaField::new('senderAddress', '寄件人地址')
            ->setHelp('寄件人详细地址，包括省市区和具体地址')
            ->setRequired(true)
            ->setNumOfRows(2)
            ->hideOnIndex()
        ;

        yield TextField::new('senderPostcode', '寄件人邮编')
            ->setHelp('寄件人所在地区邮编，6位数字')
            ->hideOnIndex()
            ->setColumns(6)
        ;

        yield TextField::new('senderProvince', '寄件人省份')
            ->hideOnIndex()
            ->setColumns(4)
        ;

        yield TextField::new('senderCity', '寄件人城市')
            ->hideOnIndex()
            ->setColumns(4)
        ;

        yield TextField::new('senderCounty', '寄件人区县')
            ->hideOnIndex()
            ->setColumns(4)
        ;

        // 收件人信息
        yield TextField::new('receiverName', '收件人姓名')
            ->setHelp('收件人的真实姓名')
            ->setRequired(true)
            ->setColumns(6)
        ;

        yield TelephoneField::new('receiverMobile', '收件人手机号')
            ->setHelp('收件人手机号，格式：1xxxxxxxxx，用于派件联系')
            ->setRequired(true)
            ->setColumns(6)
        ;

        yield TextareaField::new('receiverAddress', '收件人地址')
            ->setHelp('收件人详细地址，包括省市区和具体地址')
            ->setRequired(true)
            ->setNumOfRows(2)
            ->hideOnIndex()
        ;

        yield TextField::new('receiverPostcode', '收件人邮编')
            ->setHelp('收件人所在地区邮编，6位数字')
            ->hideOnIndex()
            ->setColumns(6)
        ;

        yield TextField::new('receiverProvince', '收件人省份')
            ->hideOnIndex()
            ->setColumns(4)
        ;

        yield TextField::new('receiverCity', '收件人城市')
            ->hideOnIndex()
            ->setColumns(4)
        ;

        yield TextField::new('receiverCounty', '收件人区县')
            ->hideOnIndex()
            ->setColumns(4)
        ;

        // 包裹信息
        yield NumberField::new('weight', '重量 (kg)')
            ->setHelp('包裹重量，单位：千克，必须大于0')
            ->setNumDecimals(2)
            ->setRequired(true)
            ->setColumns(6)
        ;

        yield NumberField::new('length', '长度 (cm)')
            ->setHelp('包裹长度，单位：厘米，可选')
            ->setNumDecimals(2)
            ->setColumns(4)
            ->hideOnIndex()
        ;

        yield NumberField::new('width', '宽度 (cm)')
            ->setHelp('包裹宽度，单位：厘米，可选')
            ->setNumDecimals(2)
            ->setColumns(4)
            ->hideOnIndex()
        ;

        yield NumberField::new('height', '高度 (cm)')
            ->setHelp('包裹高度，单位：厘米，可选')
            ->setNumDecimals(2)
            ->setColumns(4)
            ->hideOnIndex()
        ;

        yield TextField::new('packageName', '包裹名称')
            ->setHelp('包裹内容的简要描述，如"文件"、"服装"等')
            ->hideOnIndex()
            ->setColumns(6)
        ;

        yield IntegerField::new('packageQuantity', '包裹数量')
            ->setHelp('包裹件数，可选')
            ->hideOnIndex()
            ->setColumns(6)
        ;

        // 取件时间
        yield DateTimeField::new('pickupStartTime', '期望取件开始时间')
            ->setHelp('您希望快递员开始取件的时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->hideOnIndex()
        ;

        yield DateTimeField::new('pickupEndTime', '期望取件结束时间')
            ->setHelp('您希望快递员完成取件的最晚时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->hideOnIndex()
        ;

        // 备注信息
        yield TextareaField::new('remark', '备注信息')
            ->setHelp('其他需要说明的信息，如特殊取件要求等')
            ->setNumOfRows(3)
            ->hideOnIndex()
        ;

        // 系统字段
        yield TextField::new('createdBy.username', '创建人')
            ->hideOnForm()
            ->hideOnIndex()
        ;

        yield DateTimeField::new('createTime', '创建时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->hideOnForm()
        ;

        yield TextField::new('updatedBy.username', '更新人')
            ->hideOnForm()
            ->hideOnIndex()
        ;

        yield DateTimeField::new('updateTime', '更新时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->hideOnForm()
            ->hideOnIndex()
        ;
    }

    /**
     * 配置操作按钮
     */
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
        ;
    }

    /**
     * 配置过滤器
     */
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(BooleanFilter::new('valid', '是否有效'))
            ->add(EntityFilter::new('config', '京东物流配置'))
            ->add(ChoiceFilter::new('status', '订单状态')->setChoices([
                '已创建' => JdPickupOrderStatus::STATUS_CREATED,
                '已提交' => JdPickupOrderStatus::STATUS_SUBMITTED,
                '已修改' => JdPickupOrderStatus::STATUS_UPDATED,
                '已取消' => JdPickupOrderStatus::STATUS_CANCELLED,
            ]))
            ->add(TextFilter::new('senderName', '寄件人姓名'))
            ->add(TextFilter::new('senderMobile', '寄件人手机号'))
            ->add(TextFilter::new('receiverName', '收件人姓名'))
            ->add(TextFilter::new('receiverMobile', '收件人手机号'))
            ->add(TextFilter::new('pickUpCode', '取件单号'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
            ->add(DateTimeFilter::new('pickupStartTime', '取件开始时间'))
        ;
    }
}
