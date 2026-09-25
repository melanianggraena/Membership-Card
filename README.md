# 💳 Technolife Membership & Smart Access Card System

Sistem manajemen keanggotaan terpadu yang mengintegrasikan **Portal Web Administrator & Kasir** (berbasis Laravel) dengan **Aplikasi Mobile Member** (berbasis Flutter) serta dukungan kartu pintar fisik berbasis **RFID/NFC**.

---

## 📌 Daftar Isi
1. [Tentang Proyek](#-tentang-proyek)
2. [Struktur Folder](#-struktur-folder)
3. [Prasyarat Sistem (Prerequisites)](#-prasyarat-sistem-prerequisites)
4. [Langkah 1: Clone Repository](#-langkah-1-clone-repository)
5. [Langkah 2: Menyiapkan dan Menjalankan Backend (Laravel)](#-langkah-2-menyiapkan-dan-menjalankan-backend-laravel)
6. [Langkah 3: Menyiapkan dan Menjalankan Mobile App (Flutter)](#-langkah-3-menyiapkan-dan-menjalankan-mobile-app-flutter)
7. [Panduan Uji Coba / Alur Kerja Implementasi](#-panduan-uji-coba--alur-kerja-implementasi)
8. [Akun Bawaan (Default Credentials)](#-akun-bawaan-default-credentials)
9. [Solusi Kendala Umum (Troubleshooting)](#-solusi-kendala-umum-troubleshooting)

---

## 📖 Tentang Proyek

Sistem ini dibangun untuk memenuhi kebutuhan operasional fasilitas gedung/area Technolife:
- **Web Portal (Admin & Kasir)**: Mengelola keanggotaan, kartu NFC, ruangan fasilitas, outlet mitra, promo diskon, pengisian saldo (*top-up*), transaksi belanja kasir, serta simulasi dan monitoring akses pintu NFC.
- **Mobile App (Member)**: Digunakan oleh anggota untuk melihat kartu keanggotaan digital, memantau saldo deposit *real-time*, melihat notifikasi, mengecek riwayat mutasi transaksi & log akses ruangan, serta melihat info promo.

---

## 📂 Struktur Folder

```text
Membership-Card/
├── backend/
│   └── membership-api/     # Aplikasi backend (Laravel 11, REST API & Web Panel)
├── mobile/                 # Aplikasi mobile untuk Member (Flutter)
└── README.md               # Dokumentasi panduan instalasi & implementasi
```

---

## 💻 Prasyarat Sistem (Prerequisites)

Sebelum memulai, pastikan perangkat komputer Anda telah terpasang software berikut:

| Software | Versi Minimal | Fungsi |
| :--- | :--- | :--- |
| **Git** | Versi terbaru | Mengunduh source code dari GitHub |
| **PHP** | 8.2 atau lebih baru | Menjalankan backend Laravel |
| **Composer** | 2.x | Manajemen paket dependency PHP |
| **MySQL / MariaDB** | 8.0+ (atau via XAMPP) | Basis data relasional sistem |
| **Node.js & NPM** | Node v18+ | Membangun aset tampilan web (Vite & Tailwind CSS) |
| **Flutter SDK** | 3.24+ | Menjalankan aplikasi mobile |
| **Android Studio / VS Code** | Versi terbaru | Editor & emulator Android/iOS |

---

## 🚀 Langkah 1: Clone Repository

Buka aplikasi **Terminal** (macOS/Linux) atau **Git Bash / Command Prompt** (Windows), lalu jalankan perintah berikut:

```bash
git clone https://github.com/melanianggraena/Membership-Card.git
```

Setelah proses selesai, masuk ke dalam folder proyek:
```bash
cd Membership-Card
```

---

## ⚙️ Langkah 2: Menyiapkan dan Menjalankan Backend (Laravel)

### 1. Masuk ke direktori backend
```bash
cd backend/membership-api
```

### 2. Pasang dependensi PHP dan Node.js
```bash
composer install
npm install
```

### 3. Siapkan file konfigurasi `.env`
Salin file `.env.example` menjadi `.env`:
- **Windows (Command Prompt):**
  ```cmd
  copy .env.example .env
  ```
- **macOS / Linux:**
  ```bash
  cp .env.example .env
  ```

### 4. Buat Database MySQL
1. Buka aplikasi database Anda (misalnya **XAMPP / phpMyAdmin** atau MySQL CLI).
2. Buat database baru bernama: `membership_technolife`.
3. Buka file `.env` di folder `backend/membership-api/`, sesuaikan konfigurasi database berikut:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=membership_technolife
   DB_USERNAME=root
   DB_PASSWORD=
   ```
   *(Kosongkan `DB_PASSWORD` jika menggunakan XAMPP default tanpa password, atau isi sesuai kredensial MySQL komputer Anda).*

### 5. Generate Application Key
```bash
php artisan key:generate
```

### 6. Jalankan Migrasi & Seeder Database
Perintah ini akan membuat seluruh tabel yang dibutuhkan sekaligus akun administrator awal:
```bash
php artisan migrate --seed
```

### 7. Build aset frontend Web Admin
```bash
npm run build
```

### 8. Jalankan Server Lokal Laravel
Jalankan server agar dapat diakses oleh komputer dan perangkat mobile yang berada dalam satu jaringan:
```bash
php artisan serve --host=0.0.0.0 --port=8000
```
> Web Admin sekarang aktif dan dapat dibuka melalui browser di alamat:
> **http://localhost:8000** atau **http://<IP-KOMPUTER-ANDA>:8000**

---

## 📱 Langkah 3: Menyiapkan dan Menjalankan Mobile App (Flutter)

### 1. Buka terminal baru dan masuk ke folder `mobile`
```bash
cd mobile
```

### 2. Pasang dependensi Flutter
```bash
flutter pub get
```

### 3. Konfigurasi Alamat IP Server Backend
Agar aplikasi mobile dapat berkomunikasi dengan backend Laravel, pastikan URL API sudah sesuai dengan alamat IP komputer Anda:
1. Cari tahu IP lokal komputer Anda:
   - **Windows:** ketik `ipconfig` di CMD (cari *IPv4 Address*, contoh: `192.168.1.15`).
   - **macOS:** buka *System Settings > Wi-Fi > Details* atau ketik `ifconfig` di Terminal.
2. Buka file `mobile/lib/core/api_client.dart` di text editor.
3. Ubah baris `defaultBaseUrl`:
   ```dart
   // Jika menggunakan HP Fisik (terhubung di Wi-Fi yang sama dengan laptop):
   static String get defaultBaseUrl => 'http://192.168.1.XX:8000/api';

   // ATAU jika menggunakan Android Emulator bawaan Android Studio:
   // static String get defaultBaseUrl => 'http://10.0.2.2:8000/api';

   // ATAU jika menggunakan iOS Simulator:
   // static String get defaultBaseUrl => 'http://127.0.0.1:8000/api';
   ```

### 4. Jalankan Aplikasi Mobile
Pastikan emulator sudah aktif atau HP fisik Android/iOS sudah tersambung melalui kabel data dengan mode *USB Debugging* aktif:
```bash
flutter run
```

---

## 🧪 Panduan Uji Coba / Alur Kerja Implementasi

Untuk mendemonstrasikan sistem ini kepada pihak perusahaan, ikuti skenario pengujian praktis berikut:

### Skenario 1: Login Web Admin & Tambah Member
1. Buka browser dan kunjungi: `http://localhost:8000/login`.
2. Masuk menggunakan akun admin bawaan (lihat bagian [Akun Bawaan](#-akun-bawaan-default-credentials)).
3. Buka menu **Member** -> klik **Tambah Member**.
4. Masukkan data:
   - **Nama Lengkap:** Contoh `Budi Santoso`
   - **No. Handphone:** Contoh `081234567890` (catat nomor ini untuk login mobile)
   - **Email:** `budi@gmail.com`
   - **NFC UID:** Contoh `NFC-TEST-001` (atau scan kartu NFC Anda)
   - **Status:** Aktif
5. Simpan data member.

### Skenario 2: Isi Saldo (Top-Up) Member
1. Pada Web Admin, buka menu **Top Up Saldo**.
2. Pilih member `Budi Santoso`.
3. Masukkan nominal, misalnya: `Rp 100.000`.
4. Pilih metode pembayaran (`Cash` / `Transfer` / `QRIS`) lalu klik **Proses Top Up**.
5. Saldo deposit member kini bertambah.

### Skenario 3: Login Member di Aplikasi Mobile
1. Buka aplikasi **Technolife Membership** di HP / Emulator.
2. Masukkan nomor handphone yang tadi didaftarkan (`081234567890`) lalu klik **Masuk**.
3. Di lingkungan *development*, kode OTP otomatis muncul pada banner notifikasi atau respon sistem (bisa langsung dimasukkan ke 6 digit kotak OTP).
4. Berhasil masuk! Anda akan melihat:
   - Kartu digital membership atas nama Budi Santoso.
   - Sisa saldo Rp 100.000.
   - Menu riwayat transaksi & akses.

### Skenario 4: Simulasi Akses Pintu Ruangan (*Scan NFC*)
1. Pada Web Admin, buka menu **Scan NFC**.
2. Pilih Ruangan (misal: *Meeting Room* dengan tarif Rp 25.000).
3. Masukkan UID kartu member (`NFC-TEST-001`).
4. Klik **Simulasi Tap Kartu**.
5. Sistem akan memotong saldo member otomatis menjadi Rp 75.000 dan memperpanjang masa aktif kartu 1 tahun.
6. Cek aplikasi mobile: tarik layar ke bawah (*pull-to-refresh*), saldo akan berkurang menjadi Rp 75.000, serta muncul notifikasi mutasi transaksi dan akses pintu.

### Skenario 5: Transaksi Belanja di Outlet / Kasir
1. Pada Web Admin, buka menu **Transaksi Outlet** -> **Tambah Transaksi**.
2. Pilih member, pilih outlet, dan masukkan nominal belanja.
3. Anda juga dapat memilih promo diskon jika tersedia.
4. Klik **Proses Transaksi**. Saldo member akan otomatis dipotong sesuai total belanja setelah diskon.

---

## 🔑 Akun Bawaan (Default Credentials)

Setelah menjalankan perintah `php artisan migrate --seed`, akun berikut siap digunakan:

- **URL Login Admin:** `http://localhost:8000/login`
- **Email:** `admin@technolife.com`
- **Password:** `admin123`
- **Role:** Administrator (Akses Penuh)

---

## 🛠 Solusi Kendala Umum (Troubleshooting)

1. **Aplikasi Mobile Gagal Terhubung ke Backend / "Periksa koneksi internet Anda"**:
   - Pastikan laptop dan HP fisik berada di satu jaringan Wi-Fi yang sama.
   - Pastikan server backend dijalankan dengan opsi `--host=0.0.0.0` (bukan hanya `127.0.0.1`).
   - Periksa firewall laptop agar mengizinkan koneksi masuk (*incoming traffic*) pada port 8000.
2. **Error `Access denied for user 'root'@'localhost'` pada Laravel**:
   - Pastikan layanan MySQL di XAMPP / sistem Anda sudah berjalan (*running*).
   - Pastikan password MySQL di file `.env` sudah sama dengan password root database Anda.
3. **Penyimpanan Gambar atau Asset Tidak Muncul**:
   - Jalankan perintah: `php artisan storage:link` di dalam folder `backend/membership-api/`.
4. **Dependensi Flutter Bermasalah**:
   - Jalankan `flutter clean` lalu ulangi `flutter pub get`.

