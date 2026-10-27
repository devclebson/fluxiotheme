<?php
/**
 * ---------------------------------------------------------------------
 * FluxIO Custom Theme - GLPI Plugin
 * Branding and Theme Customizer for GLPI 12
 * ---------------------------------------------------------------------
 */

define('PLUGIN_FLUXIOTHEME_VERSION', '1.0.3');

function plugin_init_fluxiotheme() {
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS['csrf_compliant']['fluxiotheme'] = true;
    $PLUGIN_HOOKS['config_page']['fluxiotheme'] = 'front/config.form.php';

    Plugin::registerClass('PluginFluxiothemeConfig', ['addtabon' => ['Config']]);

    // Menu em Configurar
    if (Session::haveRight('config', UPDATE)) {
        $PLUGIN_HOOKS['menu_toadd']['fluxiotheme'] = ['config' => 'PluginFluxiothemeConfig'];
    }

    // Gancho para Comunicado/Mural na tela de login
    $PLUGIN_HOOKS['display_login']['fluxiotheme'] = 'plugin_fluxiotheme_display_login';

    // Injeção de CSS em telas públicas (login / anonymous)
    $PLUGIN_HOOKS['add_css_anonymous_page']['fluxiotheme'] = [
        'css/variables.css',
        'css/login.css'
    ];

    // Injeção de CSS em telas internas (usuários logados)
    $PLUGIN_HOOKS['add_css']['fluxiotheme'] = [
        'css/variables.css',
        'css/app.css'
    ];

    // Favicon customizado via Header Tag
    $favicon_path = '/plugins/fluxiotheme/pics/favicon.ico';
    $real_favicon = GLPI_ROOT . '/plugins/fluxiotheme/public/pics/favicon.ico';
    if (!file_exists($real_favicon)) {
        $favicon_path = '/plugins/fluxiotheme/pics/default_favicon.ico';
    }

    $favicon_tags = [
        [
            'tag' => 'link',
            'properties' => [
                'rel'  => 'shortcut icon',
                'type' => 'image/x-icon',
                'href' => $favicon_path
            ]
        ]
    ];

    $PLUGIN_HOOKS['add_header_tag_anonymous_page']['fluxiotheme'] = $favicon_tags;
    $PLUGIN_HOOKS['add_header_tag']['fluxiotheme'] = $favicon_tags;
}

function plugin_version_fluxiotheme() {
    return [
        'name'         => 'FluxIO Custom Theme',
        'version'      => PLUGIN_FLUXIOTHEME_VERSION,
        'author'       => 'FluxIO / TI SCC',
        'license'      => 'GPLv2+',
        'requirements' => [
            'glpi' => [
                'min' => '10.0.0',
            ]
        ]
    ];
}

function plugin_fluxiotheme_check_prerequisites() {
    return true;
}

function plugin_fluxiotheme_check_config() {
    return true;
}
