<?php
/**
 * Module: PPB FIP UNM - Manajemen Mata Kuliah OBE, RPS/SAP & Auto-Sync Dosen
 * Description: CRUD terpusat Mata Kuliah (S1/S2/S3), unggah/tautan RPS & SAP, penautan multi-dosen
 *              dengan sinkronisasi otomatis ke Direktori Dosen, Dossier Drawer, Halaman Profil Dosen,
 *              Halaman /akademik/kurikulum/, serta shortcode [ppb_kurikulum_rps_sap].
 * Author: Jurusan PPB FIP UNM
 */

if (!defined('ABSPATH')) exit;

/* ========================================================================== */
/* 1. STORAGE, SEEDING & NORMALIZATION HELPERS                                */
/* ========================================================================== */

if (!function_exists('ppb_matkul_normalize_text')) {
function ppb_matkul_normalize_text($str) {
    $s = strtolower(trim((string) $str));
    $s = str_replace(array('&amp;', '&'), ' dan ', $s);
    $s = preg_replace('/[^a-z0-9\s]/', ' ', $s);
    $s = preg_replace('/\s+/', ' ', $s);
    return trim($s);
}
}

if (!function_exists('ppb_generate_matkul_id')) {
function ppb_generate_matkul_id($kode, $jenjang = 'S1', $nama = '') {
    $clean_j = strtolower(preg_replace('/[^a-z0-9]/i', '', (string) ($jenjang ?: 's1')));
    $clean_k = strtolower(preg_replace('/[^a-z0-9]/i', '', (string) ($kode ?: 'mk')));
    $hash    = substr(md5(ppb_matkul_normalize_text($nama ?: uniqid('mk', true))), 0, 6);
    return 'mk_' . $clean_j . '_' . $clean_k . '_' . $hash;
}
}

if (!function_exists('ppb_matkul_infer_semester')) {
function ppb_matkul_infer_semester($kode, $jenjang = 'S1') {
    if (preg_match('/(\d{3})/', (string) $kode, $m)) {
        $digit = (int) substr($m[1], 0, 1);
        if (stripos((string) $jenjang, 'S2') !== false || stripos((string) $jenjang, 'S3') !== false) {
            if ($digit === 7 || $digit === 9) return 1;
            if ($digit === 8) return 2;
        }
        if ($digit >= 1 && $digit <= 8) {
            return $digit;
        }
    }
    return 1;
}
}

if (!function_exists('ppb_matkul_degrees_for_kurikulum')) {
function ppb_matkul_degrees_for_kurikulum($jenjang) {
    $j = strtoupper(trim((string) $jenjang));
    if ($j === 'S2/S3' || $j === 'S2 / S3') {
        return array('s2', 's3');
    }
    if (strpos($j, 'S3') !== false) return array('s3');
    if (strpos($j, 'S2') !== false) return array('s2');
    return array('s1');
}
}

if (!function_exists('ppb_matkul_sanitize_course')) {
function ppb_matkul_sanitize_course($raw, $existing = array()) {
    $kode     = strtoupper(sanitize_text_field($raw['kode'] ?? ($existing['kode'] ?? '')));
    $nama     = sanitize_text_field($raw['nama'] ?? ($existing['nama'] ?? ''));
    $jenjang  = sanitize_text_field($raw['jenjang'] ?? ($existing['jenjang'] ?? 'S1'));
    $allowed_j = array('S1', 'S2', 'S3', 'S2/S3');
    if (!in_array($jenjang, $allowed_j, true)) {
        $jenjang = 'S1';
    }

    $id = !empty($raw['id'])
        ? sanitize_key($raw['id'])
        : (!empty($existing['id']) ? sanitize_key($existing['id']) : ppb_generate_matkul_id($kode, $jenjang, $nama));

    $sem = isset($raw['semester']) ? (int) $raw['semester'] : (isset($raw['sem']) ? (int) $raw['sem'] : (int) ($existing['semester'] ?? 1));
    if ($sem < 1) $sem = 1;
    if ($sem > 8) $sem = 8;

    $sks = isset($raw['sks']) ? (int) $raw['sks'] : (int) ($existing['sks'] ?? 3);
    if ($sks < 0) $sks = 0;
    if ($sks > 12) $sks = 12;

    $raw_slugs = isset($raw['dosen_slugs']) && is_array($raw['dosen_slugs'])
        ? $raw['dosen_slugs']
        : ($existing['dosen_slugs'] ?? array());
    $clean_slugs = array();
    foreach ($raw_slugs as $sl) {
        $sl = sanitize_title($sl);
        if ($sl !== '' && !in_array($sl, $clean_slugs, true)) {
            $clean_slugs[] = $sl;
        }
    }

    return array(
        'id'            => $id,
        'kode'          => $kode,
        'nama'          => $nama,
        'sks'           => $sks,
        'jenjang'       => $jenjang,
        'semester'      => $sem,
        'sem_title'     => sanitize_text_field($raw['sem_title'] ?? ($existing['sem_title'] ?? '')),
        'rumpun'        => sanitize_text_field($raw['rumpun'] ?? ($existing['rumpun'] ?? 'Wajib Prodi')),
        'sdg'           => sanitize_text_field($raw['sdg'] ?? ($existing['sdg'] ?? 'SDG 4')),
        'rps_url'       => function_exists('ppb_clean_document_url') ? ppb_clean_document_url($raw['rps_url'] ?? ($existing['rps_url'] ?? '')) : esc_url_raw($raw['rps_url'] ?? ''),
        'sap_url'       => function_exists('ppb_clean_document_url') ? ppb_clean_document_url($raw['sap_url'] ?? ($existing['sap_url'] ?? '')) : esc_url_raw($raw['sap_url'] ?? ''),
        'cpmk'          => sanitize_textarea_field($raw['cpmk'] ?? ($existing['cpmk'] ?? '')),
        'dosen_slugs'   => $clean_slugs,
        'in_kurikulum'  => isset($raw['in_kurikulum']) ? (bool) $raw['in_kurikulum'] : (isset($existing['in_kurikulum']) ? (bool) $existing['in_kurikulum'] : true),
        'updated_at'    => current_time('mysql'),
    );
}
}

if (!function_exists('ppb_sort_mata_kuliah_list')) {
function ppb_sort_mata_kuliah_list(&$courses) {
    if (!is_array($courses)) return;
    $deg_rank = array('S1' => 1, 'S2' => 2, 'S2/S3' => 3, 'S3' => 4);
    usort($courses, function($a, $b) use ($deg_rank) {
        $ra = $deg_rank[$a['jenjang'] ?? 'S1'] ?? 9;
        $rb = $deg_rank[$b['jenjang'] ?? 'S1'] ?? 9;
        if ($ra !== $rb) return $ra - $rb;
        $sa = (int) ($a['semester'] ?? 1);
        $sb = (int) ($b['semester'] ?? 1);
        if ($sa !== $sb) return $sa - $sb;
        $ka = strcmp((string) ($a['kode'] ?? ''), (string) ($b['kode'] ?? ''));
        if ($ka !== 0) return $ka;
        return strcmp((string) ($a['nama'] ?? ''), (string) ($b['nama'] ?? ''));
    });
}
}

