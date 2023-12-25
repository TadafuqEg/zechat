<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
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
        })->orderBy('id','DESC')->paginate(12);
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
}
