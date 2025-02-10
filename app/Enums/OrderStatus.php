<?php

namespace App\Enums;

class OrderStatus
{
    const PENDING = 1;
    const RECIVED = 2;
    const CANCELED = 3;
    const AVAILABLEPRODUCT = 4;
    const UNKNOWN = 5; // You can add more statuses as needed

    public static function getStatus($value)
    {
        switch ($value) {
            case self::PENDING:
                return 'معلّق';
            case self::RECIVED:
                return 'تم التسليم';
            case self::CANCELED:
                return ' ملغي';
                case self::AVAILABLEPRODUCT:
                    return ' محجوز';
            default:
                return 'غير معروف';
        }
    }
}
