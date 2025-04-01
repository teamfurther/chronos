<?php

namespace Chronos\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('chronos::auth.profile');
    }

    public function update(Request $request)
    {
        // validate input
        $this->validate($request, [
            'email' => 'required|email|unique:users'
        ]);

        // update user
        Auth::user()->firstname = $request->has('firstname') ? $request->get('firstname') : '';
        Auth::user()->lastname = $request->has('lastname') ? $request->get('lastname') : '';
        Auth::user()->email = $request->get('email');
        Auth::user()->save();

        // redirect
        return redirect()->route('chronos.auth.profile')->with('alerts', [
            (object) [
                'type' => 'success',
                'title' => trans('chronos::alerts.Success.'),
                'message' => trans('chronos::alerts.Profile successfully updated.'),
            ]
        ]);
    }

    public function update_password(Request $request)
    {
        // validate input
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
            'password_confirmation' => 'required',
        ]);

        $validator->after(function($validator) use ($request) {
            $check = auth()->validate([
                'email'    => Auth::user()->email,
                'password' => $request->get('current_password')
            ]);

            if (!$check)
                $validator->errors()->add('current_password', trans('chronos::alerts.Your current password is incorrect, please try again.'));
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // update password
        Auth::user()->password = bcrypt($request->get('password'));
        Auth::user()->save();

        // redirect
        return redirect()->route('chronos.auth.profile')->with('alerts', [
            (object) [
                'type' => 'success',
                'title' => trans('chronos::alerts.Success.'),
                'message' => trans('chronos::alerts.Password successfully updated.'),
            ]
        ]);
    }

    public function update_picture(Request $request)
    {
        // make, crop and resize uploaded image
        $cropData = $request->input('cropData');
        $manager = new ImageManager(new Driver());
        $image = $manager->read($file);
        $image = Image::make($request->input('file'));
        $image->crop(round($cropData['width']), round($cropData['height']), round($cropData['x']), round($cropData['y']))
              ->resize(250, 250, function ($constraint) {
                  $constraint->aspectRatio();
              });

        // save uploaded image
        do {
            $filename = Str::random(12);
        } while (file_exists(public_path('/uploads/user-pictures/') . $filename . '.png'));
        if (!file_exists(public_path('/uploads/user-pictures')))
            mkdir(public_path('/uploads/user-pictures'), 0755, true);
        $image->save(public_path('/uploads/user-pictures/') . $filename . '.png', 80);

        // delete old profile picture
        if (Auth::user()->picture && file_exists(public_path('/uploads/user-pictures/') . Auth::user()->picture))
            unlink(public_path('/uploads/user-pictures/') . Auth::user()->picture);

        // update profile picture
        Auth::user()->picture = $filename . '.png';
        Auth::user()->save();

        // set response and flash messages
        $request->session()->flash('alerts', [
            (object) [
                'type' => 'success',
                'title' => trans('chronos::alerts.Success.'),
                'message' => trans('chronos::alerts.Profile picture successfully updated.'),
            ]
        ]);

        echo json_encode([
            'redirect' => route('chronos.auth.profile')
        ]);
    }
}
