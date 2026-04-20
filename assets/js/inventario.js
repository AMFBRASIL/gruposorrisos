// Variáveis globais
let paginaAtual = 1;
let inventariosSelecionados = [];
let confirmModal;

// Variáveis de paginação do modal de contagem
let paginaAtualContagem = 1;
let itensPorPaginaContagem = 50;
let totalItensContagem = 0;
let totalPagesContagem = 1;

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
    
    // Carregar dados iniciais
    carregarFilialSelecionada();
    carregarEstatisticas();
    carregarInventarios();
    
    // Verificar se há mudança na filial selecionada (para sincronizar com index.php)
    window.addEventListener('storage', function(e) {
        if (e.key === 'filialSelecionada') {
            console.log('🔄 Filial alterada em outra aba, recarregando dados...');
            carregarFilialSelecionada();
            carregarInventarios();
            carregarEstatisticas();
        }
    });
});

// Função para carregar e exibir a filial selecionada
async function carregarFilialSelecionada() {
    console.log('🔍 carregarFilialSelecionada: Iniciando...');
    
    const filialId = localStorage.getItem('filialSelecionada');
    const indicator = document.getElementById('filial-indicator');
    const filialNome = document.getElementById('filial-nome');
    
    console.log('🔍 carregarFilialSelecionada: Filial ID do localStorage:', filialId);
    console.log('🔍 carregarFilialSelecionada: Elemento indicator:', indicator);
    console.log('🔍 carregarFilialSelecionada: Elemento filialNome:', filialNome);
    
    if (filialId) {
        try {
            console.log('🔍 carregarFilialSelecionada: Buscando informações da filial...');
            
            // Buscar informações da filial na API
            const response = await fetch(`backend/api/filiais.php?action=list`);
            console.log('🔍 carregarFilialSelecionada: Resposta da API:', response.status);
            
            const data = await response.json();
            console.log('🔍 carregarFilialSelecionada: Dados da API:', data);
            
            if (data.success && data.filiais) {
                const filial = data.filiais.find(f => f.id == filialId);
                console.log('🔍 carregarFilialSelecionada: Filial encontrada:', filial);
                
                if (filial) {
                    filialNome.textContent = filial.nome;
                    indicator.style.display = 'flex';
                    indicator.className = 'alert alert-info d-flex align-items-center mb-3';
                    console.log('✅ Filial exibida:', filial.nome);
                } else {
                    console.log('⚠️ Filial não encontrada na lista');
                    indicator.style.display = 'none';
                }
            } else {
                console.log('❌ API não retornou sucesso ou não tem filiais');
                indicator.style.display = 'none';
            }
        } catch (error) {
            console.error('❌ Erro ao carregar filial:', error);
            indicator.style.display = 'none';
        }
    } else {
        // Nenhuma filial selecionada
        console.log('⚠️ Nenhuma filial selecionada no localStorage');
        filialNome.textContent = 'Nenhuma filial selecionada';
        indicator.style.display = 'flex';
        indicator.className = 'alert alert-warning d-flex align-items-center mb-3';
        console.log('⚠️ Nenhuma filial selecionada');
    }
}

