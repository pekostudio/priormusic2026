<?php

use App\Models\User;

test('home redirects to the tracks page', function () {
    $response = $this->get(route('home'));

    $response->assertRedirect(route('tracks', absolute: false));
});

test('guests are redirected from tracks to the login page', function () {
    $response = $this->get(route('tracks'));

    $response->assertRedirect(route('login'));
});

test('dashboard page is not registered', function () {
    $response = $this->get('/dashboard');

    $response->assertNotFound();
});

test('authenticated users can visit tracks from home', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->followingRedirects()->get(route('home'));
    $response->assertOk();
});
