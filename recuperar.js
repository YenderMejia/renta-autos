async function checkCorreoCaptcha() {
    const correo = document.getElementById('correo').value;
    const captchaInput = document.getElementById('captcha-input').value;
    const captchaHidden = document.getElementById('captcha-hidden').value; // Captura correctamente el valor del captcha oculto

    if (!correo || !captchaInput) {
        alert("Por favor, ingrese el correo y resuelva el captcha.");
        return;
    }

    // Validación del captcha
    if (captchaInput !== captchaHidden) {
        alert("Captcha incorrecto.");
        return;
    }

    try {
        // Verificar si el correo existe en la base de datos
        const response = await fetch('verificar_correo.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ correo: correo })
        });

        const data = await response.json();

        if (data.existe) {
            // Si el correo existe, mostrar el segundo paso (pregunta y nueva contraseña)
            document.getElementById('step-1').style.display = 'none';
            document.getElementById('step-2').style.display = 'block';
            document.getElementById('pregunta-seguridad').placeholder = data.pregunta; // Mostrar la pregunta de seguridad
        } else {
            alert("El correo no está registrado.");
        }
    } catch (error) {
        console.error("Error al verificar el correo:", error);
        alert("Hubo un error en la verificación.");
    }
}
