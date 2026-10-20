<?php
// "Today"/"Tomorrow" instead of the raw date, otherwise weekday + date.
function showingDayLabel(string $showDatetime): string
{
    $day = date('Y-m-d', strtotime($showDatetime));
    if ($day === date('Y-m-d')) {
        return 'Today';
    }
    if ($day === date('Y-m-d', strtotime('+1 day'))) {
        return 'Tomorrow';
    }
    return date('D, M j', strtotime($showDatetime));
}
