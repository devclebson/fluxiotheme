<?php
/**
 * ---------------------------------------------------------------------
 * FluxIO Custom Theme - GLPI Plugin
 * Configuration Form Controller
 * ---------------------------------------------------------------------
 */

$glpi_root = realpath(__DIR__ . '/../../..');
if (file_exists($glpi_root . "/inc/includes.php")) {
    include_once($glpi_root . "/inc/includes.php");
} else {
    die("Erro crítico: Não foi possível localizar o arquivo includes.php.");
}

Session::checkRight("config", READ);

$config = new PluginFluxiothemeConfig();

// 1. Ação: Salvar Logotipos
if (isset($_POST["action"]) && $_POST["action"] === "save_logos") {
    Session::checkRight("config", UPDATE);
    Session::checkCSRF($_POST);

    $updates = [];

    if ($file = PluginFluxiothemeConfig::handleUpload('logo_login', 'logo_login')) {
        $updates['logo_login'] = $file;
    }

    if ($file = PluginFluxiothemeConfig::handleUpload('logo_sidebar', 'logo_sidebar')) {
        $updates['logo_sidebar'] = $file;
    }

    if ($file = PluginFluxiothemeConfig::handleUpload('logo_sidebar_collapsed', 'logo_sidebar_collapsed')) {
        $updates['logo_sidebar_collapsed'] = $file;
    }

    // Favicon vai direto para favicon.ico
    if (isset($_FILES['favicon']) && $_FILES['favicon']['error'] === UPLOAD_ERR_OK) {
        $dest_fav = GLPI_ROOT . '/plugins/fluxiotheme/public/pics/favicon.ico';
        if (move_uploaded_file($_FILES['favicon']['tmp_name'], $dest_fav)) {
            chmod($dest_fav, 0664);
        }
    }

    if (!empty($updates)) {
        Config::setConfigurationValues('plugin:fluxiotheme', $updates);
    }

    PluginFluxiothemeConfig::generateCssVariables();

    Session::addMessageAfterRedirect("Logotipos atualizados com sucesso!");

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'redirect' => $CFG_GLPI['root_doc'] . "/plugins/fluxiotheme/front/config.form.php?tab=PluginFluxiothemeConfig$1"]);
        exit;
    }
    Html::redirect($CFG_GLPI['root_doc'] . "/plugins/fluxiotheme/front/config.form.php?tab=PluginFluxiothemeConfig$1");
}

// 2. Ação: Salvar Estilos da Tela de Login (Visual & Vidro)
if (isset($_POST["action"]) && $_POST["action"] === "save_login") {
    Session::checkRight("config", UPDATE);
    Session::checkCSRF($_POST);

    $updates = [
        'login_overlay_start'  => strip_tags($_POST['login_overlay_start'] ?? 'rgba(65, 117, 238, 0.78)'),
        'login_overlay_end'    => strip_tags($_POST['login_overlay_end'] ?? 'rgba(15, 23, 42, 0.78)'),
        'card_blur'            => (int)($_POST['card_blur'] ?? 18),
        'card_radius'          => (int)($_POST['card_radius'] ?? 28),
        'card_bg'              => strip_tags($_POST['card_bg'] ?? 'rgba(17, 25, 40, 0.72)'),
        'login_btn_start'      => strip_tags($_POST['login_btn_start'] ?? '#2563eb'),
        'login_btn_end'        => strip_tags($_POST['login_btn_end'] ?? '#0ea5e9'),
        'enable_saml_style'    => isset($_POST['enable_saml_style']) ? '1' : '0',
        'login_notice_enabled' => isset($_POST['login_notice_enabled']) ? '1' : '0',
        'login_notice_type'    => strip_tags($_POST['login_notice_type'] ?? 'info'),
        'login_notice_text'    => strip_tags($_POST['login_notice_text'] ?? '')
    ];

    if ($file = PluginFluxiothemeConfig::handleUpload('bg_desktop', 'bg_desktop')) {
        $updates['bg_desktop'] = $file;
    }

    if ($file = PluginFluxiothemeConfig::handleUpload('bg_mobile', 'bg_mobile')) {
        $updates['bg_mobile'] = $file;
    }

    Config::setConfigurationValues('plugin:fluxiotheme', $updates);
    PluginFluxiothemeConfig::generateCssVariables();

    Session::addMessageAfterRedirect("Estilos da tela de login atualizados com sucesso!");

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'redirect' => $CFG_GLPI['root_doc'] . "/plugins/fluxiotheme/front/config.form.php?tab=PluginFluxiothemeConfig$2"]);
        exit;
    }
    Html::redirect($CFG_GLPI['root_doc'] . "/plugins/fluxiotheme/front/config.form.php?tab=PluginFluxiothemeConfig$2");
}

