<?php
/**
 * ---------------------------------------------------------------------
 * FluxIO Custom Theme - GLPI Plugin
 * Hook definitions and installation routines
 * ---------------------------------------------------------------------
 */

function plugin_fluxiotheme_install() {
    // Configurações padrão
    $default_configs = [
        'primary_color'          => '#2563eb',
        'secondary_color'        => '#163961',
        'card_bg'                => 'rgba(17, 25, 40, 0.72)',
        'card_border'            => 'rgba(255, 255, 255, 0.12)',
        'input_bg'               => 'rgba(255, 255, 255, 0.06)',
        'card_blur'              => '18',
        'card_radius'            => '28',
        'logo_login'             => 'default_logo.png',
        'logo_sidebar'           => 'default_logo.png',
        'logo_sidebar_collapsed' => 'default_logo_collapsed.png',
        'bg_desktop'             => 'default_bg_desktop.jpg',
        'bg_mobile'              => 'background_helpdesk_mobile.png',
        'enable_saml_style'      => '1'
    ];

    $existing = Config::getConfigurationValues('plugin:fluxiotheme');
    $to_save = [];
    foreach ($default_configs as $key => $val) {
        if (!isset($existing[$key])) {
            $to_save[$key] = $val;
        }
    }

    if (!empty($to_save)) {
        Config::setConfigurationValues('plugin:fluxiotheme', $to_save);
    }

    PluginFluxiothemeConfig::generateCssVariables();

    return true;
}

function plugin_fluxiotheme_uninstall() {
    return true;
}

/**
 * Converte URLs do YouTube/Vimeo para formato Embed seguro
 */
function fluxiotheme_get_embed_url(string $url): ?string {
    if (empty($url)) return null;

    // YouTube
    if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $url, $matches)) {
        return 'https://www.youtube-nocookie.com/embed/' . $matches[1];
    }

    // Vimeo
    if (preg_match('/vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)/i', $url, $matches)) {
        return 'https://player.vimeo.com/video/' . end($matches);
    }

    return $url;
}

/**
 * Renderiza o Painel Lateral de Anúncios e/ou o Alerta Rápido no Login
 */
