// ========== APP STATE ==========
const appState = {
    currentPage: 'dashboard',
    theme: localStorage.getItem('theme') || 'light',
    sidebarOpen: window.innerWidth > 768,
    data: {
        disciplines: [],
        teachers: [],
        schedules: [],
        markings: [],
    }

    
};

// ========== INITIALIZATION ==========
document.addEventListener('DOMContentLoaded', () => {

    document.querySelector('.logout').addEventListener('click', (e) => {
    e.preventDefault();
    openLogoutModal();
});
    
    initializeApp();
    setupEventListeners();
    applyTheme();
    updateTime();
    carregarDisciplinas();
    carregarDisciplinasSelect();
    carregarProfessoresEscala();
    carregarMonitorizacao(); 
});

function initializeApp() {
    if (window.innerWidth <= 768) {
        appState.sidebarOpen = false;
    }
}

// ========== THEME MANAGEMENT ==========
function applyTheme() {
    document.documentElement.setAttribute('data-theme', appState.theme);
    localStorage.setItem('theme', appState.theme);
    
    const themeIcon = document.getElementById('themeToggle').querySelector('i');
    if (appState.theme === 'dark') {
        themeIcon.classList.remove('fa-sun');
        themeIcon.classList.add('fa-moon');
        document.getElementById('darkThemeBtn').classList.add('active');
        document.getElementById('lightThemeBtn').classList.remove('active');
    } else {
        themeIcon.classList.remove('fa-moon');
        themeIcon.classList.add('fa-sun');
        document.getElementById('lightThemeBtn').classList.add('active');
        document.getElementById('darkThemeBtn').classList.remove('active');
    }
}

function toggleTheme() {
    appState.theme = appState.theme === 'light' ? 'dark' : 'light';
    applyTheme();
}

// ========== FAKE DATA GENERATION ==========

// ========== EVENT LISTENERS ==========
function setupEventListeners() {
    // Navigation
    document.querySelectorAll('.nav-item').forEach(item => {
        item.addEventListener('click', handleNavigation);
    });

    // Theme toggle
    document.getElementById('themeToggle').addEventListener('click', toggleTheme);
    document.getElementById('darkThemeBtn').addEventListener('click', () => {
        appState.theme = 'dark';
        applyTheme();
    });
    document.getElementById('lightThemeBtn').addEventListener('click', () => {
        appState.theme = 'light';
        applyTheme();
    });

    // Sidebar
    document.getElementById('sidebarToggle').addEventListener('click', toggleSidebar);
    document.getElementById('sidebarClose').addEventListener('click', closeSidebar);

    // Notifications
    document.getElementById('notificationBtn').addEventListener('click', openNotificationModal);

    // Modal closes
    document.querySelectorAll('[data-modal]').forEach(btn => {
        btn.addEventListener('click', (e) => closeModal(e.target.getAttribute('data-modal')));
    });

    // Page-specific listeners
    document.getElementById('addTeacherBtn')?.addEventListener('click', addTeacher);
    document.getElementById('addScheduleBtn')?.addEventListener('click', addSchedule);
    document.getElementById('changePasswordBtn')?.addEventListener('click', openPasswordModal);
    document.getElementById('exportBtn')?.addEventListener('click', exportData);

    // Logout
    document.getElementById("confirmLogoutBtn").addEventListener("click", logout);

    // Search inputs
    document.getElementById('disciplinesSearch')?.addEventListener('input', filterDisciplines);
    document.getElementById('monitoringSearch')?.addEventListener('input', filterMonitoring);
    document.getElementById("monitoringStatus")?.addEventListener("change", filterMonitoring);
    document.getElementById("monitoringDate")?.addEventListener("change", filterMonitoring);

    // Filters
    document.getElementById('monitoringMonth')?.addEventListener('change', filterMonitoring);
}



// ========== NAVIGATION ==========
function handleNavigation(e) {
    e.preventDefault();
    const page = e.currentTarget.getAttribute('data-page');
    
    if (page === 'logout') {
        openLogoutModal();
        return;
    }

    navigateTo(page);
}

function navigateTo(page) {
    if (page === appState.currentPage) return;

    // Hide all pages
    document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
    
    // Show selected page
    const pageElement = document.getElementById(`${page}-page`);
    if (pageElement) {
        pageElement.classList.add('active');
    }

    // Update active nav item
    document.querySelectorAll('.nav-item').forEach(item => {
        item.classList.toggle('active', item.getAttribute('data-page') === page);
    });

    appState.currentPage = page;
    
    // Close sidebar on mobile
    if (window.innerWidth <= 768) {
        closeSidebar();
    }

    // Render page content
    renderPage(page);
}

function renderPage(page) {
    switch(page) {
        case 'dashboard':
            renderDashboard();
            break;
        case 'disciplines':
            carregarDisciplinas();
            break;
        case 'teachers':
            carregarProfessores();
            break;
        case 'schedules':
            carregarEscalas();
            break;
        case 'monitoring':
            renderMonitoring();
            break;
        case 'reports':
            renderReports();
            break;
    }
}

// ========== DASHBOARD ==========
function renderDashboard() {
    drawCharts();
    renderActivities();
}

function renderActivities() {
    const list = document.getElementById('activitiesList');
    if (!list) return;

    list.innerHTML = appState.data.markings.slice(0, 5).map(m => {
        const teacher = appState.data.teachers.find(t => t.id === m.teacher);
        const icons = {
            'presente': { icon: 'fa-check', color: '#27ae60' },
            'ausente': { icon: 'fa-times', color: '#e74c3c' },
            'atrasado': { icon: 'fa-clock', color: '#f39c12' }
        };
        const iconData = icons[m.status] || icons['presente'];

        return `
            <div class="activity-item">
                <div class="activity-avatar">
                    <i class="fas ${iconData.icon}" style="color: ${iconData.color};"></i>
                </div>
                <div class="activity-content">
                    <div class="activity-title">Marcação de ${m.status === 'presente' ? 'Presença' : m.status === 'ausente' ? 'Ausência' : 'Atraso'}</div>
                    <div class="activity-desc">${teacher.name} - ${m.entry || 'Não apresentado'}</div>
                    <div class="activity-time">Há poucos minutos</div>
                </div>
            </div>
        `;
    }).join('');
}

// ========== PESQUISA DAS DISCIPLINAS ==========

function normalizeText(text) {
    return text
        .toLowerCase()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, ""); // remove acentos
}

// ========== PESQUISA INTELIGENTE LOCAL ==========
function filterDisciplines(e) {

    const search = normalizeText(e.target.value.trim());

    const rows = document.querySelectorAll('#disciplinesList tr');

    rows.forEach(row => {

        const text = normalizeText(row.textContent);

        // divide a pesquisa em palavras
        const keywords = search.split(' ').filter(Boolean);

        // verifica se TODAS as palavras existem na linha
        const match = keywords.every(word => text.includes(word));

        row.style.display = match ? '' : 'none';
    });
}

