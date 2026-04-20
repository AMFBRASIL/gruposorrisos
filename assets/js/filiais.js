// Variáveis globais
let filiais = [];
let filtros = {
    busca: '',
    estado: '',
    status: '',
    tipo: ''
};
let paginacao = {
    pagina: 1,
    porPagina: 10,
    total: 0
};

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    carregarFiliais();
    carregarFiltros();
    setupEventListeners();
});

// Configurar event listeners
function setupEventListeners() {
    // Busca em tempo real
    document.getElementById('busca').addEventListener('input', function(e) {
        filtros.busca = e.target.value;
        paginacao.pagina = 1;
        carregarFiliais();
    });

    // Filtros
    document.getElementById('filtro-estado').addEventListener('change', function(e) {
        filtros.estado = e.target.value;
        paginacao.pagina = 1;
        carregarFiliais();
    });

    document.getElementById('filtro-status').addEventListener('change', function(e) {
        filtros.status = e.target.value;
        paginacao.pagina = 1;
        carregarFiliais();
    });

    document.getElementById('filtro-tipo').addEventListener('change', function(e) {
        filtros.tipo = e.target.value;
        paginacao.pagina = 1;
        carregarFiliais();
    });

    // Select all
    document.getElementById('select-all').addEventListener('change', function(e) {
        const checkboxes = document.querySelectorAll('#filiais-tbody input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = e.target.checked;
        });
    });
}

// Carregar filiais
function carregarFiliais() {
    mostrarLoading();
    
    const params = new URLSearchParams({
        pagina: paginacao.pagina,
        por_pagina: paginacao.porPagina,
        ...filtros
    });

    fetch(`backend/api/filiais.php?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                filiais = data.filiais;
                paginacao.total = data.total;
                renderizarTabela();
                atualizarIndicadores(data.indicadores);
                renderizarPaginacao();
            } else {
                mostrarErro(data.message || 'Erro ao carregar filiais');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            mostrarErro('Erro ao carregar filiais');
        });
}

// Renderizar tabela
function renderizarTabela() {
    const tbody = document.getElementById('filiais-tbody');
    
    if (filiais.length === 0) {
        mostrarSemDados();
        return;
    }

    tbody.innerHTML = filiais.map(filial => `
        <tr>
            <td>
                <input type="checkbox" class="form-check-input" value="${filial.id}">
            </td>
            <td>
                <span class="fw-bold">${filial.codigo}</span>
            </td>
            <td>
                <div class="d-flex align-items-center">
                    <div class="me-2">
                        <i class="bi bi-building text-primary"></i>
                    </div>
                    <div>
                        <div class="fw-semibold">${filial.nome}</div>
                        <small class="text-muted">${filial.cnpj || 'CNPJ não informado'}</small>
                    </div>
                </div>
            </td>
            <td>
                <span class="badge ${filial.tipo === 'matriz' ? 'bg-primary' : 'bg-secondary'}">
                    ${filial.tipo === 'matriz' ? 'Matriz' : 'Filial'}
                </span>
            </td>
            <td>
                <div>
                    <div class="fw-semibold">${filial.cidade}</div>
                    <small class="text-muted">${filial.estado}</small>
                </div>
            </td>
            <td>
                <div>
                    <div class="fw-semibold">${filial.responsavel}</div>
                    <small class="text-muted">${filial.email_responsavel}</small>
                </div>
            </td>
            <td>
                <span class="text-muted">${filial.telefone || '-'}</span>
            </td>
            <td>
                <span class="badge bg-info">${filial.total_funcionarios || 0}</span>
            </td>
            <td>
                <span class="badge ${filial.status === 'ativa' ? 'bg-success' : 'bg-warning'}">
                    ${filial.status === 'ativa' ? 'Ativa' : 'Inativa'}
                </span>
            </td>
            <td>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="editarFilial(${filial.id})" title="Editar">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-info" onclick="visualizarFilial(${filial.id})" title="Visualizar">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="excluirFilial(${filial.id})" title="Excluir">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');

    mostrarTabela();
}

// Atualizar indicadores
function atualizarIndicadores(indicadores) {
    document.getElementById('total-filiais').textContent = indicadores.total || 0;
    document.getElementById('filiais-ativas').textContent = indicadores.ativas || 0;
    document.getElementById('filiais-inativas').textContent = indicadores.inativas || 0;
    document.getElementById('total-funcionarios').textContent = indicadores.funcionarios || 0;
    
    const percentual = indicadores.total > 0 ? Math.round((indicadores.ativas / indicadores.total) * 100) : 0;
    document.getElementById('percentual-ativas').textContent = `${percentual}% do total`;
    
    // Atualizar status do indicador total
    const statusElement = document.getElementById('status-total-filiais');
    if (statusElement) {
        if (indicadores.total > 0) {
            statusElement.textContent = `${indicadores.total} filiais cadastradas`;
            statusElement.className = 'text-success small';
        } else {
            statusElement.textContent = 'Nenhuma filial cadastrada';
            statusElement.className = 'text-muted small';
        }
    }
    
    // Atualizar status das filiais inativas
    const statusInativas = document.getElementById('status-filiais-inativas');
    if (statusInativas) {
        if (indicadores.inativas > 0) {
            statusInativas.textContent = `${indicadores.inativas} filiais inativas`;
            statusInativas.className = 'text-warning small';
        } else {
            statusInativas.textContent = 'Todas as filiais ativas';
            statusInativas.className = 'text-success small';
        }
    }
    
    // Atualizar status dos funcionários
    const statusFuncionarios = document.getElementById('status-funcionarios');
    if (statusFuncionarios) {
        if (indicadores.funcionarios > 0) {
            statusFuncionarios.textContent = `${indicadores.funcionarios} colaboradores`;
            statusFuncionarios.className = 'text-info small';
        } else {
            statusFuncionarios.textContent = 'Nenhum funcionário cadastrado';
            statusFuncionarios.className = 'text-muted small';
        }
    }
}

// Renderizar paginação
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
        links += `<li class="page-item"><a class="page-link" href="#" href="#" onclick="irParaPagina(${paginacao.pagina + 1})">Próximo</a></li>`;
    }

    paginacaoLinks.innerHTML = links;
    paginacaoContainer.style.display = 'flex';
}

