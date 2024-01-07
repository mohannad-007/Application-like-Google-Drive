<?php

namespace App\Repository;

use App\Models\Group;

interface GroupRepositoryInterface
{
//    public function allGroupFiles($id) ;
//    public function allGroupUsers($id);
//    public function addFileToGroup($attribute,$id);
//    public function deleteFilefromGroup($attribute,$id);
//    public function addUserToGroup($attribute,$id);
//    public function deleteUserFromGroup($attribute,$id);
//    public function deleteCash();
    public function createGroup(array $data):Group;
    public function deleteGroup(array $data);
    public function allGroupFiles($data) ;
    public function groupUsers($data);
    public function allUserGroup();
    public function addUserToGroup($data);
    public function deleteUserFromGroup($data);
    public function displayallUser();
    public function displayAllGroups();
    public function searchUser($data);
    public function searchGroup($data);
    public function displayUserRequestForGroup($data);
    public function unAcceptedRequest($data);
    public function AcceptedRequest($data);
    public function RequestToJoinGroup($data);

}
