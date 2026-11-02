# AgendaAE Monitor

Painel profissional de observabilidade do servidor AgendaAE.

Depois do login único (HTTP Basic), as métricas já aparecem na tela — sem pedir senha de novo.

## O que mostra

- CPU, RAM e disco em tempo real
- Rede
- Containers Docker que mais consomem memória
- PostgreSQL (conexões, tamanho da base, transações)

Também há atalhos para:

- [Uptime Kuma](https://kuma.agendaae.com)
- [Portainer](https://portainer.agendaae.com)

## Stack

- Dashboard estático (`dashboard/`)
- Netdata (métricas)
- Uptime Kuma (uptime)
- Apache + Let's Encrypt (TLS + Basic Auth)

## Deploy

```bash
# no servidor
git clone https://github.com/lucasfulldev/agendaae-monitor.git /root/apps/monitoring
cd /root/apps/monitoring

# ajustar DSN do Postgres em netdata/go.d/postgres.conf
docker compose up -d

cp dashboard/index.html /var/www/html/monitor.agendaae.com/
cp apache/*.conf /etc/apache2/sites-available/
a2ensite monitor.agendaae.com.conf monitor.agendaae.com-le-ssl.conf
systemctl reload apache2
```

Crie o usuário do painel:

```bash
htpasswd -c /etc/apache2/monitor.htpasswd admin
```

DNS: registro `A` `monitor` → IP do servidor.

## Acesso

- URL: https://monitor.agendaae.com
- Usuário/senha: definidos no `htpasswd` do Apache
