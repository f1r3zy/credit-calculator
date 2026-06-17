<?php include __DIR__ . '/layout.php'; ?>
<div class="auth-container">
    <div class="auth-card">
        <h2 class="text-center mb-4">Înregistrare</h2>
        <form id="register-form" method="post">
            <?= \App\Core\Csrf::field() ?>
            <div class="mb-3">
                <label class="form-label fw-semibold">Nume</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Parolă</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success w-100">Creează cont</button>
        </form>
        <p class="text-center mt-3 mb-0">
            Ai deja cont? <a href="/login" class="auth-link">Autentifica-te</a>
        </p>
    </div>
</div>
<?php include __DIR__ . '/footer.php'; ?>