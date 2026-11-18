<?php
/**
 * Module: PPB FIP UNM - Manajemen Akademik & Jadwal Ujian
 * Description: Modul WP Admin untuk pengelolaan Jadwal Seminar & Ujian Skripsi serta konfigurasi Tautan Dokumen Kurikulum tanpa menyentuh kode HTML atau Elementor.
 * Author: Jurusan PPB FIP UNM
 */

if (!defined('ABSPATH')) exit;

/* ========================================================================== */
/* 1. REGISTRASI CUSTOM POST TYPE: JADWAL SEMINAR & UJIAN (ppb_jadwal)        */
/* ========================================================================== */
if (!function_exists('ppb_register_cpt_jadwal')) {
function ppb_register_cpt_jadwal() {
    $labels = [
        'name'               => 'Jadwal Seminar & Ujian',
        'singular_name'      => 'Jadwal Ujian',
        'menu_name'          => 'Jadwal Ujian',
        'name_admin_bar'     => 'Jadwal Ujian',
        'add_new'            => 'Tambah Sesi Baru',
        'add_new_item'       => 'Tambah Jadwal Seminar / Ujian Baru',
        'new_item'           => 'Jadwal Baru',
        'edit_item'          => 'Edit Jadwal Seminar / Ujian',
        'view_item'          => 'Lihat Jadwal',
        'all_items'          => 'Semua Jadwal',
        'search_items'       => 'Cari Jadwal (Nama / NIM / Judul)',
        'not_found'          => 'Belum ada jadwal seminar atau ujian.',
        'not_found_in_trash' => 'Tidak ada jadwal di tempat sampah.'
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
        'menu_position'      => 23,
        'menu_icon'          => 'dashicons-calendar-alt',
        'supports'           => ['title'], // Judul otomatis disimpan sebagai Nama Mahasiswa - Jenis Ujian
    ];

    register_post_type('ppb_jadwal', $args);
}
}
add_action('init', 'ppb_register_cpt_jadwal');

/* ========================================================================== */
/* 2. META BOX: DETAIL JADWAL SEMINAR & UJIAN                                 */
/* ========================================================================== */
if (!function_exists('ppb_jadwal_add_meta_box')) {
function ppb_jadwal_add_meta_box() {
    add_meta_box(
        'ppb_jadwal_meta_box',
        'Detail Pelaksanaan Seminar & Ujian Skripsi',
        'ppb_jadwal_render_meta_box',
        'ppb_jadwal',
        'normal',
        'high'
    );
}
}
add_action('add_meta_boxes', 'ppb_jadwal_add_meta_box');

