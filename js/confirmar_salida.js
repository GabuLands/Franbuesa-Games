document.addEventListener("DOMContentLoaded", function () {
    // 1. Crear el HTML del modal dinámicamente con los estilos oscuros/morados
    const modalSalir = document.createElement('div');
    modalSalir.id = 'modal-confirmar-salida';
    modalSalir.style = `
        display: none;
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0, 0, 0, 0.8);
        z-index: 10000; justify-content: center; align-items: center;
        font-family: sans-serif; color: white;
    `;
    modalSalir.innerHTML = `
        <div style="background: #1a1a2e; padding: 25px; border-radius: 12px; text-align: center; border: 2px solid #8a2be2; max-width: 350px; width: 85%;">
            <h3 style="color: #a155e8; margin-top: 0;">¿Cerrar Sesión?</h3>
            <p style="font-size: 14px; color: #ccc;">¿Estás seguro de que deseas salir de Franbuesa-Games?</p>
            <div style="display: flex; gap: 10px; justify-content: center; margin-top: 20px;">
                <button id="btn-confirmar-logout" style="background: #ff3333; color: white; border: none; padding: 10px 20px; font-weight: bold; border-radius: 6px; cursor: pointer; flex: 1;">
                    Salir
                </button>
                <button id="btn-cancelar-logout" style="background: #333; color: #ccc; border: 1px solid #555; padding: 10px 20px; font-weight: bold; border-radius: 6px; cursor: pointer; flex: 1;">
                    Cancelar
                </button>
            </div>
        </div>
    `;
    document.body.appendChild(modalSalir);

    // 2. Capturar el enlace de "Gestión" o donde tengas el botón de salir
    // Buscaremos cualquier enlace que apunte a logout.php
    document.body.addEventListener("click", function (e) {
        const enlaceLogout = e.target.closest("a[href='logout.php']");
        if (enlaceLogout) {
            e.preventDefault(); // Frenamos la redirección automática
            modalSalir.style.display = 'flex'; // Mostramos el modal
        }
    });

    // Action: Si pulsa "Salir"
    document.getElementById("btn-confirmar-logout").addEventListener("click", function () {
        window.location.href = "logout.php";
    });

    // Action: Si pulsa "Cancelar"
    document.getElementById("btn-cancelar-logout").addEventListener("click", function () {
        modalSalir.style.display = 'none';
    });
});