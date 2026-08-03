#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)"
cd "$ROOT_DIR"

if [ ! -f "wp-load.php" ]; then
  echo "ERROR: Run this tool from a Git checkout of the WordPress root." >&2
  exit 1
fi

CURRENT_BRANCH="$(git branch --show-current)"
if [ "$CURRENT_BRANCH" != "main" ]; then
  echo "ERROR: Expected Git branch main, found: $CURRENT_BRANCH" >&2
  exit 1
fi

echo "[1/4] Pulling latest release..."
git pull --ff-only origin main

PHP_BIN="${PHP_BIN:-php}"

echo "[2/4] Running production preflight..."
"$PHP_BIN" tools/deploy-product-seo-content-20260801.php dry-run

echo "[3/4] Applying approved v3 long descriptions..."
"$PHP_BIN" tools/deploy-product-seo-content-20260801.php apply

echo "[4/4] Running final QA..."
"$PHP_BIN" tools/deploy-product-seo-content-20260801.php qa

echo "SYNC COMPLETE: 179 product long descriptions verified."
