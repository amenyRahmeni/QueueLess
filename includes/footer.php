<?php
declare(strict_types=1);


$extraJs = $extraJs ?? [];
?>
    </main>

    <footer class="site-footer">
        <div class="container footer-content">
            <p>&copy; <span data-current-year><?= date('Y'); ?></span> QueueLess. Tous droits reserves.</p>
            <p>Reservation de creneaux simple, rapide et moderne.</p>
        </div>
    </footer>

    <script src="<?= asset('js/main.js'); ?>"></script>
    <?php foreach ($extraJs as $jsFile): ?>
        <script src="<?= asset('js/' . ltrim($jsFile, '/')); ?>"></script>
    <?php endforeach; ?>
</body>
</html>
