<?php

namespace JingdongLdopBundle\Enum;

enum JdPickupOrderStatus: string
{
    public const STATUS_CREATED = 'CREATED';       // 已创建

    public const STATUS_SUBMITTED = 'SUBMITTED';   // 已提交

    public const STATUS_UPDATED = 'UPDATED';       // 已修改

    public const STATUS_CANCELLED = 'CANCELLED';   // 已取消
}
