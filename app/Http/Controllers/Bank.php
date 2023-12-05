<?php

namespace App\Http\Controllers;

use App\Models\Banks;
use App\Models\User;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;


class Bank extends BaseController
{
    public static function updatePoints(Request $request, $user_id)
    {
        $user = User::where('uuid', $user_id)->first();

        if (!$user) {
            // Handle the case where the user is not found, maybe return an error response
            return response()->json(['message' => 'User not found'], 404);
        }

        $bank = $user->banks ?? new Banks(['user_uuid' => $user_id, 'points' => 0]);

        if (!$bank) {
            // Handle the case where the bank record for the user is not found, maybe return an error response
            return response()->json(['message' => 'Bank record for user not found'], 404);
        }

// Assuming you're sending the points to be added as "pointsToAdd" in the request
        $pointsToAdd = $request->input('pointsToAdd');

        try {
            $bank->points += $pointsToAdd;
            $bank->save();
        } catch (\Exception $e) {
            // Handle the case where the points to add is not a valid integer, maybe return an error response
            return response()->json(['message' => 'Points could not be saved'], 400);
        }


// Return the updated points or any other response you want to send
        return response()->json(['totalPoints' => $bank->points]);

        return response()->json(['totalPoints' => $bank->points]);
    }
}
