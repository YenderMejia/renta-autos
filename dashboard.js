// Verificar si el token existe en el localStorage
const token = localStorage.getItem('authToken');
if (!token) {
    // Redirigir al login si no hay token
    alert('Debes iniciar sesión para acceder a esta página.');
    window.location.href = 'login.html';
}

// Función para cerrar sesión
function logout() {
    localStorage.removeItem('authToken'); // Eliminar el token
    window.location.href = 'login.html'; // Redirigir al login
}

async function loadUserName() {
    try {
        const response = await fetch('getusuario.php', { method: 'GET' });
        if (response.ok) {
            const data = await response.json();
            const userNameElement = document.getElementById('user-name');
            userNameElement.textContent = data.nombre;
        } else {
            console.error('Error al obtener el nombre del usuario.');
        }
    } catch (error) {
        console.error('Error en la solicitud:', error);
    }
}
// Cargar el nombre al cargar la página
document.addEventListener('DOMContentLoaded', loadUserName);

// Sidebar toggle functionality
const sidebar = document.querySelector('.sidebar');
const mainContent = document.querySelector('main');
const toggleBtn = document.querySelector('.toggle-btn');

toggleBtn.addEventListener('click', () => {
    sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('expanded');
    toggleBtn.querySelector('i').classList.toggle('bi-chevron-right');
    toggleBtn.querySelector('i').classList.toggle('bi-chevron-left');
});

// Navigation active state
const navLinks = document.querySelectorAll('.nav-list a');
navLinks.forEach(link => {
    link.addEventListener('click', (e) => {
        navLinks.forEach(l => l.classList.remove('active'));
        link.classList.add('active');
    });
});

document.addEventListener("DOMContentLoaded", () => {
    // Cargar datos del panel al cargar la página
    loadDashboardData();
});

// Función para cargar los datos del panel
function loadDashboardData() {
    fetch("get_dashboard_data.php")
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Actualizar elementos del DOM
                document.getElementById("total-users").textContent = data.data.total_usuarios;
                document.getElementById("available-vehicles").textContent = data.data.total_disponibles;
                document.getElementById("in-use-vehicles").textContent = data.data.total_en_uso;
            } else {
                console.error("Error al obtener los datos:", data.message);
            }
        })
        .catch(error => console.error("Error en la solicitud:", error));
}

