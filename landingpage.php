<?php
require_once "Auth/auth.php"; // protege la página
include "conexion.php";
include("menu.php");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>MiVivienda - Inicio</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Arial, sans-serif;
    }

    body {
      background-color: #f4f7fa;
      color: #333;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    /* ===== HERO / SECCIÓN PRINCIPAL ===== */
    .hero {
      display: flex;
      justify-content: center;
      align-items: center;
      background-color: #007bff;
      color: #fff;
      padding: 60px 40px;
      height: 70vh;
    }

    .hero-container {
      display: flex;
      justify-content: space-between;
      align-items: center;
      width: 90%;
      max-width: 1100px;
      background-color: #0056b3;
      border-radius: 15px;
      padding: 40px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    .hero-text {
      flex: 1;
      padding-right: 30px;
    }

    .hero-text h1 {
      font-size: 3em;
      margin-bottom: 15px;
    }

    .hero-text p {
      font-size: 1.1em;
      margin-bottom: 25px;
      line-height: 1.5em;
    }

    .hero-text .btn {
      background-color: #ffcc00;
      color: #000;
      padding: 12px 25px;
      border: none;
      border-radius: 8px;
      text-decoration: none;
      font-weight: bold;
      transition: background 0.3s;
    }

    .hero-text .btn:hover {
      background-color: #e6b800;
    }

    .hero-img {
      flex: 1;
      text-align: center;
    }

    .hero-img img {
      width: 90%;
      max-width: 450px;
      border-radius: 10px;
    }

    /* ===== SECCIÓN PREGUNTAS ===== */
    .faq-section {
      text-align: center;
      padding: 70px 30px;
      background-color: #fff;
    }

    .faq-section h2 {
      color: #0056b3;
      font-size: 2.2em;
      margin-bottom: 50px;
    }

    .faq-container {
      display: flex;
      justify-content: center;
      flex-wrap: wrap;
      gap: 25px;
      max-width: 1100px;
      margin: 0 auto;
    }

    .faq-card {
      background-color: #f8f9fa;
      border: 1px solid #ddd;
      border-radius: 12px;
      padding: 25px;
      text-align: center;
      width: 320px;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .faq-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    }

    .faq-card img {
      width: 100%;
      height: 180px;
      object-fit: cover;
      border-radius: 8px;
      margin-bottom: 15px;
    }

    .faq-card h3 {
      margin-bottom: 12px;
      color: #007bff;
      font-size: 1.2em;
    }

    .faq-card p {
      color: #555;
      font-size: 0.95em;
      line-height: 1.5em;
    }

    /* ===== FOOTER ===== */
    footer {
      background-color: #00264d;
      color: #fff;
      text-align: center;
      padding: 25px 15px;
      margin-top: auto;
    }

    footer .footer-content {
      max-width: 1000px;
      margin: 0 auto;
    }

    footer h4 {
      font-size: 1.4em;
      margin-bottom: 10px;
      color: #ffcc00;
    }

    footer p {
      font-size: 0.95em;
      margin-bottom: 8px;
    }

    footer a {
      color: #ffcc00;
      text-decoration: none;
      margin: 0 5px;
    }

    footer a:hover {
      text-decoration: underline;
    }

    @media (max-width: 900px) {
      .hero-container {
        flex-direction: column;
        text-align: center;
      }
      .hero-text {
        padding-right: 0;
      }
      .faq-container {
        flex-direction: column;
        align-items: center;
      }
      .faq-card {
        width: 90%;
      }
    }
  </style>
</head>
<body>

  <!-- ===== HERO SECTION ===== -->
  <section class="hero">
    <div class="hero-container">
      <div class="hero-text">
        <h1>Bienvenido a <b>MiVivienda</b></h1>
        <p>Encuentra tu hogar ideal con los mejores beneficios, bonos y planes de financiamiento. 
        ¡Conoce todas nuestras opciones y haz realidad el sueño de la casa propia!</p>
        <a href="/FINANZAS/Crud_Viviendas/listar_viviendas.php" class="btn">Ver todas las viviendas</a>
      </div>

      <div class="hero-img">
        <img src="img/vivienda_home.jpg" alt="Imagen de vivienda" />
      </div>
    </div>
  </section>

  <!-- ===== FAQ SECTION ===== -->
  <section class="faq-section">
    <h2>Preguntas Frecuentes</h2>

    <div class="faq-container">
      <div class="faq-card">
        <img src="img/faq1.jpg" alt="Fondo MiVivienda">
        <h3>¿Qué es el Fondo MIVIVIENDA y cómo funciona?</h3>
        <p>El Fondo MIVIVIENDA promueve el acceso a la vivienda a través de créditos con tasas preferenciales y el Bono del Buen Pagador.</p>
      </div>

      <div class="faq-card">
        <img src="img/faq2.jpg" alt="Información del mercado inmobiliario">
        <h3>Información del mercado inmobiliario y estadísticas</h3>
        <p>Consulta los precios promedio de viviendas, la evolución del mercado y los beneficios financieros actualizados.</p>
      </div>

      <div class="faq-card">
        <img src="img/faq3.jpg" alt="Oficinas y contacto">
        <h3>Oficinas y contacto telefónico</h3>
        <p>Encuentra nuestras oficinas a nivel nacional o comunícate con nosotros para recibir asesoría personalizada.</p>
      </div>
    </div>
  </section>

  <!-- ===== FOOTER ===== -->
  <footer>
    <div class="footer-content">
      <h4>MiVivienda</h4>
      <p>© 2025 MiVivienda. Todos los derechos reservados.</p>
      <p>
        <a href="#">Términos de uso</a> | 
        <a href="#">Política de privacidad</a> | 
        <a href="contacto.php">Contáctanos</a>
      </p>
    </div>
  </footer>

</body>
</html>
