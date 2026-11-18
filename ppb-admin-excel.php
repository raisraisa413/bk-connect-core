<?php
/**
 * Module: PPB FIP UNM - Bulk Import / Export Excel (.xlsx/.csv/.xls XML) & JSON
 * Description: Halaman Hub terpusat, endpoint download binary .xlsx/.csv/.json,
 *              widget inline non-nested untuk semua modul admin, dan banner CPT.
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) exit;

require_once __DIR__ . '/includes/ppb-excel-io.php';
require_once __DIR__ . '/includes/ppb-excel-schemas.php';
require_once __DIR__ . '/includes/ppb-excel-handlers.php';

/* ========================================================================== */
/* 1. URL HELPER, GENERATOR BERKAS & ENDPOINT ADMIN-POST DOWNLOAD             */
/* ========================================================================== */

if (!function_exists('ppb_excel_download_url')) {
function ppb_excel_download_url($dataset_key, $format = 'xlsx', $type = 'export') {
    $url = admin_url(
        'admin-post.php?action=ppb_excel_download&dataset=' . rawurlencode((string) $dataset_key)
        . '&format=' . rawurlencode((string) $format)
        . '&type=' . rawurlencode((string) $type)
    );
    return wp_nonce_url($url, 'ppb_excel_dl_' . sanitize_key($dataset_key));
}
}

if (!function_exists('ppb_excel_generate_download_bytes')) {
function ppb_excel_generate_download_bytes($dataset_key, $format = 'xlsx', $type = 'export') {
    $dataset_key = sanitize_key($dataset_key);
    $format      = strtolower(sanitize_key($format));
    $type        = ($type === 'template') ? 'template' : 'export';

    if (!in_array($format, array('xlsx', 'json', 'csv'), true)) {
        $format = 'xlsx';
    }

    $payload = ppb_excel_build_export_payload($dataset_key, $type);
    if (is_wp_error($payload)) {
        return $payload;
    }

    $date_tag = gmdate('Ymd');
    $base     = 'ppb-' . $dataset_key . '-' . $type . '-' . $date_tag;

    if ($format === 'json') {
        $bytes = ppb_io_json_build($dataset_key, $payload['json_sheets']);
        $mime  = 'application/json; charset=UTF-8';
        $fname = $base . '.json';
    } elseif ($format === 'csv') {
        $csv   = $payload['csv_sheet'];
        $bytes = ppb_io_csv_build($csv['headers'] ?? array(), $csv['rows'] ?? array(), ',');
        $mime  = 'text/csv; charset=UTF-8';
        $fname = $base . '.csv';
    } else {
        $bytes = ppb_io_xlsx_build($payload['xlsx_sheets']);
        $mime  = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        $fname = $base . '.xlsx';
    }

    if ($bytes === '' || $bytes === false) {
        return new WP_Error('build_failed', 'Gagal menyusun berkas ' . strtoupper($format) . ' untuk dataset ' . $dataset_key . '.');
    }

    return array(
        'filename' => $fname,
        'mime'     => $mime,
        'bytes'    => $bytes,
        'format'   => $format,
        'type'     => $type,
    );
}
}

if (!function_exists('ppb_excel_handle_download')) {
function ppb_excel_handle_download() {
    $dataset_key = isset($_GET['dataset']) ? sanitize_key(wp_unslash($_GET['dataset'])) : '';
    $format      = isset($_GET['format']) ? sanitize_key(wp_unslash($_GET['format'])) : 'xlsx';
    $type        = isset($_GET['type']) ? sanitize_key(wp_unslash($_GET['type'])) : 'export';

    $schema = ppb_excel_get_dataset($dataset_key);
    if (!$schema) {
        wp_die('Dataset ekspor tidak ditemukan.', 'Dataset Invalid', array('response' => 404));
    }

    check_admin_referer('ppb_excel_dl_' . $dataset_key);

    if (!current_user_can($schema['capability'] ?? 'edit_posts')) {
        wp_die('Anda tidak memiliki izin mengunduh dataset ini.', 'Akses Ditolak', array('response' => 403));
    }

    $out = ppb_excel_generate_download_bytes($dataset_key, $format, $type);
    if (is_wp_error($out)) {
        wp_die(esc_html($out->get_error_message()), 'Gagal Ekspor', array('response' => 500));
    }

    while (ob_get_level() > 0) {
        @ob_end_clean();
    }
    nocache_headers();
    header('Content-Type: ' . $out['mime']);
    header('Content-Disposition: attachment; filename="' . $out['filename'] . '"');
    header('Content-Length: ' . strlen($out['bytes']));
    echo $out['bytes'];
    exit;
}
}
add_action('admin_post_ppb_excel_download', 'ppb_excel_handle_download');

