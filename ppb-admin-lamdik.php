<?php
/**
 * Module: PPB FIP UNM - Pengaturan Dokumen Akreditasi, 9 Kriteria Eviden LAMDIK & Sertifikat S1-S3
 * Description: Panel terpusat di WP Admin untuk mengelola seluruh tautan Google Drive & berkas PDF
 *              Akreditasi LAMDIK (Folder Utama, LED, LKPS, Matriks, 9 Kriteria Eviden, Renstra/Renop,
 *              dan Sertifikat + SK Resmi S1/S2/S3) dengan sinkronisasi live ke halaman publik.
 * Author: Jurusan PPB FIP UNM
 */

if (!defined('ABSPATH')) exit;

/* ========================================================================== */
/* 1. DEFAULTS, GETTERS & SANITIZED SAVE HELPERS                              */
/* ========================================================================== */

if (!function_exists('ppb_get_lamdik_defaults')) {
function ppb_get_lamdik_defaults() {
    return array(
        'general' => array(
            'folder_utama_url' => '',
            'led_url'          => '',
            'lkps_url'         => '',
            'matriks_url'      => '',
            'renstra_url'      => '',
            'renop_url'        => '',
            'upm_portal_url'   => 'https://bpm.unm.ac.id/',
        ),
        'kriteria' => array(
            0 => array('kode' => 'K1', 'judul' => 'Visi, Misi, Tujuan, dan Strategi', 'label_tombol' => 'Eviden Drive K1', 'drive_url' => ''),
            1 => array('kode' => 'K2', 'judul' => 'Tata Pamong, Tata Kelola, dan Kerjasama', 'label_tombol' => 'Eviden Drive K2', 'drive_url' => ''),
            2 => array('kode' => 'K3', 'judul' => 'Kemahasiswaan & Lulusan', 'label_tombol' => 'Eviden Drive K3', 'drive_url' => ''),
            3 => array('kode' => 'K4', 'judul' => 'Sumber Daya Manusia (Dosen)', 'label_tombol' => 'Eviden Drive K4', 'drive_url' => ''),
            4 => array('kode' => 'K5', 'judul' => 'Keuangan, Sarana, dan Prasarana', 'label_tombol' => 'Eviden Drive K5', 'drive_url' => ''),
            5 => array('kode' => 'K6', 'judul' => 'Pendidikan & Kurikulum OBE', 'label_tombol' => 'Eviden Drive K6', 'drive_url' => ''),
            6 => array('kode' => 'K7', 'judul' => 'Penelitian Dosen & Mahasiswa', 'label_tombol' => 'Eviden Drive K7', 'drive_url' => ''),
            7 => array('kode' => 'K8', 'judul' => 'Pengabdian kepada Masyarakat (PkM)', 'label_tombol' => 'Eviden Drive K8', 'drive_url' => ''),
            8 => array('kode' => 'K9', 'judul' => 'Luaran, Capaian & Penjaminan Mutu', 'label_tombol' => 'Eviden Drive K9', 'drive_url' => ''),
        ),
        'sertifikat' => array(
            's1' => array(
                'peringkat'    => 'UNGGUL (A)',
                'lembaga'      => 'LAMDIK',
                'no_sk'        => '492/SK/LAMDIK/Ak/S/V/2023',
                'masa_berlaku' => '2023 s.d. 2028',
                'cert_url'     => '',
                'sk_url'       => '',
            ),
            's2' => array(
                'peringkat'    => 'UNGGUL (A)',
                'lembaga'      => 'LAMDIK / BAN-PT',
                'no_sk'        => '315/SK/LAMDIK/Ak/M/2022',
                'masa_berlaku' => '2022 s.d. 2027',
                'cert_url'     => '',
                'sk_url'       => '',
            ),
            's3' => array(
                'peringkat'    => 'UNGGUL (A)',
                'lembaga'      => 'LAMDIK / BAN-PT',
                'no_sk'        => '588/SK/LAMDIK/Ak/D/2023',
                'masa_berlaku' => '2023 s.d. 2028',
                'cert_url'     => '',
                'sk_url'       => '',
            ),
        ),
    );
}
}

