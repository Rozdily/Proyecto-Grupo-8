<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Demostración de gestión de periodos y cohortes UPDS">
    <title>Periodos y Cohortes - UPDS</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous"
    >
</head>
<body class="vh-100 overflow-hidden d-flex flex-column bg-white">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm flex-shrink-0">
        <div class="container-fluid">
            <a class="navbar-brand fw-semibold" href="#">UPDS</a>
            <button
                class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#menuPrincipal"
                aria-controls="menuPrincipal"
                aria-expanded="false"
                aria-label="Mostrar navegación"
            >
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="menuPrincipal">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="index.html">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="usuarios_listar.html">Gestión de Usuarios</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="mg_importar.html">Importación SATS</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="cohortes_listar.php">Periodos y Cohortes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="mg_parametros.html">Parámetros del Sistema</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="auditoria_bitacora.html">Bitácora de Auditoría</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="defensas_agenda.html">Cronograma</a>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-3 text-white">
                    <div class="text-end small d-none d-sm-block">
                        <span class="d-block fw-semibold">Jonathan Arturo</span>
                        <span class="text-white-50">j.jonathan.delariva.m@upds.edu.bo</span>
                    </div>
                    <a class="btn btn-outline-light btn-sm" href="../index.php">Salir</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="container-fluid flex-grow-1 overflow-auto py-4">
        <div class="container">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
                <div>
                    <h1 class="h3 text-secondary fw-normal mb-1">
                        Estructuras Académicas
                    </h1>
                    <p class="text-secondary mb-0">
                        Periodos, cohortes y oferta universitaria
                    </p>
                </div>
                <nav aria-label="Migas de pan">
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item">
                            <a href="index.html">Inicio</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Periodos y Cohortes
                        </li>
                    </ol>
                </nav>
            </div>

            <div id="mensajeAplicacion" class="mb-3" aria-live="polite"></div>

            <ul class="nav nav-tabs mb-4" id="pestanasCajonDos" role="tablist">
                <li class="nav-item" role="presentation">
                    <button
                        class="nav-link active"
                        id="tab-estructuras"
                        data-bs-toggle="tab"
                        data-bs-target="#panel-estructuras"
                        type="button"
                        role="tab"
                        aria-controls="panel-estructuras"
                        aria-selected="true"
                    >
                        Periodos y Cohortes
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button
                        class="nav-link"
                        id="tab-oferta"
                        data-bs-toggle="tab"
                        data-bs-target="#panel-oferta"
                        type="button"
                        role="tab"
                        aria-controls="panel-oferta"
                        aria-selected="false"
                    >
                        Oferta Universitaria
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button
                        class="nav-link"
                        id="tab-expedientes"
                        data-bs-toggle="tab"
                        data-bs-target="#panel-expedientes"
                        type="button"
                        role="tab"
                        aria-controls="panel-expedientes"
                        aria-selected="false"
                    >
                        Expedientes de Grado
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="contenidoCajonDos">
                <section
                    class="tab-pane fade show active"
                    id="panel-estructuras"
                    role="tabpanel"
                    aria-labelledby="tab-estructuras"
                    tabindex="0"
                >
                    <div class="card border mb-4">
                        <div class="card-body p-3 p-lg-4">
                            <div class="row align-items-center g-3 mb-3">
                                <div class="col-12 col-md-8">
                                    <h2 class="h5 fw-bold mb-1">Periodos de Tutorías de Reforzamiento</h2>
                                    <p class="text-secondary small mb-0">
                                        Rangos de fechas mensuales para tutorías regulares por materia.
                                    </p>
                                </div>
                                <div class="col-12 col-md-4 text-md-end">
                                    <button
                                        type="button"
                                        class="btn btn-primary btn-sm"
                                        data-abrir-formulario="periodo"
                                    >
                                        + Nuevo Periodo
                                    </button>
                                </div>
                            </div>

                            <div id="contenedorTablaPeriodos">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle table-sm mb-0" id="tablaPeriodos">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col">Código</th>
                                                <th scope="col">Nombre del periodo</th>
                                                <th scope="col">Inicio</th>
                                                <th scope="col">Cierre</th>
                                                <th scope="col">Estado</th>
                                                <th scope="col" class="text-center">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                data-registro-id="1"
                                                data-codigo="PER-2026-M1"
                                                data-nombre="Módulo I - Gestión 2026"
                                                data-fecha-inicio="2026-03-02"
                                                data-fecha-fin="2026-03-27"
                                                data-estado="activo"
                                            >
                                                <td class="text-secondary">PER-2026-M1</td>
                                                <td class="fw-semibold">Módulo I - Gestión 2026</td>
                                                <td>02/03/2026</td>
                                                <td>27/03/2026</td>
                                                <td><span class="badge text-bg-success">Activo</span></td>
                                                <td class="text-center">
                                                    <button
                                                        type="button"
                                                        class="btn btn-outline-primary btn-sm"
                                                        data-editar-registro="periodo"
                                                        aria-label="Editar periodo PER-2026-M1"
                                                    >
                                                        Editar
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div id="formularioPeriodo" class="d-none" aria-live="polite"></div>
                        </div>
                    </div>

                    <div class="card border mb-4">
                        <div class="card-body p-3 p-lg-4">
                            <div class="row align-items-center g-3 mb-3">
                                <div class="col-12 col-md-8">
                                    <h2 class="h5 fw-bold mb-1">Cohortes de Grado</h2>
                                    <p class="text-secondary small mb-0">
                                        Grupos de estudiantes que inician procesos de titulación.
                                    </p>
                                </div>
                                <div class="col-12 col-md-4 text-md-end">
                                    <button
                                        type="button"
                                        class="btn btn-primary btn-sm"
                                        data-abrir-formulario="cohorte"
                                    >
                                        + Nueva Cohorte
                                    </button>
                                </div>
                            </div>

                            <div id="contenedorTablaCohortes">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle table-sm mb-0" id="tablaCohortes">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col">Código</th>
                                                <th scope="col">Denominación</th>
                                                <th scope="col">Inicio</th>
                                                <th scope="col">Estudiantes aptos</th>
                                                <th scope="col">Estado</th>
                                                <th scope="col" class="text-center">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                data-registro-id="45"
                                                data-codigo="G1-2026-03"
                                                data-nombre="Grupo 1 - Marzo 2026"
                                                data-fecha-inicio="2026-03-02"
                                                data-fecha-fin=""
                                                data-estudiantes="50"
                                                data-activa="1"
                                            >
                                                <td class="text-secondary">G1-2026-03</td>
                                                <td class="fw-semibold">Grupo 1 - Marzo 2026</td>
                                                <td>Marzo 2026</td>
                                                <td class="text-primary fw-semibold">50 estudiantes</td>
                                                <td><span class="badge text-bg-success">Habilitada</span></td>
                                                <td class="text-center">
                                                    <button
                                                        type="button"
                                                        class="btn btn-outline-primary btn-sm"
                                                        data-editar-registro="cohorte"
                                                        aria-label="Editar cohorte G1-2026-03"
                                                    >
                                                        Editar
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div id="formularioCohorte" class="d-none" aria-live="polite"></div>
                        </div>
                    </div>
                </section>

                <section
                    class="tab-pane fade"
                    id="panel-oferta"
                    role="tabpanel"
                    aria-labelledby="tab-oferta"
                    tabindex="0"
                >
                    <div class="card border mb-4">
                        <div class="card-body p-3 p-lg-4">
                            <div class="mb-4">
                                <h2 class="h5 fw-bold mb-1">Catálogo de Carreras y Materias</h2>
                                <p class="text-secondary small mb-0">
                                    Consulte los programas académicos y sus asignaturas.
                                </p>
                            </div>

                            <div class="btn-group mb-4" role="group" aria-label="Seleccionar catálogo">
                                <button
                                    type="button"
                                    class="btn btn-primary active"
                                    id="botonVerCarreras"
                                    aria-pressed="true"
                                >
                                    Ver Carreras
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-outline-primary"
                                    id="botonVerMaterias"
                                    aria-pressed="false"
                                >
                                    Ver Materias
                                </button>
                            </div>

                            <section id="catalogoCarreras" class="d-block" aria-labelledby="botonVerCarreras">
                                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
                                    <h3 class="h6 fw-bold mb-0">Programas Académicos</h3>
                                    <button type="button" class="btn btn-outline-primary btn-sm">
                                        + Nueva Carrera
                                    </button>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-hover align-middle table-sm mb-0" id="tablaCarreras">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col">Código</th>
                                                <th scope="col">Carrera</th>
                                                <th scope="col">Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr data-carrera-id="1" data-carrera-nombre="Ingeniería de Sistemas">
                                                <td class="text-secondary">ING-SIS</td>
                                                <td class="fw-semibold">Ingeniería de Sistemas</td>
                                                <td><span class="badge text-bg-success">Activa</span></td>
                                            </tr>
                                            <tr data-carrera-id="2" data-carrera-nombre="Administración de Empresas">
                                                <td class="text-secondary">ADM</td>
                                                <td class="fw-semibold">Administración de Empresas</td>
                                                <td><span class="badge text-bg-success">Activa</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </section>

                            <section
                                id="catalogoMaterias"
                                class="d-none"
                                aria-labelledby="botonVerMaterias"
                            >
                                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
                                    <h3 class="h6 fw-bold mb-0">Asignaturas Vinculadas</h3>
                                    <button type="button" class="btn btn-outline-primary btn-sm">
                                        + Nueva Materia
                                    </button>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-12 col-md-6">
                                        <label for="buscadorMaterias" class="form-label small fw-semibold">
                                            Buscar por nombre
                                        </label>
                                        <input
                                            type="text"
                                            class="form-control form-control-sm"
                                            id="buscadorMaterias"
                                            placeholder="Escriba el nombre de una materia"
                                            autocomplete="off"
                                        >
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label for="filtroCarreras" class="form-label small fw-semibold">
                                            Filtrar por carrera
                                        </label>
                                        <select class="form-select form-select-sm" id="filtroCarreras">
                                            <option value="">Ver todas las materias</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-hover align-middle table-sm mb-0" id="tablaMaterias">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col">Código</th>
                                                <th scope="col">Asignatura</th>
                                                <th scope="col">Carrera vinculada</th>
                                                <th scope="col">Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr data-carrera-id="1">
                                                <td class="text-secondary">SIS-411</td>
                                                <td class="fw-semibold" data-nombre-materia>Taller de Grado I</td>
                                                <td>Ingeniería de Sistemas</td>
                                                <td><span class="badge text-bg-success">Activa</span></td>
                                            </tr>
                                            <tr data-carrera-id="1">
                                                <td class="text-secondary">SIS-412</td>
                                                <td class="fw-semibold" data-nombre-materia>Taller de Grado II</td>
                                                <td>Ingeniería de Sistemas</td>
                                                <td><span class="badge text-bg-success">Activa</span></td>
                                            </tr>
                                            <tr data-carrera-id="2">
                                                <td class="text-secondary">ADM-210</td>
                                                <td class="fw-semibold" data-nombre-materia>Administración Financiera</td>
                                                <td>Administración de Empresas</td>
                                                <td><span class="badge text-bg-success">Activa</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <p id="mensajeSinMaterias" class="alert alert-info mt-3 mb-0 d-none" role="status">
                                    No hay materias que coincidan con esos filtros.
                                </p>
                            </section>
                        </div>
                    </div>
                </section>

                <section
                    class="tab-pane fade"
                    id="panel-expedientes"
                    role="tabpanel"
                    aria-labelledby="tab-expedientes"
                    tabindex="0"
                >
                    <div class="card border mb-4">
                        <div class="card-body p-3 p-lg-4">
                            <div class="row align-items-center g-3 mb-3">
                                <div class="col-12 col-md-8">
                                    <h2 class="h5 fw-bold mb-1">Seguimiento de Expedientes de Grado</h2>
                                    <p class="text-secondary small mb-0">
                                        Datos estáticos de demostración del flujo de modalidades de grado.
                                    </p>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label for="buscarExpediente" class="visually-hidden">Buscar expediente</label>
                                    <input
                                        type="search"
                                        class="form-control form-control-sm"
                                        id="buscarExpediente"
                                        placeholder="Buscar estudiante o trabajo"
                                    >
                                </div>
                            </div>

                            <div class="btn-group flex-wrap mb-3" role="group" aria-label="Filtrar expedientes por etapa">
                                <button type="button" class="btn btn-outline-primary btn-sm active" data-filtro-etapa="todas">
                                    Todos
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm" data-filtro-etapa="mg1">
                                    Fase Perfil (MG1)
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm" data-filtro-etapa="mg2">
                                    Fase Borrador (MG2)
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm" data-filtro-etapa="finalizado">
                                    Finalizado / Aprobado
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle table-sm mb-0" id="tablaExpedientes">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col">Estudiante</th>
                                            <th scope="col">Trabajo</th>
                                            <th scope="col">Tutor</th>
                                            <th scope="col">Etapa</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr data-etapa="mg1">
                                            <td class="fw-semibold">
                                                María Condori Choque
                                                <span class="d-block text-secondary small">RU-2026-30214</span>
                                            </td>
                                            <td>
                                                Desarrollo de un Sistema Web de Seguimiento
                                                <span class="d-block text-secondary small">Proyecto de Grado</span>
                                            </td>
                                            <td>Ing. Carlos García</td>
                                            <td><span class="badge text-bg-primary">Fase Perfil (MG1)</span></td>
                                        </tr>
                                        <tr data-etapa="mg2">
                                            <td class="fw-semibold">
                                                Juan Pérez Flores
                                                <span class="d-block text-secondary small">RU-2026-30450</span>
                                            </td>
                                            <td>
                                                Sistema de control académico
                                                <span class="d-block text-secondary small">Tesis</span>
                                            </td>
                                            <td>Ing. Ana Rojas</td>
                                            <td><span class="badge text-bg-warning">Fase Borrador (MG2)</span></td>
                                        </tr>
                                        <tr data-etapa="finalizado">
                                            <td class="fw-semibold">
                                                Lucía Flores Vargas
                                                <span class="d-block text-secondary small">RU-2025-29771</span>
                                            </td>
                                            <td>
                                                Plataforma de gestión universitaria
                                                <span class="d-block text-secondary small">Proyecto de Grado</span>
                                            </td>
                                            <td>Ing. Mario Salinas</td>
                                            <td><span class="badge text-bg-success">Finalizado / Aprobado</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-4 mb-3">
                <span class="badge text-bg-light border text-secondary">
                    Catálogos y cohortes de demostración
                </span>
                <a class="link-secondary small text-decoration-none" href="auditoria_bitacora.html">
                    Auditoría de cambios estructurales
                </a>
            </div>
        </div>
    </main>

    <footer class="flex-shrink-0 border-top bg-white">
        <div class="container py-3">
            <p class="small text-secondary mb-0">
                UPDS.NET v5.1 © 2026 - Universidad Privada Domingo Savio
            </p>
        </div>
    </footer>

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"
    ></script>

    <script>
        (() => {
            "use strict";

            const formularios = {
                periodo: {
                    contenedor: document.getElementById("formularioPeriodo"),
                    tabla: document.getElementById("contenedorTablaPeriodos"),
                    titulo: "periodo",
                    campos: [
                        { nombre: "codigo", etiqueta: "Código", tipo: "text", requerido: true },
                        { nombre: "nombre", etiqueta: "Nombre del periodo", tipo: "text", requerido: true },
                        { nombre: "fecha_inicio", etiqueta: "Fecha de inicio", tipo: "date", requerido: true },
                        { nombre: "fecha_fin", etiqueta: "Fecha de cierre", tipo: "date", requerido: true },
                        {
                            nombre: "estado",
                            etiqueta: "Estado",
                            tipo: "select",
                            requerido: true,
                            opciones: [
                                { valor: "activo", etiqueta: "Activo" },
                                { valor: "inactivo", etiqueta: "Inactivo" }
                            ]
                        }
                    ]
                },
                cohorte: {
                    contenedor: document.getElementById("formularioCohorte"),
                    tabla: document.getElementById("contenedorTablaCohortes"),
                    titulo: "cohorte",
                    campos: [
                        { nombre: "codigo", etiqueta: "Código", tipo: "text", requerido: true },
                        { nombre: "nombre", etiqueta: "Nombre de la cohorte", tipo: "text", requerido: true },
                        { nombre: "fecha_inicio", etiqueta: "Fecha de inicio", tipo: "date", requerido: true },
                        { nombre: "fecha_fin", etiqueta: "Fecha de cierre (opcional)", tipo: "date", requerido: false },
                        { nombre: "estudiantes", etiqueta: "Cantidad de estudiantes aptos", tipo: "number", requerido: false },
                        {
                            nombre: "activa",
                            etiqueta: "Estado",
                            tipo: "select",
                            requerido: true,
                            opciones: [
                                { valor: "1", etiqueta: "Habilitada" },
                                { valor: "0", etiqueta: "Inactiva" }
                            ]
                        }
                    ]
                }
            };

            const estadoInterfaz = {
                filtroEtapa: "todas"
            };

            function crearCampo(campo, valores = {}) {
                const grupo = document.createElement("div");
                grupo.className = "col-12 col-md-6";

                const etiqueta = document.createElement("label");
                etiqueta.className = "form-label";
                etiqueta.htmlFor = `${campo.nombre}Formulario`;
                etiqueta.textContent = campo.etiqueta;

                let control;

                if (campo.tipo === "select") {
                    control = document.createElement("select");
                    control.className = "form-select";

                    campo.opciones.forEach((opcion) => {
                        const elementoOpcion = document.createElement("option");
                        elementoOpcion.value = opcion.valor;
                        elementoOpcion.textContent = opcion.etiqueta;
                        control.appendChild(elementoOpcion);
                    });
                } else {
                    control = document.createElement("input");
                    control.type = campo.tipo;
                    control.className = "form-control";
                    if (campo.tipo === "number") {
                        control.min = "0";
                    }
                }

                control.id = `${campo.nombre}Formulario`;
                control.name = campo.nombre;
                control.required = campo.requerido;
                control.value = valores[campo.nombre] ?? "";
                grupo.append(etiqueta, control);

                return grupo;
            }

            function crearFormulario(tipo, modo, fila = null) {
                const configuracion = formularios[tipo];
                const formulario = document.createElement("form");
                formulario.method = "POST";
                formulario.action = "procesar_cohortes.php";
                formulario.className = "border rounded p-3 p-md-4";
                formulario.dataset.tipoRegistro = tipo;
                formulario.noValidate = false;

                const titulo = document.createElement("h3");
                titulo.className = "h5 fw-bold mb-3";
                titulo.textContent = `${modo === "editar" ? "Editar" : "Nuevo"} ${configuracion.titulo}`;

                const campoAccion = document.createElement("input");
                campoAccion.type = "hidden";
                campoAccion.name = "accion";
                campoAccion.value = modo === "editar" ? "actualizar" : "crear";
                formulario.append(campoAccion);

                if (modo === "editar" && fila) {
                    const campoId = document.createElement("input");
                    campoId.type = "hidden";
                    campoId.name = "id_registro";
                    campoId.value = fila.dataset.registroId;
                    formulario.append(campoId);
                }

                const campos = document.createElement("div");
                campos.className = "row g-3";

                const valores = modo === "editar" && fila
                    ? {
                        codigo: fila.dataset.codigo,
                        nombre: fila.dataset.nombre,
                        fecha_inicio: fila.dataset.fechaInicio,
                        fecha_fin: fila.dataset.fechaFin,
                        estado: fila.dataset.estado,
                        estudiantes: fila.dataset.estudiantes,
                        activa: fila.dataset.activa
                    }
                    : {};

                configuracion.campos.forEach((campo) => {
                    campos.appendChild(crearCampo(campo, valores));
                });

                const acciones = document.createElement("div");
                acciones.className = "col-12 d-flex flex-wrap justify-content-end gap-2 mt-3";

                const botonCancelar = document.createElement("button");
                botonCancelar.type = "button";
                botonCancelar.className = "btn btn-outline-secondary";
                botonCancelar.textContent = "Cancelar";
                botonCancelar.addEventListener("click", () => cerrarFormulario(tipo));

                const botonGuardar = document.createElement("button");
                botonGuardar.type = "submit";
                botonGuardar.className = "btn btn-primary";
                botonGuardar.textContent = modo === "editar" ? "Guardar cambios" : "Guardar";

                acciones.append(botonCancelar, botonGuardar);
                campos.appendChild(acciones);
                formulario.append(titulo, campos);

                return formulario;
            }

            function abrirFormulario(tipo, modo, fila = null) {
                const configuracion = formularios[tipo];
                configuracion.contenedor.replaceChildren(crearFormulario(tipo, modo, fila));
                configuracion.tabla.classList.add("d-none");
                configuracion.contenedor.classList.remove("d-none");
                configuracion.contenedor.classList.add("show");
                configuracion.contenedor.querySelector("input:not([type='hidden']), select")?.focus();
            }

            function cerrarFormulario(tipo) {
                const configuracion = formularios[tipo];
                const formulario = configuracion.contenedor.querySelector("form");

                if (formulario) {
                    formulario.reset();
                }

                configuracion.contenedor.classList.add("d-none");
                configuracion.contenedor.classList.remove("show");
                configuracion.contenedor.replaceChildren();
                configuracion.tabla.classList.remove("d-none");
            }

            function mostrarMensaje(tipo, mensaje) {
                const contenedor = document.getElementById("mensajeAplicacion");
                const alerta = document.createElement("div");
                alerta.className = `alert alert-${tipo} alert-dismissible fade show`;
                alerta.setAttribute("role", "alert");

                const texto = document.createElement("span");
                texto.textContent = mensaje;

                const cerrar = document.createElement("button");
                cerrar.type = "button";
                cerrar.className = "btn-close";
                cerrar.setAttribute("data-bs-dismiss", "alert");
                cerrar.setAttribute("aria-label", "Cerrar");

                alerta.append(texto, cerrar);
                contenedor.replaceChildren(alerta);
            }

            async function enviarFormulario(formulario) {
                if (!formulario.reportValidity()) {
                    return;
                }

                const botonEnviar = formulario.querySelector('button[type="submit"]');
                botonEnviar.disabled = true;
                botonEnviar.textContent = "Enviando…";

                try {
                    const respuesta = await fetch(formulario.action, {
                        method: "POST",
                        body: new FormData(formulario),
                        headers: {
                            Accept: "application/json"
                        },
                        credentials: "same-origin"
                    });

                    if (!respuesta.ok) {
                        throw new Error(`El procesador respondió con HTTP ${respuesta.status}.`);
                    }

                    const resultado = await respuesta.json();
                    if (!resultado || resultado.ok !== true) {
                        throw new Error(resultado?.mensaje || "El procesador no confirmó la operación.");
                    }

                    const tipo = formulario.dataset.tipoRegistro;
                    cerrarFormulario(tipo);
                    mostrarMensaje("success", resultado.mensaje || "La operación se completó correctamente.");
                } catch (error) {
                    mostrarMensaje(
                        "danger",
                        `No se pudo guardar. ${error.message} Esta maqueta no persiste datos: implemente procesar_cohortes.php para habilitar el POST.`
                    );
                    botonEnviar.disabled = false;
                    botonEnviar.textContent = formulario.querySelector('[name="id_registro"]')
                        ? "Guardar cambios"
                        : "Guardar";
                }
            }

            document.querySelectorAll("[data-abrir-formulario]").forEach((boton) => {
                boton.addEventListener("click", () => {
                    abrirFormulario(boton.dataset.abrirFormulario, "crear");
                });
            });

            document.querySelectorAll("[data-editar-registro]").forEach((boton) => {
                boton.addEventListener("click", () => {
                    const fila = boton.closest("tr");
                    abrirFormulario(boton.dataset.editarRegistro, "editar", fila);
                });
            });

            document.addEventListener("submit", (evento) => {
                const formulario = evento.target.closest("form[data-tipo-registro]");
                if (!formulario) {
                    return;
                }

                evento.preventDefault();
                enviarFormulario(formulario);
            });

            function cambiarCatalogo(catalogoVisible) {
                const mostrarCarreras = catalogoVisible === "carreras";
                const catalogoCarreras = document.getElementById("catalogoCarreras");
                const catalogoMaterias = document.getElementById("catalogoMaterias");
                const botonCarreras = document.getElementById("botonVerCarreras");
                const botonMaterias = document.getElementById("botonVerMaterias");

                catalogoCarreras.classList.toggle("d-block", mostrarCarreras);
                catalogoCarreras.classList.toggle("d-none", !mostrarCarreras);
                catalogoMaterias.classList.toggle("d-block", !mostrarCarreras);
                catalogoMaterias.classList.toggle("d-none", mostrarCarreras);

                botonCarreras.classList.toggle("btn-primary", mostrarCarreras);
                botonCarreras.classList.toggle("btn-outline-primary", !mostrarCarreras);
                botonMaterias.classList.toggle("btn-primary", !mostrarCarreras);
                botonMaterias.classList.toggle("btn-outline-primary", mostrarCarreras);
                botonCarreras.classList.toggle("active", mostrarCarreras);
                botonMaterias.classList.toggle("active", !mostrarCarreras);
                botonCarreras.setAttribute("aria-pressed", String(mostrarCarreras));
                botonMaterias.setAttribute("aria-pressed", String(!mostrarCarreras));
            }

            document.getElementById("botonVerCarreras").addEventListener("click", () => {
                cambiarCatalogo("carreras");
            });

            document.getElementById("botonVerMaterias").addEventListener("click", () => {
                cambiarCatalogo("materias");
            });

            function cargarOpcionesDeCarrera() {
                const selector = document.getElementById("filtroCarreras");
                const carreras = document.querySelectorAll("#tablaCarreras tbody tr");

                carreras.forEach((fila) => {
                    const opcion = document.createElement("option");
                    opcion.value = fila.dataset.carreraId;
                    opcion.textContent = fila.dataset.carreraNombre;
                    selector.appendChild(opcion);
                });
            }

            function filtrarMaterias() {
                const textoBusqueda = document
                    .getElementById("buscadorMaterias")
                    .value
                    .trim()
                    .toLocaleLowerCase("es");
                const carreraSeleccionada = document.getElementById("filtroCarreras").value;
                const filas = document.querySelectorAll("#tablaMaterias tbody tr");
                let cantidadVisible = 0;

                filas.forEach((fila) => {
                    const nombreMateria = fila
                        .querySelector("[data-nombre-materia]")
                        .textContent
                        .trim()
                        .toLocaleLowerCase("es");
                    const coincideTexto = nombreMateria.includes(textoBusqueda);
                    const coincideCarrera = carreraSeleccionada === ""
                        || fila.dataset.carreraId === carreraSeleccionada;
                    const visible = coincideTexto && coincideCarrera;

                    fila.classList.toggle("d-none", !visible);
                    if (visible) {
                        cantidadVisible += 1;
                    }
                });

                document
                    .getElementById("mensajeSinMaterias")
                    .classList.toggle("d-none", cantidadVisible !== 0);
            }

            cargarOpcionesDeCarrera();
            document.getElementById("buscadorMaterias").addEventListener("input", filtrarMaterias);
            document.getElementById("filtroCarreras").addEventListener("change", filtrarMaterias);

            document.getElementById("buscarExpediente").addEventListener("input", (evento) => {
                const texto = evento.target.value.trim().toLocaleLowerCase("es");
                document.querySelectorAll("#tablaExpedientes tbody tr").forEach((fila) => {
                    const coincideTexto = fila.textContent.toLocaleLowerCase("es").includes(texto);
                    const coincideEtapa = estadoInterfaz.filtroEtapa === "todas"
                        || fila.dataset.etapa === estadoInterfaz.filtroEtapa;
                    fila.classList.toggle("d-none", !(coincideTexto && coincideEtapa));
                });
            });

            document.querySelectorAll("[data-filtro-etapa]").forEach((boton) => {
                boton.addEventListener("click", () => {
                    estadoInterfaz.filtroEtapa = boton.dataset.filtroEtapa;

                    document.querySelectorAll("[data-filtro-etapa]").forEach((otroBoton) => {
                        const activo = otroBoton === boton;
                        otroBoton.classList.toggle("active", activo);
                        otroBoton.setAttribute("aria-pressed", String(activo));
                    });

                    const texto = document
                        .getElementById("buscarExpediente")
                        .value
                        .trim()
                        .toLocaleLowerCase("es");

                    document.querySelectorAll("#tablaExpedientes tbody tr").forEach((fila) => {
                        const coincideTexto = fila.textContent.toLocaleLowerCase("es").includes(texto);
                        const coincideEtapa = estadoInterfaz.filtroEtapa === "todas"
                            || fila.dataset.etapa === estadoInterfaz.filtroEtapa;
                        fila.classList.toggle("d-none", !(coincideTexto && coincideEtapa));
                    });
                });
            });
        })();
    </script>
</body>
</html>
