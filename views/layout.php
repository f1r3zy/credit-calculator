<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calculator de Credite</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
  <div class="container">
    <a class="navbar-brand" href="/">CreditCalc</a>
    <div class="navbar-nav ms-auto">
  <?php if (\App\Core\Auth::check()): ?>
    <a class="nav-link internal-link" href="/calculator">Calculator</a>
    <a class="nav-link internal-link" href="/profile">Profil</a>
    <a class="nav-link internal-link" href="/simulations">Simulari</a>
    <a class="nav-link" href="#" id="logoutBtn">Logout</a>
  <?php else: ?>
    <a class="nav-link internal-link" href="/login">Login</a>
    <a class="nav-link internal-link" href="/register">Inregistrare</a>
  <?php endif; ?>
    </div>
  </div>
</nav>
<div class="page-fade-in">