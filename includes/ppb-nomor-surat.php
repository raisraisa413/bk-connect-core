<?php
/**
 * Module: PPB FIP UNM - Permintaan & Registrasi Nomor Surat Resmi
 * Description: Pengajuan nomor surat (auto & approval admin), permintaan massal khusus dosen,
 *              kode tracking, notifikasi email, buku registrasi admin, dan export Excel (.xlsx/.csv).
 */

if (!defined('ABSPATH')) {
    exit;
}

define('PPB_SURAT_DB_VERSION', '1.0.2');

/* ========================================================================== */
/* 1. DATABASE TABLE & DEFAULTS                                               */
/* ========================================================================== */

if (!function_exists('ppb_surat_table_name')) {
function ppb_surat_table_name() {
    global $wpdb;
    return $wpdb->prefix . 'ppb_nomor_surat';
}
}

if (!function_exists('ppb_surat_maybe_install_table')) {
function ppb_surat_maybe_install_table() {
    if (get_option('ppb_surat_db_version') === PPB_SURAT_DB_VERSION) {
        return;
    }
    global $wpdb;
    $table = ppb_surat_table_name();
    $charset = $wpdb->get_charset_collate();

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $sql = "CREATE TABLE {$table} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        tracking_code VARCHAR(32) NOT NULL,
        batch_index SMALLINT UNSIGNED NOT NULL DEFAULT 1,
        batch_total SMALLINT UNSIGNED NOT NULL DEFAULT 1,
        jenis_surat_id VARCHAR(64) NOT NULL,
        jenis_surat_nama VARCHAR(191) NOT NULL,
        kode_hal VARCHAR(16) NOT NULL,
        mode_penerbitan VARCHAR(20) NOT NULL DEFAULT 'approval',
        pemohon_peran VARCHAR(20) NOT NULL DEFAULT 'mahasiswa',
        pemohon_nama VARCHAR(191) NOT NULL,
        pemohon_identitas VARCHAR(64) NOT NULL,
        pemohon_email VARCHAR(191) NOT NULL,
        pemohon_wa VARCHAR(32) NOT NULL DEFAULT '',
        unit_jenjang VARCHAR(32) NOT NULL DEFAULT 's1',
        perihal_tujuan TEXT NOT NULL,
        link_berkas TEXT NULL,
        link_arsip_final TEXT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'pending',
        nomor_urut INT UNSIGNED NULL,
        tahun SMALLINT UNSIGNED NOT NULL,
        nomor_surat VARCHAR(100) NULL,
        catatan_admin TEXT NULL,
        audit_log TEXT NULL,
        processed_by BIGINT UNSIGNED NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        PRIMARY KEY (id),
        KEY idx_tracking (tracking_code),
        KEY idx_status_tahun (status, tahun),
        UNIQUE KEY uniq_tahun_urut (tahun, nomor_urut),
        UNIQUE KEY uniq_nomor_surat (nomor_surat),
        KEY idx_identitas (pemohon_identitas)
    ) {$charset};";

    dbDelta($sql);
    update_option('ppb_surat_db_version', PPB_SURAT_DB_VERSION, false);
}
}
add_action('init', 'ppb_surat_maybe_install_table', 5);

if (!function_exists('ppb_surat_get_settings')) {
function ppb_surat_get_settings() {
    $defaults = [
        'prefix'             => 'B',
        'kode_unit'          => 'UN36.1',
        'pad_digits'         => 3,
        'max_batch_dosen'    => 50,
        'validate_dosen_nip' => 1,
        'admin_notify_email' => get_option('admin_email', ''),
        'offsets'            => [],
    ];
    $saved = get_option('ppb_surat_settings', []);
    if (!is_array($saved)) {
        $saved = [];
    }
    $merged = array_merge($defaults, $saved);
    $merged['pad_digits'] = max(1, min(6, (int) $merged['pad_digits']));
    $merged['max_batch_dosen'] = max(2, min(200, (int) $merged['max_batch_dosen']));
    $merged['validate_dosen_nip'] = !empty($merged['validate_dosen_nip']) ? 1 : 0;
    if (!is_array($merged['offsets'])) {
        $merged['offsets'] = [];
    }
    return $merged;
}
}

if (!function_exists('ppb_surat_get_default_jenis_list')) {
function ppb_surat_get_default_jenis_list() {
    return [
        [
            'id'          => 'aktif_kuliah',
            'nama'        => 'Surat Keterangan Aktif Kuliah',
            'kode_hal'    => 'PP',
            'mode'        => 'auto',
            'berkas_mode' => 'optional',
            'deskripsi'   => 'Untuk pengurusan tunjangan gaji orang tua, BPJS Kesehatan, dan pengajuan beasiswa.',
            'active'      => 1,
        ],
        [
            'id'          => 'izin_penelitian',
            'nama'        => 'Surat Izin Penelitian & Observasi Skripsi',
            'kode_hal'    => 'PT',
            'mode'        => 'approval',
            'berkas_mode' => 'required',
            'deskripsi'   => 'Permohonan izin riset atau pengambilan data di sekolah mitra, instansi, atau komunitas.',
            'active'      => 1,
        ],
        [
            'id'          => 'pengantar_magang',
            'nama'        => 'Surat Pengantar Magang / MBKM',
            'kode_hal'    => 'KM',
            'mode'        => 'approval',
            'berkas_mode' => 'required',
            'deskripsi'   => 'Pengantar resmi pelaksanaan PPL, Magang Industri, atau Kampus Mengajar.',
            'active'      => 1,
        ],
        [
            'id'          => 'bebas_lab',
            'nama'        => 'Surat Bebas Laboratorium & Inventaris',
            'kode_hal'    => 'LL',
            'mode'        => 'approval',
            'berkas_mode' => 'optional',
            'deskripsi'   => 'Syarat yudisium dan bebas pinjaman instrumen tes psikologi/alat asesmen Lab BK.',
            'active'      => 1,
        ],
    ];
}
}

if (!function_exists('ppb_surat_get_jenis_list')) {
function ppb_surat_get_jenis_list($active_only = false) {
    $saved = get_option('ppb_surat_jenis_list', null);
    if (!is_array($saved) || empty($saved)) {
        $saved = ppb_surat_get_default_jenis_list();
        update_option('ppb_surat_jenis_list', $saved, false);
    }
    if (!$active_only) {
        return array_values($saved);
    }
    return array_values(array_filter($saved, function ($item) {
        return !empty($item['active']);
    }));
}
}

if (!function_exists('ppb_surat_find_jenis')) {
function ppb_surat_find_jenis($jenis_id, $active_only = true) {
    $list = ppb_surat_get_jenis_list($active_only);
    foreach ($list as $item) {
        if ((string) $item['id'] === (string) $jenis_id) {
            return $item;
        }
    }
    return null;
}
}

/* ========================================================================== */
/* 2. NUMBERING, TRACKING & DOSEN NIP HELPERS                                 */
/* ========================================================================== */

if (!function_exists('ppb_surat_format_number')) {
function ppb_surat_format_number($seq, $kode_hal, $year, $settings = null) {
    if ($settings === null) {
        $settings = ppb_surat_get_settings();
    }
    $prefix    = trim((string) ($settings['prefix'] ?: 'B'));
    $kode_unit = trim((string) ($settings['kode_unit'] ?: 'UN36.1'));
    $pad       = max(1, (int) ($settings['pad_digits'] ?: 3));
    $urut_str  = str_pad((string) ((int) $seq), $pad, '0', STR_PAD_LEFT);
    $hal_clean = strtoupper(preg_replace('/[^A-Za-z0-9.\-]/', '', (string) $kode_hal)) ?: 'PP';

    return sprintf('%s/%s/%s/%s/%d', $prefix, $urut_str, $kode_unit, $hal_clean, (int) $year);
}
}

if (!function_exists('ppb_surat_get_current_last_seq')) {
function ppb_surat_get_current_last_seq($year) {
    global $wpdb;
    $table    = ppb_surat_table_name();
    $settings = ppb_surat_get_settings();
    $year     = (int) $year;

    $max_db      = (int) $wpdb->get_var($wpdb->prepare("SELECT COALESCE(MAX(nomor_urut), 0) FROM {$table} WHERE tahun = %d AND nomor_urut IS NOT NULL", $year));
    $opt_counter = (int) get_option('ppb_surat_counter_' . $year, 0);
    $offset      = isset($settings['offsets'][(string) $year]) ? (int) $settings['offsets'][(string) $year] : 0;

    return max($max_db, $opt_counter, $offset);
}
}

if (!function_exists('ppb_surat_number_exists')) {
function ppb_surat_number_exists($year, $seq, $nomor_surat) {
    global $wpdb;
    $table = ppb_surat_table_name();
    $found = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$table} WHERE (tahun = %d AND nomor_urut = %d) OR nomor_surat = %s",
        (int) $year,
        (int) $seq,
        (string) $nomor_surat
    ));
    return $found > 0;
}
}

if (!function_exists('ppb_surat_with_year_lock')) {
function ppb_surat_with_year_lock($year, callable $callback) {
    global $wpdb;
    $year = (int) $year;
    $lock = 'ppb_surat_seq_' . $year;

    $acquired = $wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, 5)', $lock));
    if ((string) $acquired !== '1') {
        return new WP_Error('lock_failed', 'Sistem sedang sibuk memproses penomoran lain. Silakan coba beberapa detik lagi.');
    }

    try {
        return call_user_func($callback);
    } finally {
        $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)', $lock));
    }
}
}

if (!function_exists('ppb_surat_allocate_numbers_locked')) {
function ppb_surat_allocate_numbers_locked($year, $count = 1, $kode_hal = 'PP') {
    $year      = (int) $year;
    $count     = max(1, (int) $count);
    $settings  = ppb_surat_get_settings();
    $candidate = ppb_surat_get_current_last_seq($year);
    $allocated = [];

    for ($i = 0; $i < $count; $i++) {
        $safety = 0;
        do {
            $candidate++;
            $formatted = ppb_surat_format_number($candidate, $kode_hal, $year, $settings);
            $exists    = ppb_surat_number_exists($year, $candidate, $formatted);
            $safety++;
        } while ($exists && $safety < 500);

        if ($exists) {
            return new WP_Error('seq_exhausted', 'Gagal menemukan nomor urut surat yang tersedia. Silakan coba lagi.');
        }

        $allocated[] = [
            'nomor_urut'  => $candidate,
            'nomor_surat' => $formatted,
        ];
    }

    update_option('ppb_surat_counter_' . $year, $candidate, false);
    return $allocated;
}
}

if (!function_exists('ppb_surat_allocate_numbers')) {
function ppb_surat_allocate_numbers($year, $count = 1, $kode_hal = 'PP') {
    return ppb_surat_with_year_lock($year, function () use ($year, $count, $kode_hal) {
        return ppb_surat_allocate_numbers_locked($year, $count, $kode_hal);
    });
}
}

if (!function_exists('ppb_surat_generate_tracking_code')) {
function ppb_surat_generate_tracking_code($year) {
    global $wpdb;
    $table = ppb_surat_table_name();
    for ($attempt = 0; $attempt < 8; $attempt++) {
        $rand = strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
        $code = sprintf('BK-%d-%s', (int) $year, $rand);
        $exists = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE tracking_code = %s", $code));
        if ($exists === 0) {
            return $code;
        }
    }
    return sprintf('BK-%d-%s', (int) $year, strtoupper(wp_generate_password(6, false, false)));
}
}

if (!function_exists('ppb_surat_normalize_id')) {
function ppb_surat_normalize_id($val) {
    return preg_replace('/[^A-Za-z0-9]/', '', (string) $val);
}
}

if (!function_exists('ppb_surat_lookup_dosen_by_nip')) {
function ppb_surat_lookup_dosen_by_nip($nip_input) {
    $norm = ppb_surat_normalize_id($nip_input);
    if (strlen($norm) < 8) {
        return ['found' => false, 'name' => '', 'nip' => '', 'email' => ''];
    }

    $found_name  = '';
    $found_nip   = '';
    $found_email = '';

    // 1. Cek di bk_dosen_data_cache dan dosen-data.json (memiliki data email dosen)
    $sources = [];
    $cached = get_option('bk_dosen_data_cache', []);
    if (is_array($cached) && !empty($cached)) {
        $sources[] = $cached;
    }
    $json_path = function_exists('get_template_directory') ? (get_template_directory() . '/dosen-data.json') : '';
    if ($json_path !== '' && file_exists($json_path)) {
        $list = json_decode((string) file_get_contents($json_path), true);
        if (is_array($list) && !empty($list)) {
            $sources[] = $list;
        }
    }
    foreach ($sources as $list) {
        foreach ($list as $d) {
            if (!is_array($d)) continue;
            $raw_nip = (string) ($d['nip'] ?? '');
            if ($raw_nip !== '' && ppb_surat_normalize_id($raw_nip) === $norm) {
                $found_nip = $raw_nip;
                if ($found_name === '') {
                    $found_name = (string) ($d['nama'] ?? ($d['nama_lengkap'] ?? ''));
                }
                if ($found_email === '' && !empty($d['email'])) {
                    $found_email = sanitize_email((string) $d['email']);
                }
            }
        }
        if ($found_nip !== '' && $found_email !== '') {
            break;
        }
    }

    // 2. Cek juga di CPT ppb_dosen jika belum ketemu atau untuk melengkapi
    if ($found_nip === '') {
        $q = new WP_Query([
            'post_type'      => 'ppb_dosen',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ]);
        if (!empty($q->posts)) {
            foreach ($q->posts as $did) {
                $raw_nip = (string) get_post_meta($did, '_ppb_dosen_nip', true);
                if ($raw_nip !== '' && ppb_surat_normalize_id($raw_nip) === $norm) {
                    $found_nip  = $raw_nip;
                    $found_name = (string) get_the_title($did);
                    $meta_email = (string) (get_post_meta($did, '_ppb_dosen_email', true) ?: get_post_meta($did, '_dosen_email', true));
                    if ($found_email === '' && $meta_email !== '') {
                        $found_email = sanitize_email($meta_email);
                    }
                    break;
                }
            }
        }
    }

    if ($found_nip !== '') {
        return [
            'found' => true,
            'name'  => $found_name,
            'nip'   => $found_nip,
            'email' => $found_email,
        ];
    }

    return ['found' => false, 'name' => '', 'nip' => '', 'email' => ''];
}
}

