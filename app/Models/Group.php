<?php

namespace App\Models;

use App\Models\Scopes\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Group extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = ['name', 'owner_id'];

    protected $searchableFields = ['*'];

    public function user()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function groupMembers()
    {
        return $this->hasMany(GroupMember::class);
    }

    public function files()
    {
        return $this->hasMany(File::class);
    }

    public function fileUserReserved()
    {
        return $this->hasMany(FileUserReserved::class);
    }
    public function requestUserToGroups()
    {
        return $this->hasMany(RequestUserToGroups::class);
    }
}
