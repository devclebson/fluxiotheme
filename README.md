# FluxIO Custom Theme — Documentação Técnica & Operacional

> **Plugin de Customização Visual, Identidade Corporativa & Mural de Avisos para GLPI 12.0.0+**  
> Desenvolvido para o ecossistema do Sistema de Comunicação (SCC) / GLPI.

---

## 1. Visão Geral da Arquitetura

O **FluxIO Custom Theme** adiciona capacidades completas de personalização visual e comunicação corporativa ao GLPI, sem alterar arquivos do núcleo (*core*) do GLPI.

### Principais Recursos
- **Tela de Login com Dois Cartões Lado a Lado (Dual-Card Layout)**:
  - **Cartão 1 (Esquerda)**: Formulário de autenticação seguro com efeito Glassmorphism (efeito vidro fosco) e logotipo corporativo centralizado no topo.
  - **Cartão 2 (Direita)**: Mural interativo para comunicados, tutoriais em vídeo (upload próprio ou YouTube) e botões rápidos de atendimento (WhatsApp, Ramal/Telefone e Portal).
- **Interface Interna Customizável**: Cores personalizadas para o menu lateral (Sidebar) e barra de navegação (Topbar), com suporte a gradientes e temas pré-configurados (como o *Azul SCC*).
- **Gerenciador de Mídias Inteligente**: Pré-visualização instantânea de imagens e vídeos, validação de tamanhos e badges de status em tempo real.

---

## 2. Estrutura de Diretórios do Plugin

```text
/var/www/glpi/plugins/fluxiotheme/
│
├── front/
│   └── config.form.php            # Controlador de formulários e processamento de configurações
│
├── inc/
│   └── config.class.php           # Lógica de negócio, renderização das abas e geração de variáveis CSS
│
├── public/
│   ├── css/
│   │   ├── login.css              # Estilos do layout de login (dual cards, logo centralizada, vidro)
│   │   ├── admin.css              # Estilos do painel de administração do plugin
│   │   └── variables.css          # Variáveis CSS (:root) geradas dinamicamente com as cores e URLs
│   │
│   ├── pics/                      # TEMPLATES E ARQUIVOS PADRÕES ORIGINAIS DO TEMA
│   │   ├── default_logo.png
│   │   ├── default_bg_desktop.jpg
│   │   └── default_favicon.ico
│   │
│   └── uploads/                   # PASTA DEDICADA EXCLUSIVA PARA UPLOADS DO USUÁRIO
│       ├── bg_desktop_custom.jpg  # Papel de parede desktop ativo
│       ├── bg_mobile_custom.jpg   # Papel de parede mobile ativo
│       └── login_video_custom.mp4 # Vídeo institucional/tutorial ativo
│
├── hook.php                       # Hooks de instalação, ativação e injeção de CSS no cabeçalho
├── setup.php                      # Registro do plugin, checagem de versão e menu de configuração
└── README.md                      # Esta documentação
```

---

## 3. Gestão de Pastas de Mídia (`pics/` vs `uploads/`)

Para garantir máxima organização, estabilidade e segurança em futuras atualizações do plugin, a estrutura foi dividida em duas pastas:

1. **`public/pics/` (Padrões de Fábrica)**:
   - Armazena apenas os arquivos originais fornecidos na instalação inicial (ex: `default_logo.png`, `default_bg_desktop.jpg`, `default_favicon.ico`).
   - Esses arquivos nunca são sobrescritos ou apagados por ações do usuário.

2. **`public/uploads/` (Arquivos Enviados pelo Administrador)**:
   - **Local no disco:** `/var/www/glpi/plugins/fluxiotheme/public/uploads/`
   - **Rota na Web:** `http://<seu-ip>/plugins/fluxiotheme/uploads/`
   - **Permissão de Escrita:** Total (`chmod 777` / usuário `ti:www-data`).
   - Todos os novos uploads de vídeos, papéis de parede e logotipos são gravados exclusivamente nesta pasta.
   - **Segurança em Atualizações:** Ao atualizar o plugin via `git pull` ou substituir os arquivos do plugin, sua pasta `public/uploads/` permanece totalmente preservada.