// ========== TEACHERS ==========
// ========== UTILIZADORES (ADMINISTRADORES E PROFESSORES) ==========
async function carregarProfessores() {

    const grid = document.getElementById("teachersGrid");

    if (!grid) return;

    try {

        const response = await fetch("phpadmin/listar_professores.php");
        const data = await response.json();

        if (!data.success) {

            showToast(data.message, "error");
            return;
        }

        // ADICIONA ISTO
        appState.data.teachers = data.dados;

        if (data.dados.length === 0) {

            grid.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <p>Nenhum utilizador cadastrado.</p>
                </div>
            `;

            return;
        }

        grid.innerHTML = data.dados.map(usuario => {

            // Avatar
            let avatar = "";

            if (usuario.foto && usuario.foto !== "") {

                avatar = `
                    <img
                        src="${usuario.foto}"
                        class="teacher-avatar"
                        alt="${usuario.nome}">
                `;

            } else {

                const iniciais = usuario.nome
                    .split(" ")
                    .map(n => n.charAt(0))
                    .slice(0,2)
                    .join("")
                    .toUpperCase();

                avatar = `
                    <div
                        style="
                            width:60px;
                            height:60px;
                            border-radius:50%;
                            background:linear-gradient(135deg,#3498db,#2980b9);
                            display:flex;
                            align-items:center;
                            justify-content:center;
                            color:#fff;
                            font-weight:bold;
                            font-size:18px;
                            border:3px solid #3498db;
                        ">
                        ${iniciais}
                    </div>
                `;
            }

            return `

                <div class="teacher-card">

                    <div class="card-header">

                        ${avatar}

                        <div>

                            <div class="card-title">
                                ${usuario.nome}
                            </div>

                            <div class="card-subtitle">
                                ${usuario.tipo === "admin"
                                    ? "Administrador"
                                    : (usuario.disciplina ?? "Sem disciplina")}
                            </div>

                        </div>

                    </div>

                    <div class="card-body">

                        <div class="card-row">

                            <span class="card-row-label">
                                Email:
                            </span>

                            <span class="card-row-value">
                                ${usuario.email}
                            </span>

                        </div>

                        <div class="card-row">

                            <span class="card-row-label">
                                Telefone:
                            </span>

                            <span class="card-row-value">
                                ${usuario.telefone ?? "-"}
                            </span>

                        </div>

                        <div class="card-row">

                            <span class="card-row-label">
                                Nº Mecanográfico:
                            </span>

                            <span class="card-row-value">
                                ${usuario.numero_mecanografico ?? "-"}
                            </span>

                        </div>

                    </div>

                    <div class="card-actions">

                        <button
                            class="btn-icon"
                            title="Visualizar"
                            data-id="${usuario.id}">

                            <i class="fas fa-eye"></i>

                        </button>

                        <button
                            class="btn-icon"
                            title="Editar"
                            data-id="${usuario.id}">

                            <i class="fas fa-edit"></i>

                        </button>

                        <button
                            class="btn-icon"
                            title="Remover"
                            data-id="${usuario.id}">

                            <i class="fas fa-trash"></i>

                        </button>

                    </div>

                </div>

            `;

        }).join("");

    } catch (error) {

        console.error(error);

        showToast(
            "Erro ao carregar utilizadores.",
            "error"
        );

    }

}

//======================== VISUALIZAR UTILIZADOR ========================

document.addEventListener("click", function (e) {

    const btn = e.target.closest(".btn-icon[title='Visualizar']");

    if (!btn) return;

    const id = btn.dataset.id;

    abrirModalVisualizacao(id);

});

//======================= FUNÇÃO QUE PREENCHE O MODAL ================

function abrirModalVisualizacao(id) {

    const usuario = appState.data.teachers.find(
        u => u.id == id
    );

    if (!usuario) {
        alert("Utilizador não encontrado.");
        return;
    }

    console.log("Usuário encontrado:", usuario);

    // FOTO
    document.getElementById("viewUserPhoto").src =
        usuario.foto || "img/default-user.png";

    // TOPO
    document.getElementById("viewUserName").textContent =
        usuario.nome;

    document.getElementById("viewUserType").textContent =
        usuario.tipo === "admin" ? "Administrador" : "Professor";

    // PESSOAL
    document.getElementById("viewNome").textContent = usuario.nome;
    document.getElementById("viewEmail").textContent = usuario.email;
    document.getElementById("viewTelefone").textContent = usuario.telefone || "-";
    document.getElementById("viewGenero").textContent = usuario.genero || "-";
    document.getElementById("viewNascimento").textContent = usuario.data_nascimento || "-";

    // SISTEMA
    document.getElementById("viewTipo").textContent =
        usuario.tipo === "admin" ? "Administrador" : "Professor";

    document.getElementById("viewCadastro").textContent =
        usuario.created_at;

    document.getElementById("viewUltimoLogin").textContent =
        usuario.ultimo_login || "Nunca iniciou sessão";

    // PROFISSIONAL
    const prof = document.getElementById("professionalSection");

    if (usuario.tipo === "professor") {

        prof.style.display = "block";

        document.getElementById("viewMecanografico").textContent =
            usuario.numero_mecanografico || "-";

        document.getElementById("viewDisciplina").textContent =
            usuario.disciplina || "-";

        document.getElementById("viewSalario").textContent =
            usuario.salario_base
                ? Number(usuario.salario_base).toLocaleString("pt-PT") + " Kz"
                : "-";

    } else {
        prof.style.display = "none";
    }

    // ABRIR MODAL
    document.getElementById("viewUserModal").classList.add("active");
}

//======================= FIM FUNÇÃO QUE PREENCHE O MODAL ================

//======================== FECHAR MODAL ========================

document
.getElementById("closeViewUserModal")
.addEventListener("click", fecharModalVisualizacao);

document
.getElementById("closeViewUserBtn")
.addEventListener("click", fecharModalVisualizacao);

function fecharModalVisualizacao() {

    document
        .getElementById("viewUserModal")
        .classList.remove("active");

}
//============================ FIM DO VIZUALIZAR 


function addTeacher() {
    showToast('Professor adicionado com sucesso!', 'success');
}

// ========== SCHEDULES ==========
// ========== ESCALAS REAIS ==========
async function carregarEscalas() {

    const grid = document.getElementById('schedulesGrid');
    if (!grid) return;

    try {

        const response = await fetch("phpadmin/listar_escalas.php");
        const data = await response.json();

        if (!data.success) {
            grid.innerHTML = `<p>Erro ao carregar escalas</p>`;
            return;
        }

        if (data.dados.length === 0) {
            grid.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-calendar"></i>
                    <p>Nenhuma escala cadastrada.</p>
                </div>
            `;
            return;
        }

        grid.innerHTML = data.dados.map(s => {

            return `
                <div class="schedule-card">

                    <div class="card-header">

                        <div>
                            <div class="card-title">
                                ${s.professor}
                            </div>

                            <div class="card-subtitle">
                                ${s.disciplina}
                            </div>
                        </div>

                    </div>

                    <div class="card-body">

                        <div class="card-row">
                            <span class="card-row-label">Período:</span>
                            <span class="card-row-value">${s.periodo}</span>
                        </div>

                        <div class="card-row">
                            <span class="card-row-label">Horário:</span>
                            <span class="card-row-value">
                                ${s.hora_entrada} - ${s.hora_saida}
                            </span>
                        </div>

                        <div class="card-row">
                            <span class="card-row-label">Dias:</span>
                            <span class="card-row-value">
                                ${s.dias_semana}
                            </span>
                        </div>

                        <div class="card-row">
                            <span class="card-row-label">Tolerância:</span>
                            <span class="card-row-value">
                                ${s.tolerancia_min} min
                            </span>
                        </div>

                    </div>

                    <div class="card-actions">

                        <button class="btn-icon" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>

                        <button class="btn-icon" title="Remover">
                            <i class="fas fa-trash"></i>
                        </button>

                    </div>

                </div>
            `;
        }).join('');

    } catch (error) {
        console.error(error);
        grid.innerHTML = `<p>Erro de conexão</p>`;
    }
}

function addSchedule() {
    showToast('Escala adicionada com sucesso!', 'success');
}

// ========== MONITORIZAÇÃO ==========
async function carregarMonitorizacao() {
    try {

        await fetch("phpadmin/registrar_faltas.php");

        const res = await fetch("phpadmin/carregar_monitorizacao.php");
        const data = await res.json();

        if (!data.success) {
            console.error("Erro monitorização:", data.message);
            return;
        }

        document.getElementById("presentToday").textContent = data.presentes;
        document.getElementById("absentToday").textContent = data.ausentes;
        document.getElementById("lateToday").textContent = data.atrasos;
        document.getElementById("totalMarkings").textContent = data.total;

    } catch (err) {
        console.error("Erro ao carregar monitorização:", err);
    }
}

let monitoringData = []; // cache global

async function carregarMonitorizacao() {
    try {
        const res = await fetch("phpadmin/carregar_monitorizacao.php");
        const data = await res.json();

        if (!data.success) return;

        document.getElementById("presentToday").textContent = data.presentes;
        document.getElementById("absentToday").textContent = data.ausentes;
        document.getElementById("lateToday").textContent = data.atrasos;
        document.getElementById("totalMarkings").textContent = data.total;

        monitoringData = data.lista || []; 
        renderMonitoring();

    } catch (err) {
        console.error("Erro ao carregar monitorização:", err);
    }
}

//=================== FILTRAGEM DE DIA =========================

function renderMonitoring() {

    const tbody = document.getElementById("monitoringList");
    if (!tbody) return;

    const today = new Date().toLocaleDateString('en-CA');

    const list = monitoringData
        .filter(m => m.data_presenca === today)
        .sort((a, b) => {
            return (a.hora_entrada || "99:99:99")
                .localeCompare(b.hora_entrada || "99:99:99");
        });

    tbody.innerHTML = list.map(m => {

    let badge = "";
    if (m.estado === "Presente") badge = "badge-success";
    if (m.estado === "Atraso") badge = "badge-warning";
    if (m.estado === "Falta") badge = "badge-danger";

    console.log("ITEM REAL:", m); // 👈 aqui é o sítio certo

    return `
        <tr>

            <td>
                ${
                    m.foto
                    ? `<img src="${m.foto}" style="width:40px;height:40px;border-radius:50%;object-fit:cover;">`
                    : `<div style="width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#3498db;color:white;font-weight:bold;">
                          ${m.nome.charAt(0)}
                       </div>`
                }
            </td>

            <td>${m.nome}</td>
            <td>${m.mecanografico}</td>
            <td>${m.disciplina}</td>
            <td>${m.data_presenca}</td>
            <td>${m.hora_entrada || "-"}</td>
            <td>${m.hora_saida || "-"}</td>

            <td>
                <span class="badge ${badge}">
                    ${m.estado}
                </span>
            </td>

            <td>
                <button class="btn-icon btn-monitoring" onclick="abrirDetalhes(${m.professor_id ?? 'null'})">
                    <i class="fas fa-info-circle"></i>
                </button>
            </td>

        </tr>
    `;
}).join("");

}

//========================== ABRIR O MODAL DE DETALHES MONITORIZAÇÃO =========
async function abrirDetalhes(professor_id) {

    console.log("Valor:", professor_id);
    console.log("Tipo:", typeof professor_id);

    if (!professor_id) {
        console.trace("ID inválido");
        return;
    }

    try {

        const url = `phpadmin/carregar_detalhe_professor.php?professor_id=${encodeURIComponent(professor_id)}`;

        const res = await fetch(url);

        // DEBUG
        const text = await res.text();
        console.log("RESPOSTA BRUTA:", text);

        let data;

        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error("Resposta não é um JSON válido.");
            console.error(text);
            return;
        }

        if (!data.success) {
            console.error("Erro backend:", data.message);
            return;
        }

        const p = data.professor;
        const a = data.assiduidade || {};

        //=========================
        // FOTO
        //=========================
        const photo = document.getElementById("teacherPhoto");

        if (photo) {
            photo.src = p.foto || "img/default-user.png";
        }

        //=========================
        // FUNÇÃO AUXILIAR
        //=========================
        function setText(id, valor) {
            const el = document.getElementById(id);
            if (el) {
                el.textContent = valor ?? "-";
            }
        }

        //=========================
        // IDENTIFICAÇÃO
        //=========================
        setText("teacherName", p.nome);
        setText("teacherType", "Professor");

        //=========================
        // DADOS PESSOAIS
        //=========================
        setText("teacherFullName", p.nome);
        setText("teacherEmail", p.email);
        setText("teacherPhone", p.telefone);

        //=========================
        // PROFISSIONAL
        //=========================
        setText("teacherNumber", p.numero_mecanografico);
        setText("teacherSubject", p.disciplina);

        setText(
            "teacherBaseSalary",
            Number(p.salario_base || 0).toLocaleString("pt-AO") + " Kz"
        );

        //=========================
        // ASSIDUIDADE
        //=========================
        setText("teacherPresences", a.presentes);
        setText("teacherDelays", a.atrasos);
        setText("teacherAbsences", a.faltas);

        setText(
            "teacherTotalDiscounts",
            Number(a.total_descontos || 0).toLocaleString("pt-AO") + " Kz"
        );

        //=========================
        // SALÁRIO
        //=========================
        setText(
            "teacherSalaryBase",
            Number(p.salario_base || 0).toLocaleString("pt-AO") + " Kz"
        );

        setText(
            "teacherDiscounts",
            Number(a.total_descontos || 0).toLocaleString("pt-AO") + " Kz"
        );

        setText(
            "teacherFinalSalary",
            Number(a.salario_final || 0).toLocaleString("pt-AO") + " Kz"
        );

        //=========================
        // ABRIR MODAL
        //=========================
        const modal = document.getElementById("teacherReportModal");

        if (!modal) {
            console.error("Modal teacherReportModal não encontrado.");
            return;
        }

        modal.classList.add("active");

    } catch (err) {
        console.error("Erro ao abrir detalhes:", err);
    }
}
//=============== FILTRAGEM DA TABELA =======================================

