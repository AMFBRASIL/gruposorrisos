// Variáveis globais
let paginaAtual = 1;
let itensPedido = [];
let materiais = [];
let fornecedores = [];
let filiais = [];
let materiaisEstoqueBaixo = [];
let statusPedidoEmEdicao = null;
let itensOriginaisEdicao = new Map();
let itensRemovidosEdicao = new Set();
let modalProcessandoPedidoInstance = null;

function mostrarModalProcessandoPedido(mensagem = 'Processando dados...') {
    const modalElement = document.getElementById('modalProcessandoPedido');
    const textoElement = document.getElementById('texto-modal-processando-pedido');
    if (!modalElement) return;

    if (textoElement) {
        textoElement.textContent = mensagem;
    }

    if (!modalProcessandoPedidoInstance) {
        modalProcessandoPedidoInstance = new bootstrap.Modal(modalElement, {
            backdrop: 'static',
            keyboard: false
        });
    }

    modalProcessandoPedidoInstance.show();
}

function ocultarModalProcessandoPedido() {
    const modalElement = document.getElementById('modalProcessandoPedido');
    if (!modalElement) return;

    const instancia = bootstrap.Modal.getInstance(modalElement) || modalProcessandoPedidoInstance;
    if (instancia) {
        instancia.hide();
    }

    // Fallback para casos em que o backdrop fica preso.
    setTimeout(() => {
        modalElement.classList.remove('show');
        modalElement.style.display = 'none';
        modalElement.setAttribute('aria-hidden', 'true');
        modalElement.removeAttribute('aria-modal');
        modalElement.removeAttribute('role');

        document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('overflow');
        document.body.style.removeProperty('padding-right');
    }, 250);
}

function pedidoPodeSerEditado(status) {
    const statusBloqueados = [
        'enviar_para_faturamento',
        'aprovado_para_faturar',
        'enviado',
        'em_transito',
        'entregue',
        'recebido',
        'cancelado'
    ];
    return !statusBloqueados.includes((status || '').toLowerCase());
}

// Funções de prioridade
function ajustarPrazoEntrega() {
    const prioridade = document.getElementById('novo_prioridade').value;
    const prazoInput = document.getElementById('novo_prazo_entrega');
    const tituloMateriais = document.getElementById('titulo-materiais');
    const subtituloMateriais = document.getElementById('subtitulo-materiais');
    const pesquisaMaterial = document.getElementById('pesquisa-material');
    const filtroMateriaisBaixo = document.getElementById('filtro-materiais-baixo');
    
    switch(prioridade) {
        case 'padrao':
            prazoInput.value = 8;
            tituloMateriais.textContent = 'Materiais com Estoque Baixo/Negativo';
            subtituloMateriais.textContent = 'Selecione uma Clínica e um fornecedor para carregar os materiais';
            pesquisaMaterial.style.display = 'none';
            if (filtroMateriaisBaixo) filtroMateriaisBaixo.style.display = 'block';
            break;
        case 'critico':
            prazoInput.value = 3;
            tituloMateriais.textContent = 'Materiais para Pedido Crítico';
            subtituloMateriais.textContent = 'Pesquise e selecione os materiais necessários';
            pesquisaMaterial.style.display = 'block';
            if (filtroMateriaisBaixo) filtroMateriaisBaixo.style.display = 'none';
            // Configurar autocomplete para pedidos críticos
            setTimeout(() => configurarAutocompletePesquisa(), 100);
            break;
        case 'urgente':
            prazoInput.value = 1;
            tituloMateriais.textContent = 'Materiais para Pedido Urgente';
            subtituloMateriais.textContent = 'Pesquise e selecione os materiais necessários';
            pesquisaMaterial.style.display = 'block';
            if (filtroMateriaisBaixo) filtroMateriaisBaixo.style.display = 'none';
            // Configurar autocomplete para pedidos urgentes
            setTimeout(() => configurarAutocompletePesquisa(), 100);
            break;
    }
    
    // Limpar materiais quando mudar prioridade
    document.getElementById('materiais-container').innerHTML = '';
    limparFiltroMateriaisEstoqueBaixo();
    
    // Limpar tabela de materiais pesquisados se existir
    const tabelaPesquisados = document.querySelector('.tabela-materiais-pesquisados');
    if (tabelaPesquisados) {
        tabelaPesquisados.remove();
    }
    
    itensPedido = [];
    calcularTotalPedido();
}

// Adicionar material ao pedido
function adicionarMaterialPedido(idMaterial, codigo, nome, precoUnitario, unidade) {
    const quantidadeInput = document.getElementById(`qtd_${idMaterial}`);
    const quantidade = parseFloat(quantidadeInput.value) || 0;
    
    if (quantidade <= 0) {
        mostrarErro('Informe uma quantidade válida');
        return;
    }
    
    // Verificar se o material já está no pedido
    const itemExistente = itensPedido.find(item => item.id_material == idMaterial);
    
    if (itemExistente) {
        // Atualizar quantidade existente
        itemExistente.quantidade = quantidade;
        itemExistente.valor_total = quantidade * precoUnitario;
    } else {
        // Adicionar novo item
        itensPedido.push({
            id_material: idMaterial,
            codigo: codigo,
            nome: nome,
            quantidade: quantidade,
            preco_unitario: precoUnitario,
            valor_total: quantidade * precoUnitario,
            unidade: unidade
        });
    }
    
    // Atualizar interface
    atualizarInterfaceItens();
    calcularTotalPedido();
    
    // Limpar campo de quantidade
    quantidadeInput.value = '';
    
    mostrarSucesso(`Material ${nome} adicionado ao pedido`);
}

