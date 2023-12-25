<?php

namespace App\Http\Controllers\Api\User;

use App\Models\Friend;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\AcceptOrRejectFriendRequestRequest;
use App\Http\Requests\SendFriendRequestRequest;
use App\Http\Resources\FriendRequestsReceivedListResource;
use App\Http\Resources\SentFriendRequestsListResource;
use Illuminate\Http\Request;
use App\Traits\SendFirebase;

class FriendController extends Controller
{
    use SendFirebase;

    public function search(Request $request)
    {
        $user = auth('api')->user();
        $friends = Friend::where('sender_id',$user->id)->orWhere('receiver_id',$user->id)->where('status','accepted')->get();
        $friendsIsArray = array_merge($friends->pluck('sender_id')->toArray(),$friends->pluck('receiver_id')->toArray());
        array_merge($friends->pluck('sender_id')->toArray(),$friends->pluck('receiver_id')->toArray());
        $users = User::where('guard','user')->where('id','<>',auth()->user()->id)->whereNotIn('id',$friendsIsArray)->when(isset($request->name_or_email) && $request->name_or_email !=null && $request->name_or_email != '' , function($query) use($request){
            $query->where('name','like','%'.$request->name_or_email.'%')->orWhere('email','like','%'.$request->name_or_email.'%');
        })->paginate(12);
        
        return $this->successWithPagination(data:$users);
    }
    public function unfriendsList()
    {
        $user = auth('api')->user();
        $friends = Friend::where('sender_id',$user->id)->orWhere('receiver_id',$user->id)->get();
        $friendsIsArray = array_merge($friends->pluck('sender_id')->toArray(),$friends->pluck('receiver_id')->toArray());
        $unfriendList = User::where('guard','user')->whereNotIn('id',$friendsIsArray)->paginate(12);
        return $this->successWithPagination(data:$unfriendList);
    }

    public function friendRequestsReceivedList()
    {
        $user = auth('api')->user();
        $friends = Friend::with('sender')->where('receiver_id',$user->id)->where('status','pending')->paginate(12);
        
        return $this->successWithPaginationResource(data:FriendRequestsReceivedListResource::collection($friends));
    }

    public function SentFriendRequestsList()
    {
        $user = auth('api')->user();
        $friends = Friend::with('receiver')->where('sender_id',$user->id)->where('status','pending')->paginate(12);
        
        return $this->successWithPaginationResource(data:SentFriendRequestsListResource::collection($friends));
    }
    
    public function friendsList()
    {
        $user = auth('api')->user();
        $friends = Friend::where('sender_id',$user->id)->orWhere('receiver_id',$user->id)->where('status','accepted')->get();
        $friendsIsArray = array_merge($friends->pluck('sender_id')->toArray(),$friends->pluck('receiver_id')->toArray());
        $unfriendList = User::where('guard','user')->whereIn('id',$friendsIsArray)->where('id','<>',$user->id)->paginate(12);
        return $this->successWithPagination(data:$unfriendList);
    }

    public function friendRequest(SendFriendRequestRequest $request)
    {
        $user = auth('api')->user();
        $data = $request->validated();
        $data['sender_id'] = $user->id;
        $data['status'] = 'pending';
        $checkIfIsset = Friend::where(function($query) use($data){
            $query->where('sender_id',$data['sender_id'])->where('receiver_id',$data['receiver_id']);
        })->orWhere(function($query) use($data){
            $query->where('sender_id',$data['receiver_id'])->where('receiver_id',$data['sender_id']);
        })->count();
        if($checkIfIsset > 0)
        {
            return $this->failure('There is a friend request already submitted',409);
        }
        Friend::create($data);
        $receiver = User::find($data['receiver_id']);
        $fcmToken = $receiver->FcmToken??'';
        $this->sendFirebaseNotification(title:'New friend request',notificationBody:[
            'type' => 'new_friend_request',
            'message' => 'you have a new friend request from '.$user->name,
            'sender_id' => $user->id,
            'sender_name' => $user->name,
            'sender_email' => $user->email,
            'receiver_id' => $receiver->id,
            'receiver_name' => $receiver->name,
            'receiver_email' => $receiver->email,
        ],token:$fcmToken);

        return $this->success('sent successfully');
    }

    public function AcceptOrRejectFriendRequest(AcceptOrRejectFriendRequestRequest $request,$id)
    {
        $user = auth('api')->user();
        $friend = Friend::where('id',$id)->where('receiver_id',$user->id)->firstOrFail();
        if($request->action == 'accept')
        {
            $friend->update(['status'=>'accepted']);
            $sender = User::find($friend->sender_id);
            $fcmToken = $sender->FcmToken??'';
            $this->sendFirebaseNotification(title:'Friend request accepted',notificationBody:[
                'type' => 'friend_request_accepted',
                'message' => $user->name.' accept your friend request',
                'sender_id' => $sender->id,
                'sender_name' => $sender->name,
                'receiver_id' => $user->id,
                'receiver_name' => $user->name,
            ],token:$fcmToken);
        }else{
            $friend->delete();
        }

        
        return $this->success('operation has been done successfully');
    }
}