// Ir para página específica
function irParaPagina(pagina) {
    paginacao.pagina = pagina;
    carregarFiliais();
}

// Carregar filtros
function carregarFiltros() {
    // Carregar estados
    fetch('backend/api/estados.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('filtro-estado');
                data.estados.forEach(estado => {
                    const option = document.createElement('option');
                    option.value = estado.uf;
                    option.textContent = estado.nome;
                    select.appendChild(option);
                });
            }
        })
        .catch(error => console.error('Erro ao carregar estados:', error));
}

// Toggle filtros avançados
function toggleFiltros() {
    const filtrosAvancados = document.getElementById('filtrosAvancados');
    const isVisible = filtrosAvancados.style.display !== 'none';
    filtrosAvancados.style.display = isVisible ? 'none' : 'block';
}

// Limpar filtros
function limparFiltros() {
    document.getElementById('busca').value = '';
    document.getElementById('filtro-estado').value = '';
    document.getElementById('filtro-status').value = '';
    document.getElementById('filtro-tipo').value = '';
    
    filtros = {
        busca: '',
        estado: '',
        status: '',
        tipo: ''
    };
    
    paginacao.pagina = 1;
    carregarFiliais();
}

// Funções de visualização
function mostrarLoading() {
    document.getElementById('loading').style.display = 'block';
    document.getElementById('tabela-container').style.display = 'none';
    document.getElementById('sem-dados').style.display = 'none';
    document.getElementById('paginacao').style.display = 'none';
}

function mostrarTabela() {
    document.getElementById('loading').style.display = 'none';
    document.getElementById('tabela-container').style.display = 'block';
    document.getElementById('sem-dados').style.display = 'none';
}

function mostrarSemDados() {
    document.getElementById('loading').style.display = 'none';
    document.getElementById('tabela-container').style.display = 'none';
    document.getElementById('sem-dados').style.display = 'block';
    document.getElementById('paginacao').style.display = 'none';
}

