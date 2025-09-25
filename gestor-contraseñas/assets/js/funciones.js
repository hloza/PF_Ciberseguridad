/**
 * Funciones JavaScript para el gestor de contraseñas
 * Version mejorada con funcionalidades avanzadas
 * Incluye validacion, generacion, exportacion y utilidades frontend
 */

// Variables globales para configuracion
const CONFIG = {
    longitudMinima: 8,
    longitudMaxima: 128,
    tiempoMostrarMensaje: 3000,
    caracteresEspeciales: '!@#$%^&*()_+-=[]{}|;:,.<>?',
    tiempoAutoOcultar: 5000,
    animacionDuracion: 300,
    tiempoInactividad: 30 // minutos
};

// Estado global de la aplicacion
const STATE = {
    contraseniasVisibles: new Set(),
    timersAutoOcultar: new Map(),
    modoOscuro: localStorage.getItem('modo_oscuro') === 'true',
    tiempoInactivo: 0,
    datosFormulario: new Map()
};

// Inicializar aplicacion cuando carga la pagina
document.addEventListener('DOMContentLoaded', function() {
    inicializarAplicacion();
    aplicarTemaGuardado();
    configurarAtajosTeclado();
});

// ==================== FUNCIONES PRINCIPALES ====================

// Inicializar funcionalidades principales
function inicializarAplicacion() {
    // Configurar tooltips dinamicos
    configurarTooltips();
    
    // Detectar campos de contraseña para auto-evaluación
    const camposPassword = document.querySelectorAll('input[type="password"]');
    camposPassword.forEach(campo => {
        campo.addEventListener('input', () => {
            if (campo.id) {
                evaluarFortaleza(campo.id);
            }
        });
    });
    
    // Configurar generador automático si existe
    configurarGeneradorAutomatico();
    
    // Auto-guardar formularios en localStorage
    configurarAutoguardado();
    
    // Detectar sesion activa para controles de seguridad
    if (document.querySelector('.barra-navegacion')) {
        document.body.setAttribute('data-session', 'active');
        inicializarControlSeguridad();
    }
    
    console.log('✅ Gestor de contraseñas inicializado correctamente');
}

// Función principal para mostrar/ocultar contraseñas (compatible con el código existente)
function togglePassword(id) {
    const campo = document.getElementById(id);
    const botonToggle = event.target;
    
    if (!campo) return;
    
    if (campo.type === 'password') {
        campo.type = 'text';
        botonToggle.textContent = '🙈';
        botonToggle.title = 'Ocultar contraseña';
        STATE.contraseniasVisibles.add(id);
        
        // Auto-ocultar después de tiempo configurado
        const timer = setTimeout(() => {
            if (STATE.contraseniasVisibles.has(id) && campo.type === 'text') {
                campo.type = 'password';
                botonToggle.textContent = '👁️';
                botonToggle.title = 'Mostrar contraseña';
                STATE.contraseniasVisibles.delete(id);
            }
        }, CONFIG.tiempoAutoOcultar);
        
        STATE.timersAutoOcultar.set(id, timer);
        
    } else {
        campo.type = 'password';
        botonToggle.textContent = '👁️';
        botonToggle.title = 'Mostrar contraseña';
        STATE.contraseniasVisibles.delete(id);
        
        // Cancelar timer de auto-ocultar
        if (STATE.timersAutoOcultar.has(id)) {
            clearTimeout(STATE.timersAutoOcultar.get(id));
            STATE.timersAutoOcultar.delete(id);
        }
    }
    
    console.log('Toggle password para elemento: ' + id);
}

