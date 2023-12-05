<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dependent extends Model
{
    use HasFactory;

    // Specify the table if the table name is not the plural form of the model name
    protected $table = 'dependents';

    // Define the primary key if it's not 'id'
    protected $primaryKey = 'uuid';

    // Indicate that the primary key is non-incrementing
    public $incrementing = false;

    // Define the data type of the primary key
    protected $keyType = 'string';

    // Define fillable attributes for mass assignment
    protected $fillable = [
        'uuid',
        'parent_id',
        'child_id',
    ];

    // Define the relationship with the User model for the parent_id
    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id', 'uuid');
    }

    // Define the relationship with the User model for the child_id
    public function child()
    {
        return $this->belongsTo(User::class, 'child_id', 'uuid');
    }
}
