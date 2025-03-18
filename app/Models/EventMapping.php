<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_event_id',
        'student_event_id',
        'teacher_id',
        'student_id',
    ];
}