### Nomes Padronizados dos Arquivos na Pasta `uploads/`
Para evitar arquivos órfãos e consumo desnecessário de disco, o plugin utiliza nomes padronizados com o sufixo `_custom`:

| Tipo de Mídia | Arquivo em `public/uploads/` | Formatos Suportados |
| :--- | :--- | :--- |
| **Vídeo do Mural de Login** | `login_video_custom.mp4` | `.mp4`, `.webm`, `.mov` |
| **Papel de Parede Desktop** | `bg_desktop_custom.jpg` | `.jpg`, `.jpeg`, `.png`, `.webp` |
| **Papel de Parede Mobile** | `bg_mobile_custom.jpg` | `.jpg`, `.jpeg`, `.png`, `.webp` |
| **Logotipo do Login** | `logo_login_custom.png` | `.png`, `.svg`, `.webp` |
| **Logotipo da Barra Lateral** | `logo_sidebar_custom.png` | `.png`, `.svg`, `.webp` |
| **Logotipo Lateral Recolhido** | `logo_sidebar_collapsed_custom.png`| `.png`, `.svg`, `.webp` |
| **Favicon do Navegador** | `favicon.ico` | `.ico` |

### Envio Manual de Arquivos (Alternativa ao Navegador)
Se você tiver um arquivo grande (por exemplo, um vídeo institucional de 50 MB) e preferir não enviar pelo formulário web, você pode simplesmente copiar o arquivo diretamente para a pasta `uploads/` via SSH/SFTP:

```bash
# Exemplo de cópia manual de um vídeo diretamente na pasta uploads:
sudo cp /caminho/meu_video.mp4 /var/www/glpi/plugins/fluxiotheme/public/uploads/login_video_custom.mp4

# Ajustar permissões para o servidor web:
sudo chown ti:www-data /var/www/glpi/plugins/fluxiotheme/public/uploads/login_video_custom.mp4
sudo chmod 664 /var/www/glpi/plugins/fluxiotheme/public/uploads/login_video_custom.mp4
```

Em seguida, basta acessar a **Aba 3 (Mural de Anúncios)** e selecionar a opção **"Enviar Arquivo de Vídeo"** para ativar o player nativo.

---

## 4. Requisitos do Servidor para Upload de Mídias (PHP & Apache)

Para suportar o envio e reprodução suave de arquivos de mídia sem timeouts ou erros como `ERR_CONNECTION_ABORTED`, as seguintes configurações estão ativas no servidor Linux:

### A. PHP (`/etc/php/8.3/apache2/php.ini`)
```ini
upload_max_filesize = 100M    ; Limite máximo por arquivo
post_max_size = 110M          ; Deve ser maior que o upload_max_filesize
max_input_time = 300          ; 5 minutos para receber o stream do arquivo
max_execution_time = 300      ; 5 minutos de tempo de execução
memory_limit = 512M           ; Memória alocada para processamento
```

### B. Apache Timeout (`/etc/apache2/mods-enabled/reqtimeout.conf`)
O módulo `mod_reqtimeout` do Apache limita o tempo de espera do envio do corpo da requisição (*body*). Para evitar que conexões mais lentas sejam derrubadas antes do fim do upload:
```apache
RequestReadTimeout body=60-300,minrate=500
```

---

## 5. Permissões de Pastas e Arquivos

O plugin requer permissão de escrita na pasta `public/uploads/` para que o servidor web (PHP/Apache) consiga salvar os arquivos enviados via painel administrativo.

### No Servidor Local (`/var/www/glpi`):
```bash
# Ajusta o proprietário para o usuário local e o grupo do servidor web (www-data)
sudo chown -R ti:www-data /var/www/glpi/plugins/fluxiotheme

# Define permissões gerais para pastas e arquivos
sudo chmod -R 775 /var/www/glpi/plugins/fluxiotheme

# Garante permissão total de escrita para a pasta de uploads
sudo chmod -R 777 /var/www/glpi/plugins/fluxiotheme/public/uploads
sudo chown -R www-data:www-data /var/www/glpi/plugins/fluxiotheme/public/uploads
```

