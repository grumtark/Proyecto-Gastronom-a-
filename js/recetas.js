    let ingredientes = [];

    function mostrarFormulario() {
      document.getElementById("formularioReceta").style.display = "block";
      document.getElementById("titulo").value = "";
      document.getElementById("descripcion").value = "";
      document.getElementById("pasos").value = "";
      ingredientes = [];
      document.querySelector("#tablaIngredientes tbody").innerHTML = "";
    }

    // 🔹 Nuevo: abrir y cerrar modal productos
    function abrirModalProductos(){ 
        document.getElementById("modalProductos").style.display="block";
        document.getElementById("buscadorProductos").value = ""; // Limpiar buscador
        filtrarProductos(); // Mostrar todos los productos
    }
    function cerrarModalProductos(){ document.getElementById("modalProductos").style.display="none"; }

    function aceptarProductos(){
        const filas = document.querySelectorAll(".fila-producto");
        let productosSeleccionados = false;
        
        filas.forEach(fila => {
            if (fila.style.display !== 'none') { // Solo procesar filas visibles
                const check = fila.querySelector(".check-producto");
                const cantidadInput = fila.querySelector(".cantidad");
                const cantidad = parseFloat(cantidadInput.value);
                
                if (check.checked && cantidad > 0) {
                    const id = check.dataset.id;
                    const nombre = check.dataset.nombre;
                    const unidad = check.dataset.unidad;
                    
                    // Verificar si ya existe este ingrediente
                    const existe = ingredientes.some(i => i.producto_id == id);
                    if (!existe) {
                        ingredientes.push({
                            producto_id: id,
                            ingrediente: nombre,
                            cantidad: cantidad,
                            unidad: unidad
                        });
                        agregarFilaTabla(nombre, cantidad, unidad);
                        productosSeleccionados = true;
                    } else {
                        alert(`"${nombre}" ya fue agregado a la receta`);
                    }
                    
                    // Resetear inputs
                    check.checked = false;
                    cantidadInput.value = "";
                }
            }
        });
        
        if (productosSeleccionados) {
            document.getElementById("inputIngredientes").value = JSON.stringify(ingredientes);
            cerrarModalProductos();
        } else {
            alert("Selecciona al menos un producto con cantidad mayor a 0");
        }
    }

    function agregarFilaTabla(nombre, cantidad, unidad){
        const tbody = document.querySelector("#tablaIngredientes tbody");
        const fila = document.createElement("tr");
        fila.innerHTML = `
            <td>${nombre}</td>
            <td>${cantidad}</td>
            <td>${unidad}</td>
            <td><button type="button" onclick="eliminarFila(this)">Eliminar</button></td>
        `;
        tbody.appendChild(fila);
    }

    function eliminarFila(btn){
        const fila = btn.closest("tr");
        const nombre = fila.children[0].innerText;
        
        // Buscar y eliminar por producto_id si está disponible, sino por nombre
        ingredientes = ingredientes.filter(i => {
            if (i.producto_id) {
                // Crear elemento temporal para comparar el nombre mostrado
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = i.ingrediente;
                const nombreIngrediente = tempDiv.textContent;
                return nombreIngrediente !== nombre;
            } else {
                return i.ingrediente !== nombre;
            }
        });
        
        fila.remove();
        document.getElementById("inputIngredientes").value = JSON.stringify(ingredientes);
    }

    function verReceta(titulo, descripcion, pasos, ingredientesHTML) {
      document.getElementById("vTitulo").textContent = titulo;
      document.getElementById("vDescripcion").textContent = descripcion;
      document.getElementById("vPasos").textContent = pasos;
      document.getElementById("vPasos").innerHTML = pasos.replace(/\n/g, "<br>");
      document.getElementById("vIngredientes").innerHTML = ingredientesHTML;
      document.getElementById("modalVer").style.display = "block";
    }

    // 🔹 Función para filtrar productos
    function filtrarProductos() {
        const buscador = document.getElementById('buscadorProductos');
        const filtro = buscador.value.toLowerCase();
        const filas = document.querySelectorAll('.fila-producto');
        
        filas.forEach(fila => {
            const nombreProducto = fila.querySelector('td:nth-child(2)').textContent.toLowerCase();
            if (nombreProducto.includes(filtro)) {
                fila.style.display = '';
            } else {
                fila.style.display = 'none';
            }
        });
    }

    // 🔹 Función para marcar automáticamente al ingresar cantidad
    function marcarSiTieneCantidad(input) {
        const fila = input.closest('tr');
        const check = fila.querySelector('.check-producto');
        const cantidad = parseFloat(input.value);
        
        if (cantidad > 0) {
            check.checked = true;
        } else {
            check.checked = false;
        }
    }


    function cerrarModal() {
      document.getElementById("modalVer").style.display = "none";
    }

    // Validar el formulario antes de enviar
    document.getElementById("formReceta").addEventListener("submit", function(e) {
      if (ingredientes.length === 0) {
        e.preventDefault();
        alert("Debe agregar al menos un ingrediente a la receta");
      }
    });