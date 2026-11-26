<?php
/**
 * Module: PPB FIP UNM - Shared Admin & Frontend Helpers
 * Description: Helper reusable untuk Media Library Uploader (WP Media), sanitasi tautan Drive/PDF, resolusi URL halaman, dan loader data dosen.
 * Author: Jurusan PPB FIP UNM
 */

if (!defined('ABSPATH')) exit;

if (!function_exists('ppb_admin_enqueue_media_assets')) {
function ppb_admin_enqueue_media_assets() {
    if (function_exists('wp_enqueue_media')) {
        wp_enqueue_media();
    }
}
}

add_action('admin_enqueue_scripts', function($hook_suffix = '') {
    if (!function_exists('wp_enqueue_media')) return;
    $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
    if (strpos($page, 'ppb-') === 0 || $page === 'bk-direktori-dosen') {
        wp_enqueue_media();
        return;
    }
    if (in_array((string) $hook_suffix, array('post.php', 'post-new.php'), true)) {
        $pt = '';
        if (function_exists('get_current_screen')) {
            $scr = get_current_screen();
            if ($scr && !empty($scr->post_type)) {
                $pt = (string) $scr->post_type;
            }
        }
        if ($pt === '' && !empty($_GET['post_type'])) {
            $pt = sanitize_key(wp_unslash($_GET['post_type']));
        }
        if (strpos($pt, 'ppb_') === 0 || $pt === 'dosen') {
            wp_enqueue_media();
        }
    }
});

if (!function_exists('ppb_print_media_picker_script')) {
function ppb_print_media_picker_script() {
    static $printed = false;
    if ($printed) return;
    $printed = true;
    ?>
    <script id="ppb-shared-media-picker-js">
    (function() {
        if (window.ppbMediaPickerInitialized) return;
        window.ppbMediaPickerInitialized = true;

        function updateOpenLink(inputEl) {
            if (!inputEl) return;
            var wrap = inputEl.closest('.ppb-media-input-wrap');
            if (!wrap) return;
            var linkEl = wrap.querySelector('.ppb-media-open-link');
            if (!linkEl) return;
            var val = (inputEl.value || '').trim();
            if (val && val !== '#' && val.indexOf('javascript:') !== 0) {
                linkEl.setAttribute('href', val);
                linkEl.style.display = 'inline-flex';
            } else {
                linkEl.setAttribute('href', '#');
                linkEl.style.display = 'none';
            }
        }

        document.addEventListener('input', function(e) {
            if (e.target && e.target.classList && e.target.classList.contains('ppb-media-url-input')) {
                updateOpenLink(e.target);
            }
        });

        document.addEventListener('click', function(e) {
            var btn = e.target.closest ? e.target.closest('.ppb-media-pick-btn') : null;
            if (!btn) return;
            e.preventDefault();

            var targetId = btn.getAttribute('data-target');
            var inputEl = targetId ? document.getElementById(targetId) : null;
            if (!inputEl && targetId && targetId.charAt(0) === '#') {
                inputEl = document.querySelector(targetId);
            }
            if (!inputEl) {
                var wrap = btn.closest('.ppb-media-input-wrap');
                inputEl = wrap ? wrap.querySelector('input[type="url"], input[type="text"]') : null;
            }

            if (typeof wp === 'undefined' || !wp.media) {
                alert('Pustaka Media WordPress belum dimuat. Pastikan Anda berada di halaman WP Admin.');
                return;
            }

            var title = btn.getAttribute('data-title') || 'Pilih atau Upload Berkas (PDF / DOCX / Gambar)';
            var buttonText = btn.getAttribute('data-button-text') || 'Gunakan Berkas Ini';
            var libraryType = btn.getAttribute('data-library') || '';

            var opts = {
                title: title,
                button: { text: buttonText },
                multiple: false
            };
            if (libraryType) {
                opts.library = { type: libraryType };
            }

            var frame = wp.media(opts);
            frame.on('select', function() {
                var attachment = frame.state().get('selection').first();
                if (!attachment) return;
                var data = attachment.toJSON();
                if (data && data.url && inputEl) {
                    inputEl.value = data.url;
                    inputEl.dispatchEvent(new Event('input', { bubbles: true }));
                    inputEl.dispatchEvent(new Event('change', { bubbles: true }));
                    updateOpenLink(inputEl);
                }
            });
            frame.open();
        });
    })();
    </script>
    <?php
}
}

