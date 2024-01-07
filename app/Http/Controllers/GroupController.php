<?php

namespace App\Http\Controllers;

use App\Aspects\Logger;
use App\Models\RequestUserToGroups;
use App\Repository\GroupRepositoryInterface;
use App\Repository\UserRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Obiefy\API\Facades\API;

#[Logger]
class GroupController extends Controller
{
    //
    protected $groupRepository;
    public function __construct(GroupRepositoryInterface $groupRepository)
    {
        $this->groupRepository = $groupRepository;
    }
    public function creatGroup(Request $request):JsonResponse
    {
        $data = $request->all();
        $rules = [
            'name' => 'required|regex:/^[a-zA-Z0-9]+$/'
        ];
        $owner_id = auth()->user()->id;
        $data['owner_id'] = $owner_id;
        $validation = Validator::make($data, $rules);
        if ($validation->fails()) {
//            $errors = $validation->errors();
            return response()->json([
                "messages" => $validation->errors()
            ], 422);
        }
        $group = $this->groupRepository->createGroup($data);
        if ($group) {
            return response()->json([
                'messages'=>'Group Created Successfully',
                'data'=>$group
            ],201);
        } else
        {
            return response()->json([
                'messages' => 'Group Not Created',
            ],406);
        }
    }
    public function deleteGroup(Request $request):JsonResponse
    {
        $data=$request->all();
        if($this->groupRepository->deleteGroup($data)) {
            return response()->json([
                'messages'=>'Group Deleted Successfully',
            ],204);
        }
        else {
            return response()->json([
                'messages'=>'Not Owned Group',
            ],401);
        }

    }
    public function allGroupFiles(Request $request):JsonResponse
    {
         $data=$request->all();
         $groupFiles=$this->groupRepository->allGroupFiles($data);
        return response()->json([
            'messages'=>'Successfully',
            'data'=>$groupFiles
        ],200);
    }

    public function groupUsers(Request $request):JsonResponse
    {
        $data=$request->all();
        $groupuser = $this->groupRepository->GroupUsers($data);
        return response()->json([
            'messages'=>'Successfully',
            'data'=>$groupuser
        ],200);
    }

    public function allUserGroup():JsonResponse
    {
        $allUserGroup = $this->groupRepository->allUserGroup();
        return response()->json([
            'messages'=>'Successfully',
            'data'=>$allUserGroup
        ],200);
    }
    public function addUserToGroup(Request $request)
    {
        return $this->groupRepository->addUserToGroup($request);
    }
    public function deleteUserFromGroup(Request $request)
    {
        return $this->groupRepository->deleteUserFromGroup($request);
    }
    public function displayAllUser()
    {
        return $this->groupRepository->displayAllUser();
    }
    public function displayAllGroups()
    {
        return $this->groupRepository->displayAllGroups();
    }
    public function searchUser(Request $request):JsonResponse
    {
        return $this->groupRepository->searchUser($request);
    }
    public function searchGroup(Request $request):JsonResponse
    {
        return $this->groupRepository->searchGroup($request);
    }

    public function displayUserRequestForGroup(Request $request)
    {
        return $this->groupRepository->displayUserRequestForGroup($request);
    }
    public function unAcceptedRequest(Request $request):JsonResponse{
        return $this->groupRepository->unAcceptedRequest($request);
    }
    public  function AcceptedRequest(Request $request):JsonResponse{
        return $this->groupRepository->AcceptedRequest($request);
    }
    public function RequestToJoinGroup(Request $request):JsonResponse{
        return $this->groupRepository->RequestToJoinGroup($request);
    }




    public function toggleDatabase()
    {
        // Get the current database name from the .env file
        $currentDatabase = env('DB_DATABASE');

        // Toggle between 'sourcefile' and 'sourcefile2'
        $newDatabase = ($currentDatabase === 'sourcefile') ? 'sourcefile2' : 'sourcefile';

        // Update the .env file with the new DB_DATABASE value
        $this->updateEnvFile(['DB_DATABASE' => $newDatabase]);

        // Clear the database configuration cache
        Artisan::call('config:clear');

        return response()->json([
           'messages' => 'Database Changed to ' . $newDatabase,
        ]);
//        return redirect()->back()->with('success', 'Database toggled successfully.');
    }

    private function updateEnvFile($data)
    {
        $envFilePath = base_path('.env');

        // Read the existing .env file
        $currentEnv = file_get_contents($envFilePath);

        // Replace the existing values with new ones
        foreach ($data as $key => $value) {
            $currentEnv = preg_replace('/^' . $key . '.+/m', $key . '=' . $value, $currentEnv);
        }

        // Save the changes back to the .env file
        file_put_contents($envFilePath, $currentEnv);
    }



}

