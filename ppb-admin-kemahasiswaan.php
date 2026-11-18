<?php
/**
 * Module: PPB Admin Kemahasiswaan & Prestasi/Karya Suite
 * Description: Modul WP-Admin khusus untuk mengelola Prestasi Mahasiswa (CPT), Karya & Inovasi Mahasiswa (CPT), Pengaturan Tracer Study & Statistik, serta Shortcode penayangan dengan Scholaro Design System.
 * Version: 1.0.0
 * Author: Jurusan PPB FIP UNM
 */

if (!defined('ABSPATH')) exit;

/* ========================================================================== */
/* 1. REGISTRASI CPT: PPB_PRESTASI (Prestasi Mahasiswa)                       */
/* ========================================================================== */
if (!function_exists('ppb_register_cpt_prestasi')) {
function ppb_register_cpt_prestasi() {
    $labels = [
        'name'               => 'Prestasi Mahasiswa',
        'singular_name'      => 'Prestasi Mahasiswa',
        'menu_name'          => 'Prestasi Mahasiswa',
        'name_admin_bar'     => 'Prestasi Mahasiswa',
        'add_new'            => 'Tambah Prestasi Baru',
        'add_new_item'       => 'Tambah Prestasi Baru',
        'new_item'           => 'Prestasi Baru',
        'edit_item'          => 'Edit Prestasi',
        'view_item'          => 'Lihat Prestasi',
        'all_items'          => 'Semua Prestasi',
        'search_items'       => 'Cari Prestasi',
        'not_found'          => 'Belum ada data prestasi mahasiswa.',
        'not_found_in_trash' => 'Tidak ada prestasi di kotak sampah.'
    ];

    $args = [
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => false,
        'query_var'          => true,
        'rewrite'            => ['slug' => 'prestasi-mahasiswa'],
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 24,
        'menu_icon'          => 'dashicons-awards',
        'supports'           => ['title'],
        'show_in_rest'       => false,
    ];

    register_post_type('ppb_prestasi', $args);
}
}
add_action('init', 'ppb_register_cpt_prestasi');

/* ========================================================================== */
/* 2. REGISTRASI CPT: PPB_KARYA (Karya & Inovasi Mahasiswa)                   */
/* ========================================================================== */
if (!function_exists('ppb_register_cpt_karya')) {
function ppb_register_cpt_karya() {
    $labels = [
        'name'               => 'Karya & Inovasi',
        'singular_name'      => 'Karya Mahasiswa',
        'menu_name'          => 'Karya Mahasiswa',
        'name_admin_bar'     => 'Karya Mahasiswa',
        'add_new'            => 'Tambah Karya Baru',
        'add_new_item'       => 'Tambah Karya Baru',
        'new_item'           => 'Karya Baru',
        'edit_item'          => 'Edit Karya',
        'view_item'          => 'Lihat Karya',
        'all_items'          => 'Semua Karya & Inovasi',
        'search_items'       => 'Cari Karya',
        'not_found'          => 'Belum ada data karya mahasiswa.',
        'not_found_in_trash' => 'Tidak ada karya di kotak sampah.'
    ];

    $args = [
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => false,
        'query_var'          => true,
        'rewrite'            => ['slug' => 'karya-mahasiswa'],
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 25,
        'menu_icon'          => 'dashicons-portfolio',
        'supports'           => ['title'],
        'show_in_rest'       => false,
    ];

    register_post_type('ppb_karya', $args);
}
}
add_action('init', 'ppb_register_cpt_karya');

/* ========================================================================== */
/* 3. META BOXES: PRESTASI MAHASISWA                                          */
/* ========================================================================== */
if (!function_exists('ppb_prestasi_add_meta_boxes')) {
function ppb_prestasi_add_meta_boxes() {
    add_meta_box(
        'ppb_prestasi_details',
        '🏆 Rincian Prestasi & Capaian Mahasiswa',
        'ppb_prestasi_render_meta_box',
        'ppb_prestasi',
        'normal',
        'high'
    );
}
}
add_action('add_meta_boxes', 'ppb_prestasi_add_meta_boxes');

if (!function_exists('ppb_prestasi_render_meta_box')) {
function ppb_prestasi_render_meta_box($post) {
    wp_nonce_field('ppb_prestasi_save_nonce', 'ppb_prestasi_nonce');

    $nama_mhs       = get_post_meta($post->ID, '_ppb_nama_mahasiswa', true);
    $nim            = get_post_meta($post->ID, '_ppb_nim', true);
    $angkatan       = get_post_meta($post->ID, '_ppb_angkatan', true);
    $capaian        = get_post_meta($post->ID, '_ppb_capaian', true);
    $nama_kegiatan  = get_post_meta($post->ID, '_ppb_nama_kegiatan', true);
    $penyelenggara  = get_post_meta($post->ID, '_ppb_penyelenggara', true);
    $tingkat        = get_post_meta($post->ID, '_ppb_tingkat', true) ?: 'nasional';
    $tahun          = get_post_meta($post->ID, '_ppb_tahun', true) ?: date('Y');
    $pembimbing     = get_post_meta($post->ID, '_ppb_dosen_pembimbing', true);
    $deskripsi      = get_post_meta($post->ID, '_ppb_deskripsi', true);
    $drive_sertif   = get_post_meta($post->ID, '_ppb_drive_sertifikat_link', true);
    $drive_foto     = get_post_meta($post->ID, '_ppb_drive_foto_link', true);
    $is_featured    = get_post_meta($post->ID, '_ppb_is_featured', true);
    ?>
    <div style="font-family:'Segoe UI',sans-serif;max-width:900px;padding:12px 0;">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:14px;">
            <div>
                <label style="font-weight:700;display:block;margin-bottom:5px;color:#0D3B22;">Nama Mahasiswa / Tim *</label>
                <input type="text" name="ppb_nama_mahasiswa" value="<?php echo esc_attr($nama_mhs); ?>" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;" placeholder="Contoh: Nurul Fadillah Ramadhani" required />
            </div>
            <div>
                <label style="font-weight:700;display:block;margin-bottom:5px;color:#0D3B22;">NIM & Angkatan</label>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                    <input type="text" name="ppb_nim" value="<?php echo esc_attr($nim); ?>" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;" placeholder="220404501024" />
                    <input type="number" name="ppb_angkatan" value="<?php echo esc_attr($angkatan); ?>" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;" placeholder="2022" />
                </div>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr 120px;gap:18px;margin-bottom:14px;">
            <div>
                <label style="font-weight:700;display:block;margin-bottom:5px;color:#0D3B22;">Capaian / Juara *</label>
                <input type="text" name="ppb_capaian" value="<?php echo esc_attr($capaian); ?>" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;" placeholder="Contoh: Juara 1 / Gold Medal" required />
            </div>
            <div>
                <label style="font-weight:700;display:block;margin-bottom:5px;color:#0D3B22;">Tingkat Kejuaraan *</label>
                <select name="ppb_tingkat" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;font-weight:600;">
                    <option value="internasional" <?php selected($tingkat, 'internasional'); ?>>🌍 Internasional</option>
                    <option value="nasional" <?php selected($tingkat, 'nasional'); ?>>🇮🇩 Nasional</option>
                    <option value="wilayah" <?php selected($tingkat, 'wilayah'); ?>>Wilayah / Regional</option>
                </select>
            </div>
            <div>
                <label style="font-weight:700;display:block;margin-bottom:5px;color:#0D3B22;">Tahun *</label>
                <input type="number" name="ppb_tahun" value="<?php echo esc_attr($tahun); ?>" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;" placeholder="2025" required />
            </div>
        </div>

        <div style="margin-bottom:14px;">
            <label style="font-weight:700;display:block;margin-bottom:5px;color:#0D3B22;">Nama Kegiatan / Lomba *</label>
            <input type="text" name="ppb_nama_kegiatan" value="<?php echo esc_attr($nama_kegiatan); ?>" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;" placeholder="Contoh: Pekan Ilmiah Mahasiswa Nasional (PIMNAS) ke-38 Bidang PKM-RSH" required />
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:14px;">
            <div>
                <label style="font-weight:700;display:block;margin-bottom:5px;color:#0D3B22;">Lembaga Penyelenggara</label>
                <input type="text" name="ppb_penyelenggara" value="<?php echo esc_attr($penyelenggara); ?>" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;" placeholder="Contoh: Belmawa Kemdiktisaintek RI" />
            </div>
            <div>
                <label style="font-weight:700;display:block;margin-bottom:5px;color:#0D3B22;">Dosen Pembimbing</label>
                <input type="text" name="ppb_dosen_pembimbing" value="<?php echo esc_attr($pembimbing); ?>" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;" placeholder="Contoh: Prof. Dr. Farida Aryani, M.Pd." />
            </div>
        </div>

        <div style="margin-bottom:14px;">
            <label style="font-weight:700;display:block;margin-bottom:5px;color:#0D3B22;">Deskripsi Capaian / Ringkasan Karya</label>
            <textarea name="ppb_deskripsi" rows="3" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;" placeholder="Jelaskan ringkasan karya ilmiah, prototipe, atau hasil kompetisi yang diraih..."><?php echo esc_textarea($deskripsi); ?></textarea>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:14px;">
            <div style="background:#FAF8F5;border:1px solid #e2e8f0;border-radius:12px;padding:12px;">
                <label for="ppb_drive_sertifikat_link" style="font-weight:700;display:block;margin-bottom:5px;color:#0D3B22;">🔗 Tautan Sertifikat (Google Drive / Upload WP Media)</label>
                <?php if (function_exists('ppb_render_media_url_control')) : ?>
                    <?php ppb_render_media_url_control([
                        'id'          => 'ppb_drive_sertifikat_link',
                        'name'        => 'ppb_drive_sertifikat_link',
                        'value'       => $drive_sertif,
                        'placeholder' => 'https://drive.google.com/file/d/... atau upload PDF/gambar',
                        'title'       => 'Pilih / Upload Sertifikat Prestasi',
                        'btn_label'   => '📁 Upload / Pilih File',
                    ]); ?>
                <?php else : ?>
                    <input type="url" id="ppb_drive_sertifikat_link" name="ppb_drive_sertifikat_link" value="<?php echo esc_attr($drive_sertif); ?>" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;" placeholder="https://drive.google.com/file/d/..." />
                <?php endif; ?>
                <span style="font-size:11px;color:#64748B;">Tombol 'Lihat Sertifikat' di kartu akan membuka link ini.</span>
            </div>
            <div style="background:#FAF8F5;border:1px solid #e2e8f0;border-radius:12px;padding:12px;">
                <label for="ppb_drive_foto_link" style="font-weight:700;display:block;margin-bottom:5px;color:#0D3B22;">📸 Tautan Dokumentasi / Foto Piala (Drive / Upload Media)</label>
                <?php if (function_exists('ppb_render_media_url_control')) : ?>
                    <?php ppb_render_media_url_control([
                        'id'          => 'ppb_drive_foto_link',
                        'name'        => 'ppb_drive_foto_link',
                        'value'       => $drive_foto,
                        'placeholder' => 'https://drive.google.com/file/d/... atau upload foto',
                        'title'       => 'Pilih / Upload Foto Dokumentasi',
                        'btn_label'   => '📷 Upload / Pilih Foto',
                        'library'     => 'image',
                    ]); ?>
                <?php else : ?>
                    <input type="url" id="ppb_drive_foto_link" name="ppb_drive_foto_link" value="<?php echo esc_attr($drive_foto); ?>" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;" placeholder="https://drive.google.com/file/d/..." />
                <?php endif; ?>
                <span style="font-size:11px;color:#64748B;">Tombol 'Foto' di kartu akan membuka link ini.</span>
            </div>
        </div>

        <div>
            <label style="display:inline-flex;align-items:center;gap:8px;cursor:pointer;font-weight:600;color:#0D3B22;">
                <input type="checkbox" name="ppb_is_featured" value="1" <?php checked($is_featured, '1'); ?> />
                Tandai sebagai Prestasi Unggulan (Featured)
            </label>
        </div>
    </div>
    <?php
}
}

