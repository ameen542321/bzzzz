<?php

namespace App\Http\Controllers\Auth;
use App\Notifications\ResetPasswordNotification;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    /**
     * عرض صفحة إعادة تعيين كلمة المرور
     */
    public function showResetForm(Request $request, string $token)
    {
        $email = $request->query('email');

        return view('auth.reset', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    /**
     * تنفيذ إعادة تعيين كلمة المرور
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        // إيجاد السجل في password_resets
        $record = DB::table('password_resets')
            ->where('email', $request->email)
            ->first();

        if (! $record) {
            return back()->withErrors(['email' => 'رابط إعادة التعيين غير صالح أو منتهي.']);
        }

        // التحقق من صلاحية الوقت (ساعة)
        if (Carbon::parse($record->created_at)->addHour()->isPast()) {
            return back()->withErrors(['email' => 'انتهت صلاحية رابط إعادة التعيين.']);
        }

        // تغيير كلمة المرور
        $user = User::where('email', $request->email)->firstOrFail();

        $user->password = Hash::make($request->password);
        $user->save();

        // حذف سجل reset
        DB::table('password_resets')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('status', 'تم تحديث كلمة المرور بنجاح، يمكنك تسجيل الدخول الآن.');
    }
}
