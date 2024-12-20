let generatedCaptcha = ''; // Variable global para almacenar el captcha

function generateCaptcha() {
    const chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    generatedCaptcha = '';
    for (let i = 0; i < 6; i++) {
        generatedCaptcha += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById('captcha').textContent = generatedCaptcha;
    document.getElementById('captcha-hidden').value = generatedCaptcha; // Sincroniza al generar
}

// Cargar el captcha al cargar la página
window.onload = function () {
    generateCaptcha();
};

// Sincronizar el captcha antes de enviar el formulario (por seguridad)
document.getElementById('.register-form').onsubmit = function () {
    document.getElementById('captcha-hidden').value = generatedCaptcha; // Actualiza antes de enviar
};
document.getElementById('.login-form').onsubmit = function () {
    document.getElementById('captcha-hidden').value = generatedCaptcha; // Actualiza antes de enviar
};