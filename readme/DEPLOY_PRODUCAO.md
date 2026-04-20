# Deploy para Produção - Sistema de Estoque Grupo Sorrisos
**Data:** 03/11/2025

## 📋 Resumo das Alterações

### 1. Correção de Bug - API Pedidos de Compra
- **Problema:** Endpoint `backend/api/pedidos_compra.php?action=create` retornava erro "Ação não especificada"
- **Solução:** Corrigido para aceitar parâmetro `action` tanto via query string quanto no corpo JSON

### 2. Nova Funcionalidade - Fabricantes
- **Descrição:** Adiciona suporte para diferenciar fornecedores que também são fabricantes
- **Impacto:** Permite associar materiais a seus fabricantes específicos

### 3. Inserção de Categorias de Materiais
- **Descrição:** Script para criar 24 categorias de materiais odontológicos no sistema

---

## 📦 Arquivos para Upload

### **Scripts SQL (Executar ANTES de fazer upload dos arquivos PHP)**

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

---

### **Arquivos PHP (Upload após executar os scripts SQL)**

#### 3. Backend - API Pedidos de Compra (CORREÇÃO DE BUG)
```
📁 backend/api/pedidos_compra.php
```
**Descrição:** Correção do bug de ação não especificada
**Ação:** Substituir arquivo no servidor

#### 4. API - Fornecedores (NOVA FUNCIONALIDADE)
```
📁 api/fornecedores.php
```
**Descrição:** Suporte para campo `is_fabricante` e novo endpoint `?action=fabricantes`
**Ação:** Substituir arquivo no servidor

#### 5. API - Materiais Nova Estrutura (NOVA FUNCIONALIDADE)
```
📁 api/materiais_nova_estrutura.php
```
**Descrição:** Suporte para campo `id_fabricante` ao criar/atualizar materiais
**Ação:** Substituir arquivo no servidor

#### 6. Model - Fornecedor (NOVA FUNCIONALIDADE)
```
📁 models/Fornecedor.php
```
**Descrição:** Métodos para trabalhar com fabricantes (`findFabricantes()`)
**Ação:** Substituir arquivo no servidor

#### 7. Tela - Adicionar/Editar Fornecedor (NOVA FUNCIONALIDADE)
```
📁 addFornecedor.php
```
**Descrição:** Checkbox "É Fabricante" na tela de fornecedores
**Ação:** Substituir arquivo no servidor

#### 8. Tela - Adicionar/Editar Material (NOVA FUNCIONALIDADE)
```
📁 addMaterial.php
```
**Descrição:** Select de fabricantes na tela de materiais
**Ação:** Substituir arquivo no servidor

---

## 🔧 Instruções de Deploy

### Passo 1: Backup
```bash
# Fazer backup do banco de dados antes de executar os scripts SQL
mysqldump -u [usuario] -p [nome_banco] > backup_antes_deploy_20251103.sql

# Fazer backup dos arquivos PHP que serão substituídos
```