// Carregar categorias para o filtro do modal de contagem
async function carregarCategoriasContagem() {
    try {
        console.log('📋 Carregando categorias para contagem...');
        const response = await fetch('api/materiais.php?action=categorias', {
            credentials: 'same-origin'
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('📋 Resposta da API de categorias:', data);
        
        const selectCategoria = document.getElementById('filtro-categoria-contagem');
        if (!selectCategoria) {
            console.error('❌ Elemento filtro-categoria-contagem não encontrado');
            return;
        }
        
        selectCategoria.innerHTML = '<option value="">Todas as Categorias</option>';
        
        if (data.success && data.data && Array.isArray(data.data)) {
            let categoriasAdicionadas = 0;
            data.data.forEach(categoria => {
                // Aceitar categorias ativas (1, '1', true) ou se não tiver campo ativo
                if (!categoria.hasOwnProperty('ativo') || categoria.ativo === 1 || categoria.ativo === '1' || categoria.ativo === true) {
                    const option = document.createElement('option');
                    option.value = categoria.id_categoria;
                    option.textContent = categoria.nome_categoria || categoria.nome || 'Sem nome';
                    selectCategoria.appendChild(option);
                    categoriasAdicionadas++;
                }
            });
            console.log(`✅ ${categoriasAdicionadas} categorias carregadas para contagem`);
        } else {
            console.warn('⚠️ Resposta da API não contém dados válidos:', data);
        }
    } catch (error) {
        console.error('❌ Erro ao carregar categorias para contagem:', error);
        // Manter pelo menos a opção padrão
        const selectCategoria = document.getElementById('filtro-categoria-contagem');
        if (selectCategoria) {
            selectCategoria.innerHTML = '<option value="">Todas as Categorias</option>';
        }
    }
}

// Carregar estatísticas
async function carregarEstatisticas() {
    try {
        const params = new URLSearchParams({
            action: 'stats'
        });
        
        // Obter filial do localStorage (selecionada no index.php)
        const filialSelecionada = localStorage.getItem('filialSelecionada');
        if (filialSelecionada) {
            params.append('id_filial', filialSelecionada);
        }
        
        const response = await fetch(`api/inventario.php?${params}`, {
            credentials: 'same-origin'
        });
        const data = await response.json();
        
        if (data.success) {
            const stats = data.data;
            document.getElementById('total-materiais').textContent = stats.total;
            document.getElementById('em-estoque').textContent = stats.em_andamento;
            document.getElementById('estoque-baixo').textContent = stats.finalizados;
            document.getElementById('sem-estoque').textContent = stats.cancelados;
            
            // Calcular percentual
            const percentual = stats.total > 0 ? Math.round((stats.em_andamento / stats.total) * 100) : 0;
            document.getElementById('percentual-estoque').textContent = `${percentual}% do total`;
        }
    } catch (error) {
        console.error('Erro ao carregar estatísticas:', error);
    }
}

// Carregar inventários
async function carregarInventarios() {
    try {
        const params = new URLSearchParams({
            action: 'list',
            page: paginaAtual,
            limit: 10
        });
        
        // Obter filial do localStorage (selecionada no index.php)
        const filialSelecionada = localStorage.getItem('filialSelecionada');
        if (filialSelecionada) {
            params.append('id_filial', filialSelecionada);
        }
        
        const response = await fetch(`api/inventario.php?${params}`, {
            credentials: 'same-origin'
        });
        const data = await response.json();
        
        if (data.inventarios) {
            renderizarTabela(data.inventarios);
            renderizarPaginacao(data.total_pages, data.total);
        }
    } catch (error) {
        console.error('Erro ao carregar inventários:', error);
        mostrarErro('Erro ao carregar inventários');
    }
}

// Renderizar tabela
function renderizarTabela(inventarios) {
    const tbody = document.querySelector('#tabelaInventarios tbody');
    tbody.innerHTML = '';
    
    inventarios.forEach(inventario => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <div class="form-check">
                    <input class="form-check-input inventario-checkbox" type="checkbox" value="${inventario.id_inventario}" onchange="updateInventariosSelecionados()">
                </div>
            </td>
            <td><strong>${inventario.numero_inventario}</strong></td>
            <td>${getStatusBadge(inventario.status)}</td>
            <td>${inventario.nome_filial}</td>
            <td>${inventario.nome_responsavel}</td>
            <td>
                <div class="progress-info">
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-success" style="width: ${calcularProgresso(inventario)}%"></div>
                    </div>
                    <small class="text-muted">${inventario.itens_contados || 0}/${inventario.total_itens || 0} itens</small>
                </div>
            </td>
            <td>${formatarMoeda(inventario.valor_total_sistema || 0)}</td>
            <td>${formatarData(inventario.data_inicio)}</td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="visualizarInventario(${inventario.id_inventario})" title="Visualizar">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn btn-outline-success" onclick="abrirModalContagem(${inventario.id_inventario})" title="Contagem" ${inventario.status !== 'em_andamento' ? 'disabled' : ''}>
                        <i class="bi bi-clipboard-check"></i>
                    </button>
                    <button class="btn btn-outline-warning" onclick="editarInventario(${inventario.id_inventario})" title="Editar" ${inventario.status !== 'em_andamento' ? 'disabled' : ''}>
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-outline-danger" onclick="confirmarExclusao(${inventario.id_inventario})" title="Excluir" ${inventario.status !== 'em_andamento' ? 'disabled' : ''}>
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Renderizar paginação
function renderizarPaginacao(totalPages, total) {
    const pagination = document.getElementById('paginacao');
    pagination.innerHTML = '';
    
    if (totalPages <= 1) return;
    
    // Informações
    const info = document.createElement('div');
    info.className = 'pagination-info';
    info.innerHTML = `Mostrando página ${paginaAtual} de ${totalPages} (${total} registros)`;
    pagination.appendChild(info);
    
    // Navegação
    const nav = document.createElement('nav');
    nav.setAttribute('aria-label', 'Navegação de páginas');
    
    const ul = document.createElement('ul');
    ul.className = 'pagination pagination-sm mb-0';
    
    // Botão anterior
    if (paginaAtual > 1) {
        const li = document.createElement('li');
        li.className = 'page-item';
        li.innerHTML = `<a class="page-link" href="#" onclick="irParaPagina(${paginaAtual - 1})">Anterior</a>`;
        ul.appendChild(li);
    }
    
    // Páginas
    const startPage = Math.max(1, paginaAtual - 2);
    const endPage = Math.min(totalPages, paginaAtual + 2);
    
    for (let i = startPage; i <= endPage; i++) {
        const li = document.createElement('li');
        li.className = `page-item ${i === paginaAtual ? 'active' : ''}`;
        li.innerHTML = `<a class="page-link" href="#" onclick="irParaPagina(${i})">${i}</a>`;
        ul.appendChild(li);
    }
    
    // Botão próximo
    if (paginaAtual < totalPages) {
        const li = document.createElement('li');
        li.className = 'page-item';
        li.innerHTML = `<a class="page-link" href="#" onclick="irParaPagina(${paginaAtual + 1})">Próximo</a>`;
        ul.appendChild(li);
    }
    
    nav.appendChild(ul);
    pagination.appendChild(nav);
}

// Ir para página
function irParaPagina(pagina) {
    paginaAtual = pagina;
    carregarInventarios();
}

// Abrir modal novo inventário
async function abrirModalNovoInventario() {
    document.getElementById('formNovoInventario').reset();
    
    // Restaurar estado do botão ao abrir o modal
    const btnCriar = document.getElementById('btnCriarInventario');
    const btnIcon = document.getElementById('btnCriarInventarioIcon');
    const btnText = document.getElementById('btnCriarInventarioText');
    const btnCancelar = document.getElementById('btnCancelarInventario');
    
    if (btnCriar) {
        btnCriar.disabled = false;
        if (btnIcon) btnIcon.innerHTML = '<i class="bi bi-check-lg me-1"></i>';
        if (btnText) btnText.textContent = 'Criar Inventário';
    }
    if (btnCancelar) {
        btnCancelar.disabled = false;
    }
    
    // Atualizar informações da filial e horário
    await atualizarInformacoesModalInventario();
    
    document.getElementById('modalNovoInventario').classList.add('show');
    document.getElementById('modalNovoInventario').style.display = 'block';
}

// Atualizar informações da filial e horário no modal
async function atualizarInformacoesModalInventario() {
    const filialNomeElement = document.getElementById('modal-filial-nome');
    const horarioCriacaoElement = document.getElementById('modal-horario-criacao');
    
    // Atualizar horário de criação
    const agora = new Date();
    const horarioFormatado = agora.toLocaleString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
    horarioCriacaoElement.textContent = horarioFormatado;
    
    // Buscar nome da filial
    const filialId = localStorage.getItem('filialSelecionada');
    
    if (filialId) {
        try {
            filialNomeElement.textContent = 'Carregando...';
            
            const response = await fetch(`backend/api/filiais.php?action=list`);
            const data = await response.json();
            
            if (data.success && data.filiais) {
                const filial = data.filiais.find(f => f.id == filialId || f.id_filial == filialId);
                if (filial) {
                    const nomeFilial = filial.nome || filial.nome_filial || 'Filial não encontrada';
                    filialNomeElement.textContent = nomeFilial;
                    filialNomeElement.className = 'ms-2';
                    console.log('✅ Filial carregada no modal:', nomeFilial);
                } else {
                    filialNomeElement.textContent = 'Filial não encontrada';
                    filialNomeElement.className = 'ms-2 text-danger';
                }
            } else {
                // Tentar usar a filial do usuário logado
                const filialUsuario = getCurrentUserFilialId();
                if (filialUsuario) {
                    filialNomeElement.textContent = `Filial ID: ${filialUsuario}`;
                } else {
                    filialNomeElement.textContent = 'Nenhuma filial selecionada';
                    filialNomeElement.className = 'ms-2 text-warning';
                }
            }
        } catch (error) {
            console.error('❌ Erro ao carregar filial no modal:', error);
            filialNomeElement.textContent = 'Erro ao carregar filial';
            filialNomeElement.className = 'ms-2 text-danger';
        }
    } else {
        // Usar filial do usuário logado
        const filialUsuario = getCurrentUserFilialId();
        if (filialUsuario) {
            filialNomeElement.textContent = `Filial ID: ${filialUsuario}`;
        } else {
            filialNomeElement.textContent = 'Nenhuma filial selecionada';
            filialNomeElement.className = 'ms-2 text-warning';
        }
    }
}

// Salvar novo inventário
async function salvarNovoInventario() {
    // Obter referências aos botões
    const btnCriar = document.getElementById('btnCriarInventario');
    const btnIcon = document.getElementById('btnCriarInventarioIcon');
    const btnText = document.getElementById('btnCriarInventarioText');
    const btnCancelar = document.getElementById('btnCancelarInventario');
    const modalProcessamentoElement = document.getElementById('modalProcessamento');
    const modalProcessamento = new bootstrap.Modal(modalProcessamentoElement);
    
    // Verificar se o botão já está desabilitado (evitar múltiplos cliques)
    if (btnCriar.disabled) {
        console.log('⚠️ Botão já está desabilitado, ignorando clique duplicado');
        return;
    }
    
    // Função auxiliar para fechar modal e restaurar botões
    const fecharModalERestaurar = () => {
        try {
            // Fechar modal de processamento usando múltiplos métodos para garantir
            if (modalProcessamentoElement) {
                // Tentar obter instância do modal
                let modalInstance = bootstrap.Modal.getInstance(modalProcessamentoElement);
                
                // Se não houver instância, criar uma nova
                if (!modalInstance) {
                    modalInstance = new bootstrap.Modal(modalProcessamentoElement, {
                        backdrop: true,
                        keyboard: true
                    });
                }
                
                // Fechar o modal
                if (modalInstance) {
                    modalInstance.hide();
                }
                
                // Aguardar um pouco e forçar remoção caso necessário
                setTimeout(() => {
                    // Forçar remoção de classes e estilos
                    modalProcessamentoElement.classList.remove('show');
                    modalProcessamentoElement.style.display = 'none';
                    modalProcessamentoElement.setAttribute('aria-hidden', 'true');
                    modalProcessamentoElement.removeAttribute('aria-modal');
                    modalProcessamentoElement.removeAttribute('role');
                    
                    // Remover classes do body
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                    
                    // Remover todos os backdrops
                    const backdrops = document.querySelectorAll('.modal-backdrop');
                    backdrops.forEach(backdrop => backdrop.remove());
                }, 100);
            }
        } catch (e) {
            console.error('Erro ao fechar modal:', e);
            // Forçar remoção mesmo em caso de erro
            if (modalProcessamentoElement) {
                modalProcessamentoElement.classList.remove('show');
                modalProcessamentoElement.style.display = 'none';
                document.body.classList.remove('modal-open');
                const backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops.forEach(backdrop => backdrop.remove());
            }
        }
        
        // Restaurar botões
        btnCriar.disabled = false;
        if (btnCancelar) btnCancelar.disabled = false;
        if (btnIcon) btnIcon.innerHTML = '<i class="bi bi-check-lg me-1"></i>';
        if (btnText) btnText.textContent = 'Criar Inventário';
    };
    
    try {
        // Obter filial do localStorage (selecionada no index.php)
        const filialSelecionada = localStorage.getItem('filialSelecionada');
        const idFilial = filialSelecionada ? parseInt(filialSelecionada) : getCurrentUserFilialId();
        
        const formData = {
            id_filial: idFilial,
            id_usuario_responsavel: getCurrentUserId(),
            observacoes: document.getElementById('observacoes').value || null
        };
        
        // Desabilitar botões e mostrar indicador de carregamento
        btnCriar.disabled = true;
        if (btnCancelar) btnCancelar.disabled = true;
        if (btnIcon) btnIcon.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>';
        if (btnText) btnText.textContent = 'Processando...';
        
        // Mostrar modal de processamento
        modalProcessamento.show();
        
        // Atualizar mensagem do modal
        document.getElementById('processamento-mensagem').textContent = 'Criando inventário e processando produtos...';
        document.getElementById('processamento-detalhes').textContent = 'Isso pode levar alguns minutos dependendo da quantidade de produtos.';
        
        console.log('💾 Iniciando criação do inventário...');
        console.log('📦 Dados:', formData);
        
        // Criar um AbortController para timeout
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 300000); // 5 minutos de timeout
        
        const response = await fetch('api/inventario.php?action=create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData),
            credentials: 'same-origin',
            signal: controller.signal
        });
        
        clearTimeout(timeoutId);
        
        // Verificar se a resposta é OK
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        // Tentar parsear JSON
        let data;
        try {
            const text = await response.text();
            if (!text) {
                throw new Error('Resposta vazia do servidor');
            }
            data = JSON.parse(text);
        } catch (parseError) {
            console.error('Erro ao parsear JSON:', parseError);
            throw new Error('Resposta inválida do servidor');
        }
        
        // Fechar modal de processamento
        fecharModalERestaurar();
        
        if (data.success) {
            fecharModalNovoInventario();
            carregarInventarios();
            carregarEstatisticas();
            mostrarSucesso('Inventário criado com sucesso!');
        } else {
            mostrarErro('Erro ao criar inventário: ' + (data.error || 'Erro desconhecido'));
        }
    } catch (error) {
        console.error('Erro ao criar inventário:', error);
        
        // Fechar modal de processamento em caso de erro
        fecharModalERestaurar();
        
        // Mostrar erro específico
        let mensagemErro = 'Erro ao criar inventário. Verifique sua conexão e tente novamente.';
        
        if (error.name === 'AbortError') {
            mensagemErro = 'A requisição demorou muito tempo. O inventário pode ter sido criado. Verifique a lista de inventários.';
        } else if (error.message) {
            mensagemErro = error.message;
        }
        
        mostrarErro(mensagemErro);
    }
}

// Fechar modal novo inventário
function fecharModalNovoInventario() {
    // Restaurar botão ao fechar o modal
    const btnCriar = document.getElementById('btnCriarInventario');
    const btnIcon = document.getElementById('btnCriarInventarioIcon');
    const btnText = document.getElementById('btnCriarInventarioText');
    const btnCancelar = document.getElementById('btnCancelarInventario');
    
    if (btnCriar) {
        btnCriar.disabled = false;
        if (btnIcon) btnIcon.innerHTML = '<i class="bi bi-check-lg me-1"></i>';
        if (btnText) btnText.textContent = 'Criar Inventário';
    }
    if (btnCancelar) {
        btnCancelar.disabled = false;
    }
    
    document.getElementById('modalNovoInventario').classList.remove('show');
    document.getElementById('modalNovoInventario').style.display = 'none';
}

// Editar inventário
async function editarInventario(id) {
    try {
        const response = await fetch(`api/inventario.php?action=get&id=${id}`, {
            credentials: 'same-origin'
        });
        const data = await response.json();
        
        if (data.success) {
            const inventario = data.data;
            
            // Preencher modal de edição
            document.getElementById('edit-id_inventario').value = inventario.id_inventario;
            document.getElementById('edit-observacoes').value = inventario.observacoes || '';
            document.getElementById('edit-status').value = inventario.status;
            
            const modal = new bootstrap.Modal(document.getElementById('modalEditarInventario'));
            modal.show();
        } else {
            mostrarErro('Erro ao carregar inventário: ' + (data.error || 'Erro desconhecido'));
        }
    } catch (error) {
        console.error('Erro ao carregar inventário:', error);
        mostrarErro('Erro ao carregar inventário');
    }
}

// Salvar edição do inventário
async function salvarEdicaoInventario() {
    try {
        const idInventario = document.getElementById('edit-id_inventario').value;
        const observacoes = document.getElementById('edit-observacoes').value;
        const status = document.getElementById('edit-status').value;
        
        const formData = {
            id_inventario: idInventario,
            observacoes: observacoes || null,
            status: status
        };
        
        const response = await fetch('api/inventario.php?action=update', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData),
            credentials: 'same-origin'
        });
        
        const data = await response.json();
        
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarInventario'));
            modal.hide();
            carregarInventarios();
            carregarEstatisticas();
            mostrarSucesso('Inventário atualizado com sucesso!');
        } else {
            mostrarErro('Erro ao atualizar inventário: ' + (data.error || 'Erro desconhecido'));
        }
    } catch (error) {
        console.error('Erro ao atualizar inventário:', error);
        mostrarErro('Erro ao atualizar inventário');
    }
}