if (!function_exists('ppb_jadwal_render_meta_box')) {
function ppb_jadwal_render_meta_box($post) {
    wp_nonce_field('ppb_jadwal_save_meta', 'ppb_jadwal_meta_nonce');

    $jenis_ujian      = get_post_meta($post->ID, '_ppb_jenis_ujian', true) ?: 'seminar_proposal';
    $status           = get_post_meta($post->ID, '_ppb_status', true) ?: 'terjadwal';
    $nama_mahasiswa   = get_post_meta($post->ID, '_ppb_nama_mahasiswa', true);
    $nim              = get_post_meta($post->ID, '_ppb_nim', true);
    $angkatan         = get_post_meta($post->ID, '_ppb_angkatan', true) ?: '2022';
    $judul_penelitian = get_post_meta($post->ID, '_ppb_judul_penelitian', true);
    $tanggal          = get_post_meta($post->ID, '_ppb_tanggal', true);
    $jam_mulai        = get_post_meta($post->ID, '_ppb_jam_mulai', true) ?: '08:30';
    $jam_selesai      = get_post_meta($post->ID, '_ppb_jam_selesai', true) ?: '10:00';
    $ruangan          = get_post_meta($post->ID, '_ppb_ruangan', true) ?: 'Ruang Sidang FIP UNM Lt. 2 (Offline)';
    $link_daring      = get_post_meta($post->ID, '_ppb_link_daring', true);
    $pembimbing_1     = get_post_meta($post->ID, '_ppb_pembimbing_1', true);
    $pembimbing_2     = get_post_meta($post->ID, '_ppb_pembimbing_2', true);
    $penguji_1        = get_post_meta($post->ID, '_ppb_penguji_1', true);
    $penguji_2        = get_post_meta($post->ID, '_ppb_penguji_2', true);
    ?>
    <style>
        .ppb-jadwal-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
        .ppb-jadwal-full { grid-column: span 2; }
        .ppb-jadwal-field label { display: block; font-weight: 600; margin-bottom: 5px; color: #1e293b; font-size: 13px; }
        .ppb-jadwal-field input, .ppb-jadwal-field select, .ppb-jadwal-field textarea {
            width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; background: #fff;
        }
        .ppb-jadwal-field input:focus, .ppb-jadwal-field select:focus, .ppb-jadwal-field textarea:focus {
            border-color: #0D3B22; outline: none; box-shadow: 0 0 0 2px rgba(13,59,34,0.15);
        }
        .ppb-badge-guide { background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 12px; margin-top: 10px; font-size: 12px; color: #64748b; }
    </style>

    <div class="ppb-jadwal-grid">
        <div class="ppb-jadwal-field">
            <label for="ppb_jenis_ujian">🎓 Tahapan Seminar / Ujian:</label>
            <select name="ppb_jenis_ujian" id="ppb_jenis_ujian" required>
                <option value="seminar_proposal" <?php selected($jenis_ujian, 'seminar_proposal'); ?>>Seminar Proposal (Sempro)</option>
                <option value="seminar_hasil" <?php selected($jenis_ujian, 'seminar_hasil'); ?>>Seminar Hasil Penelitian (Semhas)</option>
                <option value="ujian_skripsi" <?php selected($jenis_ujian, 'ujian_skripsi'); ?>>Ujian Skripsi (Ujian Tutup / Sarjana)</option>
            </select>
        </div>

        <div class="ppb-jadwal-field">
            <label for="ppb_status">⚡ Status Pelaksanaan:</label>
            <select name="ppb_status" id="ppb_status" required>
                <option value="terjadwal" <?php selected($status, 'terjadwal'); ?>>🟢 Terjadwal (Akan Datang)</option>
                <option value="sedang_berlangsung" <?php selected($status, 'sedang_berlangsung'); ?>>🔴 Sedang Berlangsung (Live)</option>
                <option value="selesai" <?php selected($status, 'selesai'); ?>>⚪ Selesai (Arsip)</option>
            </select>
        </div>

        <div class="ppb-jadwal-field">
            <label for="ppb_nama_mahasiswa">👤 Nama Lengkap Mahasiswa:</label>
            <input type="text" name="ppb_nama_mahasiswa" id="ppb_nama_mahasiswa" value="<?php echo esc_attr($nama_mahasiswa); ?>" placeholder="Contoh: Nurul Aulia Rahmah" required />
        </div>

        <div class="ppb-jadwal-field">
            <label for="ppb_nim">🔢 Nomor Induk Mahasiswa (NIM):</label>
            <input type="text" name="ppb_nim" id="ppb_nim" value="<?php echo esc_attr($nim); ?>" placeholder="Contoh: 220401501012" required />
        </div>

        <div class="ppb-jadwal-field">
            <label for="ppb_angkatan">📅 Angkatan Masuk:</label>
            <input type="number" name="ppb_angkatan" id="ppb_angkatan" value="<?php echo esc_attr($angkatan); ?>" placeholder="2022" />
        </div>

        <div class="ppb-jadwal-field">
            <label for="ppb_tanggal">🗓️ Tanggal Ujian:</label>
            <input type="date" name="ppb_tanggal" id="ppb_tanggal" value="<?php echo esc_attr($tanggal); ?>" required />
        </div>

        <div class="ppb-jadwal-field">
            <label for="ppb_jam_mulai">⏰ Waktu Mulai (WITA):</label>
            <input type="text" name="ppb_jam_mulai" id="ppb_jam_mulai" value="<?php echo esc_attr($jam_mulai); ?>" placeholder="08:30" required />
        </div>

        <div class="ppb-jadwal-field">
            <label for="ppb_jam_selesai">⏰ Waktu Selesai (WITA):</label>
            <input type="text" name="ppb_jam_selesai" id="ppb_jam_selesai" value="<?php echo esc_attr($jam_selesai); ?>" placeholder="10:00" required />
        </div>

        <div class="ppb-jadwal-field ppb-jadwal-full">
            <label for="ppb_judul_penelitian">📖 Judul Penelitian / Skripsi:</label>
            <textarea name="ppb_judul_penelitian" id="ppb_judul_penelitian" rows="3" placeholder="Masukkan judul penelitian lengkap mahasiswa..." required><?php echo esc_textarea($judul_penelitian); ?></textarea>
        </div>

        <div class="ppb-jadwal-field">
            <label for="ppb_ruangan">📍 Ruangan / Lokasi Ujian:</label>
            <input type="text" name="ppb_ruangan" id="ppb_ruangan" value="<?php echo esc_attr($ruangan); ?>" placeholder="Ruang Sidang FIP UNM Lt. 2 (Offline)" required />
        </div>

        <div class="ppb-jadwal-field">
            <label for="ppb_link_daring">🌐 Tautan Daring (Zoom / GMeet - Opsional):</label>
            <input type="url" name="ppb_link_daring" id="ppb_link_daring" value="<?php echo esc_attr($link_daring); ?>" placeholder="https://zoom.us/j/9847123456" />
        </div>

        <div class="ppb-jadwal-field">
            <label for="ppb_pembimbing_1">👨‍🏫 Pembimbing 1 (Utama):</label>
            <input type="text" name="ppb_pembimbing_1" id="ppb_pembimbing_1" value="<?php echo esc_attr($pembimbing_1); ?>" placeholder="Prof. Dr. Farida Aryani, M.Pd." required />
        </div>

        <div class="ppb-jadwal-field">
            <label for="ppb_pembimbing_2">👨‍🏫 Pembimbing 2 (Pendamping):</label>
            <input type="text" name="ppb_pembimbing_2" id="ppb_pembimbing_2" value="<?php echo esc_attr($pembimbing_2); ?>" placeholder="Dr. Abdul Saman, M.Si., Kons." required />
        </div>

        <div class="ppb-jadwal-field">
            <label for="ppb_penguji_1">👨‍⚖️ Penguji 1:</label>
            <input type="text" name="ppb_penguji_1" id="ppb_penguji_1" value="<?php echo esc_attr($penguji_1); ?>" placeholder="Prof. Dr. H. Alimuddin Mahmud, M.Pd." required />
        </div>

        <div class="ppb-jadwal-field">
            <label for="ppb_penguji_2">👨‍⚖️ Penguji 2:</label>
            <input type="text" name="ppb_penguji_2" id="ppb_penguji_2" value="<?php echo esc_attr($penguji_2); ?>" placeholder="Dr. Suciani Latif, M.Pd." required />
        </div>
    </div>

    <div class="ppb-badge-guide">
        💡 <strong>Catatan Admin:</strong> Setelah disimpan, jadwal ini akan langsung tayang pada halaman publik <code>/akademik/jadwal-ujian/</code> secara otomatis dan dapat difilter oleh mahasiswa maupun dosen penguji.
    </div>
    <?php
}
}

if (!function_exists('ppb_jadwal_save_meta_data')) {
function ppb_jadwal_save_meta_data($post_id) {
    if (!isset($_POST['ppb_jadwal_meta_nonce']) || !wp_verify_nonce($_POST['ppb_jadwal_meta_nonce'], 'ppb_jadwal_save_meta')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $fields = [
        'ppb_jenis_ujian'      => '_ppb_jenis_ujian',
        'ppb_status'           => '_ppb_status',
        'ppb_nama_mahasiswa'   => '_ppb_nama_mahasiswa',
        'ppb_nim'              => '_ppb_nim',
        'ppb_angkatan'         => '_ppb_angkatan',
        'ppb_judul_penelitian' => '_ppb_judul_penelitian',
        'ppb_tanggal'          => '_ppb_tanggal',
        'ppb_jam_mulai'        => '_ppb_jam_mulai',
        'ppb_jam_selesai'      => '_ppb_jam_selesai',
        'ppb_ruangan'          => '_ppb_ruangan',
        'ppb_link_daring'      => '_ppb_link_daring',
        'ppb_pembimbing_1'     => '_ppb_pembimbing_1',
        'ppb_pembimbing_2'     => '_ppb_pembimbing_2',
        'ppb_penguji_1'        => '_ppb_penguji_1',
        'ppb_penguji_2'        => '_ppb_penguji_2',
    ];

    foreach ($fields as $post_key => $meta_key) {
        if (isset($_POST[$post_key])) {
            $val = ($post_key === 'ppb_link_daring') ? esc_url_raw($_POST[$post_key]) : sanitize_text_field($_POST[$post_key]);
            if ($post_key === 'ppb_judul_penelitian') {
                $val = sanitize_textarea_field($_POST[$post_key]);
            }
            update_post_meta($post_id, $meta_key, $val);
        }
    }

    // Auto update title to "Nama Mahasiswa - Jenis Ujian" if title is empty or default
    if (!empty($_POST['ppb_nama_mahasiswa'])) {
        $jenis_labels = [
            'seminar_proposal' => 'Seminar Proposal',
            'seminar_hasil'    => 'Seminar Hasil',
            'ujian_skripsi'    => 'Ujian Skripsi'
        ];
        $jenis_txt = isset($jenis_labels[$_POST['ppb_jenis_ujian']]) ? $jenis_labels[$_POST['ppb_jenis_ujian']] : 'Seminar';
        $new_title = sanitize_text_field($_POST['ppb_nama_mahasiswa']) . ' (' . $jenis_txt . ')';

        // Unhook to avoid infinite loop
        remove_action('save_post_ppb_jadwal', 'ppb_jadwal_save_meta_data');
        wp_update_post([
            'ID' => $post_id,
            'post_title' => $new_title,
        ]);
        add_action('save_post_ppb_jadwal', 'ppb_jadwal_save_meta_data');
    }

    // Always invalidate Elementor cache on schedule page so fresh data appears immediately
    delete_post_meta(30, '_elementor_element_cache');
    if (class_exists('\\Elementor\\Plugin')) {
        \Elementor\Plugin::$instance->files_manager->clear_cache();
    }
}
}
add_action('save_post_ppb_jadwal', 'ppb_jadwal_save_meta_data');

/* ========================================================================== */
/* 3. KUSTOMISASI KOLOM TABEL ADMIN PPB_JADWAL                                */
/* ========================================================================== */
if (!function_exists('ppb_jadwal_custom_columns')) {
function ppb_jadwal_custom_columns($columns) {
    $new_columns = [];
    $new_columns['cb']          = $columns['cb'];
    $new_columns['mahasiswa']   = 'Mahasiswa & NIM';
    $new_columns['jenis_ujian'] = 'Jenis Ujian';
    $new_columns['judul']       = 'Judul Penelitian';
    $new_columns['jadwal']      = 'Waktu & Ruang';
    $new_columns['penguji']     = 'Pembimbing & Penguji';
    $new_columns['status']      = 'Status';
    return $new_columns;
}
}
add_filter('manage_ppb_jadwal_posts_columns', 'ppb_jadwal_custom_columns');

if (!function_exists('ppb_jadwal_render_custom_columns')) {
function ppb_jadwal_render_custom_columns($column, $post_id) {
    switch ($column) {
        case 'mahasiswa':
            $nama = get_post_meta($post_id, '_ppb_nama_mahasiswa', true) ?: get_the_title($post_id);
            $nim  = get_post_meta($post_id, '_ppb_nim', true) ?: '-';
            echo '<strong>' . esc_html($nama) . '</strong><br><span style="color:#64748b;font-family:monospace;font-size:11px;">NIM: ' . esc_html($nim) . '</span>';
            break;

        case 'jenis_ujian':
            $jenis = get_post_meta($post_id, '_ppb_jenis_ujian', true);
            if ($jenis === 'seminar_proposal') {
                echo '<span style="display:inline-block;padding:3px 8px;border-radius:6px;background:#eff6ff;color:#1e40af;font-weight:700;font-size:11px;border:1px solid #bfdbfe;">Seminar Proposal</span>';
            } elseif ($jenis === 'seminar_hasil') {
                echo '<span style="display:inline-block;padding:3px 8px;border-radius:6px;background:#fffbeb;color:#92400e;font-weight:700;font-size:11px;border:1px solid #fde68a;">Seminar Hasil</span>';
            } else {
                echo '<span style="display:inline-block;padding:3px 8px;border-radius:6px;background:#faf5ff;color:#6b21a8;font-weight:700;font-size:11px;border:1px solid #e9d5ff;">Ujian Skripsi</span>';
            }
            break;

        case 'judul':
            $judul = get_post_meta($post_id, '_ppb_judul_penelitian', true);
            echo '<span style="font-size:12px;color:#334155;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">' . esc_html($judul) . '</span>';
            break;

        case 'jadwal':
            $tgl = get_post_meta($post_id, '_ppb_tanggal', true);
            $mulai = get_post_meta($post_id, '_ppb_jam_mulai', true);
            $selesai = get_post_meta($post_id, '_ppb_jam_selesai', true);
            $ruang = get_post_meta($post_id, '_ppb_ruangan', true);
            $link = get_post_meta($post_id, '_ppb_link_daring', true);

            $formatted_tgl = $tgl ? date_i18n('d M Y', strtotime($tgl)) : '-';
            echo '<div style="font-size:12px;font-weight:600;color:#0D3B22;">' . esc_html($formatted_tgl) . '</div>';
            echo '<div style="font-size:11px;color:#64748b;">' . esc_html($mulai) . ' - ' . esc_html($selesai) . ' WITA</div>';
            echo '<div style="font-size:11px;color:#475569;margin-top:2px;">📍 ' . esc_html($ruang) . '</div>';
            if ($link) {
                echo '<a href="' . esc_url($link) . '" target="_blank" style="font-size:10px;color:#2563eb;font-weight:600;text-decoration:none;">🔗 Link Daring ↗</a>';
            }
            break;

        case 'penguji':
            $p1 = get_post_meta($post_id, '_ppb_pembimbing_1', true);
            $u1 = get_post_meta($post_id, '_ppb_penguji_1', true);
            echo '<div style="font-size:11px;color:#475569;"><strong>P1:</strong> ' . esc_html($p1 ?: '-') . '</div>';
            echo '<div style="font-size:11px;color:#475569;"><strong>U1:</strong> ' . esc_html($u1 ?: '-') . '</div>';
            break;

        case 'status':
            $status = get_post_meta($post_id, '_ppb_status', true);
            if ($status === 'terjadwal') {
                echo '<span style="display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:12px;background:#ecfdf5;color:#065f46;font-weight:700;font-size:11px;border:1px solid #a7f3d0;"><span style="width:6px;height:6px;border-radius:50%;background:#059669;"></span> Terjadwal</span>';
            } elseif ($status === 'sedang_berlangsung') {
                echo '<span style="display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:12px;background:#fff1f2;color:#9f1239;font-weight:700;font-size:11px;border:1px solid #fecdd3;"><span style="width:6px;height:6px;border-radius:50%;background:#e11d48;"></span> Berlangsung</span>';
            } else {
                echo '<span style="display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:12px;background:#f3f4f6;color:#374151;font-weight:700;font-size:11px;border:1px solid #e5e7eb;">✓ Selesai</span>';
            }
            break;
    }
}
}
add_action('manage_ppb_jadwal_posts_custom_column', 'ppb_jadwal_render_custom_columns', 10, 2);

/* ========================================================================== */
/* 4. HALAMAN PENGATURAN TAUTAN DOKUMEN AKADEMIK & KURIKULUM                  */
/* ========================================================================== */
if (!function_exists('ppb_akademik_register_settings_page')) {
function ppb_akademik_register_settings_page() {
    add_submenu_page(
        'edit.php?post_type=ppb_jadwal',
        'Tautan Dokumen Akademik',
        'Tautan Dokumen & Drive',
        'manage_options',
        'ppb-akademik-settings',
        'ppb_akademik_render_settings_page'
    );
}
}
// Handled by ppb-portal-hub
// add_action('admin_menu', 'ppb_akademik_register_settings_page');

if (!function_exists('ppb_akademik_get_all_links')) {
function ppb_akademik_get_all_links() {
    $defs = [
        'buku_kurikulum'    => ['ppb_link_buku_kurikulum', 'https://drive.google.com/file/d/1_Buku_Kurikulum_OBE_BK_UNM/view?usp=sharing'],
        'sk_kurikulum'      => ['ppb_link_sk_kurikulum', 'https://drive.google.com/file/d/1_SK_Senat_Kurikulum_OBE/view?usp=sharing'],
        'pedoman_penilaian' => ['ppb_link_pedoman_penilaian', 'https://drive.google.com/file/d/1_Panduan_Penilaian_OBE_BK/view?usp=sharing'],
        'pedoman_skripsi'   => ['ppb_link_pedoman_skripsi', 'https://drive.google.com/drive/folders/1_Pedoman_Skripsi_BK_UNM?usp=sharing'],
        'rubrik_konseling'  => ['ppb_link_rubrik_konseling', ''],
        'rubrik_project'    => ['ppb_link_rubrik_project', ''],
        'sk_kepakaran'      => ['ppb_link_sk_kepakaran', ''],
        'moa_pendidikan'    => ['ppb_link_moa_pendidikan', ''],
        'moa_penelitian'    => ['ppb_link_moa_penelitian', ''],
        'moa_pkm'           => ['ppb_link_moa_pkm', ''],
        'roadmap_p1_sk'     => ['ppb_link_roadmap_pilar1_sk', 'https://drive.google.com/file/d/1_Pengesahan_Roadmap_Pilar1/view?usp=sharing'],
        'roadmap_p1_doc'    => ['ppb_link_roadmap_pilar1_doc', 'https://drive.google.com/file/d/1_Dokumen_Roadmap_Riset_Pilar1/view?usp=sharing'],
        'roadmap_p2_sk'     => ['ppb_link_roadmap_pilar2_sk', 'https://drive.google.com/file/d/1_Pengesahan_Roadmap_Pilar2/view?usp=sharing'],
        'roadmap_p2_doc'    => ['ppb_link_roadmap_pilar2_doc', 'https://drive.google.com/file/d/1_Dokumen_Roadmap_Riset_Pilar2/view?usp=sharing'],
        'roadmap_p3_sk'     => ['ppb_link_roadmap_pilar3_sk', 'https://drive.google.com/file/d/1_Pengesahan_Roadmap_Pilar3/view?usp=sharing'],
        'roadmap_p3_doc'    => ['ppb_link_roadmap_pilar3_doc', 'https://drive.google.com/file/d/1_Dokumen_Roadmap_Riset_Pilar3/view?usp=sharing'],
    ];

    $out = [];
    foreach ($defs as $k => $pair) {
        $opt_name = $pair[0];
        $default  = $pair[1];
        $val = get_option($opt_name, null);
        if ($val === null) {
            $val = $default;
        }
        $out[$k] = function_exists('ppb_clean_document_url') ? ppb_clean_document_url($val) : esc_url_raw((string) $val);
    }
    return $out;
}
}

if (!function_exists('ppb_get_mis_pnbp_defaults')) {
function ppb_get_mis_pnbp_defaults() {
    return [
        'riset' => [
            [
                'tahun'       => '2024',
                'skema'       => 'Penelitian Unggulan PNBP',
                'judul'       => 'Pengembangan Platform Asesmen Karier Digital Berbasis Kecerdasan Emosional Mahasiswa Generasi Z',
                'ketua'       => 'Prof. Dr. Abdullah Pandang, M.Pd',
                'anggota'     => 'Dr. Kartika Sari, M.Pd., 3 Mahasiswa',
                'dana'        => 'Rp 45.000.000',
                'luaran'      => 'Artikel Sinta 2 & Aplikasi Web HKI',
                'dokumen_url' => '',
            ],
            [
                'tahun'       => '2024',
                'skema'       => 'Penelitian Terapan PNBP',
                'judul'       => 'Model Konseling Kelompok Pendekatan Mindful Self-Compassion untuk Menurunkan Stres Akademik Mahasiswa Skripsi',
                'ketua'       => 'Dr. Farida Aryani, M.Pd',
                'anggota'     => 'Suciani Latif, S.Pd., M.Pd',
                'dana'        => 'Rp 32.500.000',
                'luaran'      => 'Modul Intervensi Ber-ISBN & Jurnal Terindeks',
                'dokumen_url' => '',
            ],
            [
                'tahun'       => '2023',
                'skema'       => 'Penelitian Dasar Keilmuan',
                'judul'       => 'Validasi Instrumen Asesmen Literasi Kesehatan Mental Siswa SMA/SMK Se-Sulawesi Selatan',
                'ketua'       => 'Dr. Muhammad Anas, M.Si',
                'anggota'     => 'Dr. Akhmad Harum, S.Pd., M.Pd',
                'dana'        => 'Rp 28.000.000',
                'luaran'      => 'Skala Psikometrik Baku & Hak Cipta',
                'dokumen_url' => '',
            ],
        ],
        'pkm' => [
            [
                'tahun'       => '2024',
                'skema'       => 'PkM Kemitraan Sekolah',
                'judul'       => 'Pelatihan Peer-Counselor Anti-Bullying & Psychological First Aid Bagi Guru BK SMA/SMK Kota Makassar',
                'ketua'       => 'Dr. Suciani Latif, S.Pd., M.Pd',
                'mitra'       => 'MGBK Kota Makassar (25 Sekolah)',
                'dana'        => 'Rp 25.000.000',
                'luaran'      => 'Panduan Anti-Bullying & Sertifikasi Peer Counselor',
                'dokumen_url' => '',
            ],
            [
                'tahun'       => '2024',
                'skema'       => 'PkM Wilayah 3T',
                'judul'       => 'Pendampingan Manajemen Bimbingan Karier Terpadu Berbasis Potensi Daerah di Kepulauan Pangkep',
                'ketua'       => 'Dr. Nur Fadhilah Umar, S.Pd., M.Pd',
                'mitra'       => 'SMAN 3 & SMKN 2 Pangkajene Kepulauan',
                'dana'        => 'Rp 22.000.000',
                'luaran'      => 'Publikasi Jurnal Pengabdian & Video Dokumentasi',
                'dokumen_url' => '',
            ],
            [
                'tahun'       => '2023',
                'skema'       => 'PkM Pemberdayaan Masyarakat',
                'judul'       => 'Workshop Kesehatan Mental Keluarga dan Pola Asuh Positif di Era Digital Bagi Orang Tua Siswa SMP',
                'ketua'       => 'Prof. Dr. Sidin Ali, M.Pd',
                'mitra'       => 'Komite Sekolah SMPN 6 Makassar',
                'dana'        => 'Rp 20.000.000',
                'luaran'      => 'Booklet Parenting Digital & Laporan PkM',
                'dokumen_url' => '',
            ],
        ],
    ];
}
}

if (!function_exists('ppb_get_mis_pnbp_data')) {
function ppb_get_mis_pnbp_data() {
    $defaults = ppb_get_mis_pnbp_defaults();
    $saved = get_option('ppb_mis_pnbp_data', null);
    if (!is_array($saved) || (!isset($saved['riset']) && !isset($saved['pkm']))) {
        return $defaults;
    }
    return [
        'riset' => (isset($saved['riset']) && is_array($saved['riset'])) ? array_values($saved['riset']) : $defaults['riset'],
        'pkm'   => (isset($saved['pkm']) && is_array($saved['pkm'])) ? array_values($saved['pkm']) : $defaults['pkm'],
    ];
}
}

if (!function_exists('ppb_get_jurnal_bk_defaults')) {
function ppb_get_jurnal_bk_defaults() {
    return [
        [
            'badge'      => '🏅 Jurnal Resmi Prodi',
            'tema'       => 'green',
            'issn'       => 'ISSN (Online): 2620-xxxx',
            'nama'       => 'Jurnal Bimbingan dan Konseling (JBK)',
            'deskripsi'  => 'Berkala ilmiah resmi Program Studi Bimbingan dan Konseling FIP UNM yang menerbitkan naskah hasil penelitian eksperimen konseling, asesmen psikologis pendidikan, dan inovasi bimbingan karier.',
            'penerbit'   => 'Prodi Bimbingan dan Konseling FIP UNM',
            'frekuensi'  => '2 Kali Setahun (Mei & November)',
            'pengindeks' => 'SINTA, Google Scholar, Garuda, Crossref DOI',
            'ojs_url'    => 'https://ojs.unm.ac.id/JKBK',
            'submit_url' => 'https://ojs.unm.ac.id/JKBK/about/submissions',
        ],
        [
            'badge'      => '🤝 Jurnal Pengabdian & Hilirisasi',
            'tema'       => 'gold',
            'issn'       => 'ISSN (Online): 2745-xxxx',
            'nama'       => 'Jurnal Pengabdian Konseling Nusantara (JPKN)',
            'deskripsi'  => 'Wadah diseminasi program pengabdian kepada masyarakat, inovasi intervensi psikoedukatif, dan kolaborasi pemberdayaan konselor di sekolah serta masyarakat luas.',
            'penerbit'   => 'Laboratorium & Prodi BK FIP UNM',
            'frekuensi'  => '2 Kali Setahun (Juni & Desember)',
            'pengindeks' => 'Google Scholar, Garuda Kemdikbud, Dimensions',
            'ojs_url'    => 'https://ojs.unm.ac.id/index',
            'submit_url' => '',
        ],
    ];
}
}

if (!function_exists('ppb_get_jurnal_bk_data')) {
function ppb_get_jurnal_bk_data() {
    $saved = get_option('ppb_jurnal_bk_data', null);
    if (!is_array($saved) || empty($saved)) {
        return ppb_get_jurnal_bk_defaults();
    }
    return array_values($saved);
}
}

if (!function_exists('ppb_render_jurnal_cards_grid_html')) {
function ppb_render_jurnal_cards_grid_html($jurnal_list = null) {
    if (!is_array($jurnal_list)) {
        $jurnal_list = ppb_get_jurnal_bk_data();
    }
    $themes = [
        'green' => ['bg' => '#DCFCE7', 'fg' => '#166534', 'bd' => '#86EFAC'],
        'gold'  => ['bg' => '#FEF3C7', 'fg' => '#92400E', 'bd' => '#FCD34D'],
        'blue'  => ['bg' => '#E0E7FF', 'fg' => '#3730A3', 'bd' => '#A5B4FC'],
    ];
    ob_start();
    ?>
    <div id="ppbJurnalGrid" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(320px, 1fr));gap:24px;margin-bottom:36px;">
        <?php foreach ($jurnal_list as $jr) :
            $tm         = $themes[$jr['tema'] ?? 'green'] ?? $themes['green'];
            $ojs_url    = !empty($jr['ojs_url']) && function_exists('ppb_clean_document_url')
                ? ppb_clean_document_url($jr['ojs_url'])
                : trim((string) ($jr['ojs_url'] ?? ''));
            $submit_url = !empty($jr['submit_url']) && function_exists('ppb_clean_document_url')
                ? ppb_clean_document_url($jr['submit_url'])
                : trim((string) ($jr['submit_url'] ?? ''));
        ?>
            <div style="background:#ffffff;border:1.5px solid #D4E3D8;border-radius:18px;padding:26px;box-shadow:0 6px 20px rgba(0,0,0,0.04);display:flex;flex-direction:column;justify-content:space-between;">
                <div>
                    <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;margin-bottom:12px;flex-wrap:wrap;">
                        <span style="background:<?php echo esc_attr($tm['bg']); ?>;color:<?php echo esc_attr($tm['fg']); ?>;font-size:11.5px;font-weight:700;padding:4px 12px;border-radius:6px;border:1px solid <?php echo esc_attr($tm['bd']); ?>;">
                            <?php echo esc_html($jr['badge'] ?? '🏅 Jurnal Resmi Prodi'); ?>
                        </span>
                        <span style="font-size:12px;color:#6B7280;font-weight:600;"><?php echo esc_html($jr['issn'] ?? ''); ?></span>
                    </div>
                    <h3 style="font-family:'Fraunces',Georgia,serif;font-size:20px;color:#0D3B22;margin:0 0 10px;line-height:1.3;">
                        <?php echo esc_html($jr['nama'] ?? ''); ?>
                    </h3>
                    <p style="font-size:13.5px;color:#4B5563;line-height:1.6;margin:0 0 16px;">
                        <?php echo esc_html($jr['deskripsi'] ?? ''); ?>
                    </p>
                    <div style="font-size:12.5px;background:#FAF8F5;padding:12px 14px;border-radius:10px;border:1px solid #E5E7EB;margin-bottom:18px;line-height:1.6;color:#374151;">
                        <div><strong>Penerbit:</strong> <?php echo esc_html($jr['penerbit'] ?? '-'); ?></div>
                        <div><strong>Frekuensi:</strong> <?php echo esc_html($jr['frekuensi'] ?? '-'); ?></div>
                        <div><strong>Pengindeks:</strong> <?php echo esc_html($jr['pengindeks'] ?? '-'); ?></div>
                    </div>
                </div>
                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <?php if ($ojs_url !== '') : ?>
                        <a href="<?php echo esc_url($ojs_url); ?>" target="_blank" rel="noopener noreferrer" style="flex:1;text-align:center;background:#0D3B22;color:#ffffff;font-size:13px;font-weight:700;padding:11px 16px;border-radius:10px;text-decoration:none;white-space:nowrap;">
                            Kunjungi Portal Jurnal OJS &rarr;
                        </a>
                    <?php endif; ?>
                    <?php if ($submit_url !== '') : ?>
                        <a href="<?php echo esc_url($submit_url); ?>" target="_blank" rel="noopener noreferrer" style="background:#FAF8F5;color:#0D3B22;border:1px solid #D4E3D8;font-size:13px;font-weight:600;padding:11px 14px;border-radius:10px;text-decoration:none;white-space:nowrap;">
                            Pedoman Submit &#8599;
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}
}

if (!function_exists('ppb_akademik_render_settings_page')) {
function ppb_akademik_render_settings_page() {
    if (!current_user_can('edit_posts')) {
        wp_die('Anda tidak memiliki izin mengakses halaman ini.');
    }
    if (function_exists('ppb_admin_enqueue_media_assets')) {
        ppb_admin_enqueue_media_assets();
    }

    $active_tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'tautan';
    if (!in_array($active_tab, ['tautan', 'pnbp', 'jurnal'], true)) {
        $active_tab = 'tautan';
    }

    $option_keys = [
        'ppb_link_buku_kurikulum',
        'ppb_link_sk_kurikulum',
        'ppb_link_pedoman_penilaian',
        'ppb_link_pedoman_skripsi',
        'ppb_link_rubrik_konseling',
        'ppb_link_rubrik_project',
        'ppb_link_sk_kepakaran',
        'ppb_link_moa_pendidikan',
        'ppb_link_moa_penelitian',
        'ppb_link_moa_pkm',
        'ppb_link_roadmap_pilar1_sk',
        'ppb_link_roadmap_pilar1_doc',
        'ppb_link_roadmap_pilar2_sk',
        'ppb_link_roadmap_pilar2_doc',
        'ppb_link_roadmap_pilar3_sk',
        'ppb_link_roadmap_pilar3_doc',
    ];

    $notice = '';
    if (isset($_POST['ppb_save_akademik_options']) && check_admin_referer('ppb_akademik_options_verify')) {
        $active_tab = 'tautan';
        foreach ($option_keys as $opt_key) {
            $raw_val = isset($_POST[$opt_key]) ? wp_unslash($_POST[$opt_key]) : '';
            $clean_val = function_exists('ppb_clean_document_url') ? ppb_clean_document_url($raw_val) : esc_url_raw((string) $raw_val);
            update_option($opt_key, $clean_val, false);
        }
        delete_post_meta(13, '_elementor_element_cache');
        delete_post_meta(16, '_elementor_element_cache');
        delete_post_meta(31, '_elementor_element_cache');
        delete_post_meta(35, '_elementor_element_cache');
        if (class_exists('\\Elementor\\Plugin')) {
            \Elementor\Plugin::$instance->files_manager->clear_cache();
        }
        $notice = '<div class="notice notice-success is-dismissible"><p><strong>✅ Seluruh Tautan &amp; Berkas Dokumen Akademik berhasil diperbarui!</strong></p></div>';
    }

    // Tab 2: Simpan / Reset MIS PNBP
    if (isset($_POST['ppb_save_mis_pnbp']) && check_admin_referer('ppb_pnbp_nonce_action', 'ppb_pnbp_nonce')) {
        $active_tab = 'pnbp';
        $riset_clean = [];
        $raw_riset = isset($_POST['riset']) && is_array($_POST['riset']) ? wp_unslash($_POST['riset']) : [];
        foreach ($raw_riset as $row) {
            if (!empty($row['delete']) || empty(trim((string) ($row['judul'] ?? '')))) continue;
            $dok = function_exists('ppb_clean_document_url') ? ppb_clean_document_url($row['dokumen_url'] ?? '') : esc_url_raw((string) ($row['dokumen_url'] ?? ''));
            $riset_clean[] = [
                'tahun'       => sanitize_text_field($row['tahun'] ?? date('Y')),
                'skema'       => sanitize_text_field($row['skema'] ?? 'Penelitian PNBP'),
                'judul'       => sanitize_text_field($row['judul']),
                'ketua'       => sanitize_text_field($row['ketua'] ?? ''),
                'anggota'     => sanitize_text_field($row['anggota'] ?? ''),
                'dana'        => sanitize_text_field($row['dana'] ?? ''),
                'luaran'      => sanitize_text_field($row['luaran'] ?? ''),
                'dokumen_url' => $dok,
            ];
        }
        if (!empty($_POST['new_riset']['judul']) && trim((string) $_POST['new_riset']['judul']) !== '') {
            $nr = wp_unslash($_POST['new_riset']);
            $dok = function_exists('ppb_clean_document_url') ? ppb_clean_document_url($nr['dokumen_url'] ?? '') : esc_url_raw((string) ($nr['dokumen_url'] ?? ''));
            $riset_clean[] = [
                'tahun'       => sanitize_text_field($nr['tahun'] ?? date('Y')),
                'skema'       => sanitize_text_field($nr['skema'] ?? 'Penelitian PNBP'),
                'judul'       => sanitize_text_field($nr['judul']),
                'ketua'       => sanitize_text_field($nr['ketua'] ?? ''),
                'anggota'     => sanitize_text_field($nr['anggota'] ?? ''),
                'dana'        => sanitize_text_field($nr['dana'] ?? ''),
                'luaran'      => sanitize_text_field($nr['luaran'] ?? ''),
                'dokumen_url' => $dok,
            ];
        }

        $pkm_clean = [];
        $raw_pkm = isset($_POST['pkm']) && is_array($_POST['pkm']) ? wp_unslash($_POST['pkm']) : [];
        foreach ($raw_pkm as $row) {
            if (!empty($row['delete']) || empty(trim((string) ($row['judul'] ?? '')))) continue;
            $dok = function_exists('ppb_clean_document_url') ? ppb_clean_document_url($row['dokumen_url'] ?? '') : esc_url_raw((string) ($row['dokumen_url'] ?? ''));
            $pkm_clean[] = [
                'tahun'       => sanitize_text_field($row['tahun'] ?? date('Y')),
                'skema'       => sanitize_text_field($row['skema'] ?? 'PkM PNBP'),
                'judul'       => sanitize_text_field($row['judul']),
                'ketua'       => sanitize_text_field($row['ketua'] ?? ''),
                'mitra'       => sanitize_text_field($row['mitra'] ?? ''),
                'dana'        => sanitize_text_field($row['dana'] ?? ''),
                'luaran'      => sanitize_text_field($row['luaran'] ?? ''),
                'dokumen_url' => $dok,
            ];
        }
        if (!empty($_POST['new_pkm']['judul']) && trim((string) $_POST['new_pkm']['judul']) !== '') {
            $np = wp_unslash($_POST['new_pkm']);
            $dok = function_exists('ppb_clean_document_url') ? ppb_clean_document_url($np['dokumen_url'] ?? '') : esc_url_raw((string) ($np['dokumen_url'] ?? ''));
            $pkm_clean[] = [
                'tahun'       => sanitize_text_field($np['tahun'] ?? date('Y')),
                'skema'       => sanitize_text_field($np['skema'] ?? 'PkM PNBP'),
                'judul'       => sanitize_text_field($np['judul']),
                'ketua'       => sanitize_text_field($np['ketua'] ?? ''),
                'mitra'       => sanitize_text_field($np['mitra'] ?? ''),
                'dana'        => sanitize_text_field($np['dana'] ?? ''),
                'luaran'      => sanitize_text_field($np['luaran'] ?? ''),
                'dokumen_url' => $dok,
            ];
        }

        update_option('ppb_mis_pnbp_data', [
            'riset' => array_values($riset_clean),
            'pkm'   => array_values($pkm_clean),
        ], false);
        if (class_exists('\\Elementor\\Plugin')) {
            \Elementor\Plugin::$instance->files_manager->clear_cache();
        }
        $notice = '<div class="notice notice-success is-dismissible"><p><strong>🔬 Data MIS PNBP (' . count($riset_clean) . ' Hibah Penelitian &amp; ' . count($pkm_clean) . ' PkM) berhasil disimpan dan langsung tersinkron ke frontend!</strong></p></div>';
    }
    if (isset($_POST['ppb_reset_mis_pnbp']) && check_admin_referer('ppb_pnbp_nonce_action', 'ppb_pnbp_nonce')) {
        $active_tab = 'pnbp';
        delete_option('ppb_mis_pnbp_data');
        $notice = '<div class="notice notice-warning is-dismissible"><p>Data Hibah Penelitian &amp; PkM MIS PNBP dikembalikan ke data bawaan.</p></div>';
    }

    // Tab 3: Simpan / Reset Jurnal Prodi BK
    if (isset($_POST['ppb_save_jurnal_bk']) && check_admin_referer('ppb_jurnal_nonce_action', 'ppb_jurnal_nonce')) {
        $active_tab = 'jurnal';
        $jurnal_clean = [];
        $raw_j = isset($_POST['jurnal']) && is_array($_POST['jurnal']) ? wp_unslash($_POST['jurnal']) : [];
        foreach ($raw_j as $row) {
            if (!empty($row['delete']) || empty(trim((string) ($row['nama'] ?? '')))) continue;
            $ojs = function_exists('ppb_clean_document_url') ? ppb_clean_document_url($row['ojs_url'] ?? '') : esc_url_raw((string) ($row['ojs_url'] ?? ''));
            $sub = function_exists('ppb_clean_document_url') ? ppb_clean_document_url($row['submit_url'] ?? '') : esc_url_raw((string) ($row['submit_url'] ?? ''));
            $tema = in_array(($row['tema'] ?? ''), ['green', 'gold', 'blue'], true) ? $row['tema'] : 'green';
            $jurnal_clean[] = [
                'badge'      => sanitize_text_field($row['badge'] ?? '🏅 Jurnal Resmi Prodi'),
                'tema'       => $tema,
                'issn'       => sanitize_text_field($row['issn'] ?? ''),
                'nama'       => sanitize_text_field($row['nama']),
                'deskripsi'  => sanitize_textarea_field($row['deskripsi'] ?? ''),
                'penerbit'   => sanitize_text_field($row['penerbit'] ?? ''),
                'frekuensi'  => sanitize_text_field($row['frekuensi'] ?? ''),
                'pengindeks' => sanitize_text_field($row['pengindeks'] ?? ''),
                'ojs_url'    => $ojs,
                'submit_url' => $sub,
            ];
        }
        if (!empty($_POST['new_jurnal']['nama']) && trim((string) $_POST['new_jurnal']['nama']) !== '') {
            $nj = wp_unslash($_POST['new_jurnal']);
            $ojs = function_exists('ppb_clean_document_url') ? ppb_clean_document_url($nj['ojs_url'] ?? '') : esc_url_raw((string) ($nj['ojs_url'] ?? ''));
            $sub = function_exists('ppb_clean_document_url') ? ppb_clean_document_url($nj['submit_url'] ?? '') : esc_url_raw((string) ($nj['submit_url'] ?? ''));
            $tema = in_array(($nj['tema'] ?? ''), ['green', 'gold', 'blue'], true) ? $nj['tema'] : 'green';
            $jurnal_clean[] = [
                'badge'      => sanitize_text_field($nj['badge'] ?? '🏅 Jurnal Prodi'),
                'tema'       => $tema,
                'issn'       => sanitize_text_field($nj['issn'] ?? ''),
                'nama'       => sanitize_text_field($nj['nama']),
                'deskripsi'  => sanitize_textarea_field($nj['deskripsi'] ?? ''),
                'penerbit'   => sanitize_text_field($nj['penerbit'] ?? ''),
                'frekuensi'  => sanitize_text_field($nj['frekuensi'] ?? ''),
                'pengindeks' => sanitize_text_field($nj['pengindeks'] ?? ''),
                'ojs_url'    => $ojs,
                'submit_url' => $sub,
            ];
        }

        update_option('ppb_jurnal_bk_data', array_values($jurnal_clean), false);
        if (class_exists('\\Elementor\\Plugin')) {
            \Elementor\Plugin::$instance->files_manager->clear_cache();
        }
        $notice = '<div class="notice notice-success is-dismissible"><p><strong>📰 Data Jurnal Prodi BK (' . count($jurnal_clean) . ' jurnal) berhasil disimpan!</strong></p></div>';
    }
    if (isset($_POST['ppb_reset_jurnal_bk']) && check_admin_referer('ppb_jurnal_nonce_action', 'ppb_jurnal_nonce')) {
        $active_tab = 'jurnal';
        delete_option('ppb_jurnal_bk_data');
        $notice = '<div class="notice notice-warning is-dismissible"><p>Daftar Jurnal Prodi BK dikembalikan ke data bawaan.</p></div>';
    }

    $links = ppb_akademik_get_all_links();

    $render_field = function($name, $label, $val, $hint) {
        echo '<div style="padding-bottom:14px;border-bottom:1px dashed #e2e8f0;">';
        echo '<label for="' . esc_attr($name) . '" style="display:block;font-weight:700;font-size:13px;color:#1e293b;margin-bottom:6px;">' . esc_html($label) . '</label>';
        if (function_exists('ppb_render_media_url_control')) {
            ppb_render_media_url_control([
                'id'          => $name,
                'name'        => $name,
                'value'       => $val,
                'placeholder' => 'https://drive.google.com/... atau upload berkas WP Media',
                'title'       => 'Pilih / Upload Dokumen PDF (' . $label . ')',
                'btn_label'   => '📁 Upload / Pilih PDF',
            ]);
        } else {
            echo '<input type="url" id="' . esc_attr($name) . '" name="' . esc_attr($name) . '" value="' . esc_attr($val) . '" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:13px;" />';
        }
        echo '<span style="display:block;font-size:11.5px;color:#64748b;margin-top:5px;">' . esc_html($hint) . '</span>';
        echo '</div>';
    };

    $tabs = [
        'tautan' => '⚖️ Tautan Drive, MoA & Roadmap',
        'pnbp'   => '🔬 Hibah Penelitian & PkM (MIS PNBP)',
        'jurnal' => '📰 Jurnal & Pengabdian Prodi BK',
    ];
    ?>
    <div class="wrap" style="max-width:1120px;margin:20px auto;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;color:#1e293b;">
        <div style="background:linear-gradient(135deg,#0D3B22 0%,#185333 100%);color:#fff;border-radius:16px;padding:24px 28px;margin-bottom:18px;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:14px;">
            <div>
                <h1 style="margin:0 0 6px;color:#fff;font-size:23px;font-weight:800;">⚖️ Pusat Tautan Akademik, Hibah PNBP &amp; Jurnal Prodi BK</h1>
                <p style="margin:0;color:rgba(255,255,255,0.86);font-size:13px;">Kelola link Drive/PDF Kurikulum, MoA &amp; Roadmap, daftar Hibah Penelitian/PkM MIS PNBP, serta portal OJS Jurnal Prodi BK.</p>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <a href="<?php echo esc_url(home_url('/mis-pnbp/')); ?>" target="_blank" class="button" style="border-radius:8px;">↗ Lihat /mis-pnbp/</a>
                <a href="<?php echo esc_url(home_url('/jurnal-bk/')); ?>" target="_blank" class="button" style="border-radius:8px;">↗ Lihat /jurnal-bk/</a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=ppb-portal-hub')); ?>" class="button" style="border-radius:8px;">← Pusat Kendali</a>
            </div>
        </div>

        <nav class="nav-tab-wrapper" style="margin-bottom:20px;">
            <?php foreach ($tabs as $tk => $tl) : ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=ppb-akademik-settings&tab=' . $tk)); ?>" class="nav-tab <?php echo $active_tab === $tk ? 'nav-tab-active' : ''; ?>" style="<?php echo $active_tab === $tk ? 'background:#0D3B22;color:#fff;border-color:#0D3B22;' : ''; ?>font-weight:700;">
                    <?php echo esc_html($tl); ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <?php echo $notice; ?>

        <?php if ($active_tab === 'tautan') : ?>
            <form method="post" action="">
                <?php wp_nonce_field('ppb_akademik_options_verify'); ?>

                <div style="background:#fff;border:1px solid #cbd5e1;border-radius:14px;padding:22px;margin-bottom:18px;box-shadow:0 3px 10px rgba(0,0,0,0.03);display:flex;flex-direction:column;gap:14px;">
                    <h2 style="margin:0;color:#0D3B22;font-size:17px;border-bottom:2px solid #E8F0EA;padding-bottom:8px;">📘 1. Dokumen Kurikulum OBE &amp; Pedoman Akademik</h2>
                    <?php
                    $render_field('ppb_link_buku_kurikulum', 'Buku Kurikulum OBE Lengkap', $links['buku_kurikulum'], 'Tampil pada tombol utama di halaman /akademik/kurikulum/.');
                    $render_field('ppb_link_sk_kurikulum', 'SK Senat Pengesahan Kurikulum', $links['sk_kurikulum'], 'Tampil pada tombol SK Senat di halaman /akademik/kurikulum/.');
                    $render_field('ppb_link_pedoman_penilaian', 'Dokumen Resmi Pedoman Penilaian OBE', $links['pedoman_penilaian'], 'Tampil pada halaman /akademik/panduan/.');
                    $render_field('ppb_link_pedoman_skripsi', 'Folder Pedoman & Template Skripsi', $links['pedoman_skripsi'], 'Tampil pada tombol utama di halaman /akademik/panduan/.');
                    ?>
                </div>

                <div style="background:#fff;border:1px solid #cbd5e1;border-radius:14px;padding:22px;margin-bottom:18px;box-shadow:0 3px 10px rgba(0,0,0,0.03);display:flex;flex-direction:column;gap:14px;">
                    <h2 style="margin:0;color:#0D3B22;font-size:17px;border-bottom:2px solid #E8F0EA;padding-bottom:8px;">⚖️ 2. Lembar Rubrik Penilaian OBE &amp; SK Kepakaran Dosen</h2>
                    <?php
                    $render_field('ppb_link_rubrik_konseling', 'Lembar Rubrik Kinerja Simulasi Konseling', $links['rubrik_konseling'], 'Tampil pada Tab Rubrik Penilaian di shortcode [ppb_kurikulum_rps_sap].');
                    $render_field('ppb_link_rubrik_project', 'Lembar Rubrik Penilaian Team-Based Project', $links['rubrik_project'], 'Tampil pada Tab Rubrik Penilaian di shortcode [ppb_kurikulum_rps_sap].');
                    $render_field('ppb_link_sk_kepakaran', 'Dokumen SK Kepakaran & Konsentrasi Dosen', $links['sk_kepakaran'], 'Tampil pada halaman /profil/kepakaran-dosen/.');
                    ?>
                </div>

                <div style="background:#fff;border:1px solid #cbd5e1;border-radius:14px;padding:22px;margin-bottom:18px;box-shadow:0 3px 10px rgba(0,0,0,0.03);display:flex;flex-direction:column;gap:14px;">
                    <h2 style="margin:0;color:#0D3B22;font-size:17px;border-bottom:2px solid #E8F0EA;padding-bottom:8px;">🤝 3. Rekapitulasi MoA Kerjasama Tridharma (/profil/kerjasama-tridharma/)</h2>
                    <?php
                    $render_field('ppb_link_moa_pendidikan', 'Rekap MoA Bidang Pendidikan & MBKM (PDF)', $links['moa_pendidikan'], 'Menggantikan tombol Rekap MoA Pendidikan PDF di /profil/kerjasama-tridharma/.');
                    $render_field('ppb_link_moa_penelitian', 'Rekap MoA Bidang Penelitian Kolaboratif (PDF)', $links['moa_penelitian'], 'Menggantikan tombol Rekap MoA Penelitian PDF di /profil/kerjasama-tridharma/.');
                    $render_field('ppb_link_moa_pkm', 'Rekap MoA Bidang Pengabdian Masyarakat / PkM (PDF)', $links['moa_pkm'], 'Menggantikan tombol Rekap MoA PkM PDF di /profil/kerjasama-tridharma/.');
                    ?>
                </div>

                <div style="background:#fff;border:1px solid #cbd5e1;border-radius:14px;padding:22px;margin-bottom:20px;box-shadow:0 3px 10px rgba(0,0,0,0.03);display:flex;flex-direction:column;gap:14px;">
                    <h2 style="margin:0;color:#0D3B22;font-size:17px;border-bottom:2px solid #E8F0EA;padding-bottom:8px;">🧭 4. Dokumen &amp; SK Roadmap Riset &amp; PkM (/profil/roadmap/)</h2>
                    <?php
                    $render_field('ppb_link_roadmap_pilar1_sk', 'Pilar 1 — Lembar Pengesahan Senat', $links['roadmap_p1_sk'], 'Tombol Lihat Lembar Pengesahan Senat (Pilar 1) di /profil/roadmap/.');
                    $render_field('ppb_link_roadmap_pilar1_doc', 'Pilar 1 — Dokumen Roadmap Riset Lengkap', $links['roadmap_p1_doc'], 'Tombol Dokumen Roadmap Lengkap (Pilar 1) di /profil/roadmap/.');
                    $render_field('ppb_link_roadmap_pilar2_sk', 'Pilar 2 — Lembar Pengesahan Senat', $links['roadmap_p2_sk'], 'Tombol Lihat Lembar Pengesahan Senat (Pilar 2) di /profil/roadmap/.');
                    $render_field('ppb_link_roadmap_pilar2_doc', 'Pilar 2 — Dokumen Roadmap Riset Lengkap', $links['roadmap_p2_doc'], 'Tombol Dokumen Roadmap Lengkap (Pilar 2) di /profil/roadmap/.');
                    $render_field('ppb_link_roadmap_pilar3_sk', 'Pilar 3 — Lembar Pengesahan Senat', $links['roadmap_p3_sk'], 'Tombol Lihat Lembar Pengesahan Senat (Pilar 3) di /profil/roadmap/.');
                    $render_field('ppb_link_roadmap_pilar3_doc', 'Pilar 3 — Dokumen Roadmap Riset Lengkap', $links['roadmap_p3_doc'], 'Tombol Dokumen Roadmap Lengkap (Pilar 3) di /profil/roadmap/.');
                    ?>
                </div>

                <div style="position:sticky;bottom:14px;background:rgba(255,255,255,0.96);border:1px solid #cbd5e1;border-radius:12px;padding:14px 18px;box-shadow:0 4px 14px rgba(0,0,0,0.08);display:flex;align-items:center;justify-content:space-between;gap:12px;">
                    <span style="font-size:12.5px;color:#475569;">Kosongkan URL jika dokumen belum tersedia (halaman publik otomatis menandai <em>Segera Tersedia</em> alih-alih tautan mati).</span>
                    <button type="submit" name="ppb_save_akademik_options" class="button button-primary" style="background:#0D3B22;border-color:#0D3B22;padding:7px 22px;height:auto;font-size:13.5px;font-weight:700;border-radius:8px;">💾 Simpan Pengaturan Tautan</button>
                </div>
            </form>

        <?php elseif ($active_tab === 'pnbp') :
            $pnbp_data  = ppb_get_mis_pnbp_data();
            $riset_list = $pnbp_data['riset'];
            $pkm_list   = $pnbp_data['pkm'];
        ?>
            <form method="post" action="">
                <?php wp_nonce_field('ppb_pnbp_nonce_action', 'ppb_pnbp_nonce'); ?>

                <!-- Seksi 1: Hibah Penelitian PNBP -->
                <div style="background:#fff;border:1px solid #cbd5e1;border-radius:14px;padding:22px;margin-bottom:20px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:14px;border-bottom:2px solid #E8F0EA;padding-bottom:10px;">
                        <div>
                            <h2 style="margin:0;color:#0D3B22;font-size:18px;">🔬 Hibah Penelitian PNBP (<?php echo count($riset_list); ?>)</h2>
                            <span style="font-size:12px;color:#64748b;">Tampil pada shortcode <code>[ppb_mis_pnbp]</code> dan halaman <code>/mis-pnbp/</code>.</span>
                        </div>
                        <div style="display:flex;gap:8px;">
                            <button type="submit" name="ppb_save_mis_pnbp" class="button button-primary" style="background:#0D3B22;border-color:#0D3B22;">💾 Simpan Data MIS PNBP</button>
                            <button type="submit" name="ppb_reset_mis_pnbp" class="button" onclick="return confirm('Kembalikan data Riset & PkM PNBP ke bawaan?');">↺ Reset Default</button>
                        </div>
                    </div>

                    <div style="display:grid;gap:12px;margin-bottom:16px;">
                        <?php foreach ($riset_list as $idx => $r) : ?>
                            <div style="border:1px solid #e2e8f0;border-radius:10px;padding:14px;background:#fcfdfd;">
                                <div style="display:grid;grid-template-columns:80px 1fr 1.6fr;gap:10px;margin-bottom:8px;">
                                    <div>
                                        <label style="font-size:11px;font-weight:700;display:block;margin-bottom:2px;">Tahun</label>
                                        <input type="text" name="riset[<?php echo $idx; ?>][tahun]" value="<?php echo esc_attr($r['tahun'] ?? ''); ?>" class="widefat">
                                    </div>
                                    <div>
                                        <label style="font-size:11px;font-weight:700;display:block;margin-bottom:2px;">Skema Hibah</label>
                                        <input type="text" name="riset[<?php echo $idx; ?>][skema]" value="<?php echo esc_attr($r['skema'] ?? ''); ?>" class="widefat">
                                    </div>
                                    <div>
                                        <label style="font-size:11px;font-weight:700;display:block;margin-bottom:2px;">Judul Penelitian</label>
                                        <input type="text" name="riset[<?php echo $idx; ?>][judul]" value="<?php echo esc_attr($r['judul'] ?? ''); ?>" class="widefat" style="font-weight:600;">
                                    </div>
                                </div>
                                <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:10px;margin-bottom:8px;">
                                    <div>
                                        <label style="font-size:11px;font-weight:600;display:block;margin-bottom:2px;">Ketua Peneliti</label>
                                        <input type="text" name="riset[<?php echo $idx; ?>][ketua]" value="<?php echo esc_attr($r['ketua'] ?? ''); ?>" class="widefat">
                                    </div>
                                    <div>
                                        <label style="font-size:11px;font-weight:600;display:block;margin-bottom:2px;">Anggota / Kolaborator</label>
                                        <input type="text" name="riset[<?php echo $idx; ?>][anggota]" value="<?php echo esc_attr($r['anggota'] ?? ''); ?>" class="widefat">
                                    </div>
                                    <div>
                                        <label style="font-size:11px;font-weight:600;display:block;margin-bottom:2px;">Besaran Dana</label>
                                        <input type="text" name="riset[<?php echo $idx; ?>][dana]" value="<?php echo esc_attr($r['dana'] ?? ''); ?>" class="widefat">
                                    </div>
                                    <div>
                                        <label style="font-size:11px;font-weight:600;display:block;margin-bottom:2px;">Luaran Wajib</label>
                                        <input type="text" name="riset[<?php echo $idx; ?>][luaran]" value="<?php echo esc_attr($r['luaran'] ?? ''); ?>" class="widefat">
                                    </div>
                                </div>
                                <div style="display:grid;grid-template-columns:1fr auto;gap:10px;align-items:center;">
                                    <?php ppb_render_media_url_control([
                                        'id'          => 'riset_dok_' . $idx,
                                        'name'        => "riset[$idx][dokumen_url]",
                                        'value'       => $r['dokumen_url'] ?? '',
                                        'placeholder' => 'Link SK / Laporan Akhir Riset (Google Drive atau Upload PDF)',
                                        'title'       => 'Pilih / Upload Laporan atau SK Hibah Penelitian',
                                        'btn_label'   => '📁 Upload / Drive SK',
                                    ]); ?>
                                    <label style="color:#b91c1c;font-size:11.5px;font-weight:600;white-space:nowrap;"><input type="checkbox" name="riset[<?php echo $idx; ?>][delete]" value="1"> Hapus</label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div style="background:#f0fdf4;border:1.5px dashed #166534;border-radius:10px;padding:14px;">
                        <strong style="display:block;color:#0D3B22;margin-bottom:8px;">➕ Tambah Hibah Penelitian Baru</strong>
                        <div style="display:grid;grid-template-columns:80px 1fr 1.6fr;gap:10px;margin-bottom:8px;">
                            <input type="text" name="new_riset[tahun]" placeholder="2025" class="widefat">
                            <input type="text" name="new_riset[skema]" placeholder="Skema (mis. Penelitian Terapan PNBP)" class="widefat">
                            <input type="text" name="new_riset[judul]" placeholder="Judul Hibah Penelitian baru (isi untuk menambah)..." class="widefat">
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:10px;margin-bottom:8px;">
                            <input type="text" name="new_riset[ketua]" placeholder="Nama Ketua Peneliti" class="widefat">
                            <input type="text" name="new_riset[anggota]" placeholder="Kolaborator / Mhs" class="widefat">
                            <input type="text" name="new_riset[dana]" placeholder="Rp ..." class="widefat">
                            <input type="text" name="new_riset[luaran]" placeholder="Luaran (Artikel/HKI)" class="widefat">
                        </div>
                        <?php ppb_render_media_url_control([
                            'id'          => 'riset_dok_new',
                            'name'        => 'new_riset[dokumen_url]',
                            'value'       => '',
                            'placeholder' => 'Link SK / Laporan Penelitian (opsional)',
                            'btn_label'   => '📁 Upload / Drive SK',
                        ]); ?>
                    </div>
                </div>

                <!-- Seksi 2: Hibah PkM PNBP -->
                <div style="background:#fff;border:1px solid #cbd5e1;border-radius:14px;padding:22px;margin-bottom:20px;">
                    <h2 style="margin:0 0 14px;color:#0D3B22;font-size:18px;border-bottom:2px solid #E8F0EA;padding-bottom:10px;">🤝 Pengabdian kepada Masyarakat (PkM) PNBP (<?php echo count($pkm_list); ?>)</h2>

                    <div style="display:grid;gap:12px;margin-bottom:16px;">
                        <?php foreach ($pkm_list as $idx => $p) : ?>
                            <div style="border:1px solid #e2e8f0;border-radius:10px;padding:14px;background:#fcfdfd;">
                                <div style="display:grid;grid-template-columns:80px 1fr 1.6fr;gap:10px;margin-bottom:8px;">
                                    <div>
                                        <label style="font-size:11px;font-weight:700;display:block;margin-bottom:2px;">Tahun</label>
                                        <input type="text" name="pkm[<?php echo $idx; ?>][tahun]" value="<?php echo esc_attr($p['tahun'] ?? ''); ?>" class="widefat">
                                    </div>
                                    <div>
                                        <label style="font-size:11px;font-weight:700;display:block;margin-bottom:2px;">Skema PkM</label>
                                        <input type="text" name="pkm[<?php echo $idx; ?>][skema]" value="<?php echo esc_attr($p['skema'] ?? ''); ?>" class="widefat">
                                    </div>
                                    <div>
                                        <label style="font-size:11px;font-weight:700;display:block;margin-bottom:2px;">Judul Pengabdian</label>
                                        <input type="text" name="pkm[<?php echo $idx; ?>][judul]" value="<?php echo esc_attr($p['judul'] ?? ''); ?>" class="widefat" style="font-weight:600;">
                                    </div>
                                </div>
                                <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:10px;margin-bottom:8px;">
                                    <div>
                                        <label style="font-size:11px;font-weight:600;display:block;margin-bottom:2px;">Ketua Pelaksana</label>
                                        <input type="text" name="pkm[<?php echo $idx; ?>][ketua]" value="<?php echo esc_attr($p['ketua'] ?? ''); ?>" class="widefat">
                                    </div>
                                    <div>
                                        <label style="font-size:11px;font-weight:600;display:block;margin-bottom:2px;">Mitra Kegiatan</label>
                                        <input type="text" name="pkm[<?php echo $idx; ?>][mitra]" value="<?php echo esc_attr($p['mitra'] ?? ''); ?>" class="widefat">
                                    </div>
                                    <div>
                                        <label style="font-size:11px;font-weight:600;display:block;margin-bottom:2px;">Besaran Dana</label>
                                        <input type="text" name="pkm[<?php echo $idx; ?>][dana]" value="<?php echo esc_attr($p['dana'] ?? ''); ?>" class="widefat">
                                    </div>
                                    <div>
                                        <label style="font-size:11px;font-weight:600;display:block;margin-bottom:2px;">Luaran Kegiatan</label>
                                        <input type="text" name="pkm[<?php echo $idx; ?>][luaran]" value="<?php echo esc_attr($p['luaran'] ?? ''); ?>" class="widefat">
                                    </div>
                                </div>
                                <div style="display:grid;grid-template-columns:1fr auto;gap:10px;align-items:center;">
                                    <?php ppb_render_media_url_control([
                                        'id'          => 'pkm_dok_' . $idx,
                                        'name'        => "pkm[$idx][dokumen_url]",
                                        'value'       => $p['dokumen_url'] ?? '',
                                        'placeholder' => 'Link SK / Laporan Akhir PkM (Google Drive atau Upload PDF)',
                                        'title'       => 'Pilih / Upload Laporan atau SK PkM',
                                        'btn_label'   => '📁 Upload / Drive SK',
                                    ]); ?>
                                    <label style="color:#b91c1c;font-size:11.5px;font-weight:600;white-space:nowrap;"><input type="checkbox" name="pkm[<?php echo $idx; ?>][delete]" value="1"> Hapus</label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div style="background:#fefce8;border:1.5px dashed #ca8a04;border-radius:10px;padding:14px;">
                        <strong style="display:block;color:#854d0e;margin-bottom:8px;">➕ Tambah Hibah PkM Baru</strong>
                        <div style="display:grid;grid-template-columns:80px 1fr 1.6fr;gap:10px;margin-bottom:8px;">
                            <input type="text" name="new_pkm[tahun]" placeholder="2025" class="widefat">
                            <input type="text" name="new_pkm[skema]" placeholder="Skema (mis. PkM Kemitraan)" class="widefat">
                            <input type="text" name="new_pkm[judul]" placeholder="Judul Kegiatan PkM baru (isi untuk menambah)..." class="widefat">
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:10px;margin-bottom:8px;">
                            <input type="text" name="new_pkm[ketua]" placeholder="Nama Ketua Pelaksana" class="widefat">
                            <input type="text" name="new_pkm[mitra]" placeholder="Mitra Sekolah / Desa" class="widefat">
                            <input type="text" name="new_pkm[dana]" placeholder="Rp ..." class="widefat">
                            <input type="text" name="new_pkm[luaran]" placeholder="Luaran PkM" class="widefat">
                        </div>
                        <?php ppb_render_media_url_control([
                            'id'          => 'pkm_dok_new',
                            'name'        => 'new_pkm[dokumen_url]',
                            'value'       => '',
                            'placeholder' => 'Link SK / Laporan PkM (opsional)',
                            'btn_label'   => '📁 Upload / Drive SK',
                        ]); ?>
                    </div>
                </div>

                <div style="text-align:right;">
                    <button type="submit" name="ppb_save_mis_pnbp" class="button button-primary" style="background:#0D3B22;border-color:#0D3B22;padding:7px 22px;height:auto;font-size:13.5px;font-weight:700;border-radius:8px;">💾 Simpan Semua Data Hibah PNBP</button>
                </div>
            </form>

        <?php elseif ($active_tab === 'jurnal') :
            $jurnal_list = ppb_get_jurnal_bk_data();
        ?>
            <form method="post" action="" style="background:#fff;border:1px solid #cbd5e1;border-radius:14px;padding:22px;">
                <?php wp_nonce_field('ppb_jurnal_nonce_action', 'ppb_jurnal_nonce'); ?>
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:16px;border-bottom:2px solid #E8F0EA;padding-bottom:10px;">
                    <div>
                        <h2 style="margin:0;color:#0D3B22;font-size:18px;">📰 Daftar Jurnal &amp; Pengabdian Prodi BK (<?php echo count($jurnal_list); ?>)</h2>
                        <span style="font-size:12px;color:#64748b;">Otomatis tersinkron ke <code>/jurnal-bk/</code> dan shortcode <code>[ppb_jurnal_bk]</code>.</span>
                    </div>
                    <div style="display:flex;gap:8px;">
                        <button type="submit" name="ppb_save_jurnal_bk" class="button button-primary" style="background:#0D3B22;border-color:#0D3B22;">💾 Simpan Jurnal BK</button>
                        <button type="submit" name="ppb_reset_jurnal_bk" class="button" onclick="return confirm('Reset Daftar Jurnal ke bawaan?');">↺ Reset Default</button>
                    </div>
                </div>

                <div style="display:grid;gap:14px;margin-bottom:18px;">
                    <?php foreach ($jurnal_list as $idx => $jr) : ?>
                        <div style="border:1px solid #cbd5e1;border-radius:12px;padding:16px;background:#fcfdfd;">
                            <div style="display:grid;grid-template-columns:1.5fr 1fr 120px 1fr;gap:10px;margin-bottom:10px;">
                                <div>
                                    <label style="font-size:11px;font-weight:700;display:block;margin-bottom:2px;">Nama Jurnal</label>
                                    <input type="text" name="jurnal[<?php echo $idx; ?>][nama]" value="<?php echo esc_attr($jr['nama'] ?? ''); ?>" class="widefat" style="font-weight:700;">
                                </div>
                                <div>
                                    <label style="font-size:11px;font-weight:600;display:block;margin-bottom:2px;">Badge / Akreditasi</label>
                                    <input type="text" name="jurnal[<?php echo $idx; ?>][badge]" value="<?php echo esc_attr($jr['badge'] ?? ''); ?>" class="widefat">
                                </div>
                                <div>
                                    <label style="font-size:11px;font-weight:600;display:block;margin-bottom:2px;">Warna Badge</label>
                                    <select name="jurnal[<?php echo $idx; ?>][tema]" class="widefat">
                                        <option value="green" <?php selected($jr['tema'] ?? 'green', 'green'); ?>>Hijau</option>
                                        <option value="gold" <?php selected($jr['tema'] ?? '', 'gold'); ?>>Emas</option>
                                        <option value="blue" <?php selected($jr['tema'] ?? '', 'blue'); ?>>Biru</option>
                                    </select>
                                </div>
                                <div>
                                    <label style="font-size:11px;font-weight:600;display:block;margin-bottom:2px;">ISSN / E-ISSN</label>
                                    <input type="text" name="jurnal[<?php echo $idx; ?>][issn]" value="<?php echo esc_attr($jr['issn'] ?? ''); ?>" class="widefat">
                                </div>
                            </div>
                            <div style="margin-bottom:10px;">
                                <label style="font-size:11px;font-weight:600;display:block;margin-bottom:2px;">Deskripsi &amp; Fokus Ruang Lingkup</label>
                                <textarea name="jurnal[<?php echo $idx; ?>][deskripsi]" rows="2" class="widefat"><?php echo esc_textarea($jr['deskripsi'] ?? ''); ?></textarea>
                            </div>
                            <div style="display:grid;grid-template-columns:1fr 1fr 1.2fr;gap:10px;margin-bottom:10px;">
                                <div>
                                    <label style="font-size:11px;font-weight:600;display:block;margin-bottom:2px;">Penerbit</label>
                                    <input type="text" name="jurnal[<?php echo $idx; ?>][penerbit]" value="<?php echo esc_attr($jr['penerbit'] ?? ''); ?>" class="widefat">
                                </div>
                                <div>
                                    <label style="font-size:11px;font-weight:600;display:block;margin-bottom:2px;">Frekuensi Terbit</label>
                                    <input type="text" name="jurnal[<?php echo $idx; ?>][frekuensi]" value="<?php echo esc_attr($jr['frekuensi'] ?? ''); ?>" class="widefat">
                                </div>
                                <div>
                                    <label style="font-size:11px;font-weight:600;display:block;margin-bottom:2px;">Pengindeks (SINTA/DOAJ/Garuda)</label>
                                    <input type="text" name="jurnal[<?php echo $idx; ?>][pengindeks]" value="<?php echo esc_attr($jr['pengindeks'] ?? ''); ?>" class="widefat">
                                </div>
                            </div>
                            <div style="display:grid;grid-template-columns:1fr 1fr auto;gap:10px;align-items:end;">
                                <div>
                                    <label style="font-size:11px;font-weight:600;display:block;margin-bottom:2px;">URL Portal OJS Utama</label>
                                    <?php ppb_render_media_url_control(['id' => 'jr_ojs_' . $idx, 'name' => "jurnal[$idx][ojs_url]", 'value' => $jr['ojs_url'] ?? '', 'placeholder' => 'https://ojs.unm.ac.id/...', 'btn_label' => '🌐 Pilih / Tes']); ?>
                                </div>
                                <div>
                                    <label style="font-size:11px;font-weight:600;display:block;margin-bottom:2px;">URL Pedoman Submit / Author Guidelines</label>
                                    <?php ppb_render_media_url_control(['id' => 'jr_sub_' . $idx, 'name' => "jurnal[$idx][submit_url]", 'value' => $jr['submit_url'] ?? '', 'placeholder' => 'URL Submit OJS atau upload PDF panduan penulis', 'btn_label' => '📁 Upload / Drive']); ?>
                                </div>
                                <label style="color:#b91c1c;font-size:11.5px;font-weight:600;white-space:nowrap;padding-bottom:8px;"><input type="checkbox" name="jurnal[<?php echo $idx; ?>][delete]" value="1"> Hapus</label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div style="background:#f0fdf4;border:1.5px dashed #166534;border-radius:12px;padding:16px;">
                    <strong style="display:block;color:#0D3B22;margin-bottom:10px;">➕ Tambah Jurnal Ilmiah / Pengabdian Baru</strong>
                    <div style="display:grid;grid-template-columns:1.5fr 1fr 120px 1fr;gap:10px;margin-bottom:8px;">
                        <input type="text" name="new_jurnal[nama]" placeholder="Nama Jurnal Baru (isi untuk menambah)..." class="widefat">
                        <input type="text" name="new_jurnal[badge]" placeholder="🏅 Terakreditasi SINTA ..." class="widefat">
                        <select name="new_jurnal[tema]" class="widefat">
                            <option value="green">Hijau</option>
                            <option value="gold">Emas</option>
                            <option value="blue">Biru</option>
                        </select>
                        <input type="text" name="new_jurnal[issn]" placeholder="E-ISSN: ..." class="widefat">
                    </div>
                    <textarea name="new_jurnal[deskripsi]" rows="2" placeholder="Deskripsi singkat ruang lingkup jurnal..." class="widefat" style="margin-bottom:8px;"></textarea>
                    <div style="display:grid;grid-template-columns:1fr 1fr 1.2fr;gap:10px;margin-bottom:8px;">
                        <input type="text" name="new_jurnal[penerbit]" placeholder="Penerbit" class="widefat">
                        <input type="text" name="new_jurnal[frekuensi]" placeholder="2 Kali / Tahun" class="widefat">
                        <input type="text" name="new_jurnal[pengindeks]" placeholder="SINTA, Google Scholar, Garuda" class="widefat">
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                        <?php ppb_render_media_url_control(['id' => 'jr_ojs_new', 'name' => 'new_jurnal[ojs_url]', 'value' => '', 'placeholder' => 'URL Portal OJS', 'btn_label' => '🌐 Pilih URL']); ?>
                        <?php ppb_render_media_url_control(['id' => 'jr_sub_new', 'name' => 'new_jurnal[submit_url]', 'value' => '', 'placeholder' => 'URL Panduan Submit / PDF', 'btn_label' => '📁 Upload / Drive']); ?>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>
    <?php
}
}

/* ========================================================================== */
/* 5. SHORTCODE: [ppb_jadwal_ujian_list]                                      */
/* ========================================================================== */
if (!function_exists('ppb_shortcode_jadwal_ujian_list')) {
function ppb_shortcode_jadwal_ujian_list() {
    $args = [
        'post_type'      => 'ppb_jadwal',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'meta_key'       => '_ppb_tanggal',
        'orderby'        => 'meta_value',
        'order'          => 'DESC'
    ];

    $query = new WP_Query($args);
    $items = [];

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $pid = get_the_ID();

            $items[] = [
                'id'               => 'jadwal-' . $pid,
                'jenis_ujian'      => get_post_meta($pid, '_ppb_jenis_ujian', true) ?: 'seminar_proposal',
                'status'           => get_post_meta($pid, '_ppb_status', true) ?: 'terjadwal',
                'nama_mahasiswa'   => get_post_meta($pid, '_ppb_nama_mahasiswa', true) ?: get_the_title(),
                'nim'              => get_post_meta($pid, '_ppb_nim', true) ?: '-',
                'angkatan'         => get_post_meta($pid, '_ppb_angkatan', true) ?: '-',
                'judul_penelitian' => get_post_meta($pid, '_ppb_judul_penelitian', true) ?: get_the_title(),
                'tanggal'          => get_post_meta($pid, '_ppb_tanggal', true) ?: date('Y-m-d'),
                'jam_mulai'        => get_post_meta($pid, '_ppb_jam_mulai', true) ?: '08:30',
                'jam_selesai'      => get_post_meta($pid, '_ppb_jam_selesai', true) ?: '10:00',
                'ruangan'          => get_post_meta($pid, '_ppb_ruangan', true) ?: 'Ruang Sidang FIP UNM',
                'link_daring'      => get_post_meta($pid, '_ppb_link_daring', true) ?: '',
                'pembimbing_1'     => get_post_meta($pid, '_ppb_pembimbing_1', true) ?: '-',
                'pembimbing_2'     => get_post_meta($pid, '_ppb_pembimbing_2', true) ?: '-',
                'penguji_1'        => get_post_meta($pid, '_ppb_penguji_1', true) ?: '-',
                'penguji_2'        => get_post_meta($pid, '_ppb_penguji_2', true) ?: '-',
            ];
        }
        wp_reset_postdata();
    }

    // Counts
    $count_total     = count($items);
    $count_proposal  = 0;
    $count_hasil     = 0;
    $count_skripsi   = 0;
    $count_terjadwal = 0;

    foreach ($items as $it) {
        if ($it['jenis_ujian'] === 'seminar_proposal') $count_proposal++;
        if ($it['jenis_ujian'] === 'seminar_hasil') $count_hasil++;
        if ($it['jenis_ujian'] === 'ujian_skripsi') $count_skripsi++;
        if ($it['status'] === 'terjadwal') $count_terjadwal++;
    }

    ob_start();
    $items_json = wp_json_encode($items);
    ?>
    <section class="ppb-jadwal-wrapper" style="font-family:'Plus Jakarta Sans',sans-serif;color:#1e293b;width:100%;max-width:var(--max-w, 1160px);margin:0 auto;">
        
        <!-- Quick Counter Pills -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(140px, 1fr));gap:12px;margin-bottom:24px;">
            <div style="background:#ffffff;border:1px solid #E2E8F0;border-radius:16px;padding:16px;box-shadow:0 4px 12px rgba(13,59,34,0.04);">
                <span style="font-size:10px;text-transform:uppercase;font-weight:700;letter-spacing:0.05em;color:#64748B;display:block;margin-bottom:4px;">Seminar Proposal</span>
                <span id="ppb-cnt-proposal" style="font-family:'Fraunces',Georgia,serif;font-size:26px;font-weight:700;color:#0D3B22;"><?php echo $count_proposal; ?></span>
            </div>
            <div style="background:#ffffff;border:1px solid #E2E8F0;border-radius:16px;padding:16px;box-shadow:0 4px 12px rgba(13,59,34,0.04);">
                <span style="font-size:10px;text-transform:uppercase;font-weight:700;letter-spacing:0.05em;color:#64748B;display:block;margin-bottom:4px;">Seminar Hasil</span>
                <span id="ppb-cnt-hasil" style="font-family:'Fraunces',Georgia,serif;font-size:26px;font-weight:700;color:#0D3B22;"><?php echo $count_hasil; ?></span>
            </div>
            <div style="background:#ffffff;border:1px solid #E2E8F0;border-radius:16px;padding:16px;box-shadow:0 4px 12px rgba(13,59,34,0.04);">
                <span style="font-size:10px;text-transform:uppercase;font-weight:700;letter-spacing:0.05em;color:#64748B;display:block;margin-bottom:4px;">Ujian Skripsi</span>
                <span id="ppb-cnt-skripsi" style="font-family:'Fraunces',Georgia,serif;font-size:26px;font-weight:700;color:#0D3B22;"><?php echo $count_skripsi; ?></span>
            </div>
            <div style="background:#0D3B22;border:1px solid #185333;border-radius:16px;padding:16px;box-shadow:0 4px 12px rgba(13,59,34,0.15);color:#fff;">
                <span style="font-size:10px;text-transform:uppercase;font-weight:700;letter-spacing:0.05em;color:#F5B82E;display:block;margin-bottom:4px;">Sesi Terjadwal</span>
                <span id="ppb-cnt-terjadwal" style="font-family:'Fraunces',Georgia,serif;font-size:26px;font-weight:700;color:#F5B82E;"><?php echo $count_terjadwal; ?></span>
            </div>
        </div>

        <!-- Mode Switcher: Tampilan Kartu vs Tampilan Kalender Bulanan -->
        <div style="background:#ffffff;border:1px solid #E2E8F0;border-radius:20px;padding:14px 20px;margin-bottom:24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;box-shadow:0 4px 16px rgba(13,59,34,0.04);">
            <div>
                <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.12em;color:#B45309;display:block;margin-bottom:2px;">Format Penayangan</span>
                <h3 style="font-family:'Fraunces',Georgia,serif;font-size:18px;font-weight:600;color:#0F172A;margin:0;">Pilih Format Tampilan Jadwal Sidang</h3>
            </div>
            <div style="background:#FAF8F5;border:1px solid #E2E8F0;border-radius:12px;padding:4px;display:inline-flex;align-items:center;gap:4px;">
                <button type="button" id="ppb-view-btn-cards" class="ppb-view-toggle-btn active" style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:9px;font-size:12px;font-weight:700;cursor:pointer;border:none;background:#0D3B22;color:#F5B82E;transition:all 0.2s;">
                    📋 Daftar Kartu
                </button>
                <button type="button" id="ppb-view-btn-cal" class="ppb-view-toggle-btn" style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:9px;font-size:12px;font-weight:700;cursor:pointer;border:none;background:transparent;color:#475569;transition:all 0.2s;">
                    📅 Kalender Akademik
                </button>
            </div>
        </div>

        <!-- ================================================================== -->
        <!-- VIEW 1: DAFTAR KARTU & SEARCH TOOLBAR                              -->
        <!-- ================================================================== -->
        <div id="ppb-view-cards-container" style="display:block;">
            <!-- Filter & Search Toolbar -->
            <div style="background:#ffffff;border:1px solid #E2E8F0;border-radius:24px;padding:18px 20px;box-shadow:0 8px 24px -8px rgba(13,59,34,0.06);margin-bottom:24px;">
                <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:14px;margin-bottom:14px;">
                    <!-- Search Box -->
                    <div style="flex:1;min-width:260px;position:relative;">
                        <span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:14px;">🔍</span>
                        <input type="text" id="ppb-jadwal-search" placeholder="Cari mahasiswa, NIM, judul skripsi, atau dosen..." style="width:100%;box-sizing:border-box;padding:10px 14px 10px 38px;border:1px solid #E2E8F0;border-radius:12px;font-size:13px;background:#FAF8F5;color:#1e293b;outline:none;transition:all 0.2s;" onfocus="this.style.background='#fff';this.style.borderColor='#0D3B22';" onblur="this.style.background='#FAF8F5';this.style.borderColor='#E2E8F0';" />
                    </div>

                    <!-- Status Filter -->
                    <div style="display:flex;align-items:center;gap:6px;overflow-x:auto;">
                        <span style="font-size:12px;font-weight:600;color:#64748b;margin-right:4px;">Status:</span>
                        <button type="button" class="ppb-status-btn active" data-status="semua" style="padding:6px 12px;border-radius:8px;font-size:11px;font-weight:700;cursor:pointer;border:none;background:#0D3B22;color:#fff;transition:all 0.2s;">Semua</button>
                        <button type="button" class="ppb-status-btn" data-status="terjadwal" style="padding:6px 12px;border-radius:8px;font-size:11px;font-weight:700;cursor:pointer;border:none;background:#f1f5f9;color:#475569;transition:all 0.2s;">Terjadwal</button>
                        <button type="button" class="ppb-status-btn" data-status="selesai" style="padding:6px 12px;border-radius:8px;font-size:11px;font-weight:700;cursor:pointer;border:none;background:#f1f5f9;color:#475569;transition:all 0.2s;">Selesai</button>
                    </div>
                </div>

                <!-- Category Tab Pills -->
                <div style="border-top:1px solid #f1f5f9;padding-top:12px;display:flex;align-items:center;gap:8px;overflow-x:auto;-webkit-overflow-scrolling:touch;">
                    <button type="button" class="ppb-kategori-btn active" data-jenis="semua" style="padding:7px 16px;border-radius:12px;font-size:12px;font-weight:700;cursor:pointer;border:none;background:#F5B82E;color:#0D3B22;white-space:nowrap;transition:all 0.2s;">Semua Tahapan (<?php echo $count_total; ?>)</button>
                    <button type="button" class="ppb-kategori-btn" data-jenis="seminar_proposal" style="padding:7px 16px;border-radius:12px;font-size:12px;font-weight:700;cursor:pointer;border:none;background:#f1f5f9;color:#475569;white-space:nowrap;transition:all 0.2s;">Seminar Proposal (<?php echo $count_proposal; ?>)</button>
                    <button type="button" class="ppb-kategori-btn" data-jenis="seminar_hasil" style="padding:7px 16px;border-radius:12px;font-size:12px;font-weight:700;cursor:pointer;border:none;background:#f1f5f9;color:#475569;white-space:nowrap;transition:all 0.2s;">Seminar Hasil (<?php echo $count_hasil; ?>)</button>
                    <button type="button" class="ppb-kategori-btn" data-jenis="ujian_skripsi" style="padding:7px 16px;border-radius:12px;font-size:12px;font-weight:700;cursor:pointer;border:none;background:#f1f5f9;color:#475569;white-space:nowrap;transition:all 0.2s;">Ujian Skripsi (<?php echo $count_skripsi; ?>)</button>
                </div>
            </div>

            <!-- Schedule Cards Grid -->
            <div id="ppb-jadwal-cards-container" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(360px, 1fr));gap:20px;">
                <?php foreach ($items as $item): 
                    $badge_bg = '#EFF6FF';
                    $badge_color = '#1E40AF';
                    $badge_border = '#BFDBFE';
                    $badge_label = 'Seminar Proposal';

                    if ($item['jenis_ujian'] === 'seminar_hasil') {
                        $badge_bg = '#FFFBEB';
                        $badge_color = '#92400E';
                        $badge_border = '#FDE68A';
                        $badge_label = 'Seminar Hasil';
                    } elseif ($item['jenis_ujian'] === 'ujian_skripsi') {
                        $badge_bg = '#FAF5FF';
                        $badge_color = '#6B21A8';
                        $badge_border = '#E9D5FF';
                        $badge_label = 'Ujian Skripsi (Tutup)';
                    }

                    $tgl_obj = strtotime($item['tanggal']);
                    $hari_id = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'][date('l', $tgl_obj)];
                    $bln_id  = ['Jan'=>'Jan','Feb'=>'Feb','Mar'=>'Mar','Apr'=>'Apr','May'=>'Mei','Jun'=>'Jun','Jul'=>'Jul','Aug'=>'Agu','Sep'=>'Sep','Oct'=>'Okt','Nov'=>'Nov','Dec'=>'Des'][date('M', $tgl_obj)];
                    $tgl_formatted = $hari_id . ', ' . date('d', $tgl_obj) . ' ' . $bln_id . ' ' . date('Y', $tgl_obj);
                ?>
                <div class="ppb-jadwal-card" 
                     data-jenis="<?php echo esc_attr($item['jenis_ujian']); ?>" 
                     data-status="<?php echo esc_attr($item['status']); ?>" 
                     data-search="<?php echo esc_attr(strtolower($item['nama_mahasiswa'] . ' ' . $item['nim'] . ' ' . $item['judul_penelitian'] . ' ' . $item['pembimbing_1'] . ' ' . $item['penguji_1'])); ?>" 
                     style="background:#ffffff;border:1px solid #E2E8F0;border-radius:24px;padding:24px;box-shadow:0 4px 16px rgba(13,59,34,0.04);display:flex;flex-direction:column;justify-content:space-between;transition:transform 0.2s, box-shadow 0.2s;">
                    
                    <div>
                        <!-- Header Badges -->
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:14px;">
                            <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;padding:4px 10px;border-radius:9999px;background:<?php echo $badge_bg; ?>;color:<?php echo $badge_color; ?>;border:1px solid <?php echo $badge_border; ?>;">
                                <?php echo $badge_label; ?>
                            </span>

                            <?php if ($item['status'] === 'terjadwal'): ?>
                                <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:9999px;background:#ECFDF5;color:#065F46;border:1px solid #A7F3D0;font-size:10px;font-weight:700;text-transform:uppercase;">
                                    <span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:#059669;"></span> Terjadwal
                                </span>
                            <?php elseif ($item['status'] === 'sedang_berlangsung'): ?>
                                <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:9999px;background:#FFF1F2;color:#9F1239;border:1px solid #FECDD3;font-size:10px;font-weight:700;text-transform:uppercase;">
                                    <span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:#E11D48;"></span> Berlangsung
                                </span>
                            <?php else: ?>
                                <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:9999px;background:#F1F5F9;color:#475569;border:1px solid #E2E8F0;font-size:10px;font-weight:700;text-transform:uppercase;">
                                    ✓ Selesai
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Research Title -->
                        <h3 style="font-family:'Fraunces',Georgia,serif;font-size:17px;font-weight:600;color:#0F172A;line-height:1.35;margin:0 0 12px 0;">
                            <?php echo esc_html($item['judul_penelitian']); ?>
                        </h3>

                        <!-- Student Info -->
                        <div style="display:flex;align-items:center;gap:8px;font-size:12px;color:#475569;margin-bottom:16px;">
                            <span style="display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:50%;background:#E8F0EA;color:#0D3B22;font-weight:700;font-size:11px;">🎓</span>
                            <strong style="color:#0F172A;"><?php echo esc_html($item['nama_mahasiswa']); ?></strong>
                            <span style="color:#CBD5E1;">•</span>
                            <span style="font-family:monospace;color:#64748B;">NIM: <?php echo esc_html($item['nim']); ?></span>
                        </div>

                        <!-- Time & Room Details -->
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px;">
                            <div style="background:#FAF8F5;border:1px solid #EFECE6;border-radius:12px;padding:10px 12px;">
                                <span style="font-size:10px;color:#854D0E;font-weight:700;display:block;margin-bottom:2px;">🗓️ TANGGAL & WAKTU</span>
                                <div style="font-size:12px;font-weight:600;color:#1E293B;"><?php echo esc_html($tgl_formatted); ?></div>
                                <div style="font-size:11px;color:#64748B;margin-top:2px;"><?php echo esc_html($item['jam_mulai'] . ' - ' . $item['jam_selesai']); ?> WITA</div>
                            </div>

                            <div style="background:#FAF8F5;border:1px solid #EFECE6;border-radius:12px;padding:10px 12px;">
                                <span style="font-size:10px;color:#0D3B22;font-weight:700;display:block;margin-bottom:2px;">📍 LOKASI / RUANG</span>
                                <div style="font-size:12px;font-weight:600;color:#1E293B;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo esc_html($item['ruangan']); ?></div>
                                <?php if (!empty($item['link_daring'])): ?>
                                    <a href="<?php echo esc_url($item['link_daring']); ?>" target="_blank" rel="noopener noreferrer" style="font-size:11px;color:#2563EB;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:4px;margin-top:2px;">
                                        📹 Tautan Daring ↗
                                    </a>
                                <?php else: ?>
                                    <span style="font-size:11px;color:#64748B;margin-top:2px;display:block;">Sidang Tatap Muka</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Examiners & Supervisors -->
                        <div style="border-top:1px solid #F1F5F9;padding-top:14px;display:grid;grid-template-columns:1fr 1fr;gap:10px;font-size:11px;">
                            <div style="background:#F8FAFC;border:1px solid #F1F5F9;border-radius:12px;padding:10px 12px;">
                                <span style="font-size:9px;font-weight:700;text-transform:uppercase;color:#64748B;letter-spacing:0.04em;display:block;margin-bottom:4px;">TIM PEMBIMBING</span>
                                <div style="font-weight:500;color:#1E293B;line-height:1.4;">1. <?php echo esc_html($item['pembimbing_1']); ?></div>
                                <div style="font-weight:500;color:#1E293B;line-height:1.4;margin-top:2px;">2. <?php echo esc_html($item['pembimbing_2']); ?></div>
                            </div>

                            <div style="background:#F8FAFC;border:1px solid #F1F5F9;border-radius:12px;padding:10px 12px;">
                                <span style="font-size:9px;font-weight:700;text-transform:uppercase;color:#64748B;letter-spacing:0.04em;display:block;margin-bottom:4px;">TIM PENGUJI</span>
                                <div style="font-weight:500;color:#1E293B;line-height:1.4;">1. <?php echo esc_html($item['penguji_1']); ?></div>
                                <div style="font-weight:500;color:#1E293B;line-height:1.4;margin-top:2px;">2. <?php echo esc_html($item['penguji_2']); ?></div>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top:16px;border-top:1px solid #F1F5F9;padding-top:10px;display:flex;align-items:center;justify-content:space-between;font-size:11px;color:#94A3B8;">
                        <span>Jurusan Psikologi Pendidikan &amp; Bimbingan</span>
                        <span style="font-family:monospace;"><?php echo esc_html($item['id']); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Empty State Alert -->
            <div id="ppb-jadwal-empty" style="display:none;background:#ffffff;border:1px solid #E2E8F0;border-radius:24px;padding:40px 20px;text-align:center;box-shadow:0 4px 12px rgba(0,0,0,0.03);margin-top:20px;">
                <div style="font-size:36px;margin-bottom:8px;">🗓️</div>
                <h4 style="font-family:'Fraunces',Georgia,serif;font-size:18px;color:#0F172A;margin:0 0 6px 0;">Tidak Ada Jadwal Ditemukan</h4>
                <p style="font-size:13px;color:#64748B;margin:0;">Tidak ada sesi seminar atau ujian yang sesuai dengan kata kunci atau filter yang Anda pilih.</p>
            </div>
        </div>

        <!-- ================================================================== -->
        <!-- VIEW 2: KALENDER BULANAN INTERAKTIF                                -->
        <!-- ================================================================== -->
        <div id="ppb-view-cal-container" style="display:none;">
            <div style="background:#ffffff;border:1px solid #E2E8F0;border-radius:24px;padding:24px;box-shadow:0 8px 24px -8px rgba(13,59,34,0.06);margin-bottom:24px;">
                
                <!-- Calendar Header Bar -->
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;margin-bottom:20px;border-bottom:1px solid #F1F5F9;padding-bottom:16px;">
                    <div style="display:flex;align-items:center;gap:12px;">
                        <h2 id="ppb-cal-title" style="font-family:'Fraunces',Georgia,serif;font-size:clamp(20px, 3vw, 26px);font-weight:600;color:#0D3B22;margin:0;">
                            Oktober 2026
                        </h2>
                        <button type="button" id="ppb-cal-btn-today" style="padding:4px 12px;border-radius:8px;font-size:11px;font-weight:700;border:1px solid #0D3B22;background:#E8F0EA;color:#0D3B22;cursor:pointer;transition:all 0.2s;">
                            Hari Ini
                        </button>
                    </div>

                    <!-- Month Nav Controls -->
                    <div style="display:flex;align-items:center;gap:8px;">
                        <button type="button" id="ppb-cal-btn-prev" style="display:inline-flex;align-items:center;gap:4px;padding:7px 14px;border-radius:10px;border:1px solid #E2E8F0;background:#FAF8F5;color:#1E293B;font-size:12px;font-weight:600;cursor:pointer;transition:all 0.2s;">
                            ‹ Bulan Lalu
                        </button>
                        <button type="button" id="ppb-cal-btn-next" style="display:inline-flex;align-items:center;gap:4px;padding:7px 14px;border-radius:10px;border:1px solid #E2E8F0;background:#FAF8F5;color:#1E293B;font-size:12px;font-weight:600;cursor:pointer;transition:all 0.2s;">
                            Bulan Berikut ›
                        </button>
                    </div>
                </div>

                <!-- Calendar Legend Pills -->
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:18px;font-size:11px;font-weight:600;">
                    <span style="color:#64748B;">Legenda Sidang:</span>
                    <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:9999px;background:#EFF6FF;color:#1E40AF;border:1px solid #BFDBFE;">
                        <span style="width:6px;height:6px;border-radius:50%;background:#2563EB;"></span> Seminar Proposal
                    </span>
                    <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:9999px;background:#FFFBEB;color:#92400E;border:1px solid #FDE68A;">
                        <span style="width:6px;height:6px;border-radius:50%;background:#D97706;"></span> Seminar Hasil
                    </span>
                    <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:9999px;background:#FAF5FF;color:#6B21A8;border:1px solid #E9D5FF;">
                        <span style="width:6px;height:6px;border-radius:50%;background:#7C3AED;"></span> Ujian Skripsi
                    </span>
                </div>

                <!-- Calendar Day Headers (Sen s/d Min) -->
                <div style="display:grid;grid-template-columns:repeat(7, 1fr);gap:6px;text-align:center;font-size:12px;font-weight:700;color:#64748B;margin-bottom:8px;">
                    <div style="background:#FAF8F5;padding:8px 4px;border-radius:8px;">Senin</div>
                    <div style="background:#FAF8F5;padding:8px 4px;border-radius:8px;">Selasa</div>
                    <div style="background:#FAF8F5;padding:8px 4px;border-radius:8px;">Rabu</div>
                    <div style="background:#FAF8F5;padding:8px 4px;border-radius:8px;">Kamis</div>
                    <div style="background:#FAF8F5;padding:8px 4px;border-radius:8px;">Jumat</div>
                    <div style="background:#FAF8F5;padding:8px 4px;border-radius:8px;color:#0D3B22;">Sabtu</div>
                    <div style="background:#FAF8F5;padding:8px 4px;border-radius:8px;color:#DC2626;">Minggu</div>
                </div>

                <!-- Calendar Grid (Days Cells) -->
                <div id="ppb-cal-grid" style="display:grid;grid-template-columns:repeat(7, 1fr);gap:6px;">
                    <!-- Injected dynamically via JS -->
                </div>
            </div>

            <!-- Active Day Detailed Schedule Box -->
            <div id="ppb-cal-selected-box" style="background:#ffffff;border:1.5px solid #0D3B22;border-radius:24px;padding:24px;box-shadow:0 8px 24px -8px rgba(13,59,34,0.12);margin-bottom:24px;">
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:16px;border-bottom:1px solid #F1F5F9;padding-bottom:12px;">
                    <div>
                        <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.12em;color:#B45309;display:block;margin-bottom:2px;">Agenda Sidang Terpilih</span>
                        <h3 id="ppb-cal-selected-date-title" style="font-family:'Fraunces',Georgia,serif;font-size:19px;font-weight:600;color:#0D3B22;margin:0;">
                            Memuat Sesi Ujian...
                        </h3>
                    </div>
                    <span id="ppb-cal-selected-count-badge" style="font-size:11px;font-weight:700;background:#E8F0EA;color:#0D3B22;padding:4px 12px;border-radius:9999px;">
                        0 Sesi
                    </span>
                </div>

                <div id="ppb-cal-selected-cards" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(340px, 1fr));gap:16px;">
                    <!-- Injected dynamically when date clicked -->
                </div>
            </div>
        </div>

        <!-- Integrated Portal SIPADU Card -->
        <div style="margin-top:36px;background:linear-gradient(135deg, #0D3B22 0%, #185333 100%);border-radius:24px;padding: clamp(24px, 4vw, 36px);color:#ffffff;box-shadow:0 12px 32px rgba(13,59,34,0.18);display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:20px;">
            <div style="max-width:680px;">
                <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:#F5B82E;display:block;margin-bottom:6px;">
                    ✨ Ingin Mengajukan Ujian?
                </span>
                <h3 style="font-family:'Fraunces',Georgia,serif;font-size:clamp(20px, 2.8vw, 26px);font-weight:400;margin:0 0 8px 0;line-height:1.25;">
                    Pendaftaran Ujian Terintegrasi di Portal SIPADU
                </h3>
                <p style="font-size:13px;color:rgba(255,255,255,0.85);line-height:1.6;margin:0;">
                    Mahasiswa yang telah mendapatkan lembar persetujuan pembimbing dapat mengajukan berkas dan memilih slot ujian secara mandiri melalui modul SIPADU setelah masuk ke akun portal.
                </p>
            </div>
            <div>
                <a href="https://bk-connect-fipunm.vercel.app/sipadu" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;gap:8px;padding:12px 24px;border-radius:9999px;background:#F5B82E;color:#0D3B22;font-size:13px;font-weight:700;text-decoration:none;box-shadow:0 4px 14px rgba(245,184,46,0.4);transition:all 0.2s;">
                    Masuk Portal SIPADU →
                </a>
            </div>
        </div>

        <!-- Client-Side Filter, Live Search & Dynamic Monthly Calendar Engine -->
        <script>
        (function(){
            const allItems = <?php echo $items_json; ?>;

            // View Switcher Elements
            const btnViewCards = document.getElementById("ppb-view-btn-cards");
            const btnViewCal   = document.getElementById("ppb-view-btn-cal");
            const boxCardsView = document.getElementById("ppb-view-cards-container");
            const boxCalView   = document.getElementById("ppb-view-cal-container");

            function setViewMode(mode) {
                if (mode === "calendar") {
                    boxCardsView.style.display = "none";
                    boxCalView.style.display = "block";
                    btnViewCal.style.background = "#0D3B22";
                    btnViewCal.style.color = "#F5B82E";
                    btnViewCards.style.background = "transparent";
                    btnViewCards.style.color = "#475569";
                    renderCalendar(calCurrentYear, calCurrentMonth);
                } else {
                    boxCalView.style.display = "none";
                    boxCardsView.style.display = "block";
                    btnViewCards.style.background = "#0D3B22";
                    btnViewCards.style.color = "#F5B82E";
                    btnViewCal.style.background = "transparent";
                    btnViewCal.style.color = "#475569";
                }
            }

            if (btnViewCards && btnViewCal) {
                btnViewCards.addEventListener("click", () => setViewMode("cards"));
                btnViewCal.addEventListener("click", () => setViewMode("calendar"));
            }

            // 1. Live Filter & Search for Card View
            const searchInput = document.getElementById("ppb-jadwal-search");
            const statusBtns  = document.querySelectorAll(".ppb-status-btn");
            const katBtns     = document.querySelectorAll(".ppb-kategori-btn");
            const cards       = document.querySelectorAll(".ppb-jadwal-card");
            const emptyState  = document.getElementById("ppb-jadwal-empty");

            let currentSearch = "";
            let currentStatus = "semua";
            let currentJenis  = "semua";

            function filterJadwal() {
                let visibleCount = 0;
                cards.forEach(card => {
                    const cJenis  = card.getAttribute("data-jenis");
                    const cStatus = card.getAttribute("data-status");
                    const cSearch = card.getAttribute("data-search");

                    const matchSearch = (currentSearch === "" || cSearch.indexOf(currentSearch) !== -1);
                    const matchStatus = (currentStatus === "semua" || cStatus === currentStatus);
                    const matchJenis  = (currentJenis === "semua" || cJenis === currentJenis);

                    if (matchSearch && matchStatus && matchJenis) {
                        card.style.display = "flex";
                        visibleCount++;
                    } else {
                        card.style.display = "none";
                    }
                });

                if (emptyState) {
                    emptyState.style.display = (visibleCount === 0) ? "block" : "none";
                }
            }

            if (searchInput) {
                searchInput.addEventListener("input", function(e) {
                    currentSearch = e.target.value.toLowerCase().trim();
                    filterJadwal();
                });
            }

            statusBtns.forEach(btn => {
                btn.addEventListener("click", function() {
                    statusBtns.forEach(b => {
                        b.classList.remove("active");
                        b.style.background = "#f1f5f9";
                        b.style.color = "#475569";
                    });
                    this.classList.add("active");
                    this.style.background = "#0D3B22";
                    this.style.color = "#ffffff";
                    currentStatus = this.getAttribute("data-status");
                    filterJadwal();
                });
            });

            katBtns.forEach(btn => {
                btn.addEventListener("click", function() {
                    katBtns.forEach(b => {
                        b.classList.remove("active");
                        b.style.background = "#f1f5f9";
                        b.style.color = "#475569";
                    });
                    this.classList.add("active");
                    this.style.background = "#F5B82E";
                    this.style.color = "#0D3B22";
                    currentJenis = this.getAttribute("data-jenis");
                    filterJadwal();
                });
            });

            // 2. Dynamic Monthly Calendar Engine
            // Auto-detect initial year & month from seeded items or fallback to today
            let calCurrentYear = 2026;
            let calCurrentMonth = 9; // 0-indexed: 9 = Oktober
            let selectedDateStr = "2026-10-26";

            if (allItems.length > 0) {
                const firstUpcoming = allItems.find(it => it.status === "terjadwal") || allItems[0];
                if (firstUpcoming && firstUpcoming.tanggal) {
                    const parts = firstUpcoming.tanggal.split("-");
                    calCurrentYear = parseInt(parts[0], 10);
                    calCurrentMonth = parseInt(parts[1], 10) - 1;
                    selectedDateStr = firstUpcoming.tanggal;
                }
            }

            const monthNamesID = [
                "Januari", "Februari", "Maret", "April", "Mei", "Juni",
                "Juli", "Agustus", "September", "Oktober", "November", "Desember"
            ];
            const dayNamesID = [
                "Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"
            ];

            const calTitleEl     = document.getElementById("ppb-cal-title");
            const calGridEl      = document.getElementById("ppb-cal-grid");
            const calBtnPrev     = document.getElementById("ppb-cal-btn-prev");
            const calBtnNext     = document.getElementById("ppb-cal-btn-next");
            const calBtnToday    = document.getElementById("ppb-cal-btn-today");
            const selDateTitleEl = document.getElementById("ppb-cal-selected-date-title");
            const selCountEl     = document.getElementById("ppb-cal-selected-count-badge");
            const selCardsEl     = document.getElementById("ppb-cal-selected-cards");

            function renderCalendar(year, month) {
                if (!calGridEl) return;

                if (calTitleEl) {
                    calTitleEl.textContent = monthNamesID[month] + " " + year;
                }

                // Days calculation
                const firstDayIndex = new Date(year, month, 1).getDay(); // 0 = Sunday
                // Adjust for Monday start (0=Mon ... 6=Sun)
                const startDay = (firstDayIndex === 0) ? 6 : firstDayIndex - 1;
                const totalDays = new Date(year, month + 1, 0).getDate();

                let html = "";

                // Empty padding cells before 1st of month
                for (let i = 0; i < startDay; i++) {
                    html += '<div style="background:#FAF8F5;border:1px dashed #E2E8F0;border-radius:12px;min-height:95px;opacity:0.4;"></div>';
                }

                const todayObj = new Date();
                const todayStr = todayObj.getFullYear() + "-" + 
                    String(todayObj.getMonth() + 1).padStart(2, '0') + "-" + 
                    String(todayObj.getDate()).padStart(2, '0');

                // Day cells
                for (let day = 1; day <= totalDays; day++) {
                    const dayStr = String(day).padStart(2, '0');
                    const monthStr = String(month + 1).padStart(2, '0');
                    const fullDateStr = year + "-" + monthStr + "-" + dayStr;

                    const dayExams = allItems.filter(it => it.tanggal === fullDateStr);
                    const isToday = (fullDateStr === todayStr);
                    const isSelected = (fullDateStr === selectedDateStr);

                    let cellBorder = isSelected ? "2px solid #F5B82E" : "1px solid #E2E8F0";
                    let cellBg = isSelected ? "#FFFDF5" : (dayExams.length > 0 ? "#ffffff" : "#ffffff");
                    let cellShadow = isSelected ? "box-shadow:0 4px 14px rgba(245,184,46,0.35);" : "";

                    html += '<div class="ppb-cal-day-cell" data-date="' + fullDateStr + '" style="background:' + cellBg + ';border:' + cellBorder + ';border-radius:12px;min-height:95px;padding:8px;cursor:pointer;display:flex;flex-direction:column;justify-content:space-between;transition:all 0.15s;' + cellShadow + '" onclick="window.ppbSelectCalDate(\'' + fullDateStr + '\')">';
                    
                    // Day cell top bar: number + count badge
                    html += '<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px;">';
                    if (isToday) {
                        html += '<span style="display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:50%;background:#0D3B22;color:#F5B82E;font-size:11px;font-weight:700;">' + day + '</span>';
                    } else {
                        html += '<span style="font-size:12px;font-weight:700;color:' + (dayExams.length > 0 ? '#0D3B22' : '#64748B') + ';">' + day + '</span>';
                    }

                    if (dayExams.length > 0) {
                        html += '<span style="font-size:10px;font-weight:700;background:#E8F0EA;color:#0D3B22;padding:2px 6px;border-radius:6px;">' + dayExams.length + ' Ujian</span>';
                    }
                    html += '</div>';

                    // Exam event pills inside day cell
                    html += '<div style="display:flex;flex-direction:column;gap:3px;overflow:hidden;">';
                    dayExams.slice(0, 2).forEach(ex => {
                        let pillBg = "#EFF6FF";
                        let pillColor = "#1E40AF";
                        let shortLabel = "Sempro";

                        if (ex.jenis_ujian === "seminar_hasil") {
                            pillBg = "#FFFBEB";
                            pillColor = "#92400E";
                            shortLabel = "Semhas";
                        } else if (ex.jenis_ujian === "ujian_skripsi") {
                            pillBg = "#FAF5FF";
                            pillColor = "#6B21A8";
                            shortLabel = "Skripsi";
                        }

                        html += '<div style="background:' + pillBg + ';color:' + pillColor + ';font-size:10px;font-weight:700;padding:2px 5px;border-radius:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;line-height:1.2;" title="' + ex.nama_mahasiswa + ' (' + shortLabel + ')">';
                        html += ex.jam_mulai + ' ' + shortLabel + ' · ' + ex.nama_mahasiswa.split(' ')[0];
                        html += '</div>';
                    });

                    if (dayExams.length > 2) {
                        html += '<div style="font-size:9px;color:#64748B;font-weight:600;padding-left:2px;">+' + (dayExams.length - 2) + ' lainnya</div>';
                    }
                    html += '</div>';

                    html += '</div>';
                }

                calGridEl.innerHTML = html;
                renderSelectedDaySchedule(selectedDateStr);
            }

            function renderSelectedDaySchedule(dateStr) {
                if (!selCardsEl) return;

                const dayExams = allItems.filter(it => it.tanggal === dateStr);
                const dateObj = new Date(dateStr + "T00:00:00");
                const dayName = dayNamesID[dateObj.getDay()] || "Hari";
                const dateNum = dateObj.getDate();
                const monthName = monthNamesID[dateObj.getMonth()] || "Bulan";
                const yearNum = dateObj.getFullYear();

                const formattedTitle = dayName + ", " + dateNum + " " + monthName + " " + yearNum;

                if (selDateTitleEl) {
                    selDateTitleEl.textContent = formattedTitle;
                }
                if (selCountEl) {
                    selCountEl.textContent = dayExams.length + " Sesi Terjadwal";
                }

                if (dayExams.length === 0) {
                    selCardsEl.innerHTML = '<div style="grid-column:1/-1;background:#FAF8F5;border:1px dashed #E2E8F0;border-radius:16px;padding:30px;text-align:center;color:#64748B;font-size:13px;">' +
                        '🍃 Belum ada jadwal seminar proposal, seminar hasil, atau ujian skripsi yang diselenggarakan pada tanggal <strong>' + formattedTitle + '</strong>.' +
                        '<div style="margin-top:6px;font-size:11px;color:#94A3B8;">Silakan klik tanggal lain yang bertanda sesi sidang pada kalender di atas.</div>' +
                    '</div>';
                    return;
                }

                let cardsHtml = "";
                dayExams.forEach(item => {
                    let badgeBg = "#EFF6FF";
                    let badgeColor = "#1E40AF";
                    let badgeBorder = "#BFDBFE";
                    let badgeLabel = "Seminar Proposal";

                    if (item.jenis_ujian === "seminar_hasil") {
                        badgeBg = "#FFFBEB";
                        badgeColor = "#92400E";
                        badgeBorder = "#FDE68A";
                        badgeLabel = "Seminar Hasil";
                    } else if (item.jenis_ujian === "ujian_skripsi") {
                        badgeBg = "#FAF5FF";
                        badgeColor = "#6B21A8";
                        badgeBorder = "#E9D5FF";
                        badgeLabel = "Ujian Skripsi (Tutup)";
                    }

                    let statusBadge = "";
                    if (item.status === "terjadwal") {
                        statusBadge = '<span style="display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:9999px;background:#ECFDF5;color:#065F46;border:1px solid #A7F3D0;font-size:10px;font-weight:700;text-transform:uppercase;"><span style="width:5px;height:5px;border-radius:50%;background:#059669;"></span> Terjadwal</span>';
                    } else if (item.status === "sedang_berlangsung") {
                        statusBadge = '<span style="display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:9999px;background:#FFF1F2;color:#9F1239;border:1px solid #FECDD3;font-size:10px;font-weight:700;text-transform:uppercase;"><span style="width:5px;height:5px;border-radius:50%;background:#E11D48;"></span> Berlangsung</span>';
                    } else {
                        statusBadge = '<span style="display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:9999px;background:#F1F5F9;color:#475569;border:1px solid #E2E8F0;font-size:10px;font-weight:700;text-transform:uppercase;">✓ Selesai</span>';
                    }

                    cardsHtml += '<div style="background:#FAF8F5;border:1px solid #E2E8F0;border-radius:18px;padding:20px;display:flex;flex-direction:column;justify-content:space-between;">' +
                        '<div>' +
                            '<div style="display:flex;align-items:center;justify-content:space-between;gap:6px;margin-bottom:10px;">' +
                                '<span style="font-size:10px;font-weight:700;text-transform:uppercase;padding:3px 8px;border-radius:9999px;background:' + badgeBg + ';color:' + badgeColor + ';border:1px solid ' + badgeBorder + ';">' + badgeLabel + '</span>' +
                                statusBadge +
                            '</div>' +
                            '<h4 style="font-family:\'Fraunces\',Georgia,serif;font-size:16px;font-weight:600;color:#0F172A;margin:0 0 10px 0;line-height:1.35;">' + item.judul_penelitian + '</h4>' +
                            '<div style="display:flex;align-items:center;gap:6px;font-size:12px;color:#475569;margin-bottom:12px;">' +
                                '<strong style="color:#0F172A;">🎓 ' + item.nama_mahasiswa + '</strong>' +
                                '<span style="color:#CBD5E1;">•</span>' +
                                '<span style="font-family:monospace;color:#64748B;">NIM: ' + item.nim + '</span>' +
                            '</div>' +
                            '<div style="background:#ffffff;border:1px solid #EFECE6;border-radius:10px;padding:10px;margin-bottom:12px;font-size:11px;color:#1E293B;">' +
                                '<div>⏰ <strong>Waktu:</strong> ' + item.jam_mulai + ' - ' + item.jam_selesai + ' WITA</div>' +
                                '<div style="margin-top:2px;">📍 <strong>Ruang:</strong> ' + item.ruangan + '</div>' +
                                (item.link_daring ? '<div style="margin-top:4px;"><a href="' + item.link_daring + '" target="_blank" style="color:#2563EB;font-weight:600;text-decoration:none;">📹 Tautan Sidang Zoom ↗</a></div>' : '') +
                            '</div>' +
                            '<div style="border-top:1px solid #EFECE6;padding-top:10px;display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:10px;color:#475569;">' +
                                '<div><span style="font-weight:700;color:#64748B;">PEMBIMBING:</span><br>1. ' + item.pembimbing_1 + '<br>2. ' + item.pembimbing_2 + '</div>' +
                                '<div><span style="font-weight:700;color:#64748B;">PENGUJI:</span><br>1. ' + item.penguji_1 + '<br>2. ' + item.penguji_2 + '</div>' +
                            '</div>' +
                        '</div>' +
                    '</div>';
                });

                selCardsEl.innerHTML = cardsHtml;
            }

            window.ppbSelectCalDate = function(dateStr) {
                selectedDateStr = dateStr;
                renderCalendar(calCurrentYear, calCurrentMonth);
                const targetBox = document.getElementById("ppb-cal-selected-box");
                if (targetBox) {
                    targetBox.scrollIntoView({ behavior: "smooth", block: "nearest" });
                }
            };

            if (calBtnPrev) {
                calBtnPrev.addEventListener("click", function() {
                    calCurrentMonth--;
                    if (calCurrentMonth < 0) {
                        calCurrentMonth = 11;
                        calCurrentYear--;
                    }
                    renderCalendar(calCurrentYear, calCurrentMonth);
                });
            }

            if (calBtnNext) {
                calBtnNext.addEventListener("click", function() {
                    calCurrentMonth++;
                    if (calCurrentMonth > 11) {
                        calCurrentMonth = 0;
                        calCurrentYear++;
                    }
                    renderCalendar(calCurrentYear, calCurrentMonth);
                });
            }

            if (calBtnToday) {
                calBtnToday.addEventListener("click", function() {
                    const now = new Date();
                    calCurrentYear = now.getFullYear();
                    calCurrentMonth = now.getMonth();
                    const nowStr = calCurrentYear + "-" + 
                        String(calCurrentMonth + 1).padStart(2, '0') + "-" + 
                        String(now.getDate()).padStart(2, '0');
                    selectedDateStr = nowStr;
                    renderCalendar(calCurrentYear, calCurrentMonth);
                });
            }

            // Initial render
            renderCalendar(calCurrentYear, calCurrentMonth);
        })();
        </script>
    </section>
    <?php
    return ob_get_clean();
}
}
add_shortcode('ppb_jadwal_ujian_list', 'ppb_shortcode_jadwal_ujian_list');

