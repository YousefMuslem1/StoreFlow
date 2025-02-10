<?php
namespace App\Enums;
class ProductStatus
{
    const SOLD = 1;
    const AVAILABLE = 2;
    const DAMAGED = 3;
    const UNKNOWN = 4; // You can add more statuses as needed
    const INSTALLSOLD = 5; // تقسيط
    const ORDER = 6; //توصاي
    const BOOKED = 7; // محجوز مع دفع قسم
    public static function getStatus($value)
    {
        switch ($value) {
            case self::SOLD:
                return 'مباع';
            case self::AVAILABLE:
                return 'متوفر';
            case self::ORDER:
                return 'توصاي';
            case self::BOOKED:
                return 'محجوز';
            default:
                return 'غير معروف';
        }
    }
}
