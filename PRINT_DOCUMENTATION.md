# 📄 Fitur Print - Dokumentasi

## Overview
Sistem pelaporan sekarang memiliki fitur print yang **professional dan rapih** untuk mencetak laporan. Fitur ini menggunakan `@media print` CSS untuk mengoptimalkan tampilan print.

## Halaman yang Mendukung Print

### 1. **Admin: Semua Laporan** (`admin/laporan.php`)
- Print daftar semua laporan dengan filter
- Menampilkan: ID, Tanggal, Pelapor, Judul, Lokasi, Status, Teknisi
- Otomatis menampilkan info periode filter yang aktif

### 2. **Admin: Reporting & Statistik** (`admin/reporting.php`)
- Print laporan statistik lengkap
- Include: Status breakdown, laporan bulanan, top teknisi, lokasi, weekly stats
- Format optimal untuk presentasi

## Fitur Print

### ✅ Yang Ditampilkan di Print:
- **Header profesional** dengan judul, tanggal cetak, info filter
- **Tabel laporan** dengan format rapi
- **Statistik dan angka** yang mudah dibaca
- **Footer** dengan info total laporan dan timestamp

### ❌ Yang Disembunyikan di Print:
- Navbar / header navigasi
- Button dan form filter
- Footer website
- Alert messages
- Semua elemen interaktif

## Format Print

### 📐 Spesifikasi:
- **Ukuran Kertas**: A4 (210mm x 297mm)
- **Margin**: 15mm semua sisi
- **Font Size**: 9-11pt (optimal untuk print)
- **Warna**: Hitam-putih optimized
- **Header Tabel**: Hitam dengan teks putih
- **Zebra Striping**: Baris genap abu-abu terang

### 📑 Page Breaks:
- Auto page break untuk tabel panjang
- Header tabel repeat di setiap halaman
- Card/section tidak dipotong di tengah

## Cara Menggunakan

### Dari Browser:
1. Buka halaman laporan atau reporting
2. Klik tombol **"Cetak"** atau **"Cetak Laporan"**
3. Browser akan membuka print preview
4. Review tampilan print
5. Klik **Print** atau **Save as PDF**

### Keyboard Shortcut:
- Windows: `Ctrl + P`
- Mac: `Cmd + P`

## Contoh Output Print

### Laporan.php akan menampilkan:
```
=============================================
LAPORAN KERUSAKAN FASILITAS SEKOLAH
Daftar Semua Laporan
Dicetak pada: 17 Desember 2025, 01:00
Periode: 01/12/2024 s/d 31/12/2024
=============================================

| ID | Tanggal | Pelapor | Judul | Lokasi | Status | Teknisi |
|-----|---------|---------|-------|--------|--------|---------|
| ... | ...     | ...     | ...   | ...    | ...    | ...     |

---
Sistem Pelaporan Kerusakan Fasilitas Sekolah
Halaman ini dicetak pada 17/12/2025 01:00
Total Laporan Ditampilkan: 25
```

### Reporting.php akan menampilkan:
```
=============================================
LAPORAN STATISTIK KERUSAKAN FASILITAS SEKOLAH
Reporting & Analisis Data
Dicetak pada: 17 Desember 2025, 01:00
=============================================

Status Laporan
| Status | Jumlah | Persentase |
|--------|--------|------------|
| ...    | ...    | ...        |

Total Laporan Per Bulan (6 Bulan Terakhir)
| Bulan | Total Laporan |
|-------|---------------|
| ...   | ...           |

[dst...]
```

## CSS Print Styles

### File: `assets/css/style.css`
- Line ~360+: `@media print { ... }`
- Total ~240 lines print-specific CSS
- Mengatur semua aspek print layout

### Key CSS Rules:
```css
@media print {
    /* Hide unnecessary elements */
    .header, .navbar, .footer, button, .filter-form {
        display: none !important;
    }
    
    /* Show print-only elements */
    .print-header, .print-footer {
        display: block !important;
    }
    
    /* Page setup */
    @page {
        size: A4;
        margin: 15mm;
    }
    
    /* Table optimization */
    table thead {
        display: table-header-group; /* Repeat on each page */
    }
}
```

## Tips Print

### Untuk Hasil Terbaik:
1. **Gunakan Chrome atau Edge** - print rendering terbaik
2. **Set margins** - gunakan default 15mm
3. **Background graphics** - enable untuk lihat zebra striping
4. **Scale** - gunakan 100% (default)
5. **Save as PDF** - untuk arsip digital

### Troubleshooting:
- **Tabel terpotong**: Tabel panjang otomatis break ke halaman baru
- **Warna tidak muncul**: Enable "Background graphics" di print settings
- **Layout berantakan**: Pastikan menggunakan browser modern (Chrome/Edge/Firefox)

## Technical Details

### Print-Only Elements:
```html
<!-- Hidden on screen, shown on print -->
<div class="print-header" style="display: none;">
    <h2>LAPORAN KERUSAKAN FASILITAS SEKOLAH</h2>
    <p>Dicetak pada: ...</p>
</div>

<div class="print-footer" style="display: none;">
    <p>System info | Timestamp</p>
</div>
```

### CSS Classes Available:
- `.print-only` - Hanya tampil saat print
- `.no-print` - Tidak tampil saat print
- `.page-break-before` - Force page break sebelum element
- `.page-break-after` - Force page break setelah element
- `.no-page-break` - Prevent page break di dalam element

## Future Enhancements (Optional)

1. **PDF Export Server-Side**
   - Library: TCPDF atau mPDF
   - Generate PDF langsung dari PHP
   - Custom header/footer dengan logo

2. **Custom Print Options**
   - Pilih kolom yang ingin ditampilkan
   - Pilih orientasi (portrait/landscape)
   - Pilih ukuran kertas (A4/Letter)

3. **Print Templates**
   - Template formal untuk laporan resmi
   - Template ringkas untuk internal
   - Template dengan logo dan kop surat

## Summary

✅ **Professional print layout** dengan header/footer info
✅ **Optimized untuk A4** dengan proper margins dan font sizes  
✅ **Auto hide** semua UI elements yang tidak perlu
✅ **Table headers repeat** di setiap halaman
✅ **Zebra striping** untuk readability
✅ **Print-only content** dengan info tambahan
✅ **Page break handling** untuk konten panjang

Print sekarang **bukan hanya screenshot halaman**, tapi **dokumen professional** yang siap untuk arsip atau presentasi! 🎉
