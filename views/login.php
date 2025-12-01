<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card form-container">
            <div class="card-header bg-success text-white">
                <h2 class="card-title mb-0">
                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                </h2>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <h5 class="alert-heading">Error al iniciar sesión:</h5>
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" id="loginForm">
                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                    <?php if (!empty($redirect)): ?>
                        <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">
                    <?php endif; ?>

                    <!-- Usuario/Email -->
                    <div class="mb-3">
                        <label for="username" class="form-label required">Usuario o Email</label>
                        <input type="text" class="form-control" id="username" name="username" 
                               value="<?php echo htmlspecialchars($form_data['username']); ?>" 
                               required autofocus>
                        <div class="form-text">Puedes usar tu nombre de usuario o email</div>
                    </div>

                    <!-- Contraseña -->
                    <div class="mb-3">
                        <label for="password" class="form-label required">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div class="form-text">
                            <a href="forgot_password.php">¿Olvidaste tu contraseña?</a>
                        </div>
                    </div>

                    <!-- Recordarme -->
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me" 
                               <?php echo $form_data['remember_me'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="remember_me">
                            Recordar mi sesión
                        </label>
                    </div>

                    <!-- Botones -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                        </button>
                        <a href="register.php" class="btn btn-outline-primary">
                            <i class="fas fa-user-plus"></i> ¿No tienes cuenta? Regístrate
                        </a>
                    </div>

                    <!-- Cuenta de demostración -->
                    <div class="mt-4 p-3 bg-light rounded">
                        <h6><i class="fas fa-info-circle"></i> Cuenta de Demo</h6>
                        <p class="mb-1 small">Usuario: <strong>admin</strong></p>
                        <p class="mb-0 small">Contraseña: <strong>admin123</strong></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>