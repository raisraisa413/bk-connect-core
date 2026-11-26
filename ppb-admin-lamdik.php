<?php
/**
 * Module: PPB FIP UNM - Pengaturan Dokumen Akreditasi, 9 Kriteria Eviden LAMDIK (S1, S2, S3) & Sertifikat
 * Description: Panel terpusat di WP Admin untuk mengelola tautan & eviden Akreditasi LAMDIK secara terpisah
 *              per Jenjang/Prodi (S1, S2, S3, serta Prodi baru di Registry Jurusan PPB) dan tata kelola
 *              level Jurusan (Renstra, Renop, UPM), dengan sinkronisasi live ke halaman publik.
 * Author: Jurusan PPB FIP UNM
 */

if (!defined('ABSPATH')) exit;

/* ========================================================================== */
/* 1. DEFAULTS PER JENJANG, MIGRASI V1 -> V2 & SANITIZED SAVE HELPERS         */
/* ========================================================================== */

if (!function_exists('ppb_get_lamdik_kriteria_defaults_for_unit')) {
function ppb_get_lamdik_kriteria_defaults_for_unit($slug, $prodi_meta = array()) {
    $s = sanitize_key((string) $slug);
    $nama_prodi = !empty($prodi_meta['nama']) ? $prodi_meta['nama'] : strtoupper($s);
    $q_suffix = '?jenjang=' . rawurlencode($s);

    if ($s === 's1') {
        $items = array(
            0 => array(
                'kode' => 'K1', 'judul' => 'Visi, Misi, Tujuan, dan Strategi',
                'deskripsi' => 'Sertifikat Akreditasi Unggul S1 BK; Visi Keilmuan Prodi S1 mengacu pada UNM, FIP, dan Jurusan PPB; Bagan Kepakaran & Kolaborasi Riset; Survei Pemahaman Visi S1.',
                'tag_ringkas' => '✓ Dokumen SK, Bagan Kepakaran & Sosialisasi Visi S1',
                'internal_links' => array(
                    array('label' => 'Sertifikat S1', 'url' => '/akreditasi/sertifikat/'),
                    array('label' => 'Visi Keilmuan', 'url' => '/profil/visi-keilmuan/'),
                    array('label' => 'Visi & Misi', 'url' => '/profil/visi-misi/'),
                ),
            ),
            1 => array(
                'kode' => 'K2', 'judul' => 'Tata Pamong, Tata Kelola, dan Kerjasama',
                'deskripsi' => 'Struktur Organisasi Jurusan PPB & Prodi S1 BK; Tata Kelola Akademik; Kerjasama Tridharma dengan 35+ sekolah mitra, MGBK, ABKIN, UPSI, dan PMI.',
                'tag_ringkas' => '✓ MoA, IA & Laporan Realisasi Kerjasama S1',
                'internal_links' => array(
                    array('label' => 'Struktur Organisasi', 'url' => '/profil/struktur/'),
                    array('label' => 'Kerjasama Tridharma', 'url' => '/profil/kerjasama-tridharma/'),
                    array('label' => 'Dokumen Resmi', 'url' => '/profil/dokumen/'),
                ),
            ),
            2 => array(
                'kode' => 'K3', 'judul' => 'Kemahasiswaan & Lulusan',
                'deskripsi' => 'Statistik Penerimaan Mahasiswa Baru S1 (SNBP, SNBT, Mandiri), rasio keketatan, sebaran 3T, prestasi nasional/ASEAN, inovasi MBKM, dan Tracer Study lulusan S1.',
                'tag_ringkas' => '✓ Data Maba S1, Prestasi & Tracer Study Alumni',
                'internal_links' => array(
                    array('label' => 'Statistik Mahasiswa S1', 'url' => '/kemahasiswaan/statistik/' . $q_suffix),
                    array('label' => 'Penerimaan Maba S1', 'url' => '/kemahasiswaan/penerimaan/' . $q_suffix),
                    array('label' => 'Prestasi & Karya', 'url' => '/kemahasiswaan/prestasi/'),
                ),
            ),
            3 => array(
                'kode' => 'K4', 'judul' => 'Sumber Daya Manusia (Dosen)',
                'deskripsi' => 'Profil 25 Dosen Jurusan PPB pengampu S1 BK (4 Guru Besar, Doktor & Lektor Kepala), rasio DTPS S1, profil SINTA, HKI, buku ajar, dan tenaga kependidikan.',
                'tag_ringkas' => '✓ Direktori DTPS S1, SINTA & Buku Ajar',
                'internal_links' => array(
                    array('label' => 'Direktori Dosen', 'url' => '/profil/dosen/' . $q_suffix),
                    array('label' => 'Kepakaran & SINTA', 'url' => '/profil/kepakaran-dosen/'),
                    array('label' => 'Koleksi Buku Dosen', 'url' => '/profil/buku-dosen/'),
                ),
            ),
            4 => array(
                'kode' => 'K5', 'judul' => 'Keuangan, Sarana, dan Prasarana',
                'deskripsi' => 'Smart Classroom S1, 4 bilik Lab Mikrokonseling One-Way Mirror Kampus Gunungsari, inventaris instrumen tes psikologi (IST, CFIT, RMIB, AUM), dan alokasi dana layanan.',
                'tag_ringkas' => '✓ Sarpras Pembelajaran & Inventaris Lab BK',
                'internal_links' => array(
                    array('label' => 'Sarana & Prasarana', 'url' => '/profil/sarana-prasarana/'),
                    array('label' => 'Inventaris Lab BK', 'url' => '/lab-bk/inventaris/'),
                    array('label' => 'E-Library Modul', 'url' => '/e-library/'),
                ),
            ),
            5 => array(
                'kode' => 'K6', 'judul' => 'Pendidikan & Kurikulum OBE',
                'deskripsi' => 'Kurikulum OBE 2025 S1 (144–146 SKS, KKNI Level 6), RPS 16 pertemuan, SAP, PLP/PPL sekolah mitra, rubrik asesmen konseling, serta jadwal Sempro, Semhas & Ujian Skripsi.',
                'tag_ringkas' => '✓ Kurikulum S1 144 SKS, RPS/SAP & Jadwal Skripsi',
                'internal_links' => array(
                    array('label' => 'Kurikulum OBE S1', 'url' => '/akademik/kurikulum/' . $q_suffix),
                    array('label' => 'Jadwal Ujian Skripsi', 'url' => '/akademik/jadwal-ujian/' . $q_suffix),
                    array('label' => 'Pedoman Penilaian', 'url' => '/akademik/panduan/' . $q_suffix),
                ),
            ),
            6 => array(
                'kode' => 'K7', 'judul' => 'Penelitian Dosen & Mahasiswa',
                'deskripsi' => 'Hibah Penelitian PNBP & DRTPM yang melibatkan mahasiswa S1 dalam payung skripsi, roadmap riset Jurusan PPB, serta publikasi pada Jurnal Bimbingan dan Konseling (JBK).',
                'tag_ringkas' => '✓ Portal MIS PNBP, Roadmap & Jurnal BK',
                'internal_links' => array(
                    array('label' => 'MIS PNBP Riset', 'url' => '/mis-pnbp/'),
                    array('label' => 'Jurnal BK (JBK)', 'url' => '/jurnal-bk/'),
                    array('label' => 'Roadmap Riset', 'url' => '/profil/roadmap/'),
                ),
            ),
            7 => array(
                'kode' => 'K8', 'judul' => 'Pengabdian kepada Masyarakat (PkM)',
                'deskripsi' => 'Program Kemitraan Masyarakat (PKM-PNBP), pelatihan konselor sekolah, desa binaan, MBKM Kemanusiaan mahasiswa S1, dan publikasi Jurnal Pengabdian Konseling Nusantara (JPKN).',
                'tag_ringkas' => '✓ Luaran PkM, MBKM & Mitra Sekolah',
                'internal_links' => array(
                    array('label' => 'MIS PNBP PkM', 'url' => '/mis-pnbp/#pkm'),
                    array('label' => 'MBKM Kemanusiaan', 'url' => '/kemahasiswaan/mbkm-kemanusiaan/'),
                    array('label' => 'Mitra PkM', 'url' => '/profil/kerjasama-tridharma/'),
                ),
            ),
            8 => array(
                'kode' => 'K9', 'judul' => 'Luaran, Capaian & Penjaminan Mutu',
                'deskripsi' => 'Capaian IPK lulusan S1, masa tunggu kerja, Audit Mutu Internal (AMI), siklus PPEPP, Survei Kepuasan Mahasiswa S1 (GPM), serta dokumen Renstra & Renop Jurusan PPB.',
                'tag_ringkas' => '✓ Luaran S1, AMI & Siklus PPEPP UPM',
                'internal_links' => array(
                    array('label' => 'Renstra & UPM', 'url' => '/akreditasi/upm/'),
                    array('label' => 'Survei GPM S1', 'url' => '/survei/'),
                    array('label' => 'Portal UPM UNM', 'url' => 'https://bpm.unm.ac.id/'),
                ),
            ),
        );
    } elseif ($s === 's2') {
        $items = array(
            0 => array(
                'kode' => 'K1', 'judul' => 'Visi, Misi, Tujuan, dan Strategi',
                'deskripsi' => 'Sertifikat Akreditasi Unggul S2 BK; Visi Keilmuan Magister Bimbingan & Konseling (rekayasa model intervensi psikososial, supervisi klinis, konseling lintas budaya); sosialisasi VMTS Pascasarjana.',
                'tag_ringkas' => '✓ SK Akreditasi S2 & Visi Keilmuan Magister',
                'internal_links' => array(
                    array('label' => 'Sertifikat S2', 'url' => '/akreditasi/sertifikat/'),
                    array('label' => 'Visi & Misi S2', 'url' => '/profil/visi-misi/'),
                    array('label' => 'Visi Keilmuan', 'url' => '/profil/visi-keilmuan/'),
                ),
            ),
            1 => array(
                'kode' => 'K2', 'judul' => 'Tata Pamong, Tata Kelola, dan Kerjasama',
                'deskripsi' => 'Tata kelola Program Studi S2 BK di bawah koordinasi Jurusan PPB FIP UNM dan Pascasarjana UNM; perjanjian kerjasama riset lanjut, magang supervisi, dan kolaborasi internasional.',
                'tag_ringkas' => '✓ Tata Pamong Pascasarjana & MoA Riset S2',
                'internal_links' => array(
                    array('label' => 'Struktur S2 & Jurusan', 'url' => '/profil/struktur/'),
                    array('label' => 'Kerjasama Tridharma', 'url' => '/profil/kerjasama-tridharma/'),
                    array('label' => 'Dokumen Mutu S2', 'url' => '/profil/dokumen/'),
                ),
            ),
            2 => array(
                'kode' => 'K3', 'judul' => 'Kemahasiswaan & Lulusan',
                'deskripsi' => 'Rekapitulasi seleksi penerimaan mahasiswa baru S2 Pascasarjana (Reguler/Kerjasama), profil mahasiswa praktisi & akademisi, publikasi mahasiswa magister, serta Tracer Study alumni S2.',
                'tag_ringkas' => '✓ Admisi Pascasarjana S2 & Tracer Lulusan',
                'internal_links' => array(
                    array('label' => 'Statistik Mahasiswa S2', 'url' => '/kemahasiswaan/statistik/' . $q_suffix),
                    array('label' => 'Penerimaan S2', 'url' => '/kemahasiswaan/penerimaan/' . $q_suffix),
                    array('label' => 'Prestasi & Karya', 'url' => '/kemahasiswaan/prestasi/'),
                ),
            ),
            3 => array(
                'kode' => 'K4', 'judul' => 'Sumber Daya Manusia (Dosen)',
                'deskripsi' => 'DTPS berkualifikasi Doktor & Guru Besar pengampu mata kuliah dan pembimbing Tesis S2 BK, sertifikasi konselor ahli, rekam jejak SINTA, dan keterlibatan reviewer jurnal.',
                'tag_ringkas' => '✓ DTPS Doktor/Guru Besar Pembimbing Tesis S2',
                'internal_links' => array(
                    array('label' => 'Dosen Pengampu S2', 'url' => '/profil/dosen/' . $q_suffix),
                    array('label' => 'Kepakaran & SINTA', 'url' => '/profil/kepakaran-dosen/'),
                    array('label' => 'Buku Ajar Dosen', 'url' => '/profil/buku-dosen/'),
                ),
            ),
            4 => array(
                'kode' => 'K5', 'judul' => 'Keuangan, Sarana, dan Prasarana',
                'deskripsi' => 'Ruang seminar pascasarjana, laboratorium supervisi konseling & asesmen lanjutan (Rasch, Jamovi, psikometri terapan), akses jurnal internasional, dan dukungan pendanaan riset tesis.',
                'tag_ringkas' => '✓ Sarpras Pascasarjana & Lab Analisis Data',
                'internal_links' => array(
                    array('label' => 'Sarana & Prasarana', 'url' => '/profil/sarana-prasarana/'),
                    array('label' => 'Inventaris Lab BK', 'url' => '/lab-bk/inventaris/'),
                    array('label' => 'E-Library Lanjut', 'url' => '/e-library/'),
                ),
            ),
            5 => array(
                'kode' => 'K6', 'judul' => 'Pendidikan & Kurikulum OBE',
                'deskripsi' => 'Kurikulum Magister S2 (38–42 SKS, KKNI Level 8), mata kuliah teori konseling mutakhir & R&D BK, RPS/SAP S2, serta jadwal Sempro Tesis, Semhas Tesis, dan Ujian Tesis.',
                'tag_ringkas' => '✓ Kurikulum S2 KKNI Lv. 8 & Jadwal Ujian Tesis',
                'internal_links' => array(
                    array('label' => 'Kurikulum S2', 'url' => '/akademik/kurikulum/' . $q_suffix),
                    array('label' => 'Jadwal Ujian Tesis', 'url' => '/akademik/jadwal-ujian/' . $q_suffix),
                    array('label' => 'Pedoman Tesis', 'url' => '/akademik/panduan/' . $q_suffix),
                ),
            ),
            6 => array(
                'kode' => 'K7', 'judul' => 'Penelitian Dosen & Mahasiswa',
                'deskripsi' => 'Payung riset bersama dosen dan mahasiswa S2 (pengembangan modul intervensi, validasi instrumen psikometrik), serta luaran artikel wajib SINTA 2/Scopus mahasiswa S2.',
                'tag_ringkas' => '✓ Riset Tesis, MIS PNBP & Publikasi SINTA 2',
                'internal_links' => array(
                    array('label' => 'MIS PNBP Riset', 'url' => '/mis-pnbp/'),
                    array('label' => 'Jurnal BK (JBK)', 'url' => '/jurnal-bk/'),
                    array('label' => 'Roadmap Riset', 'url' => '/profil/roadmap/'),
                ),
            ),
            7 => array(
                'kode' => 'K8', 'judul' => 'Pengabdian kepada Masyarakat (PkM)',
                'deskripsi' => 'Hilirisasi hasil penelitian tesis S2 dalam bentuk workshop supervisi guru BK, advokasi psikososial komunitas, dan diseminasi model konseling terapan di wilayah Sulawesi Selatan.',
                'tag_ringkas' => '✓ Hilirisasi Riset S2 & Kemitraan MGBK',
                'internal_links' => array(
                    array('label' => 'MIS PNBP PkM', 'url' => '/mis-pnbp/#pkm'),
                    array('label' => 'Jurnal PkM', 'url' => '/jurnal-bk/'),
                    array('label' => 'Mitra PkM', 'url' => '/profil/kerjasama-tridharma/'),
                ),
            ),
            8 => array(
                'kode' => 'K9', 'judul' => 'Luaran, Capaian & Penjaminan Mutu',
                'deskripsi' => 'Ketepatan masa studi 4 semester, jumlah publikasi SINTA/Scopus lulusan S2, HKI modul/instrumen, AMI Pascasarjana, Survei Kepuasan Mahasiswa S2, dan siklus PPEPP.',
                'tag_ringkas' => '✓ Luaran Tesis, Publikasi & AMI S2',
                'internal_links' => array(
                    array('label' => 'Renstra & UPM', 'url' => '/akreditasi/upm/'),
                    array('label' => 'Survei GPM S2', 'url' => '/survei/'),
                    array('label' => 'Portal UPM UNM', 'url' => 'https://bpm.unm.ac.id/'),
                ),
            ),
        );
    } elseif ($s === 's3') {
        $items = array(
            0 => array(
                'kode' => 'K1', 'judul' => 'Visi, Misi, Tujuan, dan Strategi',
                'deskripsi' => 'Sertifikat Akreditasi S3 Ilmu Pendidikan (Konsentrasi BK); Visi Keilmuan Doktoral berbasis kebaruan teori (novelty) konseling indigenos & kearifan budaya Nusantara; strategi capaian reputasi internasional.',
                'tag_ringkas' => '✓ SK Akreditasi S3 & Visi Keilmuan Doktoral',
                'internal_links' => array(
                    array('label' => 'Sertifikat S3', 'url' => '/akreditasi/sertifikat/'),
                    array('label' => 'Visi & Misi S3', 'url' => '/profil/visi-misi/'),
                    array('label' => 'Visi Keilmuan', 'url' => '/profil/visi-keilmuan/'),
                ),
            ),
            1 => array(
                'kode' => 'K2', 'judul' => 'Tata Pamong, Tata Kelola, dan Kerjasama',
                'deskripsi' => 'Tata kelola Konsentrasi BK pada S3 Ilmu Pendidikan di Pascasarjana UNM bersama Jurusan PPB; jejaring penguji eksternal, visiting professor, dan kolaborasi riset internasional.',
                'tag_ringkas' => '✓ Tata Pamong Doktoral & Jejaring Internasional',
                'internal_links' => array(
                    array('label' => 'Struktur S3 & Jurusan', 'url' => '/profil/struktur/'),
                    array('label' => 'Kerjasama Internasional', 'url' => '/profil/kerjasama-tridharma/'),
                    array('label' => 'Dokumen Regulasi S3', 'url' => '/profil/dokumen/'),
                ),
            ),
            2 => array(
                'kode' => 'K3', 'judul' => 'Kemahasiswaan & Lulusan',
                'deskripsi' => 'Seleksi masuk mahasiswa doktoral (kualifikasi akademik & proposal riset), sebaran mahasiswa dosen/peneliti nasional, capaian seminar internasional, dan dampak lulusan Doktor.',
                'tag_ringkas' => '✓ Admisi Doktoral S3 & Profil Lulusan',
                'internal_links' => array(
                    array('label' => 'Statistik Mahasiswa S3', 'url' => '/kemahasiswaan/statistik/' . $q_suffix),
                    array('label' => 'Penerimaan S3', 'url' => '/kemahasiswaan/penerimaan/' . $q_suffix),
                    array('label' => 'Direktori Alumni', 'url' => '/kemahasiswaan/alumni/'),
                ),
            ),
            3 => array(
                'kode' => 'K4', 'judul' => 'Sumber Daya Manusia (Dosen)',
                'deskripsi' => 'Promotor & Ko-Promotor berkualifikasi Guru Besar (4 Profesor BK) dan Doktor senior ber-h-index Scopus, pengampu filsafat ilmu & epistemologi konseling lanjut.',
                'tag_ringkas' => '✓ Guru Besar Promotor & Kepakaran Scopus',
                'internal_links' => array(
                    array('label' => 'Promotor & Guru Besar', 'url' => '/profil/dosen/' . $q_suffix),
                    array('label' => 'Kepakaran & SINTA', 'url' => '/profil/kepakaran-dosen/'),
                    array('label' => 'Monograf & Buku', 'url' => '/profil/buku-dosen/'),
                ),
            ),
            4 => array(
                'kode' => 'K5', 'judul' => 'Keuangan, Sarana, dan Prasarana',
                'deskripsi' => 'Ruang ujian promosi doktor, pusat studi konseling indigenos & laboratorium riset lanjut, langganan database Scopus/WoS, dan dukungan hibah disertasi doktor.',
                'tag_ringkas' => '✓ Fasilitas Riset Disertasi & Ruang Promosi',
                'internal_links' => array(
                    array('label' => 'Sarana & Prasarana', 'url' => '/profil/sarana-prasarana/'),
                    array('label' => 'Lab Riset & Tes', 'url' => '/lab-bk/inventaris/'),
                    array('label' => 'E-Library Riset', 'url' => '/e-library/'),
                ),
            ),
            5 => array(
                'kode' => 'K6', 'judul' => 'Pendidikan & Kurikulum OBE',
                'deskripsi' => 'Kurikulum Doktoral S3 (42–48 SKS, KKNI Level 9), mata kuliah filsafat ilmu & konstruksi model konseling, tahapan Ujian Kualifikasi, Sempro Disertasi, Semhas, Ujian Tertutup, dan Promosi Terbuka.',
                'tag_ringkas' => '✓ Kurikulum S3 KKNI Lv. 9 & Jadwal Disertasi',
                'internal_links' => array(
                    array('label' => 'Kurikulum S3', 'url' => '/akademik/kurikulum/' . $q_suffix),
                    array('label' => 'Jadwal Disertasi', 'url' => '/akademik/jadwal-ujian/' . $q_suffix),
                    array('label' => 'Pedoman Disertasi', 'url' => '/akademik/panduan/' . $q_suffix),
                ),
            ),
            6 => array(
                'kode' => 'K7', 'judul' => 'Penelitian Dosen & Mahasiswa',
                'deskripsi' => 'Penelitian disertasi berbasis kebaruan teori (novelty) dan model intervensi lintas budaya, hibah penelitian doktor, serta publikasi wajib pada jurnal internasional bereputasi (Scopus/WoS).',
                'tag_ringkas' => '✓ Riset Novelty Disertasi & Publikasi Scopus',
                'internal_links' => array(
                    array('label' => 'MIS PNBP Riset', 'url' => '/mis-pnbp/'),
                    array('label' => 'Jurnal Ilmiah', 'url' => '/jurnal-bk/'),
                    array('label' => 'Roadmap Riset', 'url' => '/profil/roadmap/'),
                ),
            ),
            7 => array(
                'kode' => 'K8', 'judul' => 'Pengabdian kepada Masyarakat (PkM)',
                'deskripsi' => 'Pengabdian berbasis riset kebijakan nasional pendidikan dan konseling, diseminasi model intervensi doktoral kepada pemerintah daerah, asosiasi profesi, dan perguruan tinggi mitra.',
                'tag_ringkas' => '✓ Diseminasi Model & Kebijakan Konseling',
                'internal_links' => array(
                    array('label' => 'MIS PNBP PkM', 'url' => '/mis-pnbp/#pkm'),
                    array('label' => 'Jurnal PkM', 'url' => '/jurnal-bk/'),
                    array('label' => 'Mitra Kebijakan', 'url' => '/profil/kerjasama-tridharma/'),
                ),
            ),
            8 => array(
                'kode' => 'K9', 'judul' => 'Luaran, Capaian & Penjaminan Mutu',
                'deskripsi' => 'Produksi teori/model baru konseling ber-HKI/paten, artikel Q1–Q3 Scopus mahasiswa S3, tingkat kelulusan doktor tepat waktu, AMI Pascasarjana, serta evaluasi mutu GPM.',
                'tag_ringkas' => '✓ Novelty Teori, Scopus & AMI S3',
                'internal_links' => array(
                    array('label' => 'Renstra & UPM', 'url' => '/akreditasi/upm/'),
                    array('label' => 'Survei GPM S3', 'url' => '/survei/'),
                    array('label' => 'Portal UPM UNM', 'url' => 'https://bpm.unm.ac.id/'),
                ),
            ),
        );
    } else {
        $items = array(
            0 => array('kode' => 'K1', 'judul' => 'Visi, Misi, Tujuan, dan Strategi', 'deskripsi' => 'Visi keilmuan, misi, tujuan, dan strategi pencapaian ' . $nama_prodi . ' mengacu pada payung Jurusan PPB FIP UNM.', 'tag_ringkas' => '✓ VMTS & Sosialisasi ' . strtoupper($s), 'internal_links' => array(array('label' => 'Visi & Misi', 'url' => '/profil/visi-misi/'))),
            1 => array('kode' => 'K2', 'judul' => 'Tata Pamong, Tata Kelola, dan Kerjasama', 'deskripsi' => 'Struktur tata pamong, sistem penjaminan mutu, serta kerjasama tridharma pada ' . $nama_prodi . '.', 'tag_ringkas' => '✓ Tata Kelola & MoA', 'internal_links' => array(array('label' => 'Struktur Organisasi', 'url' => '/profil/struktur/'))),
            2 => array('kode' => 'K3', 'judul' => 'Kemahasiswaan & Lulusan', 'deskripsi' => 'Sistem seleksi penerimaan, layanan kemahasiswaan, prestasi, dan pelacakan lulusan ' . $nama_prodi . '.', 'tag_ringkas' => '✓ Data Mahasiswa & Lulusan', 'internal_links' => array(array('label' => 'Statistik Mahasiswa', 'url' => '/kemahasiswaan/statistik/' . $q_suffix))),
            3 => array('kode' => 'K4', 'judul' => 'Sumber Daya Manusia (Dosen)', 'deskripsi' => 'Dosen Tetap Program Studi (DTPS) pengampu ' . $nama_prodi . ', kualifikasi, kepakaran, dan SINTA.', 'tag_ringkas' => '✓ DTPS & Kepakaran', 'internal_links' => array(array('label' => 'Direktori Dosen', 'url' => '/profil/dosen/' . $q_suffix))),
            4 => array('kode' => 'K5', 'judul' => 'Keuangan, Sarana, dan Prasarana', 'deskripsi' => 'Kecukupan dana, ruang pembelajaran, laboratorium BK, dan sumber belajar untuk ' . $nama_prodi . '.', 'tag_ringkas' => '✓ Sarpras & Lab BK', 'internal_links' => array(array('label' => 'Sarana & Prasarana', 'url' => '/profil/sarana-prasarana/'))),
            5 => array('kode' => 'K6', 'judul' => 'Pendidikan & Kurikulum OBE', 'deskripsi' => 'Kurikulum OBE, RPS/SAP, metode pembelajaran, asesmen, dan tugas akhir pada ' . $nama_prodi . '.', 'tag_ringkas' => '✓ Kurikulum & RPS', 'internal_links' => array(array('label' => 'Kurikulum OBE', 'url' => '/akademik/kurikulum/' . $q_suffix))),
            6 => array('kode' => 'K7', 'judul' => 'Penelitian Dosen & Mahasiswa', 'deskripsi' => 'Pelaksanaan penelitian dosen bersama mahasiswa ' . $nama_prodi . ' sesuai roadmap Jurusan PPB.', 'tag_ringkas' => '✓ Riset & Publikasi', 'internal_links' => array(array('label' => 'MIS PNBP Riset', 'url' => '/mis-pnbp/'))),
            7 => array('kode' => 'K8', 'judul' => 'Pengabdian kepada Masyarakat (PkM)', 'deskripsi' => 'Program pengabdian kepada masyarakat oleh sivitas ' . $nama_prodi . ' bersama mitra.', 'tag_ringkas' => '✓ Luaran PkM', 'internal_links' => array(array('label' => 'MIS PNBP PkM', 'url' => '/mis-pnbp/#pkm'))),
            8 => array('kode' => 'K9', 'judul' => 'Luaran, Capaian & Penjaminan Mutu', 'deskripsi' => 'Capaian pembelajaran, luaran publikasi/HKI, Audit Mutu Internal (AMI), dan siklus PPEPP.', 'tag_ringkas' => '✓ Luaran & AMI', 'internal_links' => array(array('label' => 'Renstra & UPM', 'url' => '/akreditasi/upm/'))),
        );
    }

    foreach ($items as $idx => &$it) {
        $knum = $idx + 1;
        $it['label_tombol'] = 'Eviden Drive K' . $knum . ' (' . strtoupper($s) . ')';
        $it['drive_url']    = '';
        $it['eviden_links'] = array(
            array('label' => $it['label_tombol'], 'url' => ''),
        );
    }
    unset($it);

    return $items;
}
}