if (!function_exists('ppb_get_mata_kuliah_default_seed')) {
function ppb_get_mata_kuliah_default_seed() {
    $theme_dir = function_exists('get_template_directory') ? get_template_directory() : (dirname(__DIR__, 2) . '/themes/bk-fip-wordpress');
    $courses = array();
    $idx_by_id = array();

    // 1. Seed core curriculum from pages-data.json (slug === 'kurikulum' -> #bk-kur-data)
    $pages_file = $theme_dir . '/pages-data.json';
    if (file_exists($pages_file)) {
        $pages = json_decode((string) file_get_contents($pages_file), true);
        if (is_array($pages)) {
            foreach ($pages as $p) {
                if (($p['slug'] ?? '') !== 'kurikulum') continue;
                $content = (string) ($p['content'] ?? '');
                if (preg_match('/<script\b[^>]*\bid=["\']bk-kur-data["\'][^>]*>(.*?)<\/script>/is', $content, $m)) {
                    $kur_json = json_decode(trim($m[1]), true);
                    if (is_array($kur_json) && !empty($kur_json['courses']) && is_array($kur_json['courses'])) {
                        $deg_labels = array('s1' => 'S1', 's2' => 'S2', 's3' => 'S3');
                        foreach ($kur_json['courses'] as $deg_key => $arr) {
                            if (!is_array($arr)) continue;
                            $jenjang = $deg_labels[strtolower($deg_key)] ?? 'S1';
                            foreach ($arr as $row) {
                                $kode = strtoupper(trim((string) ($row['kode'] ?? '')));
                                $nama = trim((string) ($row['nama'] ?? ''));
                                if ($kode === '' || $nama === '') continue;
                                $id = ppb_generate_matkul_id($kode, $jenjang, $nama);
                                $courses[] = array(
                                    'id'           => $id,
                                    'kode'         => $kode,
                                    'nama'         => $nama,
                                    'sks'          => (int) ($row['sks'] ?? 3),
                                    'jenjang'      => $jenjang,
                                    'semester'     => max(1, min(8, (int) ($row['sem'] ?? 1))),
                                    'sem_title'    => (string) ($row['semTitle'] ?? ''),
                                    'rumpun'       => (string) ($row['rumpun'] ?? 'Wajib Prodi'),
                                    'sdg'          => (string) ($row['sdg'] ?? 'SDG 4'),
                                    'rps_url'      => '',
                                    'sap_url'      => '',
                                    'cpmk'         => '',
                                    'dosen_slugs'  => array(),
                                    'in_kurikulum' => true,
                                    'updated_at'   => current_time('mysql'),
                                );
                                $idx_by_id[$id] = count($courses) - 1;
                            }
                        }
                    }
                }
                break;
            }
        }
    }

    // 2. Merge & preserve all lecturer course mappings from dosen-data.json
    $dosen_list = function_exists('ppb_get_dosen_directory_cached') ? ppb_get_dosen_directory_cached() : array();
    if (is_array($dosen_list)) {
        foreach ($dosen_list as $d) {
            $slug = sanitize_title($d['slug'] ?? '');
            if ($slug === '' || empty($d['mata_kuliah']) || !is_array($d['mata_kuliah'])) continue;

            foreach ($d['mata_kuliah'] as $mk) {
                $kode    = strtoupper(trim((string) ($mk['kode'] ?? '')));
                $nama    = trim((string) ($mk['nama'] ?? ''));
                $jenjang = trim((string) ($mk['jenjang'] ?? 'S1'));
                if ($kode === '' || $nama === '') continue;

                $norm_in = ppb_matkul_normalize_text($nama);
                $matched_idx = null;

                // Try matching exact id or high-similarity core curriculum course
                foreach ($courses as $i => $existing) {
                    $same_jenjang = (strcasecmp($existing['jenjang'], $jenjang) === 0);
                    $same_kode    = (strcasecmp($existing['kode'], $kode) === 0);
                    $norm_ex      = ppb_matkul_normalize_text($existing['nama']);

                    if ($same_jenjang && $same_kode && $norm_ex === $norm_in) {
                        $matched_idx = $i;
                        break;
                    }
                    if (!empty($existing['in_kurikulum']) && $same_jenjang && $same_kode) {
                        $pct = 0.0;
                        similar_text($norm_ex, $norm_in, $pct);
                        $short = strlen($norm_ex) <= strlen($norm_in) ? $norm_ex : $norm_in;
                        $long  = strlen($norm_ex) > strlen($norm_in) ? $norm_ex : $norm_in;
                        if ($pct >= 82.0 || (strlen($short) >= 12 && strpos($long, $short) === 0)) {
                            $matched_idx = $i;
                            break;
                        }
                    }
                }

                if ($matched_idx !== null) {
                    if (!in_array($slug, $courses[$matched_idx]['dosen_slugs'], true)) {
                        $courses[$matched_idx]['dosen_slugs'][] = $slug;
                    }
                    if (!empty($mk['rps_url']) && empty($courses[$matched_idx]['rps_url'])) {
                        $courses[$matched_idx]['rps_url'] = ppb_clean_document_url($mk['rps_url']);
                    }
                    if (!empty($mk['sap_url']) && empty($courses[$matched_idx]['sap_url'])) {
                        $courses[$matched_idx]['sap_url'] = ppb_clean_document_url($mk['sap_url']);
                    }
                } else {
                    $new_id = ppb_generate_matkul_id($kode, $jenjang, $nama);
                    if (isset($idx_by_id[$new_id])) {
                        $i = $idx_by_id[$new_id];
                        if (!in_array($slug, $courses[$i]['dosen_slugs'], true)) {
                            $courses[$i]['dosen_slugs'][] = $slug;
                        }
                    } else {
                        $courses[] = array(
                            'id'           => $new_id,
                            'kode'         => $kode,
                            'nama'         => $nama,
                            'sks'          => (int) ($mk['sks'] ?? 3),
                            'jenjang'      => in_array($jenjang, array('S1', 'S2', 'S3', 'S2/S3'), true) ? $jenjang : 'S1',
                            'semester'     => !empty($mk['semester']) ? (int) $mk['semester'] : ppb_matkul_infer_semester($kode, $jenjang),
                            'sem_title'    => '',
                            'rumpun'       => (string) ($mk['sifat'] ?? 'Wajib Keilmuan'),
                            'sdg'          => 'SDG 4',
                            'rps_url'      => function_exists('ppb_clean_document_url') ? ppb_clean_document_url($mk['rps_url'] ?? '') : '',
                            'sap_url'      => function_exists('ppb_clean_document_url') ? ppb_clean_document_url($mk['sap_url'] ?? '') : '',
                            'cpmk'         => '',
                            'dosen_slugs'  => array($slug),
                            'in_kurikulum' => false,
                            'updated_at'   => current_time('mysql'),
                        );
                        $idx_by_id[$new_id] = count($courses) - 1;
                    }
                }
            }
        }
    }

    ppb_sort_mata_kuliah_list($courses);
    return $courses;
}
}

if (!function_exists('ppb_get_mata_kuliah_list')) {
function ppb_get_mata_kuliah_list($auto_seed = true) {
    $courses = get_option('ppb_mata_kuliah_list', null);
    if (is_array($courses) && !empty($courses)) {
        return $courses;
    }
    if ($auto_seed && get_option('ppb_mata_kuliah_seeded_v1', '') !== 'yes') {
        $seeded = ppb_get_mata_kuliah_default_seed();
        update_option('ppb_mata_kuliah_list', $seeded, false);
        update_option('ppb_mata_kuliah_seeded_v1', 'yes', false);
        return $seeded;
    }
    return is_array($courses) ? $courses : array();
}
}

if (!function_exists('ppb_save_mata_kuliah_list')) {
function ppb_save_mata_kuliah_list($courses) {
    if (!is_array($courses)) return false;
    ppb_sort_mata_kuliah_list($courses);
    update_option('ppb_mata_kuliah_list', array_values($courses), false);
    update_option('ppb_mata_kuliah_seeded_v1', 'yes', false);
    return true;
}
}

/* ========================================================================== */
/* 2. BI-DIRECTIONAL AUTO-SYNC: MATA KULIAH <-> DIREKTORI DOSEN               */
/* ========================================================================== */

if (!function_exists('ppb_sync_mata_kuliah_to_dosen')) {
function ppb_sync_mata_kuliah_to_dosen($courses = null, $target_slugs = null) {
    if ($courses === null) {
        $courses = ppb_get_mata_kuliah_list(true);
    }
    $dosen_list = function_exists('ppb_get_dosen_directory_cached') ? ppb_get_dosen_directory_cached() : array();
    if (!is_array($dosen_list) || empty($dosen_list)) {
        return 0;
    }

    $deg_rank = array('S1' => 1, 'S2' => 2, 'S2/S3' => 3, 'S3' => 4);
    $by_dosen = array();
    foreach ($courses as $mk) {
        if (empty($mk['dosen_slugs']) || !is_array($mk['dosen_slugs'])) continue;
        $entry = array(
            'mk_id'    => (string) ($mk['id'] ?? ''),
            'kode'     => (string) ($mk['kode'] ?? ''),
            'nama'     => (string) ($mk['nama'] ?? ''),
            'sks'      => (int) ($mk['sks'] ?? 3),
            'jenjang'  => (string) ($mk['jenjang'] ?? 'S1'),
            'sifat'    => (string) ($mk['rumpun'] ?? 'Wajib Prodi'),
            'semester' => (int) ($mk['semester'] ?? 1),
            'rps_url'  => (string) ($mk['rps_url'] ?? ''),
            'sap_url'  => (string) ($mk['sap_url'] ?? ''),
        );
        foreach ($mk['dosen_slugs'] as $slug) {
            $slug = sanitize_title($slug);
            if ($slug === '') continue;
            $by_dosen[$slug][] = $entry;
        }
    }

    $target_map = null;
    if (is_array($target_slugs)) {
        $target_map = array();
        foreach ($target_slugs as $ts) {
            $ts = sanitize_title($ts);
            if ($ts !== '') $target_map[$ts] = true;
        }
    }

    $synced_count = 0;
    foreach ($dosen_list as &$d) {
        $d_slug = sanitize_title($d['slug'] ?? '');
        if ($d_slug === '') continue;
        if ($target_map !== null && !isset($target_map[$d_slug])) {
            continue;
        }
        $list = $by_dosen[$d_slug] ?? array();
        usort($list, function($a, $b) use ($deg_rank) {
            $ra = $deg_rank[$a['jenjang'] ?? 'S1'] ?? 9;
            $rb = $deg_rank[$b['jenjang'] ?? 'S1'] ?? 9;
            if ($ra !== $rb) return $ra - $rb;
            $sa = (int) ($a['semester'] ?? 1);
            $sb = (int) ($b['semester'] ?? 1);
            if ($sa !== $sb) return $sa - $sb;
            return strcmp((string) ($a['kode'] ?? ''), (string) ($b['kode'] ?? ''));
        });
        $d['mata_kuliah'] = $list;
        $synced_count++;
    }
    unset($d);

    if (function_exists('ppb_save_dosen_directory_synced')) {
        ppb_save_dosen_directory_synced($dosen_list);
    }

    return $synced_count;
}
}

