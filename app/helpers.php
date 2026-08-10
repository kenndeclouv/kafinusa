<?php

if (! function_exists('formatWeight')) {
    /**
     * Format berat yang disimpan dalam satuan Gram di database
     * menjadi satuan yang lebih mudah dibaca (Ton, Kg, atau Gram).
     *
     * @param int|float $grams
     * @return string
     */
    function formatWeight($grams)
    {
        $kg = $grams / 1000;

        if ($kg >= 1000) {
            // Konversi ke Ton
            return number_format($kg / 1000, 2, ',', '.') . ' Ton';
        } elseif ($kg >= 1) {
            // Konversi ke Kg
            return number_format($kg, 2, ',', '.') . ' Kg';
        }

        // Tampilkan dalam gram jika sangat kecil
        return number_format($grams, 0, ',', '.') . ' gr';
    }
}