if (!function_exists('ppb_get_lamdik_prodi_defaults')) {
function ppb_get_lamdik_prodi_defaults($slug, $prodi_meta = array()) {
    $s = sanitize_key((string) $slug);
    $cert_defs = array(
        's1' => array(
            'peringkat'       => 'UNGGUL (A)',
            'lembaga'         => 'LAMDIK',
            'no_sk'           => '492/SK/LAMDIK/Ak/S/V/2023',
            'masa_berlaku'    => '2023 s.d. 2028',
            'kurikulum_label' => 'OBE 2025 · 144 SKS (KKNI Lv. 6)',
            'cert_url'        => '',
            'sk_url'          => '',
        ),
        's2' => array(
            'peringkat'       => 'UNGGUL (A)',
            'lembaga'         => 'LAMDIK / BAN-PT',
            'no_sk'           => '315/SK/LAMDIK/Ak/M/2022',
            'masa_berlaku'    => '2022 s.d. 2027',
            'kurikulum_label' => 'Riset Terapan · 38–42 SKS (KKNI Lv. 8)',
            'cert_url'        => '',
            'sk_url'          => '',
        ),
        's3' => array(
            'peringkat'       => 'BAIK SEKALI',
            'lembaga'         => 'LAMDIK / BAN-PT',
            'no_sk'           => '588/SK/LAMDIK/Ak/D/2023',
            'masa_berlaku'    => '2023 s.d. 2028',
            'kurikulum_label' => 'Disertasi & Novelty · 42–48 SKS (KKNI Lv. 9)',
            'cert_url'        => '',
            'sk_url'          => '',
        ),
    );

    $cd = $cert_defs[$s] ?? array(
        'peringkat'       => 'PERSIAPAN AKREDITASI',
        'lembaga'         => 'LAMDIK',
        'no_sk'           => '-',
        'masa_berlaku'    => '-',
        'kurikulum_label' => ($prodi_meta['kkni'] ?? 'Standar Nasional'),
        'cert_url'        => '',
        'sk_url'          => '',
    );

    return array(
        'general' => array(
            'folder_utama_url' => '',
            'led_url'          => '',
            'lkps_url'         => '',
            'matriks_url'      => '',
            'kurikulum_label'  => $cd['kurikulum_label'],
        ),
        'kriteria' => ppb_get_lamdik_kriteria_defaults_for_unit($s, $prodi_meta),
        'sertifikat' => array(
            'peringkat'    => $cd['peringkat'],
            'lembaga'      => $cd['lembaga'],
            'no_sk'        => $cd['no_sk'],
            'masa_berlaku' => $cd['masa_berlaku'],
            'cert_url'     => $cd['cert_url'],
            'sk_url'       => $cd['sk_url'],
        ),
    );
}
}

