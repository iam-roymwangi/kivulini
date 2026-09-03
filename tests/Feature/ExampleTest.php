<?php

test('renders the homepage landing content', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('Explore Kenya with', false);
    $response->assertSee('Kivulini Adventures', false);
    $response->assertSee('View upcoming trips', false);
});
