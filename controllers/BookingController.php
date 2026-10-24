<?php

class BookingController
{
    public function showSeats(int $showingId, ?int $userId): void
    {
        // Admins get the read-only seat map (any showing, past or upcoming) instead of the booking picker.
        if (($_SESSION['role'] ?? '') === 'admin') {
            header('Location: /admin/showing-seats?showing=' . $showingId);
            exit;
        }

        $showing = $this->bookableShowing($showingId, $userId);

        if ($showing) {
            // Coming back from the review page via "Change seats": start with the seats already chosen.
            $checkout = $_SESSION['checkout'] ?? null;
            $selectedSeats = $checkout && (int) $checkout['showing_id'] === (int) $showing['id'] ? $checkout['seats'] : [];

            $this->renderSeatPicker($showing, null, $selectedSeats);
        }
    }

    // Step 1: checks the seats, then parks the choice in the session and sends the user on to review it.
    public function book(int $showingId, ?int $userId, array $seats): void
    {
        if (($_SESSION['role'] ?? '') === 'admin') {
            http_response_code(403);
            $title = 'Admins can\'t book seats';
            $message = 'Log in with a customer account to make a booking.';
            require __DIR__ . '/../views/error.php';
            return;
        }

        $showing = $this->bookableShowing($showingId, $userId);

        if (!$showing) {
            return;
        }

        try {
            Booking::validateSeats($showing, $seats);
        } catch (RuntimeException $e) {
            $this->renderSeatPicker($showing, $e->getMessage(), $seats);
            return;
        }

        $_SESSION['checkout'] = ['showing_id' => (int) $showing['id'], 'seats' => array_values(array_unique($seats))];

        header('Location: /checkout');
        exit;
    }

    // Step 2: the review page. Nothing is saved yet; the seats only exist in the session.
    public function checkout(?int $userId): void
    {
        $checkout = $_SESSION['checkout'] ?? null;

        if (!$checkout) {
            header('Location: /movies');
            exit;
        }

        $showing = $this->bookableShowing($checkout['showing_id'], $userId);

        if (!$showing) {
            return;
        }

        $seats = $checkout['seats'];
        $total = (float) $showing['ticket_price'] * count($seats);
        $paymentEnabled = (new StripePayment())->isEnabled();

        require __DIR__ . '/../views/checkout.php';
    }

    // Step 3: with a Stripe key configured, sends the user to Stripe's payment page; without one, saves the booking directly.
    public function confirm(?int $userId): void
    {
        $checkout = $_SESSION['checkout'] ?? null;

        if (!$checkout) {
            header('Location: /movies');
            exit;
        }

        $showing = $this->bookableShowing($checkout['showing_id'], $userId);

        if (!$showing) {
            return;
        }

        $payment = new StripePayment();

        if (!$payment->isEnabled()) {
            $this->finishBooking($userId, $showing, $checkout['seats']);
        }

        // Already paid on an earlier attempt but never came back from Stripe: finish that payment instead of charging again.
        if (!empty($checkout['stripe_session_id'])) {
            $earlierPayment = $payment->paidIntent($checkout['stripe_session_id']);

            if ($earlierPayment !== null) {
                $this->finishBooking($userId, $showing, $checkout['seats'], $earlierPayment);
            }
        }

        $session = $payment->createCheckout(
            $showing['movie_title'] . ' - ' . implode(', ', $checkout['seats']),
            (float) $showing['ticket_price'],
            count($checkout['seats']),
            '/checkout/success',
            '/checkout'
        );

        // Remembered server-side so the return page can ask Stripe about this exact payment, not one named in the URL.
        $_SESSION['checkout']['stripe_session_id'] = $session['id'];

        header('Location: ' . $session['url']);
        exit;
    }

    // Step 4: back from Stripe. Asks Stripe whether the payment went through before anything is saved.
    public function complete(?int $userId): void
    {
        $checkout = $_SESSION['checkout'] ?? null;

        if (!$checkout || empty($checkout['stripe_session_id'])) {
            header('Location: /movies');
            exit;
        }

        $showing = $this->bookableShowing($checkout['showing_id'], $userId);

        if (!$showing) {
            return;
        }

        // Reaching this URL proves nothing on its own; only Stripe's answer does.
        $paymentIntent = (new StripePayment())->paidIntent($checkout['stripe_session_id']);

        if ($paymentIntent === null) {
            header('Location: /checkout');
            exit;
        }

        $this->finishBooking($userId, $showing, $checkout['seats'], $paymentIntent);
    }

    // Creates the booking from the session, emails the invoice, and ends on the success page.
    // $paymentIntent is Stripe's id for money already taken; it is refunded if the booking can't be saved.
    private function finishBooking(int $userId, array $showing, array $seats, ?string $paymentIntent = null): void
    {
        unset($_SESSION['checkout']);

        try {
            $bookingId = Booking::create($userId, $showing, $seats);
        } catch (RuntimeException $e) {
            $reason = $e->getMessage();

            if ($paymentIntent !== null) {
                (new StripePayment())->refund($paymentIntent);
                $reason .= ' Your payment has been refunded.';
            }

            // A seat was taken while paying: back to the picker with the reason, seats still ticked.
            $this->renderSeatPicker($showing, $reason, $seats);
            exit;
        }

        $this->sendConfirmation($bookingId);

        header('Location: /booking/success?booking=' . $bookingId);
        exit;
    }

    // Only the customer who made the booking can see its success page; anyone else gets a 404.
    public function success(int $bookingId, ?int $userId): void
    {
        $booking = Booking::find($bookingId);

        if (!$booking || $userId === null || (int) $booking['user_id'] !== $userId) {
            http_response_code(404);
            $title = 'Booking not found';
            $message = 'We could not find that booking on your account.';
            require __DIR__ . '/../views/error.php';
            return;
        }

        $total = (float) $booking['price_per_seat'] * count($booking['seats']);

        require __DIR__ . '/../views/booking-success.php';
    }

    // The booking is already saved, so a failed email is ignored rather than shown as a booking error.
    private function sendConfirmation(int $bookingId): void
    {
        $booking = Booking::find($bookingId);
        $html = Mailer::render('booking-invoice', ['booking' => $booking]);

        Mailer::send($booking['customer_email'], "Your booking at Untitled Cinema (#$bookingId)", $html, null, true);
    }

    // Sends logged-out visitors to /login and returns null (after a 404) when the showing is missing or already started.
    private function bookableShowing(int $showingId, ?int $userId): ?array
    {
        if ($userId === null) {
            header('Location: /login?reason=booking');
            exit;
        }

        $showing = Showing::findForBooking($showingId);

        if (!$showing || !Booking::isOpen($showing)) {
            http_response_code(404);
            $title = 'Showing not found';
            $message = 'That showing does not exist or has already started.';
            require __DIR__ . '/../views/error.php';
            return null;
        }

        return $showing;
    }

    private function renderSeatPicker(array $showing, ?string $error, array $selectedSeats): void
    {
        $takenSeats = Booking::takenSeats($showing['id']);

        require __DIR__ . '/../views/book.php';
    }
}
