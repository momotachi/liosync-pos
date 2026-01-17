<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class ForgotPasswordController extends Controller
{
    /**
     * Show the forgot password form.
     */
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send password reset link.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => 'No account found with this email address.',
        ]);

        // We will send password reset link always (to prevent user enumeration)
        // But only process if email exists

        $user = User::where('email', $request->email)->first();

        if ($user) {
            // Generate token
            $token = Str::random(64);

            // Store token in password_resets table
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $request->email],
                [
                    'email' => $request->email,
                    'token' => Hash::make($token),
                    'created_at' => now(),
                ]
            );

            // Generate reset URL
            $resetUrl = route('password.reset', ['token' => $token, 'email' => $request->email]);

            // Send email (using simple mail for now)
            try {
                Mail::send([], [], function ($message) use ($user, $resetUrl) {
                    $message->to($user->email)
                        ->subject('Reset Password - Liosync POS')
                        ->html($this->getResetEmailHtml($user, $resetUrl));
                });
            } catch (\Exception $e) {
                // Log error but don't reveal to user
                logger()->error('Failed to send password reset email: ' . $e->getMessage());
            }
        }

        return back()->with('status', 'Password reset link has been sent to your email.');
    }

    /**
     * Show the password reset form.
     */
    public function showResetForm(Request $request)
    {
        $token = $request->query('token');
        $email = $request->query('email');

        return view('auth.reset-password', compact('token', 'email'));
    }

    /**
     * Reset the user's password.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Get the reset token record
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        // Check if token exists and is valid (within 1 hour)
        if (!$resetRecord ||
            !Hash::check($request->token, $resetRecord->token) ||
            Carbon::parse($resetRecord->created_at)->addHour()->isPast()) {

            return back()
                ->withInput()
                ->withErrors(['email' => 'Invalid or expired reset token.']);
        }

        // Find user and update password
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()
                ->withInput()
                ->withErrors(['email' => 'User not found.']);
        }

        $user->password = Hash::make($request->password);
        $user->setRememberToken(null);
        $user->save();

        // Delete the reset token
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        return redirect()->route('login')
            ->with('status', 'Your password has been reset successfully. You can now login with your new password.');
    }

    /**
     * Get the HTML for password reset email.
     */
    private function getResetEmailHtml($user, $resetUrl)
    {
        return "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                    .header h1 { color: white; margin: 0; font-size: 24px; }
                    .content { background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; }
                    .button { display: inline-block; padding: 12px 30px; background: #f97316; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                    .footer { margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center; color: #6b7280; font-size: 12px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Liosync POS</h1>
                    </div>
                    <div class='content'>
                        <h2>Password Reset Request</h2>
                        <p>Hi {$user->name},</p>
                        <p>We received a request to reset your password. Click the button below to reset your password:</p>
                        <center><a href='{$resetUrl}' class='button'>Reset Password</a></center>
                        <p>Or copy and paste this link into your browser:</p>
                        <p style='word-break: break-all; color: #f97316;'>{$resetUrl}</p>
                        <p><strong>This link will expire in 1 hour.</strong></p>
                        <p>If you did not request a password reset, please ignore this email.</p>
                    </div>
                    <div class='footer'>
                        <p>&copy; " . date('Y') . " Liosync POS. All rights reserved.</p>
                    </div>
                </div>
            </body>
            </html>
        ";
    }
}
