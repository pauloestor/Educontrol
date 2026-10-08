<?php
session_start();

// bloqueia acesso direto sem login
if (empty($_SESSION['user_id'])) {
    header("Location: /ControlePresenca/login.html");
    exit;
}

// segurança extra contra cache do navegador
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Controlo de Presença de Professores</title>
    <!-- Font Awesome CDN para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/professor.css">
</head>
<body>
    <!-- CONTAINER PRINCIPAL -->
    <div class="container">
        
        <!-- HEADER FIXO NO TOPO -->
        <header class="header">
            <div class="header-left">
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="breadcrumbs" id="breadcrumbs">
                    <span>Dashboard</span>
                </div>
            </div>

            <div class="header-center">
                <div class="date-time">
                    <span id="currentDate"></span>
                    <span id="currentTime"></span>
                </div>
            </div>

            <div class="header-right">
                <!-- Notificações -->
                <button class="header-icon" id="notificationsBtn" title="Notificações">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge">3</span>
                </button>

                <!-- Tema Claro/Escuro -->
                <button class="header-icon" id="themeToggle" title="Alternar Tema">
                    <i class="fas fa-sun"></i>
                </button>

                <!-- Menu do Utilizador -->
                <div class="user-menu">
                    <button class="user-menu-btn" id="userMenuBtn">
                        <img src="https://ui-avatars.com/api/?name=João+Silva&background=4f46e5&color=fff" alt="Perfil" class="user-avatar">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="user-dropdown" id="userDropdown">
                        <a href="#" onclick="app.showSection('profile')">
                            <i class="fas fa-user"></i> Meu Perfil
                        </a>
                        <a href="#" onclick="app.showSection('settings')">
                            <i class="fas fa-cog"></i> Configurações
                        </a>
                        <hr>
                        <a href="#" onclick="logout(event)">
                            <i class="fas fa-sign-out-alt"></i> Sair
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- SIDEBAR FIXA À ESQUERDA -->
        <aside class="sidebar" id="sidebar">
            <!-- Logo e Nome do Sistema -->
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h1>Controlo de Presença</h1>
            </div>

            <!-- Menu de Navegação -->
            <nav class="sidebar-menu">
                <a href="#" class="menu-item active" onclick="app.showSection('dashboard'); return false;" data-section="dashboard">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
                <a href="#" class="menu-item" onclick="app.showSection('attendance'); return false;" data-section="attendance">
                    <i class="fas fa-user-check"></i>
                    <span>Marcar Presença</span>
                </a>
                <a href="#" class="menu-item" onclick="app.showSection('history'); return false;" data-section="history">
                    <i class="fas fa-history"></i>
                    <span>Histórico</span>
                </a>
                <a href="#" class="menu-item" onclick="app.showSection('assiduity'); return false;" data-section="assiduity">
                    <i class="fas fa-chart-pie"></i>
                    <span>Minhas Assiduidade</span>
                </a>
                <a href="#" class="menu-item" onclick="app.showSection('profile'); return false;" data-section="profile">
                    <i class="fas fa-user"></i>
                    <span>Meu Perfil</span>
                </a>
                <a href="#" class="menu-item" onclick="app.showSection('settings'); return false;" data-section="settings">
                    <i class="fas fa-cog"></i>
                    <span>Configurações</span>
                </a>
                <hr class="sidebar-divider">
                <a href="#" class="menu-item logout" onclick="logout(); return false;">
    <i class="fas fa-sign-out-alt"></i>
    <span>Sair</span>
