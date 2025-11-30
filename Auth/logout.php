<?php
session_start();

// Destruir toda la sesión
session_unset();
session_destroy();

// Redirigir al landing page
header("Location: /FINANZAS/landingpage.php");
exit;
?>
