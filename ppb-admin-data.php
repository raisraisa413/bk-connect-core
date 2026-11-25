<?php
/**
 * Module: PPB FIP UNM - Manajemen Data Dosen & Dokumen Mutu
 * Description: Modul WP Admin untuk pengelolaan Data Dosen dan Dokumen Mutu (Link Google Drive) tanpa menyentuh kode HTML atau Elementor.
 * Author: Jurusan PPB FIP UNM
 */

if (!defined('ABSPATH')) exit;

/* ========================================================================== */
/* 1. REGISTRASI CUSTOM POST TYPE: DOSEN (ppb_dosen)                          */
/* ========================================================================== */
if (!function_exists('ppb_register_cpt_dosen')) {
function ppb_register_cpt_dosen() {
    $labels = [
        'name'               => 'Data Dosen',
        'singular_name'      => 'Dosen',
        'menu_name'          => 'Data Dosen',
        'name_admin_bar'     => 'Dosen',
        'add_new'            => 'Tambah Dosen Baru',
        'add_new_item'       => 'Tambah Dosen Baru',
        'new_item'           => 'Dosen Baru',
        'edit_item'          => 'Edit Data Dosen',
        'view_item'          => 'Lihat Dosen',
        'all_items'          => 'Semua Dosen',
        'search_items'       => 'Cari Dosen',
        'not_found'          => 'Tidak ada data dosen.',
        'not_found_in_trash' => 'Tidak ada dosen di tempat sampah.'
    ];

    $args = [
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_menu'       => false,
        'query_var'          => false,
        'rewrite'            => false,
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 21,
        'menu_icon'          => 'dashicons-welcome-learn-more',
        'supports'           => ['title', 'page-attributes'],
    ];

    register_post_type('ppb_dosen', $args);
}
}
add_action('init', 'ppb_register_cpt_dosen');

/* ========================================================================== */
/* 2. REGISTRASI CUSTOM POST TYPE: DOKUMEN MUTU (ppb_dokumen)                 */
/* ========================================================================== */
if (!function_exists('ppb_register_cpt_dokumen')) {
function ppb_register_cpt_dokumen() {
    $labels = [
        'name'               => 'Dokumen Mutu',
        'singular_name'      => 'Dokumen',
        'menu_name'          => 'Dokumen Mutu',
        'name_admin_bar'     => 'Dokumen Mutu',
        'add_new'            => 'Tambah Dokumen',
        'add_new_item'       => 'Tambah Dokumen Baru',
        'new_item'           => 'Dokumen Baru',
        'edit_item'          => 'Edit Dokumen',
        'view_item'          => 'Lihat Dokumen',
        'all_items'          => 'Semua Dokumen',
        'search_items'       => 'Cari Dokumen',
        'not_found'          => 'Tidak ada dokumen.',
        'not_found_in_trash' => 'Tidak ada dokumen di tempat sampah.'
    ];

    $args = [
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_menu'       => false,
        'query_var'          => false,
        'rewrite'            => false,
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 22,
        'menu_icon'          => 'dashicons-media-document',
        'supports'           => ['title', 'page-attributes'],
    ];

    register_post_type('ppb_dokumen', $args);
}
}
add_action('init', 'ppb_register_cpt_dokumen');

/* ========================================================================== */
/* 3. META BOX: DATA DOSEN                                                    */
/* ========================================================================== */
if (!function_exists('ppb_dosen_add_meta_box')) {
function ppb_dosen_add_meta_box() {
    add_meta_box(
        'ppb_dosen_meta_box',
        'Informasi Lengkap Dosen & Tenaga Pengajar',
        'ppb_dosen_render_meta_box',
        'ppb_dosen',
        'normal',
        'high'
    );
}
}
add_action('add_meta_boxes', 'ppb_dosen_add_meta_box');