</a>
            </nav>
        </aside>

        <!-- ÁREA PRINCIPAL DE CONTEÚDO -->
        <main class="main-content">
            <!-- DASHBOARD -->
            <section id="dashboard-section" class="content-section active">
                <div class="section-header">
                    <h2>Dashboard</h2>
                    <p>Bem-vindo ao seu painel de controlo</p>
                </div>

                <!-- Cards Estatísticos -->
                <div class="stats-grid">
                    <!-- Card 1: Total de Presenças -->
                    <div class="stat-card">
                        <div class="stat-icon present">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-content">
                            <h3>Presenças</h3>
                            <p class="stat-value">22</p>
                            <p class="stat-desc">Este mês</p>
                        </div>
                    </div>

                    <!-- Card 2: Total de Atrasos -->
                    <div class="stat-card">
                        <div class="stat-icon late">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-content">
                            <h3>Atrasos</h3>
                            <p class="stat-value">2</p>
                            <p class="stat-desc">Este mês</p>
                        </div>
                    </div>

                    <!-- Card 3: Total de Faltas -->
                    <div class="stat-card">
                        <div class="stat-icon absent">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="stat-content">
                            <h3>Faltas</h3>
                            <p class="stat-value">1</p>
                            <p class="stat-desc">Este mês</p>
                        </div>
                    </div>

                    <!-- Card 4: Última Presença -->
                    <div class="stat-card last-attendance">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-content">
                            <h3>Última Presença</h3>
                            <p class="stat-value">26 Jun 2026</p>
                            <p class="stat-desc">09:15 - 17:45</p>
                        </div>
                    </div>
                </div>

                <!-- Gráficos -->
                <div class="charts-grid">
                    <!-- Gráfico de Barras -->
                    <div class="chart-container">
                        <h3>Presenças por Mês</h3>
                        <canvas id="barChart"></canvas>
                    </div>

                    <!-- Gráfico Circular -->
                    <div class="chart-container">
                        <h3>Distribuição de Assiduidade</h3>
                        <canvas id="pieChart"></canvas>
                    </div>
                </div>

                <!-- Atividades Recentes -->
                <div class="recent-activities">
                    <h3>Atividades Recentes</h3>
                    <div class="activities-table-wrapper">
                        <table class="activities-table">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Hora</th>
                                    <th>Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>26 Jun 2026</td>
                                    <td>09:15</td>
                                    <td><span class="status-badge present">Entrada</span></td>
                                </tr>
                                <tr>
                                    <td>26 Jun 2026</td>
                                    <td>17:45</td>
                                    <td><span class="status-badge">Saída</span></td>
                                </tr>
                                <tr>
                                    <td>25 Jun 2026</td>
                                    <td>09:32</td>
                                    <td><span class="status-badge late">Entrada (Atrasado)</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- MARCAR PRESENÇA -->
            <section id="attendance-section" class="content-section">
                <div class="section-header">
                    <h2>Marcar Presença</h2>
                    <p>Registar entrada ou saída</p>
                </div>

                <div class="attendance-form-container">
                    <!-- Informações do Professor -->
                    <div class="form-section">
                        <h3>Informações do Professor</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Nome do Professor</label>
                                <input type="text" id="profName" readonly>
                            </div>
                            <div class="form-group">
                                <label>Número Mecanográfico</label>
                                <input type="text" id="profCode" readonly>
                            </div>
                            <div class="form-group">
                                <label>Departamento</label>
                                <input type="text" id="profDepartment" readonly>
                            </div>
                            <div class="form-group">
                                <label>Data Atual</label>
                                <input type="text" id="attendanceDateInput" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Estado Atual -->
                    <div class="form-section">
                        <h3>Estado Atual</h3>
                        <div class="status-display">
                            <div class="status-item">
                                <span>Hora Atual:</span>
                                <strong id="currentTimeDisplay">--:--:--</strong>
                            </div>
                            <div class="status-item">
                                <span>Estado:</span>
                                <strong id="attendanceStatus">Não iniciado</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Ações -->
                    <div class="form-section">
                        <h3>Ações</h3>
                        <div class="button-group">
                            <button class="btn btn-primary" id="markEntryBtn" onclick="app.markEntry()">
                                <i class="fas fa-sign-in-alt"></i> Marcar Entrada
                            </button>
                            <button class="btn btn-secondary" id="markExitBtn" onclick="app.markExit()" disabled>
                                <i class="fas fa-sign-out-alt"></i> Marcar Saída
                            </button>
                        </div>
                    </div>

                    <!-- Registos do Dia -->
                    <div class="form-section">
                        <h3>Registos de Hoje</h3>
                        <div class="records-display">
                            <div class="record-item">
                                <span>Hora de Entrada:</span>
                                <strong id="entryTimeDisplay">--:--:--</strong>
                            </div>
                            <div class="record-item">
                                <span>Hora de Saída:</span>
                                <strong id="exitTimeDisplay">--:--:--</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- HISTÓRICO -->
            <section id="history-section" class="content-section">
                <div class="section-header">
                    <h2>Histórico</h2>
                    <p>Registos de presença anteriores</p>
                </div>

                <!-- Filtros -->
                <div class="filters-section">

    <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="historySearch" placeholder="Procurar por data...">
    </div>

    <select id="monthFilter" class="filter-select">
        <option value="">Todos os Meses</option>
        <option value="1">Janeiro</option>
        <option value="2">Fevereiro</option>
        <option value="3">Março</option>
        <option value="4">Abril</option>
        <option value="5">Maio</option>
        <option value="6">Junho</option>
        <option value="7">Julho</option>
        <option value="8">Agosto</option>
        <option value="9">Setembro</option>
        <option value="10">Outubro</option>
        <option value="11">Novembro</option>
        <option value="12">Dezembro</option>
    </select>

    <select id="yearFilter" class="filter-select">
        <option value="">Todos os Anos</option>
        <option value="2024">2024</option>
        <option value="2025">2025</option>
        <option value="2026">2026</option>
    </select>

