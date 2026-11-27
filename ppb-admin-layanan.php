<?php
/**
 * Module: PPB Admin Layanan & Lab BK Suite
 * Description: Modul WP-Admin khusus untuk mengelola Template Dokumen Resmi (CPT), Inventaris Alat Lab BK (CPT), Alur Prosedur SOP (CPT), Pengaturan Layanan & Perlindungan Mahasiswa, serta Shortcodes Scholaro Design System.
 * Version: 1.0.0
 * Author: Jurusan PPB FIP UNM
 */

if (!defined('ABSPATH')) exit;

/* ========================================================================== */
/* 1. REGISTRASI CPT: PPB_TEMPLATE_DOK (Template Dokumen Resmi)               */
/* ========================================================================== */
if (!function_exists('ppb_register_cpt_template_dok')) {
function ppb_register_cpt_template_dok() {
    $labels = [
        'name'               => 'Template Dokumen',
        'singular_name'      => 'Template Dokumen',
        'menu_name'          => 'Template Dokumen',
        'name_admin_bar'     => 'Template Dokumen',
        'add_new'            => 'Tambah Template Baru',
        'add_new_item'       => 'Tambah Template Baru',
        'new_item'           => 'Template Baru',
        'edit_item'          => 'Edit Template',
        'view_item'          => 'Lihat Template',
        'all_items'          => 'Semua Template Dokumen',
        'search_items'       => 'Cari Template Dokumen',
        'not_found'          => 'Belum ada data template dokumen.',
        'not_found_in_trash' => 'Tidak ada template di kotak sampah.'
    ];

    $args = [
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => false,
        'query_var'          => true,
        'rewrite'            => ['slug' => 'template-dokumen'],
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 26,
        'menu_icon'          => 'dashicons-media-document',
        'supports'           => ['title'],
        'show_in_rest'       => false,
    ];

    register_post_type('ppb_template_dok', $args);
}
}
add_action('init', 'ppb_register_cpt_template_dok');

/* ========================================================================== */
/* 2. REGISTRASI CPT: PPB_INVENTARIS_LAB (Inventaris Alat Lab BK)             */
/* ========================================================================== */
if (!function_exists('ppb_register_cpt_inventaris_lab')) {
function ppb_register_cpt_inventaris_lab() {
    $labels = [
        'name'               => 'Inventaris Alat Lab',
        'singular_name'      => 'Inventaris Lab',
        'menu_name'          => 'Inventaris Lab BK',
        'name_admin_bar'     => 'Inventaris Lab BK',
        'add_new'            => 'Tambah Alat/Instrumen',
        'add_new_item'       => 'Tambah Alat/Instrumen Baru',
        'new_item'           => 'Alat Baru',
        'edit_item'          => 'Edit Alat/Instrumen',
        'view_item'          => 'Lihat Alat',
        'all_items'          => 'Semua Inventaris Alat Lab',
        'search_items'       => 'Cari Alat/Instrumen',
        'not_found'          => 'Belum ada data inventaris alat.',
        'not_found_in_trash' => 'Tidak ada alat di kotak sampah.'
    ];

    $args = [
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => false,
        'query_var'          => true,
        'rewrite'            => ['slug' => 'inventaris-lab'],
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 27,
        'menu_icon'          => 'dashicons-forms',
        'supports'           => ['title'],
        'show_in_rest'       => false,
    ];

    register_post_type('ppb_inventaris_lab', $args);
}
}
add_action('init', 'ppb_register_cpt_inventaris_lab');

/* ========================================================================== */
/* 3. REGISTRASI CPT: PPB_ALUR_SOP (Alur Prosedur SOP Layanan)                */
/* ========================================================================== */
if (!function_exists('ppb_register_cpt_alur_sop')) {
function ppb_register_cpt_alur_sop() {
    $labels = [
        'name'               => 'Alur SOP Layanan',
        'singular_name'      => 'Alur SOP Layanan',
        'menu_name'          => 'Alur SOP Layanan',
        'name_admin_bar'     => 'Alur SOP Layanan',
        'add_new'            => 'Tambah Alur Baru',
        'add_new_item'       => 'Tambah Alur Layanan Baru',
        'new_item'           => 'Alur Baru',
        'edit_item'          => 'Edit Alur Layanan',
        'view_item'          => 'Lihat Alur',
        'all_items'          => 'Semua Alur SOP Layanan',
        'search_items'       => 'Cari Alur Layanan',
        'not_found'          => 'Belum ada data alur layanan.',
        'not_found_in_trash' => 'Tidak ada alur di kotak sampah.'
    ];

    $args = [
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => false,
        'query_var'          => true,
        'rewrite'            => ['slug' => 'alur-sop'],
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 28,
        'menu_icon'          => 'dashicons-randomize',
        'supports'           => ['title'],
        'show_in_rest'       => false,
    ];

    register_post_type('ppb_alur_sop', $args);
}
}
add_action('init', 'ppb_register_cpt_alur_sop');

/* ========================================================================== */
/* 4. META BOXES UNTUK TEMPLATE DOKUMEN & ALUR SOP LAYANAN                    */
/* ========================================================================== */
if (!function_exists('ppb_add_template_dok_meta_box')) {
function ppb_add_template_dok_meta_box() {
    add_meta_box(
        'ppb_template_dok_meta',
        '📄 Rincian Berkas Template Resmi',
        'ppb_template_dok_render_meta_box',
        'ppb_template_dok',
        'normal',
        'high'
    );
}
}
add_action('add_meta_boxes', 'ppb_add_template_dok_meta_box');

