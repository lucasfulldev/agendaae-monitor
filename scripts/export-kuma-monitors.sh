#!/usr/bin/env bash
set -euo pipefail
OUT="${1:-/var/www/html/monitor.agendaae.com/monitors.json}"
TMP="$(mktemp)"

python3 - <<PY
import json, subprocess
raw = subprocess.check_output([
    "sudo", "docker", "exec", "-w", "/app/data", "uptime-kuma",
    "sqlite3", "-separator", "|", "kuma.db",
    "SELECT m.id, m.name, COALESCE(m.url,''), COALESCE((SELECT h.status FROM heartbeat h WHERE h.monitor_id=m.id ORDER BY h.time DESC LIMIT 1), -1) FROM monitor m ORDER BY m.id;"
], text=True)
monitors = []
for line in raw.splitlines():
    if not line.strip():
        continue
    mid, name, url, status = line.split("|", 3)
    monitors.append({"id": int(mid), "name": name, "url": url, "status": int(status)})
with open("$TMP", "w", encoding="utf-8") as fh:
    json.dump(monitors, fh, ensure_ascii=False)
print(len(monitors), "monitors")
PY

sudo mv "$TMP" "$OUT"
sudo chmod 644 "$OUT"
sudo chown www-data:www-data "$OUT"