/* ========================================================================== */
/* 2. PROSESOR UPLOAD INLINE & FORMATTER NOTIFIKASI                           */
/* ========================================================================== */

if (!function_exists('ppb_excel_format_import_notice')) {
function ppb_excel_format_import_notice($res) {
    if (is_wp_error($res)) {
        return '<div class="notice notice-error is-dismissible" style="padding:12px 16px;border-left:4px solid #dc2626;border-radius:8px;">'
            . '<p style="margin:0;"><strong>❌ Gagal Impor Excel/JSON:</strong> ' . esc_html($res->get_error_message()) . '</p>'
            . '</div>';
    }

    $label   = $res['label'] ?? ($res['dataset'] ?? 'Dataset');
    $added   = (int) ($res['added'] ?? 0);
    $updated = (int) ($res['updated'] ?? 0);
    $removed = (int) ($res['removed'] ?? 0);
    $skipped = (int) ($res['skipped'] ?? 0);
    $total   = (int) ($res['total'] ?? 0);
    $fmt     = strtoupper((string) ($res['format'] ?? ''));
    $mode    = ($res['mode'] ?? 'upsert') === 'replace' ? 'Replace (Ganti Semua)' : 'Upsert (Tambah & Perbarui)';
    $warns   = isset($res['warnings']) && is_array($res['warnings']) ? $res['warnings'] : array();

    $summary_parts = array();
    $summary_parts[] = '<strong>+' . $added . '</strong> ditambahkan';
    $summary_parts[] = '<strong>' . $updated . '</strong> diperbarui';
    if ($removed > 0) $summary_parts[] = '<strong>' . $removed . '</strong> dihapus/arsip';
    if ($skipped > 0) $summary_parts[] = '<strong>' . $skipped . '</strong> dilewati';
    $summary_parts[] = 'Total aktif sekarang: <strong>' . $total . '</strong>';

    $html  = '<div class="notice notice-success is-dismissible" style="padding:14px 18px;border-left:4px solid #0D3B22;background:#f0fdf4;border-radius:10px;margin:14px 0;">';
    $html .= '<p style="margin:0 0 6px;color:#0D3B22;font-size:13.5px;"><strong>✅ Impor Berhasil — ' . esc_html($label) . '</strong>'
        . ($fmt !== '' ? ' <span style="background:#dcfce7;color:#166534;padding:1px 7px;border-radius:999px;font-size:11px;font-weight:700;margin-left:6px;">' . esc_html($fmt) . ' · ' . esc_html($mode) . '</span>' : '')
        . '</p>';
    $html .= '<p style="margin:0;color:#1e293b;font-size:12.5px;">' . implode(' &middot; ', $summary_parts) . '</p>';

    if (($res['dataset'] ?? '') === 'matkul') {
        $html .= '<p style="margin:6px 0 0;font-size:12px;color:#166534;">↳ Daftar mata kuliah pada <strong>Profil Dosen</strong>, <strong>Dossier Drawer</strong>, dan <code>/akademik/kurikulum/</code> telah tersinkron otomatis.</p>';
    }

    if (!empty($warns)) {
        $html .= '<details style="margin-top:8px;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:8px 12px;font-size:12px;color:#92400e;">';
        $html .= '<summary style="cursor:pointer;font-weight:700;">⚠️ ' . count($warns) . ' Catatan / Peringatan Baris (klik untuk lihat)</summary>';
        $html .= '<ul style="margin:6px 0 0 18px;">';
        foreach (array_slice($warns, 0, 40) as $w) {
            $html .= '<li>' . esc_html($w) . '</li>';
        }
        if (count($warns) > 40) {
            $html .= '<li><em>... dan ' . (count($warns) - 40) . ' catatan lainnya.</em></li>';
        }
        $html .= '</ul></details>';
    }

    $html .= '</div>';
    return $html;
}
}

