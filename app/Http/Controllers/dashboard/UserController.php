<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Validation\Rule;
use Image;
use Str;
use DateTime;
use DateTimeZone;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\Auth\EmailExists;
use Kreait\Firebase\Exception\Auth\AuthError;
use GuzzleHttp\Client;
use File;
use App\Models\Section;
use Google\Cloud\Core\Timestamp;
class UserController extends Controller
{
    public function index(Request $request)
    {  

        if ($request->has('search')){

            $all_users = user::where('name', 'LIKE', '%' . $request->search . '%')->orWhere('email', 'LIKE', '%' . $request->search . '%')->paginate(10);
        }else{

            $all_users= user::orderBy('id','desc')->paginate(10);
        } 
        return view('dashboard.users.index',compact('all_users'));

    }

    public function create(){
        $roles=Role::all();
        $sections=Section::where('is_active',1)->get();
        return view('dashboard.users.create',compact('roles','sections'));
    }
     
    public function store(Request $request)
    {
        // Validate request input
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|confirmed',
            'role' => ['required', Rule::in(Role::pluck('id'))],
            'section' => ['required', Rule::in(Section::pluck('id'))],
        ]);
    
        // Find the role
        $role = Role::findOrFail($request->role);
    
        // Prepare data for the User model
        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'section_id' => $request->section,
            'password' => $request->password,
            'guard' => $role->name,
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://www.google.com");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        if ($output === FALSE) {
            echo "cURL Error: " . curl_error($ch);
        } else {
            echo "cURL Success";
        }
        curl_close($ch);
        // Initialize Firebase
        $factory = (new Factory)
            ->withServiceAccount(config_path('firebase-credentials.json'));
        $auth = $factory->createAuth();
       //dd($request->email,$request->password);
        // Create the Firebase Auth user
        $timestamp = new Timestamp(new \DateTime());
        $dateTime = $timestamp->get()->format('Y-m-d H:i:s');

        // Dump the DateTime object
        
        
        $firebaseUser = $auth->createUserWithEmailAndPassword($request->email, $request->password);
        // Prepare data for Firebase Firestore
        $firebaseData = [
            'userid' => $firebaseUser->uid,
            'name' => $request->name,
            'email' => $request->email,
            'created_at'=>$dateTime
             // Note: Consider not storing plain passwords.
        ];
        
        // Create the Firestore document
        $firestore = $factory->createFirestore()->database();
        
        $firestore->collection('users')->document($firebaseUser->uid)->set($firebaseData);
    
        // Create the user in the local database
        $userData['uid'] = $firebaseUser->uid;
        $user = User::create($userData);
    
        // Assign role to the user
        $user->assignRole($role->id);

        // Redirect with success message
        return redirect()->route('users')
            ->with('success', 'User created successfully.');
    }
    
    public function edit($id){
        $user = User::findOrFail($id);
        $user->roles;
        $roles=Role::all();
        $sections=Section::where('is_active',1)->get();
        return view('dashboard.users.edit',compact('roles','user','sections'));
    }
    public function update(Request $request, User $user){
        $request->validate([
            'name' => 'required',
            'email' => 'required|unique:users,email,'.$user->id,
            'section' => ['required', Rule::in(Section::pluck('id'))],
            'role' => ['required',Rule::in(Role::pluck('id'))],
        ]);
        $role = Role::find($request->role);
        $data['name']=$request->name;
        $data['email']=$request->email;
        $data['section_id']=$request->section;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://www.google.com");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        if ($output === FALSE) {
            echo "cURL Error: " . curl_error($ch);
        } else {
            echo "cURL Success";
        }
        curl_close($ch);
        // Initialize Firebase
        $factory = (new Factory)
            ->withServiceAccount(config_path('firebase-credentials.json'));
        $auth = $factory->createAuth();
        $firestore = $factory->createFirestore()->database();
        $firebaseUid = $user->uid;
        $auth->updateUser($firebaseUid, [
            
            'email' => $data['email'],
        ]);

        // Update user document in Firestore
        $firestore->collection('users')->document($firebaseUid)->set([
            'name' => $data['name'],
            'email' => $data['email'],
            'userid' => $user->uid,
            'created_at' => date('Y-m-d H:i:s',strtotime($user->created_at))
            
            
        ]);
        $data['guard']=$role->name;
        $user->update($data);
        $user->syncRoles([$role->id]);
        return redirect()->route('users')
        ->with('success', 'User updated successfully.');
    }
    public function delete($id)
    {   
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://www.google.com");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        if ($output === FALSE) {
            echo "cURL Error: " . curl_error($ch);
        } else {
            echo "cURL Success";
        }
        curl_close($ch);
        // Initialize Firebase
        $factory = (new Factory)
            ->withServiceAccount(config_path('firebase-credentials.json'));
        $auth = $factory->createAuth();
        $firestore = $factory->createFirestore()->database();
        $user = User::findOrFail($id);
        $firebaseUid = $user->uid;
        $auth->deleteUser($firebaseUid);
        $firestore->collection('users')->document($firebaseUid)->delete();
        $user->tokens->each(function ($token) {
            $token->delete();
        });
        User::where('id', $id)->delete();
        return redirect('/users');
    }
}