if (!function_exists('ppb_prestasi_save_meta_data')) {
function ppb_prestasi_save_meta_data($post_id) {
    if (!isset($_POST['ppb_prestasi_nonce']) || !wp_verify_nonce($_POST['ppb_prestasi_nonce'], 'ppb_prestasi_save_nonce')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $fields = [
        'ppb_nama_mahasiswa'        => '_ppb_nama_mahasiswa',
        'ppb_nim'                   => '_ppb_nim',
        'ppb_angkatan'              => '_ppb_angkatan',
        'ppb_capaian'               => '_ppb_capaian',
        'ppb_nama_kegiatan'         => '_ppb_nama_kegiatan',
        'ppb_penyelenggara'         => '_ppb_penyelenggara',
        'ppb_tingkat'               => '_ppb_tingkat',
        'ppb_tahun'                 => '_ppb_tahun',
        'ppb_dosen_pembimbing'      => '_ppb_dosen_pembimbing',
        'ppb_deskripsi'             => '_ppb_deskripsi',
        'ppb_drive_sertifikat_link' => '_ppb_drive_sertifikat_link',
        'ppb_drive_foto_link'       => '_ppb_drive_foto_link',
    ];

    foreach ($fields as $key => $meta_key) {
        if (isset($_POST[$key])) {
            $raw = wp_unslash($_POST[$key]);
            if (in_array($key, ['ppb_drive_sertifikat_link', 'ppb_drive_foto_link'], true)) {
                $val = function_exists('ppb_clean_document_url') ? ppb_clean_document_url($raw) : esc_url_raw($raw);
            } elseif ($key === 'ppb_deskripsi') {
                $val = sanitize_textarea_field($raw);
            } else {
                $val = sanitize_text_field($raw);
            }
            update_post_meta($post_id, $meta_key, $val);
        }
    }

    $featured = isset($_POST['ppb_is_featured']) ? '1' : '0';
    update_post_meta($post_id, '_ppb_is_featured', $featured);

    // Auto title update
    if (!empty($_POST['ppb_capaian']) && !empty($_POST['ppb_nama_mahasiswa'])) {
        $title = sanitize_text_field($_POST['ppb_capaian']) . ' - ' . sanitize_text_field($_POST['ppb_nama_mahasiswa']);
        remove_action('save_post_ppb_prestasi', 'ppb_prestasi_save_meta_data');
        wp_update_post(['ID' => $post_id, 'post_title' => $title]);
        add_action('save_post_ppb_prestasi', 'ppb_prestasi_save_meta_data');
    }

    // Invalidate Elementor caches
    delete_post_meta(32, '_elementor_element_cache');
    if (class_exists('\\Elementor\\Plugin')) {
        \Elementor\Plugin::$instance->files_manager->clear_cache();
    }
}
}
add_action('save_post_ppb_prestasi', 'ppb_prestasi_save_meta_data');

// Columns for Prestasi Admin Table
if (!function_exists('ppb_prestasi_custom_columns')) {
function ppb_prestasi_custom_columns($cols) {
    return [
        'cb'            => $cols['cb'],
        'title'         => 'Prestasi & Capaian',
        'mahasiswa'     => 'Mahasiswa & NIM',
        'tingkat'       => 'Tingkat',
        'tahun'         => 'Tahun',
        'penyelenggara' => 'Penyelenggara & Pembimbing',
        'dokumen'       => 'Berkas Drive',
    ];
}
}
add_filter('manage_ppb_prestasi_posts_columns', 'ppb_prestasi_custom_columns');

if (!function_exists('ppb_prestasi_render_custom_columns')) {
function ppb_prestasi_render_custom_columns($col, $post_id) {
    switch ($col) {
        case 'mahasiswa':
            $nama = get_post_meta($post_id, '_ppb_nama_mahasiswa', true) ?: '-';
            $nim = get_post_meta($post_id, '_ppb_nim', true);
            $angkatan = get_post_meta($post_id, '_ppb_angkatan', true);
            echo '<strong>' . esc_html($nama) . '</strong><br>';
            echo '<span style="color:#64748B;font-size:11px;">' . esc_html($nim ? 'NIM ' . $nim : '') . ($angkatan ? ' · ' . $angkatan : '') . '</span>';
            break;
        case 'tingkat':
            $t = get_post_meta($post_id, '_ppb_tingkat', true);
            if ($t === 'internasional') {
                echo '<span style="display:inline-block;padding:2px 8px;border-radius:6px;font-size:11px;font-weight:700;background:#ede9fe;color:#5b21b6;border:1px solid #c4b5fd;">🌍 Internasional</span>';
            } elseif ($t === 'nasional') {
                echo '<span style="display:inline-block;padding:2px 8px;border-radius:6px;font-size:11px;font-weight:700;background:#dcfce7;color:#166534;border:1px solid #86efac;">🇮🇩 Nasional</span>';
            } else {
                echo '<span style="display:inline-block;padding:2px 8px;border-radius:6px;font-size:11px;font-weight:700;background:#f1f5f9;color:#334155;border:1px solid #cbd5e1;">Wilayah</span>';
            }
            break;
        case 'tahun':
            echo '<strong>' . esc_html(get_post_meta($post_id, '_ppb_tahun', true) ?: '-') . '</strong>';
            break;
        case 'penyelenggara':
            $peny = get_post_meta($post_id, '_ppb_penyelenggara', true) ?: '-';
            $pemb = get_post_meta($post_id, '_ppb_dosen_pembimbing', true);
            echo esc_html($peny);
            if ($pemb) echo '<br><span style="font-size:11px;color:#64748B;">Pembimbing: ' . esc_html($pemb) . '</span>';
            break;
        case 'dokumen':
            $sertif = get_post_meta($post_id, '_ppb_drive_sertifikat_link', true);
            $foto = get_post_meta($post_id, '_ppb_drive_foto_link', true);
            if ($sertif) echo '<a href="' . esc_url($sertif) . '" target="_blank" class="button button-small" style="margin-right:4px;">📜 Sertifikat</a>';
            if ($foto) echo '<a href="' . esc_url($foto) . '" target="_blank" class="button button-small">📸 Foto</a>';
            if (!$sertif && !$foto) echo '<span style="color:#94a3b8;">-</span>';
            break;
    }
}
}
add_action('manage_ppb_prestasi_posts_custom_column', 'ppb_prestasi_render_custom_columns', 10, 2);

/* ========================================================================== */
/* 4. META BOXES: KARYA & INOVASI MAHASISWA                                   */
/* ========================================================================== */
if (!function_exists('ppb_karya_add_meta_boxes')) {
function ppb_karya_add_meta_boxes() {
    add_meta_box(
        'ppb_karya_details',
        '💡 Rincian Produk Karya & Inovasi Mahasiswa',
        'ppb_karya_render_meta_box',
        'ppb_karya',
        'normal',
        'high'
    );
}
}
add_action('add_meta_boxes', 'ppb_karya_add_meta_boxes');

if (!function_exists('ppb_karya_render_meta_box')) {
function ppb_karya_render_meta_box($post) {
    wp_nonce_field('ppb_karya_save_nonce', 'ppb_karya_nonce');

    $judul       = get_post_meta($post->ID, '_ppb_karya_judul', true) ?: $post->post_title;
    $kategori    = get_post_meta($post->ID, '_ppb_kategori', true) ?: 'media_bimbingan';
    $pembuat     = get_post_meta($post->ID, '_ppb_pembuat', true);
    $nim_ketua   = get_post_meta($post->ID, '_ppb_nim_ketua', true);
    $angkatan    = get_post_meta($post->ID, '_ppb_angkatan', true);
    $pembimbing  = get_post_meta($post->ID, '_ppb_dosen_pembimbing', true);
    $matkul      = get_post_meta($post->ID, '_ppb_mata_kuliah', true);
    $tahun       = get_post_meta($post->ID, '_ppb_tahun', true) ?: date('Y');
    $badge       = get_post_meta($post->ID, '_ppb_badge_unggulan', true);
    $manfaat     = get_post_meta($post->ID, '_ppb_manfaat', true);
    $deskripsi   = get_post_meta($post->ID, '_ppb_deskripsi', true);
    $foto_link   = get_post_meta($post->ID, '_ppb_foto_karya_link', true);
    $drive_link  = get_post_meta($post->ID, '_ppb_drive_file_link', true);
    ?>
    <div style="font-family:'Segoe UI',sans-serif;max-width:900px;padding:12px 0;">
        <div style="margin-bottom:14px;">
            <label style="font-weight:700;display:block;margin-bottom:5px;color:#0D3B22;">Judul Produk Karya / Inovasi *</label>
            <input type="text" name="ppb_karya_judul" value="<?php echo esc_attr($judul); ?>" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;" placeholder="Contoh: Papan Permainan Konseling Karier: Karir-Pedia Nusantara" required />
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr 140px;gap:18px;margin-bottom:14px;">
            <div>
                <label style="font-weight:700;display:block;margin-bottom:5px;color:#0D3B22;">Kategori Produk *</label>
                <select name="ppb_kategori" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;font-weight:600;">
                    <option value="media_bimbingan" <?php selected($kategori, 'media_bimbingan'); ?>>🎲 Media Bimbingan</option>
                    <option value="modul_psikoedukasi" <?php selected($kategori, 'modul_psikoedukasi'); ?>>📘 Modul Psikoedukasi</option>
                    <option value="instrumen_asesmen" <?php selected($kategori, 'instrumen_asesmen'); ?>>📊 Instrumen Asesmen</option>
                    <option value="aplikasi_digital" <?php selected($kategori, 'aplikasi_digital'); ?>>📱 Aplikasi Digital</option>
                    <option value="video_podcast" <?php selected($kategori, 'video_podcast'); ?>>🎙️ Video & Podcast</option>
                </select>
            </div>
            <div>
                <label style="font-weight:700;display:block;margin-bottom:5px;color:#0D3B22;">Badge Unggulan (Opsional)</label>
                <input type="text" name="ppb_badge_unggulan" value="<?php echo esc_attr($badge); ?>" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;" placeholder="Contoh: Terdaftar Hak Cipta HKI" />
            </div>
            <div>
                <label style="font-weight:700;display:block;margin-bottom:5px;color:#0D3B22;">Tahun *</label>
                <input type="number" name="ppb_tahun" value="<?php echo esc_attr($tahun); ?>" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;" placeholder="2025" required />
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:14px;">
            <div>
                <label style="font-weight:700;display:block;margin-bottom:5px;color:#0D3B22;">Nama Pembuat / Tim Mahasiswa *</label>
                <input type="text" name="ppb_pembuat" value="<?php echo esc_attr($pembuat); ?>" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;" placeholder="Pisahkan koma jika tim: Nurul, Fathur, Andi" required />
            </div>
            <div>
                <label style="font-weight:700;display:block;margin-bottom:5px;color:#0D3B22;">NIM Ketua & Angkatan</label>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                    <input type="text" name="ppb_nim_ketua" value="<?php echo esc_attr($nim_ketua); ?>" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;" placeholder="220401501012" />
                    <input type="number" name="ppb_angkatan" value="<?php echo esc_attr($angkatan); ?>" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;" placeholder="2022" />
                </div>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:14px;">
            <div>
                <label style="font-weight:700;display:block;margin-bottom:5px;color:#0D3B22;">Dosen Pembimbing</label>
                <input type="text" name="ppb_dosen_pembimbing" value="<?php echo esc_attr($pembimbing); ?>" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;" placeholder="Contoh: Dr. Suciani Latif, M.Pd." />
            </div>
            <div>
                <label style="font-weight:700;display:block;margin-bottom:5px;color:#0D3B22;">Mata Kuliah Terkait</label>
                <input type="text" name="ppb_mata_kuliah" value="<?php echo esc_attr($matkul); ?>" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;" placeholder="Contoh: Praktik Pengembangan Media BK" />
            </div>
        </div>

        <div style="margin-bottom:14px;">
            <label style="font-weight:700;display:block;margin-bottom:5px;color:#0D3B22;">Deskripsi Produk Karya</label>
            <textarea name="ppb_deskripsi" rows="3" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;" placeholder="Jelaskan konsep, fungsi media/modul, dan keunggulan inovasi produk..."><?php echo esc_textarea($deskripsi); ?></textarea>
        </div>

        <div style="margin-bottom:14px;">
            <label style="font-weight:700;display:block;margin-bottom:5px;color:#0D3B22;">Manfaat, Penerapan & Dampak</label>
            <textarea name="ppb_manfaat" rows="2" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;" placeholder="Contoh: Telah diujicobakan pada 4 SMA mitra di Makassar dan mencatatkan peningkatan pemahaman karier 43%..."><?php echo esc_textarea($manfaat); ?></textarea>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:14px;">
            <div style="background:#FAF8F5;border:1px solid #e2e8f0;border-radius:12px;padding:12px;">
                <label for="ppb_foto_karya_link" style="font-weight:700;display:block;margin-bottom:5px;color:#0D3B22;">🖼️ Foto / Cover Produk (Upload Media / URL)</label>
                <?php if (function_exists('ppb_render_media_url_control')) : ?>
                    <?php ppb_render_media_url_control([
                        'id'             => 'ppb_foto_karya_link',
                        'name'           => 'ppb_foto_karya_link',
                        'value'          => $foto_link,
                        'placeholder'    => 'https://... atau upload gambar WP Media',
                        'title'          => 'Pilih / Upload Foto Cover Produk',
                        'btn_label'      => '📷 Upload / Pilih Foto',
                        'library'        => 'image',
                        'allow_relative' => true,
                    ]); ?>
                <?php else : ?>
                    <input type="text" id="ppb_foto_karya_link" name="ppb_foto_karya_link" value="<?php echo esc_attr($foto_link); ?>" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;" placeholder="https://..." />
                <?php endif; ?>
                <span style="font-size:11px;color:#64748B;">Foto dokumentasi produk atau cover depan modul/media.</span>
            </div>
            <div style="background:#FAF8F5;border:1px solid #e2e8f0;border-radius:12px;padding:12px;">
                <label for="ppb_drive_file_link" style="font-weight:700;display:block;margin-bottom:5px;color:#0D3B22;">📁 Tautan Berkas / Buku / Aplikasi (Drive / Upload WP Media)</label>
                <?php if (function_exists('ppb_render_media_url_control')) : ?>
                    <?php ppb_render_media_url_control([
                        'id'          => 'ppb_drive_file_link',
                        'name'        => 'ppb_drive_file_link',
                        'value'       => $drive_link,
                        'placeholder' => 'https://drive.google.com/file/d/... atau upload PDF',
                        'title'       => 'Pilih / Upload Berkas Karya Inovasi',
                        'btn_label'   => '📁 Upload / Pilih File',
                    ]); ?>
                <?php else : ?>
                    <input type="url" id="ppb_drive_file_link" name="ppb_drive_file_link" value="<?php echo esc_attr($drive_link); ?>" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;" placeholder="https://drive.google.com/file/d/..." />
                <?php endif; ?>
                <span style="font-size:11px;color:#64748B;">Tombol 'Buka Berkas & Detail Produk' akan membuka link ini.</span>
            </div>
        </div>
    </div>
    <?php
}
}

if (!function_exists('ppb_karya_save_meta_data')) {
function ppb_karya_save_meta_data($post_id) {
    if (!isset($_POST['ppb_karya_nonce']) || !wp_verify_nonce($_POST['ppb_karya_nonce'], 'ppb_karya_save_nonce')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $fields = [
        'ppb_karya_judul'       => '_ppb_karya_judul',
        'ppb_kategori'          => '_ppb_kategori',
        'ppb_pembuat'           => '_ppb_pembuat',
        'ppb_nim_ketua'         => '_ppb_nim_ketua',
        'ppb_angkatan'          => '_ppb_angkatan',
        'ppb_dosen_pembimbing'  => '_ppb_dosen_pembimbing',
        'ppb_mata_kuliah'       => '_ppb_mata_kuliah',
        'ppb_tahun'             => '_ppb_tahun',
        'ppb_badge_unggulan'    => '_ppb_badge_unggulan',
        'ppb_manfaat'           => '_ppb_manfaat',
        'ppb_deskripsi'         => '_ppb_deskripsi',
        'ppb_foto_karya_link'   => '_ppb_foto_karya_link',
        'ppb_drive_file_link'   => '_ppb_drive_file_link',
    ];

    foreach ($fields as $key => $meta_key) {
        if (isset($_POST[$key])) {
            $raw = wp_unslash($_POST[$key]);
            if ($key === 'ppb_foto_karya_link') {
                if (function_exists('bk_sanitize_asset_or_url')) {
                    $val = bk_sanitize_asset_or_url($raw);
                } elseif (function_exists('ppb_clean_document_url')) {
                    $val = ppb_clean_document_url($raw);
                } else {
                    $val = esc_url_raw($raw);
                }
            } elseif ($key === 'ppb_drive_file_link') {
                $val = function_exists('ppb_clean_document_url') ? ppb_clean_document_url($raw) : esc_url_raw($raw);
            } elseif (in_array($key, ['ppb_deskripsi', 'ppb_manfaat'], true)) {
                $val = sanitize_textarea_field($raw);
            } else {
                $val = sanitize_text_field($raw);
            }
            update_post_meta($post_id, $meta_key, $val);
        }
    }

    // Auto title update
    if (!empty($_POST['ppb_karya_judul'])) {
        $title = sanitize_text_field($_POST['ppb_karya_judul']);
        remove_action('save_post_ppb_karya', 'ppb_karya_save_meta_data');
        wp_update_post(['ID' => $post_id, 'post_title' => $title]);
        add_action('save_post_ppb_karya', 'ppb_karya_save_meta_data');
    }

    // Invalidate Elementor caches
    delete_post_meta(33, '_elementor_element_cache');
    if (class_exists('\\Elementor\\Plugin')) {
        \Elementor\Plugin::$instance->files_manager->clear_cache();
    }
}
}
add_action('save_post_ppb_karya', 'ppb_karya_save_meta_data');

// Columns for Karya Admin Table
if (!function_exists('ppb_karya_custom_columns')) {
function ppb_karya_custom_columns($cols) {
    return [
        'cb'         => $cols['cb'],
        'title'      => 'Judul Karya / Inovasi',
        'kategori'   => 'Kategori',
        'pembuat'    => 'Pencipta / Tim',
        'pembimbing' => 'Pembimbing & Mata Kuliah',
        'tahun'      => 'Tahun',
        'drive'      => 'Berkas Drive',
    ];
}
}
add_filter('manage_ppb_karya_posts_columns', 'ppb_karya_custom_columns');

if (!function_exists('ppb_karya_render_custom_columns')) {
function ppb_karya_render_custom_columns($col, $post_id) {
    switch ($col) {
        case 'kategori':
            $kat = get_post_meta($post_id, '_ppb_kategori', true);
            $labels = [
                'media_bimbingan'   => '🎲 Media Bimbingan',
                'modul_psikoedukasi' => '📘 Modul Psikoedukasi',
                'instrumen_asesmen' => '📊 Instrumen Asesmen',
                'aplikasi_digital'  => '📱 Aplikasi Digital',
                'video_podcast'     => '🎙️ Video & Podcast',
            ];
            echo '<span style="display:inline-block;padding:2px 8px;border-radius:6px;font-size:11px;font-weight:700;background:#E8F0EA;color:#0D3B22;">' . esc_html($labels[$kat] ?? $kat) . '</span>';
            break;
        case 'pembuat':
            echo esc_html(get_post_meta($post_id, '_ppb_pembuat', true) ?: '-');
            break;
        case 'pembimbing':
            $p = get_post_meta($post_id, '_ppb_dosen_pembimbing', true) ?: '-';
            $mk = get_post_meta($post_id, '_ppb_mata_kuliah', true);
            echo esc_html($p);
            if ($mk) echo '<br><span style="font-size:11px;color:#64748B;">' . esc_html($mk) . '</span>';
            break;
        case 'tahun':
            echo '<strong>' . esc_html(get_post_meta($post_id, '_ppb_tahun', true) ?: '-') . '</strong>';
            break;
        case 'drive':
            $l = get_post_meta($post_id, '_ppb_drive_file_link', true);
            if ($l) echo '<a href="' . esc_url($l) . '" target="_blank" class="button button-small">📂 Berkas</a>';
            else echo '<span style="color:#94a3b8;">-</span>';
            break;
    }
}
}
add_action('manage_ppb_karya_posts_custom_column', 'ppb_karya_render_custom_columns', 10, 2);

/* ========================================================================== */
/* 5. SUBMENU SETTINGS: PENGATURAN TRACER STUDY & STATISTIK KEMAHASISWAAN     */
/* ========================================================================== */
if (!function_exists('ppb_kemahasiswaan_admin_menu')) {
function ppb_kemahasiswaan_admin_menu() {
    add_submenu_page(
        'edit.php?post_type=ppb_prestasi',
        'Pengaturan Tracer Study & Statistik',
        '📊 Pengaturan Tracer & Data',
        'manage_options',
        'ppb-kemahasiswaan-settings',
        'ppb_render_kemahasiswaan_settings_page'
    );
}
}
// Handled by ppb-portal-hub
// add_action('admin_menu', 'ppb_kemahasiswaan_admin_menu');

if (!function_exists('ppb_get_maba_statistik_defaults')) {
function ppb_get_maba_statistik_defaults() {
    return [
        'portal_url' => 'https://penerimaan.unm.ac.id/',
        'unm_url'    => 'https://unm.ac.id/',
        'jalur'      => [
            [
                'badge'     => 'Jalur Prestasi',
                'tema'      => 'green',
                'judul'     => 'SNBP (Prestasi Rapor & Portofolio)',
                'deskripsi' => 'Seleksi berbasis nilai akademik rapor semester 1–5 dan portofolio prestasi non-akademik siswa SMA/SMK/MA sederajat.',
            ],
            [
                'badge'     => 'Jalur Tes Nasional',
                'tema'      => 'gold',
                'judul'     => 'SNBT (Ujian Tertulis Berbasis Komputer)',
                'deskripsi' => 'Seleksi nasional terpusat mengukur Tes Potensi Skolastik (TPS), Literasi Bahasa Indonesia & Inggris, dan Penalaran Matematika.',
            ],
            [
                'badge'     => 'Jalur Mandiri',
                'tema'      => 'blue',
                'judul'     => 'Jalur Mandiri Universitas Negeri Makassar',
                'deskripsi' => 'Penerimaan seleksi mandiri UNM mencakup Jalur Ujian Tulis Mandiri, Jalur Prestasi Khusus, dan Afirmasi Daerah 3T.',
            ],
        ],
        'tren' => [
            ['tahun' => '2024', 'daya_tampung' => 120, 'peminat_snbp' => 482, 'peminat_snbt' => 1120, 'peminat_mandiri' => 310, 'total_peminat' => 1912, 'keketatan' => '1 : 16'],
            ['tahun' => '2023', 'daya_tampung' => 120, 'peminat_snbp' => 465, 'peminat_snbt' => 1085, 'peminat_mandiri' => 295, 'total_peminat' => 1845, 'keketatan' => '1 : 15'],
            ['tahun' => '2022', 'daya_tampung' => 115, 'peminat_snbp' => 430, 'peminat_snbt' => 998,  'peminat_mandiri' => 280, 'total_peminat' => 1708, 'keketatan' => '1 : 15'],
            ['tahun' => '2021', 'daya_tampung' => 110, 'peminat_snbp' => 395, 'peminat_snbt' => 920,  'peminat_mandiri' => 250, 'total_peminat' => 1565, 'keketatan' => '1 : 14'],
            ['tahun' => '2020', 'daya_tampung' => 105, 'peminat_snbp' => 370, 'peminat_snbt' => 880,  'peminat_mandiri' => 230, 'total_peminat' => 1480, 'keketatan' => '1 : 14'],
        ],
    ];
}
}

if (!function_exists('ppb_get_maba_statistik_data')) {
function ppb_get_maba_statistik_data() {
    $defaults = ppb_get_maba_statistik_defaults();
    $saved    = get_option('ppb_maba_statistik_data', null);
    if (!is_array($saved) || empty($saved)) {
        return $defaults;
    }
    $clean_url = function($u, $fb) {
        $v = function_exists('ppb_clean_document_url') ? ppb_clean_document_url($u) : esc_url_raw((string) $u);
        return $v !== '' ? $v : $fb;
    };
    return [
        'portal_url' => $clean_url($saved['portal_url'] ?? '', $defaults['portal_url']),
        'unm_url'    => $clean_url($saved['unm_url'] ?? '', $defaults['unm_url']),
        'jalur'      => (!empty($saved['jalur']) && is_array($saved['jalur'])) ? array_values($saved['jalur']) : $defaults['jalur'],
        'tren'       => (!empty($saved['tren']) && is_array($saved['tren'])) ? array_values($saved['tren']) : $defaults['tren'],
    ];
}
}

if (!function_exists('ppb_get_mbkm_kemanusiaan_defaults')) {
function ppb_get_mbkm_kemanusiaan_defaults() {
    return [
        'mbkm_items' => [
            [
                'tahun'     => 'Tahun 2024',
                'lokasi'    => 'Kampus Gunungsari',
                'judul'     => 'Expo Media & Alat Permainan Edukatif (APE) Konseling',
                'deskripsi' => 'Pameran 45 karya media bimbingan kelompok interaktif hasil PPL MBKM mahasiswa di 15 SMA/SMK mitra Sulawesi Selatan.',
                'catatan'   => '👥 Diikuti oleh 120 mahasiswa praktikan dan dihadiri kepala sekolah mitra.',
                'dok_url'   => '',
            ],
            [
                'tahun'     => 'Tahun 2023',
                'lokasi'    => 'Gedung Phinisi UNM',
                'judul'     => 'Gelar Karya Inovasi Modul Konseling Sebaya & Karir',
                'deskripsi' => 'Peluncuran e-modul bimbingan karir digital untuk siswa kelas XII serta demonstrasi simulasi konseling lintas budaya.',
                'catatan'   => '👥 Disertai sesi talkshow interaktif bersama praktisi HRD industri.',
                'dok_url'   => '',
            ],
            [
                'tahun'     => 'Tahun 2022',
                'lokasi'    => 'Pelataran FIP UNM',
                'judul'     => 'Pekan Layanan Konseling & Asesmen Gratis Kampus Mengajar',
                'deskripsi' => 'Pemberian layanan asesmen minat bakat gratis bagi 250 siswa SMP/SMA binaan program Kampus Mengajar Angkatan 3 & 4.',
                'catatan'   => '👥 Dilaporkan sebagai eviden unggulan evaluasi mutu MBKM tingkat fakultas.',
                'dok_url'   => '',
            ],
        ],
        'proyek_kemanusiaan' => [
            'badge'        => 'Program Unggulan Kemanusiaan 2023',
            'judul'        => 'Trauma Healing & Psychological First Aid (PFA) Korban Banjir',
            'deskripsi'    => 'Tim Relawan Mahasiswa dan Dosen Pembimbing Bimbingan dan Konseling FIP UNM diterjunkan langsung dalam program pemulihan psikologis bagi anak-anak dan keluarga terdampak bencana banjir bandang di kawasan Makassar dan Maros pada awal 2023.',
            'poin_list'    => [
                'Layanan konseling kelompok ekspresif melalui art therapy dan play therapy',
                'Penyaluran 150 paket edukasi anak ceria dan alat tulis sekolah',
                'Mitra pelaksana: Dinas Sosial Provinsi Sulawesi Selatan dan PMI Cabang Makassar',
            ],
            'capaian_list' => [
                ['label' => '215 Anak & Remaja', 'detail' => 'terlayani sesi trauma healing terbimbing.'],
                ['label' => '25 Mahasiswa BK', 'detail' => 'memperoleh konversi 20 SKS mata kuliah MBKM Kemanusiaan.'],
                ['label' => '1 Buku Antologi', 'detail' => '"Jejak Empati di Garis Bencana" ber-ISBN terbitan UNM Press.'],
            ],
            'dok_url'      => '',
        ],
    ];
}
}

if (!function_exists('ppb_get_mbkm_kemanusiaan_data')) {
function ppb_get_mbkm_kemanusiaan_data() {
    $defaults = ppb_get_mbkm_kemanusiaan_defaults();
    $saved    = get_option('ppb_mbkm_kemanusiaan_data', null);
    if (!is_array($saved) || empty($saved)) {
        return $defaults;
    }
    $mbkm_items = (isset($saved['mbkm_items']) && is_array($saved['mbkm_items']) && !empty($saved['mbkm_items']))
        ? array_values($saved['mbkm_items'])
        : $defaults['mbkm_items'];
    $pk = (isset($saved['proyek_kemanusiaan']) && is_array($saved['proyek_kemanusiaan']))
        ? array_merge($defaults['proyek_kemanusiaan'], $saved['proyek_kemanusiaan'])
        : $defaults['proyek_kemanusiaan'];
    if (empty($pk['poin_list']) || !is_array($pk['poin_list'])) {
        $pk['poin_list'] = $defaults['proyek_kemanusiaan']['poin_list'];
    }
    if (empty($pk['capaian_list']) || !is_array($pk['capaian_list'])) {
        $pk['capaian_list'] = $defaults['proyek_kemanusiaan']['capaian_list'];
    }
    return [
        'mbkm_items'         => $mbkm_items,
        'proyek_kemanusiaan' => $pk,
    ];
}
}

if (!function_exists('ppb_render_kemahasiswaan_settings_page')) {
function ppb_render_kemahasiswaan_settings_page() {
    if (!current_user_can('edit_posts')) {
        wp_die('Anda tidak memiliki izin mengakses halaman ini.');
    }

    if (function_exists('ppb_admin_enqueue_media_assets')) {
        ppb_admin_enqueue_media_assets();
    }

    $active_tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'statistik';
    if (!in_array($active_tab, ['statistik', 'mbkm'], true)) {
        $active_tab = 'statistik';
    }

    $notice = '';

    if (function_exists('ppb_excel_handle_inline_upload')) {
        $xl_maba = ppb_excel_handle_inline_upload('maba_tren');
        if ($xl_maba !== '') {
            $active_tab = 'statistik';
            $notice     = $xl_maba;
        }
        $xl_mbkm = ppb_excel_handle_inline_upload('mbkm');
        if ($xl_mbkm !== '') {
            $active_tab = 'mbkm';
            $notice     = $xl_mbkm;
        }
    }

    // Form 1A: Indikator Demografi & Tracer Study
    if (isset($_POST['ppb_save_kemahasiswaan_options']) && check_admin_referer('ppb_save_km_nonce')) {
        $active_tab = 'statistik';
        $raw_laporan = wp_unslash($_POST['ppb_ts_drive_laporan_link'] ?? '');
        $clean_laporan = function_exists('ppb_clean_document_url')
            ? ppb_clean_document_url($raw_laporan)
            : esc_url_raw($raw_laporan);

        $options = [
            'ppb_km_total_aktif'          => sanitize_text_field(wp_unslash($_POST['ppb_km_total_aktif'] ?? '')),
            'ppb_km_lulus_tepat_waktu'    => sanitize_text_field(wp_unslash($_POST['ppb_km_lulus_tepat_waktu'] ?? '')),
            'ppb_km_persen_3t'            => sanitize_text_field(wp_unslash($_POST['ppb_km_persen_3t'] ?? '')),
            'ppb_km_rasio_dosen'          => sanitize_text_field(wp_unslash($_POST['ppb_km_rasio_dosen'] ?? '')),
            'ppb_km_ipk_rata'             => sanitize_text_field(wp_unslash($_POST['ppb_km_ipk_rata'] ?? '')),
            'ppb_ts_response_rate'        => sanitize_text_field(wp_unslash($_POST['ppb_ts_response_rate'] ?? '')),
            'ppb_ts_waktu_tunggu_3bln'    => sanitize_text_field(wp_unslash($_POST['ppb_ts_waktu_tunggu_3bln'] ?? '')),
            'ppb_ts_keselarasan_tinggi'   => sanitize_text_field(wp_unslash($_POST['ppb_ts_keselarasan_tinggi'] ?? '')),
            'ppb_ts_kepuasan_sangat_baik' => sanitize_text_field(wp_unslash($_POST['ppb_ts_kepuasan_sangat_baik'] ?? '')),
            'ppb_ts_drive_laporan_link'   => $clean_laporan,
            'ppb_ts_catatan_analisis'     => sanitize_textarea_field(wp_unslash($_POST['ppb_ts_catatan_analisis'] ?? '')),
        ];

        foreach ($options as $k => $v) {
            update_option($k, $v, false);
        }

        delete_post_meta(31, '_elementor_element_cache');
        delete_post_meta(34, '_elementor_element_cache');
        if (class_exists('\\Elementor\\Plugin')) {
            \Elementor\Plugin::$instance->files_manager->clear_cache();
        }

        $notice = '<div class="notice notice-success is-dismissible"><p><strong>✅ Pengaturan Tracer Study &amp; Data Kemahasiswaan berhasil disimpan!</strong></p></div>';
    }

    // Form 1B: Statistik Maba & Tren Daya Tampung 5 Tahun
    if (isset($_POST['ppb_save_maba_statistik']) && check_admin_referer('ppb_maba_nonce_action', 'ppb_maba_nonce')) {
        $active_tab = 'statistik';
        $defaults_maba = ppb_get_maba_statistik_defaults();
        $portal_raw = wp_unslash($_POST['maba_portal_url'] ?? '');
        $unm_raw    = wp_unslash($_POST['maba_unm_url'] ?? '');
        $portal_url = function_exists('ppb_clean_document_url') ? ppb_clean_document_url($portal_raw) : esc_url_raw($portal_raw);
        $unm_url    = function_exists('ppb_clean_document_url') ? ppb_clean_document_url($unm_raw) : esc_url_raw($unm_raw);

        $jalur_clean = [];
        $raw_jalur = isset($_POST['jalur']) && is_array($_POST['jalur']) ? wp_unslash($_POST['jalur']) : [];
        foreach ($raw_jalur as $jrow) {
            if (empty(trim((string) ($jrow['judul'] ?? '')))) continue;
            $tema = in_array(($jrow['tema'] ?? ''), ['green', 'gold', 'blue'], true) ? $jrow['tema'] : 'green';
            $jalur_clean[] = [
                'badge'     => sanitize_text_field($jrow['badge'] ?? 'Jalur Seleksi'),
                'tema'      => $tema,
                'judul'     => sanitize_text_field($jrow['judul']),
                'deskripsi' => sanitize_textarea_field($jrow['deskripsi'] ?? ''),
            ];
        }
        if (empty($jalur_clean)) {
            $jalur_clean = $defaults_maba['jalur'];
        }

        $tren_clean = [];
        $raw_tren = isset($_POST['tren']) && is_array($_POST['tren']) ? wp_unslash($_POST['tren']) : [];
        foreach ($raw_tren as $trow) {
            if (!empty($trow['delete']) || empty(trim((string) ($trow['tahun'] ?? '')))) continue;
            $dt  = max(0, (int) ($trow['daya_tampung'] ?? 0));
            $snb = max(0, (int) ($trow['peminat_snbp'] ?? 0));
            $sbt = max(0, (int) ($trow['peminat_snbt'] ?? 0));
            $mnd = max(0, (int) ($trow['peminat_mandiri'] ?? 0));
            $tot = (int) ($trow['total_peminat'] ?? 0);
            if ($tot <= 0) {
                $tot = $snb + $sbt + $mnd;
            }
            $ket = sanitize_text_field($trow['keketatan'] ?? '');
            if ($ket === '' && $dt > 0) {
                $ket = '1 : ' . max(1, (int) round($tot / $dt));
            }
            $tren_clean[] = [
                'tahun'           => sanitize_text_field($trow['tahun']),
                'daya_tampung'    => $dt,
                'peminat_snbp'    => $snb,
                'peminat_snbt'    => $sbt,
                'peminat_mandiri' => $mnd,
                'total_peminat'   => $tot,
                'keketatan'       => $ket,
            ];
        }
        if (!empty($_POST['new_tren']['tahun']) && trim((string) $_POST['new_tren']['tahun']) !== '') {
            $nt  = wp_unslash($_POST['new_tren']);
            $dt  = max(0, (int) ($nt['daya_tampung'] ?? 0));
            $snb = max(0, (int) ($nt['peminat_snbp'] ?? 0));
            $sbt = max(0, (int) ($nt['peminat_snbt'] ?? 0));
            $mnd = max(0, (int) ($nt['peminat_mandiri'] ?? 0));
            $tot = (int) ($nt['total_peminat'] ?? 0);
            if ($tot <= 0) {
                $tot = $snb + $sbt + $mnd;
            }
            $ket = sanitize_text_field($nt['keketatan'] ?? '');
            if ($ket === '' && $dt > 0) {
                $ket = '1 : ' . max(1, (int) round($tot / $dt));
            }
            array_unshift($tren_clean, [
                'tahun'           => sanitize_text_field($nt['tahun']),
                'daya_tampung'    => $dt,
                'peminat_snbp'    => $snb,
                'peminat_snbt'    => $sbt,
                'peminat_mandiri' => $mnd,
                'total_peminat'   => $tot,
                'keketatan'       => $ket,
            ]);
        }

        update_option('ppb_maba_statistik_data', [
            'portal_url' => $portal_url !== '' ? $portal_url : $defaults_maba['portal_url'],
            'unm_url'    => $unm_url !== '' ? $unm_url : $defaults_maba['unm_url'],
            'jalur'      => array_values($jalur_clean),
            'tren'       => array_values($tren_clean),
        ], false);
        $notice = '<div class="notice notice-success is-dismissible"><p><strong>🎓 Data Penerimaan Mahasiswa Baru &amp; Tren 5 Tahun (' . count($tren_clean) . ' tahun) berhasil disimpan!</strong></p></div>';
    }
    if (isset($_POST['ppb_reset_maba_statistik']) && check_admin_referer('ppb_maba_nonce_action', 'ppb_maba_nonce')) {
        $active_tab = 'statistik';
        delete_option('ppb_maba_statistik_data');
        $notice = '<div class="notice notice-warning is-dismissible"><p>Data Penerimaan Mahasiswa Baru dikembalikan ke bawaan.</p></div>';
    }

    // Form 2: Pameran MBKM & Proyek Kemanusiaan
    if (isset($_POST['ppb_save_mbkm_kemanusiaan']) && check_admin_referer('ppb_mbkm_nonce_action', 'ppb_mbkm_nonce')) {
        $active_tab = 'mbkm';
        $mbkm_clean = [];
        $raw_mbkm = isset($_POST['mbkm_items']) && is_array($_POST['mbkm_items']) ? wp_unslash($_POST['mbkm_items']) : [];
        foreach ($raw_mbkm as $mrow) {
            if (!empty($mrow['delete']) || empty(trim((string) ($mrow['judul'] ?? '')))) continue;
            $dok = function_exists('ppb_clean_document_url') ? ppb_clean_document_url($mrow['dok_url'] ?? '') : esc_url_raw((string) ($mrow['dok_url'] ?? ''));
            $mbkm_clean[] = [
                'tahun'     => sanitize_text_field($mrow['tahun'] ?? ''),
                'lokasi'    => sanitize_text_field($mrow['lokasi'] ?? ''),
                'judul'     => sanitize_text_field($mrow['judul']),
                'deskripsi' => sanitize_textarea_field($mrow['deskripsi'] ?? ''),
                'catatan'   => sanitize_text_field($mrow['catatan'] ?? ''),
                'dok_url'   => $dok,
            ];
        }
        if (!empty($_POST['new_mbkm']['judul']) && trim((string) $_POST['new_mbkm']['judul']) !== '') {
            $nm = wp_unslash($_POST['new_mbkm']);
            $dok = function_exists('ppb_clean_document_url') ? ppb_clean_document_url($nm['dok_url'] ?? '') : esc_url_raw((string) ($nm['dok_url'] ?? ''));
            $mbkm_clean[] = [
                'tahun'     => sanitize_text_field($nm['tahun'] ?? ''),
                'lokasi'    => sanitize_text_field($nm['lokasi'] ?? ''),
                'judul'     => sanitize_text_field($nm['judul']),
                'deskripsi' => sanitize_textarea_field($nm['deskripsi'] ?? ''),
                'catatan'   => sanitize_text_field($nm['catatan'] ?? ''),
                'dok_url'   => $dok,
            ];
        }

        $raw_pk = isset($_POST['pk']) && is_array($_POST['pk']) ? wp_unslash($_POST['pk']) : [];
        $poin_lines = array_values(array_filter(array_map(function($line) {
            return sanitize_text_field($line);
        }, preg_split('/\r\n|\r|\n/', (string) ($raw_pk['poin_text'] ?? '')))));

        $capaian_clean = [];
        if (!empty($raw_pk['capaian']) && is_array($raw_pk['capaian'])) {
            foreach ($raw_pk['capaian'] as $crow) {
                if (!empty($crow['delete']) || empty(trim((string) ($crow['label'] ?? '')))) continue;
                $capaian_clean[] = [
                    'label'  => sanitize_text_field($crow['label']),
                    'detail' => sanitize_text_field($crow['detail'] ?? ''),
                ];
            }
        }
        if (!empty($_POST['new_capaian']['label']) && trim((string) $_POST['new_capaian']['label']) !== '') {
            $nc = wp_unslash($_POST['new_capaian']);
            $capaian_clean[] = [
                'label'  => sanitize_text_field($nc['label']),
                'detail' => sanitize_text_field($nc['detail'] ?? ''),
            ];
        }

        $pk_dok = function_exists('ppb_clean_document_url') ? ppb_clean_document_url($raw_pk['dok_url'] ?? '') : esc_url_raw((string) ($raw_pk['dok_url'] ?? ''));
        $defaults_mbkm = ppb_get_mbkm_kemanusiaan_defaults();

        update_option('ppb_mbkm_kemanusiaan_data', [
            'mbkm_items'         => array_values($mbkm_clean),
            'proyek_kemanusiaan' => [
                'badge'        => sanitize_text_field($raw_pk['badge'] ?? $defaults_mbkm['proyek_kemanusiaan']['badge']),
                'judul'        => sanitize_text_field($raw_pk['judul'] ?? $defaults_mbkm['proyek_kemanusiaan']['judul']),
                'deskripsi'    => sanitize_textarea_field($raw_pk['deskripsi'] ?? $defaults_mbkm['proyek_kemanusiaan']['deskripsi']),
                'poin_list'    => !empty($poin_lines) ? $poin_lines : $defaults_mbkm['proyek_kemanusiaan']['poin_list'],
                'capaian_list' => !empty($capaian_clean) ? $capaian_clean : $defaults_mbkm['proyek_kemanusiaan']['capaian_list'],
                'dok_url'      => $pk_dok,
            ],
        ], false);
        $notice = '<div class="notice notice-success is-dismissible"><p><strong>🤝 Data Pameran MBKM (' . count($mbkm_clean) . ' kegiatan) &amp; Proyek Kemanusiaan berhasil disimpan!</strong></p></div>';
    }
    if (isset($_POST['ppb_reset_mbkm_kemanusiaan']) && check_admin_referer('ppb_mbkm_nonce_action', 'ppb_mbkm_nonce')) {
        $active_tab = 'mbkm';
        delete_option('ppb_mbkm_kemanusiaan_data');
        $notice = '<div class="notice notice-warning is-dismissible"><p>Data MBKM &amp; Proyek Kemanusiaan dikembalikan ke bawaan.</p></div>';
    }

    $opt_total_aktif   = get_option('ppb_km_total_aktif', '542');
    $opt_tepat_waktu   = get_option('ppb_km_lulus_tepat_waktu', '88.4');
    $opt_3t            = get_option('ppb_km_persen_3t', '24.8');
    $opt_rasio         = get_option('ppb_km_rasio_dosen', '1 : 28');
    $opt_ipk           = get_option('ppb_km_ipk_rata', '3.72');

    $opt_ts_resp       = get_option('ppb_ts_response_rate', '82.43');
    $opt_ts_tunggu     = get_option('ppb_ts_waktu_tunggu_3bln', '64.75');
    $opt_ts_selaras    = get_option('ppb_ts_keselarasan_tinggi', '78.69');
    $opt_ts_puas       = get_option('ppb_ts_kepuasan_sangat_baik', '84.5');
    $opt_ts_link       = get_option('ppb_ts_drive_laporan_link', 'https://drive.google.com/file/d/1_Laporan_Resmi_TracerStudy_TS1/view?usp=sharing');
    $opt_ts_analisis   = get_option('ppb_ts_catatan_analisis', "Response rate melampaui standar minimal akreditasi (82,43% > 50%). Waktu tunggu kerja rata-rata 2,4 bulan. Pengguna lulusan sangat mengapresiasi empati konseling dan literasi asesmen digital alumni BK UNM.");

    $tabs = [
        'statistik' => '📊 Demografi, Tracer & Maba',
        'mbkm'      => '🤝 Pameran MBKM & Kemanusiaan',
    ];
    ?>
    <div class="wrap" style="max-width:1120px;margin:20px auto;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;color:#1e293b;">
        <div style="background:linear-gradient(135deg,#0D3B22 0%,#185333 100%);color:#fff;border-radius:16px;padding:24px 28px;margin-bottom:18px;display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:14px;">
            <div>
                <h1 style="margin:0 0 6px;color:#fff;font-size:23px;font-weight:800;">🏆 Pengaturan Kemahasiswaan, Maba, Tracer Study &amp; MBKM</h1>
                <p style="margin:0;color:rgba(255,255,255,0.85);font-size:13px;">Kelola metrik demografi mahasiswa, laporan Tracer Study alumni, data peminat &amp; daya tampung Maba 5 tahun, serta dokumentasi Pameran MBKM &amp; Kemanusiaan.</p>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <a href="<?php echo esc_url(admin_url('edit.php?post_type=ppb_prestasi')); ?>" class="button" style="background:#F5B82E;color:#0D3B22;border:none;font-weight:700;border-radius:8px;">🏅 CPT Prestasi</a>
                <a href="<?php echo esc_url(admin_url('edit.php?post_type=ppb_karya')); ?>" class="button" style="border-radius:8px;">🎨 CPT Karya</a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=ppb-portal-hub')); ?>" class="button" style="border-radius:8px;">← Pusat Kendali</a>
            </div>
        </div>

        <nav class="nav-tab-wrapper" style="margin-bottom:20px;">
            <?php foreach ($tabs as $tk => $tl) : ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=ppb-kemahasiswaan-settings&tab=' . $tk)); ?>" class="nav-tab <?php echo $active_tab === $tk ? 'nav-tab-active' : ''; ?>" style="<?php echo $active_tab === $tk ? 'background:#0D3B22;color:#fff;border-color:#0D3B22;' : ''; ?>font-weight:700;">
                    <?php echo esc_html($tl); ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <?php echo $notice; ?>

        <?php if ($active_tab === 'statistik') :
            $maba_data = ppb_get_maba_statistik_data();
        ?>
            <form method="post" action="" style="margin-bottom:24px;">
                <?php wp_nonce_field('ppb_save_km_nonce'); ?>

                <!-- KARTU 1: INDIKATOR STATISTIK DEMOGRAFI -->
                <div style="background:#ffffff;border:1px solid #cbd5e1;border-radius:12px;padding:22px;margin-bottom:18px;box-shadow:0 3px 10px rgba(0,0,0,0.03);">
                    <h2 style="font-size:16px;font-weight:700;color:#0D3B22;margin-top:0;margin-bottom:14px;border-bottom:1px solid #E2E8F0;padding-bottom:10px;">📈 1. Indikator Utama Statistik Mahasiswa (/kemahasiswaan/statistik/)</h2>
                    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(160px, 1fr));gap:14px;">
                        <div>
                            <label style="font-weight:600;display:block;margin-bottom:4px;font-size:12px;">Total Mahasiswa Aktif</label>
                            <input type="text" name="ppb_km_total_aktif" value="<?php echo esc_attr($opt_total_aktif); ?>" style="width:100%;padding:6px 10px;border-radius:8px;border:1px solid #cbd5e1;" />
                        </div>
                        <div>
                            <label style="font-weight:600;display:block;margin-bottom:4px;font-size:12px;">Lulus Tepat Waktu (%)</label>
                            <input type="text" name="ppb_km_lulus_tepat_waktu" value="<?php echo esc_attr($opt_tepat_waktu); ?>" style="width:100%;padding:6px 10px;border-radius:8px;border:1px solid #cbd5e1;" />
                        </div>
                        <div>
                            <label style="font-weight:600;display:block;margin-bottom:4px;font-size:12px;">Mahasiswa Daerah 3T (%)</label>
                            <input type="text" name="ppb_km_persen_3t" value="<?php echo esc_attr($opt_3t); ?>" style="width:100%;padding:6px 10px;border-radius:8px;border:1px solid #cbd5e1;" />
                        </div>
                        <div>
                            <label style="font-weight:600;display:block;margin-bottom:4px;font-size:12px;">Rasio Dosen : Mahasiswa</label>
                            <input type="text" name="ppb_km_rasio_dosen" value="<?php echo esc_attr($opt_rasio); ?>" style="width:100%;padding:6px 10px;border-radius:8px;border:1px solid #cbd5e1;" />
                        </div>
                        <div>
                            <label style="font-weight:600;display:block;margin-bottom:4px;font-size:12px;">IPK Rata-Rata Kumulatif</label>
                            <input type="text" name="ppb_km_ipk_rata" value="<?php echo esc_attr($opt_ipk); ?>" style="width:100%;padding:6px 10px;border-radius:8px;border:1px solid #cbd5e1;" />
                        </div>
                    </div>
                </div>

                <!-- KARTU 2: METRIK TRACER STUDY ALUMNI -->
                <div style="background:#ffffff;border:1px solid #cbd5e1;border-radius:12px;padding:22px;margin-bottom:14px;box-shadow:0 3px 10px rgba(0,0,0,0.03);">
                    <h2 style="font-size:16px;font-weight:700;color:#0D3B22;margin-top:0;margin-bottom:14px;border-bottom:1px solid #E2E8F0;padding-bottom:10px;">🎓 2. Metrik Akreditasi Tracer Study (/kemahasiswaan/alumni/)</h2>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:14px;">
                        <div>
                            <label style="font-weight:600;display:block;margin-bottom:4px;font-size:12px;">Response Rate (%)</label>
                            <input type="text" name="ppb_ts_response_rate" value="<?php echo esc_attr($opt_ts_resp); ?>" style="width:100%;padding:6px 10px;border-radius:8px;border:1px solid #cbd5e1;" />
                            <span style="font-size:11px;color:#64748B;">Standar Akreditasi minimal &ge; 50%</span>
                        </div>
                        <div>
                            <label style="font-weight:600;display:block;margin-bottom:4px;font-size:12px;">Waktu Tunggu Kerja &lt; 3 Bulan (%)</label>
                            <input type="text" name="ppb_ts_waktu_tunggu_3bln" value="<?php echo esc_attr($opt_ts_tunggu); ?>" style="width:100%;padding:6px 10px;border-radius:8px;border:1px solid #cbd5e1;" />
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:14px;">
                        <div>
                            <label style="font-weight:600;display:block;margin-bottom:4px;font-size:12px;">Keselarasan Bidang Kerja Tinggi (%)</label>
                            <input type="text" name="ppb_ts_keselarasan_tinggi" value="<?php echo esc_attr($opt_ts_selaras); ?>" style="width:100%;padding:6px 10px;border-radius:8px;border:1px solid #cbd5e1;" />
                        </div>
                        <div>
                            <label style="font-weight:600;display:block;margin-bottom:4px;font-size:12px;">Kepuasan Pengguna Sangat Baik (%)</label>
                            <input type="text" name="ppb_ts_kepuasan_sangat_baik" value="<?php echo esc_attr($opt_ts_puas); ?>" style="width:100%;padding:6px 10px;border-radius:8px;border:1px solid #cbd5e1;" />
                        </div>
                    </div>
                    <div style="margin-bottom:14px;">
                        <label for="ppb_ts_drive_laporan_link" style="font-weight:600;display:block;margin-bottom:4px;font-size:12px;">🔗 Berkas / Tautan Laporan Lengkap Tracer Study Ber-SK (Upload PDF atau Link Drive)</label>
                        <?php
                        if (function_exists('ppb_render_media_url_control')) {
                            ppb_render_media_url_control(array(
                                'id'             => 'ppb_ts_drive_laporan_link',
                                'name'           => 'ppb_ts_drive_laporan_link',
                                'value'          => $opt_ts_link,
                                'allow_relative' => true,
                                'placeholder'    => 'https://drive.google.com/... atau upload PDF laporan Tracer Study',
                                'title'          => 'Pilih atau Upload Laporan Lengkap Tracer Study (PDF)',
                                'button_text'    => 'Gunakan Laporan Ini',
                                'btn_label'      => '📁 Upload / Pilih Laporan PDF',
                            ));
                        } else {
                            echo '<input type="url" id="ppb_ts_drive_laporan_link" name="ppb_ts_drive_laporan_link" value="' . esc_attr($opt_ts_link) . '" style="width:100%;padding:7px 12px;border-radius:8px;border:1px solid #cbd5e1;" />';
                        }
                        ?>
                    </div>
                    <div style="margin-bottom:10px;">
                        <label style="font-weight:600;display:block;margin-bottom:4px;font-size:12px;">Ringkasan Analisis Akreditasi</label>
                        <textarea name="ppb_ts_catatan_analisis" rows="3" style="width:100%;padding:8px 12px;border-radius:8px;border:1px solid #cbd5e1;"><?php echo esc_textarea($opt_ts_analisis); ?></textarea>
                    </div>
                </div>

                <div>
                    <button type="submit" name="ppb_save_kemahasiswaan_options" class="button button-primary" style="background:#0D3B22;border-color:#0D3B22;padding:6px 20px;height:auto;font-size:13px;font-weight:700;border-radius:8px;">💾 Simpan Pengaturan Demografi &amp; Tracer Study</button>
                </div>
            </form>

            <?php if (function_exists('ppb_excel_render_inline_widget')) ppb_excel_render_inline_widget('maba_tren'); ?>

            <!-- KARTU 3: STATISTIK PENERIMAAN MABA & TREN DAYA TAMPUNG (SHORTCODE [ppb_maba_statistik]) -->
            <form method="post" action="" style="background:#ffffff;border:1px solid #cbd5e1;border-radius:12px;padding:22px;box-shadow:0 3px 10px rgba(0,0,0,0.03);">
                <?php wp_nonce_field('ppb_maba_nonce_action', 'ppb_maba_nonce'); ?>
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:14px;border-bottom:2px solid #E8F0EA;padding-bottom:10px;">
                    <div>
                        <h2 style="margin:0;color:#0D3B22;font-size:17px;">🎯 3. Data Peminat &amp; Daya Tampung Maba 5 Tahun (Shortcode <code>[ppb_maba_statistik]</code>)</h2>
                        <span style="font-size:12px;color:#64748b;">Digunakan untuk tabel tren penerimaan Kriteria 3 LAMDIK dan kartu 3 jalur masuk (SNBP, SNBT, Mandiri).</span>
                    </div>
                    <div style="display:flex;gap:8px;">
                        <button type="submit" name="ppb_save_maba_statistik" class="button button-primary" style="background:#0D3B22;border-color:#0D3B22;">💾 Simpan Statistik Maba</button>
                        <button type="submit" name="ppb_reset_maba_statistik" class="button" onclick="return confirm('Reset Data Statistik Maba ke bawaan?');">↺ Reset Default</button>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px;">
                    <div>
                        <label style="font-weight:600;display:block;margin-bottom:4px;font-size:12px;">URL Portal Penerimaan UNM</label>
                        <input type="url" name="maba_portal_url" value="<?php echo esc_attr($maba_data['portal_url']); ?>" class="widefat">
                    </div>
                    <div>
                        <label style="font-weight:600;display:block;margin-bottom:4px;font-size:12px;">URL Website Resmi UNM</label>
                        <input type="url" name="maba_unm_url" value="<?php echo esc_attr($maba_data['unm_url']); ?>" class="widefat">
                    </div>
                </div>

                <h3 style="font-size:14px;color:#0D3B22;margin:0 0 8px;">Kartu 3 Jalur Penerimaan</h3>
                <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));gap:12px;margin-bottom:18px;">
                    <?php foreach ($maba_data['jalur'] as $jidx => $jl) : ?>
                        <div style="border:1px solid #e2e8f0;border-radius:10px;padding:12px;background:#fcfdfd;">
                            <div style="display:grid;grid-template-columns:1fr 90px;gap:8px;margin-bottom:6px;">
                                <input type="text" name="jalur[<?php echo $jidx; ?>][badge]" value="<?php echo esc_attr($jl['badge'] ?? ''); ?>" placeholder="Badge jalur" class="widefat">
                                <select name="jalur[<?php echo $jidx; ?>][tema]" class="widefat">
                                    <option value="green" <?php selected($jl['tema'] ?? 'green', 'green'); ?>>Hijau</option>
                                    <option value="gold" <?php selected($jl['tema'] ?? '', 'gold'); ?>>Emas</option>
                                    <option value="blue" <?php selected($jl['tema'] ?? '', 'blue'); ?>>Biru</option>
                                </select>
                            </div>
                            <input type="text" name="jalur[<?php echo $jidx; ?>][judul]" value="<?php echo esc_attr($jl['judul'] ?? ''); ?>" placeholder="Judul jalur seleksi" class="widefat" style="font-weight:700;margin-bottom:6px;">
                            <textarea name="jalur[<?php echo $jidx; ?>][deskripsi]" rows="2" class="widefat"><?php echo esc_textarea($jl['deskripsi'] ?? ''); ?></textarea>
                        </div>
                    <?php endforeach; ?>
                </div>

                <h3 style="font-size:14px;color:#0D3B22;margin:0 0 8px;">Tabel Tren Peminat vs Daya Tampung</h3>
                <table class="widefat striped" style="margin-bottom:12px;">
                    <thead>
                        <tr>
                            <th>Tahun</th>
                            <th>Daya Tampung</th>
                            <th>Peminat SNBP</th>
                            <th>Peminat SNBT</th>
                            <th>Peminat Mandiri</th>
                            <th>Total Peminat <small>(0 = otomatis)</small></th>
                            <th>Rasio Keketatan <small>(kosong = otomatis)</small></th>
                            <th>Hapus</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($maba_data['tren'] as $tidx => $tr) : ?>
                            <tr>
                                <td><input type="text" name="tren[<?php echo $tidx; ?>][tahun]" value="<?php echo esc_attr($tr['tahun'] ?? ''); ?>" style="width:70px;font-weight:700;"></td>
                                <td><input type="number" min="0" name="tren[<?php echo $tidx; ?>][daya_tampung]" value="<?php echo (int) ($tr['daya_tampung'] ?? 0); ?>" style="width:78px;"></td>
                                <td><input type="number" min="0" name="tren[<?php echo $tidx; ?>][peminat_snbp]" value="<?php echo (int) ($tr['peminat_snbp'] ?? 0); ?>" style="width:82px;"></td>
                                <td><input type="number" min="0" name="tren[<?php echo $tidx; ?>][peminat_snbt]" value="<?php echo (int) ($tr['peminat_snbt'] ?? 0); ?>" style="width:82px;"></td>
                                <td><input type="number" min="0" name="tren[<?php echo $tidx; ?>][peminat_mandiri]" value="<?php echo (int) ($tr['peminat_mandiri'] ?? 0); ?>" style="width:82px;"></td>
                                <td><input type="number" min="0" name="tren[<?php echo $tidx; ?>][total_peminat]" value="<?php echo (int) ($tr['total_peminat'] ?? 0); ?>" style="width:86px;font-weight:700;"></td>
                                <td><input type="text" name="tren[<?php echo $tidx; ?>][keketatan]" value="<?php echo esc_attr($tr['keketatan'] ?? ''); ?>" style="width:82px;"></td>
                                <td style="text-align:center;"><input type="checkbox" name="tren[<?php echo $tidx; ?>][delete]" value="1"></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr style="background:#f0fdf4;">
                            <td><input type="text" name="new_tren[tahun]" placeholder="2025" style="width:70px;"></td>
                            <td><input type="number" min="0" name="new_tren[daya_tampung]" placeholder="125" style="width:78px;"></td>
                            <td><input type="number" min="0" name="new_tren[peminat_snbp]" placeholder="500" style="width:82px;"></td>
                            <td><input type="number" min="0" name="new_tren[peminat_snbt]" placeholder="1150" style="width:82px;"></td>
                            <td><input type="number" min="0" name="new_tren[peminat_mandiri]" placeholder="320" style="width:82px;"></td>
                            <td><input type="number" min="0" name="new_tren[total_peminat]" placeholder="Auto" style="width:86px;"></td>
                            <td><input type="text" name="new_tren[keketatan]" placeholder="Auto" style="width:82px;"></td>
                            <td style="font-size:11px;color:#166534;font-weight:700;">+ Baru</td>
                        </tr>
                    </tbody>
                </table>
                <div style="text-align:right;">
                    <button type="submit" name="ppb_save_maba_statistik" class="button button-primary" style="background:#0D3B22;border-color:#0D3B22;padding:6px 20px;height:auto;font-weight:700;border-radius:8px;">💾 Simpan Data Statistik Maba</button>
                </div>
            </form>

        <?php elseif ($active_tab === 'mbkm') :
            $mbkm_data = ppb_get_mbkm_kemanusiaan_data();
            $mbkm_items = $mbkm_data['mbkm_items'];
            $pk         = $mbkm_data['proyek_kemanusiaan'];
        ?>
            <?php if (function_exists('ppb_excel_render_inline_widget')) ppb_excel_render_inline_widget('mbkm'); ?>

            <form method="post" action="">
                <?php wp_nonce_field('ppb_mbkm_nonce_action', 'ppb_mbkm_nonce'); ?>

                <!-- Pameran MBKM -->
                <div style="background:#fff;border:1px solid #cbd5e1;border-radius:12px;padding:22px;margin-bottom:20px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:14px;border-bottom:2px solid #E8F0EA;padding-bottom:10px;">
                        <div>
                            <h2 style="margin:0;color:#0D3B22;font-size:18px;">🤝 Pameran Inovasi Layanan MBKM (<?php echo count($mbkm_items); ?>)</h2>
                            <span style="font-size:12px;color:#64748b;">Ditampilkan pada shortcode <code>[ppb_mbkm_kemanusiaan]</code>.</span>
                        </div>
                        <div style="display:flex;gap:8px;">
                            <button type="submit" name="ppb_save_mbkm_kemanusiaan" class="button button-primary" style="background:#0D3B22;border-color:#0D3B22;">💾 Simpan MBKM &amp; Kemanusiaan</button>
                            <button type="submit" name="ppb_reset_mbkm_kemanusiaan" class="button" onclick="return confirm('Reset data MBKM & Kemanusiaan ke bawaan?');">↺ Reset Default</button>
                        </div>
                    </div>

                    <div style="display:grid;gap:12px;margin-bottom:16px;">
                        <?php foreach ($mbkm_items as $midx => $mk) : ?>
                            <div style="border:1px solid #e2e8f0;border-radius:10px;padding:14px;background:#fcfdfd;">
                                <div style="display:grid;grid-template-columns:120px 1fr 2fr;gap:10px;margin-bottom:8px;">
                                    <input type="text" name="mbkm_items[<?php echo $midx; ?>][tahun]" value="<?php echo esc_attr($mk['tahun'] ?? ''); ?>" placeholder="Tahun 2024" class="widefat">
                                    <input type="text" name="mbkm_items[<?php echo $midx; ?>][lokasi]" value="<?php echo esc_attr($mk['lokasi'] ?? ''); ?>" placeholder="Lokasi Kegiatan" class="widefat">
                                    <input type="text" name="mbkm_items[<?php echo $midx; ?>][judul]" value="<?php echo esc_attr($mk['judul'] ?? ''); ?>" placeholder="Judul Pameran / Inovasi" class="widefat" style="font-weight:700;">
                                </div>
                                <textarea name="mbkm_items[<?php echo $midx; ?>][deskripsi]" rows="2" class="widefat" placeholder="Deskripsi kegiatan..." style="margin-bottom:8px;"><?php echo esc_textarea($mk['deskripsi'] ?? ''); ?></textarea>
                                <div style="display:grid;grid-template-columns:1fr 1fr auto;gap:10px;align-items:center;">
                                    <input type="text" name="mbkm_items[<?php echo $midx; ?>][catatan]" value="<?php echo esc_attr($mk['catatan'] ?? ''); ?>" placeholder="👥 Catatan jumlah peserta / dampak" class="widefat">
                                    <?php ppb_render_media_url_control(['id' => 'mbkm_dok_' . $midx, 'name' => "mbkm_items[$midx][dok_url]", 'value' => $mk['dok_url'] ?? '', 'placeholder' => 'Link Dokumentasi / Laporan PDF (opsional)', 'btn_label' => '📁 Upload / Drive']); ?>
                                    <label style="color:#b91c1c;font-size:11.5px;font-weight:600;white-space:nowrap;"><input type="checkbox" name="mbkm_items[<?php echo $midx; ?>][delete]" value="1"> Hapus</label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div style="background:#f0fdf4;border:1.5px dashed #166534;border-radius:10px;padding:14px;">
                        <strong style="display:block;color:#0D3B22;margin-bottom:8px;">➕ Tambah Kegiatan Pameran MBKM Baru</strong>
                        <div style="display:grid;grid-template-columns:120px 1fr 2fr;gap:10px;margin-bottom:8px;">
                            <input type="text" name="new_mbkm[tahun]" placeholder="Tahun 2025" class="widefat">
                            <input type="text" name="new_mbkm[lokasi]" placeholder="Lokasi Pameran" class="widefat">
                            <input type="text" name="new_mbkm[judul]" placeholder="Judul Kegiatan MBKM baru (isi untuk menambah)..." class="widefat">
                        </div>
                        <textarea name="new_mbkm[deskripsi]" rows="2" placeholder="Ringkasan karya atau program MBKM..." class="widefat" style="margin-bottom:8px;"></textarea>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                            <input type="text" name="new_mbkm[catatan]" placeholder="👥 Catatan dampak / keterlibatan mahasiswa" class="widefat">
                            <?php ppb_render_media_url_control(['id' => 'mbkm_dok_new', 'name' => 'new_mbkm[dok_url]', 'value' => '', 'placeholder' => 'Link Dokumentasi / SK (opsional)', 'btn_label' => '📁 Upload / Drive']); ?>
                        </div>
                    </div>
                </div>

                <!-- Proyek Kemanusiaan -->
                <div style="background:#fff;border:1px solid #cbd5e1;border-radius:12px;padding:22px;margin-bottom:18px;">
                    <h2 style="margin:0 0 14px;color:#0D3B22;font-size:18px;border-bottom:2px solid #E8F0EA;padding-bottom:10px;">❤️ Sorotan Proyek Kemanusiaan Mahasiswa BK</h2>
                    <div style="display:grid;grid-template-columns:1fr 2fr;gap:10px;margin-bottom:10px;">
                        <div>
                            <label style="font-size:11.5px;font-weight:700;display:block;margin-bottom:3px;">Badge Program</label>
                            <input type="text" name="pk[badge]" value="<?php echo esc_attr($pk['badge'] ?? ''); ?>" class="widefat">
                        </div>
                        <div>
                            <label style="font-size:11.5px;font-weight:700;display:block;margin-bottom:3px;">Judul Program Kemanusiaan</label>
                            <input type="text" name="pk[judul]" value="<?php echo esc_attr($pk['judul'] ?? ''); ?>" class="widefat" style="font-weight:700;">
                        </div>
                    </div>
                    <div style="margin-bottom:10px;">
                        <label style="font-size:11.5px;font-weight:700;display:block;margin-bottom:3px;">Deskripsi Naratif</label>
                        <textarea name="pk[deskripsi]" rows="3" class="widefat"><?php echo esc_textarea($pk['deskripsi'] ?? ''); ?></textarea>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:12px;">
                        <div>
                            <label style="font-size:11.5px;font-weight:700;display:block;margin-bottom:3px;">Poin Aksi Lapangan (1 baris = 1 poin)</label>
                            <textarea name="pk[poin_text]" rows="5" class="widefat"><?php echo esc_textarea(implode("\n", $pk['poin_list'] ?? [])); ?></textarea>
                        </div>
                        <div>
                            <label style="font-size:11.5px;font-weight:700;display:block;margin-bottom:4px;">Kartu Capaian &amp; Dampak</label>
                            <div style="display:grid;gap:6px;">
                                <?php foreach ($pk['capaian_list'] ?? [] as $cidx => $cp) : ?>
                                    <div style="display:grid;grid-template-columns:1fr 1.6fr auto;gap:6px;align-items:center;">
                                        <input type="text" name="pk[capaian][<?php echo $cidx; ?>][label]" value="<?php echo esc_attr($cp['label'] ?? ''); ?>" placeholder="Angka/Label" class="widefat">
                                        <input type="text" name="pk[capaian][<?php echo $cidx; ?>][detail]" value="<?php echo esc_attr($cp['detail'] ?? ''); ?>" placeholder="Uraian dampak" class="widefat">
                                        <label style="color:#b91c1c;font-size:11px;"><input type="checkbox" name="pk[capaian][<?php echo $cidx; ?>][delete]" value="1"> Hps</label>
                                    </div>
                                <?php endforeach; ?>
                                <div style="display:grid;grid-template-columns:1fr 1.6fr;gap:6px;padding-top:4px;border-top:1px dashed #cbd5e1;">
                                    <input type="text" name="new_capaian[label]" placeholder="+ Tambah Capaian Baru" class="widefat">
                                    <input type="text" name="new_capaian[detail]" placeholder="Uraian dampak tambahan..." class="widefat">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label style="font-size:11.5px;font-weight:700;display:block;margin-bottom:4px;">Link Dokumen / Foto Laporan Proyek Kemanusiaan (Opsional)</label>
                        <?php ppb_render_media_url_control(['id' => 'pk_dok_url', 'name' => 'pk[dok_url]', 'value' => $pk['dok_url'] ?? '', 'placeholder' => 'Link Drive / upload PDF laporan kemanusiaan', 'btn_label' => '📁 Upload / Drive']); ?>
                    </div>
                </div>

                <div style="text-align:right;">
                    <button type="submit" name="ppb_save_mbkm_kemanusiaan" class="button button-primary" style="background:#0D3B22;border-color:#0D3B22;padding:7px 22px;height:auto;font-size:13.5px;font-weight:700;border-radius:8px;">💾 Simpan Pameran MBKM &amp; Kemanusiaan</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
    <?php
}
}

