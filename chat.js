document.addEventListener('DOMContentLoaded', () => {
    const buscarUsuarioInput = document.getElementById('buscarUsuario');
    const listaConversaciones = document.querySelector('.lista-conversaciones');
    const nombreUsuario = document.getElementById('nombreUsuario');
    const fotoUsuario = document.getElementById('fotoUsuario');
    const mensajesChat = document.getElementById('mensajesChat');
    const inputMensaje = document.getElementById('inputMensaje'); // Corrección: id correcto
    const botonEnviar = document.querySelector('.entrada-mensaje button');

    let usuarioSeleccionado = null; // Para almacenar el usuario actualmente seleccionado

    // Función para buscar usuarios
    function buscarUsuarios() {
        const query = buscarUsuarioInput.value;
        fetch(`buscar_usuarios.php?query=${query}`)
            .then(response => response.json())
            .then(data => {
                console.log('Usuarios encontrados:', data);
                actualizarListaConversaciones(data);
            })
            .catch(error => console.error('Error al buscar usuarios:', error));
    }

    // Función para actualizar la lista de conversaciones
    function actualizarListaConversaciones(usuarios) {
        listaConversaciones.innerHTML = ''; // Limpia la lista existente

        if (usuarios.length === 0) {
            listaConversaciones.innerHTML = '<p>No se encontraron usuarios.</p>';
            return;
        }

        usuarios.forEach(usuario => {
            const div = document.createElement('div');
            div.className = 'conversacion';
            div.innerHTML = `
                <img src="uploads/${usuario.foto}" alt="Avatar" class="avatar">
                <div class="texto-conversacion">
                    <p><strong>${usuario.nombre}</strong></p>
                </div>
            `;

            // Agregar evento de clic para seleccionar usuario
            div.addEventListener('click', () => {
                seleccionarUsuario(usuario);
            });

            listaConversaciones.appendChild(div);
        });
    }

    // Función para seleccionar un usuario y mostrar su información
    function seleccionarUsuario(usuario) {
        usuarioSeleccionado = usuario; // Guardar usuario seleccionado
        nombreUsuario.textContent = usuario.nombre;
        fotoUsuario.src = `uploads/${usuario.foto}`;
        cargarMensajes(usuario.id_usuario);
    }

    // Función para cargar los mensajes de la conversación
    function cargarMensajes(idUsuario) {
        fetch(`obtener_mensajes.php?id_usuario=${idUsuario}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Mensajes cargados:', data.mensajes);
                    actualizarMensajesChat(data.mensajes);
                } else {
                    console.error('Error al cargar mensajes:', data.error);
                }
            })
            .catch(error => console.error('Error al cargar mensajes:', error));
    }

    // Función para actualizar los mensajes en el área de chat
    // Función para actualizar los mensajes en el área de chat
    // Función para actualizar los mensajes en el área de chat
    function actualizarMensajesChat(mensajes) {
        mensajesChat.innerHTML = ''; // Limpiar el área de chat

        mensajes.forEach(mensaje => {
            const div = document.createElement('div');
            div.className = 'mensaje';

            // Usa las claves correctas del mensaje: 'nombre_usuario' y 'mensaje'
            const textoMensaje = mensaje.mensaje; // El contenido del mensaje
            const nombreRemitente = mensaje.nombre_usuario; // El nombre del usuario que envió el mensaje

            div.innerHTML = `<p><strong>${nombreRemitente}:</strong> ${textoMensaje}</p>`;
            mensajesChat.appendChild(div);
        });

        // Desplazarse al último mensaje
        mensajesChat.scrollTop = mensajesChat.scrollHeight;
    }


    // Función para enviar un mensaje
    function enviarMensaje() {
        if (!usuarioSeleccionado || !inputMensaje.value.trim()) return;

        const mensaje = {
            destinatario: usuarioSeleccionado.id_usuario,
            texto: inputMensaje.value.trim()
        };

        fetch('enviar_mensaje.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(mensaje)
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Mensaje enviado:', data);
                    cargarMensajes(usuarioSeleccionado.id_usuario); // Recargar los mensajes
                    inputMensaje.value = ''; // Limpiar el campo de entrada
                } else {
                    console.error('Error al enviar el mensaje:', data.error);
                }
            })
            .catch(error => console.error('Error al enviar mensaje:', error));
    }

    // Escuchar el evento de entrada en el campo de búsqueda
    buscarUsuarioInput.addEventListener('input', buscarUsuarios);

    // Evento para enviar mensaje al hacer clic en el botón
    botonEnviar.addEventListener('click', enviarMensaje);

    // Evento para enviar mensaje al presionar Enter
    inputMensaje.addEventListener('keypress', event => {
        if (event.key === 'Enter') {
            enviarMensaje();
        }
    });

    // Carga inicial: Mostrar todos los usuarios
    fetch('buscar_usuarios.php')
        .then(response => response.json())
        .then(data => {
            console.log('Carga inicial de usuarios:', data);
            actualizarListaConversaciones(data);
        })
        .catch(error => console.error('Error al cargar usuarios:', error));
});
