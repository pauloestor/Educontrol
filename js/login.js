/* ========== STATE MANAGEMENT ========== */
const appState = {
    theme: localStorage.getItem('theme') || 'light',
    isLoading: false,
    rememberMe: localStorage.getItem('rememberMe') === 'true'
};

/* ========== DOM ELEMENTS ========== */
const loginForm = document.getElementById('loginForm');
const usernameInput = document.getElementById('username');
const passwordInput = document.getElementById('password');
const togglePasswordBtn = document.getElementById('togglePassword');
const rememberMeCheckbox = document.getElementById('rememberMe');
const loginButton = document.getElementById('loginButton');
const loadingSpinner = document.getElementById('loadingSpinner');
const alertBox = document.getElementById('alertBox');
const alertMessage = document.getElementById('alertMessage');
const themeToggle = document.getElementById('themeToggle');
const forgotPasswordLink = document.getElementById('forgotPasswordLink');
const forgotPasswordModal = document.getElementById('forgotPasswordModal');
const modalClose = document.getElementById('modalClose');
const modalCancelBtn = document.getElementById('modalCancelBtn');
const modalRecoverBtn = document.getElementById('modalRecoverBtn');
const recoveryUsername = document.getElementById('recoveryUsername');
const toastContainer = document.getElementById('toastContainer');

/* ========== INITIALIZATION ========== */
document.addEventListener('DOMContentLoaded', () => {
    initializeApp();
    setupEventListeners();
    applyTheme();
    restoreRememberedUsername();
});

function initializeApp() {
    console.log('[v0] Aplicação iniciada');
}

/* ========== EVENT LISTENERS ========== */
function setupEventListeners() {
    // Form submission
    loginForm.addEventListener('submit', handleLogin);
    
    // Password toggle
    togglePasswordBtn.addEventListener('click', togglePasswordVisibility);
    
    // Theme toggle
    themeToggle.addEventListener('click', toggleTheme);
    
    // Remember me
    rememberMeCheckbox.addEventListener('change', handleRememberMe);
    
    // Forgot password
    forgotPasswordLink.addEventListener('click', (e) => {
        e.preventDefault();
        openForgotPasswordModal();
    });
    
    // Modal controls
    modalClose.addEventListener('click', closeForgotPasswordModal);
    modalCancelBtn.addEventListener('click', closeForgotPasswordModal);
    modalRecoverBtn.addEventListener('click', handlePasswordRecovery);
    
    // Close modal on outside click
    forgotPasswordModal.addEventListener('click', (e) => {
        if (e.target === forgotPasswordModal) {
            closeForgotPasswordModal();
        }
    });
    
    // Input focus effects
    [usernameInput, passwordInput].forEach(input => {
        input.addEventListener('focus', clearError);
    });
    
    // Enter key on recovery username
    recoveryUsername.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            handlePasswordRecovery();
        }
    });
}

/* ========== THEME MANAGEMENT ========== */
function applyTheme() {
    document.documentElement.setAttribute('data-theme', appState.theme);
    localStorage.setItem('theme', appState.theme);
    updateThemeIcon();
}

function toggleTheme() {
    appState.theme = appState.theme === 'light' ? 'dark' : 'light';
    applyTheme();
    showToast('Tema alterado com sucesso', 'success');
}

function updateThemeIcon() {
    const icon = themeToggle.querySelector('i');
    if (appState.theme === 'dark') {
        icon.classList.remove('fa-moon');
        icon.classList.add('fa-sun');
    } else {
        icon.classList.remove('fa-sun');
        icon.classList.add('fa-moon');
    }
}

/* ========== PASSWORD VISIBILITY TOGGLE ========== */
function togglePasswordVisibility() {
    const icon = togglePasswordBtn.querySelector('i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
        togglePasswordBtn.setAttribute('title', 'Ocultar Palavra-passe');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
        togglePasswordBtn.setAttribute('title', 'Mostrar Palavra-passe');
    }
}

/* ========== FORM VALIDATION ========== */
function validateForm() {
    // Clear all errors
    clearAllErrors();
    
    let isValid = true;
    
    // Validate username
    const username = usernameInput.value.trim();
    if (!username) {
        showError('usernameError', 'Preencha o nome de utilizador.');
        isValid = false;
    } else if (username.length < 3) {
        showError('usernameError', 'Nome de utilizador deve ter pelo menos 3 caracteres.');
        isValid = false;
    }
    
    // Validate password
    /*const password = passwordInput.value;
    if (!password) {
        showError('passwordError', 'Preencha a palavra-passe.');
        isValid = false;
    } else if (password.length < 6) {
        showError('passwordError', 'Palavra-passe deve ter pelo menos 6 caracteres.');
        isValid = false;
    }*/
    
    return isValid;
}

function showError(elementId, message) {
    const errorElement = document.getElementById(elementId);
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.style.display = 'block';
    }
}