/* ========================================================================== */
/* 6. SHORTCODE: [ppb_prestasi_list] (Rekapitulasi Prestasi Mahasiswa)         */
/* ========================================================================== */
if (!function_exists('ppb_shortcode_prestasi_list')) {
function ppb_shortcode_prestasi_list() {
    $args = [
        'post_type'      => 'ppb_prestasi',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'meta_key'       => '_ppb_tahun',
        'orderby'        => 'meta_value_num',
        'order'          => 'DESC'
    ];

    $q = new WP_Query($args);
    $items = [];

    if ($q->have_posts()) {
        while ($q->have_posts()) {
            $q->the_post();
            $pid = get_the_ID();
            $items[] = [
                'id'            => 'pres-' . $pid,
                'nama'          => get_post_meta($pid, '_ppb_nama_mahasiswa', true) ?: get_the_title(),
                'nim'           => get_post_meta($pid, '_ppb_nim', true) ?: '',
                'angkatan'      => get_post_meta($pid, '_ppb_angkatan', true) ?: '',
                'capaian'       => get_post_meta($pid, '_ppb_capaian', true) ?: get_the_title(),
                'kegiatan'      => get_post_meta($pid, '_ppb_nama_kegiatan', true) ?: '',
                'tingkat'       => get_post_meta($pid, '_ppb_tingkat', true) ?: 'nasional',
                'tahun'         => get_post_meta($pid, '_ppb_tahun', true) ?: date('Y'),
                'penyelenggara' => get_post_meta($pid, '_ppb_penyelenggara', true) ?: 'Kemdiktisaintek RI',
                'pembimbing'    => get_post_meta($pid, '_ppb_dosen_pembimbing', true) ?: '',
                'deskripsi'     => get_post_meta($pid, '_ppb_deskripsi', true) ?: '',
                'drive_sertif'  => get_post_meta($pid, '_ppb_drive_sertifikat_link', true) ?: '',
                'drive_foto'    => get_post_meta($pid, '_ppb_drive_foto_link', true) ?: '',
            ];
        }
        wp_reset_postdata();
    }

    $count_total = count($items);
    $count_inter = count(array_filter($items, function($it) { return $it['tingkat'] === 'internasional'; }));
    $count_nas   = count(array_filter($items, function($it) { return $it['tingkat'] === 'nasional'; }));

    ob_start();
    ?>
    <section class="ppb-prestasi-wrapper" style="font-family:'Plus Jakarta Sans',sans-serif;color:#1e293b;width:100%;max-width:var(--max-w, 1160px);margin:0 auto;">

        <!-- Filter & Search Toolbar (Sleek Capsule Toolbar) -->
        <div class="ppb-toolbar-wrap">
            <div style="display:flex;align-items:center;gap:6px;overflow-x:auto;" class="no-scrollbar">
                <button type="button" class="ppb-pres-filter-btn ppb-pill-btn active" data-filter="semua">
                    Semua Tingkat
                </button>
                <button type="button" class="ppb-pres-filter-btn ppb-pill-btn" data-filter="internasional">
                    🌍 Internasional
                </button>
                <button type="button" class="ppb-pres-filter-btn ppb-pill-btn" data-filter="nasional">
                    🇮🇩 Nasional
                </button>
                <button type="button" class="ppb-pres-filter-btn ppb-pill-btn" data-filter="wilayah">
                    Wilayah / Regional
                </button>
            </div>

            <div style="display:flex;align-items:center;gap:10px;flex:1;min-width:240px;justify-content:flex-end;">
                <div style="position:relative;flex:1;max-width:320px;">
                    <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:13px;">🔍</span>
                    <input type="text" id="ppb-pres-search" class="ppb-search-input" placeholder="Cari mahasiswa atau lomba..." />
                </div>
                <select id="ppb-pres-year" class="ppb-select-year">
                    <option value="semua">Semua Tahun</option>
                    <option value="2026">2026</option>
                    <option value="2025">2025</option>
                    <option value="2024">2024</option>
                </select>
            </div>
        </div>

        <!-- Cards Grid Container -->
        <div id="ppb-pres-cards-container" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));gap:24px;">
            <?php foreach ($items as $it): 
                $badge_bg = $it['tingkat'] === 'internasional' ? '#ede9fe' : ($it['tingkat'] === 'nasional' ? '#dcfce7' : '#f1f5f9');
                $badge_col = $it['tingkat'] === 'internasional' ? '#5b21b6' : ($it['tingkat'] === 'nasional' ? '#166534' : '#334155');
                $badge_txt = strtoupper($it['tingkat']);
            ?>
                <div class="ppb-pres-card" 
                     data-tingkat="<?php echo esc_attr($it['tingkat']); ?>" 
                     data-tahun="<?php echo esc_attr($it['tahun']); ?>"
                     data-search="<?php echo esc_attr(strtolower($it['nama'] . ' ' . $it['capaian'] . ' ' . $it['kegiatan'] . ' ' . $it['penyelenggara'])); ?>"
                     style="background:#ffffff;border:1px solid #E2E8F0;border-radius:8px;padding:22px;box-shadow:0 6px 18px rgba(13,59,34,0.04);display:flex;flex-direction:column;justify-content:space-between;transition:all 0.25s ease;">
                    
                    <div>
                        <!-- Header: Badge & Tahun -->
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                            <span style="font-size:10px;font-weight:700;letter-spacing:0.06em;padding:3px 10px;border-radius:6px;background:<?php echo $badge_bg; ?>;color:<?php echo $badge_col; ?>;">
                                <?php echo esc_html($badge_txt); ?>
                            </span>
                            <span style="font-size:12px;font-weight:600;color:#64748B;display:inline-flex;align-items:center;gap:4px;">
                                📅 <?php echo esc_html($it['tahun']); ?>
                            </span>
                        </div>

                        <!-- Capaian & Kegiatan -->
                        <h3 style="font-family:'Fraunces',Georgia,serif;font-size:18px;font-weight:700;color:#0F172A;line-height:1.3;margin:0 0 6px 0;">
                            <?php echo esc_html($it['capaian']); ?>
                        </h3>
                        <p style="font-size:12.5px;color:#475569;margin:0 0 14px 0;line-height:1.5;">
                            <?php echo esc_html($it['kegiatan']); ?>
                        </p>

                        <!-- Box Penyelenggara & Deskripsi -->
                        <div style="background:#FAF8F5;border:1px solid #F1F5F9;border-radius:14px;padding:12px;margin-bottom:14px;font-size:11.5px;color:#475569;line-height:1.5;">
                            <div style="display:flex;align-items:flex-start;gap:6px;margin-bottom:4px;">
                                <span>🏛️</span>
                                <strong style="color:#0D3B22;"><?php echo esc_html($it['penyelenggara']); ?></strong>
                            </div>
                            <?php if (!empty($it['pembimbing'])): ?>
                                <div style="color:#64748B;margin-bottom:4px;">
                                    <b>Pembimbing:</b> <?php echo esc_html($it['pembimbing']); ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($it['deskripsi'])): ?>
                                <p style="margin:4px 0 0 0;color:#64748B;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;">
                                    <?php echo esc_html($it['deskripsi']); ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- Mahasiswa Profile -->
                        <div style="display:flex;align-items:center;gap:10px;padding-top:10px;border-top:1px solid #F1F5F9;">
                            <div style="width:34px;height:34px;border-radius:50%;background:#E8F0EA;display:flex;align-items:center;justify-content:center;color:#0D3B22;font-size:14px;flex-shrink:0;">
                                🎓
                            </div>
                            <div style="min-width:0;">
                                <strong style="font-size:12.5px;color:#0F172A;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                    <?php echo esc_html($it['nama']); ?>
                                </strong>
                                <span style="font-size:11px;color:#64748B;font-family:monospace;">
                                    <?php echo esc_html($it['nim'] ? 'NIM ' . $it['nim'] : ''); ?> 
                                    <?php echo esc_html($it['angkatan'] ? ' · ' . $it['angkatan'] : ''); ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Aksi Drive Modal -->
                    <div style="display:flex;align-items:center;gap:8px;margin-top:16px;padding-top:12px;border-top:1px solid #F1F5F9;">
                        <?php if (!empty($it['drive_sertif'])): ?>
                            <button type="button" onclick="window.ppbOpenDriveModal('Sertifikat - <?php echo esc_js($it['nama']); ?>', '<?php echo esc_url($it['drive_sertif']); ?>', '<?php echo esc_js($it['kegiatan']); ?>')" style="flex:1;padding:8px 12px;border-radius:10px;background:#0D3B22;color:#ffffff;font-size:11.5px;font-weight:700;border:none;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:6px;">
                                📜 Lihat Sertifikat
                            </button>
                        <?php endif; ?>
                        <?php if (!empty($it['drive_foto'])): ?>
                            <button type="button" onclick="window.ppbOpenDriveModal('Foto Piala - <?php echo esc_js($it['nama']); ?>', '<?php echo esc_url($it['drive_foto']); ?>', '<?php echo esc_js($it['capaian']); ?>')" style="padding:8px 12px;border-radius:10px;background:#FAF8F5;border:1px solid #E2E8F0;color:#334155;font-size:11.5px;font-weight:600;cursor:pointer;">
                                📸 Foto
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div id="ppb-pres-empty" style="display:none;background:#ffffff;border:1px dashed #cbd5e1;border-radius:8px;padding:48px 20px;text-align:center;">
            <span style="font-size:36px;display:block;margin-bottom:8px;">🏅</span>
            <p style="font-size:14px;color:#64748B;margin:0;font-weight:500;">Tidak ada data prestasi yang cocok dengan filter pencarian.</p>
        </div>
    </section>

    <!-- Client-Side Filter Script -->
    <script>
    (function() {
        const filterBtns = document.querySelectorAll('.ppb-pres-filter-btn');
        const searchInput = document.getElementById('ppb-pres-search');
        const yearSelect = document.getElementById('ppb-pres-year');
        const cards = document.querySelectorAll('.ppb-pres-card');
        const emptyEl = document.getElementById('ppb-pres-empty');

        let currentTingkat = 'semua';
        let currentYear = 'semua';
        let currentQuery = '';

        function applyFilters() {
            let visibleCount = 0;
            cards.forEach(card => {
                const tingkat = card.dataset.tingkat;
                const tahun = card.dataset.tahun;
                const searchTxt = card.dataset.search;

                const matchTingkat = (currentTingkat === 'semua' || tingkat === currentTingkat);
                const matchYear = (currentYear === 'semua' || tahun === currentYear);
                const matchQuery = (!currentQuery || searchTxt.includes(currentQuery));

                if (matchTingkat && matchYear && matchQuery) {
                    card.style.display = 'flex';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            if (emptyEl) {
                emptyEl.style.display = (visibleCount === 0) ? 'block' : 'none';
            }
        }

        filterBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                filterBtns.forEach(b => {
                    b.classList.remove('active');
                    b.style.background = '#FAF8F5';
                    b.style.color = '#475569';
                    b.style.borderColor = '#E2E8F0';
                });
                this.classList.add('active');
                this.style.background = '#0D3B22';
                this.style.color = '#F5B82E';
                this.style.borderColor = 'transparent';
                currentTingkat = this.dataset.filter;
                applyFilters();
            });
        });

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                currentQuery = this.value.toLowerCase().trim();
                applyFilters();
            });
        }

        if (yearSelect) {
            yearSelect.addEventListener('change', function() {
                currentYear = this.value;
                applyFilters();
            });
        }
    })();
    </script>
    <?php
    return ob_get_clean();
}
}
add_shortcode('ppb_prestasi_list', 'ppb_shortcode_prestasi_list');

