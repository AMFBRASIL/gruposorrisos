<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Material | Grupo Sorrisos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/materiais.css">
    <style>
      body { background: #e5e9ef; }
      .card-section { border-radius: 10px; box-shadow: 0 2px 8px #0001; margin-bottom: 1.5rem; }
      .card-header-blue { background: linear-gradient(90deg, #2196f3 60%, #42a5f5 100%); color: #fff; border-radius: 10px 10px 0 0; font-weight: 600; font-size: 1.1rem; }
      .card-header-green { background: linear-gradient(90deg, #00bfae 60%, #1de9b6 100%); color: #fff; border-radius: 10px 10px 0 0; font-weight: 600; font-size: 1.1rem; }
      .card-header-gray { background: #444c54; color: #fff; border-radius: 10px 10px 0 0; font-weight: 600; font-size: 1.1rem; }
      .card-header-lightblue { background: #4fc3f7; color: #fff; border-radius: 10px 10px 0 0; font-weight: 600; font-size: 1.1rem; }
      .form-label { font-weight: 500; color: #222; }
      .form-control, .form-select { border-radius: 8px; min-height: 48px; font-size: 1.08rem; }
      .switch-status { display: flex; align-items: center; gap: 0.7rem; }
      .switch-status .form-check-input { width: 2.2em; height: 1.2em; }
      .info-system { background: #f7f9fb; border-radius: 8px; padding: 1.2rem; text-align: center; color: #64748b; }
      .btn-primary { background: #2196f3; border: none; font-size: 1.15rem; font-weight: 600; }
      .btn-primary:hover { background: #1976d2; }
      .btn-outline-secondary { font-size: 1.05rem; }
      @media (max-width: 991px) {
        .row-cols-lg-2 > .col-lg-9, .row-cols-lg-2 > .col-lg-3 { flex: 0 0 100%; max-width: 100%; }
      }
    </style>
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <?php include 'menu.php'; ?>
    <main class="col-md-10 ms-sm-auto col-lg-10 px-md-4 py-4">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-box-seam fs-3 text-primary"></i>
          <h2 class="mb-0" style="font-weight:700;font-size:2rem;">Cadastro de Material</h2>
        </div>
        <a href="material.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Voltar</a>
      </div>
      <form>
        <div class="row row-cols-1 row-cols-lg-2 g-4">
          <div class="col-lg-9">
            <!-- Informações Básicas -->
            <div class="card-section mb-4">
              <div class="card-header-blue p-3">Informações Básicas</div>
              <div class="card-body bg-white p-4">
                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <label class="form-label">Código do Material *</label>
                    <input type="text" class="form-control" placeholder="Ex: MAT001">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Código de Barras</label>
                    <input type="text" class="form-control" placeholder="7891234567890">
                    <div class="form-text"><input type="checkbox" class="form-check-input me-1">Código de barras para leitura automática</div>
                  </div>
                  <div class="col-md-12">
                    <label class="form-label">Nome do Material *</label>
                    <input type="text" class="form-control" placeholder="Nome completo do material">
                  </div>
                  <div class="col-md-12">
                    <label class="form-label">Descrição</label>
                    <textarea class="form-control" rows="2" placeholder="Descrição detalhada do material"></textarea>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Categoria *</label>
                    <select class="form-select"><option>Selecione uma opção</option></select>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Fornecedor</label>
                    <select class="form-select"><option>Selecione uma opção</option></select>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Filial *</label>
                    <select class="form-select"><option>Selecione uma opção</option></select>
                    <div class="form-text">Filial onde o material será cadastrado</div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Unidade de Medida *</label>
                    <select class="form-select"><option>Selecione uma opção</option></select>
                  </div>
                </div>
              </div>
            </div>
            <!-- Controle de Estoque -->
            <div class="card-section mb-4">
              <div class="card-header-green p-3">Controle de Estoque</div>
              <div class="card-body bg-white p-4">
                <div class="row g-3 mb-3">
                  <div class="col-md-4">
                    <label class="form-label">Preço Unitário</label>
                    <div class="input-group">
                      <span class="input-group-text">R$</span>
                      <input type="number" class="form-control" placeholder="0,00">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Localização no Estoque</label>
                    <input type="text" class="form-control" placeholder="Ex: Prateleira A-01">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Estoque Atual</label>
                    <input type="number" class="form-control" placeholder="0,000">
                    <div class="form-text">Atualizado automaticamente pelas movimentações</div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Estoque Mínimo</label>
                    <input type="number" class="form-control" placeholder="0,000">
                    <div class="form-text">Alerta quando estoque atingir este valor</div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Estoque Máximo</label>
                    <input type="number" class="form-control" placeholder="0,000">
                    <div class="form-text">Alerta quando estoque exceder este valor</div>
                  </div>
                </div>
              </div>
            </div>
            <!-- Observações -->
            <div class="card-section mb-4">
              <div class="card-header-lightblue p-3">Observações</div>
              <div class="card-body bg-white p-4">
                <textarea class="form-control" rows="2" placeholder="Observações adicionais sobre o material"></textarea>
              </div>
            </div>
          </div>
          <div class="col-lg-3">
            <!-- Status -->
            <div class="card-section mb-4">
              <div class="card-header-gray p-3">Status</div>
              <div class="card-body bg-white p-4">
                <div class="switch-status mb-2">
                  <input class="form-check-input" type="checkbox" id="ativoMaterial" checked>
                  <label class="form-label mb-0" for="ativoMaterial">Material Ativo</label>
                </div>
                <div class="form-text">Materiais inativos não aparecem nas listagens</div>
              </div>
            </div>
            <!-- Informações do Sistema -->
            <div class="card-section mb-4">
              <div class="card-header-gray p-3">Informações do Sistema</div>
              <div class="card-body bg-white p-4">
                <div class="info-system">
                  <i class="bi bi-info-circle fs-2 mb-2"></i><br>
                  Informações do sistema serão exibidas após salvar o material.
                </div>
              </div>
            </div>
            <!-- Ações -->
            <div class="card-section mb-4">
              <div class="card-header-blue p-3">Ações</div>
              <div class="card-body bg-white p-4">
                <button type="submit" class="btn btn-primary w-100 mb-2"><i class="bi bi-save me-1"></i> Salvar Material</button>
                <a href="material.php" class="btn btn-outline-secondary w-100"><i class="bi bi-list"></i> Listar Materiais</a>
              </div>
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