// Función para cargar contenido dinámico
function loadContent(section) {
    const content = document.getElementById("content");

    switch (section) {
        case "panel":
            // Mostrar indicador de carga mientras se cargan los datos
            content.innerHTML = `<p>Cargando datos...</p>`;

            // Cargar la estructura dinámica del panel
            setTimeout(() => {
                content.innerHTML = `
                    <h2>Panel de Control</h2>
                    <ul>
                        <p>Autos disponibles: <span id="available-vehicles">0</span></p>
                        <p>Autos en uso: <span id="in-use-vehicles">0</span></p>
                        <p>Cantidad de usuarios: <span id="total-users">0</span></p>
                    </ul>
                `;

                // Llamar a la función para cargar los datos del panel
                loadDashboardData();
            }, 500); // Simula un tiempo de carga
            break;
        case 'fleet':
            content.innerHTML = `
                <h2>Gestión de Flota</h2>
                
                <button onclick="showAddVehicleForm()">Agregar Vehículo</button>
                <br>
                <button onclick="showDeleteVehicleForm()">Eliminar Vehículo</button>
                <br>
                <button onclick="showEditVehicleForm()">Editar Vehículo</button>
                <br>
                <button class="view-vehicles-btn" onclick="window.location.href='vistavehiculos.html'">Ver Vehículos</button>
                <div id="add-vehicle-form" class="form-container">
                    <h3>Agregar Vehículo</h3>
                    <form id="vehicle-form" method="POST" enctype="multipart/form-data">
                        <input type="text" name="modelo" required placeholder="Modelo del vehículo">
                        <input type="text" name="marca" required placeholder="Marca">
                        <input type="number" name="anio" required placeholder="Año">
                        <input type="number" name="autonomia" required placeholder="Autonomía">
                        <input type="text" name="placa" required placeholder="Placa del vehículo">
                        <div class="form-group">
                            <select name="estado" required>
                                <option value="desocupado">Desocupado</option>
                                <option value="ocupado">Ocupado</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="imagen">Imagen del vehículo</label>
                            <input type="file" name="imagen" id="imagen" accept="image/*" required>
                        </div>
                        <div class="form-group">
                            <select name="nombre" required>
                                <option value="pequeño">Pequeño</option>
                                <option value="mediano">Mediano</option>
                                <option value="grande">Grande</option>
                                <option value="carga">Carga</option>
                            </select>
                            <input type="number" name="tarifa" required placeholder="Tarifa por día">
                        </div>
                        <button type="submit">Guardar Vehículo</button>
                    </form>
                </div>
                </div>
                <div id="edit-vehicle-form" class="form-container">
                    <h3>Editar Vehículo</h3>
                    <form id="edit-vehicle" method="POST">
                        <input type="text" name="placa_actual" required placeholder="Placa actual del vehículo">
                        <input type="text" name="modelo" placeholder="Nuevo modelo (opcional)">
                        <input type="text" name="marca" placeholder="Nueva marca (opcional)">
                        <input type="number" name="anio" placeholder="Nuevo año (opcional)">
                        <input type="number" name="autonomia" placeholder="Nueva Autonomía (opcional)">
                        <input type="text" name="placa" placeholder="Nueva placa (opcionl)">
                        <div class="form-group">
                            <select name="estado">
                                <option value="">cambiar estado (opcional)</option>
                                <option value="desocupado">Desocupado</option>
                                <option value="ocupado">Ocupado</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="imagen">Imagen del vehículo (opcional)</label>
                            <input type="file" name="imagen" id="imagen" accept="image/*">
                        </div>
                        <div class="form-group">
                            <select name="nombre">
                                <option value="">cambiar categoria (opcional)</option>
                                <option value="pequeño">Pequeño</option>
                                <option value="mediano">Mediano</option>
                                <option value="grande">Grande</option>
                                <option value="carga">Carga</option>
                            </select>
                            <input type="number" name="tarifa" placeholder="Tarifa por día (opcional)">
                        </div>
                        <button type="submit">Actualizar Vehículo</button>
                    </form>
                </div>
                <div id="delete-vehicle-form" class="form-container">
                    <h3>Eliminar Vehículo</h3>
                    <form id="delete-vehicle" method="POST">
                        <input type="text" name="placa" required placeholder="Placa del vehículo a eliminar">
                        <button type="submit">Eliminar Vehículo</button>
                    </form>
                </div>`;
            break;
        case 'users':
            content.innerHTML = `
                <h2>Gestión de Usuarios</h2>
                <button onclick="showAddUserForm()">Agregar Usuario</button>
                <br>
                <button onclick="showDeleteUserForm()">Eliminar Usuario</button>
                <br>
                <button onclick="showEditUserForm()">Editar Usuario</button>
                <br>
                <button onclick="showUserList()">Ver Usuarios</button>
                <div id="add-user-form" class="form-container">
                    <h3>Agregar Usuario</h3>
                    <form id="user-form" method="POST">
                        <input type="text" name="nombre" required placeholder="Nombre para usuario">
                        <input type="email" name="correo" required placeholder="Correo electrónico">
                        <input type="password" name="clave" required placeholder="Contraseña">
                        <input type="password" name="confirm_clave" required placeholder="Confirmar contraseña">
                        <div class="form-group">
                            <select name="rol" required>
                                <option value="cliente">Cliente</option>
                                <option value="administrador">Administrador</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <select name="estado" required>
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                            </select>
                        </div>      
                        <button type="submit">Guardar Usuario</button>
                    </form>
                </div>
                <div id="edit-user-form" class="form-container">
                    <h3>Editar Usuario</h3>
                    <form id="edit-form" method="POST">
                        <input type="text" name="nombre_usuario" required placeholder="Nombre de usuario a editar">
                        <input type="text" name="nombre" placeholder="Nuevo nombre (opcional)">
                        <input type="email" name="correo" placeholder="Nuevo correo electrónico (opcional)">
                        <div class="form-group">
                            <select name="rol">
                                <option value="">Cambiar rol (opcional)</option>
                                <option value="cliente">Cliente</option>
                                <option value="administrador">Administrador</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <select name="estado">
                                <option value="">Cambiar estado (opcional)</option>
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                            </select>
                        </div>
                        <button type="submit">Actualizar Usuario</button>
                    </form>
                </div>
                <div id="user-list" style="display: none;">
                    <h3>Lista de Usuarios</h3>
                    <input type="text" id="search-user" placeholder="Buscar por nombre" />
                    <button onclick="fetchUsers()">Buscar</button>
                    <table border="1">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Fecha Creación</th>
                                <th>Fecha Actualización</th>
                            </tr>
                        </thead>
                        <tbody id="user-table-body"></tbody>
                    </table>
                </div>
                <div id="delete-user-form" class="form-container">
                    <h3>Eliminar Usuario</h3>
                    <form id="delete-form" method="POST">
                        <input type="text" name="nombre_usuario" required placeholder="Nombre de usuario a eliminar">
                        <button type="submit">Eliminar Usuario</button>
                    </form>
                </div>`;
    break;
        case 'reports':
            content.innerHTML = `
                <h2>Reportes</h2>
                <form action="generar_reporte.php" method="get" target="_blank">
                    <button type="submit">Generar Reporte</button>
                </form>`;
            break;
        case 'support':
            content.innerHTML = `
                <h2>Soporte</h2>
                <button onclick="enviarNotifica()">Enviar Notificaciones</button>
                <br>
                <button onclick="verNotificaciones()">Ver Notificaciones</button>
                <br>
                <button>Mensajes a Usuarios</button>
                <div id="notification-form" class="form-container">
                    <h3>Enviar Notificación</h3>
                    <form id="notification-form-element" method="POST">
                        <input type="text" name="notificationType" required placeholder="Tipo de notificación">
                        <textarea name="message" required placeholder="Mensaje de la notificación"></textarea>
                        <div class="form-group">
                            <select id="notification-type" name="notificationType">
                                <option value="all">Notificación general</option>
                                <option value="specific">Notificación específica</option>
                            </select>
                        </div>
                        <div id="specific-user-field" class="form-group" style="display: none;">
                            <input type="text" name="specificUsername" placeholder="Usuario específico">
                        </div>
                        <button type="submit">Enviar Notificación</button>
                    </form>
                    <br>
                    <button id="back-to-main" style="display: none;">Volver</button>
                </div>`;
            break;
        default:
            content.innerHTML = `<h2>Bienvenido</h2>`;
    }
}
//apartado de usuarios---------------------------------------------------------------------------------------------------
// Mostrar formulario de agregar usuario
function showAddUserForm() {
    const form = document.getElementById('add-user-form');
    const buttons = document.querySelectorAll('#content > button');

    // Ocultar los botones y mostrar el formulario
    buttons.forEach(button => button.style.display = 'none');
    form.style.display = 'block';

    // Crear botón para regresar a los botones
    if (!document.getElementById('back-to-buttons')) {
        const backButton = document.createElement('button');
        backButton.id = 'back-to-buttons';
        backButton.textContent = 'Volver';
        backButton.onclick = () => {
            // Ocultar el formulario y mostrar los botones
            form.style.display = 'none';
            buttons.forEach(button => button.style.display = 'block');
            backButton.remove();
        };
        form.parentElement.insertBefore(backButton, form);
    }

    // Manejo del evento de envío del formulario
    const userForm = document.getElementById('user-form');
    userForm.addEventListener('submit', async function (event) {
        event.preventDefault(); // Evitar que se recargue la página

        const formData = new FormData(userForm);  // Recoger los datos del formulario
        const userData = new URLSearchParams(formData);  // Convertirlos a URLSearchParams

        try {
            const response = await fetch('crearusuario.php', {
                method: 'POST',
                body: userData  // Enviar los datos como URLSearchParams
            });

            const result = await response.json();  // Esperar la respuesta en formato JSON
            if (result.success) {
                alert('Usuario agregado correctamente.');
                // Ocultar el formulario y mostrar los botones
                form.style.display = 'none';
                buttons.forEach(button => button.style.display = 'block');
                document.getElementById('back-to-buttons').remove();
                userForm.reset(); // Limpiar el formulario
            } else {
                alert('Error al agregar el usuario: ' + result.message);
            }
        } catch (error) {
            console.error('Error en la solicitud:', error);
            alert('Ocurrió un error inesperado.');
        }
    });

}

