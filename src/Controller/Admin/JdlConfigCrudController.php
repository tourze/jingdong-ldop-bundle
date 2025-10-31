<?php

declare(strict_types=1);

namespace JingdongLdopBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use JingdongLdopBundle\Entity\JdlConfig;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

/**
 * 京东物流配置管理控制器
 *
 * 提供完整的 CRUD 功能来管理京东物流平台的API配置信息，
 * 包括商家编码、应用密钥、API端点等核心参数的配置管理。
 *
 * @author Linus Torvalds <linus@kernel.org>
 * @extends AbstractCrudController<JdlConfig>
 */
#[AdminCrud(
    routePath: '/jingdong-ldop/config',
    routeName: 'jingdong_ldop_config'
)]
final class JdlConfigCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return JdlConfig::class;
    }

    /**
     * 配置 CRUD 基本行为
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('京东物流配置')
            ->setEntityLabelInPlural('京东物流配置管理')
            ->setPageTitle('index', '京东物流配置列表')
            ->setPageTitle('new', '新增京东物流配置')
            ->setPageTitle('edit', '编辑京东物流配置')
            ->setPageTitle('detail', '京东物流配置详情')
            ->setHelp('index', '管理京东物流平台的API配置信息，包括商家编码、应用密钥、API端点等参数')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setSearchFields(['customerCode', 'appKey', 'apiEndpoint', 'remark'])
            ->setPaginatorPageSize(20)
            ->setEntityPermission('ROLE_ADMIN')
            ->showEntityActionsInlined()
        ;
    }

    /**
     * 配置字段显示和编辑
     */
    public function configureFields(string $pageName): iterable
    {
        // ID 字段 - 仅在列表和详情页显示
        yield IdField::new('id', 'ID')
            ->setMaxLength(19)
            ->hideOnForm()
            ->setHelp('系统自动生成的唯一标识符')
        ;

        // 暂时移除FormField分组，避免label为null的问题
        // 后续可以重新启用用于更好的表单布局
        /*
        yield FormField::addColumn(12)
            ->addCssClass('card card-primary')
            ->onlyOnForms()
        ;

        yield FormField::addFieldset('基础配置信息')
            ->setIcon('fa fa-cog')
            ->collapsible()
            ->onlyOnForms()
        ;
        */

        yield BooleanField::new('valid', '启用状态')
            ->setHelp('控制此配置是否生效，禁用后将不会被系统使用')
            ->renderAsSwitch(false)
        ;

        yield TextField::new('customerCode', '商家编码')
            ->setMaxLength(32)
            ->setRequired(true)
            ->setHelp('京东物流分配给商家的唯一编码标识')
            ->setColumns(6)
        ;

        // API认证信息分组
        /*
        yield FormField::addFieldset('API认证信息')
            ->setIcon('fa fa-key')
            ->collapsible()
            ->onlyOnForms()
        ;
        */

        yield TextField::new('appKey', '应用Key')
            ->setMaxLength(32)
            ->setRequired(true)
            ->setHelp('京东物流开放平台分配的应用标识')
            ->setColumns(6)
        ;

        yield TextField::new('appSecret', '应用密钥')
            ->setMaxLength(64)
            ->setRequired(true)
            ->setHelp('京东物流开放平台分配的应用密钥，请妥善保管')
            ->onlyOnForms()
            ->setFormType(PasswordType::class)
            ->setFormTypeOption('attr', [
                'autocomplete' => 'new-password',
                'placeholder' => '请输入应用密钥',
            ])
        ;

        // API接口配置分组
        /*
        yield FormField::addFieldset('API接口配置')
            ->setIcon('fa fa-globe')
            ->collapsible()
            ->onlyOnForms()
        ;
        */

        yield UrlField::new('apiEndpoint', 'API接口地址')
            ->setRequired(true)
            ->setHelp('京东物流API接口的基础地址，默认为生产环境地址')
            ->setColumns(8)
        ;

        yield TextField::new('version', 'API版本号')
            ->setMaxLength(10)
            ->setRequired(true)
            ->setHelp('使用的API版本，建议使用最新稳定版本')
            ->setColumns(4)
        ;

        yield ChoiceField::new('format', '数据格式')
            ->setChoices([
                'JSON格式' => 'json',
                'XML格式' => 'xml',
            ])
            ->setRequired(true)
            ->setHelp('API返回数据的格式类型')
            ->setColumns(6)
        ;

        yield ChoiceField::new('signMethod', '签名算法')
            ->setChoices([
                'MD5算法' => 'md5',
                'SHA1算法' => 'sha1',
                'SHA256算法' => 'sha256',
            ])
            ->setRequired(true)
            ->setHelp('API请求签名使用的哈希算法')
            ->setColumns(6)
        ;

        // OAuth2配置分组
        /*
        yield FormField::addFieldset('OAuth2授权配置')
            ->setIcon('fa fa-shield-alt')
            ->collapsible()
            ->onlyOnForms()
        ;
        */

        yield UrlField::new('redirectUri', '授权回调地址')
            ->setRequired(true)
            ->setHelp('OAuth2授权完成后的回调地址，必须与开放平台配置一致')
            ->setColumns(12)
        ;

        // 备注信息分组
        /*
        yield FormField::addFieldset('备注信息')
            ->setIcon('fa fa-comment')
            ->collapsible()
            ->onlyOnForms()
        ;
        */

        yield TextareaField::new('remark', '备注信息')
            ->setMaxLength(65535)
            ->setHelp('可填写配置的用途说明、注意事项等信息')
            ->hideOnIndex()
            ->setNumOfRows(3)
        ;

        // 系统字段分组 - 仅显示不可编辑
        /*
        yield FormField::addFieldset('系统信息')
            ->setIcon('fa fa-info-circle')
            ->collapsible()
            ->onlyOnForms()
        ;
        */

        yield TextField::new('createdBy', '创建人')
            ->hideOnForm()
            ->hideOnIndex()
            ->setHelp('创建此配置的用户')
        ;

        yield TextField::new('updatedBy', '更新人')
            ->hideOnForm()
            ->hideOnIndex()
            ->setHelp('最后修改此配置的用户')
        ;

        yield DateTimeField::new('createTime', '创建时间')
            ->hideOnForm()
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->setHelp('配置创建的时间')
        ;

        yield DateTimeField::new('updateTime', '更新时间')
            ->hideOnForm()
            ->hideOnIndex()
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->setHelp('配置最后更新的时间')
        ;
    }

    /**
     * 配置操作按钮
     */
    public function configureActions(Actions $actions): Actions
    {
        // 暂时移除自定义操作，避免路由不存在的问题
        // 后续在实际部署时可以重新启用，且需要先创建对应的路由
        /*
        // 创建测试连接操作
        $testConnection = Action::new('testConnection', '测试连接')
            ->linkToRoute('admin_jdl_config_test_connection', function (JdlConfig $entity) {
                return ['entityId' => $entity->getId()];
            })
            ->setCssClass('btn btn-info')
            ->setIcon('fa fa-plug')
            ->displayIf(function (JdlConfig $config) {
                return $config->isValid();
            })
        ;

        // 创建启用/禁用操作
        $toggleStatus = Action::new('toggleStatus', '切换状态')
            ->linkToRoute('admin_jdl_config_toggle_status', function (JdlConfig $entity) {
                return ['entityId' => $entity->getId()];
            })
            ->setCssClass('btn btn-secondary')
            ->setIcon('fa fa-power-off')
        ;
        */

        $actions = $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            // 暂时移除自定义操作
            // ->add(Crud::PAGE_INDEX, $testConnection)
            // ->add(Crud::PAGE_INDEX, $toggleStatus)
        ;

        // 安全地添加或更新 DETAIL 页面的 EDIT 动作
        // 根据 EasyAdmin 版本或配置，edit 动作可能已存在或不存在
        try {
            $actions = $actions->update(Crud::PAGE_DETAIL, Action::EDIT, function (Action $action) {
                return $action->setLabel('编辑配置');
            });
        } catch (\InvalidArgumentException) {
            // 如果 edit 动作不存在，则添加它
            $actions = $actions->add(Crud::PAGE_DETAIL, Action::EDIT);
        }

        return $actions
            // 暂时移除自定义操作
            // ->add(Crud::PAGE_DETAIL, $testConnection)
            // ->add(Crud::PAGE_DETAIL, $toggleStatus)
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->add(Crud::PAGE_EDIT, Action::DETAIL)
            ->add(Crud::PAGE_NEW, Action::INDEX)
            ->setPermission(Action::DELETE, 'ROLE_SUPER_ADMIN')
            ->setPermission(Action::NEW, 'ROLE_ADMIN')
            ->setPermission(Action::EDIT, 'ROLE_ADMIN')
        ;
    }

    /**
     * 配置过滤器
     */
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('customerCode', '商家编码'))
            ->add(TextFilter::new('appKey', '应用Key'))
            ->add(BooleanFilter::new('valid', '启用状态'))
            ->add(ChoiceFilter::new('format', '数据格式')
                ->setChoices([
                    'JSON格式' => 'json',
                    'XML格式' => 'xml',
                ])
            )
            ->add(ChoiceFilter::new('signMethod', '签名算法')
                ->setChoices([
                    'MD5算法' => 'md5',
                    'SHA1算法' => 'sha1',
                    'SHA256算法' => 'sha256',
                ])
            )
            ->add(DateTimeFilter::new('createTime', '创建时间'))
            ->add(DateTimeFilter::new('updateTime', '更新时间'))
        ;
    }
}