// Visualizar inventário
async function visualizarInventario(id) {
    try {
        const response = await fetch(`api/inventario.php?action=get&id=${id}`, {
            credentials: 'same-origin'
        });
        const data = await response.json();
        
        if (data.success) {
            const inventario = data.data;
            
            // Preencher modal
            document.getElementById('view-numero_inventario').textContent = inventario.numero_inventario;
            document.getElementById('view-status').textContent = getStatusText(inventario.status);
            document.getElementById('view-filial').textContent = inventario.nome_filial;
            document.getElementById('view-responsavel').textContent = inventario.nome_responsavel;
            document.getElementById('view-data_inicio').textContent = formatarDataHora(inventario.data_inicio);
            document.getElementById('view-data_fim').textContent = inventario.data_fim ? formatarDataHora(inventario.data_fim) : '-';
            document.getElementById('view-observacoes').textContent = inventario.observacoes || '-';
            
            const modal = new bootstrap.Modal(document.getElementById('modalVisualizarInventario'));
            modal.show();
        } else {
            mostrarErro('Erro ao carregar inventário: ' + (data.error || 'Erro desconhecido'));
        }
    } catch (error) {
        console.error('Erro ao visualizar inventário:', error);
        mostrarErro('Erro ao visualizar inventário');
    }
}

