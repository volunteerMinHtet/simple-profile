<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateAccountRequest;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\ResponseAPI;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use \Milon\Barcode\DNS2D;

class AuthController extends Controller
{
    use ResponseAPI;

    protected function checkAndCreateDir($path)
    {
        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0777, true, true);
        }
    }

    protected function generateQrCode($fileName, $data = null)
    {
        $path = public_path('images/user_profiles/qrcodes');

        $this->checkAndCreateDir($path);

        $qrcode = new DNS2D();
        $qrcode->setStorPath($path);
        $qrcode->getBarcodePNGPath($data, 'QRCODE');

        // return response()->json(['type' => gettype($qrcode), 200]);

        $checkFile = File::exists(public_path('images/user_profiles/qrcodes/simple-profileprofile' . $fileName . 'qrcode.png'));

        // $checkFile = Storage::disk('public')->exists('images/user_profiles/qrcodes/' . $data . 'qrcode.png');

        if ($checkFile) {
            // return public_path('images/user_profiles/qrcodes/' . $data . 'qrcode.png');
            return url('images/user_profiles/qrcodes/simple-profileprofile' . $fileName . 'qrcode.png');
        }

        return false;
    }

    public function createAccount(CreateAccountRequest $request)
    {
        try {
            $profileLink = Str::lower(str_replace(' ', '', $request->name)) . '' . rand(1000, 9999);

            // $downloadImageLink = url('images/user_profiles/qrcodes/' . $profileLink . '.qrcode.png');
            $data = url('simple-profile://profile/' . $profileLink);

            $qrcodeUrl = $this->generateQrCode($profileLink, $data);

            if ($qrcodeUrl) {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'user_name' => $request->user_name,
                    'password' => bcrypt($request->password),
                    'profile_link' => $profileLink,
                    'qrcode' => $qrcodeUrl
                ]);

                $token = $user->createToken('token')->plainTextToken;

                return $this->successResponse(['token' => $token], 201);
            }

            return $this->errorResponse('Failed to create account', 500);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
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

    public function getUserByProfileLink(Request $request, $profileLink)
    {
        $user = User::where('profile_link', '=', $profileLink)->first();

        if (!$user) {
            return $this->errorResponse(['message' => 'User not found'], 404);
        }

        return $this->successResponse($user, 200);
    }
}
