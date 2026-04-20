<?php
require_once 'config/session.php';
require_once 'config/config.php';
require_once 'config/conexao.php';
require_once 'models/Usuario.php';
require_once 'models/Perfil.php';
require_once 'models/Filial.php';
requireLogin();

$menuActive = 'usuarios';

// Carregar dados para os filtros
try {
    $pdo = Conexao::getInstance()->getPdo();
    $perfil = new Perfil($pdo);
    $filial = new Filial($pdo);
    $usuario = new Usuario($pdo);
    
    $perfis = $perfil->findAtivos();
    $filiais = $filial->findAtivas();
    $estatisticas = $usuario->getEstatisticas();
} catch (Exception $e) {
    $perfis = [];
    $filiais = [];
    $estatisticas = [
        'total_usuarios' => 0,
        'usuarios_ativos' => 0,
        'usuarios_ativos_7dias' => 0,
        'usuarios_ativos_30dias' => 0
    ];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuários | Grupo Sorrisos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/usuarios.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
</head>
<body>
<?php include 'menu.php'; ?>
<main class="main-content">
            <div class="d-flex align-items-center mb-2">
                <span class="page-title"><i class="bi bi-box-seam"></i>Usuarios de acesso</span>
            </div>
            <div class="subtitle">Gerencie os Usuarios de acesso</div>
            <div class="d-flex flex-wrap gap-2 justify-content-end mb-3">
                <button class="btn btn-outline-light btn-action"><i class="bi bi-download me-1"></i> Exportar XLS</button>
                <button class="btn btn-outline-light btn-action"><i class="bi bi-printer me-1"></i> Imprimir</button>
                <button class="btn btn-duplicate btn-action"><i class="bi bi-files me-1"></i> Duplicar</button>
                <button class="btn btn-primary btn-action" type="button"><i class="bi bi-plus-lg me-1"></i> Novo Usuario</button>
            </div>
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card card-resumo">
                        <div class="card-body">
                            <div class="card-title">Total de Usuários</div>
                            <div class="card-value"><?= $estatisticas['total_usuarios'] ?></div>
                            <div class="text-success small">Usuários cadastrados</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-resumo">
                        <div class="card-body">
                            <div class="card-title">Usuários Ativos</div>
                            <div class="card-value" style="color:#22c55e;"><?= $estatisticas['usuarios_ativos'] ?></div>
                            <div class="text-muted small">Usuários com acesso</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-resumo">
                        <div class="card-body">
                            <div class="card-title">Ativos (7 dias)</div>
                            <div class="card-value" style="color:#eab308;"><?= $estatisticas['usuarios_ativos_7dias'] ?></div>
                            <div class="text-muted small">Acessaram recentemente</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-resumo">
                        <div class="card-body">
                            <div class="card-title">Ativos (30 dias)</div>
                            <div class="card-value" style="color:#3b82f6;"><?= $estatisticas['usuarios_ativos_30dias'] ?></div>
                            <div class="text-muted small">Acessaram no mês</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card filters-card mb-4">
                <div class="card-body">
                    <div class="filters-title">Filtros e Busca</div>
                    <div class="filters-subtitle">Busque e filtre materiais por diferentes critérios</div>
                    <form class="mb-3">
                        <div class="input-group mb-3">
                            <span class="input-group-text bg-white border-0"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control search-bar" placeholder="Buscar por código, descrição ou marca...">
                            <button type="button" class="btn btn-outline-light d-flex align-items-center ms-2"><i class="bi bi-funnel me-1"></i> Mais Filtros</button>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-3">
                                <select class="form-select" id="filtroPerfil">
                                    <option value="">Todos os Perfis</option>
                                    <?php foreach ($perfis as $perfil): ?>
                                        <option value="<?= $perfil['id_perfil'] ?>"><?= htmlspecialchars($perfil['nome_perfil']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="filtroFilial">
                                    <option value="">Todas as Filiais</option>
                                    <?php foreach ($filiais as $filial): ?>
                                        <option value="<?= $filial['id_filial'] ?>"><?= htmlspecialchars($filial['nome_filial']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="filtroStatus">
                                    <option value="">Todos os Status</option>
                                    <option value="1">Ativo</option>
                                    <option value="0">Inativo</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-outline-light w-100" onclick="limparFiltros()"><i class="bi bi-arrow-clockwise me-1"></i> Limpar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card card-resumo">
                <div class="card-body">
                    <div class="fw-bold mb-1" style="font-size:1.3rem;">Lista de Usuários</div>
                    <div class="text-muted mb-3">Todos os usuários cadastrados no sistema</div>
                    <div class="table-responsive">
                        <table class="table table-borderless table-usuarios mb-0">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>E-mail</th>
                                    <th>Perfil</th>
                                    <th>Status</th>
                                    <th>Último Acesso</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody id="usuarios-tbody">
                                <!-- Linhas dinâmicas aqui -->
                            </tbody>
                        </table>
                        <div id="usuarios-paginacao" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- MODAL NOVO USUÁRIO -->
<div class="modal fade" id="modalNovoPerfil" tabindex="-1" aria-labelledby="modalNovoPerfilLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow-lg">
      <form id="formUsuario" onsubmit="salvarUsuario(event)">
        <div class="modal-header border-0 pb-0">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-person-plus text-primary fs-3"></i>
            <h4 class="modal-title fw-bold" id="modalNovoPerfilLabel">Cadastrar Novo Usuário</h4>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body pt-0">
          <!-- Dados de Identificação -->
          <div class="mb-4">
            <div class="fw-semibold mb-2" style="color:#2563eb;"><i class="bi bi-person-badge me-2"></i>Dados de Identificação</div>
            <div class="row g-3 mb-2">
              <div class="col-md-6">
                <label class="form-label">Nome Completo *</label>
                <input type="text" class="form-control" id="nomeCompleto" placeholder="Nome completo do usuário" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">E-mail *</label>
                <input type="email" class="form-control" id="emailUsuario" placeholder="usuario@gruposorriso.com" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">CPF</label>
                <input type="text" class="form-control" id="cpfUsuario" placeholder="000.000.000-00">
              </div>
              <div class="col-md-6">
                <label class="form-label">Telefone/WhatsApp</label>
                <input type="text" class="form-control" id="telefoneUsuario" placeholder="(11) 99999-9999">
              </div>
            </div>
          </div>
          <!-- Credenciais de Acesso -->
          <div class="mb-4">
            <div class="fw-semibold mb-2" style="color:#f59e42;"><i class="bi bi-key-fill me-2"></i>Credenciais de Acesso</div>
            <div class="row g-3 mb-2">
              <div class="col-md-6">
                <label class="form-label">Senha *</label>
                <div class="input-group">
                  <input type="password" class="form-control" id="senhaUsuario" placeholder="Digite a senha" required>
                  <span class="input-group-text" onclick="togglePassword('senhaUsuario')"><i class="bi bi-eye"></i></span>
                </div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Confirmar Senha *</label>
                <input type="password" class="form-control" id="confirmarSenha" placeholder="Confirme a senha" required>
              </div>
            </div>
          </div>
          <!-- Permissões e Regras -->
          <div class="mb-4">
            <div class="fw-semibold mb-2" style="color:#2563eb;"><i class="bi bi-shield-lock me-2"></i>Permissões e Regras</div>
            <div class="row g-3 mb-2">
              <div class="col-md-6">
                <label class="form-label">Perfil/Nível de Acesso *</label>
                <select class="form-select" id="perfilUsuario" required>
                    <option value="">Selecione o perfil</option>
                    <?php foreach ($perfis as $perfil): ?>
                        <option value="<?= $perfil['id_perfil'] ?>"><?= htmlspecialchars($perfil['nome_perfil']) ?></option>
                    <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Status do Usuário</label>
                <select class="form-select" id="statusUsuario">
                    <option value="1">Ativo</option>
                    <option value="0">Inativo</option>
                </select>
              </div>
            </div>
          </div>
          <!-- Dados do Departamento -->
          <div class="mb-4">
            <div class="fw-semibold mb-2" style="color:#2563eb;"><i class="bi bi-building me-2"></i>Dados do Departamento</div>
            <div class="row g-3 mb-2">
              <div class="col-md-12">
                <label class="form-label">Filial</label>
                <select class="form-select" id="filialUsuario">
                    <option value="">Selecione a filial</option>
                    <?php foreach ($filiais as $filial): ?>
                        <option value="<?= $filial['id_filial'] ?>"><?= htmlspecialchars($filial['nome_filial']) ?></option>
                    <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0 pb-4 px-4 d-flex justify-content-end gap-2">
          <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> Salvar Usuário</button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- FIM MODAL NOVO USUÁRIO -->

<!-- MODAL EDITAR USUÁRIO -->
<div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-labelledby="modalEditarUsuarioLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow-lg">
      <form id="formEditarUsuario" onsubmit="atualizarUsuario(event)">
        <div class="modal-header border-0 pb-0">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-person-check text-primary fs-3"></i>
            <h4 class="modal-title fw-bold" id="modalEditarUsuarioLabel">Editar Usuário</h4>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body pt-0">
          <!-- Dados de Identificação -->
          <div class="mb-4">
            <div class="fw-semibold mb-2" style="color:#2563eb;"><i class="bi bi-person-badge me-2"></i>Dados de Identificação</div>
            <div class="row g-3 mb-2">
              <div class="col-md-6">
                <label class="form-label">Nome Completo *</label>
                <input type="text" class="form-control" id="editNomeCompleto" placeholder="Nome completo do usuário" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">E-mail *</label>
                <input type="email" class="form-control" id="editEmailUsuario" placeholder="usuario@gruposorriso.com" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">CPF</label>
                <input type="text" class="form-control" id="editCpfUsuario" placeholder="000.000.000-00">
              </div>
              <div class="col-md-6">
                <label class="form-label">Telefone/WhatsApp</label>
                <input type="text" class="form-control" id="editTelefoneUsuario" placeholder="(11) 99999-9999">
              </div>
            </div>
            <input type="hidden" id="editIdUsuario">
          </div>
          <!-- Credenciais de Acesso -->
          <div class="mb-4">
            <div class="fw-semibold mb-2" style="color:#f59e42;"><i class="bi bi-key-fill me-2"></i>Credenciais de Acesso</div>
            <div class="row g-3 mb-2">
              <div class="col-md-6">
                <label class="form-label">Nova Senha (opcional)</label>
                <div class="input-group">
                  <input type="password" class="form-control" id="editSenhaUsuario" placeholder="Deixe em branco para manter a senha atual">
                  <span class="input-group-text" onclick="togglePassword('editSenhaUsuario')"><i class="bi bi-eye"></i></span>
                </div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Confirmar Nova Senha</label>
                <input type="password" class="form-control" id="editConfirmarSenha" placeholder="Confirme a nova senha">
              </div>
            </div>
          </div>
          <!-- Permissões e Regras -->
          <div class="mb-4">
            <div class="fw-semibold mb-2" style="color:#2563eb;"><i class="bi bi-shield-lock me-2"></i>Permissões e Regras</div>
            <div class="row g-3 mb-2">
              <div class="col-md-6">
                <label class="form-label">Perfil/Nível de Acesso *</label>
                <select class="form-select" id="editPerfilUsuario" required>
                    <option value="">Selecione o perfil</option>
                    <?php foreach ($perfis as $perfil): ?>
                        <option value="<?= $perfil['id_perfil'] ?>"><?= htmlspecialchars($perfil['nome_perfil']) ?></option>
                    <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Status do Usuário</label>
                <select class="form-select" id="editStatusUsuario">
                    <option value="1">Ativo</option>
                    <option value="0">Inativo</option>
                </select>
              </div>
            </div>
          </div>
          <!-- Dados do Departamento -->
          <div class="mb-4">
            <div class="fw-semibold mb-2" style="color:#2563eb;"><i class="bi bi-building me-2"></i>Dados do Departamento</div>
            <div class="row g-3 mb-2">
              <div class="col-md-12">
                <label class="form-label">Filial</label>
                <select class="form-select" id="editFilialUsuario">
                    <option value="">Selecione a filial</option>
                    <?php foreach ($filiais as $filial): ?>
                        <option value="<?= $filial['id_filial'] ?>"><?= htmlspecialchars($filial['nome_filial']) ?></option>
                    <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0 pb-4 px-4 d-flex justify-content-end gap-2">
          <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> Atualizar Usuário</button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- FIM MODAL EDITAR USUÁRIO -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.querySelector('.btn.btn-primary.btn-action').addEventListener('click', function() {
  var modal = new bootstrap.Modal(document.getElementById('modalNovoPerfil'));
  modal.show();
});

// Carregar estatísticas
async function carregarEstatisticas() {
    try {
        const response = await fetch('backend/api/usuarios.php?action=estatisticas');
        const result = await response.json();
        
        if (result.success) {
            const stats = result.data;
            document.getElementById('total-usuarios').textContent = stats.total_usuarios || 0;
            document.getElementById('usuarios-ativos').textContent = stats.usuarios_ativos || 0;
            document.getElementById('usuarios-ativos-7dias').textContent = stats.usuarios_ativos_7dias || 0;
            document.getElementById('usuarios-ativos-30dias').textContent = stats.usuarios_ativos_30dias || 0;
        }
    } catch (error) {
        console.error('Erro ao carregar estatísticas:', error);
    }
}

async function carregarUsuarios(page = 1, search = '') {
    try {
        // Obter filtros
        const filtroPerfil = document.getElementById('filtroPerfil').value;
        const filtroFilial = document.getElementById('filtroFilial').value;
        const filtroStatus = document.getElementById('filtroStatus').value;
        
        // Construir URL com filtros
        let url = `backend/api/usuarios.php?action=list&page=${page}&limit=10`;
        if (search) url += `&search=${encodeURIComponent(search)}`;
        if (filtroPerfil) url += `&perfil=${filtroPerfil}`;
        if (filtroFilial) url += `&filial=${filtroFilial}`;
        if (filtroStatus !== '') url += `&status=${filtroStatus}`;
        
        const response = await fetch(url);
        const result = await response.json();
        
        const tbody = document.getElementById('usuarios-tbody');
        tbody.innerHTML = '';
        
        if (result.success && result.data && result.data.length > 0) {
            result.data.forEach(usuario => {
                const ultimoAcesso = usuario.ultimo_acesso ? 
                    new Date(usuario.ultimo_acesso).toLocaleDateString('pt-BR') + ' ' + 
                    new Date(usuario.ultimo_acesso).toLocaleTimeString('pt-BR', {hour: '2-digit', minute: '2-digit'}) : 
                    '-';
                
                tbody.innerHTML += `
                    <tr>
                        <td>${usuario.nome_completo || ''}</td>
                        <td>${usuario.email || ''}</td>
                        <td>${usuario.nome_perfil || ''}</td>
                        <td>${usuario.ativo == 1 ? '<span class="badge bg-success">Ativo</span>' : '<span class="badge bg-secondary">Inativo</span>'}</td>
                        <td>${ultimoAcesso}</td>
                        <td>
                            <button class="icon-btn text-success" title="Editar" onclick="editarUsuario(${usuario.id_usuario})"><i class="bi bi-pencil"></i></button>
                            <button class="icon-btn text-danger" title="Excluir" onclick="excluirUsuario(${usuario.id_usuario})"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>
                `;
            });
        } else {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        <i class="bi bi-search fs-1 mb-2"></i><br>
                        Nenhum usuário encontrado
                    </td>
                </tr>
            `;
        }
        
        // Paginação
        const pagDiv = document.getElementById('usuarios-paginacao');
        let pagHtml = '';
        if (result.pagination && result.pagination.total_pages > 1) {
            pagHtml += `<nav><ul class='pagination pagination-sm justify-content-center'>`;
            
            // Botão anterior
            if (result.pagination.page > 1) {
                pagHtml += `<li class='page-item'><a class='page-link' href='#' onclick='carregarUsuarios(${result.pagination.page - 1}, "${search}");return false;'><i class="bi bi-chevron-left"></i></a></li>`;
            }
            
            // Páginas
            for (let i = 1; i <= result.pagination.total_pages; i++) {
                if (i === 1 || i === result.pagination.total_pages || (i >= result.pagination.page - 2 && i <= result.pagination.page + 2)) {
                    pagHtml += `<li class='page-item${i === result.pagination.page ? ' active' : ''}'><a class='page-link' href='#' onclick='carregarUsuarios(${i}, "${search}");return false;'>${i}</a></li>`;
                } else if (i === result.pagination.page - 3 || i === result.pagination.page + 3) {
                    pagHtml += `<li class='page-item disabled'><span class='page-link'>...</span></li>`;
                }
            }
            
            // Botão próximo
            if (result.pagination.page < result.pagination.total_pages) {
                pagHtml += `<li class='page-item'><a class='page-link' href='#' onclick='carregarUsuarios(${result.pagination.page + 1}, "${search}");return false;'><i class="bi bi-chevron-right"></i></a></li>`;
            }
            
            pagHtml += `</ul></nav>`;
        }
        pagDiv.innerHTML = pagHtml;
        
    } catch (error) {
        console.error('Erro ao carregar usuários:', error);
        document.getElementById('usuarios-tbody').innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-danger py-4">
                    <i class="bi bi-exclamation-triangle fs-1 mb-2"></i><br>
                    Erro ao carregar usuários
                </td>
            </tr>
        `;
    }
}

// Busca
const searchInput = document.querySelector('.search-bar');
if (searchInput) {
    searchInput.addEventListener('input', function() {
        carregarUsuarios(1, this.value);
    });
}

// Filtros
document.getElementById('filtroPerfil').addEventListener('change', function() {
    carregarUsuarios(1, searchInput ? searchInput.value : '');
});

document.getElementById('filtroFilial').addEventListener('change', function() {
    carregarUsuarios(1, searchInput ? searchInput.value : '');
});

document.getElementById('filtroStatus').addEventListener('change', function() {
    carregarUsuarios(1, searchInput ? searchInput.value : '');
});

function limparFiltros() {
    document.getElementById('filtroPerfil').value = '';
    document.getElementById('filtroFilial').value = '';
    document.getElementById('filtroStatus').value = '';
    if (searchInput) searchInput.value = '';
    carregarUsuarios(1, '');
}

function editarUsuario(id) {
    // Carregar dados do usuário
    carregarDadosUsuario(id);
    
    // Abrir modal de edição
    const modal = new bootstrap.Modal(document.getElementById('modalEditarUsuario'));
    modal.show();
}

async function carregarDadosUsuario(id) {
    try {
        const response = await fetch(`backend/api/usuarios.php?action=get&id=${id}`);
        const result = await response.json();
        
        if (result.success) {
            const usuario = result.data;
            
            // Preencher formulário com dados do usuário
            document.getElementById('editIdUsuario').value = usuario.id_usuario;
            document.getElementById('editNomeCompleto').value = usuario.nome_completo || '';
            document.getElementById('editEmailUsuario').value = usuario.email || '';
            document.getElementById('editCpfUsuario').value = usuario.cpf || '';
            document.getElementById('editTelefoneUsuario').value = usuario.telefone || '';
            document.getElementById('editPerfilUsuario').value = usuario.id_perfil || '';
            document.getElementById('editFilialUsuario').value = usuario.id_filial || '';
            document.getElementById('editStatusUsuario').value = usuario.ativo || '1';
            
            // Limpar campos de senha
            document.getElementById('editSenhaUsuario').value = '';
            document.getElementById('editConfirmarSenha').value = '';
            
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: result.error || 'Erro ao carregar dados do usuário',
                confirmButtonText: 'OK'
            });
        }
    } catch (error) {
        console.error('Erro:', error);
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'Erro de conexão. Verifique sua internet e tente novamente.',
            confirmButtonText: 'OK'
        });
    }
}