if (!function_exists('ppb_dosen_render_meta_box')) {
function ppb_dosen_render_meta_box($post) {
    wp_nonce_field('ppb_dosen_save_meta', 'ppb_dosen_meta_nonce');

    $nip         = get_post_meta($post->ID, '_ppb_dosen_nip', true);
    $kategori    = get_post_meta($post->ID, '_ppb_dosen_kategori', true) ?: 'LEKTOR';
    $jabatan     = get_post_meta($post->ID, '_ppb_dosen_jabatan', true);
    $inisial     = get_post_meta($post->ID, '_ppb_dosen_inisial', true);
    $foto_link   = get_post_meta($post->ID, '_ppb_dosen_foto_link', true) ?: get_post_meta($post->ID, '_dosen_foto_link', true);
    $kepakaran   = get_post_meta($post->ID, '_ppb_dosen_kepakaran', true);
    $sinta_url   = get_post_meta($post->ID, '_ppb_dosen_sinta_url', true) ?: 'https://sinta.kemdiktisaintek.go.id/';
    if (function_exists('wp_enqueue_media')) {
        wp_enqueue_media();
    }
    ?>
    <style>
        .ppb-form-row { margin-bottom: 18px; }
        .ppb-form-row label { display: block; font-weight: 600; margin-bottom: 6px; color: #1e293b; font-size: 13px; }
        .ppb-form-row input[type="text"],
        .ppb-form-row input[type="url"],
        .ppb-form-row select,
        .ppb-form-row textarea { width: 100%; max-width: 700px; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13.5px; }
        .ppb-form-hint { display: block; font-size: 11.5px; color: #64748b; margin-top: 4px; }
        .ppb-badge-preview { display: inline-block; padding: 3px 10px; border-radius: 9999px; font-size: 11px; font-weight: 700; margin-top: 4px; }
    </style>

    <div class="ppb-form-row">
        <label for="ppb_dosen_nip">Nomor Induk Pegawai (NIP):</label>
        <input type="text" id="ppb_dosen_nip" name="ppb_dosen_nip" value="<?php echo esc_attr($nip); ?>" placeholder="Contoh: 19720817 200212 1 001">
        <span class="ppb-form-hint">Format NIP 18 digit dengan spasi yang rapi.</span>
    </div>

    <div class="ppb-form-row">
        <label for="ppb_dosen_kategori">Kategori / Pangkat Akademik (Badge):</label>
        <select id="ppb_dosen_kategori" name="ppb_dosen_kategori">
            <option value="GURU BESAR" <?php selected($kategori, 'GURU BESAR'); ?>>GURU BESAR</option>
            <option value="KETUA JURUSAN" <?php selected($kategori, 'KETUA JURUSAN'); ?>>KETUA JURUSAN</option>
            <option value="SEKRETARIS JURUSAN" <?php selected($kategori, 'SEKRETARIS JURUSAN'); ?>>SEKRETARIS JURUSAN</option>
            <option value="LEKTOR KEPALA" <?php selected($kategori, 'LEKTOR KEPALA'); ?>>LEKTOR KEPALA</option>
            <option value="LEKTOR" <?php selected($kategori, 'LEKTOR'); ?>>LEKTOR</option>
            <option value="ASISTEN AHLI" <?php selected($kategori, 'ASISTEN AHLI'); ?>>ASISTEN AHLI</option>
            <option value="TENAGA KEPENDIDIKAN" <?php selected($kategori, 'TENAGA KEPENDIDIKAN'); ?>>TENAGA KEPENDIDIKAN</option>
        </select>
        <span class="ppb-form-hint">Badge warna penanda kategori dosen di atas kartu profil.</span>
    </div>

    <div class="ppb-form-row">
        <label for="ppb_dosen_jabatan">Jabatan Struktural / Bidang Keahlian Pokok:</label>
        <input type="text" id="ppb_dosen_jabatan" name="ppb_dosen_jabatan" value="<?php echo esc_attr($jabatan); ?>" placeholder="Contoh: Dekan FIP UNM · Guru Besar BK atau Dosen Bidang Bimbingan Belajar">
        <span class="ppb-form-hint">Teks jabatan atau keterangan yang tampil di bawah nama dosen.</span>
    </div>

    <div class="ppb-form-row">
        <label for="ppb_dosen_inisial">Inisial Avatar (2 Huruf):</label>
        <input type="text" id="ppb_dosen_inisial" name="ppb_dosen_inisial" value="<?php echo esc_attr($inisial); ?>" placeholder="Contoh: AS (Biarkan kosong untuk otomatis dari nama)">
        <span class="ppb-form-hint">Inisial huruf pada lingkaran emas avatar dosen (fallback jika foto tidak tersedia).</span>
    </div>

    <div class="ppb-form-row">
        <label for="ppb_dosen_foto_link">Foto Potret Dosen (URL / Path Aset Tema):</label>
        <div style="display:flex;gap:8px;max-width:700px;align-items:center;">
            <input type="text" id="ppb_dosen_foto_link" name="ppb_dosen_foto_link" value="<?php echo esc_attr($foto_link); ?>" placeholder="Contoh: assets/img/dosen-abid.jpg atau https://...">
            <button type="button" class="button" id="ppb_dosen_foto_picker">Pilih Media</button>
        </div>
        <span class="ppb-form-hint">Mendukung URL dari Pustaka Media atau path relatif aset tema (mis. <code>assets/img/dosen-abid.jpg</code>). Ditampilkan 4:5 &amp; avatar bulat dengan fokus wajah <code>50% 22%</code>.</span>
    </div>
    <script>
    jQuery(function($){
        var frame;
        $('#ppb_dosen_foto_picker').on('click', function(e){
            e.preventDefault();
            if (frame) { frame.open(); return; }
            frame = wp.media({ title: 'Pilih Foto Dosen', button: { text: 'Gunakan Foto' }, multiple: false });
            frame.on('select', function(){
                var att = frame.state().get('selection').first().toJSON();
                if (att && att.url) { $('#ppb_dosen_foto_link').val(att.url); }
            });
            frame.open();
        });
    });
    </script>

    <div class="ppb-form-row">
        <label for="ppb_dosen_kepakaran">Bidang Kepakaran (Pisahkan dengan tanda koma):</label>
        <textarea id="ppb_dosen_kepakaran" name="ppb_dosen_kepakaran" rows="2" placeholder="Contoh: Psikometri & Asesmen, Konseling Multikultural, Manajemen BK"><?php echo esc_textarea($kepakaran); ?></textarea>
        <span class="ppb-form-hint">Setiap kepakaran yang dipisahkan koma akan otomatis dibuatkan tombol chip di kartu.</span>
    </div>

    <div class="ppb-form-row">
        <label for="ppb_dosen_sinta_url">Tautan Profil SINTA / Google Scholar / Dikti:</label>
        <input type="url" id="ppb_dosen_sinta_url" name="ppb_dosen_sinta_url" value="<?php echo esc_url($sinta_url); ?>" placeholder="https://sinta.kemdiktisaintek.go.id/authors/detail?id=...">
        <span class="ppb-form-hint">Link publikasi ilmiah dosen yang terbuka di tab baru saat tombol diklik.</span>
    </div>

    <?php
    $dosen_jenjang = get_post_meta($post->ID, '_ppb_dosen_jenjang', true);
    if (!is_array($dosen_jenjang)) {
        $dosen_jenjang = is_string($dosen_jenjang) && $dosen_jenjang !== '' ? array_map('trim', explode(',', $dosen_jenjang)) : array('s1');
    }
    $prodi_opts = function_exists('ppb_get_prodi_units') ? ppb_get_prodi_units(false) : array('s1' => array('kode' => 'S1', 'nama' => 'S1 BK'), 's2' => array('kode' => 'S2', 'nama' => 'S2 BK'), 's3' => array('kode' => 'S3', 'nama' => 'S3 BK'));
    ?>
    <div class="ppb-form-row">
        <label>Homebase / Jenjang Pengajaran (di bawah Jurusan PPB):</label>
        <div style="display:flex;flex-wrap:wrap;gap:14px;padding:8px 0;">
            <?php foreach ($prodi_opts as $pslug => $pmeta) : ?>
                <label style="display:inline-flex;align-items:center;gap:6px;font-weight:600;margin:0;cursor:pointer;">
                    <input type="checkbox" name="ppb_dosen_jenjang[]" value="<?php echo esc_attr($pslug); ?>" <?php checked(in_array($pslug, $dosen_jenjang, true)); ?> />
                    <?php echo esc_html($pmeta['kode'] . ' — ' . $pmeta['nama']); ?>
                </label>
            <?php endforeach; ?>
        </div>
        <span class="ppb-form-hint">Digunakan untuk filter jenjang pengajaran pada Direktori Dosen dan keterkaitan borang LAMDIK S1/S2/S3.</span>
    </div>
    <?php
}
}

if (!function_exists('ppb_dosen_save_meta')) {
function ppb_dosen_save_meta($post_id) {
    if (!isset($_POST['ppb_dosen_meta_nonce']) || !wp_verify_nonce($_POST['ppb_dosen_meta_nonce'], 'ppb_dosen_save_meta')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['ppb_dosen_nip'])) {
        update_post_meta($post_id, '_ppb_dosen_nip', sanitize_text_field($_POST['ppb_dosen_nip']));
    }
    if (isset($_POST['ppb_dosen_kategori'])) {
        update_post_meta($post_id, '_ppb_dosen_kategori', sanitize_text_field($_POST['ppb_dosen_kategori']));
    }
    if (isset($_POST['ppb_dosen_jabatan'])) {
        update_post_meta($post_id, '_ppb_dosen_jabatan', sanitize_text_field($_POST['ppb_dosen_jabatan']));
    }
    if (isset($_POST['ppb_dosen_inisial'])) {
        $ini = sanitize_text_field($_POST['ppb_dosen_inisial']);
        if (empty($ini)) {
            // Auto generate dari nama post_title
            $title = get_the_title($post_id);
            $words = explode(' ', trim($title));
            $ini = '';
            foreach ($words as $w) {
                $w = trim($w);
                if (!empty($w) && !in_array(strtolower($w), ['dr.', 'prof.', 'drs.', 'dra.', 'ir.', 'm.pd.', 's.pd.', 'm.si.', 'ph.d.', 'kons.'])) {
                    $ini .= strtoupper(substr($w, 0, 1));
                    if (strlen($ini) >= 2) break;
                }
            }
            if (empty($ini)) $ini = 'BK';
        }
        update_post_meta($post_id, '_ppb_dosen_inisial', strtoupper(substr($ini, 0, 3)));
    }
    if (isset($_POST['ppb_dosen_foto_link'])) {
        $raw_foto = wp_unslash($_POST['ppb_dosen_foto_link']);
        $clean_foto = function_exists('bk_sanitize_asset_or_url') ? bk_sanitize_asset_or_url($raw_foto) : sanitize_text_field($raw_foto);
        update_post_meta($post_id, '_ppb_dosen_foto_link', $clean_foto);
        update_post_meta($post_id, '_dosen_foto_link', $clean_foto);

        $post_slug = get_post_field('post_name', $post_id);
        if ($post_slug) {
            $theme_dir = get_template_directory();
            $json_file = $theme_dir . '/dosen-data.json';
            $dosen_list = get_option('bk_dosen_data_cache', array());
            if ((empty($dosen_list) || !is_array($dosen_list)) && file_exists($json_file)) {
                $dosen_list = json_decode((string) file_get_contents($json_file), true);
            }
            if (is_array($dosen_list)) {
                $changed = false;
                foreach ($dosen_list as &$item) {
                    if (($item['slug'] ?? '') === $post_slug) {
                        $item['foto_link'] = $clean_foto;
                        $changed = true;
                        break;
                    }
                }
                unset($item);
                if ($changed) {
                    update_option('bk_dosen_data_cache', $dosen_list, false);
                    if (file_exists($json_file)) {
                        @file_put_contents($json_file, json_encode($dosen_list, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                    }
                    if (function_exists('bk_fip_rebuild_dosen_roster_html')) {
                        bk_fip_rebuild_dosen_roster_html($dosen_list);
                    }
                }
            }
        }
    }
    if (isset($_POST['ppb_dosen_kepakaran'])) {
        update_post_meta($post_id, '_ppb_dosen_kepakaran', sanitize_textarea_field($_POST['ppb_dosen_kepakaran']));
    }
    if (isset($_POST['ppb_dosen_sinta_url'])) {
        update_post_meta($post_id, '_ppb_dosen_sinta_url', esc_url_raw($_POST['ppb_dosen_sinta_url']));
    }
    if (isset($_POST['ppb_dosen_jenjang']) && is_array($_POST['ppb_dosen_jenjang'])) {
        $clean_j = array_values(array_unique(array_filter(array_map('sanitize_key', wp_unslash($_POST['ppb_dosen_jenjang'])))));
        update_post_meta($post_id, '_ppb_dosen_jenjang', $clean_j);
    } elseif (isset($_POST['ppb_dosen_meta_nonce'])) {
        update_post_meta($post_id, '_ppb_dosen_jenjang', array('s1'));
    }

    // Auto purge Elementor cache untuk Post 25
    delete_post_meta(25, '_elementor_element_cache');
    delete_post_meta(25, '_elementor_css');
}
}
add_action('save_post_ppb_dosen', 'ppb_dosen_save_meta');

/* ========================================================================== */
/* 4. META BOX: DOKUMEN MUTU (LINK GOOGLE DRIVE)                              */
/* ========================================================================== */
if (!function_exists('ppb_dokumen_add_meta_box')) {
function ppb_dokumen_add_meta_box() {
    add_meta_box(
        'ppb_dokumen_meta_box',
        'Informasi Dokumen & Link Berkas Google Drive',
        'ppb_dokumen_render_meta_box',
        'ppb_dokumen',
        'normal',
        'high'
    );
}
}
add_action('add_meta_boxes', 'ppb_dokumen_add_meta_box');

if (!function_exists('ppb_dokumen_render_meta_box')) {
function ppb_dokumen_render_meta_box($post) {
    wp_nonce_field('ppb_dokumen_save_meta', 'ppb_dokumen_meta_nonce');

    $drive_link   = get_post_meta($post->ID, '_ppb_dokumen_link', true);
    $kategori     = get_post_meta($post->ID, '_ppb_dokumen_kategori', true) ?: 'renstra';
    $unit         = get_post_meta($post->ID, '_ppb_dokumen_unit', true) ?: 'jurusan';
    $nomor_sk     = get_post_meta($post->ID, '_ppb_dokumen_nomor', true);
    $tahun        = get_post_meta($post->ID, '_ppb_dokumen_tahun', true) ?: date('Y');
    $pengesah     = get_post_meta($post->ID, '_ppb_dokumen_pengesah', true);
    $size         = get_post_meta($post->ID, '_ppb_dokumen_size', true) ?: 'PDF';
    $deskripsi    = get_post_meta($post->ID, '_ppb_dokumen_deskripsi', true);
    $unit_opts    = function_exists('ppb_get_unit_options') ? ppb_get_unit_options(true, false) : array('jurusan' => '🏛️ Level Jurusan PPB', 's1' => 'S1 — Bimbingan dan Konseling', 's2' => 'S2 — Bimbingan dan Konseling', 's3' => 'S3 — Ilmu Pendidikan (BK)');
    ?>
    <style>
        .ppb-callout { background: #E8F0EA; border-left: 4px solid #0D3B22; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; font-size: 13px; color: #1e293b; }
        .ppb-callout strong { color: #0D3B22; }
    </style>

    <div class="ppb-callout">
        <strong>💡 Panduan Input Link Google Drive:</strong><br>
        Buka file di Google Drive &rarr; Klik kanan <strong>Bagikan (Share)</strong> &rarr; Setel ke <em>"Siapa saja yang memiliki link sebagai Pelihat (Viewer)"</em> &rarr; <strong>Salin Link</strong> dan tempelkan pada kolom di bawah.
    </div>

    <div class="ppb-form-row">
        <label for="ppb_dokumen_link"><strong style="color:#0D3B22;">Tautan Berkas (Link Google Drive / Upload PDF WP Media):</strong></label>
        <?php if (function_exists('ppb_render_media_url_control')) : ?>
            <?php ppb_render_media_url_control([
                'id'          => 'ppb_dokumen_link',
                'name'        => 'ppb_dokumen_link',
                'value'       => $drive_link,
                'placeholder' => 'https://drive.google.com/file/d/XXXXXX/view?usp=sharing atau upload PDF',
                'title'       => 'Pilih / Upload Dokumen Mutu PDF',
                'btn_label'   => '📁 Upload / Pilih PDF',
                'input_style' => 'border: 2px solid #0D3B22; background: #fff;',
            ]); ?>
        <?php else : ?>
            <input type="url" id="ppb_dokumen_link" name="ppb_dokumen_link" value="<?php echo esc_url($drive_link); ?>" placeholder="https://drive.google.com/file/d/XXXXXX/view?usp=sharing" style="border: 2px solid #0D3B22; background: #fff;">
        <?php endif; ?>
        <span class="ppb-form-hint">Tautan resmi Google Drive atau unggah berkas PDF langsung dari Media Library WordPress.</span>
    </div>

    <div class="ppb-form-row">
        <label for="ppb_dokumen_unit">Unit / Jenjang Pemilik Dokumen:</label>
        <select id="ppb_dokumen_unit" name="ppb_dokumen_unit">
            <?php foreach ($unit_opts as $uslug => $ulabel) : ?>
                <option value="<?php echo esc_attr($uslug); ?>" <?php selected($unit, $uslug); ?>><?php echo esc_html($ulabel); ?></option>
            <?php endforeach; ?>
        </select>
        <span class="ppb-form-hint">Pilih <strong>Level Jurusan</strong> untuk Renstra/Renop/SOP bersama, atau pilih jenjang <strong>S1 / S2 / S3</strong> untuk dokumen khusus program studi.</span>
    </div>

    <div class="ppb-form-row">
        <label for="ppb_dokumen_kategori">Kategori Dokumen:</label>
        <select id="ppb_dokumen_kategori" name="ppb_dokumen_kategori">
            <option value="renstra" <?php selected($kategori, 'renstra'); ?>>Rencana Strategis (Renstra)</option>
            <option value="renop" <?php selected($kategori, 'renop'); ?>>Rencana Operasional (Renop)</option>
            <option value="sk_senat" <?php selected($kategori, 'sk_senat'); ?>>SK Senat Fakultas</option>
            <option value="pedoman_penilaian" <?php selected($kategori, 'pedoman_penilaian'); ?>>Pedoman Penilaian / Kurikulum OBE</option>
            <option value="sk_mahasiswa_aktif" <?php selected($kategori, 'sk_mahasiswa_aktif'); ?>>SK Mahasiswa Aktif Rektorat</option>
            <option value="perlindungan_mahasiswa" <?php selected($kategori, 'perlindungan_mahasiswa'); ?>>SOP Perlindungan Mahasiswa &amp; PPKS</option>
            <option value="akreditasi" <?php selected($kategori, 'akreditasi'); ?>>Sertifikat / Dokumen Akreditasi</option>
            <option value="lainnya" <?php selected($kategori, 'lainnya'); ?>>Dokumen &amp; Kebijakan Lainnya</option>
        </select>
        <span class="ppb-form-hint">Kategori untuk filter tab interaktif pada halaman Dokumen Mutu.</span>
    </div>

    <div class="ppb-form-row">
        <label for="ppb_dokumen_nomor">Nomor Dokumen / SK Resmi:</label>
        <input type="text" id="ppb_dokumen_nomor" name="ppb_dokumen_nomor" value="<?php echo esc_attr($nomor_sk); ?>" placeholder="Contoh: RENSTRA-BK/UN36.2/2024 atau SK-SENAT/UN36.2/08/2024">
    </div>

    <div class="ppb-form-row">
        <label for="ppb_dokumen_tahun">Tahun Dokumen / Penetapan:</label>
        <input type="text" id="ppb_dokumen_tahun" name="ppb_dokumen_tahun" value="<?php echo esc_attr($tahun); ?>" placeholder="Contoh: 2025" style="max-width: 150px;">
    </div>

    <div class="ppb-form-row">
        <label for="ppb_dokumen_pengesah">Instansi / Pejabat yang Mengesahkan:</label>
        <input type="text" id="ppb_dokumen_pengesah" name="ppb_dokumen_pengesah" value="<?php echo esc_attr($pengesah); ?>" placeholder="Contoh: Dekan Fakultas Ilmu Pendidikan & Senat Fakultas">
    </div>

    <div class="ppb-form-row">
        <label for="ppb_dokumen_size">Ukuran File / Format:</label>
        <input type="text" id="ppb_dokumen_size" name="ppb_dokumen_size" value="<?php echo esc_attr($size); ?>" placeholder="Contoh: 4.5 MB" style="max-width: 150px;">
    </div>

    <div class="ppb-form-row">
        <label for="ppb_dokumen_deskripsi">Deskripsi Singkat / Ringkasan Dokumen:</label>
        <textarea id="ppb_dokumen_deskripsi" name="ppb_dokumen_deskripsi" rows="3" placeholder="Ringkasan poin utama dokumen atau dasar hukum penetapan..."><?php echo esc_textarea($deskripsi); ?></textarea>
    </div>
    <?php
}
}

if (!function_exists('ppb_dokumen_save_meta')) {
function ppb_dokumen_save_meta($post_id) {
    if (!isset($_POST['ppb_dokumen_meta_nonce']) || !wp_verify_nonce($_POST['ppb_dokumen_meta_nonce'], 'ppb_dokumen_save_meta')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['ppb_dokumen_link'])) {
        $raw_link = wp_unslash($_POST['ppb_dokumen_link']);
        $clean_link = function_exists('ppb_clean_document_url') ? ppb_clean_document_url($raw_link) : esc_url_raw($raw_link);
        update_post_meta($post_id, '_ppb_dokumen_link', $clean_link);
    }
    if (isset($_POST['ppb_dokumen_unit'])) {
        $raw_unit = sanitize_key(wp_unslash($_POST['ppb_dokumen_unit']));
        update_post_meta($post_id, '_ppb_dokumen_unit', $raw_unit ?: 'jurusan');
    }
    if (isset($_POST['ppb_dokumen_kategori'])) {
        update_post_meta($post_id, '_ppb_dokumen_kategori', sanitize_text_field($_POST['ppb_dokumen_kategori']));
    }
    if (isset($_POST['ppb_dokumen_nomor'])) {
        update_post_meta($post_id, '_ppb_dokumen_nomor', sanitize_text_field($_POST['ppb_dokumen_nomor']));
    }
    if (isset($_POST['ppb_dokumen_tahun'])) {
        update_post_meta($post_id, '_ppb_dokumen_tahun', sanitize_text_field($_POST['ppb_dokumen_tahun']));
    }
    if (isset($_POST['ppb_dokumen_pengesah'])) {
        update_post_meta($post_id, '_ppb_dokumen_pengesah', sanitize_text_field($_POST['ppb_dokumen_pengesah']));
    }
    if (isset($_POST['ppb_dokumen_size'])) {
        update_post_meta($post_id, '_ppb_dokumen_size', sanitize_text_field($_POST['ppb_dokumen_size']));
    }
    if (isset($_POST['ppb_dokumen_deskripsi'])) {
        update_post_meta($post_id, '_ppb_dokumen_deskripsi', sanitize_textarea_field($_POST['ppb_dokumen_deskripsi']));
    }

    // Auto purge Elementor cache untuk Post 27
    delete_post_meta(27, '_elementor_element_cache');
    delete_post_meta(27, '_elementor_css');
}
}
add_action('save_post_ppb_dokumen', 'ppb_dokumen_save_meta');

/* ========================================================================== */
/* 5. KOLOM DAFTAR TABEL WP ADMIN                                             */
/* ========================================================================== */
// Kolom Dosen
add_filter('manage_ppb_dosen_posts_columns', function($columns) {
    $new = [];
    $new['cb']         = $columns['cb'];
    $new['inisial']    = 'Inisial';
    $new['title']      = 'Nama Dosen & Gelar';
    $new['nip']        = 'NIP';
    $new['kategori']   = 'Kategori';
    $new['jabatan']    = 'Jabatan';
    $new['kepakaran']  = 'Kepakaran';
    $new['sinta']      = 'Profil SINTA';
    $new['menu_order'] = 'Urutan';
    return $new;
});

add_action('manage_ppb_dosen_posts_custom_column', function($column, $post_id) {
    switch ($column) {
        case 'inisial':
            $ini = get_post_meta($post_id, '_ppb_dosen_inisial', true) ?: 'BK';
            echo '<div style="width:34px;height:34px;border-radius:10px;background:#0D3B22;color:#F5B82E;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;">' . esc_html($ini) . '</div>';
            break;
        case 'nip':
            echo '<code>' . esc_html(get_post_meta($post_id, '_ppb_dosen_nip', true) ?: '-') . '</code>';
            break;
        case 'kategori':
            $kat = get_post_meta($post_id, '_ppb_dosen_kategori', true) ?: 'LEKTOR';
            echo '<span style="background:#E8F0EA;color:#0D3B22;font-size:10px;font-weight:700;padding:2px 8px;border-radius:9999px;">' . esc_html($kat) . '</span>';
            break;
        case 'jabatan':
            echo esc_html(get_post_meta($post_id, '_ppb_dosen_jabatan', true) ?: '-');
            break;
        case 'kepakaran':
            $kep = get_post_meta($post_id, '_ppb_dosen_kepakaran', true);
            if ($kep) {
                $tags = array_map('trim', explode(',', $kep));
                foreach ($tags as $t) {
                    echo '<span style="background:#FAF8F5;border:1px solid #E2E8F0;font-size:10px;padding:1px 6px;border-radius:4px;margin-right:3px;display:inline-block;margin-bottom:2px;">' . esc_html($t) . '</span>';
                }
            } else {
                echo '-';
            }
            break;
        case 'sinta':
            $url = get_post_meta($post_id, '_ppb_dosen_sinta_url', true);
            if ($url) {
                echo '<a href="' . esc_url($url) . '" target="_blank" style="font-weight:600;color:#0D3B22;">Buka SINTA ↗</a>';
            } else {
                echo '-';
            }
            break;
        case 'menu_order':
            $post = get_post($post_id);
            echo esc_html($post->menu_order);
            break;
    }
}, 10, 2);

// Kolom Dokumen
add_filter('manage_ppb_dokumen_posts_columns', function($columns) {
    $new = [];
    $new['cb']         = $columns['cb'];
    $new['title']      = 'Judul Dokumen';
    $new['unit']       = 'Unit / Jenjang';
    $new['kategori']   = 'Kategori';
    $new['nomor']      = 'Nomor SK';
    $new['tahun']      = 'Tahun';
    $new['pengesah']   = 'Pengesah';
    $new['link_drive'] = 'Link Google Drive';
    $new['menu_order'] = 'Urutan';
    return $new;
});

add_action('manage_ppb_dokumen_posts_custom_column', function($column, $post_id) {
    switch ($column) {
        case 'unit':
            $unit = get_post_meta($post_id, '_ppb_dokumen_unit', true) ?: 'jurusan';
            echo function_exists('ppb_render_unit_badge') ? ppb_render_unit_badge($unit) : esc_html(strtoupper($unit));
            break;
        case 'kategori':
            $kat = get_post_meta($post_id, '_ppb_dokumen_kategori', true) ?: 'renstra';
            echo '<span style="background:#0D3B22;color:#F5B82E;font-size:10.5px;font-weight:700;padding:2px 8px;border-radius:6px;text-transform:uppercase;">' . esc_html($kat) . '</span>';
            break;
        case 'nomor':
            echo '<code>' . esc_html(get_post_meta($post_id, '_ppb_dokumen_nomor', true) ?: '-') . '</code>';
            break;
        case 'tahun':
            echo esc_html(get_post_meta($post_id, '_ppb_dokumen_tahun', true) ?: '-');
            break;
        case 'pengesah':
            echo esc_html(get_post_meta($post_id, '_ppb_dokumen_pengesah', true) ?: '-');
            break;
        case 'link_drive':
            $link = get_post_meta($post_id, '_ppb_dokumen_link', true);
            if ($link) {
                echo '<a href="' . esc_url($link) . '" target="_blank" style="background:#E8F0EA;color:#0D3B22;padding:4px 10px;border-radius:6px;text-decoration:none;font-weight:700;font-size:11px;display:inline-flex;align-items:center;gap:4px;">Buka Drive ↗</a>';
            } else {
                echo '<span style="color:#ef4444;font-size:11px;">Belum Diisi</span>';
            }
            break;
        case 'menu_order':
            $post = get_post($post_id);
            echo esc_html($post->menu_order);
            break;
    }
}, 10, 2);

/* ========================================================================== */
/* 6. ENGINE FRONTEND SHORTCODES: [ppb_dosen_roster] & [ppb_dokumen_grid]    */
/* ========================================================================== */

// SHORTCODE DOSEN
if (!function_exists('ppb_shortcode_dosen_roster')) {
function ppb_shortcode_dosen_roster() {
    $query = new WP_Query([
        'post_type'      => 'ppb_dosen',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => ['menu_order' => 'ASC', 'title' => 'ASC'],
    ]);

    if (!$query->have_posts()) {
        return '<div style="padding:40px;text-align:center;font-size:14px;color:#6B7280;font-family:\'Plus Jakarta Sans\',sans-serif;">Belum ada data dosen yang ditambahkan di WP Admin.</div>';
    }

    $all_dosen = [];
    $all_tags  = [];

    while ($query->have_posts()) {
        $query->the_post();
        $id = get_the_ID();
        $title = get_the_title();
        $nip = get_post_meta($id, '_ppb_dosen_nip', true);
        $kategori = get_post_meta($id, '_ppb_dosen_kategori', true) ?: 'LEKTOR';
        $jabatan = get_post_meta($id, '_ppb_dosen_jabatan', true);
        $inisial = get_post_meta($id, '_ppb_dosen_inisial', true) ?: 'BK';
        $foto_raw = get_post_meta($id, '_ppb_dosen_foto_link', true) ?: get_post_meta($id, '_dosen_foto_link', true);
        $foto_url = ($foto_raw && function_exists('bk_resolve_theme_asset_url')) ? bk_resolve_theme_asset_url($foto_raw) : $foto_raw;
        $kepakaran_raw = get_post_meta($id, '_ppb_dosen_kepakaran', true);
        $sinta_url = get_post_meta($id, '_ppb_dosen_sinta_url', true) ?: 'https://sinta.kemdiktisaintek.go.id/';
        $jenjang_raw = get_post_meta($id, '_ppb_dosen_jenjang', true);
        if (!is_array($jenjang_raw)) {
            $jenjang_raw = is_string($jenjang_raw) && $jenjang_raw !== '' ? array_map('trim', explode(',', $jenjang_raw)) : array('s1');
        }
        $jenjang_arr = array_values(array_unique(array_filter(array_map('sanitize_key', $jenjang_raw))));
        if (empty($jenjang_arr)) $jenjang_arr = array('s1');

        $kepakaran = [];
        if ($kepakaran_raw) {
            $parts = explode(',', $kepakaran_raw);
            foreach ($parts as $p) {
                $p = trim($p);
                if (!empty($p)) {
                    $kepakaran[] = $p;
                    $all_tags[$p] = true;
                }
            }
        }

        // Badge styling
        $badge_bg = '#E8F0EA';
        $badge_color = '#0D3B22';
        if ($kategori === 'GURU BESAR') {
            $badge_bg = '#FEF3C7';
            $badge_color = '#92400E';
        } elseif ($kategori === 'KETUA JURUSAN') {
            $badge_bg = '#E8F0EA';
            $badge_color = '#0D3B22';
        } elseif ($kategori === 'LEKTOR KEPALA') {
            $badge_bg = '#E0E7FF';
            $badge_color = '#3730A3';
        } elseif ($kategori === 'LEKTOR') {
            $badge_bg = '#E0F2FE';
            $badge_color = '#0369A1';
        }

        $all_dosen[] = [
            'id' => $id,
            'nama' => $title,
            'nip' => $nip,
            'kategori' => $kategori,
            'badge_bg' => $badge_bg,
            'badge_color' => $badge_color,
            'jabatan' => $jabatan,
            'inisial' => $inisial,
            'foto_url' => $foto_url,
            'kepakaran' => $kepakaran,
            'jenjang' => $jenjang_arr,
            'sinta_url' => $sinta_url,
        ];
    }
    wp_reset_postdata();

    $prodi_units = function_exists('ppb_get_prodi_units') ? ppb_get_prodi_units(true) : array('s1' => array('kode' => 'S1'), 's2' => array('kode' => 'S2'), 's3' => array('kode' => 'S3'));

    // Render HTML
    ob_start();
    ?>
    <section style="padding:0 var(--bk-edge-pad, 1rem) 40px;box-sizing:border-box;font-family:'Plus Jakarta Sans',sans-serif;">
        <div style="max-width:var(--max-w, 1160px);width:100%;margin:0 auto;">
            <!-- Filter Bar -->
            <div style="background:#ffffff;border:1px solid #D4E3D8;border-radius:20px;padding:14px 18px;margin-bottom:28px;box-shadow:0 4px 16px -6px rgba(13,59,34,0.06);display:flex;flex-direction:column;gap:10px;">
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                    <div style="font-size:12px;font-weight:700;color:#0D3B22;display:flex;align-items:center;gap:6px;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                        <span>Filter Kepakaran:</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;overflow-x:auto;padding-bottom:2px;" class="no-scrollbar" id="bk-chip-filters">
                        <button type="button" class="bk-chip-btn active" data-tag="semua" style="border:none;background:#0D3B22;color:#F5B82E;font-size:11.5px;font-weight:700;padding:6px 14px;border-radius:9999px;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;white-space:nowrap;">Semua Kepakaran</button>
                        <?php 
                        $popular_tags = ['Psikometri', 'Multikultural', 'Cybercounseling', 'Karier', 'Teori', 'Evaluasi'];
                        foreach ($popular_tags as $pt) : ?>
                            <button type="button" class="bk-chip-btn" data-tag="<?php echo esc_attr($pt); ?>" style="border:none;background:#FAF8F5;color:#4B5563;font-size:11.5px;font-weight:600;padding:6px 14px;border-radius:9999px;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;white-space:nowrap;border:1px solid #E5E7EB;"><?php echo esc_html($pt); ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;border-top:1px solid #F1F5F2;padding-top:10px;" id="bk-dosen-jenjang-filters">
                    <span style="font-size:11.5px;font-weight:700;color:#0D3B22;">Jenjang Pengajaran (Jurusan PPB):</span>
                    <button type="button" class="bk-jenjang-btn active" data-jenjang="semua" style="border:none;background:#0D3B22;color:#F5B82E;font-size:11px;font-weight:700;padding:4px 12px;border-radius:9999px;cursor:pointer;">Semua Jenjang</button>
                    <?php foreach ($prodi_units as $pslug => $pmeta) : ?>
                        <button type="button" class="bk-jenjang-btn" data-jenjang="<?php echo esc_attr($pslug); ?>" style="border:1px solid #E5E7EB;background:#FAF8F5;color:#4B5563;font-size:11px;font-weight:600;padding:4px 12px;border-radius:9999px;cursor:pointer;"><?php echo esc_html($pmeta['tab_label'] ?: $pmeta['kode']); ?></button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Grid Roster Dosen -->
            <div id="bk-dosen-grid" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(280px, 1fr));gap:20px;">
                <?php foreach ($all_dosen as $d) : 
                    $searchable = strtolower($d['nama'] . ' ' . $d['nip'] . ' ' . $d['jabatan'] . ' ' . implode(' ', $d['kepakaran']));
                    $jenjang_str = implode(',', $d['jenjang']);
                ?>
                <div class="bk-dosen-card-item" data-text="<?php echo esc_attr($searchable); ?>" data-jenjang="<?php echo esc_attr($jenjang_str); ?>" style="background:#ffffff;border:1px solid #D4E3D8;border-radius:22px;padding:22px;display:flex;flex-direction:column;justify-content:space-between;box-shadow:0 6px 18px -8px rgba(13,59,34,0.06);transition:all 0.2s;" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 10px 24px -8px rgba(13,59,34,0.12)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 6px 18px -8px rgba(13,59,34,0.06)'">
                    <div>
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                            <div class="bk-avatar<?php echo !empty($d['foto_url']) ? ' has-photo' : ''; ?>" data-fb-init="<?php echo esc_attr($d['inisial']); ?>" style="width:46px;height:46px;border-radius:50%;background:#0D3B22;color:#F5B82E;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:16px;font-family:'Fraunces',Georgia,serif;overflow:hidden;border:2px solid <?php echo ($d['kategori'] === 'GURU BESAR') ? '#F5B82E' : 'rgba(13,59,34,0.18)'; ?>;">
                                <?php if (!empty($d['foto_url'])) : ?>
                                    <img src="<?php echo esc_url($d['foto_url']); ?>" alt="<?php echo esc_attr($d['nama']); ?>" style="width:100%;height:100%;object-fit:cover;object-position:50% 22%;display:block;" onerror="var p=this.parentElement;if(p){p.classList.remove('has-photo');p.textContent=p.getAttribute('data-fb-init')||'BK';}">
                                <?php else : ?>
                                    <?php echo esc_html($d['inisial']); ?>
                                <?php endif; ?>
                            </div>
                            <span style="font-size:9.5px;font-weight:800;background:<?php echo $d['badge_bg']; ?>;color:<?php echo $d['badge_color']; ?>;padding:3px 8px;border-radius:9999px;letter-spacing:0.04em;">
                                <?php echo esc_html($d['kategori']); ?>
                            </span>
                        </div>

                        <h3 style="font-family:'Fraunces',Georgia,serif;font-size:16px;font-weight:700;color:#111827;line-height:1.3;margin:0 0 3px 0;">
                            <?php echo esc_html($d['nama']); ?>
                        </h3>
                        <p style="font-size:11.5px;color:#0D3B22;font-weight:600;margin:0 0 4px 0;"><?php echo esc_html($d['jabatan']); ?></p>
                        <p style="font-family:'IBM Plex Mono',monospace;font-size:10.5px;color:#6B7280;margin:0 0 8px 0;">NIP. <?php echo esc_html($d['nip']); ?></p>
                        <div style="display:flex;flex-wrap:wrap;gap:4px;margin-bottom:10px;">
                            <?php foreach ($d['jenjang'] as $jslug) :
                                echo function_exists('ppb_render_unit_badge') ? ppb_render_unit_badge($jslug) : '';
                            endforeach; ?>
                        </div>

                        <?php if (!empty($d['kepakaran'])) : ?>
                        <div style="border-top:1px solid #F3F4F6;padding-top:10px;margin-bottom:12px;">
                            <span style="font-size:10px;font-weight:700;color:#9CA3AF;text-transform:uppercase;letter-spacing:0.06em;display:block;margin-bottom:6px;">Kepakaran:</span>
                            <div style="display:flex;flex-wrap:wrap;gap:4px;">
                                <?php foreach ($d['kepakaran'] as $kep) : ?>
                                    <span style="font-size:10.5px;background:#FAF8F5;border:1px solid #E5E7EB;color:#374151;padding:2px 7px;border-radius:6px;"><?php echo esc_html($kep); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div style="border-top:1px solid #F3F4F6;padding-top:12px;">
                        <a href="<?php echo esc_url($d['sinta_url']); ?>" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:6px;font-size:11.5px;font-weight:700;color:#0D3B22;text-decoration:none;padding:5px 10px;background:#E8F0EA;border-radius:8px;transition:all 0.2s;" onmouseover="this.style.background='#0D3B22';this.style.color='#F5B82E'" onmouseout="this.style.background='#E8F0EA';this.style.color='#0D3B22'">
                            <span>Profil SINTA / Publikasi</span>
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Script Live Search & Filter -->
        <script>
        (function(){
            const input = document.getElementById("bk-dosen-search-input");
            const countInfo = document.getElementById("bk-dosen-count-info");
            const chips = document.querySelectorAll("#bk-chip-filters .bk-chip-btn");
            const jBtns = document.querySelectorAll("#bk-dosen-jenjang-filters .bk-jenjang-btn");
            const cards = document.querySelectorAll("#bk-dosen-grid .bk-dosen-card-item");
            let activeTag = "semua";
            let activeJenjang = "semua";

            function filterDosen() {
                const query = (input ? input.value : "").toLowerCase().trim();
                let visibleCount = 0;

                cards.forEach(card => {
                    const text = card.getAttribute("data-text") || "";
                    const jlist = (card.getAttribute("data-jenjang") || "s1").split(",");
                    const matchQuery = !query || text.includes(query);
                    const matchTag = (activeTag === "semua") || text.includes(activeTag.toLowerCase());
                    const matchJenjang = (activeJenjang === "semua") || jlist.indexOf(activeJenjang) !== -1;

                    if (matchQuery && matchTag && matchJenjang) {
                        card.style.display = "flex";
                        visibleCount++;
                    } else {
                        card.style.display = "none";
                    }
                });

                if (countInfo) {
                    if (query || activeTag !== "semua" || activeJenjang !== "semua") {
                        countInfo.innerHTML = "Ditemukan <strong style='color:#F5B82E;'>" + visibleCount + "</strong> dosen yang sesuai filter";
                    } else {
                        countInfo.innerHTML = "Menampilkan seluruh " + cards.length + " dosen dan tenaga pengajar Jurusan PPB";
                    }
                }
            }

            if (input) {
                input.addEventListener("input", filterDosen);
            }

            chips.forEach(chip => {
                chip.addEventListener("click", function(){
                    chips.forEach(c => {
                        c.style.background = "#FAF8F5";
                        c.style.color = "#4B5563";
                        c.classList.remove("active");
                    });
                    this.style.background = "#0D3B22";
                    this.style.color = "#F5B82E";
                    this.classList.add("active");
                    activeTag = this.getAttribute("data-tag") || "semua";
                    filterDosen();
                });
            });

            jBtns.forEach(btn => {
                btn.addEventListener("click", function(){
                    jBtns.forEach(b => {
                        b.style.background = "#FAF8F5";
                        b.style.color = "#4B5563";
                        b.classList.remove("active");
                    });
                    this.style.background = "#0D3B22";
                    this.style.color = "#F5B82E";
                    this.classList.add("active");
                    activeJenjang = this.getAttribute("data-jenjang") || "semua";
                    filterDosen();
                });
            });
        })();
        </script>
    </section>
    <?php
    return ob_get_clean();
}
}
add_shortcode('ppb_dosen_roster', 'ppb_shortcode_dosen_roster');

if (!function_exists('ppb_dokumen_normalize_category')) {
function ppb_dokumen_normalize_category($raw) {
    $norm = strtolower(trim((string) $raw));
    $norm = str_replace(array('&amp;', '&'), ' dan ', $norm);
    $norm = preg_replace('/\s+/', ' ', $norm);

    if ($norm === 'renop' || strpos($norm, 'operasional') !== false) {
        return ['slug' => 'renop', 'label' => 'Renop'];
    }
    if ($norm === 'renstra' || strpos($norm, 'rencana strategis') !== false || strpos($norm, 'strategis') !== false) {
        return ['slug' => 'renstra', 'label' => 'Renstra'];
    }
    if ($norm === 'sk_senat' || strpos($norm, 'sk dan regulasi') !== false || strpos($norm, 'sk & regulasi') !== false || strpos($norm, 'senat') !== false) {
        return ['slug' => 'sk_senat', 'label' => 'SK Senat'];
    }
    if ($norm === 'sk_mahasiswa_aktif' || strpos($norm, 'mahasiswa aktif') !== false) {
        return ['slug' => 'sk_mahasiswa_aktif', 'label' => 'SK Mahasiswa Aktif'];
    }
    if ($norm === 'pedoman_penilaian' || strpos($norm, 'pedoman penilaian') !== false || strpos($norm, 'kurikulum') !== false || strpos($norm, 'akademik') !== false) {
        return ['slug' => 'pedoman_penilaian', 'label' => 'Pedoman Penilaian'];
    }
    if ($norm === 'perlindungan_mahasiswa' || strpos($norm, 'perlindungan') !== false || strpos($norm, 'ppks') !== false || strpos($norm, 'sop') !== false) {
        return ['slug' => 'perlindungan_mahasiswa', 'label' => 'SOP Perlindungan'];
    }
    if ($norm === 'akreditasi' || strpos($norm, 'akreditasi') !== false || strpos($norm, 'mutu') !== false) {
        return ['slug' => 'akreditasi', 'label' => 'Akreditasi'];
    }
    return ['slug' => 'lainnya', 'label' => ($raw !== '' ? (string) $raw : 'Lainnya')];
}
}

// SHORTCODE DOKUMEN MUTU
if (!function_exists('ppb_shortcode_dokumen_grid')) {
function ppb_shortcode_dokumen_grid() {
    $query = new WP_Query([
        'post_type'      => 'ppb_dokumen',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => ['menu_order' => 'ASC', 'date' => 'DESC'],
    ]);

    if (!$query->have_posts()) {
        return '<div style="padding:40px;text-align:center;font-size:14px;color:#6B7280;font-family:\'Plus Jakarta Sans\',sans-serif;">Belum ada dokumen mutu yang ditambahkan di WP Admin.</div>';
    }

    $all_dok = [];
    while ($query->have_posts()) {
        $query->the_post();
        $id = get_the_ID();
        $raw_link = get_post_meta($id, '_ppb_dokumen_link', true);
        $clean_link = function_exists('ppb_clean_document_url') ? ppb_clean_document_url($raw_link) : esc_url_raw((string) $raw_link);
        $raw_kat  = get_post_meta($id, '_ppb_dokumen_kategori', true) ?: 'renstra';
        $cat_info = ppb_dokumen_normalize_category($raw_kat);
        $raw_unit = get_post_meta($id, '_ppb_dokumen_unit', true);
        if (!$raw_unit) {
            $raw_unit = in_array($cat_info['slug'], ['pedoman_penilaian', 'sk_mahasiswa_aktif'], true) ? 's1' : 'jurusan';
        }

        $all_dok[] = [
            'id'        => $id,
            'judul'     => get_the_title(),
            'link'      => $clean_link,
            'unit'      => sanitize_key($raw_unit),
            'kategori'  => $cat_info['slug'],
            'cat_label' => $cat_info['label'],
            'nomor'     => get_post_meta($id, '_ppb_dokumen_nomor', true),
            'tahun'     => get_post_meta($id, '_ppb_dokumen_tahun', true) ?: date('Y'),
            'pengesah'  => get_post_meta($id, '_ppb_dokumen_pengesah', true),
            'size'      => get_post_meta($id, '_ppb_dokumen_size', true) ?: 'PDF',
            'desc'      => get_post_meta($id, '_ppb_dokumen_deskripsi', true),
        ];
    }
    wp_reset_postdata();

    $has_akreditasi = false;
    $has_lainnya = false;
    foreach ($all_dok as $d_chk) {
        if ($d_chk['kategori'] === 'akreditasi') $has_akreditasi = true;
        if ($d_chk['kategori'] === 'lainnya') $has_lainnya = true;
    }

    $unit_opts = function_exists('ppb_get_unit_options') ? ppb_get_unit_options(true, true) : ['jurusan' => 'Jurusan PPB', 's1' => 'S1', 's2' => 'S2', 's3' => 'S3'];

    ob_start();
    ?>
    <section style="padding:0 var(--bk-edge-pad, 1rem) 40px;box-sizing:border-box;font-family:'Plus Jakarta Sans',sans-serif;">
        <div style="max-width:var(--max-w, 1160px);width:100%;margin:0 auto;">
            <!-- Unit / Jenjang Tabs Bar -->
            <div style="background:#ffffff;border:1px solid #D4E3D8;border-radius:16px;padding:10px 14px;margin-bottom:14px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
                <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;" id="bk-doc-unit-tabs">
                    <span style="font-size:11.5px;font-weight:700;color:#0D3B22;margin-right:4px;">🏛️ Unit / Jenjang:</span>
                    <button type="button" class="bk-doc-unit-btn active" data-unit="semua" style="border:none;background:#0D3B22;color:#F5B82E;font-size:11.5px;font-weight:700;padding:6px 13px;border-radius:9999px;cursor:pointer;">Semua Unit</button>
                    <?php foreach ($unit_opts as $uslug => $ulabel) :
                        $short_lbl = function_exists('ppb_get_unit_label') ? ppb_get_unit_label($uslug, true) : strtoupper($uslug);
                    ?>
                        <button type="button" class="bk-doc-unit-btn" data-unit="<?php echo esc_attr($uslug); ?>" style="border:1px solid #E5E7EB;background:#FAF8F5;color:#4B5563;font-size:11.5px;font-weight:600;padding:6px 13px;border-radius:9999px;cursor:pointer;"><?php echo esc_html($short_lbl); ?></button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Category Tabs Bar -->
            <div style="background:#ffffff;border:1px solid #D4E3D8;border-radius:20px;padding:12px 16px;margin-bottom:28px;box-shadow:0 4px 16px -6px rgba(13,59,34,0.06);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                <div style="display:flex;align-items:center;gap:6px;overflow-x:auto;padding-bottom:2px;" class="no-scrollbar" id="bk-doc-cat-tabs">
                    <button type="button" class="bk-doc-tab-btn active" data-cat="semua" style="border:none;background:#0D3B22;color:#F5B82E;font-size:11.5px;font-weight:700;padding:6px 14px;border-radius:9999px;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;white-space:nowrap;">Semua Dokumen</button>
                    <button type="button" class="bk-doc-tab-btn" data-cat="renstra" style="border:none;background:#FAF8F5;color:#4B5563;font-size:11.5px;font-weight:600;padding:6px 14px;border-radius:9999px;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;white-space:nowrap;border:1px solid #E5E7EB;">Renstra &amp; Renop</button>
                    <button type="button" class="bk-doc-tab-btn" data-cat="sk_senat" style="border:none;background:#FAF8F5;color:#4B5563;font-size:11.5px;font-weight:600;padding:6px 14px;border-radius:9999px;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;white-space:nowrap;border:1px solid #E5E7EB;">SK Senat &amp; Tata Kelola</button>
                    <button type="button" class="bk-doc-tab-btn" data-cat="pedoman_penilaian" style="border:none;background:#FAF8F5;color:#4B5563;font-size:11.5px;font-weight:600;padding:6px 14px;border-radius:9999px;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;white-space:nowrap;border:1px solid #E5E7EB;">Pedoman OBE</button>
                    <button type="button" class="bk-doc-tab-btn" data-cat="sk_mahasiswa_aktif" style="border:none;background:#FAF8F5;color:#4B5563;font-size:11.5px;font-weight:600;padding:6px 14px;border-radius:9999px;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;white-space:nowrap;border:1px solid #E5E7EB;">SK Mahasiswa Aktif</button>
                    <button type="button" class="bk-doc-tab-btn" data-cat="perlindungan_mahasiswa" style="border:none;background:#FAF8F5;color:#4B5563;font-size:11.5px;font-weight:600;padding:6px 14px;border-radius:9999px;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;white-space:nowrap;border:1px solid #E5E7EB;">SOP Perlindungan &amp; PPKS</button>
                    <?php if ($has_akreditasi) : ?>
                        <button type="button" class="bk-doc-tab-btn" data-cat="akreditasi" style="border:none;background:#FAF8F5;color:#4B5563;font-size:11.5px;font-weight:600;padding:6px 14px;border-radius:9999px;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;white-space:nowrap;border:1px solid #E5E7EB;">Akreditasi &amp; Mutu</button>
                    <?php endif; ?>
                    <?php if ($has_lainnya) : ?>
                        <button type="button" class="bk-doc-tab-btn" data-cat="lainnya" style="border:none;background:#FAF8F5;color:#4B5563;font-size:11.5px;font-weight:600;padding:6px 14px;border-radius:9999px;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;white-space:nowrap;border:1px solid #E5E7EB;">Lainnya</button>
                    <?php endif; ?>
                </div>
                <div style="font-size:12px;color:#6B7280;font-weight:600;" id="bk-doc-count-info">
                    Total: <strong style="color:#0D3B22;"><?php echo count($all_dok); ?></strong> dokumen tersedia
                </div>
            </div>

            <!-- Grid Dokumen -->
            <div id="bk-dokumen-grid" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(340px, 1fr));gap:20px;">
                <?php foreach ($all_dok as $d) : ?>
                <div class="bk-doc-card-item" data-cat="<?php echo esc_attr($d['kategori']); ?>" data-unit="<?php echo esc_attr($d['unit']); ?>" style="background:#ffffff;border:1px solid #D4E3D8;border-radius:24px;padding:24px;display:flex;flex-direction:column;justify-content:space-between;box-shadow:0 6px 18px -8px rgba(13,59,34,0.06);transition:all 0.2s;" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 10px 24px -8px rgba(13,59,34,0.12)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 6px 18px -8px rgba(13,59,34,0.06)'">
                    <div>
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:12px;flex-wrap:wrap;">
                            <div style="display:inline-flex;align-items:center;gap:6px;flex-wrap:wrap;">
                                <?php echo function_exists('ppb_render_unit_badge') ? ppb_render_unit_badge($d['unit']) : ''; ?>
                                <span style="font-size:10px;font-weight:700;background:#E8F0EA;color:#0D3B22;padding:3px 9px;border-radius:9999px;text-transform:uppercase;">
                                    <?php echo esc_html($d['cat_label']); ?>
                                </span>
                            </div>
                            <span style="font-size:11px;font-weight:600;color:#6B7280;">Tahun <?php echo esc_html($d['tahun']); ?></span>
                        </div>

                        <h3 style="font-family:'Fraunces',Georgia,serif;font-size:16px;font-weight:600;color:#111827;line-height:1.35;margin:0 0 10px 0;">
                            <?php echo esc_html($d['judul']); ?>
                        </h3>

                        <?php if (!empty($d['nomor'])) : ?>
                        <div style="font-family:'IBM Plex Mono',monospace;font-size:11px;color:#0D3B22;background:#FAF8F5;border:1px solid #E5E7EB;padding:4px 8px;border-radius:6px;display:inline-block;margin-bottom:10px;">
                            <?php echo esc_html($d['nomor']); ?>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($d['pengesah'])) : ?>
                        <p style="font-size:11.5px;color:#6B7280;margin:0 0 8px 0;">
                            Pengesah: <strong style="color:#374151;"><?php echo esc_html($d['pengesah']); ?></strong>
                        </p>
                        <?php endif; ?>

                        <?php if (!empty($d['desc'])) : ?>
                        <p style="font-size:12px;color:#4B5563;line-height:1.6;margin:0 0 14px 0;text-align:justify;">
                            <?php echo esc_html($d['desc']); ?>
                        </p>
                        <?php endif; ?>
                    </div>

                    <div style="border-top:1px solid #F3F4F6;padding-top:14px;display:flex;align-items:center;justify-content:space-between;gap:10px;">
                        <span style="font-size:11px;color:#9CA3AF;font-weight:500;">
                            Ukuran: <?php echo esc_html($d['size']); ?>
                        </span>
                        <?php if ($d['link'] !== '') : ?>
                            <a href="<?php echo esc_url($d['link']); ?>" target="_blank" rel="noopener noreferrer" class="bk-doc-btn" style="display:inline-flex;align-items:center;gap:6px;background:#0D3B22;color:#F5B82E;font-size:12px;font-weight:700;padding:8px 16px;border-radius:9999px;text-decoration:none;transition:all 0.2s;box-shadow:0 3px 8px rgba(13,59,34,0.18);" onmouseover="this.style.background='#185333'" onmouseout="this.style.background='#0D3B22'">
                                <span>Unduh Dokumen PDF</span>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            </a>
                        <?php else : ?>
                            <span style="display:inline-flex;align-items:center;padding:7px 14px;border-radius:9999px;background:#F3F4F6;color:#6B7280;font-size:11.5px;font-weight:600;">Segera Diunggah</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Script Filter Tabs Dokumen (Kategori & Unit) -->
        <script>
        (function(){
            const catTabs = document.querySelectorAll("#bk-doc-cat-tabs .bk-doc-tab-btn");
            const unitTabs = document.querySelectorAll("#bk-doc-unit-tabs .bk-doc-unit-btn");
            const cards = document.querySelectorAll("#bk-dokumen-grid .bk-doc-card-item");
            const countInfo = document.getElementById("bk-doc-count-info");
            let activeCat = "semua";
            let activeUnit = "semua";

            function applyDocFilter() {
                let visible = 0;
                cards.forEach(card => {
                    const cardCat = card.getAttribute("data-cat") || "";
                    const cardUnit = card.getAttribute("data-unit") || "jurusan";
                    let matchCat = (activeCat === "semua");
                    if (activeCat === "renstra" && (cardCat === "renstra" || cardCat === "renop")) matchCat = true;
                    if (activeCat === cardCat) matchCat = true;
                    let matchUnit = (activeUnit === "semua") || (cardUnit === activeUnit);

                    if (matchCat && matchUnit) {
                        card.style.display = "flex";
                        visible++;
                    } else {
                        card.style.display = "none";
                    }
                });
                if (countInfo) {
                    countInfo.innerHTML = "Total: <strong style='color:#0D3B22;'>" + visible + "</strong> dokumen";
                }
            }

            catTabs.forEach(tab => {
                tab.addEventListener("click", function(){
                    catTabs.forEach(t => { t.style.background = "#FAF8F5"; t.style.color = "#4B5563"; t.classList.remove("active"); });
                    this.style.background = "#0D3B22"; this.style.color = "#F5B82E"; this.classList.add("active");
                    activeCat = this.getAttribute("data-cat") || "semua";
                    applyDocFilter();
                });
            });

            unitTabs.forEach(tab => {
                tab.addEventListener("click", function(){
                    unitTabs.forEach(t => { t.style.background = "#FAF8F5"; t.style.color = "#4B5563"; t.classList.remove("active"); });
                    this.style.background = "#0D3B22"; this.style.color = "#F5B82E"; this.classList.add("active");
                    activeUnit = this.getAttribute("data-unit") || "semua";
                    applyDocFilter();
                });
            });
        })();
        </script>
    </section>
    <?php
    return ob_get_clean();
}
}
add_shortcode('ppb_dokumen_grid', 'ppb_shortcode_dokumen_grid');

// Sinkronkan CPT Dokumen Mutu (`ppb_dokumen`) ke halaman statis /profil/dokumen/ (#bk-dok-grid)
if (!function_exists('ppb_dokumen_sync_page_content')) {
function ppb_dokumen_sync_page_content($content) {
    if (!is_string($content) || $content === '' || is_admin()) {
        return $content;
    }
    if (!function_exists('is_page') || (!is_page() && !is_singular())) {
        return $content;
    }

    $slug_path = function_exists('ppb_get_current_page_slug_path') ? ppb_get_current_page_slug_path() : '';
    $is_dok_page = ($slug_path === 'profil/dokumen' || $slug_path === 'dokumen' || strpos($content, 'id="bk-dok-grid"') !== false || strpos($content, "id='bk-dok-grid'") !== false);
    if (!$is_dok_page || strpos($content, 'bk-dok-grid') === false) {
        return $content;
    }

    $query = new WP_Query([
        'post_type'      => 'ppb_dokumen',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => ['menu_order' => 'ASC', 'date' => 'DESC'],
    ]);

    if (!$query->have_posts()) {
        wp_reset_postdata();
        return $content;
    }

    $extra_cats = [];
    $cards_html = '';

    while ($query->have_posts()) {
        $query->the_post();
        $id       = get_the_ID();
        $judul    = get_the_title();
        $raw_link = get_post_meta($id, '_ppb_dokumen_link', true);
        $link     = function_exists('ppb_clean_document_url') ? ppb_clean_document_url($raw_link) : esc_url_raw((string) $raw_link);
        $raw_kat  = get_post_meta($id, '_ppb_dokumen_kategori', true) ?: 'renstra';
        $cat_info = ppb_dokumen_normalize_category($raw_kat);
        $raw_unit = get_post_meta($id, '_ppb_dokumen_unit', true);
        if (!$raw_unit) {
            $raw_unit = in_array($cat_info['slug'], ['pedoman_penilaian', 'sk_mahasiswa_aktif'], true) ? 's1' : 'jurusan';
        }
        $unit_slug = sanitize_key($raw_unit);
        $unit_lbl  = function_exists('ppb_get_unit_label') ? ppb_get_unit_label($unit_slug, true) : strtoupper($unit_slug);
        $nomor     = (string) get_post_meta($id, '_ppb_dokumen_nomor', true);
        $tahun     = (string) (get_post_meta($id, '_ppb_dokumen_tahun', true) ?: date('Y'));
        $pengesah  = (string) get_post_meta($id, '_ppb_dokumen_pengesah', true);
        $size      = (string) (get_post_meta($id, '_ppb_dokumen_size', true) ?: 'PDF');
        $desc      = (string) get_post_meta($id, '_ppb_dokumen_deskripsi', true);

        if (!in_array($cat_info['slug'], ['renstra', 'renop', 'sk_senat', 'sk_mahasiswa_aktif', 'pedoman_penilaian', 'perlindungan_mahasiswa'], true)) {
            $extra_cats[$cat_info['slug']] = $cat_info['label'];
        }

        $search_str = strtolower(trim($judul . ' ' . $nomor . ' ' . $pengesah . ' ' . $desc . ' ' . $cat_info['label'] . ' ' . $unit_lbl));

        $cards_html .= '<div class="bk-dok-card-item" data-kat="' . esc_attr($cat_info['slug']) . '" data-unit="' . esc_attr($unit_slug) . '" data-search="' . esc_attr($search_str) . '" style="background:#ffffff;border:1px solid #D4E3D8;border-radius:22px;padding:22px;display:flex;flex-direction:column;justify-content:space-between">'
            . '<div>'
            . '<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;gap:6px;flex-wrap:wrap">'
            . '<div style="display:inline-flex;align-items:center;gap:5px;flex-wrap:wrap">'
            . (function_exists('ppb_render_unit_badge') ? ppb_render_unit_badge($unit_slug) : '')
            . '<span style="font-size:10px;font-weight:800;color:#0D3B22;background:#E8F0EA;padding:3px 9px;border-radius:9999px;letter-spacing:0.04em;text-transform:uppercase">' . esc_html($cat_info['label']) . '</span>'
            . '</div>'
            . '<span style="font-family:\'IBM Plex Mono\',monospace;font-size:11px;color:#6B7280;font-weight:600">Tahun ' . esc_html($tahun) . '</span>'
            . '</div>'
            . '<h3 style="font-family:\'Fraunces\',Georgia,serif;font-size:15.5px;font-weight:700;color:#111827;line-height:1.35;margin:0 0 6px 0">' . esc_html($judul) . '</h3>'
            . ($nomor !== '' ? '<p style="font-family:\'IBM Plex Mono\',monospace;font-size:11px;color:#0D3B22;font-weight:600;margin:0 0 10px 0">' . esc_html($nomor) . '</p>' : '')
            . ($desc !== '' ? '<p style="font-size:12px;color:#4B5563;line-height:1.6;margin:0 0 14px 0;text-align:justify">' . esc_html($desc) . '</p>' : '')
            . ($pengesah !== '' ? '<div style="font-size:11px;color:#6B7280;margin-bottom:14px;border-top:1px solid #F3F4F6;padding-top:10px"><strong>Pengesah:</strong> ' . esc_html($pengesah) . '</div>' : '')
            . '</div>'
            . '<div style="border-top:1px solid #F3F4F6;padding-top:12px;display:flex;align-items:center;justify-content:space-between">'
            . '<span style="font-size:10.5px;color:#9CA3AF;font-weight:600">PDF &middot; ' . esc_html($size) . '</span>'
            . ($link !== ''
                ? '<a href="' . esc_url($link) . '" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;gap:6px;font-size:11.5px;font-weight:700;color:#0D3B22;background:#E8F0EA;padding:6px 14px;border-radius:9999px;text-decoration:none"><span>Buka Dokumen</span></a>'
                : '<span style="display:inline-flex;align-items:center;font-size:11px;font-weight:600;color:#6B7280;background:#F3F4F6;padding:6px 12px;border-radius:9999px">Segera Diunggah</span>')
            . '</div>'
            . '</div>';
    }
    wp_reset_postdata();

    if (!empty($extra_cats) && strpos($content, 'id="bk-dok-tabs"') !== false) {
        $extra_btns = '';
        foreach ($extra_cats as $kat_slug => $kat_label) {
            if (strpos($content, 'data-kat="' . $kat_slug . '"') === false) {
                $extra_btns .= '<button type="button" class="bk-dok-btn" data-kat="' . esc_attr($kat_slug) . '" style="border:none;background:#FAF8F5;color:#4B5563;font-size:11.5px;font-weight:600;padding:6px 14px;border-radius:9999px;cursor:pointer;font-family:\'Plus Jakarta Sans\',sans-serif;white-space:nowrap;border:1px solid #E5E7EB">' . esc_html($kat_label) . '</button>';
            }
        }
        if ($extra_btns !== '') {
            $content = preg_replace('/(<div\b[^>]*id=(["\'])bk-dok-tabs\2[^>]*>.*?)(<\/div>)/is', '$1' . $extra_btns . '$3', $content, 1);
        }
    }

    // Sisipkan bilah filter Unit/Jenjang tepat sebelum #bk-dok-grid jika belum ada
    if (strpos($content, 'id="ppb-dok-unit-bar"') === false) {
        $unit_opts = function_exists('ppb_get_unit_options') ? ppb_get_unit_options(true, true) : ['jurusan' => 'Jurusan PPB', 's1' => 'S1', 's2' => 'S2', 's3' => 'S3'];
        $unit_bar = '<div id="ppb-dok-unit-bar" style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-bottom:18px;background:#ffffff;border:1px solid #D4E3D8;border-radius:14px;padding:10px 14px;">'
            . '<span style="font-size:11.5px;font-weight:700;color:#0D3B22;">Filter Unit / Jenjang:</span>'
            . '<button type="button" class="ppb-dok-unit-pill active" data-unit="semua" style="border:none;background:#0D3B22;color:#F5B82E;font-size:11.5px;font-weight:700;padding:5px 12px;border-radius:9999px;cursor:pointer;">Semua</button>';
        foreach ($unit_opts as $uslug => $ulabel) {
            $slbl = function_exists('ppb_get_unit_label') ? ppb_get_unit_label($uslug, true) : strtoupper($uslug);
            $unit_bar .= '<button type="button" class="ppb-dok-unit-pill" data-unit="' . esc_attr($uslug) . '" style="border:1px solid #E5E7EB;background:#FAF8F5;color:#4B5563;font-size:11.5px;font-weight:600;padding:5px 12px;border-radius:9999px;cursor:pointer;">' . esc_html($slbl) . '</button>';
        }
        $unit_bar .= '</div>'
            . '<script>(function(){var bar=document.getElementById("ppb-dok-unit-bar");if(!bar||bar.dataset.w)return;bar.dataset.w="1";bar.querySelectorAll(".ppb-dok-unit-pill").forEach(function(b){b.addEventListener("click",function(){bar.querySelectorAll(".ppb-dok-unit-pill").forEach(function(x){x.style.background="#FAF8F5";x.style.color="#4B5563";});b.style.background="#0D3B22";b.style.color="#F5B82E";var u=b.getAttribute("data-unit");document.querySelectorAll("#bk-dok-grid .bk-dok-card-item").forEach(function(c){c.style.display=(u==="semua"||(c.getAttribute("data-unit")||"jurusan")===u)?"flex":"none";});});});})();</script>';
        $content = preg_replace('/(<div\b[^>]*\bid=(["\'])bk-dok-grid\2)/i', $unit_bar . '$1', $content, 1);
    }

    if (function_exists('ppb_replace_div_by_id')) {
        $content = ppb_replace_div_by_id($content, 'bk-dok-grid', $cards_html);
    }

    return $content;
}
}
add_filter('the_content', 'ppb_dokumen_sync_page_content', 12);

// Sinkronkan hierarki Jurusan PPB & Prodi (S1, S2, S3) ke halaman /profil/struktur/ dan /profil/visi-misi/
if (!function_exists('ppb_profil_jurusan_prodi_sync_content')) {
function ppb_profil_jurusan_prodi_sync_content($content) {
    if (!is_string($content) || $content === '' || is_admin()) {
        return $content;
    }
    $slug_path = function_exists('ppb_get_current_page_slug_path') ? ppb_get_current_page_slug_path() : '';
    if (!function_exists('ppb_get_jurusan_info') || !function_exists('ppb_get_prodi_units')) {
        return $content;
    }
    $jur   = ppb_get_jurusan_info();
    $prodi = ppb_get_prodi_units(true);

    // 1. /profil/struktur/ -> Sisipkan panel Tata Kelola Jurusan vs Koordinator Prodi S1/S2/S3
    if (($slug_path === 'profil/struktur' || $slug_path === 'struktur') && strpos($content, 'id="ppb-jurusan-struktur-matrix"') === false) {
        $box = '<div id="ppb-jurusan-struktur-matrix" style="background:#ffffff;border:1.5px solid #D4E3D8;border-radius:20px;padding:24px;margin:0 0 28px;box-shadow:0 6px 20px rgba(13,59,34,0.05);">'
            . '<div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:12px;margin-bottom:18px;border-bottom:1px solid #E5E7EB;padding-bottom:14px;">'
            . '<div><span style="font-size:11px;font-weight:800;color:#059669;text-transform:uppercase;letter-spacing:0.06em;">Tata Kelola Payung Institusi</span>'
            . '<h2 style="font-family:\'Fraunces\',Georgia,serif;font-size:22px;color:#0D3B22;margin:4px 0 0;">Struktur ' . esc_html($jur['nama']) . ' &amp; Program Studi</h2></div>'
            . '<a href="' . esc_url(home_url('/akreditasi/upm/')) . '" style="font-size:12px;font-weight:700;color:#0D3B22;background:#E8F0EA;padding:7px 14px;border-radius:8px;text-decoration:none;">Renstra &amp; UPM Jurusan &rarr;</a>'
            . '</div>'
            . '<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:14px;margin-bottom:18px;">'
            . '<div style="background:#0D3B22;color:#fff;border-radius:14px;padding:16px;"><div style="font-size:10.5px;font-weight:700;color:#F5B82E;text-transform:uppercase;">' . esc_html($jur['pimpinan_jabatan']) . '</div><div style="font-size:15px;font-weight:700;margin:4px 0;">' . esc_html($jur['pimpinan_nama']) . '</div>' . (!empty($jur['pimpinan_nip']) ? '<div style="font-size:11px;opacity:.8;font-family:monospace;">NIP. ' . esc_html($jur['pimpinan_nip']) . '</div>' : '') . '</div>'
            . '<div style="background:#FAF8F5;border:1px solid #D4E3D8;border-radius:14px;padding:16px;"><div style="font-size:10.5px;font-weight:700;color:#059669;text-transform:uppercase;">Sekretaris Jurusan</div><div style="font-size:14.5px;font-weight:700;color:#0D3B22;margin:4px 0;">' . esc_html($jur['sekretaris_nama'] ?: 'Sekretaris Jurusan PPB') . '</div><div style="font-size:11.5px;color:#64748b;">Koordinasi Administrasi &amp; Akademik Lintas Prodi</div></div>'
            . '<div style="background:#FAF8F5;border:1px solid #D4E3D8;border-radius:14px;padding:16px;"><div style="font-size:10.5px;font-weight:700;color:#059669;text-transform:uppercase;">Penjaminan Mutu &amp; Lab</div><div style="font-size:13.5px;font-weight:700;color:#0D3B22;margin:4px 0;">' . esc_html($jur['gpm_ketua']) . '</div><div style="font-size:12px;color:#475569;">' . esc_html($jur['lab_kepala']) . '</div></div>'
            . '</div>'
            . '<div style="font-size:11.5px;font-weight:700;color:#0D3B22;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:10px;">Program Studi di Bawah Jurusan PPB:</div>'
            . '<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));gap:14px;">';

        foreach ($prodi as $pslug => $pmeta) {
            $box .= '<div style="background:#FAF8F5;border:1px solid #D4E3D8;border-radius:14px;padding:15px;">'
                . '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">'
                . '<span style="background:#0D3B22;color:#F5B82E;font-size:10.5px;font-weight:800;padding:2px 8px;border-radius:6px;">' . esc_html($pmeta['kode']) . '</span>'
                . '<span style="font-size:11px;color:#64748b;">' . esc_html($pmeta['fakultas_unit']) . '</span>'
                . '</div>'
                . '<div style="font-family:\'Fraunces\',Georgia,serif;font-size:16px;font-weight:700;color:#0D3B22;margin-bottom:4px;">' . esc_html($pmeta['nama']) . '</div>'
                . '<div style="font-size:12.5px;color:#374151;margin-bottom:10px;"><strong>Pimpinan:</strong> ' . esc_html($pmeta['kaprodi_nama'] ?: ('Koordinator ' . $pmeta['kode'])) . '</div>'
                . '<a href="' . esc_url(home_url('/akreditasi/' . $pslug . '/')) . '" style="font-size:11.5px;font-weight:700;color:#0D3B22;text-decoration:none;">LAMDIK ' . esc_html($pmeta['kode']) . ' &rarr;</a>'
                . '</div>';
        }
        $box .= '</div></div>';

        $content = preg_replace('/(<div\b[^>]*class=(["\'])bk-seg-control\2[^>]*>.*?<\/div>\s*<\/div>)/is', '$1' . $box, $content, 1);
    }

    // 2. /profil/visi-misi/ -> Sisipkan panel Visi Payung Jurusan PPB & Visi Keilmuan per Jenjang (S1, S2, S3)
    if (($slug_path === 'profil/visi-misi' || $slug_path === 'visi-misi') && strpos($content, 'id="ppb-visi-jurusan-prodi"') === false) {
        $misi_lines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) ($jur['misi_payung'] ?? '')))));
        $vbox = '<div id="ppb-visi-jurusan-prodi" style="background:#ffffff;border:1.5px solid #D4E3D8;border-radius:20px;padding:24px;margin:0 0 28px;box-shadow:0 6px 20px rgba(13,59,34,0.05);">'
            . '<div style="background:#0D3B22;color:#fff;border-radius:14px;padding:20px;margin-bottom:18px;">'
            . '<span style="font-size:10.5px;font-weight:800;color:#F5B82E;text-transform:uppercase;letter-spacing:0.06em;">🏛️ Visi &amp; Misi Payung ' . esc_html($jur['nama']) . '</span>'
            . '<p style="font-family:\'Fraunces\',Georgia,serif;font-size:17px;line-height:1.55;margin:8px 0 12px;color:#ffffff;">&ldquo;' . esc_html($jur['visi_payung']) . '&rdquo;</p>';
        if (!empty($misi_lines)) {
            $vbox .= '<ul style="margin:0;padding-left:18px;font-size:12.5px;color:rgba(255,255,255,0.88);line-height:1.6;">';
            foreach ($misi_lines as $ml) {
                $vbox .= '<li>' . esc_html($ml) . '</li>';
            }
            $vbox .= '</ul>';
        }
        $vbox .= '</div>'
            . '<div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:10px;margin-bottom:14px;">'
            . '<strong style="font-size:14px;color:#0D3B22;">Visi Keilmuan &amp; Sasaran Per Jenjang Prodi:</strong>'
            . '<div class="bk-seg-control" id="ppb-visi-deg-tabs">';
        $first_p = true;
        foreach ($prodi as $pslug => $pmeta) {
            $vbox .= '<button type="button" class="bk-seg-btn ' . ($first_p ? 'is-active' : '') . '" data-visi-deg="' . esc_attr($pslug) . '">' . esc_html($pmeta['tab_label'] ?: $pmeta['kode']) . '</button>';
            $first_p = false;
        }
        $vbox .= '</div></div>';
        $first_p = true;
        foreach ($prodi as $pslug => $pmeta) {
            $vbox .= '<div class="ppb-visi-deg-pane" data-visi-pane="' . esc_attr($pslug) . '" style="display:' . ($first_p ? 'block' : 'none') . ';background:#FAF8F5;border:1px solid #D4E3D8;border-radius:14px;padding:18px;">'
                . '<div style="display:flex;flex-wrap:wrap;justify-content:space-between;gap:8px;margin-bottom:8px;">'
                . '<h3 style="font-family:\'Fraunces\',Georgia,serif;font-size:18px;color:#0D3B22;margin:0;">' . esc_html($pmeta['nama']) . '</h3>'
                . '<span style="font-size:11.5px;font-weight:700;color:#059669;">' . esc_html($pmeta['kkni'] . ($pmeta['gelar'] ? ' · ' . $pmeta['gelar'] : '')) . '</span>'
                . '</div>'
                . '<p style="font-size:13px;color:#374151;margin:0 0 8px;line-height:1.6;"><strong>Visi Keilmuan:</strong> ' . esc_html($pmeta['visi_keilmuan'] ?? '') . '</p>'
                . '<p style="font-size:12.5px;color:#4B5563;margin:0 0 12px;line-height:1.55;"><strong>Sasaran Lulusan:</strong> ' . esc_html($pmeta['sasaran'] ?? '') . '</p>'
                . '<div style="display:flex;flex-wrap:wrap;gap:8px;">'
                . '<a href="' . esc_url(home_url('/akreditasi/' . $pslug . '/')) . '" style="font-size:12px;font-weight:700;background:#0D3B22;color:#F5B82E;padding:6px 13px;border-radius:8px;text-decoration:none;">9 Kriteria LAMDIK ' . esc_html($pmeta['kode']) . ' &rarr;</a>'
                . '<a href="' . esc_url(home_url('/akademik/kurikulum/?jenjang=' . $pslug)) . '" style="font-size:12px;font-weight:600;background:#fff;color:#0D3B22;border:1px solid #D4E3D8;padding:6px 13px;border-radius:8px;text-decoration:none;">Kurikulum ' . esc_html($pmeta['kode']) . ' &rarr;</a>'
                . '</div></div>';
            $first_p = false;
        }
        $vbox .= '<script>(function(){var t=document.getElementById("ppb-visi-deg-tabs");if(!t||t.dataset.w)return;t.dataset.w="1";t.querySelectorAll("[data-visi-deg]").forEach(function(b){b.addEventListener("click",function(){var d=b.getAttribute("data-visi-deg");t.querySelectorAll("[data-visi-deg]").forEach(function(x){x.classList.toggle("is-active",x===b);});document.querySelectorAll(".ppb-visi-deg-pane").forEach(function(p){p.style.display=(p.getAttribute("data-visi-pane")===d)?"block":"none";});});});})();</script>'
            . '</div>';

        $content = preg_replace('/(<div\b[^>]*class=(["\'])bk-seg-control\2[^>]*>.*?<\/div>\s*<\/div>)/is', '$1' . $vbox, $content, 1);
    }

    return $content;
}
}
add_filter('the_content', 'ppb_profil_jurusan_prodi_sync_content', 12);

// Pastikan Shortcode diproses di dalam widget HTML Elementor
add_filter('elementor/widget/render_content', function($content, $widget) {
    if ($widget->get_name() === 'html') {
        return do_shortcode($content);
    }
    return $content;
}, 10, 2);

