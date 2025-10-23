<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;

class UserProfileController extends Controller
{

    public function update(Request $request)
    {

        $request->validate([
            'avatar' => ['nullable','image','mimetypes:image/png,image/jpg,image/jpeg','max:500'],
            'name' => ['required','string','max:50'],
            'email' => ['required','email','max:100', Rule::unique(User::class)->ignore($request->user()->id)],
            'user_id' => ['required', 'string', 'max:50', 'unique:users,user_name,' . $request->user()->id],
        ]);



        $user = $request->user();


        if($request->hasFile('avatar'))
        {
            $avatar = $request->file('avatar')->store('Avatars','public');

            if($user->avatar && Storage::disk('public')->exists($user->avatar))
            {
                Storage::disk('public')->delete($user->avatar);
            }

            $user->avatar = $avatar;
        }


         $user->name = $request->name;
         $user->email = $request->email;
         $user->user_name = $request->user_id;

         if($request->filled('current_password'))
         {
             $request->validate([
                 'current_password' => ['required','current_password'],
                 'password' => ['required',Password::defaults(),'confirmed'],
             ]);

            $user->password = Hash::make($request->password);

         }

         $user->save();

         notyf()->addSuccess('Profile Updated Successfully.');

         return response(['message' => 'Profile Updated Successfully'], 200);



    }
}