if (!function_exists('ppb_update_lecturer_courses')) {
function ppb_update_lecturer_courses($dosen_slug, $selected_mk_ids) {
    $dosen_slug = sanitize_title($dosen_slug);
    if ($dosen_slug === '') return array();

    $selected_map = array();
    if (is_array($selected_mk_ids)) {
        foreach ($selected_mk_ids as $mid) {
            $mid = sanitize_key($mid);
            if ($mid !== '') $selected_map[$mid] = true;
        }
    }

    $courses = ppb_get_mata_kuliah_list(true);
    foreach ($courses as &$mk) {
        $mid = sanitize_key($mk['id'] ?? '');
        $slugs = isset($mk['dosen_slugs']) && is_array($mk['dosen_slugs']) ? $mk['dosen_slugs'] : array();
        $slugs = array_values(array_filter(array_unique(array_map('sanitize_title', $slugs))));

        $has_now = in_array($dosen_slug, $slugs, true);
        $should_have = isset($selected_map[$mid]);

        if ($should_have && !$has_now) {
            $slugs[] = $dosen_slug;
        } elseif (!$should_have && $has_now) {
            $slugs = array_values(array_diff($slugs, array($dosen_slug)));
        }
        $mk['dosen_slugs'] = $slugs;
    }
    unset($mk);

    ppb_save_mata_kuliah_list($courses);
    ppb_sync_mata_kuliah_to_dosen($courses, array($dosen_slug));

    $updated_for_dosen = array();
    foreach ($courses as $mk) {
        if (!empty($mk['dosen_slugs']) && in_array($dosen_slug, $mk['dosen_slugs'], true)) {
            $updated_for_dosen[] = array(
                'mk_id'    => (string) $mk['id'],
                'kode'     => (string) $mk['kode'],
                'nama'     => (string) $mk['nama'],
                'sks'      => (int) $mk['sks'],
                'jenjang'  => (string) $mk['jenjang'],
                'sifat'    => (string) $mk['rumpun'],
                'semester' => (int) $mk['semester'],
                'rps_url'  => (string) ($mk['rps_url'] ?? ''),
                'sap_url'  => (string) ($mk['sap_url'] ?? ''),
            );
        }
    }
    return $updated_for_dosen;
}
}

/* ========================================================================== */
/* 3. HALAMAN ADMIN: MANAJEMEN MATA KULIAH OBE & LINK DOSEN                   */
/* ========================================================================== */

