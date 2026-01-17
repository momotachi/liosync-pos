<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Password Reset Controller - Contact Admin Only (No Email)
 */
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
     * Password reset via contacting admin - No email sent.
     */
    public function sendResetLinkEmail(Request $request)
    {
        // Just redirect back with info message - no email sent
        return back()->with('status', 'Please contact your administrator to reset your password.');
    }
}