function showEditUserForm() {
    const form = document.getElementById('edit-user-form');
    const buttons = document.querySelectorAll('#content > button');

    buttons.forEach(button => button.style.display = 'none');
    form.style.display = 'block';

    if (!document.getElementById('back-to-buttons-edit')) {
        const backButton = document.createElement('button');
        backButton.id = 'back-to-buttons-edit';
        backButton.textContent = 'Volver';
        backButton.onclick = () => {
            form.style.display = 'none';
            buttons.forEach(button => button.style.display = 'block');
            backButton.remove();
        };
        form.parentElement.insertBefore(backButton, form);
    }

    const editForm = document.getElementById('edit-form');
    editForm.addEventListener('submit', async function (event) {
        event.preventDefault();

        const formData = new FormData(editForm);
        const userData = Object.fromEntries(formData.entries());

        try {
            const response = await fetch('editarusuario.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(userData)
            });

            const result = await response.json();
            if (result.success) {
                alert('Usuario actualizado correctamente.');
                form.style.display = 'none';
                buttons.forEach(button => button.style.display = 'block');
                document.getElementById('back-to-buttons-edit').remove();
                editForm.reset();
            } else {
                alert('Error al actualizar el usuario: ' + result.message);
            }
        } catch (error) {
            console.error('Error en la solicitud:', error);
            alert('Ocurrió un error inesperado.');
        }
    });
}