function plugin_fluxiotheme_display_login() {
    $cfg = PluginFluxiothemeConfig::getConfig();
    $pics_url = '/plugins/fluxiotheme/pics/';

    // 1. Painel Lateral de Anúncios & Mídia
    if ($cfg['side_panel_enabled'] == '1') {
        echo "<div class='fluxio-login-sidepanel text-start'>";

        // Cabeçalho do Painel
        if (!empty($cfg['side_panel_title'])) {
            echo "<div class='sidepanel-header mb-3'>";
            echo "  <h3 class='sidepanel-title mb-1 text-white'>" . htmlspecialchars($cfg['side_panel_title'], ENT_QUOTES, 'UTF-8') . "</h3>";
            if (!empty($cfg['side_panel_subtitle'])) {
                echo "  <p class='sidepanel-subtitle text-muted small mb-0'>" . htmlspecialchars($cfg['side_panel_subtitle'], ENT_QUOTES, 'UTF-8') . "</p>";
            }
            echo "</div>";
        }

        // Mídia / Vídeo
        if ($cfg['side_panel_media_type'] === 'upload' && !empty($cfg['side_panel_video_file'])) {
            $video_url = PluginFluxiothemeConfig::getMediaUrl($cfg['side_panel_video_file']);
            echo "<div class='sidepanel-video mb-3'>";
            echo "  <video controls playsinline preload='metadata' style='width: 100%; border-radius: 14px; background: #000; max-height: 230px; box-shadow: 0 4px 15px rgba(0,0,0,0.4);'>";
            echo "    <source src='{$video_url}' type='video/mp4'>";
            echo "    Seu navegador não suporta este vídeo.";
            echo "  </video>";
            echo "</div>";
        } elseif ($cfg['side_panel_media_type'] === 'youtube' && !empty($cfg['side_panel_video_url'])) {
            $embed = fluxiotheme_get_embed_url($cfg['side_panel_video_url']);
            if ($embed) {
                echo "<div class='sidepanel-video mb-3' style='position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 14px; box-shadow: 0 4px 15px rgba(0,0,0,0.4);'>";
                echo "  <iframe src='{$embed}' style='position: absolute; top:0; left: 0; width: 100%; height: 100%; border: 0;' allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture' allowfullscreen></iframe>";
                echo "</div>";
            }
        }

        // Conteúdo do Comunicado
        if (!empty(trim($cfg['side_panel_content']))) {
            echo "<div class='sidepanel-content mb-3' style='color: #cbd5e1; font-size: 0.92rem; line-height: 1.6;'>";
            echo nl2br($cfg['side_panel_content']);
            echo "</div>";
        }

        // Canais de Atendimento Rápido
        $has_whats  = !empty($cfg['side_panel_whatsapp']);
        $has_phone  = !empty($cfg['side_panel_phone']);
        $has_portal = !empty($cfg['side_panel_portal_url']);

        if ($has_whats || $has_phone || $has_portal) {
            echo "<div class='sidepanel-actions pt-3 border-top' style='border-color: rgba(255,255,255,0.1) !important;'>";
            echo "<div class='d-flex flex-column gap-2'>";

            if ($has_whats) {
                $whats_href = (str_starts_with($cfg['side_panel_whatsapp'], 'http'))
                    ? $cfg['side_panel_whatsapp']
                    : 'https://wa.me/' . preg_replace('/[^0-9]/', '', $cfg['side_panel_whatsapp']);

                echo "<a href='{$whats_href}' target='_blank' class='btn btn-success d-flex align-items-center justify-content-center' style='border-radius: 12px; height: 44px; font-weight: 600;'>";
                echo "  <i class='ti ti-brand-whatsapp me-2 fs-2'></i> Suporte via WhatsApp";
                echo "</a>";
            }

            if ($has_phone) {
                $tel = htmlspecialchars($cfg['side_panel_phone'], ENT_QUOTES, 'UTF-8');
                echo "<div class='p-2 text-center text-white' style='background: rgba(255,255,255,0.06); border-radius: 12px; border: 1px solid rgba(255,255,255,0.1); font-size: 0.88rem;'>";
                echo "  <i class='ti ti-phone text-primary me-1'></i> <strong>{$tel}</strong>";
                echo "</div>";
            }

            if ($has_portal) {
                $p_label = !empty($cfg['side_panel_portal_label']) ? htmlspecialchars($cfg['side_panel_portal_label'], ENT_QUOTES, 'UTF-8') : 'Acessar Portal';
                $p_url   = htmlspecialchars($cfg['side_panel_portal_url'], ENT_QUOTES, 'UTF-8');
                echo "<a href='{$p_url}' target='_blank' class='btn btn-outline-info d-flex align-items-center justify-content-center' style='border-radius: 12px; height: 42px;'>";
                echo "  <i class='ti ti-world me-2'></i> {$p_label}";
                echo "</a>";
            }

            echo "</div>";
            echo "</div>";
        }

        echo "</div>"; // fluxio-login-sidepanel

        // Script auxiliar para garantir classe has-sidepanel e integrar botão FAQ no Card de Login
        echo "<script>
        (function() {
            document.body.classList.add('has-sidepanel');
            var adjustLayout = function() {
                document.body.classList.add('has-sidepanel');
                var faq = document.querySelector('.page-anonymous .text-center.mt-4.border-top');
                var colLogin = document.querySelector('.page-anonymous .col-md-5');
                if (faq && colLogin && !colLogin.contains(faq)) {
                    faq.className = 'text-center mt-3';
                    colLogin.appendChild(faq);
                }
            };
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', adjustLayout);
            } else {
                adjustLayout();
            }
        })();
        </script>";
    }

    // 2. Alerta Rápido Dentro do Cartão (se ativado)
    if ($cfg['login_notice_enabled'] == '1' && !empty(trim($cfg['login_notice_text']))) {
        $type = $cfg['login_notice_type'] ?? 'info';
        $icons = [
            'info'    => 'ti ti-info-circle',
            'warning' => 'ti ti-alert-triangle',
            'success' => 'ti ti-circle-check',
            'danger'  => 'ti ti-alert-circle'
        ];
        $icon = $icons[$type] ?? 'ti ti-info-circle';
        $text = nl2br(htmlspecialchars($cfg['login_notice_text'], ENT_QUOTES, 'UTF-8'));

        echo "<div class='alert alert-{$type} d-flex align-items-center mt-3 mb-0' role='alert' style='border-radius: 14px;'>";
        echo "  <i class='{$icon} me-2 fs-2'></i>";
        echo "  <div class='text-start'>{$text}</div>";
        echo "</div>";
    }
}
