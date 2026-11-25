<?php
/**
 * Registry Skema & Resolver Dataset Import/Export Excel (.xlsx/.csv) & JSON
 * Mendefinisikan ke-16 dataset Portal PPB FIP UNM, alias kolom, data contoh,
 * serta resolver Cerdas untuk mencocokkan nama/NIP/slug dosen pengampu.
 */

if (!defined('ABSPATH')) exit;

/* ========================================================================== */
/* 1. HELPER NORMALISASI NILAI & RESOLVER DOSEN CERDAS                        */
/* ========================================================================== */

if (!function_exists('ppb_excel_clean_doc_url')) {
function ppb_excel_clean_doc_url($url) {
    $u = trim((string) $url);
    if ($u === '' || $u === '-') return '';
    if (function_exists('ppb_clean_document_url')) {
        return ppb_clean_document_url($u);
    }
    if ($u === '#' || stripos($u, 'javascript:') === 0) return '';
    return esc_url_raw($u);
}
}

if (!function_exists('ppb_excel_normalize_date')) {
function ppb_excel_normalize_date($val) {
    $v = trim((string) $val);
    if ($v === '') return '';
    if (preg_match('/^(\d{4})[-\/](\d{1,2})[-\/](\d{1,2})$/', $v, $m)) {
        return sprintf('%04d-%02d-%02d', (int) $m[1], (int) $m[2], (int) $m[3]);
    }
    if (preg_match('/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})$/', $v, $m)) {
        return sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[2], (int) $m[1]);
    }
    // Jika serial angka Excel tanpa style tanggal
    if (is_numeric($v) && (float) $v >= 20000 && (float) $v <= 90000 && function_exists('ppb_io_excel_serial_to_date')) {
        return ppb_io_excel_serial_to_date((float) $v, false);
    }
    $ts = strtotime($v);
    if ($ts !== false) {
        return gmdate('Y-m-d', $ts);
    }
    return sanitize_text_field($v);
}
}

if (!function_exists('ppb_excel_parse_bool')) {
function ppb_excel_parse_bool($val, $default = true) {
    if (is_bool($val)) return $val;
    $v = strtolower(trim((string) $val));
    if ($v === '') return (bool) $default;
    if (in_array($v, array('0', 'false', 'tidak', 'no', 'n', 'off', 'nonaktif'), true)) {
        return false;
    }
    if (in_array($v, array('1', 'true', 'ya', 'yes', 'y', 'on', 'aktif'), true)) {
        return true;
    }
    return (bool) $default;
}
}

if (!function_exists('ppb_excel_strip_dosen_titles')) {
function ppb_excel_strip_dosen_titles($name) {
    $s = trim((string) $name);
    if ($s === '') return '';
    // Buang gelar belakang setelah koma jika berupa singkatan gelar akademik
    $parts = explode(',', $s);
    $core  = trim((string) array_shift($parts));
    // Buang gelar depan umum (Prof., Dr., Drs., Dra., Ir., H., Hj.)
    $core = preg_replace('/^(?:\s*(?:Prof|Dr|Drs|Dra|Ir|KH|Hj|H)\.?\s+)+/i', '', $core);
    // Normalisasi karakter non-alfanumerik
    $core = strtolower(trim((string) $core));
    $core = preg_replace('/[^a-z0-9\s]/', ' ', $core);
    $core = preg_replace('/\s+/', ' ', (string) $core);
    return trim((string) $core);
}
}

if (!function_exists('ppb_excel_split_dosen_tokens')) {
function ppb_excel_split_dosen_tokens($raw) {
    if (is_array($raw)) {
        $out = array();
        foreach ($raw as $item) {
            $t = trim((string) $item);
            if ($t !== '') $out[] = $t;
        }
        return $out;
    }

    $s = trim((string) $raw);
    if ($s === '') return array();

    $s = str_replace(array("\r\n", "\r", "\n", "|"), ';', $s);
    if (strpos($s, ';') !== false) {
        $raw_tokens = explode(';', $s);
        $tokens = array();
        foreach ($raw_tokens as $rt) {
            $rt = trim($rt);
            if ($rt !== '') $tokens[] = $rt;
        }
        return $tokens;
    }

    if (strpos($s, ',') === false) {
        return array($s);
    }

    // Tangani pemisah koma sambil menjaga gelar akademik (mis. ", S.Pd., M.Pd., Kons.")
    $segments = array_map('trim', explode(',', $s));
    $degree_re = '/^(?:(?:S|M|D|B|A)\.[A-Za-z\.]{1,12}|Ph\.?D\.?|Ed\.?D\.?|D\.?Ed\.?|M\.?A\.?|M\.?Sc\.?|M\.?Ed\.?|M\.?Si\.?|M\.?Pd\.?I?|M\.?Psi\.?|M\.?Kom\.?|M\.?Hum\.?|M\.?Ag\.?|M\.?M\.?|M\.?H\.?|M\.?E\.?|S\.?Pd\.?I?|S\.?Psi\.?|S\.?Sos\.?|S\.?Ag\.?|S\.?Kom\.?|S\.?H\.?|S\.?E\.?|Kons\.?|Psikolog\.?|Cht\.?|CI\.?)(?:[\s\/]+(?:(?:S|M|D|B|A)\.[A-Za-z\.]{1,12}|Ph\.?D\.?|Ed\.?D\.?|Kons\.?|Psikolog\.?))*\.?$/i';

    $tokens = array();
    $current = '';
    foreach ($segments as $seg) {
        if ($seg === '') continue;
        if ($current === '') {
            $current = $seg;
            continue;
        }
        if (preg_match($degree_re, $seg)) {
            $current .= ', ' . $seg;
        } else {
            $tokens[] = $current;
            $current = $seg;
        }
    }
    if ($current !== '') {
        $tokens[] = $current;
    }

    return $tokens;
}
}