if (!function_exists('ppb_get_lamdik_defaults')) {
function ppb_get_lamdik_defaults() {
    $prodi_units = function_exists('ppb_get_prodi_units') ? ppb_get_prodi_units(false) : array(
        's1' => array('slug' => 's1', 'kode' => 'S1', 'nama' => 'S1 Bimbingan dan Konseling'),
        's2' => array('slug' => 's2', 'kode' => 'S2', 'nama' => 'S2 Bimbingan dan Konseling'),
        's3' => array('slug' => 's3', 'kode' => 'S3', 'nama' => 'S3 Ilmu Pendidikan (Konsentrasi BK)'),
    );

    $prodi_out = array();
    foreach ($prodi_units as $slug => $meta) {
        $prodi_out[$slug] = ppb_get_lamdik_prodi_defaults($slug, $meta);
    }

    return array(
        'schema_version' => 2,
        'jurusan' => array(
            'renstra_url'    => '',
            'renop_url'      => '',
            'upm_portal_url' => 'https://bpm.unm.ac.id/',
        ),
        'prodi' => $prodi_out,
    );
}
}

if (!function_exists('ppb_lamdik_normalize_eviden_links')) {
function ppb_lamdik_normalize_eviden_links($kr, $def_kr) {
    $clean_url = function($val) {
        return function_exists('ppb_clean_document_url') ? ppb_clean_document_url($val) : esc_url_raw((string) $val);
    };

    $links = array();
    if (!empty($kr['eviden_links']) && is_array($kr['eviden_links'])) {
        foreach ($kr['eviden_links'] as $ev) {
            if (!is_array($ev)) continue;
            $u = $clean_url($ev['url'] ?? '');
            $l = sanitize_text_field($ev['label'] ?? '');
            if ($u !== '' || $l !== '') {
                $links[] = array(
                    'label' => $l !== '' ? $l : ($def_kr['label_tombol'] ?? 'Eviden Drive'),
                    'url'   => $u,
                );
            }
        }
    }

    $legacy_url = $clean_url($kr['drive_url'] ?? '');
    $legacy_lbl = sanitize_text_field($kr['label_tombol'] ?? ($def_kr['label_tombol'] ?? 'Eviden Drive'));
    if (empty($links)) {
        $links[] = array(
            'label' => $legacy_lbl,
            'url'   => $legacy_url,
        );
    } elseif ($legacy_url !== '') {
        $found = false;
        foreach ($links as $ev) {
            if ($ev['url'] === $legacy_url) { $found = true; break; }
        }
        if (!$found && empty($links[0]['url'])) {
            $links[0]['url'] = $legacy_url;
        }
    }

    $primary_url = '';
    $primary_lbl = $legacy_lbl;
    foreach ($links as $ev) {
        if (!empty($ev['url'])) {
            $primary_url = $ev['url'];
            $primary_lbl = $ev['label'];
            break;
        }
    }

    return array(
        'eviden_links' => $links,
        'drive_url'    => $primary_url,
        'label_tombol' => $primary_lbl,
    );
}
}

