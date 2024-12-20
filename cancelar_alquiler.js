document.addEventListener("DOMContentLoaded", () => {
    loadOccupiedVehicles();
  });
  
  function loadOccupiedVehicles() {
    const vehicleList = document.getElementById('occupied-vehicles');
  
    fetch('get_vehicles.php')
      .then(response => {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
      })
      .then(vehicles => {
        if (vehicles.error) {
          throw new Error(vehicles.error);
        }
        if (vehicles.length === 0) {
          vehicleList.innerHTML = `<p>No tienes vehículos alquilados actualmente.</p>`;
          return;
        }
  
        vehicleList.innerHTML = vehicles.map(vehicle => `
          <div class="vehicle-card">
            <h3>${vehicle.marca} ${vehicle.modelo}</h3>
            <p>Placa: ${vehicle.placa}</p>
            <button onclick="cancelRental(${vehicle.id_vehiculo})">Cancelar Alquiler</button>
          </div>
        `).join('');
      })
      .catch(error => {
        console.error('Error al cargar los vehículos ocupados:', error);
        vehicleList.innerHTML = `<p>Error al cargar los vehículos ocupados: ${error.message}</p>`;
      });
  }
  
  function cancelRental(vehicleId) {
    if (confirm('¿Estás seguro de que deseas cancelar este alquiler?')) {
      fetch('cancel_rental.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ vehicle_id: vehicleId }),
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert('Alquiler cancelado exitosamente.');
            loadOccupiedVehicles();
          } else {
            alert('Error al cancelar el alquiler: ' + (data.error || 'Desconocido'));
          }
        })
        .catch(error => {
          console.error('Error al cancelar el alquiler:', error);
          alert('Error al conectar con el servidor.');
        });
    }
  }
  