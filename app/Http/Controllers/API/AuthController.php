<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Auth;
use Validator;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
         ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()
            ->json(['data' => $user,'access_token' => $token, 'token_type' => 'Bearer', ]);
    }

    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password')))
        {
            return response()
                ->json(['message' => 'Unauthorized'], 401);
        }

        $user = User::where('email', $request['email'])->firstOrFail();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()
            ->json(['message' => 'Hi '.$user->name.', welcome to home','access_token' => $token, 'token_type' => 'Bearer', ]);
    }

    // method for user logout and delete token
    public function logout()
    {
        auth()->user()->tokens()->delete();

        return [
            'message' => 'You have successfully logged out and the token was successfully deleted'
        ];
    }

    // get all user
    public function getAllUser()
    {
        $user = User::all();
        // if user not found
        if ($user->isEmpty()) {
            return response()->json([
                'message' => 'user Kosong'
            ], 404);
        }
        return response()->json([
            'data' => $user
        ]);

    }

    // delete user
    public function deleteUser($id)
    {
        $user = User
            ::where('id', $id)
            ->delete();
        if ($user) {
            return response()->json([
                'success' => true,
                'message' => 'User Deleted',
                'data' => $user
            ],200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'User Not Found',
                'data' => ''
            ],404);
        }
    
    }
    // Update user by id
    public function update (Request $request)
    {
        $request->validate([
            'name' => 'required|string |max:255',
            'email' => 'required|string|max:255|unique:users,email,'. Auth::user()->id

            ]);
            if (Auth::check())
            {
                if ($request->input('password'))
                {
                    $hashed = Hash::make($request->input('password'));
                    $user = User::find(Auth::user() ->id);
                    $user->name = $request->input('name');
                    $user->email = $request->input('email');
                    $user->password = $hashed;
                    $user->update();
                }
                $user = User::find(Auth::user()->id);
                $user->name = $request->input('name');
                $user->email = $request->input('email');
                $user->update();


            }
            return response()->json([
                'massage' =>'update sukses'
            ]);
    }

}