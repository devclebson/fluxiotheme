# Resumo Executivo & Técnico — FluxIO Custom Theme (GLPI 12)
**Data:** 27 de Outubro de 2026  
**Projeto:** Plugin de Identidade Visual e Mural de Avisos para GLPI 12.0.0+  
**Cliente/Ambiente:** Grupo SCC | Sistema de Comunicação  

---

## 📌 Contexto Rápido para a Próxima Conversa
> **Como usar:** Copie o texto da seção **"Prompt para Iniciar a Nova Conversa em Casa"** no final deste documento e cole diretamente no início da nova sessão no seu computador de casa.

---

## 1. Ambientes e Diretórios
- **Servidor Local (Dev):**
  - Caminho: `/var/www/glpi/plugins/fluxiotheme/`
  - URL: `http://10.14.0.73/index.php?noAUTO=1`
- **Servidor Remoto (Produção/Homologação com Domínio):**
  - Caminho: `/opt/glpi/plugins/fluxiotheme/`
  - URL: `https://glpi12.clebsoncastilho.dev.br/index.php?noAUTO=1`
- **Permissões Essenciais na pasta de uploads:**
  ```bash
  chmod -R 777 public/uploads
  chown -R www-data:www-data public/uploads
  ```

---

## 2. O Que Foi Desenvolvido e Concluído com Sucesso

### A. Layout da Tela de Login (Dual-Card Glassmorphism)
- **Dois cartões independentes lado a lado:**
  - **Cartão Esquerdo:** Formulário de autenticação seguro. A **logo do Grupo SCC** foi redimensionada e fica **sempre perfeitamente centralizada acima do card de login**.
  - **Cartão Direito:** Painel "Central de Ajuda & Comunicados", contendo vídeo institucional, textos informativos, badges de contato (Ramais Lages e Florianópolis) e botões rápidos ("Portal SCC" e "FAQ").
- **Estilos CSS:** Implementado em `public/css/login.css` com variáveis dinâmicas em `public/css/variables.css`.

### B. Gestão de Mídias e Upload AJAX
- **Separação de Pastas:**
  - `public/pics/`: Armazena templates padrão de fábrica (nunca sobrescritos).
  - `public/uploads/`: Pasta dedicada exclusiva para arquivos enviados pelo usuário (`chmod 777`).
  - **Fallback Automático:** Métodos `getMediaPath()` e `getMediaUrl()` em `inc/config.class.php` buscam em `uploads/` primeiro e recorrem a `pics/` caso não exista arquivo personalizado.
- **Barra de Progresso em Tempo Real:**
  - Upload via `XMLHttpRequest` com monitoramento real de bytes e porcentagem (0 a 100%).
  - Modal dinâmico com overlay, feedback visual de envio e redirecionamento limpo via JSON.
- **Player de Vídeo Flexível:**
  - Suporta vídeo incorporado externo (YouTube/Vimeo) ou vídeo local hospedado em disco (`login_video_custom.mp4`).

### C. Logs e Diagnóstico
- Logs gravados em `/var/www/glpi/files/_log/fluxiotheme.log`.
- Console visual de diagnóstico integrado na **Aba 3 (Mural de Anúncios)** exibindo os últimos 30 eventos sem necessidade de acessar o terminal.

### D. Documentação Atualizada
- Arquivo `README.md` totalmente estruturado com arquitetura, tabelas de arquivos, comandos CLI de manutenção e configurações de timeout do PHP/Apache.

---

## 3. Estrutura Atual dos Arquivos Modificados
- `inc/config.class.php`: Lógica de abas, renderização do modal AJAX, fallback de mídias e logs.
- `front/config.form.php`: Processamento de upload e retorno JSON para requisições AJAX.
- `public/css/login.css`: Design dos dual-cards, logo centralizada e glassmorphism.
- `public/css/variables.css`: Variáveis CSS do tema.
- `public/uploads/`: Armazenamento de `login_video_custom.mp4`, papéis de parede e logotipos.
- `README.md`: Documentação técnica completa.

---

## 4. Prompt para Iniciar a Nova Conversa em Casa (Copie e Cole)

```markdown
Olá! Estou continuando o desenvolvimento do plugin "FluxIO Custom Theme" para GLPI 12.0.0+ do Grupo SCC.
Já temos implementado e testado com 100% de sucesso:
1. Tela de login moderna com dois cartões lado a lado (Login na esquerda com logo SCC centralizada no topo, e Mural de Avisos com vídeo na direita).
2. Player de vídeo funcional tanto para links do YouTube quanto para upload de arquivos MP4 locais.
3. Pasta dedicada exclusiva 'public/uploads/' separada da 'public/pics/' com sistema de fallback.
4. Upload assíncrono via AJAX com barra de progresso em tempo real e console de diagnóstico de logs.
5. Permissões de escrita validadas e testadas com 'chmod -R 777 public/uploads' e 'chown -R www-data:www-data'.
6. Toda a documentação técnica foi atualizada no README.md do plugin.

Por favor, leia os arquivos do plugin (especialmente README.md, inc/config.class.php e public/css/login.css) e me avise quando estiver pronto para continuarmos com os próximos passos.
```
