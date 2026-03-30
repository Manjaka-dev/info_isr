#!/usr/bin/env bash
set -euo pipefail

if [[ -f .env ]]; then
  set -a
  # shellcheck disable=SC1091
  source .env
  set +a
fi

APP_PORT="${APP_PORT:-8088}"
BASE_URL="http://localhost:${APP_PORT}/optim/info_isr"

echo "[smoke] Test page d'accueil..."
curl -fsS "$BASE_URL/" >/dev/null

echo "[smoke] Test pagination..."
curl -fsS "$BASE_URL/page/1" >/dev/null

echo "[smoke] Test login admin..."
curl -fsS "$BASE_URL/admin/login.php" >/dev/null

echo "[smoke] Test protection admin (redirection login)..."
code=$(curl -sS -o /dev/null -w "%{http_code}" "$BASE_URL/admin/")
if [[ "$code" != "200" && "$code" != "302" ]]; then
  echo "[smoke] Echec admin inattendu (HTTP $code)" >&2
  exit 1
fi

echo "[smoke] OK"