function mostrarErro(mensagem) {
    document.getElementById('loading').style.display = 'none';
    document.getElementById('tabela-container').style.display = 'none';
    document.getElementById('sem-dados').style.display = 'block';
    document.getElementById('paginacao').style.display = 'none';
    
    // Mostrar mensagem de erro
    const semDados = document.getElementById('sem-dados');
    semDados.innerHTML = `
        <i class="bi bi-exclamation-triangle fs-1 text-danger"></i>
        <p class="mt-2 text-danger">${mensagem}</p>
        <button class="btn btn-primary" onclick="carregarFiliais()">
            <i class="bi bi-arrow-clockwise me-1"></i> Tentar Novamente
        </button>
    `;
    
    // Mostrar também um SweetAlert para chamar atenção
    Swal.fire({
        icon: 'error',
        title: 'Erro ao Carregar',
        text: mensagem,
        confirmButtonColor: '#dc3545'
    });
}

// Funções de ação
function editarFilial(id) {
    abrirModalEditarFilial(id);
}

function visualizarFilial(id) {
    abrirModalVisualizarFilial(id);
}

function excluirFilial(id) {
    const filial = filiais.find(f => f.id === id);
    
    if (!filial) {
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'Filial não encontrada',
            confirmButtonColor: '#dc3545'
        });
        return;
    }
    
    Swal.fire({
        title: 'Confirmar Exclusão',
        text: `Tem certeza que deseja excluir a filial "${filial.nome}"? Esta ação não pode ser desfeita.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`backend/api/filiais.php`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: 'Filial excluída com sucesso!',
                        confirmButtonColor: '#28a745'
                    });
                    carregarFiliais();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: data.error || data.message || 'Erro ao excluir filial',
                        confirmButtonColor: '#dc3545'
                    });
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: 'Erro ao excluir filial',
                    confirmButtonColor: '#dc3545'
                });
            });
        }
    });
}

function duplicarSelecionados() {
    const checkboxes = document.querySelectorAll('#filiais-tbody input[type="checkbox"]:checked');
    const ids = Array.from(checkboxes).map(cb => cb.value);
    
    if (ids.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Seleção Necessária',
            text: 'Selecione pelo menos uma filial para duplicar',
            confirmButtonColor: '#3085d6'
        });
        return;
    }
    
    Swal.fire({
        title: 'Confirmar Duplicação',
        text: `Deseja duplicar ${ids.length} filial(is) selecionada(s)?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sim, duplicar!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('backend/api/filiais.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'duplicar',
                    ids: ids
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: 'Filiais duplicadas com sucesso!',
                        confirmButtonColor: '#28a745'
                    });
                    carregarFiliais();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: data.message || 'Erro ao duplicar filiais',
                        confirmButtonColor: '#dc3545'
                    });
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: 'Erro ao duplicar filiais',
                    confirmButtonColor: '#dc3545'
                });
            });
        }
    });
}

// Funções de exportação
function exportarXLS() {
    const params = new URLSearchParams({
        formato: 'xls',
        ...filtros
    });
    
    window.open(`backend/api/exportar_filiais.php?${params}`, '_blank');
}

function imprimir() {
    const params = new URLSearchParams({
        formato: 'pdf',
        ...filtros
    });
    
    window.open(`backend/api/exportar_filiais.php?${params}`, '_blank');
}

// Funções do Modal Nova Filial
function abrirModalNovaFilial() {
    // Limpar formulário
    document.getElementById('formNovaFilial').reset();
    
    // Abrir modal
    const modal = new bootstrap.Modal(document.getElementById('modalNovaFilial'));
    modal.show();
}

function salvarNovaFilial() {
    // Validar campos obrigatórios
    const codigo = document.getElementById('codigo').value.trim();
    const nome = document.getElementById('nome').value.trim();
    
    if (!codigo || !nome) {
        Swal.fire({
            icon: 'warning',
            title: 'Campos Obrigatórios',
            text: 'Código e Nome são campos obrigatórios!',
            confirmButtonColor: '#3085d6'
        });
        return;
    }
    
    // Coletar dados do formulário
    const formData = new FormData(document.getElementById('formNovaFilial'));
    const dados = {};
    
    for (let [key, value] of formData.entries()) {
        dados[key] = value;
    }
    
    // Mostrar loading
    const btnSalvar = document.querySelector('#modalNovaFilial .btn-primary');
    const textoOriginal = btnSalvar.innerHTML;
    btnSalvar.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Salvando...';
    btnSalvar.disabled = true;
    
    // Enviar para API
    fetch('backend/api/filiais.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(dados)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Fechar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalNovaFilial'));
            modal.hide();
            
            // Mostrar sucesso
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: 'Filial criada com sucesso!',
                confirmButtonColor: '#28a745'
            });
            
            // Recarregar lista
            carregarFiliais();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: data.message || 'Erro ao criar filial',
                confirmButtonColor: '#dc3545'
            });
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'Erro ao criar filial',
            confirmButtonColor: '#dc3545'
        });
    })
    .finally(() => {
        // Restaurar botão
        btnSalvar.innerHTML = textoOriginal;
        btnSalvar.disabled = false;
    });
}