if (!function_exists('ppb_template_dok_render_meta_box')) {
function ppb_template_dok_render_meta_box($post) {
    wp_nonce_field('ppb_template_dok_save_nonce', 'ppb_template_dok_nonce');
    $kategori     = get_post_meta($post->ID, '_ppb_tpl_kategori', true) ?: 'skripsi';
    $unit_slug    = sanitize_key(get_post_meta($post->ID, '_ppb_tpl_unit', true)) ?: 'semua';
    $format_file  = get_post_meta($post->ID, '_ppb_tpl_format_file', true) ?: 'DOCX';
    $ukuran_kb    = get_post_meta($post->ID, '_ppb_tpl_ukuran_kb', true) ?: '250';
    $nomor_revisi = get_post_meta($post->ID, '_ppb_tpl_nomor_revisi', true) ?: 'REV-2026/A';
    $file_link    = get_post_meta($post->ID, '_ppb_tpl_file_link', true) ?: '';
    $deskripsi    = get_post_meta($post->ID, '_ppb_tpl_deskripsi', true) ?: '';

    $unit_opts = ['semua' => 'Semua Jenjang & Jurusan (Umum)'];
    if (function_exists('ppb_get_unit_options')) {
        $unit_opts = array_merge($unit_opts, ppb_get_unit_options(true));
    }
    ?>
    <style>
        .ppb-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; }
        .ppb-form-group { display: flex; flex-direction: column; }
        .ppb-form-group label { font-weight: 600; margin-bottom: 5px; color: #1e293b; font-size: 13px; }
        .ppb-form-group input, .ppb-form-group select, .ppb-form-group textarea { padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; }
        .ppb-form-group small { color: #64748b; margin-top: 4px; font-size: 11px; }
    </style>
    <div class="ppb-form-grid">
        <div class="ppb-form-group">
            <label for="ppb_tpl_unit">Peruntukan Unit / Jenjang</label>
            <select name="ppb_tpl_unit" id="ppb_tpl_unit">
                <?php foreach ($unit_opts as $uslug => $ulabel) : ?>
                    <option value="<?php echo esc_attr($uslug); ?>" <?php selected($unit_slug, $uslug); ?>><?php echo esc_html($ulabel); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="ppb-form-group">
            <label for="ppb_tpl_kategori">Kategori Dokumen</label>
            <select name="ppb_tpl_kategori" id="ppb_tpl_kategori">
                <option value="skripsi" <?php selected($kategori, 'skripsi'); ?>>Tugas Akhir / Skripsi (S1)</option>
                <option value="tesis" <?php selected($kategori, 'tesis'); ?>>Tesis Pascasarjana (S2)</option>
                <option value="disertasi" <?php selected($kategori, 'disertasi'); ?>>Disertasi Doktoral (S3)</option>
                <option value="izin_penelitian" <?php selected($kategori, 'izin_penelitian'); ?>>Izin Penelitian &amp; Observasi</option>
                <option value="bebas_lab" <?php selected($kategori, 'bebas_lab'); ?>>Bebas Laboratorium</option>
                <option value="peninjauan_ukt" <?php selected($kategori, 'peninjauan_ukt'); ?>>Peninjauan &amp; Penyesuaian UKT</option>
                <option value="akademik_umum" <?php selected($kategori, 'akademik_umum'); ?>>PPL, Magang &amp; Akademik Umum</option>
            </select>
        </div>
        <div class="ppb-form-group">
            <label for="ppb_tpl_format_file">Format Berkas</label>
            <select name="ppb_tpl_format_file" id="ppb_tpl_format_file">
                <option value="DOCX" <?php selected($format_file, 'DOCX'); ?>>Microsoft Word (.docx)</option>
                <option value="PDF" <?php selected($format_file, 'PDF'); ?>>Adobe PDF (.pdf)</option>
                <option value="XLSX" <?php selected($format_file, 'XLSX'); ?>>Microsoft Excel (.xlsx)</option>
            </select>
        </div>
        <div class="ppb-form-group">
            <label for="ppb_tpl_ukuran_kb">Estimasi Ukuran File (KB)</label>
            <input type="text" name="ppb_tpl_ukuran_kb" id="ppb_tpl_ukuran_kb" value="<?php echo esc_attr($ukuran_kb); ?>">
        </div>
        <div class="ppb-form-group" style="grid-column:1/-1;">
            <label for="ppb_tpl_nomor_revisi">Kode / Nomor Revisi</label>
            <input type="text" name="ppb_tpl_nomor_revisi" id="ppb_tpl_nomor_revisi" value="<?php echo esc_attr($nomor_revisi); ?>" placeholder="REV-2026/A">
        </div>
    </div>
    <div class="ppb-form-group" style="margin-bottom: 15px;">
        <label for="ppb_tpl_file_link">Tautan Google Drive / Upload Berkas Dokumen Resmi</label>
        <?php if (function_exists('ppb_render_media_url_control')) : ?>
            <?php ppb_render_media_url_control([
                'id'          => 'ppb_tpl_file_link',
                'name'        => 'ppb_tpl_file_link',
                'value'       => $file_link,
                'placeholder' => 'https://drive.google.com/file/d/.../view?usp=sharing atau upload DOCX/PDF',
                'title'       => 'Pilih / Upload Template Dokumen',
                'btn_label'   => '📁 Upload / Pilih File',
            ]); ?>
        <?php else : ?>
            <input type="url" name="ppb_tpl_file_link" id="ppb_tpl_file_link" value="<?php echo esc_attr($file_link); ?>" placeholder="https://drive.google.com/file/d/.../view?usp=sharing">
        <?php endif; ?>
        <small>Tautan Google Drive atau unggah langsung file template (.docx / .pdf) via Media Library WordPress.</small>
    </div>
    <div class="ppb-form-group">
        <label for="ppb_tpl_deskripsi">Deskripsi &amp; Petunjuk Penggunaan Template</label>
        <textarea name="ppb_tpl_deskripsi" id="ppb_tpl_deskripsi" rows="3"><?php echo esc_textarea($deskripsi); ?></textarea>
    </div>
    <?php
}
}

if (!function_exists('ppb_template_dok_save_meta_box')) {
function ppb_template_dok_save_meta_box($post_id) {
    if (!isset($_POST['ppb_template_dok_nonce']) || !wp_verify_nonce($_POST['ppb_template_dok_nonce'], 'ppb_template_dok_save_nonce')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $fields = [
        'ppb_tpl_unit'         => '_ppb_tpl_unit',
        'ppb_tpl_kategori'     => '_ppb_tpl_kategori',
        'ppb_tpl_format_file'  => '_ppb_tpl_format_file',
        'ppb_tpl_ukuran_kb'    => '_ppb_tpl_ukuran_kb',
        'ppb_tpl_nomor_revisi' => '_ppb_tpl_nomor_revisi',
        'ppb_tpl_file_link'    => '_ppb_tpl_file_link',
        'ppb_tpl_deskripsi'    => '_ppb_tpl_deskripsi',
    ];

    foreach ($fields as $key => $meta_key) {
        if (isset($_POST[$key])) {
            $raw = wp_unslash($_POST[$key]);
            if ($key === 'ppb_tpl_unit') {
                $val = sanitize_key($raw) ?: 'semua';
            } elseif ($key === 'ppb_tpl_file_link') {
                $val = function_exists('ppb_clean_document_url') ? ppb_clean_document_url($raw) : esc_url_raw($raw);
            } elseif ($key === 'ppb_tpl_deskripsi') {
                $val = sanitize_textarea_field($raw);
            } else {
                $val = sanitize_text_field($raw);
            }
            update_post_meta($post_id, $meta_key, $val);
        }
    }
}
}
add_action('save_post_ppb_template_dok', 'ppb_template_dok_save_meta_box');

add_filter('manage_ppb_template_dok_posts_columns', function($cols) {
    return [
        'cb'       => $cols['cb'],
        'title'    => 'Nama Template Dokumen',
        'unit'     => 'Unit / Jenjang',
        'kategori' => 'Kategori',
        'revisi'   => 'Kode Revisi',
        'berkas'   => 'Berkas',
    ];
});
add_action('manage_ppb_template_dok_posts_custom_column', function($col, $post_id) {
    switch ($col) {
        case 'unit':
            $u = get_post_meta($post_id, '_ppb_tpl_unit', true) ?: 'semua';
            echo function_exists('ppb_render_unit_badge') ? ppb_render_unit_badge($u) : esc_html(strtoupper($u));
            break;
        case 'kategori':
            echo esc_html(get_post_meta($post_id, '_ppb_tpl_kategori', true) ?: '-');
            break;
        case 'revisi':
            echo '<code>' . esc_html(get_post_meta($post_id, '_ppb_tpl_nomor_revisi', true) ?: '-') . '</code>';
            break;
        case 'berkas':
            $l = get_post_meta($post_id, '_ppb_tpl_file_link', true);
            echo $l ? '<a href="' . esc_url($l) . '" target="_blank" class="button button-small">Unduh</a>' : '<span style="color:#94a3b8;">-</span>';
            break;
    }
}, 10, 2);

/* Meta Boxes & Columns for PPB_ALUR_SOP */
if (!function_exists('ppb_add_alur_sop_meta_box')) {
function ppb_add_alur_sop_meta_box() {
    add_meta_box(
        'ppb_alur_sop_meta',
        '🔄 Rincian Alur SOP Layanan Akademik & Laboratorium',
        'ppb_alur_sop_render_meta_box',
        'ppb_alur_sop',
        'normal',
        'high'
    );
}
}
add_action('add_meta_boxes', 'ppb_add_alur_sop_meta_box');

if (!function_exists('ppb_alur_sop_render_meta_box')) {
function ppb_alur_sop_render_meta_box($post) {
    wp_nonce_field('ppb_alur_sop_save_nonce', 'ppb_alur_sop_nonce');
    $unit_slug = sanitize_key(get_post_meta($post->ID, '_ppb_sop_unit', true)) ?: 'semua';
    $nomor     = get_post_meta($post->ID, '_ppb_sop_nomor', true) ?: 'SOP-PPB-AKD-01/2025';
    $estimasi  = get_post_meta($post->ID, '_ppb_sop_estimasi', true) ?: '1 - 3 Hari Kerja';
    $dok_link  = get_post_meta($post->ID, '_ppb_sop_dok_link', true) ?: '';

    $unit_opts = ['semua' => 'Semua Jenjang & Jurusan (Umum)'];
    if (function_exists('ppb_get_unit_options')) {
        $unit_opts = array_merge($unit_opts, ppb_get_unit_options(true));
    }
    ?>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;margin-bottom:14px;">
        <div>
            <label style="font-weight:700;display:block;margin-bottom:4px;font-size:12.5px;">Peruntukan Unit / Jenjang</label>
            <select name="ppb_sop_unit" style="width:100%;padding:7px 10px;border:1px solid #cbd5e1;border-radius:6px;">
                <?php foreach ($unit_opts as $uslug => $ulabel) : ?>
                    <option value="<?php echo esc_attr($uslug); ?>" <?php selected($unit_slug, $uslug); ?>><?php echo esc_html($ulabel); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label style="font-weight:700;display:block;margin-bottom:4px;font-size:12.5px;">Nomor Dokumen SOP</label>
            <input type="text" name="ppb_sop_nomor" value="<?php echo esc_attr($nomor); ?>" style="width:100%;padding:7px 10px;border:1px solid #cbd5e1;border-radius:6px;">
        </div>
        <div>
            <label style="font-weight:700;display:block;margin-bottom:4px;font-size:12.5px;">Estimasi SLA Waktu Layanan</label>
            <input type="text" name="ppb_sop_estimasi" value="<?php echo esc_attr($estimasi); ?>" style="width:100%;padding:7px 10px;border:1px solid #cbd5e1;border-radius:6px;">
        </div>
    </div>
    <div style="margin-bottom:14px;">
        <label for="ppb_sop_dok_link" style="font-weight:700;display:block;margin-bottom:4px;font-size:12.5px;">Tautan PDF / Flowchart SOP (Drive / Upload WP Media)</label>
        <?php if (function_exists('ppb_render_media_url_control')) : ?>
            <?php ppb_render_media_url_control(['id' => 'ppb_sop_dok_link', 'name' => 'ppb_sop_dok_link', 'value' => $dok_link, 'placeholder' => 'https://drive.google.com/... atau upload PDF SOP', 'btn_label' => '📁 Upload / Drive']); ?>
        <?php else : ?>
            <input type="url" name="ppb_sop_dok_link" id="ppb_sop_dok_link" value="<?php echo esc_attr($dok_link); ?>" style="width:100%;padding:7px 10px;border:1px solid #cbd5e1;border-radius:6px;">
        <?php endif; ?>
    </div>
    <?php for ($i = 1; $i <= 5; $i++) :
        $step_val = get_post_meta($post->ID, '_ppb_sop_langkah_' . $i, true) ?: '';
    ?>
        <div style="margin-bottom:10px;">
            <label style="font-weight:600;display:block;margin-bottom:3px;font-size:12px;">Langkah <?php echo $i; ?></label>
            <input type="text" name="ppb_sop_langkah_<?php echo $i; ?>" value="<?php echo esc_attr($step_val); ?>" style="width:100%;padding:7px 10px;border:1px solid #cbd5e1;border-radius:6px;">
        </div>
    <?php endfor;
}
}

if (!function_exists('ppb_alur_sop_save_meta_box')) {
function ppb_alur_sop_save_meta_box($post_id) {
    if (!isset($_POST['ppb_alur_sop_nonce']) || !wp_verify_nonce($_POST['ppb_alur_sop_nonce'], 'ppb_alur_sop_save_nonce')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['ppb_sop_unit'])) {
        update_post_meta($post_id, '_ppb_sop_unit', sanitize_key(wp_unslash($_POST['ppb_sop_unit'])) ?: 'semua');
    }
    if (isset($_POST['ppb_sop_nomor'])) {
        update_post_meta($post_id, '_ppb_sop_nomor', sanitize_text_field(wp_unslash($_POST['ppb_sop_nomor'])));
    }
    if (isset($_POST['ppb_sop_estimasi'])) {
        update_post_meta($post_id, '_ppb_sop_estimasi', sanitize_text_field(wp_unslash($_POST['ppb_sop_estimasi'])));
    }
    if (isset($_POST['ppb_sop_dok_link'])) {
        $raw_u = wp_unslash($_POST['ppb_sop_dok_link']);
        $val_u = function_exists('ppb_clean_document_url') ? ppb_clean_document_url($raw_u) : esc_url_raw($raw_u);
        update_post_meta($post_id, '_ppb_sop_dok_link', $val_u);
    }
    for ($i = 1; $i <= 5; $i++) {
        $k = 'ppb_sop_langkah_' . $i;
        if (isset($_POST[$k])) {
            update_post_meta($post_id, '_ppb_sop_langkah_' . $i, sanitize_text_field(wp_unslash($_POST[$k])));
        }
    }
}
}
add_action('save_post_ppb_alur_sop', 'ppb_alur_sop_save_meta_box');

add_filter('manage_ppb_alur_sop_posts_columns', function($cols) {
    return [
        'cb'       => $cols['cb'],
        'title'    => 'Judul SOP Layanan',
        'unit'     => 'Unit / Jenjang',
        'nomor'    => 'Nomor SOP',
        'estimasi' => 'Estimasi SLA',
        'berkas'   => 'Dokumen SOP',
    ];
});
add_action('manage_ppb_alur_sop_posts_custom_column', function($col, $post_id) {
    switch ($col) {
        case 'unit':
            $u = get_post_meta($post_id, '_ppb_sop_unit', true) ?: 'semua';
            echo function_exists('ppb_render_unit_badge') ? ppb_render_unit_badge($u) : esc_html(strtoupper($u));
            break;
        case 'nomor':
            echo '<code>' . esc_html(get_post_meta($post_id, '_ppb_sop_nomor', true) ?: '-') . '</code>';
            break;
        case 'estimasi':
            echo esc_html(get_post_meta($post_id, '_ppb_sop_estimasi', true) ?: '-');
            break;
        case 'berkas':
            $l = get_post_meta($post_id, '_ppb_sop_dok_link', true);
            echo $l ? '<a href="' . esc_url($l) . '" target="_blank" class="button button-small">Unduh SOP</a>' : '<span style="color:#94a3b8;">-</span>';
            break;
    }
}, 10, 2);

/* ========================================================================== */
/* 5. META BOXES, NORMALISASI & FRONTEND SYNC INVENTARIS ALAT LAB BK          */
/* ========================================================================== */
if (!function_exists('ppb_normalize_inventaris_kategori')) {
function ppb_normalize_inventaris_kategori($raw) {
    $val = strtolower(trim((string) $raw));
    if ($val === '' || $val === 'tes_psikologi' || strpos($val, 'psikologi') !== false || strpos($val, 'tes') !== false && strpos($val, 'non') === false) {
        return 'tes_psikologi';
    }
    if ($val === 'instrumen_non_tes' || strpos($val, 'non') !== false || strpos($val, 'aum') !== false || strpos($val, 'dcm') !== false) {
        return 'instrumen_non_tes';
    }
    if ($val === 'fasilitas_mikrokonseling' || strpos($val, 'konseling') !== false || strpos($val, 'mikro') !== false || strpos($val, 'mirror') !== false) {
        return 'fasilitas_mikrokonseling';
    }
    if ($val === 'perangkat_digital' || strpos($val, 'digital') !== false || strpos($val, 'komputer') !== false || strpos($val, 'software') !== false || strpos($val, 'rekam') !== false) {
        return 'perangkat_digital';
    }
    return 'tes_psikologi';
}
}

if (!function_exists('ppb_get_inventaris_kategori_meta')) {
function ppb_get_inventaris_kategori_meta($slug) {
    $slug = ppb_normalize_inventaris_kategori($slug);
    $map = [
        'tes_psikologi' => [
            'label'      => 'Tes Psikologi Baku',
            'badge'      => 'Tes Psikologi Baku',
            'bg'         => '#FEF3C7',
            'color'      => '#92400E',
        ],
        'instrumen_non_tes' => [
            'label'      => 'Instrumen Non-Tes (AUM/DCM)',
            'badge'      => 'Instrumen Non-Tes',
            'bg'         => '#E0F2FE',
            'color'      => '#0369A1',
        ],
        'fasilitas_mikrokonseling' => [
            'label'      => 'Mikrokonseling & Observasi',
            'badge'      => 'Mikrokonseling & Observasi',
            'bg'         => '#EDE9FE',
            'color'      => '#5B21B6',
        ],
        'perangkat_digital' => [
            'label'      => 'Perangkat Digital',
            'badge'      => 'Perangkat Digital',
            'bg'         => '#CCFBF1',
            'color'      => '#115E59',
        ],
    ];
    return $map[$slug] ?? $map['tes_psikologi'];
}
}

if (!function_exists('ppb_normalize_inventaris_kondisi')) {
function ppb_normalize_inventaris_kondisi($raw) {
    $val = strtolower(trim((string) $raw));
    if ($val === 'perlu_kalibrasi' || strpos($val, 'kalibrasi') !== false) {
        return 'perlu_kalibrasi';
    }
    if ($val === 'pemeliharaan' || strpos($val, 'perbaik') !== false || strpos($val, 'rusak') !== false) {
        return 'pemeliharaan';
    }
    return 'baik';
}
}

if (!function_exists('ppb_get_inventaris_kondisi_meta')) {
function ppb_get_inventaris_kondisi_meta($raw) {
    $key = ppb_normalize_inventaris_kondisi($raw);
    if ($key === 'perlu_kalibrasi') {
        return [
            'key'    => 'perlu_kalibrasi',
            'label'  => 'Perlu Kalibrasi',
            'bg'     => '#FEF3C7',
            'color'  => '#92400E',
            'border' => '#FDE68A',
            'icon'   => '⚠',
        ];
    }
    if ($key === 'pemeliharaan') {
        return [
            'key'    => 'pemeliharaan',
            'label'  => 'Pemeliharaan',
            'bg'     => '#FEE2E2',
            'color'  => '#991B1B',
            'border' => '#FECACA',
            'icon'   => '🛠',
        ];
    }
    return [
        'key'    => 'baik',
        'label'  => 'Kondisi: Baik',
        'bg'     => '#ECFDF5',
        'color'  => '#065F46',
        'border' => '#A7F3D0',
        'icon'   => '✓',
    ];
}
}

if (!function_exists('ppb_add_inventaris_lab_meta_box')) {
function ppb_add_inventaris_lab_meta_box() {
    add_meta_box(
        'ppb_inventaris_lab_meta',
        '🧪 Rincian Alat / Instrumen Laboratorium BK',
        'ppb_inventaris_lab_render_meta_box',
        'ppb_inventaris_lab',
        'normal',
        'high'
    );
}
}
add_action('add_meta_boxes', 'ppb_add_inventaris_lab_meta_box');

if (!function_exists('ppb_inventaris_lab_render_meta_box')) {
function ppb_inventaris_lab_render_meta_box($post) {
    wp_nonce_field('ppb_inventaris_lab_save_nonce', 'ppb_inventaris_lab_nonce');
    $kode_alat     = get_post_meta($post->ID, '_ppb_inv_kode_alat', true) ?: 'TES-001';
    $kategori      = ppb_normalize_inventaris_kategori(get_post_meta($post->ID, '_ppb_inv_kategori', true));
    $spesifikasi   = get_post_meta($post->ID, '_ppb_inv_spesifikasi', true) ?: '';
    $jumlah_unit   = get_post_meta($post->ID, '_ppb_inv_jumlah_unit', true) ?: '1 Unit';
    $kondisi       = ppb_normalize_inventaris_kondisi(get_post_meta($post->ID, '_ppb_inv_kondisi', true));
    $lokasi        = get_post_meta($post->ID, '_ppb_inv_lokasi', true) ?: 'Lemari Asesmen Lab BK';
    $manual_link   = get_post_meta($post->ID, '_ppb_inv_manual_link', true) ?: '';
    $foto_link     = get_post_meta($post->ID, '_ppb_inv_foto_link', true) ?: '';
    $keterangan    = get_post_meta($post->ID, '_ppb_inv_keterangan', true) ?: '';
    ?>
    <style>
        .ppb-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; }
        .ppb-form-group { display: flex; flex-direction: column; }
        .ppb-form-group label { font-weight: 600; margin-bottom: 5px; color: #1e293b; font-size: 13px; }
        .ppb-form-group input, .ppb-form-group select, .ppb-form-group textarea { padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; }
        .ppb-form-group small { color: #64748b; margin-top: 4px; font-size: 11px; }
    </style>
    <div class="ppb-form-grid">
        <div class="ppb-form-group">
            <label for="ppb_inv_kode_alat">Kode Registrasi Alat</label>
            <input type="text" name="ppb_inv_kode_alat" id="ppb_inv_kode_alat" value="<?php echo esc_attr($kode_alat); ?>" placeholder="TES-INT-CFIT3">
        </div>
        <div class="ppb-form-group">
            <label for="ppb_inv_kategori">Kategori Alat / Fasilitas</label>
            <select name="ppb_inv_kategori" id="ppb_inv_kategori">
                <option value="tes_psikologi" <?php selected($kategori, 'tes_psikologi'); ?>>Tes Psikologi Baku (Bakat, Minat, Inteligensi)</option>
                <option value="instrumen_non_tes" <?php selected($kategori, 'instrumen_non_tes'); ?>>Instrumen Non-Tes (AUM, DCM, ITP)</option>
                <option value="fasilitas_mikrokonseling" <?php selected($kategori, 'fasilitas_mikrokonseling'); ?>>Fasilitas Mikrokonseling &amp; Observasi</option>
                <option value="perangkat_digital" <?php selected($kategori, 'perangkat_digital'); ?>>Perangkat Digital &amp; Skoring</option>
            </select>
        </div>
        <div class="ppb-form-group">
            <label for="ppb_inv_jumlah_unit">Jumlah Unit / Ketersediaan</label>
            <input type="text" name="ppb_inv_jumlah_unit" id="ppb_inv_jumlah_unit" value="<?php echo esc_attr($jumlah_unit); ?>" placeholder="40 Unit / 15 Paket / 4 Set">
        </div>
        <div class="ppb-form-group">
            <label for="ppb_inv_kondisi">Kondisi Alat</label>
            <select name="ppb_inv_kondisi" id="ppb_inv_kondisi">
                <option value="baik" <?php selected($kondisi, 'baik'); ?>>Baik / Siap Pakai</option>
                <option value="perlu_kalibrasi" <?php selected($kondisi, 'perlu_kalibrasi'); ?>>Perlu Kalibrasi / Penggantian Buku</option>
                <option value="pemeliharaan" <?php selected($kondisi, 'pemeliharaan'); ?>>Dalam Pemeliharaan</option>
            </select>
        </div>
        <div class="ppb-form-group" style="grid-column: 1 / -1;">
            <label for="ppb_inv_lokasi">Lokasi Penyimpanan di Laboratorium</label>
            <input type="text" name="ppb_inv_lokasi" id="ppb_inv_lokasi" value="<?php echo esc_attr($lokasi); ?>" placeholder="Lemari Asesmen A-01 Lab BK FIP UNM">
        </div>
        <div class="ppb-form-group" style="grid-column: 1 / -1;">
            <label for="ppb_inv_spesifikasi">Spesifikasi Kelengkapan Alat</label>
            <input type="text" name="ppb_inv_spesifikasi" id="ppb_inv_spesifikasi" value="<?php echo esc_attr($spesifikasi); ?>" placeholder="Buku Soal, Lembar Jawaban, Kunci Transparan, Norma Baku Indonesia">
        </div>
        <div class="ppb-form-group" style="grid-column: 1 / -1;">
            <label for="ppb_inv_manual_link">Link Manual Book / SOP Alat (Google Drive / Upload PDF)</label>
            <?php if (function_exists('ppb_render_media_url_control')) : ?>
                <?php ppb_render_media_url_control([
                    'id'          => 'ppb_inv_manual_link',
                    'name'        => 'ppb_inv_manual_link',
                    'value'       => $manual_link,
                    'placeholder' => 'https://drive.google.com/file/d/... atau upload PDF SOP',
                    'title'       => 'Pilih / Upload Manual Book SOP Alat',
                    'btn_label'   => '📁 Upload / Pilih File',
                ]); ?>
            <?php else : ?>
                <input type="url" name="ppb_inv_manual_link" id="ppb_inv_manual_link" value="<?php echo esc_attr($manual_link); ?>" placeholder="https://drive.google.com/file/d/...">
            <?php endif; ?>
        </div>
        <div class="ppb-form-group" style="grid-column: 1 / -1;">
            <label for="ppb_inv_foto_link">Link Foto Alat (Google Drive / Upload Gambar)</label>
            <?php if (function_exists('ppb_render_media_url_control')) : ?>
                <?php ppb_render_media_url_control([
                    'id'          => 'ppb_inv_foto_link',
                    'name'        => 'ppb_inv_foto_link',
                    'value'       => $foto_link,
                    'placeholder' => 'https://drive.google.com/file/d/... atau upload foto alat',
                    'title'       => 'Pilih / Upload Foto Alat Lab',
                    'btn_label'   => '📷 Upload / Pilih Foto',
                    'library'     => 'image',
                ]); ?>
            <?php else : ?>
                <input type="url" name="ppb_inv_foto_link" id="ppb_inv_foto_link" value="<?php echo esc_attr($foto_link); ?>" placeholder="https://drive.google.com/file/d/...">
            <?php endif; ?>
        </div>
        <div class="ppb-form-group" style="grid-column: 1 / -1;">
            <label for="ppb_inv_keterangan">Keterangan / Catatan Peminjaman</label>
            <textarea name="ppb_inv_keterangan" id="ppb_inv_keterangan" rows="2"><?php echo esc_textarea($keterangan); ?></textarea>
        </div>
    </div>
    <?php
}
}

if (!function_exists('ppb_inventaris_lab_save_meta_box')) {
function ppb_inventaris_lab_save_meta_box($post_id) {
    if (!isset($_POST['ppb_inventaris_lab_nonce']) || !wp_verify_nonce($_POST['ppb_inventaris_lab_nonce'], 'ppb_inventaris_lab_save_nonce')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $fields = [
        'ppb_inv_kode_alat'   => '_ppb_inv_kode_alat',
        'ppb_inv_kategori'    => '_ppb_inv_kategori',
        'ppb_inv_spesifikasi' => '_ppb_inv_spesifikasi',
        'ppb_inv_jumlah_unit' => '_ppb_inv_jumlah_unit',
        'ppb_inv_kondisi'     => '_ppb_inv_kondisi',
        'ppb_inv_lokasi'      => '_ppb_inv_lokasi',
        'ppb_inv_manual_link' => '_ppb_inv_manual_link',
        'ppb_inv_foto_link'   => '_ppb_inv_foto_link',
        'ppb_inv_keterangan'  => '_ppb_inv_keterangan',
    ];

    foreach ($fields as $key => $meta_key) {
        if (isset($_POST[$key])) {
            $raw = wp_unslash($_POST[$key]);
            if (in_array($key, ['ppb_inv_manual_link', 'ppb_inv_foto_link'], true)) {
                $val = function_exists('ppb_clean_document_url') ? ppb_clean_document_url($raw) : esc_url_raw($raw);
            } elseif ($key === 'ppb_inv_kategori') {
                $val = ppb_normalize_inventaris_kategori($raw);
            } elseif ($key === 'ppb_inv_kondisi') {
                $val = ppb_normalize_inventaris_kondisi($raw);
            } elseif ($key === 'ppb_inv_keterangan') {
                $val = sanitize_textarea_field($raw);
            } else {
                $val = sanitize_text_field($raw);
            }
            update_post_meta($post_id, $meta_key, $val);
        }
    }

    if (class_exists('\\Elementor\\Plugin')) {
        \Elementor\Plugin::$instance->files_manager->clear_cache();
    }
}
}
add_action('save_post_ppb_inventaris_lab', 'ppb_inventaris_lab_save_meta_box');

add_filter('manage_ppb_inventaris_lab_posts_columns', function($cols) {
    return [
        'cb'        => '<input type="checkbox" />',
        'title'     => 'Nama Alat / Fasilitas Lab',
        'inv_kode'  => 'Kode',
        'inv_kat'   => 'Kategori',
        'inv_stok'  => 'Unit & Lokasi',
        'inv_media' => 'SOP & Foto',
        'date'      => 'Diperbarui',
    ];
});
add_action('manage_ppb_inventaris_lab_posts_custom_column', function($col, $post_id) {
    if ($col === 'inv_kode') {
        $kode = get_post_meta($post_id, '_ppb_inv_kode_alat', true) ?: '-';
        $cond = ppb_get_inventaris_kondisi_meta(get_post_meta($post_id, '_ppb_inv_kondisi', true));
        echo '<code style="font-weight:700;color:#0D3B22;">' . esc_html($kode) . '</code><br><span style="font-size:11px;color:' . esc_attr($cond['color']) . ';">' . esc_html($cond['icon'] . ' ' . $cond['label']) . '</span>';
    } elseif ($col === 'inv_kat') {
        $meta = ppb_get_inventaris_kategori_meta(get_post_meta($post_id, '_ppb_inv_kategori', true));
        echo '<span style="display:inline-block;padding:2px 8px;border-radius:6px;font-size:11px;font-weight:700;background:' . esc_attr($meta['bg']) . ';color:' . esc_attr($meta['color']) . ';">' . esc_html($meta['label']) . '</span>';
    } elseif ($col === 'inv_stok') {
        $unit = get_post_meta($post_id, '_ppb_inv_jumlah_unit', true) ?: '-';
        $lok  = get_post_meta($post_id, '_ppb_inv_lokasi', true) ?: '-';
        echo '<strong>' . esc_html($unit) . '</strong><br><span style="font-size:11.5px;color:#64748b;">' . esc_html($lok) . '</span>';
    } elseif ($col === 'inv_media') {
        $man = function_exists('ppb_clean_document_url') ? ppb_clean_document_url(get_post_meta($post_id, '_ppb_inv_manual_link', true)) : '';
        $fot = function_exists('ppb_clean_document_url') ? ppb_clean_document_url(get_post_meta($post_id, '_ppb_inv_foto_link', true)) : '';
        echo ($man !== '' ? '<span style="color:#065F46;font-weight:700;">✓ SOP</span>' : '<span style="color:#94a3b8;">– SOP</span>')
            . ' · '
            . ($fot !== '' ? '<span style="color:#0369A1;font-weight:700;">✓ Foto</span>' : '<span style="color:#94a3b8;">– Foto</span>');
    }
}, 10, 2);

if (!function_exists('ppb_get_inventaris_default_items')) {
function ppb_get_inventaris_default_items() {
    return [
        [
            'kode'           => 'TES-INT-CFIT3',
            'nama'           => 'Culture Fair Intelligence Test (CFIT) Skala 3A & 3B',
            'kategori'       => 'tes_psikologi',
            'kategori_badge' => 'Tes Inteligensi Baku',
            'spesifikasi'    => 'Buku Soal, Lembar Jawaban, Kunci Transparan, Norma Baku Indonesia',
            'jumlah'         => '40 Unit',
            'kondisi'        => 'baik',
            'lokasi'         => 'Lemari Asesmen A-01 Lab BK FIP UNM',
            'keterangan'     => 'Tersedia untuk praktikum mata kuliah Pemahaman Individu Teknik Tes dan layanan asesmen mandiri.',
            'manual_link'    => '',
            'foto_link'      => '',
        ],
        [
            'kode'           => 'TES-BAK-DAT',
            'nama'           => 'Differential Aptitude Test (DAT) Paket Lengkap',
            'kategori'       => 'tes_psikologi',
            'kategori_badge' => 'Tes Bakat Diferensial',
            'spesifikasi'    => 'Tes Penalaran Verbal, Numerik, Abstrak, Mekanik, Relasi Spasial',
            'jumlah'         => '25 Unit',
            'kondisi'        => 'baik',
            'lokasi'         => 'Lemari Asesmen A-02 Lab BK FIP UNM',
            'keterangan'     => 'Digunakan untuk pemetaan arah peminatan karier siswa sekolah menengah.',
            'manual_link'    => '',
            'foto_link'      => '',
        ],
        [
            'kode'           => 'TES-MIN-RMIB',
            'nama'           => 'Rothwell-Miller Interest Blank (RMIB) Pria & Wanita',
            'kategori'       => 'tes_psikologi',
            'kategori_badge' => 'Tes Minat Jabatan',
            'spesifikasi'    => 'Formulir Kategori Pria & Wanita, Lembar Tabulasi Minat Pekerjaan',
            'jumlah'         => '35 Unit',
            'kondisi'        => 'baik',
            'lokasi'         => 'Lemari Asesmen A-03 Lab BK FIP UNM',
            'keterangan'     => 'Instrumen baku eksplorasi pilihan karier dan kesesuaian minat studi lanjut mahasiswa.',
            'manual_link'    => '',
            'foto_link'      => '',
        ],
        [
            'kode'           => 'INS-NON-AUM',
            'nama'           => 'Alat Ungkap Masalah (AUM) Seri Umum & PTSDL',
            'kategori'       => 'instrumen_non_tes',
            'kategori_badge' => 'Instrumen Non-Tes',
            'spesifikasi'    => 'Buku AUM Seri Umum, Seri PTSDL (Format Belajar), Aplikasi Olah Data',
            'jumlah'         => '15 Paket',
            'kondisi'        => 'baik',
            'lokasi'         => 'Lemari Instrumen B-01 Lab BK FIP UNM',
            'keterangan'     => 'Instrumen baku Prof. Prayitno untuk asesmen kebutuhan layanan bimbingan di sekolah.',
            'manual_link'    => '',
            'foto_link'      => '',
        ],
        [
            'kode'           => 'INS-NON-DCM',
            'nama'           => 'Daftar Cek Masalah (DCM) Komprehensif Remaja & Dewasa',
            'kategori'       => 'instrumen_non_tes',
            'kategori_badge' => 'Instrumen Non-Tes',
            'spesifikasi'    => 'Buku Soal DCM Jenjang SMP/SMA/PT, Format Analisis Tabulasi',
            'jumlah'         => '10 Paket',
            'kondisi'        => 'baik',
            'lokasi'         => 'Lemari Instrumen B-02 Lab BK FIP UNM',
            'keterangan'     => 'Alat pemetaan komprehensif 12 bidang permasalahan siswa dan mahasiswa.',
            'manual_link'    => '',
            'foto_link'      => '',
        ],
        [
            'kode'           => 'FAS-MK-OWM',
            'nama'           => 'Ruang Observasi One-Way Mirror & Intercom System',
            'kategori'       => 'fasilitas_mikrokonseling',
            'kategori_badge' => 'Mikrokonseling & Observasi',
            'spesifikasi'    => 'Cermin Satu Arah Kedap Suara, Interkom Audio 2-Arah, AC & Kursi Ergonomis',
            'jumlah'         => '1 Unit Ruang',
            'kondisi'        => 'baik',
            'lokasi'         => 'Laboratorium BK Lantai 1 Gedung FIP UNM',
            'keterangan'     => 'Fasilitas praktikum simulasi konseling individual dengan observasi langsung dari dosen pembimbing tanpa mengganggu konseli.',
            'manual_link'    => '',
            'foto_link'      => '',
        ],
        [
            'kode'           => 'DIG-REC-MA',
            'nama'           => 'Set Perekam Video & Audio Digital Mikrokonseling',
            'kategori'       => 'perangkat_digital',
            'kategori_badge' => 'Perangkat Digital',
            'spesifikasi'    => 'Kamera PTZ Multi-Angle 4K, Wireless Lavalier Mics, Monitor Live Control',
            'jumlah'         => '4 Set',
            'kondisi'        => 'baik',
            'lokasi'         => 'Ruang Kontrol Laboratorium BK FIP UNM',
            'keterangan'     => 'Sistem perekaman resolusi tinggi untuk analisis gestur, micro-expressions, dan keterampilan konselor.',
            'manual_link'    => '',
            'foto_link'      => '',
        ],
    ];
}
}

if (!function_exists('ppb_get_inventaris_lab_items')) {
function ppb_get_inventaris_lab_items() {
    $q = new WP_Query([
        'post_type'      => 'ppb_inventaris_lab',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => ['menu_order' => 'ASC', 'date' => 'ASC'],
    ]);

    if (!$q->have_posts()) {
        return ppb_get_inventaris_default_items();
    }

    $items = [];
    while ($q->have_posts()) {
        $q->the_post();
        $pid       = get_the_ID();
        $kat_slug  = ppb_normalize_inventaris_kategori(get_post_meta($pid, '_ppb_inv_kategori', true));
        $kat_meta  = ppb_get_inventaris_kategori_meta($kat_slug);
        $raw_jml   = trim((string) get_post_meta($pid, '_ppb_inv_jumlah_unit', true));
        $jml_label = $raw_jml !== '' ? (is_numeric($raw_jml) ? ($raw_jml . ' Unit') : $raw_jml) : '1 Unit';
        $man_raw   = get_post_meta($pid, '_ppb_inv_manual_link', true);
        $fot_raw   = get_post_meta($pid, '_ppb_inv_foto_link', true);

        $items[] = [
            'id'             => $pid,
            'kode'           => (string) (get_post_meta($pid, '_ppb_inv_kode_alat', true) ?: ('LAB-' . $pid)),
            'nama'           => (string) get_the_title(),
            'kategori'       => $kat_slug,
            'kategori_badge' => $kat_meta['badge'],
            'spesifikasi'    => (string) (get_post_meta($pid, '_ppb_inv_spesifikasi', true) ?: '-'),
            'jumlah'         => $jml_label,
            'kondisi'        => ppb_normalize_inventaris_kondisi(get_post_meta($pid, '_ppb_inv_kondisi', true)),
            'lokasi'         => (string) (get_post_meta($pid, '_ppb_inv_lokasi', true) ?: 'Laboratorium BK FIP UNM'),
            'keterangan'     => (string) (get_post_meta($pid, '_ppb_inv_keterangan', true) ?: ''),
            'manual_link'    => function_exists('ppb_clean_document_url') ? ppb_clean_document_url($man_raw) : esc_url_raw((string) $man_raw),
            'foto_link'      => function_exists('ppb_clean_document_url') ? ppb_clean_document_url($fot_raw) : esc_url_raw((string) $fot_raw),
        ];
    }
    wp_reset_postdata();

    return $items;
}
}

if (!function_exists('ppb_import_default_inventaris_to_cpt')) {
function ppb_import_default_inventaris_to_cpt() {
    $defaults = ppb_get_inventaris_default_items();
    $existing = get_posts([
        'post_type'      => 'ppb_inventaris_lab',
        'posts_per_page' => -1,
        'post_status'    => ['publish', 'draft', 'pending'],
    ]);
    $known_codes  = [];
    $known_titles = [];
    foreach ($existing as $ex) {
        $c = strtoupper(trim((string) get_post_meta($ex->ID, '_ppb_inv_kode_alat', true)));
        if ($c !== '') $known_codes[$c] = true;
        $known_titles[strtolower(trim((string) $ex->post_title))] = true;
    }

    $inserted = 0;
    foreach ($defaults as $idx => $item) {
        $code_u  = strtoupper(trim($item['kode']));
        $title_l = strtolower(trim($item['nama']));
        if (isset($known_codes[$code_u]) || isset($known_titles[$title_l])) {
            continue;
        }
        $pid = wp_insert_post([
            'post_type'   => 'ppb_inventaris_lab',
            'post_title'  => $item['nama'],
            'post_status' => 'publish',
            'menu_order'  => $idx + 1,
        ]);
        if ($pid && !is_wp_error($pid)) {
            update_post_meta($pid, '_ppb_inv_kode_alat', $item['kode']);
            update_post_meta($pid, '_ppb_inv_kategori', $item['kategori']);
            update_post_meta($pid, '_ppb_inv_spesifikasi', $item['spesifikasi']);
            update_post_meta($pid, '_ppb_inv_jumlah_unit', $item['jumlah']);
            update_post_meta($pid, '_ppb_inv_kondisi', $item['kondisi']);
            update_post_meta($pid, '_ppb_inv_lokasi', $item['lokasi']);
            update_post_meta($pid, '_ppb_inv_manual_link', $item['manual_link']);
            update_post_meta($pid, '_ppb_inv_foto_link', $item['foto_link']);
            update_post_meta($pid, '_ppb_inv_keterangan', $item['keterangan']);
            $inserted++;
        }
    }
    return $inserted;
}
}

if (!function_exists('ppb_render_inventaris_catalog_html')) {
function ppb_render_inventaris_catalog_html() {
    $items = ppb_get_inventaris_lab_items();
    $counts = [
        'semua'                    => count($items),
        'tes_psikologi'            => 0,
        'instrumen_non_tes'        => 0,
        'fasilitas_mikrokonseling' => 0,
        'perangkat_digital'        => 0,
    ];
    foreach ($items as $it) {
        $k = $it['kategori'] ?? 'tes_psikologi';
        if (isset($counts[$k])) $counts[$k]++;
    }

    $pills = [
        'semua'                    => 'Semua Kategori (' . $counts['semua'] . ')',
        'tes_psikologi'            => 'Tes Psikologi Baku (' . $counts['tes_psikologi'] . ')',
        'instrumen_non_tes'        => 'Instrumen Non-Tes (' . $counts['instrumen_non_tes'] . ')',
        'fasilitas_mikrokonseling' => 'Mikrokonseling & Observasi (' . $counts['fasilitas_mikrokonseling'] . ')',
        'perangkat_digital'        => 'Perangkat Digital (' . $counts['perangkat_digital'] . ')',
    ];

    $lokasi_lab = (string) get_option('ppb_layanan_lokasi_lab', 'Gedung Laboratorium BK, FIP UNM Kampus Gunungsari Makassar');
    $jam_lab    = (string) get_option('ppb_layanan_jam_operasional', 'Senin – Jumat, 08.00 – 16.00 WITA');

    ob_start();
    ?>
    <!-- Filter & Search Toolbar (Restored & CPT-Synced) -->
    <div id="ppbInvToolbarWrap" style="background:#ffffff;border:1px solid #E5E7EB;border-radius:24px;padding:22px;margin-bottom:30px">
        <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:16px">
            <div style="display:flex;flex-wrap:wrap;align-items:center;gap:6px;">
                <?php foreach ($pills as $pcat => $plabel) :
                    $is_all = ($pcat === 'semua');
                ?>
                    <button type="button"
                            class="inv-filter-pill<?php echo $is_all ? ' active' : ''; ?>"
                            data-cat="<?php echo esc_attr($pcat); ?>"
                            style="padding:7px 14px;border-radius:10px;font-size:12px;font-weight:<?php echo $is_all ? '700' : '600'; ?>;border:<?php echo $is_all ? 'none' : '1px solid #E2E8F0'; ?>;background:<?php echo $is_all ? '#0D3B22' : '#F8FAFC'; ?>;color:<?php echo $is_all ? '#F5B82E' : '#475569'; ?>;cursor:pointer;white-space:nowrap;">
                        <?php echo esc_html($plabel); ?>
                    </button>
                <?php endforeach; ?>
            </div>
            <div style="flex:1;max-width:340px;min-width:240px;position:relative">
                <input type="search"
                       id="invSearchInput"
                       placeholder="Cari kode, nama alat tes, lokasi..."
                       aria-label="Cari inventaris alat lab"
                       style="width:100%;box-sizing:border-box;padding:9px 14px 9px 38px;border:1px solid #CBD5E1;border-radius:12px;font-size:13px;background:#F8FAFC;color:#1F2937;">
                <span style="position:absolute;left:13px;top:50%;transform:translateY(-50%);color:#94A3B8;pointer-events:none;display:inline-flex;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </span>
            </div>
        </div>
    </div>

    <!-- Grid Inventaris Dinamis dari CPT ppb_inventaris_lab -->
    <div id="invGridContainer" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(330px, 1fr));gap:22px">
        <?php foreach ($items as $it) :
            $kat_meta  = ppb_get_inventaris_kategori_meta($it['kategori']);
            $cond_meta = ppb_get_inventaris_kondisi_meta($it['kondisi']);
            $man_url   = !empty($it['manual_link']) ? $it['manual_link'] : '';
            $foto_raw  = !empty($it['foto_link']) ? $it['foto_link'] : '';
            $foto_prev = ($foto_raw !== '' && function_exists('ppb_resolve_image_preview_url')) ? ppb_resolve_image_preview_url($foto_raw) : $foto_raw;
        ?>
            <div class="inv-card"
                 data-cat="<?php echo esc_attr($it['kategori']); ?>"
                 data-kode="<?php echo esc_attr($it['kode']); ?>"
                 data-nama="<?php echo esc_attr($it['nama']); ?>"
                 data-specs="<?php echo esc_attr($it['spesifikasi']); ?>"
                 data-lokasi="<?php echo esc_attr($it['lokasi']); ?>"
                 data-jumlah="<?php echo esc_attr($it['jumlah']); ?>"
                 data-ket="<?php echo esc_attr($it['keterangan']); ?>"
                 data-manual="<?php echo esc_attr($man_url); ?>"
                 data-foto="<?php echo esc_attr($foto_prev); ?>"
                 data-foto-orig="<?php echo esc_attr($foto_raw); ?>"
                 style="background:#ffffff;border:1px solid #E5E7EB;border-radius:24px;padding:26px;display:flex;flex-direction:column;justify-content:space-between">
                <div>
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                        <span style="font-family:monospace;font-size:11.5px;color:#64748B;font-weight:600"><?php echo esc_html($it['kode']); ?></span>
                        <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:9999px;font-size:10.5px;font-weight:700;background:<?php echo esc_attr($cond_meta['bg']); ?>;color:<?php echo esc_attr($cond_meta['color']); ?>;border:1px solid <?php echo esc_attr($cond_meta['border']); ?>"><?php echo esc_html($cond_meta['icon'] . ' ' . $cond_meta['label']); ?></span>
                    </div>
                    <h3 style="font-family:'Fraunces',serif;font-size:17px;font-weight:700;color:#0D3B22;margin:0 0 6px"><?php echo esc_html($it['nama']); ?></h3>
                    <span style="display:inline-block;padding:2px 8px;border-radius:6px;font-size:10px;font-weight:700;background:<?php echo esc_attr($kat_meta['bg']); ?>;color:<?php echo esc_attr($kat_meta['color']); ?>;margin-bottom:14px"><?php echo esc_html($it['kategori_badge'] ?: $kat_meta['badge']); ?></span>

                    <div style="background:#F8FAFC;border:1px solid #F1F5F9;border-radius:14px;padding:12px 14px;font-size:12px;color:#475569;margin-bottom:14px;display:flex;flex-direction:column;gap:6px">
                        <div><b>Spesifikasi:</b> <?php echo esc_html($it['spesifikasi']); ?></div>
                        <div style="display:flex;justify-content:space-between;align-items:center;padding-top:6px;border-top:1px solid #E2E8F0">
                            <span>Ketersediaan:</span> <strong style="color:#0D3B22;font-size:13px"><?php echo esc_html($it['jumlah']); ?></strong>
                        </div>
                        <div style="font-size:11px;color:#64748B">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            <?php echo esc_html($it['lokasi']); ?>
                        </div>
                    </div>
                    <?php if (!empty($it['keterangan'])) : ?>
                        <p style="font-size:12px;color:#64748B;font-style:italic;margin:0 0 16px"><?php echo esc_html($it['keterangan']); ?></p>
                    <?php endif; ?>
                </div>
                <div style="padding-top:14px;border-top:1px solid #F1F5F9;display:flex;gap:8px">
                    <?php if ($man_url !== '') : ?>
                        <a href="<?php echo esc_url($man_url); ?>" target="_blank" rel="noopener noreferrer" style="flex:1;padding:8px 12px;border-radius:10px;background:#0D3B22;color:#ffffff;font-size:11.5px;font-weight:600;text-align:center;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;">SOP &amp; Manual &#8599;</a>
                    <?php else : ?>
                        <button type="button" class="ppb-inv-action-btn" data-mode="sop" style="flex:1;padding:8px 12px;border-radius:10px;background:#0D3B22;color:#ffffff;font-size:11.5px;font-weight:600;border:none;cursor:pointer">SOP &amp; Manual</button>
                    <?php endif; ?>
                    <button type="button" class="ppb-inv-action-btn" data-mode="foto" style="padding:8px 14px;border-radius:10px;background:#F1F5F9;color:#334155;font-size:11.5px;font-weight:600;border:1px solid #CBD5E1;cursor:pointer"><?php echo $foto_prev !== '' ? '📷 Foto Alat' : 'Detail Alat'; ?></button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div id="invEmptyNotice" style="display:none;background:#ffffff;border:1px dashed #CBD5E1;border-radius:20px;padding:30px;text-align:center;color:#64748B;margin-top:20px;">
        <p style="margin:0 0 10px;font-size:14px;">Tidak ada inventaris alat atau instrumen yang sesuai dengan kata kunci/kategori Anda.</p>
        <button type="button" id="ppbInvResetFilterBtn" style="padding:8px 16px;border-radius:10px;border:1px solid #CBD5E1;background:#F8FAFC;color:#0D3B22;font-weight:700;font-size:12px;cursor:pointer;">Tampilkan Semua Kategori</button>
    </div>

    <!-- Modal Detail SOP & Foto Alat Lab -->
    <div id="ppbInvModal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.68);z-index:99999;align-items:center;justify-content:center;padding:20px;">
        <div style="background:#ffffff;border-radius:20px;max-width:560px;width:100%;max-height:88vh;overflow-y:auto;padding:26px;box-shadow:0 24px 48px rgba(0,0,0,0.28);position:relative;">
            <button type="button" id="ppbInvModalClose" aria-label="Tutup modal" style="position:absolute;top:16px;right:16px;width:32px;height:32px;border-radius:50%;border:1px solid #E2E8F0;background:#F8FAFC;color:#334155;font-weight:700;cursor:pointer;">✕</button>
            <span id="ppbInvModalKode" style="display:inline-block;padding:3px 10px;border-radius:6px;background:#E8F0EA;color:#0D3B22;font-family:monospace;font-size:11.5px;font-weight:700;margin-bottom:8px;"></span>
            <h3 id="ppbInvModalTitle" style="font-family:'Fraunces',serif;font-size:20px;color:#0D3B22;margin:0 0 12px;"></h3>
            <div id="ppbInvModalBody" style="font-size:13px;color:#334155;line-height:1.6;"></div>
        </div>
    </div>

    <script>
    (function(){
        var wrap = document.getElementById('ppbInvToolbarWrap');
        if (!wrap || wrap.getAttribute('data-inv-wired') === '1') return;
        wrap.setAttribute('data-inv-wired', '1');

        var currentCat = 'semua';
        var searchIn = document.getElementById('invSearchInput');
        var grid = document.getElementById('invGridContainer');
        var emptyBox = document.getElementById('invEmptyNotice');
        var pills = wrap.querySelectorAll('.inv-filter-pill');
        var modal = document.getElementById('ppbInvModal');

        function applyInvFilter() {
            var q = searchIn ? searchIn.value.toLowerCase().trim() : '';
            var cards = grid ? grid.querySelectorAll('.inv-card') : [];
            var shown = 0;
            for (var i = 0; i < cards.length; i++) {
                var c = cards[i];
                var cat = c.getAttribute('data-cat') || '';
                var txt = (c.textContent || c.innerText || '').toLowerCase();
                var okCat = (currentCat === 'semua' || cat === currentCat);
                var okQ = (q === '' || txt.indexOf(q) !== -1);
                if (okCat && okQ) {
                    c.style.display = 'flex';
                    shown++;
                } else {
                    c.style.display = 'none';
                }
            }
            if (emptyBox) emptyBox.style.display = (shown === 0) ? 'block' : 'none';
        }

        function setCat(cat) {
            currentCat = cat || 'semua';
            for (var i = 0; i < pills.length; i++) {
                var active = (pills[i].getAttribute('data-cat') === currentCat);
                pills[i].style.background = active ? '#0D3B22' : '#F8FAFC';
                pills[i].style.color = active ? '#F5B82E' : '#475569';
                pills[i].style.border = active ? 'none' : '1px solid #E2E8F0';
                pills[i].style.fontWeight = active ? '700' : '600';
            }
            applyInvFilter();
        }

        for (var i = 0; i < pills.length; i++) {
            pills[i].addEventListener('click', function(){
                setCat(this.getAttribute('data-cat'));
            });
        }
        if (searchIn) searchIn.addEventListener('input', applyInvFilter);

        var resetBtn = document.getElementById('ppbInvResetFilterBtn');
        if (resetBtn) resetBtn.addEventListener('click', function(){
            if (searchIn) searchIn.value = '';
            setCat('semua');
        });

        window.ppbSetInvFilter = setCat;

        function escHtml(s) {
            return String(s == null ? '' : s)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        function openInvModal(card, mode) {
            if (!modal || !card) return;
            var kode = card.getAttribute('data-kode') || '';
            var nama = card.getAttribute('data-nama') || '';
            var specs = card.getAttribute('data-specs') || '';
            var lok = card.getAttribute('data-lokasi') || '';
            var jml = card.getAttribute('data-jumlah') || '';
            var ket = card.getAttribute('data-ket') || '';
            var man = card.getAttribute('data-manual') || '';
            var foto = card.getAttribute('data-foto') || '';
            var fotoOrig = card.getAttribute('data-foto-orig') || foto;

            document.getElementById('ppbInvModalKode').textContent = kode;
            document.getElementById('ppbInvModalTitle').textContent = nama;
            var body = '';
            if (mode === 'foto') {
                if (foto) {
                    body += '<div style="margin-bottom:14px;border-radius:12px;overflow:hidden;background:#F1F5F9;border:1px solid #E2E8F0;text-align:center;">'
                        + '<img src="' + escHtml(foto) + '" alt="' + escHtml(nama) + '" style="max-width:100%;max-height:300px;object-fit:contain;display:block;margin:0 auto;">'
                        + '</div>';
                    if (fotoOrig) {
                        body += '<p style="margin:0 0 12px;"><a href="' + escHtml(fotoOrig) + '" target="_blank" rel="noopener noreferrer" style="color:#0D3B22;font-weight:700;text-decoration:underline;">Buka Foto Resolusi Penuh &#8599;</a></p>';
                    }
                } else {
                    body += '<div style="padding:18px;border-radius:12px;background:#FAF8F5;border:1px dashed #CBD5E1;color:#64748B;margin-bottom:14px;text-align:center;">📷 Foto dokumentasi fisik alat ini belum diunggah oleh Pranata Lab BK. Anda dapat melihat atau meminjam fisik alat di lokasi penyimpanan di bawah.</div>';
                }
            } else {
                body += '<div style="padding:14px;border-radius:12px;background:#F0FDF4;border:1px solid #BBF7D0;color:#166534;margin-bottom:14px;">'
                    + '<strong>Prosedur Peminjaman &amp; SOP Penggunaan Lab BK:</strong><br>'
                    + '1. Pastikan terdaftar pada jadwal praktikum atau ajukan formulir peminjaman alat via SIPADU / Staf Lab BK.<br>'
                    + '2. Pengambilan instrumen tes baku wajib menyertakan surat pengantar Dosen Pengampu MK.<br>'
                    + '3. Jam Layanan Lab: ' + escHtml(<?php echo wp_json_encode($jam_lab); ?>) + '<br>'
                    + '4. Lokasi: ' + escHtml(<?php echo wp_json_encode($lokasi_lab); ?>)
                    + '</div>';
                if (man) {
                    body += '<p style="margin:0 0 12px;"><a href="' + escHtml(man) + '" target="_blank" rel="noopener noreferrer" style="display:inline-block;padding:8px 16px;border-radius:8px;background:#0D3B22;color:#fff;font-weight:700;text-decoration:none;">Buka Berkas SOP / Manual Book &#8599;</a></p>';
                }
            }
            body += '<div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:12px;padding:12px 14px;display:flex;flex-direction:column;gap:6px;">'
                + '<div><strong>Spesifikasi:</strong> ' + escHtml(specs) + '</div>'
                + '<div><strong>Ketersediaan:</strong> ' + escHtml(jml) + '</div>'
                + '<div><strong>Lokasi Simpan:</strong> ' + escHtml(lok) + '</div>'
                + (ket ? '<div><strong>Catatan:</strong> ' + escHtml(ket) + '</div>' : '')
                + '</div>';

            document.getElementById('ppbInvModalBody').innerHTML = body;
            modal.style.display = 'flex';
        }

        if (grid) {
            grid.addEventListener('click', function(e){
                var btn = e.target.closest('.ppb-inv-action-btn');
                if (!btn) return;
                openInvModal(btn.closest('.inv-card'), btn.getAttribute('data-mode'));
            });
        }
        var cls = document.getElementById('ppbInvModalClose');
        if (cls) cls.addEventListener('click', function(){ modal.style.display = 'none'; });
        if (modal) modal.addEventListener('click', function(e){ if (e.target === modal) modal.style.display = 'none'; });
    })();
    </script>
    <?php
    return ob_get_clean();
}
}

if (!function_exists('ppb_shortcode_inventaris_lab')) {
function ppb_shortcode_inventaris_lab($atts = []) {
    return ppb_render_inventaris_catalog_html();
}
}
add_shortcode('ppb_inventaris_lab', 'ppb_shortcode_inventaris_lab');

if (!function_exists('ppb_inventaris_sync_content')) {
function ppb_inventaris_sync_content($content) {
    if (!is_string($content) || $content === '' || (function_exists('is_admin') && is_admin())) {
        return $content;
    }

    $slug_path = function_exists('ppb_get_current_page_slug_path') ? ppb_get_current_page_slug_path() : '';
    $is_inv = ($slug_path === 'lab-bk/inventaris' || $slug_path === 'inventaris' || strpos($content, 'id="invGridContainer"') !== false);
    if (!$is_inv) {
        return $content;
    }

    if (strpos($content, 'id="ppbInvToolbarWrap"') !== false) {
        return $content;
    }

    // Hapus toolbar statis lama yang input pencariannya terstrip wp_kses_post
    $pill_pos = strpos($content, 'inv-filter-pill');
    if ($pill_pos !== false && function_exists('ppb_replace_balanced_div_at')) {
        $before = substr($content, 0, $pill_pos);
        $d1 = strrpos($before, '<div');
        if ($d1 !== false) {
            $d2 = strrpos(substr($before, 0, $d1), '<div');
            if ($d2 !== false) {
                $outer = strrpos(substr($before, 0, $d2), '<div');
                if ($outer !== false) {
                    $content = ppb_replace_balanced_div_at($content, $outer, '');
                }
            }
        }
    }

    // Ganti #invGridContainer dengan toolbar & grid dinamis CPT ppb_inventaris_lab
    $grid_pos = strpos($content, 'id="invGridContainer"');
    if ($grid_pos !== false && function_exists('ppb_replace_balanced_div_at')) {
        $grid_open = strrpos(substr($content, 0, $grid_pos), '<div');
        if ($grid_open !== false) {
            $content = ppb_replace_balanced_div_at($content, $grid_open, ppb_render_inventaris_catalog_html());
        }
    }

    return $content;
}
}
add_filter('the_content', 'ppb_inventaris_sync_content', 12);

/* ========================================================================== */
/* 5b. DATA DEFAULTS & GETTERS: KEGIATAN LAB, LOMBA LAB & SARPRAS             */
/* ========================================================================== */
if (!function_exists('ppb_get_lab_kegiatan_defaults')) {
function ppb_get_lab_kegiatan_defaults() {
    return [
        [
            'judul'     => 'Praktikum Micro-Counseling & Simulasi Konseling Individual',
            'periode'   => 'Semester Gasal & Genap 2024/2025',
            'deskripsi' => 'Pelatihan intensif keterampilan dasar komunikasi konseling, attending, empathy, dan teknik intervensi kognitif-behavioral di ruang one-way mirror Lab BK.',
            'peserta'   => 'Mahasiswa Angkatan 2022 & 2023',
            'status'    => 'Rutin Berkelanjutan',
            'dok_url'   => '',
        ],
        [
            'judul'     => 'Workshop Administrasi & Skoring Tes Psikologi Terstandar',
            'periode'   => 'Oktober – November 2024',
            'deskripsi' => 'Pelatihan penggunaan instrumen tes bakat, minat, inteligensi (DAT, RMIB, IST) serta tes inventori kepribadian untuk asesmen BK sekolah.',
            'peserta'   => '65 Mahasiswa Lab BK',
            'status'    => 'Selesai Dilaksanakan',
            'dok_url'   => '',
        ],
        [
            'judul'     => 'Program Duta Peer Counselor & Sahabat Sebaya Kampus',
            'periode'   => 'Maret – Juni 2024',
            'deskripsi' => 'Kaderisasi mahasiswa konselor sebaya untuk memberikan pertolongan pertama psikologis (Psychological First Aid) bagi civitas akademika UNM.',
            'peserta'   => '40 Mahasiswa Peer Counselor',
            'status'    => 'Aktif Berjalan',
            'dok_url'   => '',
        ],
        [
            'judul'     => 'Pengembangan Media BK Digital & Virtual Reality Therapy',
            'periode'   => 'Agustus 2024',
            'deskripsi' => 'Eksperimen inovasi pemanfaatan media interaktif dan simulasi relaksasi berbasis teknologi digital di Laboratorium BK FIP UNM.',
            'peserta'   => 'Tim Pengembang Lab BK',
            'status'    => 'Tahap Uji Coba',
            'dok_url'   => '',
        ],
    ];
}
}

if (!function_exists('ppb_get_lab_kegiatan_list')) {
function ppb_get_lab_kegiatan_list() {
    $saved = get_option('ppb_lab_kegiatan_list', []);
    return (is_array($saved) && !empty($saved)) ? array_values($saved) : ppb_get_lab_kegiatan_defaults();
}
}

if (!function_exists('ppb_get_lab_lomba_defaults')) {
function ppb_get_lab_lomba_defaults() {
    return [
        [
            'nama'          => 'Lomba Media Bimbingan dan Konseling Nasional (LMBKN) 2024',
            'penyelenggara' => 'Laboratorium BK & Himpunan Mahasiswa BK FIP UNM',
            'kategori'      => 'Tingkat Nasional',
            'prestasi'      => 'Penyelenggara & Juara 1 Kategori Media Interaktif Digital',
            'lingkup'       => 'Non-Akademik / Kreativitas Inovatif',
            'dok_url'       => '',
        ],
        [
            'nama'          => 'Kompetisi Video Simulasi Konseling Kreatif 2023',
            'penyelenggara' => 'Asosiasi Bimbingan dan Konseling Indonesia (ABKIN)',
            'kategori'      => 'Tingkat Nasional',
            'prestasi'      => 'Juara 2 Kategori Simulasi Konseling Kelompok',
            'lingkup'       => 'Keterampilan Konseling Terapan',
            'dok_url'       => '',
        ],
        [
            'nama'          => 'Festival Cipta Puisi & Poster Edukasi Mental Health 2024',
            'penyelenggara' => 'Laboratorium BK FIP UNM',
            'kategori'      => 'Tingkat Universitas / Regional',
            'prestasi'      => 'Penyelenggara & Publikasi 50 Karya Terbaik',
            'lingkup'       => 'Seni & Promosi Kesehatan Mental',
            'dok_url'       => '',
        ],
    ];
}
}

if (!function_exists('ppb_get_lab_lomba_list')) {
function ppb_get_lab_lomba_list() {
    $saved = get_option('ppb_lab_lomba_list', []);
    return (is_array($saved) && !empty($saved)) ? array_values($saved) : ppb_get_lab_lomba_defaults();
}
}

if (!function_exists('ppb_get_sarpras_defaults')) {
function ppb_get_sarpras_defaults() {
    return [
        [
            'kategori'  => 'Smart Classroom',
            'nama'      => 'Ruang Perkuliahan Modern BK',
            'deskripsi' => 'Dilengkapi smart TV interaktif, AC, proyektor nirkabel, pencahayaan alami, dan koneksi internet kampus berkecepatan tinggi.',
            'kapasitas' => '40–50 Mahasiswa',
            'lokasi'    => 'Gedung Kuliah PPB FIP UNM Gunungsari',
            'icon'      => '🏫',
            'foto_url'  => '',
            'dok_url'   => '',
            'tema'      => 'green',
        ],
        [
            'kategori'  => 'Laboratorium BK',
            'nama'      => 'Sarana & Prasarana Lab BK',
            'deskripsi' => 'Pusat administrasi instrumen tes psikologi terstandar, lemari inventaris alat tes, dan komputer pengolahan data bimbingan digital.',
            'kapasitas' => '12 Workstation & Ruang Tes',
            'lokasi'    => 'Gedung Laboratorium BK Lantai 1',
            'icon'      => '🔬',
            'foto_url'  => '',
            'dok_url'   => '',
            'tema'      => 'gold',
        ],
        [
            'kategori'  => 'Ruang Konseling Khusus',
            'nama'      => 'Ruang Konseling & Relaksasi',
            'deskripsi' => 'Ruang kedap suara dengan sistem One-Way Mirror, sofa ergonomis, dan fasilitas audio-video recording untuk supervisi konseling.',
            'kapasitas' => '4 Bilik Individual + 2 Ruang Kelompok',
            'lokasi'    => 'Lantai 1 Gedung Lab BK FIP UNM',
            'icon'      => '🛋️',
            'foto_url'  => '',
            'dok_url'   => '',
            'tema'      => 'indigo',
        ],
    ];
}
}

if (!function_exists('ppb_get_sarpras_list')) {
function ppb_get_sarpras_list() {
    $saved = get_option('ppb_sarpras_list', []);
    return (is_array($saved) && !empty($saved)) ? array_values($saved) : ppb_get_sarpras_defaults();
}
}

/* ========================================================================== */
/* 6. PENGATURAN LAYANAN, LAB BK (KEGIATAN/LOMBA) & SARANA PRASARANA          */
/* ========================================================================== */
if (!function_exists('ppb_register_layanan_settings_menu')) {
function ppb_register_layanan_settings_menu() {
    add_submenu_page(
        'edit.php?post_type=ppb_template_dok',
        'Pengaturan Layanan & Lab BK',
        'Pengaturan Layanan',
        'manage_options',
        'ppb-layanan-settings',
        'ppb_render_layanan_settings_page'
    );
}
}
// Handled by ppb-portal-hub
// add_action('admin_menu', 'ppb_register_layanan_settings_menu');

if (!function_exists('ppb_register_admin_lab_role')) {
function ppb_register_admin_lab_role() {
    $admin_role = get_role('administrator');
    if ($admin_role && !$admin_role->has_cap('ppb_manage_lab')) {
        $admin_role->add_cap('ppb_manage_lab');
    }
    if (!get_role('ppb_admin_lab')) {
        add_role('ppb_admin_lab', 'Admin Laboratorium BK', [
            'read'           => true,
            'upload_files'   => true,
            'ppb_manage_lab' => true,
        ]);
    }
}
}
add_action('init', 'ppb_register_admin_lab_role');

if (!function_exists('ppb_register_dedicated_lab_admin_menu')) {
function ppb_register_dedicated_lab_admin_menu() {
    // Hanya tampilkan menu khusus ini jika user bukan Administrator penuh (atau tetap sediakan akses cepat Lab BK)
    if (!current_user_can('ppb_manage_lab')) {
        return;
    }
    if (!current_user_can('manage_options')) {
        add_menu_page(
            'Portal Admin Lab BK',
            '🧪 Admin Lab BK',
            'ppb_manage_lab',
            'ppb-lab-admin',
            'ppb_render_dedicated_lab_admin_page',
            'dashicons-welcome-learn-more',
            26
        );
    }
}
}
add_action('admin_menu', 'ppb_register_dedicated_lab_admin_menu');

if (!function_exists('ppb_render_dedicated_lab_admin_page')) {
function ppb_render_dedicated_lab_admin_page() {
    if (!current_user_can('ppb_manage_lab')) {
        wp_die('Anda tidak memiliki izin mengakses Portal Admin Lab BK.');
    }
    $_GET['tab'] = isset($_GET['tab']) && in_array($_GET['tab'], ['lab_bidang', 'lab_kegiatan'], true)
        ? $_GET['tab']
        : 'lab_bidang';
    ppb_render_layanan_settings_page();
}
}

if (!function_exists('ppb_render_layanan_settings_page')) {
function ppb_render_layanan_settings_page() {
    if (!current_user_can('edit_posts') && !current_user_can('ppb_manage_lab')) {
        wp_die('Anda tidak memiliki izin mengakses halaman ini.');
    }
    if (function_exists('ppb_admin_enqueue_media_assets')) {
        ppb_admin_enqueue_media_assets();
    }

    $active_tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'umum';
    if (!in_array($active_tab, ['umum', 'lab_kegiatan', 'sarpras'], true)) {
        $active_tab = 'umum';
    }

    $notice = '';

    if (function_exists('ppb_excel_handle_inline_upload')) {
        $xl_keg = ppb_excel_handle_inline_upload('lab_kegiatan');
        if ($xl_keg !== '') {
            $active_tab = 'lab_kegiatan';
            $notice     = $xl_keg;
        }
        $xl_lmb = ppb_excel_handle_inline_upload('lab_lomba');
        if ($xl_lmb !== '') {
            $active_tab = 'lab_kegiatan';
            $notice     = $xl_lmb;
        }
        $xl_srp = ppb_excel_handle_inline_upload('sarpras');
        if ($xl_srp !== '') {
            $active_tab = 'sarpras';
            $notice     = $xl_srp;
        }
    }

    // Tab 1A: Simpan Pengaturan Umum Layanan
    if (isset($_POST['ppb_save_layanan_options']) && check_admin_referer('ppb_save_layanan_nonce')) {
        $active_tab = 'umum';
        foreach (['ppb_layanan_jam_operasional', 'ppb_layanan_lokasi_lab'] as $opt) {
            if (isset($_POST[$opt])) {
                update_option($opt, sanitize_text_field(wp_unslash($_POST[$opt])), false);
            }
        }
        if (isset($_POST['ppb_layanan_wa_hotline'])) {
            $wa = preg_replace('/\D+/', '', (string) wp_unslash($_POST['ppb_layanan_wa_hotline']));
            update_option('ppb_layanan_wa_hotline', $wa, false);
        }
        foreach (['ppb_layanan_sop_perlindungan_link', 'ppb_layanan_sipadu_url', 'ppb_layanan_survei_url'] as $opt) {
            if (isset($_POST[$opt])) {
                $raw = wp_unslash($_POST[$opt]);
                $val = function_exists('ppb_clean_document_url') ? ppb_clean_document_url($raw) : esc_url_raw($raw);
                update_option($opt, $val, false);
            }
        }
        $notice = '<div class="notice notice-success is-dismissible"><p><strong>✅ Pengaturan Layanan &amp; Hotline Lab BK berhasil disimpan!</strong></p></div>';
    }

    // Tab 1B: Impor 7 Inventaris Bawaan ke CPT ppb_inventaris_lab
    if (isset($_POST['ppb_import_default_inventaris']) && check_admin_referer('ppb_save_layanan_nonce')) {
        $active_tab = 'umum';
        $added = ppb_import_default_inventaris_to_cpt();
        $notice = '<div class="notice notice-success is-dismissible"><p><strong>🧪 ' . (int) $added . ' item inventaris bawaan berhasil diimpor ke CPT Inventaris Lab!</strong> Item yang sudah ada dilewati otomatis.</p></div>';
    }

    // Tab 2A: Simpan Kegiatan & Praktikum Lab BK
    if (isset($_POST['ppb_save_lab_kegiatan']) && check_admin_referer('ppb_lab_kegiatan_nonce_action', 'ppb_lab_kegiatan_nonce')) {
        $active_tab = 'lab_kegiatan';
        $raw_list = isset($_POST['kegiatan']) && is_array($_POST['kegiatan']) ? wp_unslash($_POST['kegiatan']) : [];
        $clean = [];
        foreach ($raw_list as $row) {
            if (!empty($row['delete']) || empty(trim((string) ($row['judul'] ?? '')))) continue;
            $dok = function_exists('ppb_clean_document_url') ? ppb_clean_document_url($row['dok_url'] ?? '') : esc_url_raw($row['dok_url'] ?? '');
            $clean[] = [
                'judul'     => sanitize_text_field($row['judul']),
                'periode'   => sanitize_text_field($row['periode'] ?? ''),
                'deskripsi' => sanitize_textarea_field($row['deskripsi'] ?? ''),
                'peserta'   => sanitize_text_field($row['peserta'] ?? ''),
                'status'    => sanitize_text_field($row['status'] ?? 'Aktif Berjalan'),
                'dok_url'   => $dok,
            ];
        }
        if (!empty($_POST['new_kegiatan']['judul'])) {
            $nr = wp_unslash($_POST['new_kegiatan']);
            $dok = function_exists('ppb_clean_document_url') ? ppb_clean_document_url($nr['dok_url'] ?? '') : esc_url_raw($nr['dok_url'] ?? '');
            $clean[] = [
                'judul'     => sanitize_text_field($nr['judul']),
                'periode'   => sanitize_text_field($nr['periode'] ?? ''),
                'deskripsi' => sanitize_textarea_field($nr['deskripsi'] ?? ''),
                'peserta'   => sanitize_text_field($nr['peserta'] ?? ''),
                'status'    => sanitize_text_field($nr['status'] ?? 'Aktif Berjalan'),
                'dok_url'   => $dok,
            ];
        }
        update_option('ppb_lab_kegiatan_list', array_values($clean), false);
        $notice = '<div class="notice notice-success is-dismissible"><p><strong>✅ Daftar Kegiatan Lab BK (' . count($clean) . ' item) berhasil disimpan!</strong></p></div>';
    }
    if (isset($_POST['ppb_reset_lab_kegiatan']) && check_admin_referer('ppb_lab_kegiatan_nonce_action', 'ppb_lab_kegiatan_nonce')) {
        $active_tab = 'lab_kegiatan';
        delete_option('ppb_lab_kegiatan_list');
        $notice = '<div class="notice notice-warning is-dismissible"><p>Daftar Kegiatan Lab BK dikembalikan ke data bawaan.</p></div>';
    }

    // Tab 2B: Simpan Info Lomba Lab BK
    if (isset($_POST['ppb_save_lab_lomba']) && check_admin_referer('ppb_lab_lomba_nonce_action', 'ppb_lab_lomba_nonce')) {
        $active_tab = 'lab_kegiatan';
        $raw_list = isset($_POST['lomba']) && is_array($_POST['lomba']) ? wp_unslash($_POST['lomba']) : [];
        $clean = [];
        foreach ($raw_list as $row) {
            if (!empty($row['delete']) || empty(trim((string) ($row['nama'] ?? '')))) continue;
            $dok = function_exists('ppb_clean_document_url') ? ppb_clean_document_url($row['dok_url'] ?? '') : esc_url_raw($row['dok_url'] ?? '');
            $clean[] = [
                'nama'          => sanitize_text_field($row['nama']),
                'penyelenggara' => sanitize_text_field($row['penyelenggara'] ?? ''),
                'kategori'      => sanitize_text_field($row['kategori'] ?? 'Tingkat Nasional'),
                'prestasi'      => sanitize_text_field($row['prestasi'] ?? ''),
                'lingkup'       => sanitize_text_field($row['lingkup'] ?? ''),
                'dok_url'       => $dok,
            ];
        }
        if (!empty($_POST['new_lomba']['nama'])) {
            $nr = wp_unslash($_POST['new_lomba']);
            $dok = function_exists('ppb_clean_document_url') ? ppb_clean_document_url($nr['dok_url'] ?? '') : esc_url_raw($nr['dok_url'] ?? '');
            $clean[] = [
                'nama'          => sanitize_text_field($nr['nama']),
                'penyelenggara' => sanitize_text_field($nr['penyelenggara'] ?? ''),
                'kategori'      => sanitize_text_field($nr['kategori'] ?? 'Tingkat Nasional'),
                'prestasi'      => sanitize_text_field($nr['prestasi'] ?? ''),
                'lingkup'       => sanitize_text_field($nr['lingkup'] ?? ''),
                'dok_url'       => $dok,
            ];
        }
        update_option('ppb_lab_lomba_list', array_values($clean), false);
        $notice = '<div class="notice notice-success is-dismissible"><p><strong>🏆 Daftar Lomba Mahasiswa Lab BK (' . count($clean) . ' item) berhasil disimpan!</strong></p></div>';
    }
    if (isset($_POST['ppb_reset_lab_lomba']) && check_admin_referer('ppb_lab_lomba_nonce_action', 'ppb_lab_lomba_nonce')) {
        $active_tab = 'lab_kegiatan';
        delete_option('ppb_lab_lomba_list');
        $notice = '<div class="notice notice-warning is-dismissible"><p>Daftar Lomba Lab BK dikembalikan ke data bawaan.</p></div>';
    }

    // Tab 3: Simpan Sarana & Prasarana (/profil/sarana-prasarana/)
    if (isset($_POST['ppb_save_sarpras_list']) && check_admin_referer('ppb_sarpras_nonce_action', 'ppb_sarpras_nonce')) {
        $active_tab = 'sarpras';
        $raw_list = isset($_POST['sarpras']) && is_array($_POST['sarpras']) ? wp_unslash($_POST['sarpras']) : [];
        $clean = [];
        foreach ($raw_list as $row) {
            if (!empty($row['delete']) || empty(trim((string) ($row['nama'] ?? '')))) continue;
            $foto = function_exists('ppb_clean_document_url') ? ppb_clean_document_url($row['foto_url'] ?? '') : esc_url_raw($row['foto_url'] ?? '');
            $dok  = function_exists('ppb_clean_document_url') ? ppb_clean_document_url($row['dok_url'] ?? '') : esc_url_raw($row['dok_url'] ?? '');
            $clean[] = [
                'kategori'  => sanitize_text_field($row['kategori'] ?? 'Fasilitas Prodi'),
                'nama'      => sanitize_text_field($row['nama']),
                'deskripsi' => sanitize_textarea_field($row['deskripsi'] ?? ''),
                'kapasitas' => sanitize_text_field($row['kapasitas'] ?? ''),
                'lokasi'    => sanitize_text_field($row['lokasi'] ?? ''),
                'icon'      => sanitize_text_field($row['icon'] ?? '🏫') ?: '🏫',
                'foto_url'  => $foto,
                'dok_url'   => $dok,
                'tema'      => sanitize_key($row['tema'] ?? 'green'),
            ];
        }
        if (!empty($_POST['new_sarpras']['nama'])) {
            $nr = wp_unslash($_POST['new_sarpras']);
            $foto = function_exists('ppb_clean_document_url') ? ppb_clean_document_url($nr['foto_url'] ?? '') : esc_url_raw($nr['foto_url'] ?? '');
            $dok  = function_exists('ppb_clean_document_url') ? ppb_clean_document_url($nr['dok_url'] ?? '') : esc_url_raw($nr['dok_url'] ?? '');
            $clean[] = [
                'kategori'  => sanitize_text_field($nr['kategori'] ?? 'Fasilitas Prodi'),
                'nama'      => sanitize_text_field($nr['nama']),
                'deskripsi' => sanitize_textarea_field($nr['deskripsi'] ?? ''),
                'kapasitas' => sanitize_text_field($nr['kapasitas'] ?? ''),
                'lokasi'    => sanitize_text_field($nr['lokasi'] ?? ''),
                'icon'      => sanitize_text_field($nr['icon'] ?? '🏛️') ?: '🏛️',
                'foto_url'  => $foto,
                'dok_url'   => $dok,
                'tema'      => sanitize_key($nr['tema'] ?? 'green'),
            ];
        }
        update_option('ppb_sarpras_list', array_values($clean), false);
        if (class_exists('\\Elementor\\Plugin')) {
            \Elementor\Plugin::$instance->files_manager->clear_cache();
        }
        $notice = '<div class="notice notice-success is-dismissible"><p><strong>🏛️ Data Sarana &amp; Prasarana (' . count($clean) . ' ruang/fasilitas) berhasil disimpan dan langsung tersinkron ke /profil/sarana-prasarana/!</strong></p></div>';
    }
    if (isset($_POST['ppb_reset_sarpras_list']) && check_admin_referer('ppb_sarpras_nonce_action', 'ppb_sarpras_nonce')) {
        $active_tab = 'sarpras';
        delete_option('ppb_sarpras_list');
        $notice = '<div class="notice notice-warning is-dismissible"><p>Data Sarana &amp; Prasarana dikembalikan ke 3 ruang bawaan.</p></div>';
    }

    // Tab 4: Simpan 5 Bidang Lab BK & Kelola Pendaftar
    if (isset($_POST['ppb_save_lab_bidang']) && check_admin_referer('ppb_lab_bidang_nonce_action', 'ppb_lab_bidang_nonce')) {
        $active_tab = 'lab_bidang';
        $b_slug = sanitize_key($_POST['bidang_slug'] ?? 'konseling');
        $all_bidang = function_exists('ppb_get_lab_bidang_data') ? ppb_get_lab_bidang_data() : [];
        if (isset($all_bidang[$b_slug])) {
            $raw_b = isset($_POST['bidang']) && is_array($_POST['bidang']) ? wp_unslash($_POST['bidang']) : [];
            $clean_programs = [];
            if (!empty($raw_b['program']) && is_array($raw_b['program'])) {
                foreach ($raw_b['program'] as $pr) {
                    if (!empty($pr['delete']) || empty(trim((string) ($pr['judul'] ?? '')))) continue;
                    $clean_programs[] = [
                        'judul'     => sanitize_text_field($pr['judul']),
                        'kategori'  => sanitize_text_field($pr['kategori'] ?? 'Layanan'),
                        'deskripsi' => sanitize_textarea_field($pr['deskripsi'] ?? ''),
                        'sasaran'   => sanitize_text_field($pr['sasaran'] ?? 'Mahasiswa BK & Sivitas'),
                        'jadwal'    => sanitize_text_field($pr['jadwal'] ?? ''),
                        'status'    => sanitize_text_field($pr['status'] ?? 'Aktif'),
                        'cta_label' => sanitize_text_field($pr['cta_label'] ?? 'Ajukan / Daftar'),
                        'cta_url'   => esc_url_raw(trim((string) ($pr['cta_url'] ?? ''))),
                    ];
                }
            }
            if (!empty($_POST['new_program']['judul'])) {
                $np = wp_unslash($_POST['new_program']);
                $clean_programs[] = [
                    'judul'     => sanitize_text_field($np['judul']),
                    'kategori'  => sanitize_text_field($np['kategori'] ?? 'Layanan'),
                    'deskripsi' => sanitize_textarea_field($np['deskripsi'] ?? ''),
                    'sasaran'   => sanitize_text_field($np['sasaran'] ?? 'Mahasiswa BK & Sivitas'),
                    'jadwal'    => sanitize_text_field($np['jadwal'] ?? ''),
                    'status'    => sanitize_text_field($np['status'] ?? 'Aktif'),
                    'cta_label' => sanitize_text_field($np['cta_label'] ?? 'Ajukan / Daftar'),
                    'cta_url'   => esc_url_raw(trim((string) ($np['cta_url'] ?? ''))),
                ];
            }
            $sop_lines = array_values(array_filter(array_map('trim', explode("\n", str_replace("\r", "", (string) ($raw_b['sop_text'] ?? ''))))));
            $all_bidang[$b_slug] = array_merge($all_bidang[$b_slug], [
                'nama'        => sanitize_text_field($raw_b['nama'] ?? $all_bidang[$b_slug]['nama']),
                'tagline'     => sanitize_text_field($raw_b['tagline'] ?? $all_bidang[$b_slug]['tagline']),
                'deskripsi'   => sanitize_textarea_field($raw_b['deskripsi'] ?? $all_bidang[$b_slug]['deskripsi']),
                'koordinator' => sanitize_text_field($raw_b['koordinator'] ?? $all_bidang[$b_slug]['koordinator']),
                'asisten'     => sanitize_text_field($raw_b['asisten'] ?? ($all_bidang[$b_slug]['asisten'] ?? '')),
                'jadwal'      => sanitize_text_field($raw_b['jadwal'] ?? $all_bidang[$b_slug]['jadwal']),
                'lokasi'      => sanitize_text_field($raw_b['lokasi'] ?? $all_bidang[$b_slug]['lokasi']),
                'program'     => $clean_programs,
                'sop_alur'    => $sop_lines,
            ]);
            update_option('ppb_lab_bidang_data', $all_bidang, false);
            $notice = '<div class="notice notice-success is-dismissible"><p><strong>🏷️ Data ' . esc_html($all_bidang[$b_slug]['nama']) . ' berhasil disimpan!</strong></p></div>';
        }
    }
    if (isset($_POST['ppb_reset_lab_bidang']) && check_admin_referer('ppb_lab_bidang_nonce_action', 'ppb_lab_bidang_nonce')) {
        $active_tab = 'lab_bidang';
        delete_option('ppb_lab_bidang_data');
        $notice = '<div class="notice notice-warning is-dismissible"><p>Seluruh data 5 Bidang Lab BK dikembalikan ke data bawaan.</p></div>';
    }
    if (isset($_POST['ppb_update_lab_pendaftaran']) && check_admin_referer('ppb_lab_pendaftaran_nonce_action', 'ppb_lab_pendaftaran_nonce')) {
        $active_tab = 'lab_bidang';
        $pendaftar = get_option('ppb_lab_pendaftaran_list', []);
        if (is_array($pendaftar)) {
            $statuses = isset($_POST['reg_status']) && is_array($_POST['reg_status']) ? wp_unslash($_POST['reg_status']) : [];
            $deletes  = isset($_POST['reg_delete']) && is_array($_POST['reg_delete']) ? wp_unslash($_POST['reg_delete']) : [];
            $updated = [];
            foreach ($pendaftar as $idx => $item) {
                if (!empty($deletes[$idx])) continue;
                if (isset($statuses[$idx])) {
                    $item['status'] = sanitize_text_field($statuses[$idx]);
                }
                $updated[] = $item;
            }
            update_option('ppb_lab_pendaftaran_list', array_values($updated), false);
            $notice = '<div class="notice notice-success is-dismissible"><p><strong>📋 Status pendaftaran layanan Lab BK berhasil diperbarui!</strong></p></div>';
        }
    }

    $wa_hotline       = get_option('ppb_layanan_wa_hotline', '6281242338988');
    $jam_operasional  = get_option('ppb_layanan_jam_operasional', 'Senin – Jumat, 08.00 – 16.00 WITA');
    $lokasi_lab       = get_option('ppb_layanan_lokasi_lab', 'Gedung Laboratorium BK, FIP UNM Kampus Gunungsari Makassar');
    $sop_perlindungan = get_option('ppb_layanan_sop_perlindungan_link', 'https://drive.google.com/file/d/1_SOP_Perlindungan_Mahasiswa_BK/view?usp=sharing');
    $sipadu_url       = get_option('ppb_layanan_sipadu_url', 'https://bk-connect-fipunm.vercel.app/sipadu');
    $survei_url       = get_option('ppb_layanan_survei_url', home_url('/survei/'));
    $inv_count        = wp_count_posts('ppb_inventaris_lab')->publish ?? 0;

    $is_full_admin = current_user_can('manage_options');
    $current_admin_page = $is_full_admin ? 'ppb-layanan-settings' : 'ppb-lab-admin';
    $tabs = $is_full_admin ? [
        'umum'         => '⚙️ Hotline, SOP & Impor CPT Lab',
        'lab_bidang'   => '🏷️ 5 Bidang Lab BK',
        'lab_kegiatan' => '🧪 Kegiatan & Lomba Lab BK',
        'sarpras'      => '🏛️ Sarana & Prasarana (Ruangan & Foto)',
    ] : [
        'lab_bidang'   => '🏷️ 5 Bidang Lab BK',
        'lab_kegiatan' => '🧪 Kegiatan & Lomba Lab BK',
    ];
    if (!$is_full_admin && !isset($tabs[$active_tab])) {
        $active_tab = 'lab_bidang';
    }
    ?>
    <div class="wrap" style="max-width:1120px;margin:20px auto;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;color:#1e293b;">
        <div style="background:linear-gradient(135deg,#0D3B22 0%,#185333 100%);color:#fff;border-radius:16px;padding:24px 28px;margin-bottom:18px;display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:14px;">
            <div>
                <h1 style="margin:0 0 6px;color:#fff;font-size:23px;font-weight:800;">📁 <?php echo $is_full_admin ? 'Pengaturan Pusat Layanan, Lab BK &amp; Sarana Prasarana' : 'Portal Admin Laboratorium BK'; ?></h1>
                <p style="margin:0;color:rgba(255,255,255,0.85);font-size:13px;">Kelola 5 bidang Lab BK, pendaftar layanan, kegiatan &amp; lomba, serta inventaris alat tes laboratorium.</p>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <a href="<?php echo esc_url(home_url('/lab-bk/')); ?>" target="_blank" class="button" style="border-radius:8px;">↗ Lihat Web Lab BK</a>
                <?php if ($is_full_admin) : ?>
                    <a href="<?php echo esc_url(admin_url('edit.php?post_type=ppb_inventaris_lab')); ?>" class="button" style="background:#F5B82E;color:#0D3B22;border:none;font-weight:700;border-radius:8px;">🧪 Kelola CPT Inventaris (<?php echo (int) $inv_count; ?>)</a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=ppb-portal-hub')); ?>" class="button" style="border-radius:8px;">← Pusat Kendali</a>
                <?php endif; ?>
            </div>
        </div>

        <nav class="nav-tab-wrapper" style="margin-bottom:20px;">
            <?php foreach ($tabs as $tk => $tl) : ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=' . $current_admin_page . '&tab=' . $tk)); ?>" class="nav-tab <?php echo $active_tab === $tk ? 'nav-tab-active' : ''; ?>" style="<?php echo $active_tab === $tk ? 'background:#0D3B22;color:#fff;border-color:#0D3B22;' : ''; ?>font-weight:700;">
                    <?php echo esc_html($tl); ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <?php echo $notice; ?>

        <?php if ($active_tab === 'umum') : ?>
            <div style="display:grid;grid-template-columns:2fr 1fr;gap:18px;align-items:start;">
                <form method="post" action="" style="background:#fff;padding:24px;border-radius:12px;border:1px solid #cbd5e1;box-shadow:0 2px 10px rgba(0,0,0,0.04);">
                    <?php wp_nonce_field('ppb_save_layanan_nonce'); ?>
                    <h3 style="margin-top:0;color:#0D3B22;border-bottom:1px solid #e2e8f0;padding-bottom:10px;">Hotline &amp; Operasional Lab BK</h3>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="ppb_layanan_wa_hotline">Nomor WhatsApp Konselor Siaga</label></th>
                            <td>
                                <input type="text" name="ppb_layanan_wa_hotline" id="ppb_layanan_wa_hotline" value="<?php echo esc_attr($wa_hotline); ?>" class="regular-text">
                                <p class="description">Format internasional tanpa simbol (contoh: 6281242338988).</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="ppb_layanan_jam_operasional">Jam Layanan Terbuka</label></th>
                            <td><input type="text" name="ppb_layanan_jam_operasional" id="ppb_layanan_jam_operasional" value="<?php echo esc_attr($jam_operasional); ?>" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="ppb_layanan_lokasi_lab">Lokasi Fisik Laboratorium</label></th>
                            <td><input type="text" name="ppb_layanan_lokasi_lab" id="ppb_layanan_lokasi_lab" value="<?php echo esc_attr($lokasi_lab); ?>" class="large-text"></td>
                        </tr>
                    </table>

                    <h3 style="margin-top:20px;color:#0D3B22;border-bottom:1px solid #e2e8f0;padding-bottom:10px;">Integrasi Portal &amp; Dokumen Regulasi</h3>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="ppb_layanan_sipadu_url">URL Portal SI-PADU BK</label></th>
                            <td><input type="url" name="ppb_layanan_sipadu_url" id="ppb_layanan_sipadu_url" value="<?php echo esc_attr($sipadu_url); ?>" class="large-text"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="ppb_layanan_sop_perlindungan_link">SOP Perlindungan Mahasiswa</label></th>
                            <td>
                                <?php if (function_exists('ppb_render_media_url_control')) : ?>
                                    <?php ppb_render_media_url_control([
                                        'id'          => 'ppb_layanan_sop_perlindungan_link',
                                        'name'        => 'ppb_layanan_sop_perlindungan_link',
                                        'value'       => $sop_perlindungan,
                                        'placeholder' => 'https://drive.google.com/file/d/... atau upload PDF SOP',
                                        'title'       => 'Pilih / Upload SOP Perlindungan Mahasiswa',
                                        'btn_label'   => '📁 Upload / Pilih PDF',
                                    ]); ?>
                                <?php else : ?>
                                    <input type="url" name="ppb_layanan_sop_perlindungan_link" id="ppb_layanan_sop_perlindungan_link" value="<?php echo esc_attr($sop_perlindungan); ?>" class="large-text">
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="ppb_layanan_survei_url">Tautan Survei Kepuasan Mahasiswa</label></th>
                            <td><input type="url" name="ppb_layanan_survei_url" id="ppb_layanan_survei_url" value="<?php echo esc_attr($survei_url); ?>" class="large-text"></td>
                        </tr>
                    </table>
                    <p class="submit">
                        <input type="submit" name="ppb_save_layanan_options" class="button button-primary" value="💾 Simpan Pengaturan Layanan" style="background:#0D3B22;border-color:#0D3B22;">
                    </p>
                </form>

                <div style="display:flex;flex-direction:column;gap:14px;">
                    <div style="background:#fff;border:1px solid #cbd5e1;border-radius:12px;padding:18px;">
                        <h3 style="margin:0 0 8px;color:#0D3B22;font-size:15px;">🧪 Pintasan CPT Layanan &amp; Lab</h3>
                        <p style="font-size:12.5px;color:#64748b;margin:0 0 12px;">Halaman <code>/lab-bk/inventaris/</code> membaca langsung data CPT <strong>Inventaris Alat Lab</strong> secara real-time.</p>
                        <div style="display:flex;flex-direction:column;gap:8px;">
                            <a href="<?php echo esc_url(admin_url('edit.php?post_type=ppb_inventaris_lab')); ?>" class="button">🧪 Kelola Inventaris Lab (<?php echo (int) $inv_count; ?>)</a>
                            <a href="<?php echo esc_url(admin_url('edit.php?post_type=ppb_template_dok')); ?>" class="button">📄 Kelola Template Surat</a>
                            <a href="<?php echo esc_url(admin_url('edit.php?post_type=ppb_alur_sop')); ?>" class="button">🔄 Kelola Alur SOP</a>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=ppb-excel-import&dataset=inventaris')); ?>" class="button" style="border-color:#0D3B22;color:#0D3B22;font-weight:700;">📥 Import Excel/JSON Inventaris &amp; Template</a>
                            <a href="<?php echo esc_url(home_url('/lab-bk/inventaris/')); ?>" target="_blank" class="button">↗ Lihat /lab-bk/inventaris/</a>
                        </div>
                        <form method="post" style="margin-top:12px;padding-top:12px;border-top:1px dashed #e2e8f0;">
                            <?php wp_nonce_field('ppb_save_layanan_nonce'); ?>
                            <button type="submit" name="ppb_import_default_inventaris" class="button button-secondary" style="width:100%;">📥 Impor 7 Inventaris Bawaan ke CPT</button>
                        </form>
                    </div>
                </div>
            </div>

        <?php elseif ($active_tab === 'lab_bidang') :
            $all_bidang = function_exists('ppb_get_lab_bidang_data') ? ppb_get_lab_bidang_data() : [];
            $selected_bidang = sanitize_key($_REQUEST['bidang_slug'] ?? ($_GET['bidang'] ?? 'konseling'));
            if (!isset($all_bidang[$selected_bidang])) {
                $selected_bidang = 'konseling';
            }
            $cur_b = $all_bidang[$selected_bidang];
            $pendaftar_list = get_option('ppb_lab_pendaftaran_list', []);
            if (!is_array($pendaftar_list)) $pendaftar_list = [];
        ?>
            <div style="background:#fff;border:1px solid #cbd5e1;border-radius:12px;padding:22px;margin-bottom:22px;">
                <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:12px;margin-bottom:16px;">
                    <div>
                        <h2 style="margin:0;color:#0D3B22;font-size:18px;">🏷️ Kelola 5 Bidang Laboratorium BK (Shortcode <code>[ppb_lab_bidang]</code>)</h2>
                        <span style="font-size:12px;color:#64748b;">Pilih salah satu bidang untuk mengubah koordinator, jadwal, daftar layanan/klub non-akademik, dan alur SOP.</span>
                    </div>
                    <a href="<?php echo esc_url(home_url('/lab-bk/bidang-' . $selected_bidang . '/')); ?>" target="_blank" class="button">↗ Lihat Halaman Publik</a>
                </div>

                <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid #e2e8f0;">
                    <?php foreach ($all_bidang as $b_key => $b_info) :
                        $is_cur = ($b_key === $selected_bidang);
                        $sub_url = add_query_arg(['page' => $current_admin_page, 'tab' => 'lab_bidang', 'bidang' => $b_key], admin_url('admin.php'));
                    ?>
                        <a href="<?php echo esc_url($sub_url); ?>" class="button" style="<?php echo $is_cur ? 'background:#0D3B22;color:#fff;border-color:#0D3B22;font-weight:700;' : 'background:#f8fafc;color:#1e293b;'; ?>">
                            <?php echo esc_html($b_info['icon'] . ' ' . $b_info['nama']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <form method="post">
                    <?php wp_nonce_field('ppb_lab_bidang_nonce_action', 'ppb_lab_bidang_nonce'); ?>
                    <input type="hidden" name="bidang_slug" value="<?php echo esc_attr($selected_bidang); ?>">

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                        <div>
                            <label style="font-size:11px;font-weight:700;color:#64748b;display:block;margin-bottom:4px;">Nama Bidang</label>
                            <input type="text" name="bidang[nama]" value="<?php echo esc_attr($cur_b['nama']); ?>" class="widefat" required>
                        </div>
                        <div>
                            <label style="font-size:11px;font-weight:700;color:#64748b;display:block;margin-bottom:4px;">Tagline Singkat</label>
                            <input type="text" name="bidang[tagline]" value="<?php echo esc_attr($cur_b['tagline']); ?>" class="widefat">
                        </div>
                    </div>

                    <div style="margin-bottom:12px;">
                        <label style="font-size:11px;font-weight:700;color:#64748b;display:block;margin-bottom:4px;">Deskripsi &amp; Fokus Bidang</label>
                        <textarea name="bidang[deskripsi]" rows="2" class="widefat"><?php echo esc_textarea($cur_b['deskripsi']); ?></textarea>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:10px;margin-bottom:18px;">
                        <div>
                            <label style="font-size:11px;font-weight:700;color:#64748b;display:block;margin-bottom:4px;">Dosen Koordinator</label>
                            <input type="text" name="bidang[koordinator]" value="<?php echo esc_attr($cur_b['koordinator']); ?>" class="widefat">
                        </div>
                        <div>
                            <label style="font-size:11px;font-weight:700;color:#64748b;display:block;margin-bottom:4px;">Koordinator Asisten / Peer</label>
                            <input type="text" name="bidang[asisten]" value="<?php echo esc_attr($cur_b['asisten'] ?? ''); ?>" class="widefat">
                        </div>
                        <div>
                            <label style="font-size:11px;font-weight:700;color:#64748b;display:block;margin-bottom:4px;">Jadwal Layanan</label>
                            <input type="text" name="bidang[jadwal]" value="<?php echo esc_attr($cur_b['jadwal']); ?>" class="widefat">
                        </div>
                        <div>
                            <label style="font-size:11px;font-weight:700;color:#64748b;display:block;margin-bottom:4px;">Lokasi Ruang</label>
                            <input type="text" name="bidang[lokasi]" value="<?php echo esc_attr($cur_b['lokasi']); ?>" class="widefat">
                        </div>
                    </div>

                    <h3 style="margin:0 0 10px;font-size:15px;color:#0D3B22;">Daftar Layanan / Program Kerja / Klub Bidang</h3>
                    <div style="display:grid;gap:12px;margin-bottom:16px;">
                        <?php foreach (($cur_b['program'] ?? []) as $pidx => $pr) : ?>
                            <div style="border:1px solid #e2e8f0;border-radius:10px;padding:14px;background:#f8fafc;">
                                <div style="display:grid;grid-template-columns:1.5fr 1fr 1fr 90px;gap:8px;margin-bottom:8px;">
                                    <input type="text" name="bidang[program][<?php echo $pidx; ?>][judul]" value="<?php echo esc_attr($pr['judul'] ?? ''); ?>" placeholder="Judul Layanan / Program / Klub" class="widefat">
                                    <input type="text" name="bidang[program][<?php echo $pidx; ?>][kategori]" value="<?php echo esc_attr($pr['kategori'] ?? ''); ?>" placeholder="Kategori" class="widefat">
                                    <input type="text" name="bidang[program][<?php echo $pidx; ?>][status]" value="<?php echo esc_attr($pr['status'] ?? 'Aktif'); ?>" placeholder="Status (Aktif/Buka)" class="widefat">
                                    <label style="color:#dc2626;font-size:12px;display:flex;align-items:center;gap:4px;"><input type="checkbox" name="bidang[program][<?php echo $pidx; ?>][delete]" value="1"> Hapus</label>
                                </div>
                                <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1.2fr;gap:8px;margin-bottom:8px;">
                                    <input type="text" name="bidang[program][<?php echo $pidx; ?>][sasaran]" value="<?php echo esc_attr($pr['sasaran'] ?? ''); ?>" placeholder="Sasaran peserta" class="widefat">
                                    <input type="text" name="bidang[program][<?php echo $pidx; ?>][jadwal]" value="<?php echo esc_attr($pr['jadwal'] ?? ''); ?>" placeholder="Jadwal rutin" class="widefat">
                                    <input type="text" name="bidang[program][<?php echo $pidx; ?>][cta_label]" value="<?php echo esc_attr($pr['cta_label'] ?? ''); ?>" placeholder="Label tombol CTA" class="widefat">
                                    <input type="text" name="bidang[program][<?php echo $pidx; ?>][cta_url]" value="<?php echo esc_attr($pr['cta_url'] ?? ''); ?>" placeholder="URL (kosongkan = modal form)" class="widefat">
                                </div>
                                <textarea name="bidang[program][<?php echo $pidx; ?>][deskripsi]" rows="2" class="widefat" placeholder="Deskripsi singkat layanan/program..."><?php echo esc_textarea($pr['deskripsi'] ?? ''); ?></textarea>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div style="background:#f0fdf4;border:1.5px dashed #16a34a;border-radius:10px;padding:14px;margin-bottom:18px;">
                        <strong style="display:block;color:#0D3B22;margin-bottom:8px;">➕ Tambah Layanan / Program / Klub Baru ke Bidang Ini</strong>
                        <div style="display:grid;grid-template-columns:1.5fr 1fr 1fr;gap:8px;margin-bottom:8px;">
                            <input type="text" name="new_program[judul]" placeholder="Judul Layanan / Program / Klub baru..." class="widefat">
                            <input type="text" name="new_program[kategori]" placeholder="Kategori (mis. Layanan / Klub)" class="widefat">
                            <input type="text" name="new_program[status]" placeholder="Status (mis. Pendaftaran Buka)" class="widefat">
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1.2fr;gap:8px;margin-bottom:8px;">
                            <input type="text" name="new_program[sasaran]" placeholder="Sasaran (mis. Mahasiswa BK)" class="widefat">
                            <input type="text" name="new_program[jadwal]" placeholder="Jadwal" class="widefat">
                            <input type="text" name="new_program[cta_label]" placeholder="Label Tombol (mis. Daftar Sekarang)" class="widefat">
                            <input type="text" name="new_program[cta_url]" placeholder="URL (kosongkan = buka modal daftar)" class="widefat">
                        </div>
                        <textarea name="new_program[deskripsi]" rows="2" class="widefat" placeholder="Deskripsi singkat..."></textarea>
                    </div>

                    <div style="margin-bottom:16px;">
                        <label style="font-size:12px;font-weight:700;color:#0D3B22;display:block;margin-bottom:4px;">Alur / SOP Layanan (1 langkah per baris)</label>
                        <textarea name="bidang[sop_text]" rows="4" class="widefat"><?php echo esc_textarea(implode("\n", (array) ($cur_b['sop_alur'] ?? []))); ?></textarea>
                    </div>

                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                        <button type="submit" name="ppb_save_lab_bidang" class="button button-primary" style="background:#0D3B22;border-color:#0D3B22;">💾 Simpan <?php echo esc_html($cur_b['nama']); ?></button>
                        <button type="submit" name="ppb_reset_lab_bidang" class="button" onclick="return confirm('Reset semua 5 Bidang Lab BK ke data bawaan?');">↺ Reset Semua ke Default</button>
                    </div>
                </form>
            </div>

            <!-- Daftar Pengajuan / Pendaftaran Layanan Lab BK -->
            <form method="post" style="background:#fff;border:1px solid #cbd5e1;border-radius:12px;padding:22px;">
                <?php wp_nonce_field('ppb_lab_pendaftaran_nonce_action', 'ppb_lab_pendaftaran_nonce'); ?>
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:14px;">
                    <div>
                        <h2 style="margin:0;color:#0D3B22;font-size:18px;">📋 Daftar Pengajuan &amp; Pendaftaran Layanan Lab BK (<?php echo count($pendaftar_list); ?>)</h2>
                        <span style="font-size:12px;color:#64748b;">Data pendaftar dari form terintegrasi di halaman 5 bidang Laboratorium BK.</span>
                    </div>
                    <?php if (!empty($pendaftar_list)) : ?>
                        <button type="submit" name="ppb_update_lab_pendaftaran" class="button button-primary" style="background:#0D3B22;border-color:#0D3B22;">💾 Simpan Status Pendaftar</button>
                    <?php endif; ?>
                </div>
                <?php if (empty($pendaftar_list)) : ?>
                    <p style="color:#64748b;font-size:13px;margin:0;">Belum ada pengajuan atau pendaftaran masuk melalui formulir website.</p>
                <?php else : ?>
                    <div style="overflow-x:auto;">
                        <table class="widefat striped" style="font-size:12.5px;">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Bidang &amp; Program</th>
                                    <th>Nama &amp; NIM</th>
                                    <th>Kontak (WA/Email)</th>
                                    <th>Catatan</th>
                                    <th>Status</th>
                                    <th>Hapus</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pendaftar_list as $ridx => $reg) : ?>
                                    <tr>
                                        <td><?php echo esc_html($reg['waktu'] ?? ''); ?></td>
                                        <td><strong><?php echo esc_html($reg['bidang'] ?? ''); ?></strong><br><span style="color:#64748b;"><?php echo esc_html($reg['program'] ?? ''); ?></span></td>
                                        <td><strong><?php echo esc_html($reg['nama'] ?? ''); ?></strong><br><span style="color:#64748b;">NIM: <?php echo esc_html($reg['nim'] ?? '-'); ?></span></td>
                                        <td><?php echo esc_html($reg['kontak'] ?? ''); ?></td>
                                        <td><?php echo esc_html($reg['catatan'] ?? '-'); ?></td>
                                        <td>
                                            <select name="reg_status[<?php echo (int) $ridx; ?>]">
                                                <?php foreach (['Menunggu', 'Diproses', 'Selesai'] as $st) : ?>
                                                    <option value="<?php echo esc_attr($st); ?>" <?php selected($reg['status'] ?? 'Menunggu', $st); ?>><?php echo esc_html($st); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td><input type="checkbox" name="reg_delete[<?php echo (int) $ridx; ?>]" value="1"></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </form>

        <?php elseif ($active_tab === 'lab_kegiatan') :
            $kegiatan_list = ppb_get_lab_kegiatan_list();
            $lomba_list    = ppb_get_lab_lomba_list();
        ?>
            <?php if (function_exists('ppb_excel_render_inline_widget')) ppb_excel_render_inline_widget('lab_kegiatan'); ?>

            <!-- Form Kegiatan Lab -->
            <form method="post" style="background:#fff;border:1px solid #cbd5e1;border-radius:12px;padding:22px;margin-bottom:22px;">
                <?php wp_nonce_field('ppb_lab_kegiatan_nonce_action', 'ppb_lab_kegiatan_nonce'); ?>
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:14px;">
                    <div>
                        <h2 style="margin:0;color:#0D3B22;font-size:18px;">🧪 Kegiatan &amp; Praktikum Mahasiswa Lab BK (Shortcode [ppb_lab_kegiatan])</h2>
                        <span style="font-size:12px;color:#64748b;">Ubah jadwal, deskripsi, atau unggah panduan PDF untuk kegiatan praktikum laboratorium.</span>
                    </div>
                    <div style="display:flex;gap:8px;">
                        <button type="submit" name="ppb_save_lab_kegiatan" class="button button-primary" style="background:#0D3B22;border-color:#0D3B22;">💾 Simpan Kegiatan Lab</button>
                        <button type="submit" name="ppb_reset_lab_kegiatan" class="button" onclick="return confirm('Reset Kegiatan Lab ke data bawaan?');">↺ Reset Default</button>
                    </div>
                </div>

                <div style="display:grid;gap:12px;margin-bottom:16px;">
                    <?php foreach ($kegiatan_list as $idx => $kg) : ?>
                        <div style="border:1px solid #e2e8f0;border-radius:10px;padding:14px;background:#fcfdfd;">
                            <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:10px;margin-bottom:8px;">
                                <div>
                                    <label style="font-size:11px;font-weight:700;display:block;margin-bottom:2px;">Nama Kegiatan</label>
                                    <input type="text" name="kegiatan[<?php echo $idx; ?>][judul]" value="<?php echo esc_attr($kg['judul']); ?>" class="widefat" style="font-weight:600;">
                                </div>
                                <div>
                                    <label style="font-size:11px;font-weight:600;display:block;margin-bottom:2px;">Periode</label>
                                    <input type="text" name="kegiatan[<?php echo $idx; ?>][periode]" value="<?php echo esc_attr($kg['periode']); ?>" class="widefat">
                                </div>
                                <div>
                                    <label style="font-size:11px;font-weight:600;display:block;margin-bottom:2px;">Sasaran Peserta</label>
                                    <input type="text" name="kegiatan[<?php echo $idx; ?>][peserta]" value="<?php echo esc_attr($kg['peserta']); ?>" class="widefat">
                                </div>
                                <div>
                                    <label style="font-size:11px;font-weight:600;display:block;margin-bottom:2px;">Status</label>
                                    <input type="text" name="kegiatan[<?php echo $idx; ?>][status]" value="<?php echo esc_attr($kg['status']); ?>" class="widefat">
                                </div>
                            </div>
                            <div style="display:grid;grid-template-columns:1.4fr 1fr auto;gap:10px;align-items:center;">
                                <input type="text" name="kegiatan[<?php echo $idx; ?>][deskripsi]" value="<?php echo esc_attr($kg['deskripsi']); ?>" class="widefat" placeholder="Deskripsi kegiatan...">
                                <?php ppb_render_media_url_control(['id' => 'kg_dok_' . $idx, 'name' => "kegiatan[$idx][dok_url]", 'value' => $kg['dok_url'] ?? '', 'placeholder' => 'Link Panduan / Dokumentasi (opsional)', 'btn_label' => '📁 Upload / Drive']); ?>
                                <label style="color:#b91c1c;font-size:11.5px;font-weight:600;white-space:nowrap;"><input type="checkbox" name="kegiatan[<?php echo $idx; ?>][delete]" value="1"> Hapus</label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div style="background:#f0fdf4;border:1.5px dashed #166534;border-radius:10px;padding:14px;">
                    <strong style="display:block;color:#0D3B22;margin-bottom:8px;">➕ Tambah Kegiatan Lab Baru</strong>
                    <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:10px;margin-bottom:8px;">
                        <input type="text" name="new_kegiatan[judul]" placeholder="Judul Kegiatan Lab Baru (isi untuk menambah)..." class="widefat">
                        <input type="text" name="new_kegiatan[periode]" placeholder="Semester / Bulan" class="widefat">
                        <input type="text" name="new_kegiatan[peserta]" placeholder="Sasaran Mahasiswa" class="widefat">
                        <input type="text" name="new_kegiatan[status]" placeholder="Aktif Berjalan" class="widefat">
                    </div>
                    <div style="display:grid;grid-template-columns:1.4fr 1fr;gap:10px;">
                        <input type="text" name="new_kegiatan[deskripsi]" placeholder="Ringkasan pelaksanaan kegiatan..." class="widefat">
                        <?php ppb_render_media_url_control(['id' => 'kg_dok_new', 'name' => 'new_kegiatan[dok_url]', 'value' => '', 'placeholder' => 'Link Dokumentasi / Panduan PDF', 'btn_label' => '📁 Upload / Drive']); ?>
                    </div>
                </div>
            </form>

            <?php if (function_exists('ppb_excel_render_inline_widget')) ppb_excel_render_inline_widget('lab_lomba'); ?>

            <!-- Form Lomba Lab -->
            <form method="post" style="background:#fff;border:1px solid #cbd5e1;border-radius:12px;padding:22px;">
                <?php wp_nonce_field('ppb_lab_lomba_nonce_action', 'ppb_lab_lomba_nonce'); ?>
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:14px;">
                    <div>
                        <h2 style="margin:0;color:#0D3B22;font-size:18px;">🏆 Informasi Lomba Non-Akademik Lab BK (Shortcode [ppb_lab_lomba])</h2>
                        <span style="font-size:12px;color:#64748b;">Daftar kompetisi mahasiswa binaan Lab BK beserta capaian dan juknis PDF.</span>
                    </div>
                    <div style="display:flex;gap:8px;">
                        <button type="submit" name="ppb_save_lab_lomba" class="button button-primary" style="background:#0D3B22;border-color:#0D3B22;">💾 Simpan Info Lomba</button>
                        <button type="submit" name="ppb_reset_lab_lomba" class="button" onclick="return confirm('Reset Info Lomba ke data bawaan?');">↺ Reset Default</button>
                    </div>
                </div>

                <div style="display:grid;gap:12px;margin-bottom:16px;">
                    <?php foreach ($lomba_list as $idx => $lb) : ?>
                        <div style="border:1px solid #e2e8f0;border-radius:10px;padding:14px;background:#fcfdfd;">
                            <div style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:10px;margin-bottom:8px;">
                                <input type="text" name="lomba[<?php echo $idx; ?>][nama]" value="<?php echo esc_attr($lb['nama']); ?>" class="widefat" style="font-weight:600;" placeholder="Nama Kompetisi">
                                <input type="text" name="lomba[<?php echo $idx; ?>][kategori]" value="<?php echo esc_attr($lb['kategori']); ?>" class="widefat" placeholder="Tingkat Nasional">
                                <input type="text" name="lomba[<?php echo $idx; ?>][lingkup]" value="<?php echo esc_attr($lb['lingkup']); ?>" class="widefat" placeholder="Bidang / Lingkup">
                            </div>
                            <div style="display:grid;grid-template-columns:1fr 1fr 1.2fr auto;gap:10px;align-items:center;">
                                <input type="text" name="lomba[<?php echo $idx; ?>][penyelenggara]" value="<?php echo esc_attr($lb['penyelenggara']); ?>" class="widefat" placeholder="Penyelenggara">
                                <input type="text" name="lomba[<?php echo $idx; ?>][prestasi]" value="<?php echo esc_attr($lb['prestasi']); ?>" class="widefat" placeholder="Capaian / Prestasi">
                                <?php ppb_render_media_url_control(['id' => 'lb_dok_' . $idx, 'name' => "lomba[$idx][dok_url]", 'value' => $lb['dok_url'] ?? '', 'placeholder' => 'Link Juknis / Sertifikat (opsional)', 'btn_label' => '📁 Upload / Drive']); ?>
                                <label style="color:#b91c1c;font-size:11.5px;font-weight:600;white-space:nowrap;"><input type="checkbox" name="lomba[<?php echo $idx; ?>][delete]" value="1"> Hapus</label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div style="background:#fffbeb;border:1.5px dashed #d97706;border-radius:10px;padding:14px;">
                    <strong style="display:block;color:#92400e;margin-bottom:8px;">➕ Tambah Info Lomba Baru</strong>
                    <div style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:10px;margin-bottom:8px;">
                        <input type="text" name="new_lomba[nama]" placeholder="Nama Lomba / Kompetisi Baru..." class="widefat">
                        <input type="text" name="new_lomba[kategori]" placeholder="Tingkat Nasional / Regional" class="widefat">
                        <input type="text" name="new_lomba[lingkup]" placeholder="Bidang / Inovasi" class="widefat">
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr 1.2fr;gap:10px;">
                        <input type="text" name="new_lomba[penyelenggara]" placeholder="Penyelenggara" class="widefat">
                        <input type="text" name="new_lomba[prestasi]" placeholder="Capaian / Penghargaan" class="widefat">
                        <?php ppb_render_media_url_control(['id' => 'lb_dok_new', 'name' => 'new_lomba[dok_url]', 'value' => '', 'placeholder' => 'Juknis Lomba / Dokumentasi PDF', 'btn_label' => '📁 Upload / Drive']); ?>
                    </div>
                </div>
            </form>

        <?php elseif ($active_tab === 'sarpras') :
            $sarpras_list = ppb_get_sarpras_list();
        ?>
            <?php if (function_exists('ppb_excel_render_inline_widget')) ppb_excel_render_inline_widget('sarpras'); ?>

            <form method="post" style="background:#fff;border:1px solid #cbd5e1;border-radius:12px;padding:22px;">
                <?php wp_nonce_field('ppb_sarpras_nonce_action', 'ppb_sarpras_nonce'); ?>
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:16px;">
                    <div>
                        <h2 style="margin:0;color:#0D3B22;font-size:18px;">🏛️ Kelola Fasilitas &amp; Foto Sarana Prasarana (/profil/sarana-prasarana/)</h2>
                        <span style="font-size:12px;color:#64748b;">Unggah foto ruangan via Pustaka Media atau tempel link Google Drive gambar; otomatis tersinkron ke halaman publik &amp; shortcode <code>[ppb_sarana_prasarana]</code>.</span>
                    </div>
                    <div style="display:flex;gap:8px;">
                        <a href="<?php echo esc_url(home_url('/profil/sarana-prasarana/')); ?>" target="_blank" class="button">↗ Lihat Halaman</a>
                        <button type="submit" name="ppb_save_sarpras_list" class="button button-primary" style="background:#0D3B22;border-color:#0D3B22;">💾 Simpan Sarpras</button>
                        <button type="submit" name="ppb_reset_sarpras_list" class="button" onclick="return confirm('Reset Sarana Prasarana ke 3 ruang bawaan?');">↺ Reset Default</button>
                    </div>
                </div>

                <div style="display:grid;gap:14px;margin-bottom:20px;">
                    <?php foreach ($sarpras_list as $idx => $sp) : ?>
                        <div style="border:1px solid #e2e8f0;border-radius:12px;padding:16px;background:#fcfdfd;">
                            <div style="display:grid;grid-template-columns:160px 1.5fr 1fr 1fr 80px 110px;gap:10px;margin-bottom:10px;">
                                <div>
                                    <label style="font-size:11px;font-weight:600;display:block;margin-bottom:2px;">Kategori Label</label>
                                    <input type="text" name="sarpras[<?php echo $idx; ?>][kategori]" value="<?php echo esc_attr($sp['kategori']); ?>" class="widefat">
                                </div>
                                <div>
                                    <label style="font-size:11px;font-weight:700;color:#0D3B22;display:block;margin-bottom:2px;">Nama Fasilitas / Ruangan</label>
                                    <input type="text" name="sarpras[<?php echo $idx; ?>][nama]" value="<?php echo esc_attr($sp['nama']); ?>" class="widefat" style="font-weight:600;">
                                </div>
                                <div>
                                    <label style="font-size:11px;font-weight:600;display:block;margin-bottom:2px;">Kapasitas</label>
                                    <input type="text" name="sarpras[<?php echo $idx; ?>][kapasitas]" value="<?php echo esc_attr($sp['kapasitas'] ?? ''); ?>" class="widefat">
                                </div>
                                <div>
                                    <label style="font-size:11px;font-weight:600;display:block;margin-bottom:2px;">Lokasi Gedung</label>
                                    <input type="text" name="sarpras[<?php echo $idx; ?>][lokasi]" value="<?php echo esc_attr($sp['lokasi'] ?? ''); ?>" class="widefat">
                                </div>
                                <div>
                                    <label style="font-size:11px;font-weight:600;display:block;margin-bottom:2px;">Ikon</label>
                                    <input type="text" name="sarpras[<?php echo $idx; ?>][icon]" value="<?php echo esc_attr($sp['icon'] ?? '🏫'); ?>" class="widefat" style="text-align:center;">
                                </div>
                                <div>
                                    <label style="font-size:11px;font-weight:600;display:block;margin-bottom:2px;">Tema</label>
                                    <select name="sarpras[<?php echo $idx; ?>][tema]" class="widefat">
                                        <option value="green" <?php selected($sp['tema'] ?? 'green', 'green'); ?>>Hijau</option>
                                        <option value="gold" <?php selected($sp['tema'] ?? 'green', 'gold'); ?>>Emas</option>
                                        <option value="indigo" <?php selected($sp['tema'] ?? 'green', 'indigo'); ?>>Biru</option>
                                    </select>
                                </div>
                            </div>
                            <div style="margin-bottom:10px;">
                                <input type="text" name="sarpras[<?php echo $idx; ?>][deskripsi]" value="<?php echo esc_attr($sp['deskripsi']); ?>" class="widefat" placeholder="Deskripsi fasilitas ruangan...">
                            </div>
                            <div style="display:grid;grid-template-columns:1fr 1fr auto;gap:12px;align-items:end;">
                                <div>
                                    <label style="font-size:11px;font-weight:600;display:block;margin-bottom:3px;">Foto Ruangan (Upload Media / Link Drive Foto)</label>
                                    <?php ppb_render_media_url_control(['id' => 'sp_foto_' . $idx, 'name' => "sarpras[$idx][foto_url]", 'value' => $sp['foto_url'] ?? '', 'library' => 'image', 'placeholder' => 'https://... atau upload JPG/PNG', 'btn_label' => '📷 Upload Foto']); ?>
                                </div>
                                <div>
                                    <label style="font-size:11px;font-weight:600;display:block;margin-bottom:3px;">Link SOP / Denah / Inventaris Ruang (Opsional)</label>
                                    <?php ppb_render_media_url_control(['id' => 'sp_dok_' . $idx, 'name' => "sarpras[$idx][dok_url]", 'value' => $sp['dok_url'] ?? '', 'placeholder' => 'https://drive.google.com/... atau PDF', 'btn_label' => '📁 Upload PDF']); ?>
                                </div>
                                <label style="color:#b91c1c;font-size:12px;font-weight:600;white-space:nowrap;padding-bottom:6px;"><input type="checkbox" name="sarpras[<?php echo $idx; ?>][delete]" value="1"> Hapus</label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div style="background:#f0fdf4;border:1.5px dashed #166534;border-radius:12px;padding:16px;">
                    <strong style="display:block;color:#0D3B22;margin-bottom:10px;">➕ Tambah Fasilitas Sarana &amp; Prasarana Baru</strong>
                    <div style="display:grid;grid-template-columns:160px 1.5fr 1fr 1fr 80px 110px;gap:10px;margin-bottom:10px;">
                        <input type="text" name="new_sarpras[kategori]" placeholder="Kategori (mis. Studio)" class="widefat">
                        <input type="text" name="new_sarpras[nama]" placeholder="Nama Ruangan Baru (isi untuk menambah)..." class="widefat">
                        <input type="text" name="new_sarpras[kapasitas]" placeholder="Kapasitas" class="widefat">
                        <input type="text" name="new_sarpras[lokasi]" placeholder="Lokasi Gedung" class="widefat">
                        <input type="text" name="new_sarpras[icon]" value="🏛️" class="widefat" style="text-align:center;">
                        <select name="new_sarpras[tema]" class="widefat">
                            <option value="green">Hijau</option>
                            <option value="gold">Emas</option>
                            <option value="indigo">Biru</option>
                        </select>
                    </div>
                    <div style="margin-bottom:10px;">
                        <input type="text" name="new_sarpras[deskripsi]" placeholder="Deskripsi lengkap spesifikasi fasilitas ruang..." class="widefat">
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <?php ppb_render_media_url_control(['id' => 'sp_foto_new', 'name' => 'new_sarpras[foto_url]', 'value' => '', 'library' => 'image', 'placeholder' => 'URL / Upload Foto Ruangan Baru', 'btn_label' => '📷 Upload Foto']); ?>
                        <?php ppb_render_media_url_control(['id' => 'sp_dok_new', 'name' => 'new_sarpras[dok_url]', 'value' => '', 'placeholder' => 'Link SOP / Denah PDF (opsional)', 'btn_label' => '📁 Upload PDF']); ?>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>
    <?php
}
}

/* ========================================================================== */
/* 5. SHORTCODE: LAYANAN PERSURATAN MAHASISWA & FORMAT NOMOR SURAT RESMI      */
/* ========================================================================== */
if (!function_exists('ppb_shortcode_layanan_persuratan')) {
function ppb_shortcode_layanan_persuratan($atts = []) {
    $sipadu_raw = get_option('ppb_layanan_sipadu_url', 'https://bk-connect-fipunm.vercel.app/sipadu');
    $sipadu_url = function_exists('ppb_clean_document_url') ? ppb_clean_document_url($sipadu_raw) : esc_url_raw((string) $sipadu_raw);
    if ($sipadu_url === '') {
        $sipadu_url = 'https://bk-connect-fipunm.vercel.app/sipadu';
    }

    $tpl_items = [];
    $tpl_q = new WP_Query([
        'post_type'      => 'ppb_template_dok',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => ['menu_order' => 'ASC', 'date' => 'DESC'],
    ]);
    if ($tpl_q->have_posts()) {
        while ($tpl_q->have_posts()) {
            $tpl_q->the_post();
            $tid = get_the_ID();
            $tlink = function_exists('ppb_clean_document_url')
                ? ppb_clean_document_url(get_post_meta($tid, '_ppb_tpl_file_link', true))
                : esc_url_raw((string) get_post_meta($tid, '_ppb_tpl_file_link', true));
            $tpl_unit = sanitize_key(get_post_meta($tid, '_ppb_tpl_unit', true)) ?: 'semua';
            $tpl_items[] = [
                'unit'      => $tpl_unit,
                'title'     => (string) get_the_title(),
                'kat'       => (string) get_post_meta($tid, '_ppb_tpl_kategori', true),
                'format'    => (string) (get_post_meta($tid, '_ppb_tpl_format_file', true) ?: 'DOCX'),
                'revisi'    => (string) get_post_meta($tid, '_ppb_tpl_nomor_revisi', true),
                'deskripsi' => (string) get_post_meta($tid, '_ppb_tpl_deskripsi', true),
                'link'      => $tlink,
            ];
        }
        wp_reset_postdata();
    }

    $sop_items = [];
    $sop_q = new WP_Query([
        'post_type'      => 'ppb_alur_sop',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => ['menu_order' => 'ASC', 'date' => 'DESC'],
    ]);
    if ($sop_q->have_posts()) {
        while ($sop_q->have_posts()) {
            $sop_q->the_post();
            $sid = get_the_ID();
            $slink = function_exists('ppb_clean_document_url')
                ? ppb_clean_document_url(get_post_meta($sid, '_ppb_sop_dok_link', true))
                : esc_url_raw((string) get_post_meta($sid, '_ppb_sop_dok_link', true));
            $steps = [];
            for ($si = 1; $si <= 5; $si++) {
                $st = trim((string) get_post_meta($sid, '_ppb_sop_langkah_' . $si, true));
                if ($st !== '') $steps[] = $st;
            }
            $sop_items[] = [
                'unit'     => sanitize_key(get_post_meta($sid, '_ppb_sop_unit', true)) ?: 'semua',
                'title'    => (string) get_the_title(),
                'nomor'    => (string) get_post_meta($sid, '_ppb_sop_nomor', true),
                'estimasi' => (string) get_post_meta($sid, '_ppb_sop_estimasi', true),
                'link'     => $slink,
                'steps'    => $steps,
            ];
        }
        wp_reset_postdata();
    }

    $unit_filter_opts = ['semua' => 'Semua Jenjang & Jurusan'];
    if (function_exists('ppb_get_unit_options')) {
        $unit_filter_opts = array_merge($unit_filter_opts, ppb_get_unit_options(true));
    }
    $init_unit = 'semua';
    if (isset($_GET['jenjang'])) {
        $req_u = sanitize_key(wp_unslash($_GET['jenjang']));
        if ($req_u !== '' && isset($unit_filter_opts[$req_u])) {
            $init_unit = $req_u;
        }
    }

    $find_tpl_link = function($needles) use ($tpl_items) {
        foreach ($needles as $n) {
            foreach ($tpl_items as $it) {
                if ($it['link'] === '') continue;
                $hay = strtolower($it['kat'] . ' ' . $it['title'] . ' ' . $it['revisi']);
                if (strpos($hay, strtolower($n)) !== false) {
                    return $it['link'];
                }
            }
        }
        return '';
    };

    $tpl_aktif  = $find_tpl_link(['aktif kuliah', 'kemahasiswaan', 'beasiswa', 'f-06']);
    $tpl_riset  = $find_tpl_link(['izin_penelitian', 'izin penelitian', 'observasi', 'f-03']);
    $tpl_magang = $find_tpl_link(['akademik_umum', 'magang', 'ppl', 'mbkm']);
    $tpl_lab    = $find_tpl_link(['bebas_lab', 'peminjaman lab', 'bebas lab', 'f-05', 'alat tes']);

    $render_tpl_btn = function($url) {
        if ($url !== '') {
            return '<a href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer" style="background:#FAF8F5;color:#0D3B22;border:1px solid #D4E3D8;font-size:12px;font-weight:600;padding:9px 12px;border-radius:8px;text-decoration:none;white-space:nowrap;">Template &#8599;</a>';
        }
        return '<span style="background:#F3F4F6;color:#6B7280;border:1px solid #E5E7EB;font-size:11.5px;font-weight:600;padding:9px 12px;border-radius:8px;white-space:nowrap;">Via SIPADU</span>';
    };

    ob_start();
    ?>
    <section class="ppb-persuratan-wrapper" style="font-family:'Plus Jakarta Sans',sans-serif;color:#1F2937;margin:30px 0;">
        
        <!-- Header & Aturan Kode Nomor Surat Resmi -->
        <div style="background:#0D3B22;color:#ffffff;border-radius:20px;padding:30px;margin-bottom:35px;box-shadow:0 10px 25px rgba(13,59,34,0.15);position:relative;overflow:hidden;">
            <div style="position:relative;z-index:2;">
                <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(245,184,46,0.2);color:#F5B82E;padding:4px 14px;border-radius:999px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:12px;border:1px solid rgba(245,184,46,0.35);">
                    <span>Standar Tata Kelola Dokumen &amp; Persuratan</span>
                </div>
                <h2 style="font-family:'Fraunces',Georgia,serif;font-size:clamp(22px, 3vw, 30px);color:#ffffff;margin:0 0 10px;font-weight:600;">
                    Layanan Persuratan &amp; Standar Format Nomor Surat Resmi
                </h2>
                <p style="color:rgba(255,255,255,0.85);font-size:14px;max-width:760px;margin:0 0 20px;line-height:1.6;">
                    Setiap surat keterangan dan rekomendasi akademik yang diterbitkan oleh Program Studi Bimbingan dan Konseling / Jurusan PPB FIP UNM wajib mencantumkan kode klasifikasi arsip dan nomor registrasi surat resmi Universitas Negeri Makassar.
                </p>

                <!-- Box Kode Penomoran Resmi -->
                <div style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15);border-radius:14px;padding:16px 20px;">
                    <div style="font-size:12px;font-weight:700;color:#F5B82E;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.05em;">
                        Rumus Kode Penomoran Surat Resmi UNM:
                    </div>
                    <div style="font-family:monospace;font-size:clamp(13px, 2.5vw, 17px);color:#ffffff;font-weight:700;background:rgba(0,0,0,0.25);padding:10px 16px;border-radius:8px;border-left:4px solid #F5B82E;margin-bottom:8px;">
                        B / [Nomor Urut Surat] / UN36.1 / [Kode Hal] / [Tahun Berjalan]
                    </div>
                    <div style="font-size:12px;color:rgba(255,255,255,0.8);display:flex;flex-wrap:wrap;gap:12px;line-height:1.4;">
                        <span>&bull; <strong>UN36.1</strong>: Kode Fakultas Ilmu Pendidikan UNM</span>
                        <span>&bull; <strong>PP</strong>: Pendidikan &amp; Pengajaran</span>
                        <span>&bull; <strong>PT</strong>: Penelitian &amp; Riset</span>
                        <span>&bull; <strong>KM</strong>: Kemahasiswaan &amp; Magang</span>
                        <span>&bull; <strong>LL</strong>: Layanan Laboratorium</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daftar Jenis Layanan Persuratan -->
        <h3 style="font-family:'Fraunces',Georgia,serif;font-size:22px;color:#0D3B22;margin:0 0 20px;">
            Katalog Dokumen &amp; Jenis Layanan Persuratan
        </h3>
        
        <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));gap:20px;margin-bottom:40px;">
            
            <!-- 1. Surat Keterangan Aktif Kuliah -->
            <div style="background:#ffffff;border:1px solid #D4E3D8;border-radius:16px;padding:22px;box-shadow:0 4px 15px rgba(0,0,0,0.03);display:flex;flex-direction:column;justify-content:space-between;">
                <div>
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px;">
                        <span style="background:#E8F0EA;color:#0D3B22;font-size:11px;font-weight:700;padding:3px 10px;border-radius:6px;">Kode: PP</span>
                        <span style="font-family:monospace;font-size:11px;color:#6B7280;">B/.../UN36.1/PP/2026</span>
                    </div>
                    <h4 style="font-size:17px;font-weight:700;color:#0D3B22;margin:0 0 8px;">Surat Keterangan Aktif Kuliah</h4>
                    <p style="font-size:13px;color:#4B5563;line-height:1.5;margin:0 0 14px;">
                        Dibutuhkan untuk pengurusan tunjangan gaji orang tua, BPJS Kesehatan, dan pengajuan beasiswa eksternal.
                    </p>
                    <div style="font-size:12px;background:#FAF8F5;padding:10px 12px;border-radius:8px;border:1px dashed #D4E3D8;margin-bottom:16px;">
                        <strong style="color:#0D3B22;">Syarat Pengajuan:</strong>
                        <ul style="margin:4px 0 0;padding-left:18px;color:#6B7280;line-height:1.5;">
                            <li>KRS semester berjalan (sudah tervalidasi PA)</li>
                            <li>KTM / Slip Pembayaran UKT terbaru</li>
                        </ul>
                    </div>
                </div>
                <div style="display:flex;gap:8px;">
                    <button type="button" onclick="if(window.ppbSelectJenisSurat)ppbSelectJenisSurat('aktif_kuliah');" style="flex:1;text-align:center;background:#0D3B22;color:#F5B82E;border:none;font-size:12.5px;font-weight:700;padding:9px 12px;border-radius:8px;cursor:pointer;">Ajukan Nomor Surat &rarr;</button>
                    <?php echo $render_tpl_btn($tpl_aktif); ?>
                </div>
            </div>

            <!-- 2. Surat Izin Observasi / Penelitian -->
            <div style="background:#ffffff;border:1px solid #D4E3D8;border-radius:16px;padding:22px;box-shadow:0 4px 15px rgba(0,0,0,0.03);display:flex;flex-direction:column;justify-content:space-between;">
                <div>
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px;">
                        <span style="background:#E8F0EA;color:#0D3B22;font-size:11px;font-weight:700;padding:3px 10px;border-radius:6px;">Kode: PT</span>
                        <span style="font-family:monospace;font-size:11px;color:#6B7280;">B/.../UN36.1/PT/2026</span>
                    </div>
                    <h4 style="font-size:17px;font-weight:700;color:#0D3B22;margin:0 0 8px;">Surat Izin Penelitian &amp; Observasi Skripsi</h4>
                    <p style="font-size:13px;color:#4B5563;line-height:1.5;margin:0 0 14px;">
                        Surat permohonan izin riset atau pengambilan data di sekolah mitra, instansi, atau komunitas.
                    </p>
                    <div style="font-size:12px;background:#FAF8F5;padding:10px 12px;border-radius:8px;border:1px dashed #D4E3D8;margin-bottom:16px;">
                        <strong style="color:#0D3B22;">Syarat Pengajuan:</strong>
                        <ul style="margin:4px 0 0;padding-left:18px;color:#6B7280;line-height:1.5;">
                            <li>Persetujuan Judul &amp; Tanda Tangan Pembimbing</li>
                            <li>Identitas lengkap lokasi &amp; kepala sekolah sasaran</li>
                        </ul>
                    </div>
                </div>
                <div style="display:flex;gap:8px;">
                    <button type="button" onclick="if(window.ppbSelectJenisSurat)ppbSelectJenisSurat('izin_penelitian');" style="flex:1;text-align:center;background:#0D3B22;color:#F5B82E;border:none;font-size:12.5px;font-weight:700;padding:9px 12px;border-radius:8px;cursor:pointer;">Ajukan Nomor Surat &rarr;</button>
                    <?php echo $render_tpl_btn($tpl_riset); ?>
                </div>
            </div>

            <!-- 3. Surat Pengantar Magang / MBKM -->
            <div style="background:#ffffff;border:1px solid #D4E3D8;border-radius:16px;padding:22px;box-shadow:0 4px 15px rgba(0,0,0,0.03);display:flex;flex-direction:column;justify-content:space-between;">
                <div>
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px;">
                        <span style="background:#E8F0EA;color:#0D3B22;font-size:11px;font-weight:700;padding:3px 10px;border-radius:6px;">Kode: KM</span>
                        <span style="font-family:monospace;font-size:11px;color:#6B7280;">B/.../UN36.1/KM/2026</span>
                    </div>
                    <h4 style="font-size:17px;font-weight:700;color:#0D3B22;margin:0 0 8px;">Surat Pengantar Magang / MBKM</h4>
                    <p style="font-size:13px;color:#4B5563;line-height:1.5;margin:0 0 14px;">
                        Pengantar resmi pelaksanaan Praktik Pengalaman Lapangan (PPL), Magang Industri, atau Kampus Mengajar.
                    </p>
                    <div style="font-size:12px;background:#FAF8F5;padding:10px 12px;border-radius:8px;border:1px dashed #D4E3D8;margin-bottom:16px;">
                        <strong style="color:#0D3B22;">Syarat Pengajuan:</strong>
                        <ul style="margin:4px 0 0;padding-left:18px;color:#6B7280;line-height:1.5;">
                            <li>Surat Penerimaan dari Mitra / LoA MBKM</li>
                            <li>Konfirmasi Dosen Pembimbing Lapangan (DPL)</li>
                        </ul>
                    </div>
                </div>
                <div style="display:flex;gap:8px;">
                    <button type="button" onclick="if(window.ppbSelectJenisSurat)ppbSelectJenisSurat('pengantar_magang');" style="flex:1;text-align:center;background:#0D3B22;color:#F5B82E;border:none;font-size:12.5px;font-weight:700;padding:9px 12px;border-radius:8px;cursor:pointer;">Ajukan Nomor Surat &rarr;</button>
                    <?php echo $render_tpl_btn($tpl_magang); ?>
                </div>
            </div>

            <!-- 4. Surat Keterangan Bebas Laboratorium -->
            <div style="background:#ffffff;border:1px solid #D4E3D8;border-radius:16px;padding:22px;box-shadow:0 4px 15px rgba(0,0,0,0.03);display:flex;flex-direction:column;justify-content:space-between;">
                <div>
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px;">
                        <span style="background:#E8F0EA;color:#0D3B22;font-size:11px;font-weight:700;padding:3px 10px;border-radius:6px;">Kode: LL</span>
                        <span style="font-family:monospace;font-size:11px;color:#6B7280;">B/.../UN36.1.7/LL/2026</span>
                    </div>
                    <h4 style="font-size:17px;font-weight:700;color:#0D3B22;margin:0 0 8px;">Surat Bebas Laboratorium &amp; Inventaris</h4>
                    <p style="font-size:13px;color:#4B5563;line-height:1.5;margin:0 0 14px;">
                        Syarat wajib yudisium dan bebas pinjaman instrumen tes psikologi/alat asesmen laboratorium BK.
                    </p>
                    <div style="font-size:12px;background:#FAF8F5;padding:10px 12px;border-radius:8px;border:1px dashed #D4E3D8;margin-bottom:16px;">
                        <strong style="color:#0D3B22;">Syarat Pengajuan:</strong>
                        <ul style="margin:4px 0 0;padding-left:18px;color:#6B7280;line-height:1.5;">
                            <li>Verifikasi status pengembalian alat oleh Pranata Lab</li>
                            <li>Bebas tanggungan modul dan instrumen konseling</li>
                        </ul>
                    </div>
                </div>
                <div style="display:flex;gap:8px;">
                    <button type="button" onclick="if(window.ppbSelectJenisSurat)ppbSelectJenisSurat('bebas_lab');" style="flex:1;text-align:center;background:#0D3B22;color:#F5B82E;border:none;font-size:12.5px;font-weight:700;padding:9px 12px;border-radius:8px;cursor:pointer;">Ajukan Nomor Surat &rarr;</button>
                    <?php echo $render_tpl_btn($tpl_lab); ?>
                </div>
            </div>

        </div>

        <?php if (function_exists('ppb_render_nomor_surat_widget')) { echo ppb_render_nomor_surat_widget(); } ?>

        <!-- Alur 3 Langkah Pengajuan Persuratan -->
        <div style="background:#F0F6F2;border:1.5px solid #D4E3D8;border-radius:18px;padding:26px;margin-bottom:36px;">
            <h4 style="font-family:'Fraunces',Georgia,serif;font-size:18px;color:#0D3B22;margin:0 0 16px;">
                Alur Pengajuan &amp; Validasi Nomor Surat Resmi (3 Langkah)
            </h4>
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));gap:20px;">
                <div style="background:#ffffff;padding:18px;border-radius:12px;border:1px solid #E2E8F0;">
                    <span style="font-weight:800;color:#F5B82E;font-size:24px;display:block;line-height:1;margin-bottom:8px;">01</span>
                    <strong style="font-size:14px;color:#0D3B22;display:block;margin-bottom:4px;">Pengisian Formulir Online</strong>
                    <p style="font-size:12.5px;color:#6B7280;margin:0;line-height:1.5;">Mahasiswa mengisi formulir di portal persuratan dengan melampirkan berkas persyaratan.</p>
                </div>
                <div style="background:#ffffff;padding:18px;border-radius:12px;border:1px solid #E2E8F0;">
                    <span style="font-weight:800;color:#F5B82E;font-size:24px;display:block;line-height:1;margin-bottom:8px;">02</span>
                    <strong style="font-size:14px;color:#0D3B22;display:block;margin-bottom:4px;">Penomoran Resmi Prodi</strong>
                    <p style="font-size:12.5px;color:#6B7280;margin:0;line-height:1.5;">Staf Tata Usaha memverifikasi syarat dan menerbitkan nomor surat resmi sesuai buku registrasi.</p>
                </div>
                <div style="background:#ffffff;padding:18px;border-radius:12px;border:1px solid #E2E8F0;">
                    <span style="font-weight:800;color:#F5B82E;font-size:24px;display:block;line-height:1;margin-bottom:8px;">03</span>
                    <strong style="font-size:14px;color:#0D3B22;display:block;margin-bottom:4px;">Tanda Tangan &amp; Unduh Surat</strong>
                    <p style="font-size:12.5px;color:#6B7280;margin:0;line-height:1.5;">Surat ditandatangani Ketua Jurusan/Dekan (TTE) dan dikirimkan langsung ke email mahasiswa.</p>
                </div>
            </div>
        </div>

        <?php if (!empty($tpl_items) || !empty($sop_items)) : ?>
        <!-- Direktori Template Dokumen & SOP per Unit/Jenjang -->
        <div style="border-top:2px solid #E5E7EB;padding-top:30px;">
            <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;margin-bottom:22px;">
                <div>
                    <span style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:0.08em;color:#854D0E;display:block;margin-bottom:2px;">Repositori Formulir &amp; Standar Operasional</span>
                    <h3 style="font-family:'Fraunces',Georgia,serif;font-size:22px;color:#0D3B22;margin:0;">Unduh Template Dokumen &amp; Alur SOP</h3>
                </div>
                <div style="display:flex;flex-wrap:wrap;gap:6px;">
                    <?php foreach ($unit_filter_opts as $uslug => $ulabel) :
                        $is_u = ($init_unit === $uslug);
                        $short_lbl = ($uslug === 'semua') ? 'Semua Unit' : (function_exists('ppb_get_unit_label') ? ppb_get_unit_label($uslug, true) : $ulabel);
                    ?>
                        <button type="button" class="ppb-layanan-unit-btn" data-unit="<?php echo esc_attr($uslug); ?>" style="padding:7px 14px;border-radius:9999px;border:1px solid <?php echo $is_u ? '#0D3B22' : '#D1D5DB'; ?>;background:<?php echo $is_u ? '#0D3B22' : '#ffffff'; ?>;color:<?php echo $is_u ? '#F5B82E' : '#374151'; ?>;font-size:12px;font-weight:700;cursor:pointer;transition:all 0.2s;">
                            <?php echo esc_html($short_lbl); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if (!empty($tpl_items)) : ?>
            <h4 style="font-size:16px;font-weight:700;color:#0D3B22;margin:0 0 12px;">📄 Daftar Formulir &amp; Template Resmi (<?php echo count($tpl_items); ?>)</h4>
            <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(290px, 1fr));gap:16px;margin-bottom:28px;">
                <?php foreach ($tpl_items as $tpl) :
                    $u_badge = function_exists('ppb_render_unit_badge') ? ppb_render_unit_badge($tpl['unit']) : '<span style="font-size:10px;font-weight:800;padding:2px 7px;border-radius:9999px;background:#E8F0EA;color:#0D3B22;">' . esc_html(strtoupper($tpl['unit'])) . '</span>';
                    $show_u  = ($init_unit === 'semua' || $tpl['unit'] === $init_unit || $tpl['unit'] === 'semua') ? 'flex' : 'none';
                ?>
                <div class="ppb-layanan-unit-card" data-unit="<?php echo esc_attr($tpl['unit']); ?>" style="background:#ffffff;border:1px solid #D4E3D8;border-radius:14px;padding:18px;display:<?php echo $show_u; ?>;flex-direction:column;justify-content:space-between;gap:12px;">
                    <div>
                        <div style="display:flex;justify-content:space-between;align-items:center;gap:6px;margin-bottom:8px;flex-wrap:wrap;">
                            <?php echo $u_badge; ?>
                            <span style="font-family:monospace;font-size:11px;color:#6B7280;"><?php echo esc_html($tpl['revisi'] ?: $tpl['format']); ?></span>
                        </div>
                        <h5 style="font-size:14.5px;font-weight:700;color:#0D3B22;margin:0 0 6px;line-height:1.4;"><?php echo esc_html($tpl['title']); ?></h5>
                        <?php if ($tpl['deskripsi'] !== '') : ?>
                            <p style="font-size:12px;color:#4B5563;margin:0;line-height:1.5;"><?php echo esc_html($tpl['deskripsi']); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php if ($tpl['link'] !== '') : ?>
                        <a href="<?php echo esc_url($tpl['link']); ?>" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;justify-content:center;gap:6px;background:#0D3B22;color:#F5B82E;font-size:12px;font-weight:700;padding:8px 14px;border-radius:8px;text-decoration:none;">Unduh Template (<?php echo esc_html($tpl['format']); ?>) &#8599;</a>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($sop_items)) : ?>
            <h4 style="font-size:16px;font-weight:700;color:#0D3B22;margin:0 0 12px;">🔄 Standar Operasional Prosedur (SOP) Layanan (<?php echo count($sop_items); ?>)</h4>
            <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(340px, 1fr));gap:18px;">
                <?php foreach ($sop_items as $sop) :
                    $u_badge = function_exists('ppb_render_unit_badge') ? ppb_render_unit_badge($sop['unit']) : '<span style="font-size:10px;font-weight:800;padding:2px 7px;border-radius:9999px;background:#E8F0EA;color:#0D3B22;">' . esc_html(strtoupper($sop['unit'])) . '</span>';
                    $show_u  = ($init_unit === 'semua' || $sop['unit'] === $init_unit || $sop['unit'] === 'semua') ? 'flex' : 'none';
                ?>
                <div class="ppb-layanan-unit-card" data-unit="<?php echo esc_attr($sop['unit']); ?>" style="background:#FAF8F5;border:1px solid #D4E3D8;border-radius:14px;padding:20px;display:<?php echo $show_u; ?>;flex-direction:column;justify-content:space-between;gap:12px;">
                    <div>
                        <div style="display:flex;justify-content:space-between;align-items:center;gap:6px;margin-bottom:8px;flex-wrap:wrap;">
                            <?php echo $u_badge; ?>
                            <span style="font-size:11px;font-weight:700;color:#059669;">⏱ <?php echo esc_html($sop['estimasi']); ?></span>
                        </div>
                        <h5 style="font-size:15px;font-weight:700;color:#0D3B22;margin:0 0 8px;line-height:1.4;"><?php echo esc_html($sop['title']); ?></h5>
                        <?php if (!empty($sop['steps'])) : ?>
                            <ol style="font-size:12px;color:#374151;line-height:1.55;padding-left:18px;margin:0;">
                                <?php foreach ($sop['steps'] as $st) : ?>
                                    <li style="margin-bottom:4px;"><?php echo esc_html($st); ?></li>
                                <?php endforeach; ?>
                            </ol>
                        <?php endif; ?>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;padding-top:8px;border-top:1px dashed #D4E3D8;">
                        <code style="font-size:11px;color:#6B7280;"><?php echo esc_html($sop['nomor']); ?></code>
                        <?php if ($sop['link'] !== '') : ?>
                            <a href="<?php echo esc_url($sop['link']); ?>" target="_blank" rel="noopener noreferrer" style="font-size:12px;font-weight:700;color:#0D3B22;text-decoration:none;">Unduh SOP PDF &#8599;</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <script>
        (function(){
            var btns = document.querySelectorAll('.ppb-layanan-unit-btn');
            btns.forEach(function(btn){
                btn.addEventListener('click', function(){
                    var target = btn.getAttribute('data-unit') || 'semua';
                    btns.forEach(function(b){
                        var act = (b === btn);
                        b.style.background = act ? '#0D3B22' : '#ffffff';
                        b.style.color = act ? '#F5B82E' : '#374151';
                        b.style.borderColor = act ? '#0D3B22' : '#D1D5DB';
                    });
                    document.querySelectorAll('.ppb-layanan-unit-card').forEach(function(c){
                        var u = c.getAttribute('data-unit') || 'semua';
                        c.style.display = (target === 'semua' || u === target || u === 'semua') ? 'flex' : 'none';
                    });
                });
            });
        })();
        </script>
        <?php endif; ?>

    </section>
    <?php
    return ob_get_clean();
}
}
add_shortcode('ppb_layanan_persuratan', 'ppb_shortcode_layanan_persuratan');