/* ========================================================================== */
/* 8. SHORTCODE: KURIKULUM OBE, DAFTAR MK, RPS LENGKAP & RUBRIK PENILAIAN    */
/* ========================================================================== */
if (!function_exists('ppb_shortcode_kurikulum_rps_sap')) {
function ppb_shortcode_kurikulum_rps_sap($atts = []) {
    $all_courses = function_exists('ppb_get_mata_kuliah_list') ? ppb_get_mata_kuliah_list(true) : [];
    $dosen_list  = function_exists('ppb_get_dosen_directory_cached') ? ppb_get_dosen_directory_cached() : [];
    $acad_links  = function_exists('ppb_akademik_get_all_links') ? ppb_akademik_get_all_links() : [];

    $dosen_map = [];
    foreach ($dosen_list as $d) {
        $sl = sanitize_title($d['slug'] ?? '');
        if ($sl !== '') {
            $dosen_map[$sl] = [
                'slug' => $sl,
                'nama' => (string) ($d['nama_lengkap'] ?? ($d['nama'] ?? $sl)),
                'url'  => home_url('/profil/dosen/' . $sl . '/'),
            ];
        }
    }

    // Kelompokkan MK S1 berdasarkan semester (1–8), sekaligus dukung jenjang S2/S3
    $semesters = [];
    $extra_degrees = [];
    $rps_cards = [];
    $sap_cards = [];

    foreach ($all_courses as $c) {
        $jenjang = strtoupper(trim((string) ($c['jenjang'] ?? 'S1')));
        $sem = max(1, min(8, (int) ($c['semester'] ?? 1)));
        $sks = (int) ($c['sks'] ?? 3);

        if ($jenjang === 'S1' || $jenjang === '') {
            if (!isset($semesters[$sem])) {
                $semesters[$sem] = [];
            }
            $semesters[$sem][] = $c;
        } else {
            $extra_degrees[] = $c;
        }

        if (!empty($c['rps_url']) || !empty($c['cpmk']) || !empty($c['in_kurikulum'])) {
            $rps_cards[] = $c;
        }
        if (!empty($c['sap_url'])) {
            $sap_cards[] = $c;
        }
    }
    ksort($semesters);

    // Urutkan RPS cards: yang sudah ada file RPS atau dosen pengampu muncul lebih dulu
    usort($rps_cards, function($a, $b) {
        $scoreA = (!empty($a['rps_url']) ? 4 : 0) + (!empty($a['sap_url']) ? 2 : 0) + (!empty($a['dosen_slugs']) ? 1 : 0);
        $scoreB = (!empty($b['rps_url']) ? 4 : 0) + (!empty($b['sap_url']) ? 2 : 0) + (!empty($b['dosen_slugs']) ? 1 : 0);
        if ($scoreA !== $scoreB) return $scoreB - $scoreA;
        return strcmp((string) ($a['kode'] ?? ''), (string) ($b['kode'] ?? ''));
    });

    $render_dosen_pills = function($slugs) use ($dosen_map) {
        if (empty($slugs) || !is_array($slugs)) {
            return '<span style="font-size:11px;color:#9CA3AF;font-style:italic;">Tim Dosen Prodi BK</span>';
        }
        $out = [];
        foreach ($slugs as $sl) {
            $sl = sanitize_title($sl);
            if ($sl === '') continue;
            $label = isset($dosen_map[$sl]) ? $dosen_map[$sl]['nama'] : ucwords(str_replace('-', ' ', $sl));
            $url   = isset($dosen_map[$sl]) ? $dosen_map[$sl]['url'] : home_url('/profil/dosen/' . $sl . '/');
            $out[] = '<a href="' . esc_url($url) . '" style="display:inline-flex;align-items:center;gap:3px;background:#E8F0EA;color:#0D3B22;border:1px solid #C5DACB;padding:2px 8px;border-radius:999px;font-size:11px;font-weight:600;text-decoration:none;margin:2px 3px 2px 0;">👨‍🏫 ' . esc_html($label) . '</a>';
        }
        return !empty($out) ? implode('', $out) : '<span style="font-size:11px;color:#9CA3AF;">Tim Dosen Prodi BK</span>';
    };

    $rubrik_konseling_url = $acad_links['rubrik_konseling'] ?? '';
    $rubrik_project_url   = $acad_links['rubrik_project'] ?? '';

    ob_start();
    ?>
    <section class="ppb-kurikulum-suite" style="font-family:'Plus Jakarta Sans',sans-serif;color:#1F2937;margin:30px 0;">
        
        <!-- Tab Navigation Bar -->
        <div style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:25px;border-bottom:2px solid #E5E7EB;padding-bottom:12px;">
            <button type="button" class="ppb-cur-tab-btn is-active" data-tab="tab-mk" style="padding:10px 20px;border-radius:10px;border:none;background:#0D3B22;color:#ffffff;font-size:13.5px;font-weight:700;cursor:pointer;transition:all 0.2s;">
                📚 Struktur Mata Kuliah (<?php echo count($all_courses); ?> MK)
            </button>
            <button type="button" class="ppb-cur-tab-btn" data-tab="tab-rps" style="padding:10px 20px;border-radius:10px;border:1px solid #D1D5DB;background:#ffffff;color:#374151;font-size:13.5px;font-weight:600;cursor:pointer;transition:all 0.2s;">
                📑 Repositori RPS Lengkap
            </button>
            <button type="button" class="ppb-cur-tab-btn" data-tab="tab-sap" style="padding:10px 20px;border-radius:10px;border:1px solid #D1D5DB;background:#ffffff;color:#374151;font-size:13.5px;font-weight:600;cursor:pointer;transition:all 0.2s;">
                🗓️ SAP (Satuan Acara Perkuliahan)
            </button>
            <button type="button" class="ppb-cur-tab-btn" data-tab="tab-rubrik" style="padding:10px 20px;border-radius:10px;border:1px solid #D1D5DB;background:#ffffff;color:#374151;font-size:13.5px;font-weight:600;cursor:pointer;transition:all 0.2s;">
                ⚖️ Rubrik Penilaian OBE
            </button>
        </div>

        <!-- Tab 1: Struktur Mata Kuliah per Semester -->
        <div id="tab-mk" class="ppb-cur-pane" style="display:block;">
            <div style="background:#FAF8F5;border:1px solid #D4E3D8;border-radius:16px;padding:24px;margin-bottom:25px;">
                <h3 style="font-family:'Fraunces',Georgia,serif;font-size:20px;color:#0D3B22;margin:0 0 8px;">
                    Daftar Mata Kuliah Kurikulum OBE 2025 &amp; Dosen Pengampu
                </h3>
                <p style="font-size:13px;color:#4B5563;margin:0;line-height:1.6;">
                    Kurikulum Program Studi Bimbingan dan Konseling FIP UNM mengacu pada KKNI Level 6–9 serta Standar ABKIN. Setiap mata kuliah terhubung otomatis ke profil dosen pengampu dan dokumen RPS/SAP.
                </p>
                <?php if (!empty($acad_links['buku_kurikulum']) || !empty($acad_links['sk_kurikulum'])) : ?>
                    <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:14px;">
                        <?php if (!empty($acad_links['buku_kurikulum'])) : ?>
                            <a href="<?php echo esc_url($acad_links['buku_kurikulum']); ?>" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;gap:6px;background:#0D3B22;color:#F5B82E;font-size:12px;font-weight:700;padding:8px 14px;border-radius:8px;text-decoration:none;">📘 Unduh Buku Kurikulum OBE ↗</a>
                        <?php endif; ?>
                        <?php if (!empty($acad_links['sk_kurikulum'])) : ?>
                            <a href="<?php echo esc_url($acad_links['sk_kurikulum']); ?>" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;gap:6px;background:#ffffff;color:#0D3B22;border:1px solid #D4E3D8;font-size:12px;font-weight:700;padding:8px 14px;border-radius:8px;text-decoration:none;">🏛️ SK Senat Kurikulum ↗</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="ppb-mk-sem-filter" style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-bottom:20px;">
                <span style="font-size:12px;font-weight:700;color:#4B5563;margin-right:4px;">Filter Semester (S1):</span>
                <button type="button" class="ppb-mk-sem-pill is-active" data-mk-sem="all" style="padding:7px 14px;border-radius:9999px;border:1px solid #0D3B22;background:#0D3B22;color:#F5B82E;font-size:12px;font-weight:700;cursor:pointer;transition:all 0.16s;">Semua</button>
                <?php foreach (array_keys($semesters) as $sem_num) : ?>
                    <button type="button" class="ppb-mk-sem-pill" data-mk-sem="<?php echo (int) $sem_num; ?>" style="padding:7px 13px;border-radius:9999px;border:1px solid #D1D5DB;background:#ffffff;color:#374151;font-size:12px;font-weight:600;cursor:pointer;transition:all 0.16s;">Sem <?php echo (int) $sem_num; ?></button>
                <?php endforeach; ?>
                <?php if (!empty($extra_degrees)) : ?>
                    <button type="button" class="ppb-mk-sem-pill" data-mk-sem="pasca" style="padding:7px 13px;border-radius:9999px;border:1px solid #D1D5DB;background:#ffffff;color:#374151;font-size:12px;font-weight:600;cursor:pointer;transition:all 0.16s;">S2 / S3 Pascasarjana</button>
                <?php endif; ?>
            </div>

            <div id="ppb-mk-sem-list" style="display:flex;flex-direction:column;gap:18px;">
                <?php foreach ($semesters as $sem => $mks) :
                    $total_sks = 0;
                    foreach ($mks as $m) {
                        $total_sks += (int) ($m['sks'] ?? 0);
                    }
                ?>
                    <div class="ppb-mk-sem-card" data-mk-sem-card="<?php echo (int) $sem; ?>" style="background:#ffffff;border:1px solid #E5E7EB;border-radius:14px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.03);">
                        <div style="background:#0D3B22;color:#ffffff;padding:12px 20px;display:flex;justify-content:space-between;align-items:center;">
                            <strong style="font-size:14px;letter-spacing:0.02em;">Semester <?php echo (int) $sem; ?> (S1 Sarjana)</strong>
                            <span style="font-size:12px;background:rgba(245,184,46,0.25);color:#F5B82E;padding:2px 10px;border-radius:6px;font-weight:700;">Total: <?php echo (int) $total_sks; ?> SKS</span>
                        </div>
                        <div style="overflow-x:auto;">
                            <table style="width:100%;border-collapse:collapse;font-size:13px;text-align:left;">
                                <thead>
                                    <tr style="background:#F9FAFB;border-bottom:1px solid #E5E7EB;color:#6B7280;font-size:11.5px;text-transform:uppercase;">
                                        <th style="padding:10px 16px;">Kode MK</th>
                                        <th style="padding:10px 16px;">Nama Mata Kuliah &amp; Dosen Pengampu</th>
                                        <th style="padding:10px 16px;text-align:center;">SKS</th>
                                        <th style="padding:10px 16px;">Kelompok / Sifat</th>
                                        <th style="padding:10px 16px;text-align:center;">Dokumen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($mks as $mk) :
                                        $rps_u = function_exists('ppb_clean_document_url') ? ppb_clean_document_url($mk['rps_url'] ?? '') : '';
                                        $sap_u = function_exists('ppb_clean_document_url') ? ppb_clean_document_url($mk['sap_url'] ?? '') : '';
                                    ?>
                                        <tr style="border-bottom:1px solid #F3F4F6;">
                                            <td style="padding:10px 16px;font-family:monospace;font-weight:700;color:#0D3B22;white-space:nowrap;"><?php echo esc_html($mk['kode'] ?? ''); ?></td>
                                            <td style="padding:10px 16px;">
                                                <div style="font-weight:600;color:#1F2937;margin-bottom:4px;"><?php echo esc_html($mk['nama'] ?? ''); ?></div>
                                                <div style="display:flex;flex-wrap:wrap;align-items:center;gap:2px;"><?php echo $render_dosen_pills($mk['dosen_slugs'] ?? []); ?></div>
                                            </td>
                                            <td style="padding:10px 16px;text-align:center;font-weight:700;color:#0D3B22;"><?php echo (int) ($mk['sks'] ?? 0); ?></td>
                                            <td style="padding:10px 16px;color:#4B5563;font-size:12px;"><?php echo esc_html($mk['rumpun'] ?? 'Wajib Prodi'); ?></td>
                                            <td style="padding:10px 16px;text-align:center;white-space:nowrap;">
                                                <?php if ($rps_u !== '' || $sap_u !== '') : ?>
                                                    <div style="display:inline-flex;gap:5px;flex-wrap:wrap;justify-content:center;">
                                                        <?php if ($rps_u !== '') : ?>
                                                            <a href="<?php echo esc_url($rps_u); ?>" target="_blank" rel="noopener noreferrer" style="font-size:11px;font-weight:700;color:#ffffff;background:#0D3B22;padding:4px 9px;border-radius:6px;text-decoration:none;">📄 RPS ↗</a>
                                                        <?php endif; ?>
                                                        <?php if ($sap_u !== '') : ?>
                                                            <a href="<?php echo esc_url($sap_u); ?>" target="_blank" rel="noopener noreferrer" style="font-size:11px;font-weight:700;color:#0D3B22;background:#FEF3C7;border:1px solid #FCD34D;padding:4px 9px;border-radius:6px;text-decoration:none;">🗓️ SAP ↗</a>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php else : ?>
                                                    <button type="button" onclick="window.ppbSwitchCurTab('tab-rps')" style="font-size:11px;font-weight:700;color:#0D3B22;background:#E8F0EA;padding:4px 10px;border:none;border-radius:6px;cursor:pointer;">Lihat RPS &rarr;</button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (!empty($extra_degrees)) : ?>
                    <div class="ppb-mk-sem-card" data-mk-sem-card="pasca" style="background:#ffffff;border:1px solid #E5E7EB;border-radius:14px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.03);">
                        <div style="background:#0D3B22;color:#ffffff;padding:12px 20px;display:flex;justify-content:space-between;align-items:center;">
                            <strong style="font-size:14px;letter-spacing:0.02em;">Mata Kuliah Magister (S2) &amp; Doktor (S3)</strong>
                            <span style="font-size:12px;background:rgba(245,184,46,0.25);color:#F5B82E;padding:2px 10px;border-radius:6px;font-weight:700;"><?php echo count($extra_degrees); ?> Mata Kuliah</span>
                        </div>
                        <div style="overflow-x:auto;">
                            <table style="width:100%;border-collapse:collapse;font-size:13px;text-align:left;">
                                <thead>
                                    <tr style="background:#F9FAFB;border-bottom:1px solid #E5E7EB;color:#6B7280;font-size:11.5px;text-transform:uppercase;">
                                        <th style="padding:10px 16px;">Kode / Jenjang</th>
                                        <th style="padding:10px 16px;">Nama Mata Kuliah &amp; Dosen Pengampu</th>
                                        <th style="padding:10px 16px;text-align:center;">SKS</th>
                                        <th style="padding:10px 16px;">Dokumen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($extra_degrees as $mk) :
                                        $rps_u = function_exists('ppb_clean_document_url') ? ppb_clean_document_url($mk['rps_url'] ?? '') : '';
                                        $sap_u = function_exists('ppb_clean_document_url') ? ppb_clean_document_url($mk['sap_url'] ?? '') : '';
                                    ?>
                                        <tr style="border-bottom:1px solid #F3F4F6;">
                                            <td style="padding:10px 16px;white-space:nowrap;">
                                                <span style="font-family:monospace;font-weight:700;color:#0D3B22;"><?php echo esc_html($mk['kode'] ?? ''); ?></span>
                                                <span style="display:inline-block;margin-left:6px;background:#E8F0EA;color:#0D3B22;font-size:10px;font-weight:700;padding:2px 6px;border-radius:4px;"><?php echo esc_html($mk['jenjang'] ?? 'S2'); ?></span>
                                            </td>
                                            <td style="padding:10px 16px;">
                                                <div style="font-weight:600;color:#1F2937;margin-bottom:4px;"><?php echo esc_html($mk['nama'] ?? ''); ?></div>
                                                <div style="display:flex;flex-wrap:wrap;align-items:center;gap:2px;"><?php echo $render_dosen_pills($mk['dosen_slugs'] ?? []); ?></div>
                                            </td>
                                            <td style="padding:10px 16px;text-align:center;font-weight:700;color:#0D3B22;"><?php echo (int) ($mk['sks'] ?? 0); ?></td>
                                            <td style="padding:10px 16px;">
                                                <?php if ($rps_u !== '' || $sap_u !== '') : ?>
                                                    <div style="display:inline-flex;gap:5px;">
                                                        <?php if ($rps_u !== '') : ?>
                                                            <a href="<?php echo esc_url($rps_u); ?>" target="_blank" rel="noopener noreferrer" style="font-size:11px;font-weight:700;color:#ffffff;background:#0D3B22;padding:4px 9px;border-radius:6px;text-decoration:none;">📄 RPS ↗</a>
                                                        <?php endif; ?>
                                                        <?php if ($sap_u !== '') : ?>
                                                            <a href="<?php echo esc_url($sap_u); ?>" target="_blank" rel="noopener noreferrer" style="font-size:11px;font-weight:700;color:#0D3B22;background:#FEF3C7;border:1px solid #FCD34D;padding:4px 9px;border-radius:6px;text-decoration:none;">🗓️ SAP ↗</a>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php else : ?>
                                                    <span style="font-size:11px;color:#9CA3AF;">Segera Tersedia</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tab 2: Repositori RPS Lengkap -->
        <div id="tab-rps" class="ppb-cur-pane" style="display:none;">
            <div style="background:#FAF8F5;border:1px solid #D4E3D8;border-radius:16px;padding:24px;margin-bottom:25px;">
                <h3 style="font-family:'Fraunces',Georgia,serif;font-size:20px;color:#0D3B22;margin:0 0 8px;">
                    Rencana Pembelajaran Semester (RPS) Terstandar OBE
                </h3>
                <p style="font-size:13px;color:#4B5563;margin:0;line-height:1.6;">
                    Dokumen RPS memuat Capaian Pembelajaran Lulusan (CPL), Capaian Pembelajaran Mata Kuliah (CPMK), deskripsi bahan kajian, metode perkuliahan partisipatif (Case Method &amp; Team-Based Project), dan sistem penilaian.
                </p>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));gap:18px;">
                <?php foreach ($rps_cards as $r_idx => $r) :
                    $rps_u  = function_exists('ppb_clean_document_url') ? ppb_clean_document_url($r['rps_url'] ?? '') : '';
                    $sap_u  = function_exists('ppb_clean_document_url') ? ppb_clean_document_url($r['sap_url'] ?? '') : '';
                    $cpmk_text = trim((string) ($r['cpmk'] ?? ''));
                    if ($cpmk_text === '') {
                        $cpmk_text = 'Menguasai landasan teoretis, prinsip asesmen, dan praksis bimbingan dan konseling OBE pada mata kuliah ' . ($r['nama'] ?? '') . ' (' . (int) ($r['sks'] ?? 3) . ' SKS, Semester ' . (int) ($r['semester'] ?? 1) . ').';
                    }
                    $panel_id = 'ppb-cpmk-panel-' . (int) $r_idx;
                ?>
                    <div style="background:#ffffff;border:1px solid #D4E3D8;border-radius:14px;padding:20px;box-shadow:0 3px 10px rgba(0,0,0,0.03);display:flex;flex-direction:column;justify-content:space-between;">
                        <div>
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                                <span style="font-family:monospace;font-size:11px;font-weight:700;color:#0D3B22;background:#E8F0EA;padding:2px 8px;border-radius:4px;"><?php echo esc_html(($r['kode'] ?? '') . ' · ' . ($r['jenjang'] ?? 'S1')); ?></span>
                                <span style="font-size:11px;color:#059669;font-weight:600;">Sem <?php echo (int) ($r['semester'] ?? 1); ?> · <?php echo (int) ($r['sks'] ?? 3); ?> SKS</span>
                            </div>
                            <h4 style="font-size:15px;font-weight:700;color:#0D3B22;margin:0 0 8px;line-height:1.4;"><?php echo esc_html($r['nama'] ?? ''); ?></h4>
                            <div style="margin:0 0 14px;"><?php echo $render_dosen_pills($r['dosen_slugs'] ?? []); ?></div>
                        </div>
                        <div>
                            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                <?php if ($rps_u !== '') : ?>
                                    <a href="<?php echo esc_url($rps_u); ?>" target="_blank" rel="noopener noreferrer" style="flex:1;text-align:center;background:#0D3B22;color:#ffffff;font-size:12px;font-weight:600;padding:8px 12px;border-radius:8px;text-decoration:none;">📄 Unduh RPS PDF ↗</a>
                                <?php else : ?>
                                    <span style="flex:1;text-align:center;background:#F3F4F6;color:#6B7280;font-size:12px;font-weight:600;padding:8px 12px;border-radius:8px;">⏳ RPS Segera Diunggah</span>
                                <?php endif; ?>
                                <?php if ($sap_u !== '') : ?>
                                    <a href="<?php echo esc_url($sap_u); ?>" target="_blank" rel="noopener noreferrer" style="background:#FEF3C7;color:#92400E;border:1px solid #FCD34D;font-size:12px;font-weight:600;padding:8px 12px;border-radius:8px;text-decoration:none;">🗓️ SAP ↗</a>
                                <?php endif; ?>
                                <button type="button" class="ppb-cpmk-toggle" data-cpmk-target="<?php echo esc_attr($panel_id); ?>" style="background:#FAF8F5;color:#0D3B22;border:1px solid #D4E3D8;font-size:12px;font-weight:600;padding:8px 12px;border-radius:8px;cursor:pointer;">Detail CPMK</button>
                            </div>
                            <div id="<?php echo esc_attr($panel_id); ?>" style="display:none;margin-top:12px;padding:12px;border-radius:10px;background:#FAF8F5;border:1px solid #D4E3D8;font-size:12px;color:#374151;line-height:1.55;">
                                <strong style="display:block;color:#0D3B22;margin-bottom:4px;">Capaian Pembelajaran &amp; Rumpun (<?php echo esc_html($r['rumpun'] ?? 'Wajib Prodi'); ?>):</strong>
                                <?php echo nl2br(esc_html($cpmk_text)); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Tab 3: SAP (Satuan Acara Perkuliahan) -->
        <div id="tab-sap" class="ppb-cur-pane" style="display:none;">
            <div style="background:#FAF8F5;border:1px solid #D4E3D8;border-radius:16px;padding:24px;margin-bottom:25px;">
                <h3 style="font-family:'Fraunces',Georgia,serif;font-size:20px;color:#0D3B22;margin:0 0 8px;">
                    Satuan Acara Perkuliahan (SAP) Mingguan
                </h3>
                <p style="font-size:13px;color:#4B5563;margin:0;line-height:1.6;">
                    Panduan operasional tatap muka per minggu (16 minggu perkuliahan termasuk UTS dan UAS) yang menjabarkan aktivitas dosen dan mahasiswa, media ajar, dan instrumen asesmen harian.
                </p>
            </div>
            <div style="background:#ffffff;border:1px solid #E5E7EB;border-radius:14px;padding:24px;box-shadow:0 2px 10px rgba(0,0,0,0.03);margin-bottom:22px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:8px;">
                    <strong style="color:#0D3B22;font-size:16px;">Contoh Matriks 16 Pertemuan SAP (Mata Kuliah Inti)</strong>
                    <span style="font-size:12px;color:#6B7280;">Standar Akreditasi LAMDIK Kriteria 6</span>
                </div>
                <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(220px, 1fr));gap:12px;">
                    <div style="background:#F9FAFB;padding:12px;border-radius:8px;border:1px solid #E5E7EB;"><strong style="font-size:12px;color:#0D3B22;display:block;">Minggu 1–2:</strong><span style="font-size:12px;color:#4B5563;">Orientasi CPL, CPMK, Kontrak Kuliah &amp; Konsep Fondasi</span></div>
                    <div style="background:#F9FAFB;padding:12px;border-radius:8px;border:1px solid #E5E7EB;"><strong style="font-size:12px;color:#0D3B22;display:block;">Minggu 3–7:</strong><span style="font-size:12px;color:#4B5563;">Kajian Teori Mendalam, Studi Kasus (Case Method) &amp; Diskusi</span></div>
                    <div style="background:#FEF3C7;padding:12px;border-radius:8px;border:1px solid #FCD34D;"><strong style="font-size:12px;color:#92400E;display:block;">Minggu 8:</strong><span style="font-size:12px;color:#78350F;">Evaluasi Tengah Semester (UTS) / Tes Penguasaan Konsep</span></div>
                    <div style="background:#F9FAFB;padding:12px;border-radius:8px;border:1px solid #E5E7EB;"><strong style="font-size:12px;color:#0D3B22;display:block;">Minggu 9–14:</strong><span style="font-size:12px;color:#4B5563;">Praktikum Simulasi, Team-Based Project &amp; Penyusunan Portofolio</span></div>
                    <div style="background:#F9FAFB;padding:12px;border-radius:8px;border:1px solid #E5E7EB;"><strong style="font-size:12px;color:#0D3B22;display:block;">Minggu 15:</strong><span style="font-size:12px;color:#4B5563;">Gelar Karya / Presentasi Laporan Proyek Konseling</span></div>
                    <div style="background:#DCFCE7;padding:12px;border-radius:8px;border:1px solid #86EFAC;"><strong style="font-size:12px;color:#166534;display:block;">Minggu 16:</strong><span style="font-size:12px;color:#14532D;">Evaluasi Akhir Semester (UAS) / Uji Keterampilan Portofolio</span></div>
                </div>
            </div>

            <?php if (!empty($sap_cards)) : ?>
            <div style="background:#ffffff;border:1px solid #D4E3D8;border-radius:14px;padding:22px;">
                <h4 style="color:#0D3B22;font-size:16px;font-weight:700;margin:0 0 14px;">🗓️ Berkas SAP Tersedia per Mata Kuliah</h4>
                <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(280px, 1fr));gap:14px;">
                    <?php foreach ($sap_cards as $sc) : ?>
                        <div style="background:#FAF8F5;border:1px solid #D4E3D8;border-radius:12px;padding:16px;display:flex;flex-direction:column;justify-content:space-between;gap:10px;">
                            <div>
                                <span style="font-family:monospace;font-size:11px;font-weight:700;color:#0D3B22;background:#E8F0EA;padding:2px 8px;border-radius:4px;"><?php echo esc_html(($sc['kode'] ?? '') . ' · Sem ' . (int) ($sc['semester'] ?? 1)); ?></span>
                                <h5 style="font-size:14px;font-weight:700;color:#1F2937;margin:8px 0 6px;"><?php echo esc_html($sc['nama'] ?? ''); ?></h5>
                                <div><?php echo $render_dosen_pills($sc['dosen_slugs'] ?? []); ?></div>
                            </div>
                            <a href="<?php echo esc_url($sc['sap_url']); ?>" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;justify-content:center;gap:6px;background:#0D3B22;color:#F5B82E;font-size:12px;font-weight:700;padding:8px 14px;border-radius:8px;text-decoration:none;">🗓️ Unduh Berkas SAP ↗</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Tab 4: Rubrik Penilaian OBE -->
        <div id="tab-rubrik" class="ppb-cur-pane" style="display:none;">
            <div style="background:#FAF8F5;border:1px solid #D4E3D8;border-radius:16px;padding:24px;margin-bottom:25px;">
                <h3 style="font-family:'Fraunces',Georgia,serif;font-size:20px;color:#0D3B22;margin:0 0 8px;">
                    Rubrik Penilaian Holistik &amp; Analitik Outcome-Based Education (OBE)
                </h3>
                <p style="font-size:13px;color:#4B5563;margin:0;line-height:1.6;">
                    Sistem evaluasi pembelajaran BK UNM mengedepankan penilaian autentik dengan rubrik terstandar untuk mengukur keterampilan konseling praktis, partisipasi aktif, dan etika profesional.
                </p>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(300px, 1fr));gap:20px;">
                <div style="background:#ffffff;border:1px solid #D4E3D8;border-radius:14px;padding:22px;">
                    <h4 style="color:#0D3B22;font-size:16px;font-weight:700;margin:0 0 10px;">Rubrik Kinerja Simulasi Konseling</h4>
                    <ul style="font-size:13px;color:#4B5563;line-height:1.8;padding-left:20px;margin:0 0 16px;">
                        <li>Keterampilan attending &amp; kontak mata (Bobot 20%)</li>
                        <li>Empati primer &amp; respon reflektif (Bobot 25%)</li>
                        <li>Teknik questioning &amp; eksplorasi masalah (Bobot 20%)</li>
                        <li>Perumusan tujuan intervensi (Bobot 20%)</li>
                        <li>Etika kerahasiaan &amp; terminasi sesi (Bobot 15%)</li>
                    </ul>
                    <?php if ($rubrik_konseling_url !== '') : ?>
                        <a href="<?php echo esc_url($rubrik_konseling_url); ?>" target="_blank" rel="noopener noreferrer" style="display:inline-block;background:#0D3B22;color:#ffffff;font-size:12px;font-weight:600;padding:7px 14px;border-radius:8px;text-decoration:none;">Unduh Lembar Rubrik &rarr;</a>
                    <?php else : ?>
                        <span style="display:inline-block;background:#F3F4F6;color:#6B7280;font-size:12px;font-weight:600;padding:7px 14px;border-radius:8px;">⏳ Lembar Rubrik Segera Tersedia</span>
                    <?php endif; ?>
                </div>

                <div style="background:#ffffff;border:1px solid #D4E3D8;border-radius:14px;padding:22px;">
                    <h4 style="color:#0D3B22;font-size:16px;font-weight:700;margin:0 0 10px;">Rubrik Penilaian Team-Based Project</h4>
                    <ul style="font-size:13px;color:#4B5563;line-height:1.8;padding-left:20px;margin:0 0 16px;">
                        <li>Ketepatan analisis kebutuhan (need assessment) (Bobot 25%)</li>
                        <li>Kreativitas inovasi modul/media BK (Bobot 30%)</li>
                        <li>Kolaborasi tim &amp; pembagian kerja (Bobot 20%)</li>
                        <li>Kualitas laporan &amp; video dokumentasi (Bobot 25%)</li>
                    </ul>
                    <?php if ($rubrik_project_url !== '') : ?>
                        <a href="<?php echo esc_url($rubrik_project_url); ?>" target="_blank" rel="noopener noreferrer" style="display:inline-block;background:#0D3B22;color:#ffffff;font-size:12px;font-weight:600;padding:7px 14px;border-radius:8px;text-decoration:none;">Unduh Lembar Rubrik &rarr;</a>
                    <?php else : ?>
                        <span style="display:inline-block;background:#F3F4F6;color:#6B7280;font-size:12px;font-weight:600;padding:7px 14px;border-radius:8px;">⏳ Lembar Rubrik Segera Tersedia</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <script>
        window.ppbSwitchCurTab = function(tabId) {
            document.querySelectorAll('.ppb-cur-pane').forEach(function(p) { p.style.display = 'none'; });
            document.querySelectorAll('.ppb-cur-tab-btn').forEach(function(b) {
                b.style.background = '#ffffff';
                b.style.color = '#374151';
                b.style.border = '1px solid #D1D5DB';
            });
            var target = document.getElementById(tabId);
            if (target) target.style.display = 'block';
            var btn = document.querySelector('.ppb-cur-tab-btn[data-tab="' + tabId + '"]');
            if (btn) {
                btn.style.background = '#0D3B22';
                btn.style.color = '#ffffff';
                btn.style.border = 'none';
            }
        };
        document.querySelectorAll('.ppb-cur-tab-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var tab = this.getAttribute('data-tab');
                window.ppbSwitchCurTab(tab);
            });
        });
        document.querySelectorAll('.ppb-mk-sem-pill').forEach(function(pill) {
            pill.addEventListener('click', function() {
                var semVal = this.getAttribute('data-mk-sem') || 'all';
                document.querySelectorAll('.ppb-mk-sem-pill').forEach(function(b) {
                    var active = (b === pill);
                    b.classList.toggle('is-active', active);
                    b.style.background = active ? '#0D3B22' : '#ffffff';
                    b.style.color = active ? '#F5B82E' : '#374151';
                    b.style.borderColor = active ? '#0D3B22' : '#D1D5DB';
                    b.style.fontWeight = active ? '700' : '600';
                });
                document.querySelectorAll('.ppb-mk-sem-card').forEach(function(card) {
                    var cSem = card.getAttribute('data-mk-sem-card');
                    card.style.display = (semVal === 'all' || cSem === semVal) ? 'block' : 'none';
                });
            });
        });
        document.querySelectorAll('.ppb-cpmk-toggle').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var id = this.getAttribute('data-cpmk-target');
                var panel = id ? document.getElementById(id) : null;
                if (!panel) return;
                var open = panel.style.display !== 'none';
                panel.style.display = open ? 'none' : 'block';
                this.textContent = open ? 'Detail CPMK' : 'Tutup CPMK';
            });
        });
        </script>
    </section>
    <?php
    return ob_get_clean();
}
}
add_shortcode('ppb_kurikulum_rps_sap', 'ppb_shortcode_kurikulum_rps_sap');

