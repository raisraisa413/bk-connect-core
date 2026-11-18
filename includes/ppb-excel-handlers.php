<?php
/**
 * Handler Ekspor & Impor Data Massal untuk ke-16 Dataset Portal PPB FIP UNM.
 * Menjamin validasi ketat, proteksi anti-kosong, upsert non-destruktif untuk dosen,
 * dan pembersihan cache Elementor otomatis setelah impor berhasil.
 */

if (!defined('ABSPATH')) exit;

/* ========================================================================== */
/* 1. UTILITY CACHE, JUMLAH RECORD & ENUM NORMALIZER                          */
/* ========================================================================== */

if (!function_exists('ppb_excel_purge_caches')) {
function ppb_excel_purge_caches($post_ids = array()) {
    foreach ((array) $post_ids as $pid) {
        $pid = (int) $pid;
        if ($pid > 0) {
            delete_post_meta($pid, '_elementor_element_cache');
            delete_post_meta($pid, '_elementor_css');
            clean_post_cache($pid);
        }
    }
    if (class_exists('\\Elementor\\Plugin') && isset(\Elementor\Plugin::$instance->files_manager)) {
        \Elementor\Plugin::$instance->files_manager->clear_cache();
    }
}
}

if (!function_exists('ppb_excel_get_cpt_posts')) {
function ppb_excel_get_cpt_posts($post_type) {
    $posts = get_posts(array(
        'post_type'      => $post_type,
        'posts_per_page' => -1,
        'post_status'    => array('publish', 'draft', 'pending'),
        'orderby'        => 'ID',
        'order'          => 'ASC',
    ));
    return is_array($posts) ? $posts : array();
}
}

if (!function_exists('ppb_excel_get_dataset_count')) {
function ppb_excel_get_dataset_count($key) {
    switch ($key) {
        case 'matkul':
            return function_exists('ppb_get_mata_kuliah_list') ? count(ppb_get_mata_kuliah_list(true)) : 0;
        case 'pnbp':
            $d = function_exists('ppb_get_mis_pnbp_data') ? ppb_get_mis_pnbp_data() : array('riset' => array(), 'pkm' => array());
            return count($d['riset'] ?? array()) + count($d['pkm'] ?? array());
        case 'jurnal':
            return function_exists('ppb_get_jurnal_bk_data') ? count(ppb_get_jurnal_bk_data()) : 0;
        case 'elibrary':
            return function_exists('ppb_get_elibrary_items') ? count(ppb_get_elibrary_items()) : 0;
        case 'lab_kegiatan':
            return function_exists('ppb_get_lab_kegiatan_list') ? count(ppb_get_lab_kegiatan_list()) : 0;
        case 'lab_lomba':
            return function_exists('ppb_get_lab_lomba_list') ? count(ppb_get_lab_lomba_list()) : 0;
        case 'sarpras':
            return function_exists('ppb_get_sarpras_list') ? count(ppb_get_sarpras_list()) : 0;
        case 'maba_tren':
            $m = function_exists('ppb_get_maba_statistik_data') ? ppb_get_maba_statistik_data() : array('tren' => array());
            return count($m['tren'] ?? array());
        case 'mbkm':
            $k = function_exists('ppb_get_mbkm_kemanusiaan_data') ? ppb_get_mbkm_kemanusiaan_data() : array('mbkm_items' => array());
            return count($k['mbkm_items'] ?? array());
        case 'dosen':
            return function_exists('ppb_get_dosen_directory_cached') ? count(ppb_get_dosen_directory_cached()) : 0;
        case 'inventaris':
            return (int) (wp_count_posts('ppb_inventaris_lab')->publish ?? 0);
        case 'jadwal':
            return (int) (wp_count_posts('ppb_jadwal')->publish ?? 0);
        case 'prestasi':
            return (int) (wp_count_posts('ppb_prestasi')->publish ?? 0);
        case 'karya':
            return (int) (wp_count_posts('ppb_karya')->publish ?? 0);
        case 'dokumen':
            return (int) (wp_count_posts('ppb_dokumen')->publish ?? 0);
        case 'template_dok':
            return (int) (wp_count_posts('ppb_template_dok')->publish ?? 0);
    }
    return 0;
}
}

if (!function_exists('ppb_excel_normalize_enum')) {
function ppb_excel_normalize_enum($dataset_key, $field, $raw) {
    $v = strtolower(trim((string) $raw));
    if ($dataset_key === 'jadwal' && $field === 'jenis_ujian') {
        if ($v === 'seminar_hasil' || strpos($v, 'semhas') !== false || strpos($v, 'hasil') !== false) return 'seminar_hasil';
        if ($v === 'ujian_skripsi' || strpos($v, 'skripsi') !== false || strpos($v, 'tutup') !== false) return 'ujian_skripsi';
        return 'seminar_proposal';
    }
    if ($dataset_key === 'jadwal' && $field === 'status') {
        if ($v === 'sedang_berlangsung' || strpos($v, 'berlangsung') !== false || strpos($v, 'live') !== false) return 'sedang_berlangsung';
        if ($v === 'selesai' || strpos($v, 'arsip') !== false) return 'selesai';
        return 'terjadwal';
    }
    if ($dataset_key === 'prestasi' && $field === 'tingkat') {
        if ($v === 'internasional' || strpos($v, 'inter') !== false) return 'internasional';
        if ($v === 'wilayah' || strpos($v, 'reg') !== false || strpos($v, 'lokal') !== false || strpos($v, 'prov') !== false) return 'wilayah';
        return 'nasional';
    }
    if ($dataset_key === 'karya' && $field === 'kategori') {
        if ($v === 'modul_psikoedukasi' || strpos($v, 'modul') !== false) return 'modul_psikoedukasi';
        if ($v === 'instrumen_asesmen' || strpos($v, 'instrumen') !== false || strpos($v, 'asesmen') !== false) return 'instrumen_asesmen';
        if ($v === 'aplikasi_digital' || strpos($v, 'aplikasi') !== false || strpos($v, 'digital') !== false) return 'aplikasi_digital';
        if ($v === 'video_podcast' || strpos($v, 'video') !== false || strpos($v, 'podcast') !== false) return 'video_podcast';
        return 'media_bimbingan';
    }
    if ($dataset_key === 'dokumen' && $field === 'kategori') {
        $allowed = array('renstra', 'renop', 'sk_senat', 'pedoman_penilaian', 'sk_mahasiswa_aktif', 'perlindungan_mahasiswa', 'akreditasi', 'lainnya');
        if (in_array($v, $allowed, true)) return $v;
        if (strpos($v, 'renop') !== false) return 'renop';
        if (strpos($v, 'senat') !== false) return 'sk_senat';
        if (strpos($v, 'pedoman') !== false || strpos($v, 'nilai') !== false || strpos($v, 'obe') !== false) return 'pedoman_penilaian';
        if (strpos($v, 'aktif') !== false || strpos($v, 'rektor') !== false) return 'sk_mahasiswa_aktif';
        if (strpos($v, 'lindung') !== false || strpos($v, 'ppks') !== false) return 'perlindungan_mahasiswa';
        if (strpos($v, 'akred') !== false) return 'akreditasi';
        if (strpos($v, 'renstra') !== false) return 'renstra';
        return $v !== '' ? sanitize_text_field($raw) : 'renstra';
    }
    if ($dataset_key === 'template_dok' && $field === 'kategori') {
        if ($v === 'izin_penelitian' || strpos($v, 'izin') !== false || strpos($v, 'penelitian') !== false) return 'izin_penelitian';
        if ($v === 'bebas_lab' || strpos($v, 'lab') !== false) return 'bebas_lab';
        if ($v === 'peninjauan_ukt' || strpos($v, 'ukt') !== false) return 'peninjauan_ukt';
        if ($v === 'akademik_umum' || strpos($v, 'ppl') !== false || strpos($v, 'magang') !== false || strpos($v, 'umum') !== false) return 'akademik_umum';
        return 'skripsi';
    }
    if ($dataset_key === 'template_dok' && $field === 'format_file') {
        $u = strtoupper($v);
        if (in_array($u, array('DOCX', 'PDF', 'XLSX'), true)) return $u;
        if (strpos($u, 'PDF') !== false) return 'PDF';
        if (strpos($u, 'XLS') !== false) return 'XLSX';
        return 'DOCX';
    }
    if ($dataset_key === 'elibrary' && $field === 'category') {
        if (in_array($v, array('modul', 'buku', 'asesmen', 'artikel'), true)) return $v;
        if (strpos($v, 'pedoman') !== false || strpos($v, 'buku') !== false) return 'buku';
        if (strpos($v, 'asesmen') !== false || strpos($v, 'tes') !== false) return 'asesmen';
        if (strpos($v, 'artikel') !== false || strpos($v, 'jurnal') !== false) return 'artikel';
        return 'modul';
    }
    return sanitize_text_field($raw);
}
}

/* ========================================================================== */
/* 2. PEMBANGUN DATA EKSPOR & TEMPLATE                                        */
/* ========================================================================== */

if (!function_exists('ppb_excel_assoc_list_to_indexed_rows')) {
function ppb_excel_assoc_list_to_indexed_rows($columns, $assoc_rows, $int_keys = array()) {
    $headers = array();
    foreach ($columns as $col) {
        $headers[] = $col['label'];
    }
    $indexed = array();
    foreach ($assoc_rows as $ar) {
        $r = array();
        foreach ($columns as $col) {
            $k = $col['key'];
            $v = $ar[$k] ?? '';
            if (in_array($k, $int_keys, true) && $v !== '' && is_numeric($v)) {
                $r[] = (int) $v;
            } else {
                $r[] = (string) $v;
            }
        }
        $indexed[] = $r;
    }
    return array('headers' => $headers, 'rows' => $indexed);
}
}