/* ========================================================================== */
/* 6. SHORTCODE: MODUL LABORATORIUM BK (KEGIATAN, LOMBA, & TAUTAN PORTAL)     */
/* ========================================================================== */
if (!function_exists('ppb_shortcode_lab_portals')) {
function ppb_shortcode_lab_portals($atts = []) {
    ob_start();
    ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:20px;margin:30px 0;">
        
        <!-- Portal 1: Portal 5 Bidang Lab BK -->
        <div style="background:linear-gradient(135deg, #0D3B22 0%, #155734 100%);color:#ffffff;border-radius:18px;padding:24px;box-shadow:0 8px 24px rgba(13,59,34,0.15);position:relative;">
            <div style="display:inline-block;background:rgba(245,184,46,0.2);color:#F5B82E;font-size:10px;font-weight:700;padding:2px 8px;border-radius:4px;text-transform:uppercase;margin-bottom:10px;">Portal Terpadu</div>
            <h3 style="font-family:'Fraunces',Georgia,serif;font-size:20px;color:#ffffff;margin:0 0 6px;">5 Bidang Laboratorium BK</h3>
            <p style="font-size:13px;color:rgba(255,255,255,0.8);line-height:1.5;margin:0 0 18px;">
                Jelajahi layanan Bidang Konseling, Asesmen, Training &amp; Development, Media, dan Ekstrakurikuler mahasiswa BK FIP UNM.
            </p>
            <a href="<?php echo esc_url(home_url('/lab-bk/')); ?>" style="display:inline-flex;align-items:center;gap:6px;background:#F5B82E;color:#0D3B22;font-size:13px;font-weight:700;padding:10px 18px;border-radius:10px;text-decoration:none;">
                <span>Jelajahi 5 Bidang</span> &rarr;
            </a>
        </div>

        <!-- Portal 2: Pendaftaran Konseling & Peer Support -->
        <div style="background:linear-gradient(135deg, #1E3A8A 0%, #2563EB 100%);color:#ffffff;border-radius:18px;padding:24px;box-shadow:0 8px 24px rgba(30,58,138,0.15);position:relative;">
            <div style="display:inline-block;background:rgba(255,255,255,0.2);color:#ffffff;font-size:10px;font-weight:700;padding:2px 8px;border-radius:4px;text-transform:uppercase;margin-bottom:10px;">Konseling Sebaya</div>
            <h3 style="font-family:'Fraunces',Georgia,serif;font-size:20px;color:#ffffff;margin:0 0 6px;">Pendaftaran Konseling</h3>
            <p style="font-size:13px;color:rgba(255,255,255,0.8);line-height:1.5;margin:0 0 18px;">
                Ruang aman konseling sebaya dan pendampingan mahasiswa BK UNM untuk konsultasi kesehatan mental dan adaptasi kampus.
            </p>
            <a href="https://labbk8.wixsite.com/peersupport" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;gap:6px;background:#ffffff;color:#1E3A8A;font-size:13px;font-weight:700;padding:10px 18px;border-radius:10px;text-decoration:none;">
                <span>Daftar Konseling</span> &rarr;
            </a>
        </div>

        <!-- Portal 3: Web Laboratorium BK -->
        <div style="background:linear-gradient(135deg, #7C2D12 0%, #B45309 100%);color:#ffffff;border-radius:18px;padding:24px;box-shadow:0 8px 24px rgba(124,45,18,0.15);position:relative;">
            <div style="display:inline-block;background:rgba(255,255,255,0.2);color:#ffffff;font-size:10px;font-weight:700;padding:2px 8px;border-radius:4px;text-transform:uppercase;margin-bottom:10px;">Inventaris Lab</div>
            <h3 style="font-family:'Fraunces',Georgia,serif;font-size:20px;color:#ffffff;margin:0 0 6px;">Web Lab BK &amp; Alat Tes</h3>
            <p style="font-size:13px;color:rgba(255,255,255,0.8);line-height:1.5;margin:0 0 18px;">
                Katalog digital alat tes psikologi, jadwal pemakaian ruang konseling individual/kelompok, dan panduan praktikum.
            </p>
            <a href="<?php echo esc_url(home_url('/lab-bk/inventaris/')); ?>" style="display:inline-flex;align-items:center;gap:6px;background:#ffffff;color:#7C2D12;font-size:13px;font-weight:700;padding:10px 18px;border-radius:10px;text-decoration:none;">
                <span>Cek Inventaris Lab</span> &rarr;
            </a>
        </div>

    </div>
    <?php
    return ob_get_clean();
}
}
add_shortcode('ppb_lab_portals', 'ppb_shortcode_lab_portals');