// Abrir modal de contagem
async function abrirModalContagem(idInventario) {
    try {
        console.log('🔍 Abrindo modal de contagem para inventário:', idInventario);
        console.log('✅ Versão do inventario.js: 2.0 - Ajuste em Lote e Busca implementados');
        
        // Resetar paginação
        paginaAtualContagem = 1;
        
        // Carregar preferência de itens por página do localStorage
        const savedLimit = localStorage.getItem('inventario_contagem_itens_por_pagina');
        if (savedLimit) {
            itensPorPaginaContagem = parseInt(savedLimit);
        }
        
        // Configurar o seletor
        const selectItens = document.getElementById('itens-por-pagina-contagem');
        if (selectItens) {
            selectItens.value = itensPorPaginaContagem;
        }
        
        document.getElementById('idInventarioContagem').value = idInventario;
        
        // Carregar categorias para o filtro
        await carregarCategoriasContagem();
        
        // Limpar filtros ao abrir
        document.getElementById('filtro-categoria-contagem').value = '';
        document.getElementById('busca-material-contagem').value = '';
        
        // Carregar itens com paginação
        await carregarItensContagem(idInventario);
        
        // Inicializar botões (serão atualizados após carregar itens)
        atualizarBotaoAjustarLote(0);
        verificarMateriaisNovos(idInventario);
        
        // Mostrar modal
        const modal = new bootstrap.Modal(document.getElementById('modalContagem'));
        modal.show();
    } catch (error) {
        console.error('❌ Erro ao abrir modal de contagem:', error);
        mostrarErro('Erro ao abrir modal de contagem');
    }
}