/* ========================================================================== */
/* 9. SHORTCODE: MIS PNBP (HIBAH PENELITIAN & PENGABDIAN MASYARAKAT)           */
/* ========================================================================== */
if (!function_exists('ppb_shortcode_mis_pnbp')) {
function ppb_shortcode_mis_pnbp($atts = []) {
    $atts = shortcode_atts(['show_banner' => '1'], $atts, 'ppb_mis_pnbp');
    $show_banner = ((string) $atts['show_banner'] !== '0');
    $pnbp_data   = function_exists('ppb_get_mis_pnbp_data') ? ppb_get_mis_pnbp_data() : ['riset' => [], 'pkm' => []];
    $riset_list  = $pnbp_data['riset'] ?? [];
    $pkm_list    = $pnbp_data['pkm'] ?? [];
    ob_start();
    ?>
    <section class="ppb-mis-pnbp-wrapper" style="font-family:'Plus Jakarta Sans',sans-serif;color:#1F2937;margin:<?php echo $show_banner ? '30px 0' : '0 0 30px'; ?>;">
        <?php if ($show_banner) : ?>
        <!-- Header Banner MIS PNBP -->
        <div style="background:#0D3B22;color:#ffffff;border-radius:20px;padding:30px;margin-bottom:30px;box-shadow:0 8px 25px rgba(13,59,34,0.15);">
            <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(245,184,46,0.2);color:#F5B82E;padding:4px 14px;border-radius:999px;font-size:11px;font-weight:700;text-transform:uppercase;margin-bottom:12px;border:1px solid rgba(245,184,46,0.35);">
                <span>Kriteria 7 &amp; 8 LAMDIK / BAN-PT</span>
            </div>
            <h2 style="font-family:'Fraunces',Georgia,serif;font-size:clamp(22px, 3vw, 30px);color:#ffffff;margin:0 0 10px;font-weight:600;">
                MIS PNBP: Manajemen Informasi Hibah Penelitian &amp; Pengabdian Masyarakat
            </h2>
            <p style="color:rgba(255,255,255,0.85);font-size:14px;max-width:760px;margin:0;line-height:1.6;">
                Pusat data transparansi pendanaan riset keilmuan dan hilirisasi pengabdian kepada masyarakat (PkM) civitas akademika Program Studi Bimbingan dan Konseling berbasis skema Pendapatan Negara Bukan Pajak (PNBP) UNM.
            </p>
        </div>
        <?php endif; ?>

        <!-- Tab Selector -->
        <div style="display:flex;gap:12px;margin-bottom:25px;flex-wrap:wrap;">
            <button type="button" class="ppb-pnbp-btn is-active" data-pnbp="pane-riset" style="padding:11px 22px;border-radius:12px;border:none;background:#0D3B22;color:#ffffff;font-size:14px;font-weight:700;cursor:pointer;">
                🔬 Hibah Penelitian PNBP (<?php echo count($riset_list); ?>)
            </button>
            <button type="button" class="ppb-pnbp-btn" data-pnbp="pane-pkm" style="padding:11px 22px;border-radius:12px;border:1px solid #D1D5DB;background:#ffffff;color:#374151;font-size:14px;font-weight:600;cursor:pointer;">
                🤝 Pengabdian kepada Masyarakat (PkM) PNBP (<?php echo count($pkm_list); ?>)
            </button>
        </div>

        <!-- Pane 1: Hibah Penelitian PNBP -->
        <div id="pane-riset" data-pnbp-pane="pane-riset" class="ppb-pnbp-pane" style="display:block;">
            <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));gap:20px;">
                <?php foreach ($riset_list as $r) :
                    $dok_url = !empty($r['dokumen_url']) && function_exists('ppb_clean_document_url')
                        ? ppb_clean_document_url($r['dokumen_url'])
                        : trim((string) ($r['dokumen_url'] ?? ''));
                ?>
                    <div style="background:#ffffff;border:1px solid #D4E3D8;border-radius:16px;padding:22px;box-shadow:0 4px 15px rgba(0,0,0,0.03);display:flex;flex-direction:column;justify-content:space-between;">
                        <div>
                            <div style="display:flex;justify-content:space-between;gap:8px;margin-bottom:8px;flex-wrap:wrap;">
                                <span style="font-size:11px;font-weight:700;background:#E8F0EA;color:#0D3B22;padding:3px 8px;border-radius:4px;"><?php echo esc_html($r['skema'] ?? 'Penelitian PNBP'); ?></span>
                                <span style="font-size:12px;font-weight:700;color:#059669;"><?php echo esc_html($r['tahun'] ?? ''); ?></span>
                            </div>
                            <h4 style="font-size:15px;font-weight:700;color:#0D3B22;margin:0 0 10px;line-height:1.4;"><?php echo esc_html($r['judul'] ?? ''); ?></h4>
                            <div style="font-size:12.5px;color:#4B5563;line-height:1.6;margin-bottom:12px;">
                                <div><strong>Ketua Peneliti:</strong> <?php echo esc_html($r['ketua'] ?? ''); ?></div>
                                <div><strong>Kolaborator:</strong> <?php echo esc_html($r['anggota'] ?? '-'); ?></div>
                                <div><strong>Dana Hibah:</strong> <?php echo esc_html($r['dana'] ?? '-'); ?></div>
                            </div>
                        </div>
                        <div>
                            <div style="font-size:12px;background:#FAF8F5;border:1px solid #E2E8F0;padding:8px 12px;border-radius:8px;color:#0D3B22;font-weight:600;">
                                🎯 Luaran: <?php echo esc_html($r['luaran'] ?? '-'); ?>
                            </div>
                            <?php if ($dok_url !== '') : ?>
                                <a href="<?php echo esc_url($dok_url); ?>" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;gap:6px;margin-top:10px;background:#E8F0EA;color:#0D3B22;border:1px solid #B7D0BE;font-size:12px;font-weight:700;padding:7px 12px;border-radius:8px;text-decoration:none;">📄 Laporan / SK Hibah &#8599;</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Pane 2: PkM PNBP -->
        <div id="pane-pkm" data-pnbp-pane="pane-pkm" class="ppb-pnbp-pane" style="display:none;">
            <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));gap:20px;">
                <?php foreach ($pkm_list as $p) :
                    $dok_url = !empty($p['dokumen_url']) && function_exists('ppb_clean_document_url')
                        ? ppb_clean_document_url($p['dokumen_url'])
                        : trim((string) ($p['dokumen_url'] ?? ''));
                ?>
                    <div style="background:#ffffff;border:1px solid #D4E3D8;border-radius:16px;padding:22px;box-shadow:0 4px 15px rgba(0,0,0,0.03);display:flex;flex-direction:column;justify-content:space-between;">
                        <div>
                            <div style="display:flex;justify-content:space-between;gap:8px;margin-bottom:8px;flex-wrap:wrap;">
                                <span style="font-size:11px;font-weight:700;background:#FEF3C7;color:#92400E;padding:3px 8px;border-radius:4px;"><?php echo esc_html($p['skema'] ?? 'PkM PNBP'); ?></span>
                                <span style="font-size:12px;font-weight:700;color:#059669;"><?php echo esc_html($p['tahun'] ?? ''); ?></span>
                            </div>
                            <h4 style="font-size:15px;font-weight:700;color:#0D3B22;margin:0 0 10px;line-height:1.4;"><?php echo esc_html($p['judul'] ?? ''); ?></h4>
                            <div style="font-size:12.5px;color:#4B5563;line-height:1.6;margin-bottom:12px;">
                                <div><strong>Ketua Pelaksana:</strong> <?php echo esc_html($p['ketua'] ?? ''); ?></div>
                                <div><strong>Mitra Kegiatan:</strong> <?php echo esc_html($p['mitra'] ?? '-'); ?></div>
                                <div><strong>Dana PNBP:</strong> <?php echo esc_html($p['dana'] ?? '-'); ?></div>
                            </div>
                        </div>
                        <div>
                            <div style="font-size:12px;background:#FAF8F5;border:1px solid #E2E8F0;padding:8px 12px;border-radius:8px;color:#0D3B22;font-weight:600;">
                                🎯 Luaran: <?php echo esc_html($p['luaran'] ?? '-'); ?>
                            </div>
                            <?php if ($dok_url !== '') : ?>
                                <a href="<?php echo esc_url($dok_url); ?>" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;gap:6px;margin-top:10px;background:#E8F0EA;color:#0D3B22;border:1px solid #B7D0BE;font-size:12px;font-weight:700;padding:7px 12px;border-radius:8px;text-decoration:none;">📄 Laporan / SK PkM &#8599;</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <script>
        (function(){
            var wrappers = document.querySelectorAll('.ppb-mis-pnbp-wrapper');
            wrappers.forEach(function(wrapper){
                if (wrapper.getAttribute('data-pnbp-wired') === '1') return;
                wrapper.setAttribute('data-pnbp-wired', '1');
                wrapper.querySelectorAll('.ppb-pnbp-btn').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        var target = this.getAttribute('data-pnbp');
                        wrapper.querySelectorAll('.ppb-pnbp-pane').forEach(function(pane) { pane.style.display = 'none'; });
                        wrapper.querySelectorAll('.ppb-pnbp-btn').forEach(function(b) {
                            b.style.background = '#ffffff';
                            b.style.color = '#374151';
                            b.style.border = '1px solid #D1D5DB';
                        });
                        var activePane = wrapper.querySelector('[data-pnbp-pane="' + target + '"]') || document.getElementById(target);
                        if (activePane) activePane.style.display = 'block';
                        this.style.background = '#0D3B22';
                        this.style.color = '#ffffff';
                        this.style.border = 'none';
                    });
                });
            });
        })();
        </script>
    </section>
    <?php
    return ob_get_clean();
}
}
add_shortcode('ppb_mis_pnbp', 'ppb_shortcode_mis_pnbp');

