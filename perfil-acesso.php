<?php
require_once 'config/session.php';
require_once 'config/config.php';
require_once 'config/conexao.php';
require_once 'models/Perfil.php';
requireLogin();

$menuActive = 'perfil-acesso';

try {
    $pdo = Conexao::getInstance()->getPdo();
    $perfil = new Perfil($pdo);
    
    $estatisticas = $perfil->getEstatisticas();
    $paginas = $perfil->getPaginas();
} catch (Exception $e) {
    $estatisticas = [
        'total_perfis' => 0,
        'perfis_ativos' => 0,
        'perfis_inativos' => 0,
        'total_usuarios' => 0,
        'perfis_em_uso' => 0
    ];
    $paginas = [];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Acesso | Grupo Sorrisos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/usuarios.css">
    <link rel="stylesheet" href="assets/css/perfil-acesso.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body>
<?php include 'menu.php'; ?>
<main class="main-content">
            <div class="d-flex align-items-center mb-2">
                <span class="page-title"><i class="bi bi-shield-check"></i>Perfil de Acesso</span>
            </div>
            <div class="subtitle">Gerencie os perfis de acesso do sistema</div>
            <div class="d-flex flex-wrap gap-2 justify-content-end mb-3">
                <button class="btn btn-outline-light btn-action" onclick="exportarXLS()"><i class="bi bi-download me-1"></i> Exportar XLS</button>
                <button class="btn btn-outline-light btn-action" onclick="imprimir()"><i class="bi bi-printer me-1"></i> Imprimir</button>
                <button class="btn btn-duplicate btn-action" onclick="duplicarSelecionados()"><i class="bi bi-files me-1"></i> Duplicar</button>
                <button class="btn btn-primary btn-action" onclick="abrirModalNovoPerfil()" type="button"><i class="bi bi-plus-lg me-1"></i> Novo Perfil</button>
            </div>
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card card-resumo">
                        <div class="card-body">
                            <div class="card-title">Total de Perfis</div>
                            <div class="card-value"><?= $estatisticas['total_perfis'] ?></div>
                            <div class="text-success small">Perfis cadastrados</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-resumo">
                        <div class="card-body">
                            <div class="card-title">Perfis Ativos</div>
                            <div class="card-value" style="color:#22c55e;"><?= $estatisticas['perfis_ativos'] ?></div>
                            <div class="text-muted small">Em uso no sistema</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-resumo">
                        <div class="card-body">
                            <div class="card-title">Total de Usuários</div>
                            <div class="card-value" style="color:#eab308;"><?= $estatisticas['total_usuarios'] ?></div>
                            <div class="text-muted small">Usuários ativos</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-resumo">
                        <div class="card-body">
                            <div class="card-title">Perfis em Uso</div>
                            <div class="card-value" style="color:#3b82f6;"><?= $estatisticas['perfis_em_uso'] ?></div>
                            <div class="text-muted small">Com usuários vinculados</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card filters-card mb-4">
                <div class="card-body">
                    <div class="filters-title">Filtros e Busca</div>
                    <div class="filters-subtitle">Busque e filtre perfis por diferentes critérios</div>
                    <form class="mb-3">
                        <div class="input-group mb-3">
                            <span class="input-group-text bg-white border-0"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control search-bar" id="buscaPerfil" placeholder="Buscar por nome ou descrição...">
                            <button type="button" class="btn btn-outline-light d-flex align-items-center ms-2" onclick="limparBusca()"><i class="bi bi-arrow-clockwise me-1"></i> Limpar</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card card-resumo">
                <div class="card-body">
                    <div class="fw-bold mb-1" style="font-size:1.3rem;">Lista de Perfis de Acesso</div>
                    <div class="text-muted mb-3">Todos os perfis cadastrados no sistema</div>
                    <div class="table-responsive">
                        <table class="table table-borderless table-materials mb-0" id="tabela-perfis">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Perfil</th>
                                    <th>Descrição</th>
                                    <th>Usuários</th>
                                    <th>Status</th>
                                    <th>Data Criação</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Perfis serão carregados via JS -->
                            </tbody>
                        </table>
                        <nav>
                          <ul class="pagination justify-content-end mt-3" id="paginacao-perfis"></ul>
                        </nav>
                    </div>
                </div>
            </div>
        </main>

<!-- MODAL NOVO PERFIL DE ACESSO -->
<div class="modal fade" id="modalNovoPerfil" tabindex="-1" aria-labelledby="modalNovoPerfilLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow-lg">
      <form id="formPerfil" onsubmit="salvarPerfil(event)">
        <div class="modal-header border-0 pb-0">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-shield-check text-primary fs-3"></i>
            <h4 class="modal-title fw-bold" id="modalNovoPerfilLabel">Criar Novo Perfil de Acesso</h4>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body pt-0">
          <!-- Identificação do Perfil -->
          <div class="mb-4">
            <div class="fw-semibold mb-2" style="color:#e11d48;"><i class="bi bi-pin-angle-fill me-2"></i>Identificação do Perfil</div>
            <div class="row g-3 mb-2">
              <div class="col-md-6">
                <label class="form-label">Nome do Perfil *</label>
                <input type="text" class="form-control" id="nomePerfil" placeholder="Ex: Administrador, Financeiro, Estoque" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Código Interno</label>
                <input type="text" class="form-control" id="codigoPerfil" placeholder="Ex: ADMIN, FINANCE, STOCK">
              </div>
              <div class="col-12">
                <label class="form-label">Descrição *</label>
                <textarea class="form-control" id="descricaoPerfil" rows="2" placeholder="Explique para que serve este perfil e quais são suas responsabilidades..." required></textarea>
              </div>
            </div>
          </div>
          <!-- Permissões de Módulos/Telas -->
          <div class="mb-4">
            <div class="fw-semibold mb-2" style="color:#f59e42;"><i class="bi bi-unlock-fill me-2"></i>Permissões por Página/Módulo</div>
            <div class="table-responsive">
              <table class="table table-bordered align-middle" id="tabela-permissoes">
                <thead>
                  <tr>
                    <th>Página/Módulo</th>
                    <th class="text-center">Criar</th>
                    <th class="text-center">Leitura</th>
                    <th class="text-center">Atualizar</th>
                    <th class="text-center">Deletar</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($paginas as $pagina): ?>
                  <tr>
                    <td><?= htmlspecialchars($pagina['nome_pagina']) ?></td>
                    <td class="text-center"><input type="checkbox" data-pagina="<?= htmlspecialchars($pagina['nome_pagina']) ?>" data-perm="create"></td>
                    <td class="text-center"><input type="checkbox" data-pagina="<?= htmlspecialchars($pagina['nome_pagina']) ?>" data-perm="read" checked></td>
                    <td class="text-center"><input type="checkbox" data-pagina="<?= htmlspecialchars($pagina['nome_pagina']) ?>" data-perm="update"></td>
                    <td class="text-center"><input type="checkbox" data-pagina="<?= htmlspecialchars($pagina['nome_pagina']) ?>" data-perm="delete"></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
          <!-- Ações Permitidas -->
          <div class="mb-4">
            <div class="fw-semibold mb-2" style="color:#f59e42;"><i class="bi bi-lightning-charge-fill me-2"></i>Ações Permitidas</div>
            <div class="row g-3 mb-2">
              <div class="col-md-6">
                <div class="form-check form-switch d-flex justify-content-between align-items-center mb-2">
                  <label class="form-check-label">Pode criar registros</label>
                  <input class="form-check-input" type="checkbox" id="podeCriar">
                </div>
                <div class="form-check form-switch d-flex justify-content-between align-items-center mb-2">
                  <label class="form-check-label">Pode editar registros</label>
                  <input class="form-check-input" type="checkbox" id="podeEditar">
                </div>
                <div class="form-check form-switch d-flex justify-content-between align-items-center mb-2">
                  <label class="form-check-label">Pode excluir registros</label>
                  <input class="form-check-input" type="checkbox" id="podeExcluir">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-check form-switch d-flex justify-content-between align-items-center mb-2">
                  <label class="form-check-label">Pode exportar dados</label>
                  <input class="form-check-input" type="checkbox" id="podeExportar">
                </div>
                <div class="form-check form-switch d-flex justify-content-between align-items-center mb-2">
                  <label class="form-check-label">Pode aprovar pedidos</label>
                  <input class="form-check-input" type="checkbox" id="podeAprovar">
                </div>
                <div class="form-check form-switch d-flex justify-content-between align-items-center mb-2">
                  <label class="form-check-label">Pode configurar sistema</label>
                  <input class="form-check-input" type="checkbox" id="podeConfigurar">
                </div>
              </div>
            </div>
          </div>
          <!-- Filtros e Restrições Adicionais -->
          <div class="mb-4">
            <div class="fw-semibold mb-2" style="color:#ef4444;"><i class="bi bi-slash-circle-fill me-2"></i>Filtros e Restrições Adicionais</div>
            <div class="row g-3 mb-2">
              <div class="col-md-12">
                <div class="form-check form-switch d-flex justify-content-between align-items-center mb-2">
                  <div>
                    <div class="fw-normal">Só pode ver dados do próprio departamento</div>
                    <div class="small text-muted">Usuário terá acesso limitado aos dados de seu departamento</div>
                  </div>
                  <input class="form-check-input" type="checkbox" id="restricaoDepartamento">
                </div>
                <div class="form-check form-switch d-flex justify-content-between align-items-center mb-2">
                  <div>
                    <div class="fw-normal">Não pode visualizar valores financeiros</div>
                    <div class="small text-muted">Oculta preços, custos e valores monetários</div>
                  </div>
                  <input class="form-check-input" type="checkbox" id="restricaoFinanceiro">
                </div>
                <div class="form-check form-switch d-flex justify-content-between align-items-center mb-2">
                  <div>
                    <div class="fw-normal">Limitar horários de acesso</div>
                    <div class="small text-muted">Permitir acesso apenas em horário comercial</div>
                  </div>
                  <input class="form-check-input" type="checkbox" id="restricaoHorario">
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0 pb-4 px-4 d-flex justify-content-end gap-2">
          <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> Salvar Perfil</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL EDITAR PERFIL -->
<div class="modal fade" id="modalEditarPerfil" tabindex="-1" aria-labelledby="modalEditarPerfilLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow-lg">
      <form id="formEditarPerfil" onsubmit="atualizarPerfil(event)">
        <div class="modal-header border-0 pb-0">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-shield-check text-primary fs-3"></i>
            <h4 class="modal-title fw-bold" id="modalEditarPerfilLabel">Editar Perfil de Acesso</h4>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body pt-0">
          <input type="hidden" id="editIdPerfil">
          <!-- Identificação do Perfil -->
          <div class="mb-4">
            <div class="fw-semibold mb-2" style="color:#e11d48;"><i class="bi bi-pin-angle-fill me-2"></i>Identificação do Perfil</div>
            <div class="row g-3 mb-2">
              <div class="col-md-6">
                <label class="form-label">Nome do Perfil *</label>
                <input type="text" class="form-control" id="editNomePerfil" placeholder="Ex: Administrador, Financeiro, Estoque" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Código Interno</label>
                <input type="text" class="form-control" id="editCodigoPerfil" placeholder="Ex: ADMIN, FINANCE, STOCK">
              </div>
              <div class="col-12">
                <label class="form-label">Descrição *</label>
                <textarea class="form-control" id="editDescricaoPerfil" rows="2" placeholder="Explique para que serve este perfil e quais são suas responsabilidades..." required></textarea>
              </div>
            </div>
          </div>
          <!-- Permissões de Módulos/Telas -->
          <div class="mb-4">
            <div class="fw-semibold mb-2" style="color:#f59e42;"><i class="bi bi-unlock-fill me-2"></i>Permissões por Página/Módulo</div>
            <div class="table-responsive">
              <table class="table table-bordered align-middle" id="tabela-permissoes-edit">
                <thead>
                  <tr>
                    <th>Página/Módulo</th>
                    <th class="text-center">Criar</th>
                    <th class="text-center">Leitura</th>
                    <th class="text-center">Atualizar</th>
                    <th class="text-center">Deletar</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($paginas as $pagina): ?>
                  <tr>
                    <td><?= htmlspecialchars($pagina['nome_pagina']) ?></td>
                    <td class="text-center"><input type="checkbox" data-pagina="<?= htmlspecialchars($pagina['nome_pagina']) ?>" data-perm="create"></td>
                    <td class="text-center"><input type="checkbox" data-pagina="<?= htmlspecialchars($pagina['nome_pagina']) ?>" data-perm="read"></td>
                    <td class="text-center"><input type="checkbox" data-pagina="<?= htmlspecialchars($pagina['nome_pagina']) ?>" data-perm="update"></td>
                    <td class="text-center"><input type="checkbox" data-pagina="<?= htmlspecialchars($pagina['nome_pagina']) ?>" data-perm="delete"></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0 pb-4 px-4 d-flex justify-content-end gap-2">
          <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> Atualizar Perfil</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
let currentPage = 1;

// Carregar dados iniciais
document.addEventListener('DOMContentLoaded', function() {
    carregarPerfis();
});

// Carregar perfis
async function carregarPerfis(page = 1, search = '') {
    try {
        currentPage = page;
        const busca = document.getElementById('buscaPerfil').value;
        
        let url = `backend/api/perfis.php?action=list&page=${page}&limit=10`;
        if (busca) url += `&search=${encodeURIComponent(busca)}`;
        
        const response = await fetch(url);
        const result = await response.json();
        
        const tbody = document.querySelector('#tabela-perfis tbody');
        tbody.innerHTML = '';
        
        if (result.success && result.data && result.data.length > 0) {
            result.data.forEach(perfil => {
                const dataCriacao = perfil.data_criacao ? 
                    new Date(perfil.data_criacao).toLocaleDateString('pt-BR') : '-';
                
                tbody.innerHTML += `
                    <tr>
                        <td>${perfil.id_perfil}</td>
                        <td><strong>${perfil.nome_perfil}</strong></td>
                        <td>${perfil.descricao || '-'}</td>
                        <td><span class="badge bg-info">${perfil.total_usuarios || 0}</span></td>
                        <td><span class="badge bg-${perfil.ativo == 1 ? 'success' : 'secondary'}">${perfil.ativo == 1 ? 'Ativo' : 'Inativo'}</span></td>
                        <td>${dataCriacao}</td>
                        <td>
                            <button class="icon-btn text-success me-2" title="Editar" onclick="editarPerfil(${perfil.id_perfil})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="icon-btn text-danger" title="Excluir" onclick="excluirPerfil(${perfil.id_perfil})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
        } else {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        <i class="bi bi-search fs-1 mb-2"></i><br>
                        Nenhum perfil encontrado
                    </td>
                </tr>
            `;
        }
        
        // Paginação
        const pagDiv = document.getElementById('paginacao-perfis');
        let pagHtml = '';
        if (result.pagination && result.pagination.total_pages > 1) {
            pagHtml += `<nav><ul class='pagination pagination-sm justify-content-center'>`;
            
            // Botão anterior
            if (result.pagination.page > 1) {
                pagHtml += `<li class='page-item'><a class='page-link' href='#' onclick='carregarPerfis(${result.pagination.page - 1}, "${search}");return false;'><i class="bi bi-chevron-left"></i></a></li>`;
            }
            
            // Páginas
            for (let i = 1; i <= result.pagination.total_pages; i++) {
                if (i === 1 || i === result.pagination.total_pages || (i >= result.pagination.page - 2 && i <= result.pagination.page + 2)) {
                    pagHtml += `<li class='page-item${i === result.pagination.page ? ' active' : ''}'><a class='page-link' href='#' onclick='carregarPerfis(${i}, "${search}");return false;'>${i}</a></li>`;
                } else if (i === result.pagination.page - 3 || i === result.pagination.page + 3) {
                    pagHtml += `<li class='page-item disabled'><span class='page-link'>...</span></li>`;
                }
            }
            
            // Botão próximo
            if (result.pagination.page < result.pagination.total_pages) {
                pagHtml += `<li class='page-item'><a class='page-link' href='#' onclick='carregarPerfis(${result.pagination.page + 1}, "${search}");return false;'><i class="bi bi-chevron-right"></i></a></li>`;
            }
            
            pagHtml += `</ul></nav>`;
        }
        pagDiv.innerHTML = pagHtml;
        
    } catch (error) {
        console.error('Erro ao carregar perfis:', error);
        document.querySelector('#tabela-perfis tbody').innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-danger py-4">
                    <i class="bi bi-exclamation-triangle fs-1 mb-2"></i><br>
                    Erro ao carregar perfis
                </td>
            </tr>
        `;
    }
}

// Buscar perfis
document.getElementById('buscaPerfil').addEventListener('input', function() {
    carregarPerfis(1, this.value);
});

function limparBusca() {
    document.getElementById('buscaPerfil').value = '';
    carregarPerfis(1, '');
}

// Abrir modal novo perfil
document.querySelector('.btn.btn-primary.btn-action').addEventListener('click', function() {
    const modal = new bootstrap.Modal(document.getElementById('modalNovoPerfil'));
    modal.show();
});

// Salvar perfil
async function salvarPerfil(event) {
    event.preventDefault();
    
    // Validar campos obrigatórios
    const nomePerfil = document.getElementById('nomePerfil').value.trim();
    const descricaoPerfil = document.getElementById('descricaoPerfil').value.trim();
    
    if (!nomePerfil || !descricaoPerfil) {
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'Por favor, preencha todos os campos obrigatórios!',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    // Coletar permissões
    const permissoes = {};
    document.querySelectorAll('#tabela-permissoes tbody tr').forEach(tr => {
        const pagina = tr.querySelector('td').innerText.trim();
        permissoes[pagina] = [];
        tr.querySelectorAll('input[type=checkbox]').forEach(cb => {
            if(cb.checked) permissoes[pagina].push(cb.getAttribute('data-perm'));
        });
        if(permissoes[pagina].length === 0) delete permissoes[pagina];
    });
    
    // Coletar dados do formulário
    const dados = {
        nome_perfil: nomePerfil,
        codigo_perfil: document.getElementById('codigoPerfil').value.trim() || null,
        descricao: descricaoPerfil,
        permissoes: permissoes,
        ativo: 1
    };
    
    // Mostrar loading
    const btnSubmit = event.target.querySelector('button[type="submit"]');
    const originalText = btnSubmit.innerHTML;
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Salvando...';
    
    try {
        const response = await fetch('backend/api/perfis.php?action=create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(dados)
        });
        
        const result = await response.json();
        
        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: 'Perfil criado com sucesso!',
                confirmButtonText: 'OK'
            });
            
            // Fechar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalNovoPerfil'));
            modal.hide();
            
            // Limpar formulário
            document.getElementById('formPerfil').reset();
            
            // Recarregar lista
            carregarPerfis();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: result.error || 'Erro ao criar perfil',
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

// Editar perfil
async function editarPerfil(id) {
    try {
        const response = await fetch(`backend/api/perfis.php?action=get&id=${id}`);
        const result = await response.json();
        
        if (result.success) {
            const perfil = result.data;
            
            // Preencher formulário
            document.getElementById('editIdPerfil').value = perfil.id_perfil;
            document.getElementById('editNomePerfil').value = perfil.nome_perfil || '';
            document.getElementById('editCodigoPerfil').value = perfil.codigo_perfil || '';
            document.getElementById('editDescricaoPerfil').value = perfil.descricao || '';
            
            // Limpar checkboxes
            document.querySelectorAll('#tabela-permissoes-edit input[type=checkbox]').forEach(cb => {
                cb.checked = false;
            });
            
                         // Marcar permissões existentes
             if (perfil.permissoes) {
                 perfil.permissoes.forEach(perm => {
                     const checkbox = document.querySelector(`#tabela-permissoes-edit input[data-pagina="${perm.nome_pagina}"][data-perm="create"]`);
                     if (checkbox && perm.permissao_inserir) checkbox.checked = true;
                     
                     const checkbox2 = document.querySelector(`#tabela-permissoes-edit input[data-pagina="${perm.nome_pagina}"][data-perm="read"]`);
                     if (checkbox2 && perm.permissao_visualizar) checkbox2.checked = true;
                     
                     const checkbox3 = document.querySelector(`#tabela-permissoes-edit input[data-pagina="${perm.nome_pagina}"][data-perm="update"]`);
                     if (checkbox3 && perm.permissao_editar) checkbox3.checked = true;
                     
                     const checkbox4 = document.querySelector(`#tabela-permissoes-edit input[data-pagina="${perm.nome_pagina}"][data-perm="delete"]`);
                     if (checkbox4 && perm.permissao_excluir) checkbox4.checked = true;
                 });
             }
            
            // Abrir modal
            const modal = new bootstrap.Modal(document.getElementById('modalEditarPerfil'));
            modal.show();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: result.error || 'Erro ao carregar dados do perfil',
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

// Atualizar perfil
async function atualizarPerfil(event) {
    event.preventDefault();
    
    // Validar campos obrigatórios
    const nomePerfil = document.getElementById('editNomePerfil').value.trim();
    const descricaoPerfil = document.getElementById('editDescricaoPerfil').value.trim();
    const id = document.getElementById('editIdPerfil').value;
    
    if (!nomePerfil || !descricaoPerfil) {
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'Por favor, preencha todos os campos obrigatórios!',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    // Coletar permissões
    const permissoes = {};
    document.querySelectorAll('#tabela-permissoes-edit tbody tr').forEach(tr => {
        const pagina = tr.querySelector('td').innerText.trim();
        permissoes[pagina] = [];
        tr.querySelectorAll('input[type=checkbox]').forEach(cb => {
            if(cb.checked) permissoes[pagina].push(cb.getAttribute('data-perm'));
        });
        if(permissoes[pagina].length === 0) delete permissoes[pagina];
    });
    
    // Coletar dados do formulário
    const dados = {
        nome_perfil: nomePerfil,
        codigo_perfil: document.getElementById('editCodigoPerfil').value.trim() || null,
        descricao: descricaoPerfil,
        permissoes: permissoes
    };
    
    // Mostrar loading
    const btnSubmit = event.target.querySelector('button[type="submit"]');
    const originalText = btnSubmit.innerHTML;
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Atualizando...';
    
    try {
        const response = await fetch(`backend/api/perfis.php?action=update&id=${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(dados)
        });
        
        const result = await response.json();
        
        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: 'Perfil atualizado com sucesso!',
                confirmButtonText: 'OK'
            });
            
            // Fechar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarPerfil'));
            modal.hide();
            
            // Recarregar lista
            carregarPerfis();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: result.error || 'Erro ao atualizar perfil',
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

// Excluir perfil
function excluirPerfil(id) {
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
            excluirPerfilAPI(id);
        }
    });
}

async function excluirPerfilAPI(id) {
    try {
        const response = await fetch(`backend/api/perfis.php?action=delete&id=${id}`, {
            method: 'DELETE'
        });
        const result = await response.json();
        
        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: 'Perfil excluído com sucesso!',
                confirmButtonText: 'OK'
            });
            carregarPerfis();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: result.error || 'Erro ao excluir perfil',
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

// Inicialização da página
document.addEventListener('DOMContentLoaded', function() {
    carregarPerfis();
});

// Funções básicas para os botões
function exportarXLS() {
    Swal.fire({
        icon: 'info',
        title: 'Exportar XLS',
        text: 'Funcionalidade de exportação será implementada em breve.',
        confirmButtonText: 'OK'
    });
}

function imprimir() {
    Swal.fire({
        icon: 'info',
        title: 'Imprimir',
        text: 'Funcionalidade de impressão será implementada em breve.',
        confirmButtonText: 'OK'
    });
}

function duplicarSelecionados() {
    Swal.fire({
        icon: 'info',
        title: 'Duplicar',
        text: 'Funcionalidade de duplicação será implementada em breve.',
        confirmButtonText: 'OK'
    });
}

function abrirModalNovoPerfil() {
    const modal = new bootstrap.Modal(document.getElementById('modalNovoPerfil'));
    modal.show();
}

function limparBusca() {
    document.getElementById('buscaPerfil').value = '';
    // Recarregar lista sem filtros
    carregarPerfis();
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</main>
</body>
</html>
