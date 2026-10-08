// =========================================================
// PERFIL ADMIN - CARREGAR + FOTO + UPDATE
// =========================================================

document.addEventListener("DOMContentLoaded", () => {

    // =========================
    // ELEMENTOS
    // =========================
    const profileAvatar = document.getElementById("profileAvatar");
    const avatarInput = document.getElementById("avatarInput");

    const emailInput = document.getElementById("profileEmail");
    const phoneInput = document.getElementById("profilePhone");
    const saveBtn = document.getElementById("saveProfileBtn");

    // proteção caso avatar não exista
    let lastImage = profileAvatar ? profileAvatar.src : "";

    // =========================
    // INICIAR
    // =========================
    carregarPerfil();

    async function carregarPerfil() {

    try {

        const response = await fetch("phpadmin/carregar_perfil.php");
        const data = await response.json();

        console.log("PERFIL:", data);

        if (!data.success) {
            showToast("Erro ao carregar perfil", "error");
            return;
        }

        const user = data.data;

        // =========================
        // TEXTO (HEADER)
        // =========================
        const nameEl = document.getElementById("profileName");
        const roleEl = document.getElementById("profileRole");
        const emailInfoEl = document.getElementById("profileInfoEmail");

        if (nameEl) nameEl.textContent = user.nome || "";
        if (roleEl) roleEl.textContent = user.tipo || "";
        if (emailInfoEl) emailInfoEl.textContent = user.email || "";

        // =========================
        // INPUTS EDITÁVEIS
        // =========================
        const nameInput = document.getElementById("profileNameInput");
        const genderInput = document.getElementById("profileGender");
        const roleInput = document.getElementById("profileRoleInput");

        if (nameInput) nameInput.value = user.nome || "";
        if (emailInput) emailInput.value = user.email || "";
        if (phoneInput) phoneInput.value = user.telefone || "";
        if (roleInput) roleInput.value = user.tipo || "";
        if (genderInput) genderInput.value = user.genero || "";

        // =========================
        // DATA DE ADMISSÃO
        // =========================
        const createdAtInput = document.getElementById("profileCreatedAt");

        if (createdAtInput && user.created_at) {

        createdAtInput.value = user.created_at.substring(0, 10);
        }

        // =========================
        // FOTO
        // =========================
        if (profileAvatar) {
            profileAvatar.src = user.foto
                ? user.foto
                : "img/default-user.png";

            lastImage = profileAvatar.src;
        }

    } catch (error) {
        console.error(error);
        showToast("Erro de conexão", "error");
    }
}

    // =====================================================
    // UPLOAD DE FOTO (AUTO PREVIEW + BD)
    // =====================================================
    if (avatarInput) {

        avatarInput.addEventListener("change", async function () {

            const file = this.files[0];
            if (!file) return;

            if (!file.type.startsWith("image/")) {
                showToast("Selecione uma imagem válida", "error");
                return;
            }

            // preview imediato
            const reader = new FileReader();
            reader.onload = e => {
                if (profileAvatar) profileAvatar.src = e.target.result;
            };
            reader.readAsDataURL(file);

            // enviar para backend
            const formData = new FormData();
            formData.append("foto", file);

            try {

                const response = await fetch("phpadmin/upload_foto.php", {
                    method: "POST",
                    body: formData
                });

                const data = await response.json();

                if (data.success) {

                    if (profileAvatar) profileAvatar.src = data.foto;
                    lastImage = data.foto;

                    showToast("Foto atualizada com sucesso!", "success");

                } else {

                    if (profileAvatar) profileAvatar.src = lastImage;

                    showToast(data.message || "Erro ao atualizar foto", "error");
                }

            } catch (error) {

                console.error(error);

                if (profileAvatar) profileAvatar.src = lastImage;

                showToast("Erro de conexão", "error");
            }
        });
    }

    // =====================================================
    // UPDATE PERFIL (EMAIL + TELEFONE)
    // =====================================================
    if (saveBtn) {

        saveBtn.addEventListener("click", async () => {

            const email = emailInput ? emailInput.value.trim() : "";
            const telefone = phoneInput ? phoneInput.value.trim() : "";

            if (!email || !telefone) {
                showToast("Preencha email e telefone", "error");
                return;
            }

            try {

                const formData = new FormData();
                formData.append("email", email);
                formData.append("telefone", telefone);

                const response = await fetch("phpadmin/atualizar_perfil.php", {
                    method: "POST",
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    showToast("Perfil atualizado com sucesso!", "success");
                } else {
                    showToast(data.message || "Erro ao atualizar perfil", "error");
                }

            } catch (error) {
                console.error(error);
                showToast("Erro de conexão", "error");
            }
        });
    }

});

// =========================================================
// TOAST SYSTEM
// =========================================================
function showToast(message, type = "success") {

    const container = document.getElementById("toastContainer");

    if (!container) {
        alert(message);
        return;
    }

    const toast = document.createElement("div");
    toast.className = `toast ${type}`;

    toast.innerHTML = `
        <i class="fas fa-info-circle"></i>
        <span>${message}</span>
    `;

    container.appendChild(toast);

    setTimeout(() => toast.remove(), 3000);
}