// Aplicar máscaras nos campos
function aplicarMascaras() {
    // Máscara para CNPJ
    const cnpjInput = document.getElementById('cnpj');
    if (cnpjInput) {
        cnpjInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 14) {
                value = value.replace(/^(\d{2})(\d)/, '$1.$2');
                value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
                value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
                value = value.replace(/(\d{4})(\d)/, '$1-$2');
                e.target.value = value;
            }
        });
    }
    
    // Máscara para CEP
    const cepInput = document.getElementById('cep');
    if (cepInput) {
        cepInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 8) {
                value = value.replace(/^(\d{5})(\d)/, '$1-$2');
                e.target.value = value;
            }
        });
    }
    
    // Máscara para telefones
    const telefoneInputs = document.querySelectorAll('#telefone, #telefone_responsavel');
    telefoneInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 11) {
                if (value.length <= 10) {
                    value = value.replace(/^(\d{2})(\d)/, '($1) $2');
                    value = value.replace(/(\d{4})(\d)/, '$1-$2');
                } else {
                    value = value.replace(/^(\d{2})(\d)/, '($1) $2');
                    value = value.replace(/(\d{5})(\d)/, '$1-$2');
                }
                e.target.value = value;
            }
        });
    });
}

// Auto-completar CEP
function configurarAutoCEP() {
    const cepInput = document.getElementById('cep');
    if (cepInput) {
        cepInput.addEventListener('blur', function() {
            const cep = this.value.replace(/\D/g, '');
            if (cep.length === 8) {
                fetch(`https://viacep.com.br/ws/${cep}/json/`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.erro) {
                            document.getElementById('endereco').value = data.logradouro || '';
                            document.getElementById('bairro').value = data.bairro || '';
                            document.getElementById('cidade').value = data.localidade || '';
                            document.getElementById('estado').value = data.uf || '';
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao buscar CEP:', error);
                    });
            }
        });
    }
}

