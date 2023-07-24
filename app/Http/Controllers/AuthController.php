<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    public function register(Request $request){

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'username' => 'required',
            'level' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        $newUser = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'level' => $request->level,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        if($newUser){
            return response()->json([
                'message' => 'Data berhasil disimpan',
                'Success' => true,
                'data' => $newUser
            ], 201);
        }

        return response()->json([
            'message' => 'Gagal tersimpan',
            'success' => false
        ], 400);
    }

    public function login(Request $request){

        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required|min:8'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        $credentials = $request->only('username', 'password');

        if(!$token = auth()->guard('api')->attempt($credentials)){
            return response()->json([
                'message' => 'Username atau Password Salah',
                'success' => 'false'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => auth()->guard('api')->user(),
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->guard('api')->factory()->getTTL() . ' minute'
        ]);
    }

    public function ganti_password(Request $request){

        $validator = Validator::make($request->all(), [
            'id_user' => 'required',
            'password' => 'required|min:8',
            'new_password' => 'required|min:8'
        ]);

        $getUser = User::findOrFail($request->id_user);

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        $credentials = [
            'username' => $getUser->username,
            'password' => $request->password
        ];

        if(!$token = auth()->guard('api')->attempt($credentials)){
            return response()->json([
                'message' => 'Password Salah',
                'success' => 'false'
            ], 400);
        }

        $id = auth()->guard('api')->user()->id;

        $user = User::findOrFail($id);

        $user->update([
            'password' => bcrypt($request->new_password)
        ]);

        return response()->json([
            'success' => true,
            'data' => auth()->guard('api')->user(),
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->guard('api')->factory()->getTTL() . ' minute'
        ]);
    }

    public function logout(){
        auth()->guard('api')->logout();

        return response()->json([
            'message' => 'Logout Sukses'
        ]);
    }

    public function users(Request $request){


        $User = User::latest()->get();

        if($User){
            return response()->json([
                'message' => 'Data Users',
                'Success' => true,
                'data' => $User
            ], 200);
        }

        return response()->json([
            'message' => 'Gagal tersimpan',
            'success' => false
        ], 400);
    }

    public function getUser(Request $request){

        $User = User::findOrFail($request->id_user);

        if($User){
            return response()->json([
                'data' => $User
            ], 200);
        }
    }

    public function update_users(Request $request, User $id){

        try {        

            $validator = Validator::make($request->all(), [
                'nama' =>'required',
                'username' => 'required',
                'email' => 'required|email',
                'level' => 'required'
            ]);

            if($validator->fails()){
                return response()->json($validator->errors(), 400);
            } else {

                $user = User::findOrFail($id->id);

                if($user){

                    $user->update([
                        'name' => $request->nama,
                        'username' => $request->username,
                        'email' => $request->email,
                        'level' => $request->level
                    ]);
                
                    return response()->json([
                        'message' => 'Data terupdate',
                        'success' => true,
                        'data' => $user
                    ], 200);

                } else {

                    return response()->json([
                        'message' => 'Data gagal terupdate',
                        'success' => false
                    ], 409);
                }                    

            }
        } catch (exception $e) {
            return response()->json($e);
        }

    }

    public function delete_users(Request $request, User $id){

        try {

            $user = User::findOrFail($id->id);

            if($user){

                $user->delete();
            
                return response()->json([
                    'message' => 'Data terhapus',
                    'success' => true
                ], 200);

            } else {

                return response()->json([
                    'message' => 'Data gagal terhapus',
                    'success' => false
                ], 409);
            }                    

        } catch (exception $e) {
            return response()->json($e);
        }

    }
    
}