if (!function_exists('ppb_get_lamdik_settings')) {
function ppb_get_lamdik_settings() {
    $defaults = ppb_get_lamdik_defaults();
    $saved    = get_option('ppb_lamdik_settings', array());
    if (!is_array($saved)) {
        $saved = array();
    }

    $merged = $defaults;
    if (!empty($saved['general']) && is_array($saved['general'])) {
        foreach ($defaults['general'] as $k => $v) {
            if (isset($saved['general'][$k]) && $saved['general'][$k] !== '') {
                $merged['general'][$k] = (string) $saved['general'][$k];
            } elseif (isset($saved['general'][$k])) {
                $merged['general'][$k] = (string) $saved['general'][$k];
            }
        }
    }
    if (!empty($saved['kriteria']) && is_array($saved['kriteria'])) {
        foreach ($defaults['kriteria'] as $idx => $def_k) {
            if (isset($saved['kriteria'][$idx]) && is_array($saved['kriteria'][$idx])) {
                $merged['kriteria'][$idx] = wp_parse_args($saved['kriteria'][$idx], $def_k);
            }
        }
    }
    if (!empty($saved['sertifikat']) && is_array($saved['sertifikat'])) {
        foreach (array('s1', 's2', 's3') as $deg) {
            if (isset($saved['sertifikat'][$deg]) && is_array($saved['sertifikat'][$deg])) {
                $merged['sertifikat'][$deg] = wp_parse_args($saved['sertifikat'][$deg], $defaults['sertifikat'][$deg]);
            }
        }
    }

    return $merged;
}
}

if (!function_exists('ppb_save_lamdik_settings')) {
function ppb_save_lamdik_settings($raw) {
    $defaults = ppb_get_lamdik_defaults();
    $clean_url = function($val) {
        return function_exists('ppb_clean_document_url') ? ppb_clean_document_url($val) : esc_url_raw($val);
    };

    $out = $defaults;

    $raw_gen = isset($raw['general']) && is_array($raw['general']) ? $raw['general'] : array();
    foreach ($defaults['general'] as $k => $def_val) {
        if (isset($raw_gen[$k])) {
            $cleaned = $clean_url(wp_unslash($raw_gen[$k]));
            $out['general'][$k] = ($k === 'upm_portal_url' && $cleaned === '') ? $def_val : $cleaned;
        }
    }

    $raw_krit = isset($raw['kriteria']) && is_array($raw['kriteria']) ? $raw['kriteria'] : array();
    foreach ($defaults['kriteria'] as $idx => $def_k) {
        $rk = isset($raw_krit[$idx]) && is_array($raw_krit[$idx]) ? $raw_krit[$idx] : array();
        $out['kriteria'][$idx] = array(
            'kode'         => $def_k['kode'],
            'judul'        => $def_k['judul'],
            'label_tombol' => sanitize_text_field(wp_unslash($rk['label_tombol'] ?? $def_k['label_tombol'])) ?: $def_k['label_tombol'],
            'drive_url'    => $clean_url(wp_unslash($rk['drive_url'] ?? '')),
        );
    }

    $raw_sert = isset($raw['sertifikat']) && is_array($raw['sertifikat']) ? $raw['sertifikat'] : array();
    foreach (array('s1', 's2', 's3') as $deg) {
        $def_s = $defaults['sertifikat'][$deg];
        $rs    = isset($raw_sert[$deg]) && is_array($raw_sert[$deg]) ? $raw_sert[$deg] : array();
        $out['sertifikat'][$deg] = array(
            'peringkat'    => sanitize_text_field(wp_unslash($rs['peringkat'] ?? $def_s['peringkat'])) ?: $def_s['peringkat'],
            'lembaga'      => sanitize_text_field(wp_unslash($rs['lembaga'] ?? $def_s['lembaga'])) ?: $def_s['lembaga'],
            'no_sk'        => sanitize_text_field(wp_unslash($rs['no_sk'] ?? $def_s['no_sk'])) ?: $def_s['no_sk'],
            'masa_berlaku' => sanitize_text_field(wp_unslash($rs['masa_berlaku'] ?? $def_s['masa_berlaku'])) ?: $def_s['masa_berlaku'],
            'cert_url'     => $clean_url(wp_unslash($rs['cert_url'] ?? '')),
            'sk_url'       => $clean_url(wp_unslash($rs['sk_url'] ?? '')),
        );
    }

    update_option('ppb_lamdik_settings', $out, false);
    return $out;
}
}

/* ========================================================================== */
/* 2. HALAMAN ADMIN: PENGATURAN AKREDITASI & LINK DRIVE DOKUMEN LAMDIK        */
/* ========================================================================== */

