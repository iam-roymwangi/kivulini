<?php

use function Pest\Laravel\get;

test('homepage exposes indexable seo tags', function () {
    $response = get('/');

    $response->assertOk();
    $response->assertSee('name="robots" content="index,follow"', false);
    $response->assertSee('rel="canonical" href="'.rtrim(url('/'), '/').'"', false);
    $response->assertSee('property="og:url" content="'.rtrim(url('/'), '/').'"', false);
});
