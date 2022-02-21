<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateAccountRequest;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\ResponseAPI;
use Illuminate\Support\Str;
use \Milon\Barcode\DNS2D;

class AuthController extends Controller
{
    use ResponseAPI;

    public function createAccount(CreateAccountRequest $request)
    {
        // try {
        $profile_link = Str::lower(str_replace(' ', '', $request->name)) . '' . rand(1000, 9999);

        $qrcode = new DNS2D();
        $qrcode->setStorPath(public_path('images/user_profiles/qrcodes'));
        $qrcode->getBarcodePNGPath($profile_link, 'QRCODE');

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'user_name' => $request->user_name,
            'password' => bcrypt($request->password),
            'profile_link' => $profile_link,
            'qrcode' => 'images/user_profiles/qrcodes/' . $profile_link
        ]);

        $token = $user->createToken('token')->plainTextToken;

        return $this->successResponse(['token' => $token], 201);
        // } catch (\Exception $e) {
        //     return $this->errorResponse($e->getMessage(), $e->getCode());
        // }
    }

    public function login(LoginRequest $request)
    {
        try {
            if (\Auth::attempt(['user_name' => $request->user_name, 'password' => $request->password])) {
                $user = \Auth::user();
                $token = $user->createToken('token')->plainTextToken;

                return $this->successResponse(['token' => $token], 200);
            } else {
                return $this->errorResponse(['message' => 'Username or password is incorrect'], 403);
            }
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }
}
