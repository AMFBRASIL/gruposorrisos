// Variáveis globais
let tickets = [];
let paginacao = {
    pagina: 1,
    porPagina: 10,
    total: 0
};
let filtros = {
    busca: '',
    status: '',
    prioridade: '',
    categoria: ''
};
let ticketAtual = null;
let usuariosData = []; // Armazenar dados dos usuários para acesso rápido

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    carregarTickets();
    carregarEstatisticas();
    setupEventListeners();
    carregarUsuariosEFiliais();
    
    // Preview de anexos no modal de novo ticket
    const anexosInput = document.getElementById('anexos');
    if (anexosInput) {
        anexosInput.addEventListener('change', function() {
            mostrarPreviewAnexos(this.files, 'anexos-preview');
        });
    }
    
    // Preview de anexos no modal de visualização
    const anexosComentarioInput = document.getElementById('anexos-comentario');
    if (anexosComentarioInput) {
        anexosComentarioInput.addEventListener('change', function() {
            mostrarPreviewAnexos(this.files, 'anexos-comentario-preview');
        });
    }
});

// Configurar event listeners
function setupEventListeners() {
    // Busca
    document.getElementById('busca').addEventListener('input', debounce(function() {
        filtros.busca = this.value;
        paginacao.pagina = 1;
        carregarTickets();
    }, 500));
    
    // Filtros
    document.getElementById('filtro-status').addEventListener('change', function() {
        filtros.status = this.value;
        paginacao.pagina = 1;
        carregarTickets();
    });
    
    document.getElementById('filtro-prioridade').addEventListener('change', function() {
        filtros.prioridade = this.value;
        paginacao.pagina = 1;
        carregarTickets();
    });
    
    document.getElementById('filtro-categoria').addEventListener('change', function() {
        filtros.categoria = this.value;
        paginacao.pagina = 1;
        carregarTickets();
    });
}

// Carregar tickets
async function carregarTickets() {
    try {
        mostrarLoading();
        
        const params = new URLSearchParams({
            action: 'list',
            pagina: paginacao.pagina,
            por_pagina: paginacao.porPagina,
            ...filtros
        });
        
        const response = await fetch(`backend/api/tickets.php?${params}`);
        const data = await response.json();
        
        if (data.success) {
            tickets = data.data.tickets;
            paginacao.total = data.data.total;
            renderizarTickets();
            renderizarPaginacao();
        } else {
            mostrarErro(data.error || 'Erro ao carregar tickets');
        }
    } catch (error) {
        console.error('Erro:', error);
        mostrarErro('Erro de conexão');
    }
}

// Renderizar tickets
function renderizarTickets() {
    const container = document.getElementById('tickets-container');
    
    if (tickets.length === 0) {
        mostrarSemDados();
        return;
    }
    
    let html = '';
    tickets.forEach(ticket => {
        html += criarCardTicket(ticket);
    });
    
    container.innerHTML = html;
    container.style.display = 'block';
    document.getElementById('loading').style.display = 'none';
    document.getElementById('sem-dados').style.display = 'none';
}

// Criar card do ticket
function criarCardTicket(ticket) {
    const dataAbertura = new Date(ticket.data_abertura).toLocaleDateString('pt-BR');
    const tempoDecorrido = calcularTempoDecorrido(ticket.data_abertura);
    
    return `
        <div class="ticket-card">
            <div class="ticket-header">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="ticket-number">${ticket.numero_ticket}</div>
                        <div class="ticket-title">${ticket.titulo}</div>
                    </div>
                    <div class="d-flex gap-2">
                        <span class="priority-badge prioridade-${ticket.nome_prioridade.toLowerCase().replace(' ', '-')}" style="background-color: ${ticket.cor_prioridade}20; color: ${ticket.cor_prioridade};">
                            <i class="bi ${ticket.icone_prioridade} me-1"></i>${ticket.nome_prioridade}
                        </span>
                        <span class="status-badge status-${ticket.nome_status.toLowerCase().replace(' ', '-')}" style="background-color: ${ticket.cor_status}20; color: ${ticket.cor_status};">
                            <i class="bi ${ticket.icone_status} me-1"></i>${ticket.nome_status}
                        </span>
                        <span class="category-badge" style="background-color: ${ticket.cor_categoria}20; color: ${ticket.cor_categoria};">
                            <i class="bi ${ticket.icone_categoria} me-1"></i>${ticket.nome_categoria}
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="ticket-body">
                <div class="ticket-description">
                    ${ticket.descricao ? ticket.descricao.substring(0, 150) + (ticket.descricao.length > 150 ? '...' : '') : 'Sem descrição'}
                </div>
                
                <div class="ticket-meta mt-2">
                    <div class="row">
                        <div class="col-md-6">
                            <small><i class="bi bi-person me-1"></i>Solicitante: ${ticket.solicitante_nome || 'N/A'}</small>
                        </div>
                        <div class="col-md-6">
                            <small><i class="bi bi-person-check me-1"></i>Atribuído: ${ticket.atribuido_nome || 'Não atribuído'}</small>
                        </div>
                    </div>
                    <div class="row mt-1">
                        <div class="col-md-6">
                            <small><i class="bi bi-building me-1"></i>Filial: ${ticket.nome_filial || 'N/A'}</small>
                        </div>
                        <div class="col-md-6">
                            <small><i class="bi bi-calendar me-1"></i>Aberto em: ${dataAbertura} (${tempoDecorrido})</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="ticket-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="ticket-actions">
                        <button class="btn btn-sm btn-outline-primary btn-ticket" onclick="visualizarTicket(${ticket.id_ticket})">
                            <i class="bi bi-eye me-1"></i>Visualizar
                        </button>
                        <button class="btn btn-sm btn-outline-secondary btn-ticket" onclick="editarTicket(${ticket.id_ticket})">
                            <i class="bi bi-pencil me-1"></i>Editar
                        </button>
                        <button class="btn btn-sm btn-outline-danger btn-ticket" onclick="excluirTicket(${ticket.id_ticket})">
                            <i class="bi bi-trash me-1"></i>Excluir
                        </button>
                    </div>
                    <div class="text-muted small">
                        ${ticket.tempo_resolucao ? `Resolvido em ${formatarTempo(ticket.tempo_resolucao)}` : 'Em andamento'}
                    </div>
                </div>
            </div>
        </div>
    `;
}