if (!function_exists('ppb_surat_get_pending_count')) {
function ppb_surat_get_pending_count() {
    global $wpdb;
    $table = ppb_surat_table_name();
    $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
    if ($exists !== $table) {
        return 0;
    }
    return (int) $wpdb->get_var("SELECT COUNT(DISTINCT tracking_code) FROM {$table} WHERE status = 'pending'");
}
}

if (!function_exists('ppb_surat_append_audit')) {
function ppb_surat_append_audit($existing_log, $entry_text) {
    $user = wp_get_current_user();
    $actor = ($user && !empty($user->user_login)) ? $user->user_login : 'system';
    $line = '[' . current_time('Y-m-d H:i') . ' @' . $actor . '] ' . trim((string) $entry_text);
    $lines = array_filter(array_map('trim', explode("\n", (string) $existing_log)));
    $lines[] = $line;
    if (count($lines) > 15) {
        $lines = array_slice($lines, -15);
    }
    return implode("\n", $lines);
}
}

/* ========================================================================== */
/* 4. FRONTEND AJAX HANDLERS                                                  */
/* ========================================================================== */

if (!function_exists('ppb_ajax_check_dosen_nip')) {
function ppb_ajax_check_dosen_nip() {
    check_ajax_referer('ppb_nomor_surat_nonce', 'nonce');
    $nip = isset($_POST['nip']) ? sanitize_text_field(wp_unslash($_POST['nip'])) : '';
    $res = ppb_surat_lookup_dosen_by_nip($nip);
    wp_send_json_success($res);
}
}
add_action('wp_ajax_ppb_check_dosen_nip', 'ppb_ajax_check_dosen_nip');
add_action('wp_ajax_nopriv_ppb_check_dosen_nip', 'ppb_ajax_check_dosen_nip');

if (!function_exists('ppb_ajax_submit_nomor_surat')) {
function ppb_ajax_submit_nomor_surat() {
    check_ajax_referer('ppb_nomor_surat_nonce', 'nonce');
    ppb_surat_maybe_install_table();

    global $wpdb;
    $table    = ppb_surat_table_name();
    $settings = ppb_surat_get_settings();
    $year     = (int) current_time('Y');
    $now      = current_time('mysql');

    $jenis_id  = isset($_POST['jenis_surat_id']) ? sanitize_key(wp_unslash($_POST['jenis_surat_id'])) : '';
    $peran     = isset($_POST['pemohon_peran']) ? sanitize_key(wp_unslash($_POST['pemohon_peran'])) : 'mahasiswa';
    $nama      = isset($_POST['pemohon_nama']) ? sanitize_text_field(wp_unslash($_POST['pemohon_nama'])) : '';
    $identitas = isset($_POST['pemohon_identitas']) ? sanitize_text_field(wp_unslash($_POST['pemohon_identitas'])) : '';
    $email     = isset($_POST['pemohon_email']) ? sanitize_email(wp_unslash($_POST['pemohon_email'])) : '';
    $wa        = isset($_POST['pemohon_wa']) ? sanitize_text_field(wp_unslash($_POST['pemohon_wa'])) : '';
    $unit      = isset($_POST['unit_jenjang']) ? sanitize_key(wp_unslash($_POST['unit_jenjang'])) : 's1';
    $perihal   = isset($_POST['perihal_tujuan']) ? sanitize_textarea_field(wp_unslash($_POST['perihal_tujuan'])) : '';
    $link      = isset($_POST['link_berkas']) ? esc_url_raw(trim((string) wp_unslash($_POST['link_berkas']))) : '';
    $jumlah    = isset($_POST['jumlah_nomor']) ? (int) $_POST['jumlah_nomor'] : 1;

    if (!in_array($peran, ['mahasiswa', 'dosen'], true)) {
        $peran = 'mahasiswa';
    }

    $jenis = ppb_surat_find_jenis($jenis_id, true);
    if (!$jenis) {
        wp_send_json_error(['message' => 'Jenis surat tidak ditemukan atau sedang nonaktif.']);
    }

    if ($nama === '' || $identitas === '' || !is_email($email) || $perihal === '') {
        wp_send_json_error(['message' => 'Harap lengkapi Nama, NIM/NIP, Email yang valid, dan Perihal/Tujuan surat.']);
    }

    // Anti-spam: batasi pengajuan berulang dari NIM/NIP yang sama
    $norm_id = ppb_surat_normalize_id($identitas);
    $rl_key  = 'ppb_surat_rl_' . md5($norm_id);
    $rl_cnt  = (int) get_transient($rl_key);
    $rl_max  = ($peran === 'dosen') ? 10 : 5;
    if ($rl_cnt >= $rl_max) {
        wp_send_json_error(['message' => 'Batas pengajuan sementara tercapai (maks. ' . $rl_max . ' pengajuan/jam per NIM/NIP). Silakan coba lagi nanti.']);
    }
    $dup_recent = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$table} WHERE pemohon_identitas = %s AND jenis_surat_id = %s AND perihal_tujuan = %s AND created_at >= DATE_SUB(%s, INTERVAL 2 MINUTE)",
        $identitas,
        $jenis_id,
        $perihal,
        $now
    ));
    if ($dup_recent > 0) {
        wp_send_json_error(['message' => 'Pengajuan yang sama baru saja dikirim. Silakan cek tab Tracking atau tunggu 2 menit sebelum mengajukan ulang.']);
    }

    $berkas_mode = $jenis['berkas_mode'] ?? 'optional';
    if ($berkas_mode === 'required' && $link === '') {
        wp_send_json_error(['message' => 'Jenis surat ini mewajibkan lampiran Link Google Drive / URL berkas persyaratan.']);
    }
    if ($berkas_mode === 'hidden') {
        $link = '';
    }

    if ($peran !== 'dosen') {
        $jumlah = 1;
    } else {
        $max_batch = (int) $settings['max_batch_dosen'];
        if ($jumlah < 1 || $jumlah > $max_batch) {
            wp_send_json_error(['message' => sprintf('Jumlah nomor untuk Dosen harus antara 1 sampai %d.', $max_batch)]);
        }
        if (!empty($settings['validate_dosen_nip']) && $jumlah > 1) {
            $dosen_check = ppb_surat_lookup_dosen_by_nip($identitas);
            if (!$dosen_check['found']) {
                wp_send_json_error(['message' => 'NIP tidak ditemukan pada direktori Dosen BK FIP UNM. Permintaan banyak nomor sekaligus hanya tersedia untuk Dosen terdaftar.']);
            }
        }
    }

    $tracking_code = ppb_surat_generate_tracking_code($year);
    $mode          = ($jenis['mode'] === 'auto') ? 'auto' : 'approval';
    $kode_hal      = strtoupper((string) ($jenis['kode_hal'] ?: 'PP'));
    $result_numbers = [];

    if ($mode === 'auto') {
        $tx_res = ppb_surat_with_year_lock($year, function () use ($wpdb, $table, $year, $jumlah, $kode_hal, $tracking_code, $jenis, $mode, $peran, $nama, $identitas, $email, $wa, $unit, $perihal, $link, $now) {
            $allocated = ppb_surat_allocate_numbers_locked($year, $jumlah, $kode_hal);
            if (is_wp_error($allocated)) {
                return $allocated;
            }
            $nums = [];
            for ($i = 0; $i < $jumlah; $i++) {
                $seq_num = $allocated[$i]['nomor_urut'];
                $str_num = $allocated[$i]['nomor_surat'];

                // Double-check tepat sebelum INSERT bahwa nomor belum terpakai
                if (ppb_surat_number_exists($year, $seq_num, $str_num)) {
                    $retry = ppb_surat_allocate_numbers_locked($year, 1, $kode_hal);
                    if (is_wp_error($retry)) {
                        $wpdb->delete($table, ['tracking_code' => $tracking_code]);
                        return $retry;
                    }
                    $seq_num = $retry[0]['nomor_urut'];
                    $str_num = $retry[0]['nomor_surat'];
                }

                $inserted = $wpdb->insert($table, [
                    'tracking_code'     => $tracking_code,
                    'batch_index'       => $i + 1,
                    'batch_total'       => $jumlah,
                    'jenis_surat_id'    => $jenis['id'],
                    'jenis_surat_nama'  => $jenis['nama'],
                    'kode_hal'          => $kode_hal,
                    'mode_penerbitan'   => $mode,
                    'pemohon_peran'     => $peran,
                    'pemohon_nama'      => $nama,
                    'pemohon_identitas' => $identitas,
                    'pemohon_email'     => $email,
                    'pemohon_wa'        => $wa,
                    'unit_jenjang'      => $unit,
                    'perihal_tujuan'    => $perihal,
                    'link_berkas'       => $link,
                    'status'            => 'approved',
                    'nomor_urut'        => $seq_num,
                    'tahun'             => $year,
                    'nomor_surat'       => $str_num,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ]);

                if ($inserted === false) {
                    $wpdb->delete($table, ['tracking_code' => $tracking_code]);
                    return new WP_Error('insert_conflict', 'Terjadi bentrokan nomor surat bersamaan. Silakan klik Ajukan lagi.');
                }
                $nums[] = $str_num;
            }
            return $nums;
        });

        if (is_wp_error($tx_res)) {
            wp_send_json_error(['message' => $tx_res->get_error_message()]);
        }
        $result_numbers = $tx_res;
    } else {
        for ($i = 0; $i < $jumlah; $i++) {
            $wpdb->insert($table, [
                'tracking_code'     => $tracking_code,
                'batch_index'       => $i + 1,
                'batch_total'       => $jumlah,
                'jenis_surat_id'    => $jenis['id'],
                'jenis_surat_nama'  => $jenis['nama'],
                'kode_hal'          => $kode_hal,
                'mode_penerbitan'   => $mode,
                'pemohon_peran'     => $peran,
                'pemohon_nama'      => $nama,
                'pemohon_identitas' => $identitas,
                'pemohon_email'     => $email,
                'pemohon_wa'        => $wa,
                'unit_jenjang'      => $unit,
                'perihal_tujuan'    => $perihal,
                'link_berkas'       => $link,
                'status'            => 'pending',
                'nomor_urut'        => null,
                'tahun'             => $year,
                'nomor_surat'       => null,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
        }
    }

    set_transient($rl_key, $rl_cnt + 1, HOUR_IN_SECONDS);
    $verify_url = add_query_arg('cek', $tracking_code, home_url('/layanan/persuratan/'));

    wp_send_json_success([
        'tracking_code'    => $tracking_code,
        'verify_url'       => $verify_url,
        'status'           => ($mode === 'auto') ? 'approved' : 'pending',
        'mode'             => $mode,
        'jenis_surat_id'   => $jenis['id'],
        'jenis_surat_nama' => $jenis['nama'],
        'kode_hal'         => $kode_hal,
        'pemohon_nama'     => $nama,
        'pemohon_peran'    => $peran,
        'pemohon_identitas'=> $identitas,
        'unit_jenjang'     => $unit,
        'perihal_tujuan'   => $perihal,
        'batch_total'      => $jumlah,
        'nomor_list'       => $result_numbers,
        'created_at'       => $now,
    ]);
}
}
add_action('wp_ajax_ppb_submit_nomor_surat', 'ppb_ajax_submit_nomor_surat');
add_action('wp_ajax_nopriv_ppb_submit_nomor_surat', 'ppb_ajax_submit_nomor_surat');

if (!function_exists('ppb_ajax_track_nomor_surat')) {
function ppb_ajax_track_nomor_surat() {
    check_ajax_referer('ppb_nomor_surat_nonce', 'nonce');
    ppb_surat_maybe_install_table();

    global $wpdb;
    $table = ppb_surat_table_name();
    $query = isset($_POST['query']) ? trim(sanitize_text_field(wp_unslash($_POST['query']))) : '';

    if ($query === '') {
        wp_send_json_error(['message' => 'Masukkan Kode Tracking (mis. BK-2026-XXXXXX), Nomor Surat, atau NIM/NIP.']);
    }

    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$table} WHERE tracking_code = %s ORDER BY batch_index ASC, id ASC",
        strtoupper($query)
    ), ARRAY_A);

    if (empty($rows)) {
        $norm_num = preg_replace('/\s+/', '', strtoupper($query));
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} WHERE REPLACE(UPPER(COALESCE(nomor_surat, '')), ' ', '') = %s ORDER BY batch_index ASC, id ASC",
            $norm_num
        ), ARRAY_A);
    }

    if (empty($rows)) {
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} WHERE pemohon_identitas = %s ORDER BY created_at DESC, batch_index ASC LIMIT 50",
            $query
        ), ARRAY_A);
    }

    if (empty($rows)) {
        wp_send_json_error(['message' => 'Data atau Nomor Surat tidak ditemukan di buku registrasi resmi.']);
    }

    $batches = [];
    foreach ($rows as $r) {
        $tc = $r['tracking_code'];
        if (!isset($batches[$tc])) {
            $batches[$tc] = [
                'tracking_code'     => $tc,
                'verify_url'        => add_query_arg('cek', $tc, home_url('/layanan/persuratan/')),
                'jenis_surat_id'    => $r['jenis_surat_id'],
                'jenis_surat_nama'  => $r['jenis_surat_nama'],
                'kode_hal'          => $r['kode_hal'],
                'pemohon_peran'     => $r['pemohon_peran'],
                'pemohon_nama'      => $r['pemohon_nama'],
                'pemohon_identitas' => $r['pemohon_identitas'],
                'unit_jenjang'      => $r['unit_jenjang'],
                'perihal_tujuan'    => $r['perihal_tujuan'],
                'status'            => $r['status'],
                'catatan_admin'     => (string) ($r['catatan_admin'] ?? ''),
                'link_arsip_final'  => (string) ($r['link_arsip_final'] ?? ''),
                'batch_total'       => (int) $r['batch_total'],
                'created_at'        => $r['created_at'],
                'items'             => [],
            ];
        }
        $batches[$tc]['items'][] = [
            'batch_index' => (int) $r['batch_index'],
            'status'      => $r['status'],
            'nomor_surat' => (string) ($r['nomor_surat'] ?? ''),
        ];
    }

    wp_send_json_success(['batches' => array_values($batches)]);
}
}
add_action('wp_ajax_ppb_track_nomor_surat', 'ppb_ajax_track_nomor_surat');
add_action('wp_ajax_nopriv_ppb_track_nomor_surat', 'ppb_ajax_track_nomor_surat');

