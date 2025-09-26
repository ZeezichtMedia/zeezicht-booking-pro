<?php
/**
 * Template Part: Description - SIMPLE VERSION LIKE OLD
 */

global $zzbp_current_accommodation;
$accommodation = $zzbp_current_accommodation;

if (!$accommodation) {
    return;
}
?>

<div class="bg-white rounded-2xl p-8 shadow-lg border border-gray-100 mb-8">
    <h2 class="text-2xl font-semibold text-gray-900 mb-4">Beschrijving</h2>
    <p class="text-gray-700 leading-relaxed">
        <?php echo esc_html($accommodation['description'] ?? 'Welkom in onze prachtige accommodatie! Perfect voor een ontspannen verblijf.'); ?>
    </p>
</div>