/* ========================================================================== */
/* 7. SHORTCODE: [ppb_karya_list] (Karya & Inovasi Mahasiswa)                 */
/* ========================================================================== */
if (!function_exists('ppb_shortcode_karya_list')) {
function ppb_shortcode_karya_list() {
    $args = [
        'post_type'      => 'ppb_karya',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'meta_key'       => '_ppb_tahun',
        'orderby'        => 'meta_value_num',
        'order'          => 'DESC'
    ];

    $q = new WP_Query($args);
    $items = [];

    if ($q->have_posts()) {
        while ($q->have_posts()) {
            $q->the_post();
            $pid = get_the_ID();
            $items[] = [
                'id'         => 'karya-' . $pid,
                'judul'      => get_post_meta($pid, '_ppb_karya_judul', true) ?: get_the_title(),
                'kategori'   => get_post_meta($pid, '_ppb_kategori', true) ?: 'media_bimbingan',
                'pembuat'    => get_post_meta($pid, '_ppb_pembuat', true) ?: 'Mahasiswa BK',
                'pembimbing' => get_post_meta($pid, '_ppb_dosen_pembimbing', true) ?: '',
                'matkul'     => get_post_meta($pid, '_ppb_mata_kuliah', true) ?: '',
                'tahun'      => get_post_meta($pid, '_ppb_tahun', true) ?: date('Y'),
                'badge'      => get_post_meta($pid, '_ppb_badge_unggulan', true) ?: '',
                'deskripsi'  => get_post_meta($pid, '_ppb_deskripsi', true) ?: '',
                'manfaat'    => get_post_meta($pid, '_ppb_manfaat', true) ?: '',
                'foto'       => get_post_meta($pid, '_ppb_foto_karya_link', true) ?: '',
                'drive'      => get_post_meta($pid, '_ppb_drive_file_link', true) ?: '',
            ];
        }
        wp_reset_postdata();
    }

    $count_total = count($items);
    $count_media = count(array_filter($items, function($k) { return in_array($k['kategori'], ['media_bimbingan', 'modul_psikoedukasi']); }));
    $count_app   = count(array_filter($items, function($k) { return in_array($k['kategori'], ['instrumen_asesmen', 'aplikasi_digital']); }));
    $count_hki   = count(array_filter($items, function($k) { return !empty($k['badge']); }));

    ob_start();
    ?>
    <section class="ppb-karya-wrapper" style="font-family:'Plus Jakarta Sans',sans-serif;color:#1e293b;width:100%;max-width:var(--max-w, 1160px);margin:0 auto;">

        <!-- Filter & Search Toolbar (Sleek Capsule Toolbar) -->
        <div class="ppb-toolbar-wrap">
            <div style="position:relative;flex:1;min-width:240px;max-width:400px;">
                <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:13px;">🔍</span>
                <input type="text" id="ppb-karya-search" class="ppb-search-input" placeholder="Cari karya, mahasiswa, pembimbing, matkul..." />
            </div>
            
            <div style="display:flex;align-items:center;gap:6px;overflow-x:auto;flex-wrap:wrap;" class="no-scrollbar">
                <?php 
                $kat_tabs = [
                    'semua'              => 'Semua Kategori',
                    'media_bimbingan'    => 'Media Bimbingan',
                    'modul_psikoedukasi' => 'Modul Psikoedukasi',
                    'instrumen_asesmen'  => 'Instrumen Asesmen',
                    'aplikasi_digital'   => 'Aplikasi Digital',
                    'video_podcast'      => 'Video & Podcast',
                ];
                foreach ($kat_tabs as $k_val => $k_lbl): ?>
                    <button type="button" class="ppb-karya-filter-btn ppb-pill-btn <?php echo $k_val === 'semua' ? 'active' : ''; ?>" data-kategori="<?php echo esc_attr($k_val); ?>">
                        <?php echo esc_html($k_lbl); ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Grid Kartu Karya -->
        <div id="ppb-karya-cards-grid" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));gap:24px;">
            <?php foreach ($items as $karya): 
                $kat_badge_labels = [
                    'media_bimbingan'   => 'Media Bimbingan',
                    'modul_psikoedukasi' => 'Modul Psikoedukasi',
                    'instrumen_asesmen' => 'Instrumen Asesmen',
                    'aplikasi_digital'  => 'Aplikasi Digital',
                    'video_podcast'     => 'Video & Podcast',
                ];
                $kat_label = $kat_badge_labels[$karya['kategori']] ?? $karya['kategori'];
                $default_cover = 'https://images.unsplash.com/photo-1610890716171-6b1bb98ffd09?auto=format&fit=crop&w=800&q=80';
                $cover_src = !empty($karya['foto']) ? $karya['foto'] : $default_cover;
            ?>
                <div class="ppb-karya-card"
                     data-kategori="<?php echo esc_attr($karya['kategori']); ?>"
                     data-search="<?php echo esc_attr(strtolower($karya['judul'] . ' ' . $karya['pembuat'] . ' ' . $karya['pembimbing'] . ' ' . $karya['matkul'])); ?>"
                     style="background:#ffffff;border:1px solid #E2E8F0;border-radius:8px;box-shadow:0 6px 20px rgba(13,59,34,0.04);overflow:hidden;display:flex;flex-direction:column;justify-content:space-between;transition:all 0.25s ease;">
                    
                    <div>
                        <!-- Cover Image -->
                        <div style="position:relative;height:180px;width:100%;background:#0D3B22;overflow:hidden;">
                            <img src="<?php echo esc_url($cover_src); ?>" alt="<?php echo esc_attr($karya['judul']); ?>" style="width:100%;height:100%;object-fit:cover;transition:transform 0.3s;" onmouseover="this.style.transform='scale(1.05)';" onmouseout="this.style.transform='scale(1)';" />
                            <div style="position:absolute;top:12px;left:12px;">
                                <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;padding:4px 10px;border-radius:6px;background:rgba(255,255,255,0.95);color:#0D3B22;box-shadow:0 2px 8px rgba(0,0,0,0.12);">
                                    <?php echo esc_html($kat_label); ?>
                                </span>
                            </div>
                            <?php if (!empty($karya['badge'])): ?>
                                <div style="position:absolute;top:12px;right:12px;">
                                    <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;padding:4px 10px;border-radius:6px;background:#F5B82E;color:#0D3B22;box-shadow:0 2px 8px rgba(0,0,0,0.18);">
                                        ⭐ <?php echo esc_html($karya['badge']); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Card Body -->
                        <div style="padding:20px;">
                            <h3 style="font-family:'Fraunces',Georgia,serif;font-size:17px;font-weight:700;color:#0F172A;line-height:1.35;margin:0 0 8px 0;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                                <?php echo esc_html($karya['judul']); ?>
                            </h3>
                            <p style="font-size:12px;color:#64748B;line-height:1.6;margin:0 0 12px 0;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;">
                                <?php echo esc_html($karya['deskripsi']); ?>
                            </p>

                            <!-- Dampak & Penerapan -->
                            <?php if (!empty($karya['manfaat'])): ?>
                                <div style="background:#FAF8F5;border:1px solid #F1F5F9;border-radius:12px;padding:10px 12px;margin-bottom:12px;font-size:11px;color:#475569;line-height:1.5;">
                                    <strong style="color:#0D3B22;text-transform:uppercase;display:block;font-size:9.5px;letter-spacing:0.06em;margin-bottom:2px;">Dampak & Penerapan:</strong>
                                    <span style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;"><?php echo esc_html($karya['manfaat']); ?></span>
                                </div>
                            <?php endif; ?>

                            <!-- Creators & Supervisors -->
                            <div style="font-size:11.5px;color:#475569;space-y:4px;padding-top:8px;border-top:1px solid #F1F5F9;">
                                <div style="display:flex;align-items:center;gap:6px;margin-bottom:4px;">
                                    <span>👥</span>
                                    <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><b>Pencipta:</b> <?php echo esc_html($karya['pembuat']); ?></span>
                                </div>
                                <?php if (!empty($karya['pembimbing'])): ?>
                                    <div style="display:flex;align-items:center;gap:6px;margin-bottom:4px;">
                                        <span>🎓</span>
                                        <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><b>Pembimbing:</b> <?php echo esc_html($karya['pembimbing']); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($karya['matkul'])): ?>
                                    <div style="display:flex;align-items:center;gap:6px;color:#64748B;font-size:11px;">
                                        <span>📚</span>
                                        <span><?php echo esc_html($karya['matkul']); ?> (<?php echo esc_html($karya['tahun']); ?>)</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Action -->
                    <div style="padding:0 20px 20px 20px;">
                        <?php if (!empty($karya['drive'])): ?>
                            <button type="button" onclick="window.ppbOpenDriveModal('<?php echo esc_js($karya['judul']); ?>', '<?php echo esc_url($karya['drive']); ?>', '<?php echo esc_js($karya['manfaat']); ?>')" style="width:100%;padding:10px 16px;border-radius:12px;background:#0D3B22;color:#ffffff;font-size:12px;font-weight:700;border:none;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:6px;transition:background 0.2s;" onmouseover="this.style.background='#185333';" onmouseout="this.style.background='#0D3B22';">
                                👁️ Buka Berkas & Detail Produk
                            </button>
                        <?php else: ?>
                            <span style="display:block;text-align:center;font-size:11.5px;color:#94a3b8;padding:8px 0;">Tersedia di Laboratorium BK</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="ppb-karya-empty" style="display:none;background:#ffffff;border:1px dashed #cbd5e1;border-radius:8px;padding:48px 20px;text-align:center;margin-top:20px;">
            <span style="font-size:36px;display:block;margin-bottom:8px;">💡</span>
            <p style="font-size:14px;color:#64748B;margin:0;font-weight:500;">Tidak ada produk inovasi yang cocok dengan pencarian Anda.</p>
        </div>

        <!-- Banner Laboratorium BK -->
        <div style="background:linear-gradient(135deg, #0D3B22 0%, #185333 100%);border-radius:8px;padding:32px;color:#ffffff;margin-top:40px;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:20px;box-shadow:0 12px 32px rgba(13,59,34,0.18);">
            <div style="max-width:680px;">
                <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:#F5B82E;display:block;margin-bottom:4px;">Fasilitas Inkubasi & Uji Coba</span>
                <h3 style="font-family:'Fraunces',Georgia,serif;font-size:22px;font-weight:700;margin:0 0 8px 0;">Didukung Laboratorium Konseling & Asesmen Digital FIP UNM</h3>
                <p style="font-size:13px;line-height:1.6;color:rgba(255,255,255,0.85);margin:0;">Seluruh produk media bimbingan dan instrumen asesmen melalui tahapan validasi pakar dosen BK, uji keterbacaan, dan uji coba terbatas di sekolah laboratorium mitra binaan FIP UNM.</p>
            </div>
            <a href="<?php echo esc_url(home_url('/layanan/lab-bk/')); ?>" style="display:inline-flex;align-items:center;gap:6px;padding:12px 24px;border-radius:30px;background:#F5B82E;color:#0D3B22;font-size:13px;font-weight:700;text-decoration:none;box-shadow:0 4px 14px rgba(245,184,46,0.35);">
                Eksplorasi Laboratorium BK &rarr;
            </a>
        </div>
    </section>

    <!-- Client-Side Karya Filter Script -->
    <script>
    (function() {
        const filterBtns = document.querySelectorAll('.ppb-karya-filter-btn');
        const searchInput = document.getElementById('ppb-karya-search');
        const cards = document.querySelectorAll('.ppb-karya-card');
        const emptyEl = document.getElementById('ppb-karya-empty');

        let currentKat = 'semua';
        let currentQuery = '';

        function applyKaryaFilters() {
            let count = 0;
            cards.forEach(card => {
                const kat = card.dataset.kategori;
                const searchTxt = card.dataset.search;

                const matchKat = (currentKat === 'semua' || kat === currentKat);
                const matchQuery = (!currentQuery || searchTxt.includes(currentQuery));

                if (matchKat && matchQuery) {
                    card.style.display = 'flex';
                    count++;
                } else {
                    card.style.display = 'none';
                }
            });

            if (emptyEl) {
                emptyEl.style.display = (count === 0) ? 'block' : 'none';
            }
        }

        filterBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                filterBtns.forEach(b => {
                    b.classList.remove('active');
                    b.style.background = '#FAF8F5';
                    b.style.color = '#475569';
                    b.style.borderColor = '#E2E8F0';
                });
                this.classList.add('active');
                this.style.background = '#0D3B22';
                this.style.color = '#F5B82E';
                this.style.borderColor = 'transparent';
                currentKat = this.dataset.kategori;
                applyKaryaFilters();
            });
        });

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                currentQuery = this.value.toLowerCase().trim();
                applyKaryaFilters();
            });
        }
    })();
    </script>
    <?php
    return ob_get_clean();
}
}
add_shortcode('ppb_karya_list', 'ppb_shortcode_karya_list');