// Carregar estatísticas
async function carregarEstatisticas() {
    try {
        const response = await fetch('backend/api/tickets.php?action=estatisticas');
        const data = await response.json();
        
        if (data.success) {
            const stats = data.estatisticas;
            
            document.getElementById('total-tickets').textContent = stats.total_tickets || 0;
            document.getElementById('tickets-abertos').textContent = stats.tickets_abertos || 0;
            document.getElementById('tickets-criticos').textContent = stats.tickets_criticos || 0;
            document.getElementById('tempo-medio').textContent = formatarTempo(stats.tempo_medio_resolucao || 0);
            
            // Atualizar textos dos indicadores
            const statusTotal = document.getElementById('status-total-tickets');
            if (statusTotal) {
                if (stats.total_tickets > 0) {
                    statusTotal.textContent = `${stats.total_tickets} tickets cadastrados`;
                    statusTotal.className = 'text-success small';
                } else {
                    statusTotal.textContent = 'Nenhum ticket cadastrado';
                    statusTotal.className = 'text-muted small';
                }
            }
            
            const percentualAbertos = document.getElementById('percentual-abertos');
            if (percentualAbertos) {
                const percentual = stats.total_tickets > 0 ? Math.round((stats.tickets_abertos / stats.total_tickets) * 100) : 0;
                percentualAbertos.textContent = `${percentual}% do total`;
            }
            
            const statusCriticos = document.getElementById('status-tickets-criticos');
            if (statusCriticos) {
                if (stats.tickets_criticos > 0) {
                    statusCriticos.textContent = `${stats.tickets_criticos} tickets críticos`;
                    statusCriticos.className = 'text-danger small';
                } else {
                    statusCriticos.textContent = 'Nenhum ticket crítico';
                    statusCriticos.className = 'text-success small';
                }
            }
            
            const statusTempo = document.getElementById('status-tempo-medio');
            if (statusTempo) {
                if (stats.tempo_medio_resolucao > 0) {
                    statusTempo.textContent = 'Tempo médio de resolução';
                    statusTempo.className = 'text-info small';
                } else {
                    statusTempo.textContent = 'Sem dados de resolução';
                    statusTempo.className = 'text-muted small';
                }
            }
        }
    } catch (error) {
        console.error('Erro ao carregar estatísticas:', error);
    }
}

// Carregar usuários e filiais para os modais
async function carregarUsuariosEFiliais() {
    try {
        console.log('🔍 Carregando usuários e filiais...');
        
        // Carregar usuários
        const responseUsuarios = await fetch('backend/api/usuarios.php?action=list&limit=1000');
        const dataUsuarios = await responseUsuarios.json();
        
        console.log('📋 Resposta usuários:', dataUsuarios);
        
        if (dataUsuarios.success && dataUsuarios.data) {
            const selectUsuario = document.getElementById('usuario_atribuido');
            if (selectUsuario) {
                // Limpar opções existentes (exceto a primeira)
                while (selectUsuario.children.length > 1) {
                    selectUsuario.removeChild(selectUsuario.lastChild);
                }
                
                if (Array.isArray(dataUsuarios.data)) {
                    // Armazenar dados dos usuários para acesso rápido
                    usuariosData = dataUsuarios.data;
                    console.log('📋 Dados dos usuários carregados:', usuariosData);
                    
                    dataUsuarios.data.forEach(usuario => {
                        const option = document.createElement('option');
                        option.value = usuario.id_usuario;
                        option.textContent = usuario.nome_completo;
                        // Adicionar data attributes para facilitar o acesso
                        option.setAttribute('data-id-filial', usuario.id_filial || '');
                        option.setAttribute('data-nome-filial', usuario.nome_filial || '');
                        selectUsuario.appendChild(option);
                        
                        // Log para debug de cada usuário
                        console.log(`👤 Usuário: ${usuario.nome_completo}, ID: ${usuario.id_usuario}, Filial ID: ${usuario.id_filial}, Filial Nome: ${usuario.nome_filial}`);
                    });
                    console.log(`✅ ${dataUsuarios.data.length} usuários carregados`);
                } else {
                    console.error('❌ Dados de usuários não são um array:', dataUsuarios.data);
                }
            } else {
                console.error('❌ Select de usuários não encontrado');
            }
        } else {
            console.error('❌ Erro na resposta de usuários:', dataUsuarios);
        }
        
        // Carregar filiais
        const responseFiliais = await fetch('backend/api/filiais.php?action=list&limit=1000');
        const dataFiliais = await responseFiliais.json();
        
        console.log('📋 Resposta filiais:', dataFiliais);
        
        if (dataFiliais.success && dataFiliais.filiais) {
            const selectFilial = document.getElementById('filial');
            if (selectFilial) {
                // Limpar opções existentes (exceto a primeira)
                while (selectFilial.children.length > 1) {
                    selectFilial.removeChild(selectFilial.lastChild);
                }
                
                if (Array.isArray(dataFiliais.filiais)) {
                    dataFiliais.filiais.forEach(filial => {
                        const option = document.createElement('option');
                        option.value = filial.id;
                        option.textContent = filial.nome;
                        selectFilial.appendChild(option);
                    });
                    console.log(`✅ ${dataFiliais.filiais.length} filiais carregadas`);
                } else {
                    console.error('❌ Dados de filiais não são um array:', dataFiliais.filiais);
                }
            } else {
                console.error('❌ Select de filiais não encontrado');
            }
        } else {
            console.error('❌ Erro na resposta de filiais:', dataFiliais);
        }
        
        // Configurar event listener para preenchimento automático da filial
        configurarEventListeners();
    } catch (error) {
        console.error('💥 Erro ao carregar usuários e filiais:', error);
    }
}

