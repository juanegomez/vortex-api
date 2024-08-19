<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Questions;

class Answers extends Model
{
    use HasFactory;

    protected $table = 'answers';

    protected $fillable = [
        'body',
        'question_id',
        'is_correct',
    ];

     /**
     * Una respuesta pertenece a una pregunta.
     */
    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }    
}
