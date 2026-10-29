<?php

// All contact with Stripe lives here, so the rest of the app never touches the Stripe library or its key.
class StripePayment
{
    private ?\Stripe\StripeClient $client = null;
    private string $siteUrl;

    public function __construct()
    {
        $config = require __DIR__ . '/../config/config.php';
        $this->siteUrl = $config['site_url'];

        if ($config['stripe']['secret_key'] !== '') {
            $this->client = new \Stripe\StripeClient($config['stripe']['secret_key']);
        }
    }

    // False when no key is configured: checkout then skips payment (demo mode).
    public function isEnabled(): bool
    {
        return $this->client !== null;
    }

    // Starts a payment page for $quantity tickets and returns its id and the URL to send the customer to.
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
    public function paidIntent(string $sessionId): ?string
    {
        $session = $this->client->checkout->sessions->retrieve($sessionId);

        return $session->payment_status === 'paid' ? (string) $session->payment_intent : null;
    }

    public function refund(string $paymentIntent): void
    {
        $this->client->refunds->create(['payment_intent' => $paymentIntent]);
    }
}
