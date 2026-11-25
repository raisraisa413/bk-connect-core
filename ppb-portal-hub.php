<?php
/**
 * Module: PPB FIP UNM - Unified Portal Hub Dashboard & Grouped Sub-Submenu Navigation
 * Description: Pusat kendali terpadu WP Admin untuk Jurusan PPB / Prodi BK FIP UNM.
 *              Menyatukan 20+ menu ke dalam 8 Kategori Utama dengan Sub-Submenu (Flyout & Accordion),
 *              Top Unified Sub-Navigation Bar, dan Pencarian Cepat (Instant Menu Search).
 * Author: Jurusan PPB / BK FIP UNM
 * Version: 1.3.0
 */

if (!defined('ABSPATH')) exit;

/* ========================================================================== */
/* 1. DEFINISI HIRARKI 8 KATEGORI MENU & SUB-SUBMENU PORTAL PPB FIP           */
/* ========================================================================== */
if (!function_exists('ppb_get_portal_menu_hierarchy')) {
function ppb_get_portal_menu_hierarchy() {
    return array(
        'dashboard' => array(
            'title'      => '📊 Dashboard & Pencarian',
            'short'      => 'Dashboard',
            'icon'       => '📊',
            'slug'       => 'ppb-portal-hub',
            'url'        => admin_url('admin.php?page=ppb-portal-hub'),
            'match_pages'=> array('ppb-portal-hub', 'ppb-unit-registry'),
            'match_cpts' => array(),
            'children'   => array(
                array(
                    'label'    => '📊 Pusat Kendali & Pencarian',
                    'url'      => admin_url('admin.php?page=ppb-portal-hub'),
                    'slug'     => 'ppb-portal-hub',
                    'keywords' => 'dashboard beranda pusat kendali ringkasan statistik',
                ),
                array(
                    'label'    => '🏛️ Unit & Prodi Jurusan PPB',
                    'url'      => admin_url('admin.php?page=ppb-unit-registry'),
                    'slug'     => 'ppb-unit-registry',
                    'keywords' => 'jurusan ppb prodi s1 s2 s3 ppg unit kaprodi visi registry jenjang',
                ),
                array(
                    'label'    => '📥 Hub Import / Export (16 Dataset)',
                    'url'      => admin_url('admin.php?page=ppb-excel-import'),
                    'slug'     => 'ppb-excel-import',
                    'keywords' => 'import export excel csv json backup data massal',
                ),
                array(
                    'label'    => '✏️ Live Frontend Text Editor',
                    'url'      => admin_url('options-general.php?page=wfie-settings'),
                    'slug'     => 'wfie-settings',
                    'keywords' => 'live frontend inline editor edit teks langsung wysiwyg',
                ),
            ),
        ),
        'beranda' => array(
            'title'      => '🏠 Beranda & Navigasi',
            'short'      => 'Beranda & Menu',
            'icon'       => '🏠',
            'slug'       => 'ppb-spotlight-beranda',
            'url'        => admin_url('admin.php?page=ppb-spotlight-beranda'),
            'match_pages'=> array('ppb-spotlight-beranda', 'bk-pengaturan-beranda', 'ppb-mega-menu-manager', 'wfie-settings'),
            'match_cpts' => array(),
            'match_files'=> array('nav-menus.php'),
            'children'   => array(
                array(
                    'label'    => '🌟 Spotlight Video & Slide Beranda',
                    'url'      => admin_url('admin.php?page=ppb-spotlight-beranda'),
                    'slug'     => 'ppb-spotlight-beranda',
                    'keywords' => 'spotlight hero video youtube slider beranda depan banner',
                ),
                array(
                    'label'    => '⚙️ Teks & Seksi Beranda',
                    'url'      => admin_url('admin.php?page=bk-pengaturan-beranda'),
                    'slug'     => 'bk-pengaturan-beranda',
                    'keywords' => 'teks beranda pengaturan sambutan kajur visi misi',
                ),
                array(
                    'label'    => '🧭 Mega Menu Navbar (4 Pilar)',
                    'url'      => admin_url('admin.php?page=ppb-mega-menu-manager'),
                    'slug'     => 'ppb-mega-menu-manager',
                    'keywords' => 'mega menu 4 pilar navigasi header navbar dropdown',
                ),
                array(
                    'label'    => '📋 Struktur Menu WP (nav-menus)',
                    'url'      => admin_url('nav-menus.php'),
                    'slug'     => 'nav-menus.php',
                    'keywords' => 'menu wordpress nav-menus urutan link',
                ),
                array(
                    'label'    => '✏️ Live Frontend Text Editor',
                    'url'      => admin_url('options-general.php?page=wfie-settings'),
                    'slug'     => 'wfie-settings',
                    'keywords' => 'live frontend inline editor edit teks langsung',
                ),
            ),
        ),
        'dosen_matkul' => array(
            'title'      => '👨‍🏫 Dosen & Mata Kuliah',
            'short'      => 'Dosen & Matkul',
            'icon'       => '👨‍🏫',
            'slug'       => 'bk-direktori-dosen',
            'url'        => admin_url('admin.php?page=bk-direktori-dosen'),
            'match_pages'=> array('bk-direktori-dosen', 'ppb-mata-kuliah-manager'),
            'match_cpts' => array('dosen', 'ppb_dosen'),
            'children'   => array(
                array(
                    'label'    => '👨‍🏫 Direktori Dosen & Sinkron SINTA',
                    'url'      => admin_url('admin.php?page=bk-direktori-dosen'),
                    'slug'     => 'bk-direktori-dosen',
                    'keywords' => 'dosen staf pengajar nip nidn sinta google scholar foto jabatan guru besar lektor',
                ),
                array(
                    'label'    => '📚 Mata Kuliah OBE & RPS/SAP',
                    'url'      => admin_url('admin.php?page=ppb-mata-kuliah-manager'),
                    'slug'     => 'ppb-mata-kuliah-manager',
                    'keywords' => 'mata kuliah matkul kurikulum obe rps sap sks semester dosen pengampu',
                ),
                array(
                    'label'    => '📥 Import Excel Mata Kuliah / Dosen',
                    'url'      => admin_url('admin.php?page=ppb-excel-import&dataset=matkul'),
                    'slug'     => 'ppb-excel-import-matkul',
                    'keywords' => 'import excel csv mata kuliah dosen massal',
                ),
            ),
        ),
        'akademik_riset' => array(
            'title'      => '⚖️ Akademik, Riset & E-Lib',
            'short'      => 'Akademik & E-Lib',
            'icon'       => '⚖️',
            'slug'       => 'ppb-akademik-settings',
            'url'        => admin_url('admin.php?page=ppb-akademik-settings'),
            'match_pages'=> array('ppb-akademik-settings', 'ppb-elibrary-manager'),
            'match_cpts' => array('ppb_jadwal'),
            'children'   => array(
                array(
                    'label'    => '⚖️ Tautan Dokumen Akademik & MoA',
                    'url'      => admin_url('admin.php?page=ppb-akademik-settings&tab=tautan'),
                    'slug'     => 'ppb-akademik-settings',
                    'tab'      => 'tautan',
                    'keywords' => 'akademik kurikulum buku pedoman sk senat rubrik obe moa kerjasama',
                ),
                array(
                    'label'    => '🔬 Hibah Riset & PkM PNBP',
                    'url'      => admin_url('admin.php?page=ppb-akademik-settings&tab=pnbp'),
                    'slug'     => 'ppb-akademik-settings',
                    'tab'      => 'pnbp',
                    'keywords' => 'hibah riset penelitian pengabdian pkm pnbp dana',
                ),
                array(
                    'label'    => '📰 Jurnal Ilmiah Prodi BK',
                    'url'      => admin_url('admin.php?page=ppb-akademik-settings&tab=jurnal'),
                    'slug'     => 'ppb-akademik-settings',
                    'tab'      => 'jurnal',
                    'keywords' => 'jurnal ilmiah publikasi ojs sinta',
                ),
                array(
                    'label'    => '📅 Jadwal Ujian & Seminar Skripsi',
                    'url'      => admin_url('edit.php?post_type=ppb_jadwal'),
                    'slug'     => 'edit.php?post_type=ppb_jadwal',
                    'cpt'      => 'ppb_jadwal',
                    'keywords' => 'jadwal ujian seminar proposal hasil tutup skripsi mahasiswa',
                ),
                array(
                    'label'    => '📚 E-Library & Kurasi Modul BK',
                    'url'      => admin_url('admin.php?page=ppb-elibrary-manager'),
                    'slug'     => 'ppb-elibrary-manager',
                    'keywords' => 'elibrary e-library modul buku praktikum pdf repositori naskah',
                ),
            ),
        ),
        'mutu_lamdik' => array(
            'title'      => '🏅 Mutu, LAMDIK & Survei',
            'short'      => 'Mutu & LAMDIK',
            'icon'       => '🏅',
            'slug'       => 'ppb-lamdik-settings',
            'url'        => admin_url('admin.php?page=ppb-lamdik-settings'),
            'match_pages'=> array('ppb-lamdik-settings', 'ppb-survei-manager'),
            'match_cpts' => array('ppb_dokumen'),
            'children'   => array(
                array(
                    'label'    => '🏅 Akreditasi & 9 Kriteria LAMDIK',
                    'url'      => admin_url('admin.php?page=ppb-lamdik-settings'),
                    'slug'     => 'ppb-lamdik-settings',
                    'keywords' => 'akreditasi lamdik 9 kriteria led lkps sertifikat unggul s1 s2 s3',
                ),
                array(
                    'label'    => '📂 Dokumen Mutu (Google Drive)',
                    'url'      => admin_url('edit.php?post_type=ppb_dokumen'),
                    'slug'     => 'edit.php?post_type=ppb_dokumen',
                    'cpt'      => 'ppb_dokumen',
                    'keywords' => 'dokumen mutu google drive renstra renop sop ami sk regulasi',
                ),
                array(
                    'label'    => '📊 Survei Kepuasan Mahasiswa (GPM)',
                    'url'      => admin_url('admin.php?page=ppb-survei-manager'),
                    'slug'     => 'ppb-survei-manager',
                    'keywords' => 'survei kepuasan mahasiswa gpm ikm kuesioner ami',
                ),
            ),
        ),
        'kemahasiswaan' => array(
            'title'      => '🏆 Kemahasiswaan & Alumni',
            'short'      => 'Kemahasiswaan',
            'icon'       => '🏆',
            'slug'       => 'ppb-kemahasiswaan-settings',
            'url'        => admin_url('admin.php?page=ppb-kemahasiswaan-settings'),
            'match_pages'=> array('ppb-kemahasiswaan-settings'),
            'match_cpts' => array('ppb_prestasi', 'ppb_karya'),
            'children'   => array(
                array(
                    'label'    => '📈 Statistik Maba & Tracer Study',
                    'url'      => admin_url('admin.php?page=ppb-kemahasiswaan-settings&tab=statistik'),
                    'slug'     => 'ppb-kemahasiswaan-settings',
                    'tab'      => 'statistik',
                    'keywords' => 'kemahasiswaan maba mahasiswa baru daya tampung peminat tracer study alumni',
                ),
                array(
                    'label'    => '🤝 Pameran MBKM & Kemanusiaan',
                    'url'      => admin_url('admin.php?page=ppb-kemahasiswaan-settings&tab=mbkm'),
                    'slug'     => 'ppb-kemahasiswaan-settings',
                    'tab'      => 'mbkm',
                    'keywords' => 'mbkm merdeka belajar proyek kemanusiaan kampus mengajar magang',
                ),
                array(
                    'label'    => '🏅 Daftar Prestasi Mahasiswa',
                    'url'      => admin_url('edit.php?post_type=ppb_prestasi'),
                    'slug'     => 'edit.php?post_type=ppb_prestasi',
                    'cpt'      => 'ppb_prestasi',
                    'keywords' => 'prestasi mahasiswa juara lomba kejuaraan medali',
                ),
                array(
                    'label'    => '🎨 Karya & Inovasi Mahasiswa',
                    'url'      => admin_url('edit.php?post_type=ppb_karya'),
                    'slug'     => 'edit.php?post_type=ppb_karya',
                    'cpt'      => 'ppb_karya',
                    'keywords' => 'karya inovasi mahasiswa produk aplikasi buku media bk',
                ),
            ),
        ),
        'layanan_lab' => array(
            'title'      => '🏢 Layanan, Lab & Sarpras',
            'short'      => 'Layanan & Lab',
            'icon'       => '🏢',
            'slug'       => 'ppb-layanan-settings',
            'url'        => admin_url('admin.php?page=ppb-layanan-settings'),
            'match_pages'=> array('ppb-layanan-settings'),
            'match_cpts' => array('ppb_inventaris_lab', 'ppb_template_dok', 'ppb_alur_sop'),
            'children'   => array(
                array(
                    'label'    => '⚙️ Hotline, SIPADU & Layanan',
                    'url'      => admin_url('admin.php?page=ppb-layanan-settings&tab=umum'),
                    'slug'     => 'ppb-layanan-settings',
                    'tab'      => 'umum',
                    'keywords' => 'layanan mahasiswa hotline whatsapp sipadu akademik administrasi',
                ),
                array(
                    'label'    => '🧪 Kegiatan & Lomba Lab BK',
                    'url'      => admin_url('admin.php?page=ppb-layanan-settings&tab=lab_kegiatan'),
                    'slug'     => 'ppb-layanan-settings',
                    'tab'      => 'lab_kegiatan',
                    'keywords' => 'laboratorium lab bk kegiatan praktikum lomba konseling',
                ),
                array(
                    'label'    => '🏛️ Fasilitas & Foto Sarpras',
                    'url'      => admin_url('admin.php?page=ppb-layanan-settings&tab=sarpras'),
                    'slug'     => 'ppb-layanan-settings',
                    'tab'      => 'sarpras',
                    'keywords' => 'sarana prasarana sarpras ruangan gedung fasilitas foto lab konseling',
                ),
                array(
                    'label'    => '🔬 Inventaris Alat & Tes Lab BK',
                    'url'      => admin_url('edit.php?post_type=ppb_inventaris_lab'),
                    'slug'     => 'edit.php?post_type=ppb_inventaris_lab',
                    'cpt'      => 'ppb_inventaris_lab',
                    'keywords' => 'inventaris alat tes psikologi lab bk instrumen praktikum',
                ),
                array(
                    'label'    => '📄 Template Surat & Formulir',
                    'url'      => admin_url('edit.php?post_type=ppb_template_dok'),
                    'slug'     => 'edit.php?post_type=ppb_template_dok',
                    'cpt'      => 'ppb_template_dok',
                    'keywords' => 'template surat formulir permohonan izin penelitian skripsi',
                ),
                array(
                    'label'    => '🔄 Alur SOP Layanan Akademik',
                    'url'      => admin_url('edit.php?post_type=ppb_alur_sop'),
                    'slug'     => 'edit.php?post_type=ppb_alur_sop',
                    'cpt'      => 'ppb_alur_sop',
                    'keywords' => 'alur sop prosedur layanan akademik pengajuan surat ujian',
                ),
            ),
        ),
        'warta_data' => array(
            'title'      => '📰 Warta & Import Data',
            'short'      => 'Warta & Import',
            'icon'       => '📰',
            'slug'       => 'edit.php',
            'url'        => admin_url('edit.php'),
            'match_pages'=> array('ppb-excel-import'),
            'match_cpts' => array('post'),
            'children'   => array(
                array(
                    'label'    => '📰 Semua Warta & Berita',
                    'url'      => admin_url('edit.php'),
                    'slug'     => 'edit.php',
                    'cpt'      => 'post',
                    'keywords' => 'warta berita artikel pengumuman informasi liputan kegiatan',
                ),
                array(
                    'label'    => '➕ Tulis Berita Baru',
                    'url'      => admin_url('post-new.php'),
                    'slug'     => 'post-new.php',
                    'keywords' => 'tambah tulis berita baru artikel pengumuman',
                ),
                array(
                    'label'    => '🏷️ Kategori Berita',
                    'url'      => admin_url('edit-tags.php?taxonomy=category'),
                    'slug'     => 'edit-tags.php?taxonomy=category',
                    'keywords' => 'kategori berita topik label',
                ),
                array(
                    'label'    => '📥 Import / Export Excel & JSON',
                    'url'      => admin_url('admin.php?page=ppb-excel-import'),
                    'slug'     => 'ppb-excel-import',
                    'keywords' => 'import export excel csv json 16 dataset unduh unggah',
                ),
            ),
        ),
    );
}
}

