<?php
/**
 * Module: PPB FIP UNM - Registry Terpusat Unit & Prodi Jurusan PPB
 * Description: Sumber kebenaran tunggal (Single Source of Truth) untuk tata kelola payung
 *              Jurusan Psikologi Pendidikan dan Bimbingan (PPB) serta daftar Program Studi
 *              (S1, S2, S3, dan Prodi baru seperti PPG BK) di WP Admin dan Frontend.
 * Author: Jurusan PPB FIP UNM
 */

if (!defined('ABSPATH')) exit;

/* ========================================================================== */
/* 1. DEFAULTS, GETTERS & SANITIZED SAVE HELPERS                              */
/* ========================================================================== */

if (!function_exists('ppb_get_jurusan_units_defaults')) {
function ppb_get_jurusan_units_defaults() {
    return array(
        'jurusan' => array(
            'slug'             => 'jurusan',
            'nama'             => 'Jurusan Psikologi Pendidikan dan Bimbingan (PPB)',
            'singkatan'        => 'Jurusan PPB FIP UNM',
            'fakultas'         => 'Fakultas Ilmu Pendidikan Universitas Negeri Makassar',
            'pimpinan_jabatan' => 'Ketua Jurusan PPB',
            'pimpinan_nama'    => 'Dr. Suciani Latif, S.Pd., M.Pd.',
            'pimpinan_nip'     => '19860512 201404 2 001',
            'sekretaris_nama'  => 'Sekretaris Jurusan PPB',
            'sekretaris_nip'   => '',
            'gpm_ketua'        => 'Ketua Gugus Penjaminan Mutu (GPM) PPB',
            'lab_kepala'       => 'Kepala Laboratorium Bimbingan & Konseling',
            'visi_payung'      => 'Menjadi Jurusan unggul dan rujukan nasional dalam pengembangan keilmuan psikologi pendidikan dan bimbingan yang berwawasan nilai-nilai diversitas, sains-teknologi, dan jiwa kewirausahaan.',
            'misi_payung'      => "Menyelenggarakan tata kelola tridharma perguruan tinggi lintas jenjang (S1, S2, S3) yang akuntabel dan berbudaya mutu.\nMengembangkan riset dan inovasi intervensi konseling berbasis kearifan lokal Nusantara dan teknologi digital.\nMemperkuat kemitraan strategis sekolah, industri, asosiasi profesi (ABKIN), dan jejaring internasional.",
        ),
        'prodi' => array(
            's1' => array(
                'slug'          => 's1',
                'kode'          => 'S1',
                'nama'          => 'S1 Bimbingan dan Konseling',
                'tab_label'     => 'S1 Sarjana',
                'jenjang_label' => 'Jenjang Sarjana (S1)',
                'fakultas_unit' => 'Fakultas Ilmu Pendidikan (FIP) UNM',
                'gelar'         => 'Sarjana Pendidikan (S.Pd.)',
                'beban'         => '144–146 SKS · 8 Semester',
                'kkni'          => 'KKNI Level 6',
                'kaprodi_nama'  => 'Koordinator Program Studi S1 BK',
                'kaprodi_nip'   => '',
                'visi_keilmuan' => 'Menghasilkan calon konselor sekolah dan praktisi bimbingan yang menguasai asesmen psikologis, mikrokonseling, serta manajemen program BK komprehensif berbasis teknologi dan empati budaya.',
                'sasaran'       => 'Lulusan siap bertugas di satuan pendidikan (SD/SMP/SMA/SMK), layanan konseling komunitas, dan pengembangan karir.',
                'status'        => 'aktif',
                'urutan'        => 1,
            ),
            's2' => array(
                'slug'          => 's2',
                'kode'          => 'S2',
                'nama'          => 'S2 Bimbingan dan Konseling',
                'tab_label'     => 'S2 Magister',
                'jenjang_label' => 'Jenjang Magister (S2)',
                'fakultas_unit' => 'Pascasarjana & FIP UNM',
                'gelar'         => 'Magister Pendidikan (M.Pd.)',
                'beban'         => '38–42 SKS · 4 Semester',
                'kkni'          => 'KKNI Level 8',
                'kaprodi_nama'  => 'Ketua Program Studi S2 BK',
                'kaprodi_nip'   => '',
                'visi_keilmuan' => 'Mengembangkan penguasaan metodologi riset terapan, rekayasa model intervensi psikososial, supervisi klinis, konseling lintas budaya, dan kepemimpinan profesi konseling.',
                'sasaran'       => 'Akademisi, peneliti BK, konselor ahli, dan supervisor layanan bimbingan dan konseling.',
                'status'        => 'aktif',
                'urutan'        => 2,
            ),
            's3' => array(
                'slug'          => 's3',
                'kode'          => 'S3',
                'nama'          => 'S3 Ilmu Pendidikan (Konsentrasi BK)',
                'tab_label'     => 'S3 Doktoral',
                'jenjang_label' => 'Jenjang Doktor (S3)',
                'fakultas_unit' => 'Program Pascasarjana UNM',
                'gelar'         => 'Doktor (Dr.)',
                'beban'         => '42–48 SKS · 6 Semester',
                'kkni'          => 'KKNI Level 9',
                'kaprodi_nama'  => 'Koordinator Konsentrasi BK (S3 Ilmu Pendidikan)',
                'kaprodi_nip'   => '',
                'visi_keilmuan' => 'Mencetak filosof keilmuan, akademisi perguruan tinggi, dan perumus kebijakan nasional yang melahirkan kebaruan teori (novelty) intervensi konseling berakar kearifan budaya Nusantara.',
                'sasaran'       => 'Peneliti utama, dosen perguruan tinggi, dan inovator model konseling indigenos bereputasi internasional.',
                'status'        => 'aktif',
                'urutan'        => 3,
            ),
        ),
    );
}
}

