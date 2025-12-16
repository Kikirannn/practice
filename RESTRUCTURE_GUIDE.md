# 📁 Backend-Frontend Restructuring Guide

## Overview

Script otomatis untuk memisahkan project menjadi struktur **backend** dan **frontend** yang terorganisir.

## 🎯 Hasil Akhir

### Sebelum:
```
Learning1/
├── siswa/           (mixed: logic + view)
├── admin/           (mixed: logic + view)
├── teknisi/         (mixed: logic + view)
├── assets/css/
├── assets/js/
└── uploads/
```

### Sesudah:
```
Learning1/
├── views/           ✨ Frontend - Presentation Layer
│   ├── auth/
│   ├── siswa/
│   ├── admin/
│   ├── teknisi/
│   └── partials/
├── public/          ✨ Public Assets
│   ├── css/
│   ├── js/
│   └── uploads/
├── backend/         ✨ Backend - Business Logic (ready for expansion)
├── config/          (tetap)
└── includes/        (tetap)
```

## 📋 Files yang Dibuat

1. **`RESTRUCTURE.bat`** - Master script (jalankan ini)
2. **`1_backup.bat`** - Backup otomatis
3. **`2_reorganize.bat`** - Pindahkan files
4. **`3_update_paths.bat`** - Trigger update paths
5. **`update_paths.ps1`** - PowerShell untuk update paths

## 🚀 Cara Menggunakan

### Step 1: Persiapan
```bash
1. Close semua file yang terbuka
2. Stop PHP development server (jika running)
3. Pastikan tidak ada file yang sedang digunakan
```

### Step 2: Jalankan Script
```bash
1. Buka Windows Explorer
2. Navigate ke: C:\laragon\www\Learning1\
3. Double-click: RESTRUCTURE.bat
4. Ikuti instruksi di layar
```

### Step 3: Verifikasi
```bash
1. Buka browser: http://localhost/Learning1/
2. Test login: siswa1 / password123
3. Test semua fitur
```

## 📝 Apa yang Dilakukan Script?

### 1️⃣ Backup (Otomatis)
- Membuat folder backup lengkap
- Format: `Learning1_Backup_YYYYMMDD_HHMMSS`
- Location: `C:\laragon\www\Learning1_Backup_*`

### 2️⃣ Reorganize (Otomatis)
**Membuat folder baru:**
- `views/auth/`, `views/siswa/`, `views/admin/`, `views/teknisi/`, `views/partials/`
- `public/css/`, `public/js/`, `public/uploads/`
- `backend/` (kosong, siap untuk controllers/models)

**Memindahkan files:**
- `login.php` → `views/auth/login.php`
- `siswa/*.php` → `views/siswa/`
- `admin/*.php` → `views/admin/`
- `teknisi/*.php` → `views/teknisi/`
- `includes/header.php` → `views/partials/header.php`
- `includes/footer.php` → `views/partials/footer.php`
- `assets/css/` → `public/css/`
- `assets/js/` → `public/js/`
- `uploads/` → `public/uploads/`

### 3️⃣ Update Paths (Otomatis)
**Update semua `require_once`:**
```php
// BEFORE
require_once '../config/database.php';

// AFTER  
require_once '../../config/database.php';
```

**Update asset URLs:**
```html
<!-- BEFORE -->
<link href="/Learning1/assets/css/style.css">

<!-- AFTER -->
<link href="/Learning1/public/css/style.css">
```

**Update redirect paths:**
```php
// BEFORE
redirect('/Learning1/siswa/dashboard.php');

// AFTER
redirect('/Learning1/views/siswa/dashboard.php');
```

**Update upload directory:**
```php
// BEFORE
uploadDir = 'uploads/'

// AFTER
uploadDir = 'public/uploads/'
```

## ⚠️ Important Notes

### Folder Lama TIDAK Auto-Deleted
Untuk keamanan, folder lama **tidak dihapus otomatis**:
- `siswa/` (sekarang kosong)
- `admin/` (sekarang kosong)
- `teknisi/` (sekarang kosong)
- `assets/` (sekarang kosong)

**Anda bisa hapus manual setelah verifikasi sukses.**

