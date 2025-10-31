<?php

declare(strict_types=1);

namespace JingdongLdopBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

enum JdPickupOrderStatus: string implements Itemable, Labelable, Selectable
{
    use ItemTrait;
    use SelectTrait;

    public const STATUS_CREATED = 'CREATED';       // 已创建

    public const STATUS_SUBMITTED = 'SUBMITTED';   // 已提交

    public const STATUS_UPDATED = 'UPDATED';       // 已修改

    public const STATUS_CANCELLED = 'CANCELLED';   // 已取消

    case CREATED = 'CREATED';
    case SUBMITTED = 'SUBMITTED';
    case UPDATED = 'UPDATED';
    case CANCELLED = 'CANCELLED';

    public function getLabel(): string
    {
        return match ($this) {
            self::CREATED => '已创建',
            self::SUBMITTED => '已提交',
            self::UPDATED => '已修改',
            self::CANCELLED => '已取消',
        };
    }
}