function filterMonitoring() {
    const search = document.getElementById("monitoringSearch").value.toLowerCase();
    const status = document.getElementById("monitoringStatus").value;

    const filtered = monitoringData.filter(m => {

        const matchSearch =
            m.nome.toLowerCase().includes(search) ||
            m.mecanografico.includes(search);

        const matchStatus = status ? m.estado === status : true;

        return matchSearch && matchStatus;
    });

    const tbody = document.getElementById('monitoringList');

    tbody.innerHTML = filtered.map(m => `
        <tr>
            <td>${m.nome}</td>
            <td>${m.mecanografico}</td>
            <td>${m.disciplina}</td>
            <td>${m.data}</td>
            <td>${m.hora_entrada || '-'}</td>
            <td>${m.estado}</td>
        </tr>
    `).join('');
}

//======================== FECHAR O MODAL =========
document.addEventListener("DOMContentLoaded", () => {

    const modal = document.getElementById("teacherReportModal");
    const closeBtn = document.getElementById("closeTeacherReportModal");

    if (closeBtn && modal) {
        closeBtn.addEventListener("click", () => {
            modal.classList.remove("active");
        });
    }

});
//===================== FECHAR AO CLICAR FORA ====================
document.addEventListener("DOMContentLoaded", () => {

    const modal = document.getElementById("teacherReportModal");

    if (!modal) return;

    modal.addEventListener("click", (e) => {
        if (e.target === modal) {
            modal.classList.remove("active");
        }
    });

});
//============================================ FIM DE MONITORIZAÇÃO ======================