// Carregar itens para contagem com paginação
async function carregarItensContagem(idInventario) {
    try {
        // Mostrar loading
        const tbody = document.querySelector('#tabelaContagem tbody');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        <i class="bi bi-hourglass-split fs-1"></i>
                        <div class="mt-2">Carregando itens...</div>
                    </td>
                </tr>
            `;
        }
        
        // Obter filtros
        const categoriaFiltro = document.getElementById('filtro-categoria-contagem');
        const idCategoria = categoriaFiltro ? categoriaFiltro.value : '';
        
        const buscaMaterial = document.getElementById('busca-material-contagem');
        const termoBuscaMaterial = buscaMaterial ? buscaMaterial.value.trim() : '';
        
        const params = new URLSearchParams({
            action: 'itens',
            id_inventario: idInventario,
            page: paginaAtualContagem,
            limit: itensPorPaginaContagem
        });
        
        // Adicionar filtro de categoria se selecionado
        if (idCategoria) {
            params.append('id_categoria', idCategoria);
        }
        
        // Buscar pelo mesmo termo em nome OU codigo do material
        if (termoBuscaMaterial) {
            params.append('termo_busca', termoBuscaMaterial);
        }
        
        const url = `api/inventario.php?${params}`;
        console.log('📡 Buscando itens:', url);
        
        const response = await fetch(url, {
            credentials: 'same-origin'
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        console.log('📥 Dados recebidos:', data);
        
        // Verificar se a resposta tem dados
        if (data.data && Array.isArray(data.data)) {
            // Atualizar informações de paginação
            totalItensContagem = data.total || data.data.length;
            totalPagesContagem = data.total_pages || Math.ceil(totalItensContagem / itensPorPaginaContagem);
            
            // Renderizar itens
            renderizarItensContagem(data.data);
            
            // Inicializar tooltips do Bootstrap após renderizar
            inicializarTooltips();
            
            // Verificar materiais novos após carregar itens
            const idInventario = document.getElementById('idInventarioContagem')?.value;
            if (idInventario) {
                verificarMateriaisNovos(idInventario);
            }
            
            // Renderizar informações de paginação
            renderizarInfoPaginacaoContagem();
            
            // Renderizar controles de paginação
            renderizarPaginacaoContagem();
        } else {
            console.error('❌ Formato de dados inválido:', data);
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        <i class="bi bi-exclamation-triangle fs-1"></i>
                        <div class="mt-2">Nenhum item encontrado para este inventário</div>
                    </td>
                </tr>
            `;
        }
    } catch (error) {
        console.error('❌ Erro ao carregar itens:', error);
        mostrarErro('Erro ao carregar itens do inventário');
        
        const tbody = document.querySelector('#tabelaContagem tbody');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-danger py-4">
                        <i class="bi bi-exclamation-triangle fs-1"></i>
                        <div class="mt-2">Erro ao carregar itens</div>
                    </td>
                </tr>
            `;
        }
    }
}

// Aplicar filtro de categoria na contagem
function aplicarFiltroCategoriaContagem() {
    paginaAtualContagem = 1;
    const idInventario = document.getElementById('idInventarioContagem').value;
    if (idInventario) {
        carregarItensContagem(idInventario);
    }
}

// Limpar filtro de categoria na contagem
function limparFiltroCategoriaContagem() {
    const selectCategoria = document.getElementById('filtro-categoria-contagem');
    if (selectCategoria) {
        selectCategoria.value = '';
        aplicarFiltroCategoriaContagem();
    }
}

// Aplicar busca por nome/codigo do material na contagem
function aplicarBuscaMaterialContagem() {
    // Usar debounce para não fazer muitas requisições enquanto o usuário digita
    clearTimeout(window.buscaMaterialTimeout);
    window.buscaMaterialTimeout = setTimeout(() => {
        paginaAtualContagem = 1;
        const idInventario = document.getElementById('idInventarioContagem').value;
        if (idInventario) {
            carregarItensContagem(idInventario);
        }
    }, 500); // Aguardar 500ms após o usuário parar de digitar
}

// Limpar busca por nome/codigo do material na contagem
function limparBuscaMaterialContagem() {
    const inputBusca = document.getElementById('busca-material-contagem');
    if (inputBusca) {
        inputBusca.value = '';
        aplicarBuscaMaterialContagem();
    }
}

// Imprimir/gerar PDF da listagem completa de contagem (respeitando filtros atuais)
async function imprimirContagemItens() {
    try {
        const idInventario = document.getElementById('idInventarioContagem')?.value;
        if (!idInventario) {
            mostrarErro('ID do inventário não encontrado');
            return;
        }

        Swal.fire({
            title: 'Preparando impressão...',
            text: 'Carregando todos os itens da contagem',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        const itens = await buscarTodosItensContagem(idInventario);
        if (!Array.isArray(itens) || itens.length === 0) {
            Swal.fire({
                icon: 'info',
                title: 'Sem itens para imprimir',
                text: 'Não há itens na listagem atual.'
            });
            return;
        }

        const responseInventario = await fetch(`api/inventario.php?action=get&id=${idInventario}`, {
            credentials: 'same-origin'
        });
        const dadosInventario = await responseInventario.json();
        const inventario = dadosInventario?.data || {};

        const busca = document.getElementById('busca-material-contagem')?.value?.trim() || '';
        const categoriaTexto = document.getElementById('filtro-categoria-contagem')?.selectedOptions?.[0]?.textContent || 'Todas';
        const dataHora = new Date().toLocaleString('pt-BR');

        const linhas = itens.map(item => {
            const qtdSistema = parseFloat(item.quantidade_sistema || 0);
            const qtdContada = item.quantidade_contada !== null && item.quantidade_contada !== undefined && item.quantidade_contada !== ''
                ? parseFloat(item.quantidade_contada)
                : null;
            const divergencia = qtdContada === null ? '-' : (qtdContada - qtdSistema).toFixed(3);

            return `
                <tr>
                    <td>${item.codigo_material || 'N/A'}</td>
                    <td>${item.nome_material || 'N/A'}</td>
                    <td class="num">${qtdSistema.toFixed(3)} ${item.unidade_material || 'un'}</td>
                    <td class="num">${qtdContada === null ? '-' : `${qtdContada.toFixed(3)} ${item.unidade_material || 'un'}`}</td>
                    <td class="num">${divergencia}</td>
                    <td>${item.status_item || 'pendente'}</td>
                </tr>
            `;
        }).join('');

        const html = `
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Contagem de Itens - Inventário ${inventario.numero_inventario || idInventario}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; color: #222; }
        h1 { margin: 0 0 8px; font-size: 20px; }
        .meta { margin-bottom: 16px; font-size: 12px; color: #444; }
        .meta div { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; }
        th { background: #f5f5f5; text-align: left; }
        .num { text-align: right; white-space: nowrap; }
        @media print {
            body { margin: 8mm; }
            tr, td, th { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <h1>Contagem de Itens - Inventário ${inventario.numero_inventario || idInventario}</h1>
    <div class="meta">
        <div><strong>Filial:</strong> ${inventario.nome_filial || '-'}</div>
        <div><strong>Responsável:</strong> ${inventario.nome_responsavel || '-'}</div>
        <div><strong>Categoria (filtro):</strong> ${categoriaTexto}</div>
        <div><strong>Busca (filtro):</strong> ${busca || 'Sem filtro'}</div>
        <div><strong>Total de itens:</strong> ${itens.length}</div>
        <div><strong>Gerado em:</strong> ${dataHora}</div>
    </div>
    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Material</th>
                <th>Qtd. Sistema</th>
                <th>Qtd. Contada</th>
                <th>Divergência</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            ${linhas}
        </tbody>
    </table>
</body>
</html>`;

        Swal.close();
        const win = window.open('', '_blank');
        if (!win) {
            mostrarErro('Não foi possível abrir janela de impressão. Verifique bloqueador de pop-up.');
            return;
        }
        win.document.open();
        win.document.write(html);
        win.document.close();
        win.focus();
        win.print();
    } catch (error) {
        console.error('Erro ao imprimir contagem:', error);
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'Não foi possível gerar a impressão/PDF da contagem.'
        });
    }
}

async function buscarTodosItensContagem(idInventario) {
    const categoriaFiltro = document.getElementById('filtro-categoria-contagem');
    const idCategoria = categoriaFiltro ? categoriaFiltro.value : '';
    const buscaMaterial = document.getElementById('busca-material-contagem');
    const termoBuscaMaterial = buscaMaterial ? buscaMaterial.value.trim() : '';

    const paramsBase = new URLSearchParams({
        action: 'itens',
        id_inventario: idInventario,
        page: 1,
        limit: 1
    });

    if (idCategoria) paramsBase.append('id_categoria', idCategoria);
    if (termoBuscaMaterial) paramsBase.append('termo_busca', termoBuscaMaterial);

    const respostaTotal = await fetch(`api/inventario.php?${paramsBase}`, { credentials: 'same-origin' });
    if (!respostaTotal.ok) throw new Error(`HTTP ${respostaTotal.status}`);
    const dadosTotal = await respostaTotal.json();
    const total = Math.max(1, parseInt(dadosTotal.total || 0, 10));

    const paramsTodos = new URLSearchParams({
        action: 'itens',
        id_inventario: idInventario,
        page: 1,
        limit: total
    });
    if (idCategoria) paramsTodos.append('id_categoria', idCategoria);
    if (termoBuscaMaterial) paramsTodos.append('termo_busca', termoBuscaMaterial);

    const respostaTodos = await fetch(`api/inventario.php?${paramsTodos}`, { credentials: 'same-origin' });
    if (!respostaTodos.ok) throw new Error(`HTTP ${respostaTodos.status}`);
    const dadosTodos = await respostaTodos.json();

    return dadosTodos.data || [];
}

// Renderizar itens para contagem
function renderizarItensContagem(itens) {
    console.log('🔍 Renderizando itens para contagem:', itens);
    
    if (!Array.isArray(itens)) {
        console.error('❌ Itens não é um array:', itens);
        return;
    }
    
    const tbody = document.querySelector('#tabelaContagem tbody');
    if (!tbody) {
        console.error('❌ Elemento #tabelaContagem tbody não encontrado!');
        return;
    }
    
    tbody.innerHTML = '';
    
    if (itens.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-muted py-4">
                    <i class="bi bi-inbox fs-1"></i>
                    <div class="mt-2">Nenhum item encontrado</div>
                </td>
            </tr>
        `;
        atualizarBotaoAjustarLote(0);
        return;
    }
    
    console.log(`📊 Renderizando ${itens.length} itens`);
    
    let totalDivergentes = 0;
    
    itens.forEach((item, index) => {
        const row = document.createElement('tr');
        const isDivergente = item.status_item === 'divergente';
        const isAjustado = item.status_item === 'ajustado';
        const quantidadeDivergencia = item.quantidade_contada ? (parseFloat(item.quantidade_contada) - parseFloat(item.quantidade_sistema || 0)) : 0;
        const divergenciaTexto = quantidadeDivergencia > 0 ? `+${quantidadeDivergencia}` : quantidadeDivergencia.toString();
        
        if (isDivergente && !isAjustado) {
            totalDivergentes++;
        }
        
        row.innerHTML = `
            <td>${item.codigo_material || 'N/A'}</td>
            <td>${item.nome_material || 'N/A'}</td>
            <td>${item.quantidade_sistema || 0} ${item.unidade_material || 'un'}</td>
            <td>
                <input type="number" class="form-control form-control-sm" 
                       id="contagem_${item.id_item_inventario}" 
                       value="${item.quantidade_contada || ''}" 
                       step="0.001" min="0"
                       placeholder="Quantidade contada"
                       ${isAjustado ? 'readonly' : ''}>
                ${isDivergente ? `<small class="text-warning d-block mt-1"><i class="bi bi-exclamation-triangle"></i> Diferença: ${divergenciaTexto}</small>` : ''}
            </td>
            <td>${getStatusItemBadge(item.status_item, item)}</td>
            <td>
                <div class="btn-group btn-group-sm">
                    ${!isAjustado ? `
                        <button class="btn btn-sm btn-primary" onclick="salvarContagem(${item.id_item_inventario})" title="Salvar Contagem">
                            <i class="bi bi-check"></i>
                        </button>
                    ` : ''}
                    ${isDivergente && !isAjustado ? `
                        <button class="btn btn-sm btn-warning" onclick="ajustarItemInventario(${item.id_item_inventario}, '${(item.nome_material || '').replace(/'/g, "\\'")}')" title="Ajustar e Atualizar Estoque">
                            <i class="bi bi-arrow-repeat"></i> Ajustar
                        </button>
                    ` : ''}
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
    
    // Atualizar botão de ajuste em lote com o total de divergentes na página atual
    atualizarBotaoAjustarLote(totalDivergentes);
    
    // Inicializar tooltips após renderizar
    inicializarTooltips();
    
    console.log('✅ Renderização concluída');
}

// Inicializar tooltips do Bootstrap
function inicializarTooltips() {
    // Remover tooltips existentes antes de criar novos (evitar duplicatas)
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipTriggerList.forEach(tooltipTriggerEl => {
        const existingTooltip = bootstrap.Tooltip.getInstance(tooltipTriggerEl);
        if (existingTooltip) {
            existingTooltip.dispose();
        }
    });
    
    // Inicializar novos tooltips
    const tooltipList = [...document.querySelectorAll('[data-bs-toggle="tooltip"]')].map(
        tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl)
    );
}

// Atualizar botão de ajuste em lote
function atualizarBotaoAjustarLote(totalDivergentesPagina) {
    const btnAjustarLote = document.getElementById('btnAjustarLote');
    const badgeDivergentes = document.getElementById('badgeDivergentes');
    
    if (!btnAjustarLote) return;
    
    // Buscar total de divergentes do inventário completo
    const idInventario = document.getElementById('idInventarioContagem')?.value;
    if (idInventario) {
        buscarTotalDivergentes(idInventario).then(totalDivergentes => {
            if (totalDivergentes > 0) {
                btnAjustarLote.style.display = 'inline-flex';
                badgeDivergentes.textContent = totalDivergentes;
                badgeDivergentes.style.display = 'inline-block';
            } else {
                btnAjustarLote.style.display = 'none';
                badgeDivergentes.style.display = 'none';
            }
        }).catch(err => {
            console.error('Erro ao buscar total de divergentes:', err);
            // Mostrar baseado na página atual
            if (totalDivergentesPagina > 0) {
                btnAjustarLote.style.display = 'inline-flex';
                badgeDivergentes.textContent = totalDivergentesPagina;
                badgeDivergentes.style.display = 'inline-block';
            } else {
                btnAjustarLote.style.display = 'none';
                badgeDivergentes.style.display = 'none';
            }
        });
    } else {
        btnAjustarLote.style.display = 'none';
        badgeDivergentes.style.display = 'none';
    }
}

// Buscar total de itens divergentes do inventário
async function buscarTotalDivergentes(idInventario) {
    try {
        const response = await fetch(`api/inventario.php?action=itens&id_inventario=${idInventario}&status_item=divergente&limit=1`, {
            credentials: 'same-origin'
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        return data.total || 0;
    } catch (error) {
        console.error('Erro ao buscar total de divergentes:', error);
        return 0;
    }
}

// Verificar se há materiais novos para adicionar ao inventário
async function verificarMateriaisNovos(idInventario) {
    try {
        const btnAtualizar = document.getElementById('btnAtualizarInventario');
        const badgeNovos = document.getElementById('badgeNovosMateriais');
        
        if (!btnAtualizar) return;
        
        // Buscar informações do inventário
        const responseInventario = await fetch(`api/inventario.php?action=get&id=${idInventario}`, {
            credentials: 'same-origin'
        });
        
        if (!responseInventario.ok) {
            throw new Error(`HTTP error! status: ${responseInventario.status}`);
        }
        
        const inventarioData = await responseInventario.json();
        if (!inventarioData.success || !inventarioData.data) {
            btnAtualizar.style.display = 'none';
            return;
        }
        
        // Só mostrar botão se inventário estiver em andamento
        if (inventarioData.data.status !== 'em_andamento') {
            btnAtualizar.style.display = 'none';
            if (badgeNovos) badgeNovos.style.display = 'none';
            return;
        }
        
        // Buscar total de materiais novos
        const response = await fetch(`api/inventario.php?action=materiais_novos&id_inventario=${idInventario}`, {
            credentials: 'same-origin'
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        const totalNovos = data.total || 0;
        
        if (totalNovos > 0) {
            btnAtualizar.style.display = 'inline-flex';
            if (badgeNovos) {
                badgeNovos.textContent = totalNovos;
                badgeNovos.style.display = 'inline-block';
            }
        } else {
            btnAtualizar.style.display = 'none';
            if (badgeNovos) badgeNovos.style.display = 'none';
        }
    } catch (error) {
        console.error('Erro ao verificar materiais novos:', error);
        const btnAtualizar = document.getElementById('btnAtualizarInventario');
        if (btnAtualizar) btnAtualizar.style.display = 'none';
    }
}

// Atualizar inventário com novos materiais
async function atualizarInventarioComNovosMateriais() {
    try {
        const idInventario = document.getElementById('idInventarioContagem')?.value;
        if (!idInventario) {
            mostrarErro('ID do inventário não encontrado');
            return;
        }
        
        const result = await Swal.fire({
            icon: 'question',
            title: 'Atualizar Inventário?',
            html: `
                <p>Deseja adicionar os novos materiais cadastrados após a criação deste inventário?</p>
                <p class="text-muted small">Esta ação irá:</p>
                <ul class="text-start text-muted small">
                    <li>Buscar materiais novos que não estão no inventário</li>
                    <li>Adicionar apenas materiais ativos com estoque na filial</li>
                    <li>Manter os valores do sistema no momento da adição</li>
                </ul>
            `,
            showCancelButton: true,
            confirmButtonText: 'Sim, Atualizar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#0dcaf0',
            cancelButtonColor: '#6c757d'
        });
        
        if (!result.isConfirmed) {
            return;
        }
        
        // Mostrar loading
        Swal.fire({
            title: 'Atualizando...',
            text: 'Buscando e adicionando novos materiais',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        const formData = {
            id_inventario: idInventario
        };
        
        const response = await fetch('api/inventario.php?action=atualizar_materiais', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData),
            credentials: 'same-origin'
        });
        
        const data = await response.json();
        
        if (data.success) {
            const { adicionados, total_encontrados } = data.data;
            
            if (adicionados > 0) {
                Swal.fire({
                    icon: 'success',
                    title: 'Inventário Atualizado!',
                    html: `
                        <p><strong>${adicionados} novo(s) material(is) adicionado(s) com sucesso!</strong></p>
                        <p class="text-muted small">Os novos materiais aparecerão na lista de contagem.</p>
                    `,
                    confirmButtonText: 'OK'
                });
            } else {
                Swal.fire({
                    icon: 'info',
                    title: 'Nenhum Material Novo',
                    text: 'Não há materiais novos para adicionar ao inventário.',
                    confirmButtonText: 'OK'
                });
            }
            
            // Recarregar itens mantendo a página atual
            await carregarItensContagem(idInventario);
            
            // Atualizar botão de materiais novos
            verificarMateriaisNovos(idInventario);
            
            // Recarregar lista de inventários para atualizar estatísticas
            carregarInventarios();
            carregarEstatisticas();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: data.error || 'Erro ao atualizar inventário',
                confirmButtonText: 'OK'
            });
        }
    } catch (error) {
        console.error('Erro ao atualizar inventário:', error);
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'Erro ao atualizar inventário: ' + error.message,
            confirmButtonText: 'OK'
        });
    }
}

// Renderizar informações de paginação
function renderizarInfoPaginacaoContagem() {
    const infoElement = document.getElementById('info-paginacao-contagem');
    if (!infoElement) return;
    
    const inicio = totalItensContagem === 0 ? 0 : (paginaAtualContagem - 1) * itensPorPaginaContagem + 1;
    const fim = Math.min(paginaAtualContagem * itensPorPaginaContagem, totalItensContagem);
    
    infoElement.textContent = `Mostrando ${inicio} a ${fim} de ${totalItensContagem} itens`;
}

// Renderizar controles de paginação
function renderizarPaginacaoContagem() {
    const paginacaoElement = document.getElementById('paginacao-contagem');
    if (!paginacaoElement) return;
    
    if (totalPagesContagem <= 1) {
        paginacaoElement.innerHTML = '';
        return;
    }
    
    let html = '<nav><ul class="pagination pagination-sm mb-0">';
    
    // Botão Anterior
    html += `<li class="page-item ${paginaAtualContagem === 1 ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="irParaPaginaContagem(${paginaAtualContagem - 1}); return false;">
            <i class="bi bi-chevron-left"></i>
        </a>
    </li>`;
    
    // Calcular quais páginas mostrar (similar ao alertas.js)
    const maxPagesToShow = 7;
    let startPage = Math.max(1, paginaAtualContagem - Math.floor(maxPagesToShow / 2));
    let endPage = Math.min(totalPagesContagem, startPage + maxPagesToShow - 1);
    
    // Ajustar início se estiver muito próximo do fim
    if (endPage - startPage < maxPagesToShow - 1) {
        startPage = Math.max(1, endPage - maxPagesToShow + 1);
    }
    
    // Primeira página
    if (startPage > 1) {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="irParaPaginaContagem(1); return false;">1</a></li>`;
        if (startPage > 2) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }
    
    // Páginas do meio
    for (let i = startPage; i <= endPage; i++) {
        html += `<li class="page-item ${i === paginaAtualContagem ? 'active' : ''}">
            <a class="page-link" href="#" onclick="irParaPaginaContagem(${i}); return false;">${i}</a>
        </li>`;
    }
    
    // Última página
    if (endPage < totalPagesContagem) {
        if (endPage < totalPagesContagem - 1) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
        html += `<li class="page-item"><a class="page-link" href="#" onclick="irParaPaginaContagem(${totalPagesContagem}); return false;">${totalPagesContagem}</a></li>`;
    }
    
    // Botão Próximo
    html += `<li class="page-item ${paginaAtualContagem === totalPagesContagem ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="irParaPaginaContagem(${paginaAtualContagem + 1}); return false;">
            <i class="bi bi-chevron-right"></i>
        </a>
    </li>`;
    
    html += '</ul></nav>';
    paginacaoElement.innerHTML = html;
}

// Ir para página específica
async function irParaPaginaContagem(pagina) {
    if (pagina < 1 || pagina > totalPagesContagem) return;
    
    paginaAtualContagem = pagina;
    const idInventario = document.getElementById('idInventarioContagem').value;
    await carregarItensContagem(idInventario);
    
    // Scroll para o topo da tabela
    const tableContainer = document.querySelector('#modalContagem .table-responsive');
    if (tableContainer) {
        tableContainer.scrollTop = 0;
    }
}

// Alterar itens por página
async function alterarItensPorPaginaContagem() {
    const select = document.getElementById('itens-por-pagina-contagem');
    if (!select) return;
    
    itensPorPaginaContagem = parseInt(select.value);
    localStorage.setItem('inventario_contagem_itens_por_pagina', itensPorPaginaContagem);
    
    paginaAtualContagem = 1; // Voltar para primeira página
    
    const idInventario = document.getElementById('idInventarioContagem').value;
    await carregarItensContagem(idInventario);
}

// Salvar contagem
async function salvarContagem(idItem) {
    try {
        const quantidadeContada = document.getElementById(`contagem_${idItem}`).value;
        
        if (!quantidadeContada || quantidadeContada < 0) {
            mostrarErro('Informe uma quantidade válida');
            return;
        }
        
        const formData = {
            id_item_inventario: idItem,
            quantidade_contada: parseFloat(quantidadeContada),
            quantidade_sistema: 0, // Será preenchido pelo backend
            valor_unitario: 0, // Será preenchido pelo backend
            id_usuario_contador: getCurrentUserId()
        };
        
        const response = await fetch('api/inventario.php?action=contar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData),
            credentials: 'same-origin'
        });
        
        const data = await response.json();
        
        if (data.success) {
            if (data.inventario_finalizado) {
                mostrarSucesso('🎉 Contagem salva e inventário finalizado automaticamente!');
                // Fechar modal de contagem
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalContagem'));
                modal.hide();
                // Recarregar lista de inventários
                carregarInventarios();
                carregarEstatisticas();
            } else {
                mostrarSucesso('Contagem salva com sucesso!');
            }
            
            // Recarregar itens mantendo a página atual
            const idInventario = document.getElementById('idInventarioContagem').value;
            await carregarItensContagem(idInventario);
        } else {
            mostrarErro('Erro ao salvar contagem: ' + (data.error || 'Erro desconhecido'));
        }
    } catch (error) {
        console.error('Erro ao salvar contagem:', error);
        mostrarErro('Erro ao salvar contagem');
    }
}

// Ajustar todos os itens divergentes em lote
async function ajustarLoteDivergentes() {
    console.log('🔧 ajustarLoteDivergentes chamada');
    try {
        const idInventario = document.getElementById('idInventarioContagem')?.value;
        if (!idInventario) {
            mostrarErro('ID do inventário não encontrado');
            return;
        }
        
        // Buscar total de divergentes para confirmação
        const totalDivergentes = await buscarTotalDivergentes(idInventario);
        
        if (totalDivergentes === 0) {
            Swal.fire({
                icon: 'info',
                title: 'Nenhum item divergente',
                text: 'Não há itens divergentes para ajustar.',
                confirmButtonText: 'OK'
            });
            return;
        }
        
        const result = await Swal.fire({
            icon: 'question',
            title: 'Ajustar Todos os Itens Divergentes?',
            html: `
                <p>Deseja ajustar <strong>${totalDivergentes} item(ns) divergente(s)</strong> e atualizar o estoque automaticamente?</p>
                <p class="text-muted small">Esta ação irá:</p>
                <ul class="text-start text-muted small">
                    <li>Atualizar o estoque de todos os itens divergentes com as quantidades contadas</li>
                    <li>Criar movimentações de ajuste para auditoria de cada item</li>
                    <li>Marcar todos os itens como "Ajustado"</li>
                </ul>
                <p class="text-warning small mt-2"><i class="bi bi-exclamation-triangle"></i> Esta ação não pode ser desfeita.</p>
            `,
            showCancelButton: true,
            confirmButtonText: `Sim, Ajustar ${totalDivergentes} Item(ns)`,
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d'
        });
        
        if (!result.isConfirmed) {
            return;
        }
        
        // Mostrar loading com progresso
        Swal.fire({
            title: 'Ajustando Itens...',
            html: `
                <div class="text-center">
                    <div class="spinner-border text-warning mb-3" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                    <p>Processando ${totalDivergentes} item(ns) divergente(s)...</p>
                    <p class="text-muted small">Isso pode levar alguns segundos.</p>
                </div>
            `,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        const formData = {
            id_inventario: idInventario,
            observacoes: 'Ajuste em lote de itens divergentes'
        };
        
        const response = await fetch('api/inventario.php?action=ajustar_lote', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData),
            credentials: 'same-origin'
        });
        
        const data = await response.json();
        
        if (data.success) {
            const { total, ajustados, erros } = data.data;
            
            let mensagem = `<p><strong>${ajustados} de ${total} item(ns) ajustado(s) com sucesso!</strong></p>`;
            
            if (erros && erros.length > 0) {
                mensagem += `<p class="text-danger small mt-2">${erros.length} item(ns) com erro:</p>`;
                mensagem += '<ul class="text-start small">';
                erros.forEach(erro => {
                    mensagem += `<li>${erro.material}: ${erro.erro}</li>`;
                });
                mensagem += '</ul>';
            }
            
            Swal.fire({
                icon: ajustados === total ? 'success' : 'warning',
                title: ajustados === total ? 'Ajuste em Lote Concluído!' : 'Ajuste Parcial',
                html: mensagem,
                confirmButtonText: 'OK'
            });
            
            // Recarregar itens mantendo a página atual
            await carregarItensContagem(idInventario);
            
            // Recarregar lista de inventários para atualizar estatísticas
            carregarInventarios();
            carregarEstatisticas();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: data.error || 'Erro ao ajustar itens em lote',
                confirmButtonText: 'OK'
            });
        }
    } catch (error) {
        console.error('Erro ao ajustar itens em lote:', error);
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'Erro ao ajustar itens em lote: ' + error.message,
            confirmButtonText: 'OK'
        });
    }
}

