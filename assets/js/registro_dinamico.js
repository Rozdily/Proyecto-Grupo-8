/**
 * Control dinámico y ordenamiento de campos según el Rol Seleccionado
 * Sistema de Tutorías UPDS
 */
document.addEventListener('DOMContentLoaded', function() {
    const selectorRol = document.getElementById('selectorRol');
    
    // Contenedores estructurales
    const bloqueCarrera = document.getElementById('bloqueCarrera');
    const bloqueTelefonoGeneral = document.getElementById('bloqueTelefonoGeneral');
    const bloqueBiografia = document.getElementById('bloqueBiografia');
    
    // Inputs individuales
    const inputTelefono = document.getElementById('inputTelefono');
    const inputCarrera = document.getElementById('inputCarrera');
    const inputTelefonoGeneral = document.getElementById('inputTelefonoGeneral');
    const inputBiografia = document.getElementById('inputBiografia');

    function alternarCamposPorRol() {
        const opcionSeleccionada = selectorRol.options[selectorRol.selectedIndex];
        const nombreRol = opcionSeleccionada ? opcionSeleccionada.getAttribute('data-rol') : '';

        // 1. Limpiar todas las clases dinámicas previas de los bloques
        bloqueCarrera.classList.remove('dinamico-visible', 'dinamico-oculto');
        bloqueTelefonoGeneral.classList.remove('dinamico-visible-bloque', 'dinamico-oculto');
        bloqueBiografia.classList.remove('dinamico-visible-bloque', 'dinamico-oculto');
        
        // 2. Desactivar validaciones requeridas iniciales para evitar bloqueos
        inputTelefono.removeAttribute('required');
        inputCarrera.removeAttribute('required');
        inputTelefonoGeneral.removeAttribute('required');
        inputBiografia.removeAttribute('required');

        // Caso A: No hay rol seleccionado (Estado Inicial de la página)
        if (nombreRol === '') {
            bloqueCarrera.classList.add('dinamico-oculto');
            bloqueTelefonoGeneral.classList.add('dinamico-oculto');
            bloqueBiografia.classList.add('dinamico-oculto');
            return;
        }

        // Caso B: Rol Estudiante seleccionado
        if (nombreRol === 'estudiante') {
            bloqueCarrera.classList.add('dinamico-visible');
            bloqueTelefonoGeneral.classList.add('dinamico-oculto');
            bloqueBiografia.classList.add('dinamico-oculto');
            
            inputTelefono.setAttribute('required', 'required');
            inputCarrera.setAttribute('required', 'required');

            inputTelefono.setAttribute('name', 'telefono');
            inputTelefonoGeneral.setAttribute('name', 'telefono_inactivo');
        } 
        // Caso C: Rol Tutor o Docente seleccionado
        else if (nombreRol === 'tutor' || nombreRol === 'docente') {
            bloqueCarrera.classList.add('dinamico-oculto');
            bloqueTelefonoGeneral.classList.add('dinamico-visible-bloque');
            bloqueBiografia.classList.add('dinamico-visible-bloque');
            
            inputTelefonoGeneral.setAttribute('required', 'required');
            inputBiografia.setAttribute('required', 'required');

            inputTelefono.setAttribute('name', 'telefono_inactivo');
            inputTelefonoGeneral.setAttribute('name', 'telefono');
        } 
        // Caso D: Cualquier otro rol que no necesite carrera ni biografía
        else {
            bloqueCarrera.classList.add('dinamico-oculto');
            bloqueTelefonoGeneral.classList.add('dinamico-visible-bloque');
            bloqueBiografia.classList.add('dinamico-oculto');
            
            inputTelefonoGeneral.setAttribute('required', 'required');
            
            inputTelefono.setAttribute('name', 'telefono_inactivo');
            inputTelefonoGeneral.setAttribute('name', 'telefono');
        }
    }

    // Escuchar el evento de cambio en el selector
    selectorRol.addEventListener('change', alternarCamposPorRol);

    // Inicializar la vista al cargar la página (Maneja estados iniciales y reenvíos de formularios con errores)
    alternarCamposPorRol();
});
