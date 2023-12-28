<?php

namespace App\Models;

use App\Models\Scopes\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class File extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'name',
        'extension',
        'group_id',
        'user_id',
        'path',
        'is_active',
        'is_reserved',
    ];

    protected $searchableFields = ['*'];

    protected $casts = [
        'is_active' => 'boolean',
        'is_reserved' => 'boolean',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fileEvents()
    {
        return $this->hasMany(FileEvent::class);
    }


}
