<?php


namespace Modules\User\Controllers;


use App\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Matrix\Exception;
use Modules\FrontendController;
use Illuminate\Validation\Rules\Password;
use Modules\User\Events\SendMailUserUpdatePassword;


class PasswordController extends FrontendController
{

    use ResetsPasswords;

    public function changePassword(Request $request)
    {
        $data = [
            'breadcrumbs' => [
                [
                    'name' => __('Setting'),
                    'url'  => route("user.profile.index")
                ],
                [
                    'name'  => __('Change Password'),
                    'class' => 'active'
                ]
            ],
            'page_title'  => __("Change Password"),
            'current_password' => $this->getDecryptedCurrentPassword(Auth::id()),
        ];

        return view('User::frontend.changePassword', $data);
    }

    private function getDecryptedCurrentPassword(?int $userId): ?string
    {
        if (!$userId) {
            return null;
        }

        $encrypted = User::query()
            ->whereKey($userId)
            ->value('current_password');

        if (empty($encrypted)) {
            return null;
        }

        try {
            return Crypt::decryptString($encrypted);
        } catch (DecryptException $e) {
            Log::warning('Failed to decrypt current_password', [
                'user_id' => $userId,
                'message' => $e->getMessage(),
                'length'  => strlen($encrypted),
            ]);

            return null;
        }
    }

    public function changePasswordUpdate(Request $request)
    {
        if(is_demo_mode()){
            return back()->with('error',"Demo mode: disabled");
        }
        $user = Auth::user();

        $request->validate([
            'current-password' => ['required', 'string'],
            'new-password'     => [
                'required',
                'string',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->uncompromised(),
                'confirmed',
            ],
        ]);

        if (!Hash::check($request->input('current-password'), $user->password)) {
            return redirect()->back()->with("error", __("Your current password does not matches with the password you provided. Please try again."));
        }

        if ($request->input('current-password') === $request->input('new-password')) {
            return redirect()->back()->with("error", __("New Password cannot be same as your current password. Please choose a different password."));
        }

        $this->setUserPassword($user, $request->input('new-password'));
        $user->save();
        event(new PasswordReset($user));

        try {
            event(new SendMailUserUpdatePassword($user, $request->input('new-password')));

        } catch (Exception $exception) {

            Log::warning("SendMailUserUpdatePassword: " . $exception->getMessage());
        }

        return redirect()->back()->with('success', __('Password changed successfully !'));
    }

}