/* ========================================================================== */
/* 2. REGISTRASI MENU UTAMA PORTAL & SUBMENU                                  */
/* ========================================================================== */
if (!function_exists('ppb_render_portal_settings_bridge')) {
function ppb_render_portal_settings_bridge() {
    if (function_exists('bk_fip_render_settings_page')) {
        bk_fip_render_settings_page();
    } else {
        echo '<div class="wrap"><h1>Pengaturan Beranda</h1><p>Silakan aktifkan tema BK FIP untuk mengelola pengaturan beranda.</p></div>';
    }
}
}

if (!function_exists('ppb_render_portal_dosen_bridge')) {
function ppb_render_portal_dosen_bridge() {
    if (function_exists('bk_fip_render_sync_dosen_page')) {
        bk_fip_render_sync_dosen_page();
    } else {
        echo '<div class="wrap"><h1>Data & Direktori Dosen</h1><p>Silakan aktifkan tema BK FIP untuk mengelola direktori dosen.</p></div>';
    }
}
}

if (!function_exists('ppb_register_portal_hub_menu')) {
function ppb_register_portal_hub_menu() {
    // 🏛️ SATU-SATUNYA MENU INDUK PPB FIP DI WP ADMIN
    add_menu_page(
        'Portal PPB FIP UNM',
        '🏛️ Portal PPB FIP',
        'edit_posts',
        'ppb-portal-hub',
        'ppb_render_portal_hub_dashboard',
        'dashicons-networking',
        20
    );

    // =========================================================================
    // 8 MENU KATEGORI UTAMA (TAMPIL DI SIDEBAR DENGAN SUB-SUBMENU FLYOUT/TAB)
    // =========================================================================

    // 1. Dashboard & Pencarian Cepat
    add_submenu_page(
        'ppb-portal-hub',
        'Pusat Kendali Portal PPB',
        '📊 Dashboard & Pencarian',
        'edit_posts',
        'ppb-portal-hub',
        'ppb_render_portal_hub_dashboard'
    );

    // 2. Beranda & Navigasi (Induk: Spotlight, Teks Beranda, Mega Menu, Navbar)
    add_submenu_page(
        'ppb-portal-hub',
        'Beranda & Navigasi Website',
        '🏠 Beranda & Navigasi',
        'manage_options',
        'ppb-spotlight-beranda',
        'ppb_render_spotlight_admin_page'
    );

    // 3. Dosen & Mata Kuliah (Induk: Direktori Dosen SINTA & Mata Kuliah OBE)
    add_submenu_page(
        'ppb-portal-hub',
        'Direktori Dosen & Mata Kuliah OBE',
        '👨‍🏫 Dosen & Mata Kuliah',
        'edit_posts',
        'bk-direktori-dosen',
        'ppb_render_portal_dosen_bridge'
    );

    // 4. Akademik, Riset & E-Lib (Induk: Dokumen Akademik, PNBP, Jurnal, Jadwal, E-Library)
    add_submenu_page(
        'ppb-portal-hub',
        'Akademik, Hibah PNBP, Jurnal, Jadwal & E-Library',
        '⚖️ Akademik, Riset & E-Lib',
        'edit_posts',
        'ppb-akademik-settings',
        'ppb_akademik_render_settings_page'
    );

    // 5. Mutu, LAMDIK & Survei (Induk: Akreditasi LAMDIK, Dokumen Mutu Drive, Survei GPM)
    add_submenu_page(
        'ppb-portal-hub',
        'Akreditasi LAMDIK, Dokumen Mutu & Survei GPM',
        '🏅 Mutu, LAMDIK & Survei',
        'edit_posts',
        'ppb-lamdik-settings',
        'ppb_render_lamdik_admin_page'
    );

    // 6. Kemahasiswaan & Alumni (Induk: Statistik Maba, Tracer, MBKM, Prestasi, Karya)
    add_submenu_page(
        'ppb-portal-hub',
        'Kemahasiswaan, Maba, Tracer Study, MBKM & Prestasi',
        '🏆 Kemahasiswaan & Alumni',
        'edit_posts',
        'ppb-kemahasiswaan-settings',
        'ppb_render_kemahasiswaan_settings_page'
    );

    // 7. Layanan, Lab & Sarpras (Induk: Layanan, Kegiatan Lab, Sarpras, Inventaris, Surat, SOP)
    add_submenu_page(
        'ppb-portal-hub',
        'Layanan Mahasiswa, Lab BK, Sarpras, Surat & SOP',
        '🏢 Layanan, Lab & Sarpras',
        'edit_posts',
        'ppb-layanan-settings',
        'ppb_render_layanan_settings_page'
    );

    // 8. Warta & Import Data (Induk: Berita & Import/Export Excel)
    add_submenu_page(
        'ppb-portal-hub',
        'Warta Berita & Import/Export Data',
        '📰 Warta & Import Data',
        'edit_posts',
        'edit.php'
    );

    // =========================================================================
    // SUB-HALAMAN ANAK (Tetap diregistrasikan agar izin akses WP valid 100%,
    // lalu otomatis dikelompokkan ke dalam Sub-Submenu & Tab Kategori di atas)
    // =========================================================================
    add_submenu_page(
        'ppb-portal-hub',
        'Registry Unit & Prodi Jurusan PPB',
        '🏛️ Unit & Prodi Jurusan PPB',
        'manage_options',
        'ppb-unit-registry',
        'ppb_render_unit_registry_admin_page'
    );

    add_submenu_page(
        'ppb-portal-hub',
        'Pengaturan Teks Beranda',
        '⚙️ Teks Beranda',
        'manage_options',
        'bk-pengaturan-beranda',
        'ppb_render_portal_settings_bridge'
    );

    add_submenu_page(
        'ppb-portal-hub',
        'Mata Kuliah OBE, RPS/SAP & Penugasan Dosen',
        '📚 Mata Kuliah & Dosen',
        'edit_posts',
        'ppb-mata-kuliah-manager',
        'ppb_render_mata_kuliah_admin_page'
    );

    add_submenu_page(
        'ppb-portal-hub',
        'Jadwal Ujian & Seminar',
        '📅 Jadwal Ujian & Seminar',
        'edit_posts',
        'edit.php?post_type=ppb_jadwal'
    );

    add_submenu_page(
        'ppb-portal-hub',
        'E-Library & Kurasi Modul BK',
        '📚 E-Library & Modul',
        'edit_posts',
        'ppb-elibrary-manager',
        'ppb_render_elibrary_admin_page'
    );

    add_submenu_page(
        'ppb-portal-hub',
        'Dokumen Mutu (Google Drive)',
        '📂 Dokumen Mutu (Drive)',
        'edit_posts',
        'edit.php?post_type=ppb_dokumen'
    );

    add_submenu_page(
        'ppb-portal-hub',
        'Rekap & Analitik Survei Kepuasan Mahasiswa (GPM)',
        '📊 Survei Kepuasan GPM',
        'edit_posts',
        'ppb-survei-manager',
        'ppb_render_survei_admin_page'
    );

    add_submenu_page(
        'ppb-portal-hub',
        'Prestasi Mahasiswa',
        '🏅 Prestasi Mahasiswa',
        'edit_posts',
        'edit.php?post_type=ppb_prestasi'
    );

    add_submenu_page(
        'ppb-portal-hub',
        'Karya & Inovasi Mahasiswa',
        '🎨 Karya Mahasiswa',
        'edit_posts',
        'edit.php?post_type=ppb_karya'
    );

    add_submenu_page(
        'ppb-portal-hub',
        'Inventaris Alat & Tes Lab BK',
        '🧪 Inventaris Alat Lab',
        'edit_posts',
        'edit.php?post_type=ppb_inventaris_lab'
    );

    add_submenu_page(
        'ppb-portal-hub',
        'Template Surat & Formulir',
        '📄 Template Surat',
        'edit_posts',
        'edit.php?post_type=ppb_template_dok'
    );

    add_submenu_page(
        'ppb-portal-hub',
        'Alur SOP Layanan Akademik',
        '🔄 Alur SOP Layanan',
        'edit_posts',
        'edit.php?post_type=ppb_alur_sop'
    );

    add_submenu_page(
        'ppb-portal-hub',
        'Import / Export Data Massal (Excel & JSON)',
        '📥 Import / Export Excel & JSON',
        'edit_posts',
        'ppb-excel-import',
        'ppb_render_excel_import_admin_page'
    );

    add_submenu_page(
        'ppb-portal-hub',
        'Kelola Navigasi Navbar',
        '🧭 Navigasi Menu Navbar',
        'manage_options',
        'nav-menus.php'
    );
}
}
add_action('admin_menu', 'ppb_register_portal_hub_menu', 9);

