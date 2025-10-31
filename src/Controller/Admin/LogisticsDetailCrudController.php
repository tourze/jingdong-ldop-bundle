<?php

declare(strict_types=1);

namespace JingdongLdopBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use JingdongLdopBundle\Entity\LogisticsDetail;
use JingdongLdopBundle\Enum\JdLogisticsStatus;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\EasyAdminEnumFieldBundle\Field\EnumField;

/**
 * 京东物流详情管理控制器
 * 提供完整的CRUD功能来管理京东物流跟踪信息
 *
 * @extends AbstractCrudController<LogisticsDetail>
 */
#[AdminCrud(routePath: '/jingdong-ldop/logistics-detail', routeName: 'jingdong_ldop_logistics_detail')]
#[Autoconfigure(public: true)]
final class LogisticsDetailCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return LogisticsDetail::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('京东物流详情')
            ->setEntityLabelInPlural('京东物流详情管理')
            ->setPageTitle('index', '物流详情列表')
            ->setPageTitle('new', '新增物流详情')
            ->setPageTitle('edit', '编辑物流详情')
            ->setPageTitle('detail', '物流详情信息')
            ->setHelp('index', '管理京东物流跟踪信息，包括运单状态、操作记录等')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['waybillCode', 'orderCode', 'customerCode', 'operateRemark'])
            ->setPaginatorPageSize(25)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->setMaxLength(9999)
            ->hideOnForm()
            ->setHelp('物流详情记录唯一标识')
        ;

        yield TextField::new('waybillCode', '运单号')
            ->setMaxLength(32)
            ->setRequired(true)
            ->setHelp('京东物流运单号，用于追踪包裹')
            ->setColumns('col-md-4')
        ;

        yield TextField::new('customerCode', '商家编码')
            ->setMaxLength(32)
            ->setRequired(true)
            ->setHelp('京东平台分配的商家编码')
            ->setColumns('col-md-4')
        ;

        yield TextField::new('orderCode', '订单号')
            ->setMaxLength(32)
            ->setRequired(true)
            ->setHelp('关联的订单编号')
            ->setColumns('col-md-4')
        ;

        $enumField = EnumField::new('waybillStatus', '运单状态');
        $enumField->setEnumCases(JdLogisticsStatus::cases());
        yield $enumField
            ->setRequired(true)
            ->setHelp('当前运单的物流状态')
            ->setColumns('col-md-4')
            ->renderAsBadges([
                JdLogisticsStatus::STATUS_CREATED->value => 'secondary',
                JdLogisticsStatus::STATUS_COLLECTED->value => 'primary',
                JdLogisticsStatus::STATUS_IN_TRANSIT->value => 'info',
                JdLogisticsStatus::STATUS_DELIVERING->value => 'warning',
                JdLogisticsStatus::STATUS_DELIVERED->value => 'success',
                JdLogisticsStatus::STATUS_REJECTED->value => 'danger',
                JdLogisticsStatus::STATUS_EXCEPTION->value => 'danger',
            ])
        ;

        yield DateTimeField::new('operateTime', '操作时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->setRequired(true)
            ->setHelp('物流操作发生的时间')
            ->setColumns('col-md-4')
        ;

        yield TextField::new('operateType', '操作类型')
            ->setMaxLength(32)
            ->setRequired(true)
            ->setHelp('具体的物流操作类型')
            ->setColumns('col-md-4')
        ;

        yield TextField::new('operateSite', '操作网点')
            ->setMaxLength(32)
            ->setRequired(true)
            ->setHelp('执行操作的物流网点')
            ->setColumns('col-md-6')
        ;

        yield TextField::new('operateUser', '操作人员')
            ->setMaxLength(32)
            ->setRequired(false)
            ->setHelp('执行操作的工作人员（可选）')
            ->setColumns('col-md-6')
        ;

        yield TextareaField::new('operateRemark', '操作描述')
            ->setMaxLength(255)
            ->setNumOfRows(3)
            ->setRequired(true)
            ->setHelp('详细的操作描述和备注信息')
            ->setColumns('col-md-12')
        ;

        yield TextField::new('nextSite', '下一站网点')
            ->setMaxLength(32)
            ->setRequired(false)
            ->setHelp('包裹即将到达的下一个网点（可选）')
            ->setColumns('col-md-6')
            ->hideOnIndex()
        ;

        yield TextField::new('nextCity', '下一站城市')
            ->setMaxLength(32)
            ->setRequired(false)
            ->setHelp('包裹即将到达的下一个城市（可选）')
            ->setColumns('col-md-6')
            ->hideOnIndex()
        ;

        // 系统字段 - 只读
        yield TextField::new('createdBy', '创建人')
            ->hideOnForm()
            ->hideOnIndex()
            ->onlyOnDetail()
            ->setHelp('创建该记录的用户')
        ;

        yield TextField::new('updatedBy', '更新人')
            ->hideOnForm()
            ->hideOnIndex()
            ->onlyOnDetail()
            ->setHelp('最后更新该记录的用户')
        ;

        yield DateTimeField::new('createTime', '创建时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->hideOnForm()
            ->setHelp('记录创建时间')
        ;

        yield DateTimeField::new('updateTime', '更新时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->hideOnForm()
            ->setHelp('记录最后更新时间')
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('waybillCode', '运单号'))
            ->add(TextFilter::new('orderCode', '订单号'))
            ->add(TextFilter::new('customerCode', '商家编码'))
            ->add(ChoiceFilter::new('waybillStatus', '运单状态')
                ->setChoices([
                    '已创建' => JdLogisticsStatus::STATUS_CREATED->value,
                    '已揽收' => JdLogisticsStatus::STATUS_COLLECTED->value,
                    '运输中' => JdLogisticsStatus::STATUS_IN_TRANSIT->value,
                    '派送中' => JdLogisticsStatus::STATUS_DELIVERING->value,
                    '已签收' => JdLogisticsStatus::STATUS_DELIVERED->value,
                    '已拒收' => JdLogisticsStatus::STATUS_REJECTED->value,
                    '异常' => JdLogisticsStatus::STATUS_EXCEPTION->value,
                ])
            )
            ->add(TextFilter::new('operateType', '操作类型'))
            ->add(TextFilter::new('operateSite', '操作网点'))
            ->add(DateTimeFilter::new('operateTime', '操作时间'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
            ->add(TextFilter::new('createdBy', '创建人'))
        ;
    }
}