// Configurar event listeners para preenchimento automático
function configurarEventListeners() {
    const selectUsuario = document.getElementById('usuario_atribuido');
    if (selectUsuario) {
        console.log('✅ Select de usuário encontrado, configurando event listener...');
        selectUsuario.addEventListener('change', function() {
            console.log('🔄 Usuário alterado para:', this.value);
            preencherFilialAutomaticamente(this.value);
        });
    } else {
        console.error('❌ Select de usuário não encontrado em configurarEventListeners');
    }
}

// Preencher automaticamente o campo filial baseado no usuário selecionado
function preencherFilialAutomaticamente(idUsuario) {
    console.log('🔍 Preenchendo filial para usuário:', idUsuario);
    console.log('📋 Dados dos usuários disponíveis:', usuariosData);
    
    if (!idUsuario) {
        console.log('ℹ️ Nenhum usuário selecionado, limpando campo filial');
        // Se nenhum usuário selecionado, limpar o campo filial
        const selectFilial = document.getElementById('filial');
        if (selectFilial) {
            selectFilial.value = '';
        }
        return;
    }
    
    // Buscar o usuário selecionado
    const usuario = usuariosData.find(u => u.id_usuario == idUsuario);
    console.log('👤 Usuário encontrado:', usuario);
    
    if (usuario && usuario.id_filial) {
        const selectFilial = document.getElementById('filial');
        if (selectFilial) {
            selectFilial.value = usuario.id_filial;
            console.log(`✅ Filial preenchida automaticamente: ${usuario.nome_filial} (ID: ${usuario.id_filial})`);
        } else {
            console.error('❌ Select de filial não encontrado');
        }
    } else {
        console.log('ℹ️ Usuário não possui filial associada ou dados incompletos');
        console.log('Dados do usuário:', usuario);
        // Limpar o campo filial se o usuário não tiver filial
        const selectFilial = document.getElementById('filial');
        if (selectFilial) {
            selectFilial.value = '';
        }
    }
}

// Abrir modal novo ticket
async function abrirModalNovoTicket() {
    const modal = new bootstrap.Modal(document.getElementById('modalNovoTicket'));
    modal.show();
    
    // Aguardar o modal estar visível antes de carregar dados
    setTimeout(async () => {
        // Recarregar usuários e filiais quando o modal for aberto
        await carregarUsuariosEFiliais();
    }, 100);
}

// Salvar novo ticket
async function salvarNovoTicket(event) {
    event.preventDefault();
    
    const form = document.getElementById('formNovoTicket');
    const formData = new FormData(form);
    
    const dados = {
        titulo: document.getElementById('titulo').value,
        descricao: document.getElementById('descricao').value,
        id_categoria: document.getElementById('categoria').value,
        id_prioridade: document.getElementById('prioridade').value,
        id_usuario_solicitante: 1, // Usuário logado
        id_usuario_atribuido: document.getElementById('usuario_atribuido').value || null,
        id_filial: document.getElementById('filial').value || null
    };
    
    if (!dados.titulo || !dados.id_categoria || !dados.id_prioridade) {
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'Por favor, preencha todos os campos obrigatórios!'
        });
        return;
    }
    
    // Adicionar dados ao FormData
    Object.keys(dados).forEach(key => {
        if (dados[key] !== null && dados[key] !== '') {
            formData.append(key, dados[key]);
        }
    });
    
    // Adicionar arquivos se houver
    const anexosInput = document.getElementById('anexos');
    if (anexosInput && anexosInput.files.length > 0) {
        for (let i = 0; i < anexosInput.files.length; i++) {
            formData.append('anexos[]', anexosInput.files[i]);
        }
    }
    
    try {
        const response = await fetch('backend/api/tickets.php?action=create', {
            method: 'POST',
            body: formData // Não definir Content-Type, o navegador define automaticamente com boundary
        });
        
        const result = await response.json();
        
        if (result.success) {
            let mensagem = 'Ticket criado com sucesso!';
            if (result.anexos && result.anexos.length > 0) {
                mensagem += ` ${result.anexos.length} arquivo(s) anexado(s).`;
            }
            
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: mensagem
            });
            
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalNovoTicket'));
            modal.hide();
            
            form.reset();
            document.getElementById('anexos-preview').innerHTML = '';
            carregarTickets();
            carregarEstatisticas();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: result.error || 'Erro ao criar ticket'
            });
        }
    } catch (error) {
        console.error('Erro:', error);
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'Erro de conexão. Verifique sua internet e tente novamente.'
        });
    }
}

