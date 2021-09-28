<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PasswordReset;

use App\Http\Requests\SendEmailRequest;

use Illuminate\Support\Facades\Validator as FacadesValidator;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;


use Validator;


class ForgotController extends Controller
{
    public static function forgot(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //if (! $token = auth()->attempt($validator->validated())) {
        //    return response()->json(['error' => 'Unauthorized'], 401);
        //}
        
        $user = User::where('email', $request->email)->first();

        if (!$user)
        {
            return response()->json(['status' => 401, 'message' => "we can't find a user with that email address."]);
        
        }

        $name = User::where('email',$request->email)->value('name');

        $passwordReset = PasswordReset::updateOrCreate(
                 ['email' => $user->email],
                 [
                     'email' => $user->email,
                     'token' => JWTAuth::fromUser($user)
                 ]
         );


        if ($user && $passwordReset) 
            {
                $sendMail = new SendEmailRequest();

                $sendMail->sendMail($name, $request->email, $passwordReset->token);
            }
        return response()->json(['status' => 200, 'message' => 'we have emailed your password reset link to respective mail']);
       
    }

    public function reset(Request $request)
    {
        $validate = FacadesValidator::make($request->all(), [
            'new_password' => 'min:6|required|',
            'confirm_password' => 'required|same:new_password'
        ]);

        if ($validate->fails())
        {
            return response()->json(
                ['status' => 201, 
                 'message' => "Password doesn't match"
                ]);
        }

        $passwordReset = PasswordReset::where([
            ['token', $request->token]
        ])->first();


        if (!$passwordReset) 
        {
            return response()->json(['status' => 401, 'message' => 'This token is invalid']);
        }

        $user = User::where('email', $passwordReset->email)->first();

        if (!$user)
        {
            return response()->json([
                'status' => 201, 
                'message' => "we can't find the user with that e-mail address"
            ], 201);
        }
        else
        {
            $user->password = bcrypt($request->new_password);
            $user->save();
            $passwordReset->delete();
            return response()->json([
                'status' => 201, 
                'message' => 'Password reset successfull!'
            ]);
        }
    }
}
?>
