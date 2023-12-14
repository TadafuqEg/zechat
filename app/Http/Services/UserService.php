<?php
namespace App\Http\Services;

use App\Models\User;
use App\Enums\Pagination;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserService
{
    public function login($data){
        if (auth()->attempt(['email' => $data['email'], 'password' => $data['password'], 'guard' => $data['guard']])) {
            $user =  auth()->user();
            $user->access_token = $user->createToken('testing')->plainTextToken;
            return response(['success' => true,'data'=>$user,'message'=>'The operation has been done'],200);
        }else{
            if(User::where('email',$data['email'])->count() > 0){
                return response(['success' => false,'message' => 'Invalid password.'],401);
            }
            return response(['success' => false,'message' => 'Invalid Credentials.'],401);
        }
    }
    public function changePassword($data){
        $user = User::find(auth()->user()->id);
        $user->password = Hash::make($data['password']);
        $user->save();
        return response(['success' => true,'data'=>$user,'message'=>'The operation has been done'],200);
    }

    public function index(){
        $users = User::where('guard','user')->paginate(Pagination::PER_PAGE->value);
        return $users;
    }

    public function store($data){
        return User::create($data);
    }

    public function update($data,$user){
        if(!is_object($user)){
            $user = User::findOrFail($user);
        }
        $user->update($data);
        return $user;
    }

    public function info($id){
        return  User::where('guard','user')->findOrFail($id);
    }

    // public function generateUpdateCode($userId){
    //     $user = User::where('guard','user')->findOrFail($userId);
    //     $user->update(['update_code'=> substr(Str::uuid(), 0, 5)]);
    //     return $user;
    // }

}