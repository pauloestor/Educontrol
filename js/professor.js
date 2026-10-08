/* ================================================
   SISTEMA DE CONTROLO DE PRESENÇA DE PROFESSORES
   JavaScript - Lógica da Aplicação
   ================================================ */


   
// Objeto Principal da Aplicação
const app = {
    // ========================
    // 1. DADOS GLOBAIS
    // ========================
    currentSection: 'dashboard',
    currentTheme: localStorage.getItem('theme') || 'light',
    

    init() {
    console.log('[v0] Aplicação iniciada');

    this.setupTheme();
    this.setupEventListeners();

    this.updateDateTime();
    this.drawCharts();

    // HISTÓRICO INICIAL
    this.carregarHistorico();
    this.carregarAssiduidade();
    this.carregarDashboard();

    setInterval(() => this.updateDateTime(), 1000);

    
},
    // ========================
    // 3. GERENCIAMENTO DE TEMAS
    // ========================
    setupTheme() {
        const theme = this.currentTheme;
        if (theme === 'dark') {
            document.documentElement.classList.add('dark-mode');
            document.querySelector('input[name="theme"][value="dark"]').checked = true;
        } else {
            document.documentElement.classList.remove('dark-mode');
            document.querySelector('input[name="theme"][value="light"]').checked = true;
        }
    },

    setTheme(theme) {
        this.currentTheme = theme;
        localStorage.setItem('theme', theme);
        
        if (theme === 'dark') {
            document.documentElement.classList.add('dark-mode');
        } else {
            document.documentElement.classList.remove('dark-mode');
        }
        
        this.showToast('success', 'Tema Alterado', `Tema ${theme === 'dark' ? 'escuro' : 'claro'} ativado com sucesso.`);
    },

    setupEventListeners() {

    document.getElementById('sidebarToggle')?.addEventListener('click', () => {
        document.getElementById('sidebar')?.classList.toggle('collapsed');
    });

    const userMenuBtn = document.getElementById('userMenuBtn');
    const userDropdown = document.getElementById('userDropdown');

    if (userMenuBtn && userDropdown) {
        userMenuBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            userDropdown.classList.toggle('active');
        });

        document.addEventListener('click', () => {
            userDropdown.classList.remove('active');
        });
    }

    document.getElementById('themeToggle')?.addEventListener('click', () => {
        const newTheme = this.currentTheme === 'light' ? 'dark' : 'light';
        this.setTheme(newTheme);
    });

    // =========================
    // HISTÓRICO (CORRETO)
    // =========================

    document.getElementById('historySearch')?.addEventListener('input', (e) => {
        this.filtrarLocalmente(e);
    });

    document.getElementById('monthFilter')?.addEventListener('change', () => {
        this.currentPage = 1;
        this.carregarHistorico();
    });

    document.getElementById('yearFilter')?.addEventListener('change', () => {
        this.currentPage = 1;
        this.carregarHistorico();
    });

    document.getElementById('prevPageBtn')?.addEventListener('click', () => {
        if (this.currentPage > 1) {
            this.currentPage--;
            this.carregarHistorico();
        }
    });

    document.getElementById('nextPageBtn')?.addEventListener('click', () => {
        if (this.currentPage < this.totalPages) {
            this.currentPage++;
            this.carregarHistorico();
        }
    });

    document.getElementById("assiduity-section")?.addEventListener("change", () => {
    app.carregarAssiduidade();
    });

    // ========================
    // Profile Photo Upload
    // ========================
    document.getElementById('photoInput')?.addEventListener('change', (e) => {
        this.uploadProfilePhoto(e);
    });

    
},
    // ========================
    // 5. NAVEGAÇÃO E SEÇÕES
    // ========================
    showSection(sectionName) {
        console.log('[v0] Exibindo seção:', sectionName);
        
        // Atualizar menu ativo
        document.querySelectorAll('.menu-item').forEach(item => {
            item.classList.remove('active');
            if (item.getAttribute('data-section') === sectionName) {
                item.classList.add('active');
            }
        });

        // Esconder todas as seções
        document.querySelectorAll('.content-section').forEach(section => {
            section.classList.remove('active');
        });

        // Exibir seção selecionada
        document.getElementById(`${sectionName}-section`).classList.add('active');
        
        // Atualizar breadcrumbs
        const breadcrumbText = {
            'dashboard': 'Dashboard',
            'attendance': 'Marcar Presença',
            'history': 'Histórico',
            'assiduity': 'Minhas Assiduidade',
            'profile': 'Meu Perfil',
            'settings': 'Configurações'
        };
        document.getElementById('breadcrumbs').textContent = breadcrumbText[sectionName] || sectionName;

        this.currentSection = sectionName;
    },

    // ========================
    // 6. DATA E HORA
    // ========================
    updateDateTime() {
        const now = new Date();
        
        // Formato Data
        const options = { year: 'numeric', month: 'long', day: 'numeric', weekday: 'long' };
        const dateStr = now.toLocaleDateString('pt-PT', options);
        document.getElementById('currentDate').textContent = dateStr;

        // Formato Hora
        const timeStr = now.toLocaleTimeString('pt-PT', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        document.getElementById('currentTime').textContent = timeStr;

        // Atualizar hora em Marcar Presença
        if (document.getElementById('currentTimeDisplay')) {
            document.getElementById('currentTimeDisplay').textContent = timeStr;
        }

        // Atualizar data em Marcar Presença
        if (document.getElementById('attendanceDateInput')) {
            document.getElementById('attendanceDateInput').value = dateStr;
        }
    },

    // ========================
    // 7. PRESENÇA
    // ========================
async carregarEstadoPresenca() {

    try {

        const res = await fetch("phpprofessor/carregar_estado_presenca.php");
        const data = await res.json();

        if (!data.success) return;

        const d = data.data;

        const escala = d.escala;
        const presenca = d.presenca;

        const estadoEl = document.getElementById("attendanceStatus");
        const entradaEl = document.getElementById("entryTimeDisplay");
        const saidaEl = document.getElementById("exitTimeDisplay");

        // ==============================
        // HORA ATUAL
        // ==============================
        const now = new Date();
        const horaAtual = now.getHours() * 60 + now.getMinutes();

        const [hEnt, mEnt] = escala.hora_entrada.split(":").map(Number);
        const [hSai, mSai] = escala.hora_saida.split(":").map(Number);

        const entradaMin = hEnt * 60 + mEnt;
        const saidaMin = hSai * 60 + mSai;

        const tolerancia = parseInt(escala.tolerancia) || 0;

        // ==============================
        // VALORES BASE
        // ==============================
        let estado = "Não iniciado";

        let entradaMarcada = !!presenca?.hora_entrada;
        let saidaMarcada = !!presenca?.hora_saida;

        // ==============================
        // SOBRESCREVER SE JÁ REGISTADO
        // ==============================
        if (saidaMarcada) {

            estado = "Registado";

        } else if (entradaMarcada) {

            // já marcou entrada → mantém estado fixo da BD
            estado = presenca.estado || "Presente";

        } else {

            // ==============================
            // CÁLCULO EM TEMPO REAL
            // ==============================

            if (horaAtual < entradaMin) {

                estado = "Aguardando horário";

            } else if (horaAtual <= (entradaMin + tolerancia)) {

                estado = "Presente";

            } else if (horaAtual < saidaMin) {

                estado = "Atrasado";

            } else {

                estado = "Falta";
            }
        }

        // ==============================
        // UI UPDATE
        // ==============================
        estadoEl.textContent = estado;

        entradaEl.textContent = presenca?.hora_entrada || "--:--:--";
        saidaEl.textContent = presenca?.hora_saida || "--:--:--";

        // ==============================
        // BOTÕES
        // ==============================
        const btnEntrada = document.getElementById("markEntryBtn");
        const btnSaida = document.getElementById("markExitBtn");

        if (btnEntrada && btnSaida) {

            btnEntrada.disabled = entradaMarcada;

            btnSaida.disabled = !entradaMarcada || saidaMarcada;
        }

    } catch (err) {

        console.error(err);
    }
},
// ================== ENTRADA ==================
async markEntry() {

    console.log("1 - Cliquei");

    try {

        console.log("2 - Antes do fetch");

        const res = await fetch("phpprofessor/marcar_presenca.php", {
            method: "POST"
        });

        const data = await res.json();

        console.log(data);

        if (data.success) {

            document.getElementById("entryTimeDisplay").textContent = data.hora_entrada;

            document.getElementById("attendanceStatus").textContent = data.estado;

            document.getElementById("markEntryBtn").disabled = true;

            document.getElementById("markExitBtn").disabled = false;

            this.showToast(
                "success",
                "Presença",
                "Entrada registada com sucesso."
            );

        } else {

            this.showToast(
                "error",
                "Erro",
                data.message
            );

        }

    } catch (error) {

        console.error(error);

        this.showToast(
            "error",
            "Erro",
            "Erro de comunicação com o servidor."
        );

    }

},

// ==================== SAÍDA ======================
async markExit() {

    try {

        const res = await fetch("phpprofessor/marcar_saida.php", {
            method: "POST"
        });

        const data = await res.json();

        console.log(data);

        if (data.success) {

            document.getElementById("exitTimeDisplay").textContent = data.hora_saida;

            document.getElementById("attendanceStatus").textContent = "Registado";

            document.getElementById("markExitBtn").disabled = true;

            this.showToast(
                "success",
                "Presença",
                "Saída registada com sucesso."
            );

        } else {

            this.showToast(
                "error",
                "Erro",
                data.message
            );

        }

    } catch (error) {

        console.error(error);

        this.showToast(
            "error",
            "Erro",
            "Erro de comunicação com o servidor."
        );

    }

},

// ========================
    // 8. HISTÓRICO
    // ========================
    currentPage: 1,
    totalPages: 1,
    //==========================================================================
    //===========================================================================
    async carregarHistorico() {
    try {
        const mes = document.getElementById("monthFilter")?.value || "";
        const ano = document.getElementById("yearFilter")?.value || "";
        const search = document.getElementById("historySearch")?.value || "";

        const url = `phpprofessor/carregar_historico_presencas.php?page=${this.currentPage}&mes=${mes}&ano=${ano}&search=${search}`;

        const res = await fetch(url);
        const data = await res.json();

        console.log("HISTÓRICO:", data);

        if (!data.success) {
            this.showToast("error", "Erro", data.message || "Erro ao carregar histórico");
            return;
        }

        this.renderHistorico(data.data);
        this.atualizarPaginacao(data.pagination);

    } catch (err) {
        console.error("Erro histórico:", err);
        this.showToast("error", "Erro", "Falha na comunicação com o servidor");
    }
},

renderHistorico(dados) {
    const tbody = document.getElementById("historyTableBody");
    if (!tbody) return;

    tbody.innerHTML = "";

    if (!dados || dados.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" style="text-align:center; padding:20px;">
                    Nenhum registo encontrado
                </td>
            </tr>
        `;
        return;
    }

    dados.forEach(item => {
        const tr = document.createElement("tr");

        tr.innerHTML = `
            <td>${this.formatarData(item.data_presenca)}</td>
            <td>${item.hora_entrada || "--:--"}</td>
            <td>${item.hora_saida || "--:--"}</td>
            <td>
                <span class="status-badge ${this.getEstadoClass(item.estado)}">
                    ${item.estado}
                </span>
            </td>
        `;

        tbody.appendChild(tr);
    });
},

formatarData(data) {
    if (!data) return "--";

    const d = new Date(data);
    return d.toLocaleDateString("pt-PT");
},

getEstadoClass(estado) {
    switch (estado) {
        case "Presente":
            return "present";
        case "Atrasado":
        case "Atraso":
            return "late";
        case "Falta":
            return "absent";
        default:
            return "";
    }
},

atualizarPaginacao(pagination) {
    if (!pagination) return;

    this.totalPages = pagination.pages || 1;

    const info = document.getElementById("paginationInfo");
    if (info) {
        info.textContent = `Página ${pagination.page} de ${pagination.pages}`;
    }

    const prev = document.getElementById("prevPageBtn");
    const next = document.getElementById("nextPageBtn");

    if (prev) prev.disabled = pagination.page <= 1;
    if (next) next.disabled = pagination.page >= pagination.pages;
},
    filtrarLocalmente(e) {
    const value = e.target.value.toLowerCase();
    const rows = document.querySelectorAll("#historyTableBody tr");

    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(value) ? "" : "none";
    });
},

//========================================= FIM DO HISTORICO ================

//========================================= ASSIDUIDADE ====================
async carregarAssiduidade() {
    try {
        const res = await fetch("phpprofessor/carregar_assiduidade.php");
        const data = await res.json();

        if (!data.success) return;

        const d = data.data;

        // =========================
        // INDICADORES
        // =========================
        document.querySelector(".indicator-card:nth-child(1) strong").textContent = d.dias_trabalhados;
        document.querySelector(".indicator-card:nth-child(2) strong").textContent = d.presencas;
        document.querySelector(".indicator-card:nth-child(3) strong").textContent = d.faltas;
        document.querySelector(".indicator-card:nth-child(4) strong").textContent = d.atrasos;
        document.querySelector(".indicator-card.accent strong").textContent = d.percentagem + "%";

        // =========================
        // BARRAS
        // =========================
        const bars = document.querySelectorAll(".stat-bar-progress");

        if (bars[0]) bars[0].style.width = d.percentagem + "%";
        if (bars[1]) bars[1].style.width = d.atrasos + "%";
        if (bars[2]) bars[2].style.width = d.faltas + "%";

        // =========================
        // TABELA MENSAL
        // =========================
        const tbody = document.querySelector(".summary-table tbody");
        tbody.innerHTML = "";

        d.mensal.forEach(m => {
            const tr = document.createElement("tr");

            tr.innerHTML = `
                <td>${m.mes}</td>
                <td>${m.presencas}</td>
                <td>${m.faltas}</td>
                <td>${m.atrasos}</td>
                <td><strong>${m.percentagem}%</strong></td>
            `;

            tbody.appendChild(tr);
        });

        // =========================
        // MENSAGEM INTELIGENTE
        // =========================
        const msg = document.getElementById("intelligentMessage");

        if (d.percentagem >= 95) {
            msg.textContent = "Excelente desempenho! A sua taxa de assiduidade é muito alta.";
        } else if (d.percentagem >= 80) {
            msg.textContent = "Bom desempenho, mas pode melhorar a pontualidade.";
        } else {
            msg.textContent = "Atenção! A sua assiduidade precisa de melhoria.";
        }

    } catch (err) {
        console.error("Erro ao carregar assiduidade:", err);
    }
},

//======================================= FIM DA ASSIDUIDADE =======================



    // ========================
    // 9. PERFIL
    // ========================
    uploadProfilePhoto(event) {
    const file = event.target.files[0];
    if (!file) return;

    // Preview
    const reader = new FileReader();
    reader.onload = (e) => {
        document.getElementById('profilePhoto').src = e.target.result;
    };
    reader.readAsDataURL(file);

    const formData = new FormData();
    formData.append("foto", file);

    fetch("/ControlePresenca/phpprofessor/uploadFoto.php", {
    method: "POST",
    body: formData
})
.then(async (response) => {
    const text = await response.text();

    console.log("=== STATUS HTTP ===");
    console.log(response.status);

    console.log("=== RESPOSTA BRUTA DO PHP ===");
    console.log(text);

    return text; // não faz JSON ainda
})
.catch(err => {
    console.error("ERRO FETCH:", err);
});
    
},
//===============================================================================
                                      //FIM DO PERFIL
//================================================================================

//=================================
// CARREGAR PERFIL DO PROFESSOR
//=================================

carregarPerfil() {

    fetch("/ControlePresenca/phpprofessor/carregar_perfil.php")
        .then(response => response.json())
        .then(data => {

            console.log("PERFIL:", data);

            if (!data.success) {
                this.showToast("error", "Erro", data.message);
                return;
            }

            const user = data.data;

            //=========================
            // FOTO
            //=========================

            const foto = document.getElementById("profilePhoto");

            if (foto) {

                foto.src = user.foto
                    ? "/ControlePresenca/" + user.foto + "?v=" + new Date().getTime()
                    : "https://ui-avatars.com/api/?name=" +
                        encodeURIComponent(user.nome) +
                        "&size=200&background=4f46e5&color=fff";
            }

            //=========================
            // CAMPOS
            //=========================

            const nome = document.getElementById("profileName");
            if (nome) nome.value = user.nome ?? "";

            const numero = document.getElementById("profileNumber");
            if (numero) numero.value = user.numero_mecanografico ?? "";

            const genero = document.getElementById("profileGender");
            if (genero) genero.value = user.genero ?? "";

            const nascimento = document.getElementById("profileBirth");
            if (nascimento) nascimento.value = user.data_nascimento ?? "";

            const email = document.getElementById("emailInput");
            if (email) email.value = user.email ?? "";

            const telefone = document.getElementById("phoneInput");
            if (telefone) telefone.value = user.telefone ?? "";

            const disciplina = document.getElementById("profileDiscipline");
            if (disciplina) disciplina.value = user.disciplina ?? "";

            const cargo = document.getElementById("profileRole");
            if (cargo) cargo.value = user.tipo ?? "";

            const admissao = document.getElementById("profileAdmission");

            if (admissao) {

                if (user.created_at) {

                    const dataAdm = new Date(user.created_at);

                    admissao.value = dataAdm.toLocaleDateString("pt-PT");

                } else {

                    admissao.value = "";
                }
            }

        })
        .catch(error => {

            console.error(error);

            this.showToast(
                "error",
                "Erro",
                "Falha ao carregar os dados do perfil."
            );

        });

},
//================================== FIM CARREGAR PERFIL
 // ========================
// GUARDAR ALTERAÇÕES DO PERFIL
// ========================
saveProfileChanges() {

    const email = document.getElementById("emailInput")?.value.trim();
    const phone = document.getElementById("phoneInput")?.value.trim();

    if (!email || !phone) {
        this.showToast("error", "Erro", "Preencha todos os campos.");
        return;
    }

    // Validar email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!emailRegex.test(email)) {
        this.showToast("error", "Email inválido", "Digite um endereço de email válido.");
        return;
    }

    const formData = new FormData();
    formData.append("email", email);
    formData.append("telefone", phone);

    fetch("/ControlePresenca/phpprofessor/atualizar_perfil.php", {
        method: "POST",
        body: formData
    })
    .then(async (response) => {

    const text = await response.text();

    console.log("STATUS:", response.status);
    console.log("RESPOSTA:", text);

    return JSON.parse(text);

})
    .then(data => {

        console.log("ATUALIZAR PERFIL:", data);

        if (data.success) {

            this.showToast(
                "success",
                "Perfil atualizado",
                data.message
            );

        } else {

            this.showToast(
                "error",
                "Erro",
                data.message
            );
        }

    })
    .catch(error => {

        console.error(error);

        this.showToast(
            "error",
            "Erro",
            "Falha na comunicação com o servidor."
        );

    });

},

//================================= FIM GUARDAR ALTERAÇÕES DO PERFIL
    // ========================
    // 10. CONFIGURAÇÕES
    // ========================
    togglePasswordField(fieldId) {
        const input = document.getElementById(fieldId);
        const button = event.target.closest('button');
        const icon = button.querySelector('i');

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    },

    checkPasswordStrength() {
        const password = document.getElementById('newPassword')?.value || '';
        const strengthFill = document.getElementById('strengthFill');
        const strengthText = document.getElementById('strengthText');

        let strength = 0;
        let strengthLabel = 'Fraca';
        let strengthColor = '#ef4444';

        if (password.length >= 8) strength += 25;
        if (password.match(/[a-z]/)) strength += 25;
        if (password.match(/[A-Z]/)) strength += 25;
        if (password.match(/[0-9]/)) strength += 15;
        if (password.match(/[^a-zA-Z0-9]/)) strength += 10;

        if (strength < 50) {
            strengthLabel = 'Fraca';
            strengthColor = '#ef4444';
        } else if (strength < 75) {
            strengthLabel = 'Média';
            strengthColor = '#f97316';
        } else {
            strengthLabel = 'Forte';
            strengthColor = '#22c55e';
        }

        strengthFill.style.width = strength + '%';
        strengthFill.style.background = strengthColor;
        strengthText.textContent = strengthLabel;
    },

    changePassword() {
        const currentPass = document.getElementById('currentPassword')?.value || '';
        const newPass = document.getElementById('newPassword')?.value || '';
        const confirmPass = document.getElementById('confirmPassword')?.value || '';

        if (!currentPass || !newPass || !confirmPass) {
            this.showToast('error', 'Campos Vazios', 'Preencha todos os campos de palavra-passe.');
            return;
        }

        if (newPass.length < 8) {
            this.showToast('error', 'Palavra-passe Fraca', 'A palavra-passe deve ter no mínimo 8 caracteres.');
            return;
        }

        if (newPass !== confirmPass) {
            this.showToast('error', 'Palavras-passe Diferentes', 'As palavras-passe não coincidem.');
            return;
        }

        // Simular validação
        if (currentPass !== 'password123') {
            this.showToast('error', 'Palavra-passe Incorreta', 'A palavra-passe atual está incorreta.');
            return;
        }

        localStorage.setItem('password', newPass);
        this.showToast('success', 'Palavra-passe Atualizada', 'Palavra-passe alterada com sucesso.');
        
        // Limpar campos
        document.getElementById('currentPassword').value = '';
        document.getElementById('newPassword').value = '';
        document.getElementById('confirmPassword').value = '';
        document.getElementById('strengthFill').style.width = '0%';
    },


    closeModal(modalId) {
        document.getElementById(modalId).classList.remove('active');
    },

    // ========================
    // 12. GRÁFICOS
    // ========================
    drawCharts() {
        this.drawBarChart();
        this.drawPieChart();
        this.drawLineChart();
        this.drawDonutChart();
    },

    drawBarChart() {
        const canvas = document.getElementById('barChart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        const monthLabels = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'];
        const data = [18, 20, 19, 22, 20, 22];

        const width = canvas.width = canvas.offsetWidth;
        const height = canvas.height = 300;
        const chartWidth = width - 60;
        const chartHeight = height - 60;
        const barWidth = chartWidth / monthLabels.length * 0.8;
        const maxValue = Math.max(...data);

        // Fundo
        ctx.fillStyle = this.getComputedColorVar('--bg-primary');
        ctx.fillRect(0, 0, width, height);

        // Labels
        ctx.fillStyle = this.getComputedColorVar('--text-secondary');
        ctx.font = '12px Inter';
        ctx.textAlign = 'center';

        // Desenhar barras
        data.forEach((value, index) => {
            const barHeight = (value / maxValue) * chartHeight;
            const x = 30 + (index + 0.5) * (chartWidth / monthLabels.length);
            const y = height - 30 - barHeight;

            // Barra
            ctx.fillStyle = this.getComputedColorVar('--color-primary');
            ctx.fillRect(x - barWidth / 2, y, barWidth, barHeight);

            // Label
            ctx.fillStyle = this.getComputedColorVar('--text-secondary');
            ctx.fillText(monthLabels[index], x, height - 10);
            ctx.fillText(value, x, y - 5);
        });

        // Eixo Y
        ctx.strokeStyle = this.getComputedColorVar('--border-color');
        ctx.beginPath();
        ctx.moveTo(30, 30);
        ctx.lineTo(30, height - 30);
        ctx.stroke();

        // Eixo X
        ctx.beginPath();
        ctx.moveTo(30, height - 30);
        ctx.lineTo(width - 30, height - 30);
        ctx.stroke();
    },

    drawPieChart() {
        const canvas = document.getElementById('pieChart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        const width = canvas.width = canvas.offsetWidth;
        const height = canvas.height = 300;

        const data = [
            { label: 'Presente', value: 22, color: this.getComputedColorVar('--color-present') },
            { label: 'Atrasado', value: 2, color: this.getComputedColorVar('--color-late') },
            { label: 'Ausente', value: 1, color: this.getComputedColorVar('--color-absent') }
        ];

        const total = data.reduce((sum, item) => sum + item.value, 0);
        const centerX = width / 2;
        const centerY = height / 2;
        const radius = Math.min(width, height) / 3;

        let currentAngle = -Math.PI / 2;

        data.forEach((item) => {
            const sliceAngle = (item.value / total) * 2 * Math.PI;

            // Desenhar fatia
            ctx.fillStyle = item.color;
            ctx.beginPath();
            ctx.moveTo(centerX, centerY);
            ctx.arc(centerX, centerY, radius, currentAngle, currentAngle + sliceAngle);
            ctx.closePath();
            ctx.fill();

            // Label
            const labelAngle = currentAngle + sliceAngle / 2;
            const labelX = centerX + Math.cos(labelAngle) * (radius * 0.7);
            const labelY = centerY + Math.sin(labelAngle) * (radius * 0.7);

            ctx.fillStyle = 'white';
            ctx.font = 'bold 12px Inter';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            const percentage = Math.round((item.value / total) * 100);
            ctx.fillText(percentage + '%', labelX, labelY);

            currentAngle += sliceAngle;
        });
    },

    drawLineChart() {
        const canvas = document.getElementById('lineChart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        const width = canvas.width = canvas.offsetWidth;
        const height = canvas.height = 300;

        const months = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'];
        const data = [88, 85, 90, 95, 92, 95];

        const chartWidth = width - 60;
        const chartHeight = height - 60;
        const pointSpacing = chartWidth / (data.length - 1);
        const maxValue = 100;

        // Fundo
        ctx.fillStyle = this.getComputedColorVar('--bg-primary');
        ctx.fillRect(0, 0, width, height);

        // Desenhar linha
        ctx.strokeStyle = this.getComputedColorVar('--color-primary');
        ctx.lineWidth = 2;
        ctx.beginPath();

        data.forEach((value, index) => {
            const x = 30 + index * pointSpacing;
            const y = height - 30 - (value / maxValue) * chartHeight;

            if (index === 0) {
                ctx.moveTo(x, y);
            } else {
                ctx.lineTo(x, y);
            }
        });

        ctx.stroke();

        // Desenhar pontos
        ctx.fillStyle = this.getComputedColorVar('--color-primary');
        data.forEach((value, index) => {
            const x = 30 + index * pointSpacing;
            const y = height - 30 - (value / maxValue) * chartHeight;

            ctx.beginPath();
            ctx.arc(x, y, 4, 0, 2 * Math.PI);
            ctx.fill();
        });

        // Labels
        ctx.fillStyle = this.getComputedColorVar('--text-secondary');
        ctx.font = '12px Inter';
        ctx.textAlign = 'center';

        months.forEach((month, index) => {
            const x = 30 + index * pointSpacing;
            ctx.fillText(month, x, height - 10);
        });
    },

    drawDonutChart() {
        const canvas = document.getElementById('donutChart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        const width = canvas.width = canvas.offsetWidth;
        const height = canvas.height = 300;

        const data = [
            { label: 'Presente', value: 95.7, color: this.getComputedColorVar('--color-present') },
            { label: 'Atrasado', value: 2.7, color: this.getComputedColorVar('--color-late') },
            { label: 'Ausente', value: 1.4, color: this.getComputedColorVar('--color-absent') }
        ];

        const centerX = width / 2;
        const centerY = height / 2;
        const radius = Math.min(width, height) / 3;
        const innerRadius = radius * 0.6;

        let currentAngle = -Math.PI / 2;

        data.forEach((item) => {
            const sliceAngle = (item.value / 100) * 2 * Math.PI;

            // Desenhar fatia
            ctx.fillStyle = item.color;
            ctx.beginPath();
            ctx.arc(centerX, centerY, radius, currentAngle, currentAngle + sliceAngle);
            ctx.arc(centerX, centerY, innerRadius, currentAngle + sliceAngle, currentAngle, true);
            ctx.closePath();
            ctx.fill();

            currentAngle += sliceAngle;
        });

        // Centro
        ctx.fillStyle = this.getComputedColorVar('--bg-primary');
        ctx.beginPath();
        ctx.arc(centerX, centerY, innerRadius, 0, 2 * Math.PI);
        ctx.fill();
    },

    getComputedColorVar(varName) {
        return getComputedStyle(document.documentElement).getPropertyValue(varName).trim();
    },

    // ========================
    // 13. NOTIFICAÇÕES TOAST
    // ========================
    showToast(type, title, message, duration = 4000) {
        const container = document.getElementById('toastContainer');
        const toastId = 'toast-' + Date.now();

        const icons = {
            'success': 'fa-check-circle',
            'error': 'fa-exclamation-circle',
            'warning': 'fa-exclamation-triangle'
        };

        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.id = toastId;
        toast.innerHTML = `
            <div class="toast-icon">
                <i class="fas ${icons[type]}"></i>
            </div>
            <div class="toast-content">
                <div class="toast-title">${title}</div>
                <div class="toast-message">${message}</div>
            </div>
            <button class="toast-close" onclick="document.getElementById('${toastId}').remove()">
                <i class="fas fa-times"></i>
            </button>
        `;

        container.appendChild(toast);

        setTimeout(() => {
            const element = document.getElementById(toastId);
            if (element) element.remove();
        }, duration);

        console.log(`[v0] Toast [${type}]: ${title} - ${message}`);
    },

    // ========================
    // 14. UTILITÁRIOS
    // ========================
    formatDate(date) {
        return date.toLocaleDateString('pt-PT', { 
            year: 'numeric', 
            month: '2-digit', 
            day: '2-digit' 
        });
    },

    formatTime(date) {
        return date.toLocaleTimeString('pt-PT', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
    },

    //==================================== DASHBOARD ================================

    async carregarDashboard() {
    try {
        const res = await fetch("phpprofessor/carregar_dashboard.php");
        const data = await res.json();

        if (!data.success) return;

        const d = data.data;

        // =====================
        // CARDS
        // =====================
        document.querySelector(".stat-card:nth-child(1) .stat-value").textContent = d.resumo.presencas || 0;
        document.querySelector(".stat-card:nth-child(2) .stat-value").textContent = d.resumo.atrasos || 0;
        document.querySelector(".stat-card:nth-child(3) .stat-value").textContent = d.resumo.faltas || 0;

        // =====================
        // ÚLTIMA PRESENÇA
        // =====================
        if (d.ultima) {
            document.querySelector(".last-attendance .stat-value").textContent = d.ultima.data_presenca;

            document.querySelector(".last-attendance .stat-desc").textContent =
                `${d.ultima.hora_entrada || "--"} - ${d.ultima.hora_saida || "--"}`;
        }

        // =====================
        // ATIVIDADES RECENTES
        // =====================
        const tbody = document.querySelector(".activities-table tbody");
        tbody.innerHTML = "";

        d.atividades.forEach(item => {
            const tr = document.createElement("tr");

            tr.innerHTML = `
                <td>${item.data_presenca}</td>
                <td>${item.hora_entrada || "--:--"}</td>
                <td><span class="status-badge">${item.estado}</span></td>
            `;

            tbody.appendChild(tr);
        });

    } catch (err) {
        console.error("Erro dashboard:", err);
    }
},

};

// ========================
// INICIALIZAÇÃO
// ========================
document.addEventListener('DOMContentLoaded', () => {
    app.init();
});

// Fechar modais clicando fora
document.addEventListener('click', (event) => {
    const modals = document.querySelectorAll('.modal.active');
    modals.forEach(modal => {
        if (event.target === modal) {
            modal.classList.remove('active');
        }
    });
});

// Fechar dropdown de notificações
document.getElementById('notificationsBtn')?.addEventListener('click', (e) => {
    e.stopPropagation();
    console.log('[v0] Notificações clicadas');
});

document.addEventListener("DOMContentLoaded", () => {
    app.carregarPerfil();
});


//======================= IMAGEM DE PERFIL NO HEADER ===========
async function carregarPerfilMenu() {
    try {
        const res = await fetch("phpprofessor/perfil_logado.php");
        const data = await res.json();

        if (!data.success) return;

        const user = data.data;

        const avatar = document.querySelector(".user-avatar");

        if (avatar) {
            avatar.src = user.foto 
                ? user.foto 
                : "https://ui-avatars.com/api/?name=" + encodeURIComponent(user.nome);
        }

    } catch (err) {
        console.error("Erro ao carregar perfil:", err);
    }
}

document.addEventListener("DOMContentLoaded", carregarPerfilMenu);

//===================== IMAGEM DE PERFIL NO HEADER ==================


 // ========================
    // 11. LOGOUT
    // ========================

async function logout(event) {
    event.preventDefault();

    try {
        const res = await fetch('/ControlePresenca/phpconexao/logout.html', {
            method: 'POST',
            credentials: 'include'
        });

        const data = await res.json();

        if (data.success) {
            localStorage.clear();
            sessionStorage.clear();

            window.location.replace('/ControlePresenca/login.php');
        } else {
            console.error("Erro no logout:", data.message);
        }

    } catch (err) {
        console.error('Erro ao fazer logout:', err);
    }
}


async function logout() {
    try {
        await fetch('/ControlePresenca/phpconexao/logout.php', {
            method: 'GET',
            credentials: 'include'
        });

        localStorage.clear();
        sessionStorage.clear();

        window.location.href = '/ControlePresenca/login.html';

    } catch (err) {
        console.error('Erro ao fazer logout:', err);
    }
}
console.log('[v0] Sistema de Controlo de Presença de Professores - Versão 1.0');
//===================== PROTEÇÃO DE PAGINAS =============
window.addEventListener("pageshow", function (event) {
    if (event.persisted) {
        window.location.reload();
    }
});

if (!document.cookie.includes("PHPSESSID")) {
    window.location.href = "login.html";
}