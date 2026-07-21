<?php

use App\Models\Role;
use App\Models\User;

it('displays a concrete dashboard for the connected user', function () {
    $role = Role::create(['libelle' => 'Agent_simple']);
    $user = User::factory()->create([
        'role_id' => $role->id,
        'solde_absence' => 10,
        'solde_conge' => 30,
    ]);

    actingAs($user);

    $response = $this->get('/dashboard');

    $response->assertStatus(200);
    $response->assertSee('Actions rapides');
    $response->assertSee('Résumé de votre situation');
    $response->assertSee('Demandes à traiter');
});
