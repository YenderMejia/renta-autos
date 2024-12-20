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
      body: JSON.stringify(filters)
    })
      .then(response => response.json())
      .then(vehicles => {
        vehicles.forEach(vehicle => {
          const card = document.createElement("div");
          card.classList.add("vehicle-card");
  
          card.innerHTML = `
            <img src="uploads/${vehicle.imagen}" alt="Imagen del vehículo">
            <h3>${vehicle.marca} ${vehicle.modelo}</h3>
            <p>Placa: ${vehicle.placa}</p>
            <p>Año: ${vehicle.anio}</p>
            <p>Autonomía: ${vehicle.autonomia} km</p>
            <p>Estado: ${vehicle.estado}</p>
            <p>Categoría: ${vehicle.categoria}</p>
            <p>Tarifa: $${vehicle.tarifa}/hora</p>
          `;
          vehicleList.appendChild(card);
        });
      });
  }
  
  function filterVehicles() {
    const category = document.getElementById("filter-category").value;
    const plate = document.getElementById("search-plate").value.trim();
    const status = document.getElementById("filter-status").value;
  
    const filters = { category, plate, status };
    loadVehicles(filters);
  }
  