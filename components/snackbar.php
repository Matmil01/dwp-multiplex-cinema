<?php
// Fixed-position toast for save/delete feedback; expects $snackbarType ('success'|'error')
// and $snackbarMessage to be set by the including page, renders nothing otherwise.
if (!empty($snackbarMessage)):
?>
    <p class="snackbar snackbar--<?= htmlspecialchars($snackbarType, ENT_QUOTES) ?>"><?= htmlspecialchars($snackbarMessage, ENT_QUOTES) ?></p>
<?php endif; ?>
