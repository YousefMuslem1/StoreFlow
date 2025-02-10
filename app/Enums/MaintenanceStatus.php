<?php

namespace App\Enums;

class MaintenanceStatus
{
    const PENDING = 1; // معلق مدفوع
    const RECIVED = 2; // تم التسليم وندفع اليوم
    const CANCELED = 3; // ملغي غير مدفوع
    const PENDINGNOTPAID = 4; //معلق غير مدفوع
    const CANCELEDPAID = 5; // ملغي مدفوع
    const RECIVEDPREPAID = 6; //تم التسليم ومدفوع من قبل

    public static function getStatus($value)
    { 
        switch ($value) {
            case self::PENDING:
                return 'معلّق مدفوع';
            case self::RECIVED:
                return 'تم التسليم';
            case self::CANCELED:
                return ' ملغي';
            case self::PENDINGNOTPAID:
                return ' معلّق غير مدفوع';
            case self::CANCELEDPAID:
                return ' ملغي مدفوع';
                case self::RECIVEDPREPAID:
                    return 'تم التسليم';
            default:
                return 'غير معروف';
        }
    }
}
