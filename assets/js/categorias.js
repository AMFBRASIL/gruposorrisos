// Variáveis globais
let paginaAtual = 1;
let confirmModal;
let modalNovaCategoria, modalEditarCategoria, modalVisualizarCategoria;

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    console.log('Inicializando página de categorias...');
    
    // Inicializar modais
    modalNovaCategoria = new bootstrap.Modal(document.getElementById('modalNovaCategoria'));
    modalEditarCategoria = new bootstrap.Modal(document.getElementById('modalEditarCategoria'));
    modalVisualizarCategoria = new bootstrap.Modal(document.getElementById('modalVisualizarCategoria'));
    confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
    
    console.log('Modais inicializados');
    
    // Carregar dados iniciais
    console.log('Carregando dados iniciais...');
    carregarEstatisticas();
    carregarCategorias();
    
    // Event listeners
    document.getElementById('busca').addEventListener('input', debounce(carregarCategorias, 500));
    document.getElementById('filtro-status').addEventListener('change', carregarCategorias);
    
    console.log('Event listeners configurados');
});

// Funções de carregamento
async function carregarEstatisticas() {
    try {
        console.log('Carregando estatísticas...');
        const response = await fetch('backend/api/categorias.php?action=stats', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        console.log('Status da resposta de estatísticas:', response.status);
        console.log('Headers da resposta:', response.headers);
        
        const data = await response.json();
        console.log('Resposta da API de estatísticas:', data);
        
        if (data.success) {
            document.getElementById('total-categorias').textContent = data.total || 0;
            document.getElementById('materiais-categorizados').textContent = data.categorizados || 0;
            document.getElementById('sem-categoria').textContent = data.sem_categoria || 0;
            document.getElementById('categorias-ativas').textContent = data.total || 0;
            
            // Atualizar textos
            const total = data.total || 0;
            const categorizados = data.categorizados || 0;
            const semCategoria = data.sem_categoria || 0;
            
            document.getElementById('texto-total').textContent = `${total} categorias cadastradas`;
            
            const percentual = total > 0 ? Math.round((categorizados / total) * 100) : 0;
            document.getElementById('percentual-categorizados').textContent = `${percentual}% do total`;
            
            console.log('Indicadores atualizados com sucesso');
        } else {
            console.error('API retornou erro:', data.error);
            mostrarErro('Erro ao carregar estatísticas');
        }
    } catch (error) {
        console.error('Erro ao carregar estatísticas:', error);
        mostrarErro('Erro ao carregar estatísticas');
    }
}

async function carregarCategorias() {
    console.log('Carregando categorias...');
    
    const loading = document.getElementById('loading');
    const tabela = document.getElementById('tabela-container');
    const semDados = document.getElementById('sem-dados');
    
    loading.style.display = 'block';
    tabela.style.display = 'none';
    semDados.style.display = 'none';
    
    try {
        const params = new URLSearchParams({
            action: 'list',
            page: paginaAtual,
            limit: 10,
            busca: document.getElementById('busca').value,
            status: document.getElementById('filtro-status').value
        });
        
        const url = `backend/api/categorias.php?${params}`;
        console.log('Fazendo requisição para:', url);
        
        const response = await fetch(url, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        console.log('Status da resposta de categorias:', response.status);
        console.log('Headers da resposta:', response.headers);
        
        const data = await response.json();
        console.log('Resposta da API de categorias:', data);
        
        loading.style.display = 'none';
        
        if (data.success && data.categorias && data.categorias.length > 0) {
            console.log('Renderizando tabela com', data.categorias.length, 'categorias');
            renderizarTabela(data.categorias);
            renderizarPaginacao(data.pagination);
            tabela.style.display = 'block';
        } else {
            console.log('Nenhuma categoria encontrada');
            semDados.style.display = 'block';
        }
    } catch (error) {
        console.error('Erro ao carregar categorias:', error);
        loading.style.display = 'none';
        semDados.style.display = 'block';
        mostrarErro('Erro ao carregar categorias');
    }
}

function renderizarTabela(categorias) {
    console.log('Iniciando renderização da tabela...');
    console.log('Categorias recebidas:', categorias);
    
    const tbody = document.getElementById('categorias-tbody');
    console.log('Elemento tbody encontrado:', tbody);
    
    if (!tbody) {
        console.error('Elemento tbody não encontrado!');
        return;
    }
    
    tbody.innerHTML = '';
    console.log('Tbody limpo');
    
    categorias.forEach((categoria, index) => {
        console.log(`Renderizando categoria ${index + 1}:`, categoria);
        
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <strong>${categoria.nome_categoria}</strong>
            </td>
            <td>${categoria.descricao || '-'}</td>
            <td>
                <span class="badge bg-primary">${categoria.total_materiais || 0} materiais</span>
            </td>
            <td>${getStatusBadge(categoria.ativo)}</td>
            <td>${formatarData(categoria.data_criacao)}</td>
            <td>
                <div class="btn-group btn-group-sm" role="group">
                    <button class="btn btn-outline-primary" title="Visualizar" onclick="abrirModalVisualizarCategoria(${categoria.id_categoria})">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn btn-outline-warning" title="Editar" onclick="abrirModalEditarCategoria(${categoria.id_categoria})">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-outline-danger" title="Excluir" onclick="excluirCategoria(${categoria.id_categoria})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
        console.log(`Categoria ${index + 1} adicionada ao tbody`);
    });
    
    console.log('Renderização da tabela concluída');
}

function getStatusBadge(ativo) {
    if (ativo == 1) {
        return '<span class="badge bg-success">Ativa</span>';
    } else {
        return '<span class="badge bg-secondary">Inativa</span>';
    }
}

function formatarData(data) {
    if (!data) return '-';
    const date = new Date(data);
    return date.toLocaleDateString('pt-BR');
}

function renderizarPaginacao(pagination) {
    console.log('Renderizando paginação:', pagination);
    
    const container = document.getElementById('paginacao');
    
    if (!pagination || pagination.total_pages <= 1) {
        container.style.display = 'none';
        return;
    }
    
    // Verificações de segurança
    if (!pagination.current_page || !pagination.total_pages) {
        console.error('Dados de paginação inválidos:', pagination);
        container.style.display = 'none';
        return;
    }
    
    // Limitar o número de páginas para evitar loops infinitos
    const maxPages = Math.min(pagination.total_pages, 100);
    const currentPage = Math.min(pagination.current_page, maxPages);
    
    // Criar elementos DOM em vez de usar template literals
    const wrapper = document.createElement('div');
    wrapper.className = 'd-flex justify-content-between align-items-center';
    
    // Informações de paginação
    const info = document.createElement('div');
    info.className = 'text-muted';
    info.textContent = `Mostrando ${pagination.start || 1} a ${pagination.end || 10} de ${pagination.total || 0} categorias`;
    wrapper.appendChild(info);
    
    // Lista de paginação
    const ul = document.createElement('ul');
    ul.className = 'pagination pagination-sm mb-0';
    
    // Botão anterior
    if (currentPage > 1) {
        const li = document.createElement('li');
        li.className = 'page-item';
        const a = document.createElement('a');
        a.className = 'page-link';
        a.href = '#';
        a.textContent = 'Anterior';
        a.addEventListener('click', (e) => {
            e.preventDefault();
            mudarPagina(currentPage - 1);
        });
        li.appendChild(a);
        ul.appendChild(li);
    }
    
    // Páginas - limitar a 5 páginas por vez
    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(maxPages, currentPage + 2);
    
    for (let i = startPage; i <= endPage; i++) {
        const li = document.createElement('li');
        li.className = i === currentPage ? 'page-item active' : 'page-item';
        
        const a = document.createElement('a');
        a.className = 'page-link';
        a.href = '#';
        a.textContent = i;
        a.addEventListener('click', (e) => {
            e.preventDefault();
            mudarPagina(i);
        });
        
        li.appendChild(a);
        ul.appendChild(li);
    }
    
    // Botão próximo
    if (currentPage < maxPages) {
        const li = document.createElement('li');
        li.className = 'page-item';
        const a = document.createElement('a');
        a.className = 'page-link';
        a.href = '#';
        a.textContent = 'Próximo';
        a.addEventListener('click', (e) => {
            e.preventDefault();
            mudarPagina(currentPage + 1);
        });
        li.appendChild(a);
        ul.appendChild(li);
    }
    
    wrapper.appendChild(ul);
    
    // Limpar container e adicionar nova paginação
    container.innerHTML = '';
    container.appendChild(wrapper);
    container.style.display = 'block';
    
    console.log('Paginação renderizada com sucesso');
}

function mudarPagina(pagina) {
    paginaAtual = pagina;
    carregarCategorias();
}

// Funções dos modais
function abrirModalNovaCategoria() {
    document.getElementById('formNovaCategoria').reset();
    modalNovaCategoria.show();
}

async function salvarNovaCategoria() {
    const form = document.getElementById('formNovaCategoria');
    const formData = new FormData(form);
    
    const data = {
        nome_categoria: formData.get('nome_categoria'),
        descricao: formData.get('descricao'),
        ativo: formData.get('ativo')
    };
    
    if (!data.nome_categoria.trim()) {
        mostrarErro('Nome da categoria é obrigatório');
        return;
    }
    
    try {
        const response = await fetch('backend/api/categorias.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            modalNovaCategoria.hide();
            mostrarSucesso('Categoria criada com sucesso!');
            carregarCategorias();
            carregarEstatisticas();
        } else {
            mostrarErro(result.error || 'Erro ao criar categoria');
        }
    } catch (error) {
        console.error('Erro ao criar categoria:', error);
        mostrarErro('Erro ao criar categoria');
    }
}

async function abrirModalEditarCategoria(id) {
    try {
        const response = await fetch(`backend/api/categorias.php?action=get&id=${id}`, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            const categoria = data.categoria;
            
            document.getElementById('edit_id_categoria').value = categoria.id_categoria;
            document.getElementById('edit_nome_categoria').value = categoria.nome_categoria;
            document.getElementById('edit_descricao').value = categoria.descricao || '';
            document.getElementById('edit_status').value = categoria.ativo;
            
            modalEditarCategoria.show();
        } else {
            mostrarErro('Erro ao carregar dados da categoria');
        }
    } catch (error) {
        console.error('Erro ao carregar categoria:', error);
        mostrarErro('Erro ao carregar categoria');
    }
}

async function salvarEditarCategoria() {
    const form = document.getElementById('formEditarCategoria');
    const formData = new FormData(form);
    
    const data = {
        id: formData.get('id_categoria'),
        nome_categoria: formData.get('nome_categoria'),
        descricao: formData.get('descricao'),
        ativo: formData.get('ativo')
    };
    
    if (!data.nome_categoria.trim()) {
        mostrarErro('Nome da categoria é obrigatório');
        return;
    }
    
    try {
        const response = await fetch('backend/api/categorias.php', {
            method: 'PUT',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            modalEditarCategoria.hide();
            mostrarSucesso('Categoria atualizada com sucesso!');
            carregarCategorias();
            carregarEstatisticas();
        } else {
            mostrarErro(result.error || 'Erro ao atualizar categoria');
        }
    } catch (error) {
        console.error('Erro ao atualizar categoria:', error);
        mostrarErro('Erro ao atualizar categoria');
    }
}

async function abrirModalVisualizarCategoria(id) {
    try {
        const response = await fetch(`backend/api/categorias.php?action=get&id=${id}`, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            const categoria = data.categoria;
            
            document.getElementById('view_nome_categoria').textContent = categoria.nome_categoria;
            document.getElementById('view_descricao').textContent = categoria.descricao || 'Sem descrição';
            document.getElementById('view_status').textContent = categoria.ativo == 1 ? 'Ativa' : 'Inativa';
            document.getElementById('view_data_criacao').textContent = formatarData(categoria.data_criacao);
            document.getElementById('view_data_atualizacao').textContent = formatarData(categoria.data_atualizacao);
            document.getElementById('view_total_materiais').textContent = categoria.total_materiais || 0;
            
            // Armazenar ID para edição
            document.getElementById('edit_id_categoria').value = categoria.id_categoria;
            
            modalVisualizarCategoria.show();
        } else {
            mostrarErro('Erro ao carregar dados da categoria');
        }
    } catch (error) {
        console.error('Erro ao carregar categoria:', error);
        mostrarErro('Erro ao carregar categoria');
    }
}

function editarCategoriaAtual() {
    modalVisualizarCategoria.hide();
    const id = document.getElementById('edit_id_categoria').value;
    abrirModalEditarCategoria(id);
}

function excluirCategoria(id) {
    Swal.fire({
        title: 'Confirmar exclusão',
        text: 'Tem certeza que deseja excluir esta categoria?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            confirmarExclusao(id);
        }
    });
}

async function confirmarExclusao(id) {
    try {
        const response = await fetch('backend/api/categorias.php', {
            method: 'DELETE',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        });
        
        const data = await response.json();
        
        if (data.success) {
            mostrarSucesso('Categoria excluída com sucesso!');
            carregarCategorias();
            carregarEstatisticas();
        } else {
            mostrarErro(data.error || 'Erro ao excluir categoria');
        }
    } catch (error) {
        console.error('Erro ao excluir categoria:', error);
        mostrarErro('Erro ao excluir categoria');
    }
}

// Funções auxiliares
function toggleFiltros() {
    const filtros = document.getElementById('filtrosAvancados');
    filtros.style.display = filtros.style.display === 'none' ? 'block' : 'none';
}

function limparFiltros() {
    document.getElementById('busca').value = '';
    document.getElementById('filtro-status').value = '';
    paginaAtual = 1;
    carregarCategorias();
}

function exportarXLS() {
    Swal.fire({
        title: 'Exportar Categorias',
        text: 'Funcionalidade de exportação em desenvolvimento',
        icon: 'info',
        confirmButtonText: 'OK'
    });
}

function imprimir() {
    window.print();
}

// Funções de notificação
function mostrarSucesso(mensagem) {
    Swal.fire({
        title: 'Sucesso!',
        text: mensagem,
        icon: 'success',
        confirmButtonText: 'OK'
    });
}

function mostrarErro(mensagem) {
    Swal.fire({
        title: 'Erro!',
        text: mensagem,
        icon: 'error',
        confirmButtonText: 'OK'
    });
}

// Utilitários
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function duplicarSelecionados() {
    // Verificar se há categorias selecionadas
    const checkboxes = document.querySelectorAll('input[name="categoria_selecionada"]:checked');
    
    if (checkboxes.length === 0) {
        Swal.fire({
            title: 'Nenhuma categoria selecionada',
            text: 'Selecione pelo menos uma categoria para duplicar',
            icon: 'info',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    Swal.fire({
        title: 'Confirmar duplicação',
        text: `Deseja duplicar ${checkboxes.length} categoria(s) selecionada(s)?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sim, duplicar!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Implementar lógica de duplicação aqui
            Swal.fire({
                title: 'Funcionalidade em desenvolvimento',
                text: 'A funcionalidade de duplicação será implementada em breve',
                icon: 'info',
                confirmButtonText: 'OK'
            });
        }
    });
} 