<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card form-container">
            <div class="card-header bg-primary text-white">
                <h2 class="card-title mb-0">
                    <i class="fas fa-user-plus"></i> Crear Cuenta
                </h2>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <h5 class="alert-heading">Errores en el registro:</h5>
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" id="registerForm">
                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <!-- Nombre de usuario -->
                            <div class="mb-3">
                                <label for="username" class="form-label required">Nombre de Usuario</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo htmlspecialchars($form_data['username']); ?>" 
                                       minlength="3" maxlength="50" required
                                       pattern="[a-zA-Z0-9_]+"
                                       title="Solo letras, números y guiones bajos">
                                <div class="form-text">3-50 caracteres. Solo letras, números y _</div>
                            </div>

                            <!-- Nombre -->
                            <div class="mb-3">
                                <label for="first_name" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="<?php echo htmlspecialchars($form_data['first_name']); ?>" 
                                       maxlength="100">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label required">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($form_data['email']); ?>" 
                                       required>
                                <div class="form-text">Usaremos este email para contactarte</div>
                            </div>

                            <!-- Apellido -->
                            <div class="mb-3">
                                <label for="last_name" class="form-label">Apellido</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="<?php echo htmlspecialchars($form_data['last_name']); ?>" 
                                       maxlength="100">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <!-- Contraseña -->
                            <div class="mb-3">
                                <label for="password" class="form-label required">Contraseña</label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       minlength="8" required>
                                <div class="form-text">Mínimo 8 caracteres</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <!-- Confirmar contraseña -->
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label required">Confirmar Contraseña</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                       minlength="8" required>
                            </div>
                        </div>
                    </div>

                    <!-- Términos y condiciones -->
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                        <label class="form-check-label" for="terms">
                            Acepto los <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">términos y condiciones</a>
                        </label>
                    </div>

                    <!-- Botones -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-user-plus"></i> Crear Cuenta
                        </button>
                        <a href="login.php" class="btn btn-outline-secondary">
                            <i class="fas fa-sign-in-alt"></i> ¿Ya tienes cuenta? Inicia Sesión
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Términos y Condiciones -->
<div class="modal fade" id="termsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Términos y Condiciones</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>1. Aceptación de los Términos</h6>
                <p>Al registrarte en Mi Tienda, aceptas cumplir con estos términos y condiciones.</p>
                
                <h6>2. Uso del Servicio</h6>
                <p>Te comprometes a usar el servicio de manera responsable y a no publicar contenido inapropiado.</p>
                
                <h6>3. Propiedad Intelectual</h6>
                <p>Eres responsable del contenido que publiques y garantizas que tienes los derechos necesarios.</p>
                
                <h6>4. Privacidad</h6>
                <p>Respetamos tu privacidad y protegemos tus datos personales según nuestra política de privacidad.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
// Validación de contraseñas coincidentes
document.getElementById('registerForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const terms = document.getElementById('terms').checked;
    
    if (password !== confirmPassword) {
        alert('Las contraseñas no coinciden');
        e.preventDefault();
        return;
    }
    
    if (!terms) {
        alert('Debes aceptar los términos y condiciones');
        e.preventDefault();
        return;
    }
    
    if (password.length < 8) {
        alert('La contraseña debe tener al menos 8 caracteres');
        e.preventDefault();
        return;
    }
});

// Validación en tiempo real de contraseñas
document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    
    if (confirmPassword && password !== confirmPassword) {
        this.classList.add('is-invalid');
        this.classList.remove('is-valid');
    } else if (confirmPassword) {
        this.classList.add('is-valid');
        this.classList.remove('is-invalid');
    } else {
        this.classList.remove('is-valid', 'is-invalid');
    }
});
</script>