if (!function_exists('ppb_excel_resolve_dosen_list')) {
function ppb_excel_resolve_dosen_list($raw_value, &$unresolved = array()) {
    $tokens = ppb_excel_split_dosen_tokens($raw_value);
    if (empty($tokens)) return array();

    $dosen_list = function_exists('ppb_get_dosen_directory_cached') ? ppb_get_dosen_directory_cached() : array();
    $by_slug = array();
    $by_nip  = array();
    $by_full = array();
    $by_core = array();

    foreach ($dosen_list as $d) {
        $slug = sanitize_title($d['slug'] ?? '');
        if ($slug === '') continue;
        $by_slug[$slug] = $slug;

        foreach (array('nip', 'nidn') as $id_k) {
            $digits = preg_replace('/\D+/', '', (string) ($d[$id_k] ?? ''));
            if (strlen($digits) >= 8) {
                $by_nip[$digits] = $slug;
            }
        }

        foreach (array('nama_lengkap', 'nama', 'nama_panggilan') as $nk) {
            $nval = trim((string) ($d[$nk] ?? ''));
            if ($nval === '') continue;
            $norm = strtolower((string) preg_replace('/[^a-z0-9]/', '', $nval));
            if ($norm !== '') $by_full[$norm] = $slug;

            $core = ppb_excel_strip_dosen_titles($nval);
            if ($core !== '') {
                if (!isset($by_core[$core])) {
                    $by_core[$core] = $slug;
                } elseif ($by_core[$core] !== $slug) {
                    $by_core[$core] = false; // Ambigu, lewati
                }
            }
        }
    }

    $resolved = array();
    foreach ($tokens as $tok) {
        $matched = '';
        $slug_try = sanitize_title($tok);
        $digits   = preg_replace('/\D+/', '', $tok);
        $full_try = strtolower((string) preg_replace('/[^a-z0-9]/', '', $tok));
        $core_try = ppb_excel_strip_dosen_titles($tok);

        if ($slug_try !== '' && isset($by_slug[$slug_try])) {
            $matched = $by_slug[$slug_try];
        } elseif (strlen($digits) >= 8 && isset($by_nip[$digits])) {
            $matched = $by_nip[$digits];
        } elseif ($full_try !== '' && isset($by_full[$full_try])) {
            $matched = $by_full[$full_try];
        } elseif ($core_try !== '' && !empty($by_core[$core_try])) {
            $matched = $by_core[$core_try];
        }

        if ($matched !== '') {
            if (!in_array($matched, $resolved, true)) {
                $resolved[] = $matched;
            }
        } else {
            $unresolved[] = $tok;
        }
    }

    return $resolved;
}
}

/* ========================================================================== */
/* 2. KATALOG DEFINISI 16 DATASET PORTAL PPB                                  */
/* ========================================================================== */

