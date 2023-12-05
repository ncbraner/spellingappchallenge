<?php

namespace Tests\Feature;

use App\Http\Controllers\WordManager;
use App\Models\SpellingWord;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\User;
use App\Models\UserWord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class WordManagerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();


        // If there are any other setup operations specific to your tests, you can add them here.
    }

    public function testShowWordManager()
    {
        $user = User::factory()->create();
        $this->actingAs($user)
            ->get(route('word-manager'))
            ->assertStatus(200);

    }

    public function testDeactivateWords()
    {
        $user = User::factory()->create();
        $words = ['apple', 'banana'];
        UserWord::add_words($user->uuid, $words);
        $this->actingAs($user)
            ->post(route('wordmanager.deactivate'), [
                'user_id' => $user->uuid,
                'selected_words' => $words
            ])
            ->assertStatus(200);
    }

    public function testAddWords()
    {
        $user = User::factory()->create();
        $words = ['apple', 'banana'];

        $this->actingAs($user)
            ->post(route('wordmanager.add'), [
                'user_id' => $user->uuid,
                'words' => $words
            ])
            ->assertStatus(200)
            ->assertJson(['currentWords' => $words]);
    }

}
