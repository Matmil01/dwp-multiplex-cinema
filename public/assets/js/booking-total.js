// Shows how many seats are ticked and what they cost; the server still does the real checking.
const form = document.querySelector('form[data-price]');
const pricePerSeat = Number(form.dataset.price);
const countLabel = document.getElementById('booking-count');
const totalLabel = document.getElementById('booking-total');

function updateTotal() {
	const seatCount = form.querySelectorAll('input[name="seats[]"]:checked').length;

	countLabel.textContent = seatCount + (seatCount === 1 ? ' seat' : ' seats');
	totalLabel.textContent = seatCount * pricePerSeat + ' kr.';
}

// A change event fires whenever any seat checkbox is ticked or unticked.
form.addEventListener('change', updateTotal);
updateTotal();
