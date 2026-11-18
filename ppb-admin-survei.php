<?php
/**
 * Module: PPB FIP UNM - Survei Kepuasan Mahasiswa & Penjaminan Mutu (GPM)
 * Description: Backend persistence (WP AJAX + Option), Admin Analytics Dashboard, Export CSV UTF-8,
 *              serta Frontend Restoration & Live Submission untuk halaman /survei/.
 * Author: Jurusan PPB / BK FIP UNM
 */

if (!defined('ABSPATH')) exit;

/* ========================================================================== */
/* 1. SKEMA INSTRUMEN SURVEI, PENGATURAN PERIODE & STATISTIK AGGREGATE        */
/* ========================================================================== */

if (!function_exists('ppb_get_survei_questions')) {
function ppb_get_survei_questions() {
    return array(
        'q1' => array(
            'section' => 'dosen',
            'step'    => 2,
            'no'      => 1,
            'text'    => 'Kehadiran dan ketepatan waktu dosen dalam mengajar perkuliahan sesuai jadwal kontrak kuliah (RPS).',
        ),
        'q2' => array(
            'section' => 'dosen',
            'step'    => 2,
            'no'      => 2,
            'text'    => 'Penguasaan materi, studi kasus konseling, dan penerapan kurikulum OBE oleh dosen pengampu.',
        ),
        'q3' => array(
            'section' => 'dosen',
            'step'    => 2,
            'no'      => 3,
            'text'    => 'Keterbukaan dosen pembimbing dalam memberikan koreksi, masukan, dan respon bimbingan skripsi.',
        ),
        'q4' => array(
            'section' => 'dosen',
            'step'    => 2,
            'no'      => 4,
            'text'    => 'Keramahan dan kecepatan tenaga kependidikan (staf prodi) dalam melayani administrasi surat menyurat.',
        ),
        'q5' => array(
            'section' => 'kemahasiswaan',
            'step'    => 3,
            'no'      => 5,
            'text'    => 'Pembinaan dan pendampingan prodi terhadap delegasi lomba mahasiswa di tingkat nasional / internasional.',
        ),
        'q6' => array(
            'section' => 'kemahasiswaan',
            'step'    => 3,
            'no'      => 6,
            'text'    => 'Transparansi informasi beasiswa (KIP-K, Afirmasi 3T, Beasiswa Prestasi) dan advokasi keringanan UKT.',
        ),
        'q7' => array(
            'section' => 'kemahasiswaan',
            'step'    => 3,
            'no'      => 7,
            'text'    => 'Layanan konseling sebaya dan keterbukaan akses konsultasi psikologis di Laboratorium BK FIP UNM.',
        ),
        'q8' => array(
            'section' => 'tatapamong',
            'step'    => 4,
            'no'      => 8,
            'text'    => 'Kemudahan dan kestabilan sistem pemrograman Kartu Rencana Studi (KRS Online) prodi.',
        ),
        'q9' => array(
            'section' => 'tatapamong',
            'step'    => 4,
            'no'      => 9,
            'text'    => 'Kepastian jadwal seminar proposal, seminar hasil, dan ujian skripsi tanpa penundaan sepihak.',
        ),
        'q10' => array(
            'section' => 'sarpras',
            'step'    => 5,
            'no'      => 10,
            'text'    => 'Kebersihan, kenyamanan ruang kelas ber-AC, dan kelengkapan proyektor LCD saat kuliah.',
        ),
        'q11' => array(
            'section' => 'sarpras',
            'step'    => 5,
            'no'      => 11,
            'text'    => 'Ketersediaan alat tes psikologi dan fasilitas ruang mikrokonseling kedap suara di Lab BK.',
        ),
    );
}
}

if (!function_exists('ppb_get_survei_settings')) {
function ppb_get_survei_settings() {
    $defaults = array(
        'periode_aktif'   => 'Semester Genap 2025/2026',
        'is_open'         => '1',
        'judul_pengantar' => 'Instrumen Evaluasi Mutu Akademik, Kemahasiswaan & Layanan Lab BK (SPMI / LAMDIK)',
        'backup_form_url' => '',
    );
    $saved = get_option('ppb_survei_settings', array());
    if (!is_array($saved)) {
        return $defaults;
    }
    return wp_parse_args($saved, $defaults);
}
}

if (!function_exists('ppb_get_survei_submissions')) {
function ppb_get_survei_submissions() {
    $subs = get_option('ppb_survei_submissions', array());
    return is_array($subs) ? array_values($subs) : array();
}
}

if (!function_exists('ppb_calculate_survei_summary')) {
function ppb_calculate_survei_summary($subs, $periode_filter = '') {
    $questions = ppb_get_survei_questions();
    $filtered  = array();
    foreach ((array) $subs as $row) {
        if ($periode_filter !== '' && ($row['periode'] ?? '') !== $periode_filter) {
            continue;
        }
        $filtered[] = $row;
    }

    $sec_sums   = array('dosen' => 0, 'kemahasiswaan' => 0, 'tatapamong' => 0, 'sarpras' => 0);
    $sec_counts = array('dosen' => 0, 'kemahasiswaan' => 0, 'tatapamong' => 0, 'sarpras' => 0);
    $total_sum  = 0;
    $total_cnt  = 0;
    $by_angkatan = array();
    $periodes   = array();

    foreach ((array) $subs as $row) {
        if (!empty($row['periode'])) {
            $periodes[$row['periode']] = true;
        }
    }

    foreach ($filtered as $row) {
        $ang = !empty($row['angkatan']) ? (string) $row['angkatan'] : 'Lainnya';
        $by_angkatan[$ang] = ($by_angkatan[$ang] ?? 0) + 1;

        $answers = isset($row['answers']) && is_array($row['answers']) ? $row['answers'] : array();
        foreach ($questions as $qk => $qmeta) {
            $val = isset($answers[$qk]) ? (int) $answers[$qk] : 0;
            if ($val >= 1 && $val <= 4) {
                $sec = $qmeta['section'];
                $sec_sums[$sec] += $val;
                $sec_counts[$sec]++;
                $total_sum += $val;
                $total_cnt++;
            }
        }
    }

    $avg_total = $total_cnt > 0 ? round($total_sum / $total_cnt, 2) : 0;
    $ikm_pct   = $total_cnt > 0 ? round(($avg_total / 4) * 100, 1) : 0;

    $sec_avg = array();
    foreach ($sec_sums as $sec => $sum) {
        $cnt = $sec_counts[$sec];
        $sec_avg[$sec] = $cnt > 0 ? round($sum / $cnt, 2) : 0;
    }

    ksort($by_angkatan);

    return array(
        'count'       => count($filtered),
        'avg_total'   => $avg_total,
        'ikm_pct'     => $ikm_pct,
        'sections'    => $sec_avg,
        'by_angkatan' => $by_angkatan,
        'periodes'    => array_keys($periodes),
    );
}
}

/* ========================================================================== */
/* 2. ENDPOINT WP AJAX: SIMPAN RESPON SURVEI KEPUASAN MAHASISWA               */
/* ========================================================================== */

