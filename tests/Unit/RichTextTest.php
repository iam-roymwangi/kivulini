<?php

use App\Support\RichText;

test('it keeps formatting tags from the editor', function () {
    $html = '<h2>Day one</h2><p><strong>Bold</strong>, <em>italic</em> and <u>underlined</u></p><ul><li>Water</li></ul><ol><li>Hat</li></ol>';

    expect(RichText::sanitize($html))->toBe($html);
});

test('it removes scripts, event handlers and unsafe links', function () {
    $sanitized = RichText::sanitize(
        '<p onclick="alert(1)" style="color:red">Hi</p><script>alert(2)</script><a href="javascript:alert(3)">bad</a><img src=x onerror="alert(4)">'
    );

    expect($sanitized)->toBe('<p>Hi</p><a>bad</a>');
});

test('it keeps safe links and opens them in a new tab', function () {
    expect(RichText::sanitize('<p><a href="https://example.com" class="x">site</a></p>'))
        ->toBe('<p><a href="https://example.com" target="_blank" rel="noopener noreferrer nofollow">site</a></p>');
});

test('it unwraps unknown tags but keeps their text', function () {
    expect(RichText::sanitize('<p><font color="red">Red</font> text</p>'))->toBe('<p>Red text</p>');
});

test('it converts plain text into escaped paragraphs and line breaks', function () {
    expect(RichText::sanitize("First line\nSecond & line\n\nNew paragraph"))
        ->toBe('<p>First line<br>Second &amp; line</p><p>New paragraph</p>');
});

test('it preserves non ascii characters', function () {
    expect(RichText::sanitize('<p>Café — Ngong’</p>'))->toBe('<p>Café — Ngong’</p>');
});