if (!function_exists('ppb_excel_handle_inline_upload')) {
function ppb_excel_handle_inline_upload($expected_dataset = '') {
    static $cache = array();

    if (empty($_POST['ppb_excel_import_submit'])) {
        return '';
    }

    $submitted_ds = isset($_POST['ppb_excel_dataset']) ? sanitize_key(wp_unslash($_POST['ppb_excel_dataset'])) : '';
    $expected_key = $expected_dataset !== '' ? sanitize_key($expected_dataset) : '';

    // Jika halaman punya 2 widget (mis. Lab Kegiatan & Lab Lomba), hanya proses widget yang disubmit
    if ($expected_key !== '' && $submitted_ds !== $expected_key) {
        return '';
    }

    $cache_key = $submitted_ds !== '' ? $submitted_ds : '__invalid__';
    if (isset($cache[$cache_key])) {
        return $cache[$cache_key];
    }

    check_admin_referer('ppb_excel_import_action', 'ppb_excel_nonce');

    $schema = ppb_excel_get_dataset($submitted_ds);
    if (!$schema) {
        $cache[$cache_key] = ppb_excel_format_import_notice(new WP_Error('invalid_dataset', 'Dataset target tidak dikenali.'));
        return $cache[$cache_key];
    }

    if (!current_user_can($schema['capability'] ?? 'edit_posts')) {
        $cache[$cache_key] = ppb_excel_format_import_notice(new WP_Error('forbidden', 'Anda tidak memiliki hak akses mengimpor dataset ini.'));
        return $cache[$cache_key];
    }

    $mode     = isset($_POST['ppb_excel_mode']) ? sanitize_key(wp_unslash($_POST['ppb_excel_mode'])) : 'upsert';
    $file_arr = isset($_FILES['ppb_excel_file']) ? $_FILES['ppb_excel_file'] : null;
    $raw_json = isset($_POST['ppb_excel_json_raw']) ? wp_unslash($_POST['ppb_excel_json_raw']) : '';

    $parsed = ppb_io_parse_upload_input($file_arr, $raw_json, $submitted_ds);
    if (is_wp_error($parsed)) {
        $cache[$cache_key] = ppb_excel_format_import_notice($parsed);
        return $cache[$cache_key];
    }

    $result = ppb_excel_import_dataset($submitted_ds, $parsed, $mode);
    $cache[$cache_key] = ppb_excel_format_import_notice($result);
    return $cache[$cache_key];
}
}

/* ========================================================================== */
/* 3. WIDGET INLINE REUSABLE (NON-NESTED STANDALONE FORM)                     */
/* ========================================================================== */