if (!function_exists('ppb_excel_get_datasets')) {
function ppb_excel_get_datasets() {
    return array(
        'matkul' => array(
            'key'           => 'matkul',
            'label'         => 'Mata Kuliah OBE & Dosen Pengampu',
            'icon'          => '📚',
            'group'         => 'Akademik & Kurikulum',
            'storage_type'  => 'option',
            'capability'    => 'edit_posts',
            'allow_replace' => true,
            'admin_url'     => 'admin.php?page=ppb-mata-kuliah-manager',
            'description'   => 'Data mata kuliah S1/S2/S3 beserta tautan RPS/SAP dan penugasan dosen pengampu (otomatis tersinkron ke profil dosen & /akademik/kurikulum/).',
            'columns' => array(
                array('key' => 'kode',           'label' => 'Kode MK*',           'aliases' => array('kode_mk', 'kode_mata_kuliah', 'code')),
                array('key' => 'nama',           'label' => 'Nama Mata Kuliah*',  'aliases' => array('nama_mk', 'mata_kuliah', 'course_name', 'judul')),
                array('key' => 'jenjang',        'label' => 'Jenjang (S1/S2/S3)', 'aliases' => array('program', 'strata', 'degree')),
                array('key' => 'semester',       'label' => 'Semester (1-8)',     'aliases' => array('sem')),
                array('key' => 'sks',            'label' => 'SKS',                'aliases' => array('bobot_sks')),
                array('key' => 'rumpun',         'label' => 'Rumpun / Sifat MK',  'aliases' => array('sifat', 'kategori', 'rumpun_mk')),
                array('key' => 'sdg',            'label' => 'Tag SDG',            'aliases' => array('sdgs')),
                array('key' => 'sem_title',      'label' => 'Subjudul Semester',  'aliases' => array('semtitle', 'tema_semester')),
                array('key' => 'dosen_pengampu', 'label' => 'Dosen Pengampu (Slug / NIP / Nama, pisah ;)', 'aliases' => array('dosen', 'dosen_slugs', 'pengampu', 'nama_dosen')),
                array('key' => 'cpmk',           'label' => 'CPMK / Deskripsi Singkat', 'aliases' => array('deskripsi')),
                array('key' => 'rps_url',        'label' => 'URL Berkas RPS',     'aliases' => array('rps', 'link_rps')),
                array('key' => 'sap_url',        'label' => 'URL Berkas SAP',     'aliases' => array('sap', 'link_sap')),
                array('key' => 'in_kurikulum',   'label' => 'Tampil Kurikulum (1/0)', 'aliases' => array('kurikulum', 'aktif')),
                array('key' => 'id',             'label' => 'ID Sistem (Opsional)', 'aliases' => array('mk_id')),
            ),
            'sample_rows' => array(
                array(
                    'kode' => 'BK101', 'nama' => 'Dasar-Dasar Bimbingan dan Konseling', 'jenjang' => 'S1',
                    'semester' => 1, 'sks' => 3, 'rumpun' => 'Wajib Prodi', 'sdg' => 'SDG 4',
                    'sem_title' => 'Pondasi Keilmuan & Profesi Konseling',
                    'dosen_pengampu' => 'suciani-latif; farida-aryani',
                    'cpmk' => 'Mahasiswa memahami landasan filosofis, historis, prinsip, dan etika profesi BK.',
                    'rps_url' => 'https://drive.google.com/file/d/CONTOH_RPS_BK101/view',
                    'sap_url' => 'https://drive.google.com/file/d/CONTOH_SAP_BK101/view',
                    'in_kurikulum' => 1, 'id' => '',
                ),
                array(
                    'kode' => 'BK701', 'nama' => 'Teori & Pendekatan Konseling Mutakhir', 'jenjang' => 'S2',
                    'semester' => 1, 'sks' => 3, 'rumpun' => 'Wajib Magister', 'sdg' => 'SDG 3',
                    'sem_title' => 'Penguasaan Teori & Riset Lanjut',
                    'dosen_pengampu' => 'Prof. Dr. Abdullah Pandang, M.Pd',
                    'cpmk' => 'Analisis kritis konseling integratif kontemporer dan intervensi berbasis bukti.',
                    'rps_url' => '', 'sap_url' => '', 'in_kurikulum' => 1, 'id' => '',
                ),
            ),
        ),
        'pnbp' => array(
            'key'           => 'pnbp',
            'label'         => 'Hibah Penelitian & PkM (MIS PNBP)',
            'icon'          => '🔬',
            'group'         => 'Akademik & Kurikulum',
            'storage_type'  => 'option',
            'capability'    => 'edit_posts',
            'allow_replace' => true,
            'multi_sheet'   => true,
            'admin_url'     => 'admin.php?page=ppb-akademik-settings&tab=pnbp',
            'description'   => 'Data Hibah Penelitian (Sheet 1) dan Pengabdian kepada Masyarakat / PkM (Sheet 2) untuk /mis-pnbp/.',
            'columns_riset' => array(
                array('key' => 'tahun',       'label' => 'Tahun*',           'aliases' => array('year')),
                array('key' => 'skema',       'label' => 'Skema Hibah*',     'aliases' => array('kategori', 'jenis_hibah')),
                array('key' => 'judul',       'label' => 'Judul Penelitian*','aliases' => array('title', 'judul_riset')),
                array('key' => 'ketua',       'label' => 'Ketua Peneliti',   'aliases' => array('ketua_peneliti')),
                array('key' => 'anggota',     'label' => 'Anggota / Mhs',    'aliases' => array('kolaborator', 'tim')),
                array('key' => 'dana',        'label' => 'Besaran Dana',     'aliases' => array('anggaran', 'nilai_dana')),
                array('key' => 'luaran',      'label' => 'Luaran Wajib',     'aliases' => array('output')),
                array('key' => 'dokumen_url', 'label' => 'URL SK / Laporan', 'aliases' => array('link', 'url', 'dok_url')),
            ),
            'columns_pkm' => array(
                array('key' => 'tahun',       'label' => 'Tahun*',           'aliases' => array('year')),
                array('key' => 'skema',       'label' => 'Skema PkM*',       'aliases' => array('kategori', 'jenis_hibah')),
                array('key' => 'judul',       'label' => 'Judul Pengabdian*','aliases' => array('title', 'judul_pkm')),
                array('key' => 'ketua',       'label' => 'Ketua Pelaksana',  'aliases' => array('ketua_peneliti')),
                array('key' => 'mitra',       'label' => 'Mitra Kegiatan',   'aliases' => array('sasaran', 'lokasi_mitra')),
                array('key' => 'dana',        'label' => 'Besaran Dana',     'aliases' => array('anggaran', 'nilai_dana')),
                array('key' => 'luaran',      'label' => 'Luaran Kegiatan',  'aliases' => array('output')),
                array('key' => 'dokumen_url', 'label' => 'URL SK / Laporan', 'aliases' => array('link', 'url', 'dok_url')),
            ),
            // Kolom datar untuk fallback CSV tunggal
            'columns' => array(
                array('key' => 'jenis',       'label' => 'Jenis (riset/pkm)', 'aliases' => array('tipe', 'kategori_sheet')),
                array('key' => 'tahun',       'label' => 'Tahun*',            'aliases' => array('year')),
                array('key' => 'skema',       'label' => 'Skema*',            'aliases' => array('skema_hibah')),
                array('key' => 'judul',       'label' => 'Judul*',            'aliases' => array('title')),
                array('key' => 'ketua',       'label' => 'Ketua',             'aliases' => array('ketua_peneliti', 'ketua_pelaksana')),
                array('key' => 'anggota',     'label' => 'Anggota / Mitra',   'aliases' => array('mitra', 'kolaborator')),
                array('key' => 'dana',        'label' => 'Besaran Dana',      'aliases' => array('anggaran')),
                array('key' => 'luaran',      'label' => 'Luaran',            'aliases' => array('output')),
                array('key' => 'dokumen_url', 'label' => 'URL SK / Laporan',  'aliases' => array('link', 'url')),
            ),
            'sample_riset' => array(
                array('tahun' => '2026', 'skema' => 'Penelitian Terapan PNBP', 'judul' => 'Pengembangan Instrumen Skrining Kesejahteraan Psikologis Remaja', 'ketua' => 'Dr. Suciani Latif, S.Pd., M.Pd', 'anggota' => 'Tim Dosen & Mahasiswa', 'dana' => 'Rp 40.000.000', 'luaran' => 'Artikel Sinta 2 & Hak Cipta', 'dokumen_url' => ''),
            ),
            'sample_pkm' => array(
                array('tahun' => '2026', 'skema' => 'PkM Kemitraan Sekolah', 'judul' => 'Pelatihan Psychological First Aid untuk Guru BK Makassar', 'ketua' => 'Dr. Nur Fadhilah Umar, S.Pd., M.Pd', 'mitra' => 'MGBK Kota Makassar', 'dana' => 'Rp 25.000.000', 'luaran' => 'Modul Pelatihan & Jurnal PkM', 'dokumen_url' => ''),
            ),
        ),
        'jurnal' => array(
            'key'           => 'jurnal',
            'label'         => 'Jurnal & Pengabdian Prodi BK',
            'icon'          => '📰',
            'group'         => 'Akademik & Kurikulum',
            'storage_type'  => 'option',
            'capability'    => 'edit_posts',
            'allow_replace' => true,
            'admin_url'     => 'admin.php?page=ppb-akademik-settings&tab=jurnal',
            'description'   => 'Daftar portal OJS Jurnal Prodi BK (/jurnal-bk/).',
            'columns' => array(
                array('key' => 'nama',       'label' => 'Nama Jurnal*',      'aliases' => array('judul', 'title')),
                array('key' => 'badge',      'label' => 'Badge / Akreditasi','aliases' => array('label')),
                array('key' => 'tema',       'label' => 'Tema (green/gold/blue)', 'aliases' => array('warna')),
                array('key' => 'issn',       'label' => 'Nomor ISSN',        'aliases' => array('e_issn')),
                array('key' => 'penerbit',   'label' => 'Penerbit',          'aliases' => array('pengelola')),
                array('key' => 'frekuensi',  'label' => 'Frekuensi Terbit',  'aliases' => array('jadwal_terbit')),
                array('key' => 'pengindeks', 'label' => 'Pengindeks',        'aliases' => array('indeks')),
                array('key' => 'deskripsi',  'label' => 'Deskripsi',         'aliases' => array('ringkasan')),
                array('key' => 'ojs_url',    'label' => 'URL Portal OJS',    'aliases' => array('url', 'link_ojs')),
                array('key' => 'submit_url', 'label' => 'URL Submit Naskah', 'aliases' => array('link_submit')),
            ),
            'sample_rows' => array(
                array('nama' => 'Jurnal Bimbingan dan Konseling (JBK)', 'badge' => '🏅 Jurnal Resmi Prodi', 'tema' => 'green', 'issn' => 'E-ISSN: 2620-xxxx', 'penerbit' => 'Prodi BK FIP UNM', 'frekuensi' => '2 Kali Setahun (Mei & November)', 'pengindeks' => 'SINTA, Google Scholar, Garuda', 'deskripsi' => 'Berkala ilmiah resmi Prodi BK FIP UNM.', 'ojs_url' => 'https://ojs.unm.ac.id/JKBK', 'submit_url' => 'https://ojs.unm.ac.id/JKBK/about/submissions'),
            ),
        ),
        'elibrary' => array(
            'key'           => 'elibrary',
            'label'         => 'Katalog E-Library & Modul BK',
            'icon'          => '📖',
            'group'         => 'Layanan & Lab BK',
            'storage_type'  => 'option',
            'capability'    => 'edit_posts',
            'allow_replace' => true,
            'admin_url'     => 'admin.php?page=ppb-elibrary-manager',
            'description'   => 'Koleksi modul praktikum, pedoman akademik, instrumen asesmen, dan artikel (/e-library/).',
            'columns' => array(
                array('key' => 'kode',      'label' => 'Kode Koleksi',       'aliases' => array('code')),
                array('key' => 'title',     'label' => 'Judul Koleksi*',     'aliases' => array('judul', 'nama')),
                array('key' => 'category',  'label' => 'Kategori (modul/buku/asesmen/artikel)', 'aliases' => array('kat')),
                array('key' => 'cat_label', 'label' => 'Label Kategori',     'aliases' => array('badge')),
                array('key' => 'authors',   'label' => 'Penyusun / Penulis', 'aliases' => array('penulis', 'author')),
                array('key' => 'year',      'label' => 'Tahun',              'aliases' => array('tahun')),
                array('key' => 'format',    'label' => 'Format File',        'aliases' => array('ekstensi')),
                array('key' => 'meta',      'label' => 'Spesifikasi Berkas', 'aliases' => array('info', 'halaman_ukuran')),
                array('key' => 'desc',      'label' => 'Deskripsi Singkat',  'aliases' => array('deskripsi')),
                array('key' => 'file_url',  'label' => 'URL Berkas PDF',     'aliases' => array('url', 'link', 'drive_url')),
            ),
            'sample_rows' => array(
                array('kode' => 'ELB-07', 'title' => 'Modul Teknik Konseling Solution-Focused Brief Therapy', 'category' => 'modul', 'cat_label' => 'Modul Praktikum', 'authors' => 'Tim Dosen Lab BK FIP UNM', 'year' => '2026', 'format' => 'PDF', 'meta' => '54 Hlm · PDF 2.4 MB', 'desc' => 'Panduan praktik penerapan SFBT bagi mahasiswa BK.', 'file_url' => 'https://drive.google.com/file/d/CONTOH_MODUL/view'),
            ),
        ),
        'inventaris' => array(
            'key'           => 'inventaris',
            'label'         => 'Inventaris Alat & Instrumen Lab BK',
            'icon'          => '🧪',
            'group'         => 'Layanan & Lab BK',
            'storage_type'  => 'cpt',
            'post_type'     => 'ppb_inventaris_lab',
            'capability'    => 'edit_posts',
            'allow_replace' => true,
            'admin_url'     => 'edit.php?post_type=ppb_inventaris_lab',
            'description'   => 'Data alat tes psikologi, non-tes, mikrokonseling, dan perangkat digital di Lab BK (/lab-bk/inventaris/).',
            'columns' => array(
                array('key' => 'kode_alat',   'label' => 'Kode Alat*',        'aliases' => array('kode', 'code')),
                array('key' => 'nama',        'label' => 'Nama Alat / Fasilitas*', 'aliases' => array('title', 'nama_alat', 'judul')),
                array('key' => 'kategori',    'label' => 'Kategori (tes_psikologi/instrumen_non_tes/fasilitas_mikrokonseling/perangkat_digital)', 'aliases' => array('kat')),
                array('key' => 'jumlah_unit', 'label' => 'Jumlah Unit',       'aliases' => array('jumlah', 'stok')),
                array('key' => 'kondisi',     'label' => 'Kondisi (baik/perlu_kalibrasi/pemeliharaan)', 'aliases' => array('status')),
                array('key' => 'lokasi',      'label' => 'Lokasi Simpan',     'aliases' => array('ruang')),
                array('key' => 'spesifikasi', 'label' => 'Spesifikasi',       'aliases' => array('specs')),
                array('key' => 'keterangan',  'label' => 'Keterangan',        'aliases' => array('catatan')),
                array('key' => 'manual_link', 'label' => 'URL Manual / SOP',  'aliases' => array('sop_url', 'manual_url')),
                array('key' => 'foto_link',   'label' => 'URL Foto Alat',     'aliases' => array('foto_url', 'gambar')),
            ),
            'sample_rows' => array(
                array('kode_alat' => 'TES-KEP-EPPS', 'nama' => 'Edwards Personal Preference Schedule (EPPS)', 'kategori' => 'tes_psikologi', 'jumlah_unit' => '30 Paket', 'kondisi' => 'baik', 'lokasi' => 'Lemari Asesmen A-04 Lab BK', 'spesifikasi' => 'Buku Soal, Lembar Jawab, Kunci Skoring', 'keterangan' => 'Siap dipinjam untuk praktikum asesmen kepribadian.', 'manual_link' => '', 'foto_link' => ''),
            ),
        ),
        'lab_kegiatan' => array(
            'key'           => 'lab_kegiatan',
            'label'         => 'Kegiatan & Praktikum Lab BK',
            'icon'          => '🧩',
            'group'         => 'Layanan & Lab BK',
            'storage_type'  => 'option',
            'capability'    => 'edit_posts',
            'allow_replace' => true,
            'admin_url'     => 'admin.php?page=ppb-layanan-settings&tab=lab_kegiatan',
            'description'   => 'Daftar kegiatan praktikum dan program rutin Lab BK (/layanan/laboratorium/kegiatan/).',
            'columns' => array(
                array('key' => 'judul',     'label' => 'Nama Kegiatan*',    'aliases' => array('nama', 'title')),
                array('key' => 'periode',   'label' => 'Periode',           'aliases' => array('waktu', 'semester')),
                array('key' => 'peserta',   'label' => 'Sasaran Peserta',   'aliases' => array('sasaran')),
                array('key' => 'status',    'label' => 'Status',            'aliases' => array('kondisi')),
                array('key' => 'deskripsi', 'label' => 'Deskripsi',         'aliases' => array('ringkasan')),
                array('key' => 'dok_url',   'label' => 'URL Panduan / Dok', 'aliases' => array('url', 'link')),
            ),
            'sample_rows' => array(
                array('judul' => 'Praktikum Asesmen Teknik Tes & Non-Tes', 'periode' => 'Genap 2025/2026', 'peserta' => 'Mahasiswa Angkatan 2024', 'status' => 'Aktif Berjalan', 'deskripsi' => 'Praktik administrasi instrumen tes psikologi dan AUM.', 'dok_url' => ''),
            ),
        ),
        'lab_lomba' => array(
            'key'           => 'lab_lomba',
            'label'         => 'Info Lomba Mahasiswa Lab BK',
            'icon'          => '🏅',
            'group'         => 'Layanan & Lab BK',
            'storage_type'  => 'option',
            'capability'    => 'edit_posts',
            'allow_replace' => true,
            'admin_url'     => 'admin.php?page=ppb-layanan-settings&tab=lab_kegiatan',
            'description'   => 'Daftar kompetisi binaan Laboratorium BK (/layanan/laboratorium/lomba/).',
            'columns' => array(
                array('key' => 'nama',          'label' => 'Nama Lomba*',     'aliases' => array('judul', 'title')),
                array('key' => 'penyelenggara', 'label' => 'Penyelenggara',   'aliases' => array('panitia')),
                array('key' => 'kategori',      'label' => 'Tingkat / Kategori', 'aliases' => array('tingkat')),
                array('key' => 'prestasi',      'label' => 'Capaian / Prestasi', 'aliases' => array('capaian')),
                array('key' => 'lingkup',       'label' => 'Bidang / Lingkup', 'aliases' => array('bidang')),
                array('key' => 'dok_url',       'label' => 'URL Juknis / Sertifikat', 'aliases' => array('url', 'link')),
            ),
            'sample_rows' => array(
                array('nama' => 'Lomba Media Konseling Inovatif 2026', 'penyelenggara' => 'Lab BK FIP UNM', 'kategori' => 'Tingkat Nasional', 'prestasi' => 'Juara 1 Media Interaktif', 'lingkup' => 'Inovasi Layanan BK', 'dok_url' => ''),
            ),
        ),
        'sarpras' => array(
            'key'           => 'sarpras',
            'label'         => 'Sarana & Prasarana Ruangan',
            'icon'          => '🏛️',
            'group'         => 'Layanan & Lab BK',
            'storage_type'  => 'option',
            'capability'    => 'edit_posts',
            'allow_replace' => true,
            'admin_url'     => 'admin.php?page=ppb-layanan-settings&tab=sarpras',
            'description'   => 'Daftar ruang kuliah, laboratorium, ruang konseling, dan foto dokumentasi (/profil/sarana-prasarana/).',
            'columns' => array(
                array('key' => 'nama',      'label' => 'Nama Fasilitas / Ruangan*', 'aliases' => array('judul', 'title')),
                array('key' => 'kategori',  'label' => 'Kategori Label',     'aliases' => array('kat')),
                array('key' => 'kapasitas', 'label' => 'Kapasitas',          'aliases' => array('daya_tampung')),
                array('key' => 'lokasi',    'label' => 'Lokasi Gedung',      'aliases' => array('gedung', 'lantai')),
                array('key' => 'icon',      'label' => 'Ikon Emoji',         'aliases' => array('emoji')),
                array('key' => 'tema',      'label' => 'Tema (green/gold/indigo)', 'aliases' => array('warna')),
                array('key' => 'deskripsi', 'label' => 'Deskripsi Fasilitas','aliases' => array('spesifikasi')),
                array('key' => 'foto_url',  'label' => 'URL Foto Ruangan',   'aliases' => array('foto', 'gambar')),
                array('key' => 'dok_url',   'label' => 'URL SOP / Denah',    'aliases' => array('url', 'link')),
            ),
            'sample_rows' => array(
                array('nama' => 'Studio Micro-Counseling & Podcast BK', 'kategori' => 'Laboratorium BK', 'kapasitas' => '15 Mahasiswa', 'lokasi' => 'Gedung Lab BK Lt. 2', 'icon' => '🎙️', 'tema' => 'green', 'deskripsi' => 'Dilengkapi sistem rekam multi-sudut dan peredam suara akustik.', 'foto_url' => '', 'dok_url' => ''),
            ),
        ),
        'maba_tren' => array(
            'key'           => 'maba_tren',
            'label'         => 'Tren Daya Tampung & Peminat Maba',
            'icon'          => '🎓',
            'group'         => 'Kemahasiswaan & Alumni',
            'storage_type'  => 'option',
            'capability'    => 'edit_posts',
            'allow_replace' => true,
            'admin_url'     => 'admin.php?page=ppb-kemahasiswaan-settings&tab=statistik',
            'description'   => 'Tabel data penerimaan mahasiswa baru SNBP, SNBT, dan Mandiri per tahun (/kemahasiswaan/penerimaan/).',
            'columns' => array(
                array('key' => 'jenjang',         'label' => 'Jenjang (s1/s2/s3)', 'aliases' => array('unit', 'prodi', 'program_studi')),
                array('key' => 'tahun',           'label' => 'Tahun*',             'aliases' => array('year')),
                array('key' => 'daya_tampung',    'label' => 'Daya Tampung*',      'aliases' => array('kuota')),
                array('key' => 'peminat_snbp',    'label' => 'Peminat SNBP / Reguler', 'aliases' => array('snbp', 'reguler')),
                array('key' => 'peminat_snbt',    'label' => 'Peminat SNBT / Kerjasama', 'aliases' => array('snbt', 'kerjasama')),
                array('key' => 'peminat_mandiri', 'label' => 'Peminat Mandiri / Beasiswa', 'aliases' => array('mandiri', 'beasiswa')),
                array('key' => 'total_peminat',   'label' => 'Total Peminat (0 = auto)', 'aliases' => array('total')),
                array('key' => 'keketatan',       'label' => 'Rasio Keketatan (kosong = auto)', 'aliases' => array('rasio')),
            ),
            'sample_rows' => array(
                array('jenjang' => 's1', 'tahun' => '2025', 'daya_tampung' => 130, 'peminat_snbp' => 510, 'peminat_snbt' => 1210, 'peminat_mandiri' => 320, 'total_peminat' => 2040, 'keketatan' => '1 : 16'),
                array('jenjang' => 's2', 'tahun' => '2025', 'daya_tampung' => 30, 'peminat_snbp' => 45, 'peminat_snbt' => 15, 'peminat_mandiri' => 12, 'total_peminat' => 72, 'keketatan' => '1 : 2'),
            ),
        ),
        'mbkm' => array(
            'key'           => 'mbkm',
            'label'         => 'Pameran Inovasi MBKM',
            'icon'          => '🤝',
            'group'         => 'Kemahasiswaan & Alumni',
            'storage_type'  => 'option',
            'capability'    => 'edit_posts',
            'allow_replace' => true,
            'admin_url'     => 'admin.php?page=ppb-kemahasiswaan-settings&tab=mbkm',
            'description'   => 'Daftar kegiatan dan pameran inovasi MBKM Prodi BK (/kemahasiswaan/mbkm-kemanusiaan/).',
            'columns' => array(
                array('key' => 'tahun',     'label' => 'Tahun / Periode*',  'aliases' => array('periode', 'year')),
                array('key' => 'lokasi',    'label' => 'Lokasi Kegiatan',   'aliases' => array('tempat')),
                array('key' => 'judul',     'label' => 'Judul Kegiatan*',   'aliases' => array('nama', 'title')),
                array('key' => 'deskripsi', 'label' => 'Deskripsi Singkat', 'aliases' => array('ringkasan')),
                array('key' => 'catatan',   'label' => 'Catatan Dampak / Peserta', 'aliases' => array('peserta', 'keterangan')),
                array('key' => 'dok_url',   'label' => 'URL Dokumentasi / SK', 'aliases' => array('url', 'link')),
            ),
            'sample_rows' => array(
                array('tahun' => 'Tahun 2025', 'lokasi' => 'Gedung Phinisi UNM', 'judul' => 'Showcase Media Bimbingan Karier MBKM', 'deskripsi' => 'Pameran media bimbingan hasil PPL MBKM di sekolah mitra.', 'catatan' => '👥 Diikuti 110 mahasiswa praktikan BK.', 'dok_url' => ''),
            ),
        ),
        'jadwal' => array(
            'key'           => 'jadwal',
            'label'         => 'Jadwal Seminar & Ujian (S1/S2/S3)',
            'icon'          => '📅',
            'group'         => 'Akademik & Kurikulum',
            'storage_type'  => 'cpt',
            'post_type'     => 'ppb_jadwal',
            'capability'    => 'edit_posts',
            'allow_replace' => true,
            'admin_url'     => 'edit.php?post_type=ppb_jadwal',
            'description'   => 'Data mahasiswa peserta Sempro, Semhas, Ujian Skripsi (S1), Tesis (S2), dan Disertasi (S3) beserta dosen pembimbing & penguji.',
            'columns' => array(
                array('key' => 'jenjang',          'label' => 'Jenjang (s1/s2/s3)', 'aliases' => array('unit', 'prodi', 'program_studi')),
                array('key' => 'nama_mahasiswa',   'label' => 'Nama Mahasiswa*',  'aliases' => array('nama', 'mahasiswa')),
                array('key' => 'nim',              'label' => 'NIM*',             'aliases' => array('no_induk')),
                array('key' => 'angkatan',         'label' => 'Angkatan',         'aliases' => array('tahun_masuk')),
                array('key' => 'jenis_ujian',      'label' => 'Jenis Ujian (seminar_proposal/seminar_hasil/ujian_skripsi/seminar_tesis/ujian_tesis/ujian_kualifikasi/seminar_proposal_s3/ujian_tertutup/ujian_terbuka)', 'aliases' => array('jenis', 'tahapan')),
                array('key' => 'status',           'label' => 'Status (terjadwal/sedang_berlangsung/selesai)', 'aliases' => array('status_ujian')),
                array('key' => 'tanggal',          'label' => 'Tanggal (YYYY-MM-DD)*', 'aliases' => array('tgl', 'date')),
                array('key' => 'jam_mulai',        'label' => 'Jam Mulai',        'aliases' => array('mulai')),
                array('key' => 'jam_selesai',      'label' => 'Jam Selesai',      'aliases' => array('selesai')),
                array('key' => 'ruangan',          'label' => 'Ruangan / Lokasi', 'aliases' => array('tempat', 'lokasi')),
                array('key' => 'judul_penelitian', 'label' => 'Judul Penelitian', 'aliases' => array('judul', 'title')),
                array('key' => 'pembimbing_1',     'label' => 'Pembimbing 1',     'aliases' => array('p1', 'promotor')),
                array('key' => 'pembimbing_2',     'label' => 'Pembimbing 2',     'aliases' => array('p2', 'kopromotor')),
                array('key' => 'penguji_1',        'label' => 'Penguji 1',        'aliases' => array('u1')),
                array('key' => 'penguji_2',        'label' => 'Penguji 2',        'aliases' => array('u2')),
                array('key' => 'link_daring',      'label' => 'Link Daring (Opsional)', 'aliases' => array('zoom', 'link_zoom', 'url')),
            ),
            'sample_rows' => array(
                array('jenjang' => 's1', 'nama_mahasiswa' => 'Nurul Aulia Rahmah', 'nim' => '220404501012', 'angkatan' => '2022', 'jenis_ujian' => 'seminar_proposal', 'status' => 'terjadwal', 'tanggal' => '2026-05-20', 'jam_mulai' => '09:00', 'jam_selesai' => '10:30', 'ruangan' => 'Ruang Sidang FIP UNM Lt. 2', 'judul_penelitian' => 'Efektivitas Konseling Kelompok Solution-Focused untuk Resiliensi Akademik Mahasiswa', 'pembimbing_1' => 'Prof. Dr. Farida Aryani, M.Pd.', 'pembimbing_2' => 'Dr. Suciani Latif, S.Pd., M.Pd.', 'penguji_1' => 'Prof. Dr. Abdullah Pandang, M.Pd.', 'penguji_2' => 'Dr. Nur Fadhilah Umar, M.Pd.', 'link_daring' => ''),
            ),
        ),
        'prestasi' => array(
            'key'           => 'prestasi',
            'label'         => 'Prestasi Mahasiswa',
            'icon'          => '🏆',
            'group'         => 'Kemahasiswaan & Alumni',
            'storage_type'  => 'cpt',
            'post_type'     => 'ppb_prestasi',
            'capability'    => 'edit_posts',
            'allow_replace' => true,
            'admin_url'     => 'edit.php?post_type=ppb_prestasi',
            'description'   => 'Rekapitulasi juara lomba, penghargaan, sertifikat Drive, dan status unggulan mahasiswa S1/S2/S3.',
            'columns' => array(
                array('key' => 'jenjang',               'label' => 'Jenjang (s1/s2/s3)',    'aliases' => array('unit', 'prodi')),
                array('key' => 'nama_mahasiswa',        'label' => 'Nama Mahasiswa / Tim*', 'aliases' => array('nama', 'mahasiswa')),
                array('key' => 'nim',                   'label' => 'NIM',                  'aliases' => array('no_induk')),
                array('key' => 'angkatan',              'label' => 'Angkatan',             'aliases' => array('tahun_masuk')),
                array('key' => 'capaian',               'label' => 'Capaian / Juara*',     'aliases' => array('juara', 'prestasi')),
                array('key' => 'tingkat',               'label' => 'Tingkat (internasional/nasional/wilayah)', 'aliases' => array('level')),
                array('key' => 'tahun',                 'label' => 'Tahun*',               'aliases' => array('year')),
                array('key' => 'nama_kegiatan',         'label' => 'Nama Kegiatan / Lomba*', 'aliases' => array('kegiatan', 'lomba', 'event')),
                array('key' => 'penyelenggara',         'label' => 'Penyelenggara',        'aliases' => array('lembaga')),
                array('key' => 'dosen_pembimbing',      'label' => 'Dosen Pembimbing',     'aliases' => array('pembimbing')),
                array('key' => 'deskripsi',             'label' => 'Deskripsi Karya',      'aliases' => array('ringkasan')),
                array('key' => 'drive_sertifikat_link', 'label' => 'URL Sertifikat',       'aliases' => array('sertifikat_url', 'sertifikat')),
                array('key' => 'drive_foto_link',       'label' => 'URL Foto Dokumentasi', 'aliases' => array('foto_url', 'foto')),
                array('key' => 'is_featured',           'label' => 'Unggulan (1/0)',       'aliases' => array('featured')),
            ),
            'sample_rows' => array(
                array('jenjang' => 's1', 'nama_mahasiswa' => 'Fathurrahman Alim', 'nim' => '220404501033', 'angkatan' => '2022', 'capaian' => 'Juara 1 Poster Edukasi Mental Health', 'tingkat' => 'nasional', 'tahun' => '2025', 'nama_kegiatan' => 'Festival Konseling Nasional', 'penyelenggara' => 'ABKIN & Universitas Negeri Malang', 'dosen_pembimbing' => 'Dr. Suciani Latif, M.Pd.', 'deskripsi' => 'Desain kampanye kesehatan mental berbasis budaya Bugis-Makassar.', 'drive_sertifikat_link' => '', 'drive_foto_link' => '', 'is_featured' => 1),
            ),
        ),
        'karya' => array(
            'key'           => 'karya',
            'label'         => 'Karya & Inovasi Mahasiswa',
            'icon'          => '🎨',
            'group'         => 'Kemahasiswaan & Alumni',
            'storage_type'  => 'cpt',
            'post_type'     => 'ppb_karya',
            'capability'    => 'edit_posts',
            'allow_replace' => true,
            'admin_url'     => 'edit.php?post_type=ppb_karya',
            'description'   => 'Produk media bimbingan, modul psikoedukasi, instrumen, aplikasi digital, dan video karya mahasiswa.',
            'columns' => array(
                array('key' => 'jenjang',          'label' => 'Jenjang (s1/s2/s3)',  'aliases' => array('unit', 'prodi')),
                array('key' => 'judul',            'label' => 'Judul Produk Karya*', 'aliases' => array('title', 'nama', 'karya_judul')),
                array('key' => 'kategori',         'label' => 'Kategori (media_bimbingan/modul_psikoedukasi/instrumen_asesmen/aplikasi_digital/video_podcast)', 'aliases' => array('kat')),
                array('key' => 'badge_unggulan',   'label' => 'Badge Unggulan (HKI/dll)', 'aliases' => array('badge')),
                array('key' => 'tahun',            'label' => 'Tahun*',              'aliases' => array('year')),
                array('key' => 'pembuat',          'label' => 'Nama Pembuat / Tim*', 'aliases' => array('tim', 'penulis')),
                array('key' => 'nim_ketua',        'label' => 'NIM Ketua',           'aliases' => array('nim')),
                array('key' => 'angkatan',         'label' => 'Angkatan',            'aliases' => array('tahun_masuk')),
                array('key' => 'dosen_pembimbing', 'label' => 'Dosen Pembimbing',    'aliases' => array('pembimbing')),
                array('key' => 'mata_kuliah',      'label' => 'Mata Kuliah Terkait', 'aliases' => array('matkul')),
                array('key' => 'deskripsi',        'label' => 'Deskripsi Produk',    'aliases' => array('ringkasan')),
                array('key' => 'manfaat',          'label' => 'Manfaat & Dampak',    'aliases' => array('dampak')),
                array('key' => 'foto_karya_link',  'label' => 'URL Foto / Cover',    'aliases' => array('foto_url', 'cover')),
                array('key' => 'drive_file_link',  'label' => 'URL Berkas / Produk', 'aliases' => array('file_url', 'url', 'link')),
            ),
            'sample_rows' => array(
                array('jenjang' => 's1', 'judul' => 'Kartu Eksplorasi Karier Sipakatau', 'kategori' => 'media_bimbingan', 'badge_unggulan' => 'Terdaftar Hak Cipta HKI', 'tahun' => '2025', 'pembuat' => 'Andi Nurfaizah, Muh. Ilham', 'nim_ketua' => '220404501045', 'angkatan' => '2022', 'dosen_pembimbing' => 'Dr. Suciani Latif, M.Pd.', 'mata_kuliah' => 'Pengembangan Media BK', 'deskripsi' => 'Set 48 kartu refleksi minat karier berbasis nilai lokal Sulawesi Selatan.', 'manfaat' => 'Diuji pada 3 SMA mitra di Kota Makassar.', 'foto_karya_link' => '', 'drive_file_link' => ''),
            ),
        ),
        'dokumen' => array(
            'key'           => 'dokumen',
            'label'         => 'Dokumen Mutu (Google Drive / PDF)',
            'icon'          => '📂',
            'group'         => 'Mutu & Persuratan',
            'storage_type'  => 'cpt',
            'post_type'     => 'ppb_dokumen',
            'capability'    => 'edit_posts',
            'allow_replace' => true,
            'admin_url'     => 'edit.php?post_type=ppb_dokumen',
            'description'   => 'Dokumen Renstra, Renop, SK Senat, Pedoman Penilaian, Akreditasi, dan link Drive resmi per unit/jenjang.',
            'columns' => array(
                array('key' => 'unit',      'label' => 'Unit / Jenjang (jurusan/s1/s2/s3/semua)', 'aliases' => array('jenjang', 'prodi')),
                array('key' => 'judul',     'label' => 'Judul Dokumen*',  'aliases' => array('title', 'nama', 'nama_dokumen')),
                array('key' => 'kategori',  'label' => 'Kategori (renstra/renop/sk_senat/pedoman_penilaian/sk_mahasiswa_aktif/perlindungan_mahasiswa/akreditasi/lainnya)', 'aliases' => array('kat')),
                array('key' => 'nomor',     'label' => 'Nomor SK / Dokumen', 'aliases' => array('nomor_sk', 'no_dokumen')),
                array('key' => 'tahun',     'label' => 'Tahun',           'aliases' => array('year')),
                array('key' => 'pengesah',  'label' => 'Pejabat Pengesah','aliases' => array('instansi')),
                array('key' => 'size',      'label' => 'Ukuran / Format', 'aliases' => array('ukuran')),
                array('key' => 'deskripsi', 'label' => 'Deskripsi',       'aliases' => array('ringkasan')),
                array('key' => 'link',      'label' => 'URL Drive / PDF*', 'aliases' => array('url', 'drive_link', 'file_url')),
            ),
            'sample_rows' => array(
                array('unit' => 'jurusan', 'judul' => 'Rencana Strategis (Renstra) Jurusan PPB 2025-2029', 'kategori' => 'renstra', 'nomor' => 'RENSTRA-PPB/UN36.2/2025', 'tahun' => '2025', 'pengesah' => 'Dekan FIP UNM', 'size' => '3.4 MB', 'deskripsi' => 'Arah kebijakan mutu dan pengembangan Jurusan PPB serta Prodi S1/S2/S3.', 'link' => 'https://drive.google.com/file/d/CONTOH_RENSTRA/view'),
            ),
        ),
        'template_dok' => array(
            'key'           => 'template_dok',
            'label'         => 'Template Surat & Formulir',
            'icon'          => '📄',
            'group'         => 'Mutu & Persuratan',
            'storage_type'  => 'cpt',
            'post_type'     => 'ppb_template_dok',
            'capability'    => 'edit_posts',
            'allow_replace' => true,
            'admin_url'     => 'edit.php?post_type=ppb_template_dok',
            'description'   => 'Katalog template surat skripsi, tesis, disertasi, izin penelitian, bebas lab, peninjauan UKT, dan PPL.',
            'columns' => array(
                array('key' => 'unit',         'label' => 'Unit / Jenjang (semua/jurusan/s1/s2/s3)', 'aliases' => array('jenjang', 'prodi')),
                array('key' => 'judul',        'label' => 'Nama Template*',    'aliases' => array('title', 'nama')),
                array('key' => 'kategori',     'label' => 'Kategori (skripsi/tesis/disertasi/izin_penelitian/bebas_lab/peninjauan_ukt/akademik_umum)', 'aliases' => array('kat')),
                array('key' => 'format_file',  'label' => 'Format (DOCX/PDF/XLSX)', 'aliases' => array('format')),
                array('key' => 'ukuran_kb',    'label' => 'Ukuran (KB)',       'aliases' => array('ukuran')),
                array('key' => 'nomor_revisi', 'label' => 'Kode / Revisi',     'aliases' => array('kode_form', 'revisi')),
                array('key' => 'deskripsi',    'label' => 'Petunjuk Penggunaan', 'aliases' => array('keterangan')),
                array('key' => 'file_link',    'label' => 'URL Berkas Template*', 'aliases' => array('url', 'link', 'file_url')),
            ),
            'sample_rows' => array(
                array('unit' => 's1', 'judul' => 'Formulir Pendaftaran Seminar Proposal Skripsi', 'kategori' => 'skripsi', 'format_file' => 'DOCX', 'ukuran_kb' => 180, 'nomor_revisi' => 'REV-2026/A', 'deskripsi' => 'Diisi dan ditandatangani Pembimbing I & II sebelum diunggah di SIPADU.', 'file_link' => 'https://drive.google.com/file/d/CONTOH_FORM_SEMPRO/view'),
            ),
        ),
        'dosen' => array(
            'key'           => 'dosen',
            'label'         => 'Profil Dasar Dosen (Mode Upsert Aman)',
            'icon'          => '👨‍🏫',
            'group'         => 'Direktori SDM',
            'storage_type'  => 'dosen_json',
            'capability'    => 'manage_options',
            'allow_replace' => false, // STRICT: Mode replace dikunci agar riwayat publikasi/SINTA/MK tidak pernah hilang
            'admin_url'     => 'admin.php?page=bk-direktori-dosen',
            'description'   => 'Perbarui massal biodata, NIP, NIDN, kontak, kepakaran, ID SINTA, dan foto dosen. Publikasi, Penelitian, PkM & Mata Kuliah existing dipertahankan 100%.',
            'columns' => array(
                array('key' => 'slug',               'label' => 'Slug Dosen (opsional)', 'aliases' => array('id_slug')),
                array('key' => 'nama_lengkap',       'label' => 'Nama Lengkap & Gelar*', 'aliases' => array('nama', 'nama_dosen')),
                array('key' => 'nama_panggilan',     'label' => 'Nama Panggilan',        'aliases' => array('panggilan')),
                array('key' => 'nip',                'label' => 'NIP',                   'aliases' => array('no_nip')),
                array('key' => 'nidn',               'label' => 'NIDN',                  'aliases' => array('no_nidn')),
                array('key' => 'jabatan_fungsional', 'label' => 'Jabatan Fungsional',    'aliases' => array('pangkat', 'jabfung')),
                array('key' => 'jabatan_struktural', 'label' => 'Jabatan Struktural',    'aliases' => array('jabatan')),
                array('key' => 'unit',               'label' => 'Homebase / Unit',       'aliases' => array('prodi', 'homebase')),
                array('key' => 'email',              'label' => 'Email',                 'aliases' => array('surel')),
                array('key' => 'no_hp',              'label' => 'No. HP / WhatsApp',     'aliases' => array('hp', 'telepon', 'wa')),
                array('key' => 'ruang_kerja',        'label' => 'Ruang Kerja',           'aliases' => array('ruangan')),
                array('key' => 'kepakaran',          'label' => 'Kepakaran (pisah ;)',   'aliases' => array('bidang_keahlian', 'keahlian')),
                array('key' => 'sinta_id',           'label' => 'SINTA ID',              'aliases' => array('id_sinta')),
                array('key' => 'sinta_url',          'label' => 'URL Profil SINTA',      'aliases' => array('link_sinta')),
                array('key' => 'scholar_url',        'label' => 'URL Google Scholar',    'aliases' => array('scholar')),
                array('key' => 'scopus_url',         'label' => 'URL Scopus',            'aliases' => array('scopus')),
                array('key' => 'orcid',              'label' => 'ORCID',                 'aliases' => array('orcid_id')),
                array('key' => 'foto_link',          'label' => 'URL Foto Profil',       'aliases' => array('foto_url', 'foto')),
                array('key' => 'bio',                'label' => 'Biografi Singkat',      'aliases' => array('biografi')),
            ),
            'sample_rows' => array(
                array('slug' => 'suciani-latif', 'nama_lengkap' => 'Dr. Suciani Latif, S.Pd., M.Pd.', 'nama_panggilan' => 'Ibu Uci', 'nip' => '198605122014042001', 'nidn' => '0012058604', 'jabatan_fungsional' => 'Lektor Kepala', 'jabatan_struktural' => 'Kepala Laboratorium BK', 'unit' => 'S1 - Bimbingan dan Konseling', 'email' => 'suciani.latif@unm.ac.id', 'no_hp' => '0812xxxxxxxx', 'ruang_kerja' => 'Gedung Lab BK Lt. 1', 'kepakaran' => 'Asesmen Konseling; Kesehatan Mental Remaja', 'sinta_id' => '6000001', 'sinta_url' => '', 'scholar_url' => '', 'scopus_url' => '', 'orcid' => '', 'foto_link' => '', 'bio' => 'Dosen Program Studi Bimbingan dan Konseling FIP UNM.'),
            ),
        ),
    );
}
}

