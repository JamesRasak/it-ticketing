<div class="row justify-content-center">
  <div class="col-md-5">
    <h3 class="mb-3">Register</h3>
    <form method="post" action="/register" novalidate>
      <input type="hidden" name="csrf" value="<?= htmlspecialchars(\App\csrf_token()) ?>">
      <div class="mb-3">
        <label class="form-label">Full name</label>
        <input type="text" name="name" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" minlength="8" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Confirm Password</label>
        <input type="password" name="password_confirmation" class="form-control" minlength="8" required>
      </div>
      <button class="btn btn-primary">Create account</button>
    </form>
  </div>
</div>
