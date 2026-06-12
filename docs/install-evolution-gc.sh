#!/bin/bash
# ============================================================
# Evolution API — instalação para whatsapp.guiacomercial.app
# Servidor: 177.153.59.197
# Executar como root: bash install-evolution-gc.sh
# ============================================================
set -euo pipefail

DOMAIN="whatsapp.guiacomercial.app"
APP_DIR="/opt/evolution-gc"
API_KEY=$(openssl rand -hex 32)   # token global gerado automaticamente
DB_PASS=$(openssl rand -hex 16)

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo " Evolution API — Guia Comercial"
echo " Domínio : $DOMAIN"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# ── 1. Sistema base ──────────────────────────────────────────
echo "[1/7] Atualizando sistema..."
apt-get update -qq && apt-get upgrade -y -qq
apt-get install -y -qq curl git nginx certbot python3-certbot-nginx ufw

# ── 2. Docker ────────────────────────────────────────────────
echo "[2/7] Instalando Docker..."
if ! command -v docker &>/dev/null; then
  curl -fsSL https://get.docker.com | sh
fi
systemctl enable --now docker

# ── 3. Diretório da aplicação ────────────────────────────────
echo "[3/7] Criando estrutura de arquivos..."
mkdir -p "$APP_DIR"/{data,postgres}
cd "$APP_DIR"

# ── 4. docker-compose.yml ────────────────────────────────────
cat > docker-compose.yml << COMPOSE
services:

  postgres:
    image: postgres:16-alpine
    container_name: evo-gc-postgres
    restart: always
    environment:
      POSTGRES_DB: evolution
      POSTGRES_USER: evolution
      POSTGRES_PASSWORD: ${DB_PASS}
    volumes:
      - ./postgres:/var/lib/postgresql/data
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U evolution"]
      interval: 10s
      timeout: 5s
      retries: 5

  redis:
    image: redis:7-alpine
    container_name: evo-gc-redis
    restart: always
    command: redis-server --save 60 1 --loglevel warning
    volumes:
      - ./data/redis:/data

  evolution:
    image: atendai/evolution-api:v2.2.3
    container_name: evo-gc-api
    restart: always
    ports:
      - "127.0.0.1:8084:8080"
    volumes:
      - ./data/instances:/evolution/instances
    environment:
      # Servidor
      SERVER_URL: https://${DOMAIN}
      # Auth
      AUTHENTICATION_TYPE: apikey
      AUTHENTICATION_API_KEY: ${API_KEY}
      AUTHENTICATION_EXPOSE_IN_FETCH_INSTANCES: "true"
      # Banco de dados
      DATABASE_PROVIDER: postgresql
      DATABASE_CONNECTION_URI: postgresql://evolution:${DB_PASS}@postgres:5432/evolution?schema=public
      DATABASE_SAVE_DATA_INSTANCE: "true"
      DATABASE_SAVE_DATA_NEW_MESSAGE: "true"
      DATABASE_SAVE_MESSAGE_UPDATE: "true"
      DATABASE_SAVE_DATA_CONTACTS: "true"
      DATABASE_SAVE_DATA_CHATS: "true"
      DATABASE_SAVE_DATA_LABELS: "false"
      DATABASE_SAVE_DATA_HISTORIC: "false"
      # Redis
      CACHE_REDIS_ENABLED: "true"
      CACHE_REDIS_URI: redis://redis:6379/1
      CACHE_REDIS_TTL: "604800"
      CACHE_REDIS_SAVE_INSTANCES: "false"
      # WhatsApp
      WA_BUSINESS_TOKEN_WEBHOOK: ""
      WEBSOCKET_ENABLED: "false"
      # QR Code
      QRCODE_LIMIT: "30"
      QRCODE_COLOR: "#6366f1"
      # Log
      LOG_LEVEL: "ERROR"
      LOG_COLOR: "false"
      LOG_BAILEYS: "false"
      # Outros
      DEL_INSTANCE: "false"
      LANGUAGE: "pt-BR"
    depends_on:
      postgres:
        condition: service_healthy
      redis:
        condition: service_started

COMPOSE

# Salvar as credenciais geradas
cat > .credentials << CREDS
# Gerado em: $(date)
DOMAIN=$DOMAIN
EVOLUTION_API_KEY=$API_KEY
DB_PASSWORD=$DB_PASS
CREDS
chmod 600 .credentials

echo ""
echo "╔════════════════════════════════════════╗"
echo "║  SALVE ESTAS CREDENCIAIS AGORA!        ║"
echo "╠════════════════════════════════════════╣"
echo "║  API KEY : $API_KEY"
echo "║  DB PASS : $DB_PASS"
echo "╚════════════════════════════════════════╝"
echo ""

# ── 5. Nginx ─────────────────────────────────────────────────
echo "[4/7] Configurando Nginx..."
cat > /etc/nginx/sites-available/evolution-gc << NGINX
server {
    listen 80;
    server_name $DOMAIN;

    # Permite uploads grandes (mídia WhatsApp)
    client_max_body_size 50M;

    location / {
        proxy_pass         http://127.0.0.1:8084;
        proxy_http_version 1.1;
        proxy_set_header   Upgrade \$http_upgrade;
        proxy_set_header   Connection "upgrade";
        proxy_set_header   Host \$host;
        proxy_set_header   X-Real-IP \$remote_addr;
        proxy_set_header   X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header   X-Forwarded-Proto \$scheme;
        proxy_read_timeout 300s;
        proxy_send_timeout 300s;
    }
}
NGINX

ln -sf /etc/nginx/sites-available/evolution-gc /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx

# ── 6. SSL ───────────────────────────────────────────────────
echo "[5/7] Obtendo certificado SSL..."
certbot --nginx -d "$DOMAIN" --non-interactive --agree-tos -m "suporte@guiacomercial.app" --redirect

# ── 7. Firewall ──────────────────────────────────────────────
echo "[6/7] Configurando firewall..."
ufw --force enable
ufw allow ssh
ufw allow 'Nginx Full'
ufw delete allow 8084 2>/dev/null || true   # porta interna nunca exposta

# ── 8. Subir containers ──────────────────────────────────────
echo "[7/7] Subindo Evolution API..."
cd "$APP_DIR"
docker compose up -d

echo ""
echo "Aguardando a API inicializar (30s)..."
sleep 30

# Teste rápido
HTTP=$(curl -s -o /dev/null -w "%{http_code}" "https://$DOMAIN/")
echo "Health check: HTTP $HTTP"

if [ "$HTTP" = "200" ] || [ "$HTTP" = "401" ]; then
  echo ""
  echo "✅ Instalação concluída com sucesso!"
  echo ""
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
  echo "  URL       : https://$DOMAIN"
  echo "  API KEY   : $API_KEY"
  echo "  Credenciais salvas em: $APP_DIR/.credentials"
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
  echo ""
  echo "Próximo passo — adicionar no Guia Comercial (.env):"
  echo "  EVOLUTION_API_BASE_URL=https://$DOMAIN"
  echo "  EVOLUTION_API_TOKEN=$API_KEY"
  echo "  EVOLUTION_ADMIN_INSTANCE=gc_admin"
else
  echo "⚠️  API retornou HTTP $HTTP — verifique os logs:"
  echo "   docker compose -f $APP_DIR/docker-compose.yml logs evolution"
fi
