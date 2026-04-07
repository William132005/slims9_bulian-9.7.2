<?php
/**
 * Custom Services Page for SLiMS
 */
$this->page_title = __('Services') . ' | ' . $sysconf['library_name'];
?>

<div class="row">
    <!-- Layanan Sirkulasi -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100 border-slate" style="border-radius: 12px; border: 1px solid #E2E8F0; transition: transform 0.3s ease, box-shadow 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 1px 2px 0 rgba(0, 0, 0, 0.05)';">
            <div class="card-body text-center p-4">
                <div class="mb-3 d-flex justify-content-center align-items-center mx-auto" style="width: 64px; height: 64px; background-color: #EEF2FF; border-radius: 50%; color: var(--primary-navy, #000080);">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                    </svg>
                </div>
                <h4 class="card-title font-weight-bold text-dark" style="font-size: 1.25rem;">Layanan Sirkulasi</h4>
                <p class="card-text text-muted mt-2" style="font-size: 0.95rem;">Melayani peminjaman, pengembalian, dan perpanjangan buku bagi seluruh siswa, guru, dan staf SMPK Santa Maria 2 Malang.</p>
            </div>
        </div>
    </div>

    <!-- Layanan Referensi -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100 border-slate" style="border-radius: 12px; border: 1px solid #E2E8F0; transition: transform 0.3s ease, box-shadow 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 1px 2px 0 rgba(0, 0, 0, 0.05)';">
            <div class="card-body text-center p-4">
                <div class="mb-3 d-flex justify-content-center align-items-center mx-auto" style="width: 64px; height: 64px; background-color: #EEF2FF; border-radius: 50%; color: var(--primary-navy, #000080);">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="16" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                    </svg>
                </div>
                <h4 class="card-title font-weight-bold text-dark" style="font-size: 1.25rem;">Layanan Referensi</h4>
                <p class="card-text text-muted mt-2" style="font-size: 0.95rem;">Menyediakan koleksi rujukan (ensiklopedia, kamus, atlas) dan panduan pencarian informasi lanjutan untuk kebutuhan akademik sekolah.</p>
            </div>
        </div>
    </div>

    <!-- Ruang Baca -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100 border-slate" style="border-radius: 12px; border: 1px solid #E2E8F0; transition: transform 0.3s ease, box-shadow 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 1px 2px 0 rgba(0, 0, 0, 0.05)';">
            <div class="card-body text-center p-4">
                <div class="mb-3 d-flex justify-content-center align-items-center mx-auto" style="width: 64px; height: 64px; background-color: #EEF2FF; border-radius: 50%; color: var(--primary-navy, #000080);">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                    </svg>
                </div>
                <h4 class="card-title font-weight-bold text-dark" style="font-size: 1.25rem;">Fasilitas Ruang Baca</h4>
                <p class="card-text text-muted mt-2" style="font-size: 0.95rem;">Ruang baca yang nyaman, rapi, dan kondusif untuk kegiatan literasi siswa, mengerjakan tugas, atau berdiskusi kelompok ringan.</p>
            </div>
        </div>
    </div>

    <!-- PC & Internet -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100 border-slate" style="border-radius: 12px; border: 1px solid #E2E8F0; transition: transform 0.3s ease, box-shadow 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 1px 2px 0 rgba(0, 0, 0, 0.05)';">
            <div class="card-body text-center p-4">
                <div class="mb-3 d-flex justify-content-center align-items-center mx-auto" style="width: 64px; height: 64px; background-color: #EEF2FF; border-radius: 50%; color: var(--primary-navy, #000080);">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                        <line x1="8" y1="21" x2="16" y2="21"></line>
                        <line x1="12" y1="17" x2="12" y2="21"></line>
                    </svg>
                </div>
                <h4 class="card-title font-weight-bold text-dark" style="font-size: 1.25rem;">Akses Komputer & Internet</h4>
                <p class="card-text text-muted mt-2" style="font-size: 0.95rem;">Tersedia perangkat komputer dengan jaringan internet untuk mendukung pencarian bahan ajar dan informasi digital (OPAC).</p>
            </div>
        </div>
    </div>
</div>