if (!function_exists('ppb_excel_fetch_assoc_rows')) {
function ppb_excel_fetch_assoc_rows($dataset_key) {
    switch ($dataset_key) {
        case 'matkul':
            $list = function_exists('ppb_get_mata_kuliah_list') ? ppb_get_mata_kuliah_list(true) : array();
            $out = array();
            foreach ($list as $c) {
                $out[] = array(
                    'kode'           => (string) ($c['kode'] ?? ''),
                    'nama'           => (string) ($c['nama'] ?? ''),
                    'jenjang'        => (string) ($c['jenjang'] ?? 'S1'),
                    'semester'       => (int) ($c['semester'] ?? 1),
                    'sks'            => (int) ($c['sks'] ?? 3),
                    'rumpun'         => (string) ($c['rumpun'] ?? 'Wajib Prodi'),
                    'sdg'            => (string) ($c['sdg'] ?? 'SDG 4'),
                    'sem_title'      => (string) ($c['sem_title'] ?? ''),
                    'dosen_pengampu' => !empty($c['dosen_slugs']) && is_array($c['dosen_slugs']) ? implode('; ', $c['dosen_slugs']) : '',
                    'cpmk'           => (string) ($c['cpmk'] ?? ''),
                    'rps_url'        => (string) ($c['rps_url'] ?? ''),
                    'sap_url'        => (string) ($c['sap_url'] ?? ''),
                    'in_kurikulum'   => !empty($c['in_kurikulum']) ? 1 : 0,
                    'id'             => (string) ($c['id'] ?? ''),
                );
            }
            return $out;

        case 'jurnal':
            return function_exists('ppb_get_jurnal_bk_data') ? ppb_get_jurnal_bk_data() : array();

        case 'elibrary':
            $items = function_exists('ppb_get_elibrary_items') ? ppb_get_elibrary_items() : array();
            foreach ($items as &$it) {
                if (($it['file_url'] ?? '') === '#') $it['file_url'] = '';
            }
            return $items;

        case 'lab_kegiatan':
            return function_exists('ppb_get_lab_kegiatan_list') ? ppb_get_lab_kegiatan_list() : array();

        case 'lab_lomba':
            return function_exists('ppb_get_lab_lomba_list') ? ppb_get_lab_lomba_list() : array();

        case 'sarpras':
            return function_exists('ppb_get_sarpras_list') ? ppb_get_sarpras_list() : array();

        case 'maba_tren':
            $m = function_exists('ppb_get_maba_statistik_data') ? ppb_get_maba_statistik_data() : array('tren' => array());
            return $m['tren'] ?? array();

        case 'mbkm':
            $k = function_exists('ppb_get_mbkm_kemanusiaan_data') ? ppb_get_mbkm_kemanusiaan_data() : array('mbkm_items' => array());
            return $k['mbkm_items'] ?? array();

        case 'inventaris':
            $posts = ppb_excel_get_cpt_posts('ppb_inventaris_lab');
            if (empty($posts) && function_exists('ppb_get_inventaris_default_items')) {
                $out = array();
                foreach (ppb_get_inventaris_default_items() as $it) {
                    $out[] = array(
                        'kode_alat'   => $it['kode'] ?? '',
                        'nama'        => $it['nama'] ?? '',
                        'kategori'    => $it['kategori'] ?? 'tes_psikologi',
                        'jumlah_unit' => $it['jumlah'] ?? '',
                        'kondisi'     => $it['kondisi'] ?? 'baik',
                        'lokasi'      => $it['lokasi'] ?? '',
                        'spesifikasi' => $it['spesifikasi'] ?? '',
                        'keterangan'  => $it['keterangan'] ?? '',
                        'manual_link' => $it['manual_link'] ?? '',
                        'foto_link'   => $it['foto_link'] ?? '',
                    );
                }
                return $out;
            }
            $out = array();
            foreach ($posts as $p) {
                $out[] = array(
                    'kode_alat'   => (string) get_post_meta($p->ID, '_ppb_inv_kode_alat', true),
                    'nama'        => (string) $p->post_title,
                    'kategori'    => (string) (get_post_meta($p->ID, '_ppb_inv_kategori', true) ?: 'tes_psikologi'),
                    'jumlah_unit' => (string) get_post_meta($p->ID, '_ppb_inv_jumlah_unit', true),
                    'kondisi'     => (string) (get_post_meta($p->ID, '_ppb_inv_kondisi', true) ?: 'baik'),
                    'lokasi'      => (string) get_post_meta($p->ID, '_ppb_inv_lokasi', true),
                    'spesifikasi' => (string) get_post_meta($p->ID, '_ppb_inv_spesifikasi', true),
                    'keterangan'  => (string) get_post_meta($p->ID, '_ppb_inv_keterangan', true),
                    'manual_link' => (string) get_post_meta($p->ID, '_ppb_inv_manual_link', true),
                    'foto_link'   => (string) get_post_meta($p->ID, '_ppb_inv_foto_link', true),
                );
            }
            return $out;

        case 'jadwal':
            $out = array();
            foreach (ppb_excel_get_cpt_posts('ppb_jadwal') as $p) {
                $out[] = array(
                    'nama_mahasiswa'   => (string) (get_post_meta($p->ID, '_ppb_nama_mahasiswa', true) ?: $p->post_title),
                    'nim'              => (string) get_post_meta($p->ID, '_ppb_nim', true),
                    'angkatan'         => (string) get_post_meta($p->ID, '_ppb_angkatan', true),
                    'jenis_ujian'      => (string) (get_post_meta($p->ID, '_ppb_jenis_ujian', true) ?: 'seminar_proposal'),
                    'status'           => (string) (get_post_meta($p->ID, '_ppb_status', true) ?: 'terjadwal'),
                    'tanggal'          => (string) get_post_meta($p->ID, '_ppb_tanggal', true),
                    'jam_mulai'        => (string) get_post_meta($p->ID, '_ppb_jam_mulai', true),
                    'jam_selesai'      => (string) get_post_meta($p->ID, '_ppb_jam_selesai', true),
                    'ruangan'          => (string) get_post_meta($p->ID, '_ppb_ruangan', true),
                    'judul_penelitian' => (string) get_post_meta($p->ID, '_ppb_judul_penelitian', true),
                    'pembimbing_1'     => (string) get_post_meta($p->ID, '_ppb_pembimbing_1', true),
                    'pembimbing_2'     => (string) get_post_meta($p->ID, '_ppb_pembimbing_2', true),
                    'penguji_1'        => (string) get_post_meta($p->ID, '_ppb_penguji_1', true),
                    'penguji_2'        => (string) get_post_meta($p->ID, '_ppb_penguji_2', true),
                    'link_daring'      => (string) get_post_meta($p->ID, '_ppb_link_daring', true),
                );
            }
            return $out;

        case 'prestasi':
            $out = array();
            foreach (ppb_excel_get_cpt_posts('ppb_prestasi') as $p) {
                $out[] = array(
                    'nama_mahasiswa'        => (string) get_post_meta($p->ID, '_ppb_nama_mahasiswa', true),
                    'nim'                   => (string) get_post_meta($p->ID, '_ppb_nim', true),
                    'angkatan'              => (string) get_post_meta($p->ID, '_ppb_angkatan', true),
                    'capaian'               => (string) get_post_meta($p->ID, '_ppb_capaian', true),
                    'tingkat'               => (string) (get_post_meta($p->ID, '_ppb_tingkat', true) ?: 'nasional'),
                    'tahun'                 => (string) get_post_meta($p->ID, '_ppb_tahun', true),
                    'nama_kegiatan'         => (string) get_post_meta($p->ID, '_ppb_nama_kegiatan', true),
                    'penyelenggara'         => (string) get_post_meta($p->ID, '_ppb_penyelenggara', true),
                    'dosen_pembimbing'      => (string) get_post_meta($p->ID, '_ppb_dosen_pembimbing', true),
                    'deskripsi'             => (string) get_post_meta($p->ID, '_ppb_deskripsi', true),
                    'drive_sertifikat_link' => (string) get_post_meta($p->ID, '_ppb_drive_sertifikat_link', true),
                    'drive_foto_link'       => (string) get_post_meta($p->ID, '_ppb_drive_foto_link', true),
                    'is_featured'           => get_post_meta($p->ID, '_ppb_is_featured', true) === '1' ? 1 : 0,
                );
            }
            return $out;

        case 'karya':
            $out = array();
            foreach (ppb_excel_get_cpt_posts('ppb_karya') as $p) {
                $out[] = array(
                    'judul'            => (string) (get_post_meta($p->ID, '_ppb_karya_judul', true) ?: $p->post_title),
                    'kategori'         => (string) (get_post_meta($p->ID, '_ppb_kategori', true) ?: 'media_bimbingan'),
                    'badge_unggulan'   => (string) get_post_meta($p->ID, '_ppb_badge_unggulan', true),
                    'tahun'            => (string) get_post_meta($p->ID, '_ppb_tahun', true),
                    'pembuat'          => (string) get_post_meta($p->ID, '_ppb_pembuat', true),
                    'nim_ketua'        => (string) get_post_meta($p->ID, '_ppb_nim_ketua', true),
                    'angkatan'         => (string) get_post_meta($p->ID, '_ppb_angkatan', true),
                    'dosen_pembimbing' => (string) get_post_meta($p->ID, '_ppb_dosen_pembimbing', true),
                    'mata_kuliah'      => (string) get_post_meta($p->ID, '_ppb_mata_kuliah', true),
                    'deskripsi'        => (string) get_post_meta($p->ID, '_ppb_deskripsi', true),
                    'manfaat'          => (string) get_post_meta($p->ID, '_ppb_manfaat', true),
                    'foto_karya_link'  => (string) get_post_meta($p->ID, '_ppb_foto_karya_link', true),
                    'drive_file_link'  => (string) get_post_meta($p->ID, '_ppb_drive_file_link', true),
                );
            }
            return $out;

        case 'dokumen':
            $out = array();
            foreach (ppb_excel_get_cpt_posts('ppb_dokumen') as $p) {
                $out[] = array(
                    'judul'     => (string) $p->post_title,
                    'kategori'  => (string) (get_post_meta($p->ID, '_ppb_dokumen_kategori', true) ?: 'renstra'),
                    'nomor'     => (string) get_post_meta($p->ID, '_ppb_dokumen_nomor', true),
                    'tahun'     => (string) get_post_meta($p->ID, '_ppb_dokumen_tahun', true),
                    'pengesah'  => (string) get_post_meta($p->ID, '_ppb_dokumen_pengesah', true),
                    'size'      => (string) get_post_meta($p->ID, '_ppb_dokumen_size', true),
                    'deskripsi' => (string) get_post_meta($p->ID, '_ppb_dokumen_deskripsi', true),
                    'link'      => (string) get_post_meta($p->ID, '_ppb_dokumen_link', true),
                );
            }
            return $out;

        case 'template_dok':
            $out = array();
            foreach (ppb_excel_get_cpt_posts('ppb_template_dok') as $p) {
                $out[] = array(
                    'judul'        => (string) $p->post_title,
                    'kategori'     => (string) (get_post_meta($p->ID, '_ppb_tpl_kategori', true) ?: 'skripsi'),
                    'format_file'  => (string) (get_post_meta($p->ID, '_ppb_tpl_format_file', true) ?: 'DOCX'),
                    'ukuran_kb'    => (int) get_post_meta($p->ID, '_ppb_tpl_ukuran_kb', true),
                    'nomor_revisi' => (string) get_post_meta($p->ID, '_ppb_tpl_nomor_revisi', true),
                    'deskripsi'    => (string) get_post_meta($p->ID, '_ppb_tpl_deskripsi', true),
                    'file_link'    => (string) get_post_meta($p->ID, '_ppb_tpl_file_link', true),
                );
            }
            return $out;

        case 'dosen':
            $list = function_exists('ppb_get_dosen_directory_cached') ? ppb_get_dosen_directory_cached() : array();
            $out = array();
            foreach ($list as $d) {
                $kep = '';
                if (!empty($d['kepakaran']) && is_array($d['kepakaran'])) {
                    $kep = implode('; ', $d['kepakaran']);
                } elseif (!empty($d['bidang_keahlian'])) {
                    $kep = (string) $d['bidang_keahlian'];
                }
                $out[] = array(
                    'slug'               => (string) ($d['slug'] ?? ''),
                    'nama_lengkap'       => (string) ($d['nama_lengkap'] ?? ($d['nama'] ?? '')),
                    'nama_panggilan'     => (string) ($d['nama_panggilan'] ?? ''),
                    'nip'                => (string) ($d['nip'] ?? ''),
                    'nidn'               => (string) ($d['nidn'] ?? ''),
                    'jabatan_fungsional' => (string) ($d['jabatan_fungsional'] ?? ''),
                    'jabatan_struktural' => (string) ($d['jabatan_struktural'] ?? ''),
                    'unit'               => (string) ($d['unit'] ?? ''),
                    'email'              => (string) ($d['email'] ?? ''),
                    'no_hp'              => (string) ($d['no_hp'] ?? ''),
                    'ruang_kerja'        => (string) ($d['ruang_kerja'] ?? ''),
                    'kepakaran'          => $kep,
                    'sinta_id'           => (string) ($d['sinta_id'] ?? ''),
                    'sinta_url'          => (string) ($d['sinta_url'] ?? ''),
                    'scholar_url'        => (string) ($d['scholar_url'] ?? ''),
                    'scopus_url'         => (string) ($d['scopus_url'] ?? ''),
                    'orcid'              => (string) ($d['orcid'] ?? ''),
                    'foto_link'          => (string) ($d['foto_link'] ?? ''),
                    'bio'                => (string) ($d['bio'] ?? ''),
                );
            }
            return $out;
    }
    return array();
}
}

