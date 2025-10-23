<?php

namespace App\Http\Controllers\Auth;

use Closure;
use App\Models\User;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

use Illuminate\Http\RedirectResponse;
use Illuminate\Auth\Events\Registered;
use App\Providers\RouteServiceProvider;
use App\Rules\RecaptchaRule;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {


        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'g-recaptcha-response' =>['required', new RecaptchaRule()]
        ]);

        $user_name = $this->generateUniqueUsername($request->name);
        $random_id = $this->generateUniqueRandomID();
        $user = User::create([
            'name' => $request->name,
            'user_name' => $user_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'random_id' => $random_id,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }



    public function generateUniqueUsername($name)
    {
        do {

            $user_name = '@' . Str::slug($name) . '_' . rand(0, 9999);

            $exists = User::where('user_name', $user_name)->exists();

        } while ($exists);

        return $user_name;
    }

    public function generateUniqueRandomID()
    {
        do {
            $random_id = bin2hex(random_bytes(16));
        } while (User::where('random_id', $random_id)->exists());
        return $random_id;
    }
}
