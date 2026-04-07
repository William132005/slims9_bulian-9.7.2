<?php
/**
 * Library Information Page - Custom Override for SIPANDU
 * - Menampilkan Contact Information dan Opening Hours dari database
 * - Menghapus bagian Collections dan Library Membership
 * - Menambahkan peta dan info sekolah yang dipindahkan dari Beranda
 */

// be sure that this file not accessed directly
if (!defined('INDEX_AUTH')) {
    die("can not access this file directly");
} elseif (INDEX_AUTH != 1) {
    die("can not access this file directly");
}

// Set page title
$page_title = __('Library Information');

// Ambil data dari CMS SLiMS (tabel content)
include_once LIB . 'content.inc.php';
$content    = new Content();
$content_data = $content->get($dbs, 'libinfo');

// Ambil konfigurasi template untuk peta & sosmed
$mapLink = $sysconf['template']['classic_map_link'] ?? '';
$mapDesc = $sysconf['template']['classic_map_desc'] ?? '';
$fbLink  = $sysconf['template']['classic_fb_link'] ?? '';
$twLink  = $sysconf['template']['classic_twitter_link'] ?? '';
$ytLink  = $sysconf['template']['classic_youtube_link'] ?? '';
$igLink  = $sysconf['template']['classic_instagram_link'] ?? '';
$libName = $sysconf['library_name'] ?? '';

// ---- Render konten dari DB, tapi hapus section Collections & Library Membership ----
if ($content_data) {
    $raw = $content_data['Content'];

    // Hapus blok h3 Collections beserta paragraf setelahnya
    // Pattern: dari <h3>Collections</h3> sampai sebelum <h3> berikutnya atau akhir string
    $raw = preg_replace(
        '/<h3[^>]*>\s*Collections\s*<\/h3>[\s\S]*?(?=<h3[^>]*>|$)/i',
        '',
        $raw
    );

    // Hapus blok h3 Library Membership beserta paragraf setelahnya
    $raw = preg_replace(
        '/<h3[^>]*>\s*Library Membership\s*<\/h3>[\s\S]*?(?=<h3[^>]*>|$)/i',
        '',
        $raw
    );

    echo $raw;
}
?>

<!-- ===== PETA LOKASI & INFO SEKOLAH (dipindahkan dari Beranda) ===== -->
<?php if (!empty($mapLink)): ?>
<div style="margin-top: 2.5rem;">
    <div style="
        border-radius: 1rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        border: 1px solid #E2E8F0;
        overflow: hidden;
        background: #fff;
    ">
        <div style="display: flex; flex-wrap: wrap;">
            <!-- Peta Google Maps -->
            <div style="flex: 0 0 50%; max-width: 50%; min-width: 280px;">
                <iframe
                    src="<?= htmlspecialchars($mapLink); ?>"
                    width="100%" height="380" frameborder="0"
                    style="border:0; display:block;" allowfullscreen loading="lazy">
                </iframe>
            </div>
            <!-- Info Perpustakaan / Sekolah -->
            <div style="flex: 0 0 50%; max-width: 50%; min-width: 280px; padding: 2rem 2.5rem; display: flex; flex-direction: column; justify-content: center;">
                <h4 style="font-size:1.1rem; font-weight:700; color:#1E293B; margin-bottom:0.75rem;">
                    <?= htmlspecialchars($libName); ?>
                </h4>
                <div style="color:#475569; font-size:0.875rem; line-height:1.8;">
                    <?= $mapDesc; ?>
                </div>
                <!-- Sosial Media -->
                <div style="display:flex; flex-wrap:wrap; gap:0.5rem; margin-top:1.25rem;">
                    <?php if (!empty($fbLink)): ?>
                    <a target="_blank" href="<?= htmlspecialchars($fbLink) ?>" title="Facebook"
                       style="width:40px;height:40px;background:#3b5998;border-radius:0.5rem;display:flex;align-items:center;justify-content:center;color:#fff;text-decoration:none;">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <?php endif; ?>
                    <?php if (!empty($twLink)): ?>
                    <a target="_blank" href="<?= htmlspecialchars($twLink) ?>" title="Twitter/X"
                       style="width:40px;height:40px;background:#1da1f2;border-radius:0.5rem;display:flex;align-items:center;justify-content:center;color:#fff;text-decoration:none;">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <?php endif; ?>
                    <?php if (!empty($ytLink)): ?>
                    <a target="_blank" href="<?= htmlspecialchars($ytLink) ?>" title="YouTube"
                       style="width:40px;height:40px;background:#ff0000;border-radius:0.5rem;display:flex;align-items:center;justify-content:center;color:#fff;text-decoration:none;">
                        <i class="fab fa-youtube"></i>
                    </a>
                    <?php endif; ?>
                    <?php if (!empty($igLink)): ?>
                    <a target="_blank" href="<?= htmlspecialchars($igLink) ?>" title="Instagram"
                       style="width:40px;height:40px;background:linear-gradient(45deg,#f09433,#e6683c,#dc2743,#cc2366,#bc1888);border-radius:0.5rem;display:flex;align-items:center;justify-content:center;color:#fff;text-decoration:none;">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
@media (max-width: 768px) {
    .libinfo-map-col {
        flex: 0 0 100% !important;
        max-width: 100% !important;
    }
}
</style>
