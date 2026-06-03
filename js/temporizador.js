// CONFIGURACIÓN DEL TIEMPO PARA PRUEBAS (1 minuto en total)
const TIEMPO_INACTIVIDAD = 60 * 1000;       // 60 segundos para cerrar sesión
const TIEMPO_ADVERTENCIA = 40 * 1000;       // 40 segundos para mostrar el mensaje

let temporizadorInactividad;
let temporizadorAdvertencia;
let modalAlerta; // Dejamos la variable declarada de forma global

// Ejecutar esto SOLO cuando la página esté completamente cargada en el navegador
window.addEventListener("DOMContentLoaded", function() {
    // 1. Crear el contenedor visual de la alerta (Modal)
    modalAlerta = document.createElement('div');
    modalAlerta.id = 'modal-autocierre';
    modalAlerta.style = `
        display: none;
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0, 0, 0, 0.85);
        z-index: 9999; justify-content: center; align-items: center;
        font-family: sans-serif; color: white;
    `;
    modalAlerta.innerHTML = `
        <div style="background: #1a1a2e; padding: 30px; border-radius: 12px; text-align: center; border: 2px solid #ff3333; max-width: 400px; width: 90%;">
            <h2 style="color: #ff3333; margin-top: 0;">⚠️ ¡Tu sesión va a expirar!</h2>
            <p>Has estado inactivo. Tu sesión se cerrará automáticamente en unos instantes por motivos de seguridad.</p>
            <button id="btn-seguir-conectado" style="background: #8a2be2; color: white; border: none; padding: 10px 20px; font-size: 16px; border-radius: 6px; cursor: pointer; font-weight: bold; margin-top: 15px;">
                Seguir conectado
            </button>
        </div>
    `;
    document.body.appendChild(modalAlerta);

    // Acción del botón "Seguir conectado"
    document.getElementById('btn-seguir-conectado').addEventListener('click', function() {
        fetch('gestion_consultas.php') 
            .then(() => {
                iniciarTemporizadores(); 
            })
            .catch(err => console.error("Error al extender sesión:", err));
    });

    // Arrancar el reloj una vez creado el entorno
    iniciarTemporizadores();
});

// 2. Funciones de control del tiempo
function iniciarTemporizadores() {
    clearTimeout(temporizadorInactividad);
    clearTimeout(temporizadorAdvertencia);
    ocultarAdvertencia();

    temporizadorAdvertencia = setTimeout(mostrarAdvertencia, TIEMPO_ADVERTENCIA);
    temporizadorInactividad = setTimeout(redirigirAlCierre, TIEMPO_INACTIVIDAD);
}

function mostrarAdvertencia() {
    if(modalAlerta) modalAlerta.style.display = 'flex';
}

function ocultarAdvertencia() {
    if(modalAlerta) modalAlerta.style.display = 'none';
}

function redirigirAlCierre() {
    window.location.href = 'login.php?error_sesion=inactividad';
}

function reiniciarInactividad() {
    if (modalAlerta && modalAlerta.style.display !== 'flex') {
        iniciarTemporizadores();
    }
}

// 3. Detectar actividad del usuario
const eventos = ['mousemove', 'keydown', 'click', 'scroll'];
eventos.forEach(evento => {
    window.addEventListener(evento, reiniciarInactividad);
});