// ========== MODALS ==========
function openNotificationModal() {
    const modal = document.getElementById('notificationModal');
    if (modal) {
        modal.classList.add('active');
    }
}

function openLogoutModal() {
    const modal = document.getElementById('logoutModal');
    if (modal) {
        modal.classList.add('active');
        
        /*if (confirmBtn) {
            
        }*/
    }
}

function openPasswordModal() {
    const modal = document.getElementById('passwordModal');
    if (modal) {
        modal.classList.add('active');
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
    }
}

// ========== SIDEBAR ==========
function toggleSidebar() {
    appState.sidebarOpen = !appState.sidebarOpen;
    document.getElementById('sidebar').classList.toggle('active', appState.sidebarOpen);
}

function closeSidebar() {
    appState.sidebarOpen = false;
    document.getElementById('sidebar').classList.remove('active');
}

// ========== UTILITY FUNCTIONS ==========
function updateTime() {
    const timeElement = document.getElementById('currentTime');
    if (timeElement) {
        const now = new Date();
        timeElement.textContent = now.toLocaleTimeString('pt-PT', { hour: '2-digit', minute: '2-digit' });
    }
    setTimeout(updateTime, 60000);
}

function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    
    const icons = {
        'success': 'fa-check-circle',
        'error': 'fa-exclamation-circle',
        'warning': 'fa-exclamation-triangle'
    };

    toast.innerHTML = `
        <i class="fas ${icons[type] || 'fa-info-circle'}"></i>
        <span>${message}</span>
    `;

    container.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

function exportData() {
    showToast('Exportando dados...', 'success');
}

async function logout() {

    try {

        const res = await fetch("phpconexao/logout.php");
        const data = await res.json();

        if (data.success) {

            showToast('Desconectado com sucesso!', 'success');

            setTimeout(() => {

                // limpa tudo
                localStorage.clear();
                sessionStorage.clear();

                // redireciona
                window.location.href = "login.html";

            }, 1000);
        }

    } catch (error) {
        console.error(error);
        alert("Erro ao terminar sessão");
    }
}

// Modal closes on click outside
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal')) {
        e.target.classList.remove('active');
    }
});

// Responsive sidebar
window.addEventListener('resize', () => {
    if (window.innerWidth > 768) {
        document.getElementById('sidebar').classList.remove('active');
    }
});

//============================= MODAL DISCIPLINAS =================================

const disciplineModal = document.getElementById("disciplineModal");

const openDisciplineModalBtn = document.getElementById("addDisciplineBtn");
const closeDisciplineModalBtn = document.getElementById("closeDisciplineModal");
const cancelDisciplineBtn = document.getElementById("cancelDisciplineBtn");

const disciplineForm = document.getElementById("disciplineForm");

const disciplineNameInput = document.getElementById("disciplineName");
const disciplineDescriptionInput = document.getElementById("disciplineDescription");
const disciplineStatusSelect = document.getElementById("disciplineStatus");

const disciplineModalTitle = document.getElementById("modalTitle");
const saveDisciplineBtn = document.getElementById("saveDisciplineBtn");

const descriptionCounter = document.getElementById("descriptionCounter");

/* ==========================================================
   VARIÁVEIS DE CONTROLO
========================================================== */

let disciplineEditMode = false;
let editingDisciplineId = null;

/* ==========================================================
   ABRIR MODAL
========================================================== */

openDisciplineModalBtn.addEventListener("click", () => {
    openDisciplineModal("create");
});

/* ==========================================================
   FUNÇÃO ABRIR MODAL
========================================================== */

function openDisciplineModal(mode = "create", data = null) {

    disciplineModal.style.display = "flex";

    // Modo Cadastro
    if (mode === "create") {

        disciplineEditMode = false;
        editingDisciplineId = null;

        disciplineModalTitle.textContent = "Cadastrar Disciplina";

        disciplineForm.reset();

        disciplineStatusSelect.value = "Ativa";

        updateDescriptionCounter();
    }

    // Modo Edição
    if (mode === "edit" && data) {

        disciplineEditMode = true;
        editingDisciplineId = data.id;

        disciplineModalTitle.textContent = "Editar Disciplina";

        disciplineNameInput.value = data.nome;
        disciplineDescriptionInput.value = data.descricao;
        disciplineStatusSelect.value = data.estado;

        updateDescriptionCounter();
    }

    // Dar foco ao primeiro campo
    setTimeout(() => {
        disciplineNameInput.focus();
    }, 150);
}

/* ==========================================================
   FECHAR MODAL
========================================================== */

function closeDisciplineModal() {

    disciplineModal.style.display = "none";

    disciplineForm.reset();

    disciplineEditMode = false;
    editingDisciplineId = null;

    updateDescriptionCounter();

    // Restaurar botão guardar
    saveDisciplineBtn.disabled = false;

    saveDisciplineBtn.innerHTML = `
        <i class="fas fa-save"></i>
        Guardar Disciplina
    `;
}

/* ==========================================================
   EVENTOS DE FECHO
========================================================== */

// Botão X
closeDisciplineModalBtn.addEventListener(
    "click",
    closeDisciplineModal
);

// Botão Cancelar
cancelDisciplineBtn.addEventListener(
    "click",
    closeDisciplineModal
);

// Clique fora do modal
window.addEventListener("click", (e) => {

    if (e.target === disciplineModal) {
        closeDisciplineModal();
    }

});

// Tecla ESC
document.addEventListener("keydown", (e) => {

    if (
        e.key === "Escape" &&
        disciplineModal.style.display === "flex"
    ) {
        closeDisciplineModal();
    }

});

/* ==========================================================
   CONTADOR DE CARACTERES
========================================================== */

