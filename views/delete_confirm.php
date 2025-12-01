<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h2 class="card-title mb-0">
                    <i class="fas fa-exclamation-triangle"></i> Confirmar Eliminación
                </h2>
            </div>
            <div class="card-body text-center">
                <div class="alert alert-warning">
                    <h4 class="alert-heading">¡Advertencia!</h4>
                    <p>Estás a punto de eliminar el siguiente producto. Esta acción no se puede deshacer.</p>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                        <p class="card-text">
                            <strong>Precio:</strong> $<?php echo number_format($product['price'], 2); ?><br>
                            <strong>Stock:</strong> <?php echo $product['stock']; ?> unidades<br>
                            <?php if ($product['category']): ?>
                                <strong>Categoría:</strong> <?php echo htmlspecialchars($product['category']); ?>
                            <?php endif; ?>
                        </p>
                        <?php if ($product['image_path']): ?>
                            <img src="<?php echo htmlspecialchars($product['image_path']); ?>" 
                                 class="img-thumbnail" style="max-height: 150px;">
                        <?php endif; ?>
                    </div>
                </div>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <a href="product.php?id=<?php echo $product['id']; ?>" 
                           class="btn btn-outline-secondary me-md-2">
                            <i class="fas fa-arrow-left"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Confirmar Eliminación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>