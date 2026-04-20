# Deploy para Produção - Sistema de Estoque Grupo Sorrisos
**Data:** 03/11/2025 - **Versão 2** (Atualizada)

## 📋 Resumo das Alterações

### 1. Correção de Bug - API Pedidos de Compra
- **Problema:** Endpoint `backend/api/pedidos_compra.php?action=create` retornava erro "Ação não especificada"
- **Solução:** Corrigido para aceitar parâmetro `action` tanto via query string quanto no corpo JSON

### 2. Nova Funcionalidade - Fabricantes vs Fornecedores
- **Descrição:** Sistema agora diferencia fornecedores (quem vende) de fabricantes (quem produz)
- **Impacto:** 
  - Fornecedores podem ser marcados como fabricantes (`is_fabricante = 1`)
  - Materiais podem ter fornecedor E fabricante diferentes
  - Selects separados na tela de materiais

### 3. Inserção de Categorias de Materiais
- **Descrição:** Script para criar 24 categorias de materiais odontológicos no sistema

---

## 📦 Arquivos para Upload

### **🔴 EXECUTAR PRIMEIRO - Scripts SQL:**

#### 1. Categorias de Materiais
```
📁 database/inserir_categorias_materiais.sql
```
**Descrição:** Insere 24 categorias de materiais odontológicos
**Ação:** Executar no banco de dados via phpMyAdmin ou MySQL CLI
**Ordem:** 1º

#### 2. Campos de Fabricante
```
📁 database/adicionar_campo_fabricante.sql
```
**Descrição:** Adiciona campos `is_fabricante` em `tbl_fornecedores` e `id_fabricante` em `tbl_catalogo_materiais`
**Ação:** Executar no banco de dados via phpMyAdmin ou MySQL CLI
**Ordem:** 2º

#### 3. Corrigir Fabricantes (NOVO!)
```
📁 database/corrigir_fabricantes.sql
```
**Descrição:** Ativa fornecedores e marca como fabricantes conforme necessário
**Ação:** Executar no banco de dados (ajustar IDs conforme seu ambiente)
**Ordem:** 3º (OPCIONAL - só se necessário)

---

### **🔵 DEPOIS - Arquivos PHP (Upload após executar os scripts SQL):**

#### 4. Backend - API Pedidos de Compra (CORREÇÃO DE BUG)
```
📁 backend/api/pedidos_compra.php
```
**Descrição:** Correção do bug de ação não especificada
**Ação:** Substituir arquivo no servidor

#### 5. API - Fornecedores (NOVA FUNCIONALIDADE)
```
📁 api/fornecedores.php
```
**Descrição:** 
- Suporte para campo `is_fabricante`
- Novo endpoint `?action=fabricantes` (lista só fabricantes)
- Novo endpoint `?action=apenas-fornecedores` (lista só fornecedores não-fabricantes)
**Ação:** Substituir arquivo no servidor

#### 6. API - Materiais Nova Estrutura (NOVA FUNCIONALIDADE)
```
📁 api/materiais_nova_estrutura.php
```
**Descrição:** Suporte para campo `id_fabricante` ao criar/atualizar materiais
**Ação:** Substituir arquivo no servidor

#### 7. Model - Fornecedor (NOVA FUNCIONALIDADE)
```
📁 models/Fornecedor.php
```
**Descrição:** 
- Método `findFabricantes()` - lista só fabricantes (is_fabricante = 1)
- Método `findApenasFornecedores()` - lista só fornecedores (is_fabricante = 0)
**Ação:** Substituir arquivo no servidor

#### 8. Tela - Adicionar/Editar Fornecedor (MELHORIAS)
```
📁 addFornecedor.php
```
**Descrição:** 
- Checkbox "É Fabricante" 
- Checkbox "Ativo" melhorado e destacado
- Garantia de que novos fornecedores são ativos por padrão
**Ação:** Substituir arquivo no servidor

#### 9. Tela - Adicionar/Editar Material (NOVA FUNCIONALIDADE)
```
📁 addMaterial.php
```
**Descrição:** 
- Select "Fornecedor" - lista APENAS fornecedores (is_fabricante = 0)
- Select "Fabricante" - lista APENAS fabricantes (is_fabricante = 1)
- Campos separados e bem identificados
**Ação:** Substituir arquivo no servidor

---

## 🔧 Instruções de Deploy

### Passo 1: Backup
```bash
# Fazer backup do banco de dados antes de executar os scripts SQL
mysqldump -u [usuario] -p [nome_banco] > backup_antes_deploy_v2_20251103.sql

# Fazer backup dos arquivos PHP que serão substituídos
```

### Passo 2: Executar Scripts SQL (NA ORDEM!)
```sql
-- 1. Executar primeiro (Categorias)
SOURCE database/inserir_categorias_materiais.sql;

-- 2. Executar segundo (Fabricantes - cria campos)
SOURCE database/adicionar_campo_fabricante.sql;

-- 3. IMPORTANTE: Marcar fornecedores como fabricantes
-- Opção A: Ativar fornecedor que já é fabricante
UPDATE tbl_fornecedores SET ativo = 1 WHERE id_fornecedor = 12;

-- Opção B: Marcar fornecedor ativo como fabricante
UPDATE tbl_fornecedores SET is_fabricante = 1 WHERE id_fornecedor = 13;

-- Opção C: Marcar todos os ativos como fabricantes
UPDATE tbl_fornecedores SET is_fabricante = 1 WHERE ativo = 1;

-- 4. Verificar se as alterações foram aplicadas
DESCRIBE tbl_fornecedores;
DESCRIBE tbl_catalogo_materiais;
SELECT COUNT(*) FROM tbl_categorias;

-- 5. Verificar fabricantes
SELECT id_fornecedor, razao_social, ativo, is_fabricante 
FROM tbl_fornecedores 
WHERE ativo = 1 AND is_fabricante = 1;
```

