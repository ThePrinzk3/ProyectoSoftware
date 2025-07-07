document.addEventListener('DOMContentLoaded', function() {
  const desbloqueo = localStorage.getItem('desbloqueo');
  if (desbloqueo && new Date(desbloqueo).getTime() > Date.now()) {
    mostrarBloqueo(desbloqueo);
  }
});

document.querySelector('.login-btn').addEventListener('click', async function() {
  const nombre = document.getElementById('username').value;
  const contraseña = document.getElementById('password').value;
  const loader = document.getElementById('loader-bg');
  loader.style.display = 'flex';

  try {
    const resp = await fetch('/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ nombre, contraseña })
    });
    const data = await resp.json();
    loader.style.display = 'none';

    if (data.bloqueado) {
      alert(data.message);
      mostrarBloqueo(data.desbloqueo);
      return;
    }

    if (!resp.ok) {
      alert(data.message || 'Error de autenticación');
      return;
    }

    // Login exitoso
    localStorage.removeItem('desbloqueo');
    window.location.href = '/menu.html';
  } catch (err) {
    loader.style.display = 'none';
    alert('Error de conexión');
  }
});








// APARTADO DE BLOQUEO DE USUARIO
// Este apartado maneja el bloqueo de usuario tras múltiples intentos fallidos

let timerInterval;

function mostrarBloqueo(desbloqueo) {
  const username = document.getElementById('username');
  const password = document.getElementById('password');
  const btn = document.querySelector('.login-btn');
  username.disabled = true;
  password.disabled = true;
  btn.disabled = true;

  // Guardar el tiempo de desbloqueo en localStorage
  localStorage.setItem('desbloqueo', desbloqueo);

  // Crear o mostrar mensaje de bloqueo
  let msg = document.getElementById('bloqueo-msg');
  if (!msg) {
    msg = document.createElement('div');
    msg.id = 'bloqueo-msg';
    msg.style.color = 'red';
    msg.style.marginTop = '10px';
    btn.parentNode.appendChild(msg);
  }

  function actualizarContador() {
    const ahora = Date.now();
    const msRestantes = new Date(desbloqueo).getTime() - ahora;
    if (msRestantes > 0) {
      const min = Math.floor(msRestantes / 60000);
      const seg = Math.floor((msRestantes % 60000) / 1000);
      msg.textContent = `Usuario bloqueado. Espere ${min}:${seg.toString().padStart(2, '0')} minutos para volver a intentar.`;
    } else {
      clearInterval(timerInterval);
      msg.textContent = '';
      username.disabled = false;
      password.disabled = false;
      btn.disabled = false;
      localStorage.removeItem('desbloqueo');
    }
  }

  actualizarContador();
  timerInterval = setInterval(actualizarContador, 1000);
}