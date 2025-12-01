<div class="row">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                <li class="breadcrumb-item active">Mis Productos</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <h1>
            <i class="fas fa-box"></i> Mis Productos
        </h1>
        <p class="text-muted">Gestiona los productos que has publicado</p>
    </div>
    <div class="col-md-6 text-end">
        <a href="add_product.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Agregar Nuevo Producto
        </a>
        <a href="index.php" class="btn btn-outline-secondary">
            <i class="fas fa-store"></i> Ver Todos los Productos
        </a>
    </div>
</div>

<!-- Estadísticas rápidas -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h3><?php echo $total_products; ?></h3>
                <p class="mb-0">Total Productos</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h3><?php echo array_sum(array_column($products, 'stock')); ?></h3>
                <p class="mb-0">Unidades en Stock</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h3>$<?php echo number_format(array_sum(array_column($products, 'price')), 2); ?></h3>
                <p class="mb-0">Valor Total</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body text-center">
                <h3><?php echo count(array_filter($products, function($p) { return $p['stock'] == 0; })); ?></h3>
                <p class="mb-0">Agotados</p>
            </div>
        </div>
    </div>
</div>

<?php if (empty($products)): ?>
    <div class="alert alert-info text-center">
        <i class="fas fa-info-circle fa-2x mb-3"></i>
        <h4>No has publicado ningún producto todavía</h4>
        <p>Comienza a agregar productos para vender en la tienda.</p>
        <a href="add_product.php" class="btn btn-primary btn-lg">
            <i class="fas fa-plus"></i> Publicar Mi Primer Producto
        </a>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-list"></i> Lista de Mis Productos (<?php echo $total_products; ?>)
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Estado</th>
                            <th>Publicado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if ($product['image_path']): ?>
                                            <img src="<?php echo htmlspecialchars($product['image_path']); ?>" 
                                                 class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;"
                                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                 onerror="this.style.display='none'">
                                        <?php endif; ?>
                                        <div>
                                            <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                            <?php if (strlen($product['description']) > 50): ?>
                                                <br><small class="text-muted"><?php echo htmlspecialchars(substr($product['description'], 0, 50)); ?>...</small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($product['category']): ?>
                                        <span class="badge bg-primary"><?php echo htmlspecialchars($product['category']); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">Sin categoría</span>
                                    <?php endif; ?>
                                </td>
                                <td class="price">$<?php echo number_format($product['price'], 2); ?></td>
                                <td>
                                    <span class="stock <?php 
                                        echo $product['stock'] > 10 ? 'stock-in' : 
                                             ($product['stock'] > 0 ? 'stock-low' : 'stock-out'); ?>">
                                        <?php echo $product['stock']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($product['stock'] > 10): ?>
                                        <span class="badge bg-success">Disponible</span>
                                    <?php elseif ($product['stock'] > 0): ?>
                                        <span class="badge bg-warning text-dark">Stock Bajo</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Agotado</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?php echo date('d/m/Y', strtotime($product['created_at'])); ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="product.php?id=<?php echo $product['id']; ?>" 
                                           class="btn btn-outline-primary" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="edit_product.php?id=<?php echo $product['id']; ?>" 
                                           class="btn btn-outline-warning" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="delete_product.php?id=<?php echo $product['id']; ?>" 
                                           class="btn btn-outline-danger btn-delete" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Paginación -->
    <?php if ($pagination['total_pages'] > 1): ?>
        <nav aria-label="Paginación de productos" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($pagination['has_previous']): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $pagination['current_page'] - 1; ?>">
                            <i class="fas fa-chevron-left"></i> Anterior
                        </a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                    <li class="page-item <?php echo $i === $pagination['current_page'] ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <?php if ($pagination['has_next']): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $pagination['current_page'] + 1; ?>">
                            Siguiente <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
<?php endif; ?>