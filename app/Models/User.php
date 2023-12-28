<?php

namespace App\Models;

use App\Models\Scopes\Searchable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable;
    use HasFactory;
    use Searchable;
    use HasApiTokens;
    protected $fillable = [
        'name',
        'email',
        'password',
        'first_name',
        'last_name',
        'role_id',
    ];

    //protected $searchableFields = ['*'];

    protected $hidden = ['password'];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function groups()
    {
        return $this->hasMany(Group::class, 'owner_id');
    }

    public function groupMembers()
    {
        return $this->hasMany(GroupMember::class);
    }

    public function files()
    {
        return $this->hasMany(File::class);
    }

    public function fileEvents()
    {
        return $this->hasMany(FileEvent::class);
    }

    public function isSuperAdmin(): bool
    {
        return in_array($this->email, config('auth.super_admins'));
    }

    public function fileUserReserved()
    {
        return $this->hasMany(FileUserReserved::class);
    }
}
