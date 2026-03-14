let recetasSeleccionadas = [];

function mostrarFormulario() {
  const formulario = document.getElementById("formularioPresupuesto");
  formulario.style.display = "block";
  formulario.scrollIntoView({ behavior: "smooth" });

  // Limpiar formulario
  document.getElementById("nombre").value = "";
  document.getElementById("apellido").value = "";
  document.getElementById("telefono").value = "";
  document.getElementById("email").value = "";
  document.getElementById("observaciones").value = "";
  document.querySelector("#tablaRecetas tbody").innerHTML = "";
  recetasSeleccionadas = [];
  actualizarTotalPresupuesto();
}

// Modales
function abrirModalRecetas() {
  document.getElementById("modalRecetas").style.display = "block";
  document.getElementById("buscadorRecetas").value = "";
  filtrarRecetas();
}

function cerrarModalRecetas() {
  document.getElementById("modalRecetas").style.display = "none";
}

function abrirModalCantidadPrecio() {
  document.getElementById("modalCantidadPrecio").style.display = "block";
}

function cerrarModalCantidadPrecio() {
  document.getElementById("modalCantidadPrecio").style.display = "none";
}

// Buscador de recetas
function filtrarRecetas() {
  const filtro = document.getElementById("buscadorRecetas").value.toLowerCase();
  const recetas = document.querySelectorAll(".receta-item");
  
  recetas.forEach(receta => {
    const nombre = receta.dataset.nombre.toLowerCase();
    if (nombre.includes(filtro)) {
      receta.style.display = "block";
    } else {
      receta.style.display = "none";
    }
  });
}

// Event listener para el buscador
document.getElementById("buscadorRecetas").addEventListener("input", filtrarRecetas);

// Seleccionar receta
function seleccionarReceta(id, nombre) {
  document.getElementById("recetaSeleccionadaId").value = id;
  document.getElementById("recetaSeleccionadaNombre").value = nombre;
  document.getElementById("nombreRecetaSeleccionada").textContent = nombre;
  document.getElementById("cantidadReceta").value = 1;
  document.getElementById("precioReceta").value = "";
  
  cerrarModalRecetas();
  abrirModalCantidadPrecio();
}

// Agregar receta al presupuesto
function agregarRecetaAlPresupuesto() {
  const recetaId = document.getElementById("recetaSeleccionadaId").value;
  const nombre = document.getElementById("recetaSeleccionadaNombre").value;
  const cantidad = parseFloat(document.getElementById("cantidadReceta").value);
  const precio = parseFloat(document.getElementById("precioReceta").value);
  
  // Validaciones
  if (!recetaId || !nombre) {
    alert("Error: No se ha seleccionado ninguna receta");
    return;
  }
  
  if (isNaN(cantidad) || cantidad <= 0) {
    alert("Por favor ingrese una cantidad válida (mayor a 0)");
    document.getElementById("cantidadReceta").focus();
    return;
  }
  
  if (isNaN(precio) || precio <= 0) {
    alert("Por favor ingrese un precio válido (mayor a 0)");
    document.getElementById("precioReceta").focus();
    return;
  }
  
  const total = cantidad * precio;
  
  // Verificar si ya existe
  if (recetasSeleccionadas.some(r => r.receta_id == recetaId)) {
    alert("Esta receta ya fue agregada al presupuesto");
    return;
  }
  
  // Agregar a la lista
  recetasSeleccionadas.push({
    receta_id: recetaId,
    nombre: nombre,
    cantidad: cantidad,
    precio_unitario: precio,
    total: total
  });
  
  // Agregar a la tabla
  const tbody = document.querySelector("#tablaRecetas tbody");
  const fila = document.createElement("tr");
  fila.innerHTML = `
    <td>${nombre}</td>
    <td>${cantidad}</td>
    <td>$${precio.toFixed(2)}</td>
    <td>$${total.toFixed(2)}</td>
    <td><button type="button" onclick="eliminarReceta(${recetaId})">Eliminar</button></td>
  `;
  tbody.appendChild(fila);
  
  actualizarTotalPresupuesto();
  cerrarModalCantidadPrecio();
}

// Eliminar receta
function eliminarReceta(recetaId) {
  recetasSeleccionadas = recetasSeleccionadas.filter(r => r.receta_id != recetaId);
  actualizarTablaRecetas();
  actualizarTotalPresupuesto();
}

function actualizarTablaRecetas() {
  const tbody = document.querySelector("#tablaRecetas tbody");
  tbody.innerHTML = '';
  
  recetasSeleccionadas.forEach(receta => {
    const fila = document.createElement("tr");
    fila.innerHTML = `
      <td>${receta.nombre}</td>
      <td>${receta.cantidad}</td>
      <td>$${receta.precio_unitario.toFixed(2)}</td>
      <td>$${receta.total.toFixed(2)}</td>
      <td><button type="button" onclick="eliminarReceta(${receta.receta_id})">Eliminar</button></td>
    `;
    tbody.appendChild(fila);
  });
}

function actualizarTotalPresupuesto() {
  let total = 0;
  recetasSeleccionadas.forEach(receta => {
    total += receta.total;
  });
  
  document.getElementById("totalPresupuesto").textContent = `$${total.toFixed(2)}`;
  document.getElementById("inputTotal").value = total.toFixed(2);
  document.getElementById("inputRecetas").value = JSON.stringify(recetasSeleccionadas);
}

// Validación del formulario
document.getElementById("formPresupuesto").addEventListener("submit", function(e) {
  if (recetasSeleccionadas.length === 0) {
    e.preventDefault();
    alert("Debe agregar al menos una receta al presupuesto");
  }
});

// Permitir cerrar con ESC
document.addEventListener("keydown", function(event) {
  if (event.key === "Escape") {
    cerrarModalRecetas();
    cerrarModalCantidadPrecio();
  }
});