if (!function_exists('ppb_get_jurusan_units')) {
function ppb_get_jurusan_units() {
    $defaults = ppb_get_jurusan_units_defaults();
    $saved    = get_option('ppb_jurusan_units', array());
    if (!is_array($saved) || empty($saved)) {
        return $defaults;
    }

    $out = $defaults;
    if (!empty($saved['jurusan']) && is_array($saved['jurusan'])) {
        $out['jurusan'] = wp_parse_args($saved['jurusan'], $defaults['jurusan']);
    }

    if (!empty($saved['prodi']) && is_array($saved['prodi'])) {
        $merged_prodi = array();
        foreach ($defaults['prodi'] as $slug => $def_p) {
            if (isset($saved['prodi'][$slug]) && is_array($saved['prodi'][$slug])) {
                $merged_prodi[$slug] = wp_parse_args($saved['prodi'][$slug], $def_p);
            } else {
                $merged_prodi[$slug] = $def_p;
            }
            $merged_prodi[$slug]['slug'] = $slug;
        }
        foreach ($saved['prodi'] as $slug => $sp) {
            $clean_slug = sanitize_key($slug);
            if ($clean_slug === '' || $clean_slug === 'jurusan' || isset($merged_prodi[$clean_slug]) || !is_array($sp)) {
                continue;
            }
            $merged_prodi[$clean_slug] = wp_parse_args($sp, array(
                'slug'          => $clean_slug,
                'kode'          => strtoupper($clean_slug),
                'nama'          => strtoupper($clean_slug) . ' Bimbingan dan Konseling',
                'tab_label'     => strtoupper($clean_slug),
                'jenjang_label' => 'Program ' . strtoupper($clean_slug),
                'fakultas_unit' => 'Jurusan PPB FIP UNM',
                'gelar'         => '',
                'beban'         => '',
                'kkni'          => '',
                'kaprodi_nama'  => '',
                'kaprodi_nip'   => '',
                'visi_keilmuan' => '',
                'sasaran'       => '',
                'status'        => 'aktif',
                'urutan'        => 99,
            ));
        }
        uasort($merged_prodi, function($a, $b) {
            return ((int) ($a['urutan'] ?? 99)) - ((int) ($b['urutan'] ?? 99));
        });
        $out['prodi'] = $merged_prodi;
    }

    return $out;
}
}

