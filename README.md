# School Facility Damage Report System

Sistem Pelaporan Kerusakan Fasilitas Sekolah - Aplikasi Web berbasis PHP Native dengan 3 Role User

## Features

### Multi-User Roles
- **Siswa**: Melaporkan kerusakan dan melihat riwayat
- **Admin**: Validasi laporan, assign teknisi, dan reporting
- **Teknisi**: Mengerjakan tugas dan update status perbaikan

### Key Features
- Login system dengan password hashing
- Role-based access control
- Upload foto kerusakan (validasi & secure)
- Manual tracking via PHP (bukan SQL trigger)
- State machine untuk status laporan
- Filtering dan reporting dengan agregasi data
- Responsive design dengan skema warna #FFFDD0

## Installation

1. Import database:
   ```
   mysql -u root -p < database.sql
   ```
   Atau import melalui phpMyAdmin

2. Update konfigurasi database di `config/database.php` jika diperlukan

3. Pastikan folder `uploads/` memiliki permission write (chmod 755)

4. Akses aplikasi melalui browser:
   ```
   http://localhost/Learning1/
   ```

## Default Login Credentials

**Siswa:**
- Username: siswa1
- Password: password123

**Admin:**
- Username: admin1
- Password: password123

**Teknisi:**
- Username: teknisi1
- Password: password123

## Workflow

1. **Siswa** login dan membuat laporan kerusakan
2. **Admin** login, validasi laporan (approve/reject), dan assign ke teknisi
3. **Teknisi** login, melihat tugas, dan update status (process → done)
4. **Admin** dapat melihat reporting dengan berbagai filter

## Database Structure

### Tabel: users
- user_id (PK)
- username (unique)
- password (hashed)
- nama_lengkap
- role (siswa/admin/teknisi)
- created_at

### Tabel: laporan
- report_id (PK)
- user_id (FK)
- judul
- deskripsi
- lokasi
- foto (nullable)
- status (open/process/done/reject)
- assigned_to (FK to users, nullable)
- tanggal_lapor
- created_at, updated_at

### Tabel: tracking_progress
- tracking_id (PK)
- report_id (FK)
- technician_id (FK, nullable)
- status_awal
- status_akhir
- catatan
- timestamp

## Technical Notes

- **Password Hashing**: menggunakan `password_hash()` dan `password_verify()`
- **File Upload**: Validasi tipe (JPG, JPEG, PNG) dan ukuran (max 2MB)
- **Manual Trigger**: Fungsi `logStatusChange()` di `includes/tracking.php`
- **State Machine**: Validasi perubahan status melalui `validateStatusChange()`
- **Prepared Statements**: Semua query menggunakan PDO prepared statements
- **Session Management**: Secure session dengan httponly cookies

## File Structure

```
Learning1/
├── config/
│   ├── database.php
│   └── session.php
├── includes/
│   ├── functions.php
│   ├── tracking.php
│   ├── header.php
│   └── footer.php
├── assets/
│   ├── css/style.css
│   └── js/main.js
├── siswa/
│   ├── dashboard.php
│   ├── lapor.php
│   └── riwayat.php
├── admin/
│   ├── dashboard.php
│   ├── validasi.php
│   ├── laporan.php
│   └── reporting.php
├── teknisi/
│   ├── dashboard.php
│   ├── tugas.php
│   └── update_status.php
├── uploads/
├── database.sql
├── index.php
├── login.php
└── logout.php
```

## Requirements

- PHP 7.4+
- MySQL 5.7+
- Apache with mod_rewrite (optional)
- GD Library (for image processing)

## Security Features

- Password hashing dengan bcrypt
- Session security (httponly, secure)
- Prepared statements untuk prevent SQL injection
- File upload validation (type & size)
- Role-based access control
- XSS protection dengan htmlspecialchars

## License

Educational Project
