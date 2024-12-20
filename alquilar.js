document.addEventListener("DOMContentLoaded", () => {
    const urlParams = new URLSearchParams(window.location.search);
    const vehicleId = urlParams.get("vehiculo");

    fetch(`get_vehicle_details.php?id=${vehicleId}`)
        .then((response) => response.json())
        .then((vehicle) => {
            document.getElementById("vehicle-details").innerHTML = `
                <img src="uploads/${vehicle.imagen}" alt="Imagen del vehículo">

                <h2>${vehicle.marca} ${vehicle.modelo}</h2>
                <p>Tarifa por día: $${vehicle.tarifa}</p>
            `;

            const startDateInput = document.getElementById("start-date");
            const endDateInput = document.getElementById("end-date");

            endDateInput.addEventListener("input", () => {
                const startDate = new Date(startDateInput.value);
                const endDate = new Date(endDateInput.value);

                if (endDate > startDate) {
                    const days = (endDate - startDate) / (1000 * 60 * 60 * 24);
                    const total = days * vehicle.tarifa;
                    document.getElementById("total-amount").textContent = `Monto Total: $${total.toFixed(2)}`;
                } else {
                    document.getElementById("total-amount").textContent = "Monto Total: $0.00";
                }
            });
        });
});
function goBack() {
    window.location.href = "vehiculosclientes.html";
}
document.getElementById("rental-form").addEventListener("submit", (e) => {
    e.preventDefault();

    const paymentType = document.getElementById("payment-type").value;
    const startDate = document.getElementById("start-date").value;
    const endDate = document.getElementById("end-date").value;

    const urlParams = new URLSearchParams(window.location.search);
    const vehicleId = urlParams.get("vehiculo");

    fetch("rent_vehicle.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            vehicleId,
            paymentType,
            startDate,
            endDate,
        }),
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                alert("Alquiler realizado con éxito");
                window.location.href = "vehiculosclientes.html";
            } else {
                alert(data.error || "Error al procesar el alquiler");
            }
        })
        .catch((error) => {
            console.error("Error:", error);
            alert("Hubo un problema al procesar el alquiler.");
        });
});
