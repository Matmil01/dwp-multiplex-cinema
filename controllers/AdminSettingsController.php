<?php

class AdminSettingsController
{
    public function showForm(): void
    {
        $saveError = null;
        $saved = false;
        $company = CompanyInfo::find();
        $screens = Screen::prices();

        require __DIR__ . '/../templates/admin/settings.php';
    }

    // Throws a RuntimeException with a readable reason for the first rule the input breaks.
    // Limits follow the column sizes in schema.sql.
    private function validate(array $input): void
    {
        $tagline = trim($input['tagline'] ?? '');
        if ($tagline === '' || mb_strlen($tagline) > 150) {
            throw new RuntimeException('Tagline is required (max 150 characters).');
        }

        if (mb_strlen($input['description'] ?? '') > 5000) {
            throw new RuntimeException('Description is too long (max 5000 characters).');
        }

        $timePattern = '/^([01]\d|2[0-3]):[0-5]\d$/';
        $opening = $input['opening_time'] ?? '';
        $closing = $input['closing_time'] ?? '';
        if (!preg_match($timePattern, $opening) || !preg_match($timePattern, $closing)) {
            throw new RuntimeException('Opening and closing time must be valid times (HH:MM).');
        }
        // Same-length HH:MM strings compare correctly as text.
        if ($opening >= $closing) {
            throw new RuntimeException('Closing time must be after opening time.');
        }

        if (mb_strlen(trim($input['address'] ?? '')) > 150) {
            throw new RuntimeException('Address is too long (max 150 characters).');
        }

        $phone = trim($input['phone'] ?? '');
        if (mb_strlen($phone) > 30 || !preg_match('/^[0-9 +()\-]*$/', $phone)) {
            throw new RuntimeException('Phone can only contain digits, spaces, + ( ) - (max 30 characters).');
        }
    }

    public function save(array $input): void
    {
        // Anything that isn't text (e.g. tagline[]=x) would crash trim(); treat it as empty.
        $input = array_map(fn($value) => is_string($value) ? $value : '', $input);
        $saveError = null;
        $saved = false;

        // CompanyInfo::save() throws if the new hours would cut off existing showings.
        try {
            $this->validate($input);
            CompanyInfo::save([
                'tagline' => trim($input['tagline'] ?? ''),
                'description' => trim($input['description'] ?? ''),
                'opening_time' => $input['opening_time'] ?? '',
                'closing_time' => $input['closing_time'] ?? '',
                'address' => trim($input['address'] ?? ''),
                'phone' => trim($input['phone'] ?? ''),
            ]);
            $saved = true;
        } catch (Throwable $e) {
            $saveError = $e->getMessage();
        }

        if ($saveError !== null) {
            $company = [
                'tagline' => $input['tagline'] ?? '',
                'description' => $input['description'] ?? '',
                'opening_time' => $input['opening_time'] ?? '',
                'closing_time' => $input['closing_time'] ?? '',
                'address' => $input['address'] ?? '',
                'phone' => $input['phone'] ?? '',
            ];
        } else {
            $company = CompanyInfo::find();
        }

        $screens = Screen::prices();

        require __DIR__ . '/../templates/admin/settings.php';
    }

    public function savePrices(array $input): void
    {
        $saveError = null;
        $saved = false;
        $prices = (array) ($input['prices'] ?? []);

        try {
            Screen::savePrices($prices);
            $saved = true;
        } catch (RuntimeException $e) {
            $saveError = $e->getMessage();
        }

        $company = CompanyInfo::find();
        $screens = Screen::prices();

        if ($saveError !== null) {
            // Saving failed: keep the typed prices in the form so you can correct them and retry.
            // &$screen edits the real rows; unset($screen) afterwards breaks that link so the
            // variable can't accidentally overwrite the last row later.
            foreach ($screens as &$screen) {
                if (isset($prices[$screen['id']]) && is_string($prices[$screen['id']])) {
                    $screen['ticket_price'] = $prices[$screen['id']];
                }
            }
            unset($screen);
        }

        require __DIR__ . '/../templates/admin/settings.php';
    }
}
