<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('business settings page is displayed', function () {
    $user = User::factory()->create([
        'company_name' => 'Prior Music',
    ]);

    $this
        ->actingAs($user)
        ->get(route('business.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/business')
            ->where('business.company_name', 'Prior Music'));
});

test('business settings can be updated', function () {
    $user = User::factory()->create();

    $this
        ->actingAs($user)
        ->patch(route('business.update'), [
            'company_name' => 'Prior React Ltd',
            'company_code' => '123456789',
            'vat' => 'LT123456789',
            'address' => '1 Studio Street',
            'phone' => '+37060000000',
            'contact_person' => 'Andrius',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('business.edit'));

    $user->refresh();

    expect($user->company_name)->toBe('Prior React Ltd')
        ->and($user->company_code)->toBe('123456789')
        ->and($user->vat)->toBe('LT123456789')
        ->and($user->address)->toBe('1 Studio Street')
        ->and($user->phone)->toBe('+37060000000')
        ->and($user->contact_person)->toBe('Andrius');
});