// Función mejorada para copiar al portapapeles
function copiarAlPortapapeles(texto, elemento) {
    if (!texto) {
        mostrarNotificacion('❌ No hay texto para copiar', 'error');
        return;
    }
    
    // Intentar usar la API moderna del portapapeles
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(texto).then(function() {
            mostrarNotificacion('Copiado al portapapeles ✓', 'exito');
            console.log('Copiado con API moderna (longitud: ' + texto.length + ')');
            
            // Limpiar portapapeles después de 60 segundos por seguridad
            setTimeout(() => {
                navigator.clipboard.writeText('').catch(() => {});
            }, 60000);
            
        }).catch(function(err) {
            console.error('Error al copiar con API moderna: ', err);
            copiarConFallback(texto);
        });
    } else {
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
            console.log('Copiado con fallback (longitud: ' + texto.length + ')');
        } else {
            mostrarNotificacion('Error al copiar', 'error');
        }
    } catch (err) {
        console.error('Error en fallback: ', err);
        mostrarNotificacion('Error al copiar: ' + err, 'error');
    }
    
    document.body.removeChild(textArea);
}

// ==================== GENERADOR DE CONTRASEÑAS ====================

// Generar contraseña segura con algoritmo mejorado
function generarPasswordCliente(longitud = 12, incluirEspeciales = true, incluirAmbiguos = false) {
    const minusculas = 'abcdefghijklmnopqrstuvwxyz';
    const mayusculas = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const numeros = '0123456789';
    const especiales = incluirEspeciales ? CONFIG.caracteresEspeciales : '';
    
    // Caracteres ambiguos que pueden confundirse
    const ambiguos = 'il1Lo0O';
    
    let caracteres = minusculas + mayusculas + numeros + especiales;
    
    // Remover caracteres ambiguos si está configurado
    if (!incluirAmbiguos) {
        caracteres = caracteres.split('').filter(c => !ambiguos.includes(c)).join('');
    }
    
    // Asegurar que incluya al menos uno de cada tipo requerido
    let password = '';
    const tiposRequeridos = [];
    
    // Agregar tipos disponibles
    if (minusculas.length > 0) tiposRequeridos.push(minusculas.replace(/[il]/g, ''));
    if (mayusculas.length > 0) tiposRequeridos.push(mayusculas.replace(/[LO]/g, ''));
    if (numeros.length > 0) tiposRequeridos.push(numeros.replace(/[10]/g, ''));
    if (incluirEspeciales && especiales.length > 0) tiposRequeridos.push(especiales);
    
    // Agregar un caracter de cada tipo requerido
    tiposRequeridos.forEach(tipo => {
        if (tipo.length > 0) {
            password += tipo[Math.floor(Math.random() * tipo.length)];
        }
    });
    
    // Completar la longitud deseada
    for (let i = password.length; i < longitud; i++) {
        password += caracteres[Math.floor(Math.random() * caracteres.length)];
    }
    
    // Mezclar caracteres múltiples veces para mejor randomización
    for (let i = 0; i < 3; i++) {
        password = password.split('').sort(() => Math.random() - 0.5).join('');
    }
    
    return password;
}

// Aplicar password generada (función mejorada manteniendo compatibilidad)
function aplicarPasswordGenerada(campoId) {
    const longitud = document.getElementById('longitud_generada')?.value || 12;
    const incluirEspeciales = document.getElementById('incluir_especiales')?.checked !== false;
    const incluirAmbiguos = document.getElementById('incluir_ambiguos')?.checked || false;
    
    const passwordGenerada = generarPasswordCliente(
        parseInt(longitud), 
        incluirEspeciales,
        incluirAmbiguos
    );
    
    const campo = document.getElementById(campoId);
    
    if (campo) {
        campo.value = passwordGenerada;
        campo.type = 'text'; // Mostrar temporalmente
        
        // Highlight del campo
        campo.style.backgroundColor = '#e8f5e8';
        setTimeout(() => {
            campo.style.backgroundColor = '';
        }, 1000);
        
        // Ocultar después de tiempo configurado
        setTimeout(() => {
            if (campo.type === 'text' && !STATE.contraseniasVisibles.has(campoId)) {
                campo.type = 'password';
            }
        }, CONFIG.tiempoAutoOcultar);
        
        // Actualizar medidor de fortaleza
        if (typeof evaluarFortaleza === 'function') {
            evaluarFortaleza(campoId);
        }
        
        mostrarNotificacion(`✅ Contraseña de ${longitud} caracteres generada`, 'exito');
        
        // Guardar configuración del generador
        localStorage.setItem('generador_config', JSON.stringify({
            longitud: longitud,
            incluirEspeciales: incluirEspeciales,
            incluirAmbiguos: incluirAmbiguos
        }));
    }
}

