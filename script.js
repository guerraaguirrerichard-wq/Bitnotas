// ═══════════════════════════════════════════════════════════
// PARTE 1 — Referencias del DOM
// ═══════════════════════════════════════════════════════════
const form = document.getElementById('loginForm');
const emailInput = document.getElementById('email');
const passInput = document.getElementById('password');
const loginAlert = document.getElementById('loginAlert');
const emailError = document.getElementById('emailError');
const passError = document.getElementById('passError');
const toggleBtn = document.getElementById('togglePass');

const registerForm = document.getElementById('registerForm');
const regNombre = document.getElementById('regNombre');
const regEmail = document.getElementById('regEmail');
const regPassword = document.getElementById('regPassword');
const regPasswordConf = document.getElementById('regPasswordConf');
const registerAlert = document.getElementById('registerAlert');
const nombreError = document.getElementById('nombreError');
const regEmailError = document.getElementById('regEmailError');
const regPassError = document.getElementById('regPassError');
const regPassConfError = document.getElementById('regPassConfError');
const toggleRegBtn = document.getElementById('toggleRegPass');
const toggleRegConfBtn = document.getElementById('toggleRegPassConf');

// ═══════════════════════════════════════════════════════════
// PARTE 2 — Usuarios predeterminados (siempre disponibles)
// ═══════════════════════════════════════════════════════════
const USUARIOS_DEFAULT = [
    { nombre: 'Admin', email: 'elprofe@gmail.com', password: 'Admin123' },
    { nombre: 'Estudiante', email: 'estudiante@correo.com', password: 'Pass456' }
];

// ═══════════════════════════════════════════════════════════
// PARTE 3 — Funciones de localStorage
// ═══════════════════════════════════════════════════════════

/**
 * Obtiene todos los usuarios: los fijos + los registrados en localStorage.
 */
function obtenerUsuarios() {
    const guardados = JSON.parse(localStorage.getItem('usuarios') || '[]');
    return [...USUARIOS_DEFAULT, ...guardados];
}

/**
 * Guarda un nuevo usuario en localStorage.
 */
function guardarUsuario(nuevoUsuario) {
    const guardados = JSON.parse(localStorage.getItem('usuarios') || '[]');
    guardados.push(nuevoUsuario);
    localStorage.setItem('usuarios', JSON.stringify(guardados));
}

/**
 * Verifica si un correo ya existe (en cualquiera de las listas).
 */
function emailExiste(email) {
    return obtenerUsuarios().some(u => u.email === email.toLowerCase());
}

// ═══════════════════════════════════════════════════════════
// PARTE 4 — Validaciones reutilizables
// ═══════════════════════════════════════════════════════════
function validarEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

function validarPassword(pass) {
    const errores = [];
    if (pass.length < 8) errores.push('Mínimo 8 caracteres');
    if (!/[A-Z]/.test(pass)) errores.push('Al menos una mayúscula');
    if (!/[0-9]/.test(pass)) errores.push('Al menos un número');
    return errores;
}

// ═══════════════════════════════════════════════════════════
// PARTE 5 — Mostrar / Ocultar contraseñas
// ═══════════════════════════════════════════════════════════
function crearToggle(btn, input) {
    btn.addEventListener('click', () => {
        if (input.type === 'password') {
            input.type = 'text';
            btn.textContent = '🙈';
        } else {
            input.type = 'password';
            btn.textContent = '👁️';
        }
    });
}

crearToggle(toggleBtn, passInput);
crearToggle(toggleRegBtn, regPassword);
crearToggle(toggleRegConfBtn, regPasswordConf);

// ═══════════════════════════════════════════════════════════
// PARTE 6 — Medidor de fortaleza de contraseña (registro)
// ═══════════════════════════════════════════════════════════
const barraFuerza = document.getElementById('barraFuerza');
const textoFuerza = document.getElementById('textoFuerza');

regPassword.addEventListener('input', () => {
    const errores = validarPassword(regPassword.value);
    const puntaje = 3 - errores.length;

    const config = [
        { pct: '0%', color: '#e0e0e0', label: '' },
        { pct: '33%', color: '#e53935', label: '😬 Débil' },
        { pct: '66%', color: '#ffb300', label: '😐 Regular' },
        { pct: '100%', color: '#43a047', label: '💪 Fuerte' },
    ][puntaje];

    barraFuerza.style.width = config.pct;
    barraFuerza.style.background = config.color;
    textoFuerza.textContent = config.label;
});

// ═══════════════════════════════════════════════════════════
// PARTE 7 — Alternar entre Login y Registro
// ═══════════════════════════════════════════════════════════
function mostrarTab(tab) {
    const panelLogin = document.getElementById('panelLogin');
    const panelRegistro = document.getElementById('panelRegistro');
    const tabLogin = document.getElementById('tabLogin');
    const tabRegister = document.getElementById('tabRegister');

    if (tab === 'login') {
        panelLogin.style.display = 'block';
        panelRegistro.style.display = 'none';
        tabLogin.classList.add('active');
        tabRegister.classList.remove('active');
        limpiarAlertas();
    } else {
        panelLogin.style.display = 'none';
        panelRegistro.style.display = 'block';
        tabLogin.classList.remove('active');
        tabRegister.classList.add('active');
        limpiarAlertas();
    }
}

function limpiarAlertas() {
    [emailError, passError, loginAlert,
        nombreError, regEmailError, regPassError, regPassConfError, registerAlert
    ].forEach(el => {
        el.textContent = '';
        el.className = el.className.replace(' error', '').replace(' success', '');
    });

    loginAlert.className = 'alert';
    registerAlert.className = 'alert';
}

