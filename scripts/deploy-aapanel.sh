#!/bin/bash
# aaPanel — cole INTEIRO em Git Manager → Script (alias: GitGrupoSorrisos)
# O painel inicia em /www/server/panel; o cd na 2ª linha é obrigatório.

set -e

SITE="/www/wwwroot/app.gruposorrisos.com.br"
export GIT_CONFIG_COUNT=1
export GIT_CONFIG_KEY_0="safe.directory"
export GIT_CONFIG_VALUE_0="$SITE"

echo "========== Deploy $(date) =========="
echo "PWD antes do cd: $(pwd)"

cd "$SITE" || { echo "ERRO: nao entrou em $SITE"; exit 1; }

echo "PWD no site: $(pwd)"

cp -f config/config.php /root/config.php.producao.bak 2>/dev/null || true

git pull origin main

cp -f /root/config.php.producao.bak config/config.php 2>/dev/null || true
chown -R www:www "$SITE" 2>/dev/null || true

echo "Versao: $(git log -1 --oneline)"
echo "Application deployed!"