// Funções do Modal Editar Filial
function abrirModalEditarFilial(id) {
    // Buscar dados da filial
    fetch(`backend/api/filiais.php?action=get&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Dados da filial:', data.filial); // Debug
                preencherFormularioEdicao(data.filial);
                const modal = new bootstrap.Modal(document.getElementById('modalEditarFilial'));
                modal.show();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: data.message || 'Erro ao carregar dados da filial',
                    confirmButtonColor: '#dc3545'
                });
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: 'Erro ao carregar dados da filial',
                confirmButtonColor: '#dc3545'
            });
        });
}

function preencherFormularioEdicao(filial) {
    console.log('Preenchendo formulário com dados:', filial); // Debug
    
    // Preencher campos do formulário de edição
    document.getElementById('edit_id').value = filial.id || filial.id_filial;
    document.getElementById('edit_codigo').value = filial.codigo || filial.codigo_filial || '';
    document.getElementById('edit_nome').value = filial.nome || filial.nome_filial || '';
    document.getElementById('edit_tipo').value = filial.tipo || filial.tipo_filial || 'filial';
    document.getElementById('edit_status').value = filial.status || (filial.filial_ativa == 1 ? 'ativa' : 'inativa');
    document.getElementById('edit_razao_social').value = filial.razao_social || '';
    document.getElementById('edit_cnpj').value = filial.cnpj || '';
    document.getElementById('edit_inscricao_estadual').value = filial.inscricao_estadual || '';
    document.getElementById('edit_endereco').value = filial.endereco || '';
    document.getElementById('edit_numero').value = filial.numero || '';
    document.getElementById('edit_complemento').value = filial.complemento || '';
    document.getElementById('edit_bairro').value = filial.bairro || '';
    document.getElementById('edit_cidade').value = filial.cidade || '';
    document.getElementById('edit_estado').value = filial.estado || '';
    document.getElementById('edit_cep').value = filial.cep || '';
    document.getElementById('edit_telefone').value = filial.telefone || '';
    document.getElementById('edit_email').value = filial.email || '';
    document.getElementById('edit_responsavel').value = filial.responsavel || '';
    document.getElementById('edit_email_responsavel').value = filial.email_responsavel || '';
    document.getElementById('edit_telefone_responsavel').value = filial.telefone_responsavel || '';
    document.getElementById('edit_observacoes').value = filial.observacoes || '';
    
    // Data de inauguração (tratar datas inválidas)
    const dataInauguracao = filial.data_inauguracao || filial.data_abertura;
    if (dataInauguracao && dataInauguracao !== '0000-00-00' && dataInauguracao !== 'null') {
        try {
            const data = new Date(dataInauguracao);
            if (!isNaN(data.getTime())) {
                document.getElementById('edit_data_inauguracao').value = data.toISOString().split('T')[0];
            } else {
                document.getElementById('edit_data_inauguracao').value = '';
            }
        } catch (error) {
            console.error('Erro ao processar data:', error);
            document.getElementById('edit_data_inauguracao').value = '';
        }
    } else {
        document.getElementById('edit_data_inauguracao').value = '';
    }
}

function salvarEditarFilial() {
    // Validar campos obrigatórios
    const codigo = document.getElementById('edit_codigo').value.trim();
    const nome = document.getElementById('edit_nome').value.trim();
    
    if (!codigo || !nome) {
        Swal.fire({
            icon: 'warning',
            title: 'Campos Obrigatórios',
            text: 'Código e Nome são campos obrigatórios!',
            confirmButtonColor: '#3085d6'
        });
        return;
    }
    
    // Coletar dados do formulário
    const formData = new FormData(document.getElementById('formEditarFilial'));
    const dados = {};
    
    for (let [key, value] of formData.entries()) {
        dados[key] = value;
    }
    
    // Debug: verificar o que está sendo enviado
    console.log('Dados sendo enviados para atualização:', dados);
    
    // Mostrar loading
    const btnSalvar = document.querySelector('#modalEditarFilial .btn-primary');
    const textoOriginal = btnSalvar.innerHTML;
    btnSalvar.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Salvando...';
    btnSalvar.disabled = true;
    
    // Enviar para API
    fetch('backend/api/filiais.php', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(dados)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Fechar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarFilial'));
            modal.hide();
            
            // Mostrar sucesso
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: 'Filial atualizada com sucesso!',
                confirmButtonColor: '#28a745'
            });
            
            // Recarregar lista
            carregarFiliais();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: data.message || 'Erro ao atualizar filial',
                confirmButtonColor: '#dc3545'
            });
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'Erro ao atualizar filial',
            confirmButtonColor: '#dc3545'
        });
    })
    .finally(() => {
        // Restaurar botão
        btnSalvar.innerHTML = textoOriginal;
        btnSalvar.disabled = false;
    });
}

// Aplicar máscaras nos campos de edição
function aplicarMascarasEdicao() {
    // Máscara para CNPJ
    const cnpjInput = document.getElementById('edit_cnpj');
    if (cnpjInput) {
        cnpjInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 14) {
                value = value.replace(/^(\d{2})(\d)/, '$1.$2');
                value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
                value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
                value = value.replace(/(\d{4})(\d)/, '$1-$2');
                e.target.value = value;
            }
        });
    }
    
    // Máscara para CEP
    const cepInput = document.getElementById('edit_cep');
    if (cepInput) {
        cepInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 8) {
                value = value.replace(/^(\d{5})(\d)/, '$1-$2');
                e.target.value = value;
            }
        });
    }
    
    // Máscara para telefones
    const telefoneInputs = document.querySelectorAll('#edit_telefone, #edit_telefone_responsavel');
    telefoneInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 11) {
                if (value.length <= 10) {
                    value = value.replace(/^(\d{2})(\d)/, '($1) $2');
                    value = value.replace(/(\d{4})(\d)/, '$1-$2');
                } else {
                    value = value.replace(/^(\d{2})(\d)/, '($1) $2');
                    value = value.replace(/(\d{5})(\d)/, '$1-$2');
                }
                e.target.value = value;
            }
        });
    });
}

// Auto-completar CEP para edição
function configurarAutoCEPEdicao() {
    const cepInput = document.getElementById('edit_cep');
    if (cepInput) {
        cepInput.addEventListener('blur', function() {
            const cep = this.value.replace(/\D/g, '');
            if (cep.length === 8) {
                fetch(`https://viacep.com.br/ws/${cep}/json/`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.erro) {
                            document.getElementById('edit_endereco').value = data.logradouro || '';
                            document.getElementById('edit_cidade').value = data.localidade || '';
                            document.getElementById('edit_estado').value = data.uf || '';
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao buscar CEP:', error);
                    });
            }
        });
    }
}