/* ========================================================================== */
/* 8. SHORTCODE: [ppb_tracer_study_view] (Tracer Study & Kiprah Alumni)        */
/* ========================================================================== */
if (!function_exists('ppb_shortcode_tracer_study_view')) {
function ppb_shortcode_tracer_study_view() {
    $opt_ts_resp       = get_option('ppb_ts_response_rate', '82.43');
    $opt_ts_tunggu     = get_option('ppb_ts_waktu_tunggu_3bln', '64.75');
    $opt_ts_selaras    = get_option('ppb_ts_keselarasan_tinggi', '78.69');
    $opt_ts_puas       = get_option('ppb_ts_kepuasan_sangat_baik', '84.5');
    $opt_ts_link       = get_option('ppb_ts_drive_laporan_link', 'https://drive.google.com/file/d/1_Laporan_Resmi_TracerStudy_TS1/view?usp=sharing');
    $opt_ts_analisis   = get_option('ppb_ts_catatan_analisis', "Response rate melampaui standar minimal akreditasi (82,43% > 50%). Waktu tunggu kerja rata-rata 2,4 bulan. Pengguna lulusan sangat mengapresiasi empati konseling dan literasi asesmen digital alumni BK UNM.");

    ob_start();
    ?>
    <section class="ppb-tracer-wrapper" style="font-family:'Plus Jakarta Sans',sans-serif;color:#1e293b;width:100%;max-width:var(--max-w, 1160px);margin:0 auto;">
        
        <!-- 4 Key Metrics Cards -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));gap:20px;margin-bottom:32px;">
            <div style="background:#ffffff;border:1px solid #E2E8F0;border-radius:8px;padding:24px;box-shadow:0 6px 18px rgba(13,59,34,0.04);">
                <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:#64748B;display:block;margin-bottom:6px;">Response Rate Responden</span>
                <div style="display:flex;align-items:baseline;gap:8px;margin-bottom:6px;">
                    <span style="font-family:'Fraunces',Georgia,serif;font-size:36px;font-weight:700;color:#0D3B22;"><?php echo esc_html($opt_ts_resp); ?>%</span>
                    <span style="font-size:11px;font-weight:700;background:#dcfce7;color:#166534;padding:3px 8px;border-radius:6px;">&ge; 50% Standar</span>
                </div>
                <p style="font-size:12px;color:#64748B;margin:0;">122 dari 148 populasi alumni aktif berpartisipasi dalam tracer study.</p>
            </div>

            <div style="background:#ffffff;border:1px solid #E2E8F0;border-radius:8px;padding:24px;box-shadow:0 6px 18px rgba(13,59,34,0.04);">
                <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:#64748B;display:block;margin-bottom:6px;">Waktu Tunggu Kerja &lt; 3 Bln</span>
                <div style="display:flex;align-items:baseline;gap:8px;margin-bottom:6px;">
                    <span style="font-family:'Fraunces',Georgia,serif;font-size:36px;font-weight:700;color:#1d4ed8;"><?php echo esc_html($opt_ts_tunggu); ?>%</span>
                    <span style="font-size:12px;color:#64748B;">lulusan</span>
                </div>
                <p style="font-size:12px;color:#64748B;margin:0;">Rata-rata lulusan terserap kerja dalam tempo 2,4 bulan pasca yudisium.</p>
            </div>

            <div style="background:#ffffff;border:1px solid #E2E8F0;border-radius:8px;padding:24px;box-shadow:0 6px 18px rgba(13,59,34,0.04);">
                <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:#64748B;display:block;margin-bottom:6px;">Keselarasan Bidang Kerja</span>
                <div style="display:flex;align-items:baseline;gap:8px;margin-bottom:6px;">
                    <span style="font-family:'Fraunces',Georgia,serif;font-size:36px;font-weight:700;color:#d97706;"><?php echo esc_html($opt_ts_selaras); ?>%</span>
                    <span style="font-size:11px;font-weight:700;background:#fef3c7;color:#92400e;padding:3px 8px;border-radius:6px;">Linear (Tinggi)</span>
                </div>
                <p style="font-size:12px;color:#64748B;margin:0;">Pekerjaan alumni sangat selaras dengan Capaian Pembelajaran (CPL) BK.</p>
            </div>

            <div style="background:#ffffff;border:1px solid #E2E8F0;border-radius:8px;padding:24px;box-shadow:0 6px 18px rgba(13,59,34,0.04);">
                <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:#64748B;display:block;margin-bottom:6px;">Kepuasan Pengguna Lulusan</span>
                <div style="display:flex;align-items:baseline;gap:8px;margin-bottom:6px;">
                    <span style="font-family:'Fraunces',Georgia,serif;font-size:36px;font-weight:700;color:#0D3B22;"><?php echo esc_html($opt_ts_puas); ?>%</span>
                    <span style="font-size:11px;font-weight:700;background:#dcfce7;color:#166534;padding:3px 8px;border-radius:6px;">Sangat Baik</span>
                </div>
                <p style="font-size:12px;color:#64748B;margin:0;">Penilaian langsung dari kepala sekolah, manajer HRD, dan pimpinan lembaga mitra.</p>
            </div>
        </div>

        <!-- 2 Column Breakdown: Sebaran Profesi & Kecepatan Serapan -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(320px, 1fr));gap:24px;margin-bottom:32px;">
            <!-- Profil Profesi Alumni -->
            <div style="background:#ffffff;border:1px solid #E2E8F0;border-radius:8px;padding:28px;box-shadow:0 6px 20px rgba(13,59,34,0.04);">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
                    <div>
                        <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.12em;color:#B45309;display:block;margin-bottom:2px;">Profil Profesi Alumni</span>
                        <h3 style="font-family:'Fraunces',Georgia,serif;font-size:20px;font-weight:700;color:#0F172A;margin:0;">Sebaran Lapangan Pekerjaan</h3>
                    </div>
                    <span style="font-size:24px;">💼</span>
                </div>

                <div style="space-y:16px;">
                    <?php 
                    $profesi = [
                        ['Guru Bimbingan dan Konseling / Konselor Sekolah', 54.1, '#0D3B22'],
                        ['Konselor Mandiri, Klinik & Pusat Terapi', 14.8, '#059669'],
                        ['Human Resources (HRD), Trainer & Rekruter Industri', 18.0, '#2563eb'],
                        ['Akademisi, Peneliti & Melanjutkan Studi S2', 8.2, '#7c3aed'],
                        ['Wirausaha Sosial & Bidang Kreatif Lainnya', 4.9, '#94a3b8'],
                    ];
                    foreach ($profesi as $p): ?>
                        <div style="margin-bottom:14px;">
                            <div style="display:flex;justify-content:space-between;font-size:13px;font-weight:600;color:#1e293b;margin-bottom:6px;">
                                <span><?php echo esc_html($p[0]); ?></span>
                                <span style="font-family:monospace;font-weight:700;"><?php echo $p[1]; ?>%</span>
                            </div>
                            <div style="height:10px;background:#F1F5F9;border-radius:6px;overflow:hidden;">
                                <div style="height:100%;width:<?php echo $p[1]; ?>%;background:<?php echo $p[2]; ?>;border-radius:6px;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Distribusi Waktu Tunggu & Laporan SK -->
            <div style="background:#ffffff;border:1px solid #E2E8F0;border-radius:8px;padding:28px;box-shadow:0 6px 20px rgba(13,59,34,0.04);display:flex;flex-direction:column;justify-content:space-between;">
                <div>
                    <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.12em;color:#B45309;display:block;margin-bottom:2px;">Kecepatan Keterserapan</span>
                    <h3 style="font-family:'Fraunces',Georgia,serif;font-size:20px;font-weight:700;color:#0F172A;margin:0 0 18px 0;">Distribusi Waktu Mendapatkan Kerja</h3>

                    <div class="ppb-tracer-cols-3" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(140px, 1fr));gap:12px;margin-bottom:20px;">
                        <div style="background:#dcfce7;border:1px solid #bbf7d0;border-radius:6px;padding:14px;text-align:center;">
                            <span style="font-size:11px;font-weight:700;text-transform:uppercase;color:#166534;display:block;margin-bottom:4px;">&lt; 3 Bulan</span>
                            <span style="font-family:'Fraunces',Georgia,serif;font-size:24px;font-weight:700;color:#14532d;display:block;"><?php echo esc_html($opt_ts_tunggu); ?>%</span>
                            <span style="font-size:10px;color:#166534;">Mayoritas lulusan</span>
                        </div>
                        <div style="background:#dbeafe;border:1px solid #bfdbfe;border-radius:6px;padding:14px;text-align:center;">
                            <span style="font-size:11px;font-weight:700;text-transform:uppercase;color:#1e40af;display:block;margin-bottom:4px;">3 - 6 Bulan</span>
                            <span style="font-family:'Fraunces',Georgia,serif;font-size:24px;font-weight:700;color:#1e3a8a;display:block;">26.23%</span>
                            <span style="font-size:10px;color:#1e40af;">Proses seleksi</span>
                        </div>
                        <div style="background:#f1f5f9;border:1px solid #e2e8f0;border-radius:6px;padding:14px;text-align:center;">
                            <span style="font-size:11px;font-weight:700;text-transform:uppercase;color:#475569;display:block;margin-bottom:4px;">&gt; 6 Bulan</span>
                            <span style="font-family:'Fraunces',Georgia,serif;font-size:24px;font-weight:700;color:#0f172a;display:block;">9.02%</span>
                            <span style="font-size:10px;color:#475569;">Persiapan karir</span>
                        </div>
                    </div>

                    <?php if (!empty($opt_ts_analisis)): ?>
                        <div style="background:#FAF8F5;border:1px solid #E2E8F0;border-radius:6px;padding:16px;font-size:12.5px;color:#334155;line-height:1.6;margin-bottom:20px;">
                            <strong style="color:#0D3B22;display:block;margin-bottom:4px;">📋 Ringkasan Analisis Akreditasi:</strong>
                            <p style="margin:0;"><?php echo esc_html($opt_ts_analisis); ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Tombol Laporan Drive -->
                <div>
                    <button type="button" onclick="window.ppbOpenDriveModal('Laporan Lengkap Tracer Study Ber-SK', '<?php echo esc_url($opt_ts_link); ?>', 'Dokumen resmi hasil evaluasi pelacakan karir alumni')" style="width:100%;padding:14px 20px;border-radius:14px;background:#0D3B22;color:#ffffff;font-size:13px;font-weight:700;border:none;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:8px;box-shadow:0 4px 14px rgba(13,59,34,0.18);">
                        📄 Lihat Laporan Lengkap Tracer Study Ber-SK &rarr;
                    </button>
                </div>
            </div>
        </div>
    </section>
    <?php
    return ob_get_clean();
}
}
add_shortcode('ppb_tracer_study_view', 'ppb_shortcode_tracer_study_view');

