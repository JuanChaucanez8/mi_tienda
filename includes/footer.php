    </div>

    <footer class="bg-dark text-light mt-5 py-4">
        <div class="container text-center">
            <p>&copy; <?php echo date('Y'); ?> Mi Tienda. Todos los derechos reservados.</p>
            <p class="text-muted">
                Desarrollado con PHP 8.x, PostgreSQL y Bootstrap 5
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Confirmación para eliminar productos
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.btn-delete');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    if (!confirm('¿Estás seguro de que quieres eliminar este producto? Esta acción no se puede deshacer.')) {
                        e.preventDefault();
                    }
                });
            });

            // Auto-ocultar alerts después de 5 segundos
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
    </script>
</body>
</html>