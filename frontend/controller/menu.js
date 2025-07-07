document.addEventListener('DOMContentLoaded', () => {
  // Cargar bienvenida por defecto en el iframe
  document.getElementById('contenido-frame').src = '/frontend/bienvenida.html';

  // Sidebar: cargar páginas en el iframe
  document.getElementById('producto-link').addEventListener('click', (e) => {
    e.preventDefault();
    document.getElementById('contenido-frame').src = '/frontend/producto.html';
  });

  document.getElementById('stock-link').addEventListener('click', (e) => {
    e.preventDefault();
    document.getElementById('contenido-frame').src = '/frontend/stock.html';
  });

  document.getElementById('reporte-link').addEventListener('click', (e) => {
    e.preventDefault();
    document.getElementById('contenido-frame').src = '/frontend/reporte.html';
  });





  // Simula el nombre del usuario (puedes reemplazarlo por el real si lo tienes)
  document.getElementById('sidebar-username').textContent = 'Usuario';

  // Temporizador de sesión
  let seconds = 0;
  function updateTime() {
    seconds++;
    const h = String(Math.floor(seconds / 3600)).padStart(2, '0');
    const m = String(Math.floor((seconds % 3600) / 60)).padStart(2, '0');
    const s = String(seconds % 60).padStart(2, '0');
    document.getElementById('sidebar-time').textContent = `${h}:${m}:${s}`;
  }
  setInterval(updateTime, 1000);

  // Modal de logout
  const logoutBtn = document.querySelector('.btn-logout');
  if (logoutBtn) {
    logoutBtn.addEventListener('click', function(e) {
      e.preventDefault();
      document.getElementById('logout-modal').style.display = 'flex';
    });
  }




  
  const cancelLogout = document.getElementById('cancel-logout');
  if (cancelLogout) {
    cancelLogout.addEventListener('click', function() {
      document.getElementById('logout-modal').style.display = 'none';
    });
  }

  const confirmLogout = document.getElementById('confirm-logout');
  if (confirmLogout) {
    confirmLogout.addEventListener('click', function() {
      document.getElementById('logout-modal').style.display = 'none';
      document.getElementById('loader-bg').style.display = 'flex';
      setTimeout(function() {
        window.location.href = '/frontend/login_index.html';
      }, 250);
    });
  }

  
});