<div class="row">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($product['name']); ?></li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <?php if ($product['image_path']): ?>
            <img src="<?php echo htmlspecialchars($product['image_path']); ?>" 
                 class="img-fluid rounded" 
                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                 style="max-height: 400px; width: 100%; object-fit: cover;">
        <?php else: ?>
            <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                 style="height: 400px;">
                <i class="fas fa-image fa-5x text-muted"></i>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h1 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                
                <?php if ($product['category']): ?>
                    <span class="badge bg-primary fs-6 mb-3">
                        <?php echo htmlspecialchars($product['category']); ?>
                    </span>
                <?php endif; ?>
                
                <h2 class="text-success mb-3">$<?php echo number_format($product['price'], 2); ?></h2>
                
                <div class="mb-3">
                    <span class="stock <?php 
                        echo $product['stock'] > 10 ? 'stock-in' : 
                             ($product['stock'] > 0 ? 'stock-low' : 'stock-out'); ?> fs-5">
                        <i class="fas fa-box"></i>
                        <?php echo $product['stock'] > 0 ? 
                            $product['stock'] . ' unidades en stock' : 'Producto agotado'; ?>
                    </span>
                </div>
                
                <div class="mb-4">
                    <h4>Descripción</h4>
                    <p class="card-text"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                </div>
                <div class="mb-3">
    <h5>Publicado por:</h5>
    <div class="d-flex align-items-center">
        <i class="fas fa-user me-2 text-muted"></i>
        <div>
            <strong><?php echo format_author_name($product); ?></strong>
            <?php if ($product['author_username']): ?>
                <br>
                <small class="text-muted">@<?php echo htmlspecialchars($product['author_username']); ?></small>
            <?php endif; ?>
        </div>
    </div>
</div>
                <div class="row text-muted mb-3">
                    <div class="col-6">
                        <small>
                            <i class="fas fa-calendar-plus"></i> Creado:<br>
                            <?php echo date('d/m/Y H:i', strtotime($product['created_at'])); ?>
                        </small>
                    </div>
                    <?php if ($product['updated_at']): ?>
                        <div class="col-6">
                            <small>
                                <i class="fas fa-calendar-check"></i> Actualizado:<br>
                                <?php echo date('d/m/Y H:i', strtotime($product['updated_at'])); ?>
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                    <a href="edit_product.php?id=<?php echo $product['id']; ?>" 
                       class="btn btn-warning me-md-2">
                        <i class="fas fa-edit"></i> Editar Producto
                    </a>
                    <a href="delete_product.php?id=<?php echo $product['id']; ?>" 
                       class="btn btn-danger btn-delete">
                        <i class="fas fa-trash"></i> Eliminar Producto
                    </a>
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>