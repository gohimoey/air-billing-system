<?php
function rupiah($angka) {
    return 'Rp ' . number_format((int)$angka, 0, ',', '.');
}

function esc($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function bulan_indo($ym) {
    $nama = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
             'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $p = explode('-', $ym);
    if (count($p) !== 2) return $ym;
    return $nama[(int)$p[1]] . ' ' . $p[0];
}

function format_tgl($tanggal) {
    return date('d/m/Y', strtotime($tanggal));
}

function generate_period() {
    return date('Y-m');
}

function generate_bill_number($customer_id, $period) {
    return 'BILL-' . $customer_id . '-' . str_replace('-', '', $period);
}