if (!function_exists('ppb_shortcode_lab_kegiatan')) {
function ppb_shortcode_lab_kegiatan($atts = []) {
    $kegiatan = function_exists('ppb_get_lab_kegiatan_list') ? ppb_get_lab_kegiatan_list() : [];
    ob_start();
    ?>
    <div style="margin:25px 0;">
        <h3 style="font-family:'Fraunces',Georgia,serif;font-size:22px;color:#0D3B22;margin:0 0 16px;">
            Kegiatan &amp; Praktikum Mahasiswa Laboratorium BK
        </h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));gap:20px;">
            <?php foreach ($kegiatan as $kg) :
                $dok_url = !empty($kg['dok_url']) && function_exists('ppb_clean_document_url')
                    ? ppb_clean_document_url($kg['dok_url'])
                    : trim((string) ($kg['dok_url'] ?? ''));
            ?>
                <div style="background:#ffffff;border:1px solid #D4E3D8;border-radius:16px;padding:22px;box-shadow:0 4px 15px rgba(0,0,0,0.03);display:flex;flex-direction:column;justify-content:space-between;">
                    <div>
                        <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;margin-bottom:8px;flex-wrap:wrap;">
                            <span style="font-size:11px;font-weight:700;background:#E8F0EA;color:#0D3B22;padding:3px 10px;border-radius:6px;"><?php echo esc_html($kg['status'] ?? 'Aktif Berjalan'); ?></span>
                            <span style="font-size:12px;color:#6B7280;font-weight:500;"><?php echo esc_html($kg['periode'] ?? ''); ?></span>
                        </div>
                        <h4 style="font-size:16px;font-weight:700;color:#0D3B22;margin:0 0 8px;line-height:1.4;"><?php echo esc_html($kg['judul'] ?? ''); ?></h4>
                        <p style="font-size:13px;color:#4B5563;line-height:1.6;margin:0 0 12px;"><?php echo esc_html($kg['deskripsi'] ?? ''); ?></p>
                    </div>
                    <div>
                        <div style="font-size:12px;color:#0D3B22;font-weight:600;display:flex;align-items:center;gap:6px;">
                            <span>👥 Sasaran: <?php echo esc_html($kg['peserta'] ?? ''); ?></span>
                        </div>
                        <?php if ($dok_url !== '') : ?>
                            <a href="<?php echo esc_url($dok_url); ?>" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;gap:6px;margin-top:12px;background:#E8F0EA;color:#0D3B22;border:1px solid #B7D0BE;font-size:12px;font-weight:700;padding:7px 12px;border-radius:8px;text-decoration:none;">📄 Panduan / Dokumentasi &#8599;</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
}
add_shortcode('ppb_lab_kegiatan', 'ppb_shortcode_lab_kegiatan');

