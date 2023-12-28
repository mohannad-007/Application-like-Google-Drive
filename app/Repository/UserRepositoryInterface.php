<?php

namespace App\Repository;

use App\Aspects\Logger;
use App\Models\User;

interface UserRepositoryInterface
{
    public function register(array $data): User;
    public function allUserGroups() ;
    public function allUserOwnedGroups();


}
