# Critérios de Listagem - material.php

## 📋 **Como Funciona a Listagem**

A página `material.php` lista materiais da tabela `tbl_catalogo_materiais` com JOIN nas tabelas relacionadas.

---

## ✅ **Campos OBRIGATÓRIOS para Aparecer na Lista**

### **1. Tabela Principal: `tbl_catalogo_materiais`**

| Campo | Tipo | Obrigatório | Valor Padrão | Descrição |
|-------|------|-------------|--------------|-----------|
| `ativo` | TINYINT(1) | **SIM** | `1` | **CRÍTICO**: Material deve estar ativo |
| `codigo` | VARCHAR(50) | **SIM** | - | Código único do material |
| `nome` | VARCHAR(200) | **SIM** | - | Nome/descrição do material |
| `id_categoria` | INT | Recomendado | NULL | Categoria do material |
| `id_unidade` | INT | Recomendado | `1` (UN) | Unidade de medida |
| `id_fornecedor` | INT | Opcional | NULL | Fornecedor do material |
| `id_fabricante` | INT | Opcional | NULL | Fabricante do material |

### **2. Campos Exibidos na Tabela (com JOIN)**

```sql
SELECT 
    cm.*,                          -- Todos os campos do catálogo
    c.nome_categoria,              -- Nome da categoria
    f.razao_social as fornecedor_nome,  -- Nome do fornecedor
    u.sigla as unidade_sigla,      -- Sigla da unidade (UN, CX, etc)
    ef.estoque_atual,              -- Estoque da filial
    ef.estoque_minimo,             -- Estoque mínimo
    fil.nome_filial                -- Nome da filial
FROM tbl_catalogo_materiais cm
LEFT JOIN tbl_categorias c ON cm.id_categoria = c.id_categoria
LEFT JOIN tbl_fornecedores f ON cm.id_fornecedor = f.id_fornecedor
LEFT JOIN tbl_unidades_medida u ON cm.id_unidade = u.id_unidade
LEFT JOIN tbl_estoque_filiais ef ON cm.id_catalogo = ef.id_catalogo
LEFT JOIN tbl_filiais fil ON ef.id_filial = fil.id_filial
WHERE cm.ativo = 1
  AND ef.id_filial = [FILIAL_SELECIONADA]
```

---

## 🎯 **Condições para Aparecer na Listagem**

### ✅ **Condições CRÍTICAS:**
1. `ativo = 1` na tabela `tbl_catalogo_materiais`
2. Ter registro na tabela `tbl_catalogo_materiais`
3. Filial deve estar selecionada no localStorage

### ⚠️ **Condições RECOMENDADAS:**
4. Ter `id_unidade` válido (senão aparece sem unidade)
5. Ter `id_categoria` válido (senão aparece "Sem categoria")
6. Ter registro em `tbl_estoque_filiais` para a filial (senão estoque = 0)

---

## 🔍 **Filtros Disponíveis**

A página permite filtrar por:
- **Busca por texto**: Código, descrição ou marca
- **Categoria**: Dropdown com todas as categorias
- **Fornecedor**: Dropdown com todos os fornecedores
- **Status de Estoque**:
  - Em Estoque
  - Estoque Baixo
  - Sem Estoque
  - Precisa Ressuprimento

---

## 📊 **Campos Exibidos na Tabela**

| Coluna | Campo | Origem |
|--------|-------|--------|
| Código | `codigo` | `tbl_catalogo_materiais` |
| Descrição | `nome` | `tbl_catalogo_materiais` |
| Categoria | `nome_categoria` | `tbl_categorias` (JOIN) |
| Unidade | `unidade_sigla` | `tbl_unidades_medida` (JOIN) |
| Preço | `preco_unitario` | `tbl_estoque_filiais` |
| Estoque | `estoque_atual` | `tbl_estoque_filiais` |
| Ressuprimento | Calculado | Baseado em estoque atual vs mínimo |
| Fornecedor | `fornecedor_nome` | `tbl_fornecedores` (JOIN) |
| Status | Calculado | Badge: Em Estoque / Baixo / Sem Estoque |

---

## 🚀 **SQL de Importação Gerado**

Foi criado o arquivo: **`database/materiais_odontologicos_import.sql`**

### **Características:**
- ✅ 391 materiais do CSV
- ✅ Lookup automático de categorias com LIKE
- ✅ Lookup automático de unidades
- ✅ Lookup automático de fabricantes
- ✅ Todos os campos necessários preenchidos
- ✅ `ativo = 1` por padrão

