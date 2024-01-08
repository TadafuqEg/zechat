<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\RequestCallRequest;
use App\Http\Requests\SendMessageRequest;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\SendFirebase;
class MessageController extends Controller
{
    use SendFirebase;
    public function getMessages($userId)
    {
        $user = auth('api')->user();
        $messages = Message::where(function($query) use($userId ,$user) {
            $query->where('sender_id',$user->id)->where('receiver_id',$userId);
        })->orWhere(function($query) use($userId ,$user) {
            $query->where('receiver_id',$user->id)->where('sender_id',$userId);
        })->orderBy('id','DESC')->paginate(20);
        return $this->successWithPagination(data:$messages);
    }

    public function sendMessage(SendMessageRequest $request)
    {
        $data = $request->validated();
        $user = auth('api')->user();
        $data['sender_id'] = $user->id;
        $message = Message::create($data);
        $message->receiver_id = (int)$message->receiver_id;

        $receiver = User::find($data['receiver_id']);
        $fcmToken = $receiver->FcmToken??'';
        $this->sendFirebaseNotification(title:'you have a new message  from '.$user->name,notificationBody:[
            'type' => 'new_message',
            'message' => $message->message,
            'sender_id' => $user->id,
            'sender_name' => $user->name,
            'sender_email' => $user->email,
            'receiver_id' => $receiver->id,
            'receiver_name' => $receiver->name,
            'receiver_email' => $receiver->email,
        ],token:$fcmToken,message:$message->message);

        return $this->success(data:$message);
    }

    public function requestCall(RequestCallRequest $request)
    {
        $sender = User::find($request->sender_id);
        $receiver = User::find($request->receiver_id);
        $this->sendFirebaseNotification(title:'you have a call from '.$sender->name,notificationBody:[
            'type' => 'call_request',
            'message' => 'you have a call from '.$sender->name,
            'sender_id' => $sender->id,
            'sender_name' => $sender->name,
            'sender_email' => $sender->email,
            'receiver_id' => $receiver->id,
            'receiver_name' => $receiver->name,
            'receiver_email' => $receiver->email,
        ],token:$receiver->FcmToken,message:'you have a call from '.$sender->name);
        return $this->success();
    }


    public function userChatsList()
    {
        $user = auth()->user();
        $userChats = User::whereHas('messages', function ($query) use ($user) {
            $query->where('sender_id', $user->id)->orWhere('receiver_id', $user->id);
        })->get();
        return $this->success(data:$userChats);
    }
}