if (!function_exists('ppb_render_lamdik_admin_page')) {
function ppb_render_lamdik_admin_page() {
    if (!current_user_can('edit_posts')) {
        wp_die('Anda tidak memiliki izin mengakses halaman ini.');
    }

    if (function_exists('ppb_admin_enqueue_media_assets')) {
        ppb_admin_enqueue_media_assets();
    }

    $notice = '';
    if (isset($_POST['ppb_save_lamdik_submit']) && check_admin_referer('ppb_lamdik_save_action', 'ppb_lamdik_nonce')) {
        $settings = ppb_save_lamdik_settings($_POST);
        $notice = '<div class="notice notice-success is-dismissible"><p><strong>✅ Pengaturan Akreditasi &amp; Link Drive Dokumen LAMDIK berhasil disimpan!</strong> Perubahan langsung aktif pada halaman <code>/akreditasi/</code>, <code>/akreditasi/sertifikat/</code>, <code>/akreditasi/upm/</code>, <code>/profil/visi-misi/</code>, dan Beranda.</p></div>';
    } else {
        $settings = ppb_get_lamdik_settings();
    }

    $gen  = $settings['general'];
    $krit = $settings['kriteria'];
    $sert = $settings['sertifikat'];

    $filled_k = 0;
    foreach ($krit as $k) {
        if (!empty($k['drive_url'])) $filled_k++;
    }
    $filled_core = 0;
    foreach (array('folder_utama_url', 'led_url', 'lkps_url', 'matriks_url', 'renstra_url', 'renop_url') as $ck) {
        if (!empty($gen[$ck])) $filled_core++;
    }
    ?>
    <div class="wrap" style="max-width:1180px;margin:20px auto;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;color:#1e293b;">
        <style>
            .ppb-lam-hero { background:linear-gradient(135deg,#0D3B22 0%,#185333 100%); color:#fff; border-radius:16px; padding:26px 28px; display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:18px; margin-bottom:20px; box-shadow:0 8px 22px rgba(13,59,34,0.16); }
            .ppb-lam-card { background:#fff; border:1px solid #cbd5e1; border-radius:14px; padding:22px 24px; margin-bottom:22px; box-shadow:0 3px 10px rgba(0,0,0,0.03); }
            .ppb-lam-card h2 { margin:0 0 6px; font-size:17px; font-weight:800; color:#0D3B22; }
            .ppb-lam-desc { margin:0 0 18px; font-size:13px; color:#64748b; }
            .ppb-lam-grid2 { display:grid; grid-template-columns:repeat(auto-fit, minmax(360px, 1fr)); gap:16px; }
            .ppb-lam-field { background:#FAF8F5; border:1px solid #e2e8f0; border-radius:12px; padding:14px; }
            .ppb-lam-field label { display:block; font-weight:700; font-size:12.5px; color:#0D3B22; margin-bottom:6px; }
        </style>

        <div class="ppb-lam-hero">
            <div style="max-width:740px;">
                <span style="display:inline-block;background:rgba(245,184,46,0.2);border:1px solid rgba(245,184,46,0.45);color:#F5B82E;font-size:11px;font-weight:700;padding:3px 12px;border-radius:999px;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;">Hub Bukti Fisik LAMDIK · 9 Kriteria</span>
                <h1 style="margin:0 0 6px;color:#fff;font-size:24px;font-weight:800;">🏅 Pengaturan Akreditasi &amp; Link Drive Dokumen LAMDIK</h1>
                <p style="margin:0;color:rgba(255,255,255,0.88);font-size:13.5px;line-height:1.55;">
                    Tempelkan tautan Folder/File Google Drive atau unggah berkas PDF resmi untuk <strong>Dokumen Induk LED/LKPS</strong>, <strong>9 Kriteria Eviden LAMDIK</strong>, <strong>Renstra &amp; Renop UPM</strong>, serta <strong>Sertifikat &amp; SK Resmi S1/S2/S3</strong>. Status saat ini: <strong><?php echo (int) $filled_core; ?>/6 dokumen induk</strong> &amp; <strong><?php echo (int) $filled_k; ?>/9 kriteria</strong> terhubung.
                </p>
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:8px;">
                <a href="<?php echo esc_url(home_url('/akreditasi/')); ?>" target="_blank" class="button" style="background:#F5B82E;color:#0D3B22;border:none;font-weight:800;padding:8px 15px;height:auto;border-radius:8px;">↗ Lihat /akreditasi/</a>
                <a href="<?php echo esc_url(home_url('/akreditasi/sertifikat/')); ?>" target="_blank" class="button" style="background:rgba(255,255,255,0.14);color:#fff;border:1px solid rgba(255,255,255,0.3);font-weight:600;padding:8px 14px;height:auto;border-radius:8px;">↗ Sertifikat S1–S3</a>
            </div>
        </div>

        <?php echo $notice; ?>

        <form method="post" action="">
            <?php wp_nonce_field('ppb_lamdik_save_action', 'ppb_lamdik_nonce'); ?>

            <!-- SECTION 1: DOKUMEN INDUK & FOLDER UTAMA LAMDIK + UPM -->
            <div class="ppb-lam-card">
                <h2>📁 1. Folder Utama &amp; Dokumen Induk Akreditasi / Penjaminan Mutu</h2>
                <p class="ppb-lam-desc">Tautan Folder Utama, LED, LKPS, dan Matriks Penilaian ditampilkan sebagai bilah Akses Cepat di <code>/akreditasi/</code>. Renstra, Renop, dan Portal UPM terhubung ke <code>/akreditasi/upm/</code>.</p>

                <div class="ppb-lam-grid2">
                    <div class="ppb-lam-field">
                        <label>🗂️ Folder Utama Bukti Fisik LAMDIK (Google Drive)</label>
                        <?php ppb_render_media_url_control(array('id' => 'lam_folder_utama', 'name' => 'general[folder_utama_url]', 'value' => $gen['folder_utama_url'], 'placeholder' => 'https://drive.google.com/drive/folders/...')); ?>
                    </div>
                    <div class="ppb-lam-field">
                        <label>📘 Laporan Evaluasi Diri (LED) Lengkap</label>
                        <?php ppb_render_media_url_control(array('id' => 'lam_led_url', 'name' => 'general[led_url]', 'value' => $gen['led_url'], 'placeholder' => 'https://drive.google.com/file/d/...')); ?>
                    </div>
                    <div class="ppb-lam-field">
                        <label>📊 Laporan Kinerja Program Studi (LKPS / Excel &amp; PDF)</label>
                        <?php ppb_render_media_url_control(array('id' => 'lam_lkps_url', 'name' => 'general[lkps_url]', 'value' => $gen['lkps_url'], 'placeholder' => 'https://drive.google.com/file/d/...')); ?>
                    </div>
                    <div class="ppb-lam-field">
                        <label>⚖️ Matriks Penilaian &amp; Borang Instrumen LAMDIK</label>
                        <?php ppb_render_media_url_control(array('id' => 'lam_matriks_url', 'name' => 'general[matriks_url]', 'value' => $gen['matriks_url'], 'placeholder' => 'https://drive.google.com/file/d/...')); ?>
                    </div>
                    <div class="ppb-lam-field">
                        <label>🧭 Dokumen Renstra Prodi BK / PPB (2024–2028) &rarr; /akreditasi/upm/</label>
                        <?php ppb_render_media_url_control(array('id' => 'lam_renstra_url', 'name' => 'general[renstra_url]', 'value' => $gen['renstra_url'], 'placeholder' => 'https://drive.google.com/file/d/... atau PDF')); ?>
                    </div>
                    <div class="ppb-lam-field">
                        <label>📋 Dokumen Renop Tahunan Prodi BK &rarr; /akreditasi/upm/</label>
                        <?php ppb_render_media_url_control(array('id' => 'lam_renop_url', 'name' => 'general[renop_url]', 'value' => $gen['renop_url'], 'placeholder' => 'https://drive.google.com/file/d/... atau PDF')); ?>
                    </div>
                    <div class="ppb-lam-field" style="grid-column:1 / -1;">
                        <label>🏛️ URL Portal Sistem Penjaminan Mutu Internal (UPM / LPM UNM)</label>
                        <?php ppb_render_media_url_control(array('id' => 'lam_upm_portal', 'name' => 'general[upm_portal_url]', 'value' => $gen['upm_portal_url'], 'placeholder' => 'https://bpm.unm.ac.id/')); ?>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: EVIDEN 9 KRITERIA LAMDIK -->
            <div class="ppb-lam-card">
                <h2>📑 2. Tautan Folder / Berkas Drive per Kriteria LAMDIK (K1–K9)</h2>
                <p class="ppb-lam-desc">Setiap link yang diisi di bawah otomatis menambahkan tombol eviden berwarna emas ke dalam panel akordeon Kriteria terkait pada halaman <code>/akreditasi/</code> dan memperbarui penghitung tautan.</p>

                <div style="display:flex;flex-direction:column;gap:12px;">
                    <?php foreach ($krit as $idx => $kr) : ?>
                        <div style="display:grid;grid-template-columns:280px 180px 1fr;gap:12px;align-items:center;background:#FAF8F5;border:1px solid #e2e8f0;border-radius:12px;padding:12px 14px;">
                            <div>
                                <span style="display:inline-block;background:#0D3B22;color:#F5B82E;font-family:monospace;font-size:11px;font-weight:800;padding:2px 8px;border-radius:6px;margin-bottom:4px;">KRITERIA <?php echo (int) ($idx + 1); ?></span>
                                <div style="font-size:13px;font-weight:700;color:#0f172a;line-height:1.35;"><?php echo esc_html($kr['judul']); ?></div>
                            </div>
                            <div>
                                <label style="display:block;font-size:11px;font-weight:600;color:#64748b;margin-bottom:3px;">Label Tombol Publik</label>
                                <input type="text" name="kriteria[<?php echo (int) $idx; ?>][label_tombol]" value="<?php echo esc_attr($kr['label_tombol']); ?>" style="width:100%;padding:6px 10px;border:1px solid #cbd5e1;border-radius:8px;font-size:12.5px;" />
                            </div>
                            <div>
                                <label style="display:block;font-size:11px;font-weight:600;color:#64748b;margin-bottom:3px;">Link Folder Drive / PDF Bukti Fisik</label>
                                <?php ppb_render_media_url_control(array(
                                    'id'          => 'lam_krit_' . $idx,
                                    'name'        => 'kriteria[' . $idx . '][drive_url]',
                                    'value'       => $kr['drive_url'],
                                    'placeholder' => 'https://drive.google.com/drive/folders/... atau upload berkas',
                                )); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- SECTION 3: SERTIFIKAT & SK AKREDITASI S1, S2, S3 -->
            <div class="ppb-lam-card">
                <h2>🎓 3. Sertifikat &amp; SK Resmi Akreditasi (Jenjang S1, S2, S3)</h2>
                <p class="ppb-lam-desc">Terhubung otomatis ke halaman <code>/akreditasi/sertifikat/</code>, halaman <code>/profil/visi-misi/</code>, dan kartu jenjang pada Beranda.</p>

                <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(330px, 1fr));gap:16px;">
                    <?php
                    $deg_titles = array(
                        's1' => 'Jenjang Sarjana (S1 BK)',
                        's2' => 'Jenjang Magister (S2 BK)',
                        's3' => 'Jenjang Doktor (S3 BK)',
                    );
                    foreach (array('s1', 's2', 's3') as $deg) :
                        $sd = $sert[$deg];
                    ?>
                        <div style="background:#FAF8F5;border:1.5px solid #D4E3D8;border-radius:14px;padding:16px;display:flex;flex-direction:column;gap:10px;">
                            <div style="font-size:15px;font-weight:800;color:#0D3B22;border-bottom:1px solid #e2e8f0;padding-bottom:8px;">
                                <?php echo esc_html($deg_titles[$deg]); ?>
                            </div>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                                <div>
                                    <label style="display:block;font-size:11.5px;font-weight:700;margin-bottom:3px;">Peringkat</label>
                                    <input type="text" name="sertifikat[<?php echo $deg; ?>][peringkat]" value="<?php echo esc_attr($sd['peringkat']); ?>" style="width:100%;padding:6px 9px;border:1px solid #cbd5e1;border-radius:7px;font-size:12.5px;" />
                                </div>
                                <div>
                                    <label style="display:block;font-size:11.5px;font-weight:700;margin-bottom:3px;">Lembaga</label>
                                    <input type="text" name="sertifikat[<?php echo $deg; ?>][lembaga]" value="<?php echo esc_attr($sd['lembaga']); ?>" style="width:100%;padding:6px 9px;border:1px solid #cbd5e1;border-radius:7px;font-size:12.5px;" />
                                </div>
                            </div>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                                <div>
                                    <label style="display:block;font-size:11.5px;font-weight:700;margin-bottom:3px;">Nomor SK</label>
                                    <input type="text" name="sertifikat[<?php echo $deg; ?>][no_sk]" value="<?php echo esc_attr($sd['no_sk']); ?>" style="width:100%;padding:6px 9px;border:1px solid #cbd5e1;border-radius:7px;font-size:12.5px;" />
                                </div>
                                <div>
                                    <label style="display:block;font-size:11.5px;font-weight:700;margin-bottom:3px;">Masa Berlaku</label>
                                    <input type="text" name="sertifikat[<?php echo $deg; ?>][masa_berlaku]" value="<?php echo esc_attr($sd['masa_berlaku']); ?>" style="width:100%;padding:6px 9px;border:1px solid #cbd5e1;border-radius:7px;font-size:12.5px;" />
                                </div>
                            </div>
                            <div>
                                <label style="display:block;font-size:11.5px;font-weight:700;color:#0D3B22;margin-bottom:4px;">📄 Tautan / File PDF Sertifikat <?php echo strtoupper($deg); ?></label>
                                <?php ppb_render_media_url_control(array(
                                    'id'          => 'lam_sert_cert_' . $deg,
                                    'name'        => 'sertifikat[' . $deg . '][cert_url]',
                                    'value'       => $sd['cert_url'],
                                    'placeholder' => 'https://drive.google.com/... atau upload PDF',
                                    'title'       => 'Upload / Pilih PDF Sertifikat ' . strtoupper($deg),
                                )); ?>
                            </div>
                            <div>
                                <label style="display:block;font-size:11.5px;font-weight:700;color:#0D3B22;margin-bottom:4px;">🏛️ Tautan / File SK Resmi <?php echo strtoupper($deg); ?></label>
                                <?php ppb_render_media_url_control(array(
                                    'id'          => 'lam_sert_sk_' . $deg,
                                    'name'        => 'sertifikat[' . $deg . '][sk_url]',
                                    'value'       => $sd['sk_url'],
                                    'placeholder' => 'https://drive.google.com/... atau upload PDF',
                                    'title'       => 'Upload / Pilih PDF SK Resmi ' . strtoupper($deg),
                                )); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:12px;margin-bottom:30px;">
                <button type="submit" name="ppb_save_lamdik_submit" class="button button-primary" style="background:#0D3B22;border-color:#0D3B22;color:#F5B82E;font-weight:800;font-size:14px;padding:10px 26px;height:auto;border-radius:10px;box-shadow:0 4px 12px rgba(13,59,34,0.2);">
                    💾 Simpan Seluruh Pengaturan Akreditasi &amp; LAMDIK
                </button>
            </div>
        </form>
    </div>
    <?php
}
}

/* ========================================================================== */
/* 3. FRONTEND FILTERS: /akreditasi/, /akreditasi/sertifikat/, /upm/, /visi/  */
/* ========================================================================== */

add_filter('the_content', function($content) {
    if (is_admin() || !is_string($content)) return $content;

    $slug_path = function_exists('ppb_get_current_page_slug_path') ? ppb_get_current_page_slug_path() : '';
    $settings  = ppb_get_lamdik_settings();
    $gen       = $settings['general'];
    $krit      = $settings['kriteria'];
    $sert      = $settings['sertifikat'];

    // 3a. /akreditasi/ -> Quick Access Bar + Per-Kriteria Drive Eviden Buttons
    if ($slug_path === 'akreditasi' || strpos($content, 'id="bk-lamdik-accordion"') !== false) {
        if (strpos($content, 'id="bk-lamdik-accordion"') !== false) {
            $quick_links = array();
            if (!empty($gen['folder_utama_url'])) {
                $quick_links[] = array('label' => '🗂️ Folder Induk Drive LAMDIK', 'url' => $gen['folder_utama_url'], 'primary' => true);
            }
            if (!empty($gen['led_url'])) {
                $quick_links[] = array('label' => '📘 Dokumen LED', 'url' => $gen['led_url'], 'primary' => false);
            }
            if (!empty($gen['lkps_url'])) {
                $quick_links[] = array('label' => '📊 Dokumen LKPS', 'url' => $gen['lkps_url'], 'primary' => false);
            }
            if (!empty($gen['matriks_url'])) {
                $quick_links[] = array('label' => '⚖️ Matriks Penilaian', 'url' => $gen['matriks_url'], 'primary' => false);
            }

            if (!empty($quick_links)) {
                $bar = '<div id="ppb-lamdik-drive-bar" style="background:#0D3B22;color:#fff;border-radius:16px;padding:18px 22px;margin:0 0 22px;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:14px;box-shadow:0 6px 20px rgba(13,59,34,0.14);">'
                     . '<div><div style="font-size:10.5px;font-weight:800;color:#F5B82E;text-transform:uppercase;letter-spacing:.06em;margin-bottom:3px;">Repositori Resmi Bukti Fisik</div>'
                     . '<strong style="font-size:15px;color:#fff;">Akses Cepat Dokumen Induk Akreditasi LAMDIK</strong></div>'
                     . '<div style="display:flex;flex-wrap:wrap;gap:8px;">';
                foreach ($quick_links as $ql) {
                    $style = $ql['primary']
                        ? 'background:#F5B82E;color:#0D3B22;border:none;font-weight:800;'
                        : 'background:rgba(255,255,255,0.14);color:#fff;border:1px solid rgba(255,255,255,0.28);font-weight:600;';
                    $bar .= '<a href="' . esc_url($ql['url']) . '" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:9px;font-size:12.5px;text-decoration:none;' . $style . '">' . esc_html($ql['label']) . ' ↗</a>';
                }
                $bar .= '</div></div>';
                $content = preg_replace('/(<div\b[^>]*\bid=["\']bk-lamdik-accordion["\'])/i', $bar . '$1', $content, 1);
            }

            // Inject Drive buttons & increment badge count for each configured Kriteria 0..8
            foreach ($krit as $idx => $kr) {
                $drive_url = trim((string) ($kr['drive_url'] ?? ''));
                if ($drive_url === '') continue;
                $btn_lbl = trim((string) ($kr['label_tombol'] ?? ('Eviden Drive K' . ($idx + 1))));

                $pattern = '/(data-lamdik-idx=["\']' . (int) $idx . '["\'].*?)(\d+\s*Tautan)(.*?<div\s+style=["\']display:flex;flex-wrap:wrap;gap:8px;["\'][^>]*>)(.*?)(<\/div>)/is';
                $content = preg_replace_callback($pattern, function($m) use ($drive_url, $btn_lbl) {
                    $old_count = 3;
                    if (preg_match('/(\d+)/', $m[2], $cm)) {
                        $old_count = (int) $cm[1];
                    }
                    $new_count = ($old_count + 1) . ' Tautan';
                    $ev_btn = "\n                            <a href=\"" . esc_url($drive_url) . "\" target=\"_blank\" rel=\"noopener\" style=\"display:inline-flex;align-items:center;justify-content:center;gap:5px;background:#F5B82E;color:#0D3B22;font-size:12px;font-weight:700;padding:8px 14px;border-radius:8px;text-decoration:none;\">📂 " . esc_html($btn_lbl) . " ↗</a>";
                    return $m[1] . $new_count . $m[3] . $m[4] . $ev_btn . $m[5];
                }, $content, 1);
            }
        }
    }

    // 3b. /akreditasi/sertifikat/ -> Live metadata + Cert/SK download links (zero href="#")
    if ($slug_path === 'akreditasi/sertifikat' || ($slug_path === 'sertifikat') || (strpos($content, 'Unduh Sertifikat S1') !== false && strpos($content, 'Unduh SK Resmi') !== false)) {
        $card_defs = array(
            's1' => 'Jenjang Sarjana (S1)',
            's2' => 'Jenjang Magister (S2)',
            's3' => 'Jenjang Doktor (S3)',
        );
        foreach ($card_defs as $deg => $marker) {
            if (strpos($content, $marker) === false) continue;
            $sd = $sert[$deg];
            $cert_label = '📄 Unduh Sertifikat ' . strtoupper($deg);
            $cert_btn = !empty($sd['cert_url'])
                ? '<a href="' . esc_url($sd['cert_url']) . '" target="_blank" rel="noopener" style="flex:1;text-align:center;background:#0D3B22;color:#ffffff;font-size:12.5px;font-weight:700;padding:10px 14px;border-radius:8px;text-decoration:none;">' . esc_html($cert_label) . ' ↗</a>'
                : '<span style="flex:1;text-align:center;background:#E2E8F0;color:#475569;font-size:12px;font-weight:600;padding:10px 14px;border-radius:8px;">Sertifikat Belum Diunggah</span>';
            $sk_btn = !empty($sd['sk_url'])
                ? '<a href="' . esc_url($sd['sk_url']) . '" target="_blank" rel="noopener" style="background:#FAF8F5;color:#0D3B22;border:1px solid #D4E3D8;font-size:12px;font-weight:600;padding:10px 14px;border-radius:8px;text-decoration:none;">Unduh SK Resmi ↗</a>'
                : '<span style="background:#F8FAFC;color:#64748B;border:1px solid #E2E8F0;font-size:12px;font-weight:600;padding:10px 14px;border-radius:8px;">SK Belum Diunggah</span>';

            $card_pattern = '/(' . preg_quote($marker, '/') . '.*?<div\s+style=["\']background:#FAF8F5;[^"\']*["\'][^>]*>)(.*?)(<\/div>\s*<div\s+style=["\']display:flex;gap:8px;["\'][^>]*>)(.*?)(<\/div>)/is';
            $content = preg_replace_callback($card_pattern, function($m) use ($sd, $cert_btn, $sk_btn) {
                $meta_html = '<div><strong>Lembaga:</strong> ' . esc_html($sd['lembaga']) . '</div>'
                           . '<div><strong>No. SK:</strong> ' . esc_html($sd['no_sk']) . '</div>'
                           . '<div><strong>Masa Berlaku:</strong> ' . esc_html($sd['masa_berlaku']) . '</div>';
                return $m[1] . "\n                " . $meta_html . "\n            " . $m[3] . "\n                " . $cert_btn . "\n                " . $sk_btn . "\n            " . $m[5];
            }, $content, 1);

            // Also update the peringkat pill inside that card if customized
            if (!empty($sd['peringkat'])) {
                $content = preg_replace(
                    '/(<!--\s*' . strtoupper($deg) . '\s*-->.*?<div[^>]*background:#DCFCE7[^>]*>|' . preg_quote($marker, '/') . '<\/span>\s*<span[^>]*background:#DCFCE7[^>]*>)([^<]+)(<\/span>|<\/div>)/is',
                    '$1' . esc_html($sd['peringkat']) . '$3',
                    $content,
                    1
                );
            }
        }
    }

    // 3c. /akreditasi/upm/ -> Replace Renstra PDF, Renop PDF, and UPM portal URL
    if ($slug_path === 'akreditasi/upm' || $slug_path === 'upm' || strpos($content, 'Unduh Dokumen Renstra PDF') !== false) {
        if (!empty($gen['renstra_url'])) {
            $content = preg_replace(
                '/<a\s+href=["\']#["\']([^>]*)>(\s*📄\s*Unduh Dokumen Renstra PDF\s*)<\/a>/is',
                '<a href="' . esc_url($gen['renstra_url']) . '" target="_blank" rel="noopener"$1>$2 ↗</a>',
                $content
            );
        } else {
            $content = preg_replace(
                '/<a\s+href=["\']#["\']([^>]*)>\s*📄\s*Unduh Dokumen Renstra PDF\s*<\/a>/is',
                '<span style="display:inline-block;background:#E2E8F0;color:#475569;font-size:12px;font-weight:600;padding:8px 14px;border-radius:8px;">Dokumen Renstra Belum Diunggah</span>',
                $content
            );
        }

        if (!empty($gen['renop_url'])) {
            $content = preg_replace(
                '/<a\s+href=["\']#["\']([^>]*)>(\s*📄\s*Unduh Dokumen Renop PDF\s*)<\/a>/is',
                '<a href="' . esc_url($gen['renop_url']) . '" target="_blank" rel="noopener"$1>$2 ↗</a>',
                $content
            );
        } else {
            $content = preg_replace(
                '/<a\s+href=["\']#["\']([^>]*)>\s*📄\s*Unduh Dokumen Renop PDF\s*<\/a>/is',
                '<span style="display:inline-block;background:#E2E8F0;color:#475569;font-size:12px;font-weight:600;padding:8px 14px;border-radius:8px;">Dokumen Renop Belum Diunggah</span>',
                $content
            );
        }

        if (!empty($gen['upm_portal_url'])) {
            $content = str_replace('href="https://bpm.unm.ac.id/"', 'href="' . esc_url($gen['upm_portal_url']) . '"', $content);
        }
    }

    // 3d. /profil/visi-misi/ -> Replace broken /wp-content/uploads/sertifikat-akreditasi-s{1,2,3}.pdf links
    if (strpos($content, 'sertifikat-akreditasi-s1.pdf') !== false || strpos($content, 'sertifikat-akreditasi-s2.pdf') !== false || strpos($content, 'sertifikat-akreditasi-s3.pdf') !== false) {
        $fallback_page = !empty($gen['folder_utama_url']) ? $gen['folder_utama_url'] : home_url('/akreditasi/sertifikat/');
        foreach (array('s1', 's2', 's3') as $deg) {
            $target_url = !empty($sert[$deg]['cert_url'])
                ? $sert[$deg]['cert_url']
                : (!empty($sert[$deg]['sk_url']) ? $sert[$deg]['sk_url'] : $fallback_page);
            $content = str_replace(
                'https://bk-fip.unm.ac.id/wp-content/uploads/sertifikat-akreditasi-' . $deg . '.pdf',
                esc_url($target_url),
                $content
            );
        }
    }

    return $content;
}, 12);