### **Mapeamento Automático:**

#### Categorias (busca por palavras-chave):
- "Adesivo", "Resina" → DENTÍSTICA E ESTÉTICA
- "Agulha", "Anestésico" → ANESTESICOS E AGULHA GENGIVAL
- "Arco", "Bracket" → ORTODONTIA
- "Lima", "Endo" → ENDODONTIA
- "Broca" → BROCAS
- "Alginato", "Silicone" → MOLDAGEM E MODELO
- Outros → CLINICO GERAL

#### Unidades:
- "UNIDADE OU PECA" → UN (id_unidade = 1)
- "CAIXA" → CX (id_unidade = 6)
- "PACOTE" → PCT (id_unidade = 7)

#### Fabricantes (busca com LIKE):
- "3M" → 3M DO BRASIL
- "CREMER" → DENTAL CREMER
- "ORTHOMETRIC" → (Se cadastrado)
- Outros → NULL (se não encontrar)

---

## ⚠️ **IMPORTANTE APÓS IMPORTAR**

### **1. Verificar Materiais Importados:**
```sql
SELECT COUNT(*) as total 
FROM tbl_catalogo_materiais 
WHERE ativo = 1;
```

### **2. Verificar Categorias Atribuídas:**
```sql
SELECT c.nome_categoria, COUNT(*) as total
FROM tbl_catalogo_materiais m
LEFT JOIN tbl_categorias c ON m.id_categoria = c.id_categoria
WHERE m.ativo = 1
GROUP BY c.nome_categoria
ORDER BY total DESC;
```

### **3. Verificar Fabricantes Não Encontrados:**
```sql
SELECT DISTINCT observacoes
FROM tbl_catalogo_materiais
WHERE id_fabricante IS NULL
AND ativo = 1
LIMIT 20;
```

### **4. CRIAR ESTOQUE PARA FILIAIS:**

⚠️ **ATENÇÃO**: Depois de importar os materiais no catálogo, você precisa criar o estoque para cada filial!

**Opção A: Via Interface** (para cada material)
- Acesse `material.php`
- Clique no ícone de estoque (📦) do material
- Configure o estoque para a filial

**Opção B: Via SQL** (criar estoque 0 em todas as filiais)
```sql
-- Criar estoque inicial em todas as filiais para todos os materiais importados
INSERT INTO tbl_estoque_filiais (id_catalogo, id_filial, estoque_atual, estoque_minimo, estoque_maximo, preco_unitario, ativo)
SELECT 
    cm.id_catalogo,
    f.id_filial,
    0.00 as estoque_atual,
    10.00 as estoque_minimo,
    100.00 as estoque_maximo,
    cm.preco_unitario_padrao,
    1 as ativo
FROM tbl_catalogo_materiais cm
CROSS JOIN tbl_filiais f
WHERE cm.ativo = 1
  AND f.filial_ativa = 1
  AND NOT EXISTS (
      SELECT 1 FROM tbl_estoque_filiais ef 
      WHERE ef.id_catalogo = cm.id_catalogo 
      AND ef.id_filial = f.id_filial
  );
```

---

## 📦 **Arquivos Criados para Importação**

| # | Arquivo | Descrição |
|---|---------|-----------|
| 1 | `importar_materiais_csv.php` | Script PHP que lê o CSV e gera SQL |
| 2 | `database/materiais_odontologicos_import.sql` | **SQL FINAL** para importar (391 materiais) |
| 3 | `CRITERIOS_LISTAGEM_MATERIAIS.md` | Este documento |

---

## 🚀 **Ordem de Execução**

1. ✅ Executar: `database/inserir_categorias_materiais.sql` (se ainda não executou)
2. ✅ Executar: `database/adicionar_campo_fabricante.sql` (se ainda não executou)
3. ✅ Executar: `database/materiais_odontologicos_import.sql` ← **IMPORTAR MATERIAIS**
4. ✅ Criar estoque nas filiais (SQL acima OU via interface)

---

## 📞 **Troubleshooting**

### **Materiais não aparecem na listagem?**

Verifique:
1. ✅ Material tem `ativo = 1`?
2. ✅ Filial está selecionada no sistema?
3. ✅ Material tem estoque criado para a filial?
4. ✅ Categoria existe e está ativa?

### **Categorias vazias?**
Execute: `database/inserir_categorias_materiais.sql`

### **Fabricantes NULL?**
Cadastre os fabricantes faltantes em `addFornecedor.php` marcando "É Fabricante"

---

**Documento gerado em:** 03/11/2025

