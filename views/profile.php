<div class="row">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                <li class="breadcrumb-item active">Mi Perfil</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <!-- Información del usuario -->
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user-circle"></i> Información de la Cuenta
                </h5>
            </div>
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="fas fa-user fa-3x text-muted"></i>
                </div>
                <h4><?php echo format_author_name($current_user); ?></h4>
                <p class="text-muted">@<?php echo htmlspecialchars($current_user['username']); ?></p>
                
                <hr>
                
                <div class="text-start">
                    <p><strong><i class="fas fa-envelope"></i> Email:</strong><br>
                    <?php echo htmlspecialchars($current_user['email']); ?></p>
                    
                    <p><strong><i class="fas fa-calendar-plus"></i> Miembro desde:</strong><br>
                    <?php echo date('d/m/Y', strtotime($current_user['created_at'])); ?></p>
                    
                    <?php if ($current_user['last_login']): ?>
                        <p><strong><i class="fas fa-sign-in-alt"></i> Último acceso:</strong><br>
                        <?php echo date('d/m/Y H:i', strtotime($current_user['last_login'])); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Estadísticas rápidas -->
        <div class="card mt-4">
            <div class="card-header bg-secondary text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-bar"></i> Mis Estadísticas
                </h5>
            </div>
            <div class="card-body">
                <?php
                $result = db_execute('get_user_products', [$current_user['id']]);
                $user_products = [];
                if ($result) {
                    while ($row = pg_fetch_assoc($result)) {
                        $user_products[] = $row;
                    }
                }
                $total_products = count($user_products);
                $total_value = 0;
                foreach ($user_products as $product) {
                    $total_value += $product['price'] * $product['stock'];
                }
                ?>
                <div class="text-center">
                    <h3 class="text-primary"><?php echo $total_products; ?></h3>
                    <p class="text-muted">Productos Publicados</p>
                    
                    <h3 class="text-success">$<?php echo number_format($total_value, 2); ?></h3>
                    <p class="text-muted">Valor Total en Inventario</p>
                </div>
                
                <div class="d-grid">
                    <a href="my_products.php" class="btn btn-outline-primary">
                        <i class="fas fa-box"></i> Ver Mis Productos
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <!-- Editar perfil -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user-edit"></i> Editar Perfil
                </h5>
            </div>
            <div class="card-body">
                <?php if ($success && isset($_POST['update_profile'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> Perfil actualizado correctamente.
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors) && isset($_POST['update_profile'])): ?>
                    <div class="alert alert-danger">
                        <h6 class="alert-heading">Errores:</h6>
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                    <input type="hidden" name="update_profile" value="1">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="first_name" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="<?php echo htmlspecialchars($form_data['first_name']); ?>" 
                                       maxlength="100">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="last_name" class="form-label">Apellido</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="<?php echo htmlspecialchars($form_data['last_name']); ?>" 
                                       maxlength="100">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label required">Email</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo htmlspecialchars($form_data['email']); ?>" 
                               required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nombre de Usuario</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($current_user['username']); ?>" 
                               disabled>
                        <div class="form-text">El nombre de usuario no se puede cambiar</div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Actualizar Perfil
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Cambiar contraseña -->
        <div class="card mt-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="card-title mb-0">
                    <i class="fas fa-lock"></i> Cambiar Contraseña
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($errors) && isset($_POST['change_password'])): ?>
                    <div class="alert alert-danger">
                        <h6 class="alert-heading">Errores:</h6>
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                    <input type="hidden" name="change_password" value="1">
                    
                    <div class="mb-3">
                        <label for="current_password" class="form-label required">Contraseña Actual</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label required">Nueva Contraseña</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" 
                               minlength="8" required>
                        <div class="form-text">Mínimo 8 caracteres</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label required">Confirmar Nueva Contraseña</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                               minlength="8" required>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-key"></i> Cambiar Contraseña
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>