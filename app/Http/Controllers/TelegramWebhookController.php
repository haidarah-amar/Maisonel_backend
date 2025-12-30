<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // استقبال البيانات من تيليجرام
        $update = $request->all();

        // التأكد من وجود رسالة
        if (!isset($update['message'])) {
            return response()->json(['status' => 'ok']);
        }

        $message = $update['message'];
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? '';

        // الحالة الأولى: المستخدم ضغط Start
        if ($text === '/start') {
            $this->askForContact($chatId);
        }

        // الحالة الثانية: المستخدم أرسل جهة الاتصال (ضغط على الزر)
        elseif (isset($message['contact'])) {
            $phoneNumber = $message['contact']['phone_number'];
            $userId = $message['contact']['user_id']; // يجب أن يطابق chat_id

            // تنظيف رقم الهاتف (إزالة + إذا وجدت لتطابق قاعدة البيانات)
            // ملاحظة: تيليجرام يرسل الرقم مع مفتاح الدولة (مثلاً 9639...)

            $this->linkUser($chatId, $phoneNumber);
        }

        return response()->json(['status' => 'ok']);
    }

    // دالة لإرسال زر طلب الرقم
    private function askForContact($chatId)
    {
        $token = env('TELEGRAM_BOT_TOKEN');

        // تشكيل لوحة المفاتيح (زر خاص يطلب الرقم)
        $keyboard = [
            'keyboard' => [
                [
                    [
                        'text' => '📱 مشاركة رقم هاتفي للتفعيل',
                        'request_contact' => true // هذا هو السطر السحري
                    ]
                ]
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ];

        Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
            'chat_id' => $chatId,
            'text' => "أهلاً بك! لتفعيل حسابك واستلام رمز التحقق، يرجى الضغط على الزر أدناه لمشاركة رقم هاتفك الموثق.",
            'reply_markup' => json_encode($keyboard)
        ]);
    }

    // دالة ربط المستخدم في قاعدة البيانات
    private function linkUser($chatId, $phoneNumber)
    {
        // البحث عن المستخدم بواسطة رقم الهاتف
        // ملاحظة: تأكد من تنسيق الرقم في قاعدة بياناتك ليطابق ما يرسله تيليجرام
        // تيليجرام يرسل الرقم بدون + عادة، مثال: 966500000000

        // لنفترض أننا نبحث عن الرقم كما هو أو مع إضافة +
        $user = User::where('phone', $phoneNumber)
                    ->orWhere('phone', '+' . $phoneNumber)
                    ->first();

        if ($user) {
            $user->telegram_chat_id = $chatId;
            $user->save();

            $this->sendMessage($chatId, "✅ تم ربط حسابك بنجاح! يمكنك الآن استقبال رموز التحقق.");
        } else {
            $this->sendMessage($chatId, "❌ لم نجد حساباً مرتبطاً بهذا الرقم. يرجى التسجيل في الموقع أولاً.");
        }
    }

    private function sendMessage($chatId, $text)
    {
        $token = env('TELEGRAM_BOT_TOKEN');
        Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $text
        ]);
    }
}
