<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    /** @use HasFactory<\Database\Factories\JobFactory> */
    use HasFactory;
    protected $fillable = [
        'title',
        'company',
        'description',
        'skills_required',
        'location',
        'salary',
        'source',
        'contract_type',
    ];

    protected function casts(): array
    {
        return [
            'skills_required' => 'array',
        ];
    }
}
