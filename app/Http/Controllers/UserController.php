<?php

namespace App\Http\Controllers;

use App\Helpers\FileManager;
use App\Models\User;
use Illuminate\Http\Request;
use App\Enums\StorageFolder;
use App\Http\Requests\ProfilePictureRequest;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view("user.index", ['users'=> $users]);
    }

    public function uploadProfilePic(ProfilePictureRequest $request)
    {
        try {
            $user = Auth::user();
            if($user == null) throw new Exception("User not found.");

            $profilePicPath = FileManager::uploadFile($request->file('profile_picture'), StorageFolder::PROFILE_PICTURES);
            if($profilePicPath == null){
                return response()->json([
                    'success' => false,
                    'message' => 'Can\'t save file.',
                    'error' => 'Can\'t save file.',
                ]);
            }

            if($user->profile_picture){
                FileManager::deleteFile($user->profile_picture,  StorageFolder::PROFILE_PICTURES);
            }
            $parts = explode('/', $profilePicPath);
            $user->profile_picture = end($parts);
            $user->save();

            $user->path = StorageFolder::PROFILE_PICTURES->publicPath();

            return response()->json([
                'success' => true,
                'message' => 'Profile picture updated successfully',
                'data' => $user,
            ]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function getUserProfile(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user(),
        ]);
    }

    public function deleteProfilePic(Request $request){
        try {
            $profilePic = FileManager::deleteFile($request->profile_picture,  StorageFolder::PROFILE_PICTURES);
        } catch (\Throwable $th) {
            // dd($th);
            return response()->json(['error'=>$th->getMessage()]);
        }

        return response()->json(['profilePic'=>$profilePic]);
    }

    public function getUrlProfilePic(Request $request){
        try {
            $profilePic = FileManager::getFileUrlFromName($request->profile_picture,  StorageFolder::PROFILE_PICTURES);
        } catch (\Throwable $th) {
            // dd($th);
            return response()->json(['error'=>$th->getMessage()]);
        }

        return response()->json(['profilePic'=>$profilePic]);
    }

    public function updateUserProfile(Request $request)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $request->user()->id,
        ]);

        $user = $request->user();
        $user->update($request->only('name', 'email'));

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data' => $user,
        ]);
    }

    public function requestPasswordReset(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['success' => true, 'message' => __($status)])
            : response()->json(['success' => false, 'message' => __($status)]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();

                $user->tokens()->delete(); // Invalidate all tokens
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['success' => true, 'message' => __($status)])
            : response()->json(['success' => false, 'message' => __($status)]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|confirmed|min:8',
        ]);

        if (!Hash::check($request->current_password, $request->user()->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect.',
            ]);
        }

        $request->user()->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully.',
        ]);
    }
}
