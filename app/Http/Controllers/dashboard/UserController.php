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
use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\Auth\EmailExists;
use Kreait\Firebase\Exception\Auth\AuthError;
use GuzzleHttp\Client;
use File;

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
        return view('dashboard.users.create',compact('roles'));
    }
     
    public function store(Request $request)
    {
        // Validate request input
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|confirmed',
            'role' => ['required', Rule::in(Role::pluck('id')->toArray())],
        ]);
    
        // Find the role
        $role = Role::findOrFail($request->role);
    
        // Prepare data for the User model
        $userData = [
            'name' => $request->name,
            'email' => $request->email,
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
        $firebaseUser = $auth->createUserWithEmailAndPassword($request->email, $request->password);
        // Prepare data for Firebase Firestore
        $firebaseData = [
            'uid' => $firebaseUser->uid,
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password, // Note: Consider not storing plain passwords.
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

        return view('dashboard.users.edit',compact('roles','user'));
    }
    public function update(Request $request, User $user){
        $request->validate([
            'name' => 'required',
            'email' => 'required|unique:users,email,'.$user->id,
            
            'role' => ['required',Rule::in(Role::pluck('id'))],
        ]);
        $role = Role::find($request->role);
        $data['name']=$request->name;
        $data['email']=$request->email;
        
        $data['guard']=$role->name;
        $user->update($data);
        $user->syncRoles([$role->id]);
        return redirect()->route('users')
        ->with('success', 'User updated successfully.');
    }
    public function delete($id)
    {
        User::where('id', $id)->delete();
        return redirect('/users');
    }
}