function showDeleteUserForm() {
    const form = document.getElementById('delete-user-form');
    const buttons = document.querySelectorAll('#content > button');

    buttons.forEach(button => button.style.display = 'none');
    form.style.display = 'block';

    if (!document.getElementById('back-to-buttons-delete')) {
        const backButton = document.createElement('button');
        backButton.id = 'back-to-buttons-delete';
        backButton.textContent = 'Volver';
        backButton.onclick = () => {
            form.style.display = 'none';
            buttons.forEach(button => button.style.display = 'block');
            backButton.remove();
        };
        form.parentElement.insertBefore(backButton, form);
    }

    const deleteForm = document.getElementById('delete-form');
    deleteForm.addEventListener('submit', async function (event) {
        event.preventDefault();

        const formData = new FormData(deleteForm);
        const userData = Object.fromEntries(formData.entries());

        try {
            const response = await fetch('eliminarusuario.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(userData)
            });

            const result = await response.json();
            if (result.success) {
                alert('Usuario eliminado correctamente.');
                form.style.display = 'none';
                buttons.forEach(button => button.style.display = 'block');
                document.getElementById('back-to-buttons-delete').remove();
                deleteForm.reset();
            } else {
                alert('Error al eliminar el usuario: ' + result.message);
            }
        } catch (error) {
            console.error('Error en la solicitud:', error);
            alert('Ocurrió un error inesperado.');
        }
    });
}