if (!function_exists('ppb_excel_get_dataset')) {
function ppb_excel_get_dataset($key) {
    $all = ppb_excel_get_datasets();
    return $all[sanitize_key($key)] ?? null;
}
}

if (!function_exists('ppb_excel_build_panduan_sheet')) {
function ppb_excel_build_panduan_sheet($schema) {
    $rows = array(
        array('Dataset', (string) ($schema['label'] ?? $schema['key'])),
        array('Kode Dataset', (string) ($schema['key'] ?? '')),
        array('Keterangan', (string) ($schema['description'] ?? '')),
        array('Format Didukung', 'Excel (.xlsx), Excel XML 2003 (.xls), CSV/TSV (.csv), dan JSON (.json)'),
        array('Mode Upsert (Tambah / Perbarui)', 'Mencocokkan baris dengan data lama berdasarkan kode/nama/slug. Sel kosong pada baris yang sudah ada TIDAK menimpa nilai lama; baris baru otomatis ditambahkan.'),
        array('Mode Replace (Ganti Semua)', !empty($schema['allow_replace']) ? 'Mengganti seluruh daftar data dengan isi berkas yang diunggah. Tidak berlaku jika berkas kosong (0 baris valid).' : 'DINONAKTIFKAN untuk dataset ini demi melindungi riwayat publikasi, SINTA, dan relasi mata kuliah dosen.'),
        array('', ''),
        array('Daftar Kolom & Petunjuk', 'Nama Key JSON / Header Alternatif'),
    );
    $cols = $schema['columns'] ?? array();
    if (!empty($schema['columns_riset'])) {
        $rows[] = array('[Sheet Hibah Penelitian]', 'Kolom Sheet 1:');
        foreach ($schema['columns_riset'] as $c) {
            $rows[] = array($c['label'], 'Key: ' . $c['key']);
        }
        $rows[] = array('[Sheet Hibah PkM]', 'Kolom Sheet 2:');
        foreach ($schema['columns_pkm'] as $c) {
            $rows[] = array($c['label'], 'Key: ' . $c['key']);
        }
    } else {
        foreach ($cols as $c) {
            $alias_txt = !empty($c['aliases']) ? (' · Alias: ' . implode(', ', $c['aliases'])) : '';
            $rows[] = array($c['label'], 'Key: ' . $c['key'] . $alias_txt);
        }
    }

    if (($schema['key'] ?? '') === 'matkul') {
        $rows[] = array('', '');
        $rows[] = array('Tips Kolom Dosen Pengampu', 'Isi dengan slug (contoh: suciani-latif), NIP, atau nama lengkap. Pisahkan beberapa dosen menggunakan titik koma (;). Lihat sheet Referensi Dosen.');
    }

    return array(
        'name'          => 'Panduan Pengisian',
        'headers'       => array('Topik / Kolom', 'Penjelasan & Referensi Key'),
        'rows'          => $rows,
        'widths'        => array(38, 82),
        'freeze_header' => true,
        'autofilter'    => false,
        'is_guide'      => true,
    );
}
}
