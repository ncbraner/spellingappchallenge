<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserWord extends Model
{
    use HasFactory;

    // Specify the table if the table name is not the plural form of the model name
    protected $table = 'user_words';

    // Define fillable attributes for mass assignment
    protected $fillable = [
        'user_uuid',
        'word_uuid',
        'list_name',
        'active',
    ];

    // Define the relationship with the User model
    public function user()
    {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }

    // Define the relationship with the SpellingWord model
    public function spellingWord()
    {
        return $this->belongsTo(SpellingWord::class, 'word_uuid', 'uuid');
    }

    public static function deactivateWordsForUserByWords($userUuid, array $words)
    {
        self::where('user_uuid', $userUuid)
            ->join('spelling_words', 'user_words.word_uuid', '=', 'spelling_words.uuid')
            ->whereIn('spelling_words.word', $words)
            ->update(['user_words.active' => 0]);
    }

    public static function add_words($userUuid, array $words)
    {
        $words = array_map('strtolower', $words);
        $spellingWords = SpellingWord::whereIn('word', $words)->get();
        $spellingWords = $spellingWords->map(function ($spellingWord) use ($userUuid) {
            return [
                'user_uuid' => $userUuid,
                'word_uuid' => $spellingWord->uuid,
                'list_name' => 'default',
                'active' => 1,
            ];
        });
        self::insert($spellingWords->toArray());
    }
}
