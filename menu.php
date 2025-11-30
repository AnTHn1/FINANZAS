<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<nav class="navbar">
    <div class="nav-left">
        <a href="/FINANZAS/landingpage.php" class="nav-link logo">
            🏠 MiVivienda
        </a>

        <?php 
        if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
            <a href="/FINANZAS/Crud_Viviendas/listar_viviendas.php" class="nav-link">Viviendas</a>
            <a href="/FINANZAS/Crud_Clientes/listar_cliente.php" class="nav-link">Clientes</a>
            <a href="/FINANZAS/Crud_Credito/listar_solicitudes.php" class="nav-link">Bonos Solicitados</a>
        <!--<a href="/FINANZAS/Crud_Credito/credito_cliente.php" class="nav-link">Créditos</a>  -->
        <?php elseif (isset($_SESSION['rol']) && $_SESSION['rol'] === 'cliente'): ?>
            <a href="/FINANZAS/Crud_Viviendas/listar_viviendas.php" class="nav-link">Viviendas</a>
            <!-- aquí puedes agregar más links para clientes -->
        <?php endif; ?>
    </div>

    <div class="nav-right">
        <?php if (!isset($_SESSION['usuario_id'])): ?>
            <a href="/FINANZAS/Auth/login.php" class="login-btn">Iniciar sesión</a>
        <?php else: ?>
            <a href="/FINANZAS/mi_perfil.php" class="user-info" style="cursor:pointer; color:#ffeb3b; text-decoration:none;">
                <?= htmlspecialchars($_SESSION['username']) ?>
            </a>
            <a href="/FINANZAS/Auth/logout.php" class="logout-btn">Cerrar sesión</a>
        <?php endif; ?>
    </div>
</nav>

<style>
    /* ==== Estilos generales ==== */

    .login-btn {
    background: #ffcc00;
    color: #000;
    padding: 8px 16px;
    border-radius: 5px;
    font-weight: bold;
    text-decoration: none;
    transition: background 0.2s, transform 0.2s;
}

.login-btn:hover {
    background: #e6b800;
    transform: scale(1.05);
}

    body {
        margin: 0;
        font-family: "Segoe UI", Roboto, sans-serif;
    }

    .navbar {
        background: linear-gradient(90deg, #1a73e8, #0d47a1);
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 14px 40px;
        position: sticky;
        top: 0;
        z-index: 1000;
        box-shadow: 0 3px 6px rgba(0,0,0,0.2);
    }

    .nav-left, .nav-right {
        display: flex;
        align-items: center;
        gap: 25px;
    }

    .nav-link {
        color: white;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.2s, transform 0.2s;
        font-size: 16px;
    }

    .nav-link:hover {
        color: #ffeb3b;
        transform: scale(1.05);
    }

    .logo {
        font-weight: 700;
        font-size: 18px;
    }

    .user-info {
        font-weight: bold;
        margin-right: 10px;
    }

    .logout-btn {
        background: #e74c3c;
        color: white;
        text-decoration: none;
        padding: 6px 14px;
        border-radius: 5px;
        font-weight: bold;
        transition: background 0.2s;
    }

    .logout-btn:hover {
        background: #c0392b;
    }

    @media (max-width: 768px) {
        .navbar {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }

        .nav-left, .nav-right {
            flex-wrap: wrap;
            gap: 15px;
        }
    }
</style>