</div>
                <!-- Tabela de Histórico -->
                <div class="table-wrapper">
    <table class="history-table">

        <thead>
            <tr>
                <th>Data</th>
                <th>Hora de Entrada</th>
                <th>Hora de Saída</th>
                <th>Estado</th>
            </tr>
        </thead>

        <tbody id="historyTableBody">
            <!-- JS injeta aqui -->
        </tbody>

    </table>
</div>

                <!-- Paginação -->
                <div class="pagination">

    <button class="pagination-btn" id="prevPageBtn">Anterior</button>

    <span class="pagination-info" id="paginationInfo">
        Página 1 de 1
    </span>

    <button class="pagination-btn" id="nextPageBtn">Próxima</button>

</div>
            </section>

            <!-- MINHAS ASSIDUIDADE -->
            <section id="assiduity-section" class="content-section">
                <div class="section-header">
                    <h2>Minhas Assiduidade</h2>
                    <p>Estatísticas detalhadas de presença</p>
                </div>

                <!-- Indicadores Gerais -->
                <div class="assiduity-indicators">
                    <div class="indicator-card">
                        <div class="indicator-icon">
                            <i class="fas fa-calendar-days"></i>
                        </div>
                        <div class="indicator-content">
                            <span>Dias Trabalhados</span>
                            <strong>22</strong>
                        </div>
                    </div>
                    <div class="indicator-card">
                        <div class="indicator-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="indicator-content">
                            <span>Presenças</span>
                            <strong>22</strong>
                        </div>
                    </div>
                    <div class="indicator-card">
                        <div class="indicator-icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="indicator-content">
                            <span>Faltas</span>
                            <strong>1</strong>
                        </div>
                    </div>
                    <div class="indicator-card">
                        <div class="indicator-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="indicator-content">
                            <span>Atrasos</span>
                            <strong>2</strong>
                        </div>
                    </div>
                    <div class="indicator-card accent">
                        <div class="indicator-icon">
                            <i class="fas fa-percent"></i>
                        </div>
                        <div class="indicator-content">
                            <span>Assiduidade Geral</span>
                            <strong>95.7%</strong>
                        </div>
                    </div>
                </div>

                <!-- Estatísticas em Percentagem -->
                <div class="statistics-section">
                    <h3>Estatísticas por Tipo</h3>
                    <div class="stats-bars">
                        <div class="stat-bar">
                            <div class="stat-bar-header">
                                <span>Presenças</span>
                                <strong>95.7%</strong>
                            </div>
                            <div class="stat-bar-fill">
                                <div class="stat-bar-progress" style="width: 95.7%;"></div>
                            </div>
                        </div>
                        <div class="stat-bar">
                            <div class="stat-bar-header">
                                <span>Atrasos</span>
                                <strong>2.7%</strong>
                            </div>
                            <div class="stat-bar-fill">
                                <div class="stat-bar-progress" style="width: 2.7%; background: #f97316;"></div>
                            </div>
                        </div>
                        <div class="stat-bar">
                            <div class="stat-bar-header">
                                <span>Faltas</span>
                                <strong>1.4%</strong>
                            </div>
                            <div class="stat-bar-fill">
                                <div class="stat-bar-progress" style="width: 1.4%; background: #ef4444;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráficos de Assiduidade -->
                <div class="charts-grid">
                    <div class="chart-container">
                        <h3>Evolução Mensal</h3>
                        <canvas id="lineChart"></canvas>
                    </div>
                    <div class="chart-container">
                        <h3>Desempenho</h3>
                        <canvas id="donutChart"></canvas>
                    </div>
                </div>

                <!-- Resumo Mensal -->
                <div class="monthly-summary">
                    <h3>Resumo Mensal</h3>
                    <div class="table-wrapper">
                        <table class="summary-table">
                            <thead>
                                <tr>
                                    <th>Mês</th>
                                    <th>Presenças</th>
                                    <th>Faltas</th>
                                    <th>Atrasos</th>
                                    <th>Percentagem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Junho 2026</td>
                                    <td>22</td>
                                    <td>1</td>
                                    <td>2</td>
                                    <td><strong>95.7%</strong></td>
                                </tr>
                                <tr>
                                    <td>Maio 2026</td>
                                    <td>20</td>
                                    <td>2</td>
                                    <td>1</td>
                                    <td><strong>90.9%</strong></td>
                                </tr>
                                <tr>
                                    <td>Abril 2026</td>
                                    <td>19</td>
                                    <td>1</td>
                                    <td>3</td>
                                    <td><strong>86.4%</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Mensagem Inteligente -->
                <div class="intelligent-message">
                    <i class="fas fa-lightbulb"></i>
                    <p id="intelligentMessage">Excelente desempenho! A sua taxa de assiduidade é de 95.7%.</p>
                </div>
            </section>

            <!-- MEU PERFIL -->
            <section id="profile-section" class="content-section">
                <div class="section-header">
                    <h2>Meu Perfil</h2>
                    <p>Informações pessoais</p>
                </div>

                <div class="profile-container">
                    <!-- Foto de Perfil -->
                    <div class="profile-photo-section">
                        <div class="profile-photo-wrapper" id="profilePhotoWrapper">
                            <img src="https://ui-avatars.com/api/?name=João+Silva&size=200&background=4f46e5&color=fff" alt="Fotografia de Perfil" id="profilePhoto">
                            <div class="photo-overlay" onclick="document.getElementById('photoInput').click()">
                                <i class="fas fa-camera"></i>
                                <span>Carregar Imagem</span>
                            </div>
                        </div>
                        <input type="file" id="photoInput" accept="image/*" style="display: none;" onchange="app.uploadProfilePhoto(event)">
                    </div>

                    <!-- Formulário de Perfil -->
                    <div class="profile-form">
                        <div class="form-grid">
                            <!-- Linha 1 -->
                            <div class="form-group">
                                <label>Nome Completo</label>
                                <input type="text" id="profileName" readonly>
                            </div>
                            <div class="form-group">
                                <label>Número Mecanográfico</label>
                                <input type="text" id="profileNumber" readonly>
                            </div>

                            <!-- Linha 2 -->
                            <div class="form-group">
                                <label>Género</label>
                                <input type="text" id="profileGender" readonly>
                            </div>
                            <div class="form-group">
                                <label>Data de Nascimento</label>
                                <input type="text" id="profileBirth" readonly>
                            </div>

                            <!-- Linha 3 -->
                            <div class="form-group full-width">
                                <label>Email <span class="editable-field">(Clique para editar)</span></label>
                                <input type="email" id="emailInput">
                            </div>

                            <!-- Linha 4 -->
                            <div class="form-group full-width">
                                <label>Telefone <span class="editable-field">(Clique para editar)</span></label>
                                <input type="tel" id="phoneInput">
                            </div>

                            <!-- Linha 5 -->
                            <div class="form-group">
                                <label>Disciplina</label>
                                <input type="text" id="profileDiscipline" readonly>
                            </div>
                            <div class="form-group">
                                <label>Cargo</label>
                                <input type="text" id="profileRole" readonly>
                            </div>

                            <!-- Linha 6 -->
                            <div class="form-group">
                                <label>Data de Admissão</label>
                                <input type="text" id="profileAdmission" readonly>
                            </div>
                        </div>

                        <!-- Botão Guardar -->
                        <div class="form-actions">
                            <button class="btn btn-primary" onclick="app.saveProfileChanges()">
                                <i class="fas fa-save"></i> Guardar Alterações
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- CONFIGURAÇÕES -->
            <section id="settings-section" class="content-section">
                <div class="section-header">
                    <h2>Configurações</h2>
                    <p>Personalize a sua experiência</p>
                </div>

                <div class="settings-container">
                    <!-- Secção Tema -->
                    <div class="settings-section-card">
                        <div class="settings-header">
                            <h3><i class="fas fa-palette"></i> Tema</h3>
                            <p>Escolha o esquema de cores preferido</p>
                        </div>
                        <div class="settings-content">
                            <div class="theme-options">
                                <label class="theme-option">
                                    <input type="radio" name="theme" value="light" onchange="app.setTheme('light')">
                                    <div class="theme-preview light-theme">
                                        <i class="fas fa-sun"></i>
                                    </div>
                                    <span>Claro</span>
                                </label>
                                <label class="theme-option">
                                    <input type="radio" name="theme" value="dark" onchange="app.setTheme('dark')">
                                    <div class="theme-preview dark-theme">
                                        <i class="fas fa-moon"></i>
                                    </div>
                                    <span>Escuro</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Secção Alteração de Palavra-passe -->
                    <div class="settings-section-card">
                        <div class="settings-header">
                            <h3><i class="fas fa-lock"></i> Alterar Palavra-passe</h3>
                            <p>Atualize a sua palavra-passe de segurança</p>
                        </div>
                        <div class="settings-content">
                            <div class="password-form">
                                <div class="form-group">
                                    <label>Palavra-passe Atual</label>
                                    <div class="password-input-group">
                                        <input type="password" id="currentPassword" placeholder="Digite a sua palavra-passe atual">
                                        <button class="toggle-password" onclick="app.togglePasswordField('currentPassword')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Nova Palavra-passe</label>
                                    <div class="password-input-group">
                                        <input type="password" id="newPassword" placeholder="Digite a nova palavra-passe" oninput="app.checkPasswordStrength()">
                                        <button class="toggle-password" onclick="app.togglePasswordField('newPassword')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="password-strength">
                                        <div class="strength-bar">
                                            <div class="strength-fill" id="strengthFill"></div>
                                        </div>
                                        <span class="strength-text" id="strengthText">Fraca</span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Confirmar Nova Palavra-passe</label>
                                    <div class="password-input-group">
                                        <input type="password" id="confirmPassword" placeholder="Confirme a nova palavra-passe">
                                        <button class="toggle-password" onclick="app.togglePasswordField('confirmPassword')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <button class="btn btn-primary" onclick="app.changePassword()">
                                    <i class="fas fa-key"></i> Guardar Alterações
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- MODAIS -->
    <!-- Modal de Confirmação de Logout -->
    <div class="modal" id="logoutModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Terminar Sessão</h3>
                <button class="modal-close" onclick="app.closeModal('logoutModal')">&times;</button>
            </div>
            <div class="modal-body">
                <p>Deseja realmente terminar a sessão?</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="app.closeModal('logoutModal')">Cancelar</button>
                <button class="btn btn-danger" onclick="app.confirmLogout()">Confirmar Saída</button>
            </div>
        </div>
    </div>

    <!-- Toast Notification Container -->
    <div id="toastContainer" class="toast-container"></div>

    <!-- Loading Spinner -->
    <div class="loading-spinner" id="loadingSpinner">
        <div class="spinner-ring"></div>
        <p>Carregando...</p>
    </div>

    <script src="js/professor.js"></script>
    <script src="js/professor_presenca.js"></script>
</body>
</html>
