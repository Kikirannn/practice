# 🚀 Quick Start - Backend-Frontend Restructuring

## File Utama

**`RESTRUCTURE_ALL_IN_ONE.bat`** - Script lengkap untuk reorganisasi

## Cara Pakai (3 Langkah)

### 1️⃣ Persiapan
- Close semua file di editor
- Stop PHP server (jika running)

### 2️⃣ Jalankan
- Double-click **`RESTRUCTURE_ALL_IN_ONE.bat`**
- Ikuti instruksi di layar
- Tunggu ~2-3 menit

### 3️⃣ Test
- Buka: `http://localhost/Learning1/`
- Login: `siswa1` / `password123`
- Test semua fitur

## Apa yang Dilakukan?

1. ✅ **Backup otomatis** semua file
2. ✅ **Buat folder** `views/`, `public/`, `backend/`
3. ✅ **Pindahkan file** ke lokasi baru
4. ✅ **Update paths** otomatis (~50+ paths)
5. ✅ **Verifikasi** struktur baru

## Hasil

### Before:
```
siswa/           admin/           teknisi/
assets/css/      assets/js/       uploads/
```

### After:
```
views/siswa/     views/admin/     views/teknisi/
public/css/      public/js/       public/uploads/
backend/         (ready to use)
```

## Rollback (Jika Error)

Backup auto-created di:
```
..\Learning1_Backup_YYYYMMDD_HHMMSS\
```

Untuk rollback:
1. Delete folder `Learning1/`
2. Rename backup → `Learning1/`

## URLs Baru

| Old | New |
|-----|-----|
| `/siswa/dashboard.php` | `/views/siswa/dashboard.php` |
| `/admin/validasi.php` | `/views/admin/validasi.php` |
| `/login.php` | `/views/auth/login.php` |

## Safety

✅ Auto backup sebelum mulai
✅ Old folders **tidak dihapus** (untuk safety)
✅ Bisa rollback kapan saja
✅ Zero data loss

---

**Ready?** → Double-click `RESTRUCTURE_ALL_IN_ONE.bat` 🚀
