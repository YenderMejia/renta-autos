document.addEventListener("DOMContentLoaded", () => {
    loadCategories();
    loadVehicles();
  });
  
  function loadCategories() {
    // Lógica para cargar categorías desde el backend
    const categorySelect = document.getElementById("filter-category");
    fetch("get_categories.php")
      .then(response => response.json())
      .then(categories => {
        categories.forEach(category => {
          const option = document.createElement("option");
          option.value = category.id_categoria;
          option.textContent = category.nombre;
          categorySelect.appendChild(option);
        });
      });
  }
  
function loadVehicles(filters = {}) {
    const vehicleList = document.getElementById("vehicle-list");
    vehicleList.innerHTML = "";

    fetch("get_vehicles.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(filters),
    })
        .then((response) => response.json())
        .then((vehicles) => {
            vehicles.forEach((vehicle) => {
                if (vehicle.estado === "desocupado") { // Filtrar solo desocupados
                    const card = document.createElement("div");
                    card.classList.add("vehicle-card");

                    card.innerHTML = `
                        <img src="uploads/${vehicle.imagen}" alt="Imagen del vehículo">
                        <h3>${vehicle.marca} ${vehicle.modelo}</h3>
                        <p>Año: ${vehicle.anio}</p>
                        <p>Placa: ${vehicle.placa}</p>
                        <p>Autonomía: ${vehicle.autonomia} km</p>
                        <p>Tarifa por día: $${vehicle.tarifa}</p>
                        <button onclick="goToRental(${vehicle.id_vehiculo})">Alquilar</button>
                    `;
                    vehicleList.appendChild(card);
                }
            });
        });
}

function goToRental(vehicleId) {
    window.location.href = `alquilar.html?vehiculo=${vehicleId}`;
}
function loadOccupiedVehicles() {
  const userId = localStorage.getItem('user_id');
  const occupiedVehiclesList = document.getElementById('occupied-vehicles');

  fetch(`get_vehicles.php?user_id=${userId}`)
      .then(response => response.json())
      .then(vehicles => {
          occupiedVehiclesList.innerHTML = vehicles.map(vehicle => `
              <div class="vehicle-card">
                  <h3>${vehicle.marca} ${vehicle.modelo}</h3>
                  <p>Placa: ${vehicle.placa}</p>
                  <button onclick="cancelRental(${vehicle.id_vehiculo})">Cancelar Alquiler</button>
              </div>
          `).join('');
      });
}

function cancelRental(vehicleId) {
  fetch('cancel_rental.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ vehicle_id: vehicleId }),
  })
  .then(response => response.json())
  .then(data => {
      if (data.success) {
          alert('Alquiler cancelado correctamente.');
          loadOccupiedVehicles();
      } else {
          alert('Error al cancelar el alquiler.');
      }
  });
}

document.addEventListener("DOMContentLoaded", () => {
  loadOccupiedVehicles();
});
