/**
 * Funciones Javascript
 */

// Funcion para mostrar/ocultar contraseñas
function togglePassword(id) {
    const campo = document.getElementById(id);
    if (campo.type === 'password') {
        campo.type = 'text';
    } else {
        campo.type = 'password';
    }
    console.log('Toggle password para elemento: ' + id);
}

// Funcion para copiar al portapapeles  
function copiarAlPortapapeles(texto) {
    navigator.clipboard.writeText(texto).then(function() {
        console.log('Copiado al portapapeles: ' + texto);
        // TODO: mostrar notificacion
    }).catch(function(err) {
        console.error('Error al copiar: ', err);
    });
}

// Validar formularios
function validarFormulario(form) {
    const accion = form.querySelector('input[name="accion"]');
    
    if (accion && accion.value === 'crear_maestro') {
        // Validar creacion de contraseña maestra
        const passwordNueva = form.querySelector('input[name="password_nueva"]').value;
        const passwordConfirm = form.querySelector('input[name="password_confirm"]').value;
        
        if (passwordNueva.length < 8) {
            alert('La contraseña debe tener al menos 8 caracteres');
            return false;
        }
        
        if (passwordNueva !== passwordConfirm) {
            alert('Las contraseñas no coinciden');
            return false;
        }
        
        // Verificar que tenga al menos una mayuscula y un numero (recomendacion basica)
        if (!/(?=.*[A-Z])(?=.*\d)/.test(passwordNueva)) {
            if (!confirm('Tu contraseña es debil. Se recomienda usar mayusculas y numeros. ¿Continuar?')) {
                return false;
            }
        }
    }
    
    return true;
}

// Mostrar fortaleza de contraseña (funcion auxiliar)
function verificarFortaleza(password) {
    let puntos = 0;
    
    if (password.length >= 8) puntos++;
    if (/[A-Z]/.test(password)) puntos++;
    if (/[a-z]/.test(password)) puntos++;
    if (/\d/.test(password)) puntos++;
    if (/[!@#$%^&*]/.test(password)) puntos++;
    
    return puntos;
}

console.log('Funciones cargadas correctamente');