if (!function_exists('ppb_save_jurusan_units')) {
function ppb_save_jurusan_units($raw) {
    $current = ppb_get_jurusan_units();
    $def     = ppb_get_jurusan_units_defaults();

    $raw_jur = isset($raw['jurusan']) && is_array($raw['jurusan']) ? wp_unslash($raw['jurusan']) : array();
    $jur_out = array(
        'slug'             => 'jurusan',
        'nama'             => sanitize_text_field($raw_jur['nama'] ?? $current['jurusan']['nama']) ?: $def['jurusan']['nama'],
        'singkatan'        => sanitize_text_field($raw_jur['singkatan'] ?? $current['jurusan']['singkatan']) ?: $def['jurusan']['singkatan'],
        'fakultas'         => sanitize_text_field($raw_jur['fakultas'] ?? $current['jurusan']['fakultas']) ?: $def['jurusan']['fakultas'],
        'pimpinan_jabatan' => sanitize_text_field($raw_jur['pimpinan_jabatan'] ?? $current['jurusan']['pimpinan_jabatan']) ?: $def['jurusan']['pimpinan_jabatan'],
        'pimpinan_nama'    => sanitize_text_field($raw_jur['pimpinan_nama'] ?? $current['jurusan']['pimpinan_nama']),
        'pimpinan_nip'     => sanitize_text_field($raw_jur['pimpinan_nip'] ?? $current['jurusan']['pimpinan_nip']),
        'sekretaris_nama'  => sanitize_text_field($raw_jur['sekretaris_nama'] ?? $current['jurusan']['sekretaris_nama']),
        'sekretaris_nip'   => sanitize_text_field($raw_jur['sekretaris_nip'] ?? $current['jurusan']['sekretaris_nip']),
        'gpm_ketua'        => sanitize_text_field($raw_jur['gpm_ketua'] ?? $current['jurusan']['gpm_ketua']),
        'lab_kepala'       => sanitize_text_field($raw_jur['lab_kepala'] ?? $current['jurusan']['lab_kepala']),
        'visi_payung'      => sanitize_textarea_field($raw_jur['visi_payung'] ?? $current['jurusan']['visi_payung']),
        'misi_payung'      => sanitize_textarea_field($raw_jur['misi_payung'] ?? $current['jurusan']['misi_payung']),
    );

    $raw_prodi = isset($raw['prodi']) && is_array($raw['prodi']) ? wp_unslash($raw['prodi']) : array();
    $prodi_out = array();
    $core_slugs = array('s1', 's2', 's3');

    foreach ($raw_prodi as $slug => $rp) {
        if (!is_array($rp)) continue;
        $clean_slug = sanitize_key($slug);
        if ($clean_slug === '' || $clean_slug === 'jurusan') continue;

        if (!in_array($clean_slug, $core_slugs, true) && !empty($rp['delete'])) {
            continue;
        }

        $base = $current['prodi'][$clean_slug] ?? array(
            'slug' => $clean_slug,
            'kode' => strtoupper($clean_slug),
            'nama' => strtoupper($clean_slug),
            'tab_label' => strtoupper($clean_slug),
            'jenjang_label' => 'Program ' . strtoupper($clean_slug),
            'fakultas_unit' => 'Jurusan PPB FIP UNM',
            'gelar' => '',
            'beban' => '',
            'kkni' => '',
            'kaprodi_nama' => '',
            'kaprodi_nip' => '',
            'visi_keilmuan' => '',
            'sasaran' => '',
            'status' => 'aktif',
            'urutan' => 10,
        );

        $nama = sanitize_text_field($rp['nama'] ?? $base['nama']);
        if ($nama === '') continue;

        $kode = strtoupper(sanitize_text_field($rp['kode'] ?? $base['kode'])) ?: strtoupper($clean_slug);
        $status = in_array(($rp['status'] ?? 'aktif'), array('aktif', 'persiapan', 'nonaktif'), true) ? $rp['status'] : 'aktif';
        if (in_array($clean_slug, $core_slugs, true) && $status === 'nonaktif') {
            $status = 'aktif';
        }

        $prodi_out[$clean_slug] = array(
            'slug'          => $clean_slug,
            'kode'          => $kode,
            'nama'          => $nama,
            'tab_label'     => sanitize_text_field($rp['tab_label'] ?? $base['tab_label']) ?: $kode,
            'jenjang_label' => sanitize_text_field($rp['jenjang_label'] ?? $base['jenjang_label']) ?: ('Jenjang ' . $kode),
            'fakultas_unit' => sanitize_text_field($rp['fakultas_unit'] ?? $base['fakultas_unit']),
            'gelar'         => sanitize_text_field($rp['gelar'] ?? $base['gelar']),
            'beban'         => sanitize_text_field($rp['beban'] ?? $base['beban']),
            'kkni'          => sanitize_text_field($rp['kkni'] ?? $base['kkni']),
            'kaprodi_nama'  => sanitize_text_field($rp['kaprodi_nama'] ?? $base['kaprodi_nama']),
            'kaprodi_nip'   => sanitize_text_field($rp['kaprodi_nip'] ?? $base['kaprodi_nip']),
            'visi_keilmuan' => sanitize_textarea_field($rp['visi_keilmuan'] ?? $base['visi_keilmuan']),
            'sasaran'       => sanitize_textarea_field($rp['sasaran'] ?? $base['sasaran']),
            'status'        => $status,
            'urutan'        => max(1, (int) ($rp['urutan'] ?? $base['urutan'] ?? 10)),
        );
    }

    // Pastikan S1, S2, S3 selalu tersedia
    foreach ($def['prodi'] as $cslug => $cdef) {
        if (!isset($prodi_out[$cslug])) {
            $prodi_out[$cslug] = $current['prodi'][$cslug] ?? $cdef;
        }
    }

    // Tambah Prodi baru jika diisi di form tambah prodi
    if (!empty($raw['new_prodi']) && is_array($raw['new_prodi'])) {
        $np = wp_unslash($raw['new_prodi']);
        $new_slug = sanitize_key($np['slug'] ?? '');
        $new_nama = sanitize_text_field($np['nama'] ?? '');
        if ($new_slug !== '' && $new_slug !== 'jurusan' && $new_nama !== '' && !isset($prodi_out[$new_slug])) {
            $new_kode = strtoupper(sanitize_text_field($np['kode'] ?? $new_slug)) ?: strtoupper($new_slug);
            $prodi_out[$new_slug] = array(
                'slug'          => $new_slug,
                'kode'          => $new_kode,
                'nama'          => $new_nama,
                'tab_label'     => sanitize_text_field($np['tab_label'] ?? '') ?: ($new_kode . ' ' . $new_nama),
                'jenjang_label' => sanitize_text_field($np['jenjang_label'] ?? '') ?: ('Program ' . $new_kode),
                'fakultas_unit' => sanitize_text_field($np['fakultas_unit'] ?? '') ?: 'FIP / Pascasarjana UNM',
                'gelar'         => sanitize_text_field($np['gelar'] ?? ''),
                'beban'         => sanitize_text_field($np['beban'] ?? ''),
                'kkni'          => sanitize_text_field($np['kkni'] ?? ''),
                'kaprodi_nama'  => sanitize_text_field($np['kaprodi_nama'] ?? ''),
                'kaprodi_nip'   => sanitize_text_field($np['kaprodi_nip'] ?? ''),
                'visi_keilmuan' => sanitize_textarea_field($np['visi_keilmuan'] ?? ''),
                'sasaran'       => sanitize_textarea_field($np['sasaran'] ?? ''),
                'status'        => in_array(($np['status'] ?? 'aktif'), array('aktif', 'persiapan'), true) ? $np['status'] : 'aktif',
                'urutan'        => count($prodi_out) + 1,
            );
        }
    }

    uasort($prodi_out, function($a, $b) {
        return ((int) ($a['urutan'] ?? 99)) - ((int) ($b['urutan'] ?? 99));
    });

    $out = array(
        'jurusan' => $jur_out,
        'prodi'   => $prodi_out,
    );
    update_option('ppb_jurusan_units', $out, false);
    return $out;
}
}

