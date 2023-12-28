<?php

namespace App\Models;

use App\Models\Scopes\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FileEvent extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'file_id',
        'event_type_id',
        'user_id',
        'date',
        'details',
    ];

    protected $searchableFields = ['*'];

    protected $table = 'file_events';

    protected $casts = [
        'date' => 'date',
    ];

    public function file()
    {
        return $this->belongsTo(File::class);
    }

    public function eventType()
    {
        return $this->belongsTo(EventType::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
