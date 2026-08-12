<?php

return [

    /**
     * Kriteria SAW. Semua bertipe "benefit" (nilai makin tinggi = makin prioritas).
     * Total weight harus berjumlah 1.0.
     */
    'criteria' => [
        'malware_count' => [
            'label' => 'Jumlah Malware',
            'weight' => 0.25,
            'type' => 'benefit',
        ],
        'judol_link_count' => [
            'label' => 'Jumlah Link Judi Online',
            'weight' => 0.25,
            'type' => 'benefit',
        ],
        'infected_pages' => [
            'label' => 'Halaman Terinfeksi',
            'weight' => 0.20,
            'type' => 'benefit',
        ],
        'keyword_count' => [
            'label' => 'Jumlah Keyword Judol',
            'weight' => 0.10,
            'type' => 'benefit',
        ],
        'redirect_count' => [
            'label' => 'Jumlah Redirect Mencurigakan',
            'weight' => 0.10,
            'type' => 'benefit',
        ],
        'external_link_count' => [
            'label' => 'Jumlah Link Eksternal Mencurigakan',
            'weight' => 0.10,
            'type' => 'benefit',
        ],
    ],

    /**
     * Ambang batas skor (0..1) untuk label level prioritas di tabel hasil.
     */
    'priority_thresholds' => [
        'tinggi' => 0.6,
        'sedang' => 0.3,
    ],

];
