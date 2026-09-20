<?php

// Presentation metadata (badge color, marketing blurb) per screen type — not stored in the DB
// since it's just marketing copy, shared by the front page and the Screens page.
function screenFormatMeta(string $type): array
{
    static $meta = [
        '35mm' => [
            'badge' => 'border-amber-500 text-amber-300',
            'blurb' => 'Shown in 35mm unless noted in 16mm. Classic film-print projection, warmer grain and colour than digital.',
        ],
        '70mm' => [
            'badge' => 'border-amber-400 text-amber-200',
            'blurb' => 'Large-format film print, a bigger frame and sharper image than 35mm.',
        ],
        'digital' => [
            'badge' => 'border-gray-600 text-gray-300',
            'blurb' => 'Our everyday screening room — 2K digital projection.',
        ],
        '4k' => [
            'badge' => 'border-blue-400 text-blue-200',
            'blurb' => 'Premium digital projection at 4K resolution with laser illumination.',
        ],
        'imax' => [
            'badge' => 'border-blue-500 text-blue-300',
            'blurb' => "Our largest screen, with IMAX's proprietary sound and projection system.",
        ],
        'drive-in' => [
            'badge' => 'border-gray-600 text-gray-300',
            'blurb' => 'Watch from your car on our outdoor screen — weather permitting.',
        ],
    ];
    static $default = ['badge' => 'border-gray-600 text-gray-300', 'blurb' => ''];

    return $meta[$type] ?? $default;
}

function formatBadgeClasses(string $type): string
{
    return screenFormatMeta($type)['badge'];
}

function formatBlurb(string $type): string
{
    return screenFormatMeta($type)['blurb'];
}
