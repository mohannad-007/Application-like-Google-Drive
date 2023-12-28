<?php

namespace App\Http\Controllers;

use App\Aspects\Logger;
use App\Repository\GroupRepositoryInterface;
use App\Repository\UserRepositoryInterface;
use Exception;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Obiefy\API\Facades\API;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

#[Logger]
class UserController extends Controller
{
    protected $userRepository;
    protected $groupRepository;
    public function __construct(UserRepositoryInterface $userRepository,GroupRepositoryInterface $groupRepository)
    {
        $this->userRepository = $userRepository;
        $this->groupRepository = $groupRepository;
    }
    public function register(Request $request)
    {
            try
            {
            $data = $request->all();
            $rules = [
                'name' => 'required|regex:/^[a-zA-Z0-9_]+$/|between:3,25|unique:users|alpha_dash',
                'first_name' => 'required|regex:/^[a-zA-Z0-9_]+$/|between:3,25',
                'last_name' => 'required|regex:/^[a-zA-Z0-9_]+$/|between:3,25',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:8|confirmed',
                'role_id' => 'required'
            ];
            $validation = Validator::make($data, $rules);
            if ($validation->fails()) {
                return response()->json([
                    "messages" => $validation->errors()
                ], 422);
//                $errors = $validation->errors();
//                return api()->validation('This Fields are Required.' ,$errors);
            }
            $user = $this->userRepository->register($data);
            if ($user)
            {
               $token = $user->createToken('auth_token')->plainTextToken;
               $user['token']=$token;

                return response()->json([
                    'messages'=>'User has been Created',
                    'data'=>$user
                ]);
//                return api()->ok( 'User has been Created',$user);


            }
            else
            {
                return response()->json([
                    'messages'=>'the process has been failed!',

                ]);
//                return api()->error('the process has been failed!');
            }
            }
            catch (Exception $e)
                {
                    return response()->json([
                        'messages'=>'the process has been failed!',
                        'data'=>$e
                    ]);
//                    return api()->error('the process has been failed!',$e);
                }
    }
    #[Logger]
    public function login(Request $request)
    {
        $data = $request->all();
        $rules = [
            'name' => 'required|regex:/^[a-zA-Z0-9_]+$/|between:3,25|exists:users,name|alpha_dash',
            'password' => 'required|min:8'
        ];
        $validation = Validator::make($data, $rules);
        if ($validation->fails()) {
            return response()->json([
                "messages" => $validation->errors()
            ], 422);
//            $errors = $validation->errors();
//            return api()->validation('This Fields are Required.' ,$errors);
        }
        if ((Auth::attempt(['name' => $request->name, 'password' => $request->password])))
        {
            $user=Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;
            $datares['token']=$token;
            return response()->json([
                'messages'=>'User has been Login',
                'data'=>$datares
            ]);
//            return api()->ok( 'User has been Login', $datares);


        }
        else
        {
            return response()->json([
                'messages'=>'The password is incorrect',
            ]);
//            return api()->error('the password incorrect !');
        }
    }
    public function logout(Request $request)
    {
        try {

            $accessToken = $request->bearerToken();

            // Get access token from database
            $token = PersonalAccessToken::findToken($accessToken);
            if ($token)
            {
                $token->delete();
                return response()->json([
                    'messages'=>'User has been Logout',
                ]);
//                return api()->ok('User has been Logout');
            }
            else
            {
                return response()->json([
                    'messages'=>'Token not found',
                ]);
//                return api()->error('Token not Found!');

            }
        }
        catch (Exception $e)
        {
            return response()->json([
                'messages'=>'Error',
            ]);
//            return api()->error('Error ');
        }

    }
    public function example(Request $request)
    {

        return auth()->user();
    }
    public function  allUserGroups(Request $request)
    {
        $groups=$this->userRepository->allUserGroups();
        return response()->json([
            'messages'=>'User groups',
            'data'=>$groups
        ]);
//        return api()->ok('User groups', $groups);
    }
    public function allUserOwnedGroups()
    {
        $groups=$this->userRepository->allUserOwnedGroups();
        return response()->json([
            'messages'=>'User Owned groups',
            'data'=>$groups
        ]);
//        return api()->ok('User Owned groups', $groups);

    }
    public function allUserFiles()
    {
        $files=$this->userRepository->allUserFiles();
        return response()->json([
            'messages'=>'User Files',
            'data'=>$files
        ]);
//        return API::response(200,'User Files', $files);

    }

    //
}
