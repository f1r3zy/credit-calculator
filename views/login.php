<?php include __DIR__ . '/layout.php'; ?>
<div class="auth-container">
    <div class="auth-card">
        <h2 class="text-center mb-4">Autentificare</h2>
        <form id="login-form" method="post">
            <?= \App\Core\Csrf::field() ?>
            <div class="mb-3">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Parola</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Intra in cont</button>
        </form>
        <p class="text-center mt-3 mb-0">
            Nu ai cont? <a href="/register" class="auth-link">Inregistreaza-te</a>
        </p>
    </div>
</div>
<?php include __DIR__ . '/footer.php'; ?>