// Sidebar Cleanup Hook: Hapus semua menu ganda atau menu liar di luar Portal PPB FIP
add_action('admin_menu', function() {
    remove_menu_page('edit.php');
    remove_menu_page('bk-pengaturan-beranda');
    remove_menu_page('ppb-spotlight-beranda');
    remove_menu_page('bk-direktori-dosen');
    remove_menu_page('ppb-elibrary-manager');
    remove_menu_page('ppb-survei-manager');
    remove_menu_page('edit.php?post_type=dosen');
    remove_menu_page('edit.php?post_type=ppb_dosen');
    remove_menu_page('edit.php?post_type=ppb_dokumen');
    remove_menu_page('edit.php?post_type=ppb_jadwal');
    remove_menu_page('edit.php?post_type=ppb_prestasi');
    remove_menu_page('edit.php?post_type=ppb_karya');
    remove_menu_page('edit.php?post_type=ppb_template_dok');
    remove_menu_page('edit.php?post_type=ppb_inventaris_lab');
    remove_menu_page('edit.php?post_type=ppb_alur_sop');
}, 9999);

// Pertahankan highlight menu induk "🏛️ Portal PPB FIP" saat mengedit CPT atau sub-halaman
add_filter('parent_file', function($parent_file) {
    global $current_screen, $pagenow;
    $cpts = array('ppb_jadwal', 'ppb_dokumen', 'ppb_prestasi', 'ppb_karya', 'ppb_inventaris_lab', 'ppb_template_dok', 'ppb_alur_sop', 'ppb_dosen', 'dosen');
    if (!empty($current_screen->post_type) && in_array($current_screen->post_type, $cpts, true)) {
        return 'ppb-portal-hub';
    }
    if (in_array($pagenow, array('edit.php', 'post-new.php'), true) && (empty($current_screen->post_type) || $current_screen->post_type === 'post')) {
        return 'ppb-portal-hub';
    }
    return $parent_file;
});

/* ========================================================================== */
/* 3. DETEKSI KATEGORI AKTIF & RENDER TOP UNIFIED SUB-MENU SWITCHER           */
/* ========================================================================== */
if (!function_exists('ppb_detect_active_portal_group')) {
function ppb_detect_active_portal_group() {
    global $pagenow, $typenow;
    $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
    $cpt  = $typenow;
    if (empty($cpt) && isset($_GET['post_type'])) {
        $cpt = sanitize_key(wp_unslash($_GET['post_type']));
    }
    if (empty($cpt) && isset($_GET['post'])) {
        $post_id = absint($_GET['post']);
        if ($post_id) {
            $cpt = get_post_type($post_id);
        }
    }

    $hierarchy = ppb_get_portal_menu_hierarchy();

    foreach ($hierarchy as $group_key => $group) {
        if (!empty($page) && in_array($page, $group['match_pages'], true)) {
            return $group_key;
        }
        if (!empty($cpt) && !empty($group['match_cpts']) && in_array($cpt, $group['match_cpts'], true)) {
            return $group_key;
        }
        if (!empty($group['match_files']) && in_array($pagenow, $group['match_files'], true)) {
            return $group_key;
        }
    }

    if (in_array($pagenow, array('edit.php', 'post-new.php', 'post.php'), true) && (empty($cpt) || $cpt === 'post')) {
        return 'warta_data';
    }

    return '';
}
}

