
<div class="row justify-content-center">
  <div class="col-md-4">
    <h3 class="mb-3">Login</h3>
    <form method="post" action="/login" novalidate>
      <input type="hidden" name="csrf" value="<?= htmlspecialchars(\App\csrf_token()) ?>">
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <button class="btn btn-primary">Sign in</button>
    </form>
  </div>
</div>
