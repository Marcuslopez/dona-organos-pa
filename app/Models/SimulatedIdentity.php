<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['document_type', 'document_number', 'document_code_hash', 'is_active'])]
#[Hidden(['document_code_hash'])]
class SimulatedIdentity extends Model
{
    protected function casts(): array
    {
        return [
            'document_code_hash' => 'hashed',
            'is_active' => 'boolean',
        ];
    }
}
