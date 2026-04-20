<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fornecedor | Grupo Sorrisos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/addMovimentacao.css">
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <?php include 'menu.php'; ?>
    <main class="col-md-10 ms-sm-auto col-lg-10 px-md-4 py-4">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-truck fs-3 text-primary"></i>
          <h2 class="mb-0" style="font-weight:700;font-size:2rem;">Cadastro de Fornecedor</h2>
        </div>
        <a href="fornecedores.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Voltar</a>
      </div>
      <form>
        <div class="row row-cols-1 row-cols-lg-2 g-4">
          <div class="col-lg-12">
            <!-- Dados Gerais da Movimentação -->
            <div class="card-section mb-4">
              <div class="card-header-blue p-3"><i class="bi bi-clipboard-data me-2"></i>Dados Gerais da Movimentação</div>
              <div class="card-body bg-white p-4">
                <div class="row g-3 mb-3">
                  <div class="col-md-4">
                    <label class="form-label">Tipo de Movimentação *</label>
                    <select class="form-select"><option>Selecione o tipo</option></select>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Data da Movimentação *</label>
                    <div class="input-group">
                      <input type="date" class="form-control">
                      <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Número do Documento</label>
                    <input type="text" class="form-control" placeholder="Ex: NF-123456, OS-789">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Fornecedor/Cliente</label>
                    <input type="text" class="form-control" placeholder="Nome do fornecedor ou cliente">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Responsável *</label>
                    <input type="text" class="form-control" placeholder="Nome do responsável pela movimentação">
                  </div>
                </div>
              </div>
            </div>
            <!-- Dados do Item Movimentado -->
            <div class="card-section mb-4">
              <div class="card-header-lightblue p-3"><i class="bi bi-box me-2"></i>Dados do Item Movimentado</div>
              <div class="card-body bg-white p-4">
                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <label class="form-label">Código do Produto</label>
                    <input type="text" class="form-control" placeholder="Código interno do produto">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Descrição do Produto *</label>
                    <input type="text" class="form-control" placeholder="Nome/descrição do produto">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Lote/Número de Série</label>
                    <input type="text" class="form-control" placeholder="Lote ou número de série">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Data de Validade</label>
                    <div class="input-group">
                      <input type="date" class="form-control" placeholder="dd/mm/aaaa">
                      <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- Quantidade e Custos -->
            <div class="card-section mb-4">
              <div class="card-header-green p-3"><i class="bi bi-123 me-2"></i>Quantidade e <span class="text-warning"><i class="bi bi-cash-coin"></i></span> Custos</div>
              <div class="card-body bg-white p-4">
                <div class="row g-3 mb-3">
                  <div class="col-md-4">
                    <label class="form-label">Quantidade *</label>
                    <input type="number" class="form-control" placeholder="0">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Unidade</label>
                    <select class="form-select"><option>Unidade (UN)</option></select>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Preço Unitário *</label>
                    <input type="number" class="form-control" placeholder="0,00">
                  </div>
                </div>
              </div>
            </div>
            <!-- Campos Adicionais -->
            <div class="card-section mb-4">
              <div class="card-header-gray p-3"><i class="bi bi-file-earmark-text me-2"></i>Campos Adicionais</div>
              <div class="card-body bg-white p-4">
                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <label class="form-label">Centro de Custo/Departamento</label>
                    <input type="text" class="form-control" placeholder="Ex: Produção, Vendas, Administração">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Localização</label>
                    <input type="text" class="form-control" placeholder="Ex: Almoxarifado A, Prateleira 15">
                  </div>
                  <div class="col-md-12">
                    <label class="form-label">Motivo da Movimentação</label>
                    <select class="form-select"><option>Selecione o motivo</option></select>
                  </div>
                  <div class="col-md-12">
                    <label class="form-label">Observações</label>
                    <textarea class="form-control" rows="2" placeholder="Notas internas, histórico de atendimento, detalhes da movimentação..."></textarea>
                  </div>
                </div>
              </div>
            </div>
            <div class="d-flex justify-content-end mt-4">
              <button type="submit" class="btn btn-primary px-5 py-2"><i class="bi bi-save me-1"></i> Salvar Movimentação</button>
            </div>
          </div>
        </div>
      </form>
    </main>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