/* ========================================================================== */
/* 2. API HELPER UNTUK MODUL LAIN                                             */
/* ========================================================================== */

if (!function_exists('ppb_get_jurusan_info')) {
function ppb_get_jurusan_info() {
    $units = ppb_get_jurusan_units();
    return $units['jurusan'];
}
}

if (!function_exists('ppb_get_prodi_units')) {
function ppb_get_prodi_units($active_only = true) {
    $units = ppb_get_jurusan_units();
    $prodi = $units['prodi'] ?? array();
    if (!$active_only) {
        return $prodi;
    }
    $out = array();
    foreach ($prodi as $slug => $p) {
        if (($p['status'] ?? 'aktif') !== 'nonaktif') {
            $out[$slug] = $p;
        }
    }
    return $out;
}
}

if (!function_exists('ppb_get_unit_options')) {
function ppb_get_unit_options($include_jurusan = true, $active_only = true) {
    $opts = array();
    if ($include_jurusan) {
        $jur = ppb_get_jurusan_info();
        $opts['jurusan'] = '🏛️ Level Jurusan (' . ($jur['singkatan'] ?: 'Jurusan PPB') . ')';
    }
    foreach (ppb_get_prodi_units($active_only) as $slug => $p) {
        $opts[$slug] = ($p['kode'] ? $p['kode'] . ' — ' : '') . $p['nama'];
    }
    return $opts;
}
}

if (!function_exists('ppb_get_unit_label')) {
function ppb_get_unit_label($slug, $short = false) {
    $s = sanitize_key((string) $slug);
    if ($s === '' || $s === 'semua' || $s === 'all') {
        return 'Semua Unit';
    }
    if ($s === 'jurusan') {
        $jur = ppb_get_jurusan_info();
        return $short ? 'Jurusan PPB' : ($jur['nama'] ?? 'Jurusan PPB');
    }
    $prodi = ppb_get_prodi_units(false);
    if (isset($prodi[$s])) {
        if ($short) {
            return $prodi[$s]['tab_label'] ?: $prodi[$s]['kode'];
        }
        return $prodi[$s]['nama'];
    }
    return strtoupper($s);
}
}

