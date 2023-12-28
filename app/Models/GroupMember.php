<?php

namespace App\Models;

use App\Models\Scopes\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GroupMember extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = ['group_id', 'user_id', 'join_date'];

    protected $searchableFields = ['*'];

    protected $table = 'group_members';

    protected $casts = [
        'join_date' => 'date',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