if (!function_exists('ppb_render_media_url_control')) {
function ppb_render_media_url_control($args = array()) {
    ppb_admin_enqueue_media_assets();

    $id          = isset($args['id']) ? (string) $args['id'] : ('ppb_media_' . wp_rand(100, 9999));
    $name        = isset($args['name']) ? (string) $args['name'] : $id;
    $value       = isset($args['value']) ? (string) $args['value'] : '';
    $placeholder = isset($args['placeholder']) ? (string) $args['placeholder'] : 'https://drive.google.com/... atau upload berkas WP Media';
    $title       = isset($args['title']) ? (string) $args['title'] : 'Pilih atau Upload Berkas (PDF / Dokumen / Gambar)';
    $button_text = isset($args['button_text']) ? (string) $args['button_text'] : 'Gunakan Berkas Ini';
    $btn_label   = isset($args['btn_label']) ? (string) $args['btn_label'] : '📁 Upload / Pilih File';
    $library     = isset($args['library']) ? (string) $args['library'] : '';
    $input_type  = !empty($args['allow_relative']) ? 'text' : 'url';
    $required    = !empty($args['required']) ? ' required' : '';
    $extra_style = isset($args['input_style']) ? (string) $args['input_style'] : '';

    $has_url = ($value !== '' && $value !== '#' && strpos($value, 'javascript:') !== 0);
    ?>
    <div class="ppb-media-input-wrap" style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;width:100%;">
        <input
            type="<?php echo esc_attr($input_type); ?>"
            id="<?php echo esc_attr($id); ?>"
            name="<?php echo esc_attr($name); ?>"
            value="<?php echo esc_attr($value); ?>"
            placeholder="<?php echo esc_attr($placeholder); ?>"
            class="regular-text ppb-media-url-input"
            style="flex:1;min-width:240px;padding:7px 11px;border:1px solid #cbd5e1;border-radius:8px;font-size:13px;<?php echo esc_attr($extra_style); ?>"
            <?php echo $required; ?>
        />
        <button
            type="button"
            class="button ppb-media-pick-btn"
            data-target="<?php echo esc_attr($id); ?>"
            data-title="<?php echo esc_attr($title); ?>"
            data-button-text="<?php echo esc_attr($button_text); ?>"
            data-library="<?php echo esc_attr($library); ?>"
            style="background:#E8F0EA;color:#0D3B22;border:1px solid #B7D0BE;font-weight:700;border-radius:8px;padding:4px 12px;height:36px;display:inline-flex;align-items:center;gap:5px;cursor:pointer;"
        ><?php echo esc_html($btn_label); ?></button>
        <a
            href="<?php echo $has_url ? esc_url($value) : '#'; ?>"
            target="_blank"
            rel="noopener noreferrer"
            class="button ppb-media-open-link"
            style="<?php echo $has_url ? 'display:inline-flex;' : 'display:none;'; ?>align-items:center;gap:4px;height:36px;border-radius:8px;text-decoration:none;"
        >↗ Buka</a>
    </div>
    <?php
    ppb_print_media_picker_script();
}
}

if (!function_exists('ppb_clean_document_url')) {
function ppb_clean_document_url($url) {
    $url = trim((string) $url);
    if ($url === '' || $url === '#') {
        return '';
    }
    if (stripos($url, 'javascript:') === 0) {
        return '';
    }
    if (strpos($url, '/') === 0 && strpos($url, '//') !== 0) {
        return '/' . ltrim(sanitize_text_field($url), '/');
    }
    return esc_url_raw($url);
}
}

if (!function_exists('ppb_get_current_page_slug_path')) {
function ppb_get_current_page_slug_path() {
    if (is_admin()) {
        return '';
    }
    $req = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
    $path = parse_url($req, PHP_URL_PATH);
    if (!is_string($path)) {
        $path = '';
    }
    $home_path = parse_url(home_url('/'), PHP_URL_PATH);
    if (is_string($home_path)) {
        $home_path = trim($home_path, '/');
        $clean = trim($path, '/');
        if ($home_path !== '' && $clean === $home_path) {
            $clean = '';
        } elseif ($home_path !== '' && strpos($clean, $home_path . '/') === 0) {
            $clean = substr($clean, strlen($home_path) + 1);
        }
    } else {
        $clean = trim($path, '/');
    }
    if ($clean === 'yamato') {
        $clean = '';
    } elseif (strpos($clean, 'yamato/') === 0) {
        $clean = substr($clean, 7);
    }
    $clean = trim($clean, '/');

    if ($clean === '' && function_exists('get_post')) {
        $p = get_post();
        if ($p && !empty($p->post_name)) {
            $clean = $p->post_name;
        }
    }
    return $clean;
}
}