if (!function_exists('ppb_shortcode_lab_lomba')) {
function ppb_shortcode_lab_lomba($atts = []) {
    $lomba = function_exists('ppb_get_lab_lomba_list') ? ppb_get_lab_lomba_list() : [];
    ob_start();
    ?>
    <div style="margin:25px 0;">
        <h3 style="font-family:'Fraunces',Georgia,serif;font-size:22px;color:#0D3B22;margin:0 0 16px;">
            Informasi Lomba Non-Akademik Mahasiswa Lab BK
        </h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));gap:20px;">
            <?php foreach ($lomba as $lb) :
                $dok_url = !empty($lb['dok_url']) && function_exists('ppb_clean_document_url')
                    ? ppb_clean_document_url($lb['dok_url'])
                    : trim((string) ($lb['dok_url'] ?? ''));
            ?>
                <div style="background:#ffffff;border:1px solid #D4E3D8;border-radius:16px;padding:22px;box-shadow:0 4px 15px rgba(0,0,0,0.03);display:flex;flex-direction:column;justify-content:space-between;">
                    <div>
                        <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;margin-bottom:8px;flex-wrap:wrap;">
                            <span style="font-size:11px;font-weight:700;background:#FEF3C7;color:#92400E;padding:3px 10px;border-radius:6px;"><?php echo esc_html($lb['kategori'] ?? 'Tingkat Nasional'); ?></span>
                            <span style="font-size:11px;color:#6B7280;"><?php echo esc_html($lb['lingkup'] ?? ''); ?></span>
                        </div>
                        <h4 style="font-size:16px;font-weight:700;color:#0D3B22;margin:0 0 8px;line-height:1.4;"><?php echo esc_html($lb['nama'] ?? ''); ?></h4>
                        <p style="font-size:12.5px;color:#4B5563;margin:0 0 10px;">
                            <strong>Penyelenggara:</strong> <?php echo esc_html(html_entity_decode((string) ($lb['penyelenggara'] ?? ''), ENT_QUOTES, 'UTF-8')); ?>
                        </p>
                    </div>
                    <div>
                        <div style="font-size:12.5px;background:#FAF8F5;border:1px solid #E2E8F0;padding:8px 12px;border-radius:8px;color:#0D3B22;font-weight:600;">
                            🏆 Capaian: <?php echo esc_html(html_entity_decode((string) ($lb['prestasi'] ?? ''), ENT_QUOTES, 'UTF-8')); ?>
                        </div>
                        <?php if ($dok_url !== '') : ?>
                            <a href="<?php echo esc_url($dok_url); ?>" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;gap:6px;margin-top:10px;background:#0D3B22;color:#ffffff;font-size:12px;font-weight:700;padding:7px 12px;border-radius:8px;text-decoration:none;">📄 Juknis / Dokumentasi &#8599;</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
}
add_shortcode('ppb_lab_lomba', 'ppb_shortcode_lab_lomba');

/* ========================================================================== */
/* 7. E-LIBRARY & REPOSITORI MODUL BK (CRUD, PORTAL LINKS, & KURASI NASKAH)   */
/* ========================================================================== */
if (!function_exists('ppb_get_elibrary_default_portal_links')) {
function ppb_get_elibrary_default_portal_links() {
    return array(
        array(
            'badge' => 'OJS · SINTA 2',
            'title' => 'Jurnal JKBK UNM',
            'sub'   => 'Jurnal Kajian Bimbingan & Konseling · E-ISSN 2549-8517',
            'cta'   => 'Portal OJS JKBK ↗',
            'href'  => 'https://journal.unm.ac.id/index.php/JKBK',
        ),
        array(
            'badge' => 'OJS · SINTA 4',
            'title' => 'Jurnal PJEC UNM',
            'sub'   => 'Pinisi Journal of Education & Counseling · E-ISSN 2809-5480',
            'cta'   => 'Portal OJS PJEC ↗',
            'href'  => 'https://ojs.unm.ac.id/PJEC',
        ),
        array(
            'badge' => '172 BUKU',
            'title' => 'Katalog Buku & Karya Dosen',
            'sub'   => 'Monograf ber-ISBN & publikasi SINTA 25 dosen PPB',
            'cta'   => 'Buka Direktori Dosen →',
            'href'  => home_url('/profil/dosen/'),
        ),
    );
}
}

if (!function_exists('ppb_get_elibrary_portal_links')) {
function ppb_get_elibrary_portal_links() {
    $saved = get_option('ppb_elibrary_portal_links', array());
    if (!is_array($saved) || empty($saved)) {
        return ppb_get_elibrary_default_portal_links();
    }
    return array_values($saved);
}
}

if (!function_exists('ppb_get_elibrary_default_items')) {
function ppb_get_elibrary_default_items() {
    return array(
        array(
            'id'        => 1,
            'kode'      => 'ELB-01',
            'category'  => 'modul',
            'cat_label' => 'Modul Praktikum',
            'authors'   => 'Tim Dosen Laboratorium BK FIP UNM',
            'year'      => '2025',
            'meta'      => '64 Hlm · PDF 3.2 MB',
            'pages'     => '64 Hlm',
            'size'      => '3.2 MB',
            'format'    => 'PDF',
            'title'     => 'Modul Praktikum Konseling Individual Berbasis Kognitif-Perilaku (CBT)',
            'desc'      => 'Panduan terstruktur praktikum microcounseling dan penerapan cognitive restructuring bagi mahasiswa sarjana.',
            'file_url'  => '#',
        ),
        array(
            'id'        => 2,
            'kode'      => 'ELB-02',
            'category'  => 'buku',
            'cat_label' => 'Pedoman Akademik',
            'authors'   => 'Program Studi BK FIP UNM',
            'year'      => '2025',
            'meta'      => '88 Hlm · PDF 4.1 MB',
            'pages'     => '88 Hlm',
            'size'      => '4.1 MB',
            'format'    => 'PDF',
            'title'     => 'Buku Pedoman Praktik Pengalaman Lapangan (PPL) BK Sekolah',
            'desc'      => 'Ketentuan teknis, format laporan, instrumen penilaian, dan tata tertib pelaksanaan PPL BK di SMP/SMA/SMK mitra.',
            'file_url'  => '#',
        ),
        array(
            'id'        => 3,
            'kode'      => 'ELB-03',
            'category'  => 'asesmen',
            'cat_label' => 'Instrumen Asesmen',
            'authors'   => 'Laboratorium BK FIP UNM',
            'year'      => '2024',
            'meta'      => '76 Hlm · PDF 2.8 MB',
            'pages'     => '76 Hlm',
            'size'      => '2.8 MB',
            'format'    => 'PDF',
            'title'     => 'Modul Asesmen Psikologis Non-Tes: Angket, Sosiometri & AUM',
            'desc'      => 'Petunjuk administrasi, skoring, tabulasi, dan interpretasi profil kebutuhan konseli menggunakan AUM dan Sosiometri.',
            'file_url'  => '#',
        ),
        array(
            'id'        => 4,
            'kode'      => 'ELB-04',
            'category'  => 'artikel',
            'cat_label' => 'Artikel Ilmiah',
            'authors'   => 'Sivitas Akademika BK FIP UNM',
            'year'      => '2024',
            'meta'      => '14 Hlm · PDF 850 KB',
            'pages'     => '14 Hlm',
            'size'      => '850 KB',
            'format'    => 'PDF',
            'title'     => 'Pengembangan Model Bimbingan Karir Berorientasi Industri 4.0',
            'desc'      => 'Kajian empiris efektivitas model bimbingan karir kontekstual dalam meningkatkan career readiness siswa menengah.',
            'file_url'  => '#',
        ),
        array(
            'id'        => 5,
            'kode'      => 'ELB-05',
            'category'  => 'modul',
            'cat_label' => 'Modul Pembelajaran',
            'authors'   => 'Pengampu MK Konseling Kelompok',
            'year'      => '2024',
            'meta'      => '92 Hlm · PDF 3.6 MB',
            'pages'     => '92 Hlm',
            'size'      => '3.6 MB',
            'format'    => 'PDF',
            'title'     => 'Diktat Dinamika Konseling Kelompok & Manajemen Konflik Remaja',
            'desc'      => 'Konsep tahapan pembentukan, peralihan, kegiatan, dan pengakhiran dalam kelompok serta teknik manajemen resistensi.',
            'file_url'  => '#',
        ),
        array(
            'id'        => 6,
            'kode'      => 'ELB-06',
            'category'  => 'buku',
            'cat_label' => 'Pedoman Akademik',
            'authors'   => 'GPM Program Studi BK UNM',
            'year'      => '2025',
            'meta'      => '110 Hlm · PDF 5.2 MB',
            'pages'     => '110 Hlm',
            'size'      => '5.2 MB',
            'format'    => 'PDF',
            'title'     => 'Panduan Penulisan Skripsi & Tugas Akhir Bimbingan dan Konseling',
            'desc'      => 'Standar format proposal, laporan hasil, artikel jurnal syarat yudisium, kode etik sitasi APA 7th, dan uji Turnitin.',
            'file_url'  => '#',
        ),
    );
}
}