// ═══════════════════════════════════════════════════════════
// PARTE 8 — Formulario de LOGIN
// ═══════════════════════════════════════════════════════════
form.addEventListener('submit', function(e) {
    e.preventDefault();

    emailError.textContent = '';
    passError.textContent = '';
    loginAlert.className = 'alert';
    loginAlert.textContent = '';

    const email = emailInput.value.trim().toLowerCase();
    const password = passInput.value;
    let hayError = false;

    if (!email) {
        emailError.textContent = '⚠️ El correo es obligatorio';
        hayError = true;
    } else if (!validarEmail(email)) {
        emailError.textContent = '⚠️ El formato del correo no es válido';
        hayError = true;
    }

    if (!password) {
        passError.textContent = '⚠️ La contraseña es obligatoria';
        hayError = true;
    }

    if (hayError) return;

    // Buscar en usuarios (default + localStorage)
    const usuario = obtenerUsuarios().find(
        u => u.email === email && u.password === password
    );

    if (usuario) {
        loginAlert.className = 'alert success';
        loginAlert.textContent = `✅ ¡Bienvenido, ${usuario.nombre}! Redirigiendo...`;

        setTimeout(() => {
            window.location.href = 'index.php';
        }, 1500);
    } else {
        loginAlert.className = 'alert error';
        loginAlert.textContent = '❌ Correo o contraseña incorrectos';
    }
});

// ═══════════════════════════════════════════════════════════
// PARTE 9 — Formulario de REGISTRO
// ═══════════════════════════════════════════════════════════
registerForm.addEventListener('submit', function(e) {
    e.preventDefault();

    // Limpiar errores anteriores
    [nombreError, regEmailError, regPassError, regPassConfError].forEach(el => el.textContent = '');
    registerAlert.className = 'alert';
    registerAlert.textContent = '';

    const nombre = regNombre.value.trim();
    const email = regEmail.value.trim().toLowerCase();
    const password = regPassword.value;
    const passwordC = regPasswordConf.value;

    let hayError = false;

    // Validar nombre
    if (!nombre) {
        nombreError.textContent = '⚠️ El nombre es obligatorio';
        hayError = true;
    } else if (nombre.length < 2) {
        nombreError.textContent = '⚠️ El nombre es demasiado corto';
        hayError = true;
    }

    // Validar email
    if (!email) {
        regEmailError.textContent = '⚠️ El correo es obligatorio';
        hayError = true;
    } else if (!validarEmail(email)) {
        regEmailError.textContent = '⚠️ El formato del correo no es válido';
        hayError = true;
    } else if (emailExiste(email)) {
        regEmailError.textContent = '⚠️ Este correo ya está registrado';
        hayError = true;
    }

    // Validar contraseña
    const erroresPass = validarPassword(password);
    if (!password) {
        regPassError.textContent = '⚠️ La contraseña es obligatoria';
        hayError = true;
    } else if (erroresPass.length > 0) {
        regPassError.textContent = '⚠️ ' + erroresPass.join(' · ');
        hayError = true;
    }

    // Validar confirmación
    if (!passwordC) {
        regPassConfError.textContent = '⚠️ Confirma tu contraseña';
        hayError = true;
    } else if (password !== passwordC) {
        regPassConfError.textContent = '⚠️ Las contraseñas no coinciden';
        hayError = true;
    }

    if (hayError) return;

    // ✅ Todo válido → guardar en localStorage
    guardarUsuario({ nombre, email, password });

    // Mostrar éxito
    registerAlert.className = 'alert success';
    registerAlert.textContent = `✅ ¡Cuenta creada! Bienvenido, ${nombre}. Ahora puedes iniciar sesión.`;

    // Limpiar formulario
    registerForm.reset();
    barraFuerza.style.width = '0%';
    textoFuerza.textContent = '';

    // Actualizar el panel de usuarios
    renderizarUsuarios();

    // Redirigir al login después de 2 segundos
    setTimeout(() => mostrarTab('login'), 2000);
});

// ═══════════════════════════════════════════════════════════
// PARTE 10 — Panel de usuarios (solo para práctica)
// ═══════════════════════════════════════════════════════════
function renderizarUsuarios() {
    const lista = document.getElementById('listaUsuarios');
    const guardados = JSON.parse(localStorage.getItem('usuarios') || '[]');

    lista.innerHTML = '';

    if (guardados.length === 0) {
        lista.innerHTML = '<li class="no-users">Sin usuarios registrados aún</li>';
        return;
    }

    guardados.forEach((u, i) => {
        const li = document.createElement('li');
        li.innerHTML = `
      <span class="user-info">
        <strong>${u.nombre}</strong>
        <small>${u.email}</small>
      </span>
      <button class="btn-del" data-index="${i}" title="Eliminar">✕</button>
    `;
        lista.appendChild(li);
    });

    // Botones de eliminación individual
    lista.querySelectorAll('.btn-del').forEach(btn => {
        btn.addEventListener('click', () => {
            const idx = parseInt(btn.dataset.index);
            const guardados = JSON.parse(localStorage.getItem('usuarios') || '[]');
            guardados.splice(idx, 1);
            localStorage.setItem('usuarios', JSON.stringify(guardados));
            renderizarUsuarios();
        });
    });
}

// Botón limpiar todos
document.getElementById('btnLimpiar').addEventListener('click', () => {
    if (confirm('¿Eliminar todos los usuarios registrados?')) {
        localStorage.removeItem('usuarios');
        renderizarUsuarios();
    }
});

// Inicializar panel al cargar la página
renderizarUsuarios();