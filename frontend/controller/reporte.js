document.addEventListener('DOMContentLoaded', function() {
  // Cargar datos iniciales de inventario
  cargarTablaInventario();

  // Evento para el filtro de categoría
  const filtroCategoria = document.getElementById('filtro-categoria');
  if (filtroCategoria) {
    filtroCategoria.addEventListener('change', function() {
      const categoria = this.value;
      cargarTablaInventario(categoria);
    });
  }





  // Evento para exportar a Excel
  const btnExcel = document.getElementById('btn-excel');
  if (btnExcel) {
    btnExcel.addEventListener('click', function(e) {
      e.preventDefault();
      exportarArchivo('excel');
    });
  }





  // Evento para exportar a PDF
  const btnPdf = document.getElementById('btn-pdf');
  if (btnPdf) {
    btnPdf.addEventListener('click', function(e) {
      e.preventDefault();
      exportarArchivo('pdf');
    });
  }
});





// Función para cargar la tabla de inventario, con filtro opcional por categoría
async function cargarTablaInventario(categoria = 'todas') {
  try {
    let url = '/api/reporte/inventario';
    if (categoria && categoria !== 'todas') {
      url += `?categoria=${encodeURIComponent(categoria)}`;
    }

    const resp = await fetch(url);
    const data = await resp.json();

    if (!data.exito) {
      alert(data.mensaje || 'Error al cargar inventario');
      return;
    }

    const tbody = document.querySelector('.tabla-inventario tbody');
    if (!tbody) {
      alert('No se encontró el tbody de la tabla de inventario');
      return;
    }

    tbody.innerHTML = '';

    if (!data.productos || data.productos.length === 0) {
      let tr = document.createElement('tr');
      tr.innerHTML = `<td colspan="5" style="text-align:center;">${
        categoria === 'todas'
          ? 'Sin productos en inventario'
          : 'No hay productos en esta categoría'
      }</td>`;
      tbody.appendChild(tr);
      return;
    }






    // Ordenar productos por código de menor a mayor
    data.productos.sort((a, b) => {
      if (!isNaN(a.codigo) && !isNaN(b.codigo)) {
        return Number(a.codigo) - Number(b.codigo);
      }
      return a.codigo.localeCompare(b.codigo, 'es', { numeric: true });
    });

    data.productos.forEach(prod => {
      let tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${prod.codigo}</td>
        <td>${prod.nombre}</td>
        <td>${prod.categoria}</td>
        <td>${prod.costo}</td>
        <td>${prod.stock}</td>
      `;
      tbody.appendChild(tr);
    });

  } catch (err) {
    console.error('Error al cargar inventario:', err);
    alert('Error de conexión al cargar inventario');
  }
}




// Función para exportar a Excel o PDF
function exportarArchivo(tipo) {
  // Llama al endpoint PHP para exportar el reporte
  const url = `/api/reporte/inventario/${tipo}`;
  window.open(url, '_blank');
}