### No Servidor Remoto / Produção (ex: `/opt/glpi`):
```bash
# Permissão total na pasta de uploads para o usuário do servidor web
chmod -R 777 /opt/glpi/plugins/fluxiotheme/public/uploads
chown -R www-data:www-data /opt/glpi/plugins/fluxiotheme/public/uploads

# (Opcional) Garantir permissão também na pasta pics
chmod -R 777 /opt/glpi/plugins/fluxiotheme/public/pics
chown -R www-data:www-data /opt/glpi/plugins/fluxiotheme/public/pics
```

---

## 6. Sistema de Upload AJAX com Barra de Progresso em Tempo Real

Para evitar travamentos de tela ou incerteza durante uploads de arquivos pesados (vídeos de até 100 MB), o formulário de configuração conta com um sistema moderno de envio via **AJAX com XMLHttpRequest**:

- **Barra de Progresso com Métricas:** Exibe em tempo real a porcentagem (0% a 100%) e o volume transferido (ex: `1.1 MB de 1.1 MB (100%)`).
- **Modal de Confirmação:** Mostra animação de sucesso quando o servidor confirma o salvamento em disco.
- **Tratamento de Exceções:** Detecta falhas de rede, limites do PHP excedidos e erros de permissão de pasta com alertas claros.
- **Arquitetura Fallback Inteligente:** O método `getMediaPath()` verifica primeiro se existe o arquivo personalizado em `public/uploads/`; se não houver, utiliza automaticamente o arquivo padrão de fábrica em `public/pics/`.

---

## 7. Sistema de Logs e Diagnóstico em Tempo Real

O plugin possui um canal exclusivo de telemetria e depuração para monitorar eventos de upload e configuração:

- **Arquivo no Disco:** `/var/www/glpi/files/_log/fluxiotheme.log`
- **Visualizador Integrado:** Ao final da **Aba 3 (Mural de Anúncios & Mídia)**, existe um console visual de diagnóstico que lê os últimos 30 eventos gravados no log sem precisar abrir o terminal.
- **Acompanhar via Terminal:**
  ```bash
  tail -f /var/www/glpi/files/_log/fluxiotheme.log
  ```

---

## 8. Comandos Úteis de Manutenção (CLI)

### Limpar o Cache do GLPI
Sempre que editar folhas de estilo CSS ou atualizar configurações críticas:
```bash
sudo -u www-data php /var/www/glpi/bin/console cache:clear
```

### Recarregar o Servidor Apache
Após qualquer alteração em arquivos de configuração do PHP (`php.ini`) ou Apache (`reqtimeout.conf`):
```bash
sudo systemctl reload apache2
```

### Consultar as Configurações do Plugin no Banco de Dados
```bash
sudo mysql helpdesk -e "SELECT name, value FROM glpi_configs WHERE context = 'plugin:fluxiotheme';"
```

---

## 9. Como Acessar a Configuração no GLPI

1. Acesse o GLPI como **Super-Admin**.
2. Navegue até o menu superior: **Configurar** (`Setup`) > **Plugins**.
3. Localize **FluxIO Custom Theme** e clique no ícone de chave de fenda (ou no nome do plugin).
4. Utilize as abas de navegação:
   - **Aba 1 (Logotipos)**: Logotipos do cabeçalho, tela de login e favicon.
   - **Aba 2 (Tela de Login)**: Wallpapers de fundo, nível de desfoque (blur), opacidade do vidro e aviso no cartão.
   - **Aba 3 (Mural de Anúncios & Mídia)**: Ativação do segundo cartão, player de vídeo local/YouTube, comunicados de texto, atalhos rápidos e console de logs.
   - **Aba 4 (Interface Interna)**: Paleta corporativa SCC, barra superior e menu lateral.

---
*Documentação atualizada em: 27 de Outubro de 2026 — FluxIO Custom Theme v1.0.0*

