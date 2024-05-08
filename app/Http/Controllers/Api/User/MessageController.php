<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\RequestCallRequest;
use App\Http\Requests\SendMessageRequest;
use App\Models\Message;
use Illuminate\Support\Facades\File;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Traits\SendFirebase;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Google\Cloud\Firestore\FieldPath;
use Google\Cloud\Firestore\FieldValue;
use App\Events\NotificationDevice;
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
        foreach ($messages->items() as $msg) {
            $msg->time = $msg->created_at->setTimezone('Africa/Cairo')->format('h:i A');
            if($msg->path!=null)
              $msg->path=url($msg->path);
            
        }
        return $this->successWithPagination(data:$messages);
    }
    protected function handleFileUpload($request, &$data, &$user)
    {
        $fields = ['image', 'video', 'sheet', 'voice'];
        foreach ($fields as $field) {
            if ($request->file($field)) {
                $directory = public_path($field . 's');
                if (!File::exists($directory)) {
                    File::makeDirectory($directory, 0755, true);
                }
    
                $invitation_code = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_'), 0, 12);
                $file = $request->file($field);
                $fileName = $user->id . '_' . $invitation_code . '_' . time() . '.' . $file->extension();
                $file->storeAs('public/' . $field . 's', $fileName);
                $path = '/storage/' . $field . 's/' . $fileName;
                $data['path'] = $path;
                $data['type'] = $field;
            }
        }
    }
    public function sendMessage(SendMessageRequest $request)
    {  
        $data = $request->validated();
        $user = auth('api')->user();
        $data['sender_id'] = $user->id;
    
        $this->handleFileUpload($request, $data,$user);

        $message = Message::create($data);
        $message->receiver_id = (int)$message->receiver_id;
    
        $receiver = User::find($data['receiver_id']);
        
    
        event(new NotificationDevice($user, $receiver, $message, "new_message", 'you have a new message from ' . $user->name));
    
        if ($message->path != null) {
            $message->path = url($message->path);
        }
        $message_1=Message::find($message->id);
        return $this->success(data: $message_1);
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
        // $userChats = User::whereHas('messages', function ($query) use ($user) {
        //     $query->where('sender_id', $user->id)->orWhere('receiver_id', $user->id);
        // })->get();
        $receiverIDs = Message::where('sender_id', $user->id)->pluck('receiver_id')->toArray();
        $senderIDs = Message::where('receiver_id', $user->id)->pluck('sender_id')->toArray();
        
        $mergedIDs = array_merge($receiverIDs, $senderIDs);
        
        // If you want to remove duplicates
        $uniqueIDs = array_unique($mergedIDs);
        $userChats=User::whereIn('id',$$uniqueIDs)->get();
        return $this->success(data:$userChats);
    }

    public function sendAdminMessage(Request $request)
    {  
        $data['message'] = $request->message;
        $user = auth('api')->user();
        $data['sender_id'] = $user->id;
        ini_set('post_max_size', '500M');
        ini_set('upload_max_filesize', '500M');
        ini_set('memory_limit', '1000M');
        set_time_limit(10000000);
        if($request->file('image')){
            $directory = public_path('images');

            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }
            $invitation_code = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_'), 0, 12);
            $image = $user->id.'_'.$invitation_code.''.time() . '.' . $request->image->extension();

            $request->image->move(public_path('images/'), $image);
            $path = ('/images/') . $image;
            $data['path'] = $path;
            $data['type']='image';
        }else if($request->file('video')){
            $directory = public_path('videos');

            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }
        
            $invitation_code = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_'), 0, 12);
            $video = $user->id . '_' . $invitation_code . '_' . time() . '.' . $request->video->extension();
        
            $request->video->move(public_path('videos/'), $video);
            $path = ('/videos/') . $video;
            $data['path'] = $path;
            $data['type'] = 'video';
        }else if($request->file('sheet')){
            $directory = public_path('files');

           if (!File::exists($directory)) {
               File::makeDirectory($directory, 0755, true);
           }
       
           $invitation_code = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_'), 0, 12);
           $file_1 = $user->id . '_' . $invitation_code . '_' . time() . '.' . $request->sheet->extension();
       
           $request->sheet->move(public_path('files/'), $file_1);
           $path = ('/files/') . $file_1;
           $data['path'] = $path;
           $data['type'] = 'file';
        }else if($request->file('voice')){
            $directory = public_path('voices');

           if (!File::exists($directory)) {
               File::makeDirectory($directory, 0755, true);
           }
       
           $invitation_code = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_'), 0, 12);
           $file_1 = $user->id . '_' . $invitation_code . '_' . time() . '.' . $request->voice->extension();
       
           $request->voice->move(public_path('voices/'), $file_1);
           $path = ('/voices/') . $file_1;
           $data['path'] = $path;
           $data['type'] = 'voice';
        }
        $data['location_link'] = $request->location_link;
        $all_users=User::where('id','!=',$user->id)->get();
        foreach($all_users as $client){
            $data['receiver_id']=$client->id;
            $message = Message::create($data);
            $message->receiver_id = (int)$message->receiver_id;
            
            //$receiver = User::find($data['receiver_id']);
            $fcmToken = $client->FcmToken??'';
            $this->sendFirebaseNotification(title:'you have a new message from '.$user->name,notificationBody:[
                'type' => 'new_message',
                'message' => $message->message,
                'sender_id' => $user->id,
                'sender_name' => $user->name,
                'sender_email' => $user->email,
                'receiver_id' => $client->id,
                'receiver_name' => $client->name,
                'receiver_email' => $client->email,
            ],token:$fcmToken,message:$message->message);
        }
        
        if($message->path!=null)
            $message->path=url($message->path);
        return $this->success(data:$message);
    }
}
