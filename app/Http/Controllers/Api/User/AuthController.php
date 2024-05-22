<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangeOnlineStatusRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\UpdateUserInfoRequest;
use App\Http\Requests\MakeUserAdminRequest;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use App\Http\Services\UserService;
use App\Http\Requests\User\AuthRequest;
use App\Http\Requests\User\RegisterRequest;

class AuthController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function login(AuthRequest $request)
    {   
        $data = $request->all();
        $user=User::where('email',$request->email)->first();
        if($user){
            if($user->guard=='admin'){
                $data['guard'] = 'admin';
            }else{
                $data['guard'] = 'user';
            }
        }else{
            $data['guard'] = 'user';
        }
        
        return $this->userService->login($data);
    }

    public function register(RegisterRequest $request)
    {
        $data = $request->all();
        $data['guard'] = 'user';
        $data['is_online'] = 1;
        return $this->userService->register($data);
    }


    public function userInfo()
    {
        return $this->success(data:auth()->user());
    }

    public function updateInfo(UpdateUserInfoRequest $request)
    {
        $this->userService->update($request->all(),auth()->user());
        return $this->success();
    }

    public function changeOnlineStatus(ChangeOnlineStatusRequest $request)
    {
        $this->userService->changeOnlineStatus($request->is_online,auth()->user());
        return $this->success();
    }

    

    public function changePassword(ChangePasswordRequest $request){
        return $this->userService->changePassword($request->all());
    }

    public function user_profile($id){
        $user=User::where('id',$id)->select('id','name','email','guard','is_online','profile_image')->first();
        return $this->success(data:$user);
    }

    public function make_user_admin(MakeUserAdminRequest $request){
        $user=User::find($request->user_id);
        if($user->guard=='user'){
            $user->guard='admin';
        }else{
            $user->guard='user';
        }
       
        $user->save();
        return $this->success('Request has been done successfully');
    }
    public function logout(Request $request)
    {
        $user = $request->user();
        $user->is_online=0;
        $user->FcmToken=null;
        $user->save();
        // Revoke all tokens for the user
        $user->tokens->each(function ($token) {
            $token->delete();
        });

        // Logout the user
        Auth::guard('web')->logout();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function all_notifications(){
       
        $all_notifications=Notification::where('user_id',auth('api')->user()->id)->orderBy('id','desc')->select('id','data','seen','type','created_at')->get()->map(function ($notify) {
            $notify->data= json_decode($notify->data,true);
        //     $notify->body=$notify->data['body'];
        //     $notify->title=$notify->data['title'];
        //    // $notify->sound='notification.wav';
        //     unset($notify->data);
            return $notify;
        });
       
        return $this->success(data:$all_notifications);
    }
    public function see_notification($id){
        Notification::where('id',$id)->update(['seen'=>1]);
        
        return $this->success('notification seen successfuly');
    }

    public function activation_user(Request $request){
        $user = $request->user();
        if($user->is_online==0){
            $user->is_online=1;
        }else{
            $user->is_online=0;
        }
        $user->save();
        return $this->success('you are not active now');
    }
}
