document.addEventListener('DOMContentLoaded', function() {
  const categoriaSelect = document.getElementById('categoria');
  const tablaContainer = document.getElementById('tabla-producto-container');
  const inputNombre = document.getElementById('nombre');
  const btnRegistrar = document.getElementById('btn-registrar');
  const btnActualizar = document.getElementById('btn-actualizar');
  const btnEliminar = document.getElementById('btn-eliminar');
  const btnBuscar = document.getElementById('btn-buscar');
  const btnLimpiar = document.getElementById('btn-limpiar');

  // Nombres de las tablas para mostrar el título
  const nombresTablas = {
    'EPPs': 'Tabla de EPPs',
    'Herramientas de Trabajo': 'Tabla de Herramientas de Trabajo',
    'Materiales': 'Tabla de Materiales'
  };

  // Estructura de las tablas según la categoría
  const tablas = {
    'EPPs': `
      <table class="tabla-producto">
        <thead>
          <tr>
            <th>Código</th>
            <th>Nombre</th>
            <th>Talla</th>
            <th>Descripción</th>
            <th>Costo</th>
            <th>Fecha</th>
          </tr>
        </thead>
        <tbody>
        </tbody>
      </table>
    `,
    'Herramientas de Trabajo': `
      <table class="tabla-producto">
        <thead>
          <tr>
            <th>Código</th>
            <th>Nombre</th>
            <th>Marca</th>
            <th>Modelo</th>
            <th>Descripción</th>
            <th>Costo</th>
            <th>Fecha</th>
          </tr>
        </thead>
        <tbody>
        </tbody>
      </table>
    `,
    'Materiales': `
      <table class="tabla-producto">
        <thead>
          <tr>
            <th>Código</th>
            <th>Nombre</th>
            <th>Medida</th>
            <th>Descripción</th>
            <th>Costo</th>
            <th>Fecha</th>
          </tr>
        </thead>
        <tbody>
        </tbody>
      </table>
    `
  };

  // Evento para cambiar la tabla y los campos según la categoría
  categoriaSelect.addEventListener('change', function() {
    const categoria = categoriaSelect.value;
    if (tablas[categoria]) {
      tablaContainer.innerHTML = `
        <div class="titulo-tabla">${nombresTablas[categoria]}</div>
        ${tablas[categoria]}
      `;
      cargarTablaPorCategoria(categoria);
    } else {
      tablaContainer.innerHTML = '';
    }
    mostrarCamposPorCategoria(categoria);
  });





  

  // Mostrar/ocultar campos según la categoría seleccionada
  function mostrarCamposPorCategoria(cat) {
    const campos = [
      'campo-talla', 'campo-marca', 'campo-modelo', 'campo-medida'
    ];
    campos.forEach(id => {
      const campo = document.getElementById(id);
      if (campo) campo.style.display = 'none';
    });
    if (cat === 'EPPs') {
      document.getElementById('campo-talla').style.display = '';
    } else if (cat === 'Herramientas de Trabajo') {
      document.getElementById('campo-marca').style.display = '';
      document.getElementById('campo-modelo').style.display = '';
    } else if (cat === 'Materiales') {
      document.getElementById('campo-medida').style.display = '';
    }
  }

  // Cargar la tabla al iniciar si ya hay una categoría seleccionada
  const categoriaInicial = categoriaSelect.value;
  if (tablas[categoriaInicial]) {
    tablaContainer.innerHTML = `
      <div class="titulo-tabla">${nombresTablas[categoriaInicial]}</div>
      ${tablas[categoriaInicial]}
    `;
    cargarTablaPorCategoria(categoriaInicial);
    mostrarCamposPorCategoria(categoriaInicial);
  }







    // --- BUSCAR POR CÓDIGO O NOMBRE ---
  if (btnBuscar) {
    btnBuscar.addEventListener('click', async function () {
      const categoria = categoriaSelect.value;
      const codigo = document.getElementById('codigo').value.trim();
      const nombre = inputNombre.value.trim();

      if (!categoria) {
        alert('Seleccione una categoría.');
        return;
      }

      if ((codigo && nombre) || (!codigo && !nombre)) {
        alert('Ingrese solo un campo: Código **o** Nombre para buscar.');
        return;
      }

      const busqueda = codigo || nombre;

      try {
        const resp = await fetch(`/api/productos/buscar?categoria=${encodeURIComponent(categoria)}&busqueda=${encodeURIComponent(busqueda)}`);
        const data = await resp.json();

        if (!data.exito) {
          alert(data.mensaje || 'Error al buscar productos');
          return;
        }

        const tabla = document.querySelector('.tabla-producto tbody');
        if (!tabla) return;
        tabla.innerHTML = '';

        if (data.productos.length === 0) {
          let tr = document.createElement('tr');
          tr.innerHTML = `<td colspan="7" style="text-align:center;">Sin resultados</td>`;
          tabla.appendChild(tr);
          return;
        }

        // Mostrar resultados en tabla
        data.productos.forEach(prod => {
          let tr = document.createElement('tr');
          if (categoria === 'EPPs') {
            tr.innerHTML = `
              <td>${prod.codigo}</td>
              <td>${prod.nombre}</td>
              <td>${prod.talla || ''}</td>
              <td>${prod.descripcion || ''}</td>
              <td>${prod.costo}</td>
              <td>${prod.fecha_registro ? new Date(prod.fecha_registro).toLocaleString('es-PE', { timeZone: 'America/Lima' }) : ''}</td>
            `;
          } else if (categoria === 'Herramientas de Trabajo') {
            tr.innerHTML = `
              <td>${prod.codigo}</td>
              <td>${prod.nombre}</td>
              <td>${prod.marca || ''}</td>
              <td>${prod.modelo || ''}</td>
              <td>${prod.descripcion || ''}</td>
              <td>${prod.costo}</td>
              <td>${prod.fecha_registro ? new Date(prod.fecha_registro).toLocaleString('es-PE', { timeZone: 'America/Lima' }) : ''}</td>
            `;
          } else if (categoria === 'Materiales') {
            tr.innerHTML = `
              <td>${prod.codigo}</td>
              <td>${prod.nombre}</td>
              <td>${prod.medida || ''}</td>
              <td>${prod.descripcion || ''}</td>
              <td>${prod.costo}</td>
              <td>${prod.fecha_registro ? new Date(prod.fecha_registro).toLocaleString('es-PE', { timeZone: 'America/Lima' }) : ''}</td>
            `;
          }
          tabla.appendChild(tr);
        });

        // Si la búsqueda fue por código y hay un único resultado, rellenar formulario
        if (codigo && data.productos.length === 1) {
          const prod = data.productos[0];
          document.getElementById('codigo').value = prod.codigo;
          document.getElementById('nombre').value = prod.nombre;
          document.getElementById('descripcion').value = prod.descripcion || '';
          document.getElementById('costo').value = prod.costo;

          if (categoria === 'EPPs') {
            document.getElementById('talla').value = prod.talla || '';
          } else if (categoria === 'Herramientas de Trabajo') {
            document.getElementById('marca').value = prod.marca || '';
            document.getElementById('modelo').value = prod.modelo || '';
          } else if (categoria === 'Materiales') {
            document.getElementById('medida').value = prod.medida || '';
          }

          mostrarCamposPorCategoria(categoria);
        }

      } catch (err) {
        console.error(err);
        alert('Error de conexión al buscar productos');
      }
    });
  }





   // --- REGISTRAR PRODUCTO CON VALIDACIONES ---
  if (btnRegistrar) {
    btnRegistrar.addEventListener('click', async function () {
      const categoria = categoriaSelect.value;
      const codigo = document.getElementById('codigo').value.trim();
      const nombre = inputNombre.value.trim();
      const descripcion = document.getElementById('descripcion').value.trim();
      const costo = document.getElementById('costo').value.trim();

      // Expresiones regulares
      const soloLetrasEspacios = /^[A-Za-zÁÉÍÓÚáéíóúÑñ ]+$/;
      const letrasNumerosEspacios = /^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9 ]+$/;

      if (codigo !== '') {
        alert('El campo CÓDIGO no debe contener texto al registrar. Se genera automáticamente.');
        return;
      }

      if (!categoria || !nombre) {
        alert('Seleccione una categoría y escriba un nombre para registrar.');
        return;
      }

      if (!soloLetrasEspacios.test(nombre)) {
        alert('El campo NOMBRE solo debe contener letras y espacios.');
        return;
      }

      if (!letrasNumerosEspacios.test(descripcion)) {
        alert('El campo DESCRIPCIÓN solo debe contener letras, números y espacios.');
        return;
      }

      let data = { nombre, categoria, descripcion, costo };

      // Validaciones adicionales por categoría
      if (categoria === 'EPPs') {
        const talla = document.getElementById('talla').value.trim();
        if (!letrasNumerosEspacios.test(talla)) {
          alert('El campo TALLA solo debe contener letras, números y espacios.');
          return;
        }
        data.talla = talla;
      } else if (categoria === 'Herramientas de Trabajo') {
        const marca = document.getElementById('marca').value.trim();
        const modelo = document.getElementById('modelo').value.trim();

        if (!letrasNumerosEspacios.test(marca)) {
          alert('El campo MARCA solo debe contener letras, números y espacios.');
          return;
        }
        if (!letrasNumerosEspacios.test(modelo)) {
          alert('El campo MODELO solo debe contener letras, números y espacios.');
          return;
        }

        data.marca = marca;
        data.modelo = modelo;
      } else if (categoria === 'Materiales') {
        const medida = document.getElementById('medida').value.trim();
        if (!letrasNumerosEspacios.test(medida)) {
          alert('El campo MEDIDA solo debe contener letras, números y espacios.');
          return;
        }
        data.medida = medida;
      }

      try {
        const resp = await fetch('/api/productos/registrar', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(data)
        });
        const result = await resp.json();
        if (!result.exito) {
          alert(result.mensaje || 'Error al registrar producto');
          return;
        }
        alert('Producto registrado correctamente. Código: ' + (result.codigo || ''));
        cargarTablaPorCategoria(categoria);
        limpiarFormulario();
      } catch (err) {
        alert('Error de conexión al registrar producto');
      }
    });
  }







    // --- ACTUALIZAR PRODUCTO CON VALIDACIONES ---
  if (btnActualizar) {
    btnActualizar.addEventListener('click', async function () {
      const categoria = categoriaSelect.value;
      const codigo = document.getElementById('codigo').value.trim();
      const nombre = inputNombre.value.trim();
      const descripcion = document.getElementById('descripcion').value.trim();
      const costo = document.getElementById('costo').value.trim();

      const soloLetrasEspacios = /^[A-Za-zÁÉÍÓÚáéíóúÑñ ]+$/;
      const letrasNumerosEspacios = /^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9 ]+$/;

      if (!categoria || !codigo) {
        alert('Seleccione una categoría y escriba el código del producto a actualizar.');
        return;
      }

      if (!soloLetrasEspacios.test(nombre)) {
        alert('El campo NOMBRE solo debe contener letras y espacios.');
        return;
      }

      if (!letrasNumerosEspacios.test(descripcion)) {
        alert('El campo DESCRIPCIÓN solo debe contener letras, números y espacios.');
        return;
      }

      let data = { nombre, categoria, descripcion, costo };

      if (categoria === 'EPPs') {
        const talla = document.getElementById('talla').value.trim();
        if (!letrasNumerosEspacios.test(talla)) {
          alert('El campo TALLA solo debe contener letras, números y espacios.');
          return;
        }
        data.talla = talla;
      } else if (categoria === 'Herramientas de Trabajo') {
        const marca = document.getElementById('marca').value.trim();
        const modelo = document.getElementById('modelo').value.trim();

        if (!letrasNumerosEspacios.test(marca)) {
          alert('El campo MARCA solo debe contener letras, números y espacios.');
          return;
        }

        if (!letrasNumerosEspacios.test(modelo)) {
          alert('El campo MODELO solo debe contener letras, números y espacios.');
          return;
        }

        data.marca = marca;
        data.modelo = modelo;
      } else if (categoria === 'Materiales') {
        const medida = document.getElementById('medida').value.trim();
        if (!letrasNumerosEspacios.test(medida)) {
          alert('El campo MEDIDA solo debe contener letras, números y espacios.');
          return;
        }
        data.medida = medida;
      }

      try {
        const resp = await fetch(`/api/productos/actualizar/${encodeURIComponent(codigo)}`, {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(data)
        });
        const result = await resp.json();
        if (!result.exito) {
          alert(result.mensaje || 'Error al actualizar producto');
          return;
        }
        alert('Producto actualizado correctamente.');
        cargarTablaPorCategoria(categoria);
        limpiarFormulario();
      } catch (err) {
        alert('Error de conexión al actualizar producto');
      }
    });
  }








  // --- ELIMINAR PRODUCTO ---
  if (btnEliminar) {
    btnEliminar.addEventListener('click', async function() {
      const categoria = categoriaSelect.value;
      const codigo = document.getElementById('codigo').value.trim();
      if (!categoria || !codigo) {
        alert('Seleccione una categoría y escriba el código del producto a eliminar.');
        return;
      }
      if (!confirm('¿Está seguro de eliminar este producto?')) return;
      try {
        const resp = await fetch(`/api/productos/eliminar/${encodeURIComponent(codigo)}`, {
          method: 'DELETE'
        });
        const result = await resp.json();
        if (!result.exito) {
          alert(result.mensaje || 'Error al eliminar producto');
          return;
        }
        alert('Producto eliminado correctamente.');
        cargarTablaPorCategoria(categoria);
        limpiarFormulario();
      } catch (err) {
        alert('Error de conexión al eliminar producto');
      }
    });
  }






  // --- LIMPIAR FORMULARIO ---
