<?php /* @var $title string */ ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title ?? 'IT Ticketing') ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="/assets/css/app.css" rel="stylesheet">
  <style>
    /* Override Bootstrap's default active nav-link color */
    .navbar-nav .nav-link.active, .navbar-nav .nav-link.show {
        color: #0d6efd; /* Bootstrap's primary blue color */
        border-color: #0d6efd;
    }
    /* Style nav items to look like buttons */
    .navbar-nav .nav-item {
        margin: 0 0.2rem;
    }
    .navbar-nav .nav-link {
        border: 1px solid transparent; /* Start with transparent border */
        border-radius: 0.375rem; /* Bootstrap's default border-radius */
        transition: border-color 0.15s ease-in-out;
    }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg bg-light border-bottom mb-4">
  <div class="container">
    <a class="navbar-brand" href="/">IT Ticketing</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
        <?php if (!empty($_SESSION['user'])): ?>
        <?php
          $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
          $isAllTicketsActive = ($requestUri === '/' || (str_starts_with($requestUri, '/tickets') && !str_starts_with($requestUri, '/tickets/create')));
        ?>
        <li class="nav-item"><a class="nav-link <?= $isAllTicketsActive ? 'active' : '' ?>" href="/tickets">All Tickets</a></li>
        <li class="nav-item"><a class="nav-link <?= $_SERVER['REQUEST_URI'] === '/tickets/create' ? 'active' : '' ?>" href="/tickets/create">Create Ticket</a></li>
        <?php endif; ?>
      </ul>
      <ul class="navbar-nav ms-auto">
        <?php if (empty($_SESSION['user'])): ?>
        <li class="nav-item"><a class="nav-link <?= $_SERVER['REQUEST_URI'] === '/login' ? 'active' : '' ?>" href="/login">Login</a></li>
        <li class="nav-item"><a class="btn btn-sm btn-outline-secondary" href="/register">Register</a></li>
        <?php else: ?>
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <?= htmlspecialchars($_SESSION['user']['name']) ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><h6 class="dropdown-header">Role: <?= htmlspecialchars($_SESSION['user']['role']) ?></h6></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="post" action="/logout" class="d-inline">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars(\App\csrf_token()) ?>">
                        <button class="dropdown-item">Logout</button>
                    </form>
                </li>
            </ul>
        </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<div class="container mt-5">
  <?php $flash = $flash ?? ($_SESSION['flash'] ?? null); ?>
  <?php if (!empty($flash)): ?>
    <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
  <?php endif; ?>

  <?php require __DIR__ . '/' . $template; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/app.js"></script>
</body>
</html>