if (!function_exists('ppb_render_mata_kuliah_admin_page')) {
function ppb_render_mata_kuliah_admin_page() {
    if (!current_user_can('edit_posts')) {
        wp_die('Anda tidak memiliki izin mengakses halaman ini.');
    }

    if (function_exists('ppb_admin_enqueue_media_assets')) {
        ppb_admin_enqueue_media_assets();
    }

    $courses    = ppb_get_mata_kuliah_list(true);
    $dosen_list = function_exists('ppb_get_dosen_directory_cached') ? ppb_get_dosen_directory_cached() : array();
    $dosen_map  = array();
    foreach ($dosen_list as $d) {
        $sl = sanitize_title($d['slug'] ?? '');
        if ($sl !== '') {
            $dosen_map[$sl] = array(
                'slug' => $sl,
                'nama' => (string) ($d['nama_lengkap'] ?? ($d['nama'] ?? $sl)),
                'unit' => (string) ($d['unit'] ?? ''),
            );
        }
    }

    $notice = '';

    // Handle Inline Bulk Excel / JSON Import
    if (function_exists('ppb_excel_handle_inline_upload')) {
        $xl_notice = ppb_excel_handle_inline_upload('matkul');
        if ($xl_notice !== '') {
            $notice  = $xl_notice;
            $courses = ppb_get_mata_kuliah_list(false);
        }
    }

    // Handle Save / Add / Edit Course
    if (isset($_POST['ppb_save_matkul_submit']) && check_admin_referer('ppb_matkul_save_action', 'ppb_matkul_nonce')) {
        $edit_id = sanitize_key($_POST['mk_id'] ?? '');
        $existing_idx = null;
        $existing_row = array();
        if ($edit_id !== '') {
            foreach ($courses as $idx => $c) {
                if (($c['id'] ?? '') === $edit_id) {
                    $existing_idx = $idx;
                    $existing_row = $c;
                    break;
                }
            }
        }

        $old_slugs = isset($existing_row['dosen_slugs']) && is_array($existing_row['dosen_slugs']) ? $existing_row['dosen_slugs'] : array();
        $posted_slugs = isset($_POST['mk_dosen_slugs']) && is_array($_POST['mk_dosen_slugs']) ? array_map('wp_unslash', $_POST['mk_dosen_slugs']) : array();

        $sanitized = ppb_matkul_sanitize_course(array(
            'id'           => $edit_id,
            'kode'         => wp_unslash($_POST['mk_kode'] ?? ''),
            'nama'         => wp_unslash($_POST['mk_nama'] ?? ''),
            'sks'          => $_POST['mk_sks'] ?? 3,
            'jenjang'      => wp_unslash($_POST['mk_jenjang'] ?? 'S1'),
            'semester'     => $_POST['mk_semester'] ?? 1,
            'sem_title'    => wp_unslash($_POST['mk_sem_title'] ?? ''),
            'rumpun'       => wp_unslash($_POST['mk_rumpun'] ?? 'Wajib Prodi'),
            'sdg'          => wp_unslash($_POST['mk_sdg'] ?? 'SDG 4'),
            'rps_url'      => wp_unslash($_POST['mk_rps_url'] ?? ''),
            'sap_url'      => wp_unslash($_POST['mk_sap_url'] ?? ''),
            'cpmk'         => wp_unslash($_POST['mk_cpmk'] ?? ''),
            'dosen_slugs'  => $posted_slugs,
            'in_kurikulum' => !empty($_POST['mk_in_kurikulum']),
        ), $existing_row);

        if ($sanitized['kode'] === '' || $sanitized['nama'] === '') {
            $notice = '<div class="notice notice-error is-dismissible"><p><strong>Kode MK dan Nama Mata Kuliah wajib diisi.</strong></p></div>';
        } else {
            if ($existing_idx !== null) {
                $courses[$existing_idx] = $sanitized;
                $action_lbl = 'diperbarui';
            } else {
                $courses[] = $sanitized;
                $action_lbl = 'ditambahkan';
            }

            ppb_save_mata_kuliah_list($courses);
            $affected_slugs = array_values(array_unique(array_merge($old_slugs, $sanitized['dosen_slugs'])));
            $synced_n = !empty($affected_slugs) ? ppb_sync_mata_kuliah_to_dosen($courses, $affected_slugs) : 0;

            $notice = '<div class="notice notice-success is-dismissible"><p><strong>✅ Mata Kuliah ' . esc_html($sanitized['kode'] . ' — ' . $sanitized['nama']) . ' berhasil ' . esc_html($action_lbl) . '!</strong> (' . (int) $synced_n . ' profil dosen tersinkron otomatis ke direktori &amp; halaman kurikulum)</p></div>';
            $courses = ppb_get_mata_kuliah_list(false);
        }
    }

    // Handle Delete Course
    if (isset($_POST['ppb_delete_matkul_submit']) && check_admin_referer('ppb_matkul_delete_action', 'ppb_matkul_delete_nonce')) {
        $del_id = sanitize_key($_POST['delete_mk_id'] ?? '');
        $affected_slugs = array();
        $deleted_label = '';
        $new_courses = array();
        foreach ($courses as $c) {
            if (($c['id'] ?? '') === $del_id) {
                $deleted_label = ($c['kode'] ?? '') . ' — ' . ($c['nama'] ?? '');
                $affected_slugs = is_array($c['dosen_slugs'] ?? null) ? $c['dosen_slugs'] : array();
                continue;
            }
            $new_courses[] = $c;
        }
        if ($deleted_label !== '') {
            ppb_save_mata_kuliah_list($new_courses);
            $synced_n = !empty($affected_slugs) ? ppb_sync_mata_kuliah_to_dosen($new_courses, $affected_slugs) : 0;
            $notice = '<div class="notice notice-success is-dismissible"><p><strong>🗑️ Mata Kuliah ' . esc_html($deleted_label) . ' dihapus.</strong> (' . (int) $synced_n . ' profil dosen diperbarui otomatis)</p></div>';
            $courses = ppb_get_mata_kuliah_list(false);
        }
    }

    // Handle Sync All MK -> All Dosen
    if (isset($_POST['ppb_sync_all_matkul_submit']) && check_admin_referer('ppb_matkul_sync_all_action', 'ppb_matkul_sync_all_nonce')) {
        $synced_n = ppb_sync_mata_kuliah_to_dosen($courses, null);
        $notice = '<div class="notice notice-success is-dismissible"><p><strong>🔄 Sinkronisasi Massal Selesai!</strong> Daftar mata kuliah untuk <strong>' . (int) $synced_n . ' dosen</strong> telah dibangun ulang ke <code>dosen-data.json</code>, Dossier Drawer, dan halaman profil publik.</p></div>';
    }

    // Metrics & Filter State
    $tot_mk       = count($courses);
    $tot_with_rps = 0;
    $tot_linked   = 0;
    foreach ($courses as $c) {
        if (!empty($c['rps_url']) || !empty($c['sap_url'])) $tot_with_rps++;
        if (!empty($c['dosen_slugs'])) $tot_linked++;
    }

    $f_deg   = sanitize_text_field($_GET['deg'] ?? '');
    $f_sem   = sanitize_text_field($_GET['sem'] ?? '');
    $f_dosen = sanitize_title($_GET['dosen'] ?? '');
    $f_q     = trim(sanitize_text_field($_GET['q'] ?? ''));

    $filtered = array();
    foreach ($courses as $c) {
        if ($f_deg !== '' && strcasecmp($c['jenjang'] ?? '', $f_deg) !== 0) continue;
        if ($f_sem !== '' && (int) ($c['semester'] ?? 1) !== (int) $f_sem) continue;
        if ($f_dosen !== '' && (!is_array($c['dosen_slugs'] ?? null) || !in_array($f_dosen, $c['dosen_slugs'], true))) continue;
        if ($f_q !== '') {
            $hay = strtolower(($c['kode'] ?? '') . ' ' . ($c['nama'] ?? '') . ' ' . ($c['rumpun'] ?? ''));
            if (strpos($hay, strtolower($f_q)) === false) continue;
        }
        $filtered[] = $c;
    }
    ?>
    <div class="wrap ppb-mk-wrap" style="max-width:1280px;margin:20px auto;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;color:#1e293b;">
        <style>
            .ppb-mk-hero { background: linear-gradient(135deg, #0D3B22 0%, #185333 100%); color:#fff; border-radius:16px; padding:26px 28px; display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:18px; margin-bottom:20px; box-shadow:0 8px 22px rgba(13,59,34,0.16); }
            .ppb-mk-kpis { display:grid; grid-template-columns:repeat(auto-fit, minmax(210px, 1fr)); gap:14px; margin-bottom:20px; }
            .ppb-mk-kpi { background:#fff; border:1px solid #D4E3D8; border-radius:12px; padding:16px 18px; box-shadow:0 2px 6px rgba(0,0,0,0.03); }
            .ppb-mk-kpi small { display:block; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#64748b; }
            .ppb-mk-kpi strong { display:block; font-size:28px; font-weight:800; color:#0D3B22; margin-top:4px; }
            .ppb-mk-toolbar { background:#fff; border:1px solid #cbd5e1; border-radius:12px; padding:16px 18px; margin-bottom:18px; display:flex; flex-wrap:wrap; gap:10px; align-items:center; justify-content:space-between; }
            .ppb-mk-table-wrap { background:#fff; border:1px solid #cbd5e1; border-radius:14px; overflow:hidden; box-shadow:0 3px 10px rgba(0,0,0,0.04); }
            .ppb-mk-pill { display:inline-block; padding:2px 8px; border-radius:999px; font-size:11px; font-weight:700; }
            .ppb-mk-dosen-chip { display:inline-flex; align-items:center; gap:4px; background:#E8F0EA; color:#0D3B22; border:1px solid #C5DACB; padding:2px 8px; border-radius:6px; font-size:11px; font-weight:600; margin:2px 4px 2px 0; text-decoration:none; }
            .ppb-mk-dosen-picker { border:1px solid #cbd5e1; border-radius:10px; padding:10px; background:#f8fafc; max-height:210px; overflow-y:auto; display:grid; grid-template-columns:repeat(auto-fill, minmax(240px, 1fr)); gap:6px; }
            .ppb-mk-dosen-opt { display:flex; align-items:center; gap:7px; padding:6px 8px; background:#fff; border:1px solid #e2e8f0; border-radius:7px; font-size:12px; cursor:pointer; }
            .ppb-mk-dosen-opt input:checked + span { font-weight:700; color:#0D3B22; }
        </style>

        <div class="ppb-mk-hero">
            <div style="max-width:740px;">
                <span style="display:inline-block;background:rgba(245,184,46,0.2);border:1px solid rgba(245,184,46,0.45);color:#F5B82E;font-size:11px;font-weight:700;padding:3px 12px;border-radius:999px;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;">Kurikulum OBE &amp; Penugasan Dosen</span>
                <h1 style="margin:0 0 6px;color:#fff;font-size:24px;font-weight:800;">📚 Manajemen Mata Kuliah, RPS/SAP &amp; Auto-Sync Dosen</h1>
                <p style="margin:0;color:rgba(255,255,255,0.88);font-size:13.5px;line-height:1.55;">
                    Kelola seluruh mata kuliah S1/S2/S3, unggah PDF atau tautkan Drive RPS &amp; SAP, dan kaitkan langsung ke dosen pengampu. Setiap perubahan otomatis memperbarui <strong>Daftar Mata Kuliah Dosen</strong>, Dossier Drawer, Halaman Profil Dosen, dan <code>/akademik/kurikulum/</code>.
                </p>
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;">
                <button type="button" onclick="ppbOpenMatkulModal(null)" class="button" style="background:#F5B82E;color:#0D3B22;border:none;font-weight:800;padding:9px 18px;height:auto;border-radius:10px;cursor:pointer;">➕ Tambah Mata Kuliah</button>
                <form method="post" action="" style="display:inline;margin:0;">
                    <?php wp_nonce_field('ppb_matkul_sync_all_action', 'ppb_matkul_sync_all_nonce'); ?>
                    <button type="submit" name="ppb_sync_all_matkul_submit" class="button" style="background:rgba(255,255,255,0.16);color:#fff;border:1px solid rgba(255,255,255,0.35);font-weight:700;padding:9px 16px;height:auto;border-radius:10px;cursor:pointer;">🔄 Sync Semua MK &rarr; Dosen</button>
                </form>
                <a href="<?php echo esc_url(home_url('/akademik/kurikulum/')); ?>" target="_blank" class="button" style="background:rgba(255,255,255,0.1);color:#fff;border:1px solid rgba(255,255,255,0.25);font-weight:600;padding:9px 14px;height:auto;border-radius:10px;">↗ Lihat Kurikulum Publik</a>
            </div>
        </div>

        <?php echo $notice; ?>

        <div class="ppb-mk-kpis">
            <div class="ppb-mk-kpi"><small>Total Mata Kuliah</small><strong><?php echo (int) $tot_mk; ?></strong></div>
            <div class="ppb-mk-kpi"><small>Memiliki Tautan RPS / SAP</small><strong style="color:#0369A1;"><?php echo (int) $tot_with_rps; ?></strong></div>
            <div class="ppb-mk-kpi"><small>Terhubung ke Dosen</small><strong style="color:#15803D;"><?php echo (int) $tot_linked; ?></strong></div>
            <div class="ppb-mk-kpi"><small>Total Dosen Terdaftar</small><strong><?php echo count($dosen_map); ?></strong></div>
        </div>

        <?php if (function_exists('ppb_excel_render_inline_widget')) ppb_excel_render_inline_widget('matkul'); ?>

        <!-- Toolbar Filter -->
        <form method="get" action="" class="ppb-mk-toolbar">
            <input type="hidden" name="page" value="ppb-mata-kuliah-manager" />
            <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;flex:1;">
                <input type="search" name="q" value="<?php echo esc_attr($f_q); ?>" placeholder="Cari kode atau nama mata kuliah..." style="min-width:220px;padding:7px 11px;border:1px solid #cbd5e1;border-radius:8px;font-size:13px;" />
                <select name="deg" style="padding:7px 10px;border:1px solid #cbd5e1;border-radius:8px;font-size:13px;">
                    <option value="">Semua Jenjang</option>
                    <option value="S1" <?php selected($f_deg, 'S1'); ?>>S1 Sarjana</option>
                    <option value="S2" <?php selected($f_deg, 'S2'); ?>>S2 Magister</option>
                    <option value="S3" <?php selected($f_deg, 'S3'); ?>>S3 Doktor</option>
                    <option value="S2/S3" <?php selected($f_deg, 'S2/S3'); ?>>S2/S3 Pascasarjana</option>
                </select>
                <select name="sem" style="padding:7px 10px;border:1px solid #cbd5e1;border-radius:8px;font-size:13px;">
                    <option value="">Semua Semester</option>
                    <?php for ($s = 1; $s <= 8; $s++) : ?>
                        <option value="<?php echo $s; ?>" <?php selected((int) $f_sem, $s); ?>>Semester <?php echo $s; ?></option>
                    <?php endfor; ?>
                </select>
                <select name="dosen" style="max-width:260px;padding:7px 10px;border:1px solid #cbd5e1;border-radius:8px;font-size:13px;">
                    <option value="">Semua Dosen Pengampu</option>
                    <?php foreach ($dosen_map as $sl => $dm) : ?>
                        <option value="<?php echo esc_attr($sl); ?>" <?php selected($f_dosen, $sl); ?>><?php echo esc_html($dm['nama']); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="button button-primary" style="background:#0D3B22;border-color:#0D3B22;border-radius:8px;height:35px;">Filter</button>
                <?php if ($f_deg !== '' || $f_sem !== '' || $f_dosen !== '' || $f_q !== '') : ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=ppb-mata-kuliah-manager')); ?>" class="button" style="border-radius:8px;height:35px;line-height:33px;">Reset</a>
                <?php endif; ?>
            </div>
            <div style="font-size:12.5px;color:#64748b;font-weight:600;">
                Menampilkan <strong><?php echo count($filtered); ?></strong> dari <?php echo $tot_mk; ?> MK
            </div>
        </form>

        <!-- Table -->
        <div class="ppb-mk-table-wrap">
            <div style="overflow-x:auto;">
                <table class="wp-list-table widefat striped" style="border:none;">
                    <thead>
                        <tr>
                            <th style="width:105px;font-weight:700;color:#0D3B22;">Kode MK</th>
                            <th style="font-weight:700;color:#0D3B22;">Nama Mata Kuliah &amp; Rumpun</th>
                            <th style="width:110px;text-align:center;font-weight:700;color:#0D3B22;">Jenjang / Sem</th>
                            <th style="width:65px;text-align:center;font-weight:700;color:#0D3B22;">SKS</th>
                            <th style="font-weight:700;color:#0D3B22;">Dosen Pengampu (Tersinkron)</th>
                            <th style="width:150px;font-weight:700;color:#0D3B22;">Berkas RPS / SAP</th>
                            <th style="width:145px;text-align:center;font-weight:700;color:#0D3B22;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($filtered)) : ?>
                            <tr><td colspan="7" style="text-align:center;padding:36px;color:#64748b;">Tidak ada mata kuliah yang sesuai dengan filter.</td></tr>
                        <?php else : foreach ($filtered as $c) :
                            $edit_payload = htmlspecialchars(wp_json_encode($c), ENT_QUOTES, 'UTF-8');
                        ?>
                            <tr>
                                <td style="font-family:monospace;font-weight:800;color:#0D3B22;vertical-align:middle;">
                                    <?php echo esc_html($c['kode']); ?>
                                    <?php if (!empty($c['in_kurikulum'])) : ?>
                                        <div style="font-family:sans-serif;font-size:9.5px;color:#047857;font-weight:700;margin-top:3px;">● Kurikulum</div>
                                    <?php else : ?>
                                        <div style="font-family:sans-serif;font-size:9.5px;color:#64748b;font-weight:600;margin-top:3px;">○ Ampuan Dosen</div>
                                    <?php endif; ?>
                                </td>
                                <td style="vertical-align:middle;">
                                    <strong style="font-size:13.5px;color:#0f172a;"><?php echo esc_html($c['nama']); ?></strong>
                                    <div style="font-size:11.5px;color:#64748b;margin-top:2px;">
                                        <?php echo esc_html($c['rumpun']); ?>
                                        <?php if (!empty($c['sdg'])) : ?> · <span style="color:#0D3B22;font-weight:600;"><?php echo esc_html($c['sdg']); ?></span><?php endif; ?>
                                    </div>
                                </td>
                                <td style="text-align:center;vertical-align:middle;">
                                    <span class="ppb-mk-pill" style="background:#E8F0EA;color:#0D3B22;"><?php echo esc_html($c['jenjang']); ?></span>
                                    <div style="font-size:11px;color:#64748b;margin-top:3px;">Sem <?php echo (int) $c['semester']; ?></div>
                                </td>
                                <td style="text-align:center;vertical-align:middle;font-weight:800;color:#0D3B22;">
                                    <?php echo (int) $c['sks']; ?>
                                </td>
                                <td style="vertical-align:middle;">
                                    <?php if (!empty($c['dosen_slugs'])) : ?>
                                        <?php foreach ($c['dosen_slugs'] as $sl) :
                                            $dname = $dosen_map[$sl]['nama'] ?? $sl;
                                        ?>
                                            <a href="<?php echo esc_url(home_url('/profil/dosen/' . $sl . '/')); ?>" target="_blank" class="ppb-mk-dosen-chip" title="Lihat profil dosen">👤 <?php echo esc_html($dname); ?></a>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <span style="font-size:11.5px;color:#94a3b8;font-style:italic;">Belum dikaitkan</span>
                                    <?php endif; ?>
                                </td>
                                <td style="vertical-align:middle;">
                                    <div style="display:flex;flex-wrap:wrap;gap:4px;">
                                        <?php if (!empty($c['rps_url'])) : ?>
                                            <a href="<?php echo esc_url($c['rps_url']); ?>" target="_blank" rel="noopener" class="button button-small" style="background:#DCFCE7;color:#166534;border-color:#86EFAC;font-weight:700;">📄 RPS ↗</a>
                                        <?php endif; ?>
                                        <?php if (!empty($c['sap_url'])) : ?>
                                            <a href="<?php echo esc_url($c['sap_url']); ?>" target="_blank" rel="noopener" class="button button-small" style="background:#E0F2FE;color:#0369A1;border-color:#7DD3FC;font-weight:700;">🗓️ SAP ↗</a>
                                        <?php endif; ?>
                                        <?php if (empty($c['rps_url']) && empty($c['sap_url'])) : ?>
                                            <span style="font-size:11px;color:#94a3b8;">Belum diunggah</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td style="text-align:center;vertical-align:middle;white-space:nowrap;">
                                    <button type="button" onclick='ppbOpenMatkulModal(<?php echo $edit_payload; ?>)' class="button button-small" style="background:#F5B82E;color:#0D3B22;border:none;font-weight:700;padding:3px 10px;border-radius:6px;cursor:pointer;">✏️ Edit</button>
                                    <form method="post" action="" style="display:inline;margin-left:4px;" onsubmit="return confirm('Hapus mata kuliah <?php echo esc_js($c['kode'] . ' — ' . $c['nama']); ?>? Kaitannya di profil dosen juga akan dilepas.');">
                                        <?php wp_nonce_field('ppb_matkul_delete_action', 'ppb_matkul_delete_nonce'); ?>
                                        <input type="hidden" name="delete_mk_id" value="<?php echo esc_attr($c['id']); ?>" />
                                        <button type="submit" name="ppb_delete_matkul_submit" class="button button-small" style="background:#FEE2E2;color:#991B1B;border-color:#FECACA;font-weight:700;border-radius:6px;cursor:pointer;">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ADD / EDIT MODAL -->
        <div id="ppb-mk-modal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.65);z-index:99999;backdrop-filter:blur(3px);align-items:center;justify-content:center;padding:20px;">
            <div style="background:#fff;border-radius:18px;max-width:860px;width:100%;max-height:92vh;overflow-y:auto;box-shadow:0 24px 50px rgba(0,0,0,0.35);position:relative;">
                <div style="padding:18px 24px;border-bottom:1px solid #e2e8f0;background:#FAF8F5;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:5;">
                    <div>
                        <h2 id="ppb-mk-modal-title" style="margin:0;font-size:18px;font-weight:800;color:#0D3B22;">Tambah / Edit Mata Kuliah</h2>
                        <p style="margin:2px 0 0;font-size:12px;color:#64748b;">Setelah disimpan, daftar mata kuliah pada profil dosen terpilih langsung diperbarui.</p>
                    </div>
                    <button type="button" onclick="ppbCloseMatkulModal()" style="width:32px;height:32px;border-radius:50%;border:none;background:#e2e8f0;font-size:18px;cursor:pointer;font-weight:700;">&times;</button>
                </div>

                <form method="post" action="" style="padding:22px 24px;display:flex;flex-direction:column;gap:16px;">
                    <?php wp_nonce_field('ppb_matkul_save_action', 'ppb_matkul_nonce'); ?>
                    <input type="hidden" name="mk_id" id="ppb_mk_id" value="" />

                    <div style="display:grid;grid-template-columns:140px 1fr 120px 110px 90px;gap:12px;">
                        <div>
                            <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px;">Kode MK *</label>
                            <input type="text" name="mk_kode" id="ppb_mk_kode" required placeholder="BK101" style="width:100%;padding:8px 10px;border:1px solid #cbd5e1;border-radius:8px;font-family:monospace;font-weight:700;" />
                        </div>
                        <div>
                            <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px;">Nama Mata Kuliah *</label>
                            <input type="text" name="mk_nama" id="ppb_mk_nama" required placeholder="Contoh: Dasar-Dasar Bimbingan & Konseling" style="width:100%;padding:8px 10px;border:1px solid #cbd5e1;border-radius:8px;" />
                        </div>
                        <div>
                            <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px;">Jenjang</label>
                            <select name="mk_jenjang" id="ppb_mk_jenjang" style="width:100%;padding:8px;border:1px solid #cbd5e1;border-radius:8px;">
                                <option value="S1">S1</option>
                                <option value="S2">S2</option>
                                <option value="S3">S3</option>
                                <option value="S2/S3">S2/S3</option>
                            </select>
                        </div>
                        <div>
                            <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px;">Semester</label>
                            <select name="mk_semester" id="ppb_mk_semester" style="width:100%;padding:8px;border:1px solid #cbd5e1;border-radius:8px;">
                                <?php for ($s = 1; $s <= 8; $s++) : ?>
                                    <option value="<?php echo $s; ?>">Sem <?php echo $s; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div>
                            <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px;">SKS</label>
                            <input type="number" name="mk_sks" id="ppb_mk_sks" min="0" max="12" value="3" style="width:100%;padding:8px;border:1px solid #cbd5e1;border-radius:8px;" />
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr 140px;gap:12px;">
                        <div>
                            <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px;">Kelompok / Rumpun / Sifat</label>
                            <input type="text" name="mk_rumpun" id="ppb_mk_rumpun" value="Wajib Prodi" placeholder="Wajib Keilmuan / Praktik / Pilihan" style="width:100%;padding:8px 10px;border:1px solid #cbd5e1;border-radius:8px;" />
                        </div>
                        <div>
                            <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px;">Tema Semester (Opsional)</label>
                            <input type="text" name="mk_sem_title" id="ppb_mk_sem_title" placeholder="Contoh: Fondasi Profesi & Asesmen Non-Tes" style="width:100%;padding:8px 10px;border:1px solid #cbd5e1;border-radius:8px;" />
                        </div>
                        <div>
                            <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px;">Tag SDGs</label>
                            <input type="text" name="mk_sdg" id="ppb_mk_sdg" value="SDG 4" placeholder="SDG 4" style="width:100%;padding:8px 10px;border:1px solid #cbd5e1;border-radius:8px;" />
                        </div>
                    </div>

                    <div style="background:#F9FBFA;border:1px solid #D4E3D8;border-radius:12px;padding:14px;display:flex;flex-direction:column;gap:12px;">
                        <div>
                            <label style="display:block;font-size:12px;font-weight:700;color:#0D3B22;margin-bottom:5px;">📄 Tautan / Upload File RPS (PDF atau Google Drive)</label>
                            <?php ppb_render_media_url_control(array(
                                'id'          => 'ppb_mk_rps_url',
                                'name'        => 'mk_rps_url',
                                'value'       => '',
                                'placeholder' => 'https://drive.google.com/... atau upload PDF RPS via Pustaka Media',
                                'title'       => 'Pilih atau Upload File RPS (PDF / DOCX)',
                                'button_text' => 'Gunakan File RPS',
                            )); ?>
                        </div>
                        <div>
                            <label style="display:block;font-size:12px;font-weight:700;color:#0D3B22;margin-bottom:5px;">🗓️ Tautan / Upload File SAP / Rubrik (PDF atau Google Drive)</label>
                            <?php ppb_render_media_url_control(array(
                                'id'          => 'ppb_mk_sap_url',
                                'name'        => 'mk_sap_url',
                                'value'       => '',
                                'placeholder' => 'https://drive.google.com/... atau upload PDF SAP via Pustaka Media',
                                'title'       => 'Pilih atau Upload File SAP (PDF / DOCX)',
                                'button_text' => 'Gunakan File SAP',
                            )); ?>
                        </div>
                    </div>

                    <div>
                        <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px;">Capaian Pembelajaran Mata Kuliah (CPMK) / Ringkasan</label>
                        <textarea name="mk_cpmk" id="ppb_mk_cpmk" rows="3" placeholder="Tuliskan deskripsi singkat CPMK, metode Case Method / Team-Based Project..." style="width:100%;padding:8px 10px;border:1px solid #cbd5e1;border-radius:8px;"></textarea>
                    </div>

                    <div>
                        <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:8px;margin-bottom:6px;">
                            <label style="font-size:12px;font-weight:700;color:#0D3B22;">👨‍🏫 Kaitkan ke Dosen Pengampu (Otomatis Sync ke List MK Dosen):</label>
                            <input type="search" id="ppb-mk-dosen-search" placeholder="Cari nama dosen..." oninput="ppbFilterModalDosen(this.value)" style="padding:5px 10px;border:1px solid #cbd5e1;border-radius:7px;font-size:12px;min-width:200px;" />
                        </div>
                        <div class="ppb-mk-dosen-picker" id="ppb-mk-dosen-picker">
                            <?php foreach ($dosen_map as $sl => $dm) : ?>
                                <label class="ppb-mk-dosen-opt" data-dosen-name="<?php echo esc_attr(strtolower($dm['nama'])); ?>">
                                    <input type="checkbox" name="mk_dosen_slugs[]" value="<?php echo esc_attr($sl); ?>" class="ppb-mk-dosen-cb" />
                                    <span><?php echo esc_html($dm['nama']); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <label style="display:inline-flex;align-items:center;gap:8px;font-size:12.5px;font-weight:600;color:#0D3B22;cursor:pointer;">
                        <input type="checkbox" name="mk_in_kurikulum" id="ppb_mk_in_kurikulum" value="1" checked />
                        Tampilkan juga di Tabel Struktur Kurikulum Publik (<code>/akademik/kurikulum/</code> &amp; <code>/akademik/rps-sap/</code>)
                    </label>

                    <div style="display:flex;justify-content:flex-end;gap:10px;border-top:1px solid #e2e8f0;padding-top:14px;">
                        <button type="button" onclick="ppbCloseMatkulModal()" class="button" style="border-radius:8px;padding:6px 16px;height:auto;">Batal</button>
                        <button type="submit" name="ppb_save_matkul_submit" class="button button-primary" style="background:#0D3B22;border-color:#0D3B22;color:#F5B82E;font-weight:800;border-radius:8px;padding:7px 22px;height:auto;">💾 Simpan &amp; Sync ke Dosen</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
        function ppbTriggerInputEvents(el) {
            if (!el) return;
            el.dispatchEvent(new Event('input', { bubbles: true }));
        }
        function ppbOpenMatkulModal(data) {
            var modal = document.getElementById('ppb-mk-modal');
            var isEdit = !!(data && data.id);
            document.getElementById('ppb-mk-modal-title').textContent = isEdit
                ? ('Edit Mata Kuliah: ' + (data.kode || '') + ' — ' + (data.nama || ''))
                : 'Tambah Mata Kuliah Baru (Auto-Sync Dosen)';

            document.getElementById('ppb_mk_id').value = isEdit ? data.id : '';
            document.getElementById('ppb_mk_kode').value = data ? (data.kode || '') : '';
            document.getElementById('ppb_mk_nama').value = data ? (data.nama || '') : '';
            document.getElementById('ppb_mk_jenjang').value = data ? (data.jenjang || 'S1') : 'S1';
            document.getElementById('ppb_mk_semester').value = data ? String(data.semester || 1) : '1';
            document.getElementById('ppb_mk_sks').value = data ? String(data.sks != null ? data.sks : 3) : '3';
            document.getElementById('ppb_mk_rumpun').value = data ? (data.rumpun || 'Wajib Prodi') : 'Wajib Prodi';
            document.getElementById('ppb_mk_sem_title').value = data ? (data.sem_title || '') : '';
            document.getElementById('ppb_mk_sdg').value = data ? (data.sdg || 'SDG 4') : 'SDG 4';
            document.getElementById('ppb_mk_cpmk').value = data ? (data.cpmk || '') : '';
            document.getElementById('ppb_mk_in_kurikulum').checked = data ? !!data.in_kurikulum : true;

            var rpsEl = document.getElementById('ppb_mk_rps_url');
            var sapEl = document.getElementById('ppb_mk_sap_url');
            rpsEl.value = data ? (data.rps_url || '') : '';
            sapEl.value = data ? (data.sap_url || '') : '';
            ppbTriggerInputEvents(rpsEl);
            ppbTriggerInputEvents(sapEl);

            var selectedSet = {};
            if (data && Array.isArray(data.dosen_slugs)) {
                data.dosen_slugs.forEach(function(s) { selectedSet[s] = true; });
            }
            document.querySelectorAll('.ppb-mk-dosen-cb').forEach(function(cb) {
                cb.checked = !!selectedSet[cb.value];
            });
            var searchBox = document.getElementById('ppb-mk-dosen-search');
            if (searchBox) {
                searchBox.value = '';
                ppbFilterModalDosen('');
            }
            modal.style.display = 'flex';
        }
        function ppbCloseMatkulModal() {
            document.getElementById('ppb-mk-modal').style.display = 'none';
        }
        function ppbFilterModalDosen(q) {
            var needle = (q || '').toLowerCase().trim();
            document.querySelectorAll('#ppb-mk-dosen-picker .ppb-mk-dosen-opt').forEach(function(lbl) {
                var txt = lbl.getAttribute('data-dosen-name') || '';
                lbl.style.display = (!needle || txt.indexOf(needle) !== -1) ? 'flex' : 'none';
            });
        }
        document.addEventListener('keydown', function(e) {
            var modal = document.getElementById('ppb-mk-modal');
            if (e.key === 'Escape' && modal && modal.style.display === 'flex') {
                ppbCloseMatkulModal();
            }
        });
        </script>
    </div>
    <?php
}
}

/* ========================================================================== */
/* 4. FRONTEND FILTER: /akademik/kurikulum/ LIVE DATA + RPS/SAP + DOSEN       */
/* ========================================================================== */

add_filter('the_content', function($content) {
    if (is_admin() || !is_string($content)) return $content;

    $slug_path = function_exists('ppb_get_current_page_slug_path') ? ppb_get_current_page_slug_path() : '';
    $is_kur_page = ($slug_path === 'akademik/kurikulum' || $slug_path === 'kurikulum' || strpos($content, 'id="bk-kur-data"') !== false);
    if (!$is_kur_page || strpos($content, 'id="bk-kur-data"') === false) {
        return $content;
    }

    // 4a. Inject Books/SK Kurikulum Drive Links into .bk-ugm-actions
    $link_buku = function_exists('ppb_clean_document_url') ? ppb_clean_document_url(get_option('ppb_link_buku_kurikulum', '')) : '';
    $link_sk   = function_exists('ppb_clean_document_url') ? ppb_clean_document_url(get_option('ppb_link_sk_kurikulum', '')) : '';
    if (($link_buku !== '' || $link_sk !== '') && strpos($content, 'bk-ugm-actions') !== false) {
        $extra_btns = '';
        if ($link_buku !== '') {
            $extra_btns .= "\n                    <a href=\"" . esc_url($link_buku) . "\" target=\"_blank\" rel=\"noopener\" class=\"bk-btn bk-btn-primary bk-btn-sm\">📘 Buku Kurikulum OBE ↗</a>";
        }
        if ($link_sk !== '') {
            $extra_btns .= "\n                    <a href=\"" . esc_url($link_sk) . "\" target=\"_blank\" rel=\"noopener\" class=\"bk-btn bk-btn-secondary bk-btn-sm\">🏛️ SK Senat Kurikulum ↗</a>";
        }
        $content = preg_replace(
            '/(<div\s+class=["\']bk-ugm-actions["\'][^>]*>)(.*?)(<\/div>)/is',
            '$1$2' . $extra_btns . '$3',
            $content,
            1
        );
    }

    // 4b. Rebuild #bk-kur-data JSON dynamically from ppb_mata_kuliah_list + dosen map
    if (preg_match('/(<script\b[^>]*\bid=["\']bk-kur-data["\'][^>]*>)(.*?)(<\/script>)/is', $content, $m, PREG_OFFSET_CAPTURE)) {
        $existing_json = json_decode(trim($m[2][0]), true);
        if (!is_array($existing_json)) $existing_json = array();
        $degrees_meta = isset($existing_json['degrees']) && is_array($existing_json['degrees']) ? $existing_json['degrees'] : array();

        $dosen_list = function_exists('ppb_get_dosen_directory_cached') ? ppb_get_dosen_directory_cached() : array();
        $dosen_lookup = array();
        foreach ($dosen_list as $d) {
            $sl = sanitize_title($d['slug'] ?? '');
            if ($sl !== '') {
                $dosen_lookup[$sl] = array(
                    'slug' => $sl,
                    'nama' => (string) ($d['nama_lengkap'] ?? ($d['nama'] ?? $sl)),
                    'url'  => home_url('/profil/dosen/' . $sl . '/'),
                );
            }
        }

        $courses = ppb_get_mata_kuliah_list(true);
        $grouped = array('s1' => array(), 's2' => array(), 's3' => array());
        foreach ($courses as $mk) {
            if (empty($mk['in_kurikulum'])) continue;
            $linked_dosen = array();
            if (!empty($mk['dosen_slugs']) && is_array($mk['dosen_slugs'])) {
                foreach ($mk['dosen_slugs'] as $sl) {
                    if (isset($dosen_lookup[$sl])) {
                        $linked_dosen[] = $dosen_lookup[$sl];
                    }
                }
            }
            $row = array(
                'id'       => (string) ($mk['id'] ?? ''),
                'sem'      => (int) ($mk['semester'] ?? 1),
                'kode'     => (string) ($mk['kode'] ?? ''),
                'nama'     => (string) ($mk['nama'] ?? ''),
                'sks'      => (int) ($mk['sks'] ?? 3),
                'rumpun'   => (string) ($mk['rumpun'] ?? 'Wajib Prodi'),
                'sdg'      => (string) ($mk['sdg'] ?? 'SDG 4'),
                'semTitle' => (string) ($mk['sem_title'] ?? ''),
                'rps_url'  => (string) ($mk['rps_url'] ?? ''),
                'sap_url'  => (string) ($mk['sap_url'] ?? ''),
                'cpmk'     => (string) ($mk['cpmk'] ?? ''),
                'dosen'    => $linked_dosen,
            );
            foreach (ppb_matkul_degrees_for_kurikulum($mk['jenjang'] ?? 'S1') as $deg_key) {
                $grouped[$deg_key][] = $row;
            }
        }

        $new_payload = wp_json_encode(array(
            'degrees' => $degrees_meta,
            'courses' => $grouped,
        ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $json_block_end = $m[0][1] + strlen($m[0][0]);
        $before_json = substr($content, 0, $m[0][1]);
        $after_json  = substr($content, $json_block_end);

        // Replace the trailing inline JS controller so rows use unique ID, show linked lecturers, and expose real RPS/SAP PDFs
        $enhanced_js = <<<'JS'

<script>
(function(){
var rawEl = document.getElementById("bk-kur-data");
var DATA = { degrees: {}, courses: {} };
try { DATA = JSON.parse(rawEl ? rawEl.textContent : "{}") || { degrees: {}, courses: {} }; } catch (e) { DATA = { degrees: {}, courses: {} }; }
var degWrap = document.getElementById("bk-kur-deg");
var pillsWrap = document.getElementById("bk-kur-sem-pills");
var tbodyEl = document.getElementById("bk-kur-tbody");
var sumEl = document.getElementById("bk-kur-summary");
var kkniEl = document.getElementById("bk-kur-kkni");
var skEl = document.getElementById("bk-kur-sk");
var titleEl = document.getElementById("bk-kur-deg-title");
var bdp = document.getElementById("bk-kur-backdrop");
var drw = document.getElementById("bk-kur-drawer");
var drwK = document.getElementById("bk-kur-drw-kicker");
var drwT = document.getElementById("bk-kur-drw-title");
var drwB = document.getElementById("bk-kur-drw-body");
var drwX = document.getElementById("bk-kur-drw-close");
var activeDeg = "s1";
var activeSem = "1";
var bp = window.location.pathname.indexOf("/yamato/") === 0 ? "/yamato" : "";
var qMatch = (window.location.search || "").match(/[?&]jenjang=(s1|s2|s3)/i);
var hMatch = (window.location.hash || "").match(/#(s1|s2|s3)/i);
if (qMatch && qMatch[1]) activeDeg = qMatch[1].toLowerCase();
else if (hMatch && hMatch[1]) activeDeg = hMatch[1].toLowerCase();
function routeUrl(p) { return (bp && p.indexOf("/") === 0 && p.indexOf(bp + "/") !== 0) ? (bp + p) : p; }
function esc(s) {
    return String(s == null ? "" : s)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;");
}
function getSemsFor(list) {
    var seen = {};
    var out = [];
    for (var i = 0; i < list.length; i++) {
        var s = String(list[i].sem);
        if (!seen[s]) { seen[s] = true; out.push(s); }
    }
    return out.sort(function(a, b) { return Number(a) - Number(b); });
}
function renderPills(list) {
    if (!pillsWrap) return;
    var sems = getSemsFor(list);
    if (activeSem !== "all" && sems.indexOf(String(activeSem)) === -1) activeSem = sems[0] || "all";
    var html = "<button type=\"button\" class=\"bk-pill " + (activeSem === "all" ? "is-active" : "") + "\" data-kur-sem=\"all\">Semua (" + list.length + ")</button>";
    for (var i = 0; i < sems.length; i++) {
        var s = sems[i];
        html += "<button type=\"button\" class=\"bk-pill " + (String(activeSem) === s ? "is-active" : "") + "\" data-kur-sem=\"" + esc(s) + "\">Sem " + esc(s) + "</button>";
    }
    pillsWrap.innerHTML = html;
}
function renderTable() {
    var list = (DATA.courses && DATA.courses[activeDeg]) ? DATA.courses[activeDeg] : [];
    var degMeta = (DATA.degrees && DATA.degrees[activeDeg]) ? DATA.degrees[activeDeg] : {};
    if (degWrap) {
        degWrap.querySelectorAll("[data-kur-deg]").forEach(function(b) {
            var act = b.getAttribute("data-kur-deg") === activeDeg;
            b.classList.toggle("is-active", act);
            b.setAttribute("aria-selected", act ? "true" : "false");
        });
    }
    renderPills(list);
    var vis = activeSem === "all" ? list : list.filter(function(c) { return String(c.sem) === String(activeSem); });
    var totalSks = vis.reduce(function(acc, c) { return acc + Number(c.sks || 0); }, 0);
    if (kkniEl && degMeta.kkni) kkniEl.textContent = degMeta.kkni;
    if (skEl && degMeta.sk) skEl.textContent = "SK LAMDIK " + degMeta.sk;
    if (titleEl && degMeta.title) titleEl.textContent = degMeta.title + (degMeta.gelar ? " · " + degMeta.gelar : "");
    if (sumEl) {
        var scopeLbl = activeSem === "all" ? ("Semua Semester (" + (degMeta.code || activeDeg.toUpperCase()) + ")") : ("Semester " + activeSem);
        sumEl.innerHTML = "<strong style=\"color:var(--forest);\">" + esc(scopeLbl) + " &middot; " + vis.length + " MK (" + totalSks + " SKS)</strong> &bull; Klik baris untuk detail CPL, Dosen Pengampu &amp; RPS";
    }
    if (tbodyEl) {
        tbodyEl.innerHTML = vis.map(function(c) {
            var rowKey = c.id || c.kode;
            var dosenHtml = "";
            if (Array.isArray(c.dosen) && c.dosen.length) {
                var names = c.dosen.map(function(ds) { return ds.nama; }).join(", ");
                dosenHtml = "<div style=\"font-size:11.5px;color:#0D3B22;font-weight:600;margin-top:3px;\">👨‍🏫 " + esc(names) + "</div>";
            }
            var docBadges = "";
            if (c.rps_url) docBadges += "<span style=\"display:inline-block;font-size:10px;font-weight:700;background:#DCFCE7;color:#166534;padding:1px 6px;border-radius:4px;margin-right:4px;\">RPS</span>";
            if (c.sap_url) docBadges += "<span style=\"display:inline-block;font-size:10px;font-weight:700;background:#E0F2FE;color:#0369A1;padding:1px 6px;border-radius:4px;margin-right:4px;\">SAP</span>";
            return "<tr data-kur-row=\"" + esc(activeDeg + "|" + rowKey) + "\" tabindex=\"0\">" +
                "<td class=\"bk-fm\" style=\"font-weight:700;color:var(--forest);white-space:nowrap;\">" + esc(c.kode) + "</td>" +
                "<td><strong>" + esc(c.nama) + "</strong><div style=\"font-size:12px;color:var(--ink-muted);margin-top:2px;\">" + esc(c.rumpun) + (docBadges ? " &nbsp;" + docBadges : "") + "</div>" + dosenHtml + "</td>" +
                "<td class=\"bk-fm\" style=\"white-space:nowrap;\">Sem " + Number(c.sem) + "</td>" +
                "<td class=\"bk-fm\" style=\"white-space:nowrap;\">" + Number(c.sks) + " SKS</td>" +
                "<td><span class=\"bk-tag bk-tag-sage\">" + esc(c.sdg) + "</span></td>" +
                "<td style=\"text-align:right;white-space:nowrap;\"><span style=\"font-size:12.5px;font-weight:600;color:var(--forest);\">Detail &rarr;</span></td>" +
                "</tr>";
        }).join("");
    }
}
function closeDrawer() {
    if (!drw || !bdp) return;
    bdp.classList.remove("is-open");
    drw.classList.remove("is-open");
    drw.setAttribute("aria-hidden", "true");
}
function openCourseDrawer(degKey, rowKey) {
    var list = (DATA.courses && DATA.courses[degKey]) ? DATA.courses[degKey] : [];
    var c = null;
    for (var i = 0; i < list.length; i++) {
        if ((list[i].id && list[i].id === rowKey) || list[i].kode === rowKey) { c = list[i]; break; }
    }
    if (!c || !drw || !bdp) return;
    var degMeta = (DATA.degrees && DATA.degrees[degKey]) ? DATA.degrees[degKey] : {};
    var rpsPageUrl = routeUrl("/akademik/rps-sap/");
    var elibUrl = routeUrl("/e-library/");
    var isPrac = String(c.rumpun || "").toLowerCase().indexOf("praktik") !== -1 || String(c.rumpun || "").toLowerCase().indexOf("mbkm") !== -1 || String(c.rumpun || "").toLowerCase().indexOf("akhir") !== -1;
    var metodeLbl = isPrac ? "Team-Based Project & Supervisi Praktik" : "Case Method & Team-Based Project OBE";
    drwK.textContent = "KURIKULUM OBE · " + (degMeta.code || degKey.toUpperCase()) + " · SEMESTER " + Number(c.sem);
    drwT.textContent = c.kode + " — " + c.nama;

    var dosenSection = "";
    if (Array.isArray(c.dosen) && c.dosen.length) {
        dosenSection = "<div class=\"bk-kicker\" style=\"margin-bottom:8px;\">Dosen Pengampu Mata Kuliah</div><div style=\"display:flex;flex-wrap:wrap;gap:6px;margin-bottom:18px;\">";
        c.dosen.forEach(function(ds) {
            dosenSection += "<a href=\"" + esc(ds.url || routeUrl("/profil/dosen/" + ds.slug + "/")) + "\" style=\"display:inline-flex;align-items:center;gap:6px;background:#E8F0EA;color:#0D3B22;border:1px solid #C5DACB;padding:5px 11px;border-radius:999px;font-size:12px;font-weight:700;text-decoration:none;\">👤 " + esc(ds.nama) + " &rarr;</a>";
        });
        dosenSection += "</div>";
    }

    var rpsBtn = c.rps_url
        ? "<a href=\"" + esc(c.rps_url) + "\" target=\"_blank\" rel=\"noopener\" class=\"bk-btn bk-btn-primary bk-btn-sm\" style=\"text-decoration:none;\">Unduh RPS ↗</a>"
        : "<span style=\"font-size:11.5px;color:#64748b;background:#f1f5f9;padding:4px 10px;border-radius:6px;font-weight:600;\">Belum Diunggah</span>";
    var sapBtn = c.sap_url
        ? "<a href=\"" + esc(c.sap_url) + "\" target=\"_blank\" rel=\"noopener\" class=\"bk-btn bk-btn-primary bk-btn-sm\" style=\"text-decoration:none;\">Unduh SAP ↗</a>"
        : "<span style=\"font-size:11.5px;color:#64748b;background:#f1f5f9;padding:4px 10px;border-radius:6px;font-weight:600;\">Belum Diunggah</span>";

    drwB.innerHTML = "<div style=\"display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;\">" +
        "<span class=\"bk-tag bk-tag-sage\">" + Number(c.sks) + " SKS</span>" +
        "<span class=\"bk-tag\">" + esc(c.rumpun) + "</span>" +
        "<span class=\"bk-tag bk-tag-gold\">" + esc(c.sdg) + "</span>" +
        "</div>" +
        dosenSection +
        "<ul class=\"bk-spec-list\" style=\"margin-bottom:18px;\">" +
        "<li><span style=\"color:var(--ink-muted);\">Jenjang Program</span><strong style=\"text-align:right;\">" + esc(degMeta.title || degKey.toUpperCase()) + "</strong></li>" +
        "<li><span style=\"color:var(--ink-muted);\">Semester / Fokus</span><span style=\"text-align:right;\">Semester " + Number(c.sem) + (c.semTitle ? " · " + esc(c.semTitle) : "") + "</span></li>" +
        "<li><span style=\"color:var(--ink-muted);\">Model Pembelajaran</span><strong style=\"color:var(--forest);text-align:right;\">" + esc(metodeLbl) + "</strong></li>" +
        "</ul>" +
        "<div class=\"bk-kicker\" style=\"margin-bottom:6px;\">Penyelarasan CPL, CPMK &amp; Standar OBE</div>" +
        "<p style=\"font-size:13.5px;color:var(--ink-2);line-height:1.65;margin:0 0 18px;\">" + esc(c.cpmk || degMeta.cpl || "Mata kuliah dirancang berbasis luaran (Outcome-Based Education) dengan pencapaian CPL dan indikator SDGs.") + "</p>" +
        "<div class=\"bk-kicker\" style=\"margin-bottom:8px;\">Perangkat Pembelajaran Wajib</div>" +
        "<div class=\"bk-hairline-list\" style=\"padding:0 14px;margin-bottom:20px;background:var(--bg-canvas);border:1px solid var(--border);border-radius:12px;\">" +
        "<div class=\"bk-drawer-item\" style=\"display:flex;justify-content:space-between;align-items:center;gap:12px;\"><div><strong>RPS Lengkap 16 Pertemuan</strong><div style=\"font-size:12px;color:var(--ink-muted);\">Peta CPL, CPMK &amp; penanda " + esc(c.sdg) + "</div></div>" + rpsBtn + "</div>" +
        "<div class=\"bk-drawer-item\" style=\"display:flex;justify-content:space-between;align-items:center;gap:12px;\"><div><strong>SAP &amp; Rubrik Penilaian OBE</strong><div style=\"font-size:12px;color:var(--ink-muted);\">Skenario tatap muka &amp; " + esc(metodeLbl) + "</div></div>" + sapBtn + "</div>" +
        "</div>" +
        "<div style=\"display:flex;gap:8px;flex-wrap:wrap;\">" +
        "<a href=\"" + esc(rpsPageUrl) + "\" class=\"bk-btn bk-btn-primary bk-btn-sm\">Buka Portal RPS, SAP &amp; Rubrik &rarr;</a>" +
        "<a href=\"" + esc(elibUrl) + "\" class=\"bk-btn bk-btn-secondary bk-btn-sm\">Modul di E-Library &rarr;</a>" +
        "</div>";
    drwB.scrollTop = 0;
    bdp.classList.add("is-open");
    drw.classList.add("is-open");
    drw.setAttribute("aria-hidden", "false");
}
if (degWrap) {
    degWrap.addEventListener("click", function(e) {
        var btn = e.target.closest("[data-kur-deg]");
        if (!btn) return;
        activeDeg = btn.getAttribute("data-kur-deg") || "s1";
        activeSem = "1";
        renderTable();
    });
}
if (pillsWrap) {
    pillsWrap.addEventListener("click", function(e) {
        var pill = e.target.closest("[data-kur-sem]");
        if (!pill) return;
        activeSem = pill.getAttribute("data-kur-sem") || "1";
        renderTable();
    });
}
if (tbodyEl) {
    tbodyEl.addEventListener("click", function(e) {
        var row = e.target.closest("[data-kur-row]");
        if (!row) return;
        var val = row.getAttribute("data-kur-row") || "";
        var parts = val.indexOf("|") !== -1 ? val.split("|") : val.split(":");
        if (parts.length >= 2) openCourseDrawer(parts[0], parts.slice(1).join("|"));
    });
    tbodyEl.addEventListener("keydown", function(e) {
        if (e.key !== "Enter" && e.key !== " ") return;
        var row = e.target.closest("[data-kur-row]");
        if (!row) return;
        e.preventDefault();
        var val = row.getAttribute("data-kur-row") || "";
        var parts = val.indexOf("|") !== -1 ? val.split("|") : val.split(":");
        if (parts.length >= 2) openCourseDrawer(parts[0], parts.slice(1).join("|"));
    });
}
if (drwX) drwX.addEventListener("click", closeDrawer);
if (bdp) bdp.addEventListener("click", closeDrawer);
document.addEventListener("keydown", function(e) {
    if (e.key === "Escape" && drw && drw.classList.contains("is-open")) closeDrawer();
});
renderTable();
})();
</script>
JS;

        // Strip original trailing controller <script> and replace cleanly
        $after_json_updated = preg_replace('/\s*<script>.*?renderTable\(\);\s*\}\)\(\);\s*<\/script>/is', $enhanced_js, $after_json, 1, $replaced_js);
        if (!$replaced_js) {
            $after_json_updated = $after_json . $enhanced_js;
        }

        $content = $before_json . $m[1][0] . $new_payload . $m[3][0] . $after_json_updated;
    }

    return $content;
}, 12);