if (!function_exists('ppb_excel_render_inline_widget')) {
function ppb_excel_render_inline_widget($dataset_key, $opts = array()) {
    $dataset_key = sanitize_key($dataset_key);
    $schema      = ppb_excel_get_dataset($dataset_key);
    if (!$schema) return;
    if (!current_user_can($schema['capability'] ?? 'edit_posts')) return;

    $uid           = 'ppb_xl_' . $dataset_key;
    $title         = $opts['title'] ?? ('📥 Import / Export Massal Excel & JSON — ' . $schema['label']);
    $opened        = !empty($opts['opened']);
    $allow_replace = !empty($schema['allow_replace']);
    $count         = ppb_excel_get_dataset_count($dataset_key);
    $hub_url       = admin_url('admin.php?page=ppb-excel-import&dataset=' . rawurlencode($dataset_key));
    ?>
    <div id="<?php echo esc_attr($uid); ?>_box" class="ppb-excel-inline-widget" style="background:#ffffff;border:1.5px solid #C5DACB;border-radius:14px;padding:16px 20px;margin:0 0 18px 0;box-shadow:0 3px 10px rgba(13,59,34,0.05);">
        <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:12px;">
            <div style="flex:1;min-width:260px;">
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:3px;">
                    <span style="background:#E8F0EA;color:#0D3B22;font-size:10.5px;font-weight:800;padding:2px 9px;border-radius:999px;text-transform:uppercase;letter-spacing:.04em;">Import / Export Massal</span>
                    <strong style="color:#0D3B22;font-size:14.5px;"><?php echo esc_html($title); ?></strong>
                    <span style="font-size:11.5px;color:#64748b;font-weight:600;">(<?php echo (int) $count; ?> data aktif)</span>
                </div>
                <p style="margin:0;font-size:12.5px;color:#475569;">
                    Unduh template <code>.xlsx</code> atau ekspor data saat ini, edit massal di Microsoft Excel / Google Sheets, lalu unggah kembali (<code>.xlsx</code>, <code>.csv</code>, <code>.json</code>).
                </p>
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:6px;align-items:center;">
                <a href="<?php echo esc_url(ppb_excel_download_url($dataset_key, 'xlsx', 'template')); ?>" class="button" style="background:#FAF8F5;border-color:#0D3B22;color:#0D3B22;font-weight:700;border-radius:8px;">⬇️ Template .xlsx</a>
                <a href="<?php echo esc_url(ppb_excel_download_url($dataset_key, 'xlsx', 'export')); ?>" class="button" style="background:#E8F0EA;border-color:#C5DACB;color:#0D3B22;font-weight:700;border-radius:8px;">📤 Export .xlsx</a>
                <a href="<?php echo esc_url(ppb_excel_download_url($dataset_key, 'json', 'export')); ?>" class="button" style="border-radius:8px;">📦 Export .json</a>
                <a href="<?php echo esc_url($hub_url); ?>" class="button" style="border-radius:8px;" title="Buka di Pusat Import/Export Excel & JSON">🗂️ Hub Impor</a>
            </div>
        </div>

        <details <?php echo $opened ? 'open' : ''; ?> style="margin-top:12px;border-top:1px dashed #cbd5e1;padding-top:10px;">
            <summary style="cursor:pointer;font-size:12.5px;font-weight:700;color:#0D3B22;user-select:none;">
                ⬆️ Unggah Berkas Excel (<code>.xlsx</code> / <code>.xls</code> / <code>.csv</code>) atau JSON ke Dataset Ini
            </summary>

            <!-- Form Upload Standalone (Dilarang bersarang dalam form lain) -->
            <form method="post" action="" enctype="multipart/form-data" style="margin-top:12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px;" onsubmit="var r = this.querySelector('input[name=ppb_excel_mode][value=replace]'); return !(r && r.checked) || confirm('Mode Replace akan mengganti seluruh isi dataset <?php echo esc_js($schema['label']); ?> dengan isi berkas ini. Lanjutkan?');">
                <?php wp_nonce_field('ppb_excel_import_action', 'ppb_excel_nonce'); ?>
                <input type="hidden" name="ppb_excel_dataset" value="<?php echo esc_attr($dataset_key); ?>" />

                <div style="display:flex;flex-wrap:wrap;gap:14px;align-items:flex-end;">
                    <div style="flex:1.4;min-width:240px;">
                        <label for="<?php echo esc_attr($uid); ?>_file" style="display:block;font-size:12px;font-weight:700;color:#0D3B22;margin-bottom:4px;">
                            Pilih Berkas (.xlsx, .xls XML, .csv, .json)
                        </label>
                        <input type="file" id="<?php echo esc_attr($uid); ?>_file" name="ppb_excel_file" accept=".xlsx,.xls,.csv,.tsv,.json" style="width:100%;background:#fff;border:1px solid #cbd5e1;border-radius:7px;padding:6px 8px;font-size:12.5px;" />
                    </div>

                    <div style="flex:1;min-width:220px;">
                        <span style="display:block;font-size:12px;font-weight:700;color:#0D3B22;margin-bottom:4px;">Mode Impor</span>
                        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;min-height:34px;">
                            <label style="font-size:12.5px;font-weight:600;color:#1e293b;cursor:pointer;">
                                <input type="radio" name="ppb_excel_mode" value="upsert" checked /> Upsert (Tambah &amp; Perbarui)
                            </label>
                            <?php if ($allow_replace) : ?>
                                <label style="font-size:12.5px;font-weight:600;color:#b91c1c;cursor:pointer;">
                                    <input type="radio" name="ppb_excel_mode" value="replace" /> Replace (Ganti Semua)
                                </label>
                            <?php else : ?>
                                <span style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;padding:2px 8px;border-radius:6px;font-size:11px;font-weight:700;">🔒 Mode Upsert Aman (Riwayat Terlindungi)</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div>
                        <button type="submit" name="ppb_excel_import_submit" value="1" class="button button-primary" style="background:#0D3B22;border-color:#0D3B22;font-weight:700;height:36px;padding:0 18px;border-radius:8px;">
                            🚀 Proses &amp; Impor Sekarang
                        </button>
                    </div>
                </div>

                <details style="margin-top:10px;">
                    <summary style="cursor:pointer;font-size:12px;font-weight:600;color:#475569;">📋 Atau Tempel Data JSON Langsung (Paste JSON)</summary>
                    <div style="margin-top:8px;">
                        <textarea name="ppb_excel_json_raw" rows="4" class="widefat" style="font-family:monospace;font-size:12px;background:#fff;border-radius:8px;" placeholder='Tempel array JSON [ { ... }, { ... } ] atau format { "rows": [ ... ] } di sini apabila tidak mengunggah file...'></textarea>
                    </div>
                </details>
            </form>
        </details>
    </div>
    <?php
}
}

/* ========================================================================== */
/* 4. BANNER PINTASAN PADA HALAMAN DAFTAR CPT (edit.php?post_type=...)        */
/* ========================================================================== */