// ==================== EVALUADOR DE FORTALEZA ====================

// Evaluar fortaleza de contraseña con análisis avanzado
function evaluarFortaleza(campoId) {
    const campo = document.getElementById(campoId);
    const medidor = document.getElementById('medidor_fortaleza');
    const texto = document.getElementById('texto_fortaleza');
    const detalles = document.getElementById('detalles_fortaleza');
    
    if (!campo) return;
    
    const password = campo.value;
    let puntos = 0;
    let nivel = 'Muy débil';
    let color = '#ff4444';
    let porcentaje = 0;
    let recomendaciones = [];
    
    if (password.length === 0) {
        if (medidor) medidor.style.width = '0%';
        if (texto) texto.textContent = '';
        if (detalles) detalles.innerHTML = '';
        return;
    }
    
    // Criterios de evaluación mejorados
    if (password.length >= 8) {
        puntos++;
    } else {
        recomendaciones.push('Usar al menos 8 caracteres');
    }
    
    if (password.length >= 12) puntos++;
    if (password.length >= 16) puntos += 0.5;
    
    if (/[a-z]/.test(password)) {
        puntos++;
    } else {
        recomendaciones.push('Incluir minúsculas');
    }
    
    if (/[A-Z]/.test(password)) {
        puntos++;
    } else {
        recomendaciones.push('Incluir mayúsculas');
    }
    
    if (/\d/.test(password)) {
        puntos++;
    } else {
        recomendaciones.push('Incluir números');
    }
    
    if (/[^a-zA-Z\d]/.test(password)) {
        puntos++;
    } else if (recomendaciones.length < 3) {
        recomendaciones.push('Incluir símbolos especiales');
    }
    
    // Bonificaciones por variedad
    const tiposCaracteres = [
        /[a-z]/, /[A-Z]/, /\d/, /[^a-zA-Z\d]/
    ].filter(regex => regex.test(password)).length;
    
    if (tiposCaracteres >= 4) puntos += 0.5;
    
    // Penalizar patrones débiles
    if (/(.)\1{2,}/.test(password)) {
        puntos -= 1;
        recomendaciones.push('Evitar repeticiones');
    }
    
    if (/123|abc|qwe|asd|zxc/i.test(password)) {
        puntos -= 0.5;
        recomendaciones.push('Evitar secuencias comunes');
    }
    
    if (/password|123456|qwerty|admin|login|user/i.test(password)) {
        puntos -= 2;
        recomendaciones.push('Evitar palabras comunes');
    }
    
    // Determinar nivel final
    puntos = Math.max(0, Math.min(8, puntos));
    
    if (puntos <= 1) {
        nivel = 'Muy débil';
        color = '#ff4444';
        porcentaje = 15;
    } else if (puntos <= 2.5) {
        nivel = 'Débil';
        color = '#ff8800';
        porcentaje = 30;
    } else if (puntos <= 4) {
        nivel = 'Regular';
        color = '#ffaa00';
        porcentaje = 50;
    } else if (puntos <= 5.5) {
        nivel = 'Buena';
        color = '#88dd00';
        porcentaje = 70;
    } else if (puntos <= 6.5) {
        nivel = 'Fuerte';
        color = '#44bb00';
        porcentaje = 85;
    } else {
        nivel = 'Muy fuerte';
        color = '#008844';
        porcentaje = 100;
    }
    
    // Aplicar estilos al medidor
    if (medidor) {
        medidor.style.width = porcentaje + '%';
        medidor.style.backgroundColor = color;
        medidor.style.transition = 'all 0.4s ease';
        medidor.style.boxShadow = `0 0 10px ${color}30`;
    }
    
    // Mostrar texto del nivel
    if (texto) {
        texto.textContent = `${nivel} (${Math.round(puntos * 10)/10}/8)`;
        texto.style.color = color;
        texto.style.fontWeight = 'bold';
    }
    
    // Mostrar detalles y recomendaciones
    if (detalles && recomendaciones.length > 0) {
        detalles.innerHTML = `
            <div class="recomendaciones">
                <small>💡 ${recomendaciones.join(', ')}</small>
            </div>
        `;
        detalles.style.marginTop = '5px';
        detalles.style.fontSize = '0.8em';
        detalles.style.color = '#666';
    }
    
    return {puntos, nivel, color, porcentaje, recomendaciones};
}