function clearError() {
    const errorElements = document.querySelectorAll('.error-message');
    errorElements.forEach(el => {
        el.textContent = '';
        el.style.display = 'none';
    });
}

function clearAllErrors() {
    clearError();
    hideAlert();
}

/* ========== LOGIN HANDLER ========== */
async function handleLogin(e) {
    e.preventDefault();

    if (!validateForm()) return;

    setLoadingState(true);

    const username = usernameInput.value.trim();
    const password = passwordInput.value;

    try {

        const formData = new FormData();
        formData.append("username", username);
        formData.append("password", password);

        const response = await fetch("phplogin/login.php", {
            method: "POST",
            body: formData
        });

        const data = await response.json();

        console.log("[LOGIN RESPONSE]", data);

        if (data.success) {

            showToast("Login realizado com sucesso!", "success");

            // guardar remember me
            if (rememberMeCheckbox.checked) {
                localStorage.setItem("rememberedUsername", username);
                localStorage.setItem("rememberMe", "true");
            } else {
                localStorage.removeItem("rememberedUsername");
                localStorage.setItem("rememberMe", "false");
            }

            // REDIRECIONAMENTO DO BACKEND
            setTimeout(() => {
                window.location.href = data.redirect;
            }, 800);

        } else {

            showAlert(data.message || "Erro no login", "error");

        }

    } catch (error) {

        console.error(error);
        showAlert("Erro de conexão com o servidor.", "error");

    } finally {

        setLoadingState(false);

    }
}

/* ========== AUTHENTICATION MOCK ========== */

/* ========== REMEMBER ME ========== */
function handleRememberMe() {
    if (rememberMeCheckbox.checked) {
        localStorage.setItem('rememberMe', 'true');
    } else {
        localStorage.removeItem('rememberedUsername');
        localStorage.setItem('rememberMe', 'false');
    }
}

function restoreRememberedUsername() {
    const rememberedUsername = localStorage.getItem('rememberedUsername');
    const rememberMe = localStorage.getItem('rememberMe') === 'true';
    
    if (rememberedUsername && rememberMe) {
        usernameInput.value = rememberedUsername;
        rememberMeCheckbox.checked = true;
        passwordInput.focus();
    }
}

/* ========== FORGOT PASSWORD ========== */
function openForgotPasswordModal() {
    forgotPasswordModal.classList.add('active');
    recoveryUsername.focus();
}

function closeForgotPasswordModal() {
    forgotPasswordModal.classList.remove('active');
    recoveryUsername.value = '';
}

async function handlePasswordRecovery() {
    const username = recoveryUsername.value.trim();
    
    if (!username) {
        showToast('Digite o nome de utilizador.', 'warning');
        return;
    }
    
    try {
        // Simulate API call
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        // Simulate success
        showToast('Instruções de recuperação enviadas para o email.', 'success');
        closeForgotPasswordModal();
    } catch (error) {
        showToast('Erro ao enviar instruções.', 'error');
    }
}

/* ========== LOADING STATE ========== */
function setLoadingState(isLoading) {
    appState.isLoading = isLoading;
    
    if (isLoading) {
        loginButton.disabled = true;
        loadingSpinner.style.display = 'inline-block';
        document.querySelector('.button-text').textContent = 'A processar...';
        document.querySelector('.button-icon').style.display = 'none';
    } else {
        loginButton.disabled = false;
        loadingSpinner.style.display = 'none';
        document.querySelector('.button-text').textContent = 'Entrar';
        document.querySelector('.button-icon').style.display = 'inline-block';
    }
}

/* ========== ALERT BOX ========== */
function showAlert(message, type = 'error') {
    alertMessage.textContent = message;
    alertBox.className = `alert alert-${type}`;
    alertBox.style.display = 'flex';
}

function hideAlert() {
    alertBox.style.display = 'none';
}

/* ========== TOAST NOTIFICATIONS ========== */
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    
    const icons = {
        'success': 'fa-check-circle',
        'error': 'fa-exclamation-circle',
        'warning': 'fa-exclamation-triangle'
    };
    
    const iconClass = icons[type] || 'fa-info-circle';
    
    toast.innerHTML = `
        <i class="fas ${iconClass}"></i>
        <span>${message}</span>
    `;
    
    toastContainer.appendChild(toast);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

/* ========== KEYBOARD SHORTCUTS ========== */
document.addEventListener('keydown', (e) => {
    // Ctrl+K or Cmd+K to toggle theme
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        toggleTheme();
    }
    
    // Escape to close modal
    if (e.key === 'Escape' && forgotPasswordModal.classList.contains('active')) {
        closeForgotPasswordModal();
    }
});

/* ========== ACCESSIBILITY ========== */
// Ensure proper focus management
loginForm.addEventListener('keydown', (e) => {
    if (e.key === 'Tab') {
        // Allow normal tab behavior
        return;
    }
});

console.log('[v0] Script de login carregado com sucesso');