/* ========================================================================== */
/* 9. DRIVE DOCUMENT MODAL VIEWER SCRIPT (INJECTED ONCE)                     */
/* ========================================================================== */
if (!function_exists('ppb_kemahasiswaan_footer_modal')) {
function ppb_kemahasiswaan_footer_modal() {
    ?>
    <!-- Universal Drive Modal Viewer -->
    <div id="ppb-drive-modal-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.75);backdrop-filter:blur(6px);z-index:9999999;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#ffffff;border-radius:8px;width:100%;max-width:880px;height:90vh;max-height:850px;display:flex;flex-direction:column;overflow:hidden;box-shadow:0 25px 50px -12px rgba(0,0,0,0.5);border:1px solid rgba(255,255,255,0.2);">
            
            <!-- Modal Header -->
            <div style="background:#0D3B22;color:#ffffff;padding:14px 20px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
                <div style="min-width:0;padding-right:12px;">
                    <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#F5B82E;display:block;">Pratinjau Dokumen Digital</span>
                    <h3 id="ppb-drive-modal-title" style="font-family:'Fraunces',Georgia,serif;font-size:16px;font-weight:700;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:#ffffff;">
                        Dokumen Prestasi
                    </h3>
                </div>
                <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
                    <a id="ppb-drive-modal-external" href="#" target="_blank" rel="noopener" class="button button-small" style="background:#F5B82E;color:#0D3B22;border:none;font-weight:700;border-radius:6px;padding:4px 10px;text-decoration:none;">
                        Buka di Tab Baru ↗
                    </a>
                    <button type="button" onclick="window.ppbCloseDriveModal()" style="background:rgba(255,255,255,0.15);border:none;color:#ffffff;width:32px;height:32px;border-radius:50%;cursor:pointer;font-size:18px;display:flex;align-items:center;justify-content:center;line-height:1;">
                        ✕
                    </button>
                </div>
            </div>

            <!-- Modal Iframe Body -->
            <div style="flex:1;background:#f8fafc;position:relative;">
                <iframe id="ppb-drive-modal-iframe" src="" style="width:100%;height:100%;border:none;" allow="autoplay"></iframe>
            </div>

            <!-- Modal Footer Info -->
            <div style="background:#ffffff;padding:10px 20px;border-top:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;font-size:11.5px;color:#64748B;">
                <span id="ppb-drive-modal-ket">Dokumen resmi Jurusan PPB FIP UNM</span>
                <button type="button" onclick="window.ppbCloseDriveModal()" style="background:none;border:none;color:#0D3B22;font-weight:700;cursor:pointer;padding:4px 8px;">
                    Tutup Pratinjau
                </button>
            </div>
        </div>
    </div>

    <script>
    (function() {
        const overlay = document.getElementById('ppb-drive-modal-overlay');
        const titleEl = document.getElementById('ppb-drive-modal-title');
        const iframeEl = document.getElementById('ppb-drive-modal-iframe');
        const extLink = document.getElementById('ppb-drive-modal-external');
        const ketEl = document.getElementById('ppb-drive-modal-ket');

        window.ppbOpenDriveModal = function(title, driveUrl, ket) {
            if (!overlay || !iframeEl) return;

            let previewUrl = driveUrl;
            if (driveUrl.includes('drive.google.com/file/d/')) {
                const match = driveUrl.match(/\/d\/([a-zA-Z0-9_-]+)/);
                if (match && match[1]) {
                    previewUrl = 'https://drive.google.com/file/d/' + match[1] + '/preview';
                }
            }

            titleEl.textContent = title || 'Pratinjau Dokumen';
            iframeEl.src = previewUrl;
            extLink.href = driveUrl;
            ketEl.textContent = ket || 'Dokumen resmi tersimpan di repositori Google Drive Jurusan PPB FIP UNM';

            overlay.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        };

        window.ppbCloseDriveModal = function() {
            if (!overlay || !iframeEl) return;
            overlay.style.display = 'none';
            iframeEl.src = '';
            document.body.style.overflow = '';
        };

        if (overlay) {
            overlay.addEventListener('click', function(e) {
                if (e.target === overlay) {
                    window.ppbCloseDriveModal();
                }
            });
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && overlay && overlay.style.display === 'flex') {
                window.ppbCloseDriveModal();
            }
        });
    })();
    </script>
    <?php
}
}
add_action('wp_footer', 'ppb_kemahasiswaan_footer_modal');