// ==================== VALIDACIÓN DE FORMULARIOS ====================

// Validar formularios con reglas mejoradas
function validarFormulario(form) {
    const accion = form.querySelector('input[name="accion"]');
    
    if (accion && accion.value === 'crear_maestro') {
        return validarCreacionMaestro(form);
    }
    
    if (accion && (accion.value === 'agregar' || accion.value === 'editar')) {
        return validarFormularioPassword(form);
    }
    
    return true;
}

function validarCreacionMaestro(form) {
    const passwordNueva = form.querySelector('input[name="password_nueva"]').value;
    const passwordConfirm = form.querySelector('input[name="password_confirm"]').value;
    
    if (passwordNueva.length < CONFIG.longitudMinima) {
        mostrarNotificacion(`La contraseña debe tener al menos ${CONFIG.longitudMinima} caracteres`, 'error');
        return false;
    }
    
    if (passwordNueva !== passwordConfirm) {
        mostrarNotificacion('Las contraseñas no coinciden', 'error');
        return false;
    }
    
    // Evaluación automática de fortaleza para campos con ID
    const campoPassword = form.querySelector('input[name="password_nueva"]');
    if (campoPassword && campoPassword.id) {
        const evaluacion = evaluarFortaleza(campoPassword.id);
        if (evaluacion && evaluacion.puntos < 3) {
            if (!confirm('Tu contraseña maestra es débil. Una contraseña fuerte protege mejor tus datos. ¿Continuar de todos modos?')) {
                return false;
            }
        }
    }
    
    return true;
}