// Ajustar item divergente (atualiza estoque e cria movimentação)
async function ajustarItemInventario(idItem, nomeMaterial) {
    console.log('🔧 ajustarItemInventario chamada para item:', idItem);
    try {
        const result = await Swal.fire({
            icon: 'question',
            title: 'Ajustar Item Divergente?',
            html: `
                <p>Deseja ajustar o item <strong>${nomeMaterial}</strong> e atualizar o estoque automaticamente?</p>
                <p class="text-muted small">Esta ação irá:</p>
                <ul class="text-start text-muted small">
                    <li>Atualizar o estoque com a quantidade contada</li>
                    <li>Criar uma movimentação de ajuste para auditoria</li>
                    <li>Marcar o item como "Ajustado"</li>
                </ul>
            `,
            showCancelButton: true,
            confirmButtonText: 'Sim, Ajustar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d'
        });
        
        if (!result.isConfirmed) {
            return;
        }
        
        // Mostrar loading
        Swal.fire({
            title: 'Ajustando...',
            text: 'Atualizando estoque e criando movimentação',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        const formData = {
            id_item_inventario: idItem,
            observacoes: 'Ajuste manual de item divergente'
        };
        
        const response = await fetch('api/inventario.php?action=ajustar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData),
            credentials: 'same-origin'
        });
        
        const data = await response.json();
        
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Item Ajustado!',
                html: `
                    <p>O item foi ajustado com sucesso!</p>
                    <p class="text-muted small">O estoque foi atualizado e uma movimentação foi criada para auditoria.</p>
                `,
                confirmButtonText: 'OK'
            });
            
            // Recarregar itens mantendo a página atual
            const idInventario = document.getElementById('idInventarioContagem').value;
            await carregarItensContagem(idInventario);
            
            // Recarregar lista de inventários para atualizar estatísticas
            carregarInventarios();
            carregarEstatisticas();
            
            // Atualizar botão de ajuste em lote
            atualizarBotaoAjustarLote(0);
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: data.error || 'Erro ao ajustar item',
                confirmButtonText: 'OK'
            });
        }
    } catch (error) {
        console.error('Erro ao ajustar item:', error);
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'Erro ao ajustar item: ' + error.message,
            confirmButtonText: 'OK'
        });
    }
}

