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

        // Ocultar todo por defecto usando clases CSS estables
        bloqueCarrera.classList.remove('dinamico-visible');
        bloqueTelefonoGeneral.classList.remove('dinamico-oculto');
        bloqueBiografia.classList.remove('dinamico-visible-bloque');
        
        // Quitar obligatoriedad general para evitar bloqueos de envío
        inputTelefono.removeAttribute('required');
        inputCarrera.removeAttribute('required');
        inputTelefonoGeneral.setAttribute('required', 'required');
        inputBiografia.removeAttribute('required');

        // Sincronizar el nombre del atributo POST para el teléfono
        inputTelefono.setAttribute('name', 'telefono_inactivo');
        inputTelefonoGeneral.setAttribute('name', 'telefono');

        if (nombreRol === 'estudiante') {
            // Mostrar fila doble (Teléfono + Carrera)
            bloqueCarrera.classList.add('dinamico-visible');
            // Ocultar el teléfono de ancho completo
            bloqueTelefonoGeneral.classList.add('dinamico-oculto');
            
            // Volver obligatorios los campos de la fila estudiante
            inputTelefono.setAttribute('required', 'required');
            inputCarrera.setAttribute('required', 'required');
            inputTelefonoGeneral.removeAttribute('required');

            // Intercambiar el name para que viaje el dato correcto al servidor
            inputTelefono.setAttribute('name', 'telefono');
            inputTelefonoGeneral.setAttribute('name', 'telefono_inactivo');

        } else if (nombreRol === 'tutor' || nombreRol === 'docente') {
            // Mostrar la biografía abajo de todo en formato de bloque completo
            bloqueBiografia.classList.add('dinamico-visible-bloque');
            inputBiografia.setAttribute('required', 'required');
        }
    }

    // Escuchar el evento de cambio
    selectorRol.addEventListener('change', alternarCamposPorRol);

    // Inicializar la vista al cargar la página
    alternarCamposPorRol();
});
