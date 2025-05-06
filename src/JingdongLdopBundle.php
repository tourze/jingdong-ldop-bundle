<?php

namespace JingdongLdopBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\EasyAdmin\Attribute\Permission\AsPermission;

#[AsPermission(title: '京东物流')]
class JingdongLdopBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            \HttpClientBundle\HttpClientBundle::class => ['all' => true],
        ];
    }
}
