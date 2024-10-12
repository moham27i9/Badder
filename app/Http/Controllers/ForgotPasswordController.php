<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends Controller
{
    /*
     * إرسال رابط إعادة تعيين كلمة المرور.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        $this->validate($request, ['email' => 'required|email']);

        // إرسال رابط إعادة تعيين كلمة المرور
        $response = $this->broker()->sendResetLink(
            $request->only('email')
        );

        if ($response == Password::RESET_LINK_SENT) {
            return response()->json(['message' => 'تم إرسال رابط إعادة تعيين كلمة المرور بنجاح!'], 200);
        }

        return response()->json(['error' => 'حدث خطأ أثناء إرسال رابط إعادة تعيين كلمة المرور.'], 400);
    }

    /*
     * الحصول على وسيط إعادة تعيين كلمة المرور.
     *
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    public function broker()
    {
        return Password::broker();
    }
}
