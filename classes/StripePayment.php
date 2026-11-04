<?php

class StripePayment
{
    private \Stripe\StripeClient $client;
    private string $siteUrl;

    public function __construct()
    {
        $config = require __DIR__ . '/../config/config.php';
        $this->siteUrl = $config['site_url'];

        if ($config['stripe']['secret_key'] === '') {
            throw new RuntimeException('STRIPE_SECRET_KEY is not set.');
        }

        $this->client = new \Stripe\StripeClient($config['stripe']['secret_key']);
    }

    public function createCheckout(string $productName, float $ticketPrice, int $quantity, string $successPath, string $cancelPath): array
    {
        $session = $this->client->checkout->sessions->create([
            'mode' => 'payment',
            'line_items' => [[
                'price_data' => [
                    'currency' => 'dkk',
                    // Stripe counts in the smallest unit, so 95.00 kr. is sent as 9500 øre.
                    'unit_amount' => (int) round($ticketPrice * 100),
                    'product_data' => ['name' => $productName],
                ],
                'quantity' => $quantity,
            ]],
            'success_url' => $this->siteUrl . $successPath,
            'cancel_url' => $this->siteUrl . $cancelPath,
        ]);

        return ['id' => $session->id, 'url' => $session->url];
    }

    // Asks Stripe about a payment page: returns the payment id if it was paid, or null if not.
    // The "payment intent" is Stripe's id for the actual money movement; it is what a refund needs.
    public function paidIntent(string $sessionId): ?string
    {
        $session = $this->client->checkout->sessions->retrieve($sessionId);

        return $session->payment_status === 'paid' ? (string) $session->payment_intent : null;
    }

    // Returns the full amount for a payment. Called only when a booking fails after payment.
    public function refund(string $paymentIntent): void
    {
        $this->client->refunds->create(['payment_intent' => $paymentIntent]);
    }
}
