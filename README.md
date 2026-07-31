# WMS (Warehouse Management System) 

Sebuah aplikasi Sistem Manajemen Gudang (Warehouse Management System) berbasis web yang dibangun dengan PHP Native dan MySQL. Aplikasi ini dirancang untuk mempermudah pencatatan, pemantauan, dan pengelolaan stok barang di gudang beserta lokasi penyimpanannya.

## 🚀 Fitur Utama

- **Dashboard**: Ringkasan stok barang dan notifikasi permintaan (Request) yang menunggu persetujuan.
- **Master Data**:
  - **Barang**: Kelola data barang (Kode, Kategori, Stok Minimal, Satuan).
  - **Lokasi Rak**: Kelola tata letak fisik / penempatan barang di dalam gudang.
  - **Supplier**: Kelola data pemasok barang.
  - **Divisi**: Kelola daftar divisi/departemen internal.
  - **User**: Manajemen akses pengguna (Admin, Operator, Requester).
- **Transaksi**:
  - **Barang Masuk**: Pencatatan restock barang dari Supplier.
  - **Barang Keluar**: Pencatatan distribusi barang ke Divisi.
  - **Permintaan Barang**: Alur persetujuan (*Approval*) barang keluar yang diajukan oleh Requester.
  - **Stock Opname**: Pencatatan penyesuaian (*adjustment*) antara stok sistem dan fisik.
- **Laporan**: Fitur cetak laporan transaksi.

## 👥 Role / Hak Akses
Aplikasi ini memiliki 3 level pengguna:
1. **Admin**: Memiliki akses penuh ke seluruh fitur (Master Data, Transaksi, Laporan).
2. **Operator**: Memiliki akses ke Transaksi dan pemantauan stok (tanpa akses Master Data).
3. **Requester**: Hanya dapat mengajukan permintaan barang (*Request*) dan melihat riwayat permintaannya.

## 🛠️ Teknologi yang Digunakan
- **Frontend**: HTML5, CSS3 (Custom), [Lucide Icons](https://lucide.dev/)
- **Backend**: PHP 8.x (Native)
- **Database**: MySQL / MariaDB (MySQLi Extension)

## ⚙️ Panduan Instalasi (Local)

1. Pastikan komputer Anda telah terinstal web server lokal (seperti **XAMPP**, **Laragon**, dsb).
2. Clone / salin folder project ini ke dalam direktori root web server Anda:
   - XAMPP: `C:\xampp\htdocs\wms_sim`
   - Laragon: `C:\laragon\www\wms_sim`
3. Buat database baru di MySQL dengan nama `db_wms_baru`.
4. Import file SQL yang berada di `db/db_wms_baru.sql` ke dalam database tersebut.
5. Konfigurasi **BASE_URL**:
   - Buka file `config/constants.php`
   - Sesuaikan *value* `BASE_URL` dengan alamat web lokal Anda (contoh: `http://localhost/wms_sim/` atau `http://wms_sim.test/`).
6. Akses aplikasi melalui browser dan login menggunakan akun demo:
   - **Admin**: `admin` / `123456`
   - **Operator**: `operator` / `123456`
   - **Requester**: `requester` / `123456`