if (btnLimpiar) {
  btnLimpiar.addEventListener('click', function() {
    limpiarFormulario(true); // reestablece todo
  });
}
function limpiarFormulario(reestablecerTodo = false) {
  // Guarda la categoría seleccionada antes de resetear
  const categoriaActual = categoriaSelect.value;
  document.getElementById('producto-form').reset();
  // Vuelve a asignar la categoría seleccionada
  categoriaSelect.value = categoriaActual;
  mostrarCamposPorCategoria(categoriaSelect.value);
  if (reestablecerTodo) {
    // Solo cuando se da clic en Limpiar, reestablece la tabla y la categoría
    categoriaSelect.value = ""; // Ahora sí, solo aquí se limpia la categoría
    mostrarCamposPorCategoria(""); // Oculta todos los campos especiales
    tablaContainer.innerHTML = "";
  }
}
});






// Función para cargar los datos de la tabla según la categoría
async function cargarTablaPorCategoria(categoria) {
  if (!categoria) return;
  try {
    const resp = await fetch(`/api/productos/listar?categoria=${encodeURIComponent(categoria)}`);
    const data = await resp.json();
    if (!data.exito) {
      alert(data.mensaje || 'Error al cargar productos');
      return;
    }
    const tbody = document.querySelector('.tabla-producto tbody');
    if (!tbody) {
      alert('No se encontró el tbody de la tabla');
      return;
    }
    tbody.innerHTML = '';
    if (data.productos.length === 0) {
      let tr = document.createElement('tr');
      tr.innerHTML = `<td colspan="7" style="text-align:center;">Sin productos para esta categoría</td>`;
      tbody.appendChild(tr);
      return;
    }
    data.productos.forEach(prod => {
      let tr = document.createElement('tr');
      if (categoria === 'EPPs') {
        tr.innerHTML = `
          <td>${prod.codigo}</td>
          <td>${prod.nombre}</td>
          <td>${prod.talla || ''}</td>
          <td>${prod.descripcion || ''}</td>
          <td>${prod.costo}</td>
          <td>${prod.fecha_registro ? new Date(prod.fecha_registro).toLocaleString('es-PE', { timeZone: 'America/Lima' }) : ''}</td>
        `;
      } else if (categoria === 'Herramientas de Trabajo') {
        tr.innerHTML = `
          <td>${prod.codigo}</td>
          <td>${prod.nombre}</td>
          <td>${prod.marca || ''}</td>
          <td>${prod.modelo || ''}</td>
          <td>${prod.descripcion || ''}</td>
          <td>${prod.costo}</td>
          <td>${prod.fecha_registro ? new Date(prod.fecha_registro).toLocaleString('es-PE', { timeZone: 'America/Lima' }) : ''}</td>
        `;
      } else if (categoria === 'Materiales') {
        tr.innerHTML = `
          <td>${prod.codigo}</td>
          <td>${prod.nombre}</td>
          <td>${prod.medida || ''}</td>
          <td>${prod.descripcion || ''}</td>
          <td>${prod.costo}</td>
          <td>${prod.fecha_registro ? new Date(prod.fecha_registro).toLocaleString('es-PE', { timeZone: 'America/Lima' }) : ''}</td>
        `;
      }
      // Después de agregar cada tr a la tabla:
      tr.addEventListener('click', function() {
        document.getElementById('codigo').value = prod.codigo;
        document.getElementById('nombre').value = prod.nombre;
        document.getElementById('categoria').value = categoria;
        document.getElementById('descripcion').value = prod.descripcion || '';
        document.getElementById('costo').value = prod.costo;
        if (categoria === 'EPPs') {
          document.getElementById('talla').value = prod.talla || '';
        } else if (categoria === 'Herramientas de Trabajo') {
          document.getElementById('marca').value = prod.marca || '';
          document.getElementById('modelo').value = prod.modelo || '';
        } else if (categoria === 'Materiales') {
          document.getElementById('medida').value = prod.medida || '';
        }
        mostrarCamposPorCategoria(categoria);
      });
      tbody.appendChild(tr);
    });
  } catch (err) {
    alert('Error de conexión al cargar la tabla');
  }
}

