<?php
/**
 * ---------------------------------------------------------------------
 * FluxIO Custom Theme - GLPI Plugin
 * Configuration and Admin UI Class
 * ---------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
    die("Acesso direto não permitido.");
}

class PluginFluxiothemeConfig extends CommonGLPI {

    public static string $rightname = 'config';

    public static function getTypeName($nb = 0) {
        return 'FluxIO Custom Theme';
    }

    public static function getIcon() {
        return 'ti ti-palette';
    }

    public function defineTabs($options = []) {
        $ong = [];
        $this->addStandardTab(__CLASS__, $ong, $options);
        return $ong;
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
        if ($item->getType() === __CLASS__ || $item->getType() === 'Config') {
            return [
                1 => 'Logotipos & Favicon',
                2 => 'Tela de Login (Visual & Vidro)',
                3 => 'Mural de Anúncios & Mídia',
                4 => 'Interface Interna (Sidebar & Topbar)'
            ];
        }
        return '';
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
        $self = ($item instanceof self) ? $item : new self();
        switch ($tabnum) {
            case 1:
                $self->showLogosTab();
                break;
            case 2:
                $self->showLoginTab();
                break;
            case 3:
                $self->showAnnouncementsTab();
                break;
            case 4:
                $self->showInterfaceTab();
                break;
        }
        return true;
    }

    /**
     * Retorna array com as configurações ativas mescladas com os padrões
     */
    public static function getConfig(): array {
        $defaults = [
            // Logos & Imagens
            'logo_login'              => 'default_logo.png',
            'logo_sidebar'            => 'default_logo.png',
            'logo_sidebar_collapsed'  => 'default_logo_collapsed.png',
            'bg_desktop'              => 'default_bg_desktop.jpg',
            'bg_mobile'               => 'background_helpdesk_mobile.png',

            // Tela de Login - Estilos
            'login_overlay_start'     => 'rgba(65, 117, 238, 0.78)',
            'login_overlay_end'       => 'rgba(15, 23, 42, 0.78)',
            'login_btn_start'         => '#2563eb',
            'login_btn_end'           => '#0ea5e9',
            'card_bg'                 => 'rgba(17, 25, 40, 0.72)',
            'card_border'             => 'rgba(255, 255, 255, 0.12)',
            'card_blur'               => '18',
            'card_radius'             => '28',
            'enable_saml_style'       => '1',

            // Alerta Rápido no Cartão
            'login_notice_enabled'    => '0',
            'login_notice_type'       => 'info',
            'login_notice_text'       => '',

            // Painel Lateral de Anúncios & Mídia
            'side_panel_enabled'      => '0',
            'side_panel_title'        => 'Central de Ajuda & Comunicados',
            'side_panel_subtitle'     => 'Avisos da TI e Tutoriais Rápidos',
            'side_panel_media_type'   => 'none', // 'none', 'upload', 'youtube'
            'side_panel_video_file'   => '',
            'side_panel_video_url'    => '',
            'side_panel_content'      => '',
            'side_panel_whatsapp'     => '',
            'side_panel_phone'        => '',
            'side_panel_portal_label' => 'Portal SCC',
            'side_panel_portal_url'   => '',

            // Interface Interna - Sidebar
            'custom_sidebar_enabled'  => '1',
            'sidebar_bg_start'        => '#163961',
            'sidebar_bg_end'          => '#0f172a',
            'sidebar_active_color'    => '#2563eb',

            // Interface Interna - Topbar
            'custom_topbar_enabled'   => '1',
            'topbar_bg_start'         => '#163961',
            'topbar_bg_end'           => '#0f172a',

            // Cores Globais
            'primary_color'           => '#2563eb'
        ];

        $current = Config::getConfigurationValues('plugin:fluxiotheme');
        return array_merge($defaults, $current);
    }

    /**
     * Gera o arquivo public/css/variables.css dinamicamente
     */
    /**
     * Grava log dedicado do plugin em files/_log/fluxiotheme.log
     */
    public static function log(string $message): void {
        $log_file = GLPI_ROOT . '/files/_log/fluxiotheme.log';
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
        $entry = "[{$timestamp}] [{$ip}] {$message}" . PHP_EOL;
        @file_put_contents($log_file, $entry, FILE_APPEND | LOCK_EX);
        error_log("[FluxIO] {$message}");
    }

    /**
     * Retorna as últimas linhas do arquivo de log
     */
    public static function getLastLogs(int $lines = 25): string {
        $log_file = GLPI_ROOT . '/files/_log/fluxiotheme.log';
        if (!file_exists($log_file)) {
            return "Nenhum evento registrado no arquivo de log ainda.";
        }
        $content = file($log_file);
        if ($content === false || empty($content)) {
            return "Arquivo de log vazio.";
        }
        $slice = array_slice($content, -$lines);
        return htmlspecialchars(implode("", $slice), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Retorna o caminho absoluto em disco de uma mídia (prioriza uploads/, fallback pics/)
     */
    public static function getMediaPath(string $filename): string {
        $upload_path = GLPI_ROOT . '/plugins/fluxiotheme/public/uploads/' . $filename;
        if (file_exists($upload_path)) {
            return $upload_path;
        }
        return GLPI_ROOT . '/plugins/fluxiotheme/public/pics/' . $filename;
    }

    /**
     * Retorna a URL pública web de uma mídia (prioriza uploads/, fallback pics/)
     */
    public static function getMediaUrl(string $filename): string {
        $upload_path = GLPI_ROOT . '/plugins/fluxiotheme/public/uploads/' . $filename;
        if (file_exists($upload_path)) {
            return '/plugins/fluxiotheme/uploads/' . $filename;
        }
        return '/plugins/fluxiotheme/pics/' . $filename;
    }

    /**
     * Gera o arquivo public/css/variables.css dinamicamente
     */
    public static function generateCssVariables(): bool {
        $cfg = self::getConfig();

        $bg_desktop_url  = self::getMediaUrl($cfg['bg_desktop']);
        $bg_mobile_url   = self::getMediaUrl($cfg['bg_mobile']);
        $logo_login_url  = self::getMediaUrl($cfg['logo_login']);
        $logo_side_url   = self::getMediaUrl($cfg['logo_sidebar']);
        $logo_side_c_url = self::getMediaUrl($cfg['logo_sidebar_collapsed']);

        $css = "/* FluxIO Custom Theme - Generated Variables */\n";
        $css .= ":root {\n";
        $css .= "    --login-bg-desktop: url('{$bg_desktop_url}');\n";
        $css .= "    --login-bg-mobile: url('{$bg_mobile_url}');\n";
        $css .= "    --logo-login: url('{$logo_login_url}');\n";
        $css .= "    --logo-sidebar: url('{$logo_side_url}');\n";
        $css .= "    --logo-sidebar-collapsed: url('{$logo_side_c_url}');\n";

        // Cores de Login
        $css .= "    --login-overlay-start: {$cfg['login_overlay_start']};\n";
        $css .= "    --login-overlay-end: {$cfg['login_overlay_end']};\n";
        $css .= "    --login-btn-start: {$cfg['login_btn_start']};\n";
        $css .= "    --login-btn-end: {$cfg['login_btn_end']};\n";
        $css .= "    --card-bg: {$cfg['card_bg']};\n";
        $css .= "    --card-border: {$cfg['card_border']};\n";
        $css .= "    --card-blur: {$cfg['card_blur']}px;\n";
        $css .= "    --card-radius: {$cfg['card_radius']}px;\n";

        // Cores da Sidebar
        $css .= "    --sidebar-bg-start: {$cfg['sidebar_bg_start']};\n";
        $css .= "    --sidebar-bg-end: {$cfg['sidebar_bg_end']};\n";
        $css .= "    --sidebar-active-color: {$cfg['sidebar_active_color']};\n";

        // Cores da Topbar
        $css .= "    --topbar-bg-start: {$cfg['topbar_bg_start']};\n";
        $css .= "    --topbar-bg-end: {$cfg['topbar_bg_end']};\n";

        // Geral
        $css .= "    --primary-color: {$cfg['primary_color']};\n";
        $css .= "}\n";

        // Regras condicionais para Sidebar e Topbar
        if ($cfg['custom_sidebar_enabled'] == '1') {
            $css .= "\n/* Sidebar Customizada Ativada */\n";
            $css .= ".sidebar, aside.navbar {\n";
            $css .= "    background: linear-gradient(200deg, var(--sidebar-bg-start), var(--sidebar-bg-end)) !important;\n";
            $css .= "}\n";
            $css .= ".sidebar .nav-link.active, aside.navbar .nav-link.active {\n";
            $css .= "    border-left: 3px solid var(--sidebar-active-color) !important;\n";
            $css .= "    background: rgba(255, 255, 255, 0.08) !important;\n";
            $css .= "}\n";
        }

        if ($cfg['custom_topbar_enabled'] == '1') {
            $css .= "\n/* Topbar Customizada Ativada */\n";
            $css .= ".topbar, header.navbar {\n";
            $css .= "    background: linear-gradient(90deg, var(--topbar-bg-start), var(--topbar-bg-end)) !important;\n";
            $css .= "}\n";
        }

        $css_file = GLPI_ROOT . '/plugins/fluxiotheme/public/css/variables.css';
        return (file_put_contents($css_file, $css) !== false);
    }

    /**
     * Formata bytes em formato legível (KB, MB, etc.)
     */
    public static function formatBytes(int $bytes): string {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }

    /**
     * Processa upload de imagem ou vídeo para a pasta dedicada public/uploads/
     */
    public static function handleUpload(string $field_name, string $file_prefix, array $allowed_extensions = ['png', 'jpg', 'jpeg', 'webp', 'svg', 'ico']): ?string {
        if (!isset($_FILES[$field_name])) {
            self::log("handleUpload: \$_FILES['{$field_name}'] não foi enviado na requisição.");
            return null;
        }

        $file = $_FILES[$field_name];
        self::log("handleUpload '{$field_name}': nome='{$file['name']}', tamanho={$file['size']} bytes, erro={$file['error']}, tmp='{$file['tmp_name']}'");

        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            self::log("handleUpload '{$field_name}': nenhum arquivo anexado (UPLOAD_ERR_NO_FILE).");
            return null;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $err_map = [
                UPLOAD_ERR_INI_SIZE   => "O arquivo enviado excede o limite máximo permitido pelo PHP (upload_max_filesize).",
                UPLOAD_ERR_FORM_SIZE  => "O arquivo excede o limite permitido pelo formulário HTML.",
                UPLOAD_ERR_PARTIAL    => "O upload foi apenas parcialmente concluído. A conexão foi interrompida.",
                UPLOAD_ERR_NO_TMP_DIR => "Pasta temporária de upload ausente no servidor Linux.",
                UPLOAD_ERR_CANT_WRITE => "Falha ao gravar arquivo no disco temporário do servidor.",
                UPLOAD_ERR_EXTENSION  => "Uma extensão PHP bloqueou o upload do arquivo."
            ];
            $msg = $err_map[$file['error']] ?? "Erro durante o upload (Código PHP: {$file['error']}).";
            self::log("handleUpload '{$field_name}' FALHA: {$msg}");
            Session::addMessageAfterRedirect($msg, false, ERROR);
            return null;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed_extensions, true)) {
            $allowed_str = strtoupper(implode(', ', $allowed_extensions));
            self::log("handleUpload '{$field_name}' extensão rejeitada: .{$ext}. Permitidas: {$allowed_str}");
            Session::addMessageAfterRedirect("Extensão inválida (.{$ext}). Use apenas: {$allowed_str}.", false, ERROR);
            return null;
        }

        $dest_filename = "{$file_prefix}_custom.{$ext}";
        $dest_path = GLPI_ROOT . "/plugins/fluxiotheme/public/uploads/{$dest_filename}";

        if (move_uploaded_file($file['tmp_name'], $dest_path)) {
            chmod($dest_path, 0664);
            self::log("handleUpload '{$field_name}' SUCESSO! Gravado em: {$dest_path}");
            return $dest_filename;
        }

        self::log("handleUpload '{$field_name}' ERRO CRÍTICO no move_uploaded_file para {$dest_path}. Verifique permissões.");
        Session::addMessageAfterRedirect("Falha ao salvar o arquivo em disco. Verifique as permissões da pasta uploads.", false, ERROR);
        return null;
    }

    /**
     * Aba 1: Logotipos & Favicon
     */
    public function showLogosTab() {
        global $CFG_GLPI;
        $cfg = self::getConfig();
        $pics_url = '/plugins/fluxiotheme/pics/';
        $form_action = ($CFG_GLPI['root_doc'] ?? '') . '/plugins/fluxiotheme/front/config.form.php';

        echo "<div class='container my-3'>";
        echo "<form method='post' action='{$form_action}' enctype='multipart/form-data'>";
        echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);
        echo "<input type='hidden' name='action' value='save_logos' />";

        echo "<div class='card mb-4'>";
        echo "<div class='card-header'><h3 class='card-title'><i class='ti ti-photo me-2'></i>Gerenciamento de Logotipos</h3></div>";
        echo "<div class='card-body'>";

        echo "<div class='row row-cards'>";

        // 1. Logo Login
        $this->renderUploadCard(
            'Logo da Tela de Login',
            'Exibida centralizada acima do cartão de login.',
            'logo_login',
            $pics_url . $cfg['logo_login'],
            'Dimensões recomendadas: 240x120px (Fundo transparente PNG/SVG)'
        );

        // 2. Logo Sidebar Expandida
        $this->renderUploadCard(
            'Logo da Barra Lateral (Expandida)',
            'Exibida no topo do menu lateral do GLPI.',
            'logo_sidebar',
            $pics_url . $cfg['logo_sidebar'],
            'Dimensões recomendadas: 190x80px (Fundo transparente)'
        );

        // 3. Logo Sidebar Recolhida
        $this->renderUploadCard(
            'Logo da Barra Lateral (Recolhida)',
            'Exibida quando o menu lateral está minimizado.',
            'logo_sidebar_collapsed',
            $pics_url . $cfg['logo_sidebar_collapsed'],
            'Dimensões recomendadas: 70x70px (Ícone quadrado)'
        );

        // 4. Favicon
        $favicon_url = file_exists(GLPI_ROOT . '/plugins/fluxiotheme/public/pics/favicon.ico')
            ? $pics_url . 'favicon.ico'
            : $pics_url . 'default_favicon.ico';

        $this->renderUploadCard(
            'Favicon do Navegador',
            'Ícone exibido na aba do navegador.',
            'favicon',
            $favicon_url,
            'Formato .ico ou .png quadrado (32x32px)'
        );

        echo "</div>"; // row-cards
        echo "</div>"; // card-body

        echo "<div class='card-footer text-end'>";
        echo "<button type='submit' class='btn btn-primary'><i class='ti ti-device-floppy me-1'></i>Salvar Logotipos</button>";
        echo "</div>";
        echo "</div>"; // card

        echo "</form>";
        self::renderUploadOverlayAndScripts();
        echo "</div>"; // container
    }

    /**
     * Aba 2: Tela de Login (Visual & Vidro)
     */
    public function showLoginTab() {
        global $CFG_GLPI;
        $cfg = self::getConfig();
        $pics_url = '/plugins/fluxiotheme/pics/';
        $form_action = ($CFG_GLPI['root_doc'] ?? '') . '/plugins/fluxiotheme/front/config.form.php';

        echo "<div class='container my-3'>";
        echo "<form method='post' action='{$form_action}' enctype='multipart/form-data'>";
        echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);
        echo "<input type='hidden' name='action' value='save_login' />";

        // Seção 1: Papel de Parede (Wallpapers)
        echo "<div class='card mb-4'>";
        echo "<div class='card-header'><h3 class='card-title'><i class='ti ti-wallpaper me-2'></i>1. Papel de Parede da Tela de Login</h3></div>";
        echo "<div class='card-body'>";
        echo "<div class='row row-cards'>";

        $this->renderUploadCard(
            'Fundo Desktop (Monitores)',
            'Imagem de alta resolução exibida em telas normais.',
            'bg_desktop',
            $pics_url . $cfg['bg_desktop'],
            'Resolução recomendada: 1920x1080px (JPG/PNG)'
        );

        $this->renderUploadCard(
            'Fundo Mobile (Smartphones)',
            'Imagem vertical otimizada para celulares.',
            'bg_mobile',
            $pics_url . $cfg['bg_mobile'],
            'Resolução recomendada: 1080x1920px (JPG/PNG)'
        );

        echo "</div>"; // row
        echo "</div>"; // card-body
        echo "</div>"; // card

        // Seção 2: Sobreposição e Efeito de Vidro (Glassmorphism)
        echo "<div class='card mb-4'>";
        echo "<div class='card-header'><h3 class='card-title'><i class='ti ti-wand me-2'></i>2. Gradiente de Sobreposição e Cartão de Login</h3></div>";
        echo "<div class='card-body'>";

        echo "<div class='row'>";
        echo "<div class='col-md-6 mb-3'>";
        echo "<label class='form-label'>Sobreposição de Cor no Fundo (Início - RGBA):</label>";
        echo "<input type='text' class='form-control' name='login_overlay_start' value='{$cfg['login_overlay_start']}' />";
        echo "<span class='form-hint'>Ex: rgba(65, 117, 238, 0.78) (Azul translúcido)</span>";
        echo "</div>";

        echo "<div class='col-md-6 mb-3'>";
        echo "<label class='form-label'>Sobreposição de Cor no Fundo (Fim - RGBA):</label>";
        echo "<input type='text' class='form-control' name='login_overlay_end' value='{$cfg['login_overlay_end']}' />";
        echo "<span class='form-hint'>Ex: rgba(15, 23, 42, 0.78) (Escuro translúcido)</span>";
        echo "</div>";
        echo "</div>"; // row

        echo "<div class='row mt-2'>";
        echo "<div class='col-md-4 mb-3'>";
        echo "<label class='form-label'>Intensidade do Desfoque (Blur): <strong id='blur-val'>{$cfg['card_blur']}px</strong></label>";
        echo "<input type='range' class='form-range' min='0' max='40' step='1' name='card_blur' value='{$cfg['card_blur']}' oninput=\"document.getElementById('blur-val').innerText = this.value + 'px'\" />";
        echo "<span class='form-hint'>Desfoque de fundo do cartão de login. Padrão: 18px.</span>";
        echo "</div>";

        echo "<div class='col-md-4 mb-3'>";
        echo "<label class='form-label'>Arredondamento dos Cantos: <strong id='radius-val'>{$cfg['card_radius']}px</strong></label>";
        echo "<input type='range' class='form-range' min='8' max='45' step='1' name='card_radius' value='{$cfg['card_radius']}' oninput=\"document.getElementById('radius-val').innerText = this.value + 'px'\" />";
        echo "<span class='form-hint'>Raio da borda do cartão. Padrão: 28px.</span>";
        echo "</div>";

        echo "<div class='col-md-4 mb-3'>";
        echo "<label class='form-label'>Cor de Fundo do Card (RGBA):</label>";
        echo "<input type='text' class='form-control' name='card_bg' value='{$cfg['card_bg']}' />";
        echo "<span class='form-hint'>Ex: rgba(17, 25, 40, 0.72)</span>";
        echo "</div>";
        echo "</div>"; // row

        echo "</div>"; // card-body
        echo "</div>"; // card

        // Seção 3: Botões e Integrações da Tela de Login
        echo "<div class='card mb-4'>";
        echo "<div class='card-header'><h3 class='card-title'><i class='ti ti-click me-2'></i>3. Botão Entrar e Google SAML</h3></div>";
        echo "<div class='card-body'>";

        echo "<div class='row'>";
        echo "<div class='col-md-6 mb-3'>";
        $this->renderColorInput('Botão Entrar (Cor Inicial do Gradiente)', 'login_btn_start', $cfg['login_btn_start'], 'Início do gradiente do botão principal');
        echo "</div>";

        echo "<div class='col-md-6 mb-3'>";
        $this->renderColorInput('Botão Entrar (Cor Final do Gradiente)', 'login_btn_end', $cfg['login_btn_end'], 'Fim do gradiente do botão principal');
        echo "</div>";
        echo "</div>";

        echo "<div class='form-check form-switch mt-2'>";
        $saml_checked = ($cfg['enable_saml_style'] == '1') ? 'checked' : '';
        echo "<input class='form-check-input' type='checkbox' name='enable_saml_style' value='1' id='saml_switch' {$saml_checked} />";
        echo "<label class='form-check-label' for='saml_switch'><strong>Aplicar Estilo Integrado ao Botão Google SAML</strong></label>";
        echo "<div class='form-hint'>Formata o botão do SAML com altura, cantos e alinhamento do ícone idênticos ao padrão do tema.</div>";
        echo "</div>";

        echo "</div>"; // card-body
        echo "</div>"; // card

        // Seção 4: Banner Compacto no Formulário
        echo "<div class='card mb-4'>";
        echo "<div class='card-header'><h3 class='card-title'><i class='ti ti-alert-triangle me-2'></i>4. Alerta Rápido Dentro do Formulário</h3></div>";
        echo "<div class='card-body'>";

        echo "<div class='form-check form-switch mb-3'>";
        $notice_checked = ($cfg['login_notice_enabled'] == '1') ? 'checked' : '';
        echo "<input class='form-check-input' type='checkbox' name='login_notice_enabled' value='1' id='notice_switch' {$notice_checked} onchange=\"document.getElementById('notice-box').style.display = this.checked ? 'block' : 'none'\" />";
        echo "<label class='form-check-label' for='notice_switch'><strong>Exibir Caixa de Alerta Pequena Dentro do Formulário</strong></label>";
        echo "<div class='form-hint'>Ideal para avisos curtos direto abaixo do botão Entrar. (Para avisos ricos com vídeos e links, use a aba 'Mural de Anúncios').</div>";
        echo "</div>";

        $box_style = ($cfg['login_notice_enabled'] == '1') ? 'display: block;' : 'display: none;';
        echo "<div id='notice-box' style='{$box_style}'>";
        echo "<div class='row'>";
        echo "<div class='col-md-4 mb-3'>";
        echo "<label class='form-label'>Tipo de Alerta:</label>";
        echo "<select class='form-select' name='login_notice_type'>";
        $types = [
            'info'    => 'Informativo (Azul)',
            'warning' => 'Alerta / Manutenção (Amarelo)',
            'success' => 'Sucesso / Novidade (Verde)',
            'danger'  => 'Urgência / Crítico (Vermelho)'
        ];
        foreach ($types as $k => $label) {
            $sel = ($cfg['login_notice_type'] === $k) ? 'selected' : '';
            echo "<option value='{$k}' {$sel}>{$label}</option>";
        }
        echo "</select>";
        echo "</div>";

        echo "<div class='col-md-8 mb-3'>";
        echo "<label class='form-label'>Texto do Alerta Curto:</label>";
        echo "<textarea class='form-control' name='login_notice_text' rows='2' placeholder='Ex: Em caso de dificuldades no login, acione o Suporte TI pelo ramal 1234.'>{$cfg['login_notice_text']}</textarea>";
        echo "</div>";
        echo "</div>";
        echo "</div>";

        echo "</div>"; // card-body

        echo "<div class='card-footer text-end'>";
        echo "<button type='submit' class='btn btn-primary'><i class='ti ti-device-floppy me-1'></i>Salvar Estilos de Login</button>";
        echo "</div>";
        echo "</div>"; // card

        echo "</form>";
        self::renderUploadOverlayAndScripts();
        echo "</div>"; // container
    }

    /**
     * Aba 3: Mural de Anúncios & Mídia (Painel Lateral de Login com Vídeos e Links)
     */
    public function showAnnouncementsTab() {
        global $CFG_GLPI;
        $cfg = self::getConfig();
        $pics_url = '/plugins/fluxiotheme/pics/';
        $form_action = ($CFG_GLPI['root_doc'] ?? '') . '/plugins/fluxiotheme/front/config.form.php';

        echo "<div class='container my-3'>";
        echo "<form method='post' action='{$form_action}' enctype='multipart/form-data'>";
        echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);
        echo "<input type='hidden' name='action' value='save_side_panel' />";

        echo "<div class='card mb-4'>";
        echo "<div class='card-header d-flex justify-content-between align-items-center'>";
        echo "<h3 class='card-title'><i class='ti ti-device-tv me-2'></i>Painel Lateral de Anúncios & Vídeos</h3>";
        $sp_checked = ($cfg['side_panel_enabled'] == '1') ? 'checked' : '';
        echo "<div class='form-check form-switch m-0'>";
        echo "<input class='form-check-input' type='checkbox' name='side_panel_enabled' value='1' id='sp_switch' {$sp_checked} onchange=\"document.getElementById('sp-content').style.opacity = this.checked ? '1' : '0.4';\" />";
        echo "<label class='form-check-label' for='sp_switch'><strong>Ativar Painel Lateral de Anúncios no Login</strong></label>";
        echo "</div>";
        echo "</div>"; // card-header

        $sp_opacity = ($cfg['side_panel_enabled'] == '1') ? 'opacity: 1;' : 'opacity: 0.4;';
        echo "<div class='card-body' id='sp-content' style='{$sp_opacity} transition: opacity 0.3s;'>";

        // Título e Subtítulo
        echo "<div class='row mb-3'>";
        echo "<div class='col-md-6'>";
        echo "<label class='form-label'>Título do Painel:</label>";
        echo "<input type='text' class='form-control' name='side_panel_title' value='{$cfg['side_panel_title']}' placeholder='Ex: Central de Ajuda & Comunicados' />";
        echo "</div>";

        echo "<div class='col-md-6'>";
        echo "<label class='form-label'>Subtítulo / Descrição Curta:</label>";
        echo "<input type='text' class='form-control' name='side_panel_subtitle' value='{$cfg['side_panel_subtitle']}' placeholder='Ex: Avisos importantes e tutoriais da TI' />";
        echo "</div>";
        echo "</div>";

        echo "<hr class='my-4' />";
        echo "<h4><i class='ti ti-video me-2'></i>Vídeo Institucional / Tutorial</h4>";

        // Tipo de Mídia
        echo "<div class='mb-3'>";
        echo "<label class='form-label'>Origem do Vídeo:</label>";
        echo "<div class='btn-group w-100' role='group'>";

        $type_none   = ($cfg['side_panel_media_type'] === 'none') ? 'active' : '';
        $type_upload = ($cfg['side_panel_media_type'] === 'upload') ? 'active' : '';
        $type_yt     = ($cfg['side_panel_media_type'] === 'youtube') ? 'active' : '';

        echo "<input type='radio' class='btn-check' name='side_panel_media_type' id='media_none' value='none' " . ($cfg['side_panel_media_type'] === 'none' ? 'checked' : '') . " onchange='toggleMediaType(this.value)' />";
        echo "<label class='btn btn-outline-secondary' for='media_none'><i class='ti ti-ban me-1'></i>Sem Vídeo</label>";

        echo "<input type='radio' class='btn-check' name='side_panel_media_type' id='media_upload' value='upload' " . ($cfg['side_panel_media_type'] === 'upload' ? 'checked' : '') . " onchange='toggleMediaType(this.value)' />";
        echo "<label class='btn btn-outline-primary' for='media_upload'><i class='ti ti-upload me-1'></i>Enviar Arquivo de Vídeo (MP4 / WebM)</label>";

        echo "<input type='radio' class='btn-check' name='side_panel_media_type' id='media_yt' value='youtube' " . ($cfg['side_panel_media_type'] === 'youtube' ? 'checked' : '') . " onchange='toggleMediaType(this.value)' />";
        echo "<label class='btn btn-outline-danger' for='media_yt'><i class='ti ti-brand-youtube me-1'></i>Link Externo (YouTube / Vimeo)</label>";

        echo "</div>";
        echo "</div>";

        // Bloco Upload Local
        $upload_display = ($cfg['side_panel_media_type'] === 'upload') ? 'display: block;' : 'display: none;';
        echo "<div id='block-video-upload' class='p-3 mb-3 border rounded shadow-sm' style='background: rgba(0,0,0,0.02); {$upload_display}'>";
        echo "<h5 class='d-flex align-items-center'><i class='ti ti-file-upload me-2 text-primary fs-2'></i>Upload Direto de Arquivo de Vídeo (Hospedado no Servidor)</h5>";
        echo "<p class='text-muted small'>Ideal para redes internas/intranets sem acesso externo. Formatos aceitos: .mp4 e .webm (Limite configurado: 100 MB).</p>";

        $video_file = $cfg['side_panel_video_file'] ?? '';
        $has_video = !empty($video_file) && file_exists(self::getMediaPath($video_file));
        if ($has_video) {
            $video_path = self::getMediaPath($video_file);
            $video_url  = self::getMediaUrl($video_file);
            $video_size = self::formatBytes(filesize($video_path));
            $video_date = date('d/m/Y H:i', filemtime($video_path));
            $is_in_uploads = str_contains($video_path, '/public/uploads/');
            $folder_badge = $is_in_uploads ? '<span class="badge bg-success-lt ms-2"><i class="ti ti-folder me-1"></i>pasta: uploads/</span>' : '<span class="badge bg-secondary-lt ms-2"><i class="ti ti-folder me-1"></i>pasta: pics/</span>';

            echo "<div class='alert alert-success d-flex align-items-center mb-3 p-3' style='border-radius: 12px; background: rgba(46, 204, 113, 0.12); border: 1px solid rgba(46, 204, 113, 0.4);'>";
            echo "  <i class='ti ti-circle-check fs-1 text-success me-3'></i>";
            echo "  <div class='flex-grow-1 text-start'>";
            echo "    <div class='fw-bold text-success fs-3'><i class='ti ti-check me-1'></i>Arquivo de Vídeo OK / Ativo no Sistema {$folder_badge}</div>";
            echo "    <div class='text-secondary small mt-1'>";
            echo "      <i class='ti ti-movie me-1'></i><strong>Arquivo:</strong> <code>{$video_file}</code> &nbsp;|&nbsp; ";
            echo "      <i class='ti ti-database me-1'></i><strong>Tamanho:</strong> {$video_size} &nbsp;|&nbsp; ";
            echo "      <i class='ti ti-clock me-1'></i><strong>Salvo em:</strong> {$video_date}";
            echo "    </div>";
            echo "  </div>";
            echo "  <div>";
            echo "    <a href='{$form_action}?action=delete_video' class='btn btn-sm btn-outline-danger' onclick=\"return confirm('Deseja excluir o arquivo de vídeo do servidor?');\">";
            echo "      <i class='ti ti-trash me-1'></i>Excluir Vídeo";
            echo "    </a>";
            echo "  </div>";
            echo "</div>";

            echo "<div class='mb-3 text-center p-2 rounded' style='background: #000; box-shadow: 0 4px 15px rgba(0,0,0,0.15);'>";
            echo "  <video controls playsinline style='max-width: 100%; max-height: 220px; border-radius: 8px;'>";
            echo "    <source src='{$video_url}?v=" . time() . "' type='video/mp4'>";
            echo "    Seu navegador não suporta este vídeo.";
            echo "  </video>";
            echo "</div>";
        } else {
            echo "<div class='alert alert-secondary d-flex align-items-center mb-3 p-2' style='border-radius: 10px;'>";
            echo "  <i class='ti ti-info-circle me-2 text-muted fs-2'></i>";
            echo "  <div class='small text-muted'>Nenhum arquivo de vídeo próprio carregado no momento. Selecione um arquivo abaixo para enviar.</div>";
            echo "</div>";
        }

        echo "<div class='mb-2'>";
        echo "  <label class='form-label small fw-bold mb-1'>" . ($has_video ? 'Substituir por outro arquivo de vídeo:' : 'Selecionar arquivo de vídeo para envio:') . "</label>";
        echo "  <input type='file' class='form-control fluxio-file-input' name='side_panel_video_file' id='input_side_panel_video_file' accept='.mp4,.webm,.mov' onchange=\"fluxioHandleVideoSelect(this, 'video_selection_info')\" />";
        echo "  <div id='video_selection_info' class='mt-2' style='display: none;'></div>";
        echo "</div>";
        echo "</div>";

        // Bloco YouTube / Vimeo
        $yt_display = ($cfg['side_panel_media_type'] === 'youtube') ? 'display: block;' : 'display: none;';
        echo "<div id='block-video-yt' class='p-3 mb-3 border rounded' style='background: rgba(0,0,0,0.02); {$yt_display}'>";
        echo "<h5><i class='ti ti-brand-youtube me-1'></i>Link Externo do Vídeo (YouTube / Vimeo)</h5>";
        echo "<p class='text-muted small'>Cole a URL completa do vídeo. O plugin converte automaticamente para o player incorporado.</p>";
        echo "<input type='text' class='form-control' name='side_panel_video_url' value='{$cfg['side_panel_video_url']}' placeholder='Ex: https://www.youtube.com/watch?v=dQw4w9WgXcQ' />";
        echo "</div>";

        echo "<hr class='my-4' />";
        echo "<h4><i class='ti ti-news me-2'></i>Conteúdo do Comunicado</h4>";
        echo "<p class='text-muted small'>Texto exibido logo abaixo do vídeo. Suporta formatação e links HTML (como &lt;b&gt;negrito&lt;/b&gt; ou &lt;a href='...' target='_blank'&gt;links&lt;/a&gt;).</p>";
        echo "<div class='mb-3'>";
        echo "<textarea class='form-control' name='side_panel_content' rows='5' placeholder='Digite aqui as novidades, horários de atendimento ou instruções de primeiro acesso...'>{$cfg['side_panel_content']}</textarea>";
        echo "</div>";

        echo "<hr class='my-4' />";
        echo "<h4><i class='ti ti-phone-call me-2'></i>Canais de Atendimento Rápido (Botões Interativos)</h4>";
        echo "<div class='row'>";

        echo "<div class='col-md-4 mb-3'>";
        echo "<label class='form-label'><i class='ti ti-brand-whatsapp text-success me-1'></i>Link ou WhatsApp:</label>";
        echo "<input type='text' class='form-control' name='side_panel_whatsapp' value='{$cfg['side_panel_whatsapp']}' placeholder='Ex: https://wa.me/554999999999' />";
        echo "<span class='form-hint'>Gera botão direto para iniciar conversa.</span>";
        echo "</div>";

        echo "<div class='col-md-4 mb-3'>";
        echo "<label class='form-label'><i class='ti ti-phone text-primary me-1'></i>Ramal / Telefone Suporte:</label>";
        echo "<input type='text' class='form-control' name='side_panel_phone' value='{$cfg['side_panel_phone']}' placeholder='Ex: Ramal 1234 / (49) 3221-0000' />";
        echo "<span class='form-hint'>Exibido com ícone para ligação/contato.</span>";
        echo "</div>";

        echo "<div class='col-md-4 mb-3'>";
        echo "<label class='form-label'><i class='ti ti-world text-info me-1'></i>Portal / Intranet URL:</label>";
        echo "<div class='input-group'>";
        echo "<input type='text' class='form-control' name='side_panel_portal_label' value='{$cfg['side_panel_portal_label']}' placeholder='Rótulo' style='max-width: 40%;' />";
        echo "<input type='text' class='form-control' name='side_panel_portal_url' value='{$cfg['side_panel_portal_url']}' placeholder='https://portalscc.com.br' />";
        echo "</div>";
        echo "<span class='form-hint'>Botão com link direto para seu portal.</span>";
        echo "</div>";

        echo "</div>"; // row

        echo "</div>"; // card-body

        echo "<div class='card-footer text-end'>";
        echo "<button type='submit' class='btn btn-primary'><i class='ti ti-device-floppy me-1'></i>Salvar Mural de Anúncios</button>";
        echo "</div>";
        echo "</div>"; // card

        echo "</form>";
        self::renderUploadOverlayAndScripts();

        // Bloco de Diagnóstico e Logs em Tempo Real
        $logs = self::getLastLogs(20);
        echo "<div class='card mb-4 border-0 shadow-sm' style='background: #0f172a; color: #f8fafc; border-radius: 14px;'>";
        echo "  <div class='card-header d-flex justify-content-between align-items-center' style='border-bottom: 1px solid rgba(255,255,255,0.1);'>";
        echo "    <h4 class='card-title text-white m-0'><i class='ti ti-terminal-2 text-info me-2'></i>Logs de Diagnóstico de Upload (Tempo Real)</h4>";
        echo "    <span class='badge bg-info-lt'>files/_log/fluxiotheme.log</span>";
        echo "  </div>";
        echo "  <div class='card-body p-3'>";
        echo "    <pre style='background: #020617; color: #38bdf8; font-family: monospace; font-size: 0.8rem; padding: 12px; border-radius: 8px; max-height: 180px; overflow-y: auto; margin: 0; white-space: pre-wrap;'>{$logs}</pre>";
        echo "  </div>";
        echo "</div>";

        echo "<script>
        function toggleMediaType(type) {
            document.getElementById('block-video-upload').style.display = (type === 'upload') ? 'block' : 'none';
            document.getElementById('block-video-yt').style.display = (type === 'youtube') ? 'block' : 'none';
        }
        </script>";

        echo "</div>"; // container
    }

    /**
     * Aba 4: Interface Interna (Sidebar & Topbar)
     */
    public function showInterfaceTab() {
        global $CFG_GLPI;
        $cfg = self::getConfig();
        $form_action = ($CFG_GLPI['root_doc'] ?? '') . '/plugins/fluxiotheme/front/config.form.php';

        echo "<div class='container my-3'>";

        // Painel de Presets Rápidos
        echo "<div class='card mb-4'>";
        echo "<div class='card-header'><h3 class='card-title'><i class='ti ti-sparkles me-2'></i>Paletas Prontas (1 Clique)</h3></div>";
        echo "<div class='card-body'>";
        echo "<p class='text-muted small'>Selecione um tema pré-configurado para preencher as cores automaticamente:</p>";
        echo "<div class='btn-list'>";

        echo "<a href='{$form_action}?action=apply_preset&preset=scc' class='btn btn-outline-primary'>";
        echo "<span class='badge me-1' style='background:#163961;'>&nbsp;</span> Azul SCC (Corporativo)";
        echo "</a>";

        echo "<a href='{$form_action}?action=apply_preset&preset=dark_slate' class='btn btn-outline-dark'>";
        echo "<span class='badge me-1' style='background:#0f172a;'>&nbsp;</span> Dark Slate (Grafite Moderno)";
        echo "</a>";

        echo "<a href='{$form_action}?action=apply_preset&preset=emerald' class='btn btn-outline-success'>";
        echo "<span class='badge me-1' style='background:#064e3b;'>&nbsp;</span> Emerald Green (Verde Escuro)";
        echo "</a>";

        echo "<a href='{$form_action}?action=apply_preset&preset=reset_glpi' class='btn btn-outline-secondary' onclick=\"return confirm('Deseja desativar as cores customizadas e voltar à barra e menu padrão do GLPI?');\">";
        echo "<i class='ti ti-rotate-2 me-1'></i>Padrão Nativo do GLPI";
        echo "</a>";

        echo "</div>";
        echo "</div>";
        echo "</div>";

        echo "<form method='post' action='{$form_action}'>";
        echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);
        echo "<input type='hidden' name='action' value='save_interface' />";

        // Seção 1: Menu Lateral (Sidebar)
        echo "<div class='card mb-4'>";
        echo "<div class='card-header d-flex justify-content-between align-items-center'>";
        echo "<h3 class='card-title'><i class='ti ti-layout-sidebar me-2'></i>Menu Lateral (Sidebar)</h3>";
        $sb_checked = ($cfg['custom_sidebar_enabled'] == '1') ? 'checked' : '';
        echo "<div class='form-check form-switch m-0'>";
        echo "<input class='form-check-input' type='checkbox' name='custom_sidebar_enabled' value='1' id='sb_switch' {$sb_checked} onchange=\"document.getElementById('sb-settings').style.opacity = this.checked ? '1' : '0.4';\" />";
        echo "<label class='form-check-label' for='sb_switch'><strong>Personalizar Menu Lateral</strong></label>";
        echo "</div>";
        echo "</div>"; // card-header

        $sb_style = ($cfg['custom_sidebar_enabled'] == '1') ? 'opacity: 1;' : 'opacity: 0.4;';
        echo "<div class='card-body' id='sb-settings' style='{$sb_style} transition: opacity 0.3s;'>";
        echo "<div class='row'>";

        echo "<div class='col-md-4 mb-3'>";
        $this->renderColorInput('Gradiente da Sidebar (Início)', 'sidebar_bg_start', $cfg['sidebar_bg_start'], 'Cor no topo da barra lateral');
        echo "</div>";

        echo "<div class='col-md-4 mb-3'>";
        $this->renderColorInput('Gradiente da Sidebar (Fim)', 'sidebar_bg_end', $cfg['sidebar_bg_end'], 'Cor na base da barra lateral');
        echo "</div>";

        echo "<div class='col-md-4 mb-3'>";
        $this->renderColorInput('Item Selecionado / Ativo', 'sidebar_active_color', $cfg['sidebar_active_color'], 'Cor da barra de destaque do menu ativo');
        echo "</div>";

        echo "</div>"; // row
        echo "</div>"; // card-body
        echo "</div>"; // card

        // Seção 2: Barra Superior (Topbar)
        echo "<div class='card mb-4'>";
        echo "<div class='card-header d-flex justify-content-between align-items-center'>";
        echo "<h3 class='card-title'><i class='ti ti-layout-navbar me-2'></i>Barra Superior (Topbar)</h3>";
        $top_checked = ($cfg['custom_topbar_enabled'] == '1') ? 'checked' : '';
        echo "<div class='form-check form-switch m-0'>";
        echo "<input class='form-check-input' type='checkbox' name='custom_topbar_enabled' value='1' id='top_switch' {$top_checked} onchange=\"document.getElementById('top-settings').style.opacity = this.checked ? '1' : '0.4';\" />";
        echo "<label class='form-check-label' for='top_switch'><strong>Personalizar Barra Superior</strong></label>";
        echo "</div>";
        echo "</div>"; // card-header

        $top_style = ($cfg['custom_topbar_enabled'] == '1') ? 'opacity: 1;' : 'opacity: 0.4;';
        echo "<div class='card-body' id='top-settings' style='{$top_style} transition: opacity 0.3s;'>";
        echo "<div class='row'>";

        echo "<div class='col-md-6 mb-3'>";
        $this->renderColorInput('Gradiente da Topbar (Início / Esquerda)', 'topbar_bg_start', $cfg['topbar_bg_start'], 'Cor no lado esquerdo da barra superior');
        echo "</div>";

        echo "<div class='col-md-6 mb-3'>";
        $this->renderColorInput('Gradiente da Topbar (Fim / Direita)', 'topbar_bg_end', $cfg['topbar_bg_end'], 'Cor no lado direito da barra superior');
        echo "</div>";

        echo "</div>"; // row
        echo "</div>"; // card-body
        echo "</div>"; // card

        // Seção 3: Cor Primária Geral
        echo "<div class='card mb-4'>";
        echo "<div class='card-header'><h3 class='card-title'><i class='ti ti-brush me-2'></i>Cor Geral do Sistema</h3></div>";
        echo "<div class='card-body'>";
        echo "<div class='row'>";
        echo "<div class='col-md-6 mb-3'>";
        $this->renderColorInput('Cor Primária (Links, Botões e Destaques)', 'primary_color', $cfg['primary_color'], 'Cor de destaque padrão do sistema');
        echo "</div>";
        echo "</div>";
        echo "</div>"; // card-body

        echo "<div class='card-footer text-end'>";
        echo "<button type='submit' class='btn btn-primary'><i class='ti ti-device-floppy me-1'></i>Salvar Interface Interna</button>";
        echo "</div>";
        echo "</div>"; // card

        echo "</form>";
        echo "</div>"; // container
    }

    /**
     * Helper para renderizar card com preview de imagem e upload com feedback completo
     */
    private function renderUploadCard(string $title, string $desc, string $field, string $current_url, string $hint) {
        $filename = basename(parse_url($current_url, PHP_URL_PATH));
        $real_path = self::getMediaPath($filename);
        $file_exists = file_exists($real_path);
        $current_url = self::getMediaUrl($filename);
        $filesize_str = $file_exists ? self::formatBytes(filesize($real_path)) : '';
        $img_info = $file_exists ? @getimagesize($real_path) : null;
        $dimensions_str = ($img_info && isset($img_info[0])) ? "{$img_info[0]}x{$img_info[1]} px" : '';
        $is_custom = str_contains($filename, '_custom.');

        echo "<div class='col-md-6 mb-3'>";
        echo "<div class='card h-100 shadow-sm border'>";
        echo "<div class='card-body d-flex flex-column justify-content-between'>";
        echo "<div>";
        echo "<div class='d-flex justify-content-between align-items-start mb-2'>";
        echo "  <div>";
        echo "    <h4 class='card-title mb-1'>{$title}</h4>";
        echo "    <p class='text-muted small mb-0'>{$desc}</p>";
        echo "  </div>";

        if ($file_exists) {
            $badge_class = $is_custom ? 'bg-success-lt text-success border border-success' : 'bg-blue-lt text-primary border border-primary';
            $badge_icon  = $is_custom ? 'ti ti-circle-check' : 'ti ti-info-circle';
            $badge_text  = $is_custom ? 'Personalizado (OK)' : 'Padrão do Sistema';
            echo "  <span class='badge {$badge_class}'><i class='{$badge_icon} me-1'></i>{$badge_text}</span>";
        }
        echo "</div>";

        // Preview Box com borda sutil
        echo "<div class='text-center p-3 mb-2 rounded' style='background: rgba(0,0,0,0.03); min-height: 105px; display: flex; align-items: center; justify-content: center; border: 1px dashed rgba(0,0,0,0.12);'>";
        echo "  <img id='preview_img_{$field}' src='{$current_url}?v=" . time() . "' alt='{$title}' style='max-height: 80px; max-width: 100%; object-fit: contain;' />";
        echo "</div>";

        // Info da imagem em uso
        if ($file_exists) {
            $meta = array_filter([$dimensions_str, $filesize_str]);
            $meta_str = !empty($meta) ? ' (' . implode(' • ', $meta) . ')' : '';
            echo "<div class='small text-muted mb-2'>";
            echo "  <i class='ti ti-file-check text-success me-1'></i><strong>Arquivo atual:</strong> <code>{$filename}</code>{$meta_str}";
            echo "</div>";
        }

        // Input de arquivo com preview imediato e status
        echo "<div class='mb-2'>";
        echo "  <label class='form-label small fw-bold mb-1'>Substituir por novo arquivo:</label>";
        echo "  <input type='file' class='form-control fluxio-file-input' name='{$field}' id='input_{$field}' accept='.png,.jpg,.jpeg,.webp,.svg,.ico' onchange=\"fluxioHandleFileSelect(this, 'preview_img_{$field}', 'info_{$field}')\" />";
        echo "  <div id='info_{$field}' class='mt-1 small' style='display: none;'></div>";
        echo "  <span class='form-hint small mt-1'>{$hint}</span>";
        echo "</div>";
        echo "</div>"; // wrapper top

        // Botão Restaurar se for customizado
        if ($is_custom) {
            global $CFG_GLPI;
            $form_action = ($CFG_GLPI['root_doc'] ?? '') . '/plugins/fluxiotheme/front/config.form.php';
            echo "<div class='text-end mt-2 pt-2 border-top'>";
            echo "  <a href='{$form_action}?action=reset_default&field={$field}' class='btn btn-sm btn-outline-danger' onclick=\"return confirm('Deseja restaurar a imagem padrão para {$title}?');\">";
            echo "    <i class='ti ti-rotate-2 me-1'></i>Restaurar Padrão Original";
            echo "  </a>";
            echo "</div>";
        }

        echo "</div>"; // card-body
        echo "</div>"; // card
        echo "</div>"; // col
    }

    /**
     * Helper para renderizar input de cor sincronizado em tempo real
     */
    private function renderColorInput(string $label, string $name, string $value, string $hint) {
        $color_id = "color_{$name}";
        $text_id  = "text_{$name}";

        echo "<label class='form-label'>{$label}:</label>";
        echo "<div class='input-group'>";
        echo "<input type='color' id='{$color_id}' class='form-control form-control-color' name='{$name}' value='{$value}' oninput=\"document.getElementById('{$text_id}').value = this.value;\" />";
        echo "<input type='text' id='{$text_id}' class='form-control' name='{$name}_text' value='{$value}' oninput=\"if (this.value.match(/^#[0-9a-fA-F]{6}$/)) document.getElementById('{$color_id}').value = this.value;\" />";
        echo "</div>";
        echo "<span class='form-hint'>{$hint}</span>";
    }

    /**
     * Modal de Loading Overlay e Scripts de Feedback para Uploads
     */
    public static function renderUploadOverlayAndScripts() {
        static $rendered = false;
        if ($rendered) return;
        $rendered = true;

        echo "
        <!-- Modal Overlay de Upload com Progresso Real -->
        <div id='fluxio-upload-overlay' style='display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.82); backdrop-filter: blur(8px); z-index: 999999; justify-content: center; align-items: center; flex-direction: column;'>
           <div class='card p-4 text-center shadow-lg border-0' style='max-width: 520px; width: 92%; border-radius: 24px; background: #ffffff; color: #1e293b; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);'>
              <div class='mb-3'>
                 <div id='fluxio-upload-spinner' class='spinner-border text-primary' style='width: 3.5rem; height: 3.5rem; border-width: 0.3em;' role='status'>
                    <span class='visually-hidden'>Carregando...</span>
                 </div>
                 <div id='fluxio-upload-icon-success' style='display: none;'>
                    <i class='ti ti-circle-check text-success' style='font-size: 3.8rem;'></i>
                 </div>
              </div>
              <h3 class='mb-1 fw-bold text-dark' id='fluxio-upload-title'>Enviando Arquivo...</h3>
              <p class='text-muted small mb-3' id='fluxio-upload-desc'>O arquivo está sendo transferido e salvo no servidor GLPI. Por favor, aguarde a conclusão.</p>
              
              <div class='progress mb-2' style='height: 18px; border-radius: 9px; background: rgba(0,0,0,0.08); overflow: hidden;'>
                 <div id='fluxio-upload-bar' class='progress-bar progress-bar-striped progress-bar-animated bg-primary fw-bold' role='progressbar' style='width: 0%; font-size: 0.75rem;'>0%</div>
              </div>

              <div class='p-2 rounded mt-2 text-start small' style='background: #f8fafc; border: 1px solid #e2e8f0;' id='fluxio-upload-info'>
                 <i class='ti ti-upload me-1 text-primary'></i> Preparando transferência...
              </div>

              <div class='mt-3 text-center' id='fluxio-upload-actions' style='display: none;'>
                 <button type='button' class='btn btn-sm btn-secondary' onclick=\"document.getElementById('fluxio-upload-overlay').style.display='none';\">Fechar</button>
              </div>
           </div>
        </div>

        <script>
        function fluxioFormatBytes(bytes) {
            if (bytes >= 1048576) {
                return (bytes / 1048576).toFixed(1) + ' MB';
            } else if (bytes >= 1024) {
                return (bytes / 1024).toFixed(1) + ' KB';
            }
            return bytes + ' B';
        }

        // Preview e feedback imediato para Imagens
        function fluxioHandleFileSelect(input, previewId, infoId) {
            var infoBox = document.getElementById(infoId);
            var previewImg = document.getElementById(previewId);

            if (input.files && input.files[0]) {
                var file = input.files[0];
                var sizeStr = fluxioFormatBytes(file.size);

                if (infoBox) {
                    infoBox.style.display = 'block';
                    infoBox.innerHTML = '<div class=\"alert alert-info py-2 px-3 mb-1\"><i class=\"ti ti-file-upload me-1\"></i><strong>Arquivo selecionado:</strong> ' + file.name + ' (' + sizeStr + ') — <span class=\"text-success fw-bold\">Pronto para salvar!</span></div>';
                }

                if (file.type.match('image.*') && previewImg) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            } else if (infoBox) {
                infoBox.style.display = 'none';
            }
        }

        // Feedback imediato para Vídeos
        function fluxioHandleVideoSelect(input, infoId) {
            var infoBox = document.getElementById(infoId);
            if (!infoBox) return;

            if (input.files && input.files[0]) {
                var file = input.files[0];
                var sizeMb = file.size / (1024 * 1024);
                var sizeStr = fluxioFormatBytes(file.size);

                infoBox.style.display = 'block';

                if (sizeMb > 100) {
                    infoBox.innerHTML = '<div class=\"alert alert-danger py-2 px-3 mb-0\"><i class=\"ti ti-alert-triangle me-1\"></i><strong>Atenção:</strong> O vídeo selecionado possui <strong>' + sizeStr + '</strong>, excedendo o limite de 100 MB do servidor! O upload será rejeitado.</div>';
                } else {
                    infoBox.innerHTML = '<div class=\"alert alert-primary py-2 px-3 mb-0\"><i class=\"ti ti-movie me-1\"></i><strong>Vídeo selecionado:</strong> <code>' + file.name + '</code> (' + sizeStr + ')<br><span class=\"small text-muted\">Clique no botão <strong>\"Salvar Mural de Anúncios\"</strong> abaixo para iniciar o envio com barra de progresso em tempo real.</span></div>';
                }
            } else {
                infoBox.style.display = 'none';
            }
        }

        // Upload inteligente via AJAX com monitoramento de progresso real (0% a 100%)
        (function fluxioInitForms() {
            var forms = document.querySelectorAll('form[enctype=\"multipart/form-data\"]:not([data-fluxio-bound])');
            forms.forEach(function(form) {
                form.setAttribute('data-fluxio-bound', '1');
                form.addEventListener('submit', function(e) {
                    var fileInputs = form.querySelectorAll('input[type=\"file\"]');
                    var uploadingFile = null;

                    fileInputs.forEach(function(inp) {
                        if (inp.files && inp.files.length > 0 && inp.files[0].size > 0) {
                            uploadingFile = inp.files[0];
                        }
                    });

                    // Se não houver arquivo anexado, envia normalmente via form padrão
                    if (!uploadingFile) {
                        return true;
                    }

                    // Se houver arquivo anexado, assume controle via AJAX para exibir progresso real e evitar erro de conexão
                    e.preventDefault();

                    var overlay   = document.getElementById('fluxio-upload-overlay');
                    var title     = document.getElementById('fluxio-upload-title');
                    var desc      = document.getElementById('fluxio-upload-desc');
                    var bar       = document.getElementById('fluxio-upload-bar');
                    var info      = document.getElementById('fluxio-upload-info');
                    var actions   = document.getElementById('fluxio-upload-actions');
                    var spinner   = document.getElementById('fluxio-upload-spinner');
                    var successIc = document.getElementById('fluxio-upload-icon-success');

                    var isVideo = uploadingFile.name.match(/\\.(mp4|webm|mov)$/i);
                    title.innerText = isVideo ? 'Enviando Arquivo de Vídeo...' : 'Carregando Arquivo...';
                    desc.innerHTML = 'Enviando seu arquivo para o servidor GLPI.<br>Por favor, aguarde a conclusão.';
                    info.innerHTML = '<i class=\"ti ti-file-text text-primary me-1\"></i><strong>Arquivo:</strong> ' + uploadingFile.name + ' (' + fluxioFormatBytes(uploadingFile.size) + ')';
                    bar.style.width = '0%';
                    bar.className = 'progress-bar progress-bar-striped progress-bar-animated bg-primary fw-bold';
                    bar.innerText = '0%';
                    spinner.style.display = 'inline-block';
                    successIc.style.display = 'none';
                    actions.style.display = 'none';
                    overlay.style.display = 'flex';

                    var targetUrl = form.getAttribute('action') || (window.location.pathname + window.location.search);
                    var formData = new FormData(form);
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', targetUrl, true);
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

                    // Monitora upload em tempo real
                    xhr.upload.onprogress = function(evt) {
                        if (evt.lengthComputable) {
                            var pct = Math.round((evt.loaded / evt.total) * 100);
                            bar.style.width = pct + '%';
                            bar.innerText = pct + '%';
                            info.innerHTML = '<i class=\"ti ti-cloud-upload text-primary me-1\"></i><strong>Enviando:</strong> ' + fluxioFormatBytes(evt.loaded) + ' de ' + fluxioFormatBytes(evt.total) + ' (' + pct + '%)';
                            if (pct >= 100) {
                                title.innerText = 'Salvando no Servidor...';
                                desc.innerHTML = 'Arquivo transferido com sucesso! O servidor está gravando no disco...';
                            }
                        }
                    };

                    xhr.onload = function() {
                        if (xhr.status >= 200 && xhr.status < 400) {
                            title.innerText = 'Concluído com Sucesso!';
                            desc.innerHTML = '<span class=\"text-success fw-bold\"><i class=\"ti ti-check me-1\"></i>Arquivo salvo com sucesso na pasta de uploads!</span>';
                            bar.style.width = '100%';
                            bar.className = 'progress-bar bg-success fw-bold';
                            bar.innerText = '100% OK';
                            spinner.style.display = 'none';
                            successIc.style.display = 'block';

                            var redirectUrl = window.location.href;
                            try {
                                var resp = JSON.parse(xhr.responseText);
                                if (resp && resp.redirect) {
                                    redirectUrl = resp.redirect;
                                }
                            } catch(err) {}

                            setTimeout(function() {
                                window.location.href = redirectUrl;
                            }, 700);
                        } else if (xhr.status === 404) {
                            title.innerText = 'Rota Não Encontrada (404)';
                            desc.innerHTML = '<span class=\"text-danger fw-bold\">O endereço (' + targetUrl + ') retornou 404. Verifique o caminho do formulário.</span>';
                            bar.className = 'progress-bar bg-danger';
                            actions.style.display = 'block';
                        } else {
                            title.innerText = 'Atenção ao Salvar';
                            desc.innerHTML = '<span class=\"text-danger fw-bold\">O servidor retornou status HTTP ' + xhr.status + '.</span>';
                            bar.className = 'progress-bar bg-danger';
                            actions.style.display = 'block';
                        }
                    };

                    xhr.onerror = function() {
                        title.innerText = 'Falha no Envio';
                        desc.innerHTML = '<span class=\"text-danger fw-bold\"><i class=\"ti ti-alert-triangle me-1\"></i>A conexão foi interrompida pela rede.</span><br><small class=\"text-muted\">Verifique a estabilidade da internet ou utilize o envio manual via SFTP/SSH para <code>public/uploads/</code>.</small>';
                        bar.className = 'progress-bar bg-danger';
                        actions.style.display = 'block';
                    };

                    xhr.send(formData);
                });
            });
        })();
        </script>
        ";
    }
}
