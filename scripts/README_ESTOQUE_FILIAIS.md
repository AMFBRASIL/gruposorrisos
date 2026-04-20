# 📚 **DOCUMENTAÇÃO - SISTEMA DE ESTOQUE POR FILIAL**

## 🎯 **OBJETIVO**

Este conjunto de scripts resolve o problema de **materiais cadastrados sem estoque em todas as filiais/clínicas**.

## 🏗️ **ARQUITETURA DO SISTEMA**

### **Estrutura Antiga (Depreciada):**
```
tbl_materiais
├── id_material
├── codigo
├── nome
├── id_filial (vinculado diretamente)
├── estoque_atual
└── preco_unitario
```

### **Nova Estrutura (Atual):**
```
tbl_catalogo_materiais (MATERIAL CENTRALIZADO)
├── id_catalogo
├── codigo
├── nome
├── id_fornecedor
├── preco_unitario_padrao
└── estoque_minimo_padrao

tbl_estoque_filiais (ESTOQUE POR FILIAL)
├── id_estoque
├── id_catalogo (FK para tbl_catalogo_materiais)
├── id_filial (FK para tbl_filiais)
├── estoque_atual
├── estoque_minimo
└── preco_unitario
```

## 🚀 **SCRIPTS DISPONÍVEIS**

### **1. `inicializar_estoque_filiais.php`**
**Função:** Cria estoque zerado para todos os materiais em todas as filiais.

**Como usar:**
```bash
cd scripts
php inicializar_estoque_filiais.php
```

**O que faz:**
- ✅ Verifica filiais ativas
- ✅ Verifica materiais do catálogo
- ✅ Cria estoque zerado para cada material em cada filial
- ✅ Evita duplicatas
- ✅ Relatório detalhado do processo

### **2. `verificar_estoque_filiais.php`**
**Função:** Analisa o status dos estoques em todas as filiais.

**Como usar:**
```bash
cd scripts
php verificar_estoque_filiais.php
```

**O que faz:**
- 🔍 Verifica cobertura de estoque por filial
- 📊 Calcula percentual de cobertura
- 📝 Lista materiais sem estoque
- 💡 Fornece recomendações

## 🎨 **MODIFICAÇÕES IMPLEMENTADAS**

### **1. Tela `addMaterial.php`**
- ✅ **Novos materiais:** Criam estoque automaticamente em TODAS as filiais
- ✅ **Edição:** Mantém estoque da filial atual
- ✅ **Flag especial:** `criar_em_todas_filiais: true`

### **2. API `materiais_nova_estrutura.php`**
- ✅ **Processa flag:** `criar_em_todas_filiais`
- ✅ **Cria estoque:** Em todas as filiais ativas
- ✅ **Tratamento de erros:** Logs detalhados

## 📋 **FLUXO DE FUNCIONAMENTO**

### **Cenário 1: Material Novo**
```
1. Usuário cadastra material
2. Sistema cria material no catálogo
3. Sistema cria estoque zerado em TODAS as filiais
4. Material fica disponível para todas as clínicas
```

### **Cenário 2: Material Existente**
```
1. Usuário edita material
2. Sistema atualiza catálogo
3. Sistema mantém estoque da filial atual
4. Outras filiais não são afetadas
```

## 🔧 **COMO RESOLVER PROBLEMAS EXISTENTES**

### **Passo 1: Verificar Situação Atual**
```bash
php verificar_estoque_filiais.php
```

### **Passo 2: Inicializar Estoques Faltantes**
```bash
php inicializar_estoque_filiais.php
```

### **Passo 3: Verificar Resultado**
```bash
php verificar_estoque_filiais.php
```

## ⚠️ **IMPORTANTE**

### **Antes de Executar:**
- ✅ Backup do banco de dados
- ✅ Verificar permissões de escrita
- ✅ Executar em horário de baixo movimento

### **Durante a Execução:**
- 🔄 Não interromper o processo
- 📊 Acompanhar os logs
- ⏱️ Processo pode demorar dependendo da quantidade de dados

### **Após a Execução:**
- ✅ Verificar resultado final
- 🔍 Testar funcionalidades do sistema
- 📝 Documentar alterações realizadas

## 🎯 **RESULTADO ESPERADO**

Após executar os scripts:

1. **100% de cobertura:** Todos os materiais terão estoque em todas as filiais
2. **Sistema funcional:** Movimentações, pedidos e relatórios funcionando
3. **Dados consistentes:** Estoque zerado mas configurado corretamente
4. **Facilidade de uso:** Novos materiais criam estoque automaticamente

## 🆘 **SUPORTE**

### **Problemas Comuns:**
- **Erro de permissão:** Verificar acesso ao banco
- **Timeout:** Aumentar limite de execução do PHP
- **Memória insuficiente:** Aumentar memory_limit no php.ini

### **Logs:**
- Verificar logs do PHP
- Verificar logs do banco de dados
- Console de saída dos scripts

---

**🎉 Sistema funcionando perfeitamente com estoque em todas as filiais!** 