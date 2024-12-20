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

async function loadUserData() {
    try {
        const response = await fetch('getusuario.php', { method: 'GET' });
        if (response.ok) {
            const data = await response.json();
            
            // Elementos del DOM donde se mostrarán los datos
            const userNameElement = document.getElementById('user-name');
            const userEmailElement = document.getElementById('user-email');
            const userPhotoElement = document.getElementById('user-photo');
            
            // Asignar los valores obtenidos
            userNameElement.textContent = data.nombre || 'Usuario';
            userEmailElement.textContent = data.correo || 'No disponible';
            
            // Si existe una foto, actualizar la imagen de perfil
            if (data.foto) {
                userPhotoElement.src = data.foto;
            } else {
                userPhotoElement.src = 'default-profile.png'; // Foto predeterminada si no existe
            }

            // Guardar el nombre en localStorage
            localStorage.setItem('user-name', data.nombre); // Asegúrate de usar 'data.nombre' y no 'user-name'
            
            // Verificar si se guardó correctamente
            console.log("Nombre guardado en localStorage:", localStorage.getItem('user-name'));
        } else {
            console.error('Error al obtener los datos del usuario.');
        }
    } catch (error) {
        console.error('Error en la solicitud:', error);
    }
}

// Cargar los datos al cargar la página
document.addEventListener('DOMContentLoaded', loadUserData);

// Cargar notificaciones
async function loadNotifications() {
    const notificationsList = document.getElementById('notifications-list');
    notificationsList.innerHTML = '<li>Cargando notificaciones...</li>'; // Mostrar mensaje de carga

    try {
        const userName = localStorage.getItem('user-name'); // Obtener el nombre del usuario
        if (!userName) {
            console.error('Nombre de usuario no encontrado en el almacenamiento local.');
            notificationsList.innerHTML = '<li>Error: Nombre de usuario no disponible.</li>';
            return;
        }

        const response = await fetch(`getnotificaciones.php?nombre_usuario=${encodeURIComponent(userName)}`, {
            method: 'GET',
        });

        if (response.ok) {
            const data = await response.json();

            if (data.success && data.notificaciones.length > 0) {
                notificationsList.innerHTML = '';
                data.notificaciones.forEach(notification => {
                    const li = document.createElement('li');
                    li.textContent = `${notification.mensaje} (fecha: ${notification.fecha_envio})`;
                    notificationsList.appendChild(li);
                });
            } else if (data.success) {
                notificationsList.innerHTML = '<li>No tienes notificaciones nuevas.</li>';
            } else {
                notificationsList.innerHTML = `<li>Error: ${data.message}</li>`;
            }
        } else {
            notificationsList.innerHTML = '<li>Error al cargar las notificaciones.</li>';
        }
    } catch (error) {
        console.error('Error al cargar notificaciones:', error);
        notificationsList.innerHTML = '<li>Error al conectar con el servidor.</li>';
    }
}

// Alternar ventana de notificaciones
function toggleNotifications() {
    const notificationsWindow = document.getElementById('notifications-window');
    const isVisible = notificationsWindow.style.display === 'block';
    notificationsWindow.style.display = isVisible ? 'none' : 'block';

    if (!isVisible) {
        loadNotifications(); // Cargar notificaciones al abrir
    }
}

// Cerrar ventana de notificaciones
function closeNotifications() {
    document.getElementById('notifications-window').style.display = 'none';
}