const form = document.querySelector('form[data-price]');
const pricePerSeat = Number(form.dataset.price);
const countLabel = document.getElementById('booking-count');
const totalLabel = document.getElementById('booking-total');

function updateTotal() {
	// Count the checkboxes named seats[] that are currently ticked (:checked).
	const seatCount = form.querySelectorAll('input[name="seats[]"]:checked').length;

	// textContent replaces the text inside the element.
	countLabel.textContent = seatCount + (seatCount === 1 ? ' seat' : ' seats');
	totalLabel.textContent = seatCount * pricePerSeat + ' kr.';
}

// A change event fires whenever any seat checkbox is ticked or unticked.
// Listening on the whole form (not each box) means one listener covers all 100+ seats.
form.addEventListener('change', updateTotal);
// Run once on page load too, so pre-ticked seats (coming back from review) show a correct total.
updateTotal();
