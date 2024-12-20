document.addEventListener("DOMContentLoaded", () => {
const preguntaInput = document.getElementById("pregunta");
const respuestaInput = document.getElementById("respuesta");
const form = document.querySelector("form");

// Validar que pregunta y respuesta sean opcionales, pero dependientes entre sí
form.addEventListener("submit", (e) => {
    if (preguntaInput.value.trim() && !respuestaInput.value.trim()) {
    e.preventDefault();
    alert("Si ingresas una pregunta clave, debes ingresar la respuesta.");
    respuestaInput.focus();
    return;
    }

    if (!preguntaInput.value.trim() && respuestaInput.value.trim()) {
    e.preventDefault();
    alert("Si ingresas una respuesta clave, debes ingresar la pregunta.");
    preguntaInput.focus();
    return;
    }
});
});

//logica del formulario y datos actuales
document.getElementById('editarPerfilForm').addEventListener('submit', async function (event) {
    event.preventDefault(); // Evita el envío tradicional del formulario

    const form = event.target;
    const formData = new FormData(form); // Captura los datos del formulario

    try {
        const response = await fetch('procesar_edicion.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        const mensajeDiv = document.getElementById('mensaje');
        if (data.success) {
            mensajeDiv.style.color = 'green';
            mensajeDiv.textContent = data.message;
            mensajeDiv.style.display = 'block';

            // Limpiar el formulario
            form.reset();

            // Recargar los datos actuales
            await cargarDatosActuales();
        } else {
            mensajeDiv.style.color = 'red';
            mensajeDiv.textContent = data.message;
            mensajeDiv.style.display = 'block';
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Ocurrió un error al procesar la solicitud.');
    }
});

// Función para recargar los datos actuales
async function cargarDatosActuales() {
    try {
        const response = await fetch('obtener_datos_usuario.php');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        // Usar directamente los datos obtenidos
        document.getElementById('nombre-actual').textContent = data.nombre || 'No disponible';
        document.getElementById('correo-actual').textContent = data.correo || 'No disponible';
        document.getElementById('pregunta-actual').textContent = data.pregunta || 'No disponible';

        const foto = document.querySelector('.datos-actuales img');
        foto.src = data.foto ? `uploads/${data.foto}` : 'default-profile.png';
    } catch (error) {
        console.error('Error al cargar los datos:', error);
        alert('No se pudieron cargar los datos actuales.');
    }
}

// Llamar a la función al cargar la página
document.addEventListener('DOMContentLoaded', cargarDatosActuales);
