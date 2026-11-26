<?php
/**
 * 7 Jurus BK Hebat - Universal Service Design Canvas
 * Shortcode: [ppb_7jurus_canvas] / [ppb_service_design_canvas]
 *
 * Original interactive concept & design by @ekachandraoktaviani
 */

if (!defined('ABSPATH')) exit;

function ppb_render_7jurus_canvas_shortcode($atts = array()) {
    $plugin_root_file = dirname(__DIR__) . '/bk-connect-core.php';
    $css_path = dirname(__DIR__) . '/assets/css/ppb-7jurus-canvas.css';
    $js_path  = dirname(__DIR__) . '/assets/js/ppb-7jurus-canvas.js';

    $css_ver = file_exists($css_path) ? (string) filemtime($css_path) : '1.0.0';
    $js_ver  = file_exists($js_path) ? (string) filemtime($js_path) : '1.0.0';

    wp_enqueue_style(
        'ppb-7jurus-canvas-css',
        plugins_url('assets/css/ppb-7jurus-canvas.css', $plugin_root_file),
        array(),
        $css_ver
    );

    wp_enqueue_script(
        'ppb-lucide-icons',
        'https://unpkg.com/lucide@0.460.0/dist/umd/lucide.min.js',
        array(),
        '0.460.0',
        true
    );

    wp_enqueue_script(
        'ppb-html2pdf',
        'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js',
        array(),
        '0.10.1',
        true
    );

    wp_enqueue_script(
        'ppb-7jurus-canvas-js',
        plugins_url('assets/js/ppb-7jurus-canvas.js', $plugin_root_file),
        array('ppb-lucide-icons', 'ppb-html2pdf'),
        $js_ver,
        true
    );

    ob_start();
    ?>
    <div class="bk-7jurus-app">
        <div class="min-h-screen flex flex-col justify-between antialiased">
<!-- Top Navigation Header -->
    <header class="bg-navy text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- App Brand Title -->
                <div class="flex items-center space-x-3 cursor-pointer" onclick="app.switchView('dashboard')">
                    <div class="bg-teal p-2 rounded-lg text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="compass" aria-hidden="true" class="lucide lucide-compass w-6 h-6"><circle cx="12" cy="12" r="10"></circle><path d="m16.24 7.76-1.804 5.411a2 2 0 0 1-1.265 1.265L7.76 16.24l1.804-5.411a2 2 0 0 1 1.265-1.265z"></path></svg>
                    </div>
                    <div>
                        <h1 class="font-extrabold text-base md:text-lg leading-tight tracking-tight text-white">7 JURUS BK HEBAT</h1>
                        <p class="text-xs text-gold font-medium">UNIVERSAL SERVICE DESIGN CANVAS</p>
                    </div>
                </div>

                <!-- Navigation Controls -->
                <div class="flex items-center space-x-2 md:space-x-4">
                    <button onclick="app.switchView('dashboard')" class="text-slate-200 hover:text-white px-3 py-2 rounded-md text-xs md:text-sm font-medium flex items-center space-x-1 hover:bg-slate-800 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="layout-dashboard" aria-hidden="true" class="lucide lucide-layout-dashboard w-4 h-4"><rect width="7" height="9" x="3" y="3" rx="1"></rect><rect width="7" height="5" x="14" y="3" rx="1"></rect><rect width="7" height="9" x="14" y="12" rx="1"></rect><rect width="7" height="5" x="3" y="16" rx="1"></rect></svg>
                        <span class="hidden md:inline">Dashboard</span>
                    </button>
                    <button onclick="app.switchView('canvas')" class="bg-teal hover:bg-teal/90 text-white px-3 py-2 rounded-md text-xs md:text-sm font-medium flex items-center space-x-1 shadow transition">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="edit-3" aria-hidden="true" class="lucide lucide-edit-3 w-4 h-4"><path d="M13 21h8"></path><path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"></path></svg>
                        <span>Canvas Desain</span>
                    </button>
                    <button onclick="app.openProfileModal()" class="bg-slate-800 hover:bg-slate-700 text-slate-200 px-3 py-2 rounded-md text-xs md:text-sm font-medium flex items-center space-x-1 border border-slate-700 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="user-check" aria-hidden="true" class="lucide lucide-user-check w-4 h-4 text-gold"><path d="m16 11 2 2 4-4"></path><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
                        <span id="nav-teacher-name" class="hidden sm:inline">Profil Guru (Belum Lengkap)</span>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main View Dynamic Container -->
    <main class="flex-grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">

        <!-- VIEW 1: DASHBOARD VIEW -->
        <section id="view-dashboard" class="space-y-8">
            <!-- Hero Banner -->
            <div class="bg-gradient-to-r from-navy via-slate-900 to-navy text-white rounded-2xl p-6 md:p-8 shadow-xl relative overflow-hidden border border-slate-700">
                <div class="absolute right-0 top-0 opacity-10 translate-x-12 -translate-y-6 pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="layers" aria-hidden="true" class="lucide lucide-layers w-96 h-96"><path d="M12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83z"></path><path d="M2 12a1 1 0 0 0 .58.91l8.6 3.91a2 2 0 0 0 1.65 0l8.58-3.9A1 1 0 0 0 22 12"></path><path d="M2 17a1 1 0 0 0 .58.91l8.6 3.91a2 2 0 0 0 1.65 0l8.58-3.9A1 1 0 0 0 22 17"></path></svg>
                </div>
                <div class="relative z-10 max-w-3xl">
                    <span class="inline-block px-3 py-1 bg-gold/20 text-gold rounded-full text-xs font-semibold uppercase tracking-wider mb-3">
                        From Student Needs to Student Growth
                    </span>
                    <h2 class="text-2xl md:text-4xl font-black mb-3 leading-tight">
                        Rancang Layanan Bimbingan &amp; Konseling Bermakna, Berdampak &amp; Terstruktur
                    </h2>
                    <blockquote class="italic text-slate-300 text-sm md:text-base border-l-4 border-teal pl-4 mb-6">
                        "Jangan mulai dari kegiatan apa yang akan kita lakukan kepada murid. Mulailah dari perubahan apa yang dibutuhkan murid, lalu desain pengalaman yang memungkinkan perubahan itu terjadi."
                    </blockquote>
                    
                    <div class="flex flex-wrap gap-4">
                        <button onclick="app.startFreshDesign()" class="bg-teal hover:bg-teal/90 text-white font-semibold px-6 py-3 rounded-xl shadow-lg transition flex items-center space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="plus-circle" aria-hidden="true" class="lucide lucide-plus-circle w-5 h-5"><circle cx="12" cy="12" r="10"></circle><path d="M8 12h8"></path><path d="M12 8v8"></path></svg>
                            <span>MULAI DESAIN SENDIRI (KOSONG)</span>
                        </button>
                        <button onclick="app.loadExampleDesign()" class="bg-white/10 hover:bg-white/20 text-white border border-white/30 font-semibold px-6 py-3 rounded-xl backdrop-blur transition flex items-center space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="book-open" aria-hidden="true" class="lucide lucide-book-open w-5 h-5 text-gold"><path d="M12 5v16"></path><path d="M20.001 19A2 2 0 0022 17V5a2 2 0 00-1.999-2L16 3.002A5 5 0 0012 5a5 5 0 00-4-2H4a2 2 0 00-2 2v12a2 2 0 001.999 2H8a5 5 0 014 2 5 5 0 014-2z"></path></svg>
                            <span>PELAJARI CONTOH ("AKU PUNYA KEKUATAN")</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Profile Status Alert Card -->
            <div id="profile-status-banner" class="p-4 rounded-xl border bg-amber-50 border-amber-200 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="p-2 bg-amber-600 text-white rounded-lg"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="alert-triangle" aria-hidden="true" class="lucide lucide-alert-triangle w-5 h-5"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"></path><path d="M12 9v4"></path><path d="M12 17h.01"></path></svg></div>
                            <div>
                                <h4 class="text-xs font-bold text-amber-900">DATA PROFIL GURU PERLU DILENGKAPI</h4>
                                <p class="text-xs text-amber-700">Lengkapi identitas guru untuk halaman pengesahan dokumen RPL BK.</p>
                            </div>
                        </div>
                        <button onclick="app.openProfileModal()" class="text-xs font-semibold bg-amber-600 text-white px-3 py-1.5 rounded-lg hover:bg-amber-700">Lengkapi Profil</button>
                    </div>

            <!-- Design Workflow Roadmap -->
            <div class="canvas-card p-6 border border-slate-200">
                <h3 class="text-lg font-bold text-navy mb-4 flex items-center space-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="git-commit" aria-hidden="true" class="lucide lucide-git-commit w-5 h-5 text-teal"><circle cx="12" cy="12" r="3"></circle><line x1="3" x2="9" y1="12" y2="12"></line><line x1="15" x2="21" y1="12" y2="12"></line></svg>
                    <span>8 Alur Desain Universal Service Design Canvas</span>
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-3">
                    <div class="p-3 bg-slate-50 rounded-lg text-center border border-slate-100">
                        <span class="text-xs font-bold text-teal block mb-1">LANGKAH 1</span>
                        <p class="text-xs font-semibold text-slate-700">Kebutuhan Murid</p>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-lg text-center border border-slate-100">
                        <span class="text-xs font-bold text-teal block mb-1">LANGKAH 2</span>
                        <p class="text-xs font-semibold text-slate-700">Selaraskan Kurikulum</p>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-lg text-center border border-slate-100">
                        <span class="text-xs font-bold text-teal block mb-1">LANGKAH 3</span>
                        <p class="text-xs font-semibold text-slate-700">Pilih 7 Jurus</p>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-lg text-center border border-slate-100">
                        <span class="text-xs font-bold text-teal block mb-1">LANGKAH 4</span>
                        <p class="text-xs font-semibold text-slate-700">Aktivitas ARKA</p>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-lg text-center border border-slate-100">
                        <span class="text-xs font-bold text-teal block mb-1">LANGKAH 5</span>
                        <p class="text-xs font-semibold text-slate-700">Kalibrasi Mendalam</p>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-lg text-center border border-slate-100">
                        <span class="text-xs font-bold text-teal block mb-1">LANGKAH 6</span>
                        <p class="text-xs font-semibold text-slate-700">Bukti Perkembangan</p>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-lg text-center border border-slate-100">
                        <span class="text-xs font-bold text-teal block mb-1">LANGKAH 7</span>
                        <p class="text-xs font-semibold text-slate-700">Audit Desain AI</p>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-lg text-center border border-slate-100">
                        <span class="text-xs font-bold text-teal block mb-1">LANGKAH 8</span>
                        <p class="text-xs font-semibold text-slate-700">RPL &amp; Dokumen</p>
                    </div>
                </div>
            </div>

            <!-- Current Active Canvas Summary & Actions -->
            <div class="canvas-card p-6 border border-slate-200">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-4 pb-4 border-b border-slate-100 gap-4">
                    <div>
                        <div class="flex items-center space-x-3 mb-1">
                            <span id="dash-mode-badge" class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">Mode Mandiri (Desain Sendiri)</span>
                            <span id="dash-status-badge" class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                BELUM DIMULAI
                            </span>
                        </div>
                        <h3 id="dash-topic-display" class="text-xl font-bold text-navy">Topik Layanan: [Belum ditentukan]</h3>
                        <p id="dash-target-display" class="text-xs text-slate-500">Sasaran: - | Jalur: Kemendikdasmen</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button onclick="app.switchView('canvas')" class="bg-navy hover:bg-navy/90 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="arrow-right-circle" aria-hidden="true" class="lucide lucide-arrow-right-circle w-4 h-4"><circle cx="12" cy="12" r="10"></circle><path d="m12 16 4-4-4-4"></path><path d="M8 12h8"></path></svg>
                            <span>Buka Canvas Desain</span>
                        </button>
                        <button onclick="app.confirmReset()" class="bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 px-3 py-2 rounded-lg text-sm font-medium transition flex items-center space-x-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="rotate-ccw" aria-hidden="true" class="lucide lucide-rotate-ccw w-4 h-4"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path><path d="M3 3v5h5"></path></svg>
                            <span>Reset Canvas</span>
                        </button>
                    </div>
                </div>

                <!-- Canvas Completeness Progress -->
                <div class="space-y-2">
                    <div class="flex justify-between text-xs font-semibold text-slate-600">
                        <span>Progress Kelengkapan Desain</span>
                        <span id="dash-progress-text">0%</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden">
                        <div id="dash-progress-bar" class="bg-teal h-3 rounded-full transition-all duration-500" style="width: 0%"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- VIEW 2: CANVAS DESIGNER (STAGED WORKFLOW) -->
        <section id="view-canvas" class="hidden space-y-6">
            
            <!-- Canvas Step Navigation Tabs -->
            <div class="flex overflow-x-auto pb-2 space-x-2 scrollbar-none border-b border-slate-200 no-print">
                <button onclick="app.setStep(1)" id="tab-step-1" class="tab-btn active shrink-0 px-4 py-2.5 rounded-xl font-semibold text-xs md:text-sm border transition flex items-center space-x-2">
                    <span class="w-5 h-5 rounded-full bg-teal/20 text-teal flex items-center justify-center text-xs font-bold">1</span>
                    <span>Profil &amp; Asesmen</span>
                </button>
                <button onclick="app.setStep(2)" id="tab-step-2" class="tab-btn shrink-0 px-4 py-2.5 rounded-xl font-semibold text-xs md:text-sm border transition flex items-center space-x-2">
                    <span class="w-5 h-5 rounded-full bg-teal/20 text-teal flex items-center justify-center text-xs font-bold">2</span>
                    <span>Jalur Kurikulum</span>
                </button>
                <button onclick="app.setStep(3)" id="tab-step-3" class="tab-btn shrink-0 px-4 py-2.5 rounded-xl font-semibold text-xs md:text-sm border transition flex items-center space-x-2">
                    <span class="w-5 h-5 rounded-full bg-teal/20 text-teal flex items-center justify-center text-xs font-bold">3</span>
                    <span>Pilihan 7 Jurus</span>
                </button>
                <button onclick="app.setStep(4)" id="tab-step-4" class="tab-btn shrink-0 px-4 py-2.5 rounded-xl font-semibold text-xs md:text-sm border transition flex items-center space-x-2">
                    <span class="w-5 h-5 rounded-full bg-teal/20 text-teal flex items-center justify-center text-xs font-bold">4</span>
                    <span>Aktivitas ARKA</span>
                </button>
                <button onclick="app.setStep(5)" id="tab-step-5" class="tab-btn shrink-0 px-4 py-2.5 rounded-xl font-semibold text-xs md:text-sm border transition flex items-center space-x-2">
                    <span class="w-5 h-5 rounded-full bg-teal/20 text-teal flex items-center justify-center text-xs font-bold">5</span>
                    <span>Deep Learning</span>
                </button>
                <button onclick="app.setStep(6)" id="tab-step-6" class="tab-btn shrink-0 px-4 py-2.5 rounded-xl font-semibold text-xs md:text-sm border transition flex items-center space-x-2">
                    <span class="w-5 h-5 rounded-full bg-teal/20 text-teal flex items-center justify-center text-xs font-bold">6</span>
                    <span>Evaluasi &amp; Bukti</span>
                </button>
                <button onclick="app.setStep(7)" id="tab-step-7" class="tab-btn shrink-0 px-4 py-2.5 rounded-xl font-semibold text-xs md:text-sm border transition flex items-center space-x-2">
                    <span class="w-5 h-5 rounded-full bg-teal/20 text-teal flex items-center justify-center text-xs font-bold">7</span>
                    <span>Audit Desain AI</span>
                </button>
                <button onclick="app.setStep(8)" id="tab-step-8" class="tab-btn shrink-0 px-4 py-2.5 rounded-xl font-semibold text-xs md:text-sm border transition flex items-center space-x-2">
                    <span class="w-5 h-5 rounded-full bg-teal/20 text-teal flex items-center justify-center text-xs font-bold">8</span>
                    <span>Output &amp; Ekspor</span>
                </button>
            </div>

            <!-- STEP 1: ASESMEN & KEBUTUHAN MURID -->
            <div id="step-content-1" class="step-panel space-y-6">
                <div class="canvas-card p-6 border border-slate-200">
                    <div class="flex justify-between items-center mb-4 pb-2 border-b">
                        <div>
                            <h3 class="text-lg font-bold text-navy">1. Asesmen Kebutuhan Murid &amp; Identifikasi Masalah</h3>
                            <p class="text-xs text-slate-500">Didasarkan pada data faktual murid, potensi, serta perubahan yang diharapkan.</p>
                        </div>
                        <button onclick="app.openAiAssistant('asesmen')" class="bg-teal/10 hover:bg-teal/20 text-teal font-semibold px-3 py-1.5 rounded-lg text-xs flex items-center space-x-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="sparkles" aria-hidden="true" class="lucide lucide-sparkles w-4 h-4"><path d="M11.017 2.814a1 1 0 0 1 1.966 0l1.051 5.558a2 2 0 0 0 1.594 1.594l5.558 1.051a1 1 0 0 1 0 1.966l-5.558 1.051a2 2 0 0 0-1.594 1.594l-1.051 5.558a1 1 0 0 1-1.966 0l-1.051-5.558a2 2 0 0 0-1.594-1.594l-5.558-1.051a1 1 0 0 1 0-1.966l5.558-1.051a2 2 0 0 0 1.594-1.594z"></path><path d="M20 2v4"></path><path d="M22 4h-4"></path><circle cx="4" cy="20" r="2"></circle></svg>
                            <span>Bantu Asesmen (AI)</span>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Topik Layanan BK *</label>
                            <input type="text" id="field-topik" onchange="app.saveField('topik', this.value)" placeholder="Contoh: Mengelola Kecemasan Ujian" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Bidang Layanan *</label>
                            <select id="field-bidang" onchange="app.saveField('bidang', this.value)" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal outline-none">
                                <option value="">-- Pilih Bidang BK --</option>
                                <option value="Pribadi">Pribadi</option>
                                <option value="Sosial">Sosial</option>
                                <option value="Belajar">Belajar</option>
                                <option value="Karier">Karier</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Komponen Layanan *</label>
                            <select id="field-komponen" onchange="app.saveField('komponen', this.value)" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal outline-none">
                                <option value="">-- Pilih Komponen --</option>
                                <option value="Layanan Dasar">Layanan Dasar</option>
                                <option value="Layanan Responsif">Layanan Responsif</option>
                                <option value="Perencanaan Individual">Perencanaan Individual</option>
                                <option value="Dukungan Sistem">Dukungan Sistem</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kondisi / Fenomena Lapangan (Fakta Data) *</label>
                            <textarea id="field-kondisi" onchange="app.saveField('kondisi', this.value)" rows="3" placeholder="Jelaskan fakta empiris yang ditemukan di kelas atau data hasil DCM/AKPD..." class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal outline-none"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kebutuhan Perkembangan Murid *</label>
                            <textarea id="field-kebutuhan" onchange="app.saveField('kebutuhan', this.value)" rows="3" placeholder="Perubahan atau kemampuan nyata yang dibutuhkan murid saat ini..." class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal outline-none"></textarea>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Potensi &amp; Kekuatan Murid Yang Dapat Diberdayakan</label>
                            <textarea id="field-potensi" onchange="app.saveField('potensi', this.value)" rows="3" placeholder="Aset pribadi, antusiasme, modal sosial, atau kemampuan unik murid..." class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal outline-none"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Perubahan Konkret Yang Diharapkan (Student Growth)</label>
                            <textarea id="field-perubahan" onchange="app.saveField('perubahan', this.value)" rows="3" placeholder="Sikap, pemahaman, atau tindakan nyata murid setelah layanan..." class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal outline-none"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 2: JALUR KURIKULUM & INTEGRASI -->
            <div id="step-content-2" class="step-panel hidden space-y-6">
                <div class="canvas-card p-6 border border-slate-200">
                    <div class="flex justify-between items-center mb-4 pb-2 border-b">
                        <div>
                            <h3 class="text-lg font-bold text-navy">2. Penyelarasan Kurikulum &amp; Institusi</h3>
                            <p class="text-xs text-slate-500">Sesuaikan dengan institusi, fase perkembangan, SKKPD, dan Panca Cinta (Kemenag).</p>
                        </div>
                    </div>

                    <!-- Jalur Choice -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div onclick="app.setJalur('Kemendikdasmen')" id="jalur-card-kemendik" class="p-4 rounded-xl border-2 cursor-pointer transition flex items-start space-x-3 border-teal bg-teal/5">
                            <input type="radio" name="jalur" id="radio-kemendik" checked="" class="mt-1">
                            <div>
                                <h4 class="font-bold text-navy text-sm">Jalur KEMENDIKDASMEN</h4>
                                <p class="text-xs text-slate-600 mt-1">Untuk SD, SMP, SMA, dan SMK. Berbasis Capaian Layanan &amp; SKKPD.</p>
                            </div>
                        </div>
                        <div onclick="app.setJalur('Kemenag')" id="jalur-card-kemenag" class="p-4 rounded-xl border-2 cursor-pointer transition flex items-start space-x-3 border-slate-200">
                            <input type="radio" name="jalur" id="radio-kemenag" class="mt-1">
                            <div>
                                <h4 class="font-bold text-navy text-sm">Jalur KEMENTERIAN AGAMA</h4>
                                <p class="text-xs text-slate-600 mt-1">Untuk MI, MTs, MA, MAK. Dilengkapi Kurikulum Berbasis Cinta, Panca Cinta &amp; Ekoteologi.</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Jenjang Pendidikan *</label>
                            <select id="field-jenjang" onchange="app.saveField('jenjang', this.value)" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal outline-none">
                                <option value="">-- Pilih Jenjang --</option>
                                <option value="SD/MI">SD / MI</option>
                                <option value="SMP/MTs">SMP / MTs</option>
                                <option value="SMA/MA/SMK/MAK">SMA / MA / SMK / MAK</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Fase Kurikulum *</label>
                            <select id="field-fase" onchange="app.saveField('fase', this.value)" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal outline-none">
                                <option value="">-- Pilih Fase --</option>
                                <option value="Fase A (Kelas 1-2 SD)">Fase A (Kelas 1-2 SD/MI)</option>
                                <option value="Fase B (Kelas 3-4 SD)">Fase B (Kelas 3-4 SD/MI)</option>
                                <option value="Fase C (Kelas 5-6 SD)">Fase C (Kelas 5-6 SD/MI)</option>
                                <option value="Fase D (Kelas 7-9 SMP)">Fase D (Kelas 7-9 SMP/MTs)</option>
                                <option value="Fase E (Kelas 10 SMA)">Fase E (Kelas 10 SMA/MA/SMK)</option>
                                <option value="Fase F (Kelas 11-12 SMA)">Fase F (Kelas 11-12 SMA/MA/SMK)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Sasaran Kelas / Semester *</label>
                            <input type="text" id="field-sasaran" onchange="app.saveField('sasaran', this.value)" placeholder="Contoh: Kelas X-1 / Semester Ganjil" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal outline-none">
                        </div>
                    </div>

                    <!-- Kemenag Special Section -->
                    <div id="kemenag-special-panel" class="hidden p-4 bg-emerald-50 rounded-xl border border-emerald-200 mb-4 space-y-3">
                        <h4 class="text-xs font-bold text-emerald-900 uppercase tracking-wider flex items-center space-x-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="heart" aria-hidden="true" class="lucide lucide-heart w-4 h-4 text-emerald-600"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"></path></svg>
                            <span>Pilar Panca Cinta &amp; Ekoteologi (Kemenag)</span>
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 text-xs">
                            <label class="flex items-center space-x-2 bg-white p-2 rounded border border-emerald-100">
                                <input type="checkbox" onchange="app.togglePancaCinta('Cinta Allah &amp; Rasul')" class="pc-check" value="Cinta Allah &amp; Rasul">
                                <span>1. Cinta Allah &amp; Rasul</span>
                            </label>
                            <label class="flex items-center space-x-2 bg-white p-2 rounded border border-emerald-100">
                                <input type="checkbox" onchange="app.togglePancaCinta('Cinta Ilmu Pengetahuan')" class="pc-check" value="Cinta Ilmu Pengetahuan">
                                <span>2. Cinta Ilmu Pengetahuan</span>
                            </label>
                            <label class="flex items-center space-x-2 bg-white p-2 rounded border border-emerald-100">
                                <input type="checkbox" onchange="app.togglePancaCinta('Cinta Diri &amp; Sesama')" class="pc-check" value="Cinta Diri &amp; Sesama">
                                <span>3. Cinta Diri &amp; Sesama</span>
                            </label>
                            <label class="flex items-center space-x-2 bg-white p-2 rounded border border-emerald-100">
                                <input type="checkbox" onchange="app.togglePancaCinta('Cinta Lingkungan (Ekoteologi)')" class="pc-check" value="Cinta Lingkungan (Ekoteologi)">
                                <span>4. Cinta Lingkungan</span>
                            </label>
                            <label class="flex items-center space-x-2 bg-white p-2 rounded border border-emerald-100">
                                <input type="checkbox" onchange="app.togglePancaCinta('Cinta Bangsa &amp; Negeri')" class="pc-check" value="Cinta Bangsa &amp; Negeri">
                                <span>5. Cinta Bangsa &amp; Negeri</span>
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Standar Kompetensi Kemandirian Peserta Didik (SKKPD)</label>
                            <input type="text" id="field-skkpd" onchange="app.saveField('skkpd', this.value)" placeholder="Contoh: Pengembangan Pribadi / Kematangan Emosi" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Capaian Layanan BK (CL BK)</label>
                            <input type="text" id="field-clbk" onchange="app.saveField('clbk', this.value)" placeholder="Uraian Capaian Layanan sesuai panduan kurikulum..." class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal outline-none">
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 3: PILIHAN 7 JURUS BK HEBAT -->
            <div id="step-content-3" class="step-panel hidden space-y-6">
                <div class="canvas-card p-6 border border-slate-200">
                    <div class="flex justify-between items-center mb-4 pb-2 border-b">
                        <div>
                            <h3 class="text-lg font-bold text-navy">3. Pemilihan 7 Jurus BK Hebat</h3>
                            <p class="text-xs text-slate-500">Pilih 1–2 jurus utama yang paling relevan untuk menjawab kebutuhan murid.</p>
                        </div>
                        <button onclick="app.recommendJurusAI()" class="bg-teal/10 hover:bg-teal/20 text-teal font-semibold px-3 py-1.5 rounded-lg text-xs flex items-center space-x-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="sparkles" aria-hidden="true" class="lucide lucide-sparkles w-4 h-4"><path d="M11.017 2.814a1 1 0 0 1 1.966 0l1.051 5.558a2 2 0 0 0 1.594 1.594l5.558 1.051a1 1 0 0 1 0 1.966l-5.558 1.051a2 2 0 0 0-1.594 1.594l-1.051 5.558a1 1 0 0 1-1.966 0l-1.051-5.558a2 2 0 0 0-1.594-1.594l-5.558-1.051a1 1 0 0 1 0-1.966l5.558-1.051a2 2 0 0 0 1.594-1.594z"></path><path d="M20 2v4"></path><path d="M22 4h-4"></path><circle cx="4" cy="20" r="2"></circle></svg>
                            <span>Rekomendasi Jurus (AI)</span>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="jurus-grid">
                        <!-- Jurus 1 -->
                        <div onclick="app.toggleJurus(1)" id="jurus-card-1" class="jurus-card p-4 border border-slate-200 rounded-xl cursor-pointer hover:border-teal transition relative">
                            <div class="flex items-start space-x-3">
                                <div class="p-2.5 rounded-lg bg-teal/10 text-teal font-black text-base">J1</div>
                                <div>
                                    <h4 class="font-bold text-navy text-sm">Kenali Potensi</h4>
                                    <p class="text-xs text-slate-500 mt-0.5">Asesmen Minat, Bakat &amp; Kekuatan Diri</p>
                                </div>
                            </div>
                        </div>
                        <!-- Jurus 2 -->
                        <div onclick="app.toggleJurus(2)" id="jurus-card-2" class="jurus-card p-4 border border-slate-200 rounded-xl cursor-pointer hover:border-teal transition relative">
                            <div class="flex items-start space-x-3">
                                <div class="p-2.5 rounded-lg bg-teal/10 text-teal font-black text-base">J2</div>
                                <div>
                                    <h4 class="font-bold text-navy text-sm">Kelola Emosi</h4>
                                    <p class="text-xs text-slate-500 mt-0.5">Pembelajaran Sosial Emosional (SEL)</p>
                                </div>
                            </div>
                        </div>
                        <!-- Jurus 3 -->
                        <div onclick="app.toggleJurus(3)" id="jurus-card-3" class="jurus-card p-4 border border-slate-200 rounded-xl cursor-pointer hover:border-teal transition relative">
                            <div class="flex items-start space-x-3">
                                <div class="p-2.5 rounded-lg bg-teal/10 text-teal font-black text-base">J3</div>
                                <div>
                                    <h4 class="font-bold text-navy text-sm">Tumbuhkan Resiliensi</h4>
                                    <p class="text-xs text-slate-500 mt-0.5">Daya Lenting &amp; Keberanian Bangkit</p>
                                </div>
                            </div>
                        </div>
                        <!-- Jurus 4 -->
                        <div onclick="app.toggleJurus(4)" id="jurus-card-4" class="jurus-card p-4 border border-slate-200 rounded-xl cursor-pointer hover:border-teal transition relative">
                            <div class="flex items-start space-x-3">
                                <div class="p-2.5 rounded-lg bg-teal/10 text-teal font-black text-base">J4</div>
                                <div>
                                    <h4 class="font-bold text-navy text-sm">Jaga Konsistensi</h4>
                                    <p class="text-xs text-slate-500 mt-0.5">Kebiasaan Berkesadaran &amp; Disiplin</p>
                                </div>
                            </div>
                        </div>
                        <!-- Jurus 5 -->
                        <div onclick="app.toggleJurus(5)" id="jurus-card-5" class="jurus-card p-4 border border-slate-200 rounded-xl cursor-pointer hover:border-teal transition relative">
                            <div class="flex items-start space-x-3">
                                <div class="p-2.5 rounded-lg bg-teal/10 text-teal font-black text-base">J5</div>
                                <div>
                                    <h4 class="font-bold text-navy text-sm">Jalin Koneksi</h4>
                                    <p class="text-xs text-slate-500 mt-0.5">Komunikasi Empatik &amp; Hubungan Positif</p>
                                </div>
                            </div>
                        </div>
                        <!-- Jurus 6 -->
                        <div onclick="app.toggleJurus(6)" id="jurus-card-6" class="jurus-card p-4 border border-slate-200 rounded-xl cursor-pointer hover:border-teal transition relative">
                            <div class="flex items-start space-x-3">
                                <div class="p-2.5 rounded-lg bg-teal/10 text-teal font-black text-base">J6</div>
                                <div>
                                    <h4 class="font-bold text-navy text-sm">Bangun Kolaborasi</h4>
                                    <p class="text-xs text-slate-500 mt-0.5">Kerja Sama Multipihak &amp; Sinergi</p>
                                </div>
                            </div>
                        </div>
                        <!-- Jurus 7 -->
                        <div onclick="app.toggleJurus(7)" id="jurus-card-7" class="jurus-card p-4 border border-slate-200 rounded-xl cursor-pointer hover:border-teal transition relative">
                            <div class="flex items-start space-x-3">
                                <div class="p-2.5 rounded-lg bg-teal/10 text-teal font-black text-base">J7</div>
                                <div>
                                    <h4 class="font-bold text-navy text-sm">Menata Situasi</h4>
                                    <p class="text-xs text-slate-500 mt-0.5">Lingkungan Aman, Nyaman &amp; Menggembirakan</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 p-4 bg-slate-50 rounded-xl border border-slate-200">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Rasionalitas &amp; Alasan Pemilihan Jurus</label>
                        <textarea id="field-jurus-rasional" onchange="app.saveField('jurusRasional', this.value)" rows="2" placeholder="Mengapa jurus ini dipilih untuk mencapai kriteria keberhasilan murid..." class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal outline-none"></textarea>
                    </div>
                </div>
            </div>

            <!-- STEP 4: DESAIN PENGALAMAN ARKA -->
            <div id="step-content-4" class="step-panel hidden space-y-6">
                <div class="canvas-card p-6 border border-slate-200">
                    <div class="flex justify-between items-center mb-4 pb-2 border-b">
                        <div>
                            <h3 class="text-lg font-bold text-navy">4. Desain Pengalaman Layanan Berbasis Alur ARKA</h3>
                            <p class="text-xs text-slate-500">Aktivitas -&gt; Refleksi -&gt; Konseptualisasi -&gt; Aplikasi (Saling berkesinambungan).</p>
                        </div>
                        <button onclick="app.generateArkaAI()" class="bg-teal/10 hover:bg-teal/20 text-teal font-semibold px-3 py-1.5 rounded-lg text-xs flex items-center space-x-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="sparkles" aria-hidden="true" class="lucide lucide-sparkles w-4 h-4"><path d="M11.017 2.814a1 1 0 0 1 1.966 0l1.051 5.558a2 2 0 0 0 1.594 1.594l5.558 1.051a1 1 0 0 1 0 1.966l-5.558 1.051a2 2 0 0 0-1.594 1.594l-1.051 5.558a1 1 0 0 1-1.966 0l-1.051-5.558a2 2 0 0 0-1.594-1.594l-5.558-1.051a1 1 0 0 1 0-1.966l5.558-1.051a2 2 0 0 0 1.594-1.594z"></path><path d="M20 2v4"></path><path d="M22 4h-4"></path><circle cx="4" cy="20" r="2"></circle></svg>
                            <span>Rancang ARKA Otomatis (AI)</span>
                        </button>
                    </div>

                    <!-- ARKA Tabs/Accordion Layout -->
                    <div class="space-y-4">
                        <!-- TAHAP A: AKTIVITAS -->
                        <div class="p-4 bg-blue-50/50 rounded-xl border border-blue-200">
                            <h4 class="font-bold text-blue-900 text-sm mb-3 flex items-center space-x-2">
                                <span class="px-2 py-0.5 bg-blue-600 text-white rounded text-xs">A</span>
                                <span>A - AKTIVITAS (Murid mengalami stimulus/kegiatan awal)</span>
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Aktivitas Guru BK</label>
                                    <textarea id="arka-a-guru" onchange="app.saveArka('a', 'guru', this.value)" rows="2" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg outline-none focus:ring-1 focus:ring-blue-500" placeholder="Guru menyajikan simulasi/video/permainan..."></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Aktivitas Murid</label>
                                    <textarea id="arka-a-murid" onchange="app.saveArka('a', 'murid', this.value)" rows="2" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg outline-none focus:ring-1 focus:ring-blue-500" placeholder="Murid melakukan pengamatan/praktik/simulasi..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- TAHAP R: REFLEKSI -->
                        <div class="p-4 bg-teal-50/50 rounded-xl border border-teal-200">
                            <h4 class="font-bold text-teal-900 text-sm mb-3 flex items-center space-x-2">
                                <span class="px-2 py-0.5 bg-teal-600 text-white rounded text-xs">R</span>
                                <span>R - REFLEKSI (Menggali rasa, pikiran &amp; pengalaman)</span>
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pertanyaan Pemantik &amp; Peran Guru</label>
                                    <textarea id="arka-r-guru" onchange="app.saveArka('r', 'guru', this.value)" rows="2" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg outline-none focus:ring-1 focus:ring-teal-500" placeholder="Pertanyaan: 'Apa yang kalian rasakan saat...?'"></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Aktivitas &amp; Respon Murid</label>
                                    <textarea id="arka-r-murid" onchange="app.saveArka('r', 'murid', this.value)" rows="2" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg outline-none focus:ring-1 focus:ring-teal-500" placeholder="Murid mengungkapkan perasaan &amp; pemikirannya di LKPD..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- TAHAP K: KONSEPTUALISASI -->
                        <div class="p-4 bg-amber-50/50 rounded-xl border border-amber-200">
                            <h4 class="font-bold text-amber-900 text-sm mb-3 flex items-center space-x-2">
                                <span class="px-2 py-0.5 bg-amber-600 text-white rounded text-xs">K</span>
                                <span>K - KONSEPTUALISASI (Guru menghubungkan ke konsep BK)</span>
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Penyampaian Konsep Guru</label>
                                    <textarea id="arka-k-guru" onchange="app.saveArka('k', 'guru', this.value)" rows="2" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg outline-none focus:ring-1 focus:ring-amber-500" placeholder="Guru menjelaskan materi pokok &amp; pesan kunci..."></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Aktivitas Murid</label>
                                    <textarea id="arka-k-murid" onchange="app.saveArka('k', 'murid', this.value)" rows="2" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg outline-none focus:ring-1 focus:ring-amber-500" placeholder="Murid mengaitkan pengalamannya dengan konsep..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- TAHAP A: APLIKASI -->
                        <div class="p-4 bg-emerald-50/50 rounded-xl border border-emerald-200">
                            <h4 class="font-bold text-emerald-900 text-sm mb-3 flex items-center space-x-2">
                                <span class="px-2 py-0.5 bg-emerald-600 text-white rounded text-xs">A</span>
                                <span>A - APLIKASI (Rencana tindakan nyata dalam kehidupan)</span>
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Fasilitasi Guru BK</label>
                                    <textarea id="arka-ap-guru" onchange="app.saveArka('ap', 'guru', this.value)" rows="2" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg outline-none focus:ring-1 focus:ring-emerald-500" placeholder="Guru membimbing penyusunan action plan di LKPD..."></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Aksi Nyata &amp; Komitmen Murid</label>
                                    <textarea id="arka-ap-murid" onchange="app.saveArka('ap', 'murid', this.value)" rows="2" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg outline-none focus:ring-1 focus:ring-emerald-500" placeholder="Murid menetapkan target komitmen &amp; langkah konkret..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 5: KALIBRASI PEMBELAJARAN MENDALAM -->
            <div id="step-content-5" class="step-panel hidden space-y-6">
                <div class="canvas-card p-6 border border-slate-200">
                    <div class="flex justify-between items-center mb-4 pb-2 border-b">
                        <div>
                            <h3 class="text-lg font-bold text-navy">5. Kalibrasi Pembelajaran Mendalam (Deep Learning)</h3>
                            <p class="text-xs text-slate-500">Narasi kontekstual Mindful, Meaningful, dan Joyful Learning khusus topik ini.</p>
                        </div>
                        <button onclick="app.calibrateDeepLearningAI()" class="bg-teal/10 hover:bg-teal/20 text-teal font-semibold px-3 py-1.5 rounded-lg text-xs flex items-center space-x-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="sparkles" aria-hidden="true" class="lucide lucide-sparkles w-4 h-4"><path d="M11.017 2.814a1 1 0 0 1 1.966 0l1.051 5.558a2 2 0 0 0 1.594 1.594l5.558 1.051a1 1 0 0 1 0 1.966l-5.558 1.051a2 2 0 0 0-1.594 1.594l-1.051 5.558a1 1 0 0 1-1.966 0l-1.051-5.558a2 2 0 0 0-1.594-1.594l-5.558-1.051a1 1 0 0 1 0-1.966l5.558-1.051a2 2 0 0 0 1.594-1.594z"></path><path d="M20 2v4"></path><path d="M22 4h-4"></path><circle cx="4" cy="20" r="2"></circle></svg>
                            <span>Kalibrasi Kontekstual (AI)</span>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-navy mb-1 flex items-center space-x-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="brain" aria-hidden="true" class="lucide lucide-brain w-4 h-4 text-teal"><path d="M12 18V5"></path><path d="M15 13a4.17 4.17 0 01-3-4 4.17 4.17 0 01-3 4"></path><path d="M17.707 6.294A3 3 0 1012 5a3 3 0 10-5.707 1.294"></path><path d="M17.996 5.125a4 4 0 012.526 5.77"></path><path d="M18 18a4 4 0 002-7.465"></path><path d="M19.967 17.482a4 4 0 11-7.967.517 4 4 0 11-7.967-.517"></path><path d="M6 18a4 4 0 01-2-7.465"></path><path d="M6.004 5.125a4 4 0 00-2.526 5.77"></path></svg>
                                <span>Mindful Learning (Berkesadaran)</span>
                            </label>
                            <textarea id="field-mindful" onchange="app.saveField('mindful', this.value)" rows="3" placeholder="Uraian bagaimana murid menyadari tujuan, mengamati pikiran, dan terlibat aktif..." class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal outline-none"></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-navy mb-1 flex items-center space-x-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="compass" aria-hidden="true" class="lucide lucide-compass w-4 h-4 text-gold"><circle cx="12" cy="12" r="10"></circle><path d="m16.24 7.76-1.804 5.411a2 2 0 0 1-1.265 1.265L7.76 16.24l1.804-5.411a2 2 0 0 1 1.265-1.265z"></path></svg>
                                <span>Meaningful Learning (Bermakna)</span>
                            </label>
                            <textarea id="field-meaningful" onchange="app.saveField('meaningful', this.value)" rows="3" placeholder="Uraian bagaimana materi terhubung dengan pengalaman hidup nyata murid..." class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal outline-none"></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-navy mb-1 flex items-center space-x-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="smile" aria-hidden="true" class="lucide lucide-smile w-4 h-4 text-emerald-500"><path d="M15 10V9"></path><path d="M16.472 15a6 6 0 01-8.943 0"></path><path d="M9 10V9"></path><circle cx="12" cy="12" r="10"></circle></svg>
                                <span>Joyful Learning (Menggembirakan)</span>
                            </label>
                            <textarea id="field-joyful" onchange="app.saveField('joyful', this.value)" rows="3" placeholder="Uraian suasana aman, bebas judgement, dan apresiatif yang dibangun guru..." class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal outline-none"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 6: EVALUASI & BUKTI PERKEMBANGAN -->
            <div id="step-content-6" class="step-panel hidden space-y-6">
                <div class="canvas-card p-6 border border-slate-200">
                    <div class="flex justify-between items-center mb-4 pb-2 border-b">
                        <div>
                            <h3 class="text-lg font-bold text-navy">6. Asesmen Evaluasi &amp; Bukti Perkembangan</h3>
                            <p class="text-xs text-slate-500">Membedakan bukti partisipasi fisik dengan bukti perkembangan nyata murid.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                            <h4 class="font-bold text-xs uppercase tracking-wider text-slate-700 mb-2">Evaluasi Proses (Kehadiran &amp; Antusiasme)</h4>
                            <textarea id="field-eval-proses" onchange="app.saveField('evalProses', this.value)" rows="3" placeholder="Indikator keterlibatan, respon emosi, dan dinamika saat layanan berlangsung..." class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg outline-none"></textarea>
                        </div>
                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                            <h4 class="font-bold text-xs uppercase tracking-wider text-slate-700 mb-2">Evaluasi Hasil (Student Growth)</h4>
                            <textarea id="field-eval-hasil" onchange="app.saveField('evalHasil', this.value)" rows="3" placeholder="Indikator perubahan pemahaman, sikap, dan komitmen aksi nyata murid..." class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg outline-none"></textarea>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Rencana Tindak Lanjut (RTL)</label>
                        <textarea id="field-rtl" onchange="app.saveField('rtl', this.value)" rows="2" placeholder="Langkah pendampingan lanjutan (konseling individual, kelompok, atau kolaborasi orang tua)..." class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg outline-none"></textarea>
                    </div>
                </div>
            </div>

            <!-- STEP 7: AUDIT DESAIN AI -->
            <div id="step-content-7" class="step-panel hidden space-y-6">
                <div class="canvas-card p-6 border border-slate-200">
                    <div class="flex justify-between items-center mb-4 pb-2 border-b">
                        <div>
                            <h3 class="text-lg font-bold text-navy">7. AI Design Reviewer &amp; Audit Layanan</h3>
                            <p class="text-xs text-slate-500">Lakukan audit 8 kriteria untuk menguji kesiapan dan konsistensi rancangan.</p>
                        </div>
                        <button onclick="app.runAiAudit()" class="bg-navy hover:bg-navy/90 text-white font-semibold px-4 py-2 rounded-lg text-xs flex items-center space-x-1 shadow">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="shield-check" aria-hidden="true" class="lucide lucide-shield-check w-4 h-4 text-gold"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"></path><path d="m9 12 2 2 4-4"></path></svg>
                            <span>JALANKAN AUDIT DESAIN SEKARANG</span>
                        </button>
                    </div>

                    <!-- Audit Score & Breakdown Display -->
                    <div id="audit-results-panel" class="space-y-4">
                        <div class="p-4 bg-slate-100 rounded-xl text-center text-slate-500 text-sm">
                            Tekan tombol <strong>"JALANKAN AUDIT DESAIN SEKARANG"</strong> untuk mendapatkan analisis AI komprehensif.
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 8: OUTPUT & EKSPOR DOKUMEN -->
            <div id="step-content-8" class="step-panel hidden space-y-6">
                <div class="canvas-card p-6 border border-slate-200">
                    <div class="flex justify-between items-center mb-4 pb-2 border-b">
                        <div>
                            <h3 class="text-lg font-bold text-navy">8. Pusat Dokumen, Preview &amp; Unduhan</h3>
                            <p class="text-xs text-slate-500">Pratinjau dan unduh RPL BK, Service Design Canvas, LKPD, dan Instrumen Evaluasi.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Card RPL -->
                        <div class="p-4 border border-slate-200 rounded-xl bg-slate-50 flex flex-col justify-between">
                            <div>
                                <h4 class="font-bold text-navy text-sm mb-1">RPL BK Profesional</h4>
                                <p class="text-xs text-slate-500 mb-4">Lengkap dengan rasional, ARKA, Deep Learning &amp; Pengesahan.</p>
                            </div>
                            <div class="space-y-2">
                                <button onclick="app.previewDocument('rpl')" class="w-full bg-white hover:bg-slate-100 border border-slate-300 text-slate-700 py-1.5 rounded-lg text-xs font-semibold">Preview RPL</button>
                                <button onclick=\"app.exportPDF('rpl')\" class=\"w-full bg-teal hover:bg-teal/90 text-white py-1.5 rounded-lg text-xs font-semibold\">Unduh PDF</button>
                            </div>
                        </div>

                        <!-- Card Canvas -->
                        <div class="p-4 border border-slate-200 rounded-xl bg-slate-50 flex flex-col justify-between">
                            <div>
                                <h4 class="font-bold text-navy text-sm mb-1">Service Design Canvas</h4>
                                <p class="text-xs text-slate-500 mb-4">Ringkasan matrik visual 8 pilar desain universal.</p>
                            </div>
                            <div class="space-y-2">
                                <button onclick="app.previewDocument('canvas')" class="w-full bg-white hover:bg-slate-100 border border-slate-300 text-slate-700 py-1.5 rounded-lg text-xs font-semibold">Preview Canvas</button>
                                <button onclick="app.exportPDF('canvas')" class="w-full bg-teal hover:bg-teal/90 text-white py-1.5 rounded-lg text-xs font-semibold">Unduh Canvas PDF</button>
                            </div>
                        </div>

                        <!-- Card LKPD -->
                        <div class="p-4 border border-slate-200 rounded-xl bg-slate-50 flex flex-col justify-between">
                            <div>
                                <h4 class="font-bold text-navy text-sm mb-1">LKPD Murid</h4>
                                <p class="text-xs text-slate-500 mb-4">Lembar Kerja Peserta Didik berbasis aktivitas ARKA.</p>
                            </div>
                            <div class="space-y-2">
                                <button onclick="app.previewDocument('lkpd')" class="w-full bg-white hover:bg-slate-100 border border-slate-300 text-slate-700 py-1.5 rounded-lg text-xs font-semibold">Preview LKPD</button>
                                <button onclick=\"app.exportPDF('lkpd')\" class=\"w-full bg-teal hover:bg-teal/90 text-white py-1.5 rounded-lg text-xs font-semibold\">Unduh PDF</button>
                            </div>
                        </div>

                        <!-- Card Instrumen Evaluasi -->
                        <div class="p-4 border border-slate-200 rounded-xl bg-slate-50 flex flex-col justify-between">
                            <div>
                                <h4 class="font-bold text-navy text-sm mb-1">Instrumen Evaluasi</h4>
                                <p class="text-xs text-slate-500 mb-4">Lembar observasi proses &amp; angket evaluasi hasil murid.</p>
                            </div>
                            <div class="space-y-2">
                                <button onclick="app.previewDocument('eval')" class="w-full bg-white hover:bg-slate-100 border border-slate-300 text-slate-700 py-1.5 rounded-lg text-xs font-semibold">Preview Instrumen</button>
                                <button onclick=\"app.exportPDF('eval')\" class=\"w-full bg-teal hover:bg-teal/90 text-white py-1.5 rounded-lg text-xs font-semibold\">Unduh PDF</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step Navigation Buttons -->
            <div class="flex justify-between items-center pt-4 border-t border-slate-200 no-print">
                <button onclick="app.prevStep()" id="btn-prev-step" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold px-4 py-2 rounded-xl text-xs md:text-sm transition flex items-center space-x-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="chevron-left" aria-hidden="true" class="lucide lucide-chevron-left w-4 h-4"><path d="m15 18-6-6 6-6"></path></svg>
                    <span>Sebelumnya</span>
                </button>
                <button onclick="app.nextStep()" id="btn-next-step" class="bg-teal hover:bg-teal/90 text-white font-semibold px-5 py-2 rounded-xl text-xs md:text-sm transition flex items-center space-x-1 shadow">
                    <span>Langkah Berikutnya</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="chevron-right" aria-hidden="true" class="lucide lucide-chevron-right w-4 h-4"><path d="m9 18 6-6-6-6"></path></svg>
                </button>
            </div>
        </section>

    </main>

    <!-- Mandatory Creator Credit Footer (Static & Permanent) -->
    <footer class="bg-navy text-slate-300 border-t border-slate-800 py-6 mt-12 no-print">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between text-xs">
            <div class="mb-4 md:mb-0 text-center md:text-left">
                <p class="font-bold text-white">7 JURUS BK HEBAT - UNIVERSAL SERVICE DESIGN CANVAS</p>
                <p class="text-slate-400">From Student Needs to Student Growth.</p>
            </div>
            
            <!-- Creator Badge with Active Instagram Link -->
            <div class="flex items-center space-x-2 bg-slate-800/80 px-4 py-2 rounded-full border border-slate-700">
                <span class="text-slate-400">Created by:</span>
                <a href="https://www.instagram.com/ekachandraoktaviani/" target="_blank" rel="noopener noreferrer" class="font-bold text-gold hover:text-amber-300 flex items-center space-x-1 transition">
                    <i data-lucide="instagram" class="w-4 h-4 text-pink-400"></i>
                    <span>@ekachandraoktaviani</span>
                </a>
            </div>
        </div>
    </footer>

    <!-- MODAL 1: TEACHER PROFILE EDIT MODAL -->
    <div id="modal-profile" style="z-index:99999;" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center border-b pb-3">
                <h3 class="font-bold text-navy text-base flex items-center space-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="user-check" aria-hidden="true" class="lucide lucide-user-check w-5 h-5 text-teal"><path d="m16 11 2 2 4-4"></path><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
                    <span>Profil Guru BK / Identitas Penyusun RPL</span>
                </h3>
                <button onclick="app.closeProfileModal()" class="text-slate-400 hover:text-slate-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="x" aria-hidden="true" class="lucide lucide-x w-5 h-5"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
                </button>
            </div>

            <p class="text-xs text-slate-500">
                Identitas ini akan otomatis dicantumkan dalam RPL BK dan Lembar Pengesahan.
            </p>

            <form onsubmit="app.saveProfile(event)" class="space-y-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap Guru BK / Konselor *</label>
                    <input type="text" id="prof-nama" required="" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg outline-none focus:ring-2 focus:ring-teal">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">NIP (Opsional)</label>
                        <input type="text" id="prof-nip" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg outline-none focus:ring-2 focus:ring-teal">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Pangkat / Golongan</label>
                        <input type="text" id="prof-pangkat" placeholder="Penata Muda / III/a" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg outline-none focus:ring-2 focus:ring-teal">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Satuan Pendidikan / Sekolah *</label>
                    <input type="text" id="prof-sekolah" required="" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg outline-none focus:ring-2 focus:ring-teal">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kabupaten / Kota *</label>
                        <input type="text" id="prof-kota" required="" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg outline-none focus:ring-2 focus:ring-teal">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Provinsi *</label>
                        <input type="text" id="prof-provinsi" required="" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg outline-none focus:ring-2 focus:ring-teal">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tahun Pelajaran *</label>
                    <input type="text" id="prof-tahun" required="" placeholder="2026/2027" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg outline-none focus:ring-2 focus:ring-teal">
                </div>

                <div class="pt-3 flex justify-end space-x-2">
                    <button type="button" onclick="app.closeProfileModal()" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-teal text-white rounded-lg text-xs font-semibold shadow hover:bg-teal/90">SIMPAN PROFIL GURU</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: DOCUMENT PREVIEW MODAL -->
    <div id="modal-preview" style="z-index:99999;" class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-4xl w-full h-[90vh] flex flex-col shadow-2xl overflow-hidden">
            <div class="bg-navy text-white px-6 py-4 flex items-center justify-between">
                <h3 id="preview-modal-title" class="font-bold text-base">Pratinjau Dokumen</h3>
                <button onclick="app.closePreviewModal()" class="text-slate-300 hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="x" aria-hidden="true" class="lucide lucide-x w-6 h-6"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
                </button>
            </div>
            <div id="preview-modal-body" class="p-8 flex-grow overflow-y-auto bg-slate-50 font-sans">
                <!-- Dynamic Rendered Content -->
            </div>
            <div class="p-4 bg-white border-t flex justify-end space-x-3">
                <button onclick="app.closePreviewModal()" class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold">Tutup</button>
            </div>
        </div>
    </div>

    <!-- Main Application Engine JavaScript Code -->
        </div>
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode('ppb_7jurus_canvas', 'ppb_render_7jurus_canvas_shortcode');
add_shortcode('ppb_service_design_canvas', 'ppb_render_7jurus_canvas_shortcode');