// Funções do Modal Visualizar Filial
function abrirModalVisualizarFilial(id) {
    // Buscar dados da filial
    fetch(`backend/api/filiais.php?action=get&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                preencherModalVisualizacao(data.filial);
                const modal = new bootstrap.Modal(document.getElementById('modalVisualizarFilial'));
                modal.show();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: data.message || 'Erro ao carregar dados da filial',
                    confirmButtonColor: '#dc3545'
                });
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: 'Erro ao carregar dados da filial',
                confirmButtonColor: '#dc3545'
            });
        });
}

function preencherModalVisualizacao(filial) {
    console.log('Dados da filial para visualização:', filial); // Debug
    
    // Preencher campos de visualização
    document.getElementById('view_codigo').textContent = filial.codigo || filial.codigo_filial || '-';
    document.getElementById('view_nome').textContent = filial.nome || filial.nome_filial || '-';
    document.getElementById('view_tipo').textContent = filial.tipo || filial.tipo_filial || '-';
    document.getElementById('view_status').textContent = filial.status || (filial.filial_ativa == 1 ? 'Ativa' : 'Inativa');
    document.getElementById('view_cnpj').textContent = filial.cnpj || '-';
    document.getElementById('view_inscricao_estadual').textContent = filial.inscricao_estadual || '-';
    document.getElementById('view_endereco').textContent = filial.endereco || '-';
    document.getElementById('view_numero').textContent = filial.numero || '-';
    document.getElementById('view_complemento').textContent = filial.complemento || '-';
    document.getElementById('view_bairro').textContent = filial.bairro || '-';
    document.getElementById('view_cidade').textContent = filial.cidade || '-';
    document.getElementById('view_estado').textContent = filial.estado || '-';
    document.getElementById('view_cep').textContent = filial.cep || '-';
    document.getElementById('view_telefone').textContent = filial.telefone || '-';
    document.getElementById('view_email').textContent = filial.email || '-';
    document.getElementById('view_responsavel').textContent = filial.responsavel || '-';
    document.getElementById('view_email_responsavel').textContent = filial.email_responsavel || '-';
    document.getElementById('view_telefone_responsavel').textContent = filial.telefone_responsavel || '-';
    document.getElementById('view_observacoes').textContent = filial.observacoes || '-';
    
    // Data de abertura (tratar datas inválidas)
    const dataAbertura = filial.data_abertura || filial.data_inauguracao;
    if (dataAbertura && dataAbertura !== '0000-00-00' && dataAbertura !== 'null') {
        try {
            const data = new Date(dataAbertura);
            if (!isNaN(data.getTime())) {
                document.getElementById('view_data_abertura').textContent = data.toLocaleDateString('pt-BR');
            } else {
                document.getElementById('view_data_abertura').textContent = '-';
            }
        } catch (error) {
            console.error('Erro ao processar data:', error);
            document.getElementById('view_data_abertura').textContent = '-';
        }
    } else {
        document.getElementById('view_data_abertura').textContent = '-';
    }
    
    // Datas do sistema
    const dataCriacao = filial.created_at || filial.data_criacao;
    if (dataCriacao) {
        try {
            const data = new Date(dataCriacao);
            if (!isNaN(data.getTime())) {
                document.getElementById('view_created_at').textContent = data.toLocaleString('pt-BR');
            } else {
                document.getElementById('view_created_at').textContent = '-';
            }
        } catch (error) {
            console.error('Erro ao processar data de criação:', error);
            document.getElementById('view_created_at').textContent = '-';
        }
    } else {
        document.getElementById('view_created_at').textContent = '-';
    }
    
    const dataAtualizacao = filial.updated_at || filial.data_atualizacao;
    if (dataAtualizacao) {
        try {
            const data = new Date(dataAtualizacao);
            if (!isNaN(data.getTime())) {
                document.getElementById('view_updated_at').textContent = data.toLocaleString('pt-BR');
            } else {
                document.getElementById('view_updated_at').textContent = '-';
            }
        } catch (error) {
            console.error('Erro ao processar data de atualização:', error);
            document.getElementById('view_updated_at').textContent = '-';
        }
    } else {
        document.getElementById('view_updated_at').textContent = '-';
    }
    
    // Armazenar ID da filial atual para edição
    window.filialAtualId = filial.id || filial.id_filial;
}

function editarFilialAtual() {
    // Fechar modal de visualização
    const modalVisualizar = bootstrap.Modal.getInstance(document.getElementById('modalVisualizarFilial'));
    modalVisualizar.hide();
    
    // Abrir modal de edição com a filial atual
    if (window.filialAtualId) {
        setTimeout(() => {
            abrirModalEditarFilial(window.filialAtualId);
        }, 300); // Pequeno delay para garantir que o modal anterior fechou
    }
}

// Função para consultar CNPJ
function consultarCNPJ(cnpj, isEdit = false) {
    // Remover caracteres especiais
    const cnpjLimpo = cnpj.replace(/\D/g, '');
    
    if (cnpjLimpo.length !== 14) {
        Swal.fire({
            icon: 'warning',
            title: 'CNPJ Inválido',
            text: 'Digite um CNPJ válido com 14 dígitos',
            confirmButtonColor: '#3085d6'
        });
        return;
    }
    
    // Mostrar loading
    Swal.fire({
        title: 'Consultando CNPJ...',
        text: 'Aguarde enquanto buscamos os dados da empresa',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Fazer requisição para a API
    fetch(`backend/api/consulta-cnpj.php?cnpj=${cnpjLimpo}`)
        .then(response => response.json())
        .then(data => {
            Swal.close();
            
            if (data.success) {
                // Preencher os campos com os dados retornados
                if (isEdit) {
                    // Formulário de edição
                    document.getElementById('edit_razao_social').value = data.razao_social || '';
                    document.getElementById('edit_endereco').value = data.endereco || '';
                    document.getElementById('edit_numero').value = data.numero || '';
                    document.getElementById('edit_complemento').value = data.complemento || '';
                    document.getElementById('edit_bairro').value = data.bairro || '';
                    document.getElementById('edit_cidade').value = data.cidade || '';
                    document.getElementById('edit_estado').value = data.estado || '';
                    document.getElementById('edit_cep').value = data.cep || '';
                    document.getElementById('edit_telefone').value = data.telefone || '';
                    document.getElementById('edit_email').value = data.email || '';
                    document.getElementById('edit_inscricao_estadual').value = data.inscricao_estadual || '';
                    
                    // Preencher também o nome da filial com o nome fantasia se disponível
                    if (data.nome_fantasia && data.nome_fantasia.trim() !== '') {
                        document.getElementById('edit_nome').value = data.nome_fantasia || '';
                    }
                    
                    // Preencher observações com informações adicionais
                    let observacoes = [];
                    if (data.situacao) observacoes.push(`Situação: ${data.situacao}`);
                    if (data.porte) observacoes.push(`Porte: ${data.porte}`);
                    if (data.natureza_juridica) observacoes.push(`Natureza Jurídica: ${data.natureza_juridica}`);
                    if (data.capital_social) observacoes.push(`Capital Social: R$ ${parseFloat(data.capital_social).toLocaleString('pt-BR')}`);
                    if (data.data_abertura) observacoes.push(`Data de Abertura: ${data.data_abertura}`);
                    
                    if (observacoes.length > 0) {
                        document.getElementById('edit_observacoes').value = observacoes.join(' | ');
                    }
                } else {
                    // Formulário de nova filial
                    document.getElementById('razao_social').value = data.razao_social || '';
                    document.getElementById('endereco').value = data.endereco || '';
                    document.getElementById('numero').value = data.numero || '';
                    document.getElementById('complemento').value = data.complemento || '';
                    document.getElementById('bairro').value = data.bairro || '';
                    document.getElementById('cidade').value = data.cidade || '';
                    document.getElementById('estado').value = data.estado || '';
                    document.getElementById('cep').value = data.cep || '';
                    document.getElementById('telefone').value = data.telefone || '';
                    document.getElementById('email').value = data.email || '';
                    document.getElementById('inscricao_estadual').value = data.inscricao_estadual || '';
                    
                    // Preencher também o nome da filial com o nome fantasia se disponível
                    if (data.nome_fantasia && data.nome_fantasia.trim() !== '') {
                        document.getElementById('nome').value = data.nome_fantasia || '';
                    }
                    
                    // Preencher observações com informações adicionais
                    let observacoes = [];
                    if (data.situacao) observacoes.push(`Situação: ${data.situacao}`);
                    if (data.porte) observacoes.push(`Porte: ${data.porte}`);
                    if (data.natureza_juridica) observacoes.push(`Natureza Jurídica: ${data.natureza_juridica}`);
                    if (data.capital_social) observacoes.push(`Capital Social: R$ ${parseFloat(data.capital_social).toLocaleString('pt-BR')}`);
                    if (data.data_abertura) observacoes.push(`Data de Abertura: ${data.data_abertura}`);
                    
                    if (observacoes.length > 0) {
                        document.getElementById('observacoes').value = observacoes.join(' | ');
                    }
                }
                
                // Mostrar sucesso
                Swal.fire({
                    icon: 'success',
                    title: 'CNPJ Encontrado!',
                    text: `Dados da empresa "${data.razao_social}" foram preenchidos automaticamente.`,
                    confirmButtonColor: '#28a745'
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'CNPJ não encontrado',
                    text: data.error || 'Não foi possível encontrar os dados deste CNPJ',
                    confirmButtonColor: '#dc3545'
                });
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            Swal.fire({
                icon: 'error',
                title: 'Erro na consulta',
                text: 'Erro ao consultar o CNPJ. Tente novamente.',
                confirmButtonColor: '#dc3545'
            });
        });
}

// Função para configurar consulta CNPJ nos formulários
function configurarConsultaCNPJ() {
    // Configurar para formulário de nova filial
    const cnpjInput = document.getElementById('cnpj');
    if (cnpjInput) {
        cnpjInput.addEventListener('blur', function() {
            const cnpj = this.value.trim();
            if (cnpj.length >= 14) {
                consultarCNPJ(cnpj, false);
            }
        });
    }
    
    // Configurar para formulário de edição
    const editCnpjInput = document.getElementById('edit_cnpj');
    if (editCnpjInput) {
        editCnpjInput.addEventListener('blur', function() {
            const cnpj = this.value.trim();
            if (cnpj.length >= 14) {
                consultarCNPJ(cnpj, true);
            }
        });
    }
}

// Inicializar máscaras quando o modal for aberto
document.addEventListener('DOMContentLoaded', function() {
    // Configurar máscaras e auto-completar
    aplicarMascaras();
    configurarAutoCEP();
    aplicarMascarasEdicao();
    configurarAutoCEPEdicao();
    configurarConsultaCNPJ();
    
    // Configurar evento para quando o modal for aberto
    const modalNovaFilial = document.getElementById('modalNovaFilial');
    if (modalNovaFilial) {
        modalNovaFilial.addEventListener('shown.bs.modal', function() {
            // Focar no primeiro campo
            document.getElementById('codigo').focus();
        });
    }
    
    // Configurar evento para quando o modal de edição for aberto
    const modalEditarFilial = document.getElementById('modalEditarFilial');
    if (modalEditarFilial) {
        modalEditarFilial.addEventListener('shown.bs.modal', function() {
            // Focar no primeiro campo
            document.getElementById('edit_codigo').focus();
        });
    }
});
