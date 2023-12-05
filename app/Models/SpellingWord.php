<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpellingWord extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spelling_words';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['uuid', 'word'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * Indicates if the model should be timestamped.
     *
     * By default, Eloquent expects `created_at` and `updated_at` columns.
     * If you don't have these columns in your table, set this to false.
     */
    public $timestamps = false;

    public function userWords()
    {
        return $this->hasMany(UserWord::class, 'word_uuid', 'uuid');
    }

    public function active_words()
    {
        return $this->hasMany(UserWord::class, 'word_uuid', 'uuid')->where('active', 1);

    }

}