// Visualizar ticket
async function visualizarTicket(id) {
    try {
        const response = await fetch(`backend/api/tickets.php?action=get&id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            ticketAtual = data.ticket;
            preencherModalVisualizacao(data.ticket);
            
            // Carregar comentários (que já incluem os anexos)
            await carregarComentarios(id);
            
            const modal = new bootstrap.Modal(document.getElementById('modalVisualizarTicket'));
            modal.show();
        } else {
            mostrarErro(data.error || 'Erro ao carregar ticket');
        }
    } catch (error) {
        console.error('Erro:', error);
        mostrarErro('Erro de conexão');
    }
}

// Preencher modal de visualização
function preencherModalVisualizacao(ticket) {
    const container = document.getElementById('ticket-detalhes');
    const dataAbertura = new Date(ticket.data_abertura).toLocaleString('pt-BR');
    const tempoDecorrido = calcularTempoDecorrido(ticket.data_abertura);
    
    container.innerHTML = `
        <!-- Cabeçalho do Ticket -->
        <div class="ticket-header mb-4">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-1 fw-bold text-dark">
                        <i class="bi bi-ticket-detailed me-2 text-primary"></i>${ticket.titulo}
                    </h4>
                    <p class="text-muted mb-0">
                        <i class="bi bi-hash me-1"></i>${ticket.numero_ticket}
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <span class="badge fs-6 px-3 py-2" style="background-color: ${ticket.cor_status}20; color: ${ticket.cor_status};">
                        <i class="bi ${ticket.icone_status} me-1"></i>${ticket.nome_status}
                    </span>
                    <span class="badge fs-6 px-3 py-2" style="background-color: ${ticket.cor_prioridade}20; color: ${ticket.cor_prioridade};">
                        <i class="bi ${ticket.icone_prioridade} me-1"></i>${ticket.nome_prioridade}
                    </span>
                </div>
            </div>
        </div>

        <!-- Informações Principais -->
        <div class="row g-4 mb-4">
            <!-- Categoria -->
            <div class="col-md-6">
                <div class="info-card">
                    <div class="info-icon">
                        <i class="bi ${ticket.icone_categoria} text-primary"></i>
                    </div>
                    <div class="info-content">
                        <h6 class="info-label">Categoria</h6>
                        <p class="info-value">${ticket.nome_categoria}</p>
                    </div>
                </div>
            </div>
            
            <!-- Filial -->
            <div class="col-md-6">
                <div class="info-card">
                    <div class="info-icon">
                        <i class="bi bi-building text-success"></i>
                    </div>
                    <div class="info-content">
                        <h6 class="info-label">Filial</h6>
                        <p class="info-value">${ticket.nome_filial || 'N/A'}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Descrição -->
        <div class="description-section mb-4">
            <div class="section-header">
                <i class="bi bi-file-text text-info me-2"></i>
                <h6 class="mb-0 fw-bold">Descrição</h6>
            </div>
            <div class="description-content">
                <p class="mb-0">${ticket.descricao || 'Sem descrição'}</p>
            </div>
        </div>

        <!-- Responsáveis -->
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="info-card">
                    <div class="info-icon">
                        <i class="bi bi-person text-warning"></i>
                    </div>
                    <div class="info-content">
                        <h6 class="info-label">Solicitante</h6>
                        <p class="info-value">${ticket.solicitante_nome || 'N/A'}</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="info-card">
                    <div class="info-icon">
                        <i class="bi bi-person-check text-success"></i>
                    </div>
                    <div class="info-content">
                        <h6 class="info-label">Atribuído para</h6>
                        <p class="info-value">${ticket.atribuido_nome || 'Não atribuído'}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Datas e Tempos -->
        <div class="row g-4">
            <div class="col-md-6">
                <div class="info-card">
                    <div class="info-icon">
                        <i class="bi bi-calendar-event text-primary"></i>
                    </div>
                    <div class="info-content">
                        <h6 class="info-label">Data de Abertura</h6>
                        <p class="info-value">${dataAbertura}</p>
                        <small class="text-muted">${tempoDecorrido}</small>
                    </div>
                </div>
            </div>
            
            ${ticket.tempo_resolucao ? `
            <div class="col-md-6">
                <div class="info-card">
                    <div class="info-icon">
                        <i class="bi bi-clock-history text-info"></i>
                    </div>
                    <div class="info-content">
                        <h6 class="info-label">Tempo de Resolução</h6>
                        <p class="info-value">${formatarTempo(ticket.tempo_resolucao)}</p>
                    </div>
                </div>
            </div>
            ` : ''}
            
            ${ticket.avaliacao ? `
            <div class="col-md-6">
                <div class="info-card">
                    <div class="info-icon">
                        <i class="bi bi-star text-warning"></i>
                    </div>
                    <div class="info-content">
                        <h6 class="info-label">Avaliação</h6>
                        <p class="info-value">
                            ${'★'.repeat(ticket.avaliacao)}${'☆'.repeat(5 - ticket.avaliacao)}
                        </p>
                    </div>
                </div>
            </div>
            ` : ''}
        </div>
    `;
}

// Carregar comentários
async function carregarComentarios(idTicket) {
    try {
        const response = await fetch(`backend/api/tickets.php?action=comentarios&id=${idTicket}`);
        const data = await response.json();
        
        if (data.success) {
            renderizarComentarios(data.comentarios);
        }
    } catch (error) {
        console.error('Erro ao carregar comentários:', error);
    }
}

// Renderizar comentários com anexos organizados por data
function renderizarComentarios(comentarios) {
    const container = document.getElementById('comentarios-container');
    
    if (comentarios.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4">
                <i class="bi bi-chat-dots text-muted" style="font-size: 2rem;"></i>
                <p class="text-muted mt-2 mb-0">Nenhum comentário ainda.</p>
                <small class="text-muted">Seja o primeiro a comentar!</small>
            </div>
        `;
        return;
    }
    
    // Agrupar comentários por data
    const comentariosPorData = {};
    comentarios.forEach(comentario => {
        const dataComentario = new Date(comentario.created_at);
        const dataChave = dataComentario.toLocaleDateString('pt-BR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
        
        if (!comentariosPorData[dataChave]) {
            comentariosPorData[dataChave] = [];
        }
        comentariosPorData[dataChave].push(comentario);
    });
    
    // Ordenar datas (mais recente primeiro)
    const datasOrdenadas = Object.keys(comentariosPorData).sort((a, b) => {
        return new Date(b.split('/').reverse().join('-')) - new Date(a.split('/').reverse().join('-'));
    });
    
    let html = '';
    
    datasOrdenadas.forEach(dataChave => {
        // Cabeçalho da data
        html += `
            <div class="data-separator mb-3 mt-4">
                <div class="d-flex align-items-center">
                    <hr class="flex-grow-1" style="border-color: #e9ecef;">
                    <span class="px-3 fw-bold text-muted" style="font-size: 0.9rem;">
                        <i class="bi bi-calendar3 me-2"></i>${dataChave}
                    </span>
                    <hr class="flex-grow-1" style="border-color: #e9ecef;">
                </div>
            </div>
        `;
        
        // Comentários da data
        comentariosPorData[dataChave].forEach(comentario => {
            const dataFormatada = new Date(comentario.created_at).toLocaleDateString('pt-BR');
            const horaFormatada = new Date(comentario.created_at).toLocaleTimeString('pt-BR', {
                hour: '2-digit',
                minute: '2-digit'
            });
            
            // Definir ícone baseado no tipo de comentário
            let iconeTipo = 'bi-chat-text';
            let corTipo = 'text-primary';
            
            if (comentario.tipo === 'status') {
                iconeTipo = 'bi-arrow-repeat';
                corTipo = 'text-info';
            } else if (comentario.tipo === 'atribuicao') {
                iconeTipo = 'bi-person-check';
                corTipo = 'text-warning';
            } else if (comentario.tipo === 'prioridade') {
                iconeTipo = 'bi-flag';
                corTipo = 'text-danger';
            }
            
            // Renderizar anexos do comentário
            let anexosHtml = '';
            if (comentario.anexos && comentario.anexos.length > 0) {
                anexosHtml = '<div class="anexos-comentario mt-2 pt-2 border-top">';
                anexosHtml += '<div class="d-flex align-items-center mb-2">';
                anexosHtml += '<i class="bi bi-paperclip text-primary me-2"></i>';
                anexosHtml += '<small class="fw-semibold text-muted">Anexos:</small>';
                anexosHtml += '</div>';
                anexosHtml += '<div class="d-flex flex-wrap gap-2">';
                
                comentario.anexos.forEach(anexo => {
                    const tamanho = formatarTamanhoArquivo(anexo.tamanho);
                    const icone = getIconeArquivo(anexo.nome_original);
                    
                    anexosHtml += `
                        <a href="backend/api/tickets.php?action=download_anexo&id_anexo=${anexo.id_anexo}" 
                           class="btn btn-sm btn-outline-primary d-flex align-items-center" 
                           title="${anexo.nome_original} (${tamanho})"
                           style="text-decoration: none;">
                            <i class="${icone} me-1"></i>
                            <span class="text-truncate" style="max-width: 150px;">${anexo.nome_original}</span>
                            <small class="text-muted ms-1">(${tamanho})</small>
                        </a>
                    `;
                });
                
                anexosHtml += '</div></div>';
            }
            
            html += `
                <div class="comentario-item mb-3">
                    <div class="comentario-header d-flex justify-content-between align-items-start mb-2">
                        <div class="comentario-usuario d-flex align-items-center">
                            <i class="bi bi-person-circle ${corTipo} me-2" style="font-size: 1.5rem;"></i>
                            <div>
                                <div class="fw-semibold">${comentario.usuario_nome || 'Usuário'}</div>
                                <span class="badge bg-light text-dark" style="font-size: 0.7rem;">
                                    <i class="bi ${iconeTipo} me-1"></i>${comentario.tipo}
                                </span>
                            </div>
                        </div>
                        <div class="comentario-data text-end">
                            <div class="text-muted" style="font-size: 0.85rem;">
                                <i class="bi bi-clock me-1"></i>${horaFormatada}
                            </div>
                        </div>
                    </div>
                    ${comentario.comentario && comentario.comentario !== '[Anexo de arquivo]' ? `<div class="comentario-texto mb-2">${comentario.comentario}</div>` : ''}
                    ${anexosHtml}
                </div>
            `;
        });
    });
    
    container.innerHTML = html;
}

// Adicionar comentário
async function adicionarComentario() {
    const comentario = document.getElementById('novo-comentario').value.trim();
    const anexosInput = document.getElementById('anexos-comentario');
    const temAnexos = anexosInput && anexosInput.files.length > 0;
    
    if (!comentario && !temAnexos) {
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'Por favor, digite um comentário ou anexe um arquivo!'
        });
        return;
    }
    
    if (!ticketAtual) {
        mostrarErro('Ticket não encontrado');
        return;
    }
    
    try {
        let response;
        
        // Se houver anexos, usar FormData, senão usar JSON
        if (temAnexos) {
            const formData = new FormData();
            formData.append('id_ticket', ticketAtual.id_ticket);
            formData.append('id_usuario', 1); // Usuário logado
            formData.append('comentario', comentario || '');
            formData.append('tipo', 'comentario');
            
            // Adicionar arquivos
            for (let i = 0; i < anexosInput.files.length; i++) {
                formData.append('anexos[]', anexosInput.files[i]);
            }
            
            response = await fetch('backend/api/tickets.php?action=comentario', {
                method: 'POST',
                body: formData
            });
        } else {
            response = await fetch('backend/api/tickets.php?action=comentario', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id_ticket: ticketAtual.id_ticket,
                    id_usuario: 1, // Usuário logado
                    comentario: comentario,
                    tipo: 'comentario'
                })
            });
        }
        
        const result = await response.json();
        
        if (result.success) {
            document.getElementById('novo-comentario').value = '';
            if (anexosInput) {
                anexosInput.value = '';
                document.getElementById('anexos-comentario-preview').innerHTML = '';
            }
            
            // Recarregar comentários (que já incluem os anexos)
            await carregarComentarios(ticketAtual.id_ticket);
            
            let mensagem = 'Comentário adicionado com sucesso!';
            if (result.anexos && result.anexos.length > 0) {
                mensagem += ` ${result.anexos.length} arquivo(s) anexado(s).`;
            }
            
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: mensagem
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: result.error || 'Erro ao adicionar comentário'
            });
        }
    } catch (error) {
        console.error('Erro:', error);
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'Erro de conexão'
        });
    }
}

