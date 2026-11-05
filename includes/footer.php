</main>
<footer class="bg-dark text-white-50 text-center py-3 mt-auto">
    <small>BWP501 &middot; Student Registration System &copy; <?= date('Y') ?></small>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php foreach (($pageScripts ?? []) as $script): ?>
<script src="<?= e($script) ?>"></script>
<?php endforeach; ?>
</body>
</html>
