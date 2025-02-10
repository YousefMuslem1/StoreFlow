<?php

use Telegram\Bot\Api;
// app/helpers.php

if (!function_exists('getStatusName')) {
    function getStatusName($statusNumber)
    {
        switch ($statusNumber) {
            case 1:
                return 'مباع مفرد';
            case 2:
                return 'مضاف لمنتج';
            case 3:
                return 'تدوير';
            case 4:
                return 'جديد خارجي';
            case 5:
                return 'تقصير';
            case 6:
                return 'تالف';
            case 7:
                return 'من كمية الى منتج';
            case 8:
                return 'تحويل داخلي';
            case 9:
                return 'شراء كمية';
                // Add more cases for other status numbers...
            default:
                return 'غير معروف';
        }
    }
}

if (!function_exists('sendTelegramMessage')) {
    function sendTelegramMessage($message)
    {
        $chatId = '-1002201106554';
        $telegram = new Api(env('TELEGRAM_BOT_TOKEN'));

        $telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $message
        ]);
    }
}