/* ========================================================================== */
/* 7. REDIRECT /akademik/statistik/ TO /kemahasiswaan/statistik/              */
/* ========================================================================== */
add_action('template_redirect', function() {
    if (is_page(31) || (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/akademik/statistik') !== false)) {
        wp_redirect(home_url('/kemahasiswaan/statistik/'), 301);
        exit;
    }
});

/* ========================================================================== */
/* 8. SHORTCODE: STATISTIK PENERIMAAN MABA, PEMINAT & DAYA TAMPUNG TAHUNAN    */
/* ========================================================================== */
if (!function_exists('ppb_shortcode_maba_statistik')) {
function ppb_shortcode_maba_statistik($atts = []) {
    $maba       = function_exists('ppb_get_maba_statistik_data') ? ppb_get_maba_statistik_data() : [];
    $portal_url = $maba['portal_url'] ?? 'https://penerimaan.unm.ac.id/';
    $unm_url    = $maba['unm_url'] ?? 'https://unm.ac.id/';
    $jalur_list = $maba['jalur'] ?? [];
    $data_tren  = $maba['tren'] ?? [];
    $themes = [
        'green' => ['bg' => '#E8F0EA', 'fg' => '#0D3B22'],
        'gold'  => ['bg' => '#FEF3C7', 'fg' => '#92400E'],
        'blue'  => ['bg' => '#E0E7FF', 'fg' => '#3730A3'],
    ];
    ob_start();
    ?>
    <section class="ppb-maba-wrapper" style="font-family:'Plus Jakarta Sans',sans-serif;color:#1F2937;margin:30px 0;">
        
        <!-- Header & Pendaftaran Banner -->
        <div style="background:#0D3B22;color:#ffffff;border-radius:20px;padding:30px;margin-bottom:30px;box-shadow:0 8px 25px rgba(13,59,34,0.15);">
            <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(245,184,46,0.2);color:#F5B82E;padding:4px 14px;border-radius:999px;font-size:11px;font-weight:700;text-transform:uppercase;margin-bottom:12px;border:1px solid rgba(245,184,46,0.35);">
                <span>Kriteria 3 LAMDIK: Penerimaan &amp; Seleksi Mahasiswa</span>
            </div>
            <h2 style="font-family:'Fraunces',Georgia,serif;font-size:clamp(22px, 3vw, 30px);color:#ffffff;margin:0 0 10px;font-weight:600;">
                Data Peminat, Daya Tampung &amp; Penerimaan Mahasiswa Baru
            </h2>
            <p style="color:rgba(255,255,255,0.85);font-size:14px;max-width:760px;margin:0 0 20px;line-height:1.6;">
                Program Studi Bimbingan dan Konseling FIP UNM menerima mahasiswa baru melalui jalur seleksi nasional dan mandiri terpusat di Universitas Negeri Makassar.
            </p>
            <div style="display:flex;flex-wrap:wrap;gap:12px;">
                <?php if ($portal_url !== '') : ?>
                    <a href="<?php echo esc_url($portal_url); ?>" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;gap:6px;background:#F5B82E;color:#0D3B22;font-size:13px;font-weight:700;padding:10px 18px;border-radius:10px;text-decoration:none;">
                        Portal Penerimaan UNM &rarr;
                    </a>
                <?php endif; ?>
                <?php if ($unm_url !== '') : ?>
                    <a href="<?php echo esc_url($unm_url); ?>" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,0.12);color:#ffffff;font-size:13px;font-weight:600;padding:10px 18px;border-radius:10px;text-decoration:none;border:1px solid rgba(255,255,255,0.25);">
                        Website Resmi UNM
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Kartu Jalur Penerimaan -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:20px;margin-bottom:35px;">
            <?php foreach ($jalur_list as $jl) :
                $tm = $themes[$jl['tema'] ?? 'green'] ?? $themes['green'];
            ?>
                <div style="background:#ffffff;border:1px solid #D4E3D8;border-radius:16px;padding:22px;box-shadow:0 4px 15px rgba(0,0,0,0.03);">
                    <span style="font-size:11px;font-weight:700;background:<?php echo esc_attr($tm['bg']); ?>;color:<?php echo esc_attr($tm['fg']); ?>;padding:3px 10px;border-radius:6px;display:inline-block;margin-bottom:8px;"><?php echo esc_html($jl['badge'] ?? 'Jalur Seleksi'); ?></span>
                    <h4 style="font-size:17px;font-weight:700;color:#0D3B22;margin:0 0 8px;"><?php echo esc_html($jl['judul'] ?? ''); ?></h4>
                    <p style="font-size:13px;color:#4B5563;line-height:1.5;margin:0;"><?php echo esc_html($jl['deskripsi'] ?? ''); ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Tabel Tren 5 Tahun: Peminat vs Daya Tampung -->
        <div style="background:#ffffff;border:1px solid #E5E7EB;border-radius:16px;overflow:hidden;box-shadow:0 4px 15px rgba(0,0,0,0.04);">
            <div style="background:#FAF8F5;border-bottom:1px solid #E5E7EB;padding:16px 22px;">
                <h3 style="font-family:'Fraunces',Georgia,serif;font-size:18px;color:#0D3B22;margin:0 0 4px;">
                    Tabel Tren Peminat vs. Daya Tampung <?php echo count($data_tren); ?> Tahun Terakhir
                </h3>
                <span style="font-size:12px;color:#6B7280;">Sumber: Data Resmi Penerimaan Mahasiswa Baru Bimbingan dan Konseling FIP UNM</span>
            </div>
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:13px;text-align:left;">
                    <thead>
                        <tr style="background:#0D3B22;color:#ffffff;font-size:12px;text-transform:uppercase;">
                            <th style="padding:12px 18px;">Tahun Masuk</th>
                            <th style="padding:12px 18px;text-align:center;">Daya Tampung</th>
                            <th style="padding:12px 18px;text-align:center;">Peminat SNBP</th>
                            <th style="padding:12px 18px;text-align:center;">Peminat SNBT</th>
                            <th style="padding:12px 18px;text-align:center;">Peminat Mandiri</th>
                            <th style="padding:12px 18px;text-align:center;">Total Peminat</th>
                            <th style="padding:12px 18px;text-align:center;">Rasio Keketatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data_tren as $d) : ?>
                            <tr style="border-bottom:1px solid #F3F4F6;">
                                <td style="padding:12px 18px;font-weight:700;color:#0D3B22;"><?php echo esc_html($d['tahun'] ?? ''); ?></td>
                                <td style="padding:12px 18px;text-align:center;font-weight:600;"><?php echo (int) ($d['daya_tampung'] ?? 0); ?></td>
                                <td style="padding:12px 18px;text-align:center;color:#4B5563;"><?php echo number_format((int) ($d['peminat_snbp'] ?? 0)); ?></td>
                                <td style="padding:12px 18px;text-align:center;color:#4B5563;"><?php echo number_format((int) ($d['peminat_snbt'] ?? 0)); ?></td>
                                <td style="padding:12px 18px;text-align:center;color:#4B5563;"><?php echo number_format((int) ($d['peminat_mandiri'] ?? 0)); ?></td>
                                <td style="padding:12px 18px;text-align:center;font-weight:700;color:#0D3B22;"><?php echo number_format((int) ($d['total_peminat'] ?? 0)); ?></td>
                                <td style="padding:12px 18px;text-align:center;">
                                    <span style="background:#DCFCE7;color:#166534;font-size:11.5px;font-weight:700;padding:3px 10px;border-radius:6px;"><?php echo esc_html($d['keketatan'] ?? '-'); ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </section>
    <?php
    return ob_get_clean();
}
}
add_shortcode('ppb_maba_statistik', 'ppb_shortcode_maba_statistik');