// Tampilkan Unified Sub-Menu Switcher Bar di bagian atas semua halaman Portal PPB FIP
add_action('in_admin_header', function() {
    $active_group_key = ppb_detect_active_portal_group();
    if (empty($active_group_key)) return;

    $hierarchy = ppb_get_portal_menu_hierarchy();
    if (empty($hierarchy[$active_group_key])) return;

    $active_group = $hierarchy[$active_group_key];
    $current_page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
    $current_tab  = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : '';
    $current_dataset = isset($_GET['dataset']) ? sanitize_key(wp_unslash($_GET['dataset'])) : '';
    global $pagenow, $typenow;
    $current_cpt  = $typenow ?: (isset($_GET['post_type']) ? sanitize_key(wp_unslash($_GET['post_type'])) : '');
    if (empty($current_cpt) && isset($_GET['post'])) {
        $current_cpt = get_post_type(absint($_GET['post']));
    }
    ?>
    <div id="ppb-unified-topbar" class="ppb-unified-topbar">
        <div class="ppb-topbar-main">
            <div class="ppb-topbar-left">
                <a href="<?php echo esc_url(admin_url('admin.php?page=ppb-portal-hub')); ?>" class="ppb-topbar-brand" title="Kembali ke Dashboard Portal PPB FIP">
                    <span>🏛️ Portal PPB FIP</span>
                </a>
                <span class="ppb-topbar-sep">›</span>
                <!-- Dropdown Kategori Cepat -->
                <div class="ppb-topbar-cat-switcher">
                    <button type="button" class="ppb-topbar-cat-btn" id="ppb-topbar-cat-btn">
                        <span><?php echo esc_html($active_group['title']); ?></span>
                        <span class="ppb-caret">▾</span>
                    </button>
                    <div class="ppb-topbar-cat-menu" id="ppb-topbar-cat-menu">
                        <?php foreach ($hierarchy as $g_key => $g_info) : ?>
                            <a href="<?php echo esc_url($g_info['url']); ?>" class="ppb-topbar-cat-item <?php echo $g_key === $active_group_key ? 'is-active' : ''; ?>">
                                <span><?php echo esc_html($g_info['title']); ?></span>
                                <small><?php echo count($g_info['children']); ?> sub-menu</small>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Kolom Pencarian Cepat Lintas Menu -->
            <div class="ppb-topbar-search-wrap">
                <span class="ppb-topbar-search-icon">🔍</span>
                <input type="search" id="ppb-topbar-quick-search" class="ppb-topbar-search-input" placeholder="Cari cepat menu / fitur... (Ctrl+K)" autocomplete="off">
                <div id="ppb-topbar-search-results" class="ppb-topbar-search-results" style="display:none;"></div>
            </div>
        </div>

        <!-- Baris Sub-Submenu Tabs dalam Kategori Aktif -->
        <div class="ppb-topbar-subtabs">
            <span class="ppb-subtabs-label">Sub-Menu Kategori:</span>
            <?php foreach ($active_group['children'] as $idx => $child) :
                $is_current = false;
                if (!empty($child['cpt']) && $current_cpt === $child['cpt']) {
                    $is_current = true;
                } elseif (!empty($child['slug']) && $current_page === $child['slug']) {
                    if (!empty($child['tab'])) {
                        $is_current = ($current_tab === $child['tab']) || (empty($current_tab) && $idx === 0);
                    } elseif ($child['slug'] === 'ppb-excel-import-matkul') {
                        $is_current = ($current_page === 'ppb-excel-import' && $current_dataset === 'matkul');
                    } else {
                        $is_current = true;
                    }
                } elseif ($child['slug'] === 'nav-menus.php' && $pagenow === 'nav-menus.php') {
                    $is_current = true;
                } elseif ($child['slug'] === 'post-new.php' && $pagenow === 'post-new.php' && empty($current_cpt)) {
                    $is_current = true;
                } elseif ($child['slug'] === 'edit.php' && $pagenow === 'edit.php' && empty($current_cpt)) {
                    $is_current = true;
                }
            ?>
                <a href="<?php echo esc_url($child['url']); ?>" class="ppb-subtab-pill <?php echo $is_current ? 'is-active' : ''; ?>">
                    <?php echo esc_html($child['label']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
});

/* ========================================================================== */
/* 4. SIDEBAR SUB-SUBMENU (FLYOUT + ACCORDION) & PENCARIAN SIDEBAR WP-ADMIN   */
/* ========================================================================== */
add_action('admin_head', function() {
    ?>
    <style id="ppb-portal-menu-organizer-css">
        /* Sembunyikan item anak datar di sidebar agar hanya 8 Kategori Induk yang tampil */
        #toplevel_page_ppb-portal-hub .wp-submenu > li > a[href*="page=ppb-unit-registry"],
        #toplevel_page_ppb-portal-hub .wp-submenu > li > a[href*="page=bk-pengaturan-beranda"],
        #toplevel_page_ppb-portal-hub .wp-submenu > li > a[href*="page=ppb-mata-kuliah-manager"],
        #toplevel_page_ppb-portal-hub .wp-submenu > li > a[href*="post_type=ppb_jadwal"],
        #toplevel_page_ppb-portal-hub .wp-submenu > li > a[href*="page=ppb-elibrary-manager"],
        #toplevel_page_ppb-portal-hub .wp-submenu > li > a[href*="post_type=ppb_dokumen"],
        #toplevel_page_ppb-portal-hub .wp-submenu > li > a[href*="page=ppb-survei-manager"],
        #toplevel_page_ppb-portal-hub .wp-submenu > li > a[href*="post_type=ppb_prestasi"],
        #toplevel_page_ppb-portal-hub .wp-submenu > li > a[href*="post_type=ppb_karya"],
        #toplevel_page_ppb-portal-hub .wp-submenu > li > a[href*="post_type=ppb_inventaris_lab"],
        #toplevel_page_ppb-portal-hub .wp-submenu > li > a[href*="post_type=ppb_template_dok"],
        #toplevel_page_ppb-portal-hub .wp-submenu > li > a[href*="post_type=ppb_alur_sop"],
        #toplevel_page_ppb-portal-hub .wp-submenu > li > a[href*="page=ppb-excel-import"],
        #toplevel_page_ppb-portal-hub .wp-submenu > li > a[href="nav-menus.php"],
        #toplevel_page_ppb-portal-hub .wp-submenu > li > a[href*="page=ppb-mega-menu-manager"],
        #toplevel_page_ppb-portal-hub .wp-submenu > li > a[href*="page=wfie-settings"] {
            display: none !important;
        }

        /* Kotak pencarian cepat di dalam sidebar Portal PPB FIP */
        .ppb-sidebar-search-li {
            padding: 6px 10px 8px !important;
            margin: 0 !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            list-style: none;
        }
        .ppb-sidebar-search-input {
            width: 100% !important;
            background: rgba(255, 255, 255, 0.1) !important;
            border: 1px solid rgba(245, 184, 46, 0.45) !important;
            border-radius: 6px !important;
            color: #ffffff !important;
            font-size: 11.5px !important;
            padding: 5px 8px !important;
            line-height: 1.4 !important;
            box-sizing: border-box !important;
        }
        .ppb-sidebar-search-input::placeholder {
            color: rgba(255, 255, 255, 0.65) !important;
        }
        .ppb-sidebar-search-input:focus {
            background: rgba(13, 59, 34, 0.95) !important;
            border-color: #F5B82E !important;
            outline: none !important;
            box-shadow: 0 0 0 1px #F5B82E !important;
        }

        /* Item Induk Kategori di Sidebar */
        #toplevel_page_ppb-portal-hub .wp-submenu > li.ppb-cat-parent {
            position: relative !important;
        }
        #toplevel_page_ppb-portal-hub .wp-submenu > li.ppb-cat-parent > a {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            gap: 4px !important;
            padding-right: 8px !important;
            font-weight: 500 !important;
        }
        #toplevel_page_ppb-portal-hub .wp-submenu > li.ppb-cat-parent.ppb-cat-active > a {
            color: #F5B82E !important;
            font-weight: 700 !important;
            background: rgba(245, 184, 46, 0.12) !important;
        }
        .ppb-sub-toggle-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            border-radius: 4px;
            font-size: 10px;
            color: rgba(255, 255, 255, 0.7);
            background: rgba(255, 255, 255, 0.08);
            cursor: pointer;
            flex-shrink: 0;
            transition: all 0.15s ease;
        }
        .ppb-sub-toggle-btn:hover {
            background: #F5B82E;
            color: #0D3B22;
        }
        li.ppb-cat-open > a .ppb-sub-toggle-btn {
            transform: rotate(90deg);
            background: rgba(245, 184, 46, 0.25);
            color: #F5B82E;
        }

        /* Sub-Submenu Accordion (di dalam sidebar) & Flyout (saat hover di desktop) */
        ul.ppb-sub-submenu {
            display: none;
            margin: 2px 0 6px 0 !important;
            padding: 4px 0 4px 12px !important;
            background: rgba(0, 0, 0, 0.24) !important;
            border-left: 2px solid #F5B82E !important;
            list-style: none !important;
        }
        li.ppb-cat-open > ul.ppb-sub-submenu {
            display: block !important;
        }
        /* Flyout Sub-Submenu saat kursor diarahkan (jika belum sedang terbuka inline) */
        @media screen and (min-width: 783px) {
            #toplevel_page_ppb-portal-hub .wp-submenu > li.ppb-cat-parent:not(.ppb-cat-open):hover > ul.ppb-sub-submenu {
                display: block !important;
                position: absolute !important;
                left: 100% !important;
                top: -4px !important;
                min-width: 245px !important;
                background: #0D3B22 !important;
                border: 1px solid rgba(245, 184, 46, 0.45) !important;
                border-left: 3px solid #F5B82E !important;
                border-radius: 0 10px 10px 0 !important;
                padding: 8px 0 !important;
                margin: 0 !important;
                box-shadow: 0 12px 28px rgba(0, 0, 0, 0.4) !important;
                z-index: 99999 !important;
            }
        }
        ul.ppb-sub-submenu > li {
            margin: 0 !important;
            padding: 0 !important;
        }
        ul.ppb-sub-submenu > li > a {
            display: block !important;
            padding: 5px 10px !important;
            font-size: 12px !important;
            line-height: 1.35 !important;
            color: rgba(255, 255, 255, 0.82) !important;
            text-decoration: none !important;
            transition: all 0.12s ease !important;
        }
        ul.ppb-sub-submenu > li > a:hover,
        ul.ppb-sub-submenu > li > a.is-current-sub {
            color: #F5B82E !important;
            background: rgba(255, 255, 255, 0.08) !important;
            font-weight: 600 !important;
        }
        .ppb-sub-flyout-header {
            display: none;
            padding: 4px 10px 6px;
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #F5B82E;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            margin-bottom: 4px;
        }
        @media screen and (min-width: 783px) {
            #toplevel_page_ppb-portal-hub .wp-submenu > li.ppb-cat-parent:not(.ppb-cat-open):hover .ppb-sub-flyout-header {
                display: block;
            }
        }

        /* Top Unified Sub-Navigation Bar di atas halaman admin */
        .ppb-unified-topbar {
            background: #ffffff;
            border: 1px solid #d4e3d8;
            border-left: 4px solid #0D3B22;
            border-radius: 12px;
            margin: 16px 20px 8px 2px;
            padding: 12px 18px;
            box-shadow: 0 2px 8px rgba(13, 59, 34, 0.05);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        .ppb-topbar-main {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            flex-wrap: wrap;
            padding-bottom: 10px;
            border-bottom: 1px solid #f1f5f9;
        }
        .ppb-topbar-left {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .ppb-topbar-brand {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 700;
            color: #0D3B22 !important;
            text-decoration: none !important;
            background: #E8F0EA;
            padding: 5px 11px;
            border-radius: 7px;
        }
        .ppb-topbar-brand:hover {
            background: #d4e3d8;
        }
        .ppb-topbar-sep {
            color: #94a3b8;
            font-weight: bold;
        }
        .ppb-topbar-cat-switcher {
            position: relative;
        }
        .ppb-topbar-cat-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #0D3B22;
            color: #ffffff;
            border: 1px solid #0D3B22;
            padding: 5px 12px;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }
        .ppb-topbar-cat-btn .ppb-caret {
            color: #F5B82E;
            font-size: 11px;
        }
        .ppb-topbar-cat-menu {
            display: none;
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            min-width: 270px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.15);
            padding: 6px;
            z-index: 99999;
        }
        .ppb-topbar-cat-menu.is-open {
            display: block;
        }
        .ppb-topbar-cat-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 10px;
            border-radius: 7px;
            text-decoration: none !important;
            color: #1e293b !important;
            font-size: 12.5px;
            font-weight: 600;
        }
        .ppb-topbar-cat-item small {
            color: #64748b;
            font-weight: 500;
            font-size: 11px;
        }
        .ppb-topbar-cat-item:hover,
        .ppb-topbar-cat-item.is-active {
            background: #E8F0EA;
            color: #0D3B22 !important;
        }

        /* Quick Search di Topbar */
        .ppb-topbar-search-wrap {
            position: relative;
            min-width: 260px;
            flex: 0 1 320px;
        }
        .ppb-topbar-search-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 12px;
            pointer-events: none;
        }
        .ppb-topbar-search-input {
            width: 100%;
            padding: 5px 12px 5px 30px !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            font-size: 12.5px !important;
            background: #f8fafc !important;
        }
        .ppb-topbar-search-input:focus {
            background: #ffffff !important;
            border-color: #0D3B22 !important;
            box-shadow: 0 0 0 2px rgba(13, 59, 34, 0.12) !important;
            outline: none !important;
        }
        .ppb-topbar-search-results {
            position: absolute;
            top: calc(100% + 5px);
            right: 0;
            left: 0;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.16);
            max-height: 320px;
            overflow-y: auto;
            z-index: 100000;
            padding: 6px;
        }
        .ppb-search-res-item {
            display: flex;
            flex-direction: column;
            padding: 7px 10px;
            border-radius: 7px;
            text-decoration: none !important;
            color: #0f172a !important;
            font-size: 12.5px;
            font-weight: 600;
        }
        .ppb-search-res-item small {
            font-size: 11px;
            color: #64748b;
            font-weight: 400;
        }
        .ppb-search-res-item:hover {
            background: #E8F0EA;
            color: #0D3B22 !important;
        }

        /* Sub-Submenu Pill Tabs */
        .ppb-topbar-subtabs {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            padding-top: 10px;
        }
        .ppb-subtabs-label {
            font-size: 11.5px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-right: 4px;
        }
        .ppb-subtab-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 12px;
            border-radius: 999px;
            font-size: 12.5px;
            font-weight: 600;
            text-decoration: none !important;
            color: #334155 !important;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            transition: all 0.15s ease;
        }
        .ppb-subtab-pill:hover {
            background: #E8F0EA;
            color: #0D3B22 !important;
            border-color: #cbd5e1;
        }
        .ppb-subtab-pill.is-active {
            background: #F5B82E;
            color: #0D3B22 !important;
            border-color: #e5a925;
            font-weight: 700;
            box-shadow: 0 2px 6px rgba(245, 184, 46, 0.3);
        }
    </style>
    <?php
});

