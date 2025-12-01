<?php
require_once 'includes/functions.php';

// Destruir sesión
session_start();
session_destroy();

// Redirigir al login con mensaje
redirect('login.php', 'Has cerrado sesión correctamente. ¡Vuelve pronto!');
?>