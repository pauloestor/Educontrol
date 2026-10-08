//=========================================================
// ALTERAR SENHA - PRIMEIRO ACESSO
//=========================================================

const form = document.getElementById("changePasswordForm");

const novaSenha = document.getElementById("newPassword");
const confirmarSenha = document.getElementById("confirmPassword");

const strengthFill = document.getElementById("strengthFill");
const strengthText = document.getElementById("strengthText");

const saveButton = document.getElementById("savePasswordButton");
const loadingSpinner = document.getElementById("loadingSpinner");

const alertBox = document.getElementById("alertBox");
const alertMessage = document.getElementById("alertMessage");

// BLOCO DE REGRAS (IMPORTANTE)
const validationBox = document.getElementById("passwordValidation");

//=========================================================
// MOSTRAR / ESCONDER SENHA
//=========================================================

document.querySelectorAll(".toggle-password").forEach(btn => {

    btn.addEventListener("click", () => {

        const input = document.getElementById(btn.dataset.target);
        const icon = btn.querySelector("i");

        if (input.type === "password") {

            input.type = "text";
            icon.classList.replace("fa-eye", "fa-eye-slash");

        } else {

            input.type = "password";
            icon.classList.replace("fa-eye-slash", "fa-eye");

        }

    });

});

//=========================================================
// MOSTRAR REGRAS SÓ AO DIGITAR
//=========================================================

novaSenha.addEventListener("input", () => {

    if (novaSenha.value.length > 0) {
        validationBox.style.display = "block";
    } else {
        validationBox.style.display = "none";
    }

    validarSenha();
});

//=========================================================
// VALIDAR FORÇA DA SENHA
//=========================================================

function validarSenha() {

    const senha = novaSenha.value;

    let pontos = 0;

    const regras = {
        tamanho: senha.length >= 8,
        maiuscula: /[A-Z]/.test(senha),
        minuscula: /[a-z]/.test(senha),
        numero: /\d/.test(senha),
        especial: /[^A-Za-z0-9]/.test(senha)
    };

    atualizarRegra("ruleLength", regras.tamanho);
    atualizarRegra("ruleUpper", regras.maiuscula);
    atualizarRegra("ruleLower", regras.minuscula);
    atualizarRegra("ruleNumber", regras.numero);
    atualizarRegra("ruleSpecial", regras.especial);

    Object.values(regras).forEach(r => {
        if (r) pontos++;
    });

    const largura = (pontos / 5) * 100;
    strengthFill.style.width = largura + "%";

    if (pontos <= 2) {
        strengthFill.style.background = "#e74c3c";
        strengthText.textContent = "Fraca";
    } 
    else if (pontos <= 4) {
        strengthFill.style.background = "#f39c12";
        strengthText.textContent = "Média";
    } 
    else {
        strengthFill.style.background = "#2ecc71";
        strengthText.textContent = "Forte";
    }
}

//=========================================================

function atualizarRegra(id, valida) {

    const item = document.getElementById(id);

    if (!item) return;

    const icon = item.querySelector("i");

    if (valida) {
        icon.className = "fas fa-check";
        item.style.color = "#27ae60";
    } 
    else {
        icon.className = "fas fa-times";
        item.style.color = "#888";
    }
}

//=========================================================
// SUBMIT
//=========================================================

form.addEventListener("submit", async function (e) {

    e.preventDefault();

    alertBox.style.display = "none";

    if (!novaSenha.value || !confirmarSenha.value) {
        mostrarErro("Preencha todos os campos.");
        return;
    }

    if (novaSenha.value !== confirmarSenha.value) {
        mostrarErro("As palavras-passe não coincidem.");
        return;
    }

    if (strengthText.textContent !== "Forte") {
        mostrarErro("Escolha uma palavra-passe mais segura.");
        return;
    }

    saveButton.disabled = true;
    loadingSpinner.style.display = "inline-block";

    try {

        const formData = new FormData();
        formData.append("nova_senha", novaSenha.value);

        const response = await fetch("phplogin/alterar_senha.php", {
            method: "POST",
            body: formData
        });

        const data = await response.json();

        if (data.success) {

            alert("Palavra-passe alterada com sucesso.");
            window.location.href = data.redirect;

        } else {
            mostrarErro(data.message || "Erro ao alterar senha.");
        }

    } catch (error) {

        console.error(error);
        mostrarErro("Erro de conexão com o servidor.");

    } finally {

        saveButton.disabled = false;
        loadingSpinner.style.display = "none";
    }
});

//=========================================================

function mostrarErro(msg) {

    alertMessage.textContent = msg;
    alertBox.style.display = "flex";
}