async function fetchUsers() {
    const searchValue = document.getElementById('search-user').value;

    try {
        const response = await fetch(`verusuarios.php?nombre_usuario=${encodeURIComponent(searchValue)}`);
        const result = await response.json();

        if (result.success) {
            const tableBody = document.getElementById('user-table-body');
            tableBody.innerHTML = ''; // Limpiar tabla

            result.data.forEach(user => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${user.nombre}</td>
                    <td>${user.correo}</td>
                    <td>${user.rol}</td>
                    <td>${user.estado}</td>
                    <td>${user.fecha_creacion}</td>
                    <td>${user.fecha_actualizacion}</td>
                `;
                tableBody.appendChild(row);
            });
        } else {
            alert('No se encontraron usuarios.');
        }
    } catch (error) {
        console.error('Error al obtener los usuarios:', error);
        alert('Ocurrió un error al obtener los datos.');
    }
}

// Mostrar u ocultar la lista de usuarios
function showUserList() {
    const userList = document.getElementById('user-list');
    const buttons = document.querySelectorAll('#content > button');

    buttons.forEach(button => button.style.display = 'none');
    userList.style.display = 'block';

    if (!document.getElementById('back-to-buttons-userlist')) {
        const backButton = document.createElement('button');
        backButton.id = 'back-to-buttons-userlist';
        backButton.textContent = 'Volver';
        backButton.onclick = () => {
            userList.style.display = 'none';
            buttons.forEach(button => button.style.display = 'block');
            backButton.remove();
        };
        userList.parentElement.insertBefore(backButton, userList);
    }

    fetchUsers(); // Cargar los usuarios automáticamente
}
// apartado de notificaciones---------------------------------------------------------------------------------------
function enviarNotifica() {
    const notificationForm = document.getElementById('notification-form');
    const notificationFormElement = document.getElementById('notification-form-element');
    const specificField = document.getElementById('specific-user-field');
    const contentButtons = document.querySelectorAll('#content > button');
    const backButton = document.getElementById('back-to-main');

    // Ocultar botones principales y mostrar el formulario
    contentButtons.forEach(button => button.style.display = 'none');
    notificationForm.style.display = 'block';
    backButton.style.display = 'inline-block';

    // Mostrar u ocultar el campo de usuario específico según el tipo de notificación
    const notificationType = document.getElementById('notification-type');
    notificationType.addEventListener('change', (e) => {
        specificField.style.display = e.target.value === 'specific' ? 'block' : 'none';
    });

    // Manejar el botón "Volver"
    backButton.onclick = () => {
        notificationForm.style.display = 'none';
        contentButtons.forEach(button => button.style.display = 'inline-block');
        specificField.style.display = 'none'; // Asegurar que el campo específico esté oculto
        backButton.style.display = 'none'; // Ocultar el botón de volver
        notificationFormElement.reset(); // Limpiar el formulario
    };

    // Manejar el envío del formulario
    notificationFormElement.onsubmit = async (event) => {
        event.preventDefault();

        const formData = new FormData(notificationFormElement);
        const payload = Object.fromEntries(formData.entries());

        try {
            const response = await fetch('enviarnotificacion.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });

            const result = await response.json();
            if (result.success) {
                alert('Notificación enviada correctamente.');

                // Restaurar botones principales y ocultar el formulario
                notificationForm.style.display = 'none';
                contentButtons.forEach(button => button.style.display = 'inline-block');
                backButton.style.display = 'none';
                specificField.style.display = 'none';
                notificationFormElement.reset();
            } else {
                alert('Error al enviar la notificación: ' + result.message);
            }
        } catch (error) {
            console.error('Error al enviar la notificación:', error);
            alert('Ocurrió un error inesperado.');
        }
    };
}
// Mostrar formulario de ver notificaciones
function verNotificaciones() {
    const content = document.getElementById('content');
    content.innerHTML = `
        <h2>Notificaciones</h2>
        <table border="1">
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Mensaje</th>
                    <th>Fecha de Envío</th>
                    <th>Estado</th>
                    <th>Usuario</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="notification-table-body"></tbody>
        </table>
        <button id="back-to-main">Volver</button>
    `;

    document.getElementById('back-to-main').onclick = loadContent.bind(null, 'support');

    cargarNotificaciones(); // Llamar para llenar la tabla
}

// Cargar notificaciones desde el servidor
async function cargarNotificaciones() {
    try {
        const response = await fetch('vernotificaciones.php', { method: 'GET' });
        const result = await response.json();

        if (result.success) {
            const tbody = document.getElementById('notification-table-body');
            tbody.innerHTML = ''; // Limpiar la tabla
            result.notificaciones.forEach((notificacion) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${notificacion.tipo}</td>
                    <td>${notificacion.mensaje}</td>
                    <td>${notificacion.fecha_envio}</td>
                    <td>${notificacion.estado}</td>
                    <td>${notificacion.usuario || 'General'}</td>
                    <td>
                        <button onclick="eliminarNotificacion(${notificacion.id_notificacion})">Eliminar</button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        } else {
            alert('No se encontraron notificaciones.');
        }
    } catch (error) {
        console.error('Error al cargar notificaciones:', error);
        alert('Error al cargar notificaciones.');
    }
}


// Eliminar una notificación
async function eliminarNotificacion(id_notificacion) {
    if (confirm('¿Estás seguro de eliminar esta notificación?')) {
        try {
            const response = await fetch('eliminarnotificacion.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_notificacion })
            });
            const result = await response.json();

            if (result.success) {
                alert('Notificación eliminada correctamente.');
                cargarNotificaciones(); // Actualizar la lista
            } else {
                alert('Error al eliminar la notificación: ' + result.message);
            }
        } catch (error) {
            console.error('Error al eliminar la notificación:', error);
            alert('Error al eliminar la notificación.');
        }
    }
}

//apartado de vehiculo----------------------------------------------------------------------------------------------------
// Mostrar formulario de agregar vehículo y Manejo del evento de envío del formulario para agregar vehículo
function showAddVehicleForm() {
    const form = document.getElementById('add-vehicle-form');
    const vehicleForm = document.getElementById('vehicle-form'); // Asegúrate de que existe

    toggleFormsAndButtons(form);

    vehicleForm.addEventListener('submit', async function (event) {
        event.preventDefault();

        const formData = new FormData(vehicleForm);

        try {
            const response = await fetch('agregarvehiculo.php', {
                method: 'POST',
                body: formData // Enviar FormData directamente
            });

            const result = await response.json();

            if (result.success) {
                alert('Vehículo agregado correctamente.');
                form.style.display = 'none';
                document.querySelectorAll('#content > button').forEach(button => button.style.display = 'block');
                document.getElementById('back-to-buttons-vehicle').remove();
                vehicleForm.reset(); // Limpiar el formulario
            } else {
                alert('Error al agregar el vehículo: ' + result.message);
            }
        } catch (error) {
            console.error('Error en la solicitud:', error);
            alert('Ocurrió un error inesperado.');
        }
    });
}


// Mostrar formulario de editar vehículo
function showEditVehicleForm() {
    const form = document.getElementById('edit-vehicle-form');
    const editVehicle = document.getElementById('edit-vehicle');

    toggleFormsAndButtons(form);

    editVehicle.addEventListener('submit', async function (event){
        event.preventDefault();

        const formData = new FormData(editVehicle);

        try {
            const response = await fetch('editarvehiculo.php', {
                method: 'POST',
                body: formData // Enviar FormData directamente
            });

            const result = await response.json();

            if (result.success) {
                alert('Vehículo editado correctamente.');
                form.style.display = 'none';
                document.querySelectorAll('#content > button').forEach(button => button.style.display = 'block');
                document.getElementById('back-to-buttons-vehicle').remove();
                editVehicle.reset(); // Limpiar el formulario
            } else {
                alert('Error al editar el vehículo: ' + result.message);
            }
        } catch (error) {
            console.error('Error en la solicitud:', error);
            alert('Vehículo editado correctamente.');
            form.style.display = 'none';
            document.querySelectorAll('#content > button').forEach(button => button.style.display = 'block');
            document.getElementById('back-to-buttons-vehicle').remove();
            editVehicle.reset(); // Limpiar el formulario
        }
    });
}

// Mostrar formulario de eliminar vehículo
function showDeleteVehicleForm() {
    const form = document.getElementById('delete-vehicle-form');
    const deleteVehicle = document.getElementById('delete-vehicle');

    toggleFormsAndButtons(form);

    deleteVehicle.addEventListener('submit', async function (event) {
        event.preventDefault();

        const formData = new FormData(deleteVehicle);

        try {
            const response = await fetch('eliminarvehiculo.php', {
                method: 'POST',
                body: formData // Enviar FormData directamente
            });

            const result = await response.json();

            if (result.success) {
                alert('Vehículo eliminado correctamente.');
                form.style.display = 'none';
                document.querySelectorAll('#content > button').forEach(button => button.style.display = 'block');
                document.getElementById('back-to-buttons-vehicle').remove();
                deleteVehicle.reset(); // Limpiar el formulario
            } else {
                alert('Error al eliminar el vehículo: ' + result.message);
            }
        } catch (error) {
            console.error('Error en la solicitud:', error);
            alert('Error al eliminar el vehículo.');
        }
    });
}

// Alternar entre formularios y botones
function toggleFormsAndButtons(activeForm) {
    const forms = document.querySelectorAll('.form-container');
    const buttons = document.querySelectorAll('#content > button');
    
    forms.forEach(form => form.style.display = 'none');
    buttons.forEach(button => button.style.display = 'none');
    
    activeForm.style.display = 'block';
    
    if (!document.getElementById('back-to-buttons-vehicle')) {
        const backButton = document.createElement('button');
        backButton.id = 'back-to-buttons-vehicle';
        backButton.textContent = 'Volver';
        backButton.onclick = () => {
            activeForm.style.display = 'none';
            buttons.forEach(button => button.style.display = 'block');
            backButton.remove();
        };
        activeForm.parentElement.insertBefore(backButton, activeForm);
    }
}
//apartado de reportes-----------------------------------------------------------------------------------------------------------