if (!function_exists('ppb_get_elibrary_items')) {
function ppb_get_elibrary_items() {
    $saved = get_option('ppb_elibrary_items', array());
    if (!is_array($saved) || empty($saved)) {
        return ppb_get_elibrary_default_items();
    }
    $normalized = array();
    foreach (array_values($saved) as $idx => $it) {
        $id = isset($it['id']) ? (int) $it['id'] : ($idx + 1);
        $kode = !empty($it['kode']) ? $it['kode'] : sprintf('ELB-%02d', $idx + 1);
        $cat = !empty($it['category']) ? $it['category'] : (!empty($it['cat']) ? $it['cat'] : 'modul');
        $cat_label = !empty($it['cat_label']) ? $it['cat_label'] : (!empty($it['catLabel']) ? $it['catLabel'] : 'Modul Praktikum');
        $format = !empty($it['format']) ? $it['format'] : 'PDF';
        $meta = !empty($it['meta']) ? $it['meta'] : trim((isset($it['pages']) ? $it['pages'] : '60 Hlm') . ' · ' . $format . ' ' . (isset($it['size']) ? $it['size'] : '2.5 MB'));
        $normalized[] = array(
            'id'        => $id,
            'kode'      => $kode,
            'category'  => $cat,
            'cat_label' => $cat_label,
            'authors'   => !empty($it['authors']) ? $it['authors'] : 'Tim Dosen BK FIP UNM',
            'year'      => !empty($it['year']) ? $it['year'] : date('Y'),
            'meta'      => $meta,
            'format'    => $format,
            'title'     => !empty($it['title']) ? $it['title'] : 'Modul Bimbingan dan Konseling',
            'desc'      => !empty($it['desc']) ? $it['desc'] : '',
            'file_url'  => !empty($it['file_url']) ? $it['file_url'] : '#',
        );
    }
    return $normalized;
}
}

if (!function_exists('ppb_get_elibrary_submissions')) {
function ppb_get_elibrary_submissions() {
    $subs = get_option('ppb_elibrary_submissions', array());
    return is_array($subs) ? $subs : array();
}
}

if (!function_exists('ppb_render_elibrary_workspace_html')) {
function ppb_render_elibrary_workspace_html($include_tabs = true) {
    $elibrary_items = ppb_get_elibrary_items();
    $portal_links   = ppb_get_elibrary_portal_links();
    $ajax_url       = admin_url('admin-ajax.php');
    $submit_nonce   = function_exists('wp_create_nonce') ? wp_create_nonce('ppb_elibrary_submit_nonce') : '';
    $sipadu_raw     = (string) get_option('ppb_layanan_sipadu_url', 'https://bk-connect-fipunm.vercel.app/sipadu');
    $sipadu_url     = function_exists('ppb_clean_document_url') ? (ppb_clean_document_url($sipadu_raw) ?: 'https://bk-connect-fipunm.vercel.app/sipadu') : $sipadu_raw;

    $normalized_items = array();
    foreach ($elibrary_items as $idx => $item) {
        $raw_url = isset($item['file_url']) ? (string) $item['file_url'] : '';
        $clean_url = function_exists('ppb_clean_document_url')
            ? ppb_clean_document_url($raw_url)
            : (($raw_url === '#') ? '' : trim($raw_url));
        $item['id']       = isset($item['id']) ? (int) $item['id'] : ($idx + 1);
        $item['kode']     = !empty($item['kode']) ? $item['kode'] : sprintf('ELB-%02d', $idx + 1);
        $item['file_url'] = $clean_url;
        $normalized_items[] = $item;
    }

    ob_start();
    ?>
    <div class="ppb-elibrary-workspace" data-ppb-elib-root="1">
        <?php if ($include_tabs) : ?>
            <nav class="bk-tabs" aria-label="Sub-menu Layanan &amp; Laboratorium BK" style="margin-bottom:24px;">
                <a href="<?php echo esc_url(home_url('/layanan/persuratan/')); ?>" class="bk-tab-btn">Persuratan &amp; SOP</a>
                <a href="<?php echo esc_url(home_url('/lab-bk/inventaris/')); ?>" class="bk-tab-btn">Lab BK &amp; Alat Tes</a>
                <a href="<?php echo esc_url(home_url('/e-library/')); ?>" class="bk-tab-btn is-active" aria-current="page">E-Library &amp; Modul</a>
                <a href="<?php echo esc_url(home_url('/layanan/perlindungan-mahasiswa/')); ?>" class="bk-tab-btn">Perlindungan &amp; Survei</a>
            </nav>
        <?php endif; ?>

        <div class="bk-elib-links" aria-label="Tautan Repositori &amp; Jurnal Resmi">
            <?php foreach ($portal_links as $lk) :
                $href_raw = isset($lk['href']) ? (string) $lk['href'] : '';
                $href     = function_exists('ppb_clean_document_url') ? ppb_clean_document_url($href_raw) : (($href_raw === '#') ? '' : trim($href_raw));
                $is_ext   = $href !== '' && preg_match('/^https?:\/\//i', $href) && strpos($href, home_url()) === false;
            ?>
                <a href="<?php echo esc_url($href !== '' ? $href : home_url('/e-library/')); ?>"<?php echo $is_ext ? ' target="_blank" rel="noopener noreferrer"' : ''; ?> class="bk-elib-link-card">
                    <div>
                        <span class="bk-tag bk-tag-sage" style="margin-bottom:8px;"><?php echo esc_html($lk['badge'] ?? 'REPOSITORI'); ?></span>
                        <strong class="bk-fd" style="display:block;font-size:15px;color:var(--ink, #142219);margin-bottom:4px;"><?php echo esc_html($lk['title'] ?? ''); ?></strong>
                        <div style="font-size:12.5px;color:var(--muted, #64748b);line-height:1.45;"><?php echo esc_html($lk['sub'] ?? ''); ?></div>
                    </div>
                    <span class="bk-elib-cta bk-elib-link-cta"><?php echo esc_html($lk['cta'] ?? 'Buka ↗'); ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="bk-toolbar" style="margin-bottom:18px;">
            <input type="search" class="bk-inp" id="bk-elib-search" placeholder="Cari kode, judul modul, penulis, PPL…" aria-label="Cari koleksi E-Library">
            <div class="bk-filter-pills" id="bk-elib-pills" role="tablist" aria-label="Filter Kategori Koleksi">
                <button type="button" class="bk-pill is-active" data-ek="all">Semua (<?php echo count($normalized_items); ?>)</button>
                <button type="button" class="bk-pill" data-ek="modul">Modul</button>
                <button type="button" class="bk-pill" data-ek="buku">Pedoman</button>
                <button type="button" class="bk-pill" data-ek="asesmen">Asesmen</button>
                <button type="button" class="bk-pill" data-ek="artikel">Artikel</button>
            </div>
        </div>

        <div class="bk-hairline-list" id="bk-elib-list">
            <?php foreach ($normalized_items as $item) :
                $item_id   = (int) $item['id'];
                $kode      = (string) $item['kode'];
                $cat       = !empty($item['category']) ? (string) $item['category'] : 'modul';
                $cat_label = !empty($item['cat_label']) ? (string) $item['cat_label'] : 'Modul Praktikum';
                $authors   = !empty($item['authors']) ? (string) $item['authors'] : 'Tim Dosen BK FIP UNM';
                $year      = !empty($item['year']) ? (string) $item['year'] : date('Y');
                $meta      = !empty($item['meta']) ? (string) $item['meta'] : 'PDF';
                $title     = !empty($item['title']) ? (string) $item['title'] : 'Modul Bimbingan dan Konseling';
                $desc      = !empty($item['desc']) ? (string) $item['desc'] : '';
                $file_url  = (string) $item['file_url'];
                $search_blob = strtolower($kode . ' ' . $title . ' ' . $authors . ' ' . $cat_label . ' ' . $desc);
            ?>
                <div class="bk-hairline-row bk-elib-row"
                     data-id="<?php echo esc_attr($item_id); ?>"
                     data-cat="<?php echo esc_attr($cat); ?>"
                     data-search="<?php echo esc_attr($search_blob); ?>">
                    <div class="bk-rw">
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:5px;flex-wrap:wrap;">
                            <span class="bk-tag"><?php echo esc_html($kode); ?></span>
                            <span class="bk-tag bk-tag-sage"><?php echo esc_html($cat_label); ?></span>
                            <span class="bk-fm" style="font-size:11.5px;color:var(--muted, #64748b);"><?php echo esc_html($year . ' · ' . $meta); ?></span>
                        </div>
                        <strong style="display:block;font-size:16px;color:var(--ink, #142219);margin-bottom:3px;"><?php echo esc_html($title); ?></strong>
                        <div style="font-size:12.5px;color:var(--forest, #0D3B22);font-weight:600;margin-bottom:4px;">Oleh: <?php echo esc_html($authors); ?></div>
                        <span style="font-size:13.5px;color:var(--ink-2, #374151);"><?php echo esc_html($desc); ?></span>
                    </div>
                    <div class="bk-elib-act bk-elib-actions">
                        <button type="button" class="bk-btn bk-btn-secondary bk-btn-sm" data-open-elib="<?php echo esc_attr($item_id); ?>">Detail</button>
                        <?php if ($file_url !== '') : ?>
                            <a href="<?php echo esc_url($file_url); ?>" target="_blank" rel="noopener noreferrer" class="bk-btn bk-btn-primary bk-btn-sm">Unduh PDF &#8599;</a>
                        <?php else : ?>
                            <button type="button" class="bk-btn bk-btn-secondary bk-btn-sm" data-toast="Berkas PDF sedang dalam proses pengunggahan oleh Admin/GPM.">⏳ Segera Diunggah</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="bk-empty" id="bk-elib-empty" style="display:none;">
            <p style="margin:0 0 12px;">Tidak ada modul atau dokumen yang cocok dengan pencarian Anda.</p>
            <button type="button" class="bk-btn bk-btn-secondary bk-btn-sm" id="bk-elib-reset">Reset Pencarian</button>
        </div>

        <aside class="bk-elib-submit-banner" aria-label="Unggah Karya E-Library">
            <div>
                <span class="bk-badge-hero gold" style="margin-bottom:8px;">Kontribusi Sivitas &amp; Dosen</span>
                <h3>Ajukan Unggah Modul / Naskah Dosen</h3>
                <p>Dosen, peneliti, dan mahasiswa bimbingan PPB FIP UNM dapat mendistribusikan modul praktikum, instrumen asesmen BK, atau naskah ilmiah ke repositori E-Library resmi prodi.</p>
            </div>
            <button type="button" class="bk-btn bk-btn-gold" id="bk-elib-open-submit">Ajukan Unggah Modul →</button>
        </aside>

        <div class="bk-backdrop" id="bk-elib-backdrop"></div>
        <aside class="bk-drawer" id="bk-elib-drawer" aria-label="Panel Detail dan Pengajuan E-Library" aria-hidden="true">
            <div class="bk-drawer-head">
                <div>
                    <div class="bk-kicker" id="bk-drw-kicker">E-LIBRARY BK</div>
                    <h2 class="bk-fd" style="font-size:21px;margin:4px 0 0;line-height:1.25;" id="bk-drw-title">Detail Koleksi</h2>
                </div>
                <button type="button" class="bk-icon-close" id="bk-drw-close" aria-label="Tutup Panel">✕</button>
            </div>
            <div class="bk-drawer-body" id="bk-drw-body"></div>
        </aside>

        <div class="bk-toast" id="bk-elib-toast" role="status" aria-live="polite"></div>
    </div>

    <script>
    (function(){
        var root = document.querySelector('.ppb-elibrary-workspace[data-ppb-elib-root="1"]');
        if (!root || root.getAttribute('data-wired') === '1') return;
        root.setAttribute('data-wired', '1');

        var ITEMS = <?php echo wp_json_encode(array_values($normalized_items)); ?>;
        var AJAX_URL = <?php echo wp_json_encode($ajax_url); ?>;
        var NONCE = <?php echo wp_json_encode($submit_nonce); ?>;
        var SIPADU_URL = <?php echo wp_json_encode($sipadu_url); ?>;

        var searchIn = document.getElementById('bk-elib-search');
        var pillsWrap = document.getElementById('bk-elib-pills');
        var listWrap = document.getElementById('bk-elib-list');
        var emptyBox = document.getElementById('bk-elib-empty');
        var resetBtn = document.getElementById('bk-elib-reset');
        var openSubBtn = document.getElementById('bk-elib-open-submit');

        var bdp = document.getElementById('bk-elib-backdrop');
        var drw = document.getElementById('bk-elib-drawer');
        var drwK = document.getElementById('bk-drw-kicker');
        var drwT = document.getElementById('bk-drw-title');
        var drwB = document.getElementById('bk-drw-body');
        var drwX = document.getElementById('bk-drw-close');
        var toastEl = document.getElementById('bk-elib-toast');
        var toastT = null;
        var activeCat = 'all';

        function esc(s) {
            return String(s == null ? '' : s)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        function showToast(msg) {
            if (!toastEl) return;
            toastEl.textContent = msg;
            toastEl.classList.add('is-shown');
            clearTimeout(toastT);
            toastT = setTimeout(function(){ toastEl.classList.remove('is-shown'); }, 2800);
        }

        function openDrawer(kicker, title, html) {
            if (!drw || !bdp) return;
            drwK.textContent = kicker;
            drwT.textContent = title;
            drwB.innerHTML = html;
            drwB.scrollTop = 0;
            bdp.classList.add('is-open');
            drw.classList.add('is-open');
            drw.setAttribute('aria-hidden', 'false');
        }

        function closeDrawer() {
            if (!drw || !bdp) return;
            bdp.classList.remove('is-open');
            drw.classList.remove('is-open');
            drw.setAttribute('aria-hidden', 'true');
        }

        function openItemDetail(id) {
            var el = null;
            for (var i = 0; i < ITEMS.length; i++) {
                if (Number(ITEMS[i].id) === Number(id)) {
                    el = ITEMS[i];
                    break;
                }
            }
            if (!el) return;
            var kode = el.kode || ('ELB-0' + el.id);
            var catLabel = el.cat_label || el.catLabel || 'Modul Praktikum';
            var format = el.format || 'PDF';
            var meta = el.meta || 'PDF';
            var cit = el.authors + '. (' + el.year + '). ' + el.title + ' [' + catLabel + ']. Jurusan PPB FIP Universitas Negeri Makassar.';
            var hasUrl = (el.file_url && el.file_url !== '#' && String(el.file_url).indexOf('javascript:') !== 0);
            var dlBtn = hasUrl
                ? '<a href="' + esc(el.file_url) + '" target="_blank" rel="noopener noreferrer" class="bk-btn bk-btn-primary bk-btn-sm">Unduh Dokumen PDF &#8599;</a>'
                : '<button type="button" class="bk-btn bk-btn-secondary bk-btn-sm" data-toast="Berkas PDF sedang dalam proses pengunggahan oleh Admin/GPM.">⏳ Segera Diunggah</button>';

            openDrawer('E-LIBRARY BK · ' + kode, el.title,
                '<div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:18px;">' +
                    '<span class="bk-tag bk-tag-sage">' + esc(catLabel) + '</span>' +
                    '<span class="bk-tag bk-tag-gold">' + esc(format) + '</span>' +
                    '<span class="bk-fm" style="font-size:12px;color:var(--muted, #64748b);">' + esc(kode) + '</span>' +
                '</div>' +
                '<ul class="bk-spec-list" style="margin-bottom:20px;">' +
                    '<li><span style="color:var(--muted, #64748b);">Penyusun / Penulis</span><strong style="text-align:right;">' + esc(el.authors) + '</strong></li>' +
                    '<li><span style="color:var(--muted, #64748b);">Tahun Terbit</span><span class="bk-fm">' + esc(el.year) + '</span></li>' +
                    '<li><span style="color:var(--muted, #64748b);">Spesifikasi Berkas</span><span style="text-align:right;">' + esc(meta) + '</span></li>' +
                    '<li><span style="color:var(--muted, #64748b);">Status Berkas</span><strong style="color:' + (hasUrl ? 'var(--forest, #0D3B22)' : '#B45309') + ';">' + (hasUrl ? 'Tersedia · Siap Diunduh' : 'Menunggu Unggahan Admin/GPM') + '</strong></li>' +
                '</ul>' +
                '<div class="bk-kicker" style="margin-bottom:6px;">Abstrak &amp; Ringkasan Materi</div>' +
                '<p style="font-size:14px;color:var(--ink-2, #374151);line-height:1.7;margin:0 0 20px;">' + esc(el.desc) + '</p>' +
                '<div style="background:var(--canvas, #FAF8F5);border:1px solid var(--border, #e2e8f0);border-radius:var(--r-md, 12px);padding:14px 16px;margin-bottom:22px;">' +
                    '<div style="display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:8px;flex-wrap:wrap;">' +
                        '<span class="bk-kicker" style="margin:0;">Sitasi Akademik (APA 7th)</span>' +
                        '<button type="button" class="bk-btn bk-btn-secondary bk-btn-sm" data-copy-cit="' + esc(cit) + '">Salin Sitasi</button>' +
                    '</div>' +
                    '<p style="margin:0;font-size:13px;color:var(--ink-2, #374151);line-height:1.55;">' + esc(cit) + '</p>' +
                '</div>' +
                '<div style="display:flex;gap:10px;flex-wrap:wrap;">' +
                    dlBtn +
                    '<button type="button" class="bk-btn bk-btn-secondary bk-btn-sm" id="bk-drw-open-sub">Ajukan Unggah Modul →</button>' +
                '</div>'
            );
        }

        function openSubmitDrawer() {
            openDrawer('KONTRIBUSI REPOSITORI E-LIBRARY', 'Ajukan Unggah Modul / Naskah Dosen',
                '<p style="font-size:13.5px;color:var(--ink-2, #374151);line-height:1.65;margin:0 0 18px;">Dosen, tim pengampu mata kuliah, atau peneliti Prodi BK FIP UNM dapat mengajukan modul pembelajaran, instrumen asesmen, atau panduan praktikum untuk ditinjau Gugus Penjaminan Mutu (GPM).</p>' +
                '<form id="bk-elib-submit-form" class="bk-form-stack">' +
                    '<div class="bk-form-group"><label class="bk-form-label" for="sub-jdl">Judul Modul / Pedoman / Naskah *</label><input type="text" id="sub-jdl" class="bk-inp" required placeholder="Contoh: Modul Praktikum Konseling Karier Digital"></div>' +
                    '<div class="bk-form-group"><label class="bk-form-label" for="sub-kat">Kategori Koleksi *</label><select id="sub-kat" class="bk-inp"><option value="Modul Praktikum">Modul Praktikum &amp; Pembelajaran</option><option value="Pedoman Akademik">Pedoman PPL &amp; Tugas Akhir</option><option value="Instrumen Asesmen">Instrumen &amp; Asesmen BK</option><option value="Artikel Ilmiah">Artikel Ilmiah / Monograf</option></select></div>' +
                    '<div class="bk-form-group"><label class="bk-form-label" for="sub-usr">Nama Pengusul &amp; NIP / NIM *</label><input type="text" id="sub-usr" class="bk-inp" required placeholder="Contoh: Dr. Suciani Latif, M.Pd. · NIP 1982..."></div>' +
                    '<div class="bk-form-group"><label class="bk-form-label" for="sub-lnk">Tautan Berkas PDF (Drive / OSF / Repositori) *</label><input type="url" id="sub-lnk" class="bk-inp" required placeholder="https://drive.google.com/..."></div>' +
                    '<div class="bk-form-group"><label class="bk-form-label" for="sub-cat">Ringkasan Materi &amp; Tahun Terbit (Opsional)</label><textarea id="sub-cat" class="bk-inp" style="min-height:84px;resize:vertical;" placeholder="Cantumkan sasaran mata kuliah OBE, jumlah halaman, atau catatan lisensi…"></textarea></div>' +
                    '<div style="display:flex;gap:10px;flex-wrap:wrap;padding-top:6px;">' +
                        '<button type="submit" class="bk-btn bk-btn-primary bk-btn-sm" id="bk-sub-submit-btn">Kirim Pengajuan Repositori →</button>' +
                        '<a href="' + esc(SIPADU_URL) + '" target="_blank" rel="noopener noreferrer" class="bk-btn bk-btn-secondary bk-btn-sm">Ajukan via SIPADU ↗</a>' +
                    '</div>' +
                '</form>'
            );
        }

        function renderTicketConfirmation(tik, jdl, kat, usr, cat) {
            drwB.innerHTML =
                '<div style="background:var(--sage-soft, #E8F0EA);border:1px solid var(--border-sage, #D4E3D8);border-radius:var(--r-lg, 14px);padding:18px;margin-bottom:20px;">' +
                    '<span class="bk-tag bk-tag-sage" style="margin-bottom:8px;">✓ Pengajuan Naskah Terdaftar · Tiket ' + esc(tik) + '</span>' +
                    '<h3 class="bk-fd" style="font-size:19px;margin:0 0 6px;color:var(--forest, #0D3B22);">Usulan Berhasil Dikirim ke Kurasi E-Library</h3>' +
                    '<p style="margin:0;font-size:13.5px;color:var(--ink-2, #374151);line-height:1.6;">Tim Admin E-Library &amp; GPM akan meninjau kelengkapan naskah di WP-Admin sebelum dipublikasikan pada katalog repositori Prodi BK.</p>' +
                '</div>' +
                '<ul class="bk-spec-list" style="margin-bottom:22px;">' +
                    '<li><span style="color:var(--muted, #64748b);">Kode Tiket</span><strong class="bk-fm" style="color:var(--forest, #0D3B22);">' + esc(tik) + '</strong></li>' +
                    '<li><span style="color:var(--muted, #64748b);">Judul Naskah</span><span style="text-align:right;font-weight:600;">' + esc(jdl) + '</span></li>' +
                    '<li><span style="color:var(--muted, #64748b);">Kategori</span><span style="text-align:right;">' + esc(kat) + '</span></li>' +
                    '<li><span style="color:var(--muted, #64748b);">Pengusul</span><span style="text-align:right;">' + esc(usr) + '</span></li>' +
                    (cat ? '<li><span style="color:var(--muted, #64748b);">Catatan</span><span style="text-align:right;">' + esc(cat) + '</span></li>' : '') +
                    '<li><span style="color:var(--muted, #64748b);">Status</span><strong style="color:var(--forest, #0D3B22);">Menunggu Kurasi Admin &amp; GPM</strong></li>' +
                '</ul>' +
                '<div style="display:flex;gap:10px;flex-wrap:wrap;">' +
                    '<button type="button" class="bk-btn bk-btn-secondary bk-btn-sm" id="bk-elib-another">Ajukan Naskah Lain</button>' +
                    '<button type="button" class="bk-btn bk-btn-primary bk-btn-sm" id="bk-elib-done-x">Selesai &amp; Tutup</button>' +
                '</div>';
            showToast('Pengajuan naskah ' + tik + ' tercatat untuk kurasi repositori.');
        }

        function applyFilters() {
            var q = searchIn ? searchIn.value.toLowerCase().trim() : '';
            var rows = listWrap ? listWrap.querySelectorAll('.bk-elib-row') : [];
            var visible = 0;

            for (var i = 0; i < rows.length; i++) {
                var r = rows[i];
                var rCat = r.getAttribute('data-cat') || '';
                var rSearch = r.getAttribute('data-search') || '';
                var okCat = (activeCat === 'all' || rCat === activeCat);
                var okQ = (!q || rSearch.indexOf(q) !== -1);
                if (okCat && okQ) {
                    r.style.display = 'flex';
                    visible++;
                } else {
                    r.style.display = 'none';
                }
            }

            if (listWrap) listWrap.style.display = visible > 0 ? 'block' : 'none';
            if (emptyBox) emptyBox.style.display = visible === 0 ? 'block' : 'none';
        }

        if (searchIn) searchIn.addEventListener('input', applyFilters);

        if (pillsWrap) {
            pillsWrap.addEventListener('click', function(e){
                var btn = e.target.closest('[data-ek]');
                if (!btn) return;
                activeCat = btn.getAttribute('data-ek') || 'all';
                var allBtns = pillsWrap.querySelectorAll('.bk-pill');
                for (var i = 0; i < allBtns.length; i++) {
                    allBtns[i].classList.toggle('is-active', allBtns[i] === btn);
                }
                applyFilters();
            });
        }

        if (resetBtn) {
            resetBtn.addEventListener('click', function(){
                if (searchIn) searchIn.value = '';
                activeCat = 'all';
                if (pillsWrap) {
                    var allBtns = pillsWrap.querySelectorAll('.bk-pill');
                    for (var i = 0; i < allBtns.length; i++) {
                        allBtns[i].classList.toggle('is-active', allBtns[i].getAttribute('data-ek') === 'all');
                    }
                }
                applyFilters();
            });
        }

        if (listWrap) {
            listWrap.addEventListener('click', function(e){
                var detBtn = e.target.closest('[data-open-elib]');
                if (detBtn) {
                    openItemDetail(detBtn.getAttribute('data-open-elib'));
                    return;
                }
                var tBtn = e.target.closest('[data-toast]');
                if (tBtn) showToast(tBtn.getAttribute('data-toast'));
            });
        }

        if (openSubBtn) openSubBtn.addEventListener('click', openSubmitDrawer);
        if (drwX) drwX.addEventListener('click', closeDrawer);
        if (bdp) bdp.addEventListener('click', closeDrawer);

        if (drwB) {
            drwB.addEventListener('click', function(e){
                var b = e.target.closest('button');
                if (!b) return;
                if (b.getAttribute('data-toast')) {
                    showToast(b.getAttribute('data-toast'));
                } else if (b.getAttribute('data-copy-cit')) {
                    var cit = b.getAttribute('data-copy-cit');
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(cit).catch(function(){});
                    }
                    showToast('Format sitasi APA 7th disalin.');
                } else if (b.id === 'bk-drw-open-sub' || b.id === 'bk-elib-another') {
                    openSubmitDrawer();
                } else if (b.id === 'bk-elib-done-x') {
                    closeDrawer();
                }
            });

            drwB.addEventListener('submit', function(e){
                var f = e.target.closest('#bk-elib-submit-form');
                if (!f) return;
                e.preventDefault();
                var jdl = (document.getElementById('sub-jdl') ? document.getElementById('sub-jdl').value : '').trim();
                var kat = (document.getElementById('sub-kat') ? document.getElementById('sub-kat').value : '').trim();
                var usr = (document.getElementById('sub-usr') ? document.getElementById('sub-usr').value : '').trim();
                var lnk = (document.getElementById('sub-lnk') ? document.getElementById('sub-lnk').value : '').trim();
                var cat = (document.getElementById('sub-cat') ? document.getElementById('sub-cat').value : '').trim();

                if (!jdl || !usr || !lnk) {
                    showToast('Mohon lengkapi judul, pengusul, dan tautan naskah.');
                    return;
                }

                var subBtn = document.getElementById('bk-sub-submit-btn');
                if (subBtn) { subBtn.disabled = true; subBtn.textContent = 'Mengirim ke Kurasi…'; }

                var fallbackTik = 'ELB-' + String(Date.now()).slice(-4);
                if (AJAX_URL && window.fetch) {
                    var fd = new FormData();
                    fd.append('action', 'ppb_submit_elibrary');
                    fd.append('nonce', NONCE);
                    fd.append('title', jdl);
                    fd.append('category', kat);
                    fd.append('author', usr);
                    fd.append('file_url', lnk);
                    fd.append('notes', cat);

                    fetch(AJAX_URL, { method: 'POST', body: fd, credentials: 'same-origin' })
                        .then(function(r){ return r.json(); })
                        .then(function(res){
                            if (res && res.success) {
                                var tik = (res.data && res.data.ticket) ? res.data.ticket : fallbackTik;
                                renderTicketConfirmation(tik, jdl, kat, usr, cat);
                            } else {
                                if (subBtn) { subBtn.disabled = false; subBtn.textContent = 'Kirim Pengajuan Repositori →'; }
                                showToast((res && res.data && res.data.message) ? res.data.message : 'Gagal mengirim pengajuan.');
                            }
                        })
                        .catch(function(){
                            if (subBtn) { subBtn.disabled = false; subBtn.textContent = 'Kirim Pengajuan Repositori →'; }
                            showToast('Gagal terhubung ke server.');
                        });
                }
            });
        }

        document.addEventListener('keydown', function(e){
            if (e.key === 'Escape' && drw && drw.classList.contains('is-open')) {
                closeDrawer();
            }
        });
    })();
    </script>
    <?php
    return ob_get_clean();
}
}

if (!function_exists('ppb_shortcode_elibrary')) {
function ppb_shortcode_elibrary($atts = []) {
    return ppb_render_elibrary_workspace_html(true);
}
}
add_shortcode('ppb_elibrary', 'ppb_shortcode_elibrary');

if (!function_exists('ppb_ajax_submit_elibrary')) {
function ppb_ajax_submit_elibrary() {
    check_ajax_referer('ppb_elibrary_submit_nonce', 'nonce');

    $title    = isset($_POST['title']) ? sanitize_text_field(wp_unslash($_POST['title'])) : '';
    $category = isset($_POST['category']) ? sanitize_text_field(wp_unslash($_POST['category'])) : 'Modul Praktikum';
    $author   = isset($_POST['author']) ? sanitize_text_field(wp_unslash($_POST['author'])) : '';
    $file_url = isset($_POST['file_url']) ? esc_url_raw(wp_unslash($_POST['file_url'])) : '';
    $notes    = isset($_POST['notes']) ? sanitize_textarea_field(wp_unslash($_POST['notes'])) : '';

    if (empty($title) || empty($author) || empty($file_url)) {
        wp_send_json_error(array('message' => 'Mohon lengkapi judul, pengusul, dan tautan naskah.'));
    }

    $ticket = 'ELB-' . substr((string) round(microtime(true) * 1000), -4);
    $subs   = ppb_get_elibrary_submissions();

    $entry = array(
        'ticket'       => $ticket,
        'title'        => $title,
        'category'     => $category,
        'author'       => $author,
        'file_url'     => $file_url,
        'notes'        => $notes,
        'status'       => 'pending',
        'submitted_at' => current_time('mysql'),
    );

    array_unshift($subs, $entry);
    update_option('ppb_elibrary_submissions', array_slice($subs, 0, 100), false);

    wp_send_json_success(array(
        'ticket'  => $ticket,
        'entry'   => $entry,
        'message' => 'Pengajuan naskah ' . $ticket . ' tercatat untuk kurasi repositori.',
    ));
}
}
add_action('wp_ajax_ppb_submit_elibrary', 'ppb_ajax_submit_elibrary');
add_action('wp_ajax_nopriv_ppb_submit_elibrary', 'ppb_ajax_submit_elibrary');

