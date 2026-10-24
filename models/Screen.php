<?php

// Ticket prices per screen; each screen has one price that every showing on it uses.
class Screen
{
    // Prices are whole kroner. Stripe's minimum charge is 2.50 kr., so 3 is the lowest whole price; the column holds at most 9999.99.
    public const MIN_PRICE = 3;
    public const MAX_PRICE = 9999;

    public static function prices(): array
    {
        return Db::connection()->query(
            "SELECT id, type, CAST(ticket_price AS UNSIGNED) AS ticket_price FROM screens ORDER BY FIELD(type, '16mm', '35mm', '70mm', 'imax')"
        )->fetchAll();
    }

    // $prices maps screen id => price as typed. Checks every price first, so one bad value saves nothing.
    public static function savePrices(array $prices): void
    {
        foreach ($prices as $price) {
            if (!is_string($price) || !ctype_digit($price)) {
                throw new RuntimeException('Every price must be a whole number of kroner.');
            }
            if ((int) $price < self::MIN_PRICE || (int) $price > self::MAX_PRICE) {
                throw new RuntimeException('Prices must be between ' . self::MIN_PRICE . ' and ' . self::MAX_PRICE . ' kr.');
            }
        }

        $update = Db::connection()->prepare('UPDATE screens SET ticket_price = ? WHERE id = ?');

        foreach ($prices as $screenId => $price) {
            $update->execute([(int) $price, (int) $screenId]);
        }
    }
}
