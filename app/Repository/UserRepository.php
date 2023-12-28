<?php

namespace App\Repository;

use App\Aspects\Logger;
use App\Models\File;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use Laravel\Passport\Passport;

class UserRepository implements UserRepositoryInterface
{
    protected $userModel;
    protected  $fileModel;
    protected $groupModel;
    protected  $groupMembers;
    public function __construct(User $userModel,File $fileModel,Group $groupModel,GroupMember $groupMembers)
    {
        $this->userModel = $userModel;
        $this->fileModel = $fileModel;
        $this->groupModel = $groupModel;
        $this->groupMembers = $groupMembers;


    }

    public function register(array $data): User
    {
        $user = new User();
        $user->name=$data['name'];
        $user->email=$data['email'];
        $user->password=bcrypt($data['password']);
        $user->first_name=$data['first_name'];
        $user->last_name=$data['last_name'];
        $user->role_id=$data['role_id'];
        $user->save();
        /*

        */
        return $user;
    }
    public function allUserGroups()
    {
        // TODO: Implement allUserGroups() method.
        //return from group members table where user id = id return all groups with group name
        return $this->groupMembers->where('user_id',auth()->user()->id)->with('group')->get();



    }
    public function allUserOwnedGroups()
    {

        // TODO: Implement allUserOwnedGroups() method.
        return $this->groupModel->where('owner_id',auth()->user()->id)->get();

    }
    public function allUserFiles()
    {
        // TODO: Implement allUserFiles() method.
        return $this->fileModel->where('user_id',auth()->user()->id)->with('group','user')->get();

    }
    public function allUserGroupsFiles()
    {
        // TODO: Implement allUserGroupsFiles() method.
        //
        //
    }


}