### Passo 3: Upload dos Arquivos PHP

**Via FTP/SFTP:**
```
1. Conectar ao servidor
2. Navegar até a pasta raiz do sistema
3. Fazer upload dos 6 arquivos PHP mantendo a estrutura de pastas:
   - backend/api/pedidos_compra.php
   - api/fornecedores.php
   - api/materiais_nova_estrutura.php
   - models/Fornecedor.php
   - addFornecedor.php
   - addMaterial.php
```

### Passo 4: Testes em Produção

#### 1. **Testar Fabricantes vs Fornecedores:**
```
# API de fabricantes (deve retornar apenas is_fabricante = 1)
api/fornecedores.php?action=fabricantes

# API de fornecedores (deve retornar apenas is_fabricante = 0)
api/fornecedores.php?action=apenas-fornecedores
```

#### 2. **Testar Cadastro de Fornecedor:**
- Acessar: `addFornecedor.php`
- Verificar se checkbox "Fornecedor Ativo" está visível e em destaque
- Verificar se checkbox "É Fabricante" aparece
- Cadastrar um fornecedor marcando como "Ativo" e "É Fabricante"
- Verificar se salva com `ativo = 1` e `is_fabricante = 1`

#### 3. **Testar Cadastro de Material:**
- Acessar: `addMaterial.php`
- Verificar se select "Fornecedor" lista APENAS fornecedores (não fabricantes)
- Verificar se select "Fabricante" lista APENAS fabricantes
- Cadastrar um material selecionando fornecedor E fabricante diferentes

#### 4. **Testar API de Pedidos:**
- Testar: `backend/api/pedidos_compra.php?action=create`
- Verificar se não retorna erro "Ação não especificada"

---

## ✅ Checklist de Deploy

- [ ] Backup do banco de dados realizado
- [ ] Backup dos arquivos PHP realizado
- [ ] Script SQL de categorias executado
- [ ] Script SQL de fabricantes executado
- [ ] Pelo menos 1 fornecedor marcado como fabricante E ativo
- [ ] Campos verificados no banco (DESCRIBE tables)
- [ ] 24 categorias inseridas (SELECT COUNT)
- [ ] API `?action=fabricantes` retorna fornecedores
- [ ] API `?action=apenas-fornecedores` retorna fornecedores
- [ ] Arquivo `backend/api/pedidos_compra.php` enviado
- [ ] Arquivo `api/fornecedores.php` enviado
- [ ] Arquivo `api/materiais_nova_estrutura.php` enviado
- [ ] Arquivo `models/Fornecedor.php` enviado
- [ ] Arquivo `addFornecedor.php` enviado
- [ ] Arquivo `addMaterial.php` enviado
- [ ] Teste: Cadastro de fornecedor com "Ativo" marcado OK
- [ ] Teste: Fornecedor marcado como fabricante OK
- [ ] Teste: Material com fornecedor E fabricante diferentes OK
- [ ] Teste: Select Fornecedor lista só fornecedores OK
- [ ] Teste: Select Fabricante lista só fabricantes OK

---

## 🎯 Diferença entre Fornecedor e Fabricante

| Campo | is_fabricante | Significado | Onde Aparece |
|-------|---------------|-------------|--------------|
| **Fornecedor** | 0 | Apenas vende (não produz) | Select "Fornecedor" em addMaterial.php |
| **Fabricante** | 1 | Produz/Fabrica o material | Select "Fabricante" em addMaterial.php |

**Exemplo prático:**
- **3M DO BRASIL** (is_fabricante = 1) → É fabricante, produz os materiais
- **DENTAL CREMER** (is_fabricante = 0) → É só fornecedor, revende materiais de vários fabricantes

Um material pode ter:
- `id_fornecedor` = 13 (DENTAL CREMER) - quem vende
- `id_fabricante` = 12 (3M DO BRASIL) - quem produz

---

## 🔄 Rollback (em caso de problema)

### Reverter Banco de Dados:
```sql
-- 1. Remover campo is_fabricante de fornecedores
ALTER TABLE tbl_fornecedores DROP COLUMN is_fabricante;

-- 2. Remover campo id_fabricante de materiais
ALTER TABLE tbl_catalogo_materiais DROP FOREIGN KEY fk_catalogo_materiais_fabricante;
ALTER TABLE tbl_catalogo_materiais DROP INDEX idx_catalogo_materiais_fabricante;
ALTER TABLE tbl_catalogo_materiais DROP COLUMN id_fabricante;

-- 3. Restaurar backup completo
mysql -u [usuario] -p [nome_banco] < backup_antes_deploy_v2_20251103.sql
```

### Reverter Arquivos PHP:
- Restaurar os arquivos do backup anterior

---

## 📝 Notas Importantes

- ⚠️ **CRÍTICO:** Após executar os scripts SQL, você DEVE marcar pelo menos 1 fornecedor como fabricante E ativo
- ⚠️ Fornecedores com `ativo = 0` NÃO aparecem nos selects
- ⚠️ Fornecedores com `is_fabricante = 0` NÃO aparecem no select "Fabricante"
- ✅ Um fornecedor pode ser marcado como fabricante E fornecedor (is_fabricante = 1)
- ✅ Todas as alterações são RETROCOMPATÍVEIS
- ✅ Campos novos têm valores padrão seguros

---

**Desenvolvedor:** Sistema de Estoque Grupo Sorrisos  
**Data de Preparação:** 03/11/2025  
**Versão:** 2.0

