<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    // Specify the table name if necessary
    protected $table = 'admins';

    // Define the fillable properties
    protected $fillable = [
        'username',
        'password_hash',
        'email',
        'last_login',
        'is_active',
        'created_at',
        'updated_at'
    ];
}
