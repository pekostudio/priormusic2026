<?php

test('home redirects to tracks', function () {
    $response = $this->get(route('home'));

    $response->assertRedirect(route('tracks', absolute: false));
});