function validarFormularioPassword(form) {
    const sitio = form.querySelector('input[name="sitio"]').value.trim();
    const usuario = form.querySelector('input[name="usuario"]').value.trim();
    const accion = form.querySelector('input[name="accion"]').value;
    
    if (sitio.length < 2) {
        mostrarNotificacion('El sitio debe tener al menos 2 caracteres', 'error');
        form.querySelector('input[name="sitio"]').focus();
        return false;
    }
    
    if (usuario.length < 1) {
        mostrarNotificacion('El usuario es obligatorio', 'error');
        form.querySelector('input[name="usuario"]').focus();
        return false;
    }
    
    // Para agregar, la contraseña es obligatoria
    if (accion === 'agregar') {
        const password = form.querySelector('input[name="password"]').value;
        if (password.length < 4) {
            mostrarNotificacion('La contraseña debe tener al menos 4 caracteres', 'error');
            form.querySelector('input[name="password"]').focus();
            return false;
        }
    }
    
    return true;
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

// ==================== UTILIDADES Y HELPERS ADICIONALES ====================

// Configurar tooltips dinámicos
function configurarTooltips() {
    document.querySelectorAll('[title]').forEach(elemento => {
        elemento.addEventListener('mouseenter', function(e) {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip-custom';
            tooltip.textContent = this.title;
            tooltip.style.cssText = `
                position: absolute;
                background: #333;
                color: white;
                padding: 5px 10px;
                border-radius: 4px;
                font-size: 12px;
                z-index: 10001;
                pointer-events: none;
                white-space: nowrap;
            `;
            document.body.appendChild(tooltip);
            
            // Posicionar tooltip
            const rect = this.getBoundingClientRect();
            tooltip.style.left = (rect.left + rect.width/2 - tooltip.offsetWidth/2) + 'px';
            tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';
            
            this._tooltip = tooltip;
        });
        
        elemento.addEventListener('mouseleave', function() {
            if (this._tooltip) {
                this._tooltip.remove();
                delete this._tooltip;
            }
        });
    });
}

// Configurar generador automático
function configurarGeneradorAutomatico() {
    // Cargar configuración guardada
    const configGuardada = localStorage.getItem('generador_config');
    if (configGuardada) {
        try {
            const config = JSON.parse(configGuardada);
            const longitud = document.getElementById('longitud_generada');
            const especiales = document.getElementById('incluir_especiales');
            const ambiguos = document.getElementById('incluir_ambiguos');
            
            if (longitud) longitud.value = config.longitud || 12;
            if (especiales) especiales.checked = config.incluirEspeciales !== false;
            if (ambiguos) ambiguos.checked = config.incluirAmbiguos || false;
        } catch (e) {
            console.warn('Error cargando configuración del generador:', e);
        }
    }
}

// Auto-guardado de formularios
function configurarAutoguardado() {
    document.querySelectorAll('form').forEach(form => {
        if (form.id) {
            const inputs = form.querySelectorAll('input[type="text"], input[type="email"], textarea');
            inputs.forEach(input => {
                if (input.name && !input.name.includes('password')) {
                    // Cargar valor guardado
                    const valorGuardado = localStorage.getItem(`form_${form.id}_${input.name}`);
                    if (valorGuardado && !input.value) {
                        input.value = valorGuardado;
                    }
                    
                    // Guardar cambios
                    input.addEventListener('input', function() {
                        localStorage.setItem(`form_${form.id}_${input.name}`, input.value);
                    });
                }
            });
        }
    });
}

// Aplicar tema guardado
function aplicarTemaGuardado() {
    if (STATE.modoOscuro) {
        document.body.classList.add('tema-oscuro');
    }
}

// Configurar atajos de teclado
function configurarAtajosTeclado() {
    document.addEventListener('keydown', function(e) {
        // Ctrl+G: Generar contraseña
        if (e.ctrlKey && e.key === 'g') {
            e.preventDefault();
            const campoPassword = document.querySelector('input[name="password"]');
            if (campoPassword && campoPassword.id) {
                aplicarPasswordGenerada(campoPassword.id);
            } else if (campoPassword) {
                // Generar en el campo aunque no tenga ID
                const passwordGenerada = generarPasswordCliente(12, true, false);
                campoPassword.value = passwordGenerada;
                mostrarNotificacion('✅ Contraseña generada con Ctrl+G', 'exito');
            }
        }
        
        // Esc: Cerrar notificaciones
        if (e.key === 'Escape') {
            document.querySelectorAll('.notificacion').forEach(notif => notif.remove());
        }
    });
}

// ==================== CONTROLES DE SEGURIDAD ====================

// Inicializar controles de seguridad para sesiones activas
function inicializarControlSeguridad() {
    // Detectar actividad del usuario
    ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'].forEach(function(event) {
        document.addEventListener(event, reiniciarTiempoInactivo, true);
    });
    
    // Verificar inactividad cada minuto
    setInterval(verificarInactividad, 60000);
    
    // Limpiar portapapeles periódicamente
    setInterval(limpiarPortapapeles, 300000); // 5 minutos
    
    // Advertencia antes de cerrar pestaña
    window.addEventListener('beforeunload', function(e) {
        if (document.querySelector('input[name="password"]') && 
            document.querySelector('input[name="password"]').value) {
            e.preventDefault();
            return 'Tienes una contraseña sin guardar. ¿Estás seguro de salir?';
        }
    });
}

