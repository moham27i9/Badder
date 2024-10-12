<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    // 1. تأكيد وجود المستخدم
    public function checkEmail(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        // إنشاء رمز إعادة تعيين كلمة المرور
        $token = Str::random(60);

        // حفظ رمز إعادة تعيين كلمة المرور في قاعدة البيانات
        $user->update([
            'password_reset_token' => $token,
            'password_reset_at' => now(),
        ]);

        // إرسال بريد إلكتروني إلى المستخدم
        Mail::to($user->email)->send(new PasswordResetEmail($user, $token));

        return response()->json(['message' => 'لقد تم إرسال رمز إعادة تعيين كلمة المرور إلى بريدك الإلكتروني.', 'success' => true]);
    }

    // 2. إعادة تعيين كلمة المرور
    public function resetPassword(Request $request)
    {
        $this->validate($request, [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $user = User::where('email', $request->email)
            ->where('password_reset_token', $request->token)
            ->where('password_reset_at', '>', now()->subMinutes(30))
            ->first();

        if (!$user) {
            return response()->json(['message' => 'رمز إعادة تعيين كلمة المرور غير صالح أو قد انتهى.', 'success' => false]);
        }

        // تغيير كلمة المرور
        $user->update([
            'password' => Hash::make($request->password),
            'password_reset_token' => null,
            'password_reset_at' => null,
        ]);

        return response()->json(['message' => 'لقد تم تغيير كلمة المرور بنجاح.', 'success' => true]);
    }
}
