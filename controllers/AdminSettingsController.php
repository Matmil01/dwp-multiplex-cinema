<?php

class AdminSettingsController
{
    public function showForm(): void
    {
        $saveError = null;
        $saved = false;
        $company = CompanyInfo::find();
        $screens = Screen::prices();

        require __DIR__ . '/../views/admin/settings.php';
    }

    public function save(array $input): void
    {
        $saveError = null;
        $saved = false;

        try {
            CompanyInfo::save([
                'tagline' => $input['tagline'] ?? '',
                'description' => $input['description'] ?? '',
                'opening_time' => $input['opening_time'] ?? '',
                'closing_time' => $input['closing_time'] ?? '',
                'address' => $input['address'] ?? '',
                'phone' => $input['phone'] ?? '',
            ]);
            $saved = true;
        } catch (Throwable $e) {
            $saveError = $e->getMessage();
        }

        if ($saveError !== null) {
            // Saving failed: keep your changes in the form so you can correct the problem and retry.
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

        require __DIR__ . '/../views/admin/settings.php';
    }

    public function savePrices(array $input): void
    {
        $saveError = null;
        $saved = false;
        // (array) guards against ?prices=x, which would otherwise crash the loop in Screen::savePrices().
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
            foreach ($screens as &$screen) {
                if (isset($prices[$screen['id']]) && is_string($prices[$screen['id']])) {
                    $screen['ticket_price'] = $prices[$screen['id']];
                }
            }
            unset($screen);
        }

        require __DIR__ . '/../views/admin/settings.php';
    }
}