// Confirmar exclusão
function confirmarExclusao(id) {
    document.getElementById('confirmMessage').textContent = 'Tem certeza que deseja excluir este inventário?';
    document.getElementById('confirmAction').onclick = () => excluirInventario(id);
    confirmModal.show();
}

// Excluir inventário
async function excluirInventario(id) {
    try {
        const response = await fetch(`api/inventario.php?action=delete&id=${id}`, {
            method: 'DELETE',
            credentials: 'same-origin'
        });
        
        const data = await response.json();
        
        if (data.success) {
            confirmModal.hide();
            carregarInventarios();
            carregarEstatisticas();
            mostrarSucesso('Inventário excluído com sucesso!');
        } else {
            mostrarErro('Erro ao excluir inventário: ' + (data.error || 'Erro desconhecido'));
        }
    } catch (error) {
        console.error('Erro ao excluir inventário:', error);
        mostrarErro('Erro ao excluir inventário');
    }
}

// Utilitários
function getStatusBadge(status) {
    const badges = {
        'em_andamento': '<span class="badge bg-warning">Em Andamento</span>',
        'finalizado': '<span class="badge bg-success">Finalizado</span>',
        'cancelado': '<span class="badge bg-danger">Cancelado</span>'
    };
    return badges[status] || '<span class="badge bg-secondary">Desconhecido</span>';
}