if (!function_exists('ppb_ajax_submit_survey')) {
function ppb_ajax_submit_survey() {
    check_ajax_referer('ppb_survei_submit_nonce', 'nonce');

    $settings = ppb_get_survei_settings();
    if (($settings['is_open'] ?? '1') !== '1') {
        wp_send_json_error(array(
            'message' => 'Periode pengisian survei kepuasan saat ini sedang ditutup oleh Admin / GPM.',
        ), 403);
    }

    $nim      = isset($_POST['nim']) ? sanitize_text_field(wp_unslash($_POST['nim'])) : '';
    $nama     = isset($_POST['nama']) ? sanitize_text_field(wp_unslash($_POST['nama'])) : '';
    $prodi    = isset($_POST['prodi']) ? sanitize_text_field(wp_unslash($_POST['prodi'])) : 'S1 Bimbingan dan Konseling';
    $angkatan = isset($_POST['angkatan']) ? sanitize_text_field(wp_unslash($_POST['angkatan'])) : '';
    $saran    = isset($_POST['saran']) ? sanitize_textarea_field(wp_unslash($_POST['saran'])) : '';
    $periode  = !empty($settings['periode_aktif']) ? $settings['periode_aktif'] : 'Semester Aktif';

    if ($nim === '' || $nama === '') {
        wp_send_json_error(array(
            'message' => 'Mohon lengkapi Nomor Induk Mahasiswa (NIM) dan Nama Lengkap sebelum mengirim survei.',
        ), 400);
    }

    $questions = ppb_get_survei_questions();
    $answers   = array();
    $sum       = 0;
    $answered  = 0;
    $missing   = array();

    foreach ($questions as $qk => $qmeta) {
        $raw = isset($_POST[$qk]) ? (int) $_POST[$qk] : 0;
        if ($raw < 1 || $raw > 4) {
            $missing[] = $qmeta['no'];
        } else {
            $answers[$qk] = $raw;
            $sum += $raw;
            $answered++;
        }
    }

    if (!empty($missing)) {
        wp_send_json_error(array(
            'message' => 'Mohon lengkapi penilaian skala (1-4) untuk pertanyaan nomor: ' . implode(', ', $missing) . '.',
        ), 400);
    }

    $avg_score = $answered > 0 ? round($sum / $answered, 2) : 0;
    $ticket    = 'SRV-' . gmdate('ym') . '-' . strtoupper(substr(md5(uniqid((string) wp_rand(), true)), 0, 5));
    $salt      = function_exists('wp_salt') ? wp_salt('auth') : 'ppb-survei-salt';
    $ip_raw    = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '';
    $ip_hash   = $ip_raw !== '' ? substr(hash('sha256', $ip_raw . '|' . $salt), 0, 12) : '';
    $now       = current_time('mysql');

    $entry = array(
        'ticket'       => $ticket,
        'submitted_at' => $now,
        'periode'      => $periode,
        'nim'          => $nim,
        'nama'         => $nama,
        'prodi'        => $prodi ?: 'S1 Bimbingan dan Konseling',
        'angkatan'     => $angkatan ?: '-',
        'answers'      => $answers,
        'avg_score'    => $avg_score,
        'saran'        => $saran,
        'ip_hash'      => $ip_hash,
    );

    $subs = ppb_get_survei_submissions();
    array_unshift($subs, $entry);
    if (count($subs) > 2000) {
        $subs = array_slice($subs, 0, 2000);
    }
    update_option('ppb_survei_submissions', array_values($subs), false);

    wp_send_json_success(array(
        'ticket'       => $ticket,
        'submitted_at' => $now,
        'periode'      => $periode,
        'avg_score'    => $avg_score,
        'nama'         => $nama,
        'nim'          => $nim,
        'message'      => 'Data survei kepuasan (' . $ticket . ') berhasil disimpan ke sistem GPM FIP UNM.',
    ));
}
}
add_action('wp_ajax_ppb_submit_survey', 'ppb_ajax_submit_survey');
add_action('wp_ajax_nopriv_ppb_submit_survey', 'ppb_ajax_submit_survey');

/* ========================================================================== */
/* 3. CSV EXPORT HANDLER & STREAM HELPER (CLEAN UTF-8 BOM FOR EXCEL)          */
/* ========================================================================== */

if (!function_exists('ppb_build_survei_csv_rows')) {
function ppb_build_survei_csv_rows($subs, $periode_filter = '', $angkatan_filter = '') {
    $questions = ppb_get_survei_questions();
    $header = array(
        'Tiket',
        'Waktu Kirim',
        'Periode Survei',
        'NIM',
        'Nama Mahasiswa',
        'Program Studi',
        'Angkatan',
    );
    foreach ($questions as $qk => $qmeta) {
        $header[] = 'Q' . $qmeta['no'] . ' (' . ucfirst($qmeta['section']) . ')';
    }
    $header[] = 'Rata-Rata Skor (1-4)';
    $header[] = 'Saran & Rekomendasi';

    $rows = array($header);
    foreach ((array) $subs as $item) {
        if ($periode_filter !== '' && ($item['periode'] ?? '') !== $periode_filter) {
            continue;
        }
        if ($angkatan_filter !== '' && ($item['angkatan'] ?? '') !== $angkatan_filter) {
            continue;
        }
        $answers = isset($item['answers']) && is_array($item['answers']) ? $item['answers'] : array();
        $row = array(
            $item['ticket'] ?? '-',
            $item['submitted_at'] ?? '',
            $item['periode'] ?? '',
            $item['nim'] ?? '',
            $item['nama'] ?? '',
            $item['prodi'] ?? '',
            $item['angkatan'] ?? '',
        );
        foreach ($questions as $qk => $qmeta) {
            $row[] = isset($answers[$qk]) ? (int) $answers[$qk] : '';
        }
        $row[] = isset($item['avg_score']) ? number_format((float) $item['avg_score'], 2, '.', '') : '';
        $row[] = str_replace(array("\r\n", "\r", "\n"), ' ', (string) ($item['saran'] ?? ''));
        $rows[] = $row;
    }

    return $rows;
}
}

if (!function_exists('ppb_handle_export_survei_csv')) {
function ppb_handle_export_survei_csv() {
    if (!current_user_can('edit_posts')) {
        wp_die('Anda tidak memiliki izin mengekspor data survei.');
    }
    check_admin_referer('ppb_export_survei_csv_nonce');

    $periode_filter  = isset($_GET['periode']) ? sanitize_text_field(wp_unslash($_GET['periode'])) : '';
    $angkatan_filter = isset($_GET['angkatan']) ? sanitize_text_field(wp_unslash($_GET['angkatan'])) : '';

    $subs = ppb_get_survei_submissions();
    $rows = ppb_build_survei_csv_rows($subs, $periode_filter, $angkatan_filter);

    $filename = 'rekap-survei-kepuasan-bk-unm-' . gmdate('Ymd-His') . '.csv';
    nocache_headers();
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $out = fopen('php://output', 'w');
    if ($out) {
        fwrite($out, "\xEF\xBB\xBF");
        foreach ($rows as $r) {
            fputcsv($out, $r);
        }
        fclose($out);
    }
    exit;
}
}
add_action('admin_post_ppb_export_survei_csv', 'ppb_handle_export_survei_csv');

/* ========================================================================== */
/* 4. HALAMAN WP-ADMIN: REKAP & ANALITIK SURVEI KEPUASAN GPM                  */
/* ========================================================================== */

