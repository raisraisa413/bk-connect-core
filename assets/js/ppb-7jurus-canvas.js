const EXAMPLE_DATA = {
            topik: "Aku Punya Kekuatan (Mengenali & Mengoptimalkan Potensi Diri)",
            bidang: "Pribadi",
            komponen: "Layanan Dasar",
            kondisi: "Berdasarkan data asesmen awal, 65% murid kelas X belum mampu mengidentifikasi minat, bakat, dan kekuatan diri secara spesifik. Sebagian besar cenderung bersikap ragu-ragu dan belum memiliki rasa percaya diri dalam menentukan target karier.",
            kebutuhan: "Murid membutuhkan kesadaran diri yang mendalam (self-awareness) mengenai potensi pribadi serta strategi konkret untuk mengembangkan potensi tersebut secara konsisten.",
            potensi: "Murid memiliki antusiasme tinggi saat diajak berdiskusi kelompok dan terbuka terhadap refleksi emosional mandiri.",
            perubahan: "Murid mampu mengidentifikasi minimal 3 kekuatan pribadi dan menyusun 1 rencana aksi mingguan untuk mengoptimalkan kekuatannya.",
            jalur: "Kemendikdasmen",
            jenjang: "SMA/MA/SMK/MAK",
            fase: "Fase E (Kelas 10 SMA)",
            sasaran: "Kelas X-1 / Semester Ganjil",
            skkpd: "Pengembangan Pribadi",
            clbk: "Peserta didik mampu mengenali potensi, minat, dan bakat diri serta merencanakan pengembangan potensi secara bertanggung jawab.",
            pancaCinta: [],
            jurusSelected: [1, 4], // J1: Kenali Potensi, J4: Jaga Konsistensi
            jurusRasional: "Jurus 1 dipilih untuk membimbing murid memetakan minat dan bakat secara terstruktur, sedangkan Jurus 4 digunakan untuk menjaga komitmen pengembangan potensi secara berkesadaran.",
            arka: {
                a: {
                    guru: "Guru menyajikan aktivitas 'Kertas Kekuatan' di mana murid menuliskan hal-hal yang dipuji orang lain dari dirinya dan tantangan terbesar yang pernah ditaklukkan.",
                    murid: "Murid aktif mengisi lembar refleksi awal dan berpasangan untuk mendiskusikan temuan awal tentang kekuatan teman."
                },
                r: {
                    guru: "Guru melontarkan pertanyaan pemantik: 'Apa yang kamu rasakan saat menyadari bahwa kamu memiliki kekuatan unik yang belum pernah kamu syukuri?'",
                    murid: "Murid mengekspresikan pemikirannya secara eksplisit di LKPD dan membagikan pengalaman emosionalnya di depan kelas."
                },
                k: {
                    guru: "Guru memberikan penguatan konsep tentang perbedaan antara bakat bawaan dan keterampilan yang dilatih, serta pentingnya growth mindset.",
                    murid: "Murid menyimak dan menghubungkan pengalaman pribadinya dengan matriks potensi diri."
                },
                ap: {
                    guru: "Guru memfasilitasi penyusunan 'Jurnal Konsistensi 7 Hari' untuk menerapkan kekuatan diri dalam aktivitas sekolah.",
                    murid: "Murid menetapkan komitmen tertulis dan menandatangani lembar aksi nyata."
                }
            },
            mindful: "Murid secara sadar diajak untuk mengamati pikiran negatif tentang keterbatasan dirinya dan memfokuskan perhatian pada potensi nyata yang dimiliki tanpa rasa rendah diri.",
            meaningful: "Materi ini terhubung langsung dengan keputusan masa depan murid dalam memilih jurusan dan karier, menjadikan pembelajaran terasa relevan bagi kehidupan nyata.",
            joyful: "Atmosfer kelas dibangun secara hangat dan suportif tanpa ada penghakiman (zero judgement), sehingga setiap murid merasa dihargai dan gembira saat berbagi.",
            evalProses: "Kehadiran 100%, antusiasme murid dalam berdiskusi mencapai 88%, dan respon positif terhadap media refleksi.",
            evalHasil: "85% murid mampu mengidentifikasi minimal 3 potensi utama dan menyusun jurnal aksi konsisten 7 hari.",
            rtl: "Bagi murid yang masih ragu, akan dijadwalkan sesi bimbingan kelompok atau konseling individual."
        };

        class AppEngine {
            constructor() {
                this.currentView = 'dashboard';
                this.currentStep = 1;
                this.mode = 'mandiri'; // 'mandiri' or 'example'
                
                // Active Design Data State
                this.canvasData = this.getEmptyData();
                
                // Teacher Profile State
                this.teacherProfile = JSON.parse(localStorage.getItem('bk_teacher_profile')) || {
                    nama: '',
                    nip: '',
                    pangkat: '',
                    sekolah: '',
                    kota: '',
                    provinsi: '',
                    tahun: '2026/2027'
                };

                this.init();
            }

            init() {
                this.updateProfileBanner();
                this.updateDashboardStats();
                if (window.lucide) { window.lucide.createIcons(); }
            }

            getEmptyData() {
                return {
                    topik: '',
                    bidang: '',
                    komponen: '',
                    kondisi: '',
                    kebutuhan: '',
                    potensi: '',
                    perubahan: '',
                    jalur: 'Kemendikdasmen',
                    jenjang: '',
                    fase: '',
                    sasaran: '',
                    skkpd: '',
                    clbk: '',
                    pancaCinta: [],
                    jurusSelected: [],
                    jurusRasional: '',
                    arka: {
                        a: { guru: '', murid: '' },
                        r: { guru: '', murid: '' },
                        k: { guru: '', murid: '' },
                        ap: { guru: '', murid: '' }
                    },
                    mindful: '',
                    meaningful: '',
                    joyful: '',
                    evalProses: '',
                    evalHasil: '',
                    rtl: ''
                };
            }

            switchView(viewName) {
                this.currentView = viewName;
                document.getElementById('view-dashboard').classList.toggle('hidden', viewName !== 'dashboard');
                document.getElementById('view-canvas').classList.toggle('hidden', viewName !== 'canvas');
                window.scrollTo({ top: 0, behavior: 'smooth' });
                this.updateDashboardStats();
            }

            setStep(stepNum) {
                this.currentStep = stepNum;
                for (let i = 1; i <= 8; i++) {
                    const stepPanel = document.getElementById(`step-content-${i}`);
                    const tabBtn = document.getElementById(`tab-step-${i}`);
                    if (stepPanel) stepPanel.classList.toggle('hidden', i !== stepNum);
                    if (tabBtn) tabBtn.classList.toggle('active', i === stepNum);
                }
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }

            nextStep() {
                if (this.currentStep < 8) this.setStep(this.currentStep + 1);
            }

            prevStep() {
                if (this.currentStep > 1) this.setStep(this.currentStep - 1);
            }

            startFreshDesign() {
                this.mode = 'mandiri';
                this.canvasData = this.getEmptyData();
                this.populateCanvasUI();
                this.switchView('canvas');
                this.setStep(1);
            }

            loadExampleDesign() {
                this.mode = 'example';
                this.canvasData = JSON.parse(JSON.stringify(EXAMPLE_DATA));
                this.populateCanvasUI();
                this.switchView('canvas');
                this.setStep(1);
            }

            confirmReset() {
                if (confirm('Apakah Anda yakin ingin mereset seluruh isian desain? Semua data yang belum tersimpan akan dikosongkan.')) {
                    this.startFreshDesign();
                }
            }

            populateCanvasUI() {
                const d = this.canvasData;
                document.getElementById('field-topik').value = d.topik || '';
                document.getElementById('field-bidang').value = d.bidang || '';
                document.getElementById('field-komponen').value = d.komponen || '';
                document.getElementById('field-kondisi').value = d.kondisi || '';
                document.getElementById('field-kebutuhan').value = d.kebutuhan || '';
                document.getElementById('field-potensi').value = d.potensi || '';
                document.getElementById('field-perubahan').value = d.perubahan || '';

                this.setJalur(d.jalur || 'Kemendikdasmen');
                document.getElementById('field-jenjang').value = d.jenjang || '';
                document.getElementById('field-fase').value = d.fase || '';
                document.getElementById('field-sasaran').value = d.sasaran || '';
                document.getElementById('field-skkpd').value = d.skkpd || '';
                document.getElementById('field-clbk').value = d.clbk || '';

                // Jurus UI
                for (let i = 1; i <= 7; i++) {
                    const card = document.getElementById(`jurus-card-${i}`);
                    if (card) {
                        if (d.jurusSelected.includes(i)) {
                            card.classList.add('selected');
                        } else {
                            card.classList.remove('selected');
                        }
                    }
                }
                document.getElementById('field-jurus-rasional').value = d.jurusRasional || '';

                // ARKA UI
                document.getElementById('arka-a-guru').value = d.arka.a.guru || '';
                document.getElementById('arka-a-murid').value = d.arka.a.murid || '';
                document.getElementById('arka-r-guru').value = d.arka.r.guru || '';
                document.getElementById('arka-r-murid').value = d.arka.r.murid || '';
                document.getElementById('arka-k-guru').value = d.arka.k.guru || '';
                document.getElementById('arka-k-murid').value = d.arka.k.murid || '';
                document.getElementById('arka-ap-guru').value = d.arka.ap.guru || '';
                document.getElementById('arka-ap-murid').value = d.arka.ap.murid || '';

                // Deep Learning UI
                document.getElementById('field-mindful').value = d.mindful || '';
                document.getElementById('field-meaningful').value = d.meaningful || '';
                document.getElementById('field-joyful').value = d.joyful || '';

                // Evaluasi UI
                document.getElementById('field-eval-proses').value = d.evalProses || '';
                document.getElementById('field-eval-hasil').value = d.evalHasil || '';
                document.getElementById('field-rtl').value = d.rtl || '';

                this.updateDashboardStats();
            }

            saveField(field, val) {
                this.canvasData[field] = val;
                this.updateDashboardStats();
            }

            saveArka(stage, target, val) {
                if (this.canvasData.arka[stage]) {
                    this.canvasData.arka[stage][target] = val;
                }
                this.updateDashboardStats();
            }

            setJalur(jalurName) {
                this.canvasData.jalur = jalurName;
                const kemendikCard = document.getElementById('jalur-card-kemendik');
                const kemenagCard = document.getElementById('jalur-card-kemenag');
                const radioKemendik = document.getElementById('radio-kemendik');
                const radioKemenag = document.getElementById('radio-kemenag');
                const kemenagPanel = document.getElementById('kemenag-special-panel');

                if (jalurName === 'Kemenag') {
                    kemendikCard.classList.remove('border-teal', 'bg-teal/5');
                    kemenagCard.classList.add('border-teal', 'bg-teal/5');
                    radioKemenag.checked = true;
                    if (kemenagPanel) kemenagPanel.classList.remove('hidden');
                } else {
                    kemenagCard.classList.remove('border-teal', 'bg-teal/5');
                    kemendikCard.classList.add('border-teal', 'bg-teal/5');
                    radioKemendik.checked = true;
                    if (kemenagPanel) kemenagPanel.classList.add('hidden');
                }
            }

            togglePancaCinta(item) {
                const idx = this.canvasData.pancaCinta.indexOf(item);
                if (idx > -1) {
                    this.canvasData.pancaCinta.splice(idx, 1);
                } else {
                    this.canvasData.pancaCinta.push(item);
                }
            }

            toggleJurus(num) {
                const idx = this.canvasData.jurusSelected.indexOf(num);
                if (idx > -1) {
                    this.canvasData.jurusSelected.splice(idx, 1);
                } else {
                    if (this.canvasData.jurusSelected.length >= 2) {
                        alert('Anda direkomendasikan memilih maksimal 2 jurus utama.');
                    }
                    this.canvasData.jurusSelected.push(num);
                }
                this.populateCanvasUI();
            }

            // AI Helper Simulations (Rule-based Intelligent Generators)
            recommendJurusAI() {
                const topic = this.canvasData.topik.toLowerCase();
                const kebutuhan = this.canvasData.kebutuhan.toLowerCase();

                let recommended = [1, 4];
                if (topic.includes('emosi') || kebutuhan.includes('kecemasan') || kebutuhan.includes('stres')) {
                    recommended = [2, 3]; // Kelola Emosi, Resiliensi
                } else if (topic.includes('sosial') || topic.includes('teman') || kebutuhan.includes('komunikasi')) {
                    recommended = [5, 6]; // Jalin Koneksi, Kolaborasi
                } else if (topic.includes('karir') || topic.includes('potensi') || topic.includes('snbp')) {
                    recommended = [1, 4]; // Kenali Potensi, Jaga Konsistensi
                }

                this.canvasData.jurusSelected = recommended;
                this.canvasData.jurusRasional = `Berdasarkan analisis kebutuhan murid pada topik '${this.canvasData.topik || 'Layanan BK'}', AI merekomendasikan Jurus ${recommended.join(' & ')} untuk menunjang pencapaian perkembangan murid secara optimal.`;
                this.populateCanvasUI();
                alert('Rekomendasi Jurus AI berhasil diterapkan!');
            }

            generateArkaAI() {
                const topic = this.canvasData.topik || 'Pengembangan Diri Murid';
                this.canvasData.arka = {
                    a: {
                        guru: `Guru membuka layanan dengan simulasi interaktif tentang ${topic} dan memutar klip video pemantik.`,
                        murid: `Murid mengamati klip, mencatat poin penting, dan berpartisipasi dalam diskusi pembuka.`
                    },
                    r: {
                        guru: `Guru memberikan refleksi mendalam melalui pertanyaan: 'Bagaimana isu ${topic} mempengaruhi kehidupan harianmu?'`,
                        murid: `Murid mengekspresikan perasaannya di Lembar Refleksi LKPD dan mengidentifikasi pemicu utamanya.`
                    },
                    k: {
                        guru: `Guru menyampaikan kerangka konsep BK mengenai ${topic} dan strategi penanganan yang efektif.`,
                        murid: `Murid menyelaraskan pengalaman pribadinya dengan kerangka teori yang disampaikan guru.`
                    },
                    ap: {
                        guru: `Guru memfasilitasi penyusunan Lembar Komitmen Tindakan Nyata 7 Hari.`,
                        murid: `Murid merumuskan 1 langkah konkret yang akan diterapkan mulai hari ini.`
                    }
                };
                this.populateCanvasUI();
                alert('Alur Aktivitas ARKA berhasil dirancang oleh AI!');
            }

            calibrateDeepLearningAI() {
                const topic = this.canvasData.topik || 'Pengembangan Diri';
                this.canvasData.mindful = `Dalam topik ${topic}, murid secara berkesadaran diajak mengenali dorongan internal, emosi, dan pola pikirnya tanpa menghakimi diri sendiri.`;
                this.canvasData.meaningful = `Materi ${topic} dihubungkan langsung dengan tantangan nyata yang dihadapi murid di sekolah dan lingkungan keluarga, memberikan nilai relevansi yang tinggi.`;
                this.canvasData.joyful = `Layanan dilaksanakan dengan pendekatan dialogis yang hangat, apresiatif, dan bebas intimidasi, menciptakan pengalaman belajar yang menggembirakan.`;
                this.populateCanvasUI();
                alert('Kalibrasi Pembelajaran Mendalam (Deep Learning) berhasil diperbarui!');
            }

            runAiAudit() {
                const d = this.canvasData;
                let score = 0;
                const criteria = [];

                // 1. Kebutuhan Murid
                if (d.kebutuhan && d.kondisi) { score += 15; criteria.push({ title: "Kebutuhan Murid", status: "BAIK", score: "15/15" }); }
                else { criteria.push({ title: "Kebutuhan Murid", status: "PERLU DILENGKAPI", score: "5/15" }); }

                // 2. Jalur Kurikulum
                if (d.jenjang && d.fase && d.sasaran) { score += 10; criteria.push({ title: "Penyelarasan Kurikulum", status: "BAIK", score: "10/10" }); }
                else { criteria.push({ title: "Penyelarasan Kurikulum", status: "PERLU DILENGKAPI", score: "3/10" }); }

                // 3. Relevansi 7 Jurus
                if (d.jurusSelected.length > 0) { score += 15; criteria.push({ title: "Relevansi 7 Jurus BK", status: "BAIK", score: "15/15" }); }
                else { criteria.push({ title: "Relevansi 7 Jurus BK", status: "BELUM DIPILIH", score: "0/15" }); }

                // 4. ARKA
                if (d.arka.a.guru && d.arka.r.guru && d.arka.k.guru && d.arka.ap.guru) { score += 20; criteria.push({ title: "Kesinambungan ARKA", status: "BAIK", score: "20/20" }); }
                else { criteria.push({ title: "Kesinambungan ARKA", status: "BELUM LENGKAP", score: "8/20" }); }

                // 5. Deep Learning
                if (d.mindful && d.meaningful && d.joyful) { score += 15; criteria.push({ title: "Pembelajaran Mendalam", status: "BAIK", score: "15/15" }); }
                else { criteria.push({ title: "Pembelajaran Mendalam", status: "PERLU REVISI", score: "5/15" }); }

                // 6. Evaluasi
                if (d.evalProses && d.evalHasil) { score += 15; criteria.push({ title: "Bukti Perkembangan", status: "BAIK", score: "15/15" }); }
                else { criteria.push({ title: "Bukti Perkembangan", status: "BELUM LENGKAP", score: "5/15" }); }

                // 7. RTL & Profil Guru
                if (d.rtl && this.teacherProfile.nama) { score += 10; criteria.push({ title: "Kelayakan Pelaksanaan", status: "BAIK", score: "10/10" }); }
                else { criteria.push({ title: "Kelayakan Pelaksanaan", status: "LENGKAPI PROFIL", score: "4/10" }); }

                let category = "SIAP MENJADI DRAF RPL";
                let badgeColor = "bg-emerald-100 text-emerald-800";
                if (score < 60) { category = "PERLU DESAIN ULANG"; badgeColor = "bg-rose-100 text-rose-800"; }
                else if (score < 80) { category = "REVISI RINGAN"; badgeColor = "bg-amber-100 text-amber-800"; }

                const html = `
                    <div class="bg-slate-900 text-white p-6 rounded-2xl flex flex-col md:flex-row justify-between items-center gap-4">
                        <div>
                            <span class="text-xs uppercase font-bold text-gold tracking-wider">SKOR AUDIT KELAYAKAN DESAIN</span>
                            <h4 class="text-3xl font-black">${score} / 100</h4>
                        </div>
                        <div>
                            <span class="px-3 py-1 rounded-full text-xs font-extrabold ${badgeColor}">${category}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-4">
                        ${criteria.map(c => `
                            <div class="p-3 bg-white border border-slate-200 rounded-xl flex justify-between items-center">
                                <span class="text-xs font-bold text-slate-700">${c.title}</span>
                                <span class="text-xs font-semibold text-teal">${c.score} (${c.status})</span>
                            </div>
                        `).join('')}
                    </div>
                `;

                document.getElementById('audit-results-panel').innerHTML = html;
            }

            // Profile Modal Functions
            openProfileModal() {
                document.getElementById('prof-nama').value = this.teacherProfile.nama || '';
                document.getElementById('prof-nip').value = this.teacherProfile.nip || '';
                document.getElementById('prof-pangkat').value = this.teacherProfile.pangkat || '';
                document.getElementById('prof-sekolah').value = this.teacherProfile.sekolah || '';
                document.getElementById('prof-kota').value = this.teacherProfile.kota || '';
                document.getElementById('prof-provinsi').value = this.teacherProfile.provinsi || '';
                document.getElementById('prof-tahun').value = this.teacherProfile.tahun || '2026/2027';
                document.getElementById('modal-profile').classList.remove('hidden');
            }

            closeProfileModal() {
                document.getElementById('modal-profile').classList.add('hidden');
            }

            saveProfile(e) {
                e.preventDefault();
                this.teacherProfile = {
                    nama: document.getElementById('prof-nama').value,
                    nip: document.getElementById('prof-nip').value,
                    pangkat: document.getElementById('prof-pangkat').value,
                    sekolah: document.getElementById('prof-sekolah').value,
                    kota: document.getElementById('prof-kota').value,
                    provinsi: document.getElementById('prof-provinsi').value,
                    tahun: document.getElementById('prof-tahun').value,
                };
                localStorage.setItem('bk_teacher_profile', JSON.stringify(this.teacherProfile));
                this.closeProfileModal();
                this.updateProfileBanner();
                alert('Profil Guru BK berhasil disimpan!');
            }

            updateProfileBanner() {
                const banner = document.getElementById('profile-status-banner');
                const navName = document.getElementById('nav-teacher-name');
                if (this.teacherProfile.nama && this.teacherProfile.sekolah) {
                    navName.textContent = this.teacherProfile.nama;
                    banner.className = "p-4 rounded-xl border bg-emerald-50 border-emerald-200 flex items-center justify-between";
                    banner.innerHTML = `
                        <div class="flex items-center space-x-3">
                            <div class="p-2 bg-emerald-600 text-white rounded-lg"><i data-lucide="user-check" class="w-5 h-5"></i></div>
                            <div>
                                <h4 class="text-xs font-bold text-emerald-900">PROFIL PENYUSUN TERVERIFIKASI</h4>
                                <p class="text-xs text-emerald-700">${this.teacherProfile.nama} (${this.teacherProfile.sekolah})</p>
                            </div>
                        </div>
                        <button onclick="app.openProfileModal()" class="text-xs font-semibold bg-emerald-600 text-white px-3 py-1.5 rounded-lg hover:bg-emerald-700">Edit Profil</button>
                    `;
                } else {
                    navName.textContent = "Profil Guru (Belum Lengkap)";
                    banner.className = "p-4 rounded-xl border bg-amber-50 border-amber-200 flex items-center justify-between";
                    banner.innerHTML = `
                        <div class="flex items-center space-x-3">
                            <div class="p-2 bg-amber-600 text-white rounded-lg"><i data-lucide="alert-triangle" class="w-5 h-5"></i></div>
                            <div>
                                <h4 class="text-xs font-bold text-amber-900">DATA PROFIL GURU PERLU DILENGKAPI</h4>
                                <p class="text-xs text-amber-700">Lengkapi identitas guru untuk halaman pengesahan dokumen RPL BK.</p>
                            </div>
                        </div>
                        <button onclick="app.openProfileModal()" class="text-xs font-semibold bg-amber-600 text-white px-3 py-1.5 rounded-lg hover:bg-amber-700">Lengkapi Profil</button>
                    `;
                }
                if (window.lucide) { window.lucide.createIcons(); }
            }

            updateDashboardStats() {
                const d = this.canvasData;
                let filled = 0;
                let total = 10;

                if (d.topik) filled++;
                if (d.bidang) filled++;
                if (d.kondisi) filled++;
                if (d.kebutuhan) filled++;
                if (d.jenjang) filled++;
                if (d.jurusSelected.length > 0) filled++;
                if (d.arka.a.guru) filled++;
                if (d.mindful) filled++;
                if (d.evalProses) filled++;
                if (this.teacherProfile.nama) filled++;

                const percent = Math.round((filled / total) * 100);
                document.getElementById('dash-progress-text').textContent = `${percent}%`;
                document.getElementById('dash-progress-bar').style.width = `${percent}%`;

                document.getElementById('dash-topic-display').textContent = d.topik ? `Topik Layanan: ${d.topik}` : 'Topik Layanan: [Belum ditentukan]';
                document.getElementById('dash-target-display').textContent = `Sasaran: ${d.sasaran || '-'} | Jalur: ${d.jalur}`;

                const modeBadge = document.getElementById('dash-mode-badge');
                if (this.mode === 'example') {
                    modeBadge.textContent = 'Mode Contoh / Simulasi ("AKU PUNYA KEKUATAN")';
                    modeBadge.className = 'px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-800';
                } else {
                    modeBadge.textContent = 'Mode Mandiri (Desain Sendiri)';
                    modeBadge.className = 'px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800';
                }
            }

            // Document Generation & Export Functions
            previewDocument(type) {
                const modal = document.getElementById('modal-preview');
                const title = document.getElementById('preview-modal-title');
                const body = document.getElementById('preview-modal-body');

                if (type === 'rpl') {
                    title.textContent = 'Pratinjau RPL BK Profesional';
                    body.innerHTML = this.renderRPLHTML();
                } else if (type === 'canvas') {
                    title.textContent = 'Pratinjau Service Design Canvas';
                    body.innerHTML = this.renderCanvasHTML();
                } else if (type === 'lkpd') {
                    title.textContent = 'Pratinjau Lembar Kerja Peserta Didik (LKPD)';
                    body.innerHTML = this.renderLKPDHTML();
                } else if (type === 'eval') {
                    title.textContent = 'Pratinjau Instrumen Evaluasi BK';
                    body.innerHTML = this.renderEvalHTML();
                }

                modal.classList.remove('hidden');
            }

            closePreviewModal() {
                document.getElementById('modal-preview').classList.add('hidden');
            }

            renderRPLHTML() {
                const d = this.canvasData;
                const p = this.teacherProfile;

                return `
                    <div id="print-area-rpl" class="p-8 bg-white text-slate-800 shadow-md border rounded-xl max-w-3xl mx-auto font-sans text-xs space-y-4">
                        <div class="text-center border-b-2 border-slate-900 pb-3">
                            <h2 class="text-base font-black text-slate-900 uppercase">RENCANA PELAKSANAAN LAYANAN (RPL) BK</h2>
                            <p class="font-bold text-slate-700">${p.sekolah || '[Nama Satuan Pendidikan]'}</p>
                            <p class="text-[10px] text-slate-500">Tahun Pelajaran: ${p.tahun || '2026/2027'}</p>
                        </div>

                        <!-- IDENTITAS -->
                        <table class="w-full border-collapse border border-slate-300">
                            <tr>
                                <td class="p-1.5 border border-slate-300 font-bold bg-slate-50 w-1/4">Nama Guru BK</td>
                                <td class="p-1.5 border border-slate-300">${p.nama || 'DATA PERLU DILENGKAPI'}</td>
                                <td class="p-1.5 border border-slate-300 font-bold bg-slate-50 w-1/4">Sasaran Kelas</td>
                                <td class="p-1.5 border border-slate-300">${d.sasaran || '-'}</td>
                            </tr>
                            <tr>
                                <td class="p-1.5 border border-slate-300 font-bold bg-slate-50">Bidang Layanan</td>
                                <td class="p-1.5 border border-slate-300">${d.bidang || '-'}</td>
                                <td class="p-1.5 border border-slate-300 font-bold bg-slate-50">Fase / Jenjang</td>
                                <td class="p-1.5 border border-slate-300">${d.fase || '-'} (${d.jenjang || '-'})</td>
                            </tr>
                            <tr>
                                <td class="p-1.5 border border-slate-300 font-bold bg-slate-50">Topik Layanan</td>
                                <td class="p-1.5 border border-slate-300" colspan="3"><strong>${d.topik || '-'}</strong></td>
                            </tr>
                        </table>

                        <!-- RASIONAL -->
                        <div>
                            <h3 class="font-bold text-slate-900 border-b pb-1 mb-1">I. RASIONAL & ANALISIS KEBUTUHAN</h3>
                            <p class="text-slate-700 leading-relaxed">${d.kondisi || 'Belum diisi.'}</p>
                        </div>

                        <!-- 7 JURUS & DEEP LEARNING -->
                        <div>
                            <h3 class="font-bold text-slate-900 border-b pb-1 mb-1">II. STRATEGI & KALIBRASI PEMBELAJARAN MENDALAM</h3>
                            <p><strong>7 Jurus Dipilih:</strong> Jurus ${d.jurusSelected.join(', ')}</p>
                            <p><strong>Mindful Learning:</strong> ${d.mindful || '-'}</p>
                            <p><strong>Meaningful Learning:</strong> ${d.meaningful || '-'}</p>
                            <p><strong>Joyful Learning:</strong> ${d.joyful || '-'}</p>
                        </div>

                        <!-- LANGKAH ARKA -->
                        <div>
                            <h3 class="font-bold text-slate-900 border-b pb-1 mb-1">III. LANGKAH-LANGKAH KEGIATAN (ARKA)</h3>
                            <table class="w-full border-collapse border border-slate-300 text-[10px]">
                                <thead>
                                    <tr class="bg-slate-100 font-bold">
                                        <th class="p-1.5 border border-slate-300">Tahap</th>
                                        <th class="p-1.5 border border-slate-300">Aktivitas Guru BK</th>
                                        <th class="p-1.5 border border-slate-300">Aktivitas Murid</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="p-1.5 border border-slate-300 font-bold">Aktivitas (A)</td>
                                        <td class="p-1.5 border border-slate-300">${d.arka.a.guru}</td>
                                        <td class="p-1.5 border border-slate-300">${d.arka.a.murid}</td>
                                    </tr>
                                    <tr>
                                        <td class="p-1.5 border border-slate-300 font-bold">Refleksi (R)</td>
                                        <td class="p-1.5 border border-slate-300">${d.arka.r.guru}</td>
                                        <td class="p-1.5 border border-slate-300">${d.arka.r.murid}</td>
                                    </tr>
                                    <tr>
                                        <td class="p-1.5 border border-slate-300 font-bold">Konseptualisasi (K)</td>
                                        <td class="p-1.5 border border-slate-300">${d.arka.k.guru}</td>
                                        <td class="p-1.5 border border-slate-300">${d.arka.k.murid}</td>
                                    </tr>
                                    <tr>
                                        <td class="p-1.5 border border-slate-300 font-bold">Aplikasi (A)</td>
                                        <td class="p-1.5 border border-slate-300">${d.arka.ap.guru}</td>
                                        <td class="p-1.5 border border-slate-300">${d.arka.ap.murid}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- EVALUASI & PENGESAHAN -->
                        <div class="pt-6 flex justify-between items-end">
                            <div class="text-center">
                                <p>Mengetahui,</p>
                                <p class="font-bold">Kepala Sekolah</p>
                                <div class="h-12"></div>
                                <p class="font-bold text-slate-800">( .................................................. )</p>
                            </div>
                            <div class="text-center">
                                <p>${p.kota || 'Kota'}, ${new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}</p>
                                <p class="font-bold">Guru Bimbingan dan Konseling</p>
                                <div class="h-12"></div>
                                <p class="font-bold text-slate-800">${p.nama || 'DATA PERLU DILENGKAPI'}</p>
                                <p class="text-[10px]">NIP. ${p.nip || '................................'}</p>
                            </div>
                        </div>

                        <div class="pt-4 border-t text-[9px] text-slate-400 flex justify-between">
                            <span>7 JURUS BK HEBAT - Universal Service Design Canvas</span>
                        </div>
                    </div>
                `;
            }

            renderCanvasHTML() {
                const d = this.canvasData;
                return `
                    <div id="print-area-canvas" class="p-6 bg-white border rounded-xl max-w-4xl mx-auto text-xs space-y-4">
                        <div class="bg-navy text-white p-4 rounded-xl flex justify-between items-center">
                            <div>
                                <h2 class="font-black text-base text-gold">UNIVERSAL SERVICE DESIGN CANVAS</h2>
                                <p class="text-xs">${d.topik || 'Topik Layanan'}</p>
                            </div>
                            <div class="text-right text-[10px] text-slate-300">
                                <p>Disusun oleh: ${this.teacherProfile.nama || '-'}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div class="p-3 bg-slate-50 border rounded-lg">
                                <h4 class="font-bold text-teal mb-1">1. Kebutuhan Murid</h4>
                                <p class="text-[10px]">${d.kebutuhan || '-'}</p>
                            </div>
                            <div class="p-3 bg-slate-50 border rounded-lg">
                                <h4 class="font-bold text-teal mb-1">2. Kurikulum</h4>
                                <p class="text-[10px]">Fase: ${d.fase || '-'}<br>Jalur: ${d.jalur}</p>
                            </div>
                            <div class="p-3 bg-slate-50 border rounded-lg">
                                <h4 class="font-bold text-teal mb-1">3. 7 Jurus Dipilih</h4>
                                <p class="text-[10px]">Jurus ${d.jurusSelected.join(', ')}</p>
                            </div>
                        </div>
                    </div>
                `;
            }

            renderLKPDHTML() {
                const d = this.canvasData;
                return `
                    <div id="print-area-lkpd" class="p-8 bg-white text-slate-800 border rounded-xl max-w-3xl mx-auto text-xs space-y-4">
                        <div class="text-center border-b pb-3">
                            <h2 class="text-base font-black text-navy uppercase">LEMBAR KERJA PESERTA DIDIK (LKPD)</h2>
                            <p class="font-bold text-slate-700">${d.topik || 'Topik Layanan'}</p>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-lg space-y-1">
                            <p>Nama Murid : .....................................................</p>
                            <p>Kelas / No : .....................................................</p>
                        </div>
                        <div>
                            <h4 class="font-bold text-navy mb-1">A. Refleksi Pengalaman (ARKA)</h4>
                            <div class="p-3 border rounded-lg h-24 bg-slate-50 text-slate-400">
                                Tuliskan refleksi perasaanamu saat mengikuti aktivitas awal...
                            </div>
                        </div>
                    </div>
                `;
            }

            renderEvalHTML() {
                const d = this.canvasData;
                return `
                    <div id="print-area-eval" class="p-8 bg-white border rounded-xl max-w-3xl mx-auto text-xs space-y-4">
                        <div class="text-center border-b pb-3">
                            <h2 class="text-base font-black text-navy uppercase">INSTRUMEN EVALUASI LAYANAN BK</h2>
                            <p class="font-bold text-slate-700">${d.topik || 'Topik Layanan'}</p>
                        </div>
                        <p><strong>Evaluasi Proses:</strong> ${d.evalProses || '-'}</p>
                        <p><strong>Evaluasi Hasil:</strong> ${d.evalHasil || '-'}</p>
                    </div>
                `;
            }

            exportPDF(type) {
                let elementId = 'print-area-rpl';
                if (type === 'canvas') elementId = 'print-area-canvas';
                if (type === 'lkpd') elementId = 'print-area-lkpd';
                if (type === 'eval') elementId = 'print-area-eval';

                this.previewDocument(type);

                setTimeout(() => {
                    const element = document.getElementById(elementId);
                    const opt = {
                        margin:       0.5,
                        filename:     `Dokumen_BK_${type}_${Date.now()}.pdf`,
                        image:        { type: 'jpeg', quality: 0.98 },
                        html2canvas:  { scale: 2 },
                        jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
                    };
                    html2pdf().set(opt).from(element).save();
                }, 500);
            }
        }

        // Initialize Global App Instance Safely in WordPress
        function initBk7JurusApp() {
            if (!document.getElementById("view-dashboard")) return;
            window.app = new AppEngine();
        }
        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", initBk7JurusApp);
        } else {
            initBk7JurusApp();
        }
