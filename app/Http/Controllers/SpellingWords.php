<?php

namespace App\Http\Controllers;

use App\Models\Banks;
use App\Models\SpellingWord;
use App\Models\User;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

class SpellingWords extends Controller
{
    public function showSpellingTest()
    {
        try {
            $user = auth()->user();
            $user_id = $user->uuid;
            if (!$user) {
                throw new AuthenticationException('No authenticated user found.');
            }
        } catch (Exception $e) {
            return response()->json(['message' => 'User not found'], 404);
        }
        // Get the words for the user
        $words = SpellingWord::whereHas('userWords', function ($query) use ($user_id) {
            $query->where('user_uuid', $user_id,)
                ->where('active', 1);
        })->get()->pluck('word')->toArray();

        $bank = Banks::where('user_uuid', $user_id)->first();
        $points = $bank->points ?? 0;
        return view('spelling', compact('words', 'points', 'user_id'));
    }
}

