<?php

// Presentation metadata (badge color, marketing blurb) per screen type — not stored in the DB
// since it's just marketing copy, shared by the front page and the Screens page.
function screenFormatMeta(string $type): array
{
    static $meta = [
        '35mm' => [
            'badge' => 'format-badge--35mm',
            'label' => '35mm',
            'blurb' => 'Shown in 35mm unless noted in 16mm. Classic film-print projection, warmer grain and colour than digital.',
        ],
        '70mm' => [
            'badge' => 'format-badge--70mm',
            'label' => '70mm',
            'blurb' => 'Large-format film print, a bigger frame and sharper image than 35mm.',
        ],
        'digital' => [
            'badge' => 'format-badge--digital',
            'label' => 'Digital',
            'blurb' => 'Our everyday screening room — 2K digital projection.',
        ],
        '4k' => [
            'badge' => 'format-badge--4k',
            'label' => '4K',
            'blurb' => 'Premium digital projection at 4K resolution with laser illumination.',
        ],
        'imax' => [
            'badge' => 'format-badge--imax',
            'label' => 'IMAX',
            'blurb' => "Our largest screen, with IMAX's proprietary sound and projection system.",
        ],
        'drive-in' => [
            'badge' => 'format-badge--digital',
            'label' => 'Drive-in',
            'blurb' => 'Watch from your car on our outdoor screen — weather permitting.',
        ],
    ];
    static $default = ['badge' => 'format-badge--digital', 'label' => '', 'blurb' => ''];

    return $meta[$type] ?? $default;
}

function formatBadgeClasses(string $type): string
{
    return screenFormatMeta($type)['badge'];
}

function formatBadgeLabel(string $type): string
{
    return screenFormatMeta($type)['label'];
}

function formatBlurb(string $type): string
{
    return screenFormatMeta($type)['blurb'];
}