/* ========================================================================== */
/* 5. FRONTEND WIDGET & SHORTCODE                                             */
/* ========================================================================== */

if (!function_exists('ppb_render_nomor_surat_widget')) {
function ppb_render_nomor_surat_widget($atts = []) {
    $jenis_list = ppb_surat_get_jenis_list(true);
    $settings   = ppb_surat_get_settings();
    $nonce      = wp_create_nonce('ppb_nomor_surat_nonce');
    $ajax_url   = admin_url('admin-ajax.php');

    $unit_opts = [
        's1'      => 'S1 Bimbingan dan Konseling',
        's2'      => 'S2 Bimbingan dan Konseling',
        's3'      => 'S3 Bimbingan dan Konseling',
        'jurusan' => 'Jurusan PPB / Umum',
    ];
    if (function_exists('ppb_get_unit_options')) {
        $unit_opts = ppb_get_unit_options(true);
    }

    ob_start();
    ?>
    <div id="ppb-permintaan-nomor-surat" class="ppb-ns-box" style="background:#ffffff;border:1.5px solid #D4E3D8;border-radius:20px;padding:28px;margin:32px 0;box-shadow:0 8px 24px rgba(13,59,34,0.06);font-family:'Plus Jakarta Sans',sans-serif;">
        <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:14px;margin-bottom:22px;padding-bottom:18px;border-bottom:1px solid #E5E7EB;">
            <div>
                <span style="display:inline-block;background:#E8F0EA;color:#0D3B22;font-size:11px;font-weight:800;padding:3px 10px;border-radius:999px;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px;">
                    Portal Registrasi Surat Resmi
                </span>
                <h3 style="font-family:'Fraunces',Georgia,serif;font-size:22px;color:#0D3B22;margin:0;">
                    Permintaan &amp; Pelacakan Nomor Surat
                </h3>
            </div>
            <div style="display:flex;gap:8px;background:#FAF8F5;padding:5px;border-radius:12px;border:1px solid #D4E3D8;">
                <button type="button" class="ppb-ns-tab-btn is-active" data-tab="form" style="padding:8px 16px;border-radius:9px;border:none;background:#0D3B22;color:#F5B82E;font-size:13px;font-weight:700;cursor:pointer;">
                    📝 Ajukan Nomor
                </button>
                <button type="button" class="ppb-ns-tab-btn" data-tab="track" style="padding:8px 16px;border-radius:9px;border:none;background:transparent;color:#374151;font-size:13px;font-weight:700;cursor:pointer;">
                    🔍 Cek Status / Tracking
                </button>
            </div>
        </div>

        <!-- TAB 1: FORM PENGAJUAN -->
        <div id="ppb-ns-panel-form" class="ppb-ns-panel">
            <form id="ppb-ns-form" autocomplete="off">
                <input type="hidden" name="action" value="ppb_submit_nomor_surat">
                <input type="hidden" name="nonce" value="<?php echo esc_attr($nonce); ?>">

                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px;margin-bottom:16px;">
                    <div>
                        <label style="display:block;font-size:12.5px;font-weight:700;color:#0D3B22;margin-bottom:6px;">Peran Pemohon *</label>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                            <label class="ppb-ns-role-card" style="display:flex;align-items:center;gap:8px;padding:10px 12px;border:1.5px solid #0D3B22;background:#F0F6F2;border-radius:10px;cursor:pointer;font-size:13px;font-weight:700;color:#0D3B22;">
                                <input type="radio" name="pemohon_peran" value="mahasiswa" checked> 🎓 Mahasiswa
                            </label>
                            <label class="ppb-ns-role-card" style="display:flex;align-items:center;gap:8px;padding:10px 12px;border:1.5px solid #D1D5DB;background:#ffffff;border-radius:10px;cursor:pointer;font-size:13px;font-weight:700;color:#374151;">
                                <input type="radio" name="pemohon_peran" value="dosen"> 👨‍🏫 Dosen
                            </label>
                        </div>
                    </div>

                    <div>
                        <label for="ppb_ns_jenis" style="display:block;font-size:12.5px;font-weight:700;color:#0D3B22;margin-bottom:6px;">Jenis Surat *</label>
                        <select id="ppb_ns_jenis" name="jenis_surat_id" required style="width:100%;padding:10px 12px;border:1px solid #CBD5E1;border-radius:10px;font-size:13.5px;background:#ffffff;">
                            <?php foreach ($jenis_list as $j) : ?>
                                <option
                                    value="<?php echo esc_attr($j['id']); ?>"
                                    data-mode="<?php echo esc_attr($j['mode']); ?>"
                                    data-hal="<?php echo esc_attr($j['kode_hal']); ?>"
                                    data-berkas="<?php echo esc_attr($j['berkas_mode']); ?>"
                                    data-desc="<?php echo esc_attr($j['deskripsi']); ?>">
                                    [<?php echo esc_html($j['kode_hal']); ?>] <?php echo esc_html($j['nama']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="ppb-ns-jenis-info" style="margin-top:6px;font-size:11.5px;color:#4B5563;"></div>
                    </div>
                </div>

                <!-- Opsi Jumlah Banyak Nomor (Khusus Dosen) -->
                <div id="ppb-ns-dosen-batch-box" style="display:none;background:#FFFBEB;border:1.5px solid #F5B82E;border-radius:12px;padding:14px 16px;margin-bottom:16px;">
                    <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;">
                        <div style="flex:1;min-width:220px;">
                            <strong style="font-size:13px;color:#92400E;display:block;">📦 Permintaan Banyak Nomor Sekaligus (Khusus Dosen)</strong>
                            <span style="font-size:12px;color:#78350F;">Anda dapat meminta hingga <?php echo (int) $settings['max_batch_dosen']; ?> nomor surat berurutan sekaligus untuk jenis surat yang sama.</span>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <label for="ppb_ns_jumlah" style="font-size:12.5px;font-weight:700;color:#92400E;">Jumlah Nomor:</label>
                            <input type="number" id="ppb_ns_jumlah" name="jumlah_nomor" min="1" max="<?php echo (int) $settings['max_batch_dosen']; ?>" value="1" style="width:86px;padding:8px 10px;border:1.5px solid #D97706;border-radius:8px;font-size:14px;font-weight:700;text-align:center;">
                        </div>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;margin-bottom:14px;">
                    <div>
                        <label id="ppb-ns-id-label" for="ppb_ns_identitas" style="display:block;font-size:12.5px;font-weight:700;color:#0D3B22;margin-bottom:5px;">NIM *</label>
                        <div style="display:flex;gap:6px;">
                            <input type="text" id="ppb_ns_identitas" name="pemohon_identitas" required placeholder="Contoh: 220404501001" style="flex:1;padding:10px 12px;border:1px solid #CBD5E1;border-radius:10px;font-size:13.5px;">
                            <button type="button" id="ppb-ns-check-nip" style="display:none;padding:0 12px;border-radius:10px;border:1px solid #0D3B22;background:#E8F0EA;color:#0D3B22;font-size:12px;font-weight:700;cursor:pointer;white-space:nowrap;">Cek NIP</button>
                        </div>
                        <span id="ppb-ns-nip-status" style="display:none;font-size:11.5px;margin-top:4px;"></span>
                    </div>
                    <div>
                        <label for="ppb_ns_nama" style="display:block;font-size:12.5px;font-weight:700;color:#0D3B22;margin-bottom:5px;">Nama Lengkap *</label>
                        <input type="text" id="ppb_ns_nama" name="pemohon_nama" required placeholder="Nama lengkap sesuai data akademik" style="width:100%;padding:10px 12px;border:1px solid #CBD5E1;border-radius:10px;font-size:13.5px;">
                    </div>
                    <div>
                        <label for="ppb_ns_unit" style="display:block;font-size:12.5px;font-weight:700;color:#0D3B22;margin-bottom:5px;">Jenjang / Unit *</label>
                        <select id="ppb_ns_unit" name="unit_jenjang" style="width:100%;padding:10px 12px;border:1px solid #CBD5E1;border-radius:10px;font-size:13.5px;background:#ffffff;">
                            <?php foreach ($unit_opts as $ukey => $ulabel) : ?>
                                <option value="<?php echo esc_attr($ukey); ?>"><?php echo esc_html($ulabel); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;margin-bottom:14px;">
                    <div>
                        <label for="ppb_ns_email" style="display:block;font-size:12.5px;font-weight:700;color:#0D3B22;margin-bottom:5px;">Email Aktif *</label>
                        <input type="email" id="ppb_ns_email" name="pemohon_email" required placeholder="nama@unm.ac.id / gmail.com" style="width:100%;padding:10px 12px;border:1px solid #CBD5E1;border-radius:10px;font-size:13.5px;">
                    </div>
                    <div>
                        <label for="ppb_ns_wa" style="display:block;font-size:12.5px;font-weight:700;color:#0D3B22;margin-bottom:5px;">No. WhatsApp</label>
                        <input type="text" id="ppb_ns_wa" name="pemohon_wa" placeholder="08xxxxxxxxxx" style="width:100%;padding:10px 12px;border:1px solid #CBD5E1;border-radius:10px;font-size:13.5px;">
                    </div>
                </div>

                <div style="margin-bottom:14px;">
                    <label for="ppb_ns_perihal" style="display:block;font-size:12.5px;font-weight:700;color:#0D3B22;margin-bottom:5px;">Perihal / Instansi Tujuan Surat *</label>
                    <textarea id="ppb_ns_perihal" name="perihal_tujuan" rows="2" required placeholder="Contoh: Permohonan Izin Penelitian Skripsi di SMAN 1 Makassar" style="width:100%;padding:10px 12px;border:1px solid #CBD5E1;border-radius:10px;font-size:13.5px;"></textarea>
                </div>

                <div id="ppb-ns-berkas-wrap" style="margin-bottom:18px;">
                    <label id="ppb-ns-berkas-label" for="ppb_ns_berkas" style="display:block;font-size:12.5px;font-weight:700;color:#0D3B22;margin-bottom:5px;">Link Google Drive / URL Berkas Persyaratan</label>
                    <input type="url" id="ppb_ns_berkas" name="link_berkas" placeholder="https://drive.google.com/..." style="width:100%;padding:10px 12px;border:1px solid #CBD5E1;border-radius:10px;font-size:13.5px;">
                    <span style="display:block;font-size:11.5px;color:#6B7280;margin-top:4px;">Pastikan akses link Google Drive disetel ke "Anyone with the link can view" agar Admin/TU dapat memverifikasi.</span>
                </div>

                <div id="ppb-ns-error" style="display:none;background:#FEF2F2;border:1px solid #FCA5A5;color:#991B1B;padding:10px 14px;border-radius:10px;font-size:13px;margin-bottom:14px;"></div>

                <button type="submit" id="ppb-ns-submit-btn" style="background:#0D3B22;color:#F5B82E;border:none;padding:12px 24px;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:8px;">
                    Ajukan Nomor Surat &rarr;
                </button>
            </form>

            <!-- Hasil / Resi Pengajuan -->
            <div id="ppb-ns-result" style="display:none;margin-top:22px;background:#F0F6F2;border:1.5px solid #0D3B22;border-radius:16px;padding:22px;"></div>
        </div>

        <!-- TAB 2: TRACKING STATUS -->
        <div id="ppb-ns-panel-track" class="ppb-ns-panel" style="display:none;">
            <form id="ppb-ns-track-form" style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:18px;">
                <input type="text" id="ppb_ns_track_q" placeholder="Masukkan Kode Tracking (BK-2026-...), Nomor Surat (B/...), atau NIM / NIP..." required style="flex:1;min-width:240px;padding:11px 14px;border:1px solid #CBD5E1;border-radius:10px;font-size:13.5px;">
                <button type="submit" style="background:#0D3B22;color:#F5B82E;border:none;padding:11px 22px;border-radius:10px;font-size:13.5px;font-weight:700;cursor:pointer;">
                    🔍 Lacak Pengajuan
                </button>
            </form>
            <div id="ppb-ns-track-result"></div>
        </div>
    </div>

    <script>
    (function(){
        var ajaxUrl = <?php echo wp_json_encode($ajax_url); ?>;
        var nonce   = <?php echo wp_json_encode($nonce); ?>;
        var root    = document.getElementById('ppb-permintaan-nomor-surat');
        if (!root) return;

        // Tab switching
        root.querySelectorAll('.ppb-ns-tab-btn').forEach(function(btn){
            btn.addEventListener('click', function(){
                var tab = btn.getAttribute('data-tab');
                root.querySelectorAll('.ppb-ns-tab-btn').forEach(function(b){
                    var act = (b === btn);
                    b.style.background = act ? '#0D3B22' : 'transparent';
                    b.style.color = act ? '#F5B82E' : '#374151';
                });
                document.getElementById('ppb-ns-panel-form').style.display  = (tab === 'form') ? 'block' : 'none';
                document.getElementById('ppb-ns-panel-track').style.display = (tab === 'track') ? 'block' : 'none';
            });
        });

        // Role toggle (Mahasiswa vs Dosen)
        var roleInputs   = root.querySelectorAll('input[name="pemohon_peran"]');
        var batchBox     = document.getElementById('ppb-ns-dosen-batch-box');
        var jumlahInput  = document.getElementById('ppb_ns_jumlah');
        var idLabel      = document.getElementById('ppb-ns-id-label');
        var idInput      = document.getElementById('ppb_ns_identitas');
        var checkNipBtn  = document.getElementById('ppb-ns-check-nip');
        var nipStatus    = document.getElementById('ppb-ns-nip-status');

        function syncRoleUI() {
            var selected = root.querySelector('input[name="pemohon_peran"]:checked');
            var isDosen  = selected && selected.value === 'dosen';
            root.querySelectorAll('.ppb-ns-role-card').forEach(function(lbl){
                var inp = lbl.querySelector('input');
                var act = inp && inp.checked;
                lbl.style.borderColor = act ? '#0D3B22' : '#D1D5DB';
                lbl.style.background  = act ? '#F0F6F2' : '#ffffff';
                lbl.style.color       = act ? '#0D3B22' : '#374151';
            });
            batchBox.style.display    = isDosen ? 'block' : 'none';
            checkNipBtn.style.display = isDosen ? 'inline-block' : 'none';
            if (!isDosen) {
                jumlahInput.value = 1;
                nipStatus.style.display = 'none';
            }
            idLabel.textContent = isDosen ? 'NIP / NIDN Dosen *' : 'NIM Mahasiswa *';
            idInput.placeholder = isDosen ? 'Contoh: 19720817 200212 1 001' : 'Contoh: 220404501001';
        }
        roleInputs.forEach(function(r){ r.addEventListener('change', syncRoleUI); });
        syncRoleUI();

        // Check NIP Dosen (tombol & otomatis saat selesai isi NIP)
        function runDosenNipLookup() {
            var selected = root.querySelector('input[name="pemohon_peran"]:checked');
            if (!selected || selected.value !== 'dosen') return;
            var nip = idInput.value.trim();
            if (nip.replace(/[^A-Za-z0-9]/g, '').length < 8) return;
            nipStatus.style.display = 'block';
            nipStatus.style.color = '#6B7280';
            nipStatus.textContent = 'Memeriksa NIP...';
            var fd = new FormData();
            fd.append('action', 'ppb_check_dosen_nip');
            fd.append('nonce', nonce);
            fd.append('nip', nip);
            fetch(ajaxUrl, { method: 'POST', body: fd }).then(function(r){ return r.json(); }).then(function(res){
                if (res && res.success && res.data && res.data.found) {
                    nipStatus.style.color = '#059669';
                    nipStatus.textContent = '✓ Terverifikasi: ' + res.data.name;
                    var namaEl  = document.getElementById('ppb_ns_nama');
                    var emailEl = document.getElementById('ppb_ns_email');
                    if (namaEl && !namaEl.value.trim() && res.data.name) namaEl.value = res.data.name;
                    if (emailEl && !emailEl.value.trim() && res.data.email) emailEl.value = res.data.email;
                } else {
                    nipStatus.style.color = '#B45309';
                    nipStatus.textContent = 'NIP belum terdaftar di direktori Dosen.';
                }
            }).catch(function(){ nipStatus.style.display = 'none'; });
        }
        checkNipBtn.addEventListener('click', runDosenNipLookup);
        idInput.addEventListener('blur', runDosenNipLookup);

        // Jenis surat metadata sync
        var jenisSel    = document.getElementById('ppb_ns_jenis');
        var jenisInfo   = document.getElementById('ppb-ns-jenis-info');
        var berkasWrap  = document.getElementById('ppb-ns-berkas-wrap');
        var berkasLabel = document.getElementById('ppb-ns-berkas-label');
        var berkasInput = document.getElementById('ppb_ns_berkas');

        function syncJenisUI() {
            var opt = jenisSel.options[jenisSel.selectedIndex];
            if (!opt) return;
            var mode   = opt.getAttribute('data-mode');
            var berkas = opt.getAttribute('data-berkas');
            var desc   = opt.getAttribute('data-desc') || '';
            var badge  = (mode === 'auto')
                ? '<span style="background:#D1FAE5;color:#065F46;padding:2px 7px;border-radius:5px;font-weight:700;">⚡ Terbit Otomatis</span>'
                : '<span style="background:#FEF3C7;color:#92400E;padding:2px 7px;border-radius:5px;font-weight:700;">⏳ Perlu Verifikasi Admin/TU</span>';
            jenisInfo.innerHTML = badge + (desc ? ' &mdash; ' + desc : '');

            if (berkas === 'hidden') {
                berkasWrap.style.display = 'none';
                berkasInput.required = false;
            } else if (berkas === 'required') {
                berkasWrap.style.display = 'block';
                berkasInput.required = true;
                berkasLabel.textContent = 'Link Google Drive / URL Berkas Persyaratan * (Wajib)';
            } else {
                berkasWrap.style.display = 'block';
                berkasInput.required = false;
                berkasLabel.textContent = 'Link Google Drive / URL Berkas Persyaratan (Opsional)';
            }
        }
        jenisSel.addEventListener('change', syncJenisUI);
        syncJenisUI();

        // Allow external catalog buttons to pre-select jenis surat and scroll
        window.ppbSelectJenisSurat = function(jenisId) {
            if (jenisSel) {
                for (var i = 0; i < jenisSel.options.length; i++) {
                    if (jenisSel.options[i].value === jenisId) {
                        jenisSel.selectedIndex = i;
                        syncJenisUI();
                        break;
                    }
                }
            }
            var formTab = root.querySelector('.ppb-ns-tab-btn[data-tab="form"]');
            if (formTab) formTab.click();
            root.scrollIntoView({ behavior: 'smooth', block: 'start' });
        };

        // Helper render receipt
        function renderNumbersList(list) {
            if (!list || !list.length) return '';
            var html = '<div style="margin-top:12px;background:#ffffff;border:1px solid #D4E3D8;border-radius:10px;padding:12px;">';
            html += '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">';
            html += '<strong style="font-size:12.5px;color:#0D3B22;">Daftar Nomor Surat Resmi (' + list.length + '):</strong>';
            html += '<button type="button" class="ppb-ns-copy-all" data-text="' + list.join('\n').replace(/"/g,'&quot;') + '" style="background:#0D3B22;color:#F5B82E;border:none;padding:5px 10px;border-radius:6px;font-size:11.5px;font-weight:700;cursor:pointer;">📋 Salin Semua</button>';
            html += '</div><ol style="margin:0;padding-left:20px;font-family:monospace;font-size:13.5px;color:#0D3B22;font-weight:700;line-height:1.7;">';
            list.forEach(function(num){ html += '<li>' + num + '</li>'; });
            html += '</ol></div>';
            return html;
        }

        // Submit Form
        var form   = document.getElementById('ppb-ns-form');
        var errBox = document.getElementById('ppb-ns-error');
        var resBox = document.getElementById('ppb-ns-result');
        var subBtn = document.getElementById('ppb-ns-submit-btn');

        form.addEventListener('submit', function(e){
            e.preventDefault();
            errBox.style.display = 'none';
            resBox.style.display = 'none';
            subBtn.disabled = true;
            var origText = subBtn.innerHTML;
            subBtn.textContent = 'Memproses pengajuan...';

            fetch(ajaxUrl, { method: 'POST', body: new FormData(form) })
                .then(function(r){ return r.json(); })
                .then(function(res){
                    subBtn.disabled = false;
                    subBtn.innerHTML = origText;
                    if (!res || !res.success) {
                        errBox.textContent = (res && res.data && res.data.message) ? res.data.message : 'Terjadi kesalahan saat mengirim pengajuan.';
                        errBox.style.display = 'block';
                        return;
                    }
                    var d = res.data;
                    var isApproved = (d.status === 'approved');
                    var badge = isApproved
                        ? '<span style="background:#059669;color:#ffffff;padding:4px 10px;border-radius:999px;font-size:11.5px;font-weight:700;">✓ Nomor Terbit Resmi</span>'
                        : '<span style="background:#D97706;color:#ffffff;padding:4px 10px;border-radius:999px;font-size:11.5px;font-weight:700;">⏳ Menunggu Verifikasi TU</span>';

                    var qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=130x130&data=' + encodeURIComponent(d.verify_url || '');
                    var html = '<div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:10px;margin-bottom:10px;">';
                    html += '<strong style="font-size:16px;color:#0D3B22;">Bukti Pengajuan Nomor Surat</strong>' + badge + '</div>';
                    html += '<div style="display:flex;flex-wrap:wrap;gap:16px;align-items:flex-start;">';
                    html += '<div style="flex:1;min-width:220px;font-size:13px;color:#374151;line-height:1.65;">';
                    html += 'Kode Tracking: <code style="background:#ffffff;padding:3px 8px;border-radius:6px;border:1px solid #D4E3D8;font-weight:800;color:#0D3B22;font-size:14px;">' + d.tracking_code + '</code><br>';
                    html += 'Pemohon: <strong>' + d.pemohon_nama + '</strong> (' + d.pemohon_identitas + ') &bull; ' + d.jenis_surat_nama + ' (' + d.batch_total + ' nomor)</div>';
                    html += '<div style="text-align:center;background:#ffffff;padding:8px;border-radius:10px;border:1px solid #D4E3D8;">';
                    html += '<img src="' + qrUrl + '" alt="QR Verifikasi" style="width:96px;height:96px;display:block;margin:0 auto;">';
                    html += '<span style="font-size:10px;color:#6B7280;display:block;margin-top:4px;">Scan Verifikasi</span></div></div>';
                    if (isApproved) {
                        html += renderNumbersList(d.nomor_list);
                        html += '<p style="margin:10px 0 0;font-size:12.5px;color:#065F46;background:#ECFDF5;padding:10px;border-radius:8px;border:1px solid #A7F3D0;"><strong>Wajib dicatat/disalin:</strong> Simpan Nomor Surat, Kode Tracking, dan QR Verifikasi di atas (sistem tidak mengirim email otomatis).</p>';
                    } else {
                        html += '<p style="margin:10px 0 0;font-size:12.5px;color:#78350F;background:#FFFBEB;padding:10px;border-radius:8px;border:1px solid #FDE68A;"><strong>Wajib dicatat/disalin:</strong> Simpan <strong>Kode Tracking</strong> atau scan QR di atas untuk mengecek status persetujuan &amp; mengambil nomor surat di tab <strong>Cek Status / Tracking</strong>.</p>';
                    }
                    html += '<div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap;"><button type="button" class="ppb-ns-print-btn" style="background:#ffffff;color:#0D3B22;border:1px solid #0D3B22;padding:7px 14px;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;">🖨️ Cetak Resi</button></div>';
                    resBox.innerHTML = html;
                    resBox.style.display = 'block';
                    form.reset();
                    syncRoleUI();
                    syncJenisUI();
                })
                .catch(function(){
                    subBtn.disabled = false;
                    subBtn.innerHTML = origText;
                    errBox.textContent = 'Koneksi gagal. Silakan coba lagi.';
                    errBox.style.display = 'block';
                });
        });

        // Copy & Print & Resubmit button handlers
        var lastTrackedBatches = {};
        root.addEventListener('click', function(e){
            if (e.target && e.target.classList.contains('ppb-ns-copy-all')) {
                var txt = e.target.getAttribute('data-text') || '';
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(txt);
                    e.target.textContent = '✓ Tersalin!';
                    setTimeout(function(){ e.target.textContent = '📋 Salin Semua'; }, 1800);
                }
            }
            if (e.target && e.target.classList.contains('ppb-ns-print-btn')) {
                var card = e.target.closest('#ppb-ns-result, .ppb-ns-track-card');
                if (!card) return;
                var w = window.open('', '_blank', 'width=720,height=600');
                if (w) {
                    w.document.write('<html><head><title>Resi Nomor Surat - BK FIP UNM</title><style>body{font-family:sans-serif;padding:24px;color:#111;}button{display:none!important;}</style></head><body>' + card.innerHTML + '<script>window.onload=function(){window.print();};<\/script></body></html>');
                    w.document.close();
                }
            }
            if (e.target && e.target.classList.contains('ppb-ns-resubmit-btn')) {
                var tc = e.target.getAttribute('data-tc') || '';
                var b  = lastTrackedBatches[tc];
                if (!b) return;
                var formTab = root.querySelector('.ppb-ns-tab-btn[data-tab="form"]');
                if (formTab) formTab.click();
                var rInput = root.querySelector('input[name="pemohon_peran"][value="' + (b.pemohon_peran === 'dosen' ? 'dosen' : 'mahasiswa') + '"]');
                if (rInput) { rInput.checked = true; syncRoleUI(); }
                if (b.jenis_surat_id) { jenisSel.value = b.jenis_surat_id; syncJenisUI(); }
                document.getElementById('ppb_ns_identitas').value = b.pemohon_identitas || '';
                document.getElementById('ppb_ns_nama').value      = b.pemohon_nama || '';
                document.getElementById('ppb_ns_unit').value      = b.unit_jenjang || 's1';
                document.getElementById('ppb_ns_perihal').value   = b.perihal_tujuan || '';
                root.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });

        // Track Form & ?cek= auto-verification
        var trackForm = document.getElementById('ppb-ns-track-form');
        var trackRes  = document.getElementById('ppb-ns-track-result');
        function runTrackLookup(q) {
            if (!q) return;
            trackRes.innerHTML = '<div style="font-size:13px;color:#6B7280;">Memeriksa buku registrasi...</div>';
            var fd = new FormData();
            fd.append('action', 'ppb_track_nomor_surat');
            fd.append('nonce', nonce);
            fd.append('query', q);
            fetch(ajaxUrl, { method: 'POST', body: fd }).then(function(r){ return r.json(); }).then(function(res){
                if (!res || !res.success) {
                    trackRes.innerHTML = '<div style="background:#FEF2F2;border:1px solid #FCA5A5;color:#991B1B;padding:12px;border-radius:10px;font-size:13px;">❌ ' + ((res && res.data && res.data.message) || 'Tidak ditemukan.') + '</div>';
                    return;
                }
                var out = '';
                res.data.batches.forEach(function(b){
                    lastTrackedBatches[b.tracking_code] = b;
                    var stBadge = b.status === 'approved'
                        ? '<span style="background:#D1FAE5;color:#065F46;padding:3px 9px;border-radius:999px;font-size:11px;font-weight:700;">✓ Terdaftar / Valid</span>'
                        : (b.status === 'reserved'
                            ? '<span style="background:#EDE9FE;color:#5B21B6;padding:3px 9px;border-radius:999px;font-size:11px;font-weight:700;">📌 Direservasi Admin</span>'
                            : (b.status === 'frozen'
                                ? '<span style="background:#E0F2FE;color:#075985;padding:3px 9px;border-radius:999px;font-size:11px;font-weight:700;">❄️ Dibekukan (Freeze)</span>'
                                : (b.status === 'rejected'
                                    ? '<span style="background:#FEE2E2;color:#991B1B;padding:3px 9px;border-radius:999px;font-size:11px;font-weight:700;">Ditolak</span>'
                                    : '<span style="background:#FEF3C7;color:#92400E;padding:3px 9px;border-radius:999px;font-size:11px;font-weight:700;">Menunggu Verifikasi</span>')));
                    var nums = b.items.map(function(it){ return it.nomor_surat; }).filter(Boolean);
                    var qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=110x110&data=' + encodeURIComponent(b.verify_url || '');
                    out += '<div class="ppb-ns-track-card" style="background:#FAF8F5;border:1px solid #D4E3D8;border-radius:12px;padding:16px;margin-bottom:12px;">';
                    out += '<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:8px;">';
                    out += '<strong style="color:#0D3B22;font-size:14px;">' + b.tracking_code + ' &mdash; ' + b.jenis_surat_nama + '</strong>' + stBadge + '</div>';
                    out += '<div style="display:flex;flex-wrap:wrap;gap:14px;align-items:flex-start;">';
                    out += '<div style="flex:1;min-width:220px;font-size:12.5px;color:#4B5563;line-height:1.6;">';
                    out += 'Pemohon: <strong>' + b.pemohon_nama + '</strong> (' + b.pemohon_identitas + ') &bull; ' + (b.unit_jenjang || '').toUpperCase() + '<br>';
                    out += 'Perihal/Tujuan: ' + (b.perihal_tujuan || '-') + '<br>';
                    out += 'Tanggal Pengajuan: ' + b.created_at + '</div>';
                    out += '<img src="' + qrUrl + '" alt="QR" style="width:76px;height:76px;border-radius:8px;border:1px solid #D4E3D8;background:#fff;padding:4px;"></div>';
                    if (b.catatan_admin) {
                        out += '<div style="font-size:12px;color:#78350F;margin-top:6px;">Catatan Admin: ' + b.catatan_admin + '</div>';
                    }
                    if (nums.length) out += renderNumbersList(nums);
                    out += '<div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;">';
                    out += '<button type="button" class="ppb-ns-print-btn" style="background:#ffffff;color:#0D3B22;border:1px solid #0D3B22;padding:6px 12px;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;">🖨️ Cetak Resi</button>';
                    if (b.link_arsip_final) {
                        out += '<a href="' + b.link_arsip_final + '" target="_blank" rel="noopener noreferrer" style="background:#0D3B22;color:#F5B82E;padding:6px 12px;border-radius:8px;font-size:12px;font-weight:700;text-decoration:none;">📄 Unduh Surat Final &#8599;</a>';
                    }
                    if (b.status === 'rejected') {
                        out += '<button type="button" class="ppb-ns-resubmit-btn" data-tc="' + b.tracking_code + '" style="background:#FEF3C7;color:#92400E;border:1px solid #F5B82E;padding:6px 12px;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;">🔄 Ajukan Ulang</button>';
                    }
                    out += '</div></div>';
                });
                trackRes.innerHTML = out;
            }).catch(function(){
                trackRes.innerHTML = '<div style="color:#991B1B;font-size:13px;">Gagal menghubungi server.</div>';
            });
        }
        trackForm.addEventListener('submit', function(e){
            e.preventDefault();
            runTrackLookup(document.getElementById('ppb_ns_track_q').value.trim());
        });

        var urlParams = new URLSearchParams(window.location.search);
        var cekParam  = urlParams.get('cek');
        if (cekParam) {
            var trackTab = root.querySelector('.ppb-ns-tab-btn[data-tab="track"]');
            if (trackTab) trackTab.click();
            var qInput = document.getElementById('ppb_ns_track_q');
            if (qInput) qInput.value = cekParam;
            runTrackLookup(cekParam);
            root.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    })();
    </script>
    <?php
    return ob_get_clean();
}
}
add_shortcode('ppb_permintaan_nomor_surat', 'ppb_render_nomor_surat_widget');

/* ========================================================================== */
/* 6. ADMIN EXPORT EXCEL (.XLSX / .CSV) HANDLER                               */
/* ========================================================================== */

if (!function_exists('ppb_surat_build_admin_where')) {
function ppb_surat_build_admin_where($args = []) {
    global $wpdb;
    $where  = ['1=1'];
    $params = [];

    if (!empty($args['status']) && in_array($args['status'], ['pending', 'approved', 'rejected', 'frozen', 'reserved'], true)) {
        $where[]  = 'status = %s';
        $params[] = $args['status'];
    }
    if (!empty($args['tahun'])) {
        $where[]  = 'tahun = %d';
        $params[] = (int) $args['tahun'];
    }
    if (!empty($args['kode_hal'])) {
        $where[]  = 'kode_hal = %s';
        $params[] = strtoupper(sanitize_text_field($args['kode_hal']));
    }
    if (!empty($args['peran']) && in_array($args['peran'], ['mahasiswa', 'dosen', 'admin'], true)) {
        $where[]  = 'pemohon_peran = %s';
        $params[] = $args['peran'];
    }
    if (!empty($args['s'])) {
        $like = '%' . $wpdb->esc_like($args['s']) . '%';
        $where[]  = '(tracking_code LIKE %s OR nomor_surat LIKE %s OR pemohon_nama LIKE %s OR pemohon_identitas LIKE %s OR perihal_tujuan LIKE %s)';
        array_push($params, $like, $like, $like, $like, $like);
    }

    $sql = implode(' AND ', $where);
    return empty($params) ? $sql : $wpdb->prepare($sql, $params);
}
}

if (!function_exists('ppb_surat_handle_admin_export')) {
function ppb_surat_handle_admin_export() {
    if (!is_admin() || !isset($_GET['page']) || $_GET['page'] !== 'ppb-nomor-surat') {
        return;
    }
    if (empty($_GET['ppb_surat_export'])) {
        return;
    }
    if (!current_user_can('edit_posts')) {
        wp_die('Tidak memiliki izin export.');
    }
    check_admin_referer('ppb_surat_export_nonce');
    ppb_surat_maybe_install_table();

    global $wpdb;
    $table  = ppb_surat_table_name();
    $format = sanitize_key(wp_unslash($_GET['ppb_surat_export']));
    $where  = ppb_surat_build_admin_where([
        'status'   => isset($_GET['status']) ? sanitize_key(wp_unslash($_GET['status'])) : '',
        'tahun'    => isset($_GET['tahun']) ? (int) $_GET['tahun'] : 0,
        'kode_hal' => isset($_GET['kode_hal']) ? sanitize_text_field(wp_unslash($_GET['kode_hal'])) : '',
        'peran'    => isset($_GET['peran']) ? sanitize_key(wp_unslash($_GET['peran'])) : '',
        's'        => isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '',
    ]);

    $rows = $wpdb->get_results("SELECT * FROM {$table} WHERE {$where} ORDER BY tahun DESC, COALESCE(nomor_urut, 9999999) ASC, id DESC", ARRAY_A);

    $headers = [
        'No', 'Nomor Surat', 'Nomor Urut', 'Tahun', 'Kode Hal', 'Jenis Surat',
        'Status', 'Peran', 'Nama Pemohon', 'NIM / NIP', 'Email', 'WhatsApp',
        'Unit / Jenjang', 'Perihal / Tujuan', 'Link Berkas', 'Link Arsip Final',
        'Kode Tracking', 'Batch', 'Tanggal Pengajuan', 'Catatan Admin',
    ];

    $data_rows = [];
    foreach ($rows as $idx => $r) {
        $data_rows[] = [
            $idx + 1,
            (string) ($r['nomor_surat'] ?: '-'),
            $r['nomor_urut'] !== null ? (int) $r['nomor_urut'] : '-',
            (int) $r['tahun'],
            (string) $r['kode_hal'],
            (string) $r['jenis_surat_nama'],
            strtoupper((string) $r['status']),
            strtoupper((string) $r['pemohon_peran']),
            (string) $r['pemohon_nama'],
            (string) $r['pemohon_identitas'],
            (string) $r['pemohon_email'],
            (string) $r['pemohon_wa'],
            strtoupper((string) $r['unit_jenjang']),
            (string) $r['perihal_tujuan'],
            (string) ($r['link_berkas'] ?: ''),
            (string) ($r['link_arsip_final'] ?: ''),
            (string) $r['tracking_code'],
            ((int) $r['batch_index']) . '/' . ((int) $r['batch_total']),
            (string) $r['created_at'],
            (string) ($r['catatan_admin'] ?: ''),
        ];
    }

    $fname_base = 'Buku-Registrasi-Nomor-Surat-' . current_time('Y-m-d');

    if ($format === 'xlsx' && function_exists('ppb_io_xlsx_build')) {
        $bytes = ppb_io_xlsx_build([
            [
                'name' => 'Registrasi Surat',
                'rows' => array_merge([$headers], $data_rows),
            ],
        ]);
        nocache_headers();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fname_base . '.xlsx"');
        header('Content-Length: ' . strlen($bytes));
        echo $bytes;
        exit;
    }

    // Fallback CSV (UTF-8 BOM agar rapi dibuka Excel)
    nocache_headers();
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $fname_base . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, $headers);
    foreach ($data_rows as $dr) {
        fputcsv($out, $dr);
    }
    fclose($out);
    exit;
}
}
add_action('admin_init', 'ppb_surat_handle_admin_export');

/* ========================================================================== */
/* 7. WP ADMIN PAGE: ANTREAN, MASTER JENIS & PENGATURAN                       */
/* ========================================================================== */

if (!function_exists('ppb_render_nomor_surat_admin_page')) {
function ppb_render_nomor_surat_admin_page() {
    if (!current_user_can('edit_posts')) {
        wp_die('Anda tidak memiliki izin mengakses halaman ini.');
    }
    ppb_surat_maybe_install_table();

    global $wpdb;
    $table    = ppb_surat_table_name();
    $settings = ppb_surat_get_settings();
    $tab      = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'registrasi';
    if (!in_array($tab, ['registrasi', 'jenis', 'pengaturan'], true)) {
        $tab = 'registrasi';
    }

    $notice = '';
    $notice_type = 'success';

    // Handle POST actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ppb_surat_admin_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ppb_surat_admin_nonce'])), 'ppb_surat_admin_action')) {
        $act = isset($_POST['ppb_surat_action']) ? sanitize_key(wp_unslash($_POST['ppb_surat_action'])) : '';
        $now = current_time('mysql');
        $uid = get_current_user_id();

        if ($act === 'approve_batch') {
            $tc      = isset($_POST['tracking_code']) ? sanitize_text_field(wp_unslash($_POST['tracking_code'])) : '';
            $catatan = isset($_POST['catatan_admin']) ? sanitize_textarea_field(wp_unslash($_POST['catatan_admin'])) : '';
            $manual  = isset($_POST['manual_start_seq']) ? (int) $_POST['manual_start_seq'] : 0;

            $pending_rows = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$table} WHERE tracking_code = %s AND status = 'pending' ORDER BY batch_index ASC, id ASC",
                $tc
            ), ARRAY_A);

            if (!empty($pending_rows)) {
                $year     = (int) $pending_rows[0]['tahun'];
                $kode_hal = (string) $pending_rows[0]['kode_hal'];
                $count    = count($pending_rows);

                $lock_res = ppb_surat_with_year_lock($year, function () use ($wpdb, $table, $pending_rows, $year, $kode_hal, $count, $manual, $settings, $catatan, $uid, $now) {
                    if ($manual > 0) {
                        $alloc = [];
                        for ($i = 0; $i < $count; $i++) {
                            $seq = $manual + $i;
                            $fmt = ppb_surat_format_number($seq, $kode_hal, $year, $settings);
                            if (ppb_surat_number_exists($year, $seq, $fmt)) {
                                return new WP_Error('duplicate_manual', sprintf('Nomor urut %d (%s) sudah pernah diterbitkan! Gunakan nomor lain atau kosongkan untuk otomatis.', $seq, $fmt));
                            }
                            $alloc[] = [
                                'nomor_urut'  => $seq,
                                'nomor_surat' => $fmt,
                            ];
                        }
                        $cur_opt = (int) get_option('ppb_surat_counter_' . $year, 0);
                        if ($manual + $count - 1 > $cur_opt) {
                            update_option('ppb_surat_counter_' . $year, $manual + $count - 1, false);
                        }
                    } else {
                        $alloc = ppb_surat_allocate_numbers_locked($year, $count, $kode_hal);
                        if (is_wp_error($alloc)) {
                            return $alloc;
                        }
                    }

                    foreach ($pending_rows as $idx => $pr) {
                        $updated = $wpdb->update($table, [
                            'status'        => 'approved',
                            'nomor_urut'    => $alloc[$idx]['nomor_urut'],
                            'nomor_surat'   => $alloc[$idx]['nomor_surat'],
                            'catatan_admin' => $catatan,
                            'audit_log'     => ppb_surat_append_audit($pr['audit_log'] ?? '', 'Disetujui & terbit ' . $alloc[$idx]['nomor_surat']),
                            'processed_by'  => $uid,
                            'updated_at'    => $now,
                        ], ['id' => (int) $pr['id'], 'status' => 'pending']);

                        if ($updated === false) {
                            return new WP_Error('update_conflict', 'Terjadi konflik nomor surat saat menyimpan. Silakan coba lagi.');
                        }
                    }
                    return true;
                });

                if (is_wp_error($lock_res)) {
                    $notice = $lock_res->get_error_message();
                    $notice_type = 'error';
                } else {
                    $notice = sprintf('Berhasil menyetujui %d nomor surat untuk %s.', $count, $tc);
                }
            }
        } elseif ($act === 'reject_batch') {
            $tc      = isset($_POST['tracking_code']) ? sanitize_text_field(wp_unslash($_POST['tracking_code'])) : '';
            $catatan = isset($_POST['catatan_admin']) ? sanitize_textarea_field(wp_unslash($_POST['catatan_admin'])) : '';
            $wpdb->update($table, [
                'status'        => 'rejected',
                'catatan_admin' => $catatan,
                'processed_by'  => $uid,
                'updated_at'    => $now,
            ], ['tracking_code' => $tc, 'status' => 'pending']);
            $notice = sprintf('Pengajuan %s ditandai ditolak.', $tc);
        } elseif ($act === 'freeze_row') {
            $row_id = isset($_POST['row_id']) ? (int) $_POST['row_id'] : 0;
            if ($row_id > 0) {
                $cur_log = (string) $wpdb->get_var($wpdb->prepare("SELECT audit_log FROM {$table} WHERE id = %d", $row_id));
                $wpdb->update($table, [
                    'status'       => 'frozen',
                    'audit_log'    => ppb_surat_append_audit($cur_log, 'Dibekukan (Freeze)'),
                    'processed_by' => $uid,
                    'updated_at'   => $now,
                ], ['id' => $row_id]);
                $notice = 'Nomor surat berhasil dibekukan (Freeze).';
            }
        } elseif ($act === 'unfreeze_row') {
            $row_id = isset($_POST['row_id']) ? (int) $_POST['row_id'] : 0;
            if ($row_id > 0) {
                $row_obj = $wpdb->get_row($wpdb->prepare("SELECT nomor_surat, audit_log FROM {$table} WHERE id = %d", $row_id), ARRAY_A);
                $restore_status = (!empty($row_obj['nomor_surat'])) ? 'approved' : 'pending';
                $wpdb->update($table, [
                    'status'       => $restore_status,
                    'audit_log'    => ppb_surat_append_audit($row_obj['audit_log'] ?? '', 'Diaktifkan kembali (' . $restore_status . ')'),
                    'processed_by' => $uid,
                    'updated_at'   => $now,
                ], ['id' => $row_id]);
                $notice = 'Status nomor surat berhasil diaktifkan kembali.';
            }
        } elseif ($act === 'assign_reserved_row') {
            $row_id  = isset($_POST['row_id']) ? (int) $_POST['row_id'] : 0;
            $a_nama  = isset($_POST['a_nama']) ? sanitize_text_field(wp_unslash($_POST['a_nama'])) : '';
            $a_id    = isset($_POST['a_identitas']) ? sanitize_text_field(wp_unslash($_POST['a_identitas'])) : '';
            $a_hal   = isset($_POST['a_perihal']) ? sanitize_text_field(wp_unslash($_POST['a_perihal'])) : '';
            $a_email = isset($_POST['a_email']) ? sanitize_email(wp_unslash($_POST['a_email'])) : '';
            $a_wa    = isset($_POST['a_wa']) ? sanitize_text_field(wp_unslash($_POST['a_wa'])) : '';
            if ($row_id > 0 && $a_nama !== '') {
                $cur_log = (string) $wpdb->get_var($wpdb->prepare("SELECT audit_log FROM {$table} WHERE id = %d", $row_id));
                $wpdb->update($table, [
                    'pemohon_nama'      => $a_nama,
                    'pemohon_identitas' => $a_id ?: 'UMUM',
                    'pemohon_email'     => $a_email,
                    'pemohon_wa'        => $a_wa,
                    'perihal_tujuan'    => $a_hal ?: 'Surat Resmi',
                    'status'            => 'approved',
                    'audit_log'         => ppb_surat_append_audit($cur_log, 'Di-assign ke ' . $a_nama . ' (' . $a_id . ') & diterbitkan'),
                    'processed_by'      => $uid,
                    'updated_at'        => $now,
                ], ['id' => $row_id]);
                $notice = 'Nomor reservasi berhasil di-assign ke pemohon dan diaktifkan.';
            }
        } elseif ($act === 'save_arsip_row') {
            $row_id = isset($_POST['row_id']) ? (int) $_POST['row_id'] : 0;
            $arsip  = isset($_POST['link_arsip_final']) ? esc_url_raw(trim((string) wp_unslash($_POST['link_arsip_final']))) : '';
            if ($row_id > 0) {
                $cur_log = (string) $wpdb->get_var($wpdb->prepare("SELECT audit_log FROM {$table} WHERE id = %d", $row_id));
                $wpdb->update($table, [
                    'link_arsip_final' => $arsip,
                    'audit_log'        => ppb_surat_append_audit($cur_log, $arsip !== '' ? 'Arsip PDF final ditautkan' : 'Tautan arsip PDF dihapus'),
                    'processed_by'     => $uid,
                    'updated_at'       => $now,
                ], ['id' => $row_id]);
                $notice = 'Tautan arsip surat final berhasil disimpan.';
            }
        } elseif ($act === 'delete_row') {
            $row_id = isset($_POST['row_id']) ? (int) $_POST['row_id'] : 0;
            if ($row_id > 0) {
                $wpdb->delete($table, ['id' => $row_id]);
                $notice = 'Data pengajuan nomor surat berhasil dihapus.';
            }
        } elseif ($act === 'delete_batch') {
            $tc = isset($_POST['tracking_code']) ? sanitize_text_field(wp_unslash($_POST['tracking_code'])) : '';
            if ($tc !== '') {
                $deleted_cnt = (int) $wpdb->delete($table, ['tracking_code' => $tc]);
                $notice = sprintf('Berhasil menghapus seluruh batch %s (%d baris).', $tc, $deleted_cnt);
            }
        } elseif ($act === 'reserve_numbers') {
            $r_jenis_id = isset($_POST['r_jenis_id']) ? sanitize_key(wp_unslash($_POST['r_jenis_id'])) : '';
            $r_jumlah   = isset($_POST['r_jumlah']) ? max(1, min(100, (int) $_POST['r_jumlah'])) : 1;
            $r_start    = isset($_POST['r_start_seq']) ? (int) $_POST['r_start_seq'] : 0;
            $r_perihal  = isset($_POST['r_perihal']) ? sanitize_text_field(wp_unslash($_POST['r_perihal'])) : '';
            $r_status   = (isset($_POST['r_status']) && $_POST['r_status'] === 'approved') ? 'approved' : 'reserved';
            $r_tahun    = (int) current_time('Y');

            $jenis = ppb_surat_find_jenis($r_jenis_id, false);
            if (!$jenis) {
                $notice = 'Jenis surat untuk reservasi tidak ditemukan.';
                $notice_type = 'error';
            } else {
                $kode_hal = strtoupper((string) ($jenis['kode_hal'] ?: 'PP'));
                $tc       = 'RSV-' . $r_tahun . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
                $cur_user = wp_get_current_user();
                $adm_name = ($cur_user && !empty($cur_user->display_name)) ? $cur_user->display_name : 'Admin / TU';
                $perihal  = $r_perihal !== '' ? $r_perihal : 'Nomor direservasi oleh Admin/TU';

                $lock_res = ppb_surat_with_year_lock($r_tahun, function () use ($wpdb, $table, $r_tahun, $r_jumlah, $r_start, $kode_hal, $settings, $tc, $jenis, $adm_name, $perihal, $r_status, $uid, $now) {
                    if ($r_start > 0) {
                        $alloc = [];
                        for ($i = 0; $i < $r_jumlah; $i++) {
                            $seq = $r_start + $i;
                            $fmt = ppb_surat_format_number($seq, $kode_hal, $r_tahun, $settings);
                            if (ppb_surat_number_exists($r_tahun, $seq, $fmt)) {
                                return new WP_Error('rsv_conflict', sprintf('Nomor urut %d (%s) sudah terpakai/direservasi! Pilih nomor awal lain atau kosongkan untuk otomatis.', $seq, $fmt));
                            }
                            $alloc[] = ['nomor_urut' => $seq, 'nomor_surat' => $fmt];
                        }
                        $cur_opt = (int) get_option('ppb_surat_counter_' . $r_tahun, 0);
                        if ($r_start + $r_jumlah - 1 > $cur_opt) {
                            update_option('ppb_surat_counter_' . $r_tahun, $r_start + $r_jumlah - 1, false);
                        }
                    } else {
                        $alloc = ppb_surat_allocate_numbers_locked($r_tahun, $r_jumlah, $kode_hal);
                        if (is_wp_error($alloc)) {
                            return $alloc;
                        }
                    }

                    foreach ($alloc as $idx => $al) {
                        $ins = $wpdb->insert($table, [
                            'tracking_code'     => $tc,
                            'batch_index'       => $idx + 1,
                            'batch_total'       => $r_jumlah,
                            'jenis_surat_id'    => $jenis['id'],
                            'jenis_surat_nama'  => $jenis['nama'],
                            'kode_hal'          => $kode_hal,
                            'mode_penerbitan'   => 'reservasi',
                            'pemohon_peran'     => 'admin',
                            'pemohon_nama'      => $adm_name,
                            'pemohon_identitas' => 'RESERVASI',
                            'pemohon_email'     => '',
                            'pemohon_wa'        => '',
                            'unit_jenjang'      => 'jurusan',
                            'perihal_tujuan'    => $perihal,
                            'link_berkas'       => '',
                            'status'            => $r_status,
                            'nomor_urut'        => $al['nomor_urut'],
                            'tahun'             => $r_tahun,
                            'nomor_surat'       => $al['nomor_surat'],
                            'catatan_admin'     => 'Reservasi Admin',
                            'processed_by'      => $uid,
                            'created_at'        => $now,
                            'updated_at'        => $now,
                        ]);
                        if ($ins === false) {
                            $wpdb->delete($table, ['tracking_code' => $tc]);
                            return new WP_Error('rsv_insert_err', 'Gagal menyimpan reservasi karena konflik nomor.');
                        }
                    }
                    return $alloc;
                });

                if (is_wp_error($lock_res)) {
                    $notice = $lock_res->get_error_message();
                    $notice_type = 'error';
                } else {
                    $first_num = $lock_res[0]['nomor_surat'];
                    $last_num  = $lock_res[count($lock_res) - 1]['nomor_surat'];
                    $range_str = (count($lock_res) > 1) ? "{$first_num} s.d. {$last_num}" : $first_num;
                    $notice = sprintf('Berhasil mereservasi %d nomor surat (%s) dengan kode %s.', $r_jumlah, $range_str, $tc);
                }
            }
        } elseif ($act === 'save_jenis') {
            $tab = 'jenis';
            $ids    = $_POST['j_id'] ?? [];
            $names  = $_POST['j_nama'] ?? [];
            $hals   = $_POST['j_hal'] ?? [];
            $modes  = $_POST['j_mode'] ?? [];
            $berkas = $_POST['j_berkas'] ?? [];
            $descs  = $_POST['j_desc'] ?? [];
            $acts   = $_POST['j_active'] ?? [];

            $new_list = [];
            if (is_array($names)) {
                foreach ($names as $k => $raw_name) {
                    $nm = trim(sanitize_text_field(wp_unslash($raw_name)));
                    if ($nm === '') continue;
                    $jid = isset($ids[$k]) && trim($ids[$k]) !== '' ? sanitize_key(wp_unslash($ids[$k])) : sanitize_title($nm);
                    $new_list[] = [
                        'id'          => str_replace('-', '_', $jid),
                        'nama'        => $nm,
                        'kode_hal'    => strtoupper(sanitize_text_field(wp_unslash($hals[$k] ?? 'PP'))) ?: 'PP',
                        'mode'        => (isset($modes[$k]) && $modes[$k] === 'auto') ? 'auto' : 'approval',
                        'berkas_mode' => in_array($berkas[$k] ?? 'optional', ['required', 'optional', 'hidden'], true) ? $berkas[$k] : 'optional',
                        'deskripsi'   => sanitize_text_field(wp_unslash($descs[$k] ?? '')),
                        'active'      => isset($acts[$k]) ? 1 : 0,
                    ];
                }
            }
            if (!empty($new_list)) {
                update_option('ppb_surat_jenis_list', $new_list, false);
                $notice = 'Daftar Master Jenis Surat berhasil disimpan.';
            }
        } elseif ($act === 'save_settings') {
            $tab = 'pengaturan';
            $cur_year = (int) current_time('Y');
            $new_set = [
                'prefix'             => sanitize_text_field(wp_unslash($_POST['prefix'] ?? 'B')) ?: 'B',
                'kode_unit'          => sanitize_text_field(wp_unslash($_POST['kode_unit'] ?? 'UN36.1')) ?: 'UN36.1',
                'pad_digits'         => max(1, min(6, (int) ($_POST['pad_digits'] ?? 3))),
                'max_batch_dosen'    => max(2, min(200, (int) ($_POST['max_batch_dosen'] ?? 50))),
                'validate_dosen_nip' => isset($_POST['validate_dosen_nip']) ? 1 : 0,
                'offsets'            => $settings['offsets'],
            ];
            $offset_val = isset($_POST['offset_current_year']) ? max(0, (int) $_POST['offset_current_year']) : 0;
            $new_set['offsets'][(string) $cur_year] = $offset_val;
            update_option('ppb_surat_settings', $new_set, false);
            $settings = ppb_surat_get_settings();
            $notice = 'Pengaturan penomoran & notifikasi berhasil disimpan.';
        }
    }

    $cur_year = (int) current_time('Y');
    $stats_pending  = ppb_surat_get_pending_count();
    $stats_approved = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE status = 'approved' AND tahun = %d", $cur_year));
    $last_seq       = ppb_surat_get_current_last_seq($cur_year);
    ?>
    <div class="wrap" style="max-width:1240px;">
        <h1 style="display:flex;align-items:center;gap:10px;font-size:24px;color:#0D3B22;">
            📨 Permintaan &amp; Buku Registrasi Nomor Surat
        </h1>

        <?php if ($notice !== '') : ?>
            <div class="notice notice-<?php echo $notice_type === 'error' ? 'error' : 'success'; ?> is-dismissible">
                <p><?php echo esc_html($notice); ?></p>
            </div>
        <?php endif; ?>

        <!-- Ringkasan Statistik -->
        <?php $next_preview = ppb_surat_format_number($last_seq + 1, 'XX', $cur_year, $settings); ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;margin:16px 0 14px;">
            <div style="background:#fff;border:1px solid #D4E3D8;border-left:4px solid #D97706;border-radius:10px;padding:14px 16px;">
                <div style="font-size:12px;color:#6B7280;font-weight:600;">Antrean Menunggu Approval</div>
                <div style="font-size:24px;font-weight:800;color:#92400E;"><?php echo (int) $stats_pending; ?> pengajuan</div>
            </div>
            <div style="background:#fff;border:1px solid #D4E3D8;border-left:4px solid #059669;border-radius:10px;padding:14px 16px;">
                <div style="font-size:12px;color:#6B7280;font-weight:600;">Nomor Terbit Tahun <?php echo $cur_year; ?></div>
                <div style="font-size:24px;font-weight:800;color:#065F46;"><?php echo (int) $stats_approved; ?> nomor</div>
            </div>
            <div style="background:#fff;border:1px solid #D4E3D8;border-left:4px solid #0D3B22;border-radius:10px;padding:14px 16px;">
                <div style="font-size:12px;color:#6B7280;font-weight:600;">Nomor Urut Terakhir &amp; Berikutnya</div>
                <div style="font-size:22px;font-weight:800;color:#0D3B22;font-family:monospace;"><?php echo esc_html(str_pad((string) $last_seq, (int) $settings['pad_digits'], '0', STR_PAD_LEFT)); ?></div>
                <div style="font-size:11px;color:#6B7280;font-family:monospace;">Berikutnya: <?php echo esc_html($next_preview); ?></div>
            </div>
        </div>

        <?php
        $rekap_hal = $wpdb->get_results($wpdb->prepare(
            "SELECT kode_hal, COUNT(*) AS total, SUM(CASE WHEN status='approved' THEN 1 ELSE 0 END) AS terbit, SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) AS pending, SUM(CASE WHEN status='reserved' THEN 1 ELSE 0 END) AS reservasi, SUM(CASE WHEN status='frozen' THEN 1 ELSE 0 END) AS frozen FROM {$table} WHERE tahun = %d GROUP BY kode_hal ORDER BY total DESC",
            $cur_year
        ), ARRAY_A);
        $rekap_bln = $wpdb->get_results($wpdb->prepare(
            "SELECT MONTH(created_at) AS bln, COUNT(*) AS total FROM {$table} WHERE tahun = %d GROUP BY MONTH(created_at) ORDER BY bln ASC",
            $cur_year
        ), ARRAY_A);
        if (!empty($rekap_hal)) : ?>
        <details style="background:#fff;border:1px solid #D4E3D8;border-radius:10px;padding:10px 16px;margin-bottom:16px;">
            <summary style="cursor:pointer;font-weight:700;color:#0D3B22;font-size:13px;">📊 Rekap Statistik Surat Tahun <?php echo $cur_year; ?> (per Kode Hal &amp; Bulan)</summary>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:16px;margin-top:12px;">
                <table class="widefat striped">
                    <thead><tr><th>Kode Hal</th><th>Total</th><th>Terbit</th><th>Pending</th><th>Reservasi</th><th>Freeze</th></tr></thead>
                    <tbody>
                    <?php foreach ($rekap_hal as $rh) : ?>
                        <tr>
                            <td><strong><?php echo esc_html($rh['kode_hal']); ?></strong></td>
                            <td><?php echo (int) $rh['total']; ?></td>
                            <td><?php echo (int) $rh['terbit']; ?></td>
                            <td><?php echo (int) $rh['pending']; ?></td>
                            <td><?php echo (int) $rh['reservasi']; ?></td>
                            <td><?php echo (int) $rh['frozen']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <table class="widefat striped">
                    <thead><tr><th>Bulan (<?php echo $cur_year; ?>)</th><th>Jumlah Pengajuan/Nomor</th></tr></thead>
                    <tbody>
                    <?php
                    $bulan_names = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
                    foreach ($rekap_bln as $rb) : ?>
                        <tr>
                            <td><?php echo esc_html($bulan_names[(int)$rb['bln']] ?? $rb['bln']); ?></td>
                            <td><strong><?php echo (int) $rb['total']; ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </details>
        <?php endif; ?>

        <h2 class="nav-tab-wrapper" style="margin-bottom:18px;">
            <a href="<?php echo esc_url(admin_url('admin.php?page=ppb-nomor-surat&tab=registrasi')); ?>" class="nav-tab <?php echo $tab === 'registrasi' ? 'nav-tab-active' : ''; ?>">📋 Antrean &amp; Buku Registrasi</a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=ppb-nomor-surat&tab=jenis')); ?>" class="nav-tab <?php echo $tab === 'jenis' ? 'nav-tab-active' : ''; ?>">🗂️ Master Jenis Surat</a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=ppb-nomor-surat&tab=pengaturan')); ?>" class="nav-tab <?php echo $tab === 'pengaturan' ? 'nav-tab-active' : ''; ?>">⚙️ Pengaturan Penomoran</a>
        </h2>

        <?php if ($tab === 'registrasi') :
            $f_status = isset($_GET['status']) ? sanitize_key(wp_unslash($_GET['status'])) : '';
            $f_tahun  = isset($_GET['tahun']) ? (int) $_GET['tahun'] : 0;
            $f_hal    = isset($_GET['kode_hal']) ? sanitize_text_field(wp_unslash($_GET['kode_hal'])) : '';
            $f_peran  = isset($_GET['peran']) ? sanitize_key(wp_unslash($_GET['peran'])) : '';
            $f_search = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';

            $where = ppb_surat_build_admin_where([
                'status'   => $f_status,
                'tahun'    => $f_tahun,
                'kode_hal' => $f_hal,
                'peran'    => $f_peran,
                's'        => $f_search,
            ]);
            $rows = $wpdb->get_results("SELECT * FROM {$table} WHERE {$where} ORDER BY CASE WHEN status='pending' THEN 0 ELSE 1 END, id DESC LIMIT 300", ARRAY_A);

            $export_base = wp_nonce_url(add_query_arg([
                'page'     => 'ppb-nomor-surat',
                'status'   => $f_status,
                'tahun'    => $f_tahun ?: '',
                'kode_hal' => $f_hal,
                'peran'    => $f_peran,
                's'        => $f_search,
            ], admin_url('admin.php')), 'ppb_surat_export_nonce');
        ?>
            <!-- Reservasi Nomor oleh Admin -->
            <details style="background:#F8FAFC;border:1px solid #CBD5E1;border-radius:10px;padding:12px 16px;margin-bottom:14px;">
                <summary style="cursor:pointer;font-weight:700;color:#0D3B22;font-size:13.5px;">📌 Reservasi / Booking Nomor Surat oleh Admin</summary>
                <form method="post" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;margin-top:12px;">
                    <?php wp_nonce_field('ppb_surat_admin_action', 'ppb_surat_admin_nonce'); ?>
                    <input type="hidden" name="ppb_surat_action" value="reserve_numbers">
                    <div style="min-width:210px;flex:1;">
                        <label style="display:block;font-size:11.5px;font-weight:700;margin-bottom:3px;">Jenis Surat</label>
                        <select name="r_jenis_id" style="width:100%;">
                            <?php foreach (ppb_surat_get_jenis_list(false) as $rj) : ?>
                                <option value="<?php echo esc_attr($rj['id']); ?>">[<?php echo esc_html($rj['kode_hal']); ?>] <?php echo esc_html($rj['nama']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="width:95px;">
                        <label style="display:block;font-size:11.5px;font-weight:700;margin-bottom:3px;">Jumlah</label>
                        <input type="number" name="r_jumlah" min="1" max="100" value="1" style="width:100%;">
                    </div>
                    <div style="width:155px;">
                        <label style="display:block;font-size:11.5px;font-weight:700;margin-bottom:3px;">No. Urut Awal (Opsional)</label>
                        <input type="number" name="r_start_seq" min="1" placeholder="Otomatis" style="width:100%;">
                    </div>
                    <div style="min-width:210px;flex:1;">
                        <label style="display:block;font-size:11.5px;font-weight:700;margin-bottom:3px;">Keterangan / Peruntukan</label>
                        <input type="text" name="r_perihal" placeholder="Mis. Disimpan untuk SK / Surat Tugas" style="width:100%;">
                    </div>
                    <div style="width:140px;">
                        <label style="display:block;font-size:11.5px;font-weight:700;margin-bottom:3px;">Status Awal</label>
                        <select name="r_status" style="width:100%;">
                            <option value="reserved">📌 Direservasi</option>
                            <option value="approved">✓ Langsung Terbit</option>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="button button-primary">📌 Reservasi Nomor</button>
                    </div>
                </form>
            </details>

            <!-- Filter & Export Bar -->
            <form method="get" style="background:#fff;border:1px solid #D4E3D8;border-radius:10px;padding:14px;margin-bottom:16px;display:flex;flex-wrap:wrap;gap:8px;align-items:center;">
                <input type="hidden" name="page" value="ppb-nomor-surat">
                <input type="hidden" name="tab" value="registrasi">
                <select name="status">
                    <option value="">Semua Status</option>
                    <option value="pending" <?php selected($f_status, 'pending'); ?>>Menunggu Approval</option>
                    <option value="approved" <?php selected($f_status, 'approved'); ?>>Disetujui / Terbit</option>
                    <option value="reserved" <?php selected($f_status, 'reserved'); ?>>Direservasi Admin</option>
                    <option value="frozen" <?php selected($f_status, 'frozen'); ?>>Dibekukan (Freeze)</option>
                    <option value="rejected" <?php selected($f_status, 'rejected'); ?>>Ditolak</option>
                </select>
                <select name="peran">
                    <option value="">Semua Peran</option>
                    <option value="mahasiswa" <?php selected($f_peran, 'mahasiswa'); ?>>Mahasiswa</option>
                    <option value="dosen" <?php selected($f_peran, 'dosen'); ?>>Dosen</option>
                    <option value="admin" <?php selected($f_peran, 'admin'); ?>>Admin / Reservasi</option>
                </select>
                <input type="number" name="tahun" value="<?php echo $f_tahun ?: ''; ?>" placeholder="Tahun (<?php echo $cur_year; ?>)" style="width:120px;">
                <input type="text" name="kode_hal" value="<?php echo esc_attr($f_hal); ?>" placeholder="Kode Hal (PP/PT)" style="width:130px;">
                <input type="search" name="s" value="<?php echo esc_attr($f_search); ?>" placeholder="Cari Nama / NIM / NIP / Tracking / Nomor..." style="min-width:240px;flex:1;">
                <button type="submit" class="button button-primary">Filter</button>
                <a href="<?php echo esc_url(admin_url('admin.php?page=ppb-nomor-surat&tab=registrasi')); ?>" class="button">Reset</a>
                <a href="<?php echo esc_url(add_query_arg('ppb_surat_export', 'xlsx', $export_base)); ?>" class="button" style="background:#0D3B22;color:#F5B82E;border-color:#0D3B22;font-weight:700;">📥 Export Excel (.xlsx)</a>
                <a href="<?php echo esc_url(add_query_arg('ppb_surat_export', 'csv', $export_base)); ?>" class="button">📄 Export CSV</a>
            </form>

            <!-- Tabel Registrasi -->
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width:130px;">Tracking &amp; Tgl</th>
                        <th style="width:190px;">Nomor Surat</th>
                        <th style="width:205px;">Pemohon</th>
                        <th>Jenis &amp; Perihal</th>
                        <th style="width:110px;">Status</th>
                        <th style="width:255px;">Aksi Admin</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($rows)) : ?>
                    <tr><td colspan="6" style="text-align:center;padding:24px;color:#6B7280;">Belum ada data pengajuan nomor surat.</td></tr>
                <?php else :
                    $rendered_pending_batches = [];
                    foreach ($rows as $r) :
                        $tc = $r['tracking_code'];
                        $is_pending  = ($r['status'] === 'pending');
                        $is_frozen   = ($r['status'] === 'frozen');
                        $is_reserved = ($r['status'] === 'reserved');
                        $b_total     = (int) $r['batch_total'];
                        $wa_raw      = trim((string) ($r['pemohon_wa'] ?? ''));
                        $wa_digits   = preg_replace('/[^0-9]/', '', $wa_raw);
                        if (strpos($wa_digits, '0') === 0) {
                            $wa_digits = '62' . substr($wa_digits, 1);
                        }
                ?>
                    <tr>
                        <td>
                            <code style="font-weight:700;color:#0D3B22;"><?php echo esc_html($tc); ?></code>
                            <?php if ($b_total > 1) : ?>
                                <br><span style="font-size:11px;background:#FEF3C7;color:#92400E;padding:1px 6px;border-radius:4px;font-weight:700;">Batch <?php echo (int) $r['batch_index'] . '/' . $b_total; ?></span>
                            <?php endif; ?>
                            <div style="font-size:11px;color:#6B7280;margin-top:2px;"><?php echo esc_html($r['created_at']); ?></div>
                        </td>
                        <td>
                            <?php if (!empty($r['nomor_surat'])) : ?>
                                <strong style="font-family:monospace;font-size:13px;color:<?php echo $is_frozen ? '#075985;text-decoration:line-through' : ($is_reserved ? '#5B21B6' : '#0D3B22'); ?>;"><?php echo esc_html($r['nomor_surat']); ?></strong>
                            <?php else : ?>
                                <span style="color:#9CA3AF;font-style:italic;">Belum terbit</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo esc_html($r['pemohon_nama']); ?></strong>
                            <span style="font-size:10.5px;background:#E8F0EA;color:#0D3B22;padding:1px 6px;border-radius:4px;font-weight:700;"><?php echo esc_html(strtoupper($r['pemohon_peran'])); ?></span>
                            <div style="font-size:11.5px;color:#4B5563;"><?php echo esc_html($r['pemohon_identitas']); ?> &bull; <?php echo esc_html(strtoupper($r['unit_jenjang'])); ?></div>
                            <?php if (!empty($r['pemohon_email'])) : ?>
                                <div style="font-size:11px;"><a href="mailto:<?php echo esc_attr($r['pemohon_email']); ?>" style="color:#6B7280;text-decoration:none;"><?php echo esc_html($r['pemohon_email']); ?></a></div>
                            <?php endif; ?>
                            <?php if ($wa_digits !== '') :
                                $wa_msg = ($r['status'] === 'approved' && !empty($r['nomor_surat']))
                                    ? sprintf("Halo %s, pengajuan %s (%s) Anda telah terbit dengan Nomor Surat: %s", $r['pemohon_nama'], $r['jenis_surat_nama'], $tc, $r['nomor_surat'])
                                    : sprintf("Halo %s, terkait pengajuan %s (%s) Anda di BK FIP UNM.", $r['pemohon_nama'], $r['jenis_surat_nama'], $tc);
                            ?>
                                <div style="font-size:11px;margin-top:2px;"><a href="<?php echo esc_url('https://wa.me/' . $wa_digits . '?text=' . rawurlencode($wa_msg)); ?>" target="_blank" rel="noopener noreferrer" style="color:#059669;font-weight:700;text-decoration:none;">💬 WA: <?php echo esc_html($wa_raw); ?> &#8599;</a></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong>[<?php echo esc_html($r['kode_hal']); ?>] <?php echo esc_html($r['jenis_surat_nama']); ?></strong>
                            <div style="font-size:12px;color:#374151;margin-top:2px;"><?php echo esc_html($r['perihal_tujuan']); ?></div>
                            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:3px;">
                                <?php if (!empty($r['link_berkas'])) : ?>
                                    <a href="<?php echo esc_url($r['link_berkas']); ?>" target="_blank" rel="noopener noreferrer" style="font-size:11.5px;font-weight:700;">📎 Berkas Syarat &#8599;</a>
                                <?php endif; ?>
                                <?php if (!empty($r['link_arsip_final'])) : ?>
                                    <a href="<?php echo esc_url($r['link_arsip_final']); ?>" target="_blank" rel="noopener noreferrer" style="font-size:11.5px;font-weight:700;color:#059669;">📄 Arsip PDF Final &#8599;</a>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($r['audit_log'])) : ?>
                                <details style="margin-top:4px;"><summary style="font-size:10.5px;color:#6B7280;cursor:pointer;">🕒 Riwayat Audit</summary><pre style="font-size:10px;color:#4B5563;white-space:pre-wrap;margin:3px 0 0;background:#F8FAFC;padding:4px 6px;border-radius:4px;"><?php echo esc_html($r['audit_log']); ?></pre></details>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($r['status'] === 'approved') : ?>
                                <span style="background:#D1FAE5;color:#065F46;padding:3px 8px;border-radius:6px;font-size:11px;font-weight:700;">Terbit</span>
                            <?php elseif ($r['status'] === 'reserved') : ?>
                                <span style="background:#EDE9FE;color:#5B21B6;padding:3px 8px;border-radius:6px;font-size:11px;font-weight:700;">📌 Reservasi</span>
                            <?php elseif ($r['status'] === 'frozen') : ?>
                                <span style="background:#E0F2FE;color:#075985;padding:3px 8px;border-radius:6px;font-size:11px;font-weight:700;">❄️ Freeze</span>
                            <?php elseif ($r['status'] === 'rejected') : ?>
                                <span style="background:#FEE2E2;color:#991B1B;padding:3px 8px;border-radius:6px;font-size:11px;font-weight:700;">Ditolak</span>
                            <?php else : ?>
                                <span style="background:#FEF3C7;color:#92400E;padding:3px 8px;border-radius:6px;font-size:11px;font-weight:700;">Pending</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($is_pending && !isset($rendered_pending_batches[$tc])) :
                                $rendered_pending_batches[$tc] = true;
                            ?>
                                <form method="post" style="display:flex;flex-direction:column;gap:5px;margin-bottom:6px;">
                                    <?php wp_nonce_field('ppb_surat_admin_action', 'ppb_surat_admin_nonce'); ?>
                                    <input type="hidden" name="tracking_code" value="<?php echo esc_attr($tc); ?>">
                                    <input type="text" name="catatan_admin" placeholder="Catatan / alasan penolakan" style="font-size:11.5px;">
                                    <input type="number" name="manual_start_seq" min="1" placeholder="Override No. Urut (opsional)" style="font-size:11.5px;">
                                    <div style="display:flex;gap:5px;">
                                        <button type="submit" name="ppb_surat_action" value="approve_batch" class="button button-primary button-small" style="flex:1;">
                                            ✓ Setujui<?php echo $b_total > 1 ? " ({$b_total})" : ''; ?>
                                        </button>
                                        <button type="submit" name="ppb_surat_action" value="reject_batch" class="button button-small" style="color:#991B1B;">Tolak</button>
                                    </div>
                                </form>
                            <?php elseif ($is_pending) : ?>
                                <div style="font-size:11.5px;color:#6B7280;margin-bottom:5px;">Batch <code><?php echo esc_html($tc); ?></code></div>
                            <?php elseif (!empty($r['catatan_admin'])) : ?>
                                <div style="font-size:11.5px;color:#6B7280;margin-bottom:5px;"><?php echo esc_html($r['catatan_admin']); ?></div>
                            <?php endif; ?>

                            <?php if ($is_reserved) : ?>
                                <details style="margin-bottom:6px;background:#F5F3FF;padding:5px 7px;border-radius:6px;border:1px solid #DDD6FE;">
                                    <summary style="font-size:11px;font-weight:700;color:#5B21B6;cursor:pointer;">✏️ Assign ke Pemohon</summary>
                                    <form method="post" style="display:flex;flex-direction:column;gap:4px;margin-top:5px;">
                                        <?php wp_nonce_field('ppb_surat_admin_action', 'ppb_surat_admin_nonce'); ?>
                                        <input type="hidden" name="row_id" value="<?php echo (int) $r['id']; ?>">
                                        <input type="text" name="a_nama" required placeholder="Nama Pemohon *" style="font-size:11px;">
                                        <input type="text" name="a_identitas" placeholder="NIM / NIP" style="font-size:11px;">
                                        <input type="text" name="a_perihal" placeholder="Perihal / Tujuan" style="font-size:11px;">
                                        <input type="email" name="a_email" placeholder="Email (opsional)" style="font-size:11px;">
                                        <input type="text" name="a_wa" placeholder="No. WA (opsional)" style="font-size:11px;">
                                        <button type="submit" name="ppb_surat_action" value="assign_reserved_row" class="button button-primary button-small">✓ Simpan &amp; Terbitkan</button>
                                    </form>
                                </details>
                            <?php endif; ?>

                            <details style="margin-bottom:6px;">
                                <summary style="font-size:11px;color:#0D3B22;cursor:pointer;font-weight:600;">📄 Tautkan Arsip PDF Final</summary>
                                <form method="post" style="display:flex;gap:4px;margin-top:4px;">
                                    <?php wp_nonce_field('ppb_surat_admin_action', 'ppb_surat_admin_nonce'); ?>
                                    <input type="hidden" name="row_id" value="<?php echo (int) $r['id']; ?>">
                                    <input type="url" name="link_arsip_final" value="<?php echo esc_attr($r['link_arsip_final'] ?? ''); ?>" placeholder="https://drive.google.com/..." style="font-size:11px;flex:1;min-width:0;">
                                    <button type="submit" name="ppb_surat_action" value="save_arsip_row" class="button button-small">Simpan</button>
                                </form>
                            </details>

                            <form method="post" style="display:flex;gap:5px;flex-wrap:wrap;">
                                <?php wp_nonce_field('ppb_surat_admin_action', 'ppb_surat_admin_nonce'); ?>
                                <input type="hidden" name="row_id" value="<?php echo (int) $r['id']; ?>">
                                <input type="hidden" name="tracking_code" value="<?php echo esc_attr($tc); ?>">
                                <?php if ($is_frozen || $is_reserved) : ?>
                                    <button type="submit" name="ppb_surat_action" value="unfreeze_row" class="button button-small" style="color:#065F46;">✓ Aktifkan</button>
                                <?php endif; ?>
                                <?php if (!$is_frozen) : ?>
                                    <button type="submit" name="ppb_surat_action" value="freeze_row" class="button button-small" style="color:#075985;">❄️ Freeze</button>
                                <?php endif; ?>
                                <button type="submit" name="ppb_surat_action" value="delete_row" class="button button-small" style="color:#991B1B;" onclick="return confirm('Yakin hapus data nomor surat ini?');">🗑️ Hapus</button>
                                <?php if ($b_total > 1) : ?>
                                    <button type="submit" name="ppb_surat_action" value="delete_batch" class="button button-small" style="color:#991B1B;border-color:#FCA5A5;background:#FEF2F2;" onclick="return confirm('Yakin hapus SELURUH batch <?php echo esc_js($tc); ?> (<?php echo $b_total; ?> nomor)?');">🗑️ Hapus Batch (<?php echo $b_total; ?>)</button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>

        <?php elseif ($tab === 'jenis') :
            $jenis_list = ppb_surat_get_jenis_list(false);
        ?>
            <form method="post">
                <?php wp_nonce_field('ppb_surat_admin_action', 'ppb_surat_admin_nonce'); ?>
                <input type="hidden" name="ppb_surat_action" value="save_jenis">
                <p class="description">Atur jenis surat yang tersedia di formulir publik, kode klasifikasi UNM (`PP`, `PT`, `KM`, `LL`), serta apakah nomor terbit otomatis atau menunggu persetujuan Admin/TU.</p>
                <table class="wp-list-table widefat fixed striped" id="ppb-jenis-table" style="margin:12px 0;">
                    <thead>
                        <tr>
                            <th style="width:120px;">ID Slug</th>
                            <th>Nama Jenis Surat</th>
                            <th style="width:85px;">Kode Hal</th>
                            <th style="width:150px;">Mode Penerbitan</th>
                            <th style="width:135px;">Link Berkas</th>
                            <th>Deskripsi Singkat</th>
                            <th style="width:65px;">Aktif</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($jenis_list as $idx => $j) : ?>
                        <tr>
                            <td><input type="text" name="j_id[<?php echo $idx; ?>]" value="<?php echo esc_attr($j['id']); ?>" class="regular-text" style="width:100%;"></td>
                            <td><input type="text" name="j_nama[<?php echo $idx; ?>]" value="<?php echo esc_attr($j['nama']); ?>" class="regular-text" style="width:100%;"></td>
                            <td><input type="text" name="j_hal[<?php echo $idx; ?>]" value="<?php echo esc_attr($j['kode_hal']); ?>" style="width:100%;text-transform:uppercase;"></td>
                            <td>
                                <select name="j_mode[<?php echo $idx; ?>]" style="width:100%;">
                                    <option value="auto" <?php selected($j['mode'], 'auto'); ?>>Otomatis</option>
                                    <option value="approval" <?php selected($j['mode'], 'approval'); ?>>Perlu Approval</option>
                                </select>
                            </td>
                            <td>
                                <select name="j_berkas[<?php echo $idx; ?>]" style="width:100%;">
                                    <option value="required" <?php selected($j['berkas_mode'], 'required'); ?>>Wajib</option>
                                    <option value="optional" <?php selected($j['berkas_mode'], 'optional'); ?>>Opsional</option>
                                    <option value="hidden" <?php selected($j['berkas_mode'], 'hidden'); ?>>Sembunyikan</option>
                                </select>
                            </td>
                            <td><input type="text" name="j_desc[<?php echo $idx; ?>]" value="<?php echo esc_attr($j['deskripsi']); ?>" style="width:100%;"></td>
                            <td style="text-align:center;"><input type="checkbox" name="j_active[<?php echo $idx; ?>]" value="1" <?php checked(!empty($j['active'])); ?>></td>
                        </tr>
                    <?php endforeach; ?>
                        <tr style="background:#F0F6F2;">
                            <td><input type="text" name="j_id[new]" value="" placeholder="otomatis" style="width:100%;"></td>
                            <td><input type="text" name="j_nama[new]" value="" placeholder="+ Tambah Jenis Surat Baru..." style="width:100%;"></td>
                            <td><input type="text" name="j_hal[new]" value="PP" style="width:100%;text-transform:uppercase;"></td>
                            <td>
                                <select name="j_mode[new]" style="width:100%;">
                                    <option value="approval">Perlu Approval</option>
                                    <option value="auto">Otomatis</option>
                                </select>
                            </td>
                            <td>
                                <select name="j_berkas[new]" style="width:100%;">
                                    <option value="optional">Opsional</option>
                                    <option value="required">Wajib</option>
                                    <option value="hidden">Sembunyikan</option>
                                </select>
                            </td>
                            <td><input type="text" name="j_desc[new]" value="" placeholder="Keterangan singkat" style="width:100%;"></td>
                            <td style="text-align:center;"><input type="checkbox" name="j_active[new]" value="1" checked></td>
                        </tr>
                    </tbody>
                </table>
                <button type="submit" class="button button-primary">💾 Simpan Master Jenis Surat</button>
            </form>

        <?php else :
            $cur_offset = isset($settings['offsets'][(string) $cur_year]) ? (int) $settings['offsets'][(string) $cur_year] : 0;
        ?>
            <form method="post" style="background:#fff;border:1px solid #D4E3D8;border-radius:12px;padding:20px;max-width:720px;">
                <?php wp_nonce_field('ppb_surat_admin_action', 'ppb_surat_admin_nonce'); ?>
                <input type="hidden" name="ppb_surat_action" value="save_settings">
                <table class="form-table">
                    <tr>
                        <th>Prefix &amp; Kode Unit</th>
                        <td>
                            <input type="text" name="prefix" value="<?php echo esc_attr($settings['prefix']); ?>" style="width:70px;"> / [No] /
                            <input type="text" name="kode_unit" value="<?php echo esc_attr($settings['kode_unit']); ?>" style="width:110px;"> / [Kode Hal] / [Tahun]
                        </td>
                    </tr>
                    <tr>
                        <th>Digit Nomor Urut</th>
                        <td><input type="number" name="pad_digits" min="1" max="6" value="<?php echo (int) $settings['pad_digits']; ?>" style="width:80px;"> <span class="description">Contoh: 3 digit &rarr; 001</span></td>
                    </tr>
                    <tr>
                        <th>Offset Nomor Awal (Tahun <?php echo $cur_year; ?>)</th>
                        <td><input type="number" name="offset_current_year" min="0" value="<?php echo (int) $cur_offset; ?>" style="width:110px;"> <span class="description">Isi jika sudah ada nomor surat keluar sebelumnya dari buku manual (mis. 45 agar nomor berikutnya mulai dari 046).</span></td>
                    </tr>
                    <tr>
                        <th>Maks. Batch Dosen</th>
                        <td><input type="number" name="max_batch_dosen" min="2" max="200" value="<?php echo (int) $settings['max_batch_dosen']; ?>" style="width:80px;"> <span class="description">Maksimal nomor sekaligus dalam 1 pengajuan Dosen.</span></td>
                    </tr>
                    <tr>
                        <th>Validasi NIP Dosen</th>
                        <td><label><input type="checkbox" name="validate_dosen_nip" value="1" <?php checked(!empty($settings['validate_dosen_nip'])); ?>> Wajibkan NIP cocok dengan direktori Dosen (<code>ppb_dosen</code>) saat meminta &gt;1 nomor sekaligus</label></td>
                    </tr>
                </table>
                <button type="submit" class="button button-primary">💾 Simpan Pengaturan</button>
            </form>
        <?php endif; ?>
    </div>
    <?php
}
}
