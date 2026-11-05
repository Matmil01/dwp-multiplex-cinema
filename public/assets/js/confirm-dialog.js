const dialog = document.createElement('dialog');
dialog.className = 'confirm-dialog';
dialog.innerHTML = `
	<p class="confirm-dialog__message"></p>
	<div class="confirm-dialog__actions">
		<button type="button" class="base-button">Keep</button>
		<button type="button" class="danger-button"></button>
	</div>`;
document.body.appendChild(dialog);

const message = dialog.querySelector('.confirm-dialog__message');
const keepButton = dialog.querySelector('.base-button');
const confirmButton = dialog.querySelector('.danger-button');
let pendingForm = null;

// One listener on the whole document catches the submit of EVERY form on the page
// (events "bubble up" from the form to the document). Forms without data-confirm are let through.
document.addEventListener('submit', (event) => {
	const form = event.target;

	if (!form.dataset.confirm) {
		return;
	}

	// Hold the submit until the dialog is answered; the dialog button re-submits it.
	event.preventDefault();
	pendingForm = form;
	message.textContent = form.dataset.confirm;
	confirmButton.textContent = form.dataset.confirmLabel || 'Confirm';
	dialog.showModal();
});

keepButton.addEventListener('click', () => dialog.close());

confirmButton.addEventListener('click', () => {
	dialog.close();
	// submit() skips the submit event, so this doesn't loop back into the dialog.
	pendingForm.submit();
});
