<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class TelegramAuthController extends Controller
{
    // دالة لاستقبال الويب هوك من تيليجرام
    public function handleWebhook(Request $request)
    {
        $data = $request->all();

        // التأكد من أن الرسالة تحتوي على جهة اتصال (Contact)
        if (isset($data['message']['contact'])) {
            $chatId = $data['message']['chat']['id'];
            $phoneNumber = $data['message']['contact']['phone_number'];
            $userIdFromContact = $data['message']['contact']['user_id'];

            // تنظيف الرقم السوري (قد يصل 963 أو +963)
            // تأكد من توحيد الصيغة
            if (!str_starts_with($phoneNumber, '+')) {
                $phoneNumber = '+' . $phoneNumber;
            }

            // البحث عن المستخدم وتحديث Chat ID
            $user = User::where('phone_number', $phoneNumber)->first();

            if ($user) {
                $user->telegram_chat_id = $chatId;
                $user->save();

                // إرسال رسالة ترحيب
                $this->sendMessage($chatId, "تم ربط حسابك بنجاح! يمكنك الآن استقبال رموز التحقق.");
            } else {
                 $this->sendMessage($chatId, "عذراً، هذا الرقم غير مسجل في نظامنا.");
            }
        }

        // التعامل مع أمر /start
        elseif (isset($data['message']['text']) && $data['message']['text'] == '/start') {
            $chatId = $data['message']['chat']['id'];
            // طلب مشاركة الرقم من المستخدم
            $this->requestContact($chatId);
        }

        return response('OK', 200);
    }

    // دالة مساعدة لطلب الرقم (زر خاص)
    private function requestContact($chatId)
    {
        $keyboard = [
            'keyboard' => [
                [
                    ['text' => 'مشاركة رقم هاتفي للتحقق', 'request_contact' => true]
                ]
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ];

        Http::post("https://api.telegram.org/bot".env('TELEGRAM_BOT_TOKEN')."/sendMessage", [
            'chat_id' => $chatId,
            'text' => 'مرحباً! يرجى الضغط على الزر أدناه لمشاركة رقمك السوري والتحقق من هويتك.',
            'reply_markup' => json_encode($keyboard)
        ]);
    }

    // دالة إرسال رسالة عادية
    private function sendMessage($chatId, $text)
    {
        Http::post("https://api.telegram.org/bot".env('TELEGRAM_BOT_TOKEN')."/sendMessage", [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML'
        ]);
    }
}
