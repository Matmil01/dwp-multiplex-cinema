<?php $company = CompanyInfo::find(); ?>
<footer class="site-footer">
    <div class="site-footer__details">
        <span><?= htmlspecialchars($company['address'], ENT_QUOTES) ?></span>
        <span><?= htmlspecialchars($company['phone'], ENT_QUOTES) ?></span>
        <span>Open daily <?= date('g:ia', strtotime($company['opening_time'])) ?> - <?= date('g:ia', strtotime($company['closing_time'])) ?></span>
    </div>
    <p class="site-footer__copyright">© <?php echo date('Y'); ?> Untitled Cinema. All rights reserved.</p>
</footer>
