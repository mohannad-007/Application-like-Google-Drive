<?php

namespace App\Repository;

use App\Models\File;
use App\Models\FileUserReserved;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\RequestUserToGroups;
use App\Models\User;
use Carbon\Carbon;
use http\Env\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GroupRepository implements GroupRepositoryInterface
{
    protected $groupModel;
    protected $groupMember;
    protected  $fileModel;

    public function __construct(Group $groupModel, GroupMember $groupMember,File $fileModel)
    {
        $this->groupModel = $groupModel;
        $this->groupMember = $groupMember;
        $this->fileModel = $fileModel;
    }

    public function createGroup(array $data): Group
    {
        // TODO: Implement creatGroup() method.

        $group = new Group();
        //dd($group);
        $group->name = $data['name'];
        $group->owner_id = $data['owner_id'];
        $group->save();

        $groupMember = new GroupMember();
        $groupMember->group_id = $group->id;
        $groupMember->user_id = $data['owner_id'];
        $groupMember->join_date = Carbon::now();
        $groupMember->save();

        $returnGroup = new Group();
        $returnGroup->id = $group->id;
        $returnGroup->name = $group->name;
        $returnGroup->owner_id = $group->owner_id;
        $returnGroup->updated_at = $group->updated_at;
        $returnGroup->created_at = $group->created_at;

        return $returnGroup;
    }

    public function deleteGroup(array $data)
    {
        // TODO: Implement deleteGroup() method.
        $groupOwner = Group::where('id', $data['group_id'])
            ->where('owner_id', auth()->id());
      //
        if ($groupOwner->count() > 0)
        {
//            dd($groupOwner);
            $groupOwner->delete();
            return  true;
        }
        return false;
    }

    public function allGroupFiles($data)
    {
        $groupFiles = $this->fileModel->where('group_id',$data['group_id'])->where('is_active',1)->get();
        return $groupFiles;

    }
    public function groupUsers($data)
    {
        // TODO: Implement allGroupFiles() method.
        $groupuser = GroupMember::where('group_id',$data['group_id'])->with('user')->get();
        return $groupuser;
    }
    public function allUserGroup()
    {
        // TODO: Implement allGroupFiles() method.
        $userId = auth()->id();
        $userGroups = GroupMember::where('user_id', $userId)->with('group')->get();
        return $userGroups;
    }

    public function addUserToGroup($data)
    {
        $currentUserId = Auth::id();
        $group = Group::find($data->group_id);
        if (!$group) {
            return response()->json([
                'messages'=>'Group not found',
            ]);
        }
        if ($group->owner_id !== $currentUserId) {
            return response()->json([
                'messages'=>'Dont have access to add to Group',
            ]);
        }
        $existingMember = GroupMember::where('group_id', $data->group_id)->where('user_id', $data->user_id)->first();

        if ($existingMember) {
            return response()->json([
                'messages'=>'User has in Group Already',
            ],405);
        }
        $newMember = new GroupMember();
        $newMember->group_id = $data->group_id;
        $newMember->user_id = $data->user_id;
        $newMember->join_date = now(); // يمكنك تغيير التاريخ حسب الحاجة
        $newMember->save();
        return response()->json([
            'messages'=>'User Added To Group',
        ],201);
    }

    public function deleteUserFromGroup($data){
        $currentUserId = Auth::id();
        $group = Group::find($data->group_id);
        if (!$group) {
            return response()->json([
                'messages'=>'Group not found',
            ]);
        }
        if ($group->owner_id !== $currentUserId) {
            return response()->json([
                'messages'=>'Dont have access to delete from Group',
            ]);
        }
        $file_user_reserve = FileUserReserved::where('group_id', $data->group_id)->where('user_id', $data->user_id)->first();
        if ($file_user_reserve){
            return response()->json([
                'messages'=>'User has Reserved File',
            ],405);
        }


        $existingMember = GroupMember::where('group_id', $data->group_id)->where('user_id', $data->user_id)->first();

        if (!$existingMember) {
            return response()->json([
                'messages'=>'User not in Group',
            ],405);
        }
        else{
            GroupMember::where('group_id', $data->group_id)->where('user_id', $data->user_id)->delete();
            return response()->json([
                'messages'=>'User Deleted Successfully',
            ],405);
        }
    }

    public function displayAllUser(){
        $allUser = User::all();
        return response()->json([
            'data'=>$allUser
        ],200);
    }

    public function displayAllGroups(){
        $allgroup = Group::all();
        return response()->json([
            'data'=>$allgroup
        ],200);
    }

    //method search for user
    public function searchUser($data){
        $search = $data->get('name');
        $user = User::where('name', 'LIKE', '%' . $search . '%')->get();
        if ($user->isEmpty()) {
            return response()->json(['message' => 'No user found with this name'], 404);
        }
        return response()->json([
            'data'=>$user
        ],200);
    }

    public function searchGroup($data){
        $search = $data->get('name');
        $group = Group::where('name', 'LIKE', '%' . $search . '%')->get();
        if ($group->isEmpty()) {
            return response()->json(['message' => 'No group found with this name'], 404);
        }
        return response()->json([
            'data'=>$group
        ],200);
    }
    public function displayUserRequestForGroup($data){
        $currentUserId = Auth::id();
        $group = Group::find($data->group_id);
        if (!$group) {
            return response()->json([
                'messages'=>'Group not found',
            ]);
        }
        if ($group->owner_id !== $currentUserId) {
            return response()->json([
                'messages'=>'Dont have access to show request for this Group',
            ]);
        }
        $allGroupRequest = RequestUserToGroups::where('group_id', $data->group_id)->with('user')->get();
//        $formattedData = $allGroupRequest->map(function ($request) {
//            return [
//                'user' => $request->user
//            ];
//        });
//        if ($formattedData){
//            return response()->json([
//                'data'=>$formattedData
//            ]);
//        }
        if ($allGroupRequest){
            return response()->json([
                'data'=>$allGroupRequest
            ]);
        }
        return response()->json([
            'data'=> 'you dont have any request for this group'
        ],200);
    }

    public function unAcceptedRequest($data){
        $data=$data->all();
        $rules=[
            'group_id'=>'required|integer',
            'user_id'=>'required|integer'
        ];
        $validation = Validator::make($data, $rules);
        if ($validation->fails())
        {
            return response()->json(['status'=>false,'message'=>$validation->errors()->first()],500);
        }
        $userRequest=RequestUserToGroups::where('group_id',$data['group_id'])
            ->where('user_id',$data['user_id'])
            ->first();
        if (!$userRequest){
            return response()->json(['status'=>false,'message'=>'you dont have request for this user in group'],500);
        }
        $userRequest->update(['is_accepted'=>0]);
        $userRequest->delete();

        return response()->json(['message'=>"access denied"]);
    }
    public function AcceptedRequest($data){
        $data=$data->all();
        $rules=[
            'group_id'=>'required|integer',
            'user_id'=>'required|integer'
        ];
        $validation = Validator::make($data, $rules);
        if ($validation->fails())
        {
            return response()->json(['status'=>false,'message'=>$validation->errors()->first()],500);
        }
        $userRequest=RequestUserToGroups::where('group_id',$data['group_id'])
            ->where('user_id',$data['user_id'])
            ->first();
        if (!$userRequest){
            return response()->json(['status'=>false,'message'=>'you dont have request for this user in group'],500);
        }
        $userRequest->update(['is_accepted'=>1]);

        $newGroupMember = new GroupMember();
        $newGroupMember->group_id = $data['group_id'];
        $newGroupMember->user_id = $data['user_id'];
        $newGroupMember->join_date = Carbon::now();
        $newGroupMember->save();

        $userRequest->delete();

        return response()->json(['message'=>"new member join group"]);
    }

    public function RequestToJoinGroup($data){
        $data=$data->all();
        $rules=[
            'group_id'=>'required|integer',
        ];
        $validation = Validator::make($data, $rules);
        if ($validation->fails())
        {
            return response()->json(['status'=>false,'message'=>$validation->errors()->first()],500);
        }
        $userRequest=RequestUserToGroups::where('group_id',$data['group_id'])->where('user_id',auth()->id())->first();
        if ($userRequest){
            return response()->json([
                'status'=>false,
                'message'=>'you already have request for this user in group'
            ],405);
        }
        $existingMember = GroupMember::where('group_id', $data['group_id'])->where('user_id',auth()->id())->first();

        if ($existingMember) {
            return response()->json([
                'messages'=>'User in Group',
            ],405);
        }

        $newRequestToJoinGroup=new RequestUserToGroups();
        $newRequestToJoinGroup->group_id=$data['group_id'];
        $newRequestToJoinGroup->user_id=auth()->id();
        $newRequestToJoinGroup->save();

        return response()->json(['message'=>"request sent"]);
    }



}
