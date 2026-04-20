# Checklist de Verificação - Funcionalidades de Contagem de Itens

## ✅ Arquivos que DEVEM estar em produção:

### 1. `api/inventario.php`
- [ ] Verificar se contém o case `'ajustar_lote'` (linha ~358)
- [ ] Verificar se contém o case `'ajustar'` (linha ~333)

### 2. `inventario.php`
- [ ] Verificar se contém o botão `btnAjustarLote` (linha ~291)
- [ ] Verificar se contém o campo `busca-material-contagem` (linha ~302)
- [ ] Verificar se o cabeçalho da tabela tem "Ações" (linha ~319)

### 3. `assets/js/inventario.js`
- [ ] Verificar se contém a função `ajustarLoteDivergentes()` 
- [ ] Verificar se contém a função `ajustarItemInventario()`
- [ ] Verificar se contém a função `buscarTotalDivergentes()`
- [ ] Verificar se contém a função `aplicarBuscaMaterialContagem()`
- [ ] Verificar se contém a função `inicializarTooltips()`

## 🔍 Como verificar no navegador (F12):

1. **Abrir Console (F12 > Console)**
2. **Abrir modal de contagem** - Deve aparecer:
   ```
   ✅ Versão do inventario.js: 2.0 - Ajuste em Lote e Busca implementados
   ```

3. **Verificar se funções existem** - Digite no console:
   ```javascript
   typeof ajustarLoteDivergentes
   typeof ajustarItemInventario
   typeof buscarTotalDivergentes
   ```
   Deve retornar: `"function"` para cada um

4. **Verificar elementos HTML** - Digite no console:
   ```javascript
   document.getElementById('btnAjustarLote')
   document.getElementById('busca-material-contagem')
   ```
   Não deve retornar `null`

## 🚨 Problemas Comuns e Soluções:

### Problema 1: Cache do Navegador
**Sintoma:** Funcionalidades não aparecem, mas arquivos estão corretos no servidor

**Solução:**
1. Pressione `Ctrl + Shift + R` (ou `Cmd + Shift + R` no Mac) para hard refresh
2. Ou limpe o cache: F12 > Application > Clear Storage > Clear site data
3. Ou use modo anônimo/privado para testar

### Problema 2: Arquivos não foram enviados
**Sintoma:** Console mostra erros de função não encontrada

**Solução:**
1. Verificar se os 3 arquivos foram enviados:
   - `api/inventario.php`
   - `inventario.php`
   - `assets/js/inventario.js`
2. Verificar data de modificação dos arquivos no servidor
3. Comparar tamanho dos arquivos (local vs produção)

### Problema 3: Erro de JavaScript
**Sintoma:** Console mostra erros em vermelho

**Solução:**
1. Verificar erros no console (F12 > Console)
2. Verificar se Bootstrap está carregado (necessário para tooltips)
3. Verificar se há conflitos com outros scripts

### Problema 4: Versão antiga do JS em cache
**Sintoma:** Funcionalidades antigas funcionam, novas não

**Solução:**
1. Adicionar versão ao arquivo JS:
   ```html
   <script src="assets/js/inventario.js?v=2.0"></script>
   ```
2. Ou renomear temporariamente: `inventario.js` → `inventario_v2.js`

## 📋 Teste Rápido:

1. Abrir inventário em andamento
2. Clicar em "Contagem" (ícone de clipboard)
3. **Deve aparecer:**
   - Campo "Buscar Material" no topo
   - Botão "Ajustar em Lote" (se houver divergentes)
   - Botão "Ajustar" em cada item divergente
4. Passar mouse sobre badge "Divergente" → deve mostrar tooltip

## 🔧 Comandos de Debug:

No console do navegador, execute:

```javascript
// Verificar se funções existem
console.log('ajustarLoteDivergentes:', typeof ajustarLoteDivergentes);
console.log('ajustarItemInventario:', typeof ajustarItemInventario);
console.log('buscarTotalDivergentes:', typeof buscarTotalDivergentes);

// Verificar se elementos existem
console.log('btnAjustarLote:', document.getElementById('btnAjustarLote'));
console.log('busca-material:', document.getElementById('busca-material-contagem'));

// Verificar versão do arquivo
fetch('assets/js/inventario.js')
  .then(r => r.text())
  .then(t => {
    const hasAjustarLote = t.includes('ajustarLoteDivergentes');
    const hasBusca = t.includes('busca-material-contagem');
    console.log('✅ Funções no arquivo:', { hasAjustarLote, hasBusca });
  });
```
