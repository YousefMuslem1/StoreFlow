<?php
namespace App\Enums;
class QuantitySelledTypes
{
    const SELLEDODD = 1; // مباع مفرد : تم اخذه من الكميات وبيعه بشكل منفصل
    const MARGED = 2; // تم أخذه من الكميات ودمجه مع قطعة داخلية في المحل --- تطويل قطعة
    const RECYCLED = 3; // ام أخذ وزن من الكمية الى المعمل اعادة تدوير
    const NEWQUANTITY = 4; // تم جلب بضاعة جديدة من خارج المحل
    const LOCALQUANTITY = 5;  //تم اضافة كمية جديدة من داخل المحل مثلا تم قص قطعة --تقصير قطعة
    const DAMAGEDPRODUCTTOQUANTITY = 6;
    const FROMOLDTONEW = 7; // اضافة منتج وزنه من كمية
    const TRANSFERED = 8;   // تحويل من كمية الى كمية
    const BUYQUANTITY = 9; // شراء خشر
} 
 