if (!function_exists('ppb_excel_build_export_payload')) {
function ppb_excel_build_export_payload($dataset_key, $mode = 'export') {
    $schema = ppb_excel_get_dataset($dataset_key);
    if (!$schema) {
        return new WP_Error('invalid_dataset', 'Dataset tidak dikenal: ' . $dataset_key);
    }

    $int_keys = array('semester', 'sks', 'in_kurikulum', 'daya_tampung', 'peminat_snbp', 'peminat_snbt', 'peminat_mandiri', 'total_peminat', 'ukuran_kb', 'is_featured');
    $xlsx_sheets = array();
    $json_sheets = array();
    $csv_sheet   = array('headers' => array(), 'rows' => array());

    if ($dataset_key === 'pnbp') {
        $data  = ($mode === 'template')
            ? array('riset' => $schema['sample_riset'], 'pkm' => $schema['sample_pkm'])
            : (function_exists('ppb_get_mis_pnbp_data') ? ppb_get_mis_pnbp_data() : array('riset' => array(), 'pkm' => array()));

        $r_tbl = ppb_excel_assoc_list_to_indexed_rows($schema['columns_riset'], $data['riset'] ?? array(), $int_keys);
        $p_tbl = ppb_excel_assoc_list_to_indexed_rows($schema['columns_pkm'], $data['pkm'] ?? array(), $int_keys);

        $xlsx_sheets[] = array('name' => 'Hibah Penelitian', 'headers' => $r_tbl['headers'], 'rows' => $r_tbl['rows']);
        $xlsx_sheets[] = array('name' => 'Hibah PkM',        'headers' => $p_tbl['headers'], 'rows' => $p_tbl['rows']);
        $json_sheets[] = array('name' => 'Hibah Penelitian', 'key' => 'riset', 'assoc_rows' => array_values($data['riset'] ?? array()));
        $json_sheets[] = array('name' => 'Hibah PkM',        'key' => 'pkm',   'assoc_rows' => array_values($data['pkm'] ?? array()));

        // Untuk CSV tunggal, gabung dengan kolom jenis
        $flat = array();
        foreach ($data['riset'] ?? array() as $rr) {
            $flat[] = array(
                'jenis' => 'riset', 'tahun' => $rr['tahun'] ?? '', 'skema' => $rr['skema'] ?? '',
                'judul' => $rr['judul'] ?? '', 'ketua' => $rr['ketua'] ?? '', 'anggota' => $rr['anggota'] ?? '',
                'dana' => $rr['dana'] ?? '', 'luaran' => $rr['luaran'] ?? '', 'dokumen_url' => $rr['dokumen_url'] ?? '',
            );
        }
        foreach ($data['pkm'] ?? array() as $pr) {
            $flat[] = array(
                'jenis' => 'pkm', 'tahun' => $pr['tahun'] ?? '', 'skema' => $pr['skema'] ?? '',
                'judul' => $pr['judul'] ?? '', 'ketua' => $pr['ketua'] ?? '', 'anggota' => $pr['mitra'] ?? '',
                'dana' => $pr['dana'] ?? '', 'luaran' => $pr['luaran'] ?? '', 'dokumen_url' => $pr['dokumen_url'] ?? '',
            );
        }
        $csv_sheet = ppb_excel_assoc_list_to_indexed_rows($schema['columns'], $flat, $int_keys);
    } else {
        $assoc_rows = ($mode === 'template')
            ? ($schema['sample_rows'] ?? array())
            : ppb_excel_fetch_assoc_rows($dataset_key);

        $tbl = ppb_excel_assoc_list_to_indexed_rows($schema['columns'], $assoc_rows, $int_keys);
        $sheet_label = $schema['key'] === 'matkul' ? 'Mata Kuliah' : 'Data ' . ucwords(str_replace('_', ' ', $dataset_key));
        $xlsx_sheets[] = array('name' => $sheet_label, 'headers' => $tbl['headers'], 'rows' => $tbl['rows']);
        $json_sheets[] = array('name' => 'Data', 'key' => 'rows', 'assoc_rows' => $assoc_rows);
        $csv_sheet     = $tbl;

        // Tambahkan sheet Referensi Dosen otomatis pada template/ekspor Mata Kuliah
        if ($dataset_key === 'matkul' && function_exists('ppb_get_dosen_directory_cached')) {
            $d_rows = array();
            foreach (ppb_get_dosen_directory_cached() as $d) {
                $d_rows[] = array(
                    (string) ($d['slug'] ?? ''),
                    (string) ($d['nama_lengkap'] ?? ($d['nama'] ?? '')),
                    (string) ($d['nip'] ?? ''),
                    (string) ($d['nidn'] ?? ''),
                    (string) ($d['unit'] ?? ''),
                );
            }
            if (!empty($d_rows)) {
                $xlsx_sheets[] = array(
                    'name'    => 'Referensi Dosen',
                    'headers' => array('Slug Dosen (Copy ke Sheet MK)', 'Nama Lengkap', 'NIP', 'NIDN', 'Homebase / Unit'),
                    'rows'    => $d_rows,
                );
            }
        }
    }

    $xlsx_sheets[] = ppb_excel_build_panduan_sheet($schema);

    return array(
        'schema'      => $schema,
        'xlsx_sheets' => $xlsx_sheets,
        'json_sheets' => $json_sheets,
        'csv_sheet'   => $csv_sheet,
    );
}
}

/* ========================================================================== */
/* 3. ENGINE IMPOR KE-16 DATASET                                              */
/* ========================================================================== */

if (!function_exists('ppb_excel_select_main_sheet')) {
function ppb_excel_select_main_sheet($sheets, $columns_def) {
    $best = array('rows' => array(), 'present_cols' => array(), 'matched_count' => 0);
    foreach ($sheets as $sh) {
        $sname = strtolower((string) ($sh['name'] ?? ''));
        if (strpos($sname, 'panduan') !== false || strpos($sname, 'referensi') !== false || strpos($sname, 'petunjuk') !== false) {
            continue;
        }
        $mapped = ppb_io_map_sheet_rows($sh['rows'] ?? array(), $columns_def);
        if (!empty($mapped['rows']) && $mapped['matched_count'] > $best['matched_count']) {
            $best = $mapped;
        }
    }
    return $best;
}
}

if (!function_exists('ppb_excel_has_val')) {
function ppb_excel_has_val($row, $present_map, $key) {
    if (!isset($present_map[$key])) return false;
    return trim((string) ($row[$key] ?? '')) !== '';
}
}

if (!function_exists('ppb_excel_pick_field')) {
function ppb_excel_pick_field($row, $present_map, $existing, $key, $default = '', $sanitizer = null) {
    if (isset($present_map[$key])) {
        $raw = trim((string) ($row[$key] ?? ''));
        if ($raw !== '') {
            return $sanitizer ? call_user_func($sanitizer, $raw) : sanitize_text_field($raw);
        }
    }
    if (is_array($existing) && array_key_exists($key, $existing)) {
        return $existing[$key];
    }
    return $default;
}
}

