<?php

use App\Support\KenyanPhoneNumber;

test('it normalizes common kenyan mobile formats to 254', function (string $input, string $expected) {
    expect(KenyanPhoneNumber::normalize($input))->toBe($expected);
})->with([
    ['0700000001', '254700000001'],
    ['+254700000001', '254700000001'],
    ['254700000001', '254700000001'],
    ['0110000000', '254110000000'],
    ['700000001', '254700000001'],
]);

test('it rejects invalid kenyan numbers', function (string $input) {
    expect(KenyanPhoneNumber::normalize($input))->toBeNull();
})->with([
    ['12345'],
    ['+1 202 555 0100'],
    ['254800000001'],
]);
