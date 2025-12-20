<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Requestt;
class AdminController extends Controller
{
    public function index()
    {
        $requests = Request::with('requestable')->get();
        return response()->json($requests);
    }

    // قبول الطلب (تغيير الحالة إلى approved)
    public function approveAppartment(Request $request,$id)
    {
        $request = Requestt::find($id);

        if (!$request) {
            return response()->json(['error' => 'الطلب غير موجود.'], 404);
        }

        $request->status = 'accepted';  // تأكد أنها نصية
        $request->save();

        // تفعيل العقار المرتبط عند الموافقة
        $appartment = $request->requestable;
        if ($appartment) {
            $appartment->update(['is_available' => true]); // تفعيل العقار
        }

        return response()->json(['message' => 'تم قبول الطلب وتفعيل العقار.']);
    }


    // رفض الطلب (تغيير الحالة إلى rejected)
    public function rejectAppartment(Request $request,$id)
    {
        $requestt = Requestt::findOrFail($id);
        $requestt->status = 'rejected';
        $requestt->save();

        return response()->json(['message' => 'تم رفض الطلب.']);
    }
}