/* ========================================================================== */
/* 10. SHORTCODE: JURNAL & PENGABDIAN YANG DIKELOLA PRODI BK                  */
/* ========================================================================== */
if (!function_exists('ppb_shortcode_jurnal_bk')) {
function ppb_shortcode_jurnal_bk($atts = []) {
    $jurnal_list = function_exists('ppb_get_jurnal_bk_data') ? ppb_get_jurnal_bk_data() : [];
    ob_start();
    ?>
    <section class="ppb-jurnal-wrapper" style="font-family:'Plus Jakarta Sans',sans-serif;color:#1F2937;margin:30px 0;">
        <div style="background:#0D3B22;color:#ffffff;border-radius:20px;padding:30px;margin-bottom:30px;box-shadow:0 8px 25px rgba(13,59,34,0.15);">
            <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(245,184,46,0.2);color:#F5B82E;padding:4px 14px;border-radius:999px;font-size:11px;font-weight:700;text-transform:uppercase;margin-bottom:12px;border:1px solid rgba(245,184,46,0.35);">
                <span>Publikasi Ilmiah &amp; Diseminasi Riset</span>
            </div>
            <h2 style="font-family:'Fraunces',Georgia,serif;font-size:clamp(22px, 3vw, 30px);color:#ffffff;margin:0 0 10px;font-weight:600;">
                Jurnal &amp; Pengabdian yang Dikelola Program Studi Bimbingan dan Konseling
            </h2>
            <p style="color:rgba(255,255,255,0.85);font-size:14px;max-width:760px;margin:0;line-height:1.6;">
                Publikasi ilmiah peer-reviewed resmi yang memuat karya riset empiris dan artikel pengabdian masyarakat di bidang bimbingan, konseling, dan psikologi pendidikan terapan.
            </p>
        </div>

        <?php echo ppb_render_jurnal_cards_grid_html($jurnal_list); ?>
    </section>
    <?php
    return ob_get_clean();
}
}
add_shortcode('ppb_jurnal_bk', 'ppb_shortcode_jurnal_bk');