add_action('admin_footer', function() {
    $hierarchy        = ppb_get_portal_menu_hierarchy();
    $active_group_key = ppb_detect_active_portal_group();
    ?>
    <script id="ppb-portal-menu-organizer-js">
    (function() {
        var hierarchy = <?php echo wp_json_encode($hierarchy); ?>;
        var activeGroupKey = <?php echo wp_json_encode($active_group_key); ?>;
        var currentUrl = window.location.href;

        // 1. Susun Sub-Submenu (Flyout + Accordion) & Pencarian Cepat di Sidebar WP-Admin
        var topLi = document.getElementById('toplevel_page_ppb-portal-hub');
        if (topLi) {
            var submenuUl = topLi.querySelector('ul.wp-submenu');
            if (submenuUl) {
                // Sisipkan input pencarian di atas daftar menu Portal PPB FIP
                var searchLi = document.createElement('li');
                searchLi.className = 'ppb-sidebar-search-li';
                searchLi.innerHTML = '<input type="search" class="ppb-sidebar-search-input" placeholder="🔍 Cari menu / sub-menu..." aria-label="Cari menu Portal PPB FIP">';
                var headLi = submenuUl.querySelector('.wp-submenu-head');
                if (headLi && headLi.nextSibling) {
                    submenuUl.insertBefore(searchLi, headLi.nextSibling);
                } else {
                    submenuUl.insertBefore(searchLi, submenuUl.firstChild);
                }

                var sidebarSearchInput = searchLi.querySelector('input');

                // Pasangkan Sub-Submenu ke masing-masing dari 8 Kategori Induk
                Object.keys(hierarchy).forEach(function(gKey) {
                    var group = hierarchy[gKey];
                    var parentLink = null;
                    var links = submenuUl.querySelectorAll(':scope > li > a');
                    for (var i = 0; i < links.length; i++) {
                        var href = links[i].getAttribute('href') || '';
                        if (
                            (group.slug === 'edit.php' && href === 'edit.php') ||
                            (group.slug !== 'edit.php' && href.indexOf('page=' + group.slug) !== -1)
                        ) {
                            parentLink = links[i];
                            break;
                        }
                    }
                    if (!parentLink) return;

                    var parentLi = parentLink.parentElement;
                    parentLi.classList.add('ppb-cat-parent');
                    parentLi.setAttribute('data-ppb-group', gKey);

                    // Tambahkan tombol panah untuk buka/tutup sub-submenu
                    if (group.children && group.children.length > 0) {
                        var toggleBtn = document.createElement('span');
                        toggleBtn.className = 'ppb-sub-toggle-btn';
                        toggleBtn.title = 'Klik untuk membuka/menutup sub-submenu (' + group.children.length + ' item)';
                        toggleBtn.textContent = '▸';
                        toggleBtn.addEventListener('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            parentLi.classList.toggle('ppb-cat-open');
                        });
                        parentLink.appendChild(toggleBtn);

                        // Buat elemen <ul class="ppb-sub-submenu">
                        var subUl = document.createElement('ul');
                        subUl.className = 'ppb-sub-submenu';

                        var headerLi = document.createElement('li');
                        headerLi.className = 'ppb-sub-flyout-header';
                        headerLi.textContent = group.title;
                        subUl.appendChild(headerLi);

                        group.children.forEach(function(child) {
                            var cLi = document.createElement('li');
                            cLi.setAttribute('data-keywords', (child.label + ' ' + (child.keywords || '')).toLowerCase());
                            var cA = document.createElement('a');
                            cA.href = child.url;
                            cA.textContent = child.label;
                            if (currentUrl.indexOf(child.url) !== -1) {
                                cA.classList.add('is-current-sub');
                            }
                            cLi.appendChild(cA);
                            subUl.appendChild(cLi);
                        });

                        parentLi.appendChild(subUl);
                    }

                    // Jika grup ini sedang aktif, tandai & buka otomatis sub-submenunya
                    if (activeGroupKey && activeGroupKey === gKey) {
                        parentLi.classList.add('ppb-cat-active', 'ppb-cat-open', 'current');
                        parentLink.classList.add('current');
                        topLi.classList.remove('wp-not-current-submenu');
                        topLi.classList.add('wp-has-current-submenu', 'wp-menu-open');
                    }
                });

                // Filter real-time saat mengetik di kotak pencarian sidebar
                if (sidebarSearchInput) {
                    sidebarSearchInput.addEventListener('input', function() {
                        var q = this.value.trim().toLowerCase();
                        var catItems = submenuUl.querySelectorAll(':scope > li.ppb-cat-parent');
                        catItems.forEach(function(catLi) {
                            var gKey = catLi.getAttribute('data-ppb-group');
                            var group = hierarchy[gKey];
                            var subItems = catLi.querySelectorAll('ul.ppb-sub-submenu > li[data-keywords]');
                            if (!q) {
                                catLi.style.display = '';
                                catLi.classList.toggle('ppb-cat-open', activeGroupKey === gKey);
                                subItems.forEach(function(si) { si.style.display = ''; });
                                return;
                            }
                            var groupMatches = group.title.toLowerCase().indexOf(q) !== -1;
                            var anyChildMatch = false;
                            subItems.forEach(function(si) {
                                var kw = si.getAttribute('data-keywords') || '';
                                if (groupMatches || kw.indexOf(q) !== -1) {
                                    si.style.display = '';
                                    anyChildMatch = true;
                                } else {
                                    si.style.display = 'none';
                                }
                            });
                            if (groupMatches || anyChildMatch) {
                                catLi.style.display = '';
                                catLi.classList.add('ppb-cat-open');
                            } else {
                                catLi.style.display = 'none';
                                catLi.classList.remove('ppb-cat-open');
                            }
                        });
                    });

                    sidebarSearchInput.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter') {
                            var firstVisible = submenuUl.querySelector('li.ppb-cat-parent:not([style*="display: none"]) ul.ppb-sub-submenu > li[data-keywords]:not([style*="display: none"]) > a');
                            if (firstVisible) {
                                e.preventDefault();
                                window.location.href = firstVisible.href;
                            }
                        }
                    });
                }
            }
        }

        // 2. Interaksi Top Unified Sub-Menu Bar (Dropdown Kategori & Quick Search Ctrl+K)
        var catBtn = document.getElementById('ppb-topbar-cat-btn');
        var catMenu = document.getElementById('ppb-topbar-cat-menu');
        if (catBtn && catMenu) {
            catBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                catMenu.classList.toggle('is-open');
            });
            document.addEventListener('click', function() {
                catMenu.classList.remove('is-open');
            });
        }

        var topSearchInput = document.getElementById('ppb-topbar-quick-search');
        var topSearchResults = document.getElementById('ppb-topbar-search-results');
        if (topSearchInput && topSearchResults) {
            var allItems = [];
            Object.keys(hierarchy).forEach(function(gKey) {
                var g = hierarchy[gKey];
                (g.children || []).forEach(function(ch) {
                    allItems.push({
                        label: ch.label,
                        url: ch.url,
                        group: g.title,
                        search: (ch.label + ' ' + g.title + ' ' + (ch.keywords || '')).toLowerCase()
                    });
                });
            });

            topSearchInput.addEventListener('input', function() {
                var q = this.value.trim().toLowerCase();
                if (!q) {
                    topSearchResults.style.display = 'none';
                    return;
                }
                var matches = allItems.filter(function(it) {
                    return it.search.indexOf(q) !== -1;
                });
                if (matches.length === 0) {
                    topSearchResults.innerHTML = '<div style="padding:10px;font-size:12px;color:#64748b;">Tidak ada menu yang cocok dengan "' + q.replace(/[<>&"]/g, '') + '".</div>';
                } else {
                    topSearchResults.innerHTML = matches.map(function(m) {
                        return '<a href="' + m.url + '" class="ppb-search-res-item"><span>' + m.label + '</span><small>' + m.group + '</small></a>';
                    }).join('');
                }
                topSearchResults.style.display = 'block';
            });

            topSearchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    var firstLink = topSearchResults.querySelector('a.ppb-search-res-item');
                    if (firstLink) {
                        e.preventDefault();
                        window.location.href = firstLink.href;
                    }
                }
            });

            document.addEventListener('click', function(e) {
                if (!e.target.closest('.ppb-topbar-search-wrap')) {
                    topSearchResults.style.display = 'none';
                }
            });

            // Shortcut Ctrl+K / Cmd+K untuk fokus ke kolom pencarian menu
            document.addEventListener('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                    e.preventDefault();
                    topSearchInput.focus();
                    topSearchInput.select();
                }
            });
        }
    })();
    </script>
    <?php
}, 99);


