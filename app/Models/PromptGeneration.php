<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromptGeneration extends Model
{
protected $fillable = [
    'generated_prompt',
    'image_path',
    'original_file_name',
    'file_size',
    'mime_type',
    'user_id'
];

public function user():BelongsTo
{ 
return $this->belongsTo(User::class);
}
}
