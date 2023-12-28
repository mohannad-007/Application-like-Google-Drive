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

}