if (!function_exists('ppb_get_lamdik_settings')) {
function ppb_get_lamdik_settings() {
    $defaults = ppb_get_lamdik_defaults();
    $saved    = get_option('ppb_lamdik_settings', array());
    if (!is_array($saved) || empty($saved)) {
        return $defaults;
    }

    // Auto-migrasi v1 (flat general/kriteria/sertifikat) -> v2 (jurusan + prodi[s1, s2, s3])
    if (empty($saved['schema_version']) || (empty($saved['prodi']) && (isset($saved['kriteria']) || isset($saved['general']) || isset($saved['sertifikat'])))) {
        $migrated = $defaults;
        $v1_gen   = isset($saved['general']) && is_array($saved['general']) ? $saved['general'] : array();
        $v1_krit  = isset($saved['kriteria']) && is_array($saved['kriteria']) ? $saved['kriteria'] : array();
        $v1_sert  = isset($saved['sertifikat']) && is_array($saved['sertifikat']) ? $saved['sertifikat'] : array();

        foreach (array('renstra_url', 'renop_url', 'upm_portal_url') as $jk) {
            if (!empty($v1_gen[$jk])) {
                $migrated['jurusan'][$jk] = (string) $v1_gen[$jk];
            }
        }
        foreach (array('folder_utama_url', 'led_url', 'lkps_url', 'matriks_url') as $pk) {
            if (!empty($v1_gen[$pk])) {
                $migrated['prodi']['s1']['general'][$pk] = (string) $v1_gen[$pk];
            }
        }
        foreach ($migrated['prodi']['s1']['kriteria'] as $idx => $def_k) {
            if (isset($v1_krit[$idx]) && is_array($v1_krit[$idx])) {
                $norm_ev = ppb_lamdik_normalize_eviden_links($v1_krit[$idx], $def_k);
                $migrated['prodi']['s1']['kriteria'][$idx]['label_tombol'] = $norm_ev['label_tombol'];
                $migrated['prodi']['s1']['kriteria'][$idx]['drive_url']    = $norm_ev['drive_url'];
                $migrated['prodi']['s1']['kriteria'][$idx]['eviden_links'] = $norm_ev['eviden_links'];
            }
        }
        foreach (array('s1', 's2', 's3') as $deg) {
            if (isset($v1_sert[$deg]) && is_array($v1_sert[$deg]) && isset($migrated['prodi'][$deg])) {
                $migrated['prodi'][$deg]['sertifikat'] = wp_parse_args($v1_sert[$deg], $migrated['prodi'][$deg]['sertifikat']);
            }
        }
        update_option('ppb_lamdik_settings_v1_backup', $saved, false);
        update_option('ppb_lamdik_settings', $migrated, false);
        return $migrated;
    }

    $merged = $defaults;
    if (!empty($saved['jurusan']) && is_array($saved['jurusan'])) {
        $merged['jurusan'] = wp_parse_args($saved['jurusan'], $defaults['jurusan']);
    }

    foreach ($defaults['prodi'] as $slug => $def_p) {
        $sp = isset($saved['prodi'][$slug]) && is_array($saved['prodi'][$slug]) ? $saved['prodi'][$slug] : array();
        if (!empty($sp['general']) && is_array($sp['general'])) {
            $merged['prodi'][$slug]['general'] = wp_parse_args($sp['general'], $def_p['general']);
        }
        if (!empty($sp['sertifikat']) && is_array($sp['sertifikat'])) {
            $merged['prodi'][$slug]['sertifikat'] = wp_parse_args($sp['sertifikat'], $def_p['sertifikat']);
        }
        if (!empty($sp['kriteria']) && is_array($sp['kriteria'])) {
            foreach ($def_p['kriteria'] as $idx => $def_k) {
                $sk = isset($sp['kriteria'][$idx]) && is_array($sp['kriteria'][$idx]) ? $sp['kriteria'][$idx] : array();
                $row = wp_parse_args($sk, $def_k);
                $norm_ev = ppb_lamdik_normalize_eviden_links($sk, $def_k);
                $row['label_tombol'] = $norm_ev['label_tombol'];
                $row['drive_url']    = $norm_ev['drive_url'];
                $row['eviden_links'] = $norm_ev['eviden_links'];
                $merged['prodi'][$slug]['kriteria'][$idx] = $row;
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
        return function_exists('ppb_clean_document_url') ? ppb_clean_document_url($val) : esc_url_raw((string) $val);
    };

    $out = $defaults;
    $out['schema_version'] = 2;

    $raw_jur = isset($raw['jurusan']) && is_array($raw['jurusan']) ? wp_unslash($raw['jurusan']) : array();
    $out['jurusan']['renstra_url']    = $clean_url($raw_jur['renstra_url'] ?? '');
    $out['jurusan']['renop_url']      = $clean_url($raw_jur['renop_url'] ?? '');
    $upm_clean                        = $clean_url($raw_jur['upm_portal_url'] ?? '');
    $out['jurusan']['upm_portal_url'] = $upm_clean !== '' ? $upm_clean : $defaults['jurusan']['upm_portal_url'];

    $raw_prodi = isset($raw['prodi']) && is_array($raw['prodi']) ? wp_unslash($raw['prodi']) : array();
    foreach ($defaults['prodi'] as $slug => $def_p) {
        $rp = isset($raw_prodi[$slug]) && is_array($raw_prodi[$slug]) ? $raw_prodi[$slug] : array();

        $rg = isset($rp['general']) && is_array($rp['general']) ? $rp['general'] : array();
        $out['prodi'][$slug]['general'] = array(
            'folder_utama_url' => $clean_url($rg['folder_utama_url'] ?? ''),
            'led_url'          => $clean_url($rg['led_url'] ?? ''),
            'lkps_url'         => $clean_url($rg['lkps_url'] ?? ''),
            'matriks_url'      => $clean_url($rg['matriks_url'] ?? ''),
            'kurikulum_label'  => sanitize_text_field($rg['kurikulum_label'] ?? $def_p['general']['kurikulum_label']) ?: $def_p['general']['kurikulum_label'],
        );

        $rk_list = isset($rp['kriteria']) && is_array($rp['kriteria']) ? $rp['kriteria'] : array();
        foreach ($def_p['kriteria'] as $idx => $def_k) {
            $rk = isset($rk_list[$idx]) && is_array($rk_list[$idx]) ? $rk_list[$idx] : array();
            $norm_ev = ppb_lamdik_normalize_eviden_links($rk, $def_k);
            $out['prodi'][$slug]['kriteria'][$idx] = array(
                'kode'           => $def_k['kode'],
                'judul'          => sanitize_text_field($rk['judul'] ?? $def_k['judul']) ?: $def_k['judul'],
                'deskripsi'      => sanitize_textarea_field($rk['deskripsi'] ?? $def_k['deskripsi']) ?: $def_k['deskripsi'],
                'tag_ringkas'    => sanitize_text_field($rk['tag_ringkas'] ?? $def_k['tag_ringkas']) ?: $def_k['tag_ringkas'],
                'internal_links' => $def_k['internal_links'],
                'label_tombol'   => $norm_ev['label_tombol'],
                'drive_url'      => $norm_ev['drive_url'],
                'eviden_links'   => $norm_ev['eviden_links'],
            );
        }

        $rs = isset($rp['sertifikat']) && is_array($rp['sertifikat']) ? $rp['sertifikat'] : array();
        $def_s = $def_p['sertifikat'];
        $out['prodi'][$slug]['sertifikat'] = array(
            'peringkat'    => sanitize_text_field($rs['peringkat'] ?? $def_s['peringkat']) ?: $def_s['peringkat'],
            'lembaga'      => sanitize_text_field($rs['lembaga'] ?? $def_s['lembaga']) ?: $def_s['lembaga'],
            'no_sk'        => sanitize_text_field($rs['no_sk'] ?? $def_s['no_sk']) ?: $def_s['no_sk'],
            'masa_berlaku' => sanitize_text_field($rs['masa_berlaku'] ?? $def_s['masa_berlaku']) ?: $def_s['masa_berlaku'],
            'cert_url'     => $clean_url($rs['cert_url'] ?? ''),
            'sk_url'       => $clean_url($rs['sk_url'] ?? ''),
        );
    }

    update_option('ppb_lamdik_settings', $out, false);
    return $out;
}
}

/* ========================================================================== */
/* 2. HALAMAN ADMIN: PENGATURAN AKREDITASI & LAMDIK PER PRODI + JURUSAN       */
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
        $notice = '<div class="notice notice-success is-dismissible"><p><strong>✅ Pengaturan Akreditasi &amp; 9 Kriteria LAMDIK (Level Jurusan + Seluruh Prodi) berhasil disimpan!</strong> Perubahan langsung aktif di <code>/akreditasi/</code>, <code>/akreditasi/s1/</code>, <code>/akreditasi/s2/</code>, <code>/akreditasi/s3/</code>, <code>/akreditasi/sertifikat/</code>, dan <code>/akreditasi/upm/</code>.</p></div>';
    } else {
        $settings = ppb_get_lamdik_settings();
    }

    $prodi_units = function_exists('ppb_get_prodi_units') ? ppb_get_prodi_units(false) : array();
    $jur         = $settings['jurusan'];
    $active_tab  = isset($_GET['unit']) ? sanitize_key(wp_unslash($_GET['unit'])) : 's1';
    if ($active_tab !== 'jurusan' && !isset($prodi_units[$active_tab])) {
        $active_tab = 's1';
    }

    $count_filled_prodi = function($p_cfg) {
        $cnt = 0;
        foreach ($p_cfg['kriteria'] ?? array() as $kr) {
            foreach ($kr['eviden_links'] ?? array() as $ev) {
                if (!empty($ev['url'])) { $cnt++; break; }
            }
        }
        return $cnt;
    };
    ?>
    <div class="wrap" style="max-width:1220px;margin:20px auto;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;color:#1e293b;">
        <style>
            .ppb-lam-hero { background:linear-gradient(135deg,#0D3B22 0%,#185333 100%); color:#fff; border-radius:16px; padding:26px 28px; display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:18px; margin-bottom:18px; box-shadow:0 8px 22px rgba(13,59,34,0.16); }
            .ppb-lam-tabs { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:20px; background:#fff; border:1px solid #cbd5e1; border-radius:14px; padding:10px 12px; }
            .ppb-lam-tab-btn { display:inline-flex; align-items:center; gap:8px; padding:9px 16px; border-radius:10px; border:1px solid #cbd5e1; background:#f8fafc; color:#334155; font-size:13px; font-weight:700; cursor:pointer; transition:all .15s; }
            .ppb-lam-tab-btn:hover { background:#E8F0EA; color:#0D3B22; }
            .ppb-lam-tab-btn.is-active { background:#0D3B22; color:#F5B82E; border-color:#0D3B22; }
            .ppb-lam-tab-pill { background:rgba(255,255,255,0.2); padding:1px 8px; border-radius:999px; font-size:11px; font-family:monospace; }
            .ppb-lam-tab-btn:not(.is-active) .ppb-lam-tab-pill { background:#e2e8f0; color:#475569; }
            .ppb-lam-card { background:#fff; border:1px solid #cbd5e1; border-radius:14px; padding:22px 24px; margin-bottom:20px; box-shadow:0 3px 10px rgba(0,0,0,0.03); }
            .ppb-lam-card h2 { margin:0 0 6px; font-size:17px; font-weight:800; color:#0D3B22; }
            .ppb-lam-desc { margin:0 0 18px; font-size:13px; color:#64748b; }
            .ppb-lam-grid2 { display:grid; grid-template-columns:repeat(auto-fit, minmax(360px, 1fr)); gap:16px; }
            .ppb-lam-field { background:#FAF8F5; border:1px solid #e2e8f0; border-radius:12px; padding:14px; }
            .ppb-lam-field label { display:block; font-weight:700; font-size:12.5px; color:#0D3B22; margin-bottom:6px; }
            .ppb-lam-krit-card { background:#FAF8F5; border:1px solid #d4e3d8; border-radius:12px; padding:16px; margin-bottom:14px; }
            .ppb-eviden-row { display:grid; grid-template-columns:240px 1fr auto; gap:10px; align-items:center; background:#fff; border:1px solid #e2e8f0; border-radius:9px; padding:8px 10px; margin-bottom:8px; }
            @media (max-width:860px) { .ppb-eviden-row { grid-template-columns:1fr; } }
        </style>

        <div class="ppb-lam-hero">
            <div style="max-width:760px;">
                <span style="display:inline-block;background:rgba(245,184,46,0.2);border:1px solid rgba(245,184,46,0.45);color:#F5B82E;font-size:11px;font-weight:700;padding:3px 12px;border-radius:999px;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;">Hub Bukti Fisik LAMDIK · Terpisah Per Jenjang (S1, S2, S3) &amp; Jurusan</span>
                <h1 style="margin:0 0 6px;color:#fff;font-size:24px;font-weight:800;">🏅 Pengaturan Akreditasi &amp; 9 Kriteria LAMDIK</h1>
                <p style="margin:0;color:rgba(255,255,255,0.88);font-size:13.5px;line-height:1.55;">
                    Pilih tab jenjang di bawah untuk mengatur <strong>Dokumen Induk (Folder Drive, LED, LKPS, Matriks)</strong>, <strong>9 Kriteria Eviden (multi-link per kriteria)</strong>, serta <strong>Sertifikat &amp; SK Resmi</strong> secara terpisah untuk S1, S2, S3 (dan Prodi baru di Jurusan PPB).
                </p>
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:8px;">
                <a href="<?php echo esc_url(home_url('/akreditasi/')); ?>" target="_blank" class="button" style="background:#F5B82E;color:#0D3B22;border:none;font-weight:800;padding:8px 15px;height:auto;border-radius:8px;">↗ Lihat /akreditasi/</a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=ppb-unit-registry')); ?>" class="button" style="background:rgba(255,255,255,0.14);color:#fff;border:1px solid rgba(255,255,255,0.3);font-weight:600;padding:8px 14px;height:auto;border-radius:8px;">🏛️ Kelola Prodi Jurusan</a>
            </div>
        </div>

        <?php echo $notice; ?>

        <div class="ppb-lam-tabs" id="ppb-lamdik-admin-tabs">
            <button type="button" class="ppb-lam-tab-btn <?php echo $active_tab === 'jurusan' ? 'is-active' : ''; ?>" data-lam-tab="jurusan">
                🏛️ Level Jurusan PPB (Renstra &amp; UPM)
            </button>
            <?php foreach ($prodi_units as $slug => $pmeta) :
                $pcfg = $settings['prodi'][$slug] ?? ppb_get_lamdik_prodi_defaults($slug, $pmeta);
                $k_cnt = $count_filled_prodi($pcfg);
            ?>
                <button type="button" class="ppb-lam-tab-btn <?php echo $active_tab === $slug ? 'is-active' : ''; ?>" data-lam-tab="<?php echo esc_attr($slug); ?>">
                    🎓 <?php echo esc_html($pmeta['tab_label'] ?: $pmeta['kode']); ?>
                    <span class="ppb-lam-tab-pill"><?php echo (int) $k_cnt; ?>/9</span>
                </button>
            <?php endforeach; ?>
        </div>

        <form method="post" action="">
            <?php wp_nonce_field('ppb_lamdik_save_action', 'ppb_lamdik_nonce'); ?>

            <!-- PANEL LEVEL JURUSAN PPB -->
            <div class="ppb-lam-pane" data-lam-pane="jurusan" style="display:<?php echo $active_tab === 'jurusan' ? 'block' : 'none'; ?>;">
                <div class="ppb-lam-card">
                    <h2>🏛️ Dokumen Payung Mutu Tingkat Jurusan PPB FIP UNM</h2>
                    <p class="ppb-lam-desc">Renstra Jurusan, Renop Tahunan, dan Portal SPMI/UPM berlaku sebagai payung tata kelola Jurusan PPB dan terhubung otomatis ke halaman <code>/akreditasi/upm/</code> serta bilah informasi pada <code>/akreditasi/</code>.</p>

                    <div class="ppb-lam-grid2">
                        <div class="ppb-lam-field">
                            <label>🧭 Dokumen Renstra Jurusan PPB (2024–2028) &rarr; /akreditasi/upm/</label>
                            <?php ppb_render_media_url_control(array('id' => 'lam_jur_renstra', 'name' => 'jurusan[renstra_url]', 'value' => $jur['renstra_url'], 'placeholder' => 'https://drive.google.com/file/d/... atau upload PDF')); ?>
                        </div>
                        <div class="ppb-lam-field">
                            <label>📋 Dokumen Renop Tahunan Jurusan PPB &rarr; /akreditasi/upm/</label>
                            <?php ppb_render_media_url_control(array('id' => 'lam_jur_renop', 'name' => 'jurusan[renop_url]', 'value' => $jur['renop_url'], 'placeholder' => 'https://drive.google.com/file/d/... atau upload PDF')); ?>
                        </div>
                        <div class="ppb-lam-field" style="grid-column:1 / -1;">
                            <label>🏛️ URL Portal Sistem Penjaminan Mutu Internal (UPM / LPM UNM)</label>
                            <?php ppb_render_media_url_control(array('id' => 'lam_jur_upm', 'name' => 'jurusan[upm_portal_url]', 'value' => $jur['upm_portal_url'], 'placeholder' => 'https://bpm.unm.ac.id/')); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PANEL PER PRODI (S1, S2, S3, ...) -->
            <?php foreach ($prodi_units as $slug => $pmeta) :
                $pcfg = $settings['prodi'][$slug] ?? ppb_get_lamdik_prodi_defaults($slug, $pmeta);
                $gen  = $pcfg['general'];
                $krit = $pcfg['kriteria'];
                $sd   = $pcfg['sertifikat'];
            ?>
            <div class="ppb-lam-pane" data-lam-pane="<?php echo esc_attr($slug); ?>" style="display:<?php echo $active_tab === $slug ? 'block' : 'none'; ?>;">
                <!-- 1. DOKUMEN INDUK PRODI -->
                <div class="ppb-lam-card">
                    <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:10px;margin-bottom:6px;">
                        <h2 style="margin:0;">📁 1. Dokumen Induk Akreditasi — <?php echo esc_html($pmeta['nama']); ?></h2>
                        <a href="<?php echo esc_url(home_url('/akreditasi/' . $slug . '/')); ?>" target="_blank" class="button">↗ Pratinjau /akreditasi/<?php echo esc_html($slug); ?>/</a>
                    </div>
                    <p class="ppb-lam-desc">Tautan Folder Utama, LED, LKPS, dan Matriks Penilaian khusus untuk jenjang <strong><?php echo esc_html($pmeta['kode']); ?></strong>.</p>

                    <div class="ppb-lam-grid2">
                        <div class="ppb-lam-field">
                            <label>🗂️ Folder Utama Bukti Fisik LAMDIK <?php echo esc_html($pmeta['kode']); ?> (Drive)</label>
                            <?php ppb_render_media_url_control(array('id' => 'lam_' . $slug . '_folder', 'name' => 'prodi[' . $slug . '][general][folder_utama_url]', 'value' => $gen['folder_utama_url'], 'placeholder' => 'https://drive.google.com/drive/folders/...')); ?>
                        </div>
                        <div class="ppb-lam-field">
                            <label>📘 Laporan Evaluasi Diri (LED) <?php echo esc_html($pmeta['kode']); ?></label>
                            <?php ppb_render_media_url_control(array('id' => 'lam_' . $slug . '_led', 'name' => 'prodi[' . $slug . '][general][led_url]', 'value' => $gen['led_url'], 'placeholder' => 'https://drive.google.com/file/d/...')); ?>
                        </div>
                        <div class="ppb-lam-field">
                            <label>📊 Laporan Kinerja Program Studi (LKPS) <?php echo esc_html($pmeta['kode']); ?></label>
                            <?php ppb_render_media_url_control(array('id' => 'lam_' . $slug . '_lkps', 'name' => 'prodi[' . $slug . '][general][lkps_url]', 'value' => $gen['lkps_url'], 'placeholder' => 'https://drive.google.com/file/d/...')); ?>
                        </div>
                        <div class="ppb-lam-field">
                            <label>⚖️ Matriks Penilaian &amp; Borang <?php echo esc_html($pmeta['kode']); ?></label>
                            <?php ppb_render_media_url_control(array('id' => 'lam_' . $slug . '_matriks', 'name' => 'prodi[' . $slug . '][general][matriks_url]', 'value' => $gen['matriks_url'], 'placeholder' => 'https://drive.google.com/file/d/...')); ?>
                        </div>
                        <div class="ppb-lam-field" style="grid-column:1 / -1;">
                            <label>🎓 Label Ringkas Kurikulum &amp; KKNI (tampil di kartu ringkasan publik)</label>
                            <input type="text" name="prodi[<?php echo esc_attr($slug); ?>][general][kurikulum_label]" value="<?php echo esc_attr($gen['kurikulum_label'] ?? ''); ?>" style="width:100%;padding:7px 10px;border:1px solid #cbd5e1;border-radius:8px;" />
                        </div>
                    </div>
                </div>

                <!-- 2. 9 KRITERIA LAMDIK PRODI -->
                <div class="ppb-lam-card">
                    <h2>📑 2. Eviden 9 Kriteria LAMDIK — <?php echo esc_html($pmeta['nama']); ?></h2>
                    <p class="ppb-lam-desc">Sesuaikan ringkasan deskripsi dan tambahkan 1 atau lebih tautan Folder Drive / berkas PDF untuk setiap kriteria pada jenjang <strong><?php echo esc_html($pmeta['kode']); ?></strong>.</p>

                    <?php foreach ($krit as $idx => $kr) :
                        $ev_links = !empty($kr['eviden_links']) && is_array($kr['eviden_links'])
                            ? $kr['eviden_links']
                            : array(array('label' => $kr['label_tombol'] ?? ('Eviden Drive K' . ($idx + 1)), 'url' => $kr['drive_url'] ?? ''));
                    ?>
                        <div class="ppb-lam-krit-card" data-krit-wrap="<?php echo esc_attr($slug . '_' . $idx); ?>">
                            <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:10px;margin-bottom:10px;">
                                <div>
                                    <span style="display:inline-block;background:#0D3B22;color:#F5B82E;font-family:monospace;font-size:11px;font-weight:800;padding:2px 8px;border-radius:6px;margin-right:6px;">
                                        <?php echo esc_html($pmeta['kode'] . ' · KRITERIA ' . ($idx + 1)); ?>
                                    </span>
                                    <strong style="font-size:14.5px;color:#0f172a;"><?php echo esc_html($kr['judul']); ?></strong>
                                </div>
                            </div>

                            <div style="display:grid;grid-template-columns:2fr 1fr;gap:12px;margin-bottom:12px;">
                                <div>
                                    <label style="display:block;font-size:11.5px;font-weight:700;color:#475569;margin-bottom:4px;">Ringkasan Butir Eviden <?php echo esc_html($pmeta['kode']); ?></label>
                                    <textarea name="prodi[<?php echo esc_attr($slug); ?>][kriteria][<?php echo (int) $idx; ?>][deskripsi]" rows="2" style="width:100%;padding:7px 10px;border:1px solid #cbd5e1;border-radius:8px;font-size:12.5px;"><?php echo esc_textarea($kr['deskripsi'] ?? ''); ?></textarea>
                                </div>
                                <div>
                                    <label style="display:block;font-size:11.5px;font-weight:700;color:#475569;margin-bottom:4px;">Tag Sorotan Hijau</label>
                                    <input type="text" name="prodi[<?php echo esc_attr($slug); ?>][kriteria][<?php echo (int) $idx; ?>][tag_ringkas]" value="<?php echo esc_attr($kr['tag_ringkas'] ?? ''); ?>" style="width:100%;padding:7px 10px;border:1px solid #cbd5e1;border-radius:8px;font-size:12.5px;" />
                                </div>
                            </div>

                            <div class="ppb-eviden-list" data-prodi="<?php echo esc_attr($slug); ?>" data-kidx="<?php echo (int) $idx; ?>">
                                <label style="display:block;font-size:11.5px;font-weight:700;color:#0D3B22;margin-bottom:6px;">📂 Tautan Eviden Drive / PDF:</label>
                                <?php foreach ($ev_links as $eidx => $ev) :
                                    $input_id = 'lam_ev_' . $slug . '_' . $idx . '_' . $eidx;
                                ?>
                                    <div class="ppb-eviden-row">
                                        <input type="text"
                                               name="prodi[<?php echo esc_attr($slug); ?>][kriteria][<?php echo (int) $idx; ?>][eviden_links][<?php echo (int) $eidx; ?>][label]"
                                               value="<?php echo esc_attr($ev['label'] ?? ''); ?>"
                                               placeholder="Label Tombol Eviden"
                                               style="width:100%;padding:7px 10px;border:1px solid #cbd5e1;border-radius:8px;font-size:12.5px;" />
                                        <div>
                                            <?php ppb_render_media_url_control(array(
                                                'id'          => $input_id,
                                                'name'        => 'prodi[' . $slug . '][kriteria][' . $idx . '][eviden_links][' . $eidx . '][url]',
                                                'value'       => $ev['url'] ?? '',
                                                'placeholder' => 'https://drive.google.com/drive/folders/... atau upload berkas',
                                            )); ?>
                                        </div>
                                        <button type="button" class="button ppb-remove-eviden-btn" style="color:#dc2626;border-color:#fecaca;background:#fef2f2;" title="Hapus tautan eviden">✕</button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="button ppb-add-eviden-btn" data-prodi="<?php echo esc_attr($slug); ?>" data-kidx="<?php echo (int) $idx; ?>" style="margin-top:4px;font-size:12px;font-weight:600;">+ Tambah Tautan Eviden K<?php echo (int) ($idx + 1); ?></button>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- 3. SERTIFIKAT & SK PRODI -->
                <div class="ppb-lam-card">
                    <h2>🎓 3. Sertifikat &amp; SK Akreditasi Resmi — <?php echo esc_html($pmeta['nama']); ?></h2>
                    <p class="ppb-lam-desc">Terhubung otomatis ke halaman <code>/akreditasi/sertifikat/</code>, kartu jenjang Beranda, dan <code>/profil/visi-misi/</code>.</p>

                    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:12px;margin-bottom:14px;">
                        <div class="ppb-lam-field">
                            <label>Peringkat Akreditasi</label>
                            <input type="text" name="prodi[<?php echo esc_attr($slug); ?>][sertifikat][peringkat]" value="<?php echo esc_attr($sd['peringkat']); ?>" style="width:100%;padding:7px 10px;border:1px solid #cbd5e1;border-radius:8px;" />
                        </div>
                        <div class="ppb-lam-field">
                            <label>Lembaga Akreditasi</label>
                            <input type="text" name="prodi[<?php echo esc_attr($slug); ?>][sertifikat][lembaga]" value="<?php echo esc_attr($sd['lembaga']); ?>" style="width:100%;padding:7px 10px;border:1px solid #cbd5e1;border-radius:8px;" />
                        </div>
                        <div class="ppb-lam-field">
                            <label>Nomor SK Resmi</label>
                            <input type="text" name="prodi[<?php echo esc_attr($slug); ?>][sertifikat][no_sk]" value="<?php echo esc_attr($sd['no_sk']); ?>" style="width:100%;padding:7px 10px;border:1px solid #cbd5e1;border-radius:8px;" />
                        </div>
                        <div class="ppb-lam-field">
                            <label>Masa Berlaku</label>
                            <input type="text" name="prodi[<?php echo esc_attr($slug); ?>][sertifikat][masa_berlaku]" value="<?php echo esc_attr($sd['masa_berlaku']); ?>" style="width:100%;padding:7px 10px;border:1px solid #cbd5e1;border-radius:8px;" />
                        </div>
                    </div>
                    <div class="ppb-lam-grid2">
                        <div class="ppb-lam-field">
                            <label>📄 Tautan / File PDF Sertifikat <?php echo esc_html($pmeta['kode']); ?></label>
                            <?php ppb_render_media_url_control(array('id' => 'lam_' . $slug . '_cert', 'name' => 'prodi[' . $slug . '][sertifikat][cert_url]', 'value' => $sd['cert_url'], 'placeholder' => 'https://drive.google.com/... atau upload PDF')); ?>
                        </div>
                        <div class="ppb-lam-field">
                            <label>🏛️ Tautan / File SK Resmi <?php echo esc_html($pmeta['kode']); ?></label>
                            <?php ppb_render_media_url_control(array('id' => 'lam_' . $slug . '_sk', 'name' => 'prodi[' . $slug . '][sertifikat][sk_url]', 'value' => $sd['sk_url'], 'placeholder' => 'https://drive.google.com/... atau upload PDF')); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <div style="display:flex;justify-content:flex-end;gap:12px;margin-bottom:30px;">
                <button type="submit" name="ppb_save_lamdik_submit" class="button button-primary" style="background:#0D3B22;border-color:#0D3B22;color:#F5B82E;font-weight:800;font-size:14px;padding:10px 26px;height:auto;border-radius:10px;box-shadow:0 4px 12px rgba(13,59,34,0.2);">
                    💾 Simpan Seluruh Pengaturan Akreditasi &amp; LAMDIK
                </button>
            </div>
        </form>

        <script>
        (function() {
            var tabBtns = document.querySelectorAll('#ppb-lamdik-admin-tabs .ppb-lam-tab-btn');
            var panes   = document.querySelectorAll('.ppb-lam-pane');
            tabBtns.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var target = btn.getAttribute('data-lam-tab');
                    tabBtns.forEach(function(b) { b.classList.remove('is-active'); });
                    btn.classList.add('is-active');
                    panes.forEach(function(p) {
                        p.style.display = (p.getAttribute('data-lam-pane') === target) ? 'block' : 'none';
                    });
                });
            });

            var rowCounter = 1000;
            document.addEventListener('click', function(e) {
                var addBtn = e.target.closest ? e.target.closest('.ppb-add-eviden-btn') : null;
                if (addBtn) {
                    var prodi = addBtn.getAttribute('data-prodi');
                    var kidx  = addBtn.getAttribute('data-kidx');
                    var wrap  = addBtn.previousElementSibling;
                    if (!wrap) return;
                    rowCounter++;
                    var idx = rowCounter;
                    var inputId = 'lam_ev_dyn_' + prodi + '_' + kidx + '_' + idx;
                    var row = document.createElement('div');
                    row.className = 'ppb-eviden-row';
                    row.innerHTML =
                        '<input type="text" name="prodi[' + prodi + '][kriteria][' + kidx + '][eviden_links][' + idx + '][label]" value="Eviden Tambahan K' + (parseInt(kidx, 10) + 1) + '" placeholder="Label Tombol Eviden" style="width:100%;padding:7px 10px;border:1px solid #cbd5e1;border-radius:8px;font-size:12.5px;" />' +
                        '<div class="ppb-media-input-wrap" style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;width:100%;">' +
                            '<input type="url" id="' + inputId + '" name="prodi[' + prodi + '][kriteria][' + kidx + '][eviden_links][' + idx + '][url]" value="" placeholder="https://drive.google.com/... atau upload berkas" class="regular-text ppb-media-url-input" style="flex:1;min-width:240px;padding:7px 11px;border:1px solid #cbd5e1;border-radius:8px;font-size:13px;" />' +
                            '<button type="button" class="button ppb-media-pick-btn" data-target="' + inputId + '" data-title="Pilih atau Upload Berkas Eviden" data-button-text="Gunakan Berkas Ini" style="background:#E8F0EA;color:#0D3B22;border:1px solid #B7D0BE;font-weight:700;border-radius:8px;padding:4px 12px;height:36px;display:inline-flex;align-items:center;gap:5px;cursor:pointer;">📁 Upload / Pilih File</button>' +
                            '<a href="#" target="_blank" rel="noopener noreferrer" class="button ppb-media-open-link" style="display:none;align-items:center;gap:4px;height:36px;border-radius:8px;text-decoration:none;">↗ Buka</a>' +
                        '</div>' +
                        '<button type="button" class="button ppb-remove-eviden-btn" style="color:#dc2626;border-color:#fecaca;background:#fef2f2;" title="Hapus tautan eviden">✕</button>';
                    wrap.appendChild(row);
                    return;
                }

                var remBtn = e.target.closest ? e.target.closest('.ppb-remove-eviden-btn') : null;
                if (remBtn) {
                    var list = remBtn.closest('.ppb-eviden-list');
                    var rows = list ? list.querySelectorAll('.ppb-eviden-row') : [];
                    var rowEl = remBtn.closest('.ppb-eviden-row');
                    if (rows.length <= 1 && rowEl) {
                        var inp = rowEl.querySelector('.ppb-media-url-input');
                        if (inp) inp.value = '';
                    } else if (rowEl) {
                        rowEl.remove();
                    }
                }
            });
        })();
        </script>
    </div>
    <?php
}
}

/* ========================================================================== */
/* 3. FRONTEND RENDERER: SHORTCODE [ppb_lamdik_hub] & FILTER /akreditasi/     */
/* ========================================================================== */

if (!function_exists('ppb_render_lamdik_hub_html')) {
function ppb_render_lamdik_hub_html($atts = array()) {
    $prodi_units = function_exists('ppb_get_prodi_units') ? ppb_get_prodi_units(true) : array();
    if (empty($prodi_units)) return '';

    $settings   = ppb_get_lamdik_settings();
    $jur        = $settings['jurusan'] ?? array();
    $jur_info   = function_exists('ppb_get_jurusan_info') ? ppb_get_jurusan_info() : array();
    $allowed    = array_keys($prodi_units);
    $active_deg = !empty($atts['jenjang'])
        ? sanitize_key($atts['jenjang'])
        : (function_exists('ppb_resolve_active_unit_slug') ? ppb_resolve_active_unit_slug($allowed, 's1') : 's1');
    if (!in_array($active_deg, $allowed, true)) {
        $active_deg = $allowed[0];
    }

    $base_akreditasi_url = home_url('/akreditasi/');

    ob_start();
    ?>
    <div id="ppb-lamdik-hub-root" data-base-url="<?php echo esc_attr(trailingslashit($base_akreditasi_url)); ?>">
        <!-- Switcher Jenjang S1 / S2 / S3 (+ Prodi Baru) -->
        <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:14px;background:#ffffff;border:1px solid #D4E3D8;border-radius:16px;padding:14px 18px;margin-bottom:22px;box-shadow:0 4px 16px rgba(13,59,34,0.04);">
            <div>
                <div style="font-family:'IBM Plex Mono',monospace;font-size:10.5px;font-weight:700;color:#059669;text-transform:uppercase;letter-spacing:0.08em;">
                    🏛️ <?php echo esc_html($jur_info['singkatan'] ?? 'Jurusan PPB FIP UNM'); ?> · Pilih Program Studi
                </div>
                <div style="font-size:13px;color:#4B5563;margin-top:2px;">
                    Instrumen 9 Kriteria &amp; Dokumen Induk LAMDIK dipisahkan per jenjang studi.
                </div>
            </div>
            <div class="bk-seg-control" id="ppb-lamdik-degree-tabs" role="tablist" aria-label="Pilih Jenjang Akreditasi LAMDIK" style="display:inline-flex;flex-wrap:wrap;gap:4px;">
                <?php foreach ($prodi_units as $slug => $pmeta) :
                    $is_act = ($slug === $active_deg);
                    $tab_href = trailingslashit($base_akreditasi_url) . rawurlencode($slug) . '/';
                ?>
                    <a href="<?php echo esc_url($tab_href); ?>"
                       class="bk-seg-btn ppb-lam-deg-btn <?php echo $is_act ? 'is-active' : ''; ?>"
                       data-lam-deg="<?php echo esc_attr($slug); ?>"
                       role="tab"
                       aria-selected="<?php echo $is_act ? 'true' : 'false'; ?>">
                        <?php echo esc_html($pmeta['tab_label'] ?: $pmeta['kode']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Panel per Jenjang -->
        <?php foreach ($prodi_units as $slug => $pmeta) :
            $is_act = ($slug === $active_deg);
            $pcfg   = $settings['prodi'][$slug] ?? ppb_get_lamdik_prodi_defaults($slug, $pmeta);
            $gen    = $pcfg['general'];
            $krit   = $pcfg['kriteria'];
            $sert   = $pcfg['sertifikat'];

            $filled_krit = 0;
            foreach ($krit as $kr_chk) {
                foreach ($kr_chk['eviden_links'] ?? array() as $ev_chk) {
                    if (!empty($ev_chk['url'])) { $filled_krit++; break; }
                }
            }

            $quick_links = array();
            if (!empty($gen['folder_utama_url'])) {
                $quick_links[] = array('label' => '🗂️ Folder Induk ' . $pmeta['kode'], 'url' => $gen['folder_utama_url'], 'primary' => true);
            }
            if (!empty($gen['led_url'])) {
                $quick_links[] = array('label' => '📘 Dokumen LED ' . $pmeta['kode'], 'url' => $gen['led_url'], 'primary' => false);
            }
            if (!empty($gen['lkps_url'])) {
                $quick_links[] = array('label' => '📊 Dokumen LKPS ' . $pmeta['kode'], 'url' => $gen['lkps_url'], 'primary' => false);
            }
            if (!empty($gen['matriks_url'])) {
                $quick_links[] = array('label' => '⚖️ Matriks Penilaian ' . $pmeta['kode'], 'url' => $gen['matriks_url'], 'primary' => false);
            }
            $acc_id = 'bk-lamdik-accordion-' . $slug;
        ?>
        <div class="ppb-lam-deg-panel" data-lam-deg-panel="<?php echo esc_attr($slug); ?>" style="display:<?php echo $is_act ? 'block' : 'none'; ?>;">
            <!-- Stat Cards Khusus Jenjang -->
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(min(100%, 220px), 1fr));gap:18px;margin-bottom:24px;">
                <div style="background:#ffffff;border:1px solid #D4E3D8;border-radius:16px;padding:18px 20px;box-shadow:0 4px 15px rgba(0,0,0,0.03);">
                    <div style="font-size:11px;font-weight:700;color:#6B7280;text-transform:uppercase;">Akreditasi <?php echo esc_html($pmeta['kode']); ?></div>
                    <div style="font-family:'Fraunces',Georgia,serif;font-size:24px;font-weight:700;color:#0D3B22;margin:4px 0;"><?php echo esc_html($sert['peringkat'] ?: 'UNGGUL'); ?></div>
                    <div style="font-size:12px;color:#059669;font-weight:600;"><?php echo esc_html($sert['lembaga'] . ' · ' . $sert['masa_berlaku']); ?></div>
                </div>
                <div style="background:#ffffff;border:1px solid #D4E3D8;border-radius:16px;padding:18px 20px;box-shadow:0 4px 15px rgba(0,0,0,0.03);">
                    <div style="font-size:11px;font-weight:700;color:#6B7280;text-transform:uppercase;">Nomor SK Resmi</div>
                    <div style="font-family:'IBM Plex Mono',monospace;font-size:14px;font-weight:700;color:#0D3B22;margin:8px 0 6px;word-break:break-word;"><?php echo esc_html($sert['no_sk'] ?: '-'); ?></div>
                    <div style="font-size:12px;color:#4B5563;"><?php echo esc_html($pmeta['fakultas_unit'] ?: 'Jurusan PPB FIP UNM'); ?></div>
                </div>
                <div style="background:#ffffff;border:1px solid #D4E3D8;border-radius:16px;padding:18px 20px;box-shadow:0 4px 15px rgba(0,0,0,0.03);">
                    <div style="font-size:11px;font-weight:700;color:#6B7280;text-transform:uppercase;">Kurikulum &amp; Standar</div>
                    <div style="font-family:'Fraunces',Georgia,serif;font-size:18px;font-weight:700;color:#0D3B22;margin:6px 0 4px;line-height:1.25;"><?php echo esc_html($gen['kurikulum_label'] ?: ($pmeta['kkni'] ?: 'Kurikulum OBE')); ?></div>
                    <div style="font-size:12px;color:#059669;font-weight:600;"><?php echo esc_html($pmeta['beban'] ?: $pmeta['nama']); ?></div>
                </div>
                <div style="background:#ffffff;border:1px solid #D4E3D8;border-radius:16px;padding:18px 20px;box-shadow:0 4px 15px rgba(0,0,0,0.03);">
                    <div style="font-size:11px;font-weight:700;color:#6B7280;text-transform:uppercase;">Kesiapan Eviden Drive</div>
                    <div style="font-family:'Fraunces',Georgia,serif;font-size:24px;font-weight:700;color:#F5B82E;margin:4px 0;"><?php echo (int) $filled_krit; ?> / 9</div>
                    <div style="font-size:12px;color:#4B5563;">Kriteria terhubung repositori</div>
                </div>
            </div>

            <!-- Quick Access Drive Bar per Jenjang + Tautan Payung Jurusan -->
            <div style="background:#0D3B22;color:#fff;border-radius:16px;padding:18px 22px;margin:0 0 24px;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:14px;box-shadow:0 6px 20px rgba(13,59,34,0.14);">
                <div>
                    <div style="font-size:10.5px;font-weight:800;color:#F5B82E;text-transform:uppercase;letter-spacing:.06em;margin-bottom:3px;">Repositori Bukti Fisik · <?php echo esc_html($pmeta['nama']); ?></div>
                    <strong style="font-size:15px;color:#fff;">Akses Cepat Dokumen Induk &amp; Payung Mutu Jurusan</strong>
                </div>
                <div style="display:flex;flex-wrap:wrap;gap:8px;">
                    <?php foreach ($quick_links as $ql) :
                        $st = $ql['primary']
                            ? 'background:#F5B82E;color:#0D3B22;border:none;font-weight:800;'
                            : 'background:rgba(255,255,255,0.14);color:#fff;border:1px solid rgba(255,255,255,0.28);font-weight:600;';
                    ?>
                        <a href="<?php echo esc_url($ql['url']); ?>" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:9px;font-size:12.5px;text-decoration:none;<?php echo $st; ?>"><?php echo esc_html($ql['label']); ?> &#8599;</a>
                    <?php endforeach; ?>
                    <?php if (!empty($sert['cert_url'])) : ?>
                        <a href="<?php echo esc_url($sert['cert_url']); ?>" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:9px;font-size:12.5px;text-decoration:none;background:rgba(255,255,255,0.14);color:#fff;border:1px solid rgba(255,255,255,0.28);font-weight:600;">📄 Sertifikat <?php echo esc_html($pmeta['kode']); ?> &#8599;</a>
                    <?php endif; ?>
                    <a href="<?php echo esc_url(home_url('/akreditasi/upm/')); ?>" style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:9px;font-size:12.5px;text-decoration:none;background:rgba(245,184,46,0.16);color:#F5B82E;border:1px solid rgba(245,184,46,0.4);font-weight:700;">🏛️ Renstra &amp; UPM Jurusan &rarr;</a>
                </div>
            </div>

            <!-- Accordion Heading -->
            <div style="display:flex;flex-wrap:wrap;align-items:flex-end;justify-content:space-between;gap:14px;margin-bottom:18px;">
                <div>
                    <div style="font-family:'IBM Plex Mono',monospace;font-size:11px;font-weight:600;color:#059669;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:6px;">
                        Instrumen 9 Kriteria LAMDIK &middot; <?php echo esc_html($pmeta['jenjang_label'] ?: $pmeta['nama']); ?>
                    </div>
                    <h2 style="font-family:'Fraunces',Georgia,serif;font-size:clamp(22px, 2.6vw, 28px);color:#0D3B22;margin:0;">
                        Direktori Eviden 9 Kriteria <?php echo esc_html($pmeta['kode']); ?>
                    </h2>
                </div>
                <button type="button" class="ppb-lam-toggle-all" data-target-acc="<?php echo esc_attr($acc_id); ?>" style="appearance:none;background:#ffffff;border:1px solid #D4E3D8;color:#0D3B22;font-family:'Plus Jakarta Sans',sans-serif;font-size:12.5px;font-weight:600;padding:8px 14px;border-radius:8px;cursor:pointer;">Buka Semua (9 Kriteria)</button>
            </div>

            <!-- Accordion 9 Kriteria -->
            <div class="bk-accordion" id="<?php echo esc_attr($acc_id); ?>">
                <?php foreach ($krit as $idx => $kr) :
                    $is_open = ($idx === 0);
                    $internal = !empty($kr['internal_links']) && is_array($kr['internal_links']) ? $kr['internal_links'] : array();
                    $ev_btns  = array();
                    foreach ($kr['eviden_links'] ?? array() as $ev) {
                        if (!empty($ev['url'])) {
                            $ev_btns[] = $ev;
                        }
                    }
                    $total_tautan = count($internal) + count($ev_btns);
                ?>
                <div class="bk-acc-item <?php echo $is_open ? 'is-open' : ''; ?>">
                    <button type="button" class="bk-acc-trigger" data-lamdik-idx="<?php echo (int) $idx; ?>" aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>">
                        <span class="bk-acc-title-wrap">
                            <span class="bk-acc-num"><?php echo sprintf('%02d', $idx + 1); ?></span>
                            <span class="bk-acc-title"><?php echo esc_html($kr['judul']); ?></span>
                        </span>
                        <span style="display:inline-flex;align-items:center;gap:8px;flex-shrink:0;">
                            <span style="font-family:'IBM Plex Mono',monospace;font-size:11px;font-weight:600;color:#475569;background:#F1F5F2;border:1px solid #D4E3D8;padding:3px 9px;border-radius:9999px;white-space:nowrap;"><?php echo (int) $total_tautan; ?> Tautan</span>
                            <svg class="bk-acc-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </span>
                    </button>
                    <div class="bk-acc-panel" style="<?php echo $is_open ? 'display:block;' : 'display:none;'; ?>">
                        <p style="margin:0 0 12px;"><?php echo esc_html($kr['deskripsi'] ?? ''); ?></p>
                        <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;">
                            <span class="bk-tag bk-tag-sage"><?php echo esc_html($kr['tag_ringkas'] ?? '✓ Eviden Terverifikasi'); ?></span>
                            <div style="display:flex;flex-wrap:wrap;gap:8px;">
                                <?php foreach ($internal as $iidx => $il) :
                                    $il_url = $il['url'] ?? '/';
                                    $is_ext = (strpos($il_url, 'http') === 0 && strpos($il_url, home_url()) === false);
                                    $href   = $is_ext ? $il_url : home_url($il_url);
                                    $btn_css = ($iidx === 0)
                                        ? 'background:#0D3B22;color:#ffffff;border:none;'
                                        : 'background:#FAF8F5;color:#0D3B22;border:1px solid #D4E3D8;';
                                ?>
                                    <a href="<?php echo esc_url($href); ?>" <?php echo $is_ext ? 'target="_blank" rel="noopener noreferrer"' : ''; ?> style="display:inline-flex;align-items:center;justify-content:center;font-size:12px;font-weight:600;padding:8px 14px;border-radius:8px;text-decoration:none;<?php echo $btn_css; ?>"><?php echo esc_html($il['label'] ?? 'Lihat'); ?> <?php echo $is_ext ? '&#8599;' : '&rarr;'; ?></a>
                                <?php endforeach; ?>

                                <?php foreach ($ev_btns as $ev) : ?>
                                    <a href="<?php echo esc_url($ev['url']); ?>" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;justify-content:center;gap:5px;background:#F5B82E;color:#0D3B22;font-size:12px;font-weight:700;padding:8px 14px;border-radius:8px;text-decoration:none;">📂 <?php echo esc_html($ev['label'] ?: 'Eviden Drive'); ?> &#8599;</a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <script>
        (function() {
            var root = document.getElementById('ppb-lamdik-hub-root');
            if (!root || root.getAttribute('data-wired') === '1') return;
            root.setAttribute('data-wired', '1');

            var degBtns   = root.querySelectorAll('.ppb-lam-deg-btn');
            var degPanels = root.querySelectorAll('.ppb-lam-deg-panel');
            var baseUrl   = root.getAttribute('data-base-url') || '';

            function activateDegree(deg, pushUrl) {
                degBtns.forEach(function(b) {
                    var active = (b.getAttribute('data-lam-deg') === deg);
                    b.classList.toggle('is-active', active);
                    b.setAttribute('aria-selected', active ? 'true' : 'false');
                });
                degPanels.forEach(function(p) {
                    p.style.display = (p.getAttribute('data-lam-deg-panel') === deg) ? 'block' : 'none';
                });
                if (pushUrl && window.history && window.history.replaceState && baseUrl) {
                    window.history.replaceState(null, '', baseUrl + deg + '/');
                }
            }

            degBtns.forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    activateDegree(btn.getAttribute('data-lam-deg'), true);
                });
            });

            // Wire accordions inside each degree panel cleanly
            root.querySelectorAll('.bk-accordion').forEach(function(acc) {
                var triggers = acc.querySelectorAll('.bk-acc-trigger');
                triggers.forEach(function(tr) {
                    tr.addEventListener('click', function(e) {
                        e.stopImmediatePropagation();
                        var item  = tr.closest('.bk-acc-item');
                        var panel = item ? item.querySelector('.bk-acc-panel') : null;
                        if (!item || !panel) return;
                        var willOpen = !item.classList.contains('is-open');
                        item.classList.toggle('is-open', willOpen);
                        tr.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
                        panel.style.display = willOpen ? 'block' : 'none';
                    }, true);
                });
            });

            root.querySelectorAll('.ppb-lam-toggle-all').forEach(function(tBtn) {
                tBtn.addEventListener('click', function() {
                    var acc = document.getElementById(tBtn.getAttribute('data-target-acc'));
                    if (!acc) return;
                    var items = acc.querySelectorAll('.bk-acc-item');
                    var openCount = acc.querySelectorAll('.bk-acc-item.is-open').length;
                    var shouldOpen = (openCount < items.length);
                    items.forEach(function(it) {
                        var tr = it.querySelector('.bk-acc-trigger');
                        var pn = it.querySelector('.bk-acc-panel');
                        it.classList.toggle('is-open', shouldOpen);
                        if (tr) tr.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
                        if (pn) pn.style.display = shouldOpen ? 'block' : 'none';
                    });
                    tBtn.textContent = shouldOpen ? 'Tutup Semua' : 'Buka Semua (9 Kriteria)';
                });
            });
        })();
        </script>
    </div>
    <?php
    return ob_get_clean();
}
}
add_shortcode('ppb_lamdik_hub', 'ppb_render_lamdik_hub_html');

add_filter('the_content', function($content) {
    if (is_admin() || !is_string($content)) return $content;

    $slug_path   = function_exists('ppb_get_current_page_slug_path') ? ppb_get_current_page_slug_path() : '';
    $settings    = ppb_get_lamdik_settings();
    $jur         = $settings['jurusan'] ?? array();
    $prodi_map   = $settings['prodi'] ?? array();
    $prodi_units = function_exists('ppb_get_prodi_units') ? ppb_get_prodi_units(true) : array();

    // 3a. /akreditasi/ atau /akreditasi/{jenjang}/ -> Replace stat grid + accordion dengan Hub S1/S2/S3 dinamis
    $is_akred_hub = ($slug_path === 'akreditasi' || (strpos($slug_path, 'akreditasi/') === 0 && !in_array($slug_path, array('akreditasi/sertifikat', 'akreditasi/upm'), true)));
    if (($is_akred_hub || strpos($content, 'id="bk-lamdik-accordion"') !== false) && strpos($content, 'id="ppb-lamdik-hub-root"') === false) {
        if (strpos($content, 'id="bk-lamdik-accordion"') !== false) {
            $hub_html = ppb_render_lamdik_hub_html();
            // Hapus 4 kartu stat statis sebelum accordion jika ada agar tidak ganda
            $tmp_stat = preg_replace(
                '/<div\s+style=["\']display:grid;grid-template-columns:repeat\(auto-fit,\s*minmax\([^)]*\)[^>]*>\s*<div[^>]*>\s*<div[^>]*>Akreditasi Program Studi.*?<\/div>\s*<\/div>\s*<\/div>\s*(?=<!--\s*Accordion|<div[^>]*justify-content:space-between)/is',
                '',
                $content,
                1
            );
            if (is_string($tmp_stat)) {
                $content = $tmp_stat;
            }
            // Hapus heading + tombol toggle lama tepat sebelum #bk-lamdik-accordion
            $tmp_head = preg_replace(
                '/(?:<!--[^>]*-->\s*)?<div\s+style=["\']display:flex;flex-wrap:wrap;align-items:flex-end;justify-content:space-between[^>]*>.*?Direktori Eviden 9 Kriteria LAMDIK.*?<\/button>\s*<\/div>\s*/is',
                '',
                $content,
                1
            );
            if (is_string($tmp_head)) {
                $content = $tmp_head;
            }
            if (function_exists('ppb_replace_div_by_id')) {
                $content = ppb_replace_div_by_id($content, 'bk-lamdik-accordion', $hub_html, true);
            }
        }
    }

    // 3b. /akreditasi/sertifikat/ -> Render kartu sertifikat dinamis untuk seluruh Prodi aktif
    if ($slug_path === 'akreditasi/sertifikat' || $slug_path === 'sertifikat' || (strpos($content, 'Unduh Sertifikat S1') !== false && strpos($content, 'Unduh SK Resmi') !== false)) {
        $cards_html = '<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(min(100%, 300px), 1fr));gap:24px;margin-bottom:40px;">';
        foreach ($prodi_units as $deg => $pmeta) {
            $sd = $prodi_map[$deg]['sertifikat'] ?? array('peringkat' => '-', 'lembaga' => 'LAMDIK', 'no_sk' => '-', 'masa_berlaku' => '-', 'cert_url' => '', 'sk_url' => '');
            $cert_label = '📄 Unduh Sertifikat ' . strtoupper($pmeta['kode'] ?? $deg);
            $cert_btn = !empty($sd['cert_url'])
                ? '<a href="' . esc_url($sd['cert_url']) . '" target="_blank" rel="noopener noreferrer" style="flex:1;text-align:center;background:#0D3B22;color:#ffffff;font-size:12.5px;font-weight:700;padding:10px 14px;border-radius:8px;text-decoration:none;">' . esc_html($cert_label) . ' &#8599;</a>'
                : '<span style="flex:1;text-align:center;background:#E2E8F0;color:#475569;font-size:12px;font-weight:600;padding:10px 14px;border-radius:8px;">Sertifikat Belum Diunggah</span>';
            $sk_btn = !empty($sd['sk_url'])
                ? '<a href="' . esc_url($sd['sk_url']) . '" target="_blank" rel="noopener noreferrer" style="background:#FAF8F5;color:#0D3B22;border:1px solid #D4E3D8;font-size:12px;font-weight:600;padding:10px 14px;border-radius:8px;text-decoration:none;">Unduh SK Resmi &#8599;</a>'
                : '<span style="background:#F8FAFC;color:#64748B;border:1px solid #E2E8F0;font-size:12px;font-weight:600;padding:10px 14px;border-radius:8px;">SK Belum Diunggah</span>';

            $cards_html .= '<div style="background:#ffffff;border:1.5px solid #D4E3D8;border-radius:18px;padding:28px;box-shadow:0 4px 15px rgba(0,0,0,0.03);display:flex;flex-direction:column;justify-content:space-between;">'
                . '<div>'
                . '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;gap:8px;flex-wrap:wrap;">'
                . '<span style="font-size:11px;font-weight:700;color:#6B7280;text-transform:uppercase;">' . esc_html($pmeta['jenjang_label'] ?: $pmeta['kode']) . '</span>'
                . '<span style="background:#DCFCE7;color:#166534;font-size:11px;font-weight:700;padding:3px 10px;border-radius:6px;">' . esc_html($sd['peringkat'] ?: '-') . '</span>'
                . '</div>'
                . '<h3 style="font-family:\'Fraunces\',Georgia,serif;font-size:22px;color:#0D3B22;margin:0 0 10px;">' . esc_html($pmeta['nama']) . '</h3>'
                . '<p style="font-size:13px;color:#4B5563;line-height:1.6;margin:0 0 16px;">' . esc_html($pmeta['visi_keilmuan'] ?? '') . '</p>'
                . '<div style="background:#FAF8F5;border:1px solid #E5E7EB;border-radius:10px;padding:12px;font-size:12.5px;color:#374151;margin-bottom:16px;line-height:1.6;">'
                . '<div><strong>Lembaga:</strong> ' . esc_html($sd['lembaga'] ?? '-') . '</div>'
                . '<div><strong>No. SK:</strong> ' . esc_html($sd['no_sk'] ?? '-') . '</div>'
                . '<div><strong>Masa Berlaku:</strong> ' . esc_html($sd['masa_berlaku'] ?? '-') . '</div>'
                . '</div>'
                . '</div>'
                . '<div>'
                . '<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:10px;">' . $cert_btn . $sk_btn . '</div>'
                . '<a href="' . esc_url(home_url('/akreditasi/' . $deg . '/')) . '" style="display:block;text-align:center;font-size:12px;font-weight:700;color:#0D3B22;text-decoration:none;padding:7px 10px;background:#F1F5F2;border-radius:8px;">Buka 9 Kriteria LAMDIK ' . esc_html($pmeta['kode']) . ' &rarr;</a>'
                . '</div>'
                . '</div>';
        }
        $cards_html .= '</div>';

        $replaced_cert = preg_replace(
            '/<div\s+style=["\']display:grid;grid-template-columns:repeat\(auto-fit,\s*minmax\([^)]*\)[^>]*>\s*<!--\s*S1\s*-->.*?<\/div>\s*(?=<\/div>\s*<\/section>)/is',
            $cards_html,
            $content,
            1
        );
        if (is_string($replaced_cert) && $replaced_cert !== '' && $replaced_cert !== $content) {
            $content = $replaced_cert;
        }
    }

    // 3c. /akreditasi/upm/ -> Level Jurusan PPB (Renstra, Renop, Portal UPM)
    if ($slug_path === 'akreditasi/upm' || $slug_path === 'upm' || strpos($content, 'Unduh Dokumen Renstra PDF') !== false) {
        if (!empty($jur['renstra_url'])) {
            $tmp = preg_replace(
                '/<a\s+href=["\']#["\']([^>]*)>(\s*📄\s*Unduh Dokumen Renstra PDF\s*)<\/a>/is',
                '<a href="' . esc_url($jur['renstra_url']) . '" target="_blank" rel="noopener noreferrer"$1>$2 &#8599;</a>',
                $content
            );
        } else {
            $tmp = preg_replace(
                '/<a\s+href=["\']#["\']([^>]*)>\s*📄\s*Unduh Dokumen Renstra PDF\s*<\/a>/is',
                '<span style="display:inline-block;background:#E2E8F0;color:#475569;font-size:12px;font-weight:600;padding:8px 14px;border-radius:8px;">Dokumen Renstra Belum Diunggah</span>',
                $content
            );
        }
        if (is_string($tmp)) {
            $content = $tmp;
        }

        if (!empty($jur['renop_url'])) {
            $tmp = preg_replace(
                '/<a\s+href=["\']#["\']([^>]*)>(\s*📄\s*Unduh Dokumen Renop PDF\s*)<\/a>/is',
                '<a href="' . esc_url($jur['renop_url']) . '" target="_blank" rel="noopener noreferrer"$1>$2 &#8599;</a>',
                $content
            );
        } else {
            $tmp = preg_replace(
                '/<a\s+href=["\']#["\']([^>]*)>\s*📄\s*Unduh Dokumen Renop PDF\s*<\/a>/is',
                '<span style="display:inline-block;background:#E2E8F0;color:#475569;font-size:12px;font-weight:600;padding:8px 14px;border-radius:8px;">Dokumen Renop Belum Diunggah</span>',
                $content
            );
        }
        if (is_string($tmp)) {
            $content = $tmp;
        }

        if (!empty($jur['upm_portal_url'])) {
            $content = str_replace('href="https://bpm.unm.ac.id/"', 'href="' . esc_url($jur['upm_portal_url']) . '"', $content);
        }
    }

    // 3d. /profil/visi-misi/ -> Perbaiki link sertifikat lama S1/S2/S3
    if (strpos($content, 'sertifikat-akreditasi-s1.pdf') !== false || strpos($content, 'sertifikat-akreditasi-s2.pdf') !== false || strpos($content, 'sertifikat-akreditasi-s3.pdf') !== false) {
        foreach (array('s1', 's2', 's3') as $deg) {
            $sd = $prodi_map[$deg]['sertifikat'] ?? array();
            $fallback_page = !empty($prodi_map[$deg]['general']['folder_utama_url'])
                ? $prodi_map[$deg]['general']['folder_utama_url']
                : home_url('/akreditasi/' . $deg . '/');
            $target_url = !empty($sd['cert_url'])
                ? $sd['cert_url']
                : (!empty($sd['sk_url']) ? $sd['sk_url'] : $fallback_page);
            $content = str_replace(
                'https://bk-fip.unm.ac.id/wp-content/uploads/sertifikat-akreditasi-' . $deg . '.pdf',
                esc_url($target_url),
                $content
            );
        }
    }

    return $content;
}, 9);
