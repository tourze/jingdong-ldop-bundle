<?php

namespace JingdongLdopBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;

class JingdongLdopBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            \HttpClientBundle\HttpClientBundle::class => ['all' => true],
        ];
    }
}
