<?php

if (!empty($snackbarMessage)):
?>
    <p class="snackbar snackbar--<?= htmlspecialchars($snackbarType, ENT_QUOTES) ?>"><?= htmlspecialchars($snackbarMessage, ENT_QUOTES) ?></p>

<?php endif; 
?>
