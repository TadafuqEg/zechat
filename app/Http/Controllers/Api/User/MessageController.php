<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendMessageRequest;
use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
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
        $data['sender_id'] = auth('api')->user()->id;
        $message = Message::create($data);
        $message->receiver_id = (int)$message->receiver_id;
        return $this->success(data:$message);
    }
}