/* ========================================================================== */
/* 9. SHORTCODE: DOKUMENTASI PAMERAN LAYANAN MBKM & PROYEK KEMANUSIAAN 2023   */
/* ========================================================================== */
if (!function_exists('ppb_shortcode_mbkm_kemanusiaan')) {
function ppb_shortcode_mbkm_kemanusiaan($atts = []) {
    $data       = function_exists('ppb_get_mbkm_kemanusiaan_data') ? ppb_get_mbkm_kemanusiaan_data() : [];
    $mbkm_items = $data['mbkm_items'] ?? [];
    $pk         = $data['proyek_kemanusiaan'] ?? [];
    $pk_dok     = !empty($pk['dok_url']) && function_exists('ppb_clean_document_url')
        ? ppb_clean_document_url($pk['dok_url'])
        : trim((string) ($pk['dok_url'] ?? ''));
    ob_start();
    ?>
    <section class="ppb-mbkm-wrapper" style="font-family:'Plus Jakarta Sans',sans-serif;color:#1F2937;margin:30px 0;">
        
        <div style="background:#0D3B22;color:#ffffff;border-radius:20px;padding:30px;margin-bottom:30px;box-shadow:0 8px 25px rgba(13,59,34,0.15);">
            <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(245,184,46,0.2);color:#F5B82E;padding:4px 14px;border-radius:999px;font-size:11px;font-weight:700;text-transform:uppercase;margin-bottom:12px;border:1px solid rgba(245,184,46,0.35);">
                <span>Kriteria 3 LAMDIK: Inovasi &amp; Pengabdian Mahasiswa</span>
            </div>
            <h2 style="font-family:'Fraunces',Georgia,serif;font-size:clamp(22px, 3vw, 30px);color:#ffffff;margin:0 0 10px;font-weight:600;">
                Pameran Layanan MBKM &amp; Proyek Kemanusiaan
            </h2>
            <p style="color:rgba(255,255,255,0.85);font-size:14px;max-width:760px;margin:0;line-height:1.6;">
                Dokumentasi rekaman jejak diseminasi inovasi bimbingan konseling karya mahasiswa Program Merdeka Belajar Kampus Merdeka dan aksi nyata Proyek Kemanusiaan penanganan trauma psikososial.
            </p>
        </div>

        <!-- Bagian 1: Pameran Layanan MBKM -->
        <h3 style="font-family:'Fraunces',Georgia,serif;font-size:22px;color:#0D3B22;margin:0 0 18px;">
            Pameran Inovasi Layanan BK Mahasiswa MBKM
        </h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(320px, 1fr));gap:20px;margin-bottom:40px;">
            <?php foreach ($mbkm_items as $mk) :
                $dok_url = !empty($mk['dok_url']) && function_exists('ppb_clean_document_url')
                    ? ppb_clean_document_url($mk['dok_url'])
                    : trim((string) ($mk['dok_url'] ?? ''));
            ?>
                <div style="background:#ffffff;border:1px solid #D4E3D8;border-radius:16px;padding:22px;box-shadow:0 4px 15px rgba(0,0,0,0.03);display:flex;flex-direction:column;justify-content:space-between;">
                    <div>
                        <div style="display:flex;justify-content:space-between;gap:8px;margin-bottom:8px;flex-wrap:wrap;">
                            <span style="font-size:11px;font-weight:700;background:#E8F0EA;color:#0D3B22;padding:3px 8px;border-radius:4px;"><?php echo esc_html($mk['tahun'] ?? ''); ?></span>
                            <span style="font-size:12px;color:#059669;font-weight:600;"><?php echo esc_html($mk['lokasi'] ?? ''); ?></span>
                        </div>
                        <h4 style="font-size:16px;font-weight:700;color:#0D3B22;margin:0 0 8px;"><?php echo esc_html($mk['judul'] ?? ''); ?></h4>
                        <p style="font-size:13px;color:#4B5563;line-height:1.5;margin:0 0 12px;"><?php echo esc_html($mk['deskripsi'] ?? ''); ?></p>
                    </div>
                    <div>
                        <?php if (!empty($mk['catatan'])) : ?>
                            <div style="font-size:12px;color:#6B7280;margin-bottom:<?php echo $dok_url !== '' ? '10px' : '0'; ?>;"><?php echo esc_html($mk['catatan']); ?></div>
                        <?php endif; ?>
                        <?php if ($dok_url !== '') : ?>
                            <a href="<?php echo esc_url($dok_url); ?>" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;gap:6px;background:#E8F0EA;color:#0D3B22;border:1px solid #B7D0BE;font-size:12px;font-weight:700;padding:7px 12px;border-radius:8px;text-decoration:none;">📂 Dokumentasi / Eviden &#8599;</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Bagian 2: Proyek Kemanusiaan -->
        <h3 style="font-family:'Fraunces',Georgia,serif;font-size:22px;color:#0D3B22;margin:0 0 18px;">
            Proyek Kemanusiaan Mahasiswa BK
        </h3>
        <div style="background:#FAF8F5;border:1.5px solid #D4E3D8;border-radius:18px;padding:26px;">
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(300px, 1fr));gap:24px;">
                <div>
                    <span style="background:#FEE2E2;color:#991B1B;font-size:11px;font-weight:700;padding:3px 10px;border-radius:6px;display:inline-block;margin-bottom:8px;"><?php echo esc_html($pk['badge'] ?? 'Program Unggulan Kemanusiaan'); ?></span>
                    <h4 style="font-size:18px;font-weight:700;color:#0D3B22;margin:0 0 10px;"><?php echo esc_html($pk['judul'] ?? ''); ?></h4>
                    <p style="font-size:13.5px;color:#4B5563;line-height:1.6;margin:0 0 16px;">
                        <?php echo esc_html($pk['deskripsi'] ?? ''); ?>
                    </p>
                    <?php if (!empty($pk['poin_list']) && is_array($pk['poin_list'])) : ?>
                        <ul style="font-size:13px;color:#374151;line-height:1.7;padding-left:18px;margin:0 0 14px;">
                            <?php foreach ($pk['poin_list'] as $poin) : ?>
                                <li><?php echo esc_html($poin); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <?php if ($pk_dok !== '') : ?>
                        <a href="<?php echo esc_url($pk_dok); ?>" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;gap:6px;background:#0D3B22;color:#F5B82E;font-size:12.5px;font-weight:700;padding:9px 16px;border-radius:10px;text-decoration:none;">📄 Unduh Laporan &amp; Dokumentasi Kemanusiaan &#8599;</a>
                    <?php endif; ?>
                </div>
                <div style="background:#ffffff;border:1px solid #E5E7EB;border-radius:14px;padding:20px;">
                    <h5 style="font-size:15px;font-weight:700;color:#0D3B22;margin:0 0 10px;">Capaian &amp; Dampak Program Kemanusiaan:</h5>
                    <div style="display:flex;flex-direction:column;gap:10px;font-size:13px;color:#4B5563;">
                        <?php foreach ($pk['capaian_list'] ?? [] as $cp) : ?>
                            <div style="padding:10px;background:#F9FAFB;border-radius:8px;border-left:3px solid #0D3B22;">
                                <strong style="color:#0D3B22;"><?php echo esc_html($cp['label'] ?? ''); ?></strong> <?php echo esc_html($cp['detail'] ?? ''); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

    </section>
    <?php
    return ob_get_clean();
}
}
add_shortcode('ppb_mbkm_kemanusiaan', 'ppb_shortcode_mbkm_kemanusiaan');

/* ========================================================================== */
/* 12. SYNC FRONTEND /kemahasiswaan/statistik/ DARI SETTINGS ADMIN           */
/* ========================================================================== */
if (!function_exists('ppb_kemahasiswaan_statistik_sync_content')) {
function ppb_kemahasiswaan_statistik_sync_content($content) {
    if (is_admin() || !is_string($content) || strpos($content, 'bk-page-wrapper') === false) {
        return $content;
    }

    $slug_path = function_exists('ppb_get_current_page_slug_path') ? ppb_get_current_page_slug_path() : '';
    if ($slug_path === 'akademik/statistik') {
        return $content;
    }

    $is_km_stat = ($slug_path === 'kemahasiswaan/statistik' || strpos($content, 'Total 468 Mhs') !== false);
    if (!$is_km_stat) {
        return $content;
    }

    $total_aktif = trim((string) get_option('ppb_km_total_aktif', '542'));
    $lulus_tw    = trim((string) get_option('ppb_km_lulus_tepat_waktu', '88.4'));
    $persen_3t   = trim((string) get_option('ppb_km_persen_3t', '24.8'));
    $rasio       = trim((string) get_option('ppb_km_rasio_dosen', '1 : 28'));

    if ($total_aktif !== '') {
        $content = str_replace('Total 468 Mhs', 'Total ' . esc_html($total_aktif) . ' Mhs', $content);
    }
    if ($lulus_tw !== '') {
        $clean_lulus = rtrim($lulus_tw, " %");
        $content = str_replace('86.4%', esc_html($clean_lulus) . '%', $content);
    }
    if ($rasio !== '') {
        $content = str_replace('1 : 24', esc_html($rasio), $content);
    }
    if ($persen_3t !== '') {
        $clean_3t = rtrim($persen_3t, " %");
        $content = str_replace('>18.6%<', '>' . esc_html($clean_3t) . '%<', $content);
    }

    return $content;
}
}
add_filter('the_content', 'ppb_kemahasiswaan_statistik_sync_content', 12);