async function atualizarUsuario(event) {
    event.preventDefault();
    
    // Validar senhas se foram preenchidas
    const senha = document.getElementById('editSenhaUsuario').value;
    const confirmarSenha = document.getElementById('editConfirmarSenha').value;
    
    if (senha && senha !== confirmarSenha) {
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'As senhas não coincidem!',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    // Validar campos obrigatórios
    const nomeCompleto = document.getElementById('editNomeCompleto').value.trim();
    const email = document.getElementById('editEmailUsuario').value.trim();
    const perfil = document.getElementById('editPerfilUsuario').value;
    const id = document.getElementById('editIdUsuario').value;
    
    if (!nomeCompleto || !email || !perfil) {
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'Por favor, preencha todos os campos obrigatórios!',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    // Coletar dados do formulário
    const dados = {
        nome_completo: nomeCompleto,
        email: email,
        cpf: document.getElementById('editCpfUsuario').value.trim() || null,
        telefone: document.getElementById('editTelefoneUsuario').value.trim() || null,
        id_perfil: perfil,
        id_filial: document.getElementById('editFilialUsuario').value || null,
        ativo: document.getElementById('editStatusUsuario').value
    };
    
    // Adicionar senha apenas se foi preenchida
    if (senha) {
        dados.senha = senha;
    }
    
    // Mostrar loading
    const btnSubmit = event.target.querySelector('button[type="submit"]');
    const originalText = btnSubmit.innerHTML;
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Atualizando...';
    
    try {
        const response = await fetch(`backend/api/usuarios.php?action=update&id=${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(dados)
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Mostrar mensagem de sucesso
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: 'Usuário atualizado com sucesso!',
                confirmButtonText: 'OK'
            });
            
            // Fechar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarUsuario'));
            modal.hide();
            
            // Recarregar lista
            carregarUsuarios();
            
            // Recarregar estatísticas
            carregarEstatisticas();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: result.error || 'Erro ao atualizar usuário',
                confirmButtonText: 'OK'
            });
        }
    } catch (error) {
        console.error('Erro:', error);
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'Erro de conexão. Verifique sua internet e tente novamente.',
            confirmButtonText: 'OK'
        });
    } finally {
        // Restaurar botão
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = originalText;
    }
}

function excluirUsuario(id) {
    Swal.fire({
        title: 'Tem certeza?',
        text: "Esta ação não pode ser desfeita!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            excluirUsuarioAPI(id);
        }
    });
}

async function excluirUsuarioAPI(id) {
    try {
        const response = await fetch(`backend/api/usuarios.php?action=delete&id=${id}`, {
            method: 'DELETE'
        });
        const result = await response.json();
        
        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: 'Usuário excluído com sucesso!',
                confirmButtonText: 'OK'
            });
            carregarUsuarios();
            carregarEstatisticas();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: result.error || 'Erro ao excluir usuário',
                confirmButtonText: 'OK'
            });
        }
    } catch (error) {
        console.error('Erro:', error);
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'Erro de conexão. Verifique sua internet e tente novamente.',
            confirmButtonText: 'OK'
        });
    }
}

async function salvarUsuario(event) {
    event.preventDefault();
    
    // Validar senhas
    const senha = document.getElementById('senhaUsuario').value;
    const confirmarSenha = document.getElementById('confirmarSenha').value;
    
    if (senha !== confirmarSenha) {
        alert('As senhas não coincidem!');
        return;
    }
    
    // Validar campos obrigatórios
    const nomeCompleto = document.getElementById('nomeCompleto').value.trim();
    const email = document.getElementById('emailUsuario').value.trim();
    const perfil = document.getElementById('perfilUsuario').value;
    
    if (!nomeCompleto || !email || !perfil) {
        alert('Por favor, preencha todos os campos obrigatórios!');
        return;
    }
    
    // Coletar dados do formulário (apenas campos que existem na tabela)
    const dados = {
        nome_completo: nomeCompleto,
        email: email,
        senha: senha,
        cpf: document.getElementById('cpfUsuario').value.trim() || null,
        telefone: document.getElementById('telefoneUsuario').value.trim() || null,
        id_perfil: perfil,
        id_filial: document.getElementById('filialUsuario').value || null,
        ativo: document.getElementById('statusUsuario').value
    };
    
    // Mostrar loading
    const btnSubmit = event.target.querySelector('button[type="submit"]');
    const originalText = btnSubmit.innerHTML;
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Salvando...';
    
    try {
        const response = await fetch('backend/api/usuarios.php?action=create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(dados)
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Mostrar mensagem de sucesso
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: 'Usuário criado com sucesso!',
                confirmButtonText: 'OK'
            });
            
            // Fechar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalNovoPerfil'));
            modal.hide();
            
            // Limpar formulário
            document.getElementById('formUsuario').reset();
            
            // Recarregar lista
            carregarUsuarios();
            
            // Recarregar estatísticas
            carregarEstatisticas();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: result.error || 'Erro ao criar usuário',
                confirmButtonText: 'OK'
            });
        }
    } catch (error) {
        console.error('Erro:', error);
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'Erro de conexão. Verifique sua internet e tente novamente.',
            confirmButtonText: 'OK'
        });
    } finally {
        // Restaurar botão
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = originalText;
    }
}

function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = field.nextElementSibling.querySelector('i');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

// Inicial
carregarEstatisticas();
carregarUsuarios();
</script>
</main>
</body>
</html>
