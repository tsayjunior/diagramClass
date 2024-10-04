<?php

namespace Database\Seeders;

use App\Models\TypeRelationship;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'anfitrion',
            'lastname' => 'Test User',
            'email' => 'anfitrion@gmail.com',
            'password' => '123456789',
            'ci' => '123456789',
            'type_user' => User::$USER_HOST,
        ]);
        User::factory()->create([
            'name' => 'invitado',
            'lastname' => 'Test User',
            'email' => 'invitado@gmail.com',
            'password' => '123456789',
            'ci' => '123456789',
            'type_user' => User::$USER_GHEST,
        ]);
        TypeRelationship::create([
            'name' => TypeRelationship::$ASOCIATION
        ]);
        TypeRelationship::create([
            'name' => TypeRelationship::$GENERALIZATION
        ]);
        TypeRelationship::create([
            'name' => TypeRelationship::$AGREGATION
        ]);
        TypeRelationship::create([
            'name' => TypeRelationship::$DEPENDENCIA
        ]);
        TypeRelationship::create([
            'name' => TypeRelationship::$COMPOSITION
        ]);
    }
}
