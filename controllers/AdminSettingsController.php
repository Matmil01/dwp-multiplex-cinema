<?php

class AdminSettingsController
{
    public function showForm(): void
    {
        $saveError = null;
        $saved = false;
        $company = CompanyInfo::find();

        require __DIR__ . '/../views/admin/settings.php';
    }

    public function save(array $input): void
    {
        $saveError = null;
        $saved = false;

        try {
            CompanyInfo::save([
                'tagline' => $input['tagline'],
                'description' => $input['description'],
                'opening_time' => $input['opening_time'],
                'closing_time' => $input['closing_time'],
                'address' => $input['address'],
                'phone' => $input['phone'],
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

        require __DIR__ . '/../views/admin/settings.php';
    }
}
