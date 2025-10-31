<?php

declare(strict_types=1);

namespace JingdongLdopBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use JingdongLdopBundle\Entity\JdlAccessToken;

/**
 * 京东物流访问令牌管理控制器
 */
#[AdminCrud(
    routePath: '/jingdong-ldop/access-token',
    routeName: 'jingdong_ldop_access_token'
)]
final class JdlAccessTokenCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return JdlAccessToken::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('京东物流访问令牌')
            ->setEntityLabelInPlural('京东物流访问令牌管理')
            ->setPageTitle(Crud::PAGE_INDEX, '京东物流访问令牌列表')
            ->setPageTitle(Crud::PAGE_NEW, '新建访问令牌')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑访问令牌')
            ->setPageTitle(Crud::PAGE_DETAIL, '访问令牌详情')
            ->setHelp(Crud::PAGE_INDEX, '管理京东物流API访问令牌，包括access_token、refresh_token和过期时间等信息')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setSearchFields(['accessToken', 'refreshToken', 'scope'])
            ->showEntityActionsInlined()
            ->setPaginatorPageSize(20)
            ->setFormThemes(['@EasyAdmin/crud/form_theme.html.twig'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->hideOnForm()
            ->setColumns('col-md-2')
        ;

        yield TextField::new('accessToken', '访问令牌')
            ->setColumns('col-md-6')
            ->setRequired(true)
            ->setMaxLength(32)
            ->setHelp('京东物流API的访问令牌')
            ->formatValue(fn (?string $value): string => $this->formatToken($value))
            ->hideOnIndex() // 敏感信息在列表页隐藏
        ;

        yield TextField::new('refreshToken', '刷新令牌')
            ->setColumns('col-md-6')
            ->setRequired(true)
            ->setMaxLength(32)
            ->setHelp('用于刷新访问令牌的refresh_token')
            ->formatValue(fn (?string $value): string => $this->formatToken($value))
            ->onlyOnDetail() // 只在详情页显示，确保安全性
        ;

        yield TextField::new('scope', '授权范围')
            ->setColumns('col-md-6')
            ->setMaxLength(64)
            ->setHelp('API访问权限范围，可为空')
        ;

        yield DateTimeField::new('expireTime', '过期时间')
            ->setColumns('col-md-6')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->setHelp('令牌的过期时间')
            ->formatValue(fn (?\DateTimeImmutable $value): string => $this->formatExpireTime($value))
        ;

        yield DateTimeField::new('createTime', '创建时间')
            ->hideOnForm()
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->setColumns('col-md-6')
        ;

        yield DateTimeField::new('updateTime', '更新时间')
            ->hideOnForm()
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->setColumns('col-md-6')
            ->onlyOnDetail()
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('accessToken', '访问令牌'))
            ->add(TextFilter::new('refreshToken', '刷新令牌'))
            ->add(TextFilter::new('scope', '授权范围'))
            ->add(DateTimeFilter::new('expireTime', '过期时间'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
            ->add(DateTimeFilter::new('updateTime', '更新时间'))
        ;
    }

    /**
     * 格式化令牌显示（敏感信息截断显示）
     */
    protected function formatToken(?string $token): string
    {
        if (null === $token || '' === $token) {
            return '';
        }

        // 令牌长度大于等于12个字符时，截断显示
        if (strlen($token) >= 12) {
            return substr($token, 0, 6) . '...' . substr($token, -4);
        }

        return $token;
    }

    /**
     * 格式化过期时间显示
     */
    protected function formatExpireTime(?\DateTimeImmutable $value): string
    {
        if (null === $value) {
            return '';
        }

        $now = new \DateTimeImmutable();
        $isExpired = $value < $now;
        $formatted = $value->format('Y-m-d H:i:s');

        return $isExpired ? "🔴 {$formatted} (已过期)" : "🟢 {$formatted}";
    }
}