// 3. Ação: Salvar Mural de Anúncios & Mídia
if (isset($_POST["action"]) && $_POST["action"] === "save_side_panel") {
    Session::checkRight("config", UPDATE);
    Session::checkCSRF($_POST);

    $files_summary = [];
    if (!empty($_FILES)) {
        foreach ($_FILES as $k => $f) {
            $files_summary[$k] = "name={$f['name']}, size={$f['size']}, error={$f['error']}";
        }
    }
    PluginFluxiothemeConfig::log("POST save_side_panel recebido. Arquivos: " . json_encode($files_summary));

    $updates = [
        'side_panel_enabled'      => isset($_POST['side_panel_enabled']) ? '1' : '0',
        'side_panel_title'        => strip_tags($_POST['side_panel_title'] ?? ''),
        'side_panel_subtitle'     => strip_tags($_POST['side_panel_subtitle'] ?? ''),
        'side_panel_media_type'   => strip_tags($_POST['side_panel_media_type'] ?? 'none'),
        'side_panel_video_url'    => strip_tags($_POST['side_panel_video_url'] ?? ''),
        'side_panel_content'      => $_POST['side_panel_content'] ?? '',
        'side_panel_whatsapp'     => strip_tags($_POST['side_panel_whatsapp'] ?? ''),
        'side_panel_phone'        => strip_tags($_POST['side_panel_phone'] ?? ''),
        'side_panel_portal_label' => strip_tags($_POST['side_panel_portal_label'] ?? 'Portal SCC'),
        'side_panel_portal_url'   => strip_tags($_POST['side_panel_portal_url'] ?? '')
    ];

    // Upload de arquivo de vídeo (.mp4, .webm, .mov)
    if ($video = PluginFluxiothemeConfig::handleUpload('side_panel_video_file', 'login_video', ['mp4', 'webm', 'mov'])) {
        $updates['side_panel_video_file'] = $video;
        $updates['side_panel_media_type'] = 'upload';
        Session::addMessageAfterRedirect("Vídeo carregado e salvo com sucesso ({$video})!");
    }

    Config::setConfigurationValues('plugin:fluxiotheme', $updates);
    PluginFluxiothemeConfig::generateCssVariables();

    Session::addMessageAfterRedirect("Mural de Anúncios e Vídeos atualizado com sucesso!");

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'redirect' => $CFG_GLPI['root_doc'] . "/plugins/fluxiotheme/front/config.form.php?tab=PluginFluxiothemeConfig$3"]);
        exit;
    }
    Html::redirect($CFG_GLPI['root_doc'] . "/plugins/fluxiotheme/front/config.form.php?tab=PluginFluxiothemeConfig$3");
}

// 4. Ação: Excluir Vídeo Local
if (isset($_GET["action"]) && $_GET["action"] === "delete_video") {
    Session::checkRight("config", UPDATE);

    $cfg = PluginFluxiothemeConfig::getConfig();
    if (!empty($cfg['side_panel_video_file'])) {
        $file_path = PluginFluxiothemeConfig::getMediaPath($cfg['side_panel_video_file']);
        if (file_exists($file_path)) {
            @unlink($file_path);
        }
    }

    Config::setConfigurationValues('plugin:fluxiotheme', [
        'side_panel_video_file' => '',
        'side_panel_media_type' => 'none'
    ]);

    Session::addMessageAfterRedirect("Vídeo removido com sucesso!");
    Html::redirect($CFG_GLPI['root_doc'] . "/plugins/fluxiotheme/front/config.form.php?tab=PluginFluxiothemeConfig$3");
}

// 5. Ação: Salvar Interface Interna (Sidebar e Topbar)
if (isset($_POST["action"]) && $_POST["action"] === "save_interface") {
    Session::checkRight("config", UPDATE);
    Session::checkCSRF($_POST);

    $updates = [
        'custom_sidebar_enabled' => isset($_POST['custom_sidebar_enabled']) ? '1' : '0',
        'sidebar_bg_start'       => strip_tags($_POST['sidebar_bg_start'] ?? '#163961'),
        'sidebar_bg_end'         => strip_tags($_POST['sidebar_bg_end'] ?? '#0f172a'),
        'sidebar_active_color'   => strip_tags($_POST['sidebar_active_color'] ?? '#2563eb'),

        'custom_topbar_enabled'  => isset($_POST['custom_topbar_enabled']) ? '1' : '0',
        'topbar_bg_start'        => strip_tags($_POST['topbar_bg_start'] ?? '#163961'),
        'topbar_bg_end'          => strip_tags($_POST['topbar_bg_end'] ?? '#0f172a'),

        'primary_color'          => strip_tags($_POST['primary_color'] ?? '#2563eb')
    ];

    Config::setConfigurationValues('plugin:fluxiotheme', $updates);
    PluginFluxiothemeConfig::generateCssVariables();

    Session::addMessageAfterRedirect("Cores do menu e barra superior salvas com sucesso!");
    Html::redirect($CFG_GLPI['root_doc'] . "/plugins/fluxiotheme/front/config.form.php?tab=PluginFluxiothemeConfig$4");
}

