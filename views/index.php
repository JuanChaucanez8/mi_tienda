<div class="row mb-4">
    <div class="col-md-6">
        <h1>
            <i class="fas fa-boxes"></i> Productos
            <?php if ($search): ?>
                <small class="text-muted">Búsqueda: "<?php echo htmlspecialchars($search); ?>"</small>
            <?php endif; ?>
        </h1>
    </div>
    <div class="col-md-6 text-end">
        <a href="add_product.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Agregar Producto
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="category" class="form-label">Filtrar por categoría:</label>
                <select name="category" id="category" class="form-select" onchange="this.form.submit()">
                    <option value="">Todas las categorías</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>" 
                            <?php echo $category === $cat ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-8">
                <label for="search" class="form-label">Buscar productos:</label>
                <div class="input-group">
                    <input type="text" class="form-control" name="search" id="search" 
                           value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Buscar por nombre, descripción o categoría...">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                    <?php if ($search || $category): ?>
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Limpiar
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Estadísticas -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> 
            Mostrando <?php echo count($products); ?> de <?php echo $total_products; ?> productos encontrados
            <?php if ($search): ?>
                para "<?php echo htmlspecialchars($search); ?>"
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Lista de productos -->
<?php if (empty($products)): ?>
    <div class="alert alert-warning text-center">
        <i class="fas fa-exclamation-triangle"></i>
        No se encontraron productos.
        <?php if ($search || $category): ?>
            <br>
            <a href="index.php" class="btn btn-outline-primary mt-2">Ver todos los productos</a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="row">
        <?php foreach ($products as $product): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <?php if ($product['image_path']): ?>
                        <img src="<?php echo htmlspecialchars($product['image_path']); ?>" 
                             class="card-img-top product-image" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                             onerror="this.style.display='none'">
                    <?php else: ?>
                        <div class="card-img-top product-image bg-light d-flex align-items-center justify-content-center">
                            <i class="fas fa-image fa-3x text-muted"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                        
                        <?php if ($product['category']): ?>
                            <span class="badge bg-primary badge-category mb-2">
                                <?php echo htmlspecialchars($product['category']); ?>
                            </span>
                        <?php endif; ?>
                        
                        <p class="card-text flex-grow-1">
                            <?php 
                            $description = $product['description'];
                            if (strlen($description) > 100) {
                                $description = substr($description, 0, 100) . '...';
                            }
                            echo htmlspecialchars($description);
                            ?>
                        </p>
                        
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="price">$<?php echo number_format($product['price'], 2); ?></span>
                                <span class="stock <?php 
                                    echo $product['stock'] > 10 ? 'stock-in' : 
                                         ($product['stock'] > 0 ? 'stock-low' : 'stock-out'); ?>">
                                    <?php echo $product['stock'] > 0 ? 
                                        $product['stock'] . ' en stock' : 'Agotado'; ?>
                                </span>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <a href="product.php?id=<?php echo $product['id']; ?>" 
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye"></i> Ver Detalles
                                </a>
                                <div class="btn-group w-100">
                                    <a href="edit_product.php?id=<?php echo $product['id']; ?>" 
                                       class="btn btn-outline-warning btn-sm">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    <a href="delete_product.php?id=<?php echo $product['id']; ?>" 
                                       class="btn btn-outline-danger btn-sm btn-delete">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer text-muted">
                        <small>
                            <i class="fas fa-calendar"></i> 
                            Creado: <?php echo date('d/m/Y', strtotime($product['created_at'])); ?>
                        </small>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Paginación -->
    <?php if ($pagination && $pagination['total_pages'] > 1): ?>
        <nav aria-label="Paginación de productos">
            <ul class="pagination justify-content-center">
                <?php if ($pagination['has_previous']): ?>
                    <li class="page-item">
                        <a class="page-link" 
                           href="?page=<?php echo $pagination['current_page'] - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category ? '&category=' . urlencode($category) : ''; ?>">
                            <i class="fas fa-chevron-left"></i> Anterior
                        </a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                    <li class="page-item <?php echo $i === $pagination['current_page'] ? 'active' : ''; ?>">
                        <a class="page-link" 
                           href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category ? '&category=' . urlencode($category) : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <?php if ($pagination['has_next']): ?>
                    <li class="page-item">
                        <a class="page-link" 
                           href="?page=<?php echo $pagination['current_page'] + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category ? '&category=' . urlencode($category) : ''; ?>">
                            Siguiente <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
<?php endif; ?>