if (!function_exists('ppb_render_elibrary_admin_page')) {
function ppb_render_elibrary_admin_page() {
    if (!current_user_can('edit_posts')) {
        wp_die('Anda tidak memiliki izin mengakses halaman ini.');
    }
    if (function_exists('ppb_admin_enqueue_media_assets')) {
        ppb_admin_enqueue_media_assets();
    } elseif (function_exists('wp_enqueue_media')) {
        wp_enqueue_media();
    }

    $notice    = '';
    $xl_notice = function_exists('ppb_excel_handle_inline_upload') ? ppb_excel_handle_inline_upload('elibrary') : '';

    // 1. Handle Moderation Actions (Approve / Reject Submission)
    if (isset($_POST['ppb_elib_mod_action']) && check_admin_referer('ppb_elibrary_admin_nonce', 'ppb_elibrary_nonce')) {
        $act    = sanitize_text_field(wp_unslash($_POST['ppb_elib_mod_action']));
        $ticket = isset($_POST['sub_ticket']) ? sanitize_text_field(wp_unslash($_POST['sub_ticket'])) : '';
        $subs   = ppb_get_elibrary_submissions();
        $items  = ppb_get_elibrary_items();

        foreach ($subs as $k => $sub) {
            if (isset($sub['ticket']) && $sub['ticket'] === $ticket) {
                if ($act === 'approve') {
                    $next_num = count($items) + 1;
                    $kode = sprintf('ELB-%02d', $next_num);
                    $cat_map = array(
                        'Modul Praktikum'   => 'modul',
                        'Pedoman Akademik'  => 'buku',
                        'Instrumen Asesmen' => 'asesmen',
                        'Artikel Ilmiah'    => 'artikel',
                    );
                    $cat_slug = isset($cat_map[$sub['category']]) ? $cat_map[$sub['category']] : 'modul';
                    $items[] = array(
                        'id'        => time() + $next_num,
                        'kode'      => $kode,
                        'category'  => $cat_slug,
                        'cat_label' => $sub['category'],
                        'authors'   => $sub['author'],
                        'year'      => date('Y'),
                        'meta'      => 'Dokumen Kurasi · PDF',
                        'format'    => 'PDF',
                        'title'     => $sub['title'],
                        'desc'      => !empty($sub['notes']) ? $sub['notes'] : 'Karya terkurasi melalui pengajuan repositori E-Library Prodi BK FIP UNM.',
                        'file_url'  => function_exists('ppb_clean_document_url') ? (ppb_clean_document_url($sub['file_url'] ?? '') ?: '#') : (!empty($sub['file_url']) ? esc_url_raw($sub['file_url']) : '#'),
                    );
                    update_option('ppb_elibrary_items', array_values($items), false);
                    $subs[$k]['status'] = 'approved';
                    $subs[$k]['published_kode'] = $kode;
                    update_option('ppb_elibrary_submissions', array_values($subs), false);
                    $notice = 'Usulan naskah <strong>' . esc_html($ticket) . '</strong> berhasil disetujui dan diterbitkan ke katalog E-Library sebagai <strong>' . esc_html($kode) . '</strong>.';
                } elseif ($act === 'delete') {
                    unset($subs[$k]);
                    update_option('ppb_elibrary_submissions', array_values($subs), false);
                    $notice = 'Usulan naskah <strong>' . esc_html($ticket) . '</strong> dihapus dari antrean.';
                }
                break;
            }
        }
    }

    // 2. Handle Save Portal Links (3 Top Cards)
    if (isset($_POST['ppb_save_elib_links']) && check_admin_referer('ppb_elibrary_admin_nonce', 'ppb_elibrary_nonce')) {
        $raw_links = isset($_POST['elib_links']) && is_array($_POST['elib_links']) ? wp_unslash($_POST['elib_links']) : array();
        $clean_links = array();
        foreach ($raw_links as $lk) {
            if (empty($lk['title'])) continue;
            $clean_href = function_exists('ppb_clean_document_url')
                ? (ppb_clean_document_url($lk['href'] ?? '') ?: '#')
                : esc_url_raw($lk['href'] ?? '#');
            $clean_links[] = array(
                'badge' => sanitize_text_field($lk['badge'] ?? 'REPOSITORI'),
                'title' => sanitize_text_field($lk['title'] ?? ''),
                'sub'   => sanitize_text_field($lk['sub'] ?? ''),
                'cta'   => sanitize_text_field($lk['cta'] ?? 'Buka Tautan ↗'),
                'href'  => $clean_href,
            );
        }
        if (!empty($clean_links)) {
            update_option('ppb_elibrary_portal_links', $clean_links, false);
            $notice = 'Kartu tautan portal & jurnal E-Library berhasil diperbarui.';
        }
    }

    // 3. Handle Reset Catalog & Links to Default
    if (isset($_POST['ppb_reset_elib_defaults']) && check_admin_referer('ppb_elibrary_admin_nonce', 'ppb_elibrary_nonce')) {
        delete_option('ppb_elibrary_items');
        delete_option('ppb_elibrary_portal_links');
        $notice = 'Katalog E-Library dan kartu tautan jurnal telah dikembalikan ke pengaturan awal (ELB-01 s.d. ELB-06).';
    }

    // 4. Handle Save Catalog Items + Optional New Item
    if (isset($_POST['ppb_save_elib_catalog']) && check_admin_referer('ppb_elibrary_admin_nonce', 'ppb_elibrary_nonce')) {
        $raw_items = isset($_POST['elib_items']) && is_array($_POST['elib_items']) ? wp_unslash($_POST['elib_items']) : array();
        $new_items = array();
        $cat_labels = array(
            'modul'   => 'Modul Praktikum',
            'buku'    => 'Pedoman Akademik',
            'asesmen' => 'Instrumen Asesmen',
            'artikel' => 'Artikel Ilmiah',
        );

        foreach ($raw_items as $idx => $it) {
            if (!empty($it['delete']) || empty($it['title'])) continue;
            $cat = sanitize_text_field($it['category'] ?? 'modul');
            $cat_lbl = !empty($it['cat_label']) ? sanitize_text_field($it['cat_label']) : ($cat_labels[$cat] ?? 'Modul Praktikum');
            $clean_file_url = function_exists('ppb_clean_document_url')
                ? (ppb_clean_document_url($it['file_url'] ?? '') ?: '#')
                : (!empty($it['file_url']) ? esc_url_raw($it['file_url']) : '#');
            $new_items[] = array(
                'id'        => !empty($it['id']) ? (int) $it['id'] : ($idx + 1),
                'kode'      => !empty($it['kode']) ? sanitize_text_field($it['kode']) : sprintf('ELB-%02d', count($new_items) + 1),
                'category'  => $cat,
                'cat_label' => $cat_lbl,
                'authors'   => sanitize_text_field($it['authors'] ?? 'Tim Dosen BK FIP UNM'),
                'year'      => sanitize_text_field($it['year'] ?? date('Y')),
                'meta'      => sanitize_text_field($it['meta'] ?? '64 Hlm · PDF 3.0 MB'),
                'format'    => sanitize_text_field($it['format'] ?? 'PDF'),
                'title'     => sanitize_text_field($it['title']),
                'desc'      => sanitize_textarea_field($it['desc'] ?? ''),
                'file_url'  => $clean_file_url,
            );
        }

        // Optional New Item row
        if (!empty($_POST['new_item']['title'])) {
            $ni = wp_unslash($_POST['new_item']);
            $cat = sanitize_text_field($ni['category'] ?? 'modul');
            $cat_lbl = !empty($ni['cat_label']) ? sanitize_text_field($ni['cat_label']) : ($cat_labels[$cat] ?? 'Modul Praktikum');
            $next_idx = count($new_items) + 1;
            $clean_new_file_url = function_exists('ppb_clean_document_url')
                ? (ppb_clean_document_url($ni['file_url'] ?? '') ?: '#')
                : (!empty($ni['file_url']) ? esc_url_raw($ni['file_url']) : '#');
            $new_items[] = array(
                'id'        => time(),
                'kode'      => !empty($ni['kode']) ? sanitize_text_field($ni['kode']) : sprintf('ELB-%02d', $next_idx),
                'category'  => $cat,
                'cat_label' => $cat_lbl,
                'authors'   => sanitize_text_field($ni['authors'] ?? 'Tim Dosen BK FIP UNM'),
                'year'      => sanitize_text_field($ni['year'] ?? date('Y')),
                'meta'      => sanitize_text_field($ni['meta'] ?? '64 Hlm · PDF 2.5 MB'),
                'format'    => sanitize_text_field($ni['format'] ?? 'PDF'),
                'title'     => sanitize_text_field($ni['title']),
                'desc'      => sanitize_textarea_field($ni['desc'] ?? ''),
                'file_url'  => $clean_new_file_url,
            );
        }

        update_option('ppb_elibrary_items', array_values($new_items), false);
        $notice = 'Katalog E-Library (' . count($new_items) . ' koleksi) berhasil disimpan!';
    }

    $items = ppb_get_elibrary_items();
    $links = ppb_get_elibrary_portal_links();
    $subs  = ppb_get_elibrary_submissions();
    ?>
    <div class="wrap" style="max-width:1220px;margin:20px auto;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
        <div style="background:linear-gradient(135deg,#0D3B22,#185333);color:#fff;padding:26px 30px;border-radius:14px;margin-bottom:22px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;">
            <div>
                <span style="display:inline-block;background:rgba(245,184,46,0.2);color:#F5B82E;border:1px solid rgba(245,184,46,0.4);padding:3px 12px;border-radius:999px;font-size:11px;font-weight:700;text-transform:uppercase;margin-bottom:8px;">Repositori Digital Prodi BK</span>
                <h1 style="color:#fff;margin:0 0 6px;font-size:24px;font-weight:700;">📚 Pengelolaan E-Library, Modul &amp; Kurasi Naskah</h1>
                <p style="margin:0;color:rgba(255,255,255,0.84);font-size:13.5px;">Kelola katalog modul praktikum (ELB-01..ELB-xx), tautan jurnal SINTA (JKBK &amp; PJEC), serta moderasi usulan unggah modul dari sivitas akademika.</p>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <a href="<?php echo esc_url(home_url('/e-library/')); ?>" target="_blank" class="button" style="background:#F5B82E;color:#0D3B22;border:none;font-weight:700;padding:6px 14px;border-radius:8px;">↗ Lihat Halaman E-Library</a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=ppb-portal-hub')); ?>" class="button" style="border-radius:8px;">← Pusat Kendali</a>
            </div>
        </div>

        <?php echo $xl_notice; ?>
        <?php if ($notice) : ?>
            <div class="notice notice-success is-dismissible" style="padding:12px 16px;border-radius:8px;margin-bottom:20px;"><p style="margin:0;"><?php echo wp_kses_post($notice); ?></p></div>
        <?php endif; ?>

        <!-- 1. ANTREAN USULAN UNGGAH MODUL (KURASI NASKAH) -->
        <div style="background:#fff;border:1px solid #cbd5e1;border-radius:12px;padding:22px;margin-bottom:24px;box-shadow:0 2px 8px rgba(0,0,0,0.03);">
            <h2 style="margin:0 0 6px;color:#0D3B22;font-size:18px;">📥 Antrean Kurasi Usulan Naskah Dosen &amp; Sivitas (<?php echo count($subs); ?>)</h2>
            <p style="margin:0 0 16px;color:#64748b;font-size:13px;">Usulan modul yang dikirim melalui laci <em>"Ajukan Unggah Modul"</em> di halaman E-Library masuk ke tabel ini untuk ditinjau Admin &amp; GPM sebelum terbit di katalog.</p>
            <?php if (empty($subs)) : ?>
                <div style="padding:18px;background:#f8fafc;border:1px dashed #cbd5e1;border-radius:8px;color:#64748b;font-size:13px;text-align:center;">Belum ada pengajuan unggah modul baru dari formulir frontend.</div>
            <?php else : ?>
                <table class="widefat striped" style="border-radius:8px;overflow:hidden;">
                    <thead>
                        <tr>
                            <th>Tiket &amp; Waktu</th>
                            <th>Judul Naskah &amp; Ringkasan</th>
                            <th>Kategori</th>
                            <th>Pengusul</th>
                            <th>Tautan Berkas</th>
                            <th>Status / Aksi Kurasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subs as $sub) : 
                            $st = $sub['status'] ?? 'pending';
                        ?>
                        <tr>
                            <td>
                                <strong style="font-family:monospace;color:#0D3B22;"><?php echo esc_html($sub['ticket'] ?? '-'); ?></strong><br>
                                <span style="font-size:11.5px;color:#64748b;"><?php echo esc_html($sub['submitted_at'] ?? ''); ?></span>
                            </td>
                            <td>
                                <strong><?php echo esc_html($sub['title'] ?? ''); ?></strong>
                                <?php if (!empty($sub['notes'])) : ?>
                                    <div style="font-size:12px;color:#475569;margin-top:4px;"><?php echo esc_html($sub['notes']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td><span style="background:#E8F0EA;color:#0D3B22;padding:2px 8px;border-radius:999px;font-size:11px;font-weight:700;"><?php echo esc_html($sub['category'] ?? ''); ?></span></td>
                            <td><?php echo esc_html($sub['author'] ?? ''); ?></td>
                            <td>
                                <?php if (!empty($sub['file_url'])) : ?>
                                    <a href="<?php echo esc_url($sub['file_url']); ?>" target="_blank" rel="noopener">Buka Berkas ↗</a>
                                <?php else : ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="post" style="display:inline-flex;gap:6px;align-items:center;flex-wrap:wrap;">
                                    <?php wp_nonce_field('ppb_elibrary_admin_nonce', 'ppb_elibrary_nonce'); ?>
                                    <input type="hidden" name="sub_ticket" value="<?php echo esc_attr($sub['ticket'] ?? ''); ?>">
                                    <?php if ($st === 'approved') : ?>
                                        <span style="background:#dcfce7;color:#166534;padding:3px 8px;border-radius:6px;font-size:11px;font-weight:700;">✓ Terbit (<?php echo esc_html($sub['published_kode'] ?? 'ELB'); ?>)</span>
                                    <?php else : ?>
                                        <button type="submit" name="ppb_elib_mod_action" value="approve" class="button button-primary button-small" style="background:#0D3B22;border-color:#0D3B22;">✓ Setujui &amp; Terbitkan</button>
                                    <?php endif; ?>
                                    <button type="submit" name="ppb_elib_mod_action" value="delete" class="button button-small" style="color:#b91c1c;border-color:#fecaca;" onclick="return confirm('Hapus usulan naskah ini?');">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- 2. KARTU TAUTAN PORTAL JURNAL & REPOSITORI (3 KARTU ATAS) -->
        <form method="post" style="background:#fff;border:1px solid #cbd5e1;border-radius:12px;padding:22px;margin-bottom:24px;box-shadow:0 2px 8px rgba(0,0,0,0.03);">
            <?php wp_nonce_field('ppb_elibrary_admin_nonce', 'ppb_elibrary_nonce'); ?>
            <h2 style="margin:0 0 6px;color:#0D3B22;font-size:18px;">🔗 Kartu Tautan Jurnal &amp; Repositori (Bagian Atas E-Library)</h2>
            <p style="margin:0 0 16px;color:#64748b;font-size:13px;">Tiga kartu pintasan di bagian atas halaman E-Library (Jurnal JKBK SINTA 2, Jurnal PJEC SINTA 4, dan Katalog Buku Dosen).</p>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:16px;margin-bottom:16px;">
                <?php foreach ($links as $i => $lk) : ?>
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px;">
                        <strong style="display:block;color:#0D3B22;margin-bottom:8px;">Kartu Pintasan #<?php echo ($i + 1); ?></strong>
                        <p style="margin:0 0 8px;">
                            <label style="font-size:12px;font-weight:600;display:block;margin-bottom:3px;">Badge Label</label>
                            <input type="text" name="elib_links[<?php echo $i; ?>][badge]" value="<?php echo esc_attr($lk['badge']); ?>" class="widefat">
                        </p>
                        <p style="margin:0 0 8px;">
                            <label style="font-size:12px;font-weight:600;display:block;margin-bottom:3px;">Judul Kartu</label>
                            <input type="text" name="elib_links[<?php echo $i; ?>][title]" value="<?php echo esc_attr($lk['title']); ?>" class="widefat">
                        </p>
                        <p style="margin:0 0 8px;">
                            <label style="font-size:12px;font-weight:600;display:block;margin-bottom:3px;">Subjudul / ISSN</label>
                            <input type="text" name="elib_links[<?php echo $i; ?>][sub]" value="<?php echo esc_attr($lk['sub']); ?>" class="widefat">
                        </p>
                        <div style="display:grid;grid-template-columns:1fr 1.3fr;gap:8px;">
                            <div>
                                <label style="font-size:12px;font-weight:600;display:block;margin-bottom:3px;">Teks CTA</label>
                                <input type="text" name="elib_links[<?php echo $i; ?>][cta]" value="<?php echo esc_attr($lk['cta']); ?>" class="widefat">
                            </div>
                            <div>
                                <label style="font-size:12px;font-weight:600;display:block;margin-bottom:3px;">URL Tautan</label>
                                <input type="text" name="elib_links[<?php echo $i; ?>][href]" value="<?php echo esc_attr($lk['href']); ?>" class="widefat">
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="submit" name="ppb_save_elib_links" class="button button-primary" style="background:#0D3B22;border-color:#0D3B22;">💾 Simpan Kartu Tautan Jurnal</button>
        </form>

        <!-- 3. KATALOG KOLEKSI E-LIBRARY (ELB-01..ELB-xx) -->
        <?php if (function_exists('ppb_excel_render_inline_widget')) ppb_excel_render_inline_widget('elibrary'); ?>
        <form method="post" style="background:#fff;border:1px solid #cbd5e1;border-radius:12px;padding:22px;box-shadow:0 2px 8px rgba(0,0,0,0.03);">
            <?php wp_nonce_field('ppb_elibrary_admin_nonce', 'ppb_elibrary_nonce'); ?>
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:16px;">
                <div>
                    <h2 style="margin:0 0 4px;color:#0D3B22;font-size:18px;">📖 Katalog Koleksi E-Library &amp; Modul (<?php echo count($items); ?> Koleksi)</h2>
                    <p style="margin:0;color:#64748b;font-size:13px;">Setiap item memiliki tombol Detail (membuka laci abstrak &amp; sitasi otomatis APA 7th) serta tombol Unduh PDF.</p>
                </div>
                <div style="display:flex;gap:8px;">
                    <button type="submit" name="ppb_save_elib_catalog" class="button button-primary" style="background:#0D3B22;border-color:#0D3B22;">💾 Simpan Seluruh Katalog</button>
                    <button type="submit" name="ppb_reset_elib_defaults" class="button" onclick="return confirm('Reset katalog E-Library ke 6 koleksi bawaan (ELB-01..ELB-06)?');">↺ Reset ke Default</button>
                </div>
            </div>

            <div style="display:grid;gap:14px;margin-bottom:24px;">
                <?php foreach ($items as $idx => $it) :
                    $raw_file_url = (string) ($it['file_url'] ?? '');
                    $clean_file_val = function_exists('ppb_clean_document_url')
                        ? ppb_clean_document_url($raw_file_url)
                        : (($raw_file_url === '#') ? '' : trim($raw_file_url));
                    $has_file = ($clean_file_val !== '');
                ?>
                    <div style="border:1px solid #e2e8f0;border-radius:10px;padding:16px;background:#fcfdfd;">
                        <input type="hidden" name="elib_items[<?php echo $idx; ?>][id]" value="<?php echo esc_attr($it['id']); ?>">
                        <div style="display:grid;grid-template-columns:110px 160px 170px 1fr 90px 180px;gap:10px;margin-bottom:10px;align-items:end;">
                            <div>
                                <label style="font-size:11.5px;font-weight:700;color:#0D3B22;display:block;margin-bottom:3px;">Kode</label>
                                <input type="text" name="elib_items[<?php echo $idx; ?>][kode]" value="<?php echo esc_attr($it['kode']); ?>" class="widefat" style="font-family:monospace;font-weight:700;">
                            </div>
                            <div>
                                <label style="font-size:11.5px;font-weight:600;display:block;margin-bottom:3px;">Filter Kategori</label>
                                <select name="elib_items[<?php echo $idx; ?>][category]" class="widefat">
                                    <option value="modul" <?php selected($it['category'], 'modul'); ?>>Modul (modul)</option>
                                    <option value="buku" <?php selected($it['category'], 'buku'); ?>>Pedoman (buku)</option>
                                    <option value="asesmen" <?php selected($it['category'], 'asesmen'); ?>>Asesmen (asesmen)</option>
                                    <option value="artikel" <?php selected($it['category'], 'artikel'); ?>>Artikel (artikel)</option>
                                </select>
                            </div>
                            <div>
                                <label style="font-size:11.5px;font-weight:600;display:block;margin-bottom:3px;">Label Badge</label>
                                <input type="text" name="elib_items[<?php echo $idx; ?>][cat_label]" value="<?php echo esc_attr($it['cat_label']); ?>" class="widefat">
                            </div>
                            <div>
                                <label style="font-size:11.5px;font-weight:600;display:block;margin-bottom:3px;">Penyusun / Penulis (Sitasi APA)</label>
                                <input type="text" name="elib_items[<?php echo $idx; ?>][authors]" value="<?php echo esc_attr($it['authors']); ?>" class="widefat">
                            </div>
                            <div>
                                <label style="font-size:11.5px;font-weight:600;display:block;margin-bottom:3px;">Tahun</label>
                                <input type="text" name="elib_items[<?php echo $idx; ?>][year]" value="<?php echo esc_attr($it['year']); ?>" class="widefat">
                            </div>
                            <div>
                                <label style="font-size:11.5px;font-weight:600;display:block;margin-bottom:3px;">Spesifikasi Berkas</label>
                                <input type="text" name="elib_items[<?php echo $idx; ?>][meta]" value="<?php echo esc_attr($it['meta']); ?>" class="widefat" placeholder="64 Hlm · PDF 3.2 MB">
                                <input type="hidden" name="elib_items[<?php echo $idx; ?>][format]" value="<?php echo esc_attr($it['format'] ?? 'PDF'); ?>">
                            </div>
                        </div>
                        <div style="display:grid;grid-template-columns:1.05fr 1.25fr;gap:12px;margin-bottom:10px;align-items:end;">
                            <div>
                                <label style="font-size:11.5px;font-weight:600;display:block;margin-bottom:3px;">Judul Lengkap Modul / Pedoman / Artikel</label>
                                <input type="text" name="elib_items[<?php echo $idx; ?>][title]" value="<?php echo esc_attr($it['title']); ?>" class="widefat" style="font-weight:600;">
                            </div>
                            <div>
                                <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;margin-bottom:3px;">
                                    <label for="elib_file_<?php echo $idx; ?>" style="font-size:11.5px;font-weight:600;">URL Berkas PDF (Google Drive / Pustaka Media)</label>
                                    <?php if ($has_file) : ?>
                                        <span style="background:#dcfce7;color:#166534;padding:2px 8px;border-radius:999px;font-size:10.5px;font-weight:700;">✓ File Terhubung</span>
                                    <?php else : ?>
                                        <span style="background:#fef3c7;color:#92400e;padding:2px 8px;border-radius:999px;font-size:10.5px;font-weight:700;">⚠️ Belum ada file PDF</span>
                                    <?php endif; ?>
                                </div>
                                <?php if (function_exists('ppb_render_media_url_control')) : ?>
                                    <?php ppb_render_media_url_control([
                                        'id'             => 'elib_file_' . $idx,
                                        'name'           => "elib_items[$idx][file_url]",
                                        'value'          => $clean_file_val,
                                        'allow_relative' => true,
                                        'placeholder'    => 'https://drive.google.com/file/d/... atau upload PDF',
                                        'title'          => 'Pilih / Upload Dokumen PDF E-Library',
                                        'btn_label'      => '📁 Upload / Pilih PDF',
                                    ]); ?>
                                <?php else : ?>
                                    <input type="text" id="elib_file_<?php echo $idx; ?>" name="elib_items[<?php echo $idx; ?>][file_url]" value="<?php echo esc_attr($clean_file_val); ?>" class="widefat" placeholder="https://drive.google.com/...">
                                <?php endif; ?>
                            </div>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;gap:14px;">
                            <div style="flex:1;">
                                <input type="text" name="elib_items[<?php echo $idx; ?>][desc]" value="<?php echo esc_attr($it['desc']); ?>" class="widefat" placeholder="Abstrak / ringkasan materi...">
                            </div>
                            <label style="color:#b91c1c;font-size:12px;font-weight:600;white-space:nowrap;">
                                <input type="checkbox" name="elib_items[<?php echo $idx; ?>][delete]" value="1"> Hapus Koleksi Ini
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Tambah Koleksi Baru -->
            <div style="background:#f0fdf4;border:1.5px dashed #166534;border-radius:10px;padding:18px;margin-bottom:18px;">
                <h3 style="margin:0 0 10px;color:#0D3B22;font-size:15px;">➕ Tambah Koleksi Modul / Buku Baru ke E-Library</h3>
                <div style="display:grid;grid-template-columns:110px 160px 170px 1fr 90px 180px;gap:10px;margin-bottom:10px;">
                    <input type="text" name="new_item[kode]" placeholder="<?php echo esc_attr(sprintf('ELB-%02d', count($items) + 1)); ?>" class="widefat">
                    <select name="new_item[category]" class="widefat">
                        <option value="modul">Modul (modul)</option>
                        <option value="buku">Pedoman (buku)</option>
                        <option value="asesmen">Asesmen (asesmen)</option>
                        <option value="artikel">Artikel (artikel)</option>
                    </select>
                    <input type="text" name="new_item[cat_label]" placeholder="Modul Praktikum" class="widefat">
                    <input type="text" name="new_item[authors]" placeholder="Nama Dosen / Tim Penyusun" class="widefat">
                    <input type="text" name="new_item[year]" value="<?php echo esc_attr(date('Y')); ?>" class="widefat">
                    <input type="text" name="new_item[meta]" placeholder="72 Hlm · PDF 2.4 MB" class="widefat">
                </div>
                <div style="display:grid;grid-template-columns:1.05fr 1.25fr;gap:12px;margin-bottom:10px;align-items:center;">
                    <input type="text" name="new_item[title]" placeholder="Judul Modul / Buku / Instrumen Baru (Isi untuk menambahkan)..." class="widefat">
                    <?php if (function_exists('ppb_render_media_url_control')) : ?>
                        <?php ppb_render_media_url_control([
                            'id'             => 'elib_file_new',
                            'name'           => 'new_item[file_url]',
                            'value'          => '',
                            'allow_relative' => true,
                            'placeholder'    => 'https://drive.google.com/file/d/... atau upload PDF',
                            'title'          => 'Pilih / Upload Dokumen PDF Baru',
                            'btn_label'      => '📁 Upload / Pilih PDF',
                        ]); ?>
                    <?php else : ?>
                        <input type="text" id="elib_file_new" name="new_item[file_url]" placeholder="https://drive.google.com/file/d/..." class="widefat">
                    <?php endif; ?>
                </div>
                <input type="text" name="new_item[desc]" placeholder="Ringkasan materi / abstrak singkat untuk ditampilkan di kartu dan laci detail..." class="widefat">
            </div>

            <button type="submit" name="ppb_save_elib_catalog" class="button button-primary button-hero" style="background:#0D3B22;border-color:#0D3B22;">💾 Simpan Perubahan &amp; Koleksi Baru</button>
        </form>
    </div>
    <?php
}
}

/* ========================================================================== */
/* 10. SYNC FRONTEND /layanan/perlindungan-mahasiswa/ DARI PENGATURAN ADMIN   */
/* ========================================================================== */
if (!function_exists('ppb_layanan_perlindungan_sync_content')) {
function ppb_layanan_perlindungan_sync_content($content) {
    if (is_admin() || !is_string($content) || strpos($content, 'bk-page-wrapper') === false) {
        return $content;
    }

    $slug_path = function_exists('ppb_get_current_page_slug_path') ? ppb_get_current_page_slug_path() : '';
    $is_target = ($slug_path === 'layanan/perlindungan-mahasiswa' || strpos($content, 'Hubungi Konselor Siaga (WhatsApp)') !== false);
    if (!$is_target) {
        return $content;
    }

    $wa_raw = (string) get_option('ppb_layanan_wa_hotline', '6281242338988');
    $wa_num = preg_replace('/\D+/', '', $wa_raw);
    if ($wa_num !== '') {
        $content = str_replace('https://wa.me/6281242338988', 'https://wa.me/' . $wa_num, $content);
    }

    $sop_raw = (string) get_option('ppb_layanan_sop_perlindungan_link', 'https://drive.google.com/file/d/1_SOP_Perlindungan_Mahasiswa_BK/view?usp=sharing');
    $sop_url = function_exists('ppb_clean_document_url') ? ppb_clean_document_url($sop_raw) : trim($sop_raw);

    if ($sop_url !== '') {
        $cta_pattern = '/(<a\b[^>]*>Hubungi Konselor Siaga \(WhatsApp\)<\/a>)/i';
        if (preg_match($cta_pattern, $content)) {
            $sop_btn = '<a href="' . esc_url($sop_url) . '" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;gap:8px;padding:12px 20px;border-radius:12px;background:rgba(255,255,255,0.14);border:1.5px solid rgba(255,255,255,0.35);color:#ffffff;font-size:13px;font-weight:700;text-decoration:none">📄 Unduh SOP Perlindungan Mahasiswa &#8599;</a>';
            $content = preg_replace(
                $cta_pattern,
                '<div style="display:flex;flex-wrap:wrap;gap:10px;align-items:center">$1' . $sop_btn . '</div>',
                $content,
                1
            );
        }
    }

    return $content;
}
}
add_filter('the_content', 'ppb_layanan_perlindungan_sync_content', 12);

/* ========================================================================== */
/* 11. SHORTCODE & FRONTEND SYNC: SARANA & PRASARANA (/profil/sarana-prasarana) */
/* ========================================================================== */
if (!function_exists('ppb_render_sarpras_grid_html')) {
function ppb_render_sarpras_grid_html() {
    $items = function_exists('ppb_get_sarpras_list') ? ppb_get_sarpras_list() : [];
    $themes = [
        'green'  => ['bg' => '#E8F0EA', 'fg' => '#0D3B22'],
        'gold'   => ['bg' => '#FEF3C7', 'fg' => '#92400E'],
        'indigo' => ['bg' => '#E0E7FF', 'fg' => '#3730A3'],
    ];

    ob_start();
    ?>
    <div id="ppbSarprasGrid" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(min(100%, 300px), 1fr));gap:24px;">
        <?php foreach ($items as $sp) :
            $tema     = $themes[$sp['tema'] ?? 'green'] ?? $themes['green'];
            $foto_raw = trim((string) ($sp['foto_url'] ?? ''));
            $foto_src = ($foto_raw !== '' && function_exists('ppb_resolve_image_preview_url'))
                ? ppb_resolve_image_preview_url($foto_raw)
                : (function_exists('ppb_clean_document_url') ? ppb_clean_document_url($foto_raw) : $foto_raw);
            $dok_url  = !empty($sp['dok_url']) && function_exists('ppb_clean_document_url')
                ? ppb_clean_document_url($sp['dok_url'])
                : trim((string) ($sp['dok_url'] ?? ''));
            $kap      = trim((string) ($sp['kapasitas'] ?? ''));
            $lok      = trim((string) ($sp['lokasi'] ?? ''));
        ?>
            <div style="background:#ffffff;border:1px solid #D4E3D8;border-radius:18px;overflow:hidden;box-shadow:0 4px 15px rgba(0,0,0,0.03);display:flex;flex-direction:column;">
                <?php if ($foto_src !== '') : ?>
                    <div style="height:200px;background:<?php echo esc_attr($tema['bg']); ?>;position:relative;overflow:hidden;">
                        <img src="<?php echo esc_url($foto_src); ?>" alt="<?php echo esc_attr($sp['nama'] ?? 'Fasilitas BK UNM'); ?>" loading="lazy" style="width:100%;height:100%;object-fit:cover;display:block;">
                    </div>
                <?php else : ?>
                    <div style="height:200px;background:<?php echo esc_attr($tema['bg']); ?>;display:flex;align-items:center;justify-content:center;color:<?php echo esc_attr($tema['fg']); ?>;font-size:48px;">
                        <?php echo esc_html($sp['icon'] ?? '🏫'); ?>
                    </div>
                <?php endif; ?>
                <div style="padding:22px;display:flex;flex-direction:column;justify-content:space-between;flex:1;">
                    <div>
                        <span style="font-size:11px;font-weight:700;background:<?php echo esc_attr($tema['bg']); ?>;color:<?php echo esc_attr($tema['fg']); ?>;padding:3px 8px;border-radius:4px;display:inline-block;"><?php echo esc_html($sp['kategori'] ?? 'Fasilitas Prodi'); ?></span>
                        <h3 style="font-family:'Fraunces',Georgia,serif;font-size:18px;color:#0D3B22;margin:8px 0 6px;"><?php echo esc_html($sp['nama'] ?? ''); ?></h3>
                        <p style="font-size:13px;color:#4B5563;line-height:1.6;margin:0 0 12px;"><?php echo esc_html($sp['deskripsi'] ?? ''); ?></p>
                    </div>
                    <?php if ($kap !== '' || $lok !== '' || $dok_url !== '') : ?>
                        <div style="padding-top:10px;border-top:1px dashed #E2E8F0;display:flex;flex-direction:column;gap:6px;">
                            <?php if ($kap !== '' || $lok !== '') : ?>
                                <div style="display:flex;flex-wrap:wrap;gap:6px;font-size:11.5px;color:#475569;">
                                    <?php if ($kap !== '') : ?>
                                        <span style="background:#FAF8F5;border:1px solid #E5E7EB;padding:3px 8px;border-radius:6px;">👥 <?php echo esc_html($kap); ?></span>
                                    <?php endif; ?>
                                    <?php if ($lok !== '') : ?>
                                        <span style="background:#FAF8F5;border:1px solid #E5E7EB;padding:3px 8px;border-radius:6px;">📍 <?php echo esc_html($lok); ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($dok_url !== '') : ?>
                                <div style="margin-top:4px;">
                                    <a href="<?php echo esc_url($dok_url); ?>" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;gap:6px;background:#E8F0EA;color:#0D3B22;border:1px solid #B7D0BE;font-size:12px;font-weight:700;padding:6px 12px;border-radius:8px;text-decoration:none;">📄 SOP / Denah Ruang &#8599;</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}
}

if (!function_exists('ppb_shortcode_sarana_prasarana')) {
function ppb_shortcode_sarana_prasarana($atts = []) {
    return ppb_render_sarpras_grid_html();
}
}
add_shortcode('ppb_sarana_prasarana', 'ppb_shortcode_sarana_prasarana');

if (!function_exists('ppb_sarpras_sync_content')) {
function ppb_sarpras_sync_content($content) {
    if (is_admin() || !is_string($content) || $content === '') {
        return $content;
    }

    $slug_path = function_exists('ppb_get_current_page_slug_path') ? ppb_get_current_page_slug_path() : '';
    $is_target = ($slug_path === 'profil/sarana-prasarana' || strpos($content, 'Ruang Perkuliahan Modern BK') !== false);
    if (!$is_target) {
        return $content;
    }

    if (strpos($content, 'id="ppbSarprasGrid"') !== false) {
        return $content;
    }

    $marker_pos = strpos($content, 'Ruang Perkuliahan Modern BK');
    if ($marker_pos !== false && function_exists('ppb_replace_balanced_div_at')) {
        $before = substr($content, 0, $marker_pos);
        $grid_open = strrpos($before, '<div style="display:grid');
        if ($grid_open === false) {
            $d1 = strrpos($before, '<div');
            $d2 = ($d1 !== false) ? strrpos(substr($before, 0, $d1), '<div') : false;
            $grid_open = ($d2 !== false) ? strrpos(substr($before, 0, $d2), '<div') : false;
        }
        if ($grid_open !== false) {
            $content = ppb_replace_balanced_div_at($content, $grid_open, ppb_render_sarpras_grid_html());
        }
    }

    return $content;
}
}
add_filter('the_content', 'ppb_sarpras_sync_content', 12);

