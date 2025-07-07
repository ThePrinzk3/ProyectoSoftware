document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('stock-form');
  const btnEntrada = document.getElementById('btn-entrada');
  const btnSalida = document.getElementById('btn-salida');
  const btnBuscar = document.getElementById('btn-buscar');
  const btnLimpiar = document.getElementById('btn-limpiar');
  const inputCodigo = document.getElementById('codigo');
  const inputCantidad = document.getElementById('cantidad-stock');

  // Cargar tabla de movimientos al iniciar
  cargarTablaMovimientos();






  // Registrar entrada de stock
  if (btnEntrada) {
    btnEntrada.addEventListener('click', async function (e) {
      e.preventDefault();
      const codigo = inputCodigo.value.trim();
      const cantidad = parseInt(inputCantidad.value, 10);

      if (!codigo || isNaN(cantidad) || cantidad <= 0) {
        alert('Ingrese un código válido y una cantidad mayor a 0.');
        return;
      }

      try {
        const resp = await fetch('/api/stock/entrada', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ codigo, cantidad })
        });
        const data = await resp.json();
        if (data.exito) {
          alert('Entrada de stock registrada correctamente.');
          form.reset();
          cargarTablaMovimientos();
        } else {
          alert(data.mensaje + (data.error ? ' (' + data.error + ')' : '') || 'Error al registrar entrada de stock.');
        }
      } catch (err) {
        alert('Error de conexión con el servidor.');
      }
    });
  }







  // Registrar salida de stock
  if (btnSalida) {
    btnSalida.addEventListener('click', async function (e) {
      e.preventDefault();
      const codigo = inputCodigo.value.trim();
      const cantidad = parseInt(inputCantidad.value, 10);

      if (!codigo || isNaN(cantidad) || cantidad <= 0) {
        alert('Ingrese un código válido y una cantidad mayor a 0.');
        return;
      }

      try {
        const resp = await fetch('/api/stock/salida', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ codigo, cantidad })
        });
        const data = await resp.json();
        if (data.exito) {
          alert('Salida de stock registrada correctamente.');
          form.reset();
          cargarTablaMovimientos();
        } else {
          alert(data.mensaje || 'Error al registrar salida de stock.');
        }
      } catch (err) {
        alert('Error de conexión con el servidor.');
      }
    });
  }







  // Buscar movimientos por código y resaltar
  if (btnBuscar) {
    btnBuscar.addEventListener('click', async function (e) {
      e.preventDefault();
      const codigo = inputCodigo.value.trim();
      if (!codigo) {
        alert('Ingrese el código.');
        return;
      }
      await cargarTablaMovimientos(codigo);
    });
  }






  // Limpiar formulario y quitar resaltado
  if (btnLimpiar) {
    btnLimpiar.addEventListener('click', function (e) {
      e.preventDefault();
      form.reset();
      cargarTablaMovimientos(); // Sin código, muestra todos
    });
  }
});





async function cargarTablaMovimientos(codigo = null) {
  try {
    let data;
    if (codigo) {
      const resp = await fetch(`/api/stock/buscar?codigo=${encodeURIComponent(codigo)}`);
      data = await resp.json();
      if (!data.exito) {
        alert(data.mensaje || 'Error al buscar movimientos');
        return;
      }
      // Para compatibilidad, ajusta el formato
      data = { movimientos: data.movimientos || [] };
    } else {
      const resp = await fetch('/api/stock/movimientos');
      data = await resp.json();
      if (!data.exito) {
        alert(data.mensaje || 'Error al cargar movimientos');
        return;
      }
    }
    const tbody = document.querySelector('.tabla-stock tbody');
    if (!tbody) {
      alert('No se encontró el tbody de la tabla');
      return;
    }
    tbody.innerHTML = '';
    if (!data.movimientos || data.movimientos.length === 0) {
      let tr = document.createElement('tr');
      tr.innerHTML = `<td colspan="5" style="text-align:center;">Sin movimientos registrados</td>`;
      tbody.appendChild(tr);
      return;
    }
    data.movimientos.forEach(mov => {
      let tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${mov.codigo}</td>
        <td>${mov.stock}</td>
        <td>${mov.tipo === 'entrada' ? 'Ingreso' : 'Descuento'}</td>
        <td>${mov.cantidad}</td>
        <td>${mov.fecha ? new Date(mov.fecha).toLocaleString('es-PE', { timeZone: 'America/Lima' }) : ''}</td>
      `;
      tbody.appendChild(tr);
    });
  } catch (err) {
    alert('Error de conexión al cargar la tabla');
  }
}

