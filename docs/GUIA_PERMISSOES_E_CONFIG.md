# Guia Rápido de Permissões, Configurações e Comandos

Este guia reúne os comandos operacionais essenciais para manter e subir o plugin **FluxIO Custom Theme** em qualquer servidor GLPI 12.

---

## 1. Permissões de Pastas

### Servidor Local (`/var/www/glpi/plugins/fluxiotheme/`):
```bash
sudo chown -R ti:www-data /var/www/glpi/plugins/fluxiotheme
sudo chmod -R 775 /var/www/glpi/plugins/fluxiotheme
sudo chmod -R 777 /var/www/glpi/plugins/fluxiotheme/public/uploads
sudo chown -R www-data:www-data /var/www/glpi/plugins/fluxiotheme/public/uploads
```

### Servidor Remoto / Produção (`/opt/glpi/plugins/fluxiotheme/`):
```bash
chmod -R 777 /opt/glpi/plugins/fluxiotheme/public/uploads
chown -R www-data:www-data /opt/glpi/plugins/fluxiotheme/public/uploads
chmod -R 777 /opt/glpi/plugins/fluxiotheme/public/pics
chown -R www-data:www-data /opt/glpi/plugins/fluxiotheme/public/pics
```

---

## 2. Configurações de Upload no PHP & Apache

### PHP (`/etc/php/8.3/apache2/php.ini`):
```ini
upload_max_filesize = 100M
post_max_size = 110M
max_input_time = 300
max_execution_time = 300
memory_limit = 512M
```

### Apache Timeout (`/etc/apache2/mods-enabled/reqtimeout.conf`):
```apache
RequestReadTimeout body=60-300,minrate=500
```

---

## 3. Comandos Úteis do GLPI

### Limpar Cache:
```bash
sudo -u www-data php /var/www/glpi/bin/console cache:clear
```

### Recarregar Servidor Web:
```bash
sudo systemctl reload apache2
```

### Ver Logs de Upload em Tempo Real:
```bash
tail -f /var/www/glpi/files/_log/fluxiotheme.log
```