/* ========================================================================== */
/* 9. MODUL 5 BIDANG LABORATORIUM BK (CRUD, SHORTCODE, & PENDAFTARAN)         */
/* ========================================================================== */
if (!function_exists('ppb_get_lab_bidang_defaults')) {
function ppb_get_lab_bidang_defaults() {
    return [
        'konseling' => [
            'slug'        => 'konseling',
            'icon'        => '💬',
            'nama'        => 'Bidang Bimbingan & Konseling',
            'tagline'     => 'Ruang aman konseling sebaya dan pendampingan profesional yang rahasia & etis.',
            'deskripsi'   => 'Menyelenggarakan layanan konseling individual, konseling kelompok, dan layanan konseling sebaya (Peer Support) bagi mahasiswa BK maupun sivitas akademika FIP UNM dengan menjunjung tinggi kode etik kerahasiaan konselor.',
            'koordinator' => 'Qawiyyan Fitri, M.Pd',
            'asisten'     => 'Tim Konselor Sebaya & Asisten Bidang Konseling',
            'jadwal'      => 'Senin – Jumat, 08.00 – 16.00 WITA',
            'lokasi'      => 'Ruang Konseling Kedap Suara & One-Way Mirror, Gedung Lab BK FIP UNM',
            'program'     => [
                [
                    'judul'     => 'Konseling Sebaya (Peer Support)',
                    'kategori'  => 'Layanan Utama',
                    'deskripsi' => 'Sesi cerita dan pendampingan awal bersama konselor sebaya mahasiswa BK yang terlatih, nyaman, bebas penghakiman, dan 100% rahasia.',
                    'sasaran'   => 'Mahasiswa BK & Sivitas UNM',
                    'jadwal'    => 'Senin – Jumat (Sesuai Janji Temu)',
                    'status'    => 'Layanan Aktif',
                    'cta_label' => 'Daftar Konseling ↗',
                    'cta_url'   => 'https://labbk8.wixsite.com/peersupport',
                ],
                [
                    'judul'     => 'Konseling Individual & Kelompok Profesional',
                    'kategori'  => 'Layanan Dosen',
                    'deskripsi' => 'Pendampingan konseling lanjutan untuk isu akademik, karir, pribadi, maupun sosial bersama dosen pembina dan praktisi BK.',
                    'sasaran'   => 'Mahasiswa & Masyarakat Umum',
                    'jadwal'    => 'Senin – Jumat, 09.00 – 15.00 WITA',
                    'status'    => 'Layanan Aktif',
                    'cta_label' => 'Daftar Konseling ↗',
                    'cta_url'   => 'https://labbk8.wixsite.com/peersupport',
                ],
                [
                    'judul'     => 'Praktikum & Observasi Ruang One-Way Mirror',
                    'kategori'  => 'Praktikum Akademik',
                    'deskripsi' => 'Reservasi penggunaan ruang konseling kedap suara dan ruang observasi one-way mirror untuk keperluan praktikum mata kuliah keterampilan konseling.',
                    'sasaran'   => 'Mahasiswa & Dosen Pengampu MK',
                    'jadwal'    => 'Sesuai Jadwal Perkuliahan / Reservasi',
                    'status'    => 'Reservasi Buka',
                    'cta_label' => 'Ajukan Reservasi Ruang',
                    'cta_url'   => '',
                ],
            ],
            'sop_alur'    => [
                'Akses laman pendaftaran konseling melalui tombol Daftar Konseling atau pilih layanan praktikum ruang.',
                'Isi identitas singkat, pilih jenis layanan (konseling sebaya/individual/kelompok), dan tentukan preferensi waktu.',
                'Tim Bidang Konseling mengonfirmasi jadwal dan ruang/kanal sesi secara privat.',
                'Seluruh catatan dan proses sesi dilindungi kode etik kerahasiaan profesi BK.',
            ],
            'links'       => [
                ['label' => 'Pendaftaran Konseling (Peersupport) ↗', 'url' => 'https://labbk8.wixsite.com/peersupport', 'external' => true],
                ['label' => 'Perlindungan Mahasiswa & Pengaduan', 'url' => home_url('/layanan/perlindungan-mahasiswa/'), 'external' => false],
                ['label' => 'Lihat Kegiatan Lab BK', 'url' => home_url('/laboratorium/kegiatan/'), 'external' => false],
            ],
        ],
        'asesmen' => [
            'slug'        => 'asesmen',
            'icon'        => '🧠',
            'nama'        => 'Bidang Asesmen',
            'tagline'     => 'Asesmen psikologis, pemetaan potensi, serta pengelolaan instrumen tes dan non-tes terstandar.',
            'deskripsi'   => 'Mengelola bank alat tes psikologi dan non-tes, menyelenggarakan layanan administrasi serta skoring asesmen minat-bakat dan kondisi diri mahasiswa, serta melayani peminjaman instrumen praktikum laboratorium.',
            'koordinator' => 'Nurfaidah Ardis, M.Psi., Psikolog',
            'asisten'     => 'Tim Asisten Bidang Asesmen & Inventaris Lab',
            'jadwal'      => 'Senin – Jumat, 08.00 – 16.00 WITA',
            'lokasi'      => 'Ruang Asesmen & Bank Alat Tes, Gedung Lab BK FIP UNM',
            'program'     => [
                [
                    'judul'     => 'Layanan Asesmen Psikologis & Pemetaan Potensi',
                    'kategori'  => 'Asesmen Mandiri / Kelompok',
                    'deskripsi' => 'Layanan asesmen kecemasan akademik, stres, kepribadian, dan pemetaan minat-bakat beserta umpan balik hasil secara rahasia.',
                    'sasaran'   => 'Mahasiswa BK & Mitra Sekolah',
                    'jadwal'    => 'Senin – Jumat (Sesuai Jadwal)',
                    'status'    => 'Layanan Aktif',
                    'cta_label' => 'Ajukan Layanan Asesmen',
                    'cta_url'   => '',
                ],
                [
                    'judul'     => 'Workshop Interpretasi Alat Tes Grafis (DAP, BAUM, HTP)',
                    'kategori'  => 'Pendampingan Teknis',
                    'deskripsi' => 'Pelatihan teknis administrasi, skoring, dan interpretasi tes grafis serta inventori psikologis bagi mahasiswa dan asisten laboratorium.',
                    'sasaran'   => 'Mahasiswa Semester 3–7 & Asisten',
                    'jadwal'    => 'Berkala Per Semester',
                    'status'    => 'Terjadwal',
                    'cta_label' => 'Daftar Workshop Asesmen',
                    'cta_url'   => '',
                ],
                [
                    'judul'     => 'Peminjaman Alat Tes & Surat Bebas Laboratorium',
                    'kategori'  => 'Inventaris & Persuratan',
                    'deskripsi' => 'Peminjaman instrumen tes psikologi (IST, EPPS, RMIB, Grafis, Inventori) untuk praktikum/skripsi serta verifikasi bebas pinjaman lab untuk yudisium.',
                    'sasaran'   => 'Mahasiswa BK & Peneliti',
                    'jadwal'    => 'Senin – Jumat, 08.30 – 15.30 WITA',
                    'status'    => 'Aktif',
                    'cta_label' => 'Buka Katalog Alat Tes →',
                    'cta_url'   => '/lab-bk/inventaris/',
                ],
            ],
            'sop_alur'    => [
                'Cek ketersediaan instrumen tes atau jadwal layanan asesmen pada katalog Inventaris Alat Tes Lab BK.',
                'Ajukan permohonan peminjaman alat atau layanan asesmen melalui formulir terpadu atau loket persuratan.',
                'Tunjukkan KTM/surat pengantar dosen pengampu saat pengambilan instrumen di Ruang Asesmen.',
                'Kembalikan alat tes tepat waktu dalam kondisi lengkap untuk menjaga kelayakan syarat Surat Bebas Laboratorium.',
            ],
            'links'       => [
                ['label' => 'Katalog Inventaris & Alat Tes Lab', 'url' => home_url('/lab-bk/inventaris/'), 'external' => false],
                ['label' => 'Persuratan & Surat Bebas Lab', 'url' => home_url('/layanan/persuratan/'), 'external' => false],
                ['label' => 'E-Library Instrumen & Pedoman', 'url' => home_url('/e-library/'), 'external' => false],
            ],
        ],
        'training' => [
            'slug'        => 'training',
            'icon'        => '🚀',
            'nama'        => 'Bidang Training & Development',
            'tagline'     => 'Pelatihan kompetensi konselor, pengembangan diri, dan pembinaan Peer Collaborator.',
            'deskripsi'   => 'Merancang dan melaksanakan program pelatihan terstruktur (microcounseling, soft skills, Self Development Camp), penyediaan modul pelatihan, serta rekrutmen dan pembinaan Peer Collaborator Laboratorium BK.',
            'koordinator' => 'Abid Raisardhi',
            'asisten'     => 'Tim Asisten Bidang Training & Development',
            'jadwal'      => 'Senin – Jumat (Pelatihan Terjadwal & Akhir Pekan)',
            'lokasi'      => 'Ruang Seminar / Kelas Microcounseling Lab BK FIP UNM',
            'program'     => [
                [
                    'judul'     => 'Self Development Camp & Pelatihan Soft Skills',
                    'kategori'  => 'Pengembangan Diri',
                    'deskripsi' => 'Program intensif penggalian potensi diri, manajemen emosi, kepemimpinan, dan penyusunan rencana pengembangan pribadi mahasiswa.',
                    'sasaran'   => 'Mahasiswa BK FIP UNM',
                    'jadwal'    => 'Sesuai Kalender Kegiatan Lab',
                    'status'    => 'Pendaftaran Buka',
                    'cta_label' => 'Daftar Pelatihan',
                    'cta_url'   => '',
                ],
                [
                    'judul'     => 'Rekrutmen & Pembinaan Peer Collaborator Lab BK',
                    'kategori'  => 'Kaderisasi Asisten',
                    'deskripsi' => 'Kesempatan mahasiswa BK untuk bergabung menjadi asisten dan peer collaborator guna memperoleh pengalaman praktik layanan laboratorium secara nyata.',
                    'sasaran'   => 'Mahasiswa BK Aktif (Min. Sem. 3)',
                    'jadwal'    => 'Batch Rekrutmen Tahunan / Semester',
                    'status'    => 'Info Rekrutmen',
                    'cta_label' => 'Daftar Peer Collaborator',
                    'cta_url'   => '',
                ],
                [
                    'judul'     => 'Klinik Microcounseling & Modul Pelatihan Terstruktur',
                    'kategori'  => 'Peningkatan Kompetensi',
                    'deskripsi' => 'Latihan terstruktur keterampilan dasar komunikasi konseling dan akses modul pelatihan interaktif bersama fasilitator lab.',
                    'sasaran'   => 'Mahasiswa Praktikan PPL & MK Konseling',
                    'jadwal'    => 'Mingguan / Sesuai Permintaan Kelas',
                    'status'    => 'Aktif',
                    'cta_label' => 'Buka Modul di E-Library →',
                    'cta_url'   => '/e-library/',
                ],
            ],
            'sop_alur'    => [
                'Pilih program pelatihan atau batch rekrutmen Peer Collaborator yang sedang dibuka.',
                'Lengkapi formulir pendaftaran langsung pada laman ini beserta motivasi/kebutuhan pelatihan.',
                'Panitia Bidang Training & Development mengirimkan konfirmasi peserta, jadwal, dan modul pra-pelatihan.',
                'Peserta mengikuti sesi pelatihan/pendampingan hingga memperoleh sertifikat atau penugasan praktik.',
            ],
            'links'       => [
                ['label' => 'Agenda Kegiatan & Praktikum Lab', 'url' => home_url('/laboratorium/kegiatan/'), 'external' => false],
                ['label' => 'E-Library & Modul Pelatihan', 'url' => home_url('/e-library/'), 'external' => false],
                ['label' => '7 Jurus BK Service Canvas', 'url' => home_url('/layanan/7jurusbk/'), 'external' => false],
            ],
        ],
        'media' => [
            'slug'        => 'media',
            'icon'        => '🎙️',
            'nama'        => 'Bidang Media',
            'tagline'     => 'Produksi podcast, konten edukasi kesehatan mental, dan publikasi digital Lab BK.',
            'deskripsi'   => 'Mengelola Studio Podcast & Media Lab BK, memproduksi konten edukasi audio-visual (podcast, reels, infografis), mendokumentasikan kegiatan laboratorium, serta menyelenggarakan pelatihan kreator konten edukatif bagi mahasiswa.',
            'koordinator' => 'Dwi Endrasto Wibowo, M.Pd',
            'asisten'     => 'Tim Kreatif & Produksi Media Lab BK',
            'jadwal'      => 'Senin – Jumat, 09.00 – 16.00 WITA',
            'lokasi'      => 'Studio Podcast & Media Kreatif, Gedung Lab BK FIP UNM',
            'program'     => [
                [
                    'judul'     => 'Studio Podcast & Produksi Audio-Visual BK',
                    'kategori'  => 'Fasilitas Studio',
                    'deskripsi' => 'Produksi episode podcast warta kesehatan mental, wawancara pakar/dosen, serta rekaman materi video edukasi Bimbingan dan Konseling.',
                    'sasaran'   => 'Dosen, Asisten Lab & Mahasiswa BK',
                    'jadwal'    => 'Senin – Jumat (Booking Studio)',
                    'status'    => 'Aktif',
                    'cta_label' => 'Ajukan Booking Studio',
                    'cta_url'   => '',
                ],
                [
                    'judul'     => 'Workshop Content Creator BK: Podcast & Reels',
                    'kategori'  => 'Pelatihan Kreatif',
                    'deskripsi' => 'Pelatihan penulisan skrip edukasi kesehatan mental, teknik perekaman audio-video, dan penyuntingan konten media sosial yang etis.',
                    'sasaran'   => 'Mahasiswa BK & Peer Collaborator',
                    'jadwal'    => 'Terjadwal Per Semester',
                    'status'    => 'Pendaftaran Buka',
                    'cta_label' => 'Daftar Workshop Media',
                    'cta_url'   => '',
                ],
                [
                    'judul'     => 'Publikasi Kampanye Edukasi & Media Praktikum',
                    'kategori'  => 'Publikasi Digital',
                    'deskripsi' => 'Pengembangan poster, infografis, modul media BK interaktif, serta publikasi berita dan dokumentasi kegiatan melalui kanal resmi @labbk.unm.',
                    'sasaran'   => 'Sivitas Akademika & Publik',
                    'jadwal'    => 'Berkelanjutan',
                    'status'    => 'Aktif',
                    'cta_label' => 'Ajukan Kolaborasi Konten',
                    'cta_url'   => '',
                ],
            ],
            'sop_alur'    => [
                'Ajukan permohonan peminjaman studio podcast atau kolaborasi publikasi media melalui formulir bidang.',
                'Sertakan konsep singkat/topik konten (dipastikan sesuai etika profesi BK dan bebas konten sensitif tanpa pendampingan).',
                'Tim Bidang Media menjadwalkan sesi produksi dan pendampingan teknis di Studio Lab BK.',
                'Hasil karya dikurasi bersama Dosen Koordinator sebelum dipublikasikan di kanal resmi jurusan & lab.',
            ],
            'links'       => [
                ['label' => 'Berita & Publikasi Jurusan', 'url' => home_url('/berita/'), 'external' => false],
                ['label' => 'E-Library Media & Modul BK', 'url' => home_url('/e-library/'), 'external' => false],
                ['label' => 'Kegiatan Lab BK', 'url' => home_url('/laboratorium/kegiatan/'), 'external' => false],
            ],
        ],
        'ekstrakurikuler' => [
            'slug'        => 'ekstrakurikuler',
            'icon'        => '🎨',
            'nama'        => 'Bidang Ekstrakurikuler',
            'tagline'     => 'Wadah pengembangan minat, bakat, dan keterampilan non-akademik mahasiswa BK.',
            'deskripsi'   => 'Mewadahi dan membina keterampilan non-akademik mahasiswa Bimbingan dan Konseling di luar perkuliahan melalui komunitas/klub minat-bakat (seni, public speaking, olahraga, desain kreatif) serta menyiapkan delegasi lomba non-akademik.',
            'koordinator' => 'Maslina, M.Pd',
            'asisten'     => 'Tim Koordinator Minat Bakat & Komunitas Mahasiswa',
            'jadwal'      => 'Rutin Mingguan (Sore / Akhir Pekan sesuai Klub)',
            'lokasi'      => 'Area Kampus FIP UNM Gunungsari & Ruang Aktivitas Mahasiswa',
            'program'     => [
                [
                    'judul'     => 'Pekan Seni & Bakat Mahasiswa BK (Musik, Tari & Teater)',
                    'kategori'  => 'Seni & Budaya',
                    'deskripsi' => 'Wadah ekspresi seni musik akustik, tari kreasi, dan teater/psikodrama mahasiswa BK sekaligus panggung apresiasi kreativitas kampus.',
                    'sasaran'   => 'Seluruh Mahasiswa BK FIP UNM',
                    'jadwal'    => 'Latihan Mingguan & Panggung Terbuka',
                    'status'    => 'Pendaftaran Buka',
                    'cta_label' => 'Gabung Komunitas Seni',
                    'cta_url'   => '',
                ],
                [
                    'judul'     => 'Klub Public Speaking, MC & Debat Mahasiswa',
                    'kategori'  => 'Keterampilan Komunikasi',
                    'deskripsi' => 'Melatih kepercayaan diri berbicara di depan umum, keprotokoleran MC acara kampus, moderasi diskusi, dan keterampilan persuasi.',
                    'sasaran'   => 'Mahasiswa BK Angkatan Aktif',
                    'jadwal'    => 'Setiap Kamis, 15.30 WITA',
                    'status'    => 'Aktif',
                    'cta_label' => 'Daftar Klub Public Speaking',
                    'cta_url'   => '',
                ],
                [
                    'judul'     => 'Klub Olahraga & Kebugaran Mahasiswa BK',
                    'kategori'  => 'Olahraga & Kesehatan',
                    'deskripsi' => 'Kegiatan olahraga bersama (bulutangkis, futsal, voli, dan fun run) untuk menjaga kesehatan fisik, sportivitas, dan solidaritas antar-angkatan.',
                    'sasaran'   => 'Mahasiswa BK FIP UNM',
                    'jadwal'    => 'Jumat Sore / Akhir Pekan',
                    'status'    => 'Aktif',
                    'cta_label' => 'Gabung Klub Olahraga',
                    'cta_url'   => '',
                ],
                [
                    'judul'     => 'Pembinaan & Delegasi Lomba Non-Akademik',
                    'kategori'  => 'Kompetisi & Prestasi',
                    'deskripsi' => 'Pendampingan tim dan individu mahasiswa BK yang mengikuti kompetisi seni, kreativitas media, esai populer, dan lomba minat bakat tingkat regional/nasional.',
                    'sasaran'   => 'Tim / Delegasi Mahasiswa BK',
                    'jadwal'    => 'Berkala Sesuai Kalender Lomba',
                    'status'    => 'Buka Informasi',
                    'cta_label' => 'Lihat Info Lomba Non-Akademik →',
                    'cta_url'   => '/laboratorium/lomba/',
                ],
            ],
            'sop_alur'    => [
                'Pilih klub atau wadah keterampilan non-akademik yang sesuai dengan minat dan bakatmu.',
                'Daftarkan diri melalui tombol Gabung / Daftar pada klub yang diminati di laman ini.',
                'Koordinator asisten bidang akan mengundangmu ke jadwal latihan rutin dan grup koordinasi klub.',
                'Anggota aktif berkesempatan tampil di Pekan Seni & Bakat serta didelegasikan pada lomba non-akademik.',
            ],
            'links'       => [
                ['label' => 'Info Lomba Non-Akademik Lab BK', 'url' => home_url('/laboratorium/lomba/'), 'external' => false],
                ['label' => 'Prestasi Mahasiswa Prodi BK', 'url' => home_url('/kemahasiswaan/prestasi/'), 'external' => false],
                ['label' => 'Agenda Kegiatan Lab BK', 'url' => home_url('/laboratorium/kegiatan/'), 'external' => false],
            ],
        ],
    ];
}
}

if (!function_exists('ppb_get_lab_bidang_data')) {
function ppb_get_lab_bidang_data($slug = '') {
    $defaults = ppb_get_lab_bidang_defaults();
    $saved    = get_option('ppb_lab_bidang_data', []);
    if (is_array($saved) && !empty($saved)) {
        foreach ($defaults as $key => $def) {
            if (!empty($saved[$key]) && is_array($saved[$key])) {
                $defaults[$key] = array_merge($def, $saved[$key]);
            }
        }
    }
    if ($slug !== '') {
        $key = sanitize_key($slug);
        return $defaults[$key] ?? null;
    }
    return $defaults;
}
}

if (!function_exists('ppb_shortcode_lab_bidang')) {
function ppb_shortcode_lab_bidang($atts = []) {
    $atts = shortcode_atts(['bidang' => 'konseling'], $atts, 'ppb_lab_bidang');
    $slug = sanitize_key($atts['bidang']);
    $data = ppb_get_lab_bidang_data($slug);
    if (!$data) {
        return '<div class="bk-callout"><p>Bidang laboratorium tidak ditemukan.</p></div>';
    }

    $ajax_url = admin_url('admin-ajax.php');
    $nonce    = wp_create_nonce('ppb_lab_pendaftaran_public_nonce');
    $modal_id = 'ppb-lab-reg-modal-' . esc_attr($slug);

    ob_start();
    ?>
    <div class="ppb-lab-bidang-wrap" data-bidang="<?php echo esc_attr($slug); ?>">
        <!-- Info Ringkas Bidang & Koordinator -->
        <div style="background:#ffffff;border:1px solid #D4E3D8;border-radius:18px;padding:26px;margin-bottom:28px;box-shadow:0 6px 20px rgba(13,59,34,0.05);">
            <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:flex-start;gap:18px;margin-bottom:18px;">
                <div style="flex:1 1 420px;">
                    <span style="display:inline-block;background:#E8F0EA;color:#0D3B22;font-size:11px;font-weight:700;padding:4px 10px;border-radius:99px;text-transform:uppercase;letter-spacing:0.04em;margin-bottom:10px;">
                        <?php echo esc_html($data['icon'] . ' ' . $data['nama']); ?>
                    </span>
                    <h2 style="font-family:'Fraunces',Georgia,serif;font-size:26px;color:#0D3B22;margin:0 0 8px;line-height:1.25;">
                        <?php echo esc_html($data['tagline']); ?>
                    </h2>
                    <p style="font-size:14.5px;color:#374151;line-height:1.7;margin:0;">
                        <?php echo esc_html($data['deskripsi']); ?>
                    </p>
                </div>
                <div style="flex:0 1 320px;background:#FAF8F5;border:1px solid #E2E8F0;border-radius:14px;padding:18px;width:100%;">
                    <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:10px;">Informasi Pengelola Bidang</div>
                    <div style="margin-bottom:10px;">
                        <div style="font-size:11.5px;color:#64748b;">Dosen Koordinator</div>
                        <div style="font-size:14px;font-weight:700;color:#0D3B22;"><?php echo esc_html($data['koordinator']); ?></div>
                    </div>
                    <?php if (!empty($data['asisten'])) : ?>
                    <div style="margin-bottom:10px;">
                        <div style="font-size:11.5px;color:#64748b;">Tim Pelaksana</div>
                        <div style="font-size:13px;font-weight:600;color:#1F2937;"><?php echo esc_html($data['asisten']); ?></div>
                    </div>
                    <?php endif; ?>
                    <div style="margin-bottom:10px;">
                        <div style="font-size:11.5px;color:#64748b;">Jam Layanan</div>
                        <div style="font-size:13px;font-weight:600;color:#1F2937;">🕒 <?php echo esc_html($data['jadwal']); ?></div>
                    </div>
                    <div>
                        <div style="font-size:11.5px;color:#64748b;">Lokasi</div>
                        <div style="font-size:13px;font-weight:600;color:#1F2937;">📍 <?php echo esc_html($data['lokasi']); ?></div>
                    </div>
                </div>
            </div>

            <?php if (!empty($data['links'])) : ?>
                <div style="display:flex;flex-wrap:wrap;gap:10px;padding-top:16px;border-top:1px solid #E8F0EA;">
                    <?php foreach ($data['links'] as $lk) :
                        $is_ext = !empty($lk['external']);
                    ?>
                        <a href="<?php echo esc_url($lk['url']); ?>"<?php echo $is_ext ? ' target="_blank" rel="noopener noreferrer"' : ''; ?> style="display:inline-flex;align-items:center;gap:6px;background:#F3F7F4;color:#0D3B22;border:1px solid #C6DBC9;font-size:12.5px;font-weight:700;padding:8px 14px;border-radius:9px;text-decoration:none;">
                            <?php echo esc_html($lk['label']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Daftar Layanan / Program / Klub -->
        <div style="margin-bottom:32px;">
            <h3 style="font-family:'Fraunces',Georgia,serif;font-size:22px;color:#0D3B22;margin:0 0 16px;">
                Layanan, Program Kerja &amp; Wadah Kegiatan
            </h3>
            <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(310px, 1fr));gap:20px;">
                <?php foreach (($data['program'] ?? []) as $pr) :
                    $cta_url   = trim((string) ($pr['cta_url'] ?? ''));
                    $cta_label = !empty($pr['cta_label']) ? $pr['cta_label'] : 'Ajukan / Daftar';
                    $is_ext    = $cta_url !== '' && preg_match('/^https?:\/\//i', $cta_url) && strpos($cta_url, home_url()) === false;
                    $href_final = $cta_url;
                    if ($cta_url !== '' && strpos($cta_url, '/') === 0) {
                        $href_final = home_url($cta_url);
                    }
                ?>
                    <div style="background:#ffffff;border:1px solid #D4E3D8;border-radius:16px;padding:22px;box-shadow:0 4px 15px rgba(0,0,0,0.03);display:flex;flex-direction:column;justify-content:space-between;">
                        <div>
                            <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;margin-bottom:10px;flex-wrap:wrap;">
                                <span style="font-size:11px;font-weight:700;background:#E8F0EA;color:#0D3B22;padding:3px 10px;border-radius:6px;"><?php echo esc_html($pr['kategori'] ?? 'Layanan'); ?></span>
                                <span style="font-size:11px;font-weight:700;background:#FEF3C7;color:#92400E;padding:3px 10px;border-radius:6px;"><?php echo esc_html($pr['status'] ?? 'Aktif'); ?></span>
                            </div>
                            <h4 style="font-size:17px;font-weight:700;color:#0D3B22;margin:0 0 8px;line-height:1.35;"><?php echo esc_html($pr['judul'] ?? ''); ?></h4>
                            <p style="font-size:13.5px;color:#4B5563;line-height:1.6;margin:0 0 14px;"><?php echo esc_html($pr['deskripsi'] ?? ''); ?></p>
                        </div>
                        <div>
                            <div style="font-size:12px;color:#475569;margin-bottom:12px;display:grid;gap:4px;">
                                <?php if (!empty($pr['sasaran'])) : ?>
                                    <div>👥 <strong>Sasaran:</strong> <?php echo esc_html($pr['sasaran']); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($pr['jadwal'])) : ?>
                                    <div>🗓️ <strong>Jadwal:</strong> <?php echo esc_html($pr['jadwal']); ?></div>
                                <?php endif; ?>
                            </div>
                            <?php if ($cta_url !== '') : ?>
                                <a href="<?php echo esc_url($href_final); ?>"<?php echo $is_ext ? ' target="_blank" rel="noopener noreferrer"' : ''; ?> style="display:inline-flex;align-items:center;justify-content:center;gap:6px;background:#0D3B22;color:#ffffff;font-size:13px;font-weight:700;padding:10px 16px;border-radius:10px;text-decoration:none;">
                                    <?php echo esc_html($cta_label); ?>
                                </a>
                            <?php else : ?>
                                <button type="button"
                                    class="ppb-open-lab-reg"
                                    data-modal="<?php echo $modal_id; ?>"
                                    data-bidang="<?php echo esc_attr($data['nama']); ?>"
                                    data-program="<?php echo esc_attr($pr['judul'] ?? ''); ?>"
                                    style="display:inline-flex;align-items:center;justify-content:center;gap:6px;background:#0D3B22;color:#ffffff;border:none;cursor:pointer;font-size:13px;font-weight:700;padding:10px 16px;border-radius:10px;">
                                    <?php echo esc_html($cta_label); ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Alur / SOP Layanan -->
        <?php if (!empty($data['sop_alur'])) : ?>
        <div style="background:#ffffff;border:1px solid #D4E3D8;border-radius:18px;padding:24px;margin-bottom:24px;">
            <h3 style="font-family:'Fraunces',Georgia,serif;font-size:20px;color:#0D3B22;margin:0 0 14px;">
                Alur &amp; Prosedur Layanan
            </h3>
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:14px;">
                <?php foreach ($data['sop_alur'] as $sidx => $step) : ?>
                    <div style="background:#FAF8F5;border:1px solid #E2E8F0;border-radius:12px;padding:16px;">
                        <div style="width:28px;height:28px;border-radius:99px;background:#0D3B22;color:#F5B82E;font-size:13px;font-weight:800;display:flex;align-items:center;justify-content:center;margin-bottom:10px;">
                            <?php echo (int) ($sidx + 1); ?>
                        </div>
                        <p style="font-size:13px;color:#374151;line-height:1.55;margin:0;"><?php echo esc_html($step); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Modal Form Pendaftaran Terpadu -->
        <div id="<?php echo $modal_id; ?>" class="ppb-lab-reg-modal" style="display:none;position:fixed;inset:0;z-index:99999;background:rgba(15,23,42,0.65);align-items:center;justify-content:center;padding:16px;">
            <div style="background:#ffffff;border-radius:18px;max-width:500px;width:100%;padding:26px;box-shadow:0 20px 50px rgba(0,0,0,0.25);position:relative;">
                <button type="button" class="ppb-close-lab-reg" aria-label="Tutup formulir" style="position:absolute;top:14px;right:14px;background:#F1F5F9;border:none;width:32px;height:32px;border-radius:99px;font-size:18px;cursor:pointer;color:#475569;">&times;</button>
                <div style="font-size:11px;font-weight:700;color:#0D3B22;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;">Formulir Terpadu Lab BK</div>
                <h3 class="ppb-reg-title" style="font-family:'Fraunces',Georgia,serif;font-size:20px;color:#0D3B22;margin:0 0 14px;">Pendaftaran Layanan</h3>
                <form class="ppb-lab-reg-form">
                    <input type="hidden" name="action" value="ppb_submit_lab_pendaftaran">
                    <input type="hidden" name="nonce" value="<?php echo esc_attr($nonce); ?>">
                    <input type="hidden" name="bidang" class="ppb-reg-bidang-inp" value="<?php echo esc_attr($data['nama']); ?>">
                    <input type="hidden" name="program" class="ppb-reg-program-inp" value="">

                    <div style="margin-bottom:10px;">
                        <label style="display:block;font-size:12px;font-weight:700;color:#334155;margin-bottom:4px;">Nama Lengkap *</label>
                        <input type="text" name="nama" required placeholder="Masukkan nama lengkap" style="width:100%;padding:9px 12px;border:1px solid #CBD5E1;border-radius:8px;font-size:13.5px;">
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px;">
                        <div>
                            <label style="display:block;font-size:12px;font-weight:700;color:#334155;margin-bottom:4px;">NIM / Status *</label>
                            <input type="text" name="nim" required placeholder="Contoh: 23040450..." style="width:100%;padding:9px 12px;border:1px solid #CBD5E1;border-radius:8px;font-size:13.5px;">
                        </div>
                        <div>
                            <label style="display:block;font-size:12px;font-weight:700;color:#334155;margin-bottom:4px;">No. WhatsApp / Email *</label>
                            <input type="text" name="kontak" required placeholder="08xxxx / email" style="width:100%;padding:9px 12px;border:1px solid #CBD5E1;border-radius:8px;font-size:13.5px;">
                        </div>
                    </div>
                    <div style="margin-bottom:14px;">
                        <label style="display:block;font-size:12px;font-weight:700;color:#334155;margin-bottom:4px;">Catatan / Kebutuhan / Pilihan Jadwal</label>
                        <textarea name="catatan" rows="3" placeholder="Tuliskan kebutuhan layanan, alat, atau minat klub..." style="width:100%;padding:9px 12px;border:1px solid #CBD5E1;border-radius:8px;font-size:13.5px;"></textarea>
                    </div>
                    <div class="ppb-reg-msg" style="display:none;padding:10px 12px;border-radius:8px;font-size:13px;margin-bottom:12px;"></div>
                    <button type="submit" class="ppb-reg-submit" style="width:100%;background:#0D3B22;color:#ffffff;border:none;border-radius:10px;padding:11px 16px;font-size:14px;font-weight:700;cursor:pointer;">
                        Kirim Pendaftaran / Pengajuan
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
    (function(){
        if (window.__ppbLabBidangBound) return;
        window.__ppbLabBidangBound = true;
        var ajaxUrl = <?php echo wp_json_encode($ajax_url); ?>;

        document.addEventListener('click', function(e) {
            var openBtn = e.target.closest('.ppb-open-lab-reg');
            if (openBtn) {
                var modal = document.getElementById(openBtn.getAttribute('data-modal'));
                if (!modal) return;
                modal.querySelector('.ppb-reg-title').textContent = openBtn.getAttribute('data-program') || 'Pendaftaran Layanan';
                modal.querySelector('.ppb-reg-bidang-inp').value = openBtn.getAttribute('data-bidang') || '';
                modal.querySelector('.ppb-reg-program-inp').value = openBtn.getAttribute('data-program') || '';
                var msg = modal.querySelector('.ppb-reg-msg');
                msg.style.display = 'none';
                modal.style.display = 'flex';
                return;
            }
            if (e.target.closest('.ppb-close-lab-reg') || e.target.classList.contains('ppb-lab-reg-modal')) {
                var m = e.target.closest('.ppb-lab-reg-modal') || e.target;
                m.style.display = 'none';
            }
        });

        document.addEventListener('submit', function(e) {
            var form = e.target.closest('.ppb-lab-reg-form');
            if (!form) return;
            e.preventDefault();
            var btn = form.querySelector('.ppb-reg-submit');
            var msg = form.querySelector('.ppb-reg-msg');
            btn.disabled = true;
            btn.textContent = 'Mengirim...';
            var fd = new FormData(form);
            fetch(ajaxUrl, { method: 'POST', body: fd })
                .then(function(r){ return r.json(); })
                .then(function(res){
                    msg.style.display = 'block';
                    if (res && res.success) {
                        msg.style.background = '#DCFCE7';
                        msg.style.color = '#166534';
                        msg.textContent = (res.data && res.data.message) ? res.data.message : 'Pendaftaran berhasil dikirim!';
                        form.reset();
                    } else {
                        msg.style.background = '#FEE2E2';
                        msg.style.color = '#991B1B';
                        msg.textContent = (res && res.data && res.data.message) ? res.data.message : 'Gagal mengirim pendaftaran.';
                    }
                })
                .catch(function(){
                    msg.style.display = 'block';
                    msg.style.background = '#FEE2E2';
                    msg.style.color = '#991B1B';
                    msg.textContent = 'Terjadi kesalahan jaringan. Silakan coba lagi.';
                })
                .finally(function(){
                    btn.disabled = false;
                    btn.textContent = 'Kirim Pendaftaran / Pengajuan';
                });
        });
    })();
    </script>
    <?php
    return ob_get_clean();
}
}
add_shortcode('ppb_lab_bidang', 'ppb_shortcode_lab_bidang');

if (!function_exists('ppb_ajax_submit_lab_pendaftaran')) {
function ppb_ajax_submit_lab_pendaftaran() {
    if (!check_ajax_referer('ppb_lab_pendaftaran_public_nonce', 'nonce', false)) {
        wp_send_json_error(['message' => 'Sesi formulir tidak valid. Silakan muat ulang halaman.']);
    }
    $nama    = sanitize_text_field(wp_unslash($_POST['nama'] ?? ''));
    $nim     = sanitize_text_field(wp_unslash($_POST['nim'] ?? ''));
    $kontak  = sanitize_text_field(wp_unslash($_POST['kontak'] ?? ''));
    $bidang  = sanitize_text_field(wp_unslash($_POST['bidang'] ?? ''));
    $program = sanitize_text_field(wp_unslash($_POST['program'] ?? ''));
    $catatan = sanitize_textarea_field(wp_unslash($_POST['catatan'] ?? ''));

    if ($nama === '' || $nim === '' || $kontak === '') {
        wp_send_json_error(['message' => 'Nama, NIM/Status, dan Kontak wajib diisi.']);
    }

    $list = get_option('ppb_lab_pendaftaran_list', []);
    if (!is_array($list)) $list = [];
    array_unshift($list, [
        'waktu'   => current_time('Y-m-d H:i'),
        'bidang'  => $bidang,
        'program' => $program,
        'nama'    => $nama,
        'nim'     => $nim,
        'kontak'  => $kontak,
        'catatan' => $catatan,
        'status'  => 'Menunggu',
    ]);
    update_option('ppb_lab_pendaftaran_list', array_slice($list, 0, 200), false);

    wp_send_json_success(['message' => 'Terima kasih! Pengajuan Anda untuk ' . $program . ' telah diterima oleh tim Laboratorium BK.']);
}
}
add_action('wp_ajax_ppb_submit_lab_pendaftaran', 'ppb_ajax_submit_lab_pendaftaran');
add_action('wp_ajax_nopriv_ppb_submit_lab_pendaftaran', 'ppb_ajax_submit_lab_pendaftaran');