if (!function_exists('ppb_excel_import_matkul')) {
function ppb_excel_import_matkul($mapped, $mode) {
    $rows         = $mapped['rows'];
    $present_cols = $mapped['present_cols'];
    $present_map  = array_flip($present_cols);

    $existing_list = ($mode === 'replace') ? array() : (function_exists('ppb_get_mata_kuliah_list') ? ppb_get_mata_kuliah_list(true) : array());
    $by_id   = array();
    $by_key  = array();
    $by_code = array();
    $old_affected_slugs = array();

    if ($mode === 'replace' && function_exists('ppb_get_mata_kuliah_list')) {
        foreach (ppb_get_mata_kuliah_list(true) as $old_c) {
            foreach ((array) ($old_c['dosen_slugs'] ?? array()) as $sl) {
                $old_affected_slugs[] = sanitize_title($sl);
            }
        }
    }

    foreach ($existing_list as $idx => $c) {
        $cid = sanitize_key($c['id'] ?? '');
        if ($cid !== '') $by_id[$cid] = $idx;
        $jen  = strtoupper(trim((string) ($c['jenjang'] ?? 'S1')));
        $kod  = strtoupper(trim((string) ($c['kode'] ?? '')));
        $norm = function_exists('ppb_matkul_normalize_text') ? ppb_matkul_normalize_text($c['nama'] ?? '') : strtolower(trim((string) ($c['nama'] ?? '')));
        $by_key[$jen . '|' . $kod . '|' . $norm] = $idx;
        $by_code[$jen . '|' . $kod][] = $idx;
    }

    $added = 0; $updated = 0; $skipped = 0; $warnings = array();
    $new_affected_slugs = array();

    foreach ($rows as $row) {
        $row_num = (int) ($row['_row_num'] ?? 0);
        $kode = strtoupper(sanitize_text_field($row['kode'] ?? ''));
        $nama = sanitize_text_field($row['nama'] ?? '');
        if ($kode === '' || $nama === '') {
            $skipped++;
            $warnings[] = 'Baris ' . $row_num . ' dilewati karena Kode MK atau Nama Mata Kuliah kosong.';
            continue;
        }

        $jenjang = strtoupper(sanitize_text_field($row['jenjang'] ?? ''));
        if (!in_array($jenjang, array('S1', 'S2', 'S3', 'S2/S3'), true)) {
            if ($jenjang !== '' && strpos($jenjang, 'S2') !== false && strpos($jenjang, 'S3') !== false) $jenjang = 'S2/S3';
            elseif ($jenjang !== '' && strpos($jenjang, 'S3') !== false) $jenjang = 'S3';
            elseif ($jenjang !== '' && strpos($jenjang, 'S2') !== false) $jenjang = 'S2';
            else $jenjang = '';
        }

        $norm_nama = function_exists('ppb_matkul_normalize_text') ? ppb_matkul_normalize_text($nama) : strtolower($nama);
        $raw_id    = sanitize_key($row['id'] ?? '');

        $match_idx = null;
        if ($raw_id !== '' && isset($by_id[$raw_id])) {
            $match_idx = $by_id[$raw_id];
        } else {
            $try_jen = $jenjang !== '' ? $jenjang : 'S1';
            $k_exact = $try_jen . '|' . $kode . '|' . $norm_nama;
            if (isset($by_key[$k_exact])) {
                $match_idx = $by_key[$k_exact];
            } elseif (isset($by_code[$try_jen . '|' . $kode]) && count($by_code[$try_jen . '|' . $kode]) === 1) {
                $cand_i   = $by_code[$try_jen . '|' . $kode][0];
                $cand_n   = function_exists('ppb_matkul_normalize_text') ? ppb_matkul_normalize_text($existing_list[$cand_i]['nama'] ?? '') : '';
                $pct      = 0.0;
                similar_text($cand_n, $norm_nama, $pct);
                if ($pct >= 85.0) {
                    $match_idx = $cand_i;
                }
            }
        }

        $existing = ($match_idx !== null) ? $existing_list[$match_idx] : array();
        if ($jenjang === '') {
            $jenjang = $existing['jenjang'] ?? 'S1';
        }

        // Kumpulkan dosen sebelum update agar yang dilepas juga tersinkron
        foreach ((array) ($existing['dosen_slugs'] ?? array()) as $osl) {
            $old_affected_slugs[] = sanitize_title($osl);
        }

        // Dosen pengampu
        $dosen_slugs = $existing['dosen_slugs'] ?? array();
        if (isset($present_map['dosen_pengampu'])) {
            $raw_dp = trim((string) ($row['dosen_pengampu'] ?? ''));
            if ($raw_dp !== '') {
                $unres = array();
                $dosen_slugs = ppb_excel_resolve_dosen_list($raw_dp, $unres);
                foreach ($unres as $ut) {
                    $warnings[] = 'Baris ' . $row_num . ' (' . $kode . '): dosen "' . $ut . '" belum ditemukan di direktori dosen.';
                }
            } elseif ($mode === 'replace') {
                $dosen_slugs = array();
            }
        }
        foreach ($dosen_slugs as $nsl) {
            $new_affected_slugs[] = $nsl;
        }

        $sem = ppb_excel_has_val($row, $present_map, 'semester')
            ? (int) $row['semester']
            : ($existing['semester'] ?? (function_exists('ppb_matkul_infer_semester') ? ppb_matkul_infer_semester($kode, $jenjang) : 1));
        $sks = ppb_excel_has_val($row, $present_map, 'sks')
            ? (int) $row['sks']
            : ($existing['sks'] ?? 3);

        $in_kur = $existing['in_kurikulum'] ?? true;
        if (isset($present_map['in_kurikulum']) && trim((string) ($row['in_kurikulum'] ?? '')) !== '') {
            $in_kur = ppb_excel_parse_bool($row['in_kurikulum'], true);
        }

        $course_raw = array(
            'id'           => !empty($existing['id']) ? $existing['id'] : ($raw_id !== '' ? $raw_id : ''),
            'kode'         => $kode,
            'nama'         => $nama,
            'jenjang'      => $jenjang,
            'semester'     => $sem,
            'sks'          => $sks,
            'sem_title'    => ppb_excel_pick_field($row, $present_map, $existing, 'sem_title', ''),
            'rumpun'       => ppb_excel_pick_field($row, $present_map, $existing, 'rumpun', 'Wajib Prodi'),
            'sdg'          => ppb_excel_pick_field($row, $present_map, $existing, 'sdg', 'SDG 4'),
            'cpmk'         => ppb_excel_pick_field($row, $present_map, $existing, 'cpmk', '', 'sanitize_textarea_field'),
            'rps_url'      => ppb_excel_pick_field($row, $present_map, $existing, 'rps_url', '', 'ppb_excel_clean_doc_url'),
            'sap_url'      => ppb_excel_pick_field($row, $present_map, $existing, 'sap_url', '', 'ppb_excel_clean_doc_url'),
            'dosen_slugs'  => $dosen_slugs,
            'in_kurikulum' => $in_kur,
        );
        $clean = function_exists('ppb_matkul_sanitize_course') ? ppb_matkul_sanitize_course($course_raw, $existing) : $course_raw;

        if ($match_idx !== null) {
            $existing_list[$match_idx] = $clean;
            $updated++;
        } else {
            $existing_list[] = $clean;
            $new_i = count($existing_list) - 1;
            $by_id[$clean['id']] = $new_i;
            $by_key[$clean['jenjang'] . '|' . $clean['kode'] . '|' . $norm_nama] = $new_i;
            $added++;
        }
    }

    if ($added === 0 && $updated === 0) {
        return new WP_Error('no_valid_rows', 'Tidak ada baris mata kuliah valid yang dapat diimpor. Data existing tetap dipertahankan.');
    }

    if (function_exists('ppb_save_mata_kuliah_list')) {
        ppb_save_mata_kuliah_list($existing_list);
    } else {
        update_option('ppb_mata_kuliah_list', array_values($existing_list), false);
    }

    if (function_exists('ppb_sync_mata_kuliah_to_dosen')) {
        if ($mode === 'replace') {
            ppb_sync_mata_kuliah_to_dosen($existing_list, null);
        } else {
            $target_slugs = array_values(array_filter(array_unique(array_merge($old_affected_slugs, $new_affected_slugs))));
            if (!empty($target_slugs)) {
                ppb_sync_mata_kuliah_to_dosen($existing_list, $target_slugs);
            }
        }
    }
    ppb_excel_purge_caches();

    return array(
        'added'    => $added,
        'updated'  => $updated,
        'removed'  => 0,
        'skipped'  => $skipped,
        'total'    => count($existing_list),
        'warnings' => $warnings,
    );
}
}

if (!function_exists('ppb_excel_import_pnbp')) {
function ppb_excel_import_pnbp($sheets, $schema, $mode) {
    $current = function_exists('ppb_get_mis_pnbp_data') ? ppb_get_mis_pnbp_data() : array('riset' => array(), 'pkm' => array());
    $riset_list = ($mode === 'replace') ? array() : array_values($current['riset'] ?? array());
    $pkm_list   = ($mode === 'replace') ? array() : array_values($current['pkm'] ?? array());

    $r_idx = array();
    foreach ($riset_list as $i => $r) {
        $r_idx[strtolower(trim((string) ($r['tahun'] ?? '')) . '|' . trim((string) ($r['judul'] ?? '')))] = $i;
    }
    $p_idx = array();
    foreach ($pkm_list as $i => $p) {
        $p_idx[strtolower(trim((string) ($p['tahun'] ?? '')) . '|' . trim((string) ($p['judul'] ?? '')))] = $i;
    }

    $added = 0; $updated = 0; $skipped = 0; $warnings = array();
    $processed_riset = false;
    $processed_pkm   = false;

    foreach ($sheets as $sh) {
        $sname = strtolower((string) ($sh['name'] ?? ''));
        if (strpos($sname, 'panduan') !== false || strpos($sname, 'referensi') !== false) continue;

        $is_pkm_sheet   = (strpos($sname, 'pkm') !== false || strpos($sname, 'pengabdian') !== false);
        $is_riset_sheet = (strpos($sname, 'riset') !== false || strpos($sname, 'penelitian') !== false);

        $cols_def = $is_pkm_sheet ? $schema['columns_pkm'] : ($is_riset_sheet ? $schema['columns_riset'] : $schema['columns']);
        $mapped   = ppb_io_map_sheet_rows($sh['rows'] ?? array(), $cols_def);
        if (empty($mapped['rows'])) continue;

        $pmap = array_flip($mapped['present_cols']);
        foreach ($mapped['rows'] as $row) {
            $judul = sanitize_text_field($row['judul'] ?? '');
            if ($judul === '') { $skipped++; continue; }

            $target_pkm = $is_pkm_sheet;
            if (!$is_pkm_sheet && !$is_riset_sheet) {
                $jval = strtolower(trim((string) ($row['jenis'] ?? ($row['skema'] ?? ''))));
                $target_pkm = (strpos($jval, 'pkm') !== false || strpos($jval, 'pengabdian') !== false || isset($pmap['mitra']));
            }

            $tahun = sanitize_text_field($row['tahun'] ?? date('Y'));
            $key   = strtolower($tahun . '|' . $judul);

            if ($target_pkm) {
                $processed_pkm = true;
                $m_i  = $p_idx[$key] ?? null;
                $prev = $m_i !== null ? $pkm_list[$m_i] : array();
                $item = array(
                    'tahun'       => $tahun,
                    'skema'       => ppb_excel_pick_field($row, $pmap, $prev, 'skema', 'PkM PNBP'),
                    'judul'       => $judul,
                    'ketua'       => ppb_excel_pick_field($row, $pmap, $prev, 'ketua', ''),
                    'mitra'       => ppb_excel_has_val($row, $pmap, 'mitra')
                        ? sanitize_text_field($row['mitra'])
                        : ppb_excel_pick_field($row, $pmap, $prev, 'anggota', ''),
                    'dana'        => ppb_excel_pick_field($row, $pmap, $prev, 'dana', ''),
                    'luaran'      => ppb_excel_pick_field($row, $pmap, $prev, 'luaran', ''),
                    'dokumen_url' => ppb_excel_pick_field($row, $pmap, $prev, 'dokumen_url', '', 'ppb_excel_clean_doc_url'),
                );
                if ($m_i !== null) { $pkm_list[$m_i] = $item; $updated++; }
                else { $pkm_list[] = $item; $p_idx[$key] = count($pkm_list) - 1; $added++; }
            } else {
                $processed_riset = true;
                $m_i  = $r_idx[$key] ?? null;
                $prev = $m_i !== null ? $riset_list[$m_i] : array();
                $item = array(
                    'tahun'       => $tahun,
                    'skema'       => ppb_excel_pick_field($row, $pmap, $prev, 'skema', 'Penelitian PNBP'),
                    'judul'       => $judul,
                    'ketua'       => ppb_excel_pick_field($row, $pmap, $prev, 'ketua', ''),
                    'anggota'     => ppb_excel_pick_field($row, $pmap, $prev, 'anggota', ''),
                    'dana'        => ppb_excel_pick_field($row, $pmap, $prev, 'dana', ''),
                    'luaran'      => ppb_excel_pick_field($row, $pmap, $prev, 'luaran', ''),
                    'dokumen_url' => ppb_excel_pick_field($row, $pmap, $prev, 'dokumen_url', '', 'ppb_excel_clean_doc_url'),
                );
                if ($m_i !== null) { $riset_list[$m_i] = $item; $updated++; }
                else { $riset_list[] = $item; $r_idx[$key] = count($riset_list) - 1; $added++; }
            }
        }
    }

    if ($added === 0 && $updated === 0) {
        return new WP_Error('no_valid_rows', 'Tidak ditemukan baris Hibah Penelitian atau PkM yang valid dalam file.');
    }
    if ($mode === 'replace') {
        if (!$processed_riset && !empty($current['riset'])) {
            $riset_list = $current['riset'];
            $warnings[] = 'Sheet Hibah Penelitian tidak disertakan sehingga data riset existing tetap dipertahankan.';
        }
        if (!$processed_pkm && !empty($current['pkm'])) {
            $pkm_list = $current['pkm'];
            $warnings[] = 'Sheet Hibah PkM tidak disertakan sehingga data PkM existing tetap dipertahankan.';
        }
    }

    update_option('ppb_mis_pnbp_data', array(
        'riset' => array_values($riset_list),
        'pkm'   => array_values($pkm_list),
    ), false);
    ppb_excel_purge_caches();

    return array(
        'added'    => $added,
        'updated'  => $updated,
        'removed'  => 0,
        'skipped'  => $skipped,
        'total'    => count($riset_list) + count($pkm_list),
        'warnings' => $warnings,
    );
}
}