if (!function_exists('ppb_render_survei_admin_page')) {
function ppb_render_survei_admin_page() {
    if (!current_user_can('edit_posts')) {
        wp_die('Anda tidak memiliki izin mengakses halaman ini.');
    }

    $notice = '';

    // 1. Simpan Pengaturan Periode Survei
    if (isset($_POST['ppb_save_survei_settings']) && check_admin_referer('ppb_survei_admin_action', 'ppb_survei_nonce')) {
        $backup_raw = isset($_POST['backup_form_url']) ? wp_unslash($_POST['backup_form_url']) : '';
        $backup_url = function_exists('ppb_clean_document_url') ? ppb_clean_document_url($backup_raw) : esc_url_raw($backup_raw);
        $new_cfg = array(
            'periode_aktif'   => sanitize_text_field(wp_unslash($_POST['periode_aktif'] ?? 'Semester Genap 2025/2026')) ?: 'Semester Genap 2025/2026',
            'is_open'         => !empty($_POST['is_open']) ? '1' : '0',
            'judul_pengantar' => sanitize_text_field(wp_unslash($_POST['judul_pengantar'] ?? '')),
            'backup_form_url' => $backup_url,
        );
        update_option('ppb_survei_settings', $new_cfg, false);
        $notice = '<div class="notice notice-success is-dismissible"><p><strong>✅ Pengaturan periode &amp; status Survei Kepuasan GPM berhasil disimpan!</strong></p></div>';
    }

    // 2. Hapus 1 responden berdasarkan ticket
    if (isset($_POST['ppb_delete_survei_ticket']) && check_admin_referer('ppb_survei_admin_action', 'ppb_survei_nonce')) {
        $del_ticket = sanitize_text_field(wp_unslash($_POST['ppb_delete_survei_ticket']));
        $subs = ppb_get_survei_submissions();
        $kept = array();
        foreach ($subs as $s) {
            if (($s['ticket'] ?? '') !== $del_ticket) {
                $kept[] = $s;
            }
        }
        update_option('ppb_survei_submissions', array_values($kept), false);
        $notice = '<div class="notice notice-warning is-dismissible"><p>Respon survei dengan tiket <strong>' . esc_html($del_ticket) . '</strong> telah dihapus.</p></div>';
    }

    // 3. Filter tampilan
    $periode_filter  = isset($_GET['f_periode']) ? sanitize_text_field(wp_unslash($_GET['f_periode'])) : '';
    $angkatan_filter = isset($_GET['f_angkatan']) ? sanitize_text_field(wp_unslash($_GET['f_angkatan'])) : '';
    $search_kw       = isset($_GET['f_q']) ? strtolower(trim(sanitize_text_field(wp_unslash($_GET['f_q'])))) : '';

    $settings = ppb_get_survei_settings();
    $subs     = ppb_get_survei_submissions();
    $summary  = ppb_calculate_survei_summary($subs, $periode_filter);
    $questions = ppb_get_survei_questions();

    $visible_subs = array();
    $all_angkatans = array();
    foreach ($subs as $s) {
        if (!empty($s['angkatan'])) {
            $all_angkatans[$s['angkatan']] = true;
        }
        if ($periode_filter !== '' && ($s['periode'] ?? '') !== $periode_filter) continue;
        if ($angkatan_filter !== '' && ($s['angkatan'] ?? '') !== $angkatan_filter) continue;
        if ($search_kw !== '') {
            $blob = strtolower(($s['ticket'] ?? '') . ' ' . ($s['nim'] ?? '') . ' ' . ($s['nama'] ?? '') . ' ' . ($s['saran'] ?? ''));
            if (strpos($blob, $search_kw) === false) continue;
        }
        $visible_subs[] = $s;
    }
    ksort($all_angkatans);

    $csv_export_url = wp_nonce_url(
        add_query_arg(array(
            'action'   => 'ppb_export_survei_csv',
            'periode'  => $periode_filter,
            'angkatan' => $angkatan_filter,
        ), admin_url('admin-post.php')),
        'ppb_export_survei_csv_nonce'
    );
    ?>
    <div class="wrap" style="max-width:1240px;margin:20px auto;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;color:#1e293b;">
        <div style="background:linear-gradient(135deg,#0D3B22 0%,#185333 100%);color:#fff;border-radius:16px;padding:24px 28px;margin-bottom:20px;display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:14px;box-shadow:0 8px 20px rgba(13,59,34,0.16);">
            <div>
                <span style="display:inline-block;background:rgba(245,184,46,0.2);color:#F5B82E;border:1px solid rgba(245,184,46,0.4);padding:3px 12px;border-radius:999px;font-size:11px;font-weight:700;text-transform:uppercase;margin-bottom:8px;">SPMI &amp; Evaluasi GPM FIP UNM</span>
                <h1 style="margin:0 0 6px;color:#fff;font-size:23px;font-weight:800;">📊 Rekap &amp; Analitik Survei Kepuasan Mahasiswa</h1>
                <p style="margin:0;color:rgba(255,255,255,0.85);font-size:13px;">Data isian instrumen /survei/ tersimpan otomatis di database WordPress dan siap diekspor ke Excel (CSV UTF-8) untuk dokumen LAMDIK &amp; AMI.</p>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <a href="<?php echo esc_url($csv_export_url); ?>" class="button" style="background:#F5B82E;color:#0D3B22;border:none;font-weight:700;padding:6px 15px;border-radius:8px;height:auto;">📥 Export CSV (<?php echo count($visible_subs); ?>)</a>
                <a href="<?php echo esc_url(home_url('/survei/')); ?>" target="_blank" class="button" style="border-radius:8px;">↗ Buka Halaman /survei/</a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=ppb-portal-hub')); ?>" class="button" style="border-radius:8px;">← Pusat Kendali</a>
            </div>
        </div>

        <?php echo $notice; ?>

        <!-- KPI Ringkas GPM -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:14px;margin-bottom:22px;">
            <div style="background:#fff;border:1px solid #cbd5e1;border-radius:12px;padding:16px 18px;">
                <span style="font-size:11px;font-weight:700;text-transform:uppercase;color:#64748b;display:block;">Total Responden</span>
                <strong style="font-size:26px;font-weight:800;color:#0D3B22;display:block;margin-top:4px;"><?php echo (int) $summary['count']; ?></strong>
                <span style="font-size:11.5px;color:#059669;font-weight:600;"><?php echo $periode_filter ? esc_html($periode_filter) : 'Semua Periode'; ?></span>
            </div>
            <div style="background:#0D3B22;color:#fff;border-radius:12px;padding:16px 18px;">
                <span style="font-size:11px;font-weight:700;text-transform:uppercase;color:#F5B82E;display:block;">Indeks Kepuasan (IKM)</span>
                <strong style="font-size:26px;font-weight:800;color:#fff;display:block;margin-top:4px;"><?php echo esc_html(number_format($summary['ikm_pct'], 1)); ?>%</strong>
                <span style="font-size:11.5px;color:rgba(255,255,255,0.85);">Skor Rata: <?php echo esc_html(number_format($summary['avg_total'], 2)); ?> / 4.00</span>
            </div>
            <div style="background:#fff;border:1px solid #cbd5e1;border-radius:12px;padding:16px 18px;">
                <span style="font-size:11px;font-weight:700;text-transform:uppercase;color:#64748b;display:block;">Dosen &amp; Tendik (Q1–Q4)</span>
                <strong style="font-size:24px;font-weight:800;color:#0D3B22;display:block;margin-top:4px;"><?php echo esc_html(number_format($summary['sections']['dosen'] ?? 0, 2)); ?> / 4</strong>
            </div>
            <div style="background:#fff;border:1px solid #cbd5e1;border-radius:12px;padding:16px 18px;">
                <span style="font-size:11px;font-weight:700;text-transform:uppercase;color:#64748b;display:block;">Kemahasiswaan (Q5–Q7)</span>
                <strong style="font-size:24px;font-weight:800;color:#0D3B22;display:block;margin-top:4px;"><?php echo esc_html(number_format($summary['sections']['kemahasiswaan'] ?? 0, 2)); ?> / 4</strong>
            </div>
            <div style="background:#fff;border:1px solid #cbd5e1;border-radius:12px;padding:16px 18px;">
                <span style="font-size:11px;font-weight:700;text-transform:uppercase;color:#64748b;display:block;">Tata Pamong &amp; Sarpras</span>
                <strong style="font-size:24px;font-weight:800;color:#0D3B22;display:block;margin-top:4px;"><?php echo esc_html(number_format($summary['sections']['tatapamong'] ?? 0, 2)); ?> · <?php echo esc_html(number_format($summary['sections']['sarpras'] ?? 0, 2)); ?></strong>
            </div>
        </div>

        <!-- Pengaturan Periode Survei -->
        <form method="post" style="background:#fff;border:1px solid #cbd5e1;border-radius:12px;padding:20px 22px;margin-bottom:22px;">
            <?php wp_nonce_field('ppb_survei_admin_action', 'ppb_survei_nonce'); ?>
            <h2 style="margin:0 0 12px;font-size:16px;color:#0D3B22;">⚙️ Pengaturan Periode &amp; Status Pengisian Survei</h2>
            <div style="display:grid;grid-template-columns:1.2fr 1.6fr 1.2fr auto;gap:12px;align-items:end;flex-wrap:wrap;">
                <div>
                    <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px;">Periode / Semester Aktif</label>
                    <input type="text" name="periode_aktif" value="<?php echo esc_attr($settings['periode_aktif']); ?>" class="widefat" placeholder="Semester Genap 2025/2026">
                </div>
                <div>
                    <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px;">Subjudul Pengantar GPM</label>
                    <input type="text" name="judul_pengantar" value="<?php echo esc_attr($settings['judul_pengantar']); ?>" class="widefat">
                </div>
                <div>
                    <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px;">Tautan Backup Google Form (Opsional)</label>
                    <input type="url" name="backup_form_url" value="<?php echo esc_attr($settings['backup_form_url']); ?>" class="widefat" placeholder="https://forms.gle/...">
                </div>
                <div style="display:flex;align-items:center;gap:12px;">
                    <label style="font-size:12.5px;font-weight:700;white-space:nowrap;">
                        <input type="checkbox" name="is_open" value="1" <?php checked($settings['is_open'], '1'); ?>> Formulir Dibuka
                    </label>
                    <button type="submit" name="ppb_save_survei_settings" class="button button-primary" style="background:#0D3B22;border-color:#0D3B22;">💾 Simpan</button>
                </div>
            </div>
        </form>

        <!-- Filter & Daftar Responden -->
        <div style="background:#fff;border:1px solid #cbd5e1;border-radius:12px;padding:22px;">
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:16px;">
                <h2 style="margin:0;font-size:17px;color:#0D3B22;">📋 Daftar Responden &amp; Masukan Mahasiswa (<?php echo count($visible_subs); ?>)</h2>
                <form method="get" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                    <input type="hidden" name="page" value="ppb-survei-manager">
                    <select name="f_periode">
                        <option value="">Semua Periode</option>
                        <?php foreach ($summary['periodes'] as $per) : ?>
                            <option value="<?php echo esc_attr($per); ?>" <?php selected($periode_filter, $per); ?>><?php echo esc_html($per); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="f_angkatan">
                        <option value="">Semua Angkatan</option>
                        <?php foreach (array_keys($all_angkatans) as $ang) : ?>
                            <option value="<?php echo esc_attr($ang); ?>" <?php selected($angkatan_filter, $ang); ?>>Angkatan <?php echo esc_html($ang); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="search" name="f_q" value="<?php echo esc_attr($search_kw); ?>" placeholder="Cari NIM, nama, saran..." style="min-width:210px;">
                    <button type="submit" class="button">Filter</button>
                    <?php if ($periode_filter || $angkatan_filter || $search_kw) : ?>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=ppb-survei-manager')); ?>" class="button">Reset</a>
                    <?php endif; ?>
                </form>
            </div>

            <?php if (empty($visible_subs)) : ?>
                <div style="padding:26px;background:#f8fafc;border:1px dashed #cbd5e1;border-radius:10px;text-align:center;color:#64748b;">
                    Belum ada data respon survei mahasiswa yang tersimpan untuk filter ini. Kunjungi <a href="<?php echo esc_url(home_url('/survei/')); ?>" target="_blank">/survei/</a> untuk menguji pengisian instrumen GPM.
                </div>
            <?php else : ?>
                <div style="overflow-x:auto;">
                    <table class="widefat striped" style="border-radius:8px;overflow:hidden;">
                        <thead>
                            <tr>
                                <th>Tiket &amp; Waktu</th>
                                <th>Responden (NIM / Nama)</th>
                                <th>Prodi &amp; Angkatan</th>
                                <th>Skor (Q1–Q11)</th>
                                <th>Rata-Rata</th>
                                <th>Saran Perbaikan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($visible_subs as $idx => $s) :
                                $answers = isset($s['answers']) && is_array($s['answers']) ? $s['answers'] : array();
                                $avg = (float) ($s['avg_score'] ?? 0);
                                $badge_bg = $avg >= 3.25 ? '#dcfce7' : ($avg >= 2.5 ? '#fef3c7' : '#fee2e2');
                                $badge_fg = $avg >= 3.25 ? '#166534' : ($avg >= 2.5 ? '#92400e' : '#991b1b');
                            ?>
                            <tr>
                                <td>
                                    <strong style="font-family:monospace;color:#0D3B22;"><?php echo esc_html($s['ticket'] ?? '-'); ?></strong><br>
                                    <span style="font-size:11px;color:#64748b;"><?php echo esc_html($s['submitted_at'] ?? ''); ?></span><br>
                                    <span style="font-size:11px;background:#E8F0EA;color:#0D3B22;padding:1px 6px;border-radius:4px;font-weight:600;"><?php echo esc_html($s['periode'] ?? '-'); ?></span>
                                </td>
                                <td>
                                    <strong><?php echo esc_html($s['nama'] ?? '-'); ?></strong><br>
                                    <span style="font-family:monospace;font-size:12px;color:#475569;">NIM: <?php echo esc_html($s['nim'] ?? '-'); ?></span>
                                </td>
                                <td>
                                    <span style="font-size:12px;font-weight:600;"><?php echo esc_html($s['prodi'] ?? 'S1 BK'); ?></span><br>
                                    <span style="font-size:11.5px;color:#64748b;">Angkatan <?php echo esc_html($s['angkatan'] ?? '-'); ?></span>
                                </td>
                                <td>
                                    <div style="display:flex;flex-wrap:wrap;gap:4px;max-width:260px;">
                                        <?php foreach ($questions as $qk => $qmeta) :
                                            $qv = isset($answers[$qk]) ? (int) $answers[$qk] : 0;
                                        ?>
                                            <span title="<?php echo esc_attr('Q' . $qmeta['no'] . ': ' . $qmeta['text']); ?>" style="font-size:10.5px;font-family:monospace;background:#f1f5f9;padding:2px 5px;border-radius:4px;border:1px solid #e2e8f0;">
                                                Q<?php echo (int) $qmeta['no']; ?>:<strong><?php echo $qv ?: '-'; ?></strong>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td>
                                    <span style="display:inline-block;padding:4px 10px;border-radius:8px;font-weight:800;font-size:13px;background:<?php echo esc_attr($badge_bg); ?>;color:<?php echo esc_attr($badge_fg); ?>;">
                                        <?php echo esc_html(number_format($avg, 2)); ?> / 4
                                    </span>
                                </td>
                                <td style="max-width:260px;font-size:12.5px;color:#334155;">
                                    <?php echo !empty($s['saran']) ? esc_html($s['saran']) : '<em style="color:#94a3b8;">(Tidak ada saran tertulis)</em>'; ?>
                                </td>
                                <td>
                                    <form method="post" onsubmit="return confirm('Hapus respon survei ini secara permanen?');">
                                        <?php wp_nonce_field('ppb_survei_admin_action', 'ppb_survei_nonce'); ?>
                                        <input type="hidden" name="ppb_delete_survei_ticket" value="<?php echo esc_attr($s['ticket'] ?? ''); ?>">
                                        <button type="submit" class="button button-small" style="color:#b91c1c;border-color:#fecaca;">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}
}