function reiniciarTiempoInactivo() {
    STATE.tiempoInactivo = 0;
}

function verificarInactividad() {
    STATE.tiempoInactivo++;
    
    if (STATE.tiempoInactivo >= CONFIG.tiempoInactividad) {
        if (confirm('Has estado inactivo por mucho tiempo. ¿Cerrar sesión por seguridad?')) {
            window.location.href = '?logout=1';
        } else {
            reiniciarTiempoInactivo();
        }
    }
}

function limpiarPortapapeles() {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText('').catch(() => {});
    }
}

// ==================== FUNCIONES DE COMPATIBILIDAD ====================

// Alias para mantener compatibilidad con código existente
function copiarPortapapeles(texto) {
    copiarAlPortapapeles(texto, null);
}

function confirmarAccion(mensaje, callback) {
    if (confirm(mensaje)) {
        callback();
    }
}

// Función auxiliar mejorada para verificar fortaleza (versión simplificada)
function verificarFortaleza(password) {
    let puntos = 0;
    
    if (password.length >= 8) puntos++;
    if (password.length >= 12) puntos++;
    if (/[A-Z]/.test(password)) puntos++;
    if (/[a-z]/.test(password)) puntos++;
    if (/\d/.test(password)) puntos++;
    if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) puntos++;
    
    // Penalizar patrones comunes
    if (/(.)\1{2,}/.test(password)) puntos--;
    if (/123|abc|qwerty/i.test(password)) puntos--;
    
    return Math.max(0, Math.min(6, puntos));
}

// Limpiar datos temporales del localStorage
function limpiarDatosTemporales() {
    Object.keys(localStorage).forEach(key => {
        if (key.startsWith('temp_') || key.startsWith('form_')) {
            localStorage.removeItem(key);
        }
    });
}

// ==================== ESTILOS DINÁMICOS ====================

// Agregar estilos CSS para animaciones y mejoras visuales
const estilosDinamicos = document.createElement('style');
estilosDinamicos.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    
    .tooltip-custom {
        animation: fadeIn 0.2s ease-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-5px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .recomendaciones {
        background: rgba(255,255,255,0.1);
        padding: 8px;
        border-radius: 4px;
        margin-top: 5px;
    }
    
    /* Mejoras visuales para el tema oscuro */
    .tema-oscuro {
        background-color: #1a1a1a;
        color: #e0e0e0;
    }
    
    .tema-oscuro .contenedor {
        background-color: #2d2d2d;
        border: 1px solid #444;
    }
    
    .tema-oscuro input, .tema-oscuro textarea, .tema-oscuro select {
        background-color: #333;
        color: #e0e0e0;
        border-color: #555;
    }
    
    .tema-oscuro .boton {
        background-color: #444;
        color: #e0e0e0;
    }
    
    .tema-oscuro .boton:hover {
        background-color: #555;
    }
`;
document.head.appendChild(estilosDinamicos);

// ==================== INICIALIZACIÓN FINAL ====================

console.log('✅ Gestor de contraseñas - Sistema JavaScript avanzado cargado correctamente');
console.log('🔧 Funcionalidades: Generación, Evaluación, Validación, Seguridad, Auto-guardado');

// Log de diagnóstico después de la carga inicial
setTimeout(() => {
    const diagnostico = {
        campos_password: document.querySelectorAll('input[type="password"]').length,
        formularios: document.querySelectorAll('form').length,
        session_activa: document.body.hasAttribute('data-session'),
        soporte_clipboard: !!(navigator.clipboard && window.isSecureContext),
        local_storage_disponible: typeof(Storage) !== "undefined"
    };
    console.log('📊 Diagnóstico del sistema:', diagnostico);
}, 1000);