if (!function_exists('ppb_normalize_unit_slug')) {
function ppb_normalize_unit_slug($raw, $default = 's1') {
    $raw_low = strtolower(trim((string) $raw));
    $s       = preg_replace('/[^a-z0-9_-]/', '', $raw_low);
    if ($s === 'sarjana') $s = 's1';
    if ($s === 'magister' || $s === 'pascasarjana') $s = 's2';
    if ($s === 'doktor' || $s === 'doktoral') $s = 's3';
    $prodi = ppb_get_prodi_units(false);
    if ($s === 'jurusan' || isset($prodi[$s])) {
        return $s;
    }
    if ($raw_low !== '') {
        foreach ($prodi as $p_slug => $p_meta) {
            if (preg_match('/(?:^|[^a-z0-9])' . preg_quote($p_slug, '/') . '(?:$|[^a-z0-9])/', $raw_low)) {
                return $p_slug;
            }
            $kode_low = strtolower(trim((string) ($p_meta['kode'] ?? '')));
            if ($kode_low !== '' && $raw_low === $kode_low) {
                return $p_slug;
            }
        }
        if (strpos($raw_low, 'doktor') !== false || strpos($raw_low, 'disertasi') !== false) {
            return 's3';
        }
        if (strpos($raw_low, 'magister') !== false || strpos($raw_low, 'pascasarjana') !== false || strpos($raw_low, 'tesis') !== false) {
            return 's2';
        }
        if (strpos($raw_low, 'sarjana') !== false || strpos($raw_low, 'skripsi') !== false) {
            return 's1';
        }
        if (strpos($raw_low, 'jurusan') !== false || strpos($raw_low, 'payung') !== false) {
            return 'jurusan';
        }
    }
    return $default;
}
}

if (!function_exists('ppb_resolve_active_unit_slug')) {
function ppb_resolve_active_unit_slug($allowed_slugs = null, $default = 's1') {
    if ($allowed_slugs === null) {
        $allowed_slugs = array_keys(ppb_get_prodi_units(true));
    }
    $candidates = array();
    if (!empty($GLOBALS['ppb_active_akreditasi_unit'])) {
        $candidates[] = $GLOBALS['ppb_active_akreditasi_unit'];
    }
    if (!empty($_GET['jenjang'])) {
        $candidates[] = wp_unslash($_GET['jenjang']);
    }
    if (!empty($_GET['prodi'])) {
        $candidates[] = wp_unslash($_GET['prodi']);
    }
    if (!empty($_GET['unit'])) {
        $candidates[] = wp_unslash($_GET['unit']);
    }
    foreach ($candidates as $cand) {
        $norm = ppb_normalize_unit_slug($cand, '');
        if ($norm !== '' && in_array($norm, $allowed_slugs, true)) {
            return $norm;
        }
    }
    return in_array($default, $allowed_slugs, true) ? $default : ($allowed_slugs[0] ?? 's1');
}
}

/* ========================================================================== */
/* 3. HALAMAN ADMIN: PENGATURAN UNIT & PRODI JURUSAN PPB                      */
/* ========================================================================== */