if (!function_exists('ppb_get_dosen_directory_cached')) {
function ppb_get_dosen_directory_cached() {
    if (function_exists('bk_fip_load_dosen_data')) {
        $list = bk_fip_load_dosen_data();
        if (is_array($list) && !empty($list)) {
            return $list;
        }
    }
    $cached = get_option('bk_dosen_data_cache', array());
    if (is_array($cached) && !empty($cached)) {
        return $cached;
    }
    $theme_dir = function_exists('get_template_directory') ? get_template_directory() : (dirname(__DIR__, 2) . '/themes/bk-fip-wordpress');
    $json_file = $theme_dir . '/dosen-data.json';
    if (file_exists($json_file)) {
        $raw = json_decode((string) file_get_contents($json_file), true);
        if (is_array($raw)) {
            return $raw;
        }
    }
    return array();
}
}

if (!function_exists('ppb_save_dosen_directory_synced')) {
function ppb_save_dosen_directory_synced($dosen_list) {
    if (!is_array($dosen_list)) return false;

    if (function_exists('bk_fip_save_dosen_data')) {
        bk_fip_save_dosen_data($dosen_list);
        return true;
    }

    $theme_dir = function_exists('get_template_directory') ? get_template_directory() : (dirname(__DIR__, 2) . '/themes/bk-fip-wordpress');
    $json_path = $theme_dir . '/dosen-data.json';
    if (is_dir($theme_dir)) {
        @file_put_contents($json_path, json_encode($dosen_list, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
    update_option('bk_dosen_data_cache', $dosen_list, false);
    update_option('bk_dosen_last_synced', current_time('mysql'), false);
    if (function_exists('bk_fip_rebuild_dosen_roster_html')) {
        bk_fip_rebuild_dosen_roster_html($dosen_list);
    }
    return true;
}
}

if (!function_exists('ppb_replace_div_by_id')) {
function ppb_replace_div_by_id($html, $id, $new_inner_html, $replace_outer = false) {
    $pattern = '/<div\b[^>]*\bid=(["\'])' . preg_quote($id, '/') . '\1[^>]*>/i';
    if (!preg_match($pattern, $html, $m, PREG_OFFSET_CAPTURE)) {
        return $html;
    }

    $open_start = $m[0][1];
    $open_end   = $open_start + strlen($m[0][0]);
    $len        = strlen($html);
    $depth      = 1;
    $pos        = $open_end;

    while ($depth > 0 && $pos < $len) {
        $next_open  = stripos($html, '<div', $pos);
        $next_close = stripos($html, '</div', $pos);

        if ($next_close === false) {
            return $html;
        }

        if ($next_open !== false && $next_open < $next_close) {
            $char_after = ($next_open + 4 < $len) ? $html[$next_open + 4] : '';
            if ($char_after === '>' || $char_after === ' ' || $char_after === "\n" || $char_after === "\r" || $char_after === "\t") {
                $depth++;
            }
            $pos = $next_open + 4;
        } else {
            $depth--;
            if ($depth === 0) {
                if ($replace_outer) {
                    $close_gt = strpos($html, '>', $next_close);
                    $close_end = ($close_gt !== false) ? ($close_gt + 1) : ($next_close + 6);
                    return substr($html, 0, $open_start) . $new_inner_html . substr($html, $close_end);
                }
                return substr($html, 0, $open_end) . $new_inner_html . substr($html, $next_close);
            }
            $pos = $next_close + 5;
        }
    }

    return $html;
}
}

if (!function_exists('ppb_replace_balanced_div_at')) {
function ppb_replace_balanced_div_at($html, $start_offset, $replacement_html) {
    if (!is_string($html) || $start_offset === false || $start_offset < 0) {
        return $html;
    }
    $gt = strpos($html, '>', $start_offset);
    if ($gt === false) {
        return $html;
    }
    $len   = strlen($html);
    $depth = 1;
    $pos   = $gt + 1;

    while ($depth > 0 && $pos < $len) {
        $next_open  = stripos($html, '<div', $pos);
        $next_close = stripos($html, '</div', $pos);

        if ($next_close === false) {
            return $html;
        }

        if ($next_open !== false && $next_open < $next_close) {
            $char_after = ($next_open + 4 < $len) ? $html[$next_open + 4] : '';
            if ($char_after === '>' || $char_after === ' ' || $char_after === "\n" || $char_after === "\r" || $char_after === "\t") {
                $depth++;
            }
            $pos = $next_open + 4;
        } else {
            $depth--;
            if ($depth === 0) {
                $close_end = strpos($html, '>', $next_close);
                if ($close_end === false) {
                    return $html;
                }
                return substr($html, 0, $start_offset) . $replacement_html . substr($html, $close_end + 1);
            }
            $pos = $next_close + 5;
        }
    }

    return $html;
}
}

if (!function_exists('ppb_resolve_image_preview_url')) {
function ppb_resolve_image_preview_url($url) {
    $clean = ppb_clean_document_url($url);
    if ($clean === '') {
        return '';
    }
    if (strpos($clean, 'drive.google.com') !== false) {
        if (preg_match('/\/file\/d\/([a-zA-Z0-9_-]+)/', $clean, $m)) {
            return 'https://drive.google.com/thumbnail?id=' . $m[1] . '&sz=w1200';
        }
        if (preg_match('/[?&]id=([a-zA-Z0-9_-]+)/', $clean, $m)) {
            return 'https://drive.google.com/thumbnail?id=' . $m[1] . '&sz=w1200';
        }
    }
    return $clean;
}
}

if (!function_exists('ppb_render_unit_badge')) {
function ppb_render_unit_badge($unit_slug) {
    $slug = sanitize_key((string) ($unit_slug ?: 's1'));
    $label = function_exists('ppb_get_unit_label') ? ppb_get_unit_label($slug, true) : strtoupper($slug);
    $styles = array(
        'jurusan' => 'background:#0D3B22;color:#F5B82E;border:1px solid #0D3B22;',
        's1'      => 'background:#E8F0EA;color:#0D3B22;border:1px solid #B7D0BE;',
        's2'      => 'background:#FEF3C7;color:#92400E;border:1px solid #FCD34D;',
        's3'      => 'background:#E0E7FF;color:#3730A3;border:1px solid #A5B4FC;',
    );
    $style = $styles[$slug] ?? 'background:#F1F5F9;color:#334155;border:1px solid #CBD5E1;';
    return '<span style="display:inline-flex;align-items:center;padding:2px 9px;border-radius:9999px;font-size:10.5px;font-weight:700;letter-spacing:0.03em;' . $style . '">' . esc_html($label) . '</span>';
}
}

if (!function_exists('ppb_render_unit_segmented_bar')) {
function ppb_render_unit_segmented_bar($units, $active_slug, $opts = array()) {
    $mode       = $opts['mode'] ?? 'js'; // 'js' or 'link'
    $base_url   = $opts['base_url'] ?? '';
    $attr_name  = $opts['attr_name'] ?? 'data-unit-tab';
    $control_id = $opts['id'] ?? 'ppb-unit-seg-bar';
    $pretty_sub = !empty($opts['pretty_suburl']);

    $html = '<div class="bk-seg-control" id="' . esc_attr($control_id) . '" role="tablist" aria-label="Pilih Unit atau Jenjang Studi" style="display:inline-flex;flex-wrap:wrap;gap:4px;">';
    foreach ($units as $slug => $u) {
        $is_active = ($slug === $active_slug);
        $label = is_array($u) ? ($u['tab_label'] ?? ($u['kode'] ?? strtoupper($slug))) : (string) $u;
        if ($mode === 'link' && $base_url !== '') {
            $href = $pretty_sub
                ? trailingslashit($base_url) . rawurlencode($slug) . '/'
                : add_query_arg('jenjang', $slug, $base_url);
            $html .= '<a href="' . esc_url($href) . '" class="bk-seg-btn ' . ($is_active ? 'is-active' : '') . '" ' . $attr_name . '="' . esc_attr($slug) . '" role="tab" aria-selected="' . ($is_active ? 'true' : 'false') . '">' . esc_html($label) . '</a>';
        } else {
            $html .= '<button type="button" class="bk-seg-btn ' . ($is_active ? 'is-active' : '') . '" ' . $attr_name . '="' . esc_attr($slug) . '" role="tab" aria-selected="' . ($is_active ? 'true' : 'false') . '">' . esc_html($label) . '</button>';
        }
    }
    $html .= '</div>';
    return $html;
}
}



