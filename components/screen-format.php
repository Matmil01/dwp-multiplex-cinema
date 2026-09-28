<?php

function screenFormatMeta(string $type): array
{
    static $meta = [
        '16mm' => [
            'badge' => 'format-badge--16mm',
            'label' => '16mm',
            'blurb' => 'A smaller, grainier film print than 35mm, historically used for archive and repertory prints.',
        ],
        '35mm' => [
            'badge' => 'format-badge--35mm',
            'label' => '35mm',
            'blurb' => 'The classic theatrical film format, with a larger image area and finer detail than 16mm.',
        ],
        '70mm' => [
            'badge' => 'format-badge--70mm',
            'label' => '70mm',
            'blurb' => 'Large-format film print, a bigger frame and sharper image than 35mm.',
        ],
        'imax' => [
            'badge' => 'format-badge--imax',
            'label' => 'IMAX',
            'blurb' => "Our largest screen, with IMAX's proprietary sound and projection system.",
        ],
    ];
    static $default = ['badge' => 'format-badge--unknown', 'label' => 'Unknown', 'blurb' => 'Format information unavailable.'];

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

function screenDisplayName(string $type): string
{
    return formatBadgeLabel($type) . ' Screen';
}