### Backup Location
```
C:\laragon\www\Learning1_Backup_YYYYMMDD_HHMMSS\
```
Simpan backup ini sampai yakin restructure berhasil!

## 🧪 Testing Checklist

Setelah restructure, test semua fitur:

- [ ] Login siswa berhasil
- [ ] Login admin berhasil
- [ ] Login teknisi berhasil
- [ ] Submit laporan (siswa)
- [ ] Upload foto berfungsi
- [ ] Validasi laporan (admin)
- [ ] Update status (teknisi)
- [ ] Filtering laporan
- [ ] Print laporan
- [ ] Print reporting
- [ ] CSS loading dengan benar
- [ ] JavaScript berfungsi

## 🔄 Rollback (Jika Ada Masalah)

Jika terjadi error setelah restructure:

```bash
1. Delete folder: Learning1/
2. Rename: Learning1_Backup_* → Learning1/
3. Aplikasi kembali seperti semula
```

## 🎨 Path Changes Summary

| Item | Old Path | New Path |
|------|----------|----------|
| Views | `/siswa/`, `/admin/`, `/teknisi/` | `/views/siswa/`, `/views/admin/`, `/views/teknisi/` |
| Login | `/login.php` | `/views/auth/login.php` |
| CSS | `/assets/css/` | `/public/css/` |
| JS | `/assets/js/` | `/public/js/` |
| Uploads | `/uploads/` | `/public/uploads/` |
| Header | `/includes/header.php` | `/views/partials/header.php` |
| Footer | `/includes/footer.php` | `/views/partials/footer.php` |

## 📂 New URL Structure

### Before:
```
http://localhost/Learning1/siswa/dashboard.php
http://localhost/Learning1/admin/validasi.php
http://localhost/Learning1/login.php
```

### After:
```
http://localhost/Learning1/views/siswa/dashboard.php
http://localhost/Learning1/views/admin/validasi.php
http://localhost/Learning1/views/auth/login.php
```

**Note:** Anda bisa tambahkan `.htaccess` untuk URL rewriting jika diinginkan.

## 🔮 Future Expansion

Dengan struktur baru, Anda siap untuk:

### Backend Enhancement
```
backend/
├── controllers/
│   ├── AuthController.php
│   ├── LaporanController.php
│   └── UserController.php
├── models/
│   ├── User.php
│   ├── Laporan.php
│   └── TrackingProgress.php
└── api/
    └── v1/
        └── (REST API endpoints)
```

### Frontend Enhancement
- Tambah template engine (Twig, Blade)
- Component-based architecture
- AJAX/Fetch API integration

## ❓ FAQ

**Q: Apakah database perlu diupdate?**
A: Tidak, database tetap sama.

**Q: Apakah password hash berubah?**
A: Tidak, semua data tetap sama.

**Q: File apa yang dimodifikasi?**
A: Hanya path di dalam file PHP. Logic tidak berubah.

**Q: Berapa lama prosesnya?**
A: ~1-2 menit untuk backup + reorganize + update paths.

**Q: Apakah bisa di-undo?**
A: Ya, restore dari backup folder.

## 🎯 Benefits

✅ **Clear Separation**: Views terpisah dari logic
✅ **Organized**: Files grouped by function
✅ **Scalable**: Ready untuk MVC pattern
✅ **Professional**: Industry-standard structure
✅ **Maintainable**: Easier to find and update files
✅ **Secure**: Public assets in dedicated folder

## 📞 Support

Jika ada error:
1. Check backup masih ada
2. Rollback jika perlu
3. Review error message
4. Test step by step

## ✨ Summary

Script ini akan **otomatis**:
1. ✅ Backup semua files
2. ✅ Buat struktur folder baru
3. ✅ Pindahkan semua files
4. ✅ Update semua paths
5. ✅ Preserve old folders (untuk safety)

**Total waktu: ~2 menit**
**Risk: Minimal** (ada backup otomatis)
**Effort: Zero** (fully automated)

---

🚀 **Ready to restructure?** 
→ Double-click `RESTRUCTURE.bat` dan ikuti instruksi!
