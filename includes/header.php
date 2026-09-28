<?php
/**
 * ARCHIVO: includes/header.php
 * Inyección de dependencias CSS, tipografías y metaetiquetas globales para el <head>.
 * Diseñado para ser absorbido por los enrutadores principales (index.php) de cada rol.
 */
?>
<!-- Iconografía Oficial FontAwesome 6.0.0 (Cloudflare) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<!-- Hoja de Estilos Globales UPDS.NET v5.1 -->
<style>
    /* Tipografía y fondo base para toda la plataforma */
    body {
        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        background-color: #f4f6f9;
        -webkit-font-smoothing: antialiased;
    }

    /* ---------------------------------------------------
       IDENTIDAD VISUAL INSTITUCIONAL
       --------------------------------------------------- */
    .text-institucional { 
        color: #0B427B !important; 
    }
    .bg-institucional { 
        background-color: #0B427B !important; 
        color: #ffffff !important; 
    }
    .border-institucional {
        border-color: #0B427B !important;
    }
    
    /* Botón estandarizado de la sede */
    .btn-institucional {
        background-color: #0B427B;
        color: white;
        border: none;
    }
    .btn-institucional:hover {
        background-color: #08335e;
        color: white;
    }

    /* Bordes planos para mantener la estética limpia (Bootstrap 5 adaptado) */
    .border-plano { 
        border-radius: 0 !important; 
    }

    /* ---------------------------------------------------
       COMPORTAMIENTO UI/UX (Tarjetas SPA)
       --------------------------------------------------- */
    /* Efecto de elevación sutil para los menús de inicio (basado en el prototipo) */
    .hover-elevate {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    .hover-elevate:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
    }

    /* ---------------------------------------------------
       SCROLLBAR DISCRETO (Para paneles internos)
       --------------------------------------------------- */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }
    ::-webkit-scrollbar-track {
        background: #f8f9fa; 
    }
    ::-webkit-scrollbar-thumb {
        background: #ced4da; 
        border-radius: 4px;
    }
    ::-webkit-scrollbar-thumb:hover {
        background: #0B427B; 
    }
</style>