if (!function_exists('ppb_render_unit_registry_admin_page')) {
function ppb_render_unit_registry_admin_page() {
    if (!current_user_can('manage_options')) {
        wp_die('Anda tidak memiliki izin mengakses halaman ini.');
    }

    $notice = '';
    if (isset($_POST['ppb_save_units_submit']) && check_admin_referer('ppb_units_save_action', 'ppb_units_nonce')) {
        $units = ppb_save_jurusan_units($_POST);
        $notice = '<div class="notice notice-success is-dismissible"><p><strong>✅ Registry Unit &amp; Prodi Jurusan PPB berhasil disimpan!</strong> Seluruh modul (LAMDIK, Dokumen Mutu, Kurikulum, Jadwal Ujian, Kemahasiswaan, Survei GPM, dan Navigasi) otomatis mengikuti daftar ini.</p></div>';
    } else {
        $units = ppb_get_jurusan_units();
    }

    $jur   = $units['jurusan'];
    $prodi = $units['prodi'];
    $core  = array('s1', 's2', 's3');
    ?>
    <div class="wrap" style="max-width:1180px;margin:20px auto;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;color:#1e293b;">
        <style>
            .ppb-reg-hero { background:linear-gradient(135deg,#0D3B22 0%,#185333 100%); color:#fff; border-radius:16px; padding:26px 28px; display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:18px; margin-bottom:20px; box-shadow:0 8px 22px rgba(13,59,34,0.16); }
            .ppb-reg-card { background:#fff; border:1px solid #cbd5e1; border-radius:14px; padding:22px 24px; margin-bottom:22px; box-shadow:0 3px 10px rgba(0,0,0,0.03); }
            .ppb-reg-card h2 { margin:0 0 6px; font-size:17px; font-weight:800; color:#0D3B22; }
            .ppb-reg-desc { margin:0 0 18px; font-size:13px; color:#64748b; }
            .ppb-reg-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:14px; }
            .ppb-reg-field { background:#FAF8F5; border:1px solid #e2e8f0; border-radius:10px; padding:12px; }
            .ppb-reg-field label { display:block; font-weight:700; font-size:12px; color:#0D3B22; margin-bottom:5px; }
            .ppb-reg-field input, .ppb-reg-field select, .ppb-reg-field textarea { width:100%; padding:7px 10px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px; box-sizing:border-box; }
            .ppb-prodi-box { background:#FAF8F5; border:1.5px solid #D4E3D8; border-radius:14px; padding:18px; margin-bottom:16px; }
        </style>

        <div class="ppb-reg-hero">
            <div style="max-width:760px;">
                <span style="display:inline-block;background:rgba(245,184,46,0.2);border:1px solid rgba(245,184,46,0.45);color:#F5B82E;font-size:11px;font-weight:700;padding:3px 12px;border-radius:999px;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;">Fondasi Arsitektur Jurusan PPB</span>
                <h1 style="margin:0 0 6px;color:#fff;font-size:24px;font-weight:800;">🏛️ Registry Unit &amp; Program Studi Jurusan PPB</h1>
                <p style="margin:0;color:rgba(255,255,255,0.88);font-size:13.5px;line-height:1.55;">
                    Kelola identitas payung <strong>Jurusan PPB FIP UNM</strong> beserta daftar Program Studi di bawahnya (<strong>S1, S2, S3</strong>, hingga penambahan prodi baru seperti <strong>PPG BK</strong>). Perubahan di sini otomatis mengatur tab LAMDIK, Dokumen Mutu, Struktur Pimpinan, Kurikulum, Jadwal Ujian, dan Survei GPM.
                </p>
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:8px;">
                <a href="<?php echo esc_url(admin_url('admin.php?page=ppb-lamdik-settings')); ?>" class="button" style="background:#F5B82E;color:#0D3B22;border:none;font-weight:800;padding:8px 15px;height:auto;border-radius:8px;">🏅 Pengaturan LAMDIK</a>
                <a href="<?php echo esc_url(home_url('/profil/struktur/')); ?>" target="_blank" class="button" style="background:rgba(255,255,255,0.14);color:#fff;border:1px solid rgba(255,255,255,0.3);font-weight:600;padding:8px 14px;height:auto;border-radius:8px;">↗ Lihat Struktur Publik</a>
            </div>
        </div>

        <?php echo $notice; ?>

        <form method="post" action="">
            <?php wp_nonce_field('ppb_units_save_action', 'ppb_units_nonce'); ?>

            <!-- LEVEL JURUSAN PPB -->
            <div class="ppb-reg-card">
                <h2>🏛️ 1. Identitas &amp; Pimpinan Payung Tingkat Jurusan PPB</h2>
                <p class="ppb-reg-desc">Data ini tampil sebagai payung tata kelola Jurusan pada halaman <code>/profil/struktur/</code>, <code>/profil/visi-misi/</code>, dan <code>/akreditasi/upm/</code>.</p>

                <div class="ppb-reg-grid">
                    <div class="ppb-reg-field">
                        <label>Nama Resmi Jurusan</label>
                        <input type="text" name="jurusan[nama]" value="<?php echo esc_attr($jur['nama']); ?>" />
                    </div>
                    <div class="ppb-reg-field">
                        <label>Label Singkat Jurusan</label>
                        <input type="text" name="jurusan[singkatan]" value="<?php echo esc_attr($jur['singkatan']); ?>" />
                    </div>
                    <div class="ppb-reg-field">
                        <label>Fakultas / Institusi Induk</label>
                        <input type="text" name="jurusan[fakultas]" value="<?php echo esc_attr($jur['fakultas']); ?>" />
                    </div>
                    <div class="ppb-reg-field">
                        <label>Jabatan Pimpinan Jurusan</label>
                        <input type="text" name="jurusan[pimpinan_jabatan]" value="<?php echo esc_attr($jur['pimpinan_jabatan']); ?>" />
                    </div>
                    <div class="ppb-reg-field">
                        <label>Nama Ketua Jurusan</label>
                        <input type="text" name="jurusan[pimpinan_nama]" value="<?php echo esc_attr($jur['pimpinan_nama']); ?>" />
                    </div>
                    <div class="ppb-reg-field">
                        <label>NIP Ketua Jurusan</label>
                        <input type="text" name="jurusan[pimpinan_nip]" value="<?php echo esc_attr($jur['pimpinan_nip']); ?>" />
                    </div>
                    <div class="ppb-reg-field">
                        <label>Nama Sekretaris Jurusan</label>
                        <input type="text" name="jurusan[sekretaris_nama]" value="<?php echo esc_attr($jur['sekretaris_nama']); ?>" />
                    </div>
                    <div class="ppb-reg-field">
                        <label>NIP Sekretaris Jurusan</label>
                        <input type="text" name="jurusan[sekretaris_nip]" value="<?php echo esc_attr($jur['sekretaris_nip']); ?>" />
                    </div>
                    <div class="ppb-reg-field">
                        <label>Ketua GPM Jurusan</label>
                        <input type="text" name="jurusan[gpm_ketua]" value="<?php echo esc_attr($jur['gpm_ketua']); ?>" />
                    </div>
                    <div class="ppb-reg-field">
                        <label>Kepala Laboratorium BK</label>
                        <input type="text" name="jurusan[lab_kepala]" value="<?php echo esc_attr($jur['lab_kepala']); ?>" />
                    </div>
                    <div class="ppb-reg-field" style="grid-column:1 / -1;">
                        <label>Visi Payung Jurusan PPB</label>
                        <textarea name="jurusan[visi_payung]" rows="2"><?php echo esc_textarea($jur['visi_payung']); ?></textarea>
                    </div>
                    <div class="ppb-reg-field" style="grid-column:1 / -1;">
                        <label>Misi Payung Jurusan PPB (1 poin per baris)</label>
                        <textarea name="jurusan[misi_payung]" rows="3"><?php echo esc_textarea($jur['misi_payung']); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- DAFTAR PROGRAM STUDI DI BAWAH JURUSAN -->
            <div class="ppb-reg-card">
                <h2>🎓 2. Daftar Program Studi / Jenjang di Bawah Jurusan PPB (<?php echo count($prodi); ?> Unit)</h2>
                <p class="ppb-reg-desc">Setiap Program Studi di bawah ini otomatis memperoleh ruang terpisah untuk 9 Kriteria LAMDIK, Dokumen Mutu, Kurikulum, Jadwal Ujian, Statistik Mahasiswa, dan Survei GPM.</p>

                <?php foreach ($prodi as $slug => $p) :
                    $is_core = in_array($slug, $core, true);
                ?>
                    <div class="ppb-prodi-box">
                        <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:10px;border-bottom:1px solid #D4E3D8;padding-bottom:10px;margin-bottom:14px;">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <span style="background:#0D3B22;color:#F5B82E;font-family:monospace;font-size:12px;font-weight:800;padding:4px 10px;border-radius:6px;"><?php echo esc_html($p['kode']); ?></span>
                                <strong style="font-size:16px;color:#0D3B22;"><?php echo esc_html($p['nama']); ?></strong>
                                <code style="font-size:11px;background:#fff;padding:2px 7px;border-radius:5px;border:1px solid #cbd5e1;">slug: <?php echo esc_html($slug); ?></code>
                            </div>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <label style="font-size:12px;font-weight:700;color:#475569;">Status:</label>
                                <select name="prodi[<?php echo esc_attr($slug); ?>][status]" style="padding:4px 10px;border-radius:6px;border:1px solid #cbd5e1;font-size:12px;font-weight:700;">
                                    <option value="aktif" <?php selected($p['status'], 'aktif'); ?>>✅ Aktif</option>
                                    <option value="persiapan" <?php selected($p['status'], 'persiapan'); ?>>🚧 Persiapan</option>
                                    <?php if (!$is_core) : ?>
                                        <option value="nonaktif" <?php selected($p['status'], 'nonaktif'); ?>>⏸️ Nonaktif</option>
                                    <?php endif; ?>
                                </select>
                                <?php if (!$is_core) : ?>
                                    <label style="font-size:12px;color:#dc2626;font-weight:700;cursor:pointer;">
                                        <input type="checkbox" name="prodi[<?php echo esc_attr($slug); ?>][delete]" value="1" /> Hapus Prodi
                                    </label>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="ppb-reg-grid">
                            <div class="ppb-reg-field" style="background:#fff;">
                                <label>Kode Jenjang (S1/S2/S3/PPG)</label>
                                <input type="text" name="prodi[<?php echo esc_attr($slug); ?>][kode]" value="<?php echo esc_attr($p['kode']); ?>" />
                            </div>
                            <div class="ppb-reg-field" style="background:#fff;">
                                <label>Nama Resmi Program Studi</label>
                                <input type="text" name="prodi[<?php echo esc_attr($slug); ?>][nama]" value="<?php echo esc_attr($p['nama']); ?>" />
                            </div>
                            <div class="ppb-reg-field" style="background:#fff;">
                                <label>Label Tab Publik (Ringkas)</label>
                                <input type="text" name="prodi[<?php echo esc_attr($slug); ?>][tab_label]" value="<?php echo esc_attr($p['tab_label']); ?>" />
                            </div>
                            <div class="ppb-reg-field" style="background:#fff;">
                                <label>Label Jenjang Lengkap</label>
                                <input type="text" name="prodi[<?php echo esc_attr($slug); ?>][jenjang_label]" value="<?php echo esc_attr($p['jenjang_label']); ?>" />
                            </div>
                            <div class="ppb-reg-field" style="background:#fff;">
                                <label>Unit / Fakultas Pengelola</label>
                                <input type="text" name="prodi[<?php echo esc_attr($slug); ?>][fakultas_unit]" value="<?php echo esc_attr($p['fakultas_unit']); ?>" />
                            </div>
                            <div class="ppb-reg-field" style="background:#fff;">
                                <label>Gelar Lulusan</label>
                                <input type="text" name="prodi[<?php echo esc_attr($slug); ?>][gelar]" value="<?php echo esc_attr($p['gelar']); ?>" />
                            </div>
                            <div class="ppb-reg-field" style="background:#fff;">
                                <label>Beban Studi &amp; Masa Tempuh</label>
                                <input type="text" name="prodi[<?php echo esc_attr($slug); ?>][beban]" value="<?php echo esc_attr($p['beban']); ?>" />
                            </div>
                            <div class="ppb-reg-field" style="background:#fff;">
                                <label>Level KKNI</label>
                                <input type="text" name="prodi[<?php echo esc_attr($slug); ?>][kkni]" value="<?php echo esc_attr($p['kkni']); ?>" />
                            </div>
                            <div class="ppb-reg-field" style="background:#fff;">
                                <label>Nama Kaprodi / Koordinator Prodi</label>
                                <input type="text" name="prodi[<?php echo esc_attr($slug); ?>][kaprodi_nama]" value="<?php echo esc_attr($p['kaprodi_nama']); ?>" />
                            </div>
                            <div class="ppb-reg-field" style="background:#fff;">
                                <label>NIP Kaprodi / Koordinator</label>
                                <input type="text" name="prodi[<?php echo esc_attr($slug); ?>][kaprodi_nip]" value="<?php echo esc_attr($p['kaprodi_nip']); ?>" />
                            </div>
                            <div class="ppb-reg-field" style="background:#fff;">
                                <label>Urutan Tampil</label>
                                <input type="number" min="1" max="50" name="prodi[<?php echo esc_attr($slug); ?>][urutan]" value="<?php echo (int) ($p['urutan'] ?? 1); ?>" />
                            </div>
                            <div class="ppb-reg-field" style="background:#fff;grid-column:1 / -1;">
                                <label>Visi Keilmuan Prodi</label>
                                <textarea name="prodi[<?php echo esc_attr($slug); ?>][visi_keilmuan]" rows="2"><?php echo esc_textarea($p['visi_keilmuan']); ?></textarea>
                            </div>
                            <div class="ppb-reg-field" style="background:#fff;grid-column:1 / -1;">
                                <label>Sasaran &amp; Profil Lulusan</label>
                                <textarea name="prodi[<?php echo esc_attr($slug); ?>][sasaran]" rows="2"><?php echo esc_textarea($p['sasaran']); ?></textarea>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- TAMBAH PRODI BARU (PERSIAPAN EKSPANSI JURUSAN) -->
            <div class="ppb-reg-card" style="border-style:dashed;border-color:#0D3B22;">
                <h2>➕ 3. Tambah Program Studi Baru di Bawah Jurusan PPB (Opsional)</h2>
                <p class="ppb-reg-desc">Gunakan bagian ini apabila Jurusan PPB membuka atau mempersiapkan Prodi baru (misalnya <code>ppg</code> untuk Pendidikan Profesi Guru BK atau prodi baru lainnya). Biarkan kosong jika tidak menambah prodi.</p>

                <div class="ppb-reg-grid">
                    <div class="ppb-reg-field">
                        <label>Slug Unik (huruf kecil, tanpa spasi)</label>
                        <input type="text" name="new_prodi[slug]" placeholder="contoh: ppg" />
                    </div>
                    <div class="ppb-reg-field">
                        <label>Kode Singkat</label>
                        <input type="text" name="new_prodi[kode]" placeholder="contoh: PPG" />
                    </div>
                    <div class="ppb-reg-field">
                        <label>Nama Resmi Program Studi</label>
                        <input type="text" name="new_prodi[nama]" placeholder="contoh: PPG Bimbingan dan Konseling" />
                    </div>
                    <div class="ppb-reg-field">
                        <label>Label Tab Ringkas</label>
                        <input type="text" name="new_prodi[tab_label]" placeholder="contoh: PPG Profesi" />
                    </div>
                    <div class="ppb-reg-field">
                        <label>Status Awal</label>
                        <select name="new_prodi[status]">
                            <option value="aktif">✅ Aktif</option>
                            <option value="persiapan">🚧 Persiapan</option>
                        </select>
                    </div>
                    <div class="ppb-reg-field">
                        <label>Nama Kaprodi / Koordinator</label>
                        <input type="text" name="new_prodi[kaprodi_nama]" placeholder="Nama &amp; Gelar" />
                    </div>
                </div>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:12px;margin-bottom:30px;">
                <button type="submit" name="ppb_save_units_submit" class="button button-primary" style="background:#0D3B22;border-color:#0D3B22;color:#F5B82E;font-weight:800;font-size:14px;padding:10px 26px;height:auto;border-radius:10px;box-shadow:0 4px 12px rgba(13,59,34,0.2);">
                    💾 Simpan Registry Unit &amp; Prodi Jurusan PPB
                </button>
            </div>
        </form>
    </div>
    <?php
}
}
