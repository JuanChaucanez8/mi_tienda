<div class="row">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                <li class="breadcrumb-item active">
                    <?php echo $is_edit ? 'Editar Producto' : 'Agregar Producto'; ?>
                </li>
            </ol>
        </nav>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card form-container">
            <div class="card-header">
                <h2 class="card-title mb-0">
                    <i class="fas <?php echo $is_edit ? 'fa-edit' : 'fa-plus'; ?>"></i>
                    <?php echo $is_edit ? 'Editar Producto' : 'Agregar Nuevo Producto'; ?>
                </h2>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <h5 class="alert-heading">Errores:</h5>
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" id="productForm">
                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                    
                    <?php if ($is_edit): ?>
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6">
                            <!-- Nombre -->
                            <div class="mb-3">
                                <label for="name" class="form-label required">Nombre del Producto</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo htmlspecialchars($form_data['name']); ?>" 
                                       maxlength="255" required>
                                <div class="form-text">Máximo 255 caracteres</div>
                            </div>

                            <!-- Precio -->
                            <div class="mb-3">
                                <label for="price" class="form-label required">Precio ($)</label>
                                <input type="number" class="form-control" id="price" name="price" 
                                       value="<?php echo htmlspecialchars($form_data['price']); ?>" 
                                       step="0.01" min="0" required>
                                <div class="form-text">Precio en dólares. Ej: 99.99</div>
                            </div>

                            <!-- Stock -->
                            <div class="mb-3">
                                <label for="stock" class="form-label required">Stock</label>
                                <input type="number" class="form-control" id="stock" name="stock" 
                                       value="<?php echo htmlspecialchars($form_data['stock']); ?>" 
                                       min="0" required>
                                <div class="form-text">Cantidad disponible en inventario</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- Categoría -->
                            <div class="mb-3">
                                <label for="category" class="form-label">Categoría</label>
                                <input type="text" class="form-control" id="category" name="category" 
                                       value="<?php echo htmlspecialchars($form_data['category']); ?>" 
                                       maxlength="100">
                                <div class="form-text">Ej: Electrónicos, Ropa, Hogar</div>
                            </div>

                            <!-- Imagen -->
                            <div class="mb-3">
                                <label for="image" class="form-label">Imagen del Producto</label>
                                <input type="file" class="form-control" id="image" name="image" 
                                       accept="image/jpeg,image/png,image/gif">
                                <div class="form-text">
                                    Formatos: JPG, PNG, GIF. Tamaño máximo: 5MB
                                </div>
                                
                                <?php if ($is_edit && $form_data['current_image']): ?>
                                    <div class="mt-2">
                                        <img src="<?php echo htmlspecialchars($form_data['current_image']); ?>" 
                                             class="img-thumbnail upload-preview" 
                                             style="max-height: 150px;">
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="delete_image" id="delete_image">
                                            <label class="form-check-label" for="delete_image">
                                                Eliminar imagen actual
                                            </label>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <img src="" class="img-thumbnail upload-preview mt-2" 
                                         style="max-height: 150px; display: none;">
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Descripción -->
                    <div class="mb-3">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea class="form-control" id="description" name="description" 
                                  rows="5"><?php echo htmlspecialchars($form_data['description']); ?></textarea>
                        <div class="form-text">Descripción detallada del producto</div>
                    </div>

                    <!-- Botones -->
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="<?php echo $is_edit ? 'product.php?id=' . $product_id : 'index.php'; ?>" 
                           class="btn btn-outline-secondary me-md-2">
                            <i class="fas fa-arrow-left"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> 
                            <?php echo $is_edit ? 'Actualizar Producto' : 'Guardar Producto'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Vista previa de imagen
document.getElementById('image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.querySelector('.upload-preview');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
    }
});

// Validación de formulario
document.getElementById('productForm').addEventListener('submit', function(e) {
    const price = document.getElementById('price').value;
    const stock = document.getElementById('stock').value;
    
    if (price < 0) {
        alert('El precio no puede ser negativo');
        e.preventDefault();
        return;
    }
    
    if (stock < 0 || !Number.isInteger(parseFloat(stock))) {
        alert('El stock debe ser un número entero no negativo');
        e.preventDefault();
        return;
    }
});
</script>