// Fechar ticket
async function fecharTicket() {
    if (!ticketAtual) {
        mostrarErro('Ticket não encontrado');
        return;
    }
    
    const { value: avaliacao } = await Swal.fire({
        title: 'Avaliar Ticket',
        text: 'Como você avalia a resolução deste ticket?',
        input: 'select',
        inputOptions: {
            '5': '5 estrelas - Excelente',
            '4': '4 estrelas - Muito bom',
            '3': '3 estrelas - Bom',
            '2': '2 estrelas - Regular',
            '1': '1 estrela - Ruim'
        },
        inputPlaceholder: 'Selecione uma avaliação',
        showCancelButton: true,
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Fechar Ticket'
    });
    
    if (avaliacao) {
        try {
            const response = await fetch(`backend/api/tickets.php?action=fechar&id=${ticketAtual.id_ticket}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    avaliacao: parseInt(avaliacao)
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Sucesso!',
                    text: 'Ticket fechado com sucesso!'
                });
                
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalVisualizarTicket'));
                modal.hide();
                
                carregarTickets();
                carregarEstatisticas();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: result.error || 'Erro ao fechar ticket'
                });
            }
        } catch (error) {
            console.error('Erro:', error);
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: 'Erro de conexão'
            });
        }
    }
}

// Funções utilitárias
function calcularTempoDecorrido(dataString) {
    const agora = new Date();
    const data = new Date(dataString);
    const diffMs = agora - data;
    const diffMin = Math.floor(diffMs / (1000 * 60));
    
    if (diffMin < 60) {
        return `${diffMin} min`;
    } else if (diffMin < 1440) {
        const horas = Math.floor(diffMin / 60);
        return `${horas}h`;
    } else {
        const dias = Math.floor(diffMin / 1440);
        return `${dias}d`;
    }
}

function formatarTempo(minutos) {
    if (minutos < 60) {
        return `${minutos} min`;
    } else if (minutos < 1440) {
        const horas = Math.floor(minutos / 60);
        return `${horas}h`;
    } else {
        const dias = Math.floor(minutos / 1440);
        return `${dias}d`;
    }
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

function mostrarLoading() {
    document.getElementById('loading').style.display = 'block';
    document.getElementById('tickets-container').style.display = 'none';
    document.getElementById('sem-dados').style.display = 'none';
}

function mostrarSemDados() {
    document.getElementById('loading').style.display = 'none';
    document.getElementById('tickets-container').style.display = 'none';
    document.getElementById('sem-dados').style.display = 'block';
}

function mostrarErro(mensagem) {
    Swal.fire({
        icon: 'error',
        title: 'Erro!',
        text: mensagem
    });
}

function toggleFiltros() {
    const filtrosAvancados = document.getElementById('filtrosAvancados');
    const isVisible = filtrosAvancados.style.display !== 'none';
    filtrosAvancados.style.display = isVisible ? 'none' : 'block';
}

function limparFiltros() {
    document.getElementById('busca').value = '';
    document.getElementById('filtro-status').value = '';
    document.getElementById('filtro-prioridade').value = '';
    document.getElementById('filtro-categoria').value = '';
    
    filtros = {
        busca: '',
        status: '',
        prioridade: '',
        categoria: ''
    };
    
    paginacao.pagina = 1;
    carregarTickets();
}

function exportarXLS() {
    Swal.fire({
        icon: 'info',
        title: 'Exportar XLS',
        text: 'Funcionalidade em desenvolvimento'
    });
}

function imprimir() {
    window.print();
}

function duplicarSelecionados() {
    Swal.fire({
        icon: 'info',
        title: 'Duplicar Tickets',
        text: 'Funcionalidade em desenvolvimento'
    });
}

// Funções de paginação
function renderizarPaginacao() {
    const totalPaginas = Math.ceil(paginacao.total / paginacao.porPagina);
    const paginacaoContainer = document.getElementById('paginacao');
    const paginacaoLinks = document.getElementById('paginacao-links');
    
    if (totalPaginas <= 1) {
        paginacaoContainer.style.display = 'none';
        return;
    }

    // Atualizar contadores
    const inicio = ((paginacao.pagina - 1) * paginacao.porPagina) + 1;
    const fim = Math.min(paginacao.pagina * paginacao.porPagina, paginacao.total);
    
    document.getElementById('inicio-pagina').textContent = inicio;
    document.getElementById('fim-pagina').textContent = fim;
    document.getElementById('total-registros').textContent = paginacao.total;

    // Gerar links de paginação
    let links = '';
    
    // Botão anterior
    if (paginacao.pagina > 1) {
        links += `<li class="page-item"><a class="page-link" href="#" onclick="irParaPagina(${paginacao.pagina - 1})">Anterior</a></li>`;
    }

    // Páginas numeradas
    const inicioPagina = Math.max(1, paginacao.pagina - 2);
    const fimPagina = Math.min(totalPaginas, paginacao.pagina + 2);

    for (let i = inicioPagina; i <= fimPagina; i++) {
        const active = i === paginacao.pagina ? 'active' : '';
        links += `<li class="page-item ${active}"><a class="page-link" href="#" onclick="irParaPagina(${i})">${i}</a></li>`;
    }

    // Botão próximo
    if (paginacao.pagina < totalPaginas) {
        links += `<li class="page-item"><a class="page-link" href="#" onclick="irParaPagina(${paginacao.pagina + 1})">Próximo</a></li>`;
    }

    paginacaoLinks.innerHTML = links;
    paginacaoContainer.style.display = 'flex';
}

function irParaPagina(pagina) {
    paginacao.pagina = pagina;
    carregarTickets();
}

// Preview de anexos selecionados
function mostrarPreviewAnexos(files, containerId = 'anexos-preview') {
    const preview = document.getElementById(containerId);
    if (!preview) return;
    
    preview.innerHTML = '';
    
    if (files.length === 0) return;
    
    const list = document.createElement('div');
    list.className = 'list-group';
    
    Array.from(files).forEach((file, index) => {
        const item = document.createElement('div');
        item.className = 'list-group-item d-flex justify-content-between align-items-center';
        
        const tamanho = (file.size / 1024).toFixed(2) + ' KB';
        const icone = getIconeArquivo(file.name);
        
        item.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="${icone} me-2 text-primary"></i>
                <div>
                    <div class="fw-semibold">${file.name}</div>
                    <small class="text-muted">${tamanho}</small>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removerAnexoPreview(${index}, '${containerId}')">
                <i class="bi bi-x"></i>
            </button>
        `;
        
        list.appendChild(item);
    });
    
    preview.appendChild(list);
}

// Remover anexo do preview
function removerAnexoPreview(index, containerId = 'anexos-preview') {
    // Determinar qual input usar baseado no container
    const inputId = containerId === 'anexos-comentario-preview' ? 'anexos-comentario' : 'anexos';
    const input = document.getElementById(inputId);
    
    if (!input) return;
    
    const dt = new DataTransfer();
    const files = Array.from(input.files);
    
    files.forEach((file, i) => {
        if (i !== index) {
            dt.items.add(file);
        }
    });
    
    input.files = dt.files;
    mostrarPreviewAnexos(input.files, containerId);
}

// Obter ícone do arquivo
function getIconeArquivo(nomeArquivo) {
    const extensao = nomeArquivo.split('.').pop().toLowerCase();
    const icones = {
        'pdf': 'bi-file-pdf',
        'doc': 'bi-file-word',
        'docx': 'bi-file-word',
        'xls': 'bi-file-excel',
        'xlsx': 'bi-file-excel',
        'jpg': 'bi-file-image',
        'jpeg': 'bi-file-image',
        'png': 'bi-file-image',
        'gif': 'bi-file-image',
        'txt': 'bi-file-text'
    };
    return icones[extensao] || 'bi-file';
}

// Carregar anexos do ticket
async function carregarAnexos(idTicket) {
    try {
        const response = await fetch(`backend/api/tickets.php?action=anexos&id=${idTicket}`);
        const data = await response.json();
        
        if (data.success) {
            renderizarAnexos(data.anexos);
        }
    } catch (error) {
        console.error('Erro ao carregar anexos:', error);
    }
}

// Renderizar anexos
function renderizarAnexos(anexos) {
    const anexosSection = document.getElementById('anexos-section');
    const anexosContainer = document.getElementById('anexos-container');
    
    if (!anexosSection || !anexosContainer) return;
    
    // Mostrar seção se houver anexos
    if (anexos && anexos.length > 0) {
        anexosSection.style.display = 'block';
    } else {
        anexosSection.style.display = 'none';
        anexosContainer.innerHTML = '';
        return;
    }
    
    if (anexos.length === 0) {
        anexosContainer.innerHTML = `
            <div class="text-center py-3 text-muted">
                <i class="bi bi-paperclip" style="font-size: 1.5rem;"></i>
                <p class="mt-2 mb-0">Nenhum anexo</p>
            </div>
        `;
        return;
    }
    
    if (anexos.length === 0) {
        anexosContainer.innerHTML = `
            <div class="text-center py-3 text-muted">
                <i class="bi bi-paperclip" style="font-size: 1.5rem;"></i>
                <p class="mt-2 mb-0">Nenhum anexo</p>
            </div>
        `;
        return;
    }
    
    let html = '<div class="row g-2">';
    anexos.forEach(anexo => {
        const tamanho = formatarTamanhoArquivo(anexo.tamanho);
        const icone = getIconeArquivo(anexo.nome_original);
        const dataUpload = new Date(anexo.created_at).toLocaleString('pt-BR');
        
        html += `
            <div class="col-md-6">
                <div class="card border">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center flex-grow-1">
                                <i class="${icone} me-2 text-primary fs-4"></i>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold text-truncate" style="max-width: 200px;" title="${anexo.nome_original}">
                                        ${anexo.nome_original}
                                    </div>
                                    <small class="text-muted">${tamanho} • ${dataUpload}</small>
                                </div>
                            </div>
                            <div class="d-flex gap-1">
                                <a href="backend/api/tickets.php?action=download_anexo&id_anexo=${anexo.id_anexo}" 
                                   class="btn btn-sm btn-outline-primary" 
                                   title="Baixar">
                                    <i class="bi bi-download"></i>
                                </a>
                                <button type="button" 
                                        class="btn btn-sm btn-outline-danger" 
                                        onclick="deletarAnexo(${anexo.id_anexo}, ${anexo.id_ticket})"
                                        title="Excluir">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    html += '</div>';
    
    anexosContainer.innerHTML = html;
}

// Formatar tamanho do arquivo
function formatarTamanhoArquivo(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

// Adicionar apenas anexos (sem comentário)
async function adicionarApenasAnexos() {
    const anexosInput = document.getElementById('anexos-comentario');
    
    if (!anexosInput || !anexosInput.files || anexosInput.files.length === 0) {
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'Por favor, selecione pelo menos um arquivo!'
        });
        return;
    }
    
    if (!ticketAtual) {
        mostrarErro('Ticket não encontrado');
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('id_ticket', ticketAtual.id_ticket);
        formData.append('id_usuario', 1); // Usuário logado
        formData.append('comentario', '[Anexo de arquivo]'); // Comentário padrão para anexos sem texto
        formData.append('tipo', 'comentario');
        
        // Adicionar arquivos
        for (let i = 0; i < anexosInput.files.length; i++) {
            formData.append('anexos[]', anexosInput.files[i]);
        }
        
        const response = await fetch('backend/api/tickets.php?action=comentario', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            anexosInput.value = '';
            document.getElementById('anexos-comentario-preview').innerHTML = '';
            
            // Recarregar comentários (que já incluem os anexos)
            await carregarComentarios(ticketAtual.id_ticket);
            
            let mensagem = 'Arquivo(s) anexado(s) com sucesso!';
            if (result.anexos && result.anexos.length > 0) {
                mensagem = `${result.anexos.length} arquivo(s) anexado(s) com sucesso!`;
            }
            
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: mensagem
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: result.error || 'Erro ao anexar arquivos'
            });
        }
    } catch (error) {
        console.error('Erro:', error);
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'Erro de conexão'
        });
    }
}

// Deletar anexo
async function deletarAnexo(idAnexo, idTicket) {
    Swal.fire({
        title: 'Tem certeza?',
        text: 'Esta ação não pode ser desfeita!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const response = await fetch(`backend/api/tickets.php?action=anexo&id_anexo=${idAnexo}`, {
                    method: 'DELETE'
                });
                
                const data = await response.json();
                
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: 'Anexo excluído com sucesso!'
                    });
                    // Recarregar comentários (que já incluem os anexos)
                    if (ticketAtual) {
                        await carregarComentarios(ticketAtual.id_ticket);
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: data.error || 'Erro ao excluir anexo'
                    });
                }
            } catch (error) {
                console.error('Erro:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: 'Erro de conexão'
                });
            }
        }
    });
} 