### Passo 2: Executar Scripts SQL
```sql
-- 1. Executar primeiro (Categorias)
SOURCE database/inserir_categorias_materiais.sql;

-- 2. Executar segundo (Fabricantes)
SOURCE database/adicionar_campo_fabricante.sql;

-- 3. Verificar se as alterações foram aplicadas
DESCRIBE tbl_fornecedores;
DESCRIBE tbl_catalogo_materiais;
SELECT COUNT(*) FROM tbl_categorias;
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

**Via Git (se aplicável):**
```bash
git add backend/api/pedidos_compra.php
git add api/fornecedores.php
git add api/materiais_nova_estrutura.php
git add models/Fornecedor.php
git add addFornecedor.php
git add addMaterial.php
git commit -m "feat: Adiciona suporte a fabricantes e corrige bug em pedidos de compra"
git push origin main
```

### Passo 4: Testes em Produção

1. **Testar API de Pedidos de Compra:**
   - Acessar: `backend/api/pedidos_compra.php?action=create`
   - Verificar se não retorna erro "Ação não especificada"

2. **Testar Cadastro de Fornecedor:**
   - Acessar: `addFornecedor.php`
   - Verificar se aparece o checkbox "É Fabricante"
   - Cadastrar um fornecedor marcando como fabricante
   - Verificar se salva corretamente

3. **Testar Cadastro de Material:**
   - Acessar: `addMaterial.php`
   - Verificar se aparece o select "Fabricante"
   - Verificar se lista apenas fornecedores marcados como fabricantes
   - Cadastrar um material associando a um fabricante

4. **Testar Categorias:**
   - Acessar: `material.php` ou `addMaterial.php`
   - Verificar se as 24 categorias aparecem no select de categorias

---

## ✅ Checklist de Deploy

- [ ] Backup do banco de dados realizado
- [ ] Backup dos arquivos PHP realizado
- [ ] Script SQL de categorias executado
- [ ] Script SQL de fabricantes executado
- [ ] Campos verificados no banco (DESCRIBE tables)
- [ ] 24 categorias inseridas (SELECT COUNT)
- [ ] Arquivo `backend/api/pedidos_compra.php` enviado
- [ ] Arquivo `api/fornecedores.php` enviado
- [ ] Arquivo `api/materiais_nova_estrutura.php` enviado
- [ ] Arquivo `models/Fornecedor.php` enviado
- [ ] Arquivo `addFornecedor.php` enviado
- [ ] Arquivo `addMaterial.php` enviado
- [ ] Teste de criação de pedido de compra OK
- [ ] Teste de cadastro de fornecedor como fabricante OK
- [ ] Teste de cadastro de material com fabricante OK
- [ ] Teste de listagem de categorias OK

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

-- 3. Remover categorias (opcional - só se causarem problemas)
DELETE FROM tbl_categorias WHERE nome_categoria IN (
    'ANESTESICOS E AGULHA GENGIVAL', 'ARTICULADOR', 'BIOSSEGURANÇA', 
    'BROCAS', 'CIMENTOS', 'CIRURGIA E PERIODONTIA', 'CLINICO GERAL',
    'DENTÍSTICA E ESTÉTICA', 'DESCARTÁVEIS', 'ENDODONTIA',
    'EQUIPAMENTOS LABORATORIAIS', 'HIGIENE ORAL', 'IMPLANTODONTIA',
    'INSTRUMENTAIS', 'MANDRIL', 'MATERIAS DE LIMPEZA',
    'MOLDAGEM E MODELO', 'ORTODONTIA', 'PLASTIFICADORA',
    'PREVENÇAO E PROFILAXIA', 'PROTESE', 'PROTESE CLINICA',
    'PROTESE LABORATORIAL', 'RADIOLOGIA'
);

-- 4. Restaurar backup
mysql -u [usuario] -p [nome_banco] < backup_antes_deploy_20251103.sql
```

### Reverter Arquivos PHP:
- Restaurar os arquivos do backup anterior

---

## 📞 Suporte

Em caso de dúvidas ou problemas durante o deploy:
1. Verificar logs do servidor: `/var/log/apache2/error.log` ou `/var/log/nginx/error.log`
2. Verificar logs PHP: `php_error.log`
3. Verificar console do navegador (F12) para erros JavaScript

---

## 📝 Notas Importantes

- ⚠️ **IMPORTANTE:** Executar os scripts SQL ANTES de fazer upload dos arquivos PHP
- ⚠️ A alteração no banco é IRREVERSÍVEL sem backup (exceto pelo script de rollback)
- ✅ Todas as alterações são RETROCOMPATÍVEIS - sistema continuará funcionando mesmo sem preencher os novos campos
- ✅ Fornecedores existentes terão `is_fabricante = 0` por padrão
- ✅ Materiais existentes terão `id_fabricante = NULL` por padrão

---

**Desenvolvedor:** Sistema de Estoque Grupo Sorrisos  
**Data de Preparação:** 03/11/2025  
**Versão:** 1.0

