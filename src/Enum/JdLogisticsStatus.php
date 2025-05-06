<?php

namespace JingdongLdopBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

enum JdLogisticsStatus: string implements Labelable, Itemable, Selectable
{
    use ItemTrait;
    use SelectTrait;

    case STATUS_CREATED = 'CREATED';         // 已创建
    case STATUS_COLLECTED = 'COLLECTED';     // 已揽收
    case STATUS_IN_TRANSIT = 'IN_TRANSIT';   // 运输中
    case STATUS_DELIVERING = 'DELIVERING';   // 派送中
    case STATUS_DELIVERED = 'DELIVERED';     // 已签收
    case STATUS_REJECTED = 'REJECTED';       // 已拒收
    case STATUS_EXCEPTION = 'EXCEPTION';     // 异常

    public function getLabel(): string
    {
        return match ($this) {
            self::STATUS_CREATED => '已创建',
            self::STATUS_COLLECTED => '已揽收',
            self::STATUS_IN_TRANSIT => '运输中',
            self::STATUS_DELIVERING => '派送中',
            self::STATUS_DELIVERED => '已签收',
            self::STATUS_REJECTED => '已拒收',
            self::STATUS_EXCEPTION => '异常',
        };
    }
}