if (!function_exists('ppb_excel_render_cpt_list_banner')) {
function ppb_excel_render_cpt_list_banner() {
    if (!is_admin() || !function_exists('get_current_screen')) return;
    $screen = get_current_screen();
    if (!$screen || ($screen->base ?? '') !== 'edit') return;

    $cpt_map = array(
        'ppb_inventaris_lab' => 'inventaris',
        'ppb_jadwal'         => 'jadwal',
        'ppb_prestasi'       => 'prestasi',
        'ppb_karya'          => 'karya',
        'ppb_dokumen'        => 'dokumen',
        'ppb_template_dok'   => 'template_dok',
    );
    $pt = $screen->post_type ?? '';
    if (!isset($cpt_map[$pt])) return;

    $ds_key = $cpt_map[$pt];
    $schema = ppb_excel_get_dataset($ds_key);
    if (!$schema || !current_user_can($schema['capability'] ?? 'edit_posts')) return;

    $hub_url = admin_url('admin.php?page=ppb-excel-import&dataset=' . rawurlencode($ds_key));
    ?>
    <div class="notice" style="border-left:4px solid #0D3B22;background:#f8fafc;padding:12px 16px;margin:12px 0;border-radius:8px;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;">
        <div>
            <strong style="color:#0D3B22;font-size:13.5px;"><?php echo esc_html($schema['icon'] . ' Impor / Ekspor Massal — ' . $schema['label']); ?></strong>
            <div style="font-size:12px;color:#475569;margin-top:2px;">Tambah atau perbarui puluhan data sekaligus memakai Excel (<code>.xlsx</code> / <code>.csv</code>) atau <code>.json</code> tanpa input satu per satu.</div>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:6px;">
            <a href="<?php echo esc_url($hub_url); ?>" class="button button-primary" style="background:#0D3B22;border-color:#0D3B22;font-weight:700;">📥 Import Massal Excel / JSON</a>
            <a href="<?php echo esc_url(ppb_excel_download_url($ds_key, 'xlsx', 'template')); ?>" class="button">⬇️ Template .xlsx</a>
            <a href="<?php echo esc_url(ppb_excel_download_url($ds_key, 'xlsx', 'export')); ?>" class="button">📤 Export .xlsx</a>
            <a href="<?php echo esc_url(ppb_excel_download_url($ds_key, 'json', 'export')); ?>" class="button">📦 Export .json</a>
        </div>
    </div>
    <?php
}
}
add_action('admin_notices', 'ppb_excel_render_cpt_list_banner');

/* ========================================================================== */
/* 5. HALAMAN HUB TERPUSAT: IMPORT / EXPORT EXCEL & JSON                      */
/* ========================================================================== */

