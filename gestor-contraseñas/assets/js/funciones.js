/**
 * Funciones Javascript para el gestor de contraseñas
 */

// Funcion para mostrar/ocultar contraseñas
function togglePassword(id) {
    const campo = document.getElementById(id);
    const botonToggle = event.target;
    
    if (campo.type === 'password') {
        campo.type = 'text';
        botonToggle.textContent = '🙈';
        botonToggle.title = 'Ocultar contraseña';
    } else {
        campo.type = 'password';
        botonToggle.textContent = '👁️';
        botonToggle.title = 'Mostrar contraseña';
    }
    console.log('Toggle password para elemento: ' + id);
}

// Funcion para copiar al portapapeles  
function copiarAlPortapapeles(texto) {
    // Intentar usar la API moderna del portapapeles
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(texto).then(function() {
            mostrarNotificacion('Copiado al portapapeles ✓', 'exito');
            console.log('Copiado con API moderna: ' + texto);
        }).catch(function(err) {
            console.error('Error al copiar con API moderna: ', err);
            copiarConFallback(texto);
        });
    } else {
        // Fallback para navegadores más antiguos
        copiarConFallback(texto);
    }
}

// Fallback para copiar al portapapeles
function copiarConFallback(texto) {
    const textArea = document.createElement('textarea');
    textArea.value = texto;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    textArea.style.top = '-999999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        const exitoso = document.execCommand('copy');
        if (exitoso) {
            mostrarNotificacion('Copiado al portapapeles ✓', 'exito');
            console.log('Copiado con fallback: ' + texto);
        } else {
            mostrarNotificacion('Error al copiar', 'error');
        }
    } catch (err) {
        console.error('Error en fallback: ', err);
        mostrarNotificacion('Error al copiar: ' + err, 'error');
    }
    
    document.body.removeChild(textArea);
}

// Mostrar notificacion temporal
function mostrarNotificacion(mensaje, tipo = 'info') {
    // Crear elemento de notificacion
    const notif = document.createElement('div');
    notif.className = `notificacion notif-${tipo}`;
    notif.textContent = mensaje;
    
    // Estilos inline para la notificacion
    notif.style.cssText = `
        position: fixed;
        top: 70px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 5px;
        color: white;
        font-weight: bold;
        z-index: 10000;
        animation: slideIn 0.3s ease-out;
    `;
    
    // Colores segun tipo
    switch(tipo) {
        case 'exito':
            notif.style.background = '#27ae60';
            break;
        case 'error':
            notif.style.background = '#e74c3c';
            break;
        default:
            notif.style.background = '#3498db';
    }
    
    // Agregar al DOM
    document.body.appendChild(notif);
    
    // Remover despues de 3 segundos
    setTimeout(() => {
        notif.style.animation = 'slideOut 0.3s ease-in';
        setTimeout(() => {
            if (notif.parentNode) {
                notif.parentNode.removeChild(notif);
            }
        }, 300);
    }, 3000);
}

// Validar formularios
function validarFormulario(form) {
    const accion = form.querySelector('input[name="accion"]');
    
    if (accion && accion.value === 'crear_maestro') {
        // Validar creacion de contraseña maestra
        const passwordNueva = form.querySelector('input[name="password_nueva"]').value;
        const passwordConfirm = form.querySelector('input[name="password_confirm"]').value;
        
        if (passwordNueva.length < 8) {
            mostrarNotificacion('La contraseña debe tener al menos 8 caracteres', 'error');
            return false;
        }
        
        if (passwordNueva !== passwordConfirm) {
            mostrarNotificacion('Las contraseñas no coinciden', 'error');
            return false;
        }
        
        // Verificar que tenga al menos una mayuscula y un numero (recomendacion basica)
        if (!/(?=.*[A-Z])(?=.*\d)/.test(passwordNueva)) {
            if (!confirm('Tu contraseña es débil. Se recomienda usar mayúsculas y números. ¿Continuar?')) {
                return false;
            }
        }
    }
    
    if (accion && (accion.value === 'agregar' || accion.value === 'editar')) {
        // Validar formularios de contraseñas
        const sitio = form.querySelector('input[name="sitio"]').value.trim();
        const usuario = form.querySelector('input[name="usuario"]').value.trim();
        
        if (sitio.length < 2) {
            mostrarNotificacion('El sitio debe tener al menos 2 caracteres', 'error');
            form.querySelector('input[name="sitio"]').focus();
            return false;
        }
        
        if (usuario.length < 2) {
            mostrarNotificacion('El usuario debe tener al menos 2 caracteres', 'error');
            form.querySelector('input[name="usuario"]').focus();
            return false;
        }
        
        // Para agregar, la contraseña es obligatoria
        if (accion.value === 'agregar') {
            const password = form.querySelector('input[name="password"]').value;
            if (password.length < 4) {
                mostrarNotificacion('La contraseña debe tener al menos 4 caracteres', 'error');
                form.querySelector('input[name="password"]').focus();
                return false;
            }
        }
    }
    
    return true;
}