disciplineDescriptionInput.addEventListener(
    "input",
    updateDescriptionCounter
);

function updateDescriptionCounter() {

    const total = disciplineDescriptionInput.value.length;

    descriptionCounter.textContent =
        `${total}/500 caracteres`;
}

/* ==========================================================
   SUBMISSÃO DO FORMULÁRIO
========================================================== */

disciplineForm.addEventListener("submit", (e) => {

    e.preventDefault();

    const nome =
        disciplineNameInput.value.trim();

    const descricao =
        disciplineDescriptionInput.value.trim();

    const estado =
        disciplineStatusSelect.value;

    /* ==========================
       VALIDAÇÕES
    ========================== */

    if (nome === "") {

        showDisciplineToast(
            "Informe o nome da disciplina.",
            "error"
        );

        disciplineNameInput.focus();

        return;
    }

    if (nome.length < 2) {

        showDisciplineToast(
            "O nome deve possuir pelo menos 2 caracteres.",
            "error"
        );

        disciplineNameInput.focus();

        return;
    }

    // Simulação de disciplina duplicada
    if (
        !disciplineEditMode &&
        nome.toLowerCase() === "matemática"
    ) {

        showDisciplineToast(
            "Esta disciplina já está cadastrada.",
            "error"
        );

        return;
    }

    /* ==========================
       LOADING BUTTON
    ========================== */

    saveDisciplineBtn.disabled = true;

    saveDisciplineBtn.innerHTML = `
        <i class="fas fa-spinner fa-spin"></i>
        A guardar...
    `;

    /* ==========================
       SIMULAÇÃO DE PROCESSAMENTO
    ========================== */

    setTimeout(() => {

        if (disciplineEditMode) {

            showDisciplineToast(
                "Disciplina atualizada com sucesso.",
                "success"
            );

        } else {

            showDisciplineToast(
                "Disciplina cadastrada com sucesso.",
                "success"
            );
        }

        closeDisciplineModal();

    }, 1200);

});

/* ==========================================================
   TOAST NOTIFICATIONS
========================================================== */

function showDisciplineToast(
    message,
    type = "success"
) {

    const toast = document.createElement("div");

    toast.className = `toast ${type}`;

    toast.innerHTML = `
        <i class="fas ${
            type === "success"
                ? "fa-check-circle"
                : "fa-exclamation-circle"
        }"></i>

        <span>${message}</span>
    `;

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.classList.add("show");
    }, 100);

    setTimeout(() => {

        toast.classList.remove("show");

        setTimeout(() => {
            toast.remove();
        }, 300);

    }, 3000);
}

// =====================================================================
// FIM DO MODAL DE DISCIPLINAS
// =====================================================================

/* ========================= MODAL USUARIOS========================= */

const userModal = document.getElementById("userModal");
const userType = document.getElementById("userType");
const professorFields = document.getElementById("professorFields");

const addTeacherBtn = document.getElementById("addTeacherBtn");
const closeUserModal = document.getElementById("closeUserModal");
const cancelUserBtn = document.getElementById("cancelUserBtn");

/* =========================
   ABRIR MODAL
========================= */
addTeacherBtn.addEventListener("click", () => {
    userModal.style.display = "flex";
});

/* =========================
   FECHAR MODAL
========================= */
function closeModal() {
    userModal.style.display = "none";
}

closeUserModal.addEventListener("click", closeModal);
cancelUserBtn.addEventListener("click", closeModal);

window.addEventListener("click", (e) => {
    if (e.target === userModal) closeModal();
});

/* =========================
   ALTERAR CAMPOS DINÂMICOS
========================= */
userType.addEventListener("change", () => {

    if (userType.value === "admin") {
        professorFields.style.display = "none";
    } else {
        professorFields.style.display = "block";
    }

});

//================================== MODAL USUARIOS ====================


//================================== MODAL ESCALAS =====================
// ================= MODAL ESCALAS =================

const scheduleModal = document.getElementById("scaleModal"); 
// (mantém o id do modal HTML que criámos: scaleModal)

const addScheduleBtn = document.getElementById("addScheduleBtn");
const closeScheduleModal = document.getElementById("closeScaleModal");
const cancelScheduleBtn = document.getElementById("cancelScaleBtn");

const scheduleTeacher = document.getElementById("scaleTeacher");
const scheduleCode = document.getElementById("scaleCode");
const scheduleDiscipline = document.getElementById("scaleDiscipline");

// ================= ABRIR MODAL =================
if (addScheduleBtn) {
    addScheduleBtn.addEventListener("click", () => {
        scheduleModal.style.display = "flex";
        document.getElementById("scaleForm").reset();
        scheduleCode.value = "";
        scheduleDiscipline.value = "";
    });
}

// ================= FECHAR MODAL =================
function closeSchedule() {
    scheduleModal.style.display = "none";
}

if (closeScheduleModal) {
    closeScheduleModal.addEventListener("click", closeSchedule);
}

if (cancelScheduleBtn) {
    cancelScheduleBtn.addEventListener("click", closeSchedule);
}

// fechar ao clicar fora
window.addEventListener("click", (e) => {
    if (e.target === scheduleModal) {
        closeSchedule();
    }
});

// fechar com ESC
document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
        closeSchedule();
    }
});

// ================= SELECT ALL DIAS =================
const selectAllDays = document.getElementById("selectAllDays");
const dayCheckboxes = document.querySelectorAll(".day");

if (selectAllDays) {
    selectAllDays.addEventListener("change", () => {
        dayCheckboxes.forEach(day => {
            day.checked = selectAllDays.checked;
        });
    });
}

// sincronização inversa
dayCheckboxes.forEach(day => {
    day.addEventListener("change", () => {
        const allChecked = [...dayCheckboxes].every(d => d.checked);
        if (selectAllDays) {
            selectAllDays.checked = allChecked;
        }
    });
});

// ================= AUTO FILL PROFESSOR =================
// (simulação — depois liga ao backend)
scheduleTeacher.addEventListener("change", () => {

    const fakeData = {
        "1": { code: "PROF-001", discipline: "Matemática" },
        "2": { code: "PROF-002", discipline: "História" },
        "3": { code: "PROF-003", discipline: "Física" }
    };

    const selected = scheduleTeacher.value;

    if (fakeData[selected]) {
        scheduleCode.value = fakeData[selected].code;
        scheduleDiscipline.value = fakeData[selected].discipline;
    } else {
        scheduleCode.value = "";
        scheduleDiscipline.value = "";
    }
});

//======================================== MODAL ESCALAS ======================

//======================================== FECHAR GERAL MODAL ==================
document.addEventListener('click', function (e) {

    // Fechar botão X
    if (e.target.closest('.modal-close')) {
        const modal = e.target.closest('.modal');
        if (modal) modal.classList.remove('active');
    }

    // Botões com data-modal (Cancelar)
    if (e.target.dataset.modal) {
        const modal = document.getElementById(e.target.dataset.modal);
        if (modal) modal.classList.remove('active');
    }

});


document.addEventListener('click', function (e) {
    const modal = document.querySelector('#passwordModal.active');

    if (modal && e.target === modal) {
        modal.classList.remove('active');
    }
});

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal.active')
            .forEach(m => m.classList.remove('active'));
    }
});