// Atualizar interface dos itens do pedido
function atualizarInterfaceItens() {
    const container = document.getElementById('materiais-container');
    
    // Limpar container
    container.innerHTML = '';
    
    if (itensPedido.length === 0) {
        container.innerHTML = '<p class="text-muted text-center">Nenhum material adicionado ao pedido</p>';
        return;
    }
    
    // Renderizar itens do pedido
    itensPedido.forEach((item, index) => {
        const itemDiv = document.createElement('div');
        itemDiv.className = 'material-item border rounded p-3 mb-2 bg-light';
        itemDiv.innerHTML = `
            <div class="row align-items-center">
                <div class="col-md-3">
                    <strong>${item.codigo}</strong>
                    <br><small class="text-muted">${item.nome}</small>
                </div>
                <div class="col-md-2">
                    <small class="text-muted">Estoque: ${item.estoque_atual || 'N/A'} ${item.unidade}</small>
                </div>
                <div class="col-md-2">
                    <small class="text-muted">Preço: ${formatarMoeda(item.preco_unitario)}</small>
                </div>
                <div class="col-md-2">
                    <strong>Qtd: ${item.quantidade} ${item.unidade}</strong>
                </div>
                <div class="col-md-2">
                    <strong class="text-success">Total: ${formatarMoeda(item.valor_total)}</strong>
                </div>
                <div class="col-md-1">
                    <button class="btn btn-sm btn-outline-danger" onclick="removerMaterialPedido(${index})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;
        container.appendChild(itemDiv);
    });
}

// Remover material do pedido
function removerMaterialPedido(index) {
    itensPedido.splice(index, 1);
    atualizarInterfaceItens();
    calcularTotalPedido();
    mostrarSucesso('Material removido do pedido');
}

// Pesquisar material para pedidos críticos/urgentes
async function pesquisarMaterial() {
    const busca = document.getElementById('busca-material').value.trim();
    const idFilial = document.getElementById('novo_id_filial').value;
    const idFornecedor = document.getElementById('novo_id_fornecedor').value;
    
    console.log('🔍 Pesquisando material:', { busca, idFilial, idFornecedor });
    
    if (!busca) {
        mostrarErro('Digite um termo para pesquisa');
        return;
    }
    
    if (!idFilial || !idFornecedor) {
        mostrarErro('Selecione uma Clínica e um Fornecedor primeiro');
        return;
    }
    
    try {
        const params = new URLSearchParams({
            action: 'pesquisar_material',
            busca: busca,
            id_filial: idFilial,
            id_fornecedor: idFornecedor
        });
        
        console.log('📡 Fazendo requisição para:', `backend/api/pedidos_compra.php?${params}`);
        
        const response = await fetch(`backend/api/pedidos_compra.php?${params}`, {
            credentials: 'same-origin'
        });
        
        const data = await response.json();
        console.log('📋 Resposta da API:', data);
        
        if (data.success && data.data && data.data.length > 0) {
            console.log(`✅ ${data.data.length} materiais encontrados`);
            renderizarResultadoPesquisa(data.data);
        } else {
            console.log('❌ Nenhum material encontrado');
            mostrarErro('Nenhum material encontrado com esses critérios');
        }
    } catch (error) {
        console.error('Erro ao pesquisar material:', error);
        mostrarErro('Erro ao pesquisar material');
    }
}

// Configurar autocomplete para pesquisa de materiais
function configurarAutocompletePesquisa() {
    const input = document.getElementById('busca-material');
    const results = document.getElementById('autocomplete-results');
    
    if (!input || !results) return;
    
    input.addEventListener('input', async function() {
        const query = this.value.trim();
        
        if (query.length < 2) {
            results.style.display = 'none';
            return;
        }
        
        const idFilial = document.getElementById('novo_id_filial').value;
        const idFornecedor = document.getElementById('novo_id_fornecedor').value;
        
        if (!idFilial || !idFornecedor) {
            results.style.display = 'none';
            return;
        }
        
        try {
            const params = new URLSearchParams({
                action: 'pesquisar_material',
                busca: query,
                id_filial: idFilial,
                id_fornecedor: idFornecedor
            });
            
            const response = await fetch(`backend/api/pedidos_compra.php?${params}`, {
                credentials: 'same-origin'
            });
            
            const data = await response.json();
            
            if (data.success && data.data.length > 0) {
                results.innerHTML = data.data.map(material => 
                    `<div class="autocomplete-item" data-material-id="${material.id_material}" data-material-codigo="${material.codigo}" data-material-nome="${material.nome}" data-material-preco="${material.preco_unitario}" data-material-unidade="${material.unidade_medida}" data-material-estoque="${material.estoque_atual}">
                        <strong>${material.codigo}</strong> - ${material.nome}
                        <br><small class="text-muted">Preço: ${formatarMoeda(material.preco_unitario)} | Estoque: ${material.estoque_atual} ${material.unidade_medida}</small>
                    </div>`
                ).join('');
                results.style.display = 'block';
            } else {
                results.style.display = 'none';
            }
        } catch (error) {
            console.error('Erro no autocomplete:', error);
            results.style.display = 'none';
        }
    });
    
    // Selecionar material do autocomplete
    results.addEventListener('click', function(e) {
        if (e.target.classList.contains('autocomplete-item')) {
            const materialId = e.target.dataset.materialId;
            const materialCodigo = e.target.dataset.materialCodigo;
            const materialNome = e.target.dataset.materialNome;
            const materialPreco = e.target.dataset.materialPreco;
            const materialUnidade = e.target.dataset.materialUnidade;
            const materialEstoque = e.target.dataset.materialEstoque;
            
            // Preencher campo de busca
            input.value = `${materialCodigo} - ${materialNome}`;
            results.style.display = 'none';
            
            // Renderizar material selecionado diretamente (sem fazer nova pesquisa)
            const materialSelecionado = [{
                id_material: materialId,
                codigo: materialCodigo,
                nome: materialNome,
                preco_unitario: materialPreco,
                unidade_medida: materialUnidade,
                estoque_atual: materialEstoque
            }];
            
            renderizarResultadoPesquisa(materialSelecionado);
        }
    });
    
    // Esconder resultados quando clicar fora
    document.addEventListener('click', function(e) {
        if (!input.contains(e.target) && !results.contains(e.target)) {
            results.style.display = 'none';
        }
    });
}

// Renderizar resultado da pesquisa
function renderizarResultadoPesquisa(materiais) {
    console.log('🔧 Renderizando resultado da pesquisa:', materiais);
    
    const container = document.getElementById('materiais-container');
    if (!container) {
        console.error('❌ Container de materiais não encontrado');
        return;
    }
    
    // Verificar se já existe uma tabela de materiais pesquisados
    let tabelaPesquisados = container.querySelector('.tabela-materiais-pesquisados');
    
    if (!tabelaPesquisados) {
        // Criar nova tabela para materiais pesquisados
        tabelaPesquisados = document.createElement('div');
        tabelaPesquisados.className = 'tabela-materiais-pesquisados mb-4';
        tabelaPesquisados.innerHTML = `
            <h6 class="mb-3 text-primary">
                <i class="bi bi-search me-2"></i>Materiais Selecionados para Pedido
            </h6>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Material</th>
                            <th>Estoque</th>
                            <th>Preço Unit.</th>
                            <th>Quantidade</th>
                            <th>Valor Unit.</th>
                            <th>Total</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-materiais-pesquisados">
                    </tbody>
                </table>
            </div>
        `;
        
        // Adicionar a nova tabela ao container (após os materiais com estoque baixo)
        container.appendChild(tabelaPesquisados);
    }
    
    // Adicionar materiais à tabela existente
    const tbody = tabelaPesquisados.querySelector('#tbody-materiais-pesquisados');
    
    materiais.forEach((material, index) => {
        console.log(`🔧 Adicionando material pesquisado ${index}:`, material);
        
        // Verificar se o material já existe na tabela
        const materialExistente = tbody.querySelector(`[data-material-id="${material.id_material}"]`);
        if (materialExistente) {
            console.log(`⚠️ Material ${material.codigo} já existe na tabela`);
            return;
        }
        
        const tr = document.createElement('tr');
        tr.setAttribute('data-material-id', material.id_material);
        tr.innerHTML = `
            <td>
                <strong>${material.codigo}</strong><br>
                <small class="text-muted">${material.nome}</small>
            </td>
            <td>
                <span class="badge bg-secondary">${material.estoque_atual || 0} ${material.unidade_medida || 'UN'}</span>
            </td>
            <td>
                <small class="text-muted">${formatarMoeda(material.preco_unitario || 0)}</small>
            </td>
            <td>
                <input type="number" class="form-control form-control-sm quantidade-pesquisada" 
                       data-material-id="${material.id_material}"
                       placeholder="0" min="0.001" step="0.001" 
                       onchange="calcularTotalMaterialPesquisado('${material.id_material}')">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm valor-unitario-pesquisado" 
                       data-material-id="${material.id_material}"
                       value="${formatarMoeda(material.preco_unitario || 0)}"
                       placeholder="R$ 0,00"
                       oninput="aplicarMascaraMoeda(event); calcularTotalMaterialPesquisado('${material.id_material}')"
                       onblur="aplicarMascaraMoeda(event)">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm valor-total-material-pesquisado" 
                       data-material-id="${material.id_material}"
                       value="${formatarMoeda(0)}"
                       readonly>
            </td>
            <td>
                <button class="btn btn-sm btn-outline-danger" onclick="removerMaterialPesquisado('${material.id_material}')">
                    <i class="bi bi-trash"></i>
                </button>
                <button class="btn btn-sm btn-outline-info ms-1" onclick="testarCalculo('${material.id_material}')" title="Testar cálculo">
                    <i class="bi bi-calculator"></i>
                </button>
            </td>
        `;
        
        tbody.appendChild(tr);
    });
    
    console.log('✅ Materiais pesquisados adicionados com sucesso');
    console.log('🔍 Total de materiais na tabela:', tbody.children.length);
    
    // Verificar se os campos foram criados corretamente
    setTimeout(() => {
        const camposQuantidade = tbody.querySelectorAll('.quantidade-pesquisada');
        const camposValorUnitario = tbody.querySelectorAll('.valor-unitario-pesquisado');
        const camposValorTotal = tbody.querySelectorAll('.valor-total-material-pesquisado');
        
        console.log('🔍 Verificação dos campos criados:', {
            quantidade: camposQuantidade.length,
            valorUnitario: camposValorUnitario.length,
            valorTotal: camposValorTotal.length
        });
        
        // Verificar se os event listeners estão funcionando
        camposQuantidade.forEach((campo, index) => {
            console.log(`🔍 Campo quantidade ${index}:`, {
                id: campo.id,
                className: campo.className,
                onchange: campo.onchange ? 'Definido' : 'Não definido'
            });
        });
    }, 100);
    
    // Calcular total inicial
    calcularTotalPedido();
    filtrarItensPedidoExistente();
}

function normalizarTextoComparacao(valor) {
    return (valor || '')
        .toString()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim();
}

function parseNumeroCSV(valor) {
    if (valor === null || valor === undefined) return 0;

    let texto = String(valor).trim();
    if (!texto) return 0;

    texto = texto.replace(/\s/g, '').replace(/R\$/gi, '');

    if (texto.includes(',')) {
        texto = texto.replace(/\./g, '').replace(',', '.');
        return parseFloat(texto) || 0;
    }

    if (/^\d{1,3}(\.\d{3})+$/.test(texto)) {
        texto = texto.replace(/\./g, '');
        return parseFloat(texto) || 0;
    }

    return parseFloat(texto) || 0;
}

function configurarBotaoImportacaoCsvCliente(ativo) {
    const btnImportar = document.getElementById('btn-importar-csv-cliente-edicao');
    const inputCsv = document.getElementById('input-csv-cliente-edicao');
    const badgeImportados = document.getElementById('badge-itens-importados-csv');

    if (!btnImportar || !inputCsv) return;

    btnImportar.classList.toggle('d-none', !ativo);
    if (badgeImportados) {
        badgeImportados.classList.toggle('d-none', !ativo);
        badgeImportados.textContent = 'Itens importados: 0';
    }
    if (!ativo) {
        inputCsv.value = '';
    }
    renderizarItensNaoEncontradosCsv([]);
}

function renderizarItensNaoEncontradosCsv(itensNaoEncontrados = []) {
    const box = document.getElementById('itens-nao-encontrados-csv-box');
    const lista = document.getElementById('itens-nao-encontrados-csv-lista');

    if (!box || !lista) return;

    const unicos = [...new Set((itensNaoEncontrados || []).map(item => (item || '').toString().trim()).filter(Boolean))];
    if (unicos.length === 0) {
        box.classList.add('d-none');
        lista.innerHTML = '';
        return;
    }

    lista.innerHTML = unicos.map(item => `<span class="badge bg-danger me-1 mb-1">${item}</span>`).join('');
    box.classList.remove('d-none');
}

function processarCsvClienteEdicao(textoCsv) {
    const linhas = textoCsv.split(/\r?\n/).map(l => l.trim()).filter(Boolean);
    if (linhas.length < 2) {
        mostrarErro('CSV inválido: arquivo sem linhas de itens.');
        return;
    }

    const delimitador = linhas[0].includes(';') ? ';' : ',';
    const cabecalho = linhas[0].split(delimitador).map(col => normalizarTextoComparacao(col).replace(/[^a-z0-9]/g, ''));

    let idxModelo = -1;
    let idxProduto = -1;
    let idxQuant = -1;
    let idxUnitario = -1;
    let idxTotal = -1;

    cabecalho.forEach((coluna, indice) => {
        if (idxModelo === -1 && coluna.startsWith('modelo')) idxModelo = indice;
        if (idxProduto === -1 && coluna.startsWith('produto')) idxProduto = indice;
        if (idxQuant === -1 && (coluna.startsWith('quant') || coluna.startsWith('quat'))) idxQuant = indice;
        if (idxUnitario === -1 && coluna.startsWith('unit')) idxUnitario = indice;
        if (idxTotal === -1 && coluna.startsWith('total')) idxTotal = indice;
    });

    if (idxModelo === -1 || idxProduto === -1 || idxQuant === -1 || idxUnitario === -1 || idxTotal === -1) {
        mostrarErro('CSV inválido: colunas esperadas não encontradas (Modelo, Produto, Quant, Unitario, Total).');
        return;
    }

    const mapaMateriaisPorCodigo = new Map();
    const mapaMateriaisPorNome = new Map();
    materiais.forEach(material => {
        const codigo = (material.codigo || '').toString().trim();
        const nomeNormalizado = normalizarTextoComparacao(material.nome || '');
        if (codigo) mapaMateriaisPorCodigo.set(codigo, material);
        if (nomeNormalizado) mapaMateriaisPorNome.set(nomeNormalizado, material);
    });

    const itensImportadosMap = new Map();
    const itensNaoEncontrados = [];

    for (let i = 1; i < linhas.length; i++) {
        const colunas = linhas[i].split(delimitador);
        if (!colunas.length) continue;

        const codigoCsv = (colunas[idxModelo] || '').toString().trim();
        const nomeCsv = (colunas[idxProduto] || '').toString().trim();
        const quantidade = parseNumeroCSV(colunas[idxQuant]);
        const valorUnitario = parseNumeroCSV(colunas[idxUnitario]);
        const totalCsv = parseNumeroCSV(colunas[idxTotal]);

        if (!codigoCsv && !nomeCsv) continue;
        if (quantidade <= 0) continue;

        let material = mapaMateriaisPorCodigo.get(codigoCsv);
        if (!material && nomeCsv) {
            material = mapaMateriaisPorNome.get(normalizarTextoComparacao(nomeCsv));
        }

        if (!material) {
            itensNaoEncontrados.push(codigoCsv || nomeCsv);
            continue;
        }

        const chave = String(material.id_material);
        const existente = itensImportadosMap.get(chave);
        const totalLinha = totalCsv > 0 ? totalCsv : (quantidade * valorUnitario);

        if (existente) {
            existente.quantidade += quantidade;
            existente.total += totalLinha;
            if (valorUnitario > 0) existente.valorUnitario = valorUnitario;
        } else {
            itensImportadosMap.set(chave, {
                material,
                quantidade,
                valorUnitario,
                total: totalLinha
            });
        }
    }

    if (itensImportadosMap.size === 0) {
        mostrarErro('Nenhum item do CSV foi associado ao catálogo de materiais.');
        return;
    }

    const materiaisParaRenderizar = Array.from(itensImportadosMap.values()).map(({ material, valorUnitario }) => ({
        id_material: material.id_material,
        codigo: material.codigo,
        nome: material.nome,
        preco_unitario: valorUnitario > 0 ? valorUnitario : parseFloat(material.preco_unitario || 0),
        unidade_medida: material.unidade_medida || 'UN',
        estoque_atual: material.estoque_atual || 0
    }));

    renderizarResultadoPesquisa(materiaisParaRenderizar);

    itensImportadosMap.forEach((itemImportado, materialId) => {
        const inputQtd = document.querySelector(`input.quantidade-pesquisada[data-material-id="${materialId}"]`);
        const inputUnit = document.querySelector(`input.valor-unitario-pesquisado[data-material-id="${materialId}"]`);
        const inputTotal = document.querySelector(`input.valor-total-material-pesquisado[data-material-id="${materialId}"]`);

        if (inputQtd) inputQtd.value = itemImportado.quantidade;
        if (inputUnit) inputUnit.value = formatarMoeda(itemImportado.valorUnitario || 0);
        if (inputTotal) inputTotal.value = formatarMoeda(itemImportado.total || 0);
    });

    calcularTotalPedido();
    const badgeImportados = document.getElementById('badge-itens-importados-csv');
    if (badgeImportados) {
        badgeImportados.textContent = `Itens importados: ${itensImportadosMap.size}`;
    }

    const qtdNaoEncontrados = itensNaoEncontrados.length;
    renderizarItensNaoEncontradosCsv(itensNaoEncontrados);
    if (qtdNaoEncontrados > 0) {
        mostrarSucesso(`CSV importado: ${itensImportadosMap.size} item(ns) aplicado(s). ${qtdNaoEncontrados} item(ns) não foram encontrados no catálogo.`);
    } else {
        mostrarSucesso(`CSV importado com sucesso: ${itensImportadosMap.size} item(ns) adicionado(s).`);
    }
}

function importarCsvClienteNaEdicao(arquivo) {
    if (!arquivo) return;

    const nomeArquivo = (arquivo.name || '').toLowerCase();
    if (!nomeArquivo.endsWith('.csv')) {
        mostrarErro('Selecione um arquivo CSV válido.');
        return;
    }

    const reader = new FileReader();
    reader.onload = (event) => {
        try {
            processarCsvClienteEdicao(event.target?.result || '');
        } catch (error) {
            console.error('Erro ao processar CSV do cliente:', error);
            mostrarErro('Não foi possível processar o CSV informado.');
        }
    };
    reader.onerror = () => mostrarErro('Erro ao ler o arquivo CSV.');
    reader.readAsText(arquivo, 'ISO-8859-1');
}

// Função de teste para debug
function testarCalculo(materialId) {
    console.log(`🧪 Testando cálculo para material ${materialId}`);
    
    // Verificar se a função está sendo chamada
    console.log(`🧪 Função testarCalculo chamada com ID: ${materialId}`);
    
    // Chamar a função de cálculo
    calcularTotalMaterialPesquisado(materialId);
}

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    carregarEstatisticas();
    carregarPedidos();
    carregarFornecedores();
    carregarFiliais();
    carregarMateriais();
    
    // Event listeners
    document.getElementById('busca').addEventListener('input', debounce(function() {
        paginaAtual = 1;
        carregarPedidos();
    }, 500));
    
    document.getElementById('filtro-status').addEventListener('change', function() {
        paginaAtual = 1;
        carregarPedidos();
    });
    
    document.getElementById('filtro-fornecedor').addEventListener('change', function() {
        paginaAtual = 1;
        carregarPedidos();
    });
    
    document.getElementById('data-inicio').addEventListener('change', function() {
        paginaAtual = 1;
        carregarPedidos();
    });
});

// Carregar estatísticas
async function carregarEstatisticas() {
    try {
        const response = await fetch('backend/api/pedidos_compra.php?action=stats', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            const stats = data.stats;
            
            document.getElementById('total-pedidos').textContent = stats.total_pedidos || 0;
            document.getElementById('pedidos-pendentes').textContent = stats.pendentes || 0;
            document.getElementById('em-producao').textContent = stats.em_producao || 0;
            document.getElementById('valor-total').textContent = formatarMoeda(stats.valor_total || 0);
            document.getElementById('texto-total').textContent = `${stats.total_pedidos || 0} pedidos cadastrados`;
        }
    } catch (error) {
        console.error('Erro ao carregar estatísticas:', error);
    }
}

// Carregar pedidos
async function carregarPedidos() {
    try {
        const busca = document.getElementById('busca').value;
        const status = document.getElementById('filtro-status').value;
        const fornecedor = document.getElementById('filtro-fornecedor').value;
        const dataInicio = document.getElementById('data-inicio').value;
        
        const params = new URLSearchParams({
            action: 'list',
            page: paginaAtual,
            limit: 10,
            busca: busca,
            status: status,
            fornecedor: fornecedor,
            data_inicio: dataInicio
        });
        
        const response = await fetch(`backend/api/pedidos_compra.php?${params}`, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            console.log('✅ Pedidos carregados:', data);
            console.log('📊 Dados dos pedidos:', data.data.pedidos);
            
            // Debug: verificar campos de cada pedido
            if (data.data.pedidos && data.data.pedidos.length > 0) {
                console.log('🔍 Primeiro pedido (exemplo):', data.data.pedidos[0]);
                console.log('🔍 Campos disponíveis:', Object.keys(data.data.pedidos[0]));
            }
            
            renderizarTabela(data.data.pedidos);
            renderizarPaginacao(data.data);
            document.getElementById('loading').style.display = 'none';
            document.getElementById('tabela-container').style.display = 'block';
        } else {
            console.error('❌ Erro ao carregar pedidos:', data.error);
            mostrarErro('Erro ao carregar pedidos: ' + data.error);
        }
    } catch (error) {
        console.error('Erro ao carregar pedidos:', error);
        mostrarErro('Erro ao carregar pedidos');
    }
}

// Renderizar tabela
function renderizarTabela(pedidos) {
    const tbody = document.getElementById('pedidos-tbody');
    tbody.innerHTML = '';
    
    if (pedidos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-muted">Nenhum pedido encontrado</td></tr>';
        return;
    }
    
    pedidos.forEach(pedido => {
        console.log('Renderizando pedido:', pedido); // Debug
        
        const row = document.createElement('tr');
        
        // Verificar se a entrega está atrasada
        const entregaAtrasada = verificarEntregaAtrasada(pedido.data_entrega_prevista, pedido.status);
        const classeRow = entregaAtrasada ? 'table-warning' : '';
        
        if (classeRow) {
            row.className = classeRow;
        }
        
        const podeEditarPedido = pedidoPodeSerEditado(pedido.status);
        row.innerHTML = `
            <td><strong>${pedido.numero_pedido || 'N/A'}</strong></td>
            <td>${pedido.nome_fornecedor || 'N/A'}</td>
            <td>${formatarData(pedido.data_solicitacao)}</td>
            <td>${getDataEntregaComIndicador(pedido.data_entrega_prevista, pedido.status)}</td>
            <td class="text-center">${getPrioridadeBadge(pedido.prioridade)}</td>
            <td class="text-center"><strong>${formatarMoeda(pedido.valor_total)}</strong></td>
            <td class="text-center">${getStatusBadge(pedido.status)}</td>
            <td>${pedido.nome_usuario || 'N/A'}</td>
            <td class="text-center">
                <button class="btn btn-sm btn-outline-primary btn-action-simple" onclick="visualizarPedido(${pedido.id_pedido})" title="Visualizar">
                    <i class="bi bi-eye"></i>
                </button>
                <button class="btn btn-sm ${podeEditarPedido ? 'btn-outline-success' : 'btn-outline-warning'} btn-action-simple"
                        onclick="${podeEditarPedido ? `editarPedido(${pedido.id_pedido})` : `mostrarModalEdicaoBloqueada(${pedido.id_pedido})`}"
                        title="${podeEditarPedido ? 'Editar' : 'Edição bloqueada'}">
                    <i class="bi bi-pencil"></i>
                </button>
                ${podeEditarPedido ? `
                <button class="btn btn-sm btn-outline-danger btn-action-simple" onclick="excluirPedido(${pedido.id_pedido})" title="Excluir">
                    <i class="bi bi-trash"></i>
                </button>
                ` : ''}
            </td>
        `;
        tbody.appendChild(row);
    });
    
    console.log(`✅ Tabela renderizada com ${pedidos.length} pedidos`);
}

// Badge de prioridade
function getPrioridadeBadge(prioridade) {
    console.log('Prioridade recebida:', prioridade); // Debug
    
    if (!prioridade) {
        return `<span class="badge badge-secondary">Não definida</span>`;
    }
    
    const prioridadeMap = {
        'padrao': { class: 'badge-secondary', text: 'Padrão' },
        'critico': { class: 'badge-warning', text: 'Crítico' },
        'urgente': { class: 'badge-danger', text: 'Urgente' }
    };
    
    const prioridadeInfo = prioridadeMap[prioridade.toLowerCase()] || { class: 'badge-secondary', text: prioridade || 'Padrão' };
    return `<span class="badge ${prioridadeInfo.class}">${prioridadeInfo.text}</span>`;
}

// Texto de prioridade (sem badge)
function getPrioridadeText(prioridade) {
    const prioridadeMap = {
        'padrao': 'Padrão',
        'critico': 'Crítico',
        'urgente': 'Urgente'
    };
    
    return prioridadeMap[prioridade] || 'Padrão';
}

// Badge de status
function getStatusBadge(status) {
    console.log('Status recebido:', status); // Debug
    
    if (!status) {
        return `<span class="badge badge-secondary">Não definido</span>`;
    }
    
    const statusMap = {
        'em_analise': { class: 'badge-info', text: 'Em Análise' },
        'pendente': { class: 'badge-warning', text: 'Pendente' },
        'aprovado': { class: 'badge-success', text: 'Aprovado' },
        'em_producao': { class: 'badge-primary', text: 'Em Produção' },
        'enviado': { class: 'badge-info', text: 'Enviado' },
        'recebido': { class: 'badge-success', text: 'Recebido' },
        'cancelado': { class: 'badge-danger', text: 'Cancelado' },
        'entregue': { class: 'badge-success', text: 'Entregue' },
        'atrasado': { class: 'badge-danger', text: 'Atrasado' },
        'urgente': { class: 'badge-warning', text: 'Urgente' },
        'em_transito': { class: 'badge-info', text: 'Em Trânsito' },
        'aguardando_aprovacao': { class: 'badge-secondary', text: 'Aguardando Aprovação' },
        'parcialmente_recebido': { class: 'badge-warning', text: 'Parcialmente Recebido' }
    };
    
    const statusInfo = statusMap[status.toLowerCase()] || { class: 'badge-secondary', text: status };
    return `<span class="badge ${statusInfo.class}">${statusInfo.text}</span>`;
}

// Formatar data
function formatarData(data) {
    if (!data || data === '0000-00-00' || data === '0000-00-00 00:00:00') {
        return 'N/A';
    }
    
    // Verificar se a data é válida
    const dataObj = new Date(data);
    if (isNaN(dataObj.getTime()) || dataObj.getFullYear() < 1900) {
        return 'N/A';
    }
    
    return dataObj.toLocaleDateString('pt-BR');
}

// Formatar moeda
function formatarMoeda(valor) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(valor);
}

// Aplicar máscara de moeda brasileira em campos de input
function aplicarMascaraMoeda(event) {
    const input = event.target;
    let value = input.value.replace(/\D/g, ''); // Remove tudo que não é dígito
    
    if (value.length === 0) {
        input.value = '';
        return;
    }
    
    // Converte para número e formata
    const number = parseFloat(value) / 100;
    const valorFormatado = number.toLocaleString('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
    
    // Adicionar prefixo "R$ "
    input.value = `R$ ${valorFormatado}`;
}

// Converter valor formatado (R$ 0,00) para número
function converterMoedaParaNumero(valorFormatado) {
    if (!valorFormatado) return 0;
    
    // Remove R$, espaços e converte vírgula para ponto
    const valorLimpo = valorFormatado
        .replace(/R\$\s?/g, '')
        .replace(/\./g, '')
        .replace(',', '.')
        .trim();
    
    return parseFloat(valorLimpo) || 0;
}

// Verificar se a entrega está atrasada
function verificarEntregaAtrasada(dataEntrega, status) {
    if (!dataEntrega || status === 'recebido' || status === 'entregue' || status === 'cancelado') {
        return false;
    }
    
    const hoje = new Date();
    const entrega = new Date(dataEntrega);
    
    // Considera atrasado se a data de entrega já passou
    return entrega < hoje;
}

// Formatar data de entrega com indicadores visuais
function getDataEntregaComIndicador(dataEntrega, status) {
    if (!dataEntrega || dataEntrega === '0000-00-00' || dataEntrega === '0000-00-00 00:00:00') {
        return 'N/A';
    }
    
    const dataFormatada = formatarData(dataEntrega);
    const atrasada = verificarEntregaAtrasada(dataEntrega, status);
    
    if (atrasada) {
        return `<span class="text-danger font-weight-bold">${dataFormatada} <i class="bi bi-exclamation-triangle" title="Entrega atrasada"></i></span>`;
    }
    
    // Verificar se está próximo do vencimento (3 dias)
    const hoje = new Date();
    const entrega = new Date(dataEntrega);
    const diffDias = Math.ceil((entrega - hoje) / (1000 * 60 * 60 * 24));
    
    if (diffDias <= 3 && diffDias >= 0 && status !== 'recebido' && status !== 'entregue' && status !== 'cancelado') {
        return `<span class="text-warning font-weight-bold">${dataFormatada} <i class="bi bi-clock" title="Entrega próxima"></i></span>`;
    }
    
    return dataFormatada;
}

// Renderizar paginação
function renderizarPaginacao(data) {
    const paginacao = document.getElementById('paginacao');
    const { total, paginas, pagina_atual } = data;
    
    if (paginas <= 1) {
        paginacao.innerHTML = '';
        return;
    }
    
    const paginacaoContainer = document.createElement('div');
    paginacaoContainer.className = 'd-flex align-items-center gap-2';
    
    // Informações
    const info = document.createElement('div');
    info.className = 'text-muted small';
    info.textContent = `Mostrando ${((pagina_atual - 1) * 10) + 1} a ${Math.min(pagina_atual * 10, total)} de ${total} pedidos`;
    paginacaoContainer.appendChild(info);
    
    // Navegação
    const nav = document.createElement('nav');
    nav.innerHTML = '<ul class="pagination pagination-sm mb-0"></ul>';
    const ul = nav.querySelector('ul');
    
    // Botão anterior
    if (pagina_atual > 1) {
        const li = document.createElement('li');
        li.className = 'page-item';
        li.innerHTML = `<button class="page-link" onclick="mudarPagina(${pagina_atual - 1})">Anterior</button>`;
        ul.appendChild(li);
    }
    
    // Páginas
    const inicio = Math.max(1, pagina_atual - 2);
    const fim = Math.min(paginas, pagina_atual + 2);
    
    for (let i = inicio; i <= fim; i++) {
        const li = document.createElement('li');
        li.className = `page-item ${i === pagina_atual ? 'active' : ''}`;
        li.innerHTML = `<button class="page-link" onclick="mudarPagina(${i})">${i}</button>`;
        ul.appendChild(li);
    }
    
    // Botão próximo
    if (pagina_atual < paginas) {
        const li = document.createElement('li');
        li.className = 'page-item';
        li.innerHTML = `<button class="page-link" onclick="mudarPagina(${pagina_atual + 1})">Próximo</button>`;
        ul.appendChild(li);
    }
    
    paginacaoContainer.appendChild(nav);
    paginacao.innerHTML = '';
    paginacao.appendChild(paginacaoContainer);
}

// Mudar página
function mudarPagina(pagina) {
    paginaAtual = pagina;
    carregarPedidos();
}

// Carregar fornecedores
async function carregarFornecedores() {
    try {
        const response = await fetch('backend/api/pedidos_compra.php?action=fornecedores', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            fornecedores = data.fornecedores;
            
            // Preencher select de filtro
            const filtroFornecedor = document.getElementById('filtro-fornecedor');
            filtroFornecedor.innerHTML = '<option value="">Todos os Fornecedores</option>';
            
            fornecedores.forEach(fornecedor => {
                const option = document.createElement('option');
                option.value = fornecedor.id_fornecedor;
                option.textContent = fornecedor.nome_fornecedor;
                filtroFornecedor.appendChild(option);
            });
            
            // Preencher select do modal
            const novoFornecedor = document.getElementById('novo_id_fornecedor');
            novoFornecedor.innerHTML = '<option value="">Selecione um fornecedor</option>';
            
            fornecedores.forEach(fornecedor => {
                const option = document.createElement('option');
                option.value = fornecedor.id_fornecedor;
                option.textContent = fornecedor.nome_fornecedor;
                novoFornecedor.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Erro ao carregar fornecedores:', error);
    }
}

// Carregar filiais
async function carregarFiliais() {
    try {
        const response = await fetch('backend/api/pedidos_compra.php?action=filiais', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            filiais = data.filiais;
            
            // Preencher select de filial no modal
            const novoFilial = document.getElementById('novo_id_filial');
            novoFilial.innerHTML = '<option value="">Selecione uma filial</option>';
            
            filiais.forEach(filial => {
                const option = document.createElement('option');
                option.value = filial.id_filial;
                option.textContent = filial.nome_filial;
                novoFilial.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Erro ao carregar filiais:', error);
    }
}

// Função auxiliar para carregar filiais e obter nome
async function carregarFiliaisParaNome() {
    try {
        const response = await fetch('backend/api/pedidos_compra.php?action=filiais', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            return data.filiais;
        }
        return [];
    } catch (error) {
        console.error('Erro ao carregar filiais:', error);
        return [];
    }
}

// Carregar fornecedores para obter nomes
async function carregarFornecedoresParaNome() {
    try {
        const response = await fetch('backend/api/pedidos_compra.php?action=fornecedores', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            return data.fornecedores;
        } else {
            return [];
        }
    } catch (error) {
        console.error('Erro ao carregar fornecedores para nome:', error);
        return [];
    }
}

// Carregar materiais
async function carregarMateriais() {
    try {
        const response = await fetch('backend/api/pedidos_compra.php?action=materiais', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            materiais = data.materiais;
        }
    } catch (error) {
        console.error('Erro ao carregar materiais:', error);
    }
}

// Calcular quantidade de ressuprimento para um material
function calcularRessuprimentoQuantidade(material) {
    const estoqueAtual = parseFloat(material.estoque_atual || 0);
    const estoqueMinimo = parseFloat(material.estoque_minimo || 0);
    
    if (estoqueAtual <= estoqueMinimo) {
        // Estoque crítico - calcular quantidade para reposição
        const margemSeguranca = estoqueMinimo * 0.5;
        const quantidadeNecessaria = Math.max(0, (estoqueMinimo + margemSeguranca) - estoqueAtual);
        return quantidadeNecessaria.toFixed(2);
    } else {
        // Estoque preventivo - calcular quantidade para manutenção
        const estoqueMaximo = parseFloat(material.estoque_maximo || (estoqueMinimo * 3));
        const quantidadePreventiva = Math.max(0, estoqueMaximo - estoqueAtual);
        return quantidadePreventiva.toFixed(2);
    }
}

// Carregar materiais com estoque baixo
async function carregarMateriaisEstoqueBaixo() {
    const idFilial = document.getElementById('novo_id_filial').value;
    const idFornecedor = document.getElementById('novo_id_fornecedor').value;
    const filtroEstoque = document.getElementById('filtro-estoque-pedido')?.value || 'critico';
    
    if (!idFilial || !idFornecedor) {
        const filtroMateriaisBaixo = document.getElementById('filtro-materiais-baixo');
        if (filtroMateriaisBaixo) filtroMateriaisBaixo.style.display = 'none';
        if (!idFilial && !idFornecedor) {
            document.getElementById('materiais-container').innerHTML = '<div class="text-center text-muted py-4">Selecione uma Clinica e um fornecedor para carregar os materiais</div>';
        } else if (!idFilial) {
            document.getElementById('materiais-container').innerHTML = '<div class="text-center text-muted py-4">Selecione uma Clinica para carregar os materiais</div>';
        } else {
            document.getElementById('materiais-container').innerHTML = '<div class="text-center text-muted py-4">Selecione um fornecedor para carregar os materiais</div>';
        }
        return;
    }
    
    try {
        const response = await fetch(`backend/api/pedidos_compra.php?action=materiais-estoque-baixo&id_filial=${idFilial}&id_fornecedor=${idFornecedor}&filtro_estoque=${encodeURIComponent(filtroEstoque)}`, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            materiaisEstoqueBaixo = data.materiais;
            
            if (materiaisEstoqueBaixo.length === 0) {
                const filtroMateriaisBaixo = document.getElementById('filtro-materiais-baixo');
                if (filtroMateriaisBaixo) filtroMateriaisBaixo.style.display = 'none';
                // Buscar nomes da filial e fornecedor para mostrar no alert
                const filiais = await carregarFiliaisParaNome();
                const fornecedores = await carregarFornecedoresParaNome();
                
                const filialSelecionada = filiais.find(f => f.id_filial == idFilial);
                const fornecedorSelecionado = fornecedores.find(f => f.id_fornecedor == idFornecedor);
                
                const nomeFilial = filialSelecionada ? filialSelecionada.nome_filial : 'a filial selecionada';
                const nomeFornecedor = fornecedorSelecionado ? fornecedorSelecionado.nome_fornecedor : 'o fornecedor selecionado';
                
                // Mostrar SweetAlert informando que não há materiais
                Swal.fire({
                    title: 'Nenhum material encontrado',
                    text: `Não foram encontrados materiais com estoque baixo ou zerado em ${nomeFilial} do fornecedor ${nomeFornecedor}.`,
                    icon: 'info',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#3085d6'
                });
                
                // Limpar container
                document.getElementById('materiais-container').innerHTML = '<div class="text-center text-muted py-4">Nenhum material com estoque baixo encontrado para esta filial e fornecedor</div>';
            } else {
                const filtroMateriaisBaixo = document.getElementById('filtro-materiais-baixo');
                if (filtroMateriaisBaixo && document.getElementById('novo_prioridade')?.value === 'padrao') {
                    filtroMateriaisBaixo.style.display = 'block';
                }
                renderizarMateriaisEstoqueBaixo();
            }
        } else {
            const filtroMateriaisBaixo = document.getElementById('filtro-materiais-baixo');
            if (filtroMateriaisBaixo) filtroMateriaisBaixo.style.display = 'none';
            document.getElementById('materiais-container').innerHTML = '<div class="text-center text-danger py-4">Erro ao carregar materiais</div>';
        }
    } catch (error) {
        console.error('Erro ao carregar materiais com estoque baixo:', error);
        const filtroMateriaisBaixo = document.getElementById('filtro-materiais-baixo');
        if (filtroMateriaisBaixo) filtroMateriaisBaixo.style.display = 'none';
        document.getElementById('materiais-container').innerHTML = '<div class="text-center text-danger py-4">Erro ao carregar materiais</div>';
    }
}

// Renderizar materiais com estoque baixo
function renderizarMateriaisEstoqueBaixo() {
    console.log('🔧 Renderizando materiais com estoque baixo:', materiaisEstoqueBaixo);
    
    const container = document.getElementById('materiais-container');
    
    if (materiaisEstoqueBaixo.length === 0) {
        container.innerHTML = '<div class="text-center text-muted py-4">Nenhum material com estoque baixo encontrado para esta filial</div>';
        return;
    }
    
    let html = `
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Material</th>
                        <th>Estoque Atual</th>
                        <th>Estoque Mínimo</th>
                        <th>Ressuprimento</th>
                        <th>Quantidade Solicitada</th>
                        <th>Valor Unitário</th>
                        <th>Valor Total</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    materiaisEstoqueBaixo.forEach((material, index) => {
        console.log(`🔧 Renderizando material ${index}:`, material);
        
        const statusClass = material.estoque_atual <= 0 ? 'table-danger' : 'table-warning';
        const statusText = material.estoque_atual <= 0 ? 'Zerado' : 'Baixo';
        
        // Calcular ressuprimento
        const ressuprimento = calcularRessuprimentoQuantidade(material);
        
        html += `
            <tr class="${statusClass}">
                <td>
                    <strong>${material.codigo}</strong><br>
                    <small class="text-muted">${material.nome}</small>
                </td>
                <td>
                    <span class="badge bg-${material.estoque_atual <= 0 ? 'danger' : 'warning'}">${material.estoque_atual}</span>
                </td>
                <td>${material.estoque_minimo}</td>
                <td>
                    <span class="badge bg-info">${ressuprimento}</span>
                    <small class="d-block text-muted">${material.estoque_atual <= 0 ? 'Reposição urgente' : 'Reposição preventiva'}</small>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm quantidade-solicitada" 
                           data-material-id="${material.id_material}"
                           data-index="${index}"
                           min="0" step="0.001" 
                           onchange="calcularTotalMaterial(${index})"
                           placeholder="0">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm valor-unitario" 
                           data-material-id="${material.id_material}"
                           data-index="${index}"
                           value="${formatarMoeda(material.preco_unitario || 0)}"
                           placeholder="R$ 0,00"
                           oninput="aplicarMascaraMoeda(event); calcularTotalMaterial(${index})"
                           onblur="aplicarMascaraMoeda(event)">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm valor-total-material" 
                           data-material-id="${material.id_material}"
                           data-index="${index}"
                           readonly>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removerItemEstoqueBaixo(${index})" title="Remover item">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    
    html += `
                </tbody>
            </table>
        </div>
    `;
    
    container.innerHTML = html;
    filtrarMateriaisEstoqueBaixoPorNome();
    
    console.log('✅ Tabela de materiais com estoque baixo renderizada com sucesso');
    console.log('🔍 Campos encontrados:', {
        quantidade: document.querySelectorAll('.quantidade-solicitada').length,
        valorUnitario: document.querySelectorAll('.valor-unitario').length,
        valorTotal: document.querySelectorAll('.valor-total-material').length
    });
    
    calcularTotalPedido();
}

function filtrarMateriaisEstoqueBaixoPorNome() {
    const input = document.getElementById('filtro-nome-material-baixo');
    const termo = (input?.value || '').toLowerCase().trim();
    const linhas = document.querySelectorAll('#materiais-container tbody tr');

    linhas.forEach(linha => {
        const codigoEl = linha.querySelector('td strong');
        const nomeEl = linha.querySelector('td small.text-muted');
        const codigo = (codigoEl?.textContent || '').toLowerCase();
        const nome = (nomeEl?.textContent || '').toLowerCase();
        linha.style.display = (nome.includes(termo) || codigo.includes(termo)) ? '' : 'none';
    });
}

function limparFiltroMateriaisEstoqueBaixo() {
    const input = document.getElementById('filtro-nome-material-baixo');
    if (input) input.value = '';
    filtrarMateriaisEstoqueBaixoPorNome();
}

function configurarBuscaItensPedidoExistente(ativo) {
    const box = document.getElementById('busca-itens-pedido-existente');
    const input = document.getElementById('filtro-itens-pedido-existente');
    const resultado = document.getElementById('resultado-busca-itens-pedido-existente');

    if (!box) return;

    box.classList.toggle('d-none', !ativo);
    if (!ativo) {
        if (input) input.value = '';
        if (resultado) {
            resultado.textContent = 'Informe um termo para conferir os itens deste pedido.';
        }
        document.querySelectorAll('.tabela-materiais-pesquisados tbody tr').forEach(linha => {
            linha.style.display = '';
            linha.classList.remove('table-success');
        });
        return;
    }

    filtrarItensPedidoExistente();
}

function filtrarItensPedidoExistente() {
    const input = document.getElementById('filtro-itens-pedido-existente');
    const resultado = document.getElementById('resultado-busca-itens-pedido-existente');
    const termo = normalizarTextoComparacao(input?.value || '');
    const linhas = document.querySelectorAll('.tabela-materiais-pesquisados tbody tr');

    if (!resultado) return;

    if (linhas.length === 0) {
        resultado.textContent = 'Nenhum item carregado neste pedido.';
        return;
    }

    let encontrados = 0;
    linhas.forEach(linha => {
        const textoLinha = normalizarTextoComparacao(linha.textContent || '');
        const corresponde = !termo || textoLinha.includes(termo);
        linha.style.display = corresponde ? '' : 'none';
        linha.classList.toggle('table-success', Boolean(termo && corresponde));
        if (corresponde) encontrados++;
    });

    if (!termo) {
        resultado.textContent = `Lista com ${linhas.length} item(ns) do pedido. Digite para conferir se um item existe.`;
        return;
    }

    resultado.textContent = encontrados > 0
        ? `${encontrados} item(ns) encontrado(s) na lista já criada.`
        : 'Nenhum item encontrado na lista já criada.';
}

function limparBuscaItensPedidoExistente() {
    const input = document.getElementById('filtro-itens-pedido-existente');
    if (input) input.value = '';
    filtrarItensPedidoExistente();
}

function removerItemEstoqueBaixo(index) {
    const quantidadeInput = document.querySelector(`input[data-index="${index}"].quantidade-solicitada`);
    const valorTotalInput = document.querySelector(`input[data-index="${index}"].valor-total-material`);
    const materialId = quantidadeInput?.getAttribute('data-material-id');

    if (quantidadeInput) {
        quantidadeInput.value = '';
    }
    if (valorTotalInput) {
        valorTotalInput.value = formatarMoeda(0);
    }
    if (materialId) {
        itensRemovidosEdicao.add(String(materialId));
    }

    calcularTotalPedido();
}

function coletarItensSelecionados() {
    const itensMap = new Map();

    const adicionarItem = (idMaterial, quantidade, precoUnitario, valorTotal) => {
        if (!idMaterial || quantidade <= 0) {
            return;
        }

        itensMap.set(String(idMaterial), {
            id_material: String(idMaterial),
            quantidade: quantidade,
            preco_unitario: precoUnitario,
            valor_total: valorTotal
        });
    };

    document.querySelectorAll('.quantidade-solicitada').forEach((input, index) => {
        const quantidade = parseFloat(input.value) || 0;
        const valorUnitarioInput = document.querySelector(`input[data-index="${index}"].valor-unitario`);
        const valorTotalInput = document.querySelector(`input[data-index="${index}"].valor-total-material`);

        if (!valorUnitarioInput || !valorTotalInput) {
            return;
        }

        const valorUnitario = converterMoedaParaNumero(valorUnitarioInput.value);
        const valorTotal = converterMoedaParaNumero(valorTotalInput.value);
        adicionarItem(input.getAttribute('data-material-id'), quantidade, valorUnitario, valorTotal);
    });

    document.querySelectorAll('.quantidade-pesquisada').forEach((input) => {
        const quantidade = parseFloat(input.value) || 0;
        const materialId = input.getAttribute('data-material-id');
        const valorUnitarioInput = document.querySelector(`input[data-material-id="${materialId}"].valor-unitario-pesquisado`);
        const valorTotalInput = document.querySelector(`input[data-material-id="${materialId}"].valor-total-material-pesquisado`);

        if (!valorUnitarioInput || !valorTotalInput) {
            return;
        }

        const valorUnitario = converterMoedaParaNumero(valorUnitarioInput.value);
        const valorTotal = converterMoedaParaNumero(valorTotalInput.value);
        adicionarItem(materialId, quantidade, valorUnitario, valorTotal);
    });

    return Array.from(itensMap.values());
}

// Calcular total de um material específico
function calcularTotalMaterial(index) {
    const material = materiaisEstoqueBaixo[index];
    const quantidadeInput = document.querySelector(`input[data-index="${index}"].quantidade-solicitada`);
    const valorUnitarioInput = document.querySelector(`input[data-index="${index}"].valor-unitario`);
    const valorTotalInput = document.querySelector(`input[data-index="${index}"].valor-total-material`);
    
    const quantidade = parseFloat(quantidadeInput.value) || 0;
    const valorUnitario = converterMoedaParaNumero(valorUnitarioInput.value);
    const valorTotal = quantidade * valorUnitario;
    
    valorTotalInput.value = formatarMoeda(valorTotal);
    calcularTotalPedido();
}

// Calcular total de um material pesquisado (pedidos críticos/urgentes)
function calcularTotalMaterialPesquisado(materialId) {
    console.log(`🔧 Calculando total para material ID: ${materialId}`);
    
    // Debug: mostrar todos os campos com data-material-id
    const todosCampos = document.querySelectorAll(`[data-material-id="${materialId}"]`);
    console.log(`🔍 Debug: Todos os campos com data-material-id="${materialId}":`, todosCampos);
    
    // Buscar elementos diretamente pelo data-material-id
    const quantidadeInput = document.querySelector(`input[data-material-id="${materialId}"].quantidade-pesquisada`);
    const valorUnitarioInput = document.querySelector(`input[data-material-id="${materialId}"].valor-unitario-pesquisado`);
    const valorTotalInput = document.querySelector(`input[data-material-id="${materialId}"].valor-total-material-pesquisado`);
    
    console.log(`🔍 Campos encontrados:`, {
        quantidade: quantidadeInput,
        valorUnitario: valorUnitarioInput,
        valorTotal: valorTotalInput
    });
    
    if (!quantidadeInput || !valorUnitarioInput || !valorTotalInput) {
        console.error(`❌ Elementos não encontrados para material ${materialId}:`, {
            quantidade: !!quantidadeInput,
            valorUnitario: !!valorUnitarioInput,
            valorTotal: !!valorTotalInput
        });
        
        // Tentar buscar de outras formas
        console.log(`🔍 Tentando buscar por classe...`);
        const todosQuantidade = document.querySelectorAll('.quantidade-pesquisada');
        const todosValorUnitario = document.querySelectorAll('.valor-unitario-pesquisado');
        const todosValorTotal = document.querySelectorAll('.valor-total-material-pesquisado');
        
        console.log(`🔍 Total de campos por classe:`, {
            quantidade: todosQuantidade.length,
            valorUnitario: todosValorUnitario.length,
            valorTotal: todosValorTotal.length
        });
        
        return;
    }
    
    const quantidade = parseFloat(quantidadeInput.value) || 0;
    const valorUnitario = converterMoedaParaNumero(valorUnitarioInput.value);
    const valorTotal = quantidade * valorUnitario;
    
    console.log(`📊 Material ${materialId} - Valores: Qtd=${quantidade}, Unit=${valorUnitario}, Total=${valorTotal}`);
    
    valorTotalInput.value = formatarMoeda(valorTotal);
    console.log(`✅ Valor total atualizado: ${valorTotalInput.value}`);
    
    calcularTotalPedido();
}

// Remover material pesquisado da tabela
function removerMaterialPesquisado(materialId) {
    console.log(`🗑️ Removendo material pesquisado: ${materialId}`);
    
    const materialRow = document.querySelector(`[data-material-id="${materialId}"]`);
    if (materialRow) {
        materialRow.remove();
        itensRemovidosEdicao.add(String(materialId));
        console.log(`✅ Material ${materialId} removido com sucesso`);
        
        // Recalcular total
        calcularTotalPedido();
    } else {
        console.error(`❌ Material ${materialId} não encontrado para remoção`);
    }
}

// Abrir modal novo pedido
function abrirModalNovoPedido() {
    itensPedido = [];
    statusPedidoEmEdicao = null;
    itensOriginaisEdicao = new Map();
    itensRemovidosEdicao = new Set();
    configurarBuscaItensPedidoExistente(false);
    document.getElementById('formNovoPedido').reset();
    document.getElementById('materiais-container').innerHTML = '<div class="text-center text-muted py-4">Selecione uma filial e um fornecedor para carregar os materiais</div>';
    document.getElementById('total-pedido-modal').textContent = 'R$ 0,00';
    const totalItensElement = document.getElementById('total-itens-modal');
    const totalQuantidadeElement = document.getElementById('total-quantidade-modal');
    if (totalItensElement) totalItensElement.textContent = '0';
    if (totalQuantidadeElement) totalQuantidadeElement.textContent = '0';
    configurarBotaoImportacaoCsvCliente(false);
    
    // Reabilitar botão de salvar (caso tenha ficado desabilitado)
    const btnSalvar = document.querySelector('#modalNovoPedido .modal-footer .btn-primary');
    if (btnSalvar) {
        btnSalvar.disabled = false;
        btnSalvar.innerHTML = 'Salvar Pedido';
        btnSalvar.removeAttribute('data-original-html');
    }
    
    // Limpar tabela de materiais pesquisados se existir
    const tabelaPesquisados = document.querySelector('.tabela-materiais-pesquisados');
    if (tabelaPesquisados) {
        tabelaPesquisados.remove();
    }
    
    // Adicionar event listeners para os campos
    document.getElementById('novo_id_filial').addEventListener('change', function() {
        // Limpar fornecedor quando filial for alterada
        document.getElementById('novo_id_fornecedor').value = '';
        document.getElementById('materiais-container').innerHTML = '<div class="text-center text-muted py-4">Selecione um fornecedor para carregar os materiais</div>';
        
        // Limpar tabela de materiais pesquisados
        const tabelaPesquisados = document.querySelector('.tabela-materiais-pesquisados');
        if (tabelaPesquisados) {
            tabelaPesquisados.remove();
        }
    });
    
    const modal = new bootstrap.Modal(document.getElementById('modalNovoPedido'));
    modal.show();
}

// Funções antigas removidas - agora usando nova estrutura com materiais de estoque baixo

// Calcular total do pedido
function calcularTotalPedido() {
    let total = 0;
    
    console.log('🔄 Calculando total do pedido...');
    
    // Calcular total dos materiais com estoque baixo
    const camposEstoqueBaixo = document.querySelectorAll('.valor-total-material');
    console.log(`🔍 Campos estoque baixo encontrados: ${camposEstoqueBaixo.length}`);
    
    const totaisEstoqueBaixo = Array.from(camposEstoqueBaixo)
        .map(input => {
            const valor = converterMoedaParaNumero(input.value);
            console.log(`📦 Campo estoque baixo: valor="${input.value}" -> parseado=${valor}`);
            return valor;
        });
    const totalEstoqueBaixo = totaisEstoqueBaixo.reduce((sum, valor) => sum + valor, 0);
    total += totalEstoqueBaixo;
    
    console.log(`📦 Total estoque baixo: ${formatarMoeda(totalEstoqueBaixo)}`);
    
    // Calcular total dos materiais pesquisados (pedidos críticos/urgentes)
    const camposPesquisados = document.querySelectorAll('.valor-total-material-pesquisado');
    console.log(`🔍 Campos pesquisados encontrados: ${camposPesquisados.length}`);
    
    const totaisPesquisados = Array.from(camposPesquisados)
        .map(input => {
            const valor = converterMoedaParaNumero(input.value);
            console.log(`🔍 Campo pesquisado: valor="${input.value}" -> parseado=${valor}`);
            return valor;
        });
    const totalPesquisados = totaisPesquisados.reduce((sum, valor) => sum + valor, 0);
    total += totalPesquisados;
    
    console.log(`🔍 Total pesquisados: ${formatarMoeda(totalPesquisados)}`);
    console.log(`💰 Total geral: ${formatarMoeda(total)}`);
    
    document.getElementById('total-pedido-modal').textContent = formatarMoeda(total);

    const itensSelecionados = coletarItensSelecionados();
    const totalItens = itensSelecionados.length;
    const quantidadeTotal = itensSelecionados.reduce((sum, item) => sum + (parseFloat(item.quantidade) || 0), 0);

    const totalItensElement = document.getElementById('total-itens-modal');
    const totalQuantidadeElement = document.getElementById('total-quantidade-modal');
    if (totalItensElement) totalItensElement.textContent = totalItens;
    if (totalQuantidadeElement) totalQuantidadeElement.textContent = quantidadeTotal.toLocaleString('pt-BR', { maximumFractionDigits: 3 });
}

// Funções de estoque removidas - agora usando nova estrutura

// Salvar novo pedido
async function salvarNovoPedido() {
    // Obter referência ao botão
    const btnSalvar = document.querySelector('#modalNovoPedido .modal-footer .btn-primary');
    
    // Verificar se o botão já está desabilitado (evitar múltiplos cliques)
    if (btnSalvar && btnSalvar.disabled) {
        console.log('⚠️ Botão já está desabilitado, ignorando clique duplicado');
        return;
    }
    
    try {
        const form = document.getElementById('formNovoPedido');
        const formData = new FormData(form);
        
        // Validar filial
        if (!formData.get('id_filial')) {
            mostrarErro('Selecione uma filial');
            return;
        }
        
        // Validar fornecedor
        if (!formData.get('id_fornecedor')) {
            mostrarErro('Selecione um fornecedor');
            return;
        }
        
        // Desabilitar botão e mostrar indicador de carregamento
        if (btnSalvar) {
            btnSalvar.disabled = true;
            const originalText = btnSalvar.innerHTML;
            btnSalvar.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Salvando...';
            
            // Armazenar texto original para restaurar em caso de erro
            btnSalvar.setAttribute('data-original-html', originalText);
        }
        mostrarModalProcessandoPedido('Processando dados do pedido...');
        
        // Coletar itens selecionados sem duplicidade
        const itens = coletarItensSelecionados();
        
        if (itens.length === 0) {
            ocultarModalProcessandoPedido();
            mostrarErro('Adicione pelo menos um item ao pedido');
            if (btnSalvar) {
                btnSalvar.disabled = false;
                btnSalvar.innerHTML = btnSalvar.getAttribute('data-original-html') || 'Salvar Pedido';
            }
            return;
        }
        
        const dados = {
            id_filial: formData.get('id_filial'),
            id_fornecedor: formData.get('id_fornecedor'),
            data_entrega_prevista: formData.get('data_entrega_prevista'),
            prioridade: formData.get('prioridade'),
            prazo_entrega: parseInt(formData.get('prazo_entrega')),
            observacoes: formData.get('observacoes'),
            valor_total: parseFloat(document.getElementById('total-pedido-modal').textContent.replace(/[^\d,]/g, '').replace(',', '.')) || 0,
            itens: itens
        };
        
        const response = await fetch('backend/api/pedidos_compra.php?action=create', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(dados)
        });
        
        const result = await response.json();
        
        if (result.success) {
            ocultarModalProcessandoPedido();
            mostrarSucesso('Pedido criado com sucesso!');
            bootstrap.Modal.getInstance(document.getElementById('modalNovoPedido')).hide();
            carregarPedidos();
            carregarEstatisticas();
            
            // Reabilitar botão após sucesso
            if (btnSalvar) {
                btnSalvar.disabled = false;
                btnSalvar.innerHTML = btnSalvar.getAttribute('data-original-html') || 'Salvar Pedido';
            }
        } else {
            ocultarModalProcessandoPedido();
            mostrarErro(result.error || 'Erro ao criar pedido');
            
            // Reabilitar botão em caso de erro
            if (btnSalvar) {
                btnSalvar.disabled = false;
                btnSalvar.innerHTML = btnSalvar.getAttribute('data-original-html') || 'Salvar Pedido';
            }
        }
    } catch (error) {
        ocultarModalProcessandoPedido();
        console.error('Erro ao salvar pedido:', error);
        mostrarErro('Erro ao salvar pedido');
        
        // Reabilitar botão em caso de exceção
        if (btnSalvar) {
            btnSalvar.disabled = false;
            btnSalvar.innerHTML = btnSalvar.getAttribute('data-original-html') || 'Salvar Pedido';
        }
    }
}

// Visualizar pedido
async function visualizarPedido(id) {
    try {
        const response = await fetch(`backend/api/pedidos_compra.php?action=get&id=${id}`, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            const pedido = data.pedido;
            
            // Debug: verificar campos do pedido
            console.log('📦 Dados completos do pedido:', pedido);
            console.log('📄 URL Nota Fiscal:', pedido.url_nota_fiscal);
            console.log('🔑 Chaves do objeto pedido:', Object.keys(pedido));
            
            // Definir window.pedidoAtual para o chat
            window.pedidoAtual = pedido;
            console.log('window.pedidoAtual definido:', window.pedidoAtual);
            
            // Verificar se o modal existe e está acessível
            const modalElement = document.getElementById('modalVisualizarPedido');
            if (!modalElement) {
                console.error('Modal não encontrado');
                mostrarErro('Erro ao abrir modal de visualização');
                return;
            }
            
            // Verificar se os elementos essenciais existem
            const elementosEssenciais = [
                'view_numero_pedido',
                'view_data_pedido',
                'view_itens_tbody'
            ];
            
            const elementosEssenciaisFaltando = elementosEssenciais.filter(id => !document.getElementById(id));
            if (elementosEssenciaisFaltando.length > 0) {
                console.error('Elementos essenciais faltando:', elementosEssenciaisFaltando);
                mostrarErro('Erro na estrutura do modal. Recarregue a página.');
                return;
            }
            
            // Cabeçalho principal
            document.getElementById('view_numero_pedido').textContent = pedido.numero_pedido;
            document.getElementById('view_data_pedido').textContent = formatarData(pedido.data_solicitacao);
            
            // Status badge e card - com verificação opcional
            const statusBadge = document.getElementById('view-status-badge');
            const statusText = document.getElementById('view-status-text');
            const statusCard = document.getElementById('view-status-card');
            const statusAtivo = document.getElementById('view-status-ativo');
            
            // Debug: verificar quais elementos de status foram encontrados
            console.log('🔍 Verificação dos elementos de status:');
            console.log('  - statusBadge:', statusBadge ? '✅ Encontrado' : '❌ Não encontrado');
            console.log('  - statusText:', statusText ? '✅ Encontrado' : '❌ Não encontrado');
            console.log('  - statusCard:', statusCard ? '✅ Encontrado' : '❌ Não encontrado');
            console.log('  - statusAtivo:', statusAtivo ? '✅ Encontrado' : '❌ Não encontrado');
            
            // Verificar se os elementos de status existem antes de configurar
            if (statusBadge && statusText && statusCard && statusAtivo) {
                // Configurar status com cores apropriadas
                configurarStatusPedido(statusBadge, statusText, statusCard, statusAtivo, pedido.status);
                
                // Configurar botões de ação baseados no status
                configurarBotoesAcao(pedido.status);
            } else {
                console.warn('Alguns elementos de status não encontrados, continuando sem configuração de status');
            }
            
            // Informações do pedido - com verificação individual
            const filialElement = document.getElementById('view_filial');
            const fornecedorElement = document.getElementById('view_fornecedor');
            const dataEntregaElement = document.getElementById('view_data_entrega');
            const solicitanteElement = document.getElementById('view_solicitante');
            const prioridadeElement = document.getElementById('view_prioridade');
            const prazoEntregaElement = document.getElementById('view_prazo_entrega');
            
            if (filialElement) filialElement.textContent = pedido.nome_filial || 'N/A';
            if (fornecedorElement) fornecedorElement.textContent = pedido.nome_fornecedor || 'N/A';
            if (dataEntregaElement) dataEntregaElement.textContent = pedido.data_entrega_prevista ? formatarData(pedido.data_entrega_prevista) : 'Não informado';
            if (solicitanteElement) solicitanteElement.textContent = pedido.nome_usuario_solicitante || 'Sistema';
            if (prioridadeElement) prioridadeElement.textContent = getPrioridadeText(pedido.prioridade || 'padrao');
            if (prazoEntregaElement) prazoEntregaElement.textContent = `${pedido.prazo_entrega || 8} dias`;
            
            // Métricas de itens
            let totalItens = 0;
            let quantidadeTotal = 0;
            let valorTotal = 0;
            let precoMedio = 0;
            
            // Renderizar itens e calcular métricas
            const tbody = document.getElementById('view_itens_tbody');
            if (tbody) {
            tbody.innerHTML = '';
            
            if (pedido.itens && pedido.itens.length > 0) {
                    totalItens = pedido.itens.length;
                    
                pedido.itens.forEach(item => {
                        // Quantidade solicitada (campo quantidade)
                        const quantidadeSolicitada = parseFloat(item.quantidade) || 0;
                        
                        // Acessar quantidade_disponivel (pode ser null, undefined ou string vazia)
                        const quantidadeDisponivelRaw = item.quantidade_disponivel;
                        const quantidadeDisponivel = (quantidadeDisponivelRaw !== null && quantidadeDisponivelRaw !== undefined && quantidadeDisponivelRaw !== '') 
                            ? parseFloat(quantidadeDisponivelRaw) 
                            : null;
                        
                        const precoUnitario = parseFloat(item.preco_unitario) || 0;
                        const precoFornecedorRaw = item.preco_fornecedor;
                        const precoFornecedor = (precoFornecedorRaw !== null && precoFornecedorRaw !== undefined && precoFornecedorRaw !== '') 
                            ? parseFloat(precoFornecedorRaw) 
                            : null;
                        
                        // Acessar disponivel (pode ser null, undefined, 0, 1, '0', '1')
                        const disponivelRaw = item.disponivel;
                        const disponivel = (disponivelRaw !== null && disponivelRaw !== undefined && disponivelRaw !== '') 
                            ? parseInt(disponivelRaw) 
                            : null;
                        
                        // Mesma regra do backend (pedidos-fornecedor.php):
                        // - disponivel = 0 => item não soma
                        // - quantidade base: quantidade_disponivel (se > 0) senão quantidade solicitada
                        // - preço base: preco_fornecedor (se > 0) senão preco_unitario
                        const quantidadeBase = (disponivel === 0)
                            ? 0
                            : ((quantidadeDisponivel !== null && quantidadeDisponivel > 0) ? quantidadeDisponivel : quantidadeSolicitada);
                        const precoParaCalculo = (precoFornecedor !== null && precoFornecedor > 0) ? precoFornecedor : precoUnitario;
                        const valorItem = quantidadeBase * precoParaCalculo;
                        
                        quantidadeTotal += quantidadeSolicitada;
                        valorTotal += valorItem;
                        
                    const row = document.createElement('tr');
                    
                    // Determinar qual preço mostrar (fornecedor se disponível, senão unitário)
                    const precoExibir = precoParaCalculo;
                    
                    // Determinar quantidade disponível a exibir
                    let qtdDisponivelHtml = '';
                    if (disponivel === 1) {
                        // Item está disponível
                        if (quantidadeDisponivel !== null && !isNaN(quantidadeDisponivel)) {
                            if (quantidadeDisponivel === quantidadeSolicitada) {
                                qtdDisponivelHtml = `<span class="badge bg-success">${quantidadeDisponivel}</span>`;
                            } else if (quantidadeDisponivel < quantidadeSolicitada) {
                                qtdDisponivelHtml = `<span class="badge bg-warning">${quantidadeDisponivel}</span> <small class="text-muted">(menor que solicitado)</small>`;
                            } else {
                                qtdDisponivelHtml = `<span class="badge bg-info">${quantidadeDisponivel}</span>`;
                            }
                        } else {
                            // Disponível mas sem quantidade informada (usar quantidade solicitada)
                            qtdDisponivelHtml = `<span class="badge bg-success">${quantidadeSolicitada}</span>`;
                        }
                    } else if (disponivel === 0) {
                        qtdDisponivelHtml = `<span class="badge bg-danger">Não disponível</span>`;
                    } else {
                        // disponivel é NULL ou não foi definido ainda (fornecedor não respondeu)
                        qtdDisponivelHtml = `<span class="text-muted">Aguardando resposta</span>`;
                    }
                    
                    row.innerHTML = `
                            <td>
                                <strong>${item.codigo_material || 'N/A'}</strong><br>
                                <small class="text-muted">${item.nome_material || 'Material não encontrado'}</small>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary">${quantidadeSolicitada}</span>
                                ${(quantidadeBase !== quantidadeSolicitada && quantidadeBase > 0) ? `<br><small class="text-muted">Base cálculo: ${quantidadeBase}</small>` : ''}
                            </td>
                            <td class="text-center">
                                ${qtdDisponivelHtml}
                            </td>
                            <td class="text-center">
                                ${formatarMoeda(precoExibir)}
                                ${(disponivel === 1 && precoFornecedor !== null) ? '<br><small class="text-success">Preço do fornecedor</small>' : ''}
                            </td>
                            <td class="text-center">
                                <strong class="text-success">${formatarMoeda(valorItem)}</strong>
                            </td>
                    `;
                    tbody.appendChild(row);
                });
                    
                    // Calcular preço médio ponderado
                    if (quantidadeTotal > 0) {
                        precoMedio = valorTotal / quantidadeTotal;
                    } else {
                        precoMedio = 0;
                    }
            } else {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Nenhum item encontrado</td></tr>';
                }
            }
            
            // Atualizar métricas - com verificação individual
            const totalItensElement = document.getElementById('view-total-itens');
            const quantidadeTotalElement = document.getElementById('view-quantidade-total');
            const precoMedioElement = document.getElementById('view-preco-medio');
            const valorTotalElement = document.getElementById('view_valor_total');
            
            if (totalItensElement) totalItensElement.textContent = totalItens;
            if (quantidadeTotalElement) quantidadeTotalElement.textContent = quantidadeTotal;
            if (precoMedioElement) precoMedioElement.textContent = formatarMoeda(precoMedio);
            const valorTotalOficial = parseFloat(pedido.valor_total) || valorTotal;
            if (valorTotalElement) valorTotalElement.textContent = formatarMoeda(valorTotalOficial);
            
            // Atualizar total no rodapé da tabela
            const totalFooterElement = document.getElementById('view_itens_total_footer');
            if (totalFooterElement) {
                totalFooterElement.textContent = formatarMoeda(valorTotalOficial);
            }
            
            // Observações
            const observacoesElement = document.getElementById('view_observacoes');
            if (observacoesElement) {
                observacoesElement.textContent = pedido.observacoes || 'Nenhuma observação registrada';
            }
            
            // Nota Fiscal
            console.log('📄 Verificando Nota Fiscal:', {
                url_nota_fiscal: pedido.url_nota_fiscal,
                tipo: typeof pedido.url_nota_fiscal,
                existe: 'url_nota_fiscal' in pedido,
                pedido_completo: pedido
            });
            
            const cardNotaFiscal = document.getElementById('card-nota-fiscal');
            console.log('🔍 Card NF encontrado:', cardNotaFiscal);
            
            if (cardNotaFiscal) {
                // Verificar se existe url_nota_fiscal (pode ser null, undefined, ou string vazia)
                const urlNF = pedido.url_nota_fiscal;
                const temNF = urlNF !== null && urlNF !== undefined && String(urlNF).trim() !== '';
                
                console.log('📋 Status da NF:', {
                    urlNF: urlNF,
                    temNF: temNF,
                    tipo: typeof urlNF
                });
                
                if (temNF) {
                    console.log('✅ NF encontrada, exibindo card');
                    cardNotaFiscal.style.display = 'block';
                    // Armazenar URL da NF no botão para uso posterior
                    const btnVisualizarNF = document.getElementById('btn-visualizar-nf');
                    if (btnVisualizarNF) {
                        btnVisualizarNF.setAttribute('data-nf-url', String(urlNF).trim());
                        console.log('✅ URL da NF armazenada:', String(urlNF).trim());
                    } else {
                        console.error('❌ Botão btn-visualizar-nf não encontrado');
                    }
                } else {
                    console.log('⚠️ NF não encontrada ou vazia, ocultando card');
                    cardNotaFiscal.style.display = 'none';
                }
            } else {
                console.error('❌ Card card-nota-fiscal não encontrado no DOM');
            }
            
            // Histórico
            const dataCriacaoElement = document.getElementById('view-data-criacao');
            const dataAtualizacaoElement = document.getElementById('view-data-atualizacao');
            
            if (dataCriacaoElement) {
                dataCriacaoElement.textContent = formatarData(pedido.data_criacao);
            }
            if (dataAtualizacaoElement) {
                dataAtualizacaoElement.textContent = pedido.data_atualizacao ? formatarData(pedido.data_atualizacao) : 'Não atualizado';
            }
            
            // Armazenar ID para edição
            modalElement.setAttribute('data-pedido-id', id);
            modalElement.setAttribute('data-pedido-status', pedido.status || '');
            
            const btnEditarPedido = document.getElementById('btn-editar-pedido');
            if (btnEditarPedido) {
                btnEditarPedido.classList.remove('d-none');
                btnEditarPedido.classList.toggle('btn-primary', pedidoPodeSerEditado(pedido.status));
                btnEditarPedido.classList.toggle('btn-warning', !pedidoPodeSerEditado(pedido.status));
            }
            
            // Carregar histórico de status
            await carregarHistoricoStatus(id);
            
            // Carregar fluxo de status
            await carregarFluxoStatus(id, pedido.status);
            
            // Verificar se o modal já está aberto
            const modalInstance = bootstrap.Modal.getInstance(modalElement);
            const isModalOpen = modalInstance && modalElement.classList.contains('show');
            
            if (isModalOpen) {
                // Se o modal já está aberto, apenas atualizar os dados sem fechar/abrir
                // Isso evita que o listener de limpeza seja acionado
                console.log('✅ Modal já está aberto, apenas atualizando dados');
            } else {
                // Se o modal não está aberto, abrir normalmente
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            }
        } else {
            mostrarErro('Erro ao carregar dados do pedido');
        }
    } catch (error) {
        console.error('Erro ao carregar pedido:', error);
        mostrarErro('Erro ao carregar pedido: ' + error.message);
    }
}

async function editarPedido(id) {
    const modalElement = document.getElementById('modalVisualizarPedido');
    if (modalElement) {
        modalElement.setAttribute('data-pedido-id', id);
    }
    await editarPedidoAtual();
}

// Configurar status do pedido com cores apropriadas
function configurarStatusPedido(statusBadge, statusText, statusCard, statusAtivo, status) {
    console.log('🎨 Iniciando configuração de status:', status);
    
    // Verificar se todos os elementos foram passados corretamente
    if (!statusBadge || !statusText || !statusCard || !statusAtivo) {
        console.error('❌ Elementos de status não fornecidos corretamente:');
        console.error('  - statusBadge:', statusBadge ? '✅' : '❌');
        console.error('  - statusText:', statusText ? '✅' : '❌');
        console.error('  - statusCard:', statusCard ? '✅' : '❌');
        console.error('  - statusAtivo:', statusAtivo ? '✅' : '❌');
        return;
    }
    
    let statusConfig = {
        icon: 'bi-clock',
        text: 'Pendente',
        color: 'warning',
        bgColor: 'rgba(234, 179, 8, 0.1)',
        borderColor: 'rgba(234, 179, 8, 0.2)',
        textColor: '#d97706'
    };
    
    switch (status) {
        case 'em_analise':
            statusConfig = {
                icon: 'bi-search',
                text: 'Em Análise',
                color: 'info',
                bgColor: 'rgba(6, 182, 212, 0.1)',
                borderColor: 'rgba(6, 182, 212, 0.2)',
                textColor: '#0891b2'
            };
            break;
        case 'pendente':
            statusConfig = {
                icon: 'bi-clock',
                text: 'Pendente',
                color: 'warning',
                bgColor: 'rgba(234, 179, 8, 0.1)',
                borderColor: 'rgba(234, 179, 8, 0.2)',
                textColor: '#d97706'
            };
            break;
        case 'aprovado':
            statusConfig = {
                icon: 'bi-check-circle',
                text: 'Aprovado',
                color: 'success',
                bgColor: 'rgba(34, 197, 94, 0.1)',
                borderColor: 'rgba(34, 197, 94, 0.2)',
                textColor: '#16a34a'
            };
            break;
        case 'em_producao':
            statusConfig = {
                icon: 'bi-gear',
                text: 'Em Produção',
                color: 'primary',
                bgColor: 'rgba(59, 130, 246, 0.1)',
                borderColor: 'rgba(59, 130, 246, 0.2)',
                textColor: '#2563eb'
            };
            break;
        case 'enviado':
            statusConfig = {
                icon: 'bi-truck',
                text: 'Enviado',
                color: 'info',
                bgColor: 'rgba(6, 182, 212, 0.1)',
                borderColor: 'rgba(6, 182, 212, 0.2)',
                textColor: '#0891b2'
            };
            break;
        case 'recebido':
            statusConfig = {
                icon: 'bi-box-seam',
                text: 'Recebido',
                color: 'success',
                bgColor: 'rgba(34, 197, 94, 0.1)',
                borderColor: 'rgba(34, 197, 94, 0.2)',
                textColor: '#16a34a'
            };
            break;
        case 'cancelado':
            statusConfig = {
                icon: 'bi-x-circle',
                text: 'Cancelado',
                color: 'danger',
                bgColor: 'rgba(239, 68, 68, 0.1)',
                borderColor: 'rgba(239, 68, 68, 0.2)',
                textColor: '#dc2626'
            };
            break;
        case 'entregue':
            statusConfig = {
                icon: 'bi-check2-all',
                text: 'Entregue',
                color: 'success',
                bgColor: 'rgba(34, 197, 94, 0.1)',
                borderColor: 'rgba(34, 197, 94, 0.2)',
                textColor: '#16a34a'
            };
            break;
        case 'atrasado':
            statusConfig = {
                icon: 'bi-exclamation-triangle',
                text: 'Atrasado',
                color: 'danger',
                bgColor: 'rgba(239, 68, 68, 0.1)',
                borderColor: 'rgba(239, 68, 68, 0.2)',
                textColor: '#dc2626'
            };
            break;
        case 'urgente':
            statusConfig = {
                icon: 'bi-lightning',
                text: 'Urgente',
                color: 'warning',
                bgColor: 'rgba(234, 179, 8, 0.1)',
                borderColor: 'rgba(234, 179, 8, 0.2)',
                textColor: '#d97706'
            };
            break;
        case 'em_transito':
            statusConfig = {
                icon: 'bi-arrow-right-circle',
                text: 'Em Trânsito',
                color: 'info',
                bgColor: 'rgba(6, 182, 212, 0.1)',
                borderColor: 'rgba(6, 182, 212, 0.2)',
                textColor: '#0891b2'
            };
            break;
        case 'aguardando_aprovacao':
            statusConfig = {
                icon: 'bi-hourglass-split',
                text: 'Aguardando Aprovação',
                color: 'secondary',
                bgColor: 'rgba(107, 114, 128, 0.1)',
                borderColor: 'rgba(107, 114, 128, 0.2)',
                textColor: '#6b7280'
            };
            break;
        case 'parcialmente_recebido':
            statusConfig = {
                icon: 'bi-box',
                text: 'Parcialmente Recebido',
                color: 'warning',
                bgColor: 'rgba(234, 179, 8, 0.1)',
                borderColor: 'rgba(234, 179, 8, 0.2)',
                textColor: '#d97706'
            };
            break;
    }
    
    console.log('🎨 Configuração de status selecionada:', statusConfig);
    
    try {
        // Aplicar configurações com verificações de segurança
        if (statusBadge) {
            console.log('🎨 Aplicando configuração ao statusBadge');
            statusBadge.innerHTML = `<i class="${statusConfig.icon} me-2"></i><span id="view-status-text">${statusConfig.text}</span>`;
            statusBadge.style.background = statusConfig.bgColor;
            statusBadge.style.color = statusConfig.textColor;
            statusBadge.style.borderColor = statusConfig.borderColor;
            statusBadge.setAttribute('data-status', status);
        }
        
        if (statusText) {
            console.log('🎨 Aplicando configuração ao statusText');
            statusText.textContent = statusConfig.text;
        }
        
        if (statusCard) {
            console.log('🎨 Aplicando configuração ao statusCard');
            statusCard.innerHTML = `<i class="bi ${statusConfig.icon} text-${statusConfig.color} me-2"></i><span class="fw-bold" id="view-status-ativo">${statusConfig.text}</span>`;
            statusCard.style.background = statusConfig.bgColor;
            statusCard.style.color = statusConfig.textColor;
            statusCard.style.borderColor = statusConfig.borderColor;
            statusCard.setAttribute('data-status', status);
        }
        
        if (statusAtivo) {
            console.log('🎨 Aplicando configuração ao statusAtivo');
            statusAtivo.textContent = statusConfig.text;
            statusAtivo.className = `fw-bold text-${statusConfig.color}`;
        }
        
        console.log('✅ Status configurado com sucesso!');
    } catch (error) {
        console.error('❌ Erro ao configurar status do pedido:', error);
    }
}

// Imprimir pedido
async function imprimirPedido() {
    const modal = document.getElementById('modalVisualizarPedido');
    const pedidoId = modal?.getAttribute('data-pedido-id') || window.pedidoAtual?.id_pedido;

    if (!pedidoId) {
        mostrarErro('Não foi possível identificar o pedido para impressão.');
        return;
    }

    const printWindow = window.open('', '_blank');
    if (!printWindow) {
        mostrarErro('O navegador bloqueou a nova aba de impressão. Permita pop-ups para este site.');
        return;
    }

    printWindow.document.write(`
        <!DOCTYPE html>
        <html lang="pt-br">
        <head>
            <meta charset="UTF-8">
            <title>Carregando impressão...</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 32px; color: #111827; }
                .loading { text-align: center; margin-top: 20vh; color: #6b7280; }
            </style>
        </head>
        <body>
            <div class="loading">
                <h2>Preparando impressão do pedido...</h2>
                <p>Aguarde enquanto carregamos todos os dados.</p>
            </div>
        </body>
        </html>
    `);
    printWindow.document.close();

    try {
        const response = await fetch(`backend/api/pedidos_compra.php?action=get&id=${pedidoId}`, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        const data = await response.json();
        if (!data.success || !data.pedido) {
            printWindow.close();
            mostrarErro(data.error || 'Erro ao carregar dados do pedido para impressão.');
            return;
        }

        const pedido = data.pedido;
        const itens = pedido.itens || [];
        const escaparHtml = (valor) => String(valor ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

        let quantidadeSolicitadaTotal = 0;
        let quantidadeBaseTotal = 0;
        let subtotalItens = 0;

        const linhasItens = itens.map((item, index) => {
            const quantidadeSolicitada = parseFloat(item.quantidade) || 0;
            const quantidadeDisponivelRaw = item.quantidade_disponivel;
            const quantidadeDisponivel = (quantidadeDisponivelRaw !== null && quantidadeDisponivelRaw !== undefined && quantidadeDisponivelRaw !== '')
                ? parseFloat(quantidadeDisponivelRaw)
                : null;
            const disponivel = (item.disponivel !== null && item.disponivel !== undefined && item.disponivel !== '')
                ? parseInt(item.disponivel, 10)
                : null;
            const precoFornecedor = parseFloat(item.preco_fornecedor);
            const precoUnitarioOriginal = parseFloat(item.preco_unitario) || 0;
            const precoUnitario = (!Number.isNaN(precoFornecedor) && precoFornecedor > 0) ? precoFornecedor : precoUnitarioOriginal;
            const quantidadeBase = disponivel === 0
                ? 0
                : ((quantidadeDisponivel !== null && !Number.isNaN(quantidadeDisponivel) && quantidadeDisponivel > 0) ? quantidadeDisponivel : quantidadeSolicitada);
            const totalItem = quantidadeBase * precoUnitario;

            quantidadeSolicitadaTotal += quantidadeSolicitada;
            quantidadeBaseTotal += quantidadeBase;
            subtotalItens += totalItem;

            const disponibilidadeTexto = disponivel === 0
                ? 'Não disponível'
                : (disponivel === 1 ? 'Disponível' : 'Aguardando resposta');

            return `
                <tr>
                    <td class="center">${index + 1}</td>
                    <td>
                        <strong>${escaparHtml(item.codigo_material || 'N/A')}</strong><br>
                        <span class="muted">${escaparHtml(item.nome_material || 'Material não encontrado')}</span>
                    </td>
                    <td>${escaparHtml(item.unidade_medida_sigla || item.unidade_medida || 'UN')}</td>
                    <td class="right">${quantidadeSolicitada.toLocaleString('pt-BR')}</td>
                    <td class="right">${quantidadeBase.toLocaleString('pt-BR')}</td>
                    <td>${disponibilidadeTexto}</td>
                    <td class="right">${formatarMoeda(precoUnitario)}</td>
                    <td class="right strong">${formatarMoeda(totalItem)}</td>
                </tr>
            `;
        }).join('');

        const valorTotalPedido = parseFloat(pedido.valor_total) || subtotalItens;
        const precoMedio = quantidadeBaseTotal > 0 ? subtotalItens / quantidadeBaseTotal : 0;
        const dataImpressao = new Date().toLocaleString('pt-BR');
        const statusNome = getStatusNome(pedido.status || '');

        const html = `
            <!DOCTYPE html>
            <html lang="pt-br">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Pedido ${escaparHtml(pedido.numero_pedido || pedidoId)} - Impressão</title>
                <style>
                    * { box-sizing: border-box; }
                    body {
                        font-family: Arial, Helvetica, sans-serif;
                        margin: 0;
                        background: #f3f4f6;
                        color: #111827;
                    }
                    .page {
                        width: min(1180px, calc(100% - 32px));
                        margin: 24px auto;
                        background: #fff;
                        padding: 32px;
                        border-radius: 14px;
                        box-shadow: 0 12px 32px rgba(15, 23, 42, 0.12);
                    }
                    .topbar {
                        display: flex;
                        justify-content: space-between;
                        gap: 16px;
                        align-items: flex-start;
                        border-bottom: 3px solid #2563eb;
                        padding-bottom: 18px;
                        margin-bottom: 22px;
                    }
                    h1, h2, h3 { margin: 0; }
                    h1 { font-size: 26px; color: #1e3a8a; }
                    h2 { font-size: 18px; color: #1f2937; margin-bottom: 10px; }
                    .muted { color: #6b7280; font-size: 12px; }
                    .badge {
                        display: inline-block;
                        padding: 6px 10px;
                        border-radius: 999px;
                        background: #dbeafe;
                        color: #1e40af;
                        font-weight: 700;
                        font-size: 12px;
                        text-transform: uppercase;
                    }
                    .actions { display: flex; gap: 8px; justify-content: flex-end; margin-bottom: 16px; }
                    button {
                        border: 0;
                        border-radius: 8px;
                        padding: 10px 14px;
                        cursor: pointer;
                        font-weight: 700;
                    }
                    .btn-print { background: #2563eb; color: #fff; }
                    .btn-close { background: #e5e7eb; color: #111827; }
                    .grid {
                        display: grid;
                        grid-template-columns: repeat(4, 1fr);
                        gap: 12px;
                        margin-bottom: 22px;
                    }
                    .card {
                        border: 1px solid #e5e7eb;
                        border-radius: 10px;
                        padding: 14px;
                        background: #f9fafb;
                    }
                    .card-label {
                        color: #6b7280;
                        font-size: 12px;
                        text-transform: uppercase;
                        letter-spacing: .04em;
                        margin-bottom: 5px;
                    }
                    .card-value { font-weight: 800; font-size: 17px; }
                    .section {
                        margin-top: 22px;
                        border: 1px solid #e5e7eb;
                        border-radius: 12px;
                        overflow: hidden;
                    }
                    .section-header {
                        background: #f8fafc;
                        padding: 12px 14px;
                        border-bottom: 1px solid #e5e7eb;
                    }
                    .section-body { padding: 14px; }
                    .info-grid {
                        display: grid;
                        grid-template-columns: repeat(2, 1fr);
                        gap: 10px 24px;
                        font-size: 14px;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        font-size: 12px;
                    }
                    th {
                        background: #eff6ff;
                        color: #1e3a8a;
                        text-align: left;
                        border: 1px solid #dbeafe;
                        padding: 9px;
                    }
                    td {
                        border: 1px solid #e5e7eb;
                        padding: 8px;
                        vertical-align: top;
                    }
                    tfoot td {
                        background: #f9fafb;
                        font-weight: 800;
                    }
                    .right { text-align: right; }
                    .center { text-align: center; }
                    .strong { font-weight: 800; }
                    .obs {
                        min-height: 54px;
                        white-space: pre-wrap;
                        line-height: 1.45;
                    }
                    .footer {
                        margin-top: 26px;
                        display: flex;
                        justify-content: space-between;
                        color: #6b7280;
                        font-size: 11px;
                    }
                    @media print {
                        body { background: #fff; }
                        .page {
                            width: 100%;
                            margin: 0;
                            padding: 12mm;
                            box-shadow: none;
                            border-radius: 0;
                        }
                        .actions { display: none; }
                        .section, .card { break-inside: avoid; }
                        table { page-break-inside: auto; }
                        tr { page-break-inside: avoid; page-break-after: auto; }
                    }
                    @media (max-width: 900px) {
                        .grid { grid-template-columns: repeat(2, 1fr); }
                        .info-grid { grid-template-columns: 1fr; }
                        .topbar { flex-direction: column; }
                    }
                </style>
            </head>
            <body>
                <div class="page">
                    <div class="actions">
                        <button class="btn-print" onclick="window.print()">Imprimir</button>
                        <button class="btn-close" onclick="window.close()">Fechar</button>
                    </div>

                    <div class="topbar">
                        <div>
                            <h1>Pedido de Compra</h1>
                            <div class="muted">Resumo completo para impressão</div>
                        </div>
                        <div class="right">
                            <h2>${escaparHtml(pedido.numero_pedido || 'N/A')}</h2>
                            <span class="badge">${escaparHtml(statusNome || pedido.status || 'Status não informado')}</span>
                        </div>
                    </div>

                    <div class="grid">
                        <div class="card">
                            <div class="card-label">Valor Total</div>
                            <div class="card-value">${formatarMoeda(valorTotalPedido)}</div>
                        </div>
                        <div class="card">
                            <div class="card-label">Total de Itens</div>
                            <div class="card-value">${itens.length}</div>
                        </div>
                        <div class="card">
                            <div class="card-label">Qtd. Solicitada</div>
                            <div class="card-value">${quantidadeSolicitadaTotal.toLocaleString('pt-BR')}</div>
                        </div>
                        <div class="card">
                            <div class="card-label">Preço Médio</div>
                            <div class="card-value">${formatarMoeda(precoMedio)}</div>
                        </div>
                    </div>

                    <div class="section">
                        <div class="section-header"><h2>Dados do Pedido</h2></div>
                        <div class="section-body info-grid">
                            <div><strong>Clínica:</strong> ${escaparHtml(pedido.nome_filial || 'N/A')}</div>
                            <div><strong>Fornecedor:</strong> ${escaparHtml(pedido.nome_fornecedor || 'N/A')}</div>
                            <div><strong>Solicitante:</strong> ${escaparHtml(pedido.nome_usuario_solicitante || pedido.nome_usuario || 'Sistema')}</div>
                            <div><strong>Data do Pedido:</strong> ${formatarData(pedido.data_solicitacao || pedido.data_criacao)}</div>
                            <div><strong>Entrega Prevista:</strong> ${pedido.data_entrega_prevista ? formatarData(pedido.data_entrega_prevista) : 'Não informado'}</div>
                            <div><strong>Prioridade:</strong> ${escaparHtml(getPrioridadeText(pedido.prioridade || 'padrao'))}</div>
                            <div><strong>Prazo de Entrega:</strong> ${escaparHtml(pedido.prazo_entrega || 'Não informado')} ${pedido.prazo_entrega ? 'dias' : ''}</div>
                            <div><strong>Última Atualização:</strong> ${pedido.data_atualizacao ? formatarData(pedido.data_atualizacao) : 'Não informado'}</div>
                        </div>
                    </div>

                    <div class="section">
                        <div class="section-header"><h2>Itens do Pedido</h2></div>
                        <div class="section-body" style="padding:0;">
                            <table>
                                <thead>
                                    <tr>
                                        <th class="center">#</th>
                                        <th>Material</th>
                                        <th>Un.</th>
                                        <th class="right">Qtd. Solicitada</th>
                                        <th class="right">Qtd. Cálculo</th>
                                        <th>Disponibilidade</th>
                                        <th class="right">Preço Unit.</th>
                                        <th class="right">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${linhasItens || '<tr><td colspan="8" class="center muted">Nenhum item encontrado</td></tr>'}
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="right">Totais</td>
                                        <td class="right">${quantidadeSolicitadaTotal.toLocaleString('pt-BR')}</td>
                                        <td class="right">${quantidadeBaseTotal.toLocaleString('pt-BR')}</td>
                                        <td colspan="2" class="right">Subtotal dos Itens</td>
                                        <td class="right">${formatarMoeda(subtotalItens)}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="7" class="right">Valor Total do Pedido</td>
                                        <td class="right">${formatarMoeda(valorTotalPedido)}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="section">
                        <div class="section-header"><h2>Observações</h2></div>
                        <div class="section-body obs">${escaparHtml(pedido.observacoes || 'Nenhuma observação registrada.')}</div>
                    </div>

                    <div class="footer">
                        <div>Impresso em ${dataImpressao}</div>
                        <div>${escaparHtml(document.title || 'Sistema Grupo Sorrisos')}</div>
                    </div>
                </div>
            </body>
            </html>
        `;

        printWindow.document.open();
        printWindow.document.write(html);
        printWindow.document.close();
        printWindow.focus();
    } catch (error) {
        console.error('Erro ao imprimir pedido:', error);
        printWindow.close();
        mostrarErro('Erro ao preparar impressão do pedido.');
    }
}

// Editar pedido atual
async function editarPedidoAtual() {
    try {
        // Obter o ID do pedido do modal
        const modal = document.getElementById('modalVisualizarPedido');
        const pedidoId = modal.getAttribute('data-pedido-id');
        
        if (!pedidoId) {
            console.error('ID do pedido não encontrado');
            mostrarErro('Erro ao identificar pedido para edição');
            return;
        }
        
        const statusAtualPedido = (modal.getAttribute('data-pedido-status') || '').toLowerCase();
        if (statusAtualPedido && !pedidoPodeSerEditado(statusAtualPedido)) {
            await mostrarModalEdicaoBloqueada(pedidoId);
            return;
        }
        
        console.log('🔄 Editando pedido ID:', pedidoId);
        
        // Fechar modal de visualização
        const modalVisualizar = bootstrap.Modal.getInstance(modal);
        if (modalVisualizar) {
            modalVisualizar.hide();
        }
        
        // Aguardar um pouco para o modal fechar completamente
        setTimeout(async () => {
            try {
                // Buscar dados atualizados do pedido
                const response = await fetch(`backend/api/pedidos_compra.php?action=get&id=${pedidoId}`, {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    const pedido = data.pedido;

                    if (!pedidoPodeSerEditado(pedido.status)) {
                        await mostrarModalEdicaoBloqueada(pedidoId);
                        return;
                    }
                    
                    // Preencher o modal de edição com os dados do pedido
                    await preencherModalEdicao(pedido);
                    
                    // Abrir modal de edição
                    const modalEdicao = new bootstrap.Modal(document.getElementById('modalNovoPedido'));
                    modalEdicao.show();
                    
                    console.log('✅ Modal de edição aberto com sucesso');
                } else {
                    mostrarErro('Erro ao carregar dados do pedido para edição');
                }
            } catch (error) {
                console.error('Erro ao carregar dados para edição:', error);
                mostrarErro('Erro ao carregar dados para edição');
            }
        }, 300);
        
    } catch (error) {
        console.error('Erro ao editar pedido:', error);
        mostrarErro('Erro ao abrir edição do pedido');
    }
}

// Preencher modal de edição com dados do pedido
async function preencherModalEdicao(pedido) {
    try {
        console.log('📝 Preenchendo modal de edição com dados:', pedido);
        
        // Limpar formulário
        document.getElementById('formNovoPedido').reset();
        statusPedidoEmEdicao = pedido.status || null;
        
        // Preencher campos básicos - usando os IDs corretos do modal
        const filialElement = document.getElementById('novo_id_filial');
        const fornecedorElement = document.getElementById('novo_id_fornecedor');
        const dataEntregaElement = document.getElementById('novo_data_entrega_prevista');
        const observacoesElement = document.getElementById('novo_observacoes');
        const prioridadeElement = document.getElementById('novo_prioridade');
        const prazoEntregaElement = document.getElementById('novo_prazo_entrega');
        
        // Verificar se os elementos existem antes de tentar acessá-los
        if (filialElement) filialElement.value = pedido.id_filial || '';
        if (fornecedorElement) fornecedorElement.value = pedido.id_fornecedor || '';
        if (dataEntregaElement) dataEntregaElement.value = pedido.data_entrega_prevista ? pedido.data_entrega_prevista.split(' ')[0] : '';
        if (observacoesElement) observacoesElement.value = pedido.observacoes || '';
        if (prioridadeElement) prioridadeElement.value = pedido.prioridade || 'padrao';
        if (prazoEntregaElement) prazoEntregaElement.value = pedido.prazo_entrega || 8;
        
        const pesquisaMaterial = document.getElementById('pesquisa-material');
        if (pesquisaMaterial) pesquisaMaterial.style.display = 'block';
        setTimeout(() => configurarAutocompletePesquisa(), 100);
        
        console.log('✅ Campos básicos preenchidos');
        
        // Carregar fornecedores se filial estiver selecionada
        if (pedido.id_filial) {
            console.log('🔄 Carregando fornecedores para filial:', pedido.id_filial);
            await carregarFornecedores(pedido.id_filial);
            
            // Aguardar um pouco para os fornecedores serem carregados
            setTimeout(() => {
                if (fornecedorElement) {
                    fornecedorElement.value = pedido.id_fornecedor || '';
                    console.log('✅ Fornecedor selecionado:', pedido.id_fornecedor);
                }
            }, 500);
        }
        
        // Carregar materiais com estoque baixo se ambos estiverem selecionados
        if (pedido.id_filial && pedido.id_fornecedor) {
            console.log('🔄 Carregando materiais com estoque baixo');
            await carregarMateriaisEstoqueBaixo();
        }

        // Preencher itens existentes imediatamente após carregar estrutura
        if (pedido.itens && pedido.itens.length > 0) {
            console.log('📋 Preenchendo itens existentes');
            preencherItensExistentes(pedido.itens);
        } else {
            console.warn('⚠️ Pedido sem itens para edição');
        }

        calcularTotalPedido();
        console.log('💰 Total calculado');
        
        // Alterar título e botão do modal
        const modalTitle = document.querySelector('#modalNovoPedido .modal-title');
        const modalButton = document.querySelector('#modalNovoPedido .btn-primary');
        
        if (modalTitle) {
            modalTitle.textContent = 'Editar Pedido de Compra';
            console.log('✅ Título do modal alterado');
        }
        
        if (modalButton) {
            modalButton.textContent = 'Atualizar Pedido';
            modalButton.onclick = () => atualizarPedido(pedido.id_pedido);
            console.log('✅ Botão do modal alterado');
        }

        configurarBotaoImportacaoCsvCliente(true);
        configurarBuscaItensPedidoExistente(true);
        
        console.log('✅ Modal de edição preenchido com sucesso');
        
    } catch (error) {
        console.error('Erro ao preencher modal de edição:', error);
        mostrarErro('Erro ao preparar dados para edição');
    }
}

// Preencher itens existentes no modal de edição
function preencherItensExistentes(itens) {
    try {
        console.log('📋 Preenchendo itens existentes:', itens);
        itensOriginaisEdicao = new Map();
        itensRemovidosEdicao = new Set();
        const container = document.getElementById('materiais-container');
        if (!container) {
            console.error('❌ Container de materiais não encontrado ao preencher edição');
            return;
        }

        // Limpar tabela de selecionados para evitar dados antigos
        const tabelaSelecionadosAntiga = container.querySelector('.tabela-materiais-pesquisados');
        if (tabelaSelecionadosAntiga) {
            tabelaSelecionadosAntiga.remove();
        }

        // Criar lista base de itens já escolhidos para exibir imediatamente na edição
        const itensNormalizados = itens.map(item => {
            const itemId = item.id_material || item.id_catalogo;
            const quantidade = parseFloat(item.quantidade) || 0;
            const precoUnitario = parseFloat(item.preco_unitario) || 0;

            itensOriginaisEdicao.set(String(itemId), {
                id_material: String(itemId),
                quantidade: quantidade,
                preco_unitario: precoUnitario,
                valor_total: parseFloat(item.valor_total) || (quantidade * precoUnitario)
            });

            return {
            id_material: String(itemId),
            codigo: item.codigo_material || `MAT-${itemId}`,
            nome: item.nome_material || 'Material',
            preco_unitario: precoUnitario,
            unidade_medida: item.unidade_medida || item.unidade_medida_sigla || item.unidade || 'UN',
            estoque_atual: item.estoque_atual || 0
        }});

        renderizarResultadoPesquisa(itensNormalizados);

        itens.forEach((item, index) => {
            const materialId = String(item.id_material || item.id_catalogo);
            const materialEstoqueBaixo = materiaisEstoqueBaixo.find(m => String(m.id_material) === materialId);
            const quantidade = parseFloat(item.quantidade) || 0;
            const precoUnitario = parseFloat(item.preco_unitario) || 0;

            const quantidadePesquisadaInput = document.querySelector(`.quantidade-pesquisada[data-material-id="${materialId}"]`);
            const valorUnitarioPesquisadoInput = document.querySelector(`.valor-unitario-pesquisado[data-material-id="${materialId}"]`);

            if (quantidadePesquisadaInput) {
                quantidadePesquisadaInput.value = quantidade;
            }
            if (valorUnitarioPesquisadoInput) {
                valorUnitarioPesquisadoInput.value = formatarMoeda(precoUnitario);
            }
            calcularTotalMaterialPesquisado(materialId);
            
            if (materialEstoqueBaixo) {
                const quantidadeInput = document.querySelector(`.quantidade-solicitada[data-material-id="${materialId}"]`);
                const valorUnitarioInput = document.querySelector(`.valor-unitario[data-material-id="${materialId}"]`);
                
                if (quantidadeInput) {
                    quantidadeInput.value = quantidade;
                }
                if (valorUnitarioInput) {
                    valorUnitarioInput.value = formatarMoeda(precoUnitario);
                }
            }
            
            console.log(`✅ Item ${index + 1} carregado para edição`);
        });
        
        // Recalcular totais dos itens de estoque baixo preenchidos
        document.querySelectorAll('.quantidade-solicitada').forEach((input) => {
            const index = input.getAttribute('data-index');
            if (index !== null) {
                calcularTotalMaterial(parseInt(index, 10));
            }
        });
        
        calcularTotalPedido();
        console.log('✅ Itens existentes preenchidos com sucesso');
        
    } catch (error) {
        console.error('Erro ao preencher itens existentes:', error);
    }
}

// Atualizar pedido existente
async function atualizarPedido(pedidoId) {
    try {
        console.log('🔄 Atualizando pedido ID:', pedidoId);
        const btnSalvar = document.querySelector('#modalNovoPedido .modal-footer .btn-primary');
        if (btnSalvar && btnSalvar.disabled) {
            return;
        }
        if (btnSalvar) {
            btnSalvar.disabled = true;
            const originalText = btnSalvar.innerHTML;
            btnSalvar.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Processando...';
            btnSalvar.setAttribute('data-original-html', originalText);
        }
        mostrarModalProcessandoPedido('Processando dados do pedido...');
        
        // Coletar dados do formulário
        const formData = new FormData(document.getElementById('formNovoPedido'));
        
        // Coletar itens selecionados sem duplicidade
        const itensColetadosTela = coletarItensSelecionados();
        const itensMapFinal = new Map();

        // Preservar itens originais na edição, exceto os removidos explicitamente
        itensOriginaisEdicao.forEach((itemBase, idMaterial) => {
            if (!itensRemovidosEdicao.has(String(idMaterial))) {
                itensMapFinal.set(String(idMaterial), { ...itemBase });
            }
        });

        // Sobrescrever/adicionar itens que estão atualmente na tela
        itensColetadosTela.forEach(item => {
            itensMapFinal.set(String(item.id_material), item);
        });

        const itens = Array.from(itensMapFinal.values());
        
        if (itens.length === 0) {
            ocultarModalProcessandoPedido();
            mostrarErro('Adicione pelo menos um item ao pedido');
            return;
        }
        
        const dados = {
            id_filial: formData.get('id_filial'),
            id_fornecedor: formData.get('id_fornecedor'),
            data_entrega_prevista: formData.get('data_entrega_prevista'),
            prioridade: formData.get('prioridade') || 'padrao',
            prazo_entrega: parseInt(formData.get('prazo_entrega')) || 8,
            status: statusPedidoEmEdicao,
            observacoes: formData.get('observacoes'),
            valor_total: parseFloat(document.getElementById('total-pedido-modal').textContent.replace(/[^\d,]/g, '').replace(',', '.')) || 0,
            itens: itens
        };
        
        console.log('📤 Dados para atualização:', dados);
        
        const response = await fetch(`backend/api/pedidos_compra.php?action=update&id=${pedidoId}`, {
            method: 'PUT',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(dados)
        });
        
        const result = await response.json();
        
        if (result.success) {
            ocultarModalProcessandoPedido();
            mostrarSucesso('Pedido atualizado com sucesso!');
            
            // Fechar modal de edição
            bootstrap.Modal.getInstance(document.getElementById('modalNovoPedido')).hide();
            
            // Recarregar dados
            carregarPedidos();
            carregarEstatisticas();
            
            // Resetar modal para criação
            resetarModalParaCriacao();
            
        } else {
            ocultarModalProcessandoPedido();
            mostrarErro(result.error || 'Erro ao atualizar pedido');
        }
        
    } catch (error) {
        ocultarModalProcessandoPedido();
        console.error('Erro ao atualizar pedido:', error);
        mostrarErro('Erro ao atualizar pedido');
    } finally {
        const btnSalvar = document.querySelector('#modalNovoPedido .modal-footer .btn-primary');
        if (btnSalvar) {
            btnSalvar.disabled = false;
            btnSalvar.innerHTML = btnSalvar.getAttribute('data-original-html') || (btnSalvar.textContent.includes('Atualizar') ? 'Atualizar Pedido' : 'Salvar Pedido');
        }
    }
}

// Resetar modal para modo de criação
function resetarModalParaCriacao() {
    try {
        // Limpar formulário
        document.getElementById('formNovoPedido').reset();
        
        // Limpar itens
        itensPedido = [];
        statusPedidoEmEdicao = null;
        itensOriginaisEdicao = new Map();
        itensRemovidosEdicao = new Set();
        atualizarInterfaceItens();
        
        // Resetar título e botão
        const modalTitle = document.querySelector('#modalNovoPedido .modal-title');
        const modalButton = document.querySelector('#modalNovoPedido .btn-primary');
        
        if (modalTitle) modalTitle.textContent = 'Novo Pedido de Compra';
        if (modalButton) {
            modalButton.textContent = 'Salvar Pedido';
            modalButton.onclick = salvarNovoPedido;
        }
        configurarBotaoImportacaoCsvCliente(false);
        configurarBuscaItensPedidoExistente(false);
        
        // Limpar container de materiais
        document.getElementById('materiais-container').innerHTML = '<div class="text-center text-muted py-4">Selecione uma Clínica e um fornecedor para carregar os materiais</div>';
        
        // Resetar total
        document.getElementById('total-pedido-modal').textContent = 'R$ 0,00';
        const totalItensElement = document.getElementById('total-itens-modal');
        const totalQuantidadeElement = document.getElementById('total-quantidade-modal');
        if (totalItensElement) totalItensElement.textContent = '0';
        if (totalQuantidadeElement) totalQuantidadeElement.textContent = '0';
        
        console.log('✅ Modal resetado para modo de criação');
        
    } catch (error) {
        console.error('Erro ao resetar modal:', error);
    }
}

// ===== FUNÇÕES DE APROVAÇÃO E MUDANÇA DE STATUS =====

// Configurar botões de ação baseados no status do pedido
function configurarBotoesAcao(status) {
    try {
        console.log('🎛️ Configurando botões de ação para status:', status);
        
        // Ocultar todos os botões primeiro
        const botoes = [
            'btn-aprovar-pendente',
            'btn-aprovar-cotacao',
            'btn-aprovar-faturamento',
            'btn-marcar-entregue',
            'btn-confirmar-recebimento',
            'btn-cancelar',
            'btn-voltar-status'
        ];
        
        botoes.forEach(id => {
            const botao = document.getElementById(id);
            if (botao) botao.classList.add('d-none');
        });
        
        // Mostrar botões baseados no novo fluxo de status
        switch (status) {
            case 'em_analise':
                // Gestor pode aprovar para Pendente
                document.getElementById('btn-aprovar-pendente')?.classList.remove('d-none');
                document.getElementById('btn-cancelar')?.classList.remove('d-none');
                break;
                
            case 'pendente':
                // Setor de compras pode aprovar para Aprovado Cotação
                document.getElementById('btn-aprovar-cotacao')?.classList.remove('d-none');
                document.getElementById('btn-cancelar')?.classList.remove('d-none');
                document.getElementById('btn-voltar-status')?.classList.remove('d-none');
                break;
                
            case 'aprovado_cotacao':
                // Fornecedor faz cotação (será tratado em pedidos-fornecedores.php)
                document.getElementById('btn-cancelar')?.classList.remove('d-none');
                document.getElementById('btn-voltar-status')?.classList.remove('d-none');
                break;
                
            case 'enviar_para_faturamento':
                // Setor de compras avalia e aprova para faturamento
                document.getElementById('btn-aprovar-faturamento')?.classList.remove('d-none');
                document.getElementById('btn-cancelar')?.classList.remove('d-none');
                document.getElementById('btn-voltar-status')?.classList.remove('d-none');
                break;
                
            case 'aprovado_para_faturar':
                // Fornecedor pode enviar (será tratado em pedidos-fornecedores.php)
                document.getElementById('btn-cancelar')?.classList.remove('d-none');
                document.getElementById('btn-voltar-status')?.classList.remove('d-none');
                break;
                
            case 'em_transito':
                // Pode marcar como entregue
                document.getElementById('btn-marcar-entregue')?.classList.remove('d-none');
                document.getElementById('btn-voltar-status')?.classList.remove('d-none');
                break;
                
            case 'entregue':
                // Pode confirmar recebimento
                document.getElementById('btn-confirmar-recebimento')?.classList.remove('d-none');
                document.getElementById('btn-voltar-status')?.classList.remove('d-none');
                break;
                
            case 'recebido':
                // Status final - nenhum botão de ação
                break;
                
            case 'cancelado':
                // Status final - nenhum botão de ação
                break;
        }
        
        console.log('✅ Botões de ação configurados para status:', status);
        
    } catch (error) {
        console.error('Erro ao configurar botões de ação:', error);
    }
}

/**
 * Função genérica para atualizar status do pedido
 */
async function atualizarStatusPedido(novoStatus, observacao = null) {
    try {
        const modalElement = document.getElementById('modalVisualizarPedido');
        const pedidoId = modalElement ? modalElement.getAttribute('data-pedido-id') : null;
        
        if (!pedidoId) {
            mostrarErro('ID do pedido não encontrado');
            return;
        }
        
        // Confirmar ação com o usuário
        const statusNomes = {
            'em_analise': 'Em Análise',
            'pendente': 'Pendente',
            'aprovado_cotacao': 'Aprovado Cotação',
            'enviar_para_faturamento': 'Enviar para Faturamento',
            'aprovado_para_faturar': 'Aprovado para Faturar',
            'em_transito': 'Em Trânsito',
            'entregue': 'Entregue',
            'recebido': 'Recebido',
            'cancelado': 'Cancelado'
        };
        
        // Usar modal Bootstrap customizado em vez de SweetAlert para evitar conflitos
        const result = await new Promise((resolve) => {
            const modalConfirmElement = document.getElementById('modalConfirmarStatus');
            const messageElement = document.getElementById('modal-status-message');
            const observacaoElement = document.getElementById('observacao-status');
            const btnConfirmar = document.getElementById('btn-confirmar-status');
            
            if (!modalConfirmElement || !messageElement || !observacaoElement || !btnConfirmar) {
                console.error('❌ Elementos do modal de confirmação não encontrados');
                resolve({ isConfirmed: false });
                return;
            }
            
            // Limpar campo de observação (garantir que seja sempre uma string)
            observacaoElement.value = (typeof observacao === 'string' && observacao) ? observacao : '';
            
            // Atualizar mensagem
            messageElement.textContent = `Deseja alterar o status para "${statusNomes[novoStatus]}"?`;
            
            // Remover event listeners anteriores clonando o botão
            const novoBtnConfirmar = btnConfirmar.cloneNode(true);
            btnConfirmar.parentNode.replaceChild(novoBtnConfirmar, btnConfirmar);
            
            // Configurar event listeners
            const modal = new bootstrap.Modal(modalConfirmElement, {
                backdrop: 'static',
                keyboard: false
            });
            
            let resolved = false;
            
            // Handler para confirmar
            novoBtnConfirmar.addEventListener('click', () => {
                if (resolved) return;
                resolved = true;
                const observacaoDigitada = observacaoElement.value.trim();
                modal.hide();
                resolve({ isConfirmed: true, value: observacaoDigitada });
            });
            
            // Handler para cancelar (botão X ou botão Cancelar)
            const handleCancel = () => {
                if (resolved) return;
                resolved = true;
                resolve({ isConfirmed: false });
            };
            
            modalConfirmElement.addEventListener('hidden.bs.modal', handleCancel, { once: true });
            
            // Focar no campo de observação quando o modal abrir
            modalConfirmElement.addEventListener('shown.bs.modal', () => {
                setTimeout(() => {
                    observacaoElement.focus();
                    observacaoElement.select();
                }, 100);
            }, { once: true });
            
            // Mostrar modal
            modal.show();
        });
        
        if (!result.isConfirmed) return;
        
        // Obter observação do campo de texto
        const observacaoDigitada = result.value || observacao || '';
        
        mostrarLoading(true);
        
        const response = await fetch('backend/api/pedidos_compra.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'atualizar_status',
                id_pedido: pedidoId,
                novo_status: novoStatus,
                observacao: observacaoDigitada
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            mostrarSucesso(`Status atualizado para "${statusNomes[novoStatus]}" com sucesso!`);
            
            // Fechar modal de confirmação se ainda estiver aberto
            const modalConfirmar = bootstrap.Modal.getInstance(document.getElementById('modalConfirmarStatus'));
            if (modalConfirmar) {
                modalConfirmar.hide();
            }
            
            // Recarregar dados do pedido no modal principal (sem fechá-lo)
            const modalVisualizar = document.getElementById('modalVisualizarPedido');
            if (modalVisualizar) {
                const pedidoIdAtual = modalVisualizar.getAttribute('data-pedido-id') || pedidoId;
                console.log('🔄 Recarregando dados do pedido no modal. ID:', pedidoIdAtual);
                if (pedidoIdAtual) {
                    // Recarregar os dados do pedido no modal
                    await visualizarPedido(pedidoIdAtual);
                } else {
                    console.error('❌ ID do pedido não encontrado para recarregar');
                }
            } else {
                console.error('❌ Modal de visualização não encontrado');
            }
            
            // Recarregar lista de pedidos
            await carregarPedidos();
            await carregarEstatisticas();
            
        } else {
            mostrarErro(data.message || 'Erro ao atualizar status do pedido');
        }
        
    } catch (error) {
        console.error('Erro ao atualizar status:', error);
        mostrarErro('Erro ao atualizar status do pedido');
    } finally {
        mostrarLoading(false);
    }
}

/**
 * Função para mostrar opções de voltar status
 */
async function mostrarOpcoesVoltarStatus() {
    try {
        const pedidoId = document.getElementById('view_numero_pedido').dataset.pedidoId;
        const statusAtual = document.getElementById('view_numero_pedido').dataset.statusAtual;
        
        // Definir opções de volta baseadas no status atual
        const opcoesVolta = {
            'pendente': [{ valor: 'em_analise', nome: 'Em Análise' }],
            'aprovado_cotacao': [{ valor: 'pendente', nome: 'Pendente' }],
            'enviar_para_faturamento': [{ valor: 'aprovado_cotacao', nome: 'Aprovado Cotação' }],
            'aprovado_para_faturar': [{ valor: 'enviar_para_faturamento', nome: 'Enviar para Faturamento' }],
            'em_transito': [{ valor: 'aprovado_para_faturar', nome: 'Aprovado para Faturar' }],
            'entregue': [{ valor: 'em_transito', nome: 'Em Trânsito' }]
        };
        
        const opcoes = opcoesVolta[statusAtual] || [];
        
        if (opcoes.length === 0) {
            mostrarErro('Não é possível voltar o status deste pedido');
            return;
        }
        
        // Criar HTML das opções
        const opcoesHtml = opcoes.map(opcao => 
            `<button class="btn btn-outline-warning me-2 mb-2" onclick="atualizarStatusPedido('${opcao.valor}')">
                <i class="bi bi-arrow-left me-2"></i>${opcao.nome}
            </button>`
        ).join('');
        
        await Swal.fire({
            title: 'Voltar Status',
            html: `
                <p class="mb-3">Selecione para qual status deseja voltar:</p>
                <div class="d-flex flex-wrap justify-content-center">
                    ${opcoesHtml}
                </div>
            `,
            icon: 'question',
            showConfirmButton: false,
            showCancelButton: true,
            cancelButtonText: 'Cancelar',
            cancelButtonColor: '#6c757d'
        });
        
    } catch (error) {
        console.error('Erro ao mostrar opções de volta:', error);
        mostrarErro('Erro ao carregar opções de volta');
    }
}

async function buscarHistoricoStatus(pedidoId) {
    try {
        const response = await fetch(`backend/api/historico_status.php?action=get&pedido_id=${pedidoId}`, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success && data.historico) {
            return data.historico;
        }

        console.error('Erro ao carregar histórico:', data.message || 'Dados não encontrados');
        return [];
    } catch (error) {
        console.error('Erro ao carregar histórico de status:', error);
        return [];
    }
}

// Carregar histórico de status do pedido
async function carregarHistoricoStatus(pedidoId) {
    const historico = await buscarHistoricoStatus(pedidoId);
    const container = document.getElementById('timeline-status');
    if (!container) return historico;

    if (historico.length > 0) {
        renderizarTimelineStatus(historico, 'timeline-status');
    } else {
        container.innerHTML = '<p class="text-muted">Nenhum histórico de status encontrado.</p>';
    }

    return historico;
}

// Carregar fluxo de status do pedido
async function carregarFluxoStatus(pedidoId, statusAtual) {
    try {
        const response = await fetch(`backend/api/historico_status.php?action=get&pedido_id=${pedidoId}`, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            renderizarFluxoStatus(data.historico || [], statusAtual, pedidoId);
        } else {
            console.error('Erro ao carregar fluxo:', data.message || 'Dados não encontrados');
            document.getElementById('status-flow').innerHTML = '<p class="text-muted">Erro ao carregar fluxo de status.</p>';
        }
    } catch (error) {
        console.error('Erro ao carregar fluxo de status:', error);
        document.getElementById('status-flow').innerHTML = '<p class="text-muted">Erro ao carregar fluxo de status.</p>';
    }
}

// Renderizar fluxo de status
function renderizarFluxoStatus(historico, statusAtual, pedidoId) {
    const container = document.getElementById('status-flow');
    if (!container) return;
    
    // Definir ordem dos status
    const fluxoStatus = [
        { key: 'em_analise', nome: 'Em Análise', icon: '📋' },
        { key: 'pendente', nome: 'Pendente', icon: '⏳' },
        { key: 'aprovado_cotacao', nome: 'Aprovado Cotação', icon: '✅' },
        { key: 'enviar_para_faturamento', nome: 'Enviar para Faturamento', icon: '📄' },
        { key: 'aprovado_para_faturar', nome: 'Aprovado p/ Faturar', icon: '💰' },
        { key: 'em_transito', nome: 'Em Trânsito', icon: '🚚' },
        { key: 'entregue', nome: 'Entregue', icon: '📦' }
    ];
    
    // Criar mapa de histórico por status
    const historicoMap = {};
    historico.forEach(item => {
        // Usar status_novo (campo retornado pela API) ou status (compatibilidade)
        const statusKey = item.status_novo || item.status;
        if (statusKey) {
            historicoMap[statusKey] = item;
        }
    });
    
    // Encontrar índice do status atual
    const indiceAtual = fluxoStatus.findIndex(s => s.key === statusAtual);
    
    let html = '';
    
    fluxoStatus.forEach((status, index) => {
        const historicoDeste = historicoMap[status.key];
        let classe = 'status-step';
        let icone = status.icon;
        let dataTexto = '';
        
        if (historicoDeste) {
            // Status já passou
            classe += ' completed';
            icone = '✓';
            dataTexto = `<div class="status-date">${formatarDataHora(historicoDeste.data_alteracao)}</div>`;
        } else if (status.key === statusAtual) {
            // Status atual
            classe += ' current';
        } else if (index === indiceAtual + 1) {
            // Próximo status disponível
            classe += ' available';
        } else {
            // Status não disponível
            classe += ' disabled';
        }
        
        const onClick = (index === indiceAtual + 1) ? `onclick="alterarStatusPedido('${status.key}', ${pedidoId})"` : '';
        
        html += `
            <div class="${classe}" ${onClick}>
                <div class="status-icon">${icone}</div>
                <div class="flex-grow-1">
                    <div class="status-text">${status.nome}</div>
                    ${dataTexto}
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// Alterar status do pedido via fluxo
async function alterarStatusPedido(novoStatus, pedidoId) {
    const result = await Swal.fire({
        title: 'Confirmar Alteração',
        text: `Deseja alterar o status do pedido para "${getStatusNome(novoStatus)}"?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sim, alterar',
        cancelButtonText: 'Cancelar'
    });
    
    if (result.isConfirmed) {
        try {
            // Marcar como loading
            const statusStep = document.querySelector(`[onclick*="${novoStatus}"]`);
            if (statusStep) {
                statusStep.classList.add('loading');
            }
            
            await atualizarStatusPedido(novoStatus, result.value);
            
            // Recarregar dados
            await visualizarPedido(pedidoId);
            
            mostrarSucesso('Status alterado com sucesso!');
        } catch (error) {
            console.error('Erro ao alterar status:', error);
            mostrarErro('Erro ao alterar status do pedido.');
        }
    }
}

// Obter nome do status
function getStatusNome(status) {
    const nomes = {
        'em_analise': 'Em Análise',
        'pendente': 'Pendente',
        'aprovado_cotacao': 'Aprovado Cotação',
        'enviar_para_faturamento': 'Enviar para Faturamento',
        'aprovado_para_faturar': 'Aprovado para Faturar',
        'em_transito': 'Em Trânsito',
        'entregue': 'Entregue',
        'cancelado': 'Cancelado'
    };
    return nomes[status] || status;
}

function renderizarFluxoStatusBloqueio(historico, statusAtual, containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;

    const fluxoStatus = [
        { key: 'em_analise', nome: 'Em Análise' },
        { key: 'pendente', nome: 'Pendente' },
        { key: 'aprovado_cotacao', nome: 'Aprovado Cotação' },
        { key: 'enviar_para_faturamento', nome: 'Enviar para Faturamento' },
        { key: 'aprovado_para_faturar', nome: 'Aprovado para Faturar' },
        { key: 'em_transito', nome: 'Em Trânsito' },
        { key: 'entregue', nome: 'Entregue' },
        { key: 'recebido', nome: 'Recebido' }
    ];

    const historicoMap = {};
    (historico || []).forEach(item => {
        const statusKey = item.status_novo || item.status;
        if (statusKey) historicoMap[statusKey] = item;
    });

    const indiceAtual = fluxoStatus.findIndex(status => status.key === statusAtual);
    container.innerHTML = fluxoStatus.map((status, index) => {
        const historicoStatus = historicoMap[status.key];
        const concluido = historicoStatus || (indiceAtual >= 0 && index < indiceAtual);
        const atual = status.key === statusAtual;
        const badgeClass = atual ? 'bg-warning text-dark' : (concluido ? 'bg-success' : 'bg-secondary');
        const dataStatus = historicoStatus?.data_alteracao ? formatarDataHora(historicoStatus.data_alteracao) : '';

        return `
            <div class="d-flex align-items-start gap-2 mb-2">
                <span class="badge ${badgeClass}" style="min-width: 24px;">${atual ? 'Atual' : (concluido ? 'OK' : '-')}</span>
                <div>
                    <div class="fw-semibold">${status.nome}</div>
                    ${dataStatus ? `<div class="text-muted small">${dataStatus}</div>` : ''}
                </div>
            </div>
        `;
    }).join('');
}

async function mostrarModalEdicaoBloqueada(pedidoId) {
    try {
        const modalVisualizar = document.getElementById('modalVisualizarPedido');
        const modalVisualizarInstance = modalVisualizar ? bootstrap.Modal.getInstance(modalVisualizar) : null;
        if (modalVisualizarInstance && modalVisualizar.classList.contains('show')) {
            modalVisualizarInstance.hide();
        }

        const response = await fetch(`backend/api/pedidos_compra.php?action=get&id=${pedidoId}`, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        const data = await response.json();
        if (!data.success || !data.pedido) {
            mostrarErro(data.error || 'Erro ao carregar detalhes do pedido bloqueado.');
            return;
        }

        const pedido = data.pedido;
        const historico = await buscarHistoricoStatus(pedidoId);
        const statusNome = getStatusNome(pedido.status || '');
        const itens = pedido.itens || [];
        const quantidadeTotal = itens.reduce((sum, item) => sum + (parseFloat(item.quantidade) || 0), 0);

        const modalElement = document.getElementById('modalEdicaoBloqueadaPedido');
        if (!modalElement) return;
        modalElement.setAttribute('data-pedido-id', pedidoId);

        document.getElementById('bloqueio-edicao-mensagem').textContent =
            `O pedido está na situação "${statusNome}". A partir desta fase o processo fica bloqueado para alterações e deve ser apenas acompanhado.`;
        document.getElementById('bloqueio-numero-pedido').textContent = pedido.numero_pedido || 'N/A';
        document.getElementById('bloqueio-status-atual').innerHTML = getStatusBadge(pedido.status || '');
        document.getElementById('bloqueio-valor-total').textContent = formatarMoeda(parseFloat(pedido.valor_total) || 0);
        document.getElementById('bloqueio-total-itens').textContent = `${itens.length} item(ns) / ${quantidadeTotal} qtd.`;
        document.getElementById('bloqueio-filial').textContent = pedido.nome_filial || 'N/A';
        document.getElementById('bloqueio-fornecedor').textContent = pedido.nome_fornecedor || 'N/A';
        document.getElementById('bloqueio-solicitante').textContent = pedido.nome_usuario_solicitante || pedido.nome_usuario || 'Sistema';
        document.getElementById('bloqueio-data-pedido').textContent = formatarData(pedido.data_solicitacao || pedido.data_criacao);
        document.getElementById('bloqueio-data-entrega').textContent = pedido.data_entrega_prevista ? formatarData(pedido.data_entrega_prevista) : 'Não informado';
        document.getElementById('bloqueio-prioridade').textContent = getPrioridadeText(pedido.prioridade || 'padrao');

        const tbody = document.getElementById('bloqueio-itens-tbody');
        if (tbody) {
            tbody.innerHTML = itens.length ? itens.map(item => {
                const quantidade = parseFloat(item.quantidade) || 0;
                const precoFornecedor = parseFloat(item.preco_fornecedor);
                const precoUnitario = (!Number.isNaN(precoFornecedor) && precoFornecedor > 0)
                    ? precoFornecedor
                    : (parseFloat(item.preco_unitario) || 0);
                const total = quantidade * precoUnitario;

                return `
                    <tr>
                        <td>
                            <strong>${item.codigo_material || 'N/A'}</strong><br>
                            <small class="text-muted">${item.nome_material || 'Material não encontrado'}</small>
                        </td>
                        <td class="text-center">${quantidade}</td>
                        <td class="text-center">${formatarMoeda(precoUnitario)}</td>
                        <td class="text-center"><strong>${formatarMoeda(total)}</strong></td>
                    </tr>
                `;
            }).join('') : '<tr><td colspan="4" class="text-center text-muted py-3">Nenhum item encontrado</td></tr>';
        }

        renderizarFluxoStatusBloqueio(historico, pedido.status || '', 'bloqueio-fluxo-status');
        renderizarTimelineStatus(historico, 'bloqueio-timeline-status', true);

        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    } catch (error) {
        console.error('Erro ao mostrar bloqueio de edição:', error);
        mostrarErro('Erro ao carregar detalhes do bloqueio de edição.');
    }
}

function acompanharPedidoBloqueado() {
    const modalElement = document.getElementById('modalEdicaoBloqueadaPedido');
    const pedidoId = modalElement?.getAttribute('data-pedido-id');
    const modal = modalElement ? bootstrap.Modal.getInstance(modalElement) : null;
    if (modal) modal.hide();
    if (pedidoId) {
        setTimeout(() => visualizarPedido(pedidoId), 250);
    }
}

// Renderizar timeline de status
function renderizarTimelineStatus(historico, containerId, isCompleta = false) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    const statusNomes = {
        'em_analise': 'Em Análise',
        'pendente': 'Pendente',
        'aprovado_cotacao': 'Aprovado Cotação',
        'enviar_para_faturamento': 'Enviar para Faturamento',
        'aprovado_para_faturar': 'Aprovado para Faturar',
        'em_transito': 'Em Trânsito',
        'entregue': 'Entregue',
        'recebido': 'Recebido',
        'cancelado': 'Cancelado'
    };
    
    if (!historico || historico.length === 0) {
        container.innerHTML = '<div class="text-muted text-center py-3">Nenhum histórico disponível</div>';
        return;
    }
    
    // Ordenar por data (mais recente primeiro)
    const historicoOrdenado = [...historico].sort((a, b) => new Date(b.data_alteracao) - new Date(a.data_alteracao));
    
    // Limitar a 3 itens se não for completa
    const itensParaExibir = isCompleta ? historicoOrdenado : historicoOrdenado.slice(0, 3);
    
    let html = '';
    
    itensParaExibir.forEach((item, index) => {
        const isFirst = index === 0;
        // Usar status_novo (campo retornado pela API) ou status (compatibilidade)
        const statusAtual = item.status_novo || item.status || '';
        const isCancelled = statusAtual === 'cancelado';
        const statusClass = isFirst ? 'current' : (isCancelled ? 'cancelled' : 'completed');
        
        const dataFormatada = formatarDataHora(item.data_alteracao || item.data_alteracao);
        // Usar status_novo_nome se disponível, senão buscar no mapeamento
        let statusNome = item.status_novo_nome || statusNomes[statusAtual];
        
        // Se ainda não tiver nome, formatar o status (substituir _ por espaços e capitalizar)
        if (!statusNome && statusAtual) {
            statusNome = statusAtual
                .split('_')
                .map(palavra => palavra.charAt(0).toUpperCase() + palavra.slice(1))
                .join(' ');
        }
        
        // Se ainda não tiver, usar o status original ou mensagem padrão
        if (!statusNome) {
            statusNome = statusAtual || 'Status não informado';
            console.warn('Status não mapeado encontrado:', statusAtual, item);
        }
        
        // Usar usuario_nome (campo retornado pela API) ou usuario (compatibilidade)
        const usuarioNome = item.usuario_nome || item.usuario || 'Sistema';
        
        html += `
            <div class="timeline-item ${statusClass}">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <div class="timeline-status">${statusNome}</div>
                    <div class="timeline-date">${dataFormatada}</div>
                    ${usuarioNome ? `<div class="timeline-user">por ${usuarioNome}</div>` : ''}
                    ${item.observacao && isCompleta ? `<div class="timeline-observacao">${item.observacao}</div>` : ''}
                </div>
            </div>
        `;
    });
    
    if (!isCompleta && historico.length > 3) {
        html += `
            <div class="text-center mt-2">
                <small class="text-muted">+ ${historico.length - 3} alterações anteriores</small>
            </div>
        `;
    }
    
    container.innerHTML = html;
}

// Mostrar histórico completo
async function mostrarHistoricoCompleto() {
    try {
        const modal = document.getElementById('modalVisualizarPedido');
        const pedidoId = modal.getAttribute('data-pedido-id');
        
        if (!pedidoId) {
            mostrarErro('ID do pedido não encontrado');
            return;
        }
        
        // Carregar histórico
        const historico = await carregarHistoricoStatus(pedidoId);
        
        // Preencher informações do modal
        const numeroPedido = document.getElementById('view_numero_pedido')?.textContent || 'N/A';
        const statusAtual = document.getElementById('view-status-text')?.textContent || 'N/A';
        
        document.getElementById('historico-numero-pedido').textContent = numeroPedido;
        document.getElementById('historico-status-atual').textContent = statusAtual;
        
        // Renderizar timeline completa
        renderizarTimelineStatus(historico, 'timeline-completa', true);
        
        // Abrir modal
        const modalHistorico = new bootstrap.Modal(document.getElementById('modalHistoricoCompleto'));
        modalHistorico.show();
        
    } catch (error) {
        console.error('Erro ao mostrar histórico completo:', error);
        mostrarErro('Erro ao carregar histórico completo');
    }
}

// Formatar data e hora
function formatarDataHora(dataString) {
    const data = new Date(dataString);
    return data.toLocaleString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Enviar email do pedido para o fornecedor
 */
async function enviarEmailPedido() {
    try {
        const pedidoId = document.querySelector('#modalVisualizarPedido').getAttribute('data-pedido-id');
        
        if (!pedidoId) {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: 'ID do pedido não encontrado'
            });
            return;
        }
        
        // Mostrar loading
        Swal.fire({
            title: 'Enviando email...',
            text: 'Aguarde enquanto enviamos o pedido para o fornecedor',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Fazer requisição para enviar email
        const response = await fetch(`backend/api/pedidos_compra.php?action=enviar-email&id=${pedidoId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Email Enviado!',
                text: result.message,
                confirmButtonText: 'OK'
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro ao Enviar Email',
                text: result.error || 'Erro desconhecido ao enviar email',
                confirmButtonText: 'OK'
            });
        }
        
    } catch (error) {
        console.error('Erro ao enviar email:', error);
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'Erro interno ao enviar email',
            confirmButtonText: 'OK'
        });
    }
}

// Aprovar pedido
async function aprovarPedido() {
    try {
        const modal = document.getElementById('modalVisualizarPedido');
        const pedidoId = modal.getAttribute('data-pedido-id');
        
        if (!pedidoId) {
            mostrarErro('ID do pedido não encontrado');
            return;
        }
        
        // Obter status atual do pedido
        const statusAtual = document.getElementById('view-status-text')?.textContent?.toLowerCase().replace(/\s+/g, '_') || '';
        
        // Determinar próximo status baseado no atual
        let proximoStatus, tituloConfirmacao, textoConfirmacao, mensagemSucesso;
        
        if (statusAtual === 'em_analise' || statusAtual === 'em análise') {
            proximoStatus = 'pendente';
            tituloConfirmacao = 'Aprovar para Pendente';
            textoConfirmacao = 'Tem certeza que deseja aprovar este pedido para status Pendente?';
            mensagemSucesso = 'Pedido aprovado para status Pendente!';
        } else {
            proximoStatus = 'aprovado';
            tituloConfirmacao = 'Confirmar Aprovação';
            textoConfirmacao = 'Tem certeza que deseja aprovar este pedido de compra?';
            mensagemSucesso = 'Pedido aprovado com sucesso!';
        }
        
        // Confirmação de aprovação
        const result = await Swal.fire({
            title: tituloConfirmacao,
            text: textoConfirmacao,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, Aprovar!',
            cancelButtonText: 'Cancelar'
        });
        
        if (result.isConfirmed) {
            console.log('🔄 Aprovando pedido ID:', pedidoId, 'para status:', proximoStatus);
            
            const response = await fetch(`backend/api/pedidos_compra.php?action=update-status&id=${pedidoId}`, {
                method: 'PUT',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    status: proximoStatus,
                    observacao: `Pedido aprovado pelo usuário para ${proximoStatus}`
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                Swal.fire({
                    title: 'Pedido Aprovado!',
                    text: mensagemSucesso,
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
                
                // Atualizar status no modal
                const statusBadge = document.getElementById('view-status-badge');
                const statusText = document.getElementById('view-status-text');
                const statusCard = document.getElementById('view-status-card');
                const statusAtivo = document.getElementById('view-status-ativo');
                
                if (statusBadge && statusText && statusCard && statusAtivo) {
                    configurarStatusPedido(statusBadge, statusText, statusCard, statusAtivo, proximoStatus);
                }
                
                // Reconfigurar botões de ação
                configurarBotoesAcao(proximoStatus);
                
                // Recarregar dados
                carregarPedidos();
                carregarEstatisticas();
                
            } else {
                mostrarErro(data.error || 'Erro ao aprovar pedido');
            }
        }
        
    } catch (error) {
        console.error('Erro ao aprovar pedido:', error);
        mostrarErro('Erro ao aprovar pedido');
    }
}

// Enviar pedido para produção
async function enviarParaProducao() {
    try {
        const modal = document.getElementById('modalVisualizarPedido');
        const pedidoId = modal.getAttribute('data-pedido-id');
        
        if (!pedidoId) {
            mostrarErro('ID do pedido não encontrado');
            return;
        }
        
        const result = await Swal.fire({
            title: 'Enviar para Produção',
            text: 'Tem certeza que deseja enviar este pedido para produção?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#17a2b8',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, Enviar!',
            cancelButtonText: 'Cancelar'
        });
        
        if (result.isConfirmed) {
            console.log('🔄 Enviando pedido para produção ID:', pedidoId);
            
            const response = await fetch(`backend/api/pedidos_compra.php?action=update-status&id=${pedidoId}`, {
                method: 'PUT',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    status: 'em_producao',
                    observacao: 'Pedido enviado para produção'
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                Swal.fire({
                    title: 'Pedido Enviado para Produção!',
                    text: 'O pedido foi enviado para produção com sucesso.',
                    icon: 'info',
                    confirmButtonText: 'OK'
                });
                
                // Atualizar status no modal
                const statusBadge = document.getElementById('view-status-badge');
                const statusText = document.getElementById('view-status-text');
                const statusCard = document.getElementById('view-status-card');
                const statusAtivo = document.getElementById('view-status-ativo');
                
                if (statusBadge && statusText && statusCard && statusAtivo) {
                    configurarStatusPedido(statusBadge, statusText, statusCard, statusAtivo, 'em_producao');
                }
                
                // Reconfigurar botões de ação
                configurarBotoesAcao('em_producao');
                
                // Recarregar dados
                carregarPedidos();
                carregarEstatisticas();
                
            } else {
                mostrarErro(data.error || 'Erro ao enviar pedido para produção');
            }
        }
        
    } catch (error) {
        console.error('Erro ao enviar pedido para produção:', error);
        mostrarErro('Erro ao enviar pedido para produção');
    }
}

// Marcar pedido como enviado
async function marcarComoEnviado() {
    try {
        const modal = document.getElementById('modalVisualizarPedido');
        const pedidoId = modal.getAttribute('data-pedido-id');
        
        if (!pedidoId) {
            mostrarErro('ID do pedido não encontrado');
            return;
        }
        
        const result = await Swal.fire({
            title: 'Marcar como Enviado',
            text: 'Tem certeza que deseja marcar este pedido como enviado?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, Marcar!',
            cancelButtonText: 'Cancelar'
        });
        
        if (result.isConfirmed) {
            console.log('🔄 Marcando pedido como enviado ID:', pedidoId);
            
            const response = await fetch(`backend/api/pedidos_compra.php?action=update-status&id=${pedidoId}`, {
                method: 'PUT',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    status: 'enviado',
                    observacao: 'Pedido marcado como enviado'
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                Swal.fire({
                    title: 'Pedido Marcado como Enviado!',
                    text: 'O pedido foi marcado como enviado com sucesso.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                
                // Atualizar status no modal
                const statusBadge = document.getElementById('view-status-badge');
                const statusText = document.getElementById('view-status-text');
                const statusCard = document.getElementById('view-status-card');
                const statusAtivo = document.getElementById('view-status-ativo');
                
                if (statusBadge && statusText && statusCard && statusAtivo) {
                    configurarStatusPedido(statusBadge, statusText, statusCard, statusAtivo, 'enviado');
                }
                
                // Reconfigurar botões de ação
                configurarBotoesAcao('enviado');
                
                // Recarregar dados
                carregarPedidos();
                carregarEstatisticas();
                
            } else {
                mostrarErro(data.error || 'Erro ao marcar pedido como enviado');
            }
        }
        
    } catch (error) {
        console.error('Erro ao marcar pedido como enviado:', error);
        mostrarErro('Erro ao marcar pedido como enviado');
    }
}

// Marcar pedido como recebido
async function marcarComoRecebido() {
    try {
        const modal = document.getElementById('modalVisualizarPedido');
        const pedidoId = modal.getAttribute('data-pedido-id');
        
        if (!pedidoId) {
            mostrarErro('ID do pedido não encontrado');
            return;
        }
        
        const result = await Swal.fire({
            title: 'Marcar como Recebido',
            text: 'Tem certeza que deseja marcar este pedido como recebido?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, Marcar!',
            cancelButtonText: 'Cancelar'
        });
        
        if (result.isConfirmed) {
            console.log('🔄 Marcando pedido como recebido ID:', pedidoId);
            
            const response = await fetch(`backend/api/pedidos_compra.php?action=update-status&id=${pedidoId}`, {
                method: 'PUT',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    status: 'recebido',
                    observacao: 'Pedido marcado como recebido'
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                Swal.fire({
                    title: 'Pedido Marcado como Recebido!',
                    text: 'O pedido foi marcado como recebido com sucesso.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
                
                // Atualizar status no modal
                const statusBadge = document.getElementById('view-status-badge');
                const statusText = document.getElementById('view-status-text');
                const statusCard = document.getElementById('view-status-card');
                const statusAtivo = document.getElementById('view-status-ativo');
                
                if (statusBadge && statusText && statusCard && statusAtivo) {
                    configurarStatusPedido(statusBadge, statusText, statusCard, statusAtivo, 'recebido');
                }
                
                // Reconfigurar botões de ação
                configurarBotoesAcao('recebido');
                
                // Recarregar dados
                carregarPedidos();
                carregarEstatisticas();
                
            } else {
                mostrarErro(data.error || 'Erro ao marcar pedido como recebido');
            }
        }
        
    } catch (error) {
        console.error('Erro ao marcar pedido como recebido:', error);
        mostrarErro('Erro ao marcar pedido como recebido');
    }
}

// Cancelar pedido
async function cancelarPedido() {
    try {
        const modal = document.getElementById('modalVisualizarPedido');
        const pedidoId = modal.getAttribute('data-pedido-id');
        
        if (!pedidoId) {
            mostrarErro('ID do pedido não encontrado');
            return;
        }
        
        const result = await Swal.fire({
            title: 'Cancelar Pedido',
            text: 'Tem certeza que deseja cancelar este pedido? Esta ação não pode ser desfeita.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, Cancelar!',
            cancelButtonText: 'Não'
        });
        
        if (result.isConfirmed) {
            console.log('🔄 Cancelando pedido ID:', pedidoId);
            
            const response = await fetch(`backend/api/pedidos_compra.php?action=update-status&id=${pedidoId}`, {
                method: 'PUT',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    status: 'cancelado',
                    observacao: 'Pedido cancelado pelo usuário'
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                Swal.fire({
                    title: 'Pedido Cancelado!',
                    text: 'O pedido foi cancelado com sucesso.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                
                // Atualizar status no modal
                const statusBadge = document.getElementById('view-status-badge');
                const statusText = document.getElementById('view-status-text');
                const statusCard = document.getElementById('view-status-card');
                const statusAtivo = document.getElementById('view-status-ativo');
                
                if (statusBadge && statusText && statusCard && statusAtivo) {
                    configurarStatusPedido(statusBadge, statusText, statusCard, statusAtivo, 'cancelado');
                }
                
                // Reconfigurar botões de ação
                configurarBotoesAcao('cancelado');
                
                // Recarregar dados
                carregarPedidos();
                carregarEstatisticas();
                
            } else {
                mostrarErro(data.error || 'Erro ao cancelar pedido');
            }
        }
        
    } catch (error) {
        console.error('Erro ao cancelar pedido:', error);
        mostrarErro('Erro ao cancelar pedido');
    }
}

// Limpar modal quando fechado
function limparModalVisualizacao() {
    try {
        // Limpar apenas o conteúdo dos campos, não remover os elementos
        const camposParaLimpar = [
            'view_numero_pedido',
            'view_data_pedido',
            'view_filial',
            'view_fornecedor',
            'view_data_entrega',
            'view_solicitante',
            'view-total-itens',
            'view-quantidade-total',
            'view-preco-medio',
            'view_valor_total',
            'view_observacoes',
            'view-data-criacao',
            'view-data-atualizacao'
        ];
        
        camposParaLimpar.forEach(id => {
            const elemento = document.getElementById(id);
            if (elemento) {
                elemento.textContent = '';
            }
        });
        
        // Limpar tabela de itens
        const tbody = document.getElementById('view_itens_tbody');
        if (tbody) {
            tbody.innerHTML = '';
        }
        
        // Resetar status badge e card - PRESERVAR os elementos, apenas atualizar conteúdo
        const statusBadge = document.getElementById('view-status-badge');
        const statusText = document.getElementById('view-status-text');
        const statusCard = document.getElementById('view-status-card');
        const statusAtivo = document.getElementById('view-status-ativo');
        
        // Verificar se os elementos existem antes de tentar modificá-los
        if (statusBadge && statusText) {
            // Limpar apenas o texto, preservando o elemento span
            statusText.textContent = 'Pendente';
            
            // Resetar estilos do badge
            statusBadge.style.background = 'rgba(234, 179, 8, 0.1)';
            statusBadge.style.color = '#d97706';
            statusBadge.style.borderColor = 'rgba(234, 179, 8, 0.2)';
        }
        
        if (statusCard && statusAtivo) {
            // Limpar apenas o texto, preservando o elemento span
            statusAtivo.textContent = 'Pendente';
            statusAtivo.className = 'fw-bold text-warning';
            
            // Resetar estilos do card
            statusCard.style.background = 'rgba(234, 179, 8, 0.1)';
            statusCard.style.color = '#d97706';
            statusCard.style.borderColor = 'rgba(234, 179, 8, 0.2)';
        }
        
        // Preservar atributo de ID do pedido para funcionalidades do modal
        // O ID será mantido enquanto o modal estiver ativo
        
        console.log('Modal de visualização limpo com sucesso');
    } catch (error) {
        console.error('Erro ao limpar modal:', error);
    }
}

// Adicionar event listeners quando o documento estiver carregado
document.addEventListener('DOMContentLoaded', function() {
    // Event listener para quando o modal for fechado
    const modalVisualizar = document.getElementById('modalVisualizarPedido');
    if (modalVisualizar) {
        modalVisualizar.addEventListener('hidden.bs.modal', function() {
            limparModalVisualizacao();
        });
    }
    
    // Event listener para o modal de novo pedido - reabilitar botão quando fechado ou aberto
    const modalNovoPedido = document.getElementById('modalNovoPedido');
    if (modalNovoPedido) {
        modalNovoPedido.addEventListener('hidden.bs.modal', function() {
            const btnSalvar = document.querySelector('#modalNovoPedido .modal-footer .btn-primary');
            if (btnSalvar) {
                btnSalvar.disabled = false;
                btnSalvar.innerHTML = 'Salvar Pedido';
                btnSalvar.removeAttribute('data-original-html');
            }
        });
        
        // Reabilitar botão quando o modal for mostrado
        modalNovoPedido.addEventListener('show.bs.modal', function() {
            const btnSalvar = document.querySelector('#modalNovoPedido .modal-footer .btn-primary');
            if (btnSalvar) {
                btnSalvar.disabled = false;
                btnSalvar.innerHTML = 'Salvar Pedido';
                btnSalvar.removeAttribute('data-original-html');
            }
        });
    }
    
    // Event listeners para os novos botões do fluxo de status
    document.getElementById('btn-aprovar-pendente')?.addEventListener('click', () => atualizarStatusPedido('pendente'));
    document.getElementById('btn-aprovar-cotacao')?.addEventListener('click', () => atualizarStatusPedido('aprovado_cotacao'));
    document.getElementById('btn-aprovar-faturamento')?.addEventListener('click', () => atualizarStatusPedido('aprovado_para_faturar'));
    document.getElementById('btn-marcar-transito')?.addEventListener('click', () => atualizarStatusPedido('em_transito'));
    document.getElementById('btn-marcar-entregue')?.addEventListener('click', () => atualizarStatusPedido('entregue'));
    document.getElementById('btn-confirmar-recebimento')?.addEventListener('click', () => atualizarStatusPedido('recebido'));
    document.getElementById('btn-cancelar')?.addEventListener('click', () => atualizarStatusPedido('cancelado'));
    document.getElementById('btn-voltar-status')?.addEventListener('click', mostrarOpcoesVoltarStatus);
    
    // Event listeners para botões antigos (mantidos para compatibilidade)
    document.getElementById('btn-imprimir')?.addEventListener('click', imprimirPedido);
    document.getElementById('btn-editar')?.addEventListener('click', editarPedidoAtual);
    document.getElementById('btn-editar-pedido')?.addEventListener('click', editarPedidoAtual);
    document.getElementById('btn-enviar-email')?.addEventListener('click', enviarEmailPedido);
    document.getElementById('btn-importar-csv-cliente-edicao')?.addEventListener('click', () => {
        document.getElementById('input-csv-cliente-edicao')?.click();
    });
    document.getElementById('input-csv-cliente-edicao')?.addEventListener('change', (event) => {
        const arquivo = event.target?.files?.[0];
        importarCsvClienteNaEdicao(arquivo);
        event.target.value = '';
    });
    // Event listener removido - botões específicos já têm onclick definido no HTML
    document.getElementById('btn-enviar-producao')?.addEventListener('click', enviarParaProducao);
    document.getElementById('btn-marcar-enviado')?.addEventListener('click', marcarComoEnviado);
    document.getElementById('btn-marcar-recebido')?.addEventListener('click', marcarComoRecebido);
    document.getElementById('btn-cancelar')?.addEventListener('click', cancelarPedido);
});

// Excluir pedido
function excluirPedido(id) {
    Swal.fire({
        title: 'Confirmar exclusão',
        text: 'Tem certeza que deseja excluir este pedido?',
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

// Confirmar exclusão
async function confirmarExclusao(id) {
    try {
        const response = await fetch(`backend/api/pedidos_compra.php?id=${id}`, {
            method: 'DELETE',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            mostrarSucesso('Pedido excluído com sucesso!');
            carregarPedidos();
            carregarEstatisticas();
        } else {
            mostrarErro(data.error || 'Erro ao excluir pedido');
        }
    } catch (error) {
        console.error('Erro ao excluir pedido:', error);
        mostrarErro('Erro ao excluir pedido');
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
    document.getElementById('filtro-fornecedor').value = '';
    document.getElementById('data-inicio').value = '';
    paginaAtual = 1;
    carregarPedidos();
}

function exportarXLS() {
    Swal.fire({
        title: 'Exportar Pedidos',
        text: 'Funcionalidade de exportação em desenvolvimento',
        icon: 'info',
        confirmButtonText: 'OK'
    });
}

function imprimir() {
    window.print();
}

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

// Função de loading
function mostrarLoading(mostrar) {
    const loadingElement = document.getElementById('loading');
    if (loadingElement) {
        loadingElement.style.display = mostrar ? 'block' : 'none';
    }
} 

// Função para buscar o último preço pago de um material
async function buscarUltimoPrecoMaterial(idMaterial, idFilial, precoAtual) {
    try {
        const params = new URLSearchParams({
            id_material: idMaterial
        });
        
        if (idFilial) {
            params.append('id_filial', idFilial);
        }
        
        const response = await fetch(`backend/api/pedidos_compra.php?action=ultimo-preco-material&${params}`, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        const container = document.getElementById(`ultimo-preco-${idMaterial}`);
        
        if (data.success && data.ultimo_preco) {
            const ultimoPreco = parseFloat(data.ultimo_preco.ultimo_preco);
            const precoAtualFloat = parseFloat(precoAtual);
            
            let comparacaoClass = '';
            let comparacaoIcon = '';
            let comparacaoTexto = '';
            
            if (ultimoPreco > 0) {
                const diferenca = precoAtualFloat - ultimoPreco;
                const percentual = (diferenca / ultimoPreco) * 100;
                
                if (diferenca > 0) {
                    comparacaoClass = 'text-danger';
                    comparacaoIcon = 'bi-arrow-up';
                    comparacaoTexto = `+${formatarMoeda(diferenca)} (+${percentual.toFixed(1)}%)`;
                } else if (diferenca < 0) {
                    comparacaoClass = 'text-success';
                    comparacaoIcon = 'bi-arrow-down';
                    comparacaoTexto = `${formatarMoeda(diferenca)} (${percentual.toFixed(1)}%)`;
                } else {
                    comparacaoClass = 'text-muted';
                    comparacaoIcon = 'bi-dash';
                    comparacaoTexto = 'Sem alteração';
                }
            }
            
            container.innerHTML = `
                <div class="ultimo-preco-info">
                    <small class="text-muted">
                        <i class="bi bi-clock-history me-1"></i>
                        Último: ${formatarMoeda(ultimoPreco)}
                    </small>
                    ${ultimoPreco > 0 ? `
                        <br>
                        <small class="${comparacaoClass}">
                            <i class="bi ${comparacaoIcon} me-1"></i>
                            ${comparacaoTexto}
                        </small>
                    ` : ''}
                </div>
            `;
        } else {
            container.innerHTML = `
                <small class="text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    Sem histórico de preços
                </small>
            `;
        }
    } catch (error) {
        console.error('Erro ao buscar último preço:', error);
        const container = document.getElementById(`ultimo-preco-${idMaterial}`);
        if (container) {
            container.innerHTML = `
                <small class="text-muted">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Erro ao carregar histórico
                </small>
            `;
        }
    }
}

// ===== FUNÇÕES DO CHAT =====
let chatInterval;
let pedidoIdAtual;

// Carregar mensagens do chat
async function carregarChat() {
    console.log('carregarChat() chamada');
    console.log('window.pedidoAtual:', window.pedidoAtual);
    
    // Tentar obter o ID do pedido de diferentes fontes
    let pedidoId = null;
    
    if (window.pedidoAtual && window.pedidoAtual.id) {
        pedidoId = window.pedidoAtual.id;
    } else {
        // Tentar obter do modal
        const modalElement = document.getElementById('modalVisualizarPedido');
        if (modalElement) {
            pedidoId = modalElement.getAttribute('data-pedido-id');
        }
    }
    
    if (!pedidoId) {
        console.error('ID do pedido não encontrado');
        document.getElementById('chat-messages').innerHTML = `
            <div class="text-center text-danger">
                <i class="bi bi-exclamation-triangle fs-1"></i>
                <p>Erro: ID do pedido não encontrado</p>
            </div>
        `;
        return;
    }
    
    pedidoIdAtual = parseInt(pedidoId);
    console.log('pedidoIdAtual definido como:', pedidoIdAtual);
    
    try {
        const requestData = {
            action: 'listar_mensagens',
            pedido_id: pedidoIdAtual
        };
        console.log('Enviando dados:', requestData);
        
        const response = await fetch('backend/api/chat-pedidos.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(requestData)
        });
        
        console.log('Response status:', response.status);
        console.log('Response ok:', response.ok);
        
        const data = await response.json();
        console.log('Dados recebidos da API:', data);
        
        if (data.success) {
            console.log('Mensagens recebidas:', data.mensagens);
            console.log('Quantidade de mensagens:', data.mensagens ? data.mensagens.length : 0);
            renderizarMensagens(data.mensagens);
            marcarMensagensComoLidas();
            
            // Iniciar atualização automática
            if (chatInterval) clearInterval(chatInterval);
            chatInterval = setInterval(() => {
                if (document.getElementById('chat').classList.contains('active')) {
                    carregarNovasMensagens();
                }
            }, 3000);
        } else {
            console.error('API retornou erro:', data.error);
        }
    } catch (error) {
        console.error('Erro ao carregar chat:', error);
        document.getElementById('chat-messages').innerHTML = `
            <div class="text-center text-danger">
                <i class="bi bi-exclamation-triangle fs-1"></i>
                <p>Erro ao carregar mensagens</p>
            </div>
        `;
    }
}

// Renderizar mensagens
function renderizarMensagens(mensagens) {
    console.log('renderizarMensagens chamada com:', mensagens);
    const container = document.getElementById('chat-messages');
    
    if (!container) {
        console.error('Container chat-messages não encontrado! Verifique se o elemento existe no DOM.');
        return;
    }
    
    if (!mensagens || mensagens.length === 0) {
        container.innerHTML = `
            <div class="text-center text-muted">
                <i class="bi bi-chat-dots fs-1"></i>
                <p>Nenhuma mensagem ainda. Inicie a conversa!</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = mensagens.map(msg => {
        // Usar eh_minha se disponível, senão verificar pelo tipo_usuario
        const isUsuario = msg.eh_minha !== undefined ? msg.eh_minha : (msg.tipo_usuario === 'empresa');
        const dataFormatada = msg.data_envio_formatada || new Date(msg.data_envio).toLocaleString('pt-BR');
        const nomeRemetente = msg.nome_remetente || (isUsuario ? 'Você' : 'Fornecedor');
        
        // Escapar HTML para prevenir XSS
        const mensagemEscapada = String(msg.mensagem || '').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        
        return `
            <div class="message mb-3 ${isUsuario ? 'text-end' : 'text-start'}">
                <div class="d-inline-block p-3 rounded ${isUsuario ? 'bg-primary text-white' : 'bg-light'}" style="max-width: 70%;">
                    <div class="message-content">${mensagemEscapada}</div>
                    <small class="${isUsuario ? 'text-light' : 'text-muted'} d-block mt-1">
                        ${nomeRemetente} • ${dataFormatada}
                    </small>
                </div>
            </div>
        `;
    }).join('');
    
    // Scroll para o final
    container.scrollTop = container.scrollHeight;
}

// Carregar apenas novas mensagens
async function carregarNovasMensagens() {
    if (!pedidoIdAtual) return;
    
    try {
        const response = await fetch('backend/api/chat-pedidos.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'listar_mensagens',
                pedido_id: pedidoIdAtual
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            renderizarMensagens(data.mensagens);
        }
    } catch (error) {
        console.error('Erro ao carregar novas mensagens:', error);
    }
}

// Enviar mensagem
async function enviarMensagem() {
    console.log('enviarMensagem() chamada');
    
    const input = document.getElementById('nova-mensagem');
    console.log('Input encontrado:', input);
    
    const mensagem = input ? input.value.trim() : '';
    console.log('Mensagem:', mensagem);
    console.log('pedidoIdAtual:', pedidoIdAtual);
    
    if (!mensagem) {
        console.log('Retornando: mensagem vazia');
        Swal.fire('Atenção', 'Digite uma mensagem antes de enviar.', 'warning');
        return;
    }
    
    // Verificar se temos um ID válido do pedido
    if (!pedidoIdAtual || isNaN(pedidoIdAtual) || pedidoIdAtual <= 0) {
        console.log('Retornando: pedidoIdAtual não definido ou inválido');
        Swal.fire('Erro', 'ID do pedido não encontrado. Feche e abra o pedido novamente.', 'error');
        return;
    }
    
    try {
        const requestData = {
            action: 'enviar_mensagem',
            pedido_id: pedidoIdAtual,
            mensagem: mensagem
        };
        console.log('Enviando dados:', requestData);
        
        const response = await fetch('backend/api/chat-pedidos.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(requestData)
        });
        
        console.log('Response status:', response.status);
        console.log('Response ok:', response.ok);
        
        const data = await response.json();
        console.log('Dados recebidos da API:', data);
        
        if (data.success) {
            console.log('Mensagem enviada com sucesso');
            input.value = '';
            carregarNovasMensagens();
        } else {
            console.error('API retornou erro:', data.error);
            Swal.fire('Erro', 'Erro ao enviar mensagem. Tente novamente.', 'error');
        }
    } catch (error) {
        console.error('Erro ao enviar mensagem:', error);
        Swal.fire('Erro', 'Erro ao enviar mensagem. Tente novamente.', 'error');
    }
}

// Marcar mensagens como lidas
async function marcarMensagensComoLidas() {
    if (!pedidoIdAtual) return;
    
    try {
        await fetch('backend/api/chat-pedidos.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'marcar_como_lida',
                pedido_id: pedidoIdAtual
            })
        });
    } catch (error) {
        console.error('Erro ao marcar mensagens como lidas:', error);
    }
}

// Event listeners para o chat
document.addEventListener('DOMContentLoaded', function() {
    console.log('Event listeners do chat sendo configurados');
    
    // Event listener para enviar mensagem com Enter
    const inputMensagem = document.getElementById('nova-mensagem');
    console.log('Input mensagem encontrado:', inputMensagem);
    if (inputMensagem) {
        inputMensagem.addEventListener('keypress', function(e) {
            console.log('Tecla pressionada no input:', e.key);
            if (e.key === 'Enter' && !e.shiftKey) {
                console.log('Enter pressionado, enviando mensagem');
                e.preventDefault();
                enviarMensagem();
            }
        });
    }
    
    // Event listener para o botão de enviar
    const btnEnviar = document.getElementById('btn-enviar-mensagem');
    console.log('Botão enviar encontrado:', btnEnviar);
    if (btnEnviar) {
        btnEnviar.addEventListener('click', function() {
            console.log('Botão enviar clicado');
            enviarMensagem();
        });
    }
    
    // Event listener para quando a aba de chat é ativada
    const chatTab = document.querySelector('[data-bs-target="#chat"]');
    console.log('Aba chat encontrada:', chatTab);
    if (chatTab) {
        chatTab.addEventListener('shown.bs.tab', function() {
            console.log('Aba chat ativada');
            carregarChat();
        });
    }
    
    // Limpar interval quando modal é fechado
    const modalPedido = document.getElementById('modalVisualizarPedido');
    console.log('Modal pedido encontrado:', modalPedido);
    if (modalPedido) {
        modalPedido.addEventListener('hidden.bs.modal', function() {
            console.log('Modal fechado, limpando chat');
            if (chatInterval) {
                clearInterval(chatInterval);
                chatInterval = null;
            }
            pedidoIdAtual = null;
        });
    }
});

// Visualizar Nota Fiscal do Pedido
async function visualizarNFPedido() {
    try {
        const btnVisualizarNF = document.getElementById('btn-visualizar-nf');
        if (!btnVisualizarNF) {
            mostrarErro('Botão de visualizar NF não encontrado');
            return;
        }
        
        const urlNF = btnVisualizarNF.getAttribute('data-nf-url');
        if (!urlNF || urlNF.trim() === '') {
            mostrarErro('URL da Nota Fiscal não encontrada');
            return;
        }
        
        // Construir URL completa
        let url = urlNF;
        
        // Se a URL não começar com http:// ou https://, construir URL completa
        if (!url.match(/^https?:\/\//)) {
            // Se começar com /, usar como está (relativa à raiz)
            if (url.startsWith('/')) {
                // Construir URL completa usando a origem atual
                url = window.location.origin + url;
            } else {
                // Se não começar com /, adicionar /
                url = window.location.origin + '/' + url;
            }
        }
        
        // Remover barras duplicadas
        url = url.replace(/([^:]\/)\/+/g, '$1');
        
        // Abrir em nova aba
        window.open(url, '_blank');
        
    } catch (error) {
        console.error('Erro ao visualizar NF:', error);
        mostrarErro('Erro ao carregar Nota Fiscal: ' + error.message);
    }
}