if (!function_exists('ppb_render_excel_import_admin_page')) {
function ppb_render_excel_import_admin_page() {
    if (!current_user_can('edit_posts')) {
        wp_die('Anda tidak memiliki izin mengakses halaman ini.');
    }

    $notice      = ppb_excel_handle_inline_upload('');
    $all_schemas = ppb_excel_get_datasets();

    $selected_key = isset($_REQUEST['dataset']) ? sanitize_key(wp_unslash($_REQUEST['dataset'])) : '';
    if ($selected_key === '' && isset($_POST['ppb_excel_dataset'])) {
        $selected_key = sanitize_key(wp_unslash($_POST['ppb_excel_dataset']));
    }
    if (!isset($all_schemas[$selected_key])) {
        $selected_key = 'matkul';
    }
    $active_schema = $all_schemas[$selected_key];

    $grouped = array();
    foreach ($all_schemas as $k => $sc) {
        if (!current_user_can($sc['capability'] ?? 'edit_posts')) continue;
        $grp = $sc['group'] ?? 'Lainnya';
        $grouped[$grp][$k] = $sc;
    }

    $schemas_meta = array();
    foreach ($all_schemas as $k => $sc) {
        if (!current_user_can($sc['capability'] ?? 'edit_posts')) continue;
        $col_labels = array();
        if (!empty($sc['columns_riset'])) {
            foreach ($sc['columns_riset'] as $c) $col_labels[] = $c['label'];
        } else {
            foreach ($sc['columns'] as $c) $col_labels[] = $c['label'];
        }
        $schemas_meta[$k] = array(
            'key'           => $k,
            'label'         => $sc['label'],
            'icon'          => $sc['icon'],
            'description'   => $sc['description'],
            'allow_replace' => !empty($sc['allow_replace']),
            'columns'       => $col_labels,
            'tpl_xlsx'      => ppb_excel_download_url($k, 'xlsx', 'template'),
            'exp_xlsx'      => ppb_excel_download_url($k, 'xlsx', 'export'),
            'tpl_json'      => ppb_excel_download_url($k, 'json', 'template'),
            'exp_json'      => ppb_excel_download_url($k, 'json', 'export'),
            'exp_csv'       => ppb_excel_download_url($k, 'csv', 'export'),
            'admin_url'     => !empty($sc['admin_url']) ? admin_url($sc['admin_url']) : '',
            'count'         => ppb_excel_get_dataset_count($k),
        );
    }
    ?>
    <div class="wrap" style="max-width:1280px;margin:20px auto;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;color:#1e293b;">
        <style>
            .ppb-xl-hero { background:linear-gradient(135deg,#0D3B22 0%,#185333 100%); color:#fff; border-radius:16px; padding:26px 28px; display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:18px; margin-bottom:20px; box-shadow:0 8px 24px rgba(13,59,34,0.16); }
            .ppb-xl-upload-panel { background:#fff; border:2px solid #0D3B22; border-radius:16px; padding:22px 24px; margin-bottom:26px; box-shadow:0 6px 18px rgba(13,59,34,0.08); }
            .ppb-xl-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(360px, 1fr)); gap:16px; margin-bottom:24px; }
            .ppb-xl-card { background:#fff; border:1px solid #cbd5e1; border-radius:14px; padding:18px; display:flex; flex-direction:column; justify-content:space-between; transition:all .15s ease; }
            .ppb-xl-card.is-selected { border:2px solid #0D3B22; background:#f8fdf9; box-shadow:0 4px 14px rgba(13,59,34,0.1); }
            .ppb-xl-badge { display:inline-block; background:#E8F0EA; color:#0D3B22; font-size:11px; font-weight:700; padding:2px 9px; border-radius:999px; }
        </style>

        <div class="ppb-xl-hero">
            <div style="max-width:780px;">
                <span style="display:inline-block;background:rgba(245,184,46,0.2);border:1px solid rgba(245,184,46,0.45);color:#F5B82E;font-size:11px;font-weight:700;padding:3px 12px;border-radius:999px;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;">Impor &amp; Ekspor Massal Terpadu</span>
                <h1 style="margin:0 0 6px;color:#fff;font-size:24px;font-weight:800;">📥 Pusat Import / Export Data Portal (Excel &amp; JSON)</h1>
                <p style="margin:0;color:rgba(255,255,255,0.88);font-size:13.5px;line-height:1.55;">
                    Kelola puluhan hingga ratusan data Mata Kuliah, Jadwal Ujian, Hibah PNBP, E-Library, Inventaris Lab, Profil Dosen, dan Dokumen Mutu sekaligus. Mendukung berkas asli <strong>Excel (<code>.xlsx</code>)</strong>, <strong>CSV</strong>, <strong>Excel XML (<code>.xls</code>)</strong>, dan <strong>JSON (<code>.json</code> / Paste)</strong> tanpa instalasi pustaka eksternal.
                </p>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <a href="<?php echo esc_url(admin_url('admin.php?page=ppb-mata-kuliah-manager')); ?>" class="button" style="background:#F5B82E;color:#0D3B22;border:none;font-weight:800;border-radius:8px;">📚 Kelola Mata Kuliah</a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=ppb-portal-hub')); ?>" class="button" style="border-radius:8px;">← Pusat Kendali</a>
            </div>
        </div>

        <?php echo $notice; ?>

        <!-- PANEL UNGGAH UTAMA (STANDALONE FORM) -->
        <div class="ppb-xl-upload-panel" id="ppbXlUploadPanel">
            <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:flex-start;gap:14px;margin-bottom:16px;padding-bottom:14px;border-bottom:1px solid #e2e8f0;">
                <div>
                    <span class="ppb-xl-badge">Langkah 1 &rarr; Unduh Template &bull; Langkah 2 &rarr; Edit &bull; Langkah 3 &rarr; Upload</span>
                    <h2 style="margin:6px 0 4px;color:#0D3B22;font-size:18px;" id="ppbXlActiveTitle">
                        <?php echo esc_html($active_schema['icon'] . ' ' . $active_schema['label']); ?>
                    </h2>
                    <p style="margin:0;font-size:13px;color:#475569;" id="ppbXlActiveDesc"><?php echo esc_html($active_schema['description']); ?></p>
                </div>
                <div style="display:flex;flex-wrap:wrap;gap:6px;" id="ppbXlActiveDownloads">
                    <a id="ppbXlBtnTplXlsx" href="<?php echo esc_url(ppb_excel_download_url($selected_key, 'xlsx', 'template')); ?>" class="button" style="background:#FAF8F5;border-color:#0D3B22;color:#0D3B22;font-weight:700;">⬇️ Template Excel (.xlsx)</a>
                    <a id="ppbXlBtnExpXlsx" href="<?php echo esc_url(ppb_excel_download_url($selected_key, 'xlsx', 'export')); ?>" class="button" style="background:#E8F0EA;border-color:#C5DACB;color:#0D3B22;font-weight:700;">📤 Export Data (.xlsx)</a>
                    <a id="ppbXlBtnTplJson" href="<?php echo esc_url(ppb_excel_download_url($selected_key, 'json', 'template')); ?>" class="button">📋 Template .json</a>
                    <a id="ppbXlBtnExpJson" href="<?php echo esc_url(ppb_excel_download_url($selected_key, 'json', 'export')); ?>" class="button">📦 Export .json</a>
                    <a id="ppbXlBtnExpCsv" href="<?php echo esc_url(ppb_excel_download_url($selected_key, 'csv', 'export')); ?>" class="button">📄 CSV</a>
                </div>
            </div>

            <form method="post" action="" enctype="multipart/form-data" onsubmit="var r = document.getElementById('ppb_hub_mode_replace'); return !(r && r.checked) || confirm('Mode Replace akan mengganti seluruh isi dataset yang dipilih dengan isi file ini. Lanjutkan?');">
                <?php wp_nonce_field('ppb_excel_import_action', 'ppb_excel_nonce'); ?>

                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;margin-bottom:14px;">
                    <div>
                        <label for="ppb_hub_dataset_select" style="display:block;font-size:12px;font-weight:700;color:#0D3B22;margin-bottom:4px;">1. Pilih Dataset Tujuan *</label>
                        <select id="ppb_hub_dataset_select" name="ppb_excel_dataset" style="width:100%;height:38px;border-radius:8px;border:1.5px solid #0D3B22;font-weight:700;" onchange="ppbXlSelectDataset(this.value)">
                            <?php foreach ($grouped as $grp_label => $items) : ?>
                                <optgroup label="<?php echo esc_attr($grp_label); ?>">
                                    <?php foreach ($items as $dk => $dsc) : ?>
                                        <option value="<?php echo esc_attr($dk); ?>" <?php selected($selected_key, $dk); ?>>
                                            <?php echo esc_html($dsc['icon'] . ' ' . $dsc['label'] . ' (' . ($schemas_meta[$dk]['count'] ?? 0) . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="ppb_hub_file_input" style="display:block;font-size:12px;font-weight:700;color:#0D3B22;margin-bottom:4px;">2. Pilih Berkas Excel (.xlsx / .xls / .csv) atau .json</label>
                        <input type="file" id="ppb_hub_file_input" name="ppb_excel_file" accept=".xlsx,.xls,.csv,.tsv,.json" style="width:100%;background:#f8fafc;border:1px solid #cbd5e1;border-radius:8px;padding:6px 10px;" />
                    </div>

                    <div>
                        <span style="display:block;font-size:12px;font-weight:700;color:#0D3B22;margin-bottom:4px;">3. Mode Impor</span>
                        <div style="display:flex;gap:12px;align-items:center;min-height:38px;flex-wrap:wrap;">
                            <label style="font-size:12.5px;font-weight:700;color:#0f172a;cursor:pointer;">
                                <input type="radio" name="ppb_excel_mode" id="ppb_hub_mode_upsert" value="upsert" checked /> Upsert (Tambah &amp; Perbarui)
                            </label>
                            <label id="ppb_hub_replace_wrap" style="font-size:12.5px;font-weight:700;color:#b91c1c;cursor:pointer;<?php echo empty($active_schema['allow_replace']) ? 'display:none;' : ''; ?>">
                                <input type="radio" name="ppb_excel_mode" id="ppb_hub_mode_replace" value="replace" /> Replace (Ganti Semua)
                            </label>
                            <span id="ppb_hub_replace_locked" style="font-size:11px;font-weight:700;color:#047857;background:#ecfdf5;border:1px solid #a7f3d0;padding:3px 9px;border-radius:6px;<?php echo !empty($active_schema['allow_replace']) ? 'display:none;' : ''; ?>">
                                🔒 Mode Replace Dikunci untuk Dataset Dosen
                            </span>
                        </div>
                    </div>
                </div>

                <details style="margin-bottom:14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:10px 14px;">
                    <summary style="cursor:pointer;font-size:12.5px;font-weight:700;color:#334155;">📋 Alternatif Cepat: Tempel Teks JSON Langsung (Tanpa Upload File)</summary>
                    <p style="margin:6px 0;font-size:12px;color:#64748b;">Anda dapat mengklik <strong>Template .json</strong> atau <strong>Export .json</strong> di kanan atas, mengeditnya, lalu menempel hasilnya di kotak di bawah ini:</p>
                    <textarea name="ppb_excel_json_raw" rows="6" class="widefat" style="font-family:monospace;font-size:12px;background:#fff;border-radius:8px;" placeholder='[ { "kode": "BK101", "nama": "Dasar-Dasar Bimbingan dan Konseling", "jenjang": "S1", "semester": 1, "sks": 3, "dosen_pengampu": "suciani-latif" } ]'></textarea>
                </details>

                <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:12px;">
                    <div style="font-size:12px;color:#475569;">
                        <strong>Kolom Header Utama:</strong>
                        <span id="ppbXlColsPreview" style="color:#0D3B22;">
                            <?php echo esc_html(implode(' · ', $schemas_meta[$selected_key]['columns'] ?? array())); ?>
                        </span>
                    </div>
                    <button type="submit" name="ppb_excel_import_submit" value="1" class="button button-primary" style="background:#0D3B22;border-color:#0D3B22;font-weight:800;font-size:14px;height:42px;padding:0 24px;border-radius:10px;">
                        🚀 Jalankan Impor ke Database
                    </button>
                </div>
            </form>
        </div>

        <!-- DAFTAR KE-16 DATASET -->
        <?php foreach ($grouped as $grp_label => $items) : ?>
            <h2 style="margin:24px 0 12px;font-size:17px;color:#0D3B22;border-bottom:2px solid #D4E3D8;padding-bottom:6px;"><?php echo esc_html($grp_label); ?></h2>
            <div class="ppb-xl-grid">
                <?php foreach ($items as $dk => $dsc) :
                    $meta = $schemas_meta[$dk];
                    $is_sel = ($dk === $selected_key);
                ?>
                    <div class="ppb-xl-card <?php echo $is_sel ? 'is-selected' : ''; ?>" id="ppb_xl_card_<?php echo esc_attr($dk); ?>">
                        <div>
                            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;margin-bottom:6px;">
                                <strong style="font-size:15px;color:#0f172a;"><?php echo esc_html($meta['icon'] . ' ' . $meta['label']); ?></strong>
                                <span class="ppb-xl-badge"><?php echo (int) $meta['count']; ?> data</span>
                            </div>
                            <p style="margin:0 0 10px;font-size:12.5px;color:#64748b;line-height:1.5;"><?php echo esc_html($meta['description']); ?></p>
                            <div style="font-size:11px;color:#475569;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:7px 10px;margin-bottom:12px;">
                                <strong>Kolom:</strong> <?php echo esc_html(implode(', ', array_slice($meta['columns'], 0, 8))); ?><?php echo count($meta['columns']) > 8 ? ' (+' . (count($meta['columns']) - 8) . ' lagi)' : ''; ?>
                            </div>
                        </div>

                        <div style="display:flex;flex-wrap:wrap;gap:6px;border-top:1px solid #f1f5f9;padding-top:10px;">
                            <button type="button" class="button button-primary button-small" style="background:#0D3B22;border-color:#0D3B22;font-weight:700;" onclick="ppbXlSelectDataset('<?php echo esc_js($dk); ?>', true)">
                                ⬆️ Pilih &amp; Impor
                            </button>
                            <a href="<?php echo esc_url($meta['tpl_xlsx']); ?>" class="button button-small">⬇️ Template .xlsx</a>
                            <a href="<?php echo esc_url($meta['exp_xlsx']); ?>" class="button button-small">📤 Export .xlsx</a>
                            <a href="<?php echo esc_url($meta['exp_json']); ?>" class="button button-small">📦 .json</a>
                            <a href="<?php echo esc_url($meta['exp_csv']); ?>" class="button button-small">📄 CSV</a>
                            <?php if (!empty($meta['admin_url'])) : ?>
                                <a href="<?php echo esc_url($meta['admin_url']); ?>" class="button button-small" title="Buka halaman kelola satuan">↗</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>

        <script>
        (function(){
            var METAS = <?php echo wp_json_encode($schemas_meta); ?>;
            window.ppbXlSelectDataset = function(key, scrollTop) {
                var m = METAS[key];
                if (!m) return;
                var sel = document.getElementById('ppb_hub_dataset_select');
                if (sel && sel.value !== key) sel.value = key;

                document.getElementById('ppbXlActiveTitle').textContent = m.icon + ' ' + m.label;
                document.getElementById('ppbXlActiveDesc').textContent  = m.description;
                document.getElementById('ppbXlBtnTplXlsx').href = m.tpl_xlsx;
                document.getElementById('ppbXlBtnExpXlsx').href = m.exp_xlsx;
                document.getElementById('ppbXlBtnTplJson').href = m.tpl_json;
                document.getElementById('ppbXlBtnExpJson').href = m.exp_json;
                document.getElementById('ppbXlBtnExpCsv').href  = m.exp_csv;
                document.getElementById('ppbXlColsPreview').textContent = (m.columns || []).join(' · ');

                var repWrap   = document.getElementById('ppb_hub_replace_wrap');
                var repLocked = document.getElementById('ppb_hub_replace_locked');
                var rUpsert   = document.getElementById('ppb_hub_mode_upsert');
                var rReplace  = document.getElementById('ppb_hub_mode_replace');
                if (m.allow_replace) {
                    repWrap.style.display = 'inline-block';
                    repLocked.style.display = 'none';
                } else {
                    repWrap.style.display = 'none';
                    repLocked.style.display = 'inline-block';
                    if (rUpsert) rUpsert.checked = true;
                    if (rReplace) rReplace.checked = false;
                }

                var cards = document.querySelectorAll('.ppb-xl-card');
                for (var i = 0; i < cards.length; i++) {
                    cards[i].classList.remove('is-selected');
                }
                var activeCard = document.getElementById('ppb_xl_card_' + key);
                if (activeCard) activeCard.classList.add('is-selected');

                if (scrollTop) {
                    var panel = document.getElementById('ppbXlUploadPanel');
                    if (panel && panel.scrollIntoView) {
                        panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }
            };
        })();
        </script>
    </div>
    <?php
}
}