//=============================== validação dos campos dos usuarios ===========
/* ===============================
   ELEMENTOS
================================ */

const userName = document.getElementById("userName");
const userEmail = document.getElementById("userEmail");
const userPhone = document.getElementById("userPhone");
const userPassword = document.getElementById("userPassword");
const userSalary = document.getElementById("userSalary");
const userDiscipline = document.getElementById("userDiscipline")

const saveUserBtn = document.getElementById("saveUserBtn");

/* ===============================
   ESTADOS
================================ */

let validName = false;
let validEmail = false;
let validPhone = true;
let validPassword = false;
let validSalary = true;
let validDiscipline = true;

/* ===============================
   ATUALIZAR BOTÃO
================================ */

function updateSaveButton() {

    if (userType.value === "professor") {

        saveUserBtn.disabled = !(
            validName &&
            validEmail &&
            validPhone &&
            validPassword &&
            validSalary &&
            validDiscipline
        );

    } else {

        saveUserBtn.disabled = !(
            validName &&
            validEmail &&
            validPhone &&
            validPassword
        );
    }
}

/* ===============================
   NOME
================================ */

const nameFeedback = document.createElement("small");
userName.parentNode.appendChild(nameFeedback);

userName.addEventListener("input", () => {

    const value = userName.value.trim();

    if (value.length < 3) {
        validName = false;
        nameFeedback.textContent = "Nome deve ter pelo menos 3 caracteres";
        nameFeedback.style.color = "red";
        userName.classList.add("input-error");
    } else {
        validName = true;
        nameFeedback.textContent = "";
        userName.classList.remove("input-error");
        userName.classList.add("input-success");
    }

    updateSaveButton();
});

//========================== Validação do email + verificação na bd ======

userEmail.addEventListener("blur", async () => {

    const feedback = document.getElementById("emailFeedback");
    const email = userEmail.value.trim();

    // vazio não valida
    if (email === "") {
        feedback.textContent = "";
        validEmail = false;
        updateSaveButton();
        return;
    }

    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!regex.test(email)) {

        feedback.textContent = "Email inválido.";
        feedback.className = "validation-message validation-error";

        validEmail = false;
        updateSaveButton();
        return;
    }

    try {

        const formData = new FormData();
        formData.append("email", email);

        const resposta = await fetch("phpvalidar/validar_email.php", {
            method: "POST",
            body: formData
        });

        const dados = await resposta.json();

        if (dados.existe) {

            feedback.textContent = dados.mensagem;
            feedback.className = "validation-message validation-error";

            validEmail = false;

        } else {

            feedback.textContent = "Email disponível.";
            feedback.className = "validation-message validation-success";

            validEmail = true;
        }

    } catch (error) {

        console.error("Erro no backend:", error);

        feedback.textContent = "Erro ao validar email.";
        feedback.className = "validation-message validation-error";

        validEmail = false;
    }

    updateSaveButton();
});

//======================================= Validação do telefone + verificação na bd ========]
userPhone.addEventListener("input", () => {

    const feedback = document.getElementById("phoneFeedback");

    // Remove tudo que não for número
    userPhone.value = userPhone.value.replace(/\D/g, '');

    // Garante no máximo 9 dígitos
    userPhone.value = userPhone.value.slice(0, 9);

    const telefone = userPhone.value;

    // Campo vazio é permitido
    if (telefone === "") {
        feedback.textContent = "";
        validPhone = true;
        updateSaveButton();
        return;
    }

    // Verifica se começa com 9
    if (!telefone.startsWith("9")) {

        feedback.textContent =
            "O telefone deve começar com 9.";

        feedback.className =
            "validation-message validation-error";

        validPhone = false;

        updateSaveButton();
        return;
    }

    // Verifica se possui exatamente 9 dígitos
    if (telefone.length < 9) {

        feedback.textContent =
            "O telefone deve possuir 9 dígitos.";

        feedback.className =
            "validation-message validation-error";

        validPhone = false;

    } else {

        feedback.textContent =
            "Telefone válido.";

        feedback.className =
            "validation-message validation-success";

        validPhone = true;
    }

    updateSaveButton();

});

//================================= Senha ===========================

const userBirth = document.getElementById("userBirth");
const birthFeedback = document.createElement("small");
userBirth.parentNode.appendChild(birthFeedback);

userBirth.addEventListener("change", () => {

    const value = new Date(userBirth.value);

    if (!userBirth.value) {
        validBirth = false;
        birthFeedback.textContent = "";
        updateSaveButton();
        return;
    }

    const today = new Date();
    let age = today.getFullYear() - value.getFullYear();

    const m = today.getMonth() - value.getMonth();

    if (m < 0 || (m === 0 && today.getDate() < value.getDate())) {
        age--;
    }

    if (age < 18) {
        validBirth = false;
        birthFeedback.textContent = "Deve ter pelo menos 18 anos";
        birthFeedback.style.color = "red";
    } else {
        validBirth = true;
        birthFeedback.textContent = "";
    }

    updateSaveButton();
});

//================================= validação de senha ==============

userPassword.addEventListener("input", () => {

    const feedback =
        document.getElementById("passwordFeedback");

    // Limita a 4 caracteres
    userPassword.value =
        userPassword.value.slice(0, 4);

    const senha = userPassword.value;

    if (senha.length < 4) {

        feedback.textContent =
            "A senha deve possuir 4 caracteres.";

        feedback.className =
            "validation-message validation-error";

        validPassword = false;

    } else {

        feedback.textContent =
            "Senha válida.";

        feedback.className =
            "validation-message validation-success";

        validPassword = true;
    }

    updateSaveButton();

});
//================================= validação do salario =============

const salaryFeedback = document.createElement("small");
userSalary.parentNode.appendChild(salaryFeedback);

userSalary.addEventListener("input", () => {

    let value = userSalary.value.replace(/\D/g, "");

    userSalary.value = value;

    if (value.length === 0) {
        validSalary = false;
        salaryFeedback.textContent = "";
        updateSaveButton();
        return;
    }

    if (parseInt(value) < 100) {
        validSalary = false;
        salaryFeedback.textContent = "Salário mínimo 100";
        salaryFeedback.style.color = "red";
    } else {
        validSalary = true;
        salaryFeedback.textContent = "";
    }

    updateSaveButton();
});

//================================================== Validação  do botao guardar =========
function updateSaveButton() {

    const btn = document.getElementById("saveUserBtn");

    if (validName && validEmail && validBirth && validSalary) {
        btn.disabled = false;
    } else {
        btn.disabled = true;
    }
}

//============================ CADASTRAR USUARIO =================

const userForm = document.getElementById("userForm");

userForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    // =============================
    // PEGAR DADOS DO FORMULÁRIO
    // =============================
    const nome = document.getElementById("userName").value.trim();
    const email = document.getElementById("userEmail").value.trim();
    const senha = document.getElementById("userPassword").value.trim();
    const tipo = userType.value;

    const telefone = document.getElementById("userPhone").value.trim();
    const genero = document.getElementById("userGender").value;
    const data_nascimento = document.getElementById("userBirth").value;

    const disciplina_id = document.getElementById("userDiscipline").value;
    const salario_base = document.getElementById("userSalary").value;

    // =============================
    // VALIDACAO FINAL BÁSICA
    // =============================
    if (!nome || !email || !senha) {
        alert("Preencha todos os campos obrigatórios.");
        return;
    }

    if (tipo === "professor" && !disciplina_id) {
        alert("Selecione a disciplina do professor.");
        return;
    }

    // =============================
    // LOADING BUTTON
    // =============================
    saveUserBtn.disabled = true;
    saveUserBtn.innerHTML = `<i class="fas fa-spinner fa-spin"></i> A guardar...`;

    try {

        // =============================
        // MONTAR FORM DATA
        // =============================
        const formData = new FormData();

        formData.append("nome", nome);
        formData.append("email", email);
        formData.append("senha", senha);
        formData.append("tipo", tipo);
        formData.append("telefone", telefone);
        formData.append("genero", genero);
        formData.append("data_nascimento", data_nascimento);

        if (tipo === "professor") {
            formData.append("disciplina_id", disciplina_id);
            formData.append("salario_base", salario_base);
        }

        // =============================
        // ENVIAR PARA BACKEND
        // =============================
        const response = await fetch("phpadmin/cadastrar_usuario.php", {
            method: "POST",
            body: formData
        });

        const data = await response.json();

        // =============================
        // TRATAR RESPOSTA
        // =============================
        if (data.success) {

            alert(data.message);

            // limpar form
            userForm.reset();

            // reset UI professor
            document.getElementById("professorFields").style.display = "block";

            // fechar modal
            userModal.classList.remove("active");

        } else {
            alert(data.message || "Erro ao cadastrar usuário.");
        }

    } catch (error) {
        console.error(error);
        alert("Erro de conexão com o servidor.");
    }

    // =============================
    // RESTAURAR BOTÃO
    // =============================
    saveUserBtn.disabled = false;
    saveUserBtn.innerHTML = `<i class="fas fa-save"></i> Guardar Usuário`;
});


//============================== CARREGAR DISCIPLINA NO SELECT ========

async function carregarDisciplinasSelect() {

    const select = document.getElementById("userDiscipline");

    if (!select) {
        console.error("select não existe no DOM");
        return;
    }

    try {

        select.innerHTML = `<option value="">A carregar...</option>`;

        const response = await fetch("phpadmin/listar_disciplinas_select.php");
        const data = await response.json();

        console.log("disciplinas:", data);

        if (!data.success) {
            select.innerHTML = `<option value="">Erro ao carregar disciplinas</option>`;
            return;
        }

        // IMPORTANTE: limpar corretamente
        select.innerHTML = `<option value="">Selecionar disciplina</option>`;

        data.dados.forEach(d => {
            const option = document.createElement("option");
            option.value = d.id;
            option.textContent = d.nome;
            select.appendChild(option);
        });

    } catch (error) {
        console.error("ERRO FETCH:", error);
        select.innerHTML = `<option value="">Erro de conexão</option>`;
    }
}
//================================ FIM CADASTRAR USUARIO =============================

//================================ CADASTRAR DISCIPLINA ==========================

// Campos
const nameInput = document.getElementById("disciplineName");
const descInput = document.getElementById("disciplineDescription");
const statusSelect = document.getElementById("disciplineStatus");

const counter = document.getElementById("descriptionCounter");

function updateCounter() {

    const totalCaracteres =
        descInput.value.length;

    counter.textContent =
        `${totalCaracteres}/500 caracteres`;
}

descInput.addEventListener(
    "input",
    updateCounter
);

// Botão
const saveBtn = document.getElementById("saveDisciplineBtn");

// Evento de submissão
disciplineForm.addEventListener("submit", async (e) => {

    e.preventDefault();

    // =========================
    // OBTER DADOS
    // =========================
    const nome = nameInput.value.trim();
    const descricao = descInput.value.trim();
    const estado = statusSelect.value;

    // =========================
    // VALIDAÇÕES
    // =========================
    if (nome === "") {
        showToast("Informe o nome da disciplina.", "error");
        return;
    }

    if (nome.length < 2) {
        showToast(
            "O nome deve possuir pelo menos 2 caracteres.",
            "error"
        );
        return;
    }

    // =========================
    // LOADING BOTÃO
    // =========================
    saveBtn.disabled = true;

    saveBtn.innerHTML =
        `<i class="fas fa-spinner fa-spin"></i> A guardar...`;

    try {

        // =========================
        // FORM DATA
        // =========================
        const formData = new FormData();

        formData.append("nome", nome);
        formData.append("descricao", descricao);
        formData.append("estado", estado);

        // =========================
        // ENVIAR PARA O BACKEND
        // =========================
        const response = await fetch(
            "phpadmin/cadastrar_disciplina.php",
            {
                method: "POST",
                body: formData
            }
        );

        const data = await response.json();

        // =========================
        // TRATAR RESPOSTA
        // =========================
        if (data.success) {

            showToast(data.message, "success");

            disciplineForm.reset();

            carregarDisciplinas();

            updateCounter();

            closeModal();

            

        } else {

            showToast(data.message, "error");
        }

    } catch (error) {

        console.error(error);

        showToast(
            "Erro de conexão com o servidor.",
            "error"
        );

    } finally {

        // =========================
        // RESTAURAR BOTÃO
        // =========================
        saveBtn.disabled = false;

        saveBtn.innerHTML =
            `<i class="fas fa-save"></i> Guardar Disciplina`;
    }

});

//================================ FIM CADASTRAR DISCIPLINA ==========================

//================================ LISTAR DISCIPLINA NA TABELA =======================
// =========================
// LISTAR DISCIPLINAS
// =========================

async function carregarDisciplinas() {

    try {

        const response = await fetch(
            "phpadmin/listar_disciplina.php"
        );

        const data = await response.json();

        const tbody =
            document.getElementById("disciplinesList");

        tbody.innerHTML = "";

        if (!data.success) {

            showToast(
                "Erro ao carregar disciplinas.",
                "error"
            );

            return;
        }

        if (data.dados.length === 0) {

            tbody.innerHTML = `
                <tr>
                    <td colspan="5" style="text-align:center;">
                        Nenhuma disciplina cadastrada.
                    </td>
                </tr>
            `;

            return;
        }

        data.dados.forEach(disciplina => {

            const descricao =
                disciplina.descricao
                ? disciplina.descricao
                : "-";

            const dataCriacao =
                new Date(disciplina.criado_em)
                .toLocaleDateString("pt-PT");

            tbody.innerHTML += `
                <tr>

                    <td>${disciplina.nome}</td>

                    <td>${descricao}</td>

                    <td>${dataCriacao}</td>

                    <td>
                        <span class="status-badge ${disciplina.estado === 'Ativa'
                            ? 'status-active'
                            : 'status-inactive'}">

                            ${disciplina.estado}
                        </span>
                    </td>

                    <td>

                        <button class="action-btn edit-btn"
                            data-id="${disciplina.id}"
                            title="Editar">

                            <i class="fas fa-edit"></i>
                        </button>

                        <button class="action-btn delete-btn"
                            data-id="${disciplina.id}"
                            title="Eliminar">

                            <i class="fas fa-trash"></i>
                        </button>

                    </td>

                </tr>
            `;
        });

    } catch (error) {

        console.error(error);

        showToast(
            "Erro de conexão com o servidor.",
            "error"
        );
    }
}