/* ========================================================================== */
/* 5. FRONTEND FORM BUILDER & LIVE SYNC FILTER HALAMAN /survei/               */
/* ========================================================================== */

if (!function_exists('ppb_render_survei_form_bundle_html')) {
function ppb_render_survei_form_bundle_html() {
    $settings  = ppb_get_survei_settings();
    $questions = ppb_get_survei_questions();
    $ajax_url  = function_exists('admin_url') ? admin_url('admin-ajax.php') : '/wp-admin/admin-ajax.php';
    $nonce     = function_exists('wp_create_nonce') ? wp_create_nonce('ppb_survei_submit_nonce') : '';
    $is_open   = ($settings['is_open'] ?? '1') === '1';

    $sections = array(
        2 => array('kicker' => 'Bagian I', 'title' => 'Kepuasan terhadap Layanan Dosen dan Tenaga Kependidikan', 'desc' => 'Evaluasi keandalan, daya tanggap, kepastian, dan empati dosen serta staf administrasi jurusan.', 'keys' => array('q1', 'q2', 'q3', 'q4')),
        3 => array('kicker' => 'Bagian II', 'title' => 'Kepuasan terhadap Layanan Kemahasiswaan &amp; Lab BK', 'desc' => 'Evaluasi fasilitas pengembangan minat bakat, beasiswa, pendampingan lomba, dan konseling sebaya.', 'keys' => array('q5', 'q6', 'q7')),
        4 => array('kicker' => 'Bagian III', 'title' => 'Kepuasan terhadap Tata Pamong &amp; Tata Kelola Akademik', 'desc' => 'Evaluasi sistem KRS, penjadwalan ujian, keterbukaan informasi kurikulum, dan pengurusan yudisium.', 'keys' => array('q8', 'q9')),
        5 => array('kicker' => 'Bagian IV &amp; Masukan', 'title' => 'Sarana Prasarana &amp; Rekomendasi Peningkatan Mutu', 'desc' => 'Evaluasi kondisi ruang kuliah, wifi, fasilitas laboratorium BK, dan saran konstruktif Anda.', 'keys' => array('q10', 'q11')),
    );

    $scales = array(
        1 => '1 (STP)',
        2 => '2 (TP)',
        3 => '3 (P)',
        4 => '4 (SP)',
    );

    ob_start();
    ?>
    <style>
        .ppb-srv-scale-opt { display:flex;align-items:center;justify-content:center;gap:6px;padding:10px 8px;border-radius:10px;background:#fff;border:1.5px solid #CBD5E1;font-size:12px;font-weight:600;cursor:pointer;transition:all 0.15s ease;user-select:none; }
        .ppb-srv-scale-opt input { accent-color:#0D3B22;margin:0; }
        .ppb-srv-scale-opt.is-selected { background:#E8F0EA;border-color:#0D3B22;color:#0D3B22;font-weight:700; }
        .ppb-srv-input { width:100%;box-sizing:border-box;padding:10px 13px;border:1px solid #CBD5E1;border-radius:10px;font-size:13.5px;background:#fff;color:#1F2937; }
        .ppb-srv-input:focus { border-color:#0D3B22;outline:none;box-shadow:0 0 0 3px rgba(13,59,34,0.12); }
        @media (max-width: 640px) { .ppb-srv-steps-grid { grid-template-columns:repeat(2, 1fr) !important; } .ppb-srv-scale-grid { grid-template-columns:repeat(2, 1fr) !important; } }
    </style>

    <!-- PROGRESS & STEPPER WIZARD -->
    <div id="ppbSurveyStepperWrap" style="background:#ffffff;border:1px solid #E5E7EB;border-radius:24px;padding:24px 28px;margin-bottom:24px;">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:10px;">
            <span style="display:inline-flex;align-items:center;gap:6px;padding:4px 12px;border-radius:999px;background:#E8F0EA;color:#0D3B22;font-size:11.5px;font-weight:700;">
                🗓️ Periode: <?php echo esc_html($settings['periode_aktif']); ?>
            </span>
            <span style="font-size:12px;color:#4B5563;"><?php echo esc_html($settings['judul_pengantar']); ?></span>
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
            <span id="stepCounterText" style="font-size:12px;font-weight:700;color:#0D3B22;text-transform:uppercase;letter-spacing:0.06em">Langkah 1 dari 5: Identitas Responden</span>
            <span id="stepPercentText" style="font-size:12px;font-weight:700;color:#059669">20% Selesai</span>
        </div>
        <div style="width:100%;height:8px;background:#E5E7EB;border-radius:9999px;overflow:hidden;margin-bottom:18px">
            <div id="surveyProgressBar" style="width:20%;height:100%;background:linear-gradient(90deg, #0D3B22, #10B981);transition:width 0.25s ease;"></div>
        </div>
        <div class="ppb-srv-steps-grid" style="display:grid;grid-template-columns:repeat(5, 1fr);gap:8px">
            <button type="button" class="survey-step-btn active" data-step="1" id="btnStep1" style="padding:8px 10px;border-radius:10px;border:none;background:#0D3B22;color:#F5B82E;font-size:11px;font-weight:700;cursor:pointer;white-space:nowrap;text-align:center">1. Identitas</button>
            <button type="button" class="survey-step-btn" data-step="2" id="btnStep2" style="padding:8px 10px;border-radius:10px;border:1px solid #E5E7EB;background:#F9FAFB;color:#6B7280;font-size:11px;font-weight:600;cursor:pointer;white-space:nowrap;text-align:center">2. Dosen &amp; Tendik</button>
            <button type="button" class="survey-step-btn" data-step="3" id="btnStep3" style="padding:8px 10px;border-radius:10px;border:1px solid #E5E7EB;background:#F9FAFB;color:#6B7280;font-size:11px;font-weight:600;cursor:pointer;white-space:nowrap;text-align:center">3. Kemahasiswaan</button>
            <button type="button" class="survey-step-btn" data-step="4" id="btnStep4" style="padding:8px 10px;border-radius:10px;border:1px solid #E5E7EB;background:#F9FAFB;color:#6B7280;font-size:11px;font-weight:600;cursor:pointer;white-space:nowrap;text-align:center">4. Tata Pamong</button>
            <button type="button" class="survey-step-btn" data-step="5" id="btnStep5" style="padding:8px 10px;border-radius:10px;border:1px solid #E5E7EB;background:#F9FAFB;color:#6B7280;font-size:11px;font-weight:600;cursor:pointer;white-space:nowrap;text-align:center">5. Sarpras &amp; Saran</button>
        </div>
    </div>

    <?php if (!$is_open) : ?>
        <div style="background:#FFFBEB;border:1.5px solid #FCD34D;border-radius:20px;padding:28px;text-align:center;color:#92400E;">
            <strong style="display:block;font-size:18px;margin-bottom:6px;">⏳ Pengisian Survei Periode <?php echo esc_html($settings['periode_aktif']); ?> Sedang Ditutup</strong>
            <p style="margin:0;font-size:13.5px;">Admin/GPM sedang memfinalisasi rekapitulasi survei. Silakan kembali saat periode pengisian berikutnya dibuka.</p>
        </div>
    <?php else : ?>
        <div id="surveyFormCard"
             data-ajax-url="<?php echo esc_url($ajax_url); ?>"
             data-nonce="<?php echo esc_attr($nonce); ?>"
             data-periode="<?php echo esc_attr($settings['periode_aktif']); ?>"
             style="background:#ffffff;border:1px solid #E5E7EB;border-radius:24px;padding:clamp(22px, 3vw, 36px)">

            <div id="surveyErrorAlert" style="display:none;background:#FEF2F2;border:1px solid #FECACA;color:#991B1B;border-radius:12px;padding:12px 16px;font-size:13px;font-weight:600;margin-bottom:18px;"></div>

            <!-- STEP 1: IDENTITAS -->
            <div id="surveyStep1" class="survey-step-panel" style="display:block">
                <div style="margin-bottom:22px">
                    <span style="font-size:11px;font-weight:700;text-transform:uppercase;color:#0D3B22;letter-spacing:0.1em;display:block">Bagian A</span>
                    <h3 style="font-family:'Fraunces',serif;font-size:22px;font-weight:700;color:#0D3B22;margin:2px 0 8px">Data Responden &amp; Petunjuk Pengisian</h3>
                    <p style="font-size:13px;color:#4B5563;line-height:1.6;margin:0">
                        Survei ini bertujuan untuk mengukur tingkat kepuasan Anda secara objektif. Data responden dirahasiakan dan hanya digunakan untuk keperluan peningkatan mutu akademik (SPMI &amp; LAMDIK).
                    </p>
                </div>

                <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(250px, 1fr));gap:16px;margin-bottom:22px">
                    <div style="display:flex;flex-direction:column;gap:6px">
                        <label for="respNim" style="font-size:12.5px;font-weight:700;color:#1F2937">Nomor Induk Mahasiswa (NIM) <span style="color:#DC2626">*</span></label>
                        <input type="text" id="respNim" name="nim" class="ppb-srv-input" placeholder="Contoh: 220404501015" required>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:6px">
                        <label for="respNama" style="font-size:12.5px;font-weight:700;color:#1F2937">Nama Lengkap Mahasiswa <span style="color:#DC2626">*</span></label>
                        <input type="text" id="respNama" name="nama" class="ppb-srv-input" placeholder="Masukkan nama lengkap Anda" required>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:6px">
                        <label for="respProdi" style="font-size:12.5px;font-weight:700;color:#1F2937">Program Studi</label>
                        <select id="respProdi" name="prodi" class="ppb-srv-input">
                            <option value="S1 Bimbingan dan Konseling">S1 Bimbingan dan Konseling</option>
                            <option value="S2 Bimbingan dan Konseling">S2 Bimbingan dan Konseling (Magister)</option>
                            <option value="S3 Ilmu Pendidikan (BK)">S3 Bimbingan dan Konseling (Doktoral)</option>
                        </select>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:6px">
                        <label for="respAngkatan" style="font-size:12.5px;font-weight:700;color:#1F2937">Tahun Angkatan <span style="color:#DC2626">*</span></label>
                        <select id="respAngkatan" name="angkatan" class="ppb-srv-input" required>
                            <option value="2026">Angkatan 2026</option>
                            <option value="2025">Angkatan 2025</option>
                            <option value="2024">Angkatan 2024</option>
                            <option value="2023" selected>Angkatan 2023</option>
                            <option value="2022">Angkatan 2022</option>
                            <option value="2021 / Lainnya">Angkatan 2021 / Lainnya</option>
                        </select>
                    </div>
                </div>

                <div style="background:#FAF8F5;border:1px solid #E5E7EB;border-radius:16px;padding:18px;margin-bottom:24px">
                    <h5 style="font-family:'Fraunces',serif;font-size:14px;font-weight:700;color:#0D3B22;margin:0 0 8px">Petunjuk Skala Penilaian:</h5>
                    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:8px;font-size:12px">
                        <div style="padding:6px 10px;border-radius:8px;background:#FEE2E2;color:#991B1B;font-weight:600">1 = Sangat Tidak Puas (STP)</div>
                        <div style="padding:6px 10px;border-radius:8px;background:#FEF3C7;color:#92400E;font-weight:600">2 = Tidak Puas (TP)</div>
                        <div style="padding:6px 10px;border-radius:8px;background:#E0F2FE;color:#0369A1;font-weight:600">3 = Puas (P)</div>
                        <div style="padding:6px 10px;border-radius:8px;background:#D1FAE5;color:#065F46;font-weight:600">4 = Sangat Puas (SP)</div>
                    </div>
                </div>
            </div>

            <!-- STEPS 2-5 -->
            <?php foreach ($sections as $step_num => $sec) : ?>
                <div id="surveyStep<?php echo (int) $step_num; ?>" class="survey-step-panel" style="display:none">
                    <div style="margin-bottom:22px">
                        <span style="font-size:11px;font-weight:700;text-transform:uppercase;color:#0D3B22;letter-spacing:0.1em;display:block"><?php echo esc_html($sec['kicker']); ?></span>
                        <h3 style="font-family:'Fraunces',serif;font-size:22px;font-weight:700;color:#0D3B22;margin:2px 0 6px"><?php echo wp_kses_post($sec['title']); ?></h3>
                        <p style="font-size:13px;color:#4B5563;margin:0"><?php echo esc_html($sec['desc']); ?></p>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:18px;margin-bottom:24px">
                        <?php foreach ($sec['keys'] as $qk) :
                            $qm = $questions[$qk];
                        ?>
                            <div class="ppb-srv-qbox" data-q="<?php echo esc_attr($qk); ?>" style="padding:16px;border-radius:14px;border:1px solid #E5E7EB;background:#FAF8F5">
                                <p style="font-size:13px;font-weight:600;color:#1F2937;margin:0 0 12px"><?php echo (int) $qm['no']; ?>. <?php echo esc_html($qm['text']); ?> <span style="color:#DC2626">*</span></p>
                                <div class="ppb-srv-scale-grid" style="display:grid;grid-template-columns:repeat(4, 1fr);gap:8px">
                                    <?php foreach ($scales as $sval => $slabel) : ?>
                                        <label class="ppb-srv-scale-opt">
                                            <input type="radio" name="<?php echo esc_attr($qk); ?>" value="<?php echo (int) $sval; ?>">
                                            <span><?php echo esc_html($slabel); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php if ($step_num === 5) : ?>
                            <div style="display:flex;flex-direction:column;gap:8px">
                                <label for="respSaran" style="font-size:13px;font-weight:700;color:#0D3B22">Saran &amp; Rekomendasi Perbaikan Mutu Prodi BK (Opsional):</label>
                                <textarea id="respSaran" name="saran" rows="4" class="ppb-srv-input" placeholder="Tuliskan masukan konstruktif untuk layanan perkuliahan, lab, atau administrasi prodi..." style="line-height:1.6"></textarea>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- NAVIGATION BUTTONS -->
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;padding-top:20px;border-top:1px solid #E5E7EB">
                <button type="button" id="surveyPrevBtn" style="display:none;padding:10px 20px;border-radius:12px;border:1px solid #CBD5E1;background:#F8FAFC;color:#334155;font-size:13px;font-weight:600;cursor:pointer">&larr; Langkah Sebelumnya</button>
                <div style="margin-left:auto;display:flex;gap:10px;align-items:center;">
                    <?php if (!empty($settings['backup_form_url'])) : ?>
                        <a href="<?php echo esc_url($settings['backup_form_url']); ?>" target="_blank" rel="noopener noreferrer" style="font-size:12px;color:#0D3B22;font-weight:600;text-decoration:underline;">Alternatif Google Form &#8599;</a>
                    <?php endif; ?>
                    <button type="button" id="surveyNextBtn" style="padding:11px 24px;border-radius:12px;border:none;background:#0D3B22;color:#F5B82E;font-size:13px;font-weight:700;cursor:pointer">Lanjutkan Langkah Berikutnya &rarr;</button>
                    <button type="button" id="surveySubmitBtn" style="display:none;padding:11px 26px;border-radius:12px;border:none;background:#059669;color:#ffffff;font-size:13px;font-weight:700;cursor:pointer">&#10003; Kirim Survei Kepuasan</button>
                </div>
            </div>
        </div>

        <!-- COMPLETION SUCCESS SCREEN -->
        <div id="surveySuccessCard" style="display:none;background:#ffffff;border:1px solid #A7F3D0;border-radius:24px;padding:45px 36px;text-align:center">
            <div style="width:68px;height:68px;border-radius:50%;background:#D1FAE5;color:#059669;font-size:32px;display:flex;align-items:center;justify-content:center;margin:0 auto 18px">&#10003;</div>
            <span id="surveySuccessTicketBadge" style="display:inline-block;padding:4px 12px;border-radius:999px;background:#E8F0EA;color:#0D3B22;font-family:monospace;font-size:12px;font-weight:700;margin-bottom:10px;">TERCATAT DI DATABASE GPM</span>
            <h3 style="font-family:'Fraunces',serif;font-size:24px;font-weight:700;color:#0D3B22;margin:0 0 10px">Terima Kasih atas Partisipasi Anda!</h3>
            <p id="surveySuccessMsg" style="font-size:14px;color:#4B5563;max-width:620px;margin:0 auto 24px;line-height:1.65">
                Data survei kepuasan Anda telah berhasil tercatat pada sistem penjaminan mutu Gugus Penjaminan Mutu (GPM) FIP UNM. Masukan Anda sangat berharga untuk akreditasi LAMDIK dan pembenahan fasilitas akademik.
            </p>
            <div style="display:inline-flex;gap:12px;flex-wrap:wrap;justify-content:center">
                <a href="<?php echo esc_url(home_url('/layanan/')); ?>" style="padding:10px 20px;border-radius:12px;background:#0D3B22;color:#F5B82E;font-size:13px;font-weight:700;text-decoration:none">Kembali ke Pusat Layanan</a>
                <button type="button" id="surveyResetBtn" style="padding:10px 20px;border-radius:12px;background:#F1F5F9;color:#334155;font-size:13px;font-weight:600;border:1px solid #CBD5E1;cursor:pointer">Isi Ulang Formulir</button>
            </div>
        </div>
    <?php endif; ?>

    <script>
    (function(){
        var card = document.getElementById('surveyFormCard');
        if (!card || card.getAttribute('data-ppb-wired') === '1') return;
        card.setAttribute('data-ppb-wired', '1');

        var step = 1, total = 5;
        var titles = [
            'Langkah 1 dari 5: Identitas Responden',
            'Langkah 2 dari 5: Layanan Dosen & Tenaga Kependidikan',
            'Langkah 3 dari 5: Layanan Kemahasiswaan & Lab BK',
            'Langkah 4 dari 5: Tata Pamong & Tata Kelola',
            'Langkah 5 dari 5: Sarana Prasarana & Saran Perbaikan'
        ];
        var errBox = document.getElementById('surveyErrorAlert');

        function showErr(msg) {
            if (!errBox) return;
            if (!msg) { errBox.style.display = 'none'; errBox.textContent = ''; return; }
            errBox.textContent = msg;
            errBox.style.display = 'block';
            errBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function setStep(n) {
            if (n < 1 || n > total) return;
            showErr('');
            step = n;
            for (var i = 1; i <= total; i++) {
                var p = document.getElementById('surveyStep' + i);
                var b = document.getElementById('btnStep' + i);
                if (p) p.style.display = (i === step) ? 'block' : 'none';
                if (b) {
                    b.style.background = (i === step) ? '#0D3B22' : '#F9FAFB';
                    b.style.color = (i === step) ? '#F5B82E' : '#6B7280';
                    b.style.border = (i === step) ? 'none' : '1px solid #E5E7EB';
                }
            }
            var c = document.getElementById('stepCounterText');
            var pc = document.getElementById('stepPercentText');
            var bar = document.getElementById('surveyProgressBar');
            var pct = Math.round((step / total) * 100);
            if (c) c.textContent = titles[step - 1];
            if (pc) pc.textContent = pct + '% Selesai';
            if (bar) bar.style.width = pct + '%';

            var prev = document.getElementById('surveyPrevBtn');
            var next = document.getElementById('surveyNextBtn');
            var sub  = document.getElementById('surveySubmitBtn');
            if (prev) prev.style.display = (step === 1) ? 'none' : 'inline-flex';
            if (next) next.style.display = (step === total) ? 'none' : 'inline-flex';
            if (sub)  sub.style.display  = (step === total) ? 'inline-flex' : 'none';
        }

        function validateStep(n) {
            if (n === 1) {
                var nim = (document.getElementById('respNim') ? document.getElementById('respNim').value : '').trim();
                var nama = (document.getElementById('respNama') ? document.getElementById('respNama').value : '').trim();
                if (!nim || !nama) {
                    showErr('Mohon isi Nomor Induk Mahasiswa (NIM) dan Nama Lengkap sebelum melanjutkan.');
                    return false;
                }
            } else {
                var panel = document.getElementById('surveyStep' + n);
                if (panel) {
                    var qboxes = panel.querySelectorAll('.ppb-srv-qbox');
                    for (var i = 0; i < qboxes.length; i++) {
                        var qk = qboxes[i].getAttribute('data-q');
                        if (!panel.querySelector('input[name="' + qk + '"]:checked')) {
                            showErr('Mohon pilih skala penilaian (1-4) untuk seluruh pertanyaan pada bagian ini.');
                            return false;
                        }
                    }
                }
            }
            return true;
        }

        card.addEventListener('change', function(e){
            if (e.target && e.target.type === 'radio') {
                var grp = e.target.closest('.ppb-srv-scale-grid');
                if (grp) {
                    var opts = grp.querySelectorAll('.ppb-srv-scale-opt');
                    for (var i = 0; i < opts.length; i++) opts[i].classList.remove('is-selected');
                    var lbl = e.target.closest('.ppb-srv-scale-opt');
                    if (lbl) lbl.classList.add('is-selected');
                }
                showErr('');
            }
        });

        var stepBtns = document.querySelectorAll('#ppbSurveyStepperWrap .survey-step-btn');
        for (var j = 0; j < stepBtns.length; j++) {
            stepBtns[j].addEventListener('click', function(){
                var target = parseInt(this.getAttribute('data-step'), 10);
                if (target <= step || validateStep(step)) setStep(target);
            });
        }

        var prevBtn = document.getElementById('surveyPrevBtn');
        var nextBtn = document.getElementById('surveyNextBtn');
        var subBtn  = document.getElementById('surveySubmitBtn');
        var rstBtn  = document.getElementById('surveyResetBtn');

        if (prevBtn) prevBtn.addEventListener('click', function(){ setStep(step - 1); });
        if (nextBtn) nextBtn.addEventListener('click', function(){ if (validateStep(step)) setStep(step + 1); });

        function doSubmit() {
            for (var s = 1; s <= total; s++) {
                if (!validateStep(s)) { setStep(s); return; }
            }
            var ajaxUrl = card.getAttribute('data-ajax-url') || '';
            var nonce   = card.getAttribute('data-nonce') || '';
            var fd = new FormData();
            fd.append('action', 'ppb_submit_survey');
            fd.append('nonce', nonce);
            fd.append('nim', (document.getElementById('respNim') ? document.getElementById('respNim').value : '').trim());
            fd.append('nama', (document.getElementById('respNama') ? document.getElementById('respNama').value : '').trim());
            fd.append('prodi', document.getElementById('respProdi') ? document.getElementById('respProdi').value : '');
            fd.append('angkatan', document.getElementById('respAngkatan') ? document.getElementById('respAngkatan').value : '');
            fd.append('saran', document.getElementById('respSaran') ? document.getElementById('respSaran').value : '');
            for (var q = 1; q <= 11; q++) {
                var chk = card.querySelector('input[name="q' + q + '"]:checked');
                if (chk) fd.append('q' + q, chk.value);
            }

            if (subBtn) { subBtn.disabled = true; subBtn.textContent = 'Menyimpan Data Survei...'; }

            fetch(ajaxUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(function(r){ return r.json(); })
                .then(function(res){
                    if (subBtn) { subBtn.disabled = false; subBtn.innerHTML = '&#10003; Kirim Survei Kepuasan'; }
                    if (res && res.success && res.data) {
                        card.style.display = 'none';
                        var sc = document.getElementById('surveySuccessCard');
                        var bd = document.getElementById('surveySuccessTicketBadge');
                        var ms = document.getElementById('surveySuccessMsg');
                        if (bd) bd.textContent = 'TIKET: ' + res.data.ticket + ' · SKOR RATA: ' + res.data.avg_score + '/4.00 · ' + res.data.periode;
                        if (ms) ms.textContent = 'Terima kasih ' + res.data.nama + ' (NIM ' + res.data.nim + '). Data evaluasi Anda telah tersimpan permanen di sistem GPM FIP UNM dan siap digunakan untuk audit mutu.';
                        if (sc) {
                            sc.style.display = 'block';
                            sc.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }
                    } else {
                        showErr((res && res.data && res.data.message) ? res.data.message : 'Gagal menyimpan survei. Silakan periksa kembali isian Anda.');
                    }
                })
                .catch(function(){
                    if (subBtn) { subBtn.disabled = false; subBtn.innerHTML = '&#10003; Kirim Survei Kepuasan'; }
                    showErr('Terjadi gangguan koneksi saat mengirim data survei. Silakan coba lagi.');
                });
        }

        if (subBtn) subBtn.addEventListener('click', doSubmit);
        window.ppbSubmitSurveyForm = doSubmit;

        if (rstBtn) {
            rstBtn.addEventListener('click', function(){
                var sc = document.getElementById('surveySuccessCard');
                if (sc) sc.style.display = 'none';
                var inputs = card.querySelectorAll('input[type="radio"]:checked');
                for (var i = 0; i < inputs.length; i++) inputs[i].checked = false;
                var opts = card.querySelectorAll('.ppb-srv-scale-opt.is-selected');
                for (var k = 0; k < opts.length; k++) opts[k].classList.remove('is-selected');
                if (document.getElementById('respSaran')) document.getElementById('respSaran').value = '';
                card.style.display = 'block';
                setStep(1);
            });
        }
    })();
    </script>
    <?php
    return ob_get_clean();
}
}

if (!function_exists('ppb_shortcode_survei_form')) {
function ppb_shortcode_survei_form($atts = array()) {
    return ppb_render_survei_form_bundle_html();
}
}
add_shortcode('ppb_survei_form', 'ppb_shortcode_survei_form');

if (!function_exists('ppb_survei_sync_content')) {
function ppb_survei_sync_content($content) {
    if (!is_string($content) || $content === '' || (function_exists('is_admin') && is_admin())) {
        return $content;
    }

    $slug_path = function_exists('ppb_get_current_page_slug_path') ? ppb_get_current_page_slug_path() : '';
    $is_survei = ($slug_path === 'survei' || $slug_path === 'layanan/survei' || strpos($content, 'id="surveyFormCard"') !== false);
    if (!$is_survei) {
        return $content;
    }

    // Hindari double-render jika sudah dirender oleh shortcode
    if (strpos($content, 'id="ppbSurveyStepperWrap"') !== false) {
        return $content;
    }

    // 1. Hapus wrapper stepper lama jika ada (mengandung #stepCounterText)
    $step_marker = strpos($content, 'id="stepCounterText"');
    if ($step_marker !== false && function_exists('ppb_replace_balanced_div_at')) {
        $before = substr($content, 0, $step_marker);
        // Card terluar stepper selalu berada dua <div di atas span#stepCounterText
        $first_open = strrpos($before, '<div');
        if ($first_open !== false) {
            $outer_open = strrpos(substr($before, 0, $first_open), '<div');
            if ($outer_open !== false) {
                $content = ppb_replace_balanced_div_at($content, $outer_open, '');
            }
        }
    }

    // 2. Hapus success card statis lama (akan digantikan dalam bundle lengkap)
    $succ_pos = strpos($content, 'id="surveySuccessCard"');
    if ($succ_pos !== false && function_exists('ppb_replace_balanced_div_at')) {
        $div_start = strrpos(substr($content, 0, $succ_pos), '<div');
        if ($div_start !== false) {
            $content = ppb_replace_balanced_div_at($content, $div_start, '');
        }
    }

    // 3. Ganti #surveyFormCard dengan form & stepper fungsional lengkap (inputs, radios, AJAX persistence)
    $form_pos = strpos($content, 'id="surveyFormCard"');
    if ($form_pos !== false && function_exists('ppb_replace_balanced_div_at')) {
        $div_start = strrpos(substr($content, 0, $form_pos), '<div');
        if ($div_start !== false) {
            $content = ppb_replace_balanced_div_at($content, $div_start, ppb_render_survei_form_bundle_html());
        }
    }

    return $content;
}
}
add_filter('the_content', 'ppb_survei_sync_content', 12);
