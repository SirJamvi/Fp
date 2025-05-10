<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Authenticatable
{
    protected $table = 'users'; // Pastikan ini sesuai dengan nama tabel
}