// Mostrar fortaleza de contraseña (funcion auxiliar mejorada)
function verificarFortaleza(password) {
    let puntos = 0;
    
    // Longitud minima
    if (password.length >= 8) puntos++;
    if (password.length >= 12) puntos++;
    
    // Tipos de caracteres
    if (/[A-Z]/.test(password)) puntos++;
    if (/[a-z]/.test(password)) puntos++;
    if (/\d/.test(password)) puntos++;
    if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) puntos++;
    
    // Penalizar patrones comunes
    if (/(.)\1{2,}/.test(password)) puntos--; // caracteres repetidos
    if (/123|abc|qwerty/i.test(password)) puntos--; // secuencias comunes
    
    return Math.max(0, Math.min(5, puntos));
}

// Funciones utiles adicionales

// Generar una contraseña aleatoria en el frontend
function generarPasswordCliente(longitud = 12) {
    const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    let password = '';
    
    for (let i = 0; i < longitud; i++) {
        password += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    
    return password;
}

// Confirmar accion con dialogo personalizado
function confirmarAccion(mensaje, callback) {
    if (confirm(mensaje)) {
        callback();
    }
}

// Auto-guardar temporal en localStorage (opcional)
function autoGuardar(formId) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    const inputs = form.querySelectorAll('input[type="text"], input[type="email"], textarea');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            localStorage.setItem(`temp_${input.name}`, input.value);
        });
        
        // Restaurar valor si existe
        const valorGuardado = localStorage.getItem(`temp_${input.name}`);
        if (valorGuardado && input.value === '') {
            input.value = valorGuardado;
        }
    });
}

// Limpiar datos temporales
function limpiarDatosTemporales() {
    Object.keys(localStorage).forEach(key => {
        if (key.startsWith('temp_')) {
            localStorage.removeItem(key);
        }
    });
}

// Detectar inactividad y recordar cerrar sesion
let tiempoInactivo = 0;
const TIEMPO_MAX_INACTIVIDAD = 30; // minutos

function reiniciarTiempo() {
    tiempoInactivo = 0;
}

function verificarInactividad() {
    tiempoInactivo++;
    
    if (tiempoInactivo >= TIEMPO_MAX_INACTIVIDAD) {
        if (confirm('Has estado inactivo por mucho tiempo. ¿Cerrar sesión por seguridad?')) {
            window.location.href = '?logout=1';
        } else {
            reiniciarTiempo();
        }
    }
}

// Inicializar verificación de inactividad solo si hay sesión activa
if (document.body.getAttribute('data-session') === 'active') {
    // Detectar actividad del usuario
    ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'].forEach(function(event) {
        document.addEventListener(event, reiniciarTiempo, true);
    });
    
    // Verificar cada minuto
    setInterval(verificarInactividad, 60000);
}

// Animaciones CSS via JS
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(style);

console.log('✅ Gestor de contraseñas - Funciones JavaScript cargadas correctamente');

// Marcar la sesión como activa si está autenticado
document.addEventListener('DOMContentLoaded', function() {
    // Verificar si hay barra de navegación (indicador de sesión activa)
    if (document.querySelector('.barra-navegacion')) {
        document.body.setAttribute('data-session', 'active');
    }
});