/* ========================================================================== */
/* 5. RENDER DASHBOARD PUSAT KENDALI (DENGAN PENCARIAN & FILTER KATEGORI)     */
/* ========================================================================== */
if (!function_exists('ppb_render_portal_hub_dashboard')) {
function ppb_render_portal_hub_dashboard() {
    $count_dosen   = wp_count_posts('ppb_dosen')->publish ?: (wp_count_posts('dosen')->publish ?: 25);
    $count_dokumen = wp_count_posts('ppb_dokumen')->publish ?: 8;
    $count_jadwal  = wp_count_posts('ppb_jadwal')->publish ?: 5;
    $count_berita  = wp_count_posts('post')->publish ?: 2;
    $mk_list       = function_exists('ppb_get_mata_kuliah_list') ? ppb_get_mata_kuliah_list(true) : array();
    $count_matkul  = is_array($mk_list) ? count($mk_list) : 0;
    $lamdik_cfg    = function_exists('ppb_get_lamdik_settings') ? ppb_get_lamdik_settings() : array();
    $lamdik_filled = 0;
    if (!empty($lamdik_cfg['prodi']) && is_array($lamdik_cfg['prodi'])) {
        $total_slots = 0;
        foreach ($lamdik_cfg['prodi'] as $p_cfg) {
            if (!empty($p_cfg['kriteria']) && is_array($p_cfg['kriteria'])) {
                foreach ($p_cfg['kriteria'] as $kr) {
                    $total_slots++;
                    $has_link = !empty($kr['drive_url']);
                    if (!$has_link && !empty($kr['eviden_links']) && is_array($kr['eviden_links'])) {
                        foreach ($kr['eviden_links'] as $ev) {
                            if (!empty($ev['url'])) { $has_link = true; break; }
                        }
                    }
                    if ($has_link) $lamdik_filled++;
                }
            }
        }
        $lamdik_total_slots = $total_slots > 0 ? $total_slots : 27;
    } else {
        $lamdik_total_slots = 9;
        if (!empty($lamdik_cfg['kriteria']) && is_array($lamdik_cfg['kriteria'])) {
            foreach ($lamdik_cfg['kriteria'] as $kr) {
                if (!empty($kr['drive_url'])) $lamdik_filled++;
            }
        }
    }
    $elib_items    = function_exists('ppb_get_elibrary_items') ? ppb_get_elibrary_items() : array();
    $count_elib    = is_array($elib_items) && !empty($elib_items) ? count($elib_items) : 6;
    $elib_subs     = function_exists('ppb_get_elibrary_submissions') ? ppb_get_elibrary_submissions() : array();
    $pending_elib  = 0;
    if (is_array($elib_subs)) {
        foreach ($elib_subs as $sub) {
            if (empty($sub['status']) || $sub['status'] === 'pending') {
                $pending_elib++;
            }
        }
    }
    $count_inventaris = wp_count_posts('ppb_inventaris_lab')->publish ?: 7;
    $survei_subs      = function_exists('ppb_get_survei_submissions') ? ppb_get_survei_submissions() : array();
    $count_survei     = is_array($survei_subs) ? count($survei_subs) : 0;
    $survei_summary   = function_exists('ppb_calculate_survei_summary') ? ppb_calculate_survei_summary($survei_subs) : array('ikm_pct' => 0);
    ?>
    <div class="wrap ppb-hub-wrap">
        <style>
            .ppb-hub-wrap { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif; max-width: 1240px; margin: 20px auto; color: #1e293b; }
            .ppb-header-card { background: linear-gradient(135deg, #0D3B22 0%, #185333 100%); color: #ffffff; padding: 32px; border-radius: 16px; margin-bottom: 24px; box-shadow: 0 10px 25px rgba(13, 59, 34, 0.2); position: relative; overflow: hidden; }
            .ppb-header-card::after { content: "PPB"; position: absolute; right: 20px; bottom: -20px; font-size: 110px; font-weight: 900; color: rgba(255, 255, 255, 0.04); pointer-events: none; }
            .ppb-header-badge { display: inline-flex; align-items: center; gap: 8px; background: rgba(245, 184, 46, 0.18); border: 1px solid rgba(245, 184, 46, 0.4); color: #F5B82E; padding: 4px 14px; border-radius: 20px; font-size: 11px; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; margin-bottom: 12px; }
            .ppb-header-title { font-size: 26px; font-weight: 700; margin: 0 0 8px 0; color: #ffffff; line-height: 1.25; }
            .ppb-header-subtitle { font-size: 14px; color: rgba(255, 255, 255, 0.85); margin: 0; max-width: 780px; line-height: 1.6; }

            /* Bar Pencarian Cepat & Filter Kategori di Dashboard */
            .ppb-filter-bar { background: #ffffff; border: 1px solid #cbd5e1; border-radius: 14px; padding: 16px 20px; margin-bottom: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); display: flex; flex-direction: column; gap: 14px; }
            .ppb-search-box-wrap { position: relative; width: 100%; }
            .ppb-search-box-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); font-size: 16px; pointer-events: none; }
            .ppb-hub-search-input { width: 100%; padding: 11px 16px 11px 42px !important; font-size: 14px !important; border: 2px solid #cbd5e1 !important; border-radius: 10px !important; background: #f8fafc !important; color: #0f172a !important; transition: all 0.15s ease; }
            .ppb-hub-search-input:focus { background: #ffffff !important; border-color: #0D3B22 !important; box-shadow: 0 0 0 3px rgba(13, 59, 34, 0.12) !important; outline: none !important; }
            .ppb-cat-pills { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
            .ppb-cat-pill { display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 999px; font-size: 12.5px; font-weight: 600; background: #f1f5f9; color: #334155; border: 1px solid #e2e8f0; cursor: pointer; transition: all 0.15s ease; }
            .ppb-cat-pill:hover { background: #E8F0EA; color: #0D3B22; }
            .ppb-cat-pill.is-active { background: #0D3B22; color: #ffffff; border-color: #0D3B22; }

            /* Metrik Ringkas */
            .ppb-metrics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(185px, 1fr)); gap: 16px; margin-bottom: 28px; }
            .ppb-metric-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 5px rgba(0,0,0,0.03); transition: transform 0.15s ease; }
            .ppb-metric-card:hover { transform: translateY(-2px); box-shadow: 0 6px 14px rgba(0,0,0,0.06); }
            .ppb-metric-info span { display: block; font-size: 11.5px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em; }
            .ppb-metric-info strong { display: block; font-size: 24px; font-weight: 800; color: #0D3B22; margin-top: 2px; }
            .ppb-metric-icon { width: 42px; height: 42px; border-radius: 10px; background: #E8F0EA; color: #0D3B22; display: flex; align-items: center; justify-content: center; font-size: 19px; flex-shrink: 0; }

            /* Grid Kartu Aksi */
            .ppb-actions-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 20px; margin-bottom: 32px; }
            .ppb-action-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 24px; box-shadow: 0 4px 10px rgba(0,0,0,0.03); display: flex; flex-direction: column; justify-content: space-between; transition: all 0.2s ease; position: relative; }
            .ppb-action-card:hover { border-color: #cbd5e1; box-shadow: 0 8px 20px rgba(0,0,0,0.06); }
            .ppb-card-cat-badge { display: inline-block; font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: #0D3B22; background: #E8F0EA; padding: 3px 9px; border-radius: 999px; margin-bottom: 10px; }
            .ppb-card-top { display: flex; align-items: flex-start; gap: 14px; margin-bottom: 14px; }
            .ppb-card-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0; }
            .ppb-card-icon.green { background: #E8F0EA; color: #0D3B22; }
            .ppb-card-icon.gold { background: #FEF3C7; color: #92400E; }
            .ppb-card-icon.blue { background: #E0F2FE; color: #0369A1; }
            .ppb-card-icon.purple { background: #F3E8FF; color: #6B21A8; }
            .ppb-card-icon.orange { background: #FFEDD5; color: #C2410C; }
            .ppb-card-icon.dark { background: #0D3B22; color: #F5B82E; }
            .ppb-card-text h3 { font-size: 17px; font-weight: 700; margin: 0 0 6px 0; color: #0f172a; }
            .ppb-card-text p { font-size: 13px; color: #64748b; margin: 0; line-height: 1.5; }
            .ppb-card-btns { display: flex; gap: 10px; margin-top: 18px; border-top: 1px solid #f1f5f9; padding-top: 16px; flex-wrap: wrap; }
            
            .ppb-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 8px 16px; border-radius: 8px; font-size: 12.5px; font-weight: 600; text-decoration: none; transition: all 0.15s ease; cursor: pointer; }
            .ppb-btn-primary { background: #0D3B22; color: #ffffff !important; border: 1px solid #0D3B22; }
            .ppb-btn-primary:hover { background: #185333; }
            .ppb-btn-gold { background: #F5B82E; color: #0D3B22 !important; border: 1px solid #F5B82E; font-weight: 700; }
            .ppb-btn-gold:hover { background: #e5a925; }
            .ppb-btn-secondary { background: #f8fafc; color: #334155 !important; border: 1px solid #cbd5e1; }
            .ppb-btn-secondary:hover { background: #f1f5f9; color: #0f172a !important; }

            /* Bagian Panduan */
            .ppb-guide-box { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 24px; box-shadow: 0 4px 10px rgba(0,0,0,0.03); }
            .ppb-guide-header { font-size: 16px; font-weight: 700; margin: 0 0 16px 0; color: #0D3B22; display: flex; align-items: center; gap: 8px; }
            .ppb-guide-list { margin: 0; padding-left: 20px; }
            .ppb-guide-list li { margin-bottom: 12px; font-size: 13.5px; line-height: 1.6; color: #475569; }
            .ppb-guide-list li strong { color: #0f172a; }
        </style>

        <!-- Header Banner -->
        <div class="ppb-header-card">
            <span class="ppb-header-badge">⭐ Jurusan PPB / Prodi BK FIP UNM</span>
            <h1 class="ppb-header-title">Pusat Kendali Portal Web PPB (8 Kategori Terpadu)</h1>
            <p class="ppb-header-subtitle">
                Menu sidebar kini telah diringkas menjadi <strong>8 Kategori Utama</strong> lengkap dengan <strong>Sub-Submenu (arahkan kursor atau klik panah ▸ di sidebar)</strong> serta <strong>Pencarian Cepat</strong> di bawah ini agar Anda dapat menemukan fitur apa pun dalam hitungan detik.
            </p>
        </div>

        <!-- Bar Pencarian Instan & Filter Kategori -->
        <div class="ppb-filter-bar">
            <div class="ppb-search-box-wrap">
                <span class="ppb-search-box-icon">🔍</span>
                <input type="search" id="ppb-dashboard-search" class="ppb-hub-search-input" placeholder="Ketik menu atau fitur yang dicari... (contoh: RPS, SINTA, LAMDIK, Lab, Surat, SOP, MBKM, Jurnal, Mega Menu, Frontend Editor)">
            </div>
            <div class="ppb-cat-pills" id="ppb-dashboard-cat-pills">
                <button type="button" class="ppb-cat-pill is-active" data-cat="all">✨ Semua Menu (14)</button>
                <button type="button" class="ppb-cat-pill" data-cat="beranda">🏠 Beranda &amp; Navigasi</button>
                <button type="button" class="ppb-cat-pill" data-cat="dosen_matkul">👨‍🏫 Dosen &amp; Mata Kuliah</button>
                <button type="button" class="ppb-cat-pill" data-cat="akademik_riset">⚖️ Akademik, Riset &amp; E-Lib</button>
                <button type="button" class="ppb-cat-pill" data-cat="mutu_lamdik">🏅 Mutu, LAMDIK &amp; Survei</button>
                <button type="button" class="ppb-cat-pill" data-cat="kemahasiswaan">🏆 Kemahasiswaan &amp; Alumni</button>
                <button type="button" class="ppb-cat-pill" data-cat="layanan_lab">🏢 Layanan, Lab &amp; Sarpras</button>
                <button type="button" class="ppb-cat-pill" data-cat="warta_data">📰 Warta &amp; Import Data</button>
            </div>
        </div>

        <!-- Metrik Ringkas -->
        <div class="ppb-metrics-grid">
            <div class="ppb-metric-card">
                <div class="ppb-metric-info">
                    <span>Total Dosen</span>
                    <strong><?php echo esc_html($count_dosen); ?></strong>
                </div>
                <div class="ppb-metric-icon">👥</div>
            </div>
            <div class="ppb-metric-card">
                <div class="ppb-metric-info">
                    <span>Mata Kuliah OBE</span>
                    <strong><?php echo esc_html($count_matkul); ?></strong>
                </div>
                <div class="ppb-metric-icon">📖</div>
            </div>
            <div class="ppb-metric-card">
                <div class="ppb-metric-info">
                    <span>Eviden LAMDIK</span>
                    <strong><?php echo esc_html($lamdik_filled); ?>/<?php echo esc_html($lamdik_total_slots ?? 27); ?></strong>
                </div>
                <div class="ppb-metric-icon">🏅</div>
            </div>
            <div class="ppb-metric-card">
                <div class="ppb-metric-info">
                    <span>Responden Survei</span>
                    <strong><?php echo esc_html($count_survei); ?></strong>
                </div>
                <div class="ppb-metric-icon">📊</div>
            </div>
            <div class="ppb-metric-card">
                <div class="ppb-metric-info">
                    <span>Inventaris Lab</span>
                    <strong><?php echo esc_html($count_inventaris); ?></strong>
                </div>
                <div class="ppb-metric-icon">🧪</div>
            </div>
            <div class="ppb-metric-card">
                <div class="ppb-metric-info">
                    <span>Modul E-Library</span>
                    <strong><?php echo esc_html($count_elib); ?></strong>
                </div>
                <div class="ppb-metric-icon">📚</div>
            </div>
            <div class="ppb-metric-card">
                <div class="ppb-metric-info">
                    <span>Dokumen Mutu</span>
                    <strong><?php echo esc_html($count_dokumen); ?></strong>
                </div>
                <div class="ppb-metric-icon">📂</div>
            </div>
            <div class="ppb-metric-card">
                <div class="ppb-metric-info">
                    <span>Jadwal &amp; Berita</span>
                    <strong><?php echo esc_html($count_jadwal + $count_berita); ?></strong>
                </div>
                <div class="ppb-metric-icon">📅</div>
            </div>
        </div>

        <!-- Kartu Aksi Cepat Terpadu -->
        <div class="ppb-actions-grid" id="ppb-dashboard-cards-grid">

            <!-- 00. Registry Unit & Prodi Jurusan PPB -->
            <div class="ppb-action-card" data-cat="beranda" data-keywords="jurusan ppb prodi s1 s2 s3 ppg unit kaprodi visi registry jenjang">
                <div>
                    <span class="ppb-card-cat-badge">🏛️ Fondasi Jurusan PPB</span>
                    <div class="ppb-card-top">
                        <div class="ppb-card-icon dark">🏛️</div>
                        <div class="ppb-card-text">
                            <h3>Unit &amp; Program Studi Jurusan PPB</h3>
                            <p>Atur pimpinan &amp; visi payung Jurusan PPB serta daftar Prodi (S1, S2, S3, dan penambahan prodi baru seperti PPG BK).</p>
                        </div>
                    </div>
                </div>
                <div class="ppb-card-btns">
                    <a href="<?php echo admin_url('admin.php?page=ppb-unit-registry'); ?>" class="ppb-btn ppb-btn-gold">🏛️ Kelola Unit &amp; Prodi</a>
                    <a href="<?php echo esc_url(home_url('/profil/struktur/')); ?>" target="_blank" class="ppb-btn ppb-btn-secondary">↗ Struktur Publik</a>
                </div>
            </div>
            
            <!-- 0. Spotlight & Media Beranda -->
            <div class="ppb-action-card" data-cat="beranda" data-keywords="beranda spotlight video youtube slider teks sambutan kajur visi misi">
                <div>
                    <span class="ppb-card-cat-badge">🏠 Beranda &amp; Navigasi</span>
                    <div class="ppb-card-top">
                        <div class="ppb-card-icon dark">🌟</div>
                        <div class="ppb-card-text">
                            <h3>Spotlight &amp; Teks Beranda</h3>
                            <p>Atur video profil YouTube/Drive, kartu akreditasi LAMDIK, foto kegiatan, dan teks seksi pada halaman depan beranda.</p>
                        </div>
                    </div>
                </div>
                <div class="ppb-card-btns">
                    <a href="<?php echo admin_url('admin.php?page=ppb-spotlight-beranda'); ?>" class="ppb-btn ppb-btn-primary">🎬 Kelola Slide Spotlight</a>
                    <a href="<?php echo admin_url('admin.php?page=bk-pengaturan-beranda'); ?>" class="ppb-btn ppb-btn-secondary">⚙️ Teks &amp; Seksi Beranda</a>
                </div>
            </div>

            <!-- 0b. Navigasi Navbar & Live Frontend Editor -->
            <div class="ppb-action-card" data-cat="beranda" data-keywords="beranda navigasi mega menu 4 pilar navbar live frontend inline editor edit teks langsung wysiwyg">
                <div>
                    <span class="ppb-card-cat-badge">🏠 Beranda &amp; Navigasi</span>
                    <div class="ppb-card-top">
                        <div class="ppb-card-icon dark">🧭</div>
                        <div class="ppb-card-text">
                            <h3>Mega Menu Navbar &amp; Live Frontend Editor</h3>
                            <p>Atur susunan 4 Pilar Mega Menu (Beranda, Profil &amp; Mutu, Akademik, Layanan &amp; Warta) serta pengaturan Live Frontend Text Editor.</p>
                        </div>
                    </div>
                </div>
                <div class="ppb-card-btns">
                    <a href="<?php echo admin_url('admin.php?page=ppb-mega-menu-manager'); ?>" class="ppb-btn ppb-btn-gold">✨ Mega Menu (4 Pilar)</a>
                    <a href="<?php echo admin_url('nav-menus.php'); ?>" class="ppb-btn ppb-btn-secondary">📋 Menu WP</a>
                    <a href="<?php echo admin_url('options-general.php?page=wfie-settings'); ?>" class="ppb-btn ppb-btn-secondary">✏️ Frontend Editor</a>
                </div>
            </div>

            <!-- 1. Dosen -->
            <div class="ppb-action-card" data-cat="dosen_matkul" data-keywords="dosen staf pengajar nip nidn sinta google scholar foto jabatan guru besar lektor">
                <div>
                    <span class="ppb-card-cat-badge">👨‍🏫 Dosen &amp; Mata Kuliah</span>
                    <div class="ppb-card-top">
                        <div class="ppb-card-icon green">👥</div>
                        <div class="ppb-card-text">
                            <h3>Data Dosen &amp; Sinkron SINTA</h3>
                            <p>Kelola profil dosen, gelar, NIP, pangkat akademik, foto potret 4:5, profil SINTA, serta pilihan mata kuliah yang diampu.</p>
                        </div>
                    </div>
                </div>
                <div class="ppb-card-btns">
                    <a href="<?php echo admin_url('admin.php?page=bk-direktori-dosen'); ?>" class="ppb-btn ppb-btn-primary">👨‍🏫 Direktori Dosen (<?php echo esc_html($count_dosen); ?>)</a>
                    <a href="<?php echo esc_url(home_url('/profil/dosen/')); ?>" target="_blank" class="ppb-btn ppb-btn-secondary">↗ Lihat Halaman</a>
                </div>
            </div>

            <!-- 1b. Mata Kuliah & Auto-Sync Dosen -->
            <div class="ppb-action-card" data-cat="dosen_matkul" data-keywords="mata kuliah matkul kurikulum obe rps sap sks semester dosen pengampu">
                <div>
                    <span class="ppb-card-cat-badge">👨‍🏫 Dosen &amp; Mata Kuliah</span>
                    <div class="ppb-card-top">
                        <div class="ppb-card-icon gold">📖</div>
                        <div class="ppb-card-text">
                            <h3>Mata Kuliah OBE &amp; RPS/SAP</h3>
                            <p>Kelola daftar mata kuliah S1/S2/S3, upload file RPS &amp; SAP, serta kaitkan ke dosen agar otomatis tersinkron ke profil dosen.</p>
                        </div>
                    </div>
                </div>
                <div class="ppb-card-btns">
                    <a href="<?php echo admin_url('admin.php?page=ppb-mata-kuliah-manager'); ?>" class="ppb-btn ppb-btn-gold">📚 Kelola Mata Kuliah (<?php echo esc_html($count_matkul); ?>)</a>
                    <a href="<?php echo esc_url(home_url('/akademik/kurikulum/')); ?>" target="_blank" class="ppb-btn ppb-btn-secondary">↗ Kurikulum Publik</a>
                </div>
            </div>

            <!-- 1c. Akreditasi & LAMDIK -->
            <div class="ppb-action-card" data-cat="mutu_lamdik" data-keywords="akreditasi lamdik 9 kriteria led lkps sertifikat unggul s1 s2 s3 mutu">
                <div>
                    <span class="ppb-card-cat-badge">🏅 Mutu, LAMDIK &amp; Survei</span>
                    <div class="ppb-card-top">
                        <div class="ppb-card-icon dark">🏅</div>
                        <div class="ppb-card-text">
                            <h3>Akreditasi &amp; 9 Kriteria LAMDIK (S1, S2, S3)</h3>
                            <p>Atur tautan Google Drive Folder Utama, LED, LKPS, 9 Kriteria Eviden terpisah untuk S1, S2, S3, Renstra/Renop Jurusan, dan Sertifikat resmi.</p>
                        </div>
                    </div>
                </div>
                <div class="ppb-card-btns">
                    <a href="<?php echo admin_url('admin.php?page=ppb-lamdik-settings'); ?>" class="ppb-btn ppb-btn-gold">🏅 Link Drive LAMDIK (<?php echo esc_html($lamdik_filled); ?>/<?php echo esc_html($lamdik_total_slots ?? 27); ?>)</a>
                    <a href="<?php echo esc_url(home_url('/akreditasi/')); ?>" target="_blank" class="ppb-btn ppb-btn-secondary">↗ Hub Akreditasi</a>
                </div>
            </div>

            <!-- 1d. Dokumen Mutu & Survei GPM -->
            <div class="ppb-action-card" data-cat="mutu_lamdik" data-keywords="dokumen mutu google drive renstra sk regulasi survei kepuasan mahasiswa gpm ikm ami">
                <div>
                    <span class="ppb-card-cat-badge">🏅 Mutu, LAMDIK &amp; Survei</span>
                    <div class="ppb-card-top">
                        <div class="ppb-card-icon green">📊</div>
                        <div class="ppb-card-text">
                            <h3>Dokumen Mutu &amp; Survei Kepuasan GPM</h3>
                            <p>Kelola link Google Drive Dokumen Mutu (<?php echo esc_html($count_dokumen); ?> berkas) serta pantau IKM Survei Kepuasan GPM (<?php echo esc_html(number_format((float) ($survei_summary['ikm_pct'] ?? 0), 1)); ?>%).</p>
                        </div>
                    </div>
                </div>
                <div class="ppb-card-btns">
                    <a href="<?php echo admin_url('edit.php?post_type=ppb_dokumen'); ?>" class="ppb-btn ppb-btn-primary">📂 Dokumen Mutu (<?php echo esc_html($count_dokumen); ?>)</a>
                    <a href="<?php echo admin_url('admin.php?page=ppb-survei-manager'); ?>" class="ppb-btn ppb-btn-gold">📊 Survei GPM (<?php echo esc_html($count_survei); ?>)</a>
                </div>
            </div>

            <!-- 2. Dokumen Akademik, Hibah PNBP & Jurnal -->
            <div class="ppb-action-card" data-cat="akademik_riset" data-keywords="akademik kurikulum sk senat rubrik obe moa kerjasama hibah pnbp riset penelitian pengabdian jurnal ilmiah">
                <div>
                    <span class="ppb-card-cat-badge">⚖️ Akademik, Riset &amp; E-Lib</span>
                    <div class="ppb-card-top">
                        <div class="ppb-card-icon blue">⚖️</div>
                        <div class="ppb-card-text">
                            <h3>Akademik, Hibah PNBP &amp; Jurnal</h3>
                            <p>Kelola tautan Buku Kurikulum, SK Senat, Rubrik OBE, MoA, Roadmap Riset, serta data Hibah PNBP &amp; Jurnal Prodi BK.</p>
                        </div>
                    </div>
                </div>
                <div class="ppb-card-btns">
                    <a href="<?php echo admin_url('admin.php?page=ppb-akademik-settings&tab=tautan'); ?>" class="ppb-btn ppb-btn-primary">⚖️ Tautan &amp; MoA</a>
                    <a href="<?php echo admin_url('admin.php?page=ppb-akademik-settings&tab=pnbp'); ?>" class="ppb-btn ppb-btn-secondary">🔬 Hibah PNBP</a>
                    <a href="<?php echo admin_url('admin.php?page=ppb-akademik-settings&tab=jurnal'); ?>" class="ppb-btn ppb-btn-secondary">📰 Jurnal BK</a>
                </div>
            </div>

            <!-- 2b. Jadwal Ujian & E-Library -->
            <div class="ppb-action-card" data-cat="akademik_riset" data-keywords="jadwal ujian seminar proposal hasil tutup skripsi elibrary e-library modul buku praktikum pdf kurasi naskah">
                <div>
                    <span class="ppb-card-cat-badge">⚖️ Akademik, Riset &amp; E-Lib</span>
                    <div class="ppb-card-top">
                        <div class="ppb-card-icon gold">📚</div>
                        <div class="ppb-card-text">
                            <h3>Jadwal Seminar/Ujian &amp; E-Library</h3>
                            <p>Publikasikan jadwal ujian skripsi mahasiswa (<?php echo esc_html($count_jadwal); ?> jadwal) serta kelola katalog modul E-Library (<?php echo esc_html($count_elib); ?> modul).</p>
                        </div>
                    </div>
                </div>
                <div class="ppb-card-btns">
                    <a href="<?php echo admin_url('edit.php?post_type=ppb_jadwal'); ?>" class="ppb-btn ppb-btn-primary">📅 Jadwal Ujian (<?php echo esc_html($count_jadwal); ?>)</a>
                    <a href="<?php echo admin_url('admin.php?page=ppb-elibrary-manager'); ?>" class="ppb-btn ppb-btn-gold">📚 E-Library (<?php echo esc_html($count_elib); ?>)</a>
                </div>
            </div>

            <!-- 5. Kemahasiswaan, Maba & MBKM -->
            <div class="ppb-action-card" data-cat="kemahasiswaan" data-keywords="kemahasiswaan maba mahasiswa baru tracer study alumni mbkm kemanusiaan prestasi juara karya inovasi">
                <div>
                    <span class="ppb-card-cat-badge">🏆 Kemahasiswaan &amp; Alumni</span>
                    <div class="ppb-card-top">
                        <div class="ppb-card-icon purple">🏆</div>
                        <div class="ppb-card-text">
                            <h3>Kemahasiswaan, Maba, Tracer &amp; MBKM</h3>
                            <p>Kelola prestasi, karya inovasi, statistik mahasiswa, Tracer Study, tren daya tampung Maba, dan Pameran MBKM / Kemanusiaan.</p>
                        </div>
                    </div>
                </div>
                <div class="ppb-card-btns">
                    <a href="<?php echo admin_url('admin.php?page=ppb-kemahasiswaan-settings&tab=statistik'); ?>" class="ppb-btn ppb-btn-primary">📈 Maba &amp; Tracer</a>
                    <a href="<?php echo admin_url('admin.php?page=ppb-kemahasiswaan-settings&tab=mbkm'); ?>" class="ppb-btn ppb-btn-secondary">🤝 MBKM</a>
                    <a href="<?php echo admin_url('edit.php?post_type=ppb_prestasi'); ?>" class="ppb-btn ppb-btn-secondary">🏅 Prestasi</a>
                    <a href="<?php echo admin_url('edit.php?post_type=ppb_karya'); ?>" class="ppb-btn ppb-btn-secondary">🎨 Karya</a>
                </div>
            </div>

            <!-- 6. Layanan, Lab BK & Sarpras -->
            <div class="ppb-action-card" data-cat="layanan_lab" data-keywords="layanan hotline whatsapp sipadu lab bk kegiatan lomba sarpras fasilitas ruangan inventaris alat tes surat sop">
                <div>
                    <span class="ppb-card-cat-badge">🏢 Layanan, Lab &amp; Sarpras</span>
                    <div class="ppb-card-top">
                        <div class="ppb-card-icon orange">🏢</div>
                        <div class="ppb-card-text">
                            <h3>Layanan, Lab BK, Sarpras, Surat &amp; SOP</h3>
                            <p>Kelola hotline &amp; SIPADU, kegiatan &amp; lomba Lab BK, foto Sarpras, inventaris alat tes, template surat, dan alur SOP.</p>
                        </div>
                    </div>
                </div>
                <div class="ppb-card-btns">
                    <a href="<?php echo admin_url('admin.php?page=ppb-layanan-settings&tab=umum'); ?>" class="ppb-btn ppb-btn-primary">⚙️ Layanan</a>
                    <a href="<?php echo admin_url('admin.php?page=ppb-layanan-settings&tab=lab_kegiatan'); ?>" class="ppb-btn ppb-btn-secondary">🧪 Kegiatan Lab</a>
                    <a href="<?php echo admin_url('admin.php?page=ppb-layanan-settings&tab=sarpras'); ?>" class="ppb-btn ppb-btn-secondary">🏛️ Sarpras</a>
                    <a href="<?php echo admin_url('edit.php?post_type=ppb_inventaris_lab'); ?>" class="ppb-btn ppb-btn-secondary">🔬 Alat Lab</a>
                    <a href="<?php echo admin_url('edit.php?post_type=ppb_template_dok'); ?>" class="ppb-btn ppb-btn-secondary">📄 Surat</a>
                    <a href="<?php echo admin_url('edit.php?post_type=ppb_alur_sop'); ?>" class="ppb-btn ppb-btn-secondary">🔄 SOP</a>
                </div>
            </div>

            <!-- 8. Warta & Import / Export Massal Excel & JSON -->
            <div class="ppb-action-card" data-cat="warta_data" data-keywords="warta berita pengumuman artikel import export excel csv json 16 dataset backup">
                <div>
                    <span class="ppb-card-cat-badge">📰 Warta &amp; Import Data</span>
                    <div class="ppb-card-top">
                        <div class="ppb-card-icon green">📥</div>
                        <div class="ppb-card-text">
                            <h3>Warta Berita &amp; Import/Export Excel</h3>
                            <p>Kelola berita kegiatan jurusan (<?php echo esc_html($count_berita); ?> artikel) serta unggah/unduh massal 16 dataset Portal via <code>.xlsx</code>, <code>.csv</code>, atau <code>.json</code>.</p>
                        </div>
                    </div>
                </div>
                <div class="ppb-card-btns">
                    <a href="<?php echo admin_url('edit.php'); ?>" class="ppb-btn ppb-btn-primary">📰 Semua Berita (<?php echo esc_html($count_berita); ?>)</a>
                    <a href="<?php echo admin_url('post-new.php'); ?>" class="ppb-btn ppb-btn-secondary">➕ Tulis Berita</a>
                    <a href="<?php echo admin_url('admin.php?page=ppb-excel-import'); ?>" class="ppb-btn ppb-btn-gold">📥 Import / Export (16 Dataset)</a>
                </div>
            </div>

        </div>

        <!-- Panduan Singkat Admin -->
        <div class="ppb-guide-box">
            <h2 class="ppb-guide-header">💡 Panduan Navigasi Baru &amp; Live Frontend Editor</h2>
            <ol class="ppb-guide-list">
                <li>
                    <strong>Membuka Sub-Submenu di Sidebar:</strong> 
                    Di menu kiri <strong>🏛️ Portal PPB FIP</strong>, kini hanya ada <strong>8 Kategori Utama</strong>. Arahkan kursor (hover) ke salah satu kategori untuk memunculkan <em>Flyout Sub-Submenu</em>, atau klik ikon panah <code>▸</code> di sebelah kanan nama menu untuk membuka daftar sub-submenu secara permanen.
                </li>
                <li>
                    <strong>Pencarian Cepat Menu (<code>Ctrl + K</code>):</strong> 
                    Gunakan kotak <strong>🔍 Cari menu / sub-menu...</strong> di bagian atas sidebar <em>Portal PPB FIP</em> atau bar atas halaman untuk langsung menemukan fitur seperti <em>RPS</em>, <em>LAMDIK</em>, <em>Inventaris Lab</em>, <em>Template Surat</em>, hingga <em>SINTA</em>.
                </li>
                <li>
                    <strong>Mengedit Teks Langsung di Tampilan Depan (Live Frontend Inline Editor):</strong> 
                    Saat membuka halaman atau berita di tampilan depan website, klik tombol <strong>✏️ Mode Edit</strong> di pojok kanan bawah (atau <strong>Mode Edit Frontend</strong> di Admin Bar atas). Klik teks mana saja untuk mengedit, blok teks untuk memformat (Bold/Italic/Heading/Link), lalu tekan <kbd>Ctrl + S</kbd> atau klik <strong>💾 Simpan</strong>!
                </li>
            </ol>
        </div>

        <script>
        (function() {
            var searchInput = document.getElementById('ppb-dashboard-search');
            var pills = document.querySelectorAll('#ppb-dashboard-cat-pills .ppb-cat-pill');
            var cards = document.querySelectorAll('#ppb-dashboard-cards-grid .ppb-action-card');
            var activeCat = 'all';

            function filterDashboardCards() {
                var q = (searchInput ? searchInput.value : '').trim().toLowerCase();
                cards.forEach(function(card) {
                    var cardCat = card.getAttribute('data-cat') || '';
                    var kw = (card.textContent + ' ' + (card.getAttribute('data-keywords') || '')).toLowerCase();
                    var matchCat = (activeCat === 'all' || cardCat === activeCat);
                    var matchQ = (!q || kw.indexOf(q) !== -1);
                    card.style.display = (matchCat && matchQ) ? '' : 'none';
                });
            }

            if (searchInput) {
                searchInput.addEventListener('input', filterDashboardCards);
            }
            pills.forEach(function(pill) {
                pill.addEventListener('click', function() {
                    pills.forEach(function(p) { p.classList.remove('is-active'); });
                    pill.classList.add('is-active');
                    activeCat = pill.getAttribute('data-cat') || 'all';
                    filterDashboardCards();
                });
            });
        })();
        </script>
    </div>
    <?php
}
}

/* ========================================================================== */
/* 6. AUTO-SEEDING DUMMY & DEFAULT RECORDS (SELF-HEALING WP-ADMIN)            */
/* ========================================================================== */
require_once plugin_dir_path(__FILE__) . 'ppb-seed-data.php';

add_action('admin_init', 'ppb_auto_seed_portal_data');
if (!function_exists('ppb_auto_seed_portal_data')) {
function ppb_auto_seed_portal_data() {
    if (!current_user_can('manage_options')) return;
    if (get_option('ppb_portal_dummy_seeded_v4') === 'yes') return;

    if (function_exists('ppb_run_complete_dummy_seeder')) {
        ppb_run_complete_dummy_seeder();
    }
    update_option('ppb_portal_dummy_seeded_v4', 'yes');
}
}

/* ========================================================================== */
/* 7. PENANDA BLOK DINAMIS YANG DIKELOLA VIA WP ADMIN (UNTUK FRONTEND EDITOR) */
/* ========================================================================== */
if (!function_exists('ppb_get_admin_managed_shortcode_map')) {
function ppb_get_admin_managed_shortcode_map() {
    return array(
        'ppb_dosen_roster'       => array('label' => 'Direktori Dosen & SINTA',       'url' => admin_url('admin.php?page=bk-direktori-dosen')),
        'ppb_lamdik_hub'         => array('label' => 'Akreditasi & 9 Kriteria LAMDIK', 'url' => admin_url('admin.php?page=ppb-lamdik-settings')),
        'ppb_kurikulum_rps_sap'  => array('label' => 'Mata Kuliah OBE & RPS/SAP',     'url' => admin_url('admin.php?page=ppb-mata-kuliah-manager')),
        'ppb_jadwal_ujian_list'  => array('label' => 'Jadwal Ujian & Seminar',        'url' => admin_url('edit.php?post_type=ppb_jadwal')),
        'ppb_mis_pnbp'           => array('label' => 'Hibah Riset & PkM PNBP',        'url' => admin_url('admin.php?page=ppb-akademik-settings&tab=pnbp')),
        'ppb_jurnal_bk'          => array('label' => 'Jurnal Ilmiah Prodi BK',        'url' => admin_url('admin.php?page=ppb-akademik-settings&tab=jurnal')),
        'ppb_dokumen_grid'       => array('label' => 'Dokumen Mutu (Google Drive)',   'url' => admin_url('edit.php?post_type=ppb_dokumen')),
        'ppb_prestasi_list'      => array('label' => 'Prestasi Mahasiswa',            'url' => admin_url('edit.php?post_type=ppb_prestasi')),
        'ppb_karya_list'         => array('label' => 'Karya & Inovasi Mahasiswa',     'url' => admin_url('edit.php?post_type=ppb_karya')),
        'ppb_tracer_study_view'  => array('label' => 'Tracer Study & Alumni',         'url' => admin_url('admin.php?page=ppb-kemahasiswaan-settings&tab=statistik')),
        'ppb_maba_statistik'     => array('label' => 'Statistik Maba & Daya Tampung', 'url' => admin_url('admin.php?page=ppb-kemahasiswaan-settings&tab=statistik')),
        'ppb_mbkm_kemanusiaan'   => array('label' => 'Pameran MBKM & Kemanusiaan',    'url' => admin_url('admin.php?page=ppb-kemahasiswaan-settings&tab=mbkm')),
        'ppb_inventaris_lab'     => array('label' => 'Inventaris Alat & Tes Lab BK',  'url' => admin_url('edit.php?post_type=ppb_inventaris_lab')),
        'ppb_layanan_persuratan' => array('label' => 'Template Surat & Alur SOP',     'url' => admin_url('edit.php?post_type=ppb_template_dok')),
        'ppb_lab_portals'        => array('label' => 'Layanan & Portal Lab BK',       'url' => admin_url('admin.php?page=ppb-layanan-settings&tab=umum')),
        'ppb_lab_kegiatan'       => array('label' => 'Kegiatan Lab BK',               'url' => admin_url('admin.php?page=ppb-layanan-settings&tab=lab_kegiatan')),
        'ppb_lab_lomba'          => array('label' => 'Lomba & Event Lab BK',          'url' => admin_url('admin.php?page=ppb-layanan-settings&tab=lab_kegiatan')),
        'ppb_elibrary'           => array('label' => 'E-Library & Modul Praktikum',   'url' => admin_url('admin.php?page=ppb-elibrary-manager')),
        'ppb_sarana_prasarana'   => array('label' => 'Fasilitas & Foto Sarpras',      'url' => admin_url('admin.php?page=ppb-layanan-settings&tab=sarpras')),
        'ppb_survei_form'        => array('label' => 'Survei Kepuasan GPM',           'url' => admin_url('admin.php?page=ppb-survei-manager')),
        'ppb_berita_grid'        => array('label' => 'Warta & Berita Jurusan',        'url' => admin_url('edit.php')),
        'ppb_home_news'          => array('label' => 'Warta & Berita Beranda',        'url' => admin_url('edit.php')),
    );
}
}

add_filter('do_shortcode_tag', function($output, $tag, $attr, $m) {
    if (is_admin() || !is_user_logged_in() || !current_user_can('edit_posts')) {
        return $output;
    }
    $map = ppb_get_admin_managed_shortcode_map();
    if (!isset($map[$tag])) {
        return $output;
    }
    $info = $map[$tag];
    $raw_shortcode = isset($m[0]) ? $m[0] : '[' . $tag . ']';
    return sprintf(
        '<div class="ppb-admin-managed-zone" data-ppb-admin-label="%s" data-ppb-admin-url="%s" data-ppb-shortcode="%s">%s</div>',
        esc_attr($info['label']),
        esc_url($info['url']),
        esc_attr($raw_shortcode),
        $output
    );
}, 20, 4);