//============================ FIM DO LISTAR DISCIPLINA ===================


//============================ CADASTRAR ESCALA ==========================

const scaleForm = document.getElementById("scaleForm");

scaleForm.addEventListener("submit", async (e) => {

    e.preventDefault();

    //=========================
    // PEGAR DADOS
    //=========================

    const professor_id = document.getElementById("scaleTeacher").value;

    const disciplina_id =
        document.getElementById("scaleDisciplineId").value;

    const hora_entrada =
        document.getElementById("scaleStart").value;

    const hora_saida =
        document.getElementById("scaleEnd").value;

    const tolerancia_min =
        document.getElementById("scaleTolerance").value;

    const periodoSelecionado =
        document.querySelector('input[name="period"]:checked');

    const periodo = periodoSelecionado
        ? periodoSelecionado.parentElement.textContent.trim()
        : "";

    //=========================
    // DIAS
    //=========================

    const dias = [];

    document
        .querySelectorAll(".days-grid .day:checked")
        .forEach(item => {

            if (!item.disabled) {

                dias.push(
                    item.parentElement.textContent.trim()
                );

            }

        });

    //=========================
    // VALIDAÇÕES
    //=========================

    if (!professor_id) {

        alert("Selecione o professor.");
        return;

    }

    if (!disciplina_id) {

        alert("Disciplina inválida.");
        return;

    }

    if (dias.length === 0) {

        alert("Selecione pelo menos um dia.");
        return;

    }

    if (!hora_entrada || !hora_saida) {

        alert("Preencha os horários.");
        return;

    }

    if (!periodo) {

        alert("Selecione o período.");
        return;

    }

    if (hora_entrada >= hora_saida) {

        alert("Hora de entrada deve ser inferior à saída.");
        return;

    }

    //=========================
    // BOTÃO
    //=========================

    const saveBtn = document.getElementById("saveScaleBtn");

    saveBtn.disabled = true;

    saveBtn.innerHTML =
        `<i class="fas fa-spinner fa-spin"></i> A guardar...`;

    try {

        const formData = new FormData();

        formData.append("professor_id", professor_id);
        formData.append("disciplina_id", disciplina_id);
        formData.append("hora_entrada", hora_entrada);
        formData.append("hora_saida", hora_saida);
        formData.append("tolerancia_min", tolerancia_min);
        formData.append("periodo", periodo);

        dias.forEach(dia => {

            formData.append("dias_semana[]", dia);

        });

        console.log("Dados enviados:");

        for (let pair of formData.entries()) {

            console.log(pair[0], pair[1]);

        }

        const response = await fetch(
            "phpadmin/cadastrar_escala.php",
            {
                method: "POST",
                body: formData
            }
        );

        const data = await response.json();

        console.log(data);

        if (data.success) {

            alert(data.message);

            scaleForm.reset();

            document
                .getElementById("scaleCode")
                .value = "";

            document
                .getElementById("scaleDiscipline")
                .value = "";

            document
                .getElementById("scaleDisciplineId")
                .value = "";

            document
                .getElementById("scaleModal")
                .classList.remove("active");

        } else {

            alert(data.message);

        }

    } catch (error) {

        console.error(error);

        alert("Erro de conexão com o servidor.");

    }

    saveBtn.disabled = false;

    saveBtn.innerHTML =
        `<i class="fas fa-save"></i> Guardar Escala`;

});

//==============================================================
// LISTAR PROFESSORES
//==============================================================

async function carregarProfessoresEscala() {

    const select =
        document.getElementById("scaleTeacher");

    if (!select) return;

    try {

        const response =
            await fetch("phpadmin/listar_professores_select.php");

        const data = await response.json();

        if (!data.success) return;

        select.innerHTML =
            `<option value="">Selecionar professor</option>`;

        data.dados.forEach(p => {

            const option =
                document.createElement("option");

            option.value = p.professor_id;

            option.textContent = p.nome;

            option.dataset.mecanografico =
                p.numero_mecanografico;

            option.dataset.disciplinaId =
                p.disciplina_id;

            option.dataset.disciplinaNome =
                p.disciplina;

            select.appendChild(option);

        });

    } catch (error) {

        console.error(error);

    }

}

//==============================================================
// AO ESCOLHER PROFESSOR
//==============================================================

document
.getElementById("scaleTeacher")
.addEventListener("change", function () {

    const option =
        this.options[this.selectedIndex];

    if (!option.value) {

        document.getElementById("scaleCode").value = "";

        document.getElementById("scaleDiscipline").value = "";

        document.getElementById("scaleDisciplineId").value = "";

        return;

    }

    document.getElementById("scaleCode").value =
        option.dataset.mecanografico;

    document.getElementById("scaleDiscipline").value =
        option.dataset.disciplinaNome;

    document.getElementById("scaleDisciplineId").value =
        option.dataset.disciplinaId;

});

//===================== CARREGAR IMAGEM NO HEADER =====
async function carregarPerfilAdmin() {

    try {

        const res = await fetch("phpadmin/perfiladmin.php");
        const data = await res.json();

        if (!data.success) return;

        const usuario = data.data;

        document.getElementById("adminName").textContent = usuario.nome;
        document.getElementById("adminRole").textContent = usuario.tipo;

        document.getElementById("adminAvatar").src =
            usuario.foto && usuario.foto.trim() !== ""
                ? usuario.foto
                : "img/default-user.png";

    } catch (erro) {

        console.error("Erro ao carregar perfil:", erro);

    }

}

document.addEventListener("DOMContentLoaded", carregarPerfilAdmin);

//==================== DASHBOARD ======================
async function carregarDashboard() {

    try {

        const res = await fetch("phpadmin/dashboard.php");
        const data = await res.json();

        if (!data.success) return;

        const d = data.dashboard;

        document.getElementById("totalProfessores").textContent = d.professores;
        document.getElementById("professoresMes").textContent = `+${d.novos_professores} este mês`;

        document.getElementById("totalPresentes").textContent = d.presentes;
        document.getElementById("taxaPresenca").textContent = `${d.taxa_presenca}% taxa`;

        document.getElementById("totalFaltas").textContent = d.faltas;
        document.getElementById("totalAtrasos").textContent = d.atrasos;
        document.getElementById("totalDisciplinas").textContent = d.disciplinas;
        document.getElementById("totalEscalas").textContent = d.escalas;

    } catch (e) {

        console.error("Erro ao carregar dashboard:", e);

    }

}

document.addEventListener("DOMContentLoaded", carregarDashboard);
//============================= SEGURANÇA =====
window.addEventListener("pageshow", function (event) {
    if (event.persisted) {
        window.location.reload();
    }
});