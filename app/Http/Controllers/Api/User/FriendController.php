<?php

namespace App\Http\Controllers\Api\User;

use App\Models\Friend;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Resources\FriendRequestsReceivedListResource;
use App\Http\Resources\SentFriendRequestsListResource;
use Illuminate\Http\Request;

class FriendController extends Controller
{
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
    
}