// 6. Ação: Aplicar Presets Prontos
if (isset($_GET["action"]) && $_GET["action"] === "apply_preset" && isset($_GET["preset"])) {
    Session::checkRight("config", UPDATE);

    $preset = $_GET["preset"];
    $updates = [];

    switch ($preset) {
        case 'scc':
            $updates = [
                'custom_sidebar_enabled' => '1',
                'sidebar_bg_start'       => '#163961',
                'sidebar_bg_end'         => '#0f172a',
                'sidebar_active_color'   => '#2563eb',
                'custom_topbar_enabled'  => '1',
                'topbar_bg_start'        => '#163961',
                'topbar_bg_end'          => '#0f172a',
                'primary_color'          => '#2563eb'
            ];
            Session::addMessageAfterRedirect("Preset 'Azul SCC' aplicado com sucesso!");
            break;

        case 'dark_slate':
            $updates = [
                'custom_sidebar_enabled' => '1',
                'sidebar_bg_start'       => '#1e293b',
                'sidebar_bg_end'         => '#0f172a',
                'sidebar_active_color'   => '#38bdf8',
                'custom_topbar_enabled'  => '1',
                'topbar_bg_start'        => '#1e293b',
                'topbar_bg_end'          => '#0f172a',
                'primary_color'          => '#38bdf8'
            ];
            Session::addMessageAfterRedirect("Preset 'Dark Slate' aplicado com sucesso!");
            break;

        case 'emerald':
            $updates = [
                'custom_sidebar_enabled' => '1',
                'sidebar_bg_start'       => '#064e3b',
                'sidebar_bg_end'         => '#022c22',
                'sidebar_active_color'   => '#10b981',
                'custom_topbar_enabled'  => '1',
                'topbar_bg_start'        => '#064e3b',
                'topbar_bg_end'          => '#022c22',
                'primary_color'          => '#10b981'
            ];
            Session::addMessageAfterRedirect("Preset 'Emerald Green' aplicado com sucesso!");
            break;

        case 'reset_glpi':
            $updates = [
                'custom_sidebar_enabled' => '0',
                'custom_topbar_enabled'  => '0',
                'primary_color'          => '#206bc4'
            ];
            Session::addMessageAfterRedirect("Cores nativas do GLPI restauradas!");
            break;
    }

    if (!empty($updates)) {
        Config::setConfigurationValues('plugin:fluxiotheme', $updates);
        PluginFluxiothemeConfig::generateCssVariables();
    }

    Html::redirect($CFG_GLPI['root_doc'] . "/plugins/fluxiotheme/front/config.form.php?tab=PluginFluxiothemeConfig$4");
}

// 7. Ação: Restaurar Padrão de Arquivo
if (isset($_GET["action"]) && $_GET["action"] === "reset_default" && isset($_GET["field"])) {
    Session::checkRight("config", UPDATE);

    $field = $_GET["field"];
    $defaults = [
        'logo_login'             => 'default_logo.png',
        'logo_sidebar'           => 'default_logo.png',
        'logo_sidebar_collapsed' => 'default_logo_collapsed.png',
        'bg_desktop'             => 'default_bg_desktop.jpg',
        'bg_mobile'              => 'background_helpdesk_mobile.png'
    ];

    if ($field === 'favicon') {
        $custom_fav = GLPI_ROOT . '/plugins/fluxiotheme/public/pics/favicon.ico';
        if (file_exists($custom_fav)) {
            @unlink($custom_fav);
        }
        Session::addMessageAfterRedirect("Favicon restaurado para o padrão!");
        Html::redirect($CFG_GLPI['root_doc'] . "/plugins/fluxiotheme/front/config.form.php?tab=PluginFluxiothemeConfig$1");
    }

    if (isset($defaults[$field])) {
        Config::setConfigurationValues('plugin:fluxiotheme', [$field => $defaults[$field]]);
        PluginFluxiothemeConfig::generateCssVariables();
        Session::addMessageAfterRedirect("Item restaurado para o padrão com sucesso!");
    }

    $tab = ($field === 'bg_desktop' || $field === 'bg_mobile') ? 2 : 1;
    Html::redirect($CFG_GLPI['root_doc'] . "/plugins/fluxiotheme/front/config.form.php?tab=PluginFluxiothemeConfig${tab}");
}

// Renderização da Interface
Html::header('FluxIO Custom Theme', $_SERVER['PHP_SELF'], "config", "PluginFluxiothemeConfig");
$config->display(['loaded' => true, 'show_nav_header' => false]);
Html::footer();