function getStatusText(status) {
    const texts = {
        'em_andamento': 'Em Andamento',
        'finalizado': 'Finalizado',
        'cancelado': 'Cancelado'
    };
    return texts[status] || 'Desconhecido';
}

function getStatusItemBadge(status, item = null) {
    const badges = {
        'pendente': '<span class="badge bg-secondary">Pendente</span>',
        'contado': '<span class="badge bg-success">Contado</span>',
        'ajustado': '<span class="badge bg-info">Ajustado</span>'
    };
    
    // Badge especial para divergente com tooltip
    if (status === 'divergente' && item) {
        const quantidadeSistema = parseFloat(item.quantidade_sistema || 0);
        const quantidadeContada = parseFloat(item.quantidade_contada || 0);
        const quantidadeDivergencia = quantidadeContada - quantidadeSistema;
        const unidade = item.unidade_material || 'un';
        const divergenciaTexto = quantidadeDivergencia > 0 ? `+${quantidadeDivergencia.toFixed(3)}` : quantidadeDivergencia.toFixed(3);
        
        // Criar ID único para o tooltip
        const tooltipId = `tooltip-divergente-${item.id_item_inventario || Date.now()}`;
        
        // Criar tooltip com informações detalhadas (usando escape seguro)
        const tooltipContent = [
            '<div class="text-start">',
            '<strong>Detalhes da Divergência:</strong><br>',
            '<small>',
            '<i class="bi bi-box-seam"></i> Sistema: <strong>' + quantidadeSistema.toFixed(3) + ' ' + unidade + '</strong><br>',
            '<i class="bi bi-clipboard-check"></i> Contado: <strong>' + quantidadeContada.toFixed(3) + ' ' + unidade + '</strong><br>',
            '<i class="bi bi-arrow-left-right"></i> Diferença: <strong class="text-warning">' + divergenciaTexto + ' ' + unidade + '</strong>',
            '</small>',
            '</div>'
        ].join('');
        
        // Escapar aspas e caracteres especiais para atributo HTML
        const tooltipEscaped = tooltipContent
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;')
            .replace(/\n/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();
        
        return `<span class="badge bg-warning" 
                      id="${tooltipId}"
                      data-bs-toggle="tooltip" 
                      data-bs-html="true"
                      data-bs-placement="top"
                      data-bs-title="${tooltipEscaped}"
                      style="cursor: help;">
                    Divergente
                </span>`;
    }
    
    // Badge padrão para divergente sem informações do item
    if (status === 'divergente') {
        return '<span class="badge bg-warning">Divergente</span>';
    }
    
    return badges[status] || '<span class="badge bg-secondary">Desconhecido</span>';
}

function calcularProgresso(inventario) {
    if (!inventario.total_itens || inventario.total_itens === 0) return 0;
    return Math.round((inventario.itens_contados / inventario.total_itens) * 100);
}

function formatarMoeda(valor) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(valor);
}

function formatarData(data) {
    return new Date(data).toLocaleDateString('pt-BR');
}

function formatarDataHora(data) {
    return new Date(data).toLocaleString('pt-BR');
}

function mostrarSucesso(mensagem) {
    Swal.fire({
        icon: 'success',
        title: 'Sucesso!',
        text: mensagem,
        timer: 3000,
        showConfirmButton: false
    });
}

function mostrarErro(mensagem) {
    Swal.fire({
        icon: 'error',
        title: 'Erro!',
        text: mensagem
    });
}

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

function updateInventariosSelecionados() {
    inventariosSelecionados = Array.from(document.querySelectorAll('.inventario-checkbox:checked'))
        .map(checkbox => checkbox.value);
}

function toggleSelectAll() {
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.inventario-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateInventariosSelecionados();
}

function duplicarSelecionados() {
    if (inventariosSelecionados.length === 0) {
        mostrarErro('Selecione pelo menos um inventário para duplicar');
        return;
    }
    
    Swal.fire({
        title: 'Confirmar duplicação',
        text: `Deseja duplicar ${inventariosSelecionados.length} inventário(s)?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sim, duplicar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            mostrarSucesso('Funcionalidade de duplicação em desenvolvimento');
        }
    });
}


function exportarXLS() {
    mostrarSucesso('Funcionalidade de exportação em desenvolvimento');
}

function imprimir() {
    window.print();
} 