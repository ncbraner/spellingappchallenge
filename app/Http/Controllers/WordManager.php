<?php

namespace App\Http\Controllers;

use App\Models\SpellingWord;
use App\Models\User;
use App\Models\UserWord;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

class WordManager extends Controller
{
    public static function show_word_manager()
    {
        $user = auth()->user();
        $user_id = $user->uuid;

        if (!$user) {
            // Handle the case where the user is not found, maybe return an error response
            return response()->json(['message' => 'User not found'], 404);
        }
        $currentWords = $user->userWords->where('active', 1)->map(function ($userWord) {
            return $userWord->spellingWord->word;
        });

        return view('wordmanager', compact('currentWords', 'user_id'));
    }

    public static function deactivate_words(Request $request)
    {
        $words = $request->input('selected_words');

        $user_id = $request->input('user_id');
        $user = auth()->user();

        if (!$user) {
            // Handle the case where the user is not found, maybe return an error response
            return response()->json(['message' => 'User not found'], 404);
        }

        UserWord::deactivateWordsForUserByWords($user_id, $words);
        $currentWords = $user->userWords->where('active', 1)->map(function ($userWord) {
            return $userWord->spellingWord->word;
        });

        return response()->json(['currentWords' => $currentWords]);
    }


    public static function add_words(Request $request){

        $user = auth()->user();
        $filteredWords = array_filter($request->input('words'));
        $words = array_map('strtolower', $filteredWords);

        $user_id = $request->input('user_id');

        if (!$user) {
            // Handle the case where the user is not found, maybe return an error response
            return response()->json(['message' => 'User not found'], 404);
        }


        foreach ($words as $word) {

            // Check if word already exists and return uuid or null
            $wordUuid = SpellingWord::where('word', $word)->first()->uuid ?? null;

            if (!$wordUuid) {
                $spellingWord = new SpellingWord();
                $spellingWord->uuid = Uuid::uuid4();
                $spellingWord->word = $word;
                $spellingWord->save();
                $wordUuid = $spellingWord->uuid;
            }

            // Check if word is already assigned to user
            $userWord = UserWord::where('user_uuid', $user_id)->where('word_uuid', $wordUuid)->first();

            if ($userWord and $userWord->active == 0) {
                $userWord->active = 1;
                $userWord->save();
            } elseif (!$userWord) {
                UserWord::add_words($user_id, [$word]);
            }

        }


        $currentWords = $user->userWords->where('active', 1)->map(function ($userWord) {
            return $userWord->spellingWord->word;
        });

        return response()->json(['currentWords' => $currentWords]);
    }

}
