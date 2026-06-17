<?php
include __DIR__ . '/layout.php';
use App\Core\Auth;
$user = Auth::user();
if (!$user) {
    header('Location: /login');
    exit;
}
?>
<div class="container mt-4">
    <h2>Profilul meu</h2>
    <p><strong>Nume:</strong> <?= htmlspecialchars($user->name) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($user->email) ?></p>
    <a href="/simulations" class="btn btn-outline-primary">Simularile mele</a>
</div>
</body>
</html>
<?php include __DIR__ . '/footer.php'; ?>