if (!function_exists('ppb_excel_import_option_list')) {
function ppb_excel_import_option_list($dataset_key, $mapped, $mode) {
    $rows = $mapped['rows'];
    $pmap = array_flip($mapped['present_cols']);
    $added = 0; $updated = 0; $skipped = 0; $warnings = array();

    if ($dataset_key === 'jurnal') {
        $list = ($mode === 'replace') ? array() : (function_exists('ppb_get_jurnal_bk_data') ? ppb_get_jurnal_bk_data() : array());
        $by_name = array();
        foreach ($list as $i => $it) $by_name[strtolower(trim((string) ($it['nama'] ?? '')))] = $i;
        foreach ($rows as $row) {
            $nama = sanitize_text_field($row['nama'] ?? '');
            if ($nama === '') { $skipped++; continue; }
            $k = strtolower($nama);
            $mi = $by_name[$k] ?? null;
            $prev = $mi !== null ? $list[$mi] : array();
            $tema = strtolower(ppb_excel_pick_field($row, $pmap, $prev, 'tema', 'green'));
            if (!in_array($tema, array('green', 'gold', 'blue'), true)) $tema = 'green';
            $item = array(
                'badge'      => ppb_excel_pick_field($row, $pmap, $prev, 'badge', '🏅 Jurnal Resmi Prodi'),
                'tema'       => $tema,
                'issn'       => ppb_excel_pick_field($row, $pmap, $prev, 'issn', ''),
                'nama'       => $nama,
                'deskripsi'  => ppb_excel_pick_field($row, $pmap, $prev, 'deskripsi', '', 'sanitize_textarea_field'),
                'penerbit'   => ppb_excel_pick_field($row, $pmap, $prev, 'penerbit', ''),
                'frekuensi'  => ppb_excel_pick_field($row, $pmap, $prev, 'frekuensi', ''),
                'pengindeks' => ppb_excel_pick_field($row, $pmap, $prev, 'pengindeks', ''),
                'ojs_url'    => ppb_excel_pick_field($row, $pmap, $prev, 'ojs_url', '', 'ppb_excel_clean_doc_url'),
                'submit_url' => ppb_excel_pick_field($row, $pmap, $prev, 'submit_url', '', 'ppb_excel_clean_doc_url'),
            );
            if ($mi !== null) { $list[$mi] = $item; $updated++; }
            else { $list[] = $item; $by_name[$k] = count($list) - 1; $added++; }
        }
        if ($added + $updated === 0) return new WP_Error('no_valid_rows', 'Tidak ada baris jurnal valid untuk diimpor.');
        update_option('ppb_jurnal_bk_data', array_values($list), false);
        ppb_excel_purge_caches();
        $total = count($list);
    } elseif ($dataset_key === 'elibrary') {
        $list = ($mode === 'replace') ? array() : (function_exists('ppb_get_elibrary_items') ? ppb_get_elibrary_items() : array());
        $cat_labels = array('modul' => 'Modul Praktikum', 'buku' => 'Pedoman Akademik', 'asesmen' => 'Instrumen Asesmen', 'artikel' => 'Artikel Ilmiah');
        $by_kode = array(); $by_title = array(); $max_id = 0;
        foreach ($list as $i => $it) {
            if (($it['kode'] ?? '') !== '') $by_kode[strtoupper(trim($it['kode']))] = $i;
            if (($it['title'] ?? '') !== '') $by_title[strtolower(trim($it['title']))] = $i;
            $max_id = max($max_id, (int) ($it['id'] ?? ($i + 1)));
        }
        foreach ($rows as $row) {
            $title = sanitize_text_field($row['title'] ?? '');
            if ($title === '') { $skipped++; continue; }
            $kode = strtoupper(sanitize_text_field($row['kode'] ?? ''));
            $mi = ($kode !== '' && isset($by_kode[$kode])) ? $by_kode[$kode] : ($by_title[strtolower($title)] ?? null);
            $prev = $mi !== null ? $list[$mi] : array();
            if ($kode === '') $kode = $prev['kode'] ?? sprintf('ELB-%02d', count($list) + 1);
            $cat = ppb_excel_normalize_enum('elibrary', 'category', ppb_excel_pick_field($row, $pmap, $prev, 'category', 'modul'));
            $furl = ppb_excel_pick_field($row, $pmap, $prev, 'file_url', '#', 'ppb_excel_clean_doc_url');
            if ($furl === '') $furl = '#';
            $item = array(
                'id'        => !empty($prev['id']) ? (int) $prev['id'] : (++$max_id),
                'kode'      => $kode,
                'category'  => $cat,
                'cat_label' => ppb_excel_pick_field($row, $pmap, $prev, 'cat_label', $cat_labels[$cat] ?? 'Modul Praktikum'),
                'authors'   => ppb_excel_pick_field($row, $pmap, $prev, 'authors', 'Tim Dosen BK FIP UNM'),
                'year'      => ppb_excel_pick_field($row, $pmap, $prev, 'year', date('Y')),
                'meta'      => ppb_excel_pick_field($row, $pmap, $prev, 'meta', '64 Hlm · PDF 2.5 MB'),
                'format'    => ppb_excel_pick_field($row, $pmap, $prev, 'format', 'PDF'),
                'title'     => $title,
                'desc'      => ppb_excel_pick_field($row, $pmap, $prev, 'desc', '', 'sanitize_textarea_field'),
                'file_url'  => $furl,
            );
            if ($mi !== null) { $list[$mi] = $item; $updated++; }
            else { $list[] = $item; $ni = count($list) - 1; $by_kode[strtoupper($kode)] = $ni; $by_title[strtolower($title)] = $ni; $added++; }
        }
        if ($added + $updated === 0) return new WP_Error('no_valid_rows', 'Tidak ada baris E-Library valid untuk diimpor.');
        update_option('ppb_elibrary_items', array_values($list), false);
        $total = count($list);
    } elseif ($dataset_key === 'lab_kegiatan') {
        $list = ($mode === 'replace') ? array() : (function_exists('ppb_get_lab_kegiatan_list') ? ppb_get_lab_kegiatan_list() : array());
        $idx_map = array();
        foreach ($list as $i => $it) $idx_map[strtolower(trim((string) ($it['judul'] ?? '')))] = $i;
        foreach ($rows as $row) {
            $judul = sanitize_text_field($row['judul'] ?? '');
            if ($judul === '') { $skipped++; continue; }
            $k = strtolower($judul);
            $mi = $idx_map[$k] ?? null;
            $prev = $mi !== null ? $list[$mi] : array();
            $item = array(
                'judul'     => $judul,
                'periode'   => ppb_excel_pick_field($row, $pmap, $prev, 'periode', ''),
                'deskripsi' => ppb_excel_pick_field($row, $pmap, $prev, 'deskripsi', '', 'sanitize_textarea_field'),
                'peserta'   => ppb_excel_pick_field($row, $pmap, $prev, 'peserta', ''),
                'status'    => ppb_excel_pick_field($row, $pmap, $prev, 'status', 'Aktif Berjalan'),
                'dok_url'   => ppb_excel_pick_field($row, $pmap, $prev, 'dok_url', '', 'ppb_excel_clean_doc_url'),
            );
            if ($mi !== null) { $list[$mi] = $item; $updated++; }
            else { $list[] = $item; $idx_map[$k] = count($list) - 1; $added++; }
        }
        if ($added + $updated === 0) return new WP_Error('no_valid_rows', 'Tidak ada baris Kegiatan Lab valid untuk diimpor.');
        update_option('ppb_lab_kegiatan_list', array_values($list), false);
        $total = count($list);
    } elseif ($dataset_key === 'lab_lomba') {
        $list = ($mode === 'replace') ? array() : (function_exists('ppb_get_lab_lomba_list') ? ppb_get_lab_lomba_list() : array());
        $idx_map = array();
        foreach ($list as $i => $it) $idx_map[strtolower(trim((string) ($it['nama'] ?? '')))] = $i;
        foreach ($rows as $row) {
            $nama = sanitize_text_field($row['nama'] ?? '');
            if ($nama === '') { $skipped++; continue; }
            $k = strtolower($nama);
            $mi = $idx_map[$k] ?? null;
            $prev = $mi !== null ? $list[$mi] : array();
            $item = array(
                'nama'          => $nama,
                'penyelenggara' => ppb_excel_pick_field($row, $pmap, $prev, 'penyelenggara', ''),
                'kategori'      => ppb_excel_pick_field($row, $pmap, $prev, 'kategori', 'Tingkat Nasional'),
                'prestasi'      => ppb_excel_pick_field($row, $pmap, $prev, 'prestasi', ''),
                'lingkup'       => ppb_excel_pick_field($row, $pmap, $prev, 'lingkup', ''),
                'dok_url'       => ppb_excel_pick_field($row, $pmap, $prev, 'dok_url', '', 'ppb_excel_clean_doc_url'),
            );
            if ($mi !== null) { $list[$mi] = $item; $updated++; }
            else { $list[] = $item; $idx_map[$k] = count($list) - 1; $added++; }
        }
        if ($added + $updated === 0) return new WP_Error('no_valid_rows', 'Tidak ada baris Lomba Lab valid untuk diimpor.');
        update_option('ppb_lab_lomba_list', array_values($list), false);
        $total = count($list);
    } elseif ($dataset_key === 'sarpras') {
        $list = ($mode === 'replace') ? array() : (function_exists('ppb_get_sarpras_list') ? ppb_get_sarpras_list() : array());
        $idx_map = array();
        foreach ($list as $i => $it) $idx_map[strtolower(trim((string) ($it['nama'] ?? '')))] = $i;
        foreach ($rows as $row) {
            $nama = sanitize_text_field($row['nama'] ?? '');
            if ($nama === '') { $skipped++; continue; }
            $k = strtolower($nama);
            $mi = $idx_map[$k] ?? null;
            $prev = $mi !== null ? $list[$mi] : array();
            $tema = sanitize_key(ppb_excel_pick_field($row, $pmap, $prev, 'tema', 'green'));
            if (!in_array($tema, array('green', 'gold', 'indigo'), true)) $tema = 'green';
            $item = array(
                'kategori'  => ppb_excel_pick_field($row, $pmap, $prev, 'kategori', 'Fasilitas Prodi'),
                'nama'      => $nama,
                'deskripsi' => ppb_excel_pick_field($row, $pmap, $prev, 'deskripsi', '', 'sanitize_textarea_field'),
                'kapasitas' => ppb_excel_pick_field($row, $pmap, $prev, 'kapasitas', ''),
                'lokasi'    => ppb_excel_pick_field($row, $pmap, $prev, 'lokasi', ''),
                'icon'      => ppb_excel_pick_field($row, $pmap, $prev, 'icon', '🏛️') ?: '🏛️',
                'foto_url'  => ppb_excel_pick_field($row, $pmap, $prev, 'foto_url', '', 'ppb_excel_clean_doc_url'),
                'dok_url'   => ppb_excel_pick_field($row, $pmap, $prev, 'dok_url', '', 'ppb_excel_clean_doc_url'),
                'tema'      => $tema,
            );
            if ($mi !== null) { $list[$mi] = $item; $updated++; }
            else { $list[] = $item; $idx_map[$k] = count($list) - 1; $added++; }
        }
        if ($added + $updated === 0) return new WP_Error('no_valid_rows', 'Tidak ada baris Sarpras valid untuk diimpor.');
        update_option('ppb_sarpras_list', array_values($list), false);
        ppb_excel_purge_caches();
        $total = count($list);
    } elseif ($dataset_key === 'maba_tren') {
        $full = function_exists('ppb_get_maba_statistik_data') ? ppb_get_maba_statistik_data() : array('portal_url' => 'https://penerimaan.unm.ac.id/', 'unm_url' => 'https://unm.ac.id/', 'jalur' => array(), 'tren' => array());
        $list = ($mode === 'replace') ? array() : array_values($full['tren'] ?? array());
        $idx_map = array();
        foreach ($list as $i => $it) $idx_map[trim((string) ($it['tahun'] ?? ''))] = $i;
        foreach ($rows as $row) {
            $tahun = sanitize_text_field($row['tahun'] ?? '');
            if ($tahun === '') { $skipped++; continue; }
            $mi   = $idx_map[$tahun] ?? null;
            $prev = $mi !== null ? $list[$mi] : array();
            $dt   = ppb_excel_has_val($row, $pmap, 'daya_tampung')    ? max(0, (int) $row['daya_tampung'])    : (int) ($prev['daya_tampung'] ?? 0);
            $snb  = ppb_excel_has_val($row, $pmap, 'peminat_snbp')    ? max(0, (int) $row['peminat_snbp'])    : (int) ($prev['peminat_snbp'] ?? 0);
            $sbt  = ppb_excel_has_val($row, $pmap, 'peminat_snbt')    ? max(0, (int) $row['peminat_snbt'])    : (int) ($prev['peminat_snbt'] ?? 0);
            $mnd  = ppb_excel_has_val($row, $pmap, 'peminat_mandiri') ? max(0, (int) $row['peminat_mandiri']) : (int) ($prev['peminat_mandiri'] ?? 0);
            $tot  = ppb_excel_has_val($row, $pmap, 'total_peminat')   ? max(0, (int) $row['total_peminat'])   : 0;
            if ($tot <= 0) $tot = $snb + $sbt + $mnd;
            $ket = ppb_excel_has_val($row, $pmap, 'keketatan') ? sanitize_text_field($row['keketatan']) : '';
            if ($ket === '' && $dt > 0) $ket = '1 : ' . max(1, (int) round($tot / $dt));
            $item = array(
                'tahun'           => $tahun,
                'daya_tampung'    => $dt,
                'peminat_snbp'    => $snb,
                'peminat_snbt'    => $sbt,
                'peminat_mandiri' => $mnd,
                'total_peminat'   => $tot,
                'keketatan'       => $ket,
            );
            if ($mi !== null) { $list[$mi] = $item; $updated++; }
            else { $list[] = $item; $idx_map[$tahun] = count($list) - 1; $added++; }
        }
        if ($added + $updated === 0) return new WP_Error('no_valid_rows', 'Tidak ada baris Tren Maba valid untuk diimpor.');
        usort($list, function($a, $b) { return strcmp((string) ($b['tahun'] ?? ''), (string) ($a['tahun'] ?? '')); });
        $full['tren'] = array_values($list);
        update_option('ppb_maba_statistik_data', $full, false);
        $total = count($list);
    } elseif ($dataset_key === 'mbkm') {
        $full = function_exists('ppb_get_mbkm_kemanusiaan_data') ? ppb_get_mbkm_kemanusiaan_data() : array('mbkm_items' => array(), 'proyek_kemanusiaan' => array());
        $list = ($mode === 'replace') ? array() : array_values($full['mbkm_items'] ?? array());
        $idx_map = array();
        foreach ($list as $i => $it) $idx_map[strtolower(trim((string) ($it['judul'] ?? '')))] = $i;
        foreach ($rows as $row) {
            $judul = sanitize_text_field($row['judul'] ?? '');
            if ($judul === '') { $skipped++; continue; }
            $k = strtolower($judul);
            $mi = $idx_map[$k] ?? null;
            $prev = $mi !== null ? $list[$mi] : array();
            $item = array(
                'tahun'     => ppb_excel_pick_field($row, $pmap, $prev, 'tahun', ''),
                'lokasi'    => ppb_excel_pick_field($row, $pmap, $prev, 'lokasi', ''),
                'judul'     => $judul,
                'deskripsi' => ppb_excel_pick_field($row, $pmap, $prev, 'deskripsi', '', 'sanitize_textarea_field'),
                'catatan'   => ppb_excel_pick_field($row, $pmap, $prev, 'catatan', ''),
                'dok_url'   => ppb_excel_pick_field($row, $pmap, $prev, 'dok_url', '', 'ppb_excel_clean_doc_url'),
            );
            if ($mi !== null) { $list[$mi] = $item; $updated++; }
            else { $list[] = $item; $idx_map[$k] = count($list) - 1; $added++; }
        }
        if ($added + $updated === 0) return new WP_Error('no_valid_rows', 'Tidak ada baris MBKM valid untuk diimpor.');
        $full['mbkm_items'] = array_values($list);
        update_option('ppb_mbkm_kemanusiaan_data', $full, false);
        $total = count($list);
    } else {
        return new WP_Error('unsupported_dataset', 'Dataset tidak dikenali.');
    }

    return array(
        'added'    => $added,
        'updated'  => $updated,
        'removed'  => 0,
        'skipped'  => $skipped,
        'total'    => $total,
        'warnings' => $warnings,
    );
}
}

if (!function_exists('ppb_excel_import_cpt')) {
function ppb_excel_import_cpt($dataset_key, $schema, $mapped, $mode) {
    $post_type = $schema['post_type'];
    $rows      = $mapped['rows'];
    $pmap      = array_flip($mapped['present_cols']);
    $existing  = ppb_excel_get_cpt_posts($post_type);

    $idx_primary = array();
    $idx_second  = array();
    foreach ($existing as $p) {
        if ($dataset_key === 'inventaris') {
            $c = strtoupper(trim((string) get_post_meta($p->ID, '_ppb_inv_kode_alat', true)));
            if ($c !== '') $idx_primary[$c] = $p->ID;
            $idx_second[strtolower(trim((string) $p->post_title))] = $p->ID;
        } elseif ($dataset_key === 'jadwal') {
            $nim = strtolower(trim((string) get_post_meta($p->ID, '_ppb_nim', true)));
            $jns = strtolower(trim((string) get_post_meta($p->ID, '_ppb_jenis_ujian', true)));
            $tgl = trim((string) get_post_meta($p->ID, '_ppb_tanggal', true));
            $nm  = strtolower(trim((string) get_post_meta($p->ID, '_ppb_nama_mahasiswa', true)));
            if ($nim !== '') $idx_primary[$nim . '|' . $jns . '|' . $tgl] = $p->ID;
            if ($nm !== '')  $idx_second[$nm . '|' . $jns . '|' . $tgl] = $p->ID;
        } elseif ($dataset_key === 'prestasi') {
            $nm  = strtolower(trim((string) get_post_meta($p->ID, '_ppb_nama_mahasiswa', true)));
            $cap = strtolower(trim((string) get_post_meta($p->ID, '_ppb_capaian', true)));
            $thn = trim((string) get_post_meta($p->ID, '_ppb_tahun', true));
            $idx_primary[$nm . '|' . $cap . '|' . $thn] = $p->ID;
        } elseif ($dataset_key === 'karya') {
            $jdl = strtolower(trim((string) (get_post_meta($p->ID, '_ppb_karya_judul', true) ?: $p->post_title)));
            $thn = trim((string) get_post_meta($p->ID, '_ppb_tahun', true));
            $idx_primary[$jdl . '|' . $thn] = $p->ID;
            $idx_second[$jdl] = $p->ID;
        } elseif ($dataset_key === 'dokumen') {
            $nom = strtolower(trim((string) get_post_meta($p->ID, '_ppb_dokumen_nomor', true)));
            if ($nom !== '') $idx_primary[$nom] = $p->ID;
            $idx_second[strtolower(trim((string) $p->post_title))] = $p->ID;
        } elseif ($dataset_key === 'template_dok') {
            $rev = strtolower(trim((string) get_post_meta($p->ID, '_ppb_tpl_nomor_revisi', true)));
            $t   = strtolower(trim((string) $p->post_title));
            if ($rev !== '' && $t !== '') $idx_primary[$t . '|' . $rev] = $p->ID;
            $idx_second[$t] = $p->ID;
        }
    }

    $added = 0; $updated = 0; $removed = 0; $skipped = 0; $touched_ids = array(); $warnings = array();
    $meta_save_list = array();

    foreach ($rows as $row) {
        $title = ''; $meta = array(); $match_id = 0;

        if ($dataset_key === 'inventaris') {
            $kode = strtoupper(sanitize_text_field($row['kode_alat'] ?? ''));
            $nama = sanitize_text_field($row['nama'] ?? '');
            if ($nama === '' && $kode === '') { $skipped++; continue; }
            $title    = $nama !== '' ? $nama : $kode;
            $match_id = ($kode !== '' && isset($idx_primary[$kode])) ? $idx_primary[$kode] : ($idx_second[strtolower($title)] ?? 0);
            $meta = array(
                '_ppb_inv_kode_alat'   => array('kode_alat', $kode ?: 'LAB-NEW', null),
                '_ppb_inv_kategori'    => array('kategori', 'tes_psikologi', function($v) { return function_exists('ppb_normalize_inventaris_kategori') ? ppb_normalize_inventaris_kategori($v) : sanitize_text_field($v); }),
                '_ppb_inv_jumlah_unit' => array('jumlah_unit', '1 Unit', null),
                '_ppb_inv_kondisi'     => array('kondisi', 'baik', function($v) { return function_exists('ppb_normalize_inventaris_kondisi') ? ppb_normalize_inventaris_kondisi($v) : sanitize_text_field($v); }),
                '_ppb_inv_lokasi'      => array('lokasi', 'Laboratorium BK FIP UNM', null),
                '_ppb_inv_spesifikasi' => array('spesifikasi', '', null),
                '_ppb_inv_keterangan'  => array('keterangan', '', 'sanitize_textarea_field'),
                '_ppb_inv_manual_link' => array('manual_link', '', 'ppb_excel_clean_doc_url'),
                '_ppb_inv_foto_link'   => array('foto_link', '', 'ppb_excel_clean_doc_url'),
            );
        } elseif ($dataset_key === 'jadwal') {
            $nama = sanitize_text_field($row['nama_mahasiswa'] ?? '');
            if ($nama === '') { $skipped++; continue; }
            $jns_raw = trim((string) ($row['jenis_ujian'] ?? 'seminar_proposal'));
            $jns     = ppb_excel_normalize_enum('jadwal', 'jenis_ujian', $jns_raw);
            $tgl     = ppb_excel_normalize_date($row['tanggal'] ?? '');
            $nim     = sanitize_text_field($row['nim'] ?? '');
            $j_lbls  = array('seminar_proposal' => 'Seminar Proposal', 'seminar_hasil' => 'Seminar Hasil', 'ujian_skripsi' => 'Ujian Skripsi');
            $title   = $nama . ' (' . ($j_lbls[$jns] ?? 'Seminar') . ')';
            $match_id = ($nim !== '' && isset($idx_primary[strtolower($nim) . '|' . $jns . '|' . $tgl]))
                ? $idx_primary[strtolower($nim) . '|' . $jns . '|' . $tgl]
                : ($idx_second[strtolower($nama) . '|' . $jns . '|' . $tgl] ?? 0);
            $meta = array(
                '_ppb_jenis_ujian'      => array('jenis_ujian', 'seminar_proposal', function($v) { return ppb_excel_normalize_enum('jadwal', 'jenis_ujian', $v); }),
                '_ppb_status'           => array('status', 'terjadwal', function($v) { return ppb_excel_normalize_enum('jadwal', 'status', $v); }),
                '_ppb_nama_mahasiswa'   => array('nama_mahasiswa', $nama, null),
                '_ppb_nim'              => array('nim', '', null),
                '_ppb_angkatan'         => array('angkatan', '2022', null),
                '_ppb_tanggal'          => array('tanggal', $tgl, 'ppb_excel_normalize_date'),
                '_ppb_jam_mulai'        => array('jam_mulai', '08:30', null),
                '_ppb_jam_selesai'      => array('jam_selesai', '10:00', null),
                '_ppb_ruangan'          => array('ruangan', 'Ruang Sidang FIP UNM Lt. 2', null),
                '_ppb_judul_penelitian' => array('judul_penelitian', '', 'sanitize_textarea_field'),
                '_ppb_pembimbing_1'     => array('pembimbing_1', '', null),
                '_ppb_pembimbing_2'     => array('pembimbing_2', '', null),
                '_ppb_penguji_1'        => array('penguji_1', '', null),
                '_ppb_penguji_2'        => array('penguji_2', '', null),
                '_ppb_link_daring'      => array('link_daring', '', 'ppb_excel_clean_doc_url'),
            );
        } elseif ($dataset_key === 'prestasi') {
            $nama = sanitize_text_field($row['nama_mahasiswa'] ?? '');
            $cap  = sanitize_text_field($row['capaian'] ?? '');
            if ($nama === '' || $cap === '') { $skipped++; continue; }
            $thn      = sanitize_text_field($row['tahun'] ?? date('Y'));
            $title    = $cap . ' - ' . $nama;
            $match_id = $idx_primary[strtolower($nama . '|' . $cap . '|' . $thn)] ?? 0;
            $meta = array(
                '_ppb_nama_mahasiswa'        => array('nama_mahasiswa', $nama, null),
                '_ppb_nim'                   => array('nim', '', null),
                '_ppb_angkatan'              => array('angkatan', '', null),
                '_ppb_capaian'               => array('capaian', $cap, null),
                '_ppb_tingkat'               => array('tingkat', 'nasional', function($v) { return ppb_excel_normalize_enum('prestasi', 'tingkat', $v); }),
                '_ppb_tahun'                 => array('tahun', date('Y'), null),
                '_ppb_nama_kegiatan'         => array('nama_kegiatan', '', null),
                '_ppb_penyelenggara'         => array('penyelenggara', '', null),
                '_ppb_dosen_pembimbing'      => array('dosen_pembimbing', '', null),
                '_ppb_deskripsi'             => array('deskripsi', '', 'sanitize_textarea_field'),
                '_ppb_drive_sertifikat_link' => array('drive_sertifikat_link', '', 'ppb_excel_clean_doc_url'),
                '_ppb_drive_foto_link'       => array('drive_foto_link', '', 'ppb_excel_clean_doc_url'),
                '_ppb_is_featured'           => array('is_featured', '0', function($v) { return ppb_excel_parse_bool($v, false) ? '1' : '0'; }),
            );
        } elseif ($dataset_key === 'karya') {
            $judul = sanitize_text_field($row['judul'] ?? '');
            if ($judul === '') { $skipped++; continue; }
            $thn      = sanitize_text_field($row['tahun'] ?? date('Y'));
            $title    = $judul;
            $match_id = $idx_primary[strtolower($judul . '|' . $thn)] ?? ($idx_second[strtolower($judul)] ?? 0);
            $meta = array(
                '_ppb_karya_judul'      => array('judul', $judul, null),
                '_ppb_kategori'         => array('kategori', 'media_bimbingan', function($v) { return ppb_excel_normalize_enum('karya', 'kategori', $v); }),
                '_ppb_badge_unggulan'   => array('badge_unggulan', '', null),
                '_ppb_tahun'            => array('tahun', date('Y'), null),
                '_ppb_pembuat'          => array('pembuat', '', null),
                '_ppb_nim_ketua'        => array('nim_ketua', '', null),
                '_ppb_angkatan'         => array('angkatan', '', null),
                '_ppb_dosen_pembimbing' => array('dosen_pembimbing', '', null),
                '_ppb_mata_kuliah'      => array('mata_kuliah', '', null),
                '_ppb_deskripsi'        => array('deskripsi', '', 'sanitize_textarea_field'),
                '_ppb_manfaat'          => array('manfaat', '', 'sanitize_textarea_field'),
                '_ppb_foto_karya_link'  => array('foto_karya_link', '', 'ppb_excel_clean_doc_url'),
                '_ppb_drive_file_link'  => array('drive_file_link', '', 'ppb_excel_clean_doc_url'),
            );
        } elseif ($dataset_key === 'dokumen') {
            $judul = sanitize_text_field($row['judul'] ?? '');
            if ($judul === '') { $skipped++; continue; }
            $nomor    = strtolower(trim((string) ($row['nomor'] ?? '')));
            $title    = $judul;
            $match_id = ($nomor !== '' && isset($idx_primary[$nomor])) ? $idx_primary[$nomor] : ($idx_second[strtolower($judul)] ?? 0);
            $meta = array(
                '_ppb_dokumen_kategori'  => array('kategori', 'renstra', function($v) { return ppb_excel_normalize_enum('dokumen', 'kategori', $v); }),
                '_ppb_dokumen_nomor'     => array('nomor', '', null),
                '_ppb_dokumen_tahun'     => array('tahun', date('Y'), null),
                '_ppb_dokumen_pengesah'  => array('pengesah', '', null),
                '_ppb_dokumen_size'      => array('size', 'PDF', null),
                '_ppb_dokumen_deskripsi' => array('deskripsi', '', 'sanitize_textarea_field'),
                '_ppb_dokumen_link'      => array('link', '', 'ppb_excel_clean_doc_url'),
            );
        } elseif ($dataset_key === 'template_dok') {
            $judul = sanitize_text_field($row['judul'] ?? '');
            if ($judul === '') { $skipped++; continue; }
            $rev      = strtolower(trim((string) ($row['nomor_revisi'] ?? '')));
            $title    = $judul;
            $match_id = ($rev !== '' && isset($idx_primary[strtolower($judul) . '|' . $rev]))
                ? $idx_primary[strtolower($judul) . '|' . $rev]
                : ($idx_second[strtolower($judul)] ?? 0);
            $meta = array(
                '_ppb_tpl_kategori'     => array('kategori', 'skripsi', function($v) { return ppb_excel_normalize_enum('template_dok', 'kategori', $v); }),
                '_ppb_tpl_format_file'  => array('format_file', 'DOCX', function($v) { return ppb_excel_normalize_enum('template_dok', 'format_file', $v); }),
                '_ppb_tpl_ukuran_kb'    => array('ukuran_kb', '250', null),
                '_ppb_tpl_nomor_revisi' => array('nomor_revisi', 'REV-2026/A', null),
                '_ppb_tpl_deskripsi'    => array('deskripsi', '', 'sanitize_textarea_field'),
                '_ppb_tpl_file_link'    => array('file_link', '', 'ppb_excel_clean_doc_url'),
            );
        }

        $meta_save_list[] = array('match_id' => $match_id, 'title' => $title, 'row' => $row, 'meta_spec' => $meta);
    }

    if (empty($meta_save_list)) {
        return new WP_Error('no_valid_rows', 'Tidak ditemukan baris data valid untuk CPT ' . $post_type . '.');
    }

    foreach ($meta_save_list as $entry) {
        $pid = (int) $entry['match_id'];
        if ($pid > 0) {
            if (function_exists('wp_update_post')) {
                wp_update_post(array('ID' => $pid, 'post_title' => $entry['title'], 'post_status' => 'publish'));
            }
            $updated++;
        } else {
            $pid = wp_insert_post(array(
                'post_type'   => $post_type,
                'post_title'  => $entry['title'],
                'post_status' => 'publish',
            ));
            if (!$pid || is_wp_error($pid)) {
                $skipped++;
                continue;
            }
            $added++;
        }
        $touched_ids[] = (int) $pid;

        foreach ($entry['meta_spec'] as $mkey => $spec) {
            list($col_k, $def_val, $cb) = $spec;
            $has_col = isset($pmap[$col_k]);
            $raw_val = $has_col ? trim((string) ($entry['row'][$col_k] ?? '')) : '';

            if ($raw_val !== '') {
                $clean_val = $cb ? call_user_func($cb, $raw_val) : sanitize_text_field($raw_val);
                update_post_meta($pid, $mkey, $clean_val);
            } elseif ($pid > 0 && $entry['match_id'] === 0) {
                update_post_meta($pid, $mkey, $def_val);
            } elseif ($mode === 'replace' && $has_col) {
                update_post_meta($pid, $mkey, $def_val);
            }
        }
    }

    if ($mode === 'replace' && !empty($touched_ids)) {
        foreach ($existing as $ex_post) {
            if (!in_array((int) $ex_post->ID, $touched_ids, true)) {
                if (function_exists('wp_trash_post')) {
                    wp_trash_post($ex_post->ID);
                } elseif (function_exists('wp_update_post')) {
                    wp_update_post(array('ID' => $ex_post->ID, 'post_status' => 'trash'));
                }
                $removed++;
            }
        }
    }

    $cache_pids = array('jadwal' => array(30), 'prestasi' => array(32), 'karya' => array(33), 'dokumen' => array(27));
    ppb_excel_purge_caches($cache_pids[$dataset_key] ?? array());

    return array(
        'added'    => $added,
        'updated'  => $updated,
        'removed'  => $removed,
        'skipped'  => $skipped,
        'total'    => count($touched_ids),
        'warnings' => $warnings,
    );
}
}

if (!function_exists('ppb_excel_import_dosen')) {
function ppb_excel_import_dosen($mapped, $mode) {
    if (!current_user_can('manage_options')) {
        return new WP_Error('forbidden', 'Hanya Administrator (manage_options) yang diizinkan mengimpor data Profil Dosen.');
    }
    if ($mode === 'replace') {
        return new WP_Error('replace_disabled', 'Mode Replace dikunci untuk Data Dosen demi menjaga riwayat publikasi, SINTA, dan mata kuliah.');
    }

    $rows = $mapped['rows'];
    $pmap = array_flip($mapped['present_cols']);

    $dosen_list = function_exists('ppb_get_dosen_directory_cached') ? ppb_get_dosen_directory_cached() : array();
    $by_slug = array(); $by_nip = array(); $by_core = array();
    foreach ($dosen_list as $i => $d) {
        $sl = sanitize_title($d['slug'] ?? '');
        if ($sl !== '') $by_slug[$sl] = $i;
        foreach (array('nip', 'nidn') as $ik) {
            $dig = preg_replace('/\D+/', '', (string) ($d[$ik] ?? ''));
            if (strlen($dig) >= 8) $by_nip[$dig] = $i;
        }
        foreach (array('nama_lengkap', 'nama') as $nk) {
            $core = ppb_excel_strip_dosen_titles($d[$nk] ?? '');
            if ($core !== '' && !isset($by_core[$core])) $by_core[$core] = $i;
        }
    }

    $added = 0; $updated = 0; $skipped = 0; $warnings = array();
    $scalar_fields = array(
        'nama_panggilan'     => 'sanitize_text_field',
        'nip'                => 'sanitize_text_field',
        'nidn'               => 'sanitize_text_field',
        'jabatan_fungsional' => 'sanitize_text_field',
        'jabatan_struktural' => 'sanitize_text_field',
        'unit'               => 'sanitize_text_field',
        'email'              => 'sanitize_email',
        'no_hp'              => 'sanitize_text_field',
        'ruang_kerja'        => 'sanitize_text_field',
        'sinta_id'           => 'sanitize_text_field',
        'sinta_url'          => 'ppb_excel_clean_doc_url',
        'scholar_url'        => 'ppb_excel_clean_doc_url',
        'scopus_url'         => 'ppb_excel_clean_doc_url',
        'orcid'              => 'sanitize_text_field',
        'bio'                => 'sanitize_textarea_field',
    );

    $foto_updated_slugs = array();

    foreach ($rows as $row) {
        $raw_slug = sanitize_title($row['slug'] ?? '');
        $nama_in  = sanitize_text_field($row['nama_lengkap'] ?? '');
        $nip_dig  = preg_replace('/\D+/', '', (string) ($row['nip'] ?? ($row['nidn'] ?? '')));
        $core_in  = ppb_excel_strip_dosen_titles($nama_in);

        if ($raw_slug === '' && $nama_in === '' && strlen($nip_dig) < 8) {
            $skipped++;
            continue;
        }

        $mi = null;
        if ($raw_slug !== '' && isset($by_slug[$raw_slug])) {
            $mi = $by_slug[$raw_slug];
        } elseif (strlen($nip_dig) >= 8 && isset($by_nip[$nip_dig])) {
            $mi = $by_nip[$nip_dig];
        } elseif ($core_in !== '' && isset($by_core[$core_in])) {
            $mi = $by_core[$core_in];
        }

        if ($mi !== null) {
            $d = $dosen_list[$mi];
            if ($nama_in !== '') {
                $d['nama_lengkap'] = $nama_in;
                $d['nama']         = $nama_in;
            }
            $updated++;
        } else {
            if ($nama_in === '') {
                $skipped++;
                $warnings[] = 'Baris dosen baru dilewati karena Nama Lengkap kosong.';
                continue;
            }
            $new_slug = $raw_slug !== '' ? $raw_slug : sanitize_title(ppb_excel_strip_dosen_titles($nama_in) ?: $nama_in);
            if ($new_slug === '') $new_slug = 'dosen-' . substr(md5($nama_in), 0, 6);
            $d = array(
                'id'                 => function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid('dosen_', true),
                'slug'               => $new_slug,
                'nama'               => $nama_in,
                'nama_lengkap'       => $nama_in,
                'nip'                => '',
                'nidn'               => '',
                'gelar_depan'        => '',
                'gelar_belakang'     => '',
                'jabatan_fungsional' => 'Dosen BK',
                'jabatan_struktural' => '',
                'unit'               => 'S1 - Bimbingan dan Konseling',
                'bio'                => 'Dosen Program Studi Bimbingan dan Konseling FIP UNM.',
                'foto_link'          => '',
                'email'              => '',
                'no_hp'              => '',
                'sinta_id'           => '',
                'sinta_url'          => '',
                'scholar_url'        => '',
                'scopus_url'         => '',
                'orcid'              => '',
                'kepakaran'          => array(),
                'bidang_keahlian'    => '',
                'pendidikan'         => array(),
                'pengalaman'         => array(),
                'mata_kuliah'        => array(),
                'publikasi'          => array(),
                'penelitian'         => array(),
                'pengabdian'         => array(),
                'buku'               => array(),
                'stat'               => array('publikasi' => 0, 'penelitian' => 0, 'pengabdian' => 0, 'buku' => 0),
            );
            $added++;
        }

        foreach ($scalar_fields as $fk => $fn) {
            if (ppb_excel_has_val($row, $pmap, $fk)) {
                $d[$fk] = call_user_func($fn, trim((string) $row[$fk]));
            }
        }

        if (ppb_excel_has_val($row, $pmap, 'kepakaran')) {
            $raw_kep = str_replace(array("\n", '|', ','), ';', (string) $row['kepakaran']);
            $kep_arr = array_values(array_filter(array_map(function($x) {
                return sanitize_text_field(trim($x));
            }, explode(';', $raw_kep))));
            $d['kepakaran']       = $kep_arr;
            $d['bidang_keahlian'] = implode(', ', $kep_arr);
        }

        if (ppb_excel_has_val($row, $pmap, 'foto_link')) {
            $raw_foto = trim((string) $row['foto_link']);
            $d['foto_link'] = function_exists('bk_sanitize_asset_or_url') ? bk_sanitize_asset_or_url($raw_foto) : ppb_excel_clean_doc_url($raw_foto);
            if (!empty($d['slug'])) {
                $foto_updated_slugs[$d['slug']] = $d['foto_link'];
            }
        }

        if ($mi !== null) {
            $dosen_list[$mi] = $d;
        } else {
            $dosen_list[] = $d;
            $ni = count($dosen_list) - 1;
            $by_slug[$d['slug']] = $ni;
        }
    }

    if ($added === 0 && $updated === 0) {
        return new WP_Error('no_valid_rows', 'Tidak ada baris profil dosen valid untuk diperbarui.');
    }

    if (function_exists('ppb_save_dosen_directory_synced')) {
        ppb_save_dosen_directory_synced($dosen_list);
    }

    // Sinkronkan foto_link ke CPT dosen/ppb_dosen jika ada
    if (!empty($foto_updated_slugs) && function_exists('get_page_by_path')) {
        foreach ($foto_updated_slugs as $sl => $furl) {
            foreach (array('dosen', 'ppb_dosen') as $cpt_type) {
                $cpt_post = get_page_by_path($sl, OBJECT, $cpt_type);
                if ($cpt_post) {
                    update_post_meta($cpt_post->ID, '_dosen_foto_link', $furl);
                    update_post_meta($cpt_post->ID, '_ppb_dosen_foto_link', $furl);
                    clean_post_cache($cpt_post->ID);
                }
            }
        }
    }
    ppb_excel_purge_caches(array(25));

    return array(
        'added'    => $added,
        'updated'  => $updated,
        'removed'  => 0,
        'skipped'  => $skipped,
        'total'    => count($dosen_list),
        'warnings' => $warnings,
    );
}
}

if (!function_exists('ppb_excel_import_dataset')) {
function ppb_excel_import_dataset($dataset_key, $parsed_input, $mode = 'upsert') {
    $schema = ppb_excel_get_dataset($dataset_key);
    if (!$schema) {
        return new WP_Error('invalid_dataset', 'Dataset tidak dikenali.');
    }
    if (!current_user_can($schema['capability'] ?? 'edit_posts')) {
        return new WP_Error('forbidden', 'Anda tidak memiliki hak akses untuk mengimpor dataset ' . $schema['label'] . '.');
    }

    $mode = ($mode === 'replace') ? 'replace' : 'upsert';
    if (empty($schema['allow_replace']) && $mode === 'replace') {
        return new WP_Error('replace_disabled', 'Mode Replace dinonaktifkan untuk ' . $schema['label'] . ' agar relasi dan riwayat tidak terhapus.');
    }

    $sheets = $parsed_input['sheets'] ?? array();
    if (empty($sheets)) {
        return new WP_Error('empty_sheets', 'Berkas tidak berisi lembar kerja / data.');
    }

    if ($dataset_key === 'pnbp') {
        $res = ppb_excel_import_pnbp($sheets, $schema, $mode);
    } else {
        $mapped = ppb_excel_select_main_sheet($sheets, $schema['columns']);
        if (empty($mapped['rows'])) {
            return new WP_Error(
                'no_matching_headers',
                'Kolom header wajib pada dataset "' . $schema['label'] . '" tidak cocok atau seluruh baris kosong. Pastikan Anda memakai template resmi atau cek nama kolom.'
            );
        }

        if ($dataset_key === 'matkul') {
            $res = ppb_excel_import_matkul($mapped, $mode);
        } elseif ($dataset_key === 'dosen') {
            $res = ppb_excel_import_dosen($mapped, $mode);
        } elseif (($schema['storage_type'] ?? '') === 'cpt') {
            $res = ppb_excel_import_cpt($dataset_key, $schema, $mapped, $mode);
        } else {
            $res = ppb_excel_import_option_list($dataset_key, $mapped, $mode);
        }
    }

    if (is_wp_error($res)) return $res;

    $res['dataset'] = $dataset_key;
    $res['label']   = $schema['label'];
    $res['mode']    = $mode;
    $res['format']  = $parsed_input['format'] ?? '';
    return $res;
}
}