/* ========================================================================== */
/* 11. FRONTEND SYNC FILTER: PANDUAN, ROADMAP, KERJASAMA, KEPAKARAN & BUKU    */
/* ========================================================================== */
if (!function_exists('ppb_akademik_sync_frontend_content')) {
function ppb_akademik_sync_frontend_content($content) {
    if (!is_string($content) || $content === '' || is_admin()) {
        return $content;
    }
    if (!function_exists('is_page') || (!is_page() && !is_singular())) {
        return $content;
    }

    $slug_path = function_exists('ppb_get_current_page_slug_path') ? ppb_get_current_page_slug_path() : '';

    $is_panduan   = ($slug_path === 'akademik/panduan' || strpos($content, '1_Pedoman_Skripsi_BK_UNM') !== false);
    $is_roadmap   = ($slug_path === 'profil/roadmap' || strpos($content, '1_Pengesahan_Roadmap_Pilar1') !== false);
    $is_kerjasama = ($slug_path === 'profil/kerjasama-tridharma' || strpos($content, 'Rekap MoA Pendidikan PDF') !== false);
    $is_kepakaran = ($slug_path === 'profil/kepakaran-dosen' || strpos($content, 'Unduh Dokumen SK Kepakaran') !== false);
    $is_buku      = ($slug_path === 'profil/buku-dosen' || strpos($content, 'Koleksi Buku Karya Dosen') !== false);
    $is_pnbp_static   = (strpos($content, '<!-- QUICK STATS DASHBOARD -->') !== false && strpos($content, '<!-- TAUTAN PORTAL LP2M RESMI -->') !== false && strpos($content, 'ppb-mis-pnbp-wrapper') === false);
    $is_jurnal_static = (strpos($content, '<!-- JURNAL CARDS GRID -->') !== false && strpos($content, 'id="ppbJurnalGrid"') === false);

    if (!$is_panduan && !$is_roadmap && !$is_kerjasama && !$is_kepakaran && !$is_buku && !$is_pnbp_static && !$is_jurnal_static) {
        return $content;
    }

    $links = ppb_akademik_get_all_links();

    // 1. /akademik/panduan/
    if ($is_panduan) {
        $skripsi_url   = $links['pedoman_skripsi'] ?? '';
        $penilaian_url = $links['pedoman_penilaian'] ?? '';

        $content = preg_replace_callback(
            '/<div>\s*<a\b[^>]*1_Pedoman_Skripsi_BK_UNM[^>]*>.*?<\/a>\s*<\/div>/is',
            function() use ($skripsi_url, $penilaian_url) {
                $btns = '';
                if ($skripsi_url !== '') {
                    $btns .= '<a href="' . esc_url($skripsi_url) . '" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;gap:8px;padding:12px 24px;border-radius:9999px;background:#F5B82E;color:#0D3B22;font-size:13px;font-weight:700;text-decoration:none">Buka Folder Template Skripsi &#8599;</a>';
                } else {
                    $btns .= '<span style="display:inline-flex;align-items:center;gap:8px;padding:12px 22px;border-radius:9999px;background:rgba(255,255,255,0.14);color:rgba(255,255,255,0.82);font-size:13px;font-weight:600">Folder Template Skripsi Segera Tersedia</span>';
                }
                if ($penilaian_url !== '') {
                    $btns .= '<a href="' . esc_url($penilaian_url) . '" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;gap:8px;padding:12px 22px;border-radius:9999px;background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.35);color:#ffffff;font-size:13px;font-weight:700;text-decoration:none">Pedoman Penilaian OBE &#8599;</a>';
                }
                return '<div style="display:flex;flex-wrap:wrap;gap:10px">' . $btns . '</div>';
            },
            $content,
            1
        );
    }

    // 2. /profil/roadmap/
    if ($is_roadmap) {
        $roadmap_map = [
            'https://drive.google.com/file/d/1_Pengesahan_Roadmap_Pilar1/view?usp=sharing'    => $links['roadmap_p1_sk'] ?? '',
            'https://drive.google.com/file/d/1_Dokumen_Roadmap_Riset_Pilar1/view?usp=sharing' => $links['roadmap_p1_doc'] ?? '',
            'https://drive.google.com/file/d/1_Pengesahan_Roadmap_Pilar2/view?usp=sharing'    => $links['roadmap_p2_sk'] ?? '',
            'https://drive.google.com/file/d/1_Dokumen_Roadmap_Riset_Pilar2/view?usp=sharing' => $links['roadmap_p2_doc'] ?? '',
            'https://drive.google.com/file/d/1_Pengesahan_Roadmap_Pilar3/view?usp=sharing'    => $links['roadmap_p3_sk'] ?? '',
            'https://drive.google.com/file/d/1_Dokumen_Roadmap_Riset_Pilar3/view?usp=sharing' => $links['roadmap_p3_doc'] ?? '',
        ];
        foreach ($roadmap_map as $seed_url => $target_url) {
            if ($target_url !== '') {
                $content = str_replace($seed_url, esc_url($target_url), $content);
            } else {
                $pattern = '/<a\b[^>]*href=(["\'])' . preg_quote($seed_url, '/') . '\1[^>]*>.*?<\/a>/is';
                $content = preg_replace(
                    $pattern,
                    '<span style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:9999px;background:#F3F4F6;color:#6B7280;font-size:12px;font-weight:600">Berkas Segera Diunggah</span>',
                    $content
                );
            }
        }
    }

    // 3. /profil/kerjasama-tridharma/
    if ($is_kerjasama) {
        $moa_map = [
            'Rekap MoA Pendidikan PDF' => $links['moa_pendidikan'] ?? '',
            'Rekap MoA Penelitian PDF' => $links['moa_penelitian'] ?? '',
            'Rekap MoA PkM PDF'        => $links['moa_pkm'] ?? '',
        ];
        foreach ($moa_map as $label_txt => $url_val) {
            $pattern = '/<a\b[^>]*href=(["\'])#\1[^>]*>[^<]*' . preg_quote($label_txt, '/') . '[^<]*<\/a>/is';
            if ($url_val !== '') {
                $replacement = '<a href="' . esc_url($url_val) . '" target="_blank" rel="noopener noreferrer" style="display:inline-block;background:#0D3B22;color:#ffffff;font-size:12px;font-weight:600;padding:8px 14px;border-radius:8px;text-decoration:none;">📄 ' . esc_html($label_txt) . ' &#8599;</a>';
            } else {
                $replacement = '<span style="display:inline-block;background:#F3F4F6;color:#6B7280;font-size:12px;font-weight:600;padding:8px 14px;border-radius:8px;border:1px solid #E5E7EB;">📄 Rekap Segera Diunggah</span>';
            }
            $content = preg_replace($pattern, $replacement, $content);
        }
    }

    // 4. /profil/kepakaran-dosen/
    if ($is_kepakaran) {
        $sk_kep_url = $links['sk_kepakaran'] ?? '';
        $pattern = '/<a\b[^>]*href=(["\'])#\1[^>]*>\s*[^<]*Unduh Dokumen SK Kepakaran[^<]*<\/a>/is';
        if ($sk_kep_url !== '') {
            $replacement = '<a href="' . esc_url($sk_kep_url) . '" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;gap:6px;background:#F5B82E;color:#0D3B22;font-size:13px;font-weight:700;padding:12px 20px;border-radius:10px;text-decoration:none;">📄 Unduh Dokumen SK Kepakaran &#8599;</a>';
        } else {
            $replacement = '<span style="display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,0.14);color:rgba(255,255,255,0.85);font-size:13px;font-weight:600;padding:12px 20px;border-radius:10px;border:1px solid rgba(255,255,255,0.22);">SK Kepakaran Segera Tersedia</span>';
        }
        $content = preg_replace($pattern, $replacement, $content);
    }

    // 5. /profil/buku-dosen/
    if ($is_buku) {
        $valid_slugs = [];
        if (function_exists('ppb_get_dosen_directory_cached')) {
            foreach ((array) ppb_get_dosen_directory_cached() as $d_item) {
                if (!empty($d_item['slug'])) {
                    $valid_slugs[$d_item['slug']] = true;
                }
            }
        }
        $target_slugs = ['farida-aryani', 'alimuddin-mahmud', 'abdullah-pandang'];
        $idx = 0;
        $content = preg_replace_callback(
            '/<a\b([^>]*)href=(["\'])#\2([^>]*)>(\s*Lihat Detail\s*(?:&amp;|&)\s*Akses Buku\s*)<\/a>/is',
            function($m) use (&$idx, $target_slugs, $valid_slugs) {
                $slug = isset($target_slugs[$idx]) ? $target_slugs[$idx] : '';
                $idx++;
                if ($slug !== '' && isset($valid_slugs[$slug])) {
                    $url = home_url('/profil/dosen/' . $slug . '/');
                    $label = 'Lihat Profil &amp; Karya Dosen &rarr;';
                } else {
                    $url = home_url('/profil/dosen/');
                    $label = 'Lihat Direktori Dosen &rarr;';
                }
                return '<a' . $m[1] . 'href="' . esc_url($url) . '"' . $m[3] . '>' . $label . '</a>';
            },
            $content
        );
    }

    // 6. Halaman statis /mis-pnbp/ (fallback jika belum pakai shortcode [ppb_mis_pnbp])
    if ($is_pnbp_static) {
        $start = strpos($content, '<!-- QUICK STATS DASHBOARD -->');
        $end   = strpos($content, '<!-- TAUTAN PORTAL LP2M RESMI -->');
        if ($start !== false && $end !== false && $end > $start) {
            $pnbp_data  = function_exists('ppb_get_mis_pnbp_data') ? ppb_get_mis_pnbp_data() : ['riset' => [], 'pkm' => []];
            $cnt_r      = count($pnbp_data['riset'] ?? []);
            $cnt_p      = count($pnbp_data['pkm'] ?? []);
            $stats_html = '<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:20px;margin-bottom:28px;">'
                . '<div style="background:#ffffff;border:1px solid #E5E7EB;border-radius:16px;padding:20px;border-left:4px solid #0D3B22;">'
                . '<span style="font-size:11.5px;color:#64748B;font-weight:700;text-transform:uppercase;">Hibah Penelitian PNBP</span>'
                . '<div style="font-family:\'Fraunces\',Georgia,serif;font-size:28px;font-weight:700;color:#0D3B22;margin-top:4px;">' . (int) $cnt_r . ' Judul</div>'
                . '</div>'
                . '<div style="background:#ffffff;border:1px solid #E5E7EB;border-radius:16px;padding:20px;border-left:4px solid #F5B82E;">'
                . '<span style="font-size:11.5px;color:#64748B;font-weight:700;text-transform:uppercase;">Hibah Pengabdian (PkM)</span>'
                . '<div style="font-family:\'Fraunces\',Georgia,serif;font-size:28px;font-weight:700;color:#92400E;margin-top:4px;">' . (int) $cnt_p . ' Judul</div>'
                . '</div>'
                . '<div style="background:#ffffff;border:1px solid #E5E7EB;border-radius:16px;padding:20px;border-left:4px solid #0369A1;">'
                . '<span style="font-size:11.5px;color:#64748B;font-weight:700;text-transform:uppercase;">Total Portofolio Aktif</span>'
                . '<div style="font-family:\'Fraunces\',Georgia,serif;font-size:28px;font-weight:700;color:#0369A1;margin-top:4px;">' . (int) ($cnt_r + $cnt_p) . ' Program</div>'
                . '</div>'
                . '</div>';
            $content = substr($content, 0, $start)
                . $stats_html
                . ppb_shortcode_mis_pnbp(['show_banner' => '0']) . "\n    "
                . substr($content, $end);
        }
    }

    // 7. Halaman statis /jurnal-bk/ (fallback jika belum pakai shortcode [ppb_jurnal_bk])
    if ($is_jurnal_static && function_exists('ppb_replace_balanced_div_at')) {
        $marker = strpos($content, '<!-- JURNAL CARDS GRID -->');
        if ($marker !== false) {
            $div_start = stripos($content, '<div', $marker);
            if ($div_start !== false) {
                $content = ppb_replace_balanced_div_at($content, $div_start, ppb_render_jurnal_cards_grid_html());
            }
        }
    }

    return $content;
}
}
add_filter('the_content', 'ppb_akademik_sync_frontend_content', 12);

