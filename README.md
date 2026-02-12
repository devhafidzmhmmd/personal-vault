# Pass Manager

Aplikasi manajer password berbasis web dengan workspace, vault terenkripsi, dan pintasan. Dibangun dengan Laravel dan Flowbite.

---

## Stack

| Kategori    | Teknologi |
|------------|-----------|
| **Backend** | PHP 8.2+, [Laravel](https://laravel.com) 12 |
| **Auth**   | [Laravel Breeze](https://laravel.com/docs/starter-kits#breeze) (Blade) |
| **Frontend** | [Tailwind CSS](https://tailwindcss.com) 3, [Flowbite](https://flowbite.com), [Alpine.js](https://alpinejs.dev) |
| **Build**  | [Vite](https://vitejs.dev) 7 |
| **Database** | SQLite (default), bisa diganti MySQL/PostgreSQL lewat `.env` |

---

## Fitur

- **Autentikasi** — Register, login, reset password, verifikasi email
- **Workspace** — Banyak workspace per user; pilih workspace setelah login; ganti workspace lewat dropdown di sidebar
- **Master Password & Vault** — Set master password sekali; unlock vault dengan master password; password disimpan terenkripsi (PBKDF2 + AES); tombol Kunci untuk mengunci vault
- **Passwords** — CRUD password; tipe: App, Database, Server, Lainnya; enkripsi dengan vault key; tombol copy password; generate password; filter berdasarkan tipe; data per workspace aktif
- **Pintasan (Shortcuts)** — CRUD pintasan; icon picker (emoji); tampil di dashboard sebagai kartu; per workspace
- **Dashboard** — Kartu pintasan (homescreen style) untuk workspace aktif
- **Pengaturan** — Ganti master password (re-encrypt vault); kelola workspace (CRUD)
- **Profil** — Edit profil, ganti password login, hapus akun
- **Dark mode** — Toggle dark/light di sidebar

---

## Persyaratan

- PHP 8.2 atau lebih baru
- Composer
- Node.js 18+ & npm
- Ekstensi PHP: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML

---

## Cara Setup

### 1. Clone & masuk direktori

```bash
git clone <url-repo> passmanager
cd passmanager
```

### 2. Install dependency PHP

```bash
composer install
```

### 3. Environment

```bash
cp .env.example .env
php artisan key:generate
```

Sesuaikan `.env` jika perlu (misalnya database). Default pakai SQLite; file `database/database.sqlite` akan dipakai.

### 4. Database

```bash
php artisan migrate
```

### 5. Install dependency frontend & build

```bash
npm install
npm run build
```

Untuk development (watch + hot reload):

```bash
npm run dev
```

### 6. Jalankan aplikasi

```bash
php artisan serve
```

Buka di browser: **http://localhost:8000**

---

## Alur Penggunaan

1. **Register** atau **Login**
2. **Pilih workspace** (atau buat baru dari halaman pilih workspace)
3. **Set Master Password** (jika pertama kali) atau **Unlock** dengan master password
4. Setelah vault terbuka: akses **Dashboard**, **Passwords**, **Pintasan**
5. Ganti workspace kapan saja lewat **dropdown workspace** di sidebar
6. **Pengaturan** → Master Password (ganti) atau Workspace (tambah/edit/hapus)

---

## Struktur Penting

```
app/
├── Http/Controllers/
│   ├── DashboardController.php
│   ├── MasterPasswordController.php   # set/unlock/lock vault
│   ├── PasswordController.php         # CRUD password
│   ├── ShortcutController.php        # CRUD pintasan
│   ├── WorkspaceController.php       # CRUD workspace (settings)
│   └── WorkspaceSelectController.php # pilih & switch workspace
├── Models/
│   ├── User.php
│   ├── Workspace.php
│   ├── Password.php
│   └── Shortcut.php
├── Services/
│   └── VaultCryptoService.php        # enkripsi/dekripsi vault
└── Http/Middleware/
    └── EnsureVaultUnlocked.php       # wajib unlock sebelum akses app
```

---

## License

MIT
