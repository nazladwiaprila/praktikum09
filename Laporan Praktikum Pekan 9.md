# LAPORAN PRAKTIKUM PEKAN 9
## HMVC, Authorization, dan AJAX pada Laravel React Starter Kit

### Identitas
- Nama: ................................................
- NIM: ..................................................
- Kelas: ................................................
- Tanggal praktikum: ....................................

## 1. Tujuan Praktikum

Praktikum ini mempelajari penerapan pola HMVC pada Laravel dengan halaman React di setiap modul, penggunaan Inertia.js, pengaturan hak akses berdasarkan role, serta pembuatan fitur CRUD melalui Inertia dan AJAX. Praktikum juga membahas validasi dan unggah berkas.

## 2. Gambaran Proyek

Proyek menggabungkan Laravel sebagai pengelola route, validasi, dan akses basis data dengan React sebagai pembangun antarmuka. Inertia.js menghubungkan keduanya sehingga halaman React dapat menerima data dari controller Laravel.

Pola HMVC memisahkan fitur aplikasi menjadi modul yang memiliki bagian-bagian sendiri. Pada proyek ini, contoh modul utamanya adalah:

- `Modules/Pendaftaran`: mengelola data pendaftar, validasi, unggah berkas, dan halaman React.
- `Modules/UserManagement`: mengelola akun pengguna dan halaman React untuk admin.

Pemisahan tersebut membuat kode untuk satu fitur lebih mudah ditemukan dan dikembangkan tanpa mencampurkannya dengan fitur lain.

## 3. Penjelasan Bagian Kode Penting

### 3.1 Modul Pendaftaran

#### Route dan pembatasan akses

File `Modules/Pendaftaran/routes/web.php` mendaftarkan halaman dan endpoint pendaftaran. Semua route dikelompokkan di dalam middleware `auth` dan `role:admin,user`, sehingga pengguna harus masuk dan memiliki salah satu role tersebut untuk mengakses fitur.

Route penting yang digunakan:

- `GET /pendaftaran`: menampilkan halaman React.
- `GET /pendaftaran/data`: mengambil daftar pendaftar dalam format JSON.
- `POST /pendaftaran`: menyimpan pendaftar baru.
- `GET /pendaftaran/{daftar}/edit`: mengambil satu data untuk formulir edit.
- `PUT /pendaftaran/{daftar}`: memperbarui data.
- `DELETE /pendaftaran/{daftar}`: menghapus data.

#### Controller

`PendaftaranController.php` menghubungkan route dengan model dan respons aplikasi. Method `index()` menampilkan halaman melalui `Inertia::render('Pendaftaran::index')`, sedangkan `getData()` mengirim data terbaru sebagai JSON.

Method `store()` mengambil data yang telah lolos validasi, menyimpan berkas ke disk `public` pada folder `berkas_pendaftaran`, menyimpan nama asli berkas, lalu membuat data pendaftar. Method `update()` mengganti berkas lama hanya jika pengguna mengunggah berkas baru. Method `destroy()` menghapus berkas terkait dari penyimpanan sebelum menghapus data pendaftarnya.

#### Validasi formulir dan berkas

`DaftarRequest.php` memusatkan aturan validasi. Nama, sekolah, program studi tujuan, email, dan nomor HP wajib diisi. Email harus unik; nomor HP dibatasi pada karakter angka atau tanda tambah dengan panjang 8-20 karakter. Berkas wajib pada pendaftaran baru, tetapi boleh kosong saat edit agar berkas lama tetap digunakan. Jenis berkas yang diterima adalah JPG, JPEG, PNG, atau PDF dengan ukuran maksimum 2 MB.

#### Model dan basis data

Model `Daftar.php` menentukan kolom yang boleh diisi melalui `$fillable`, sehingga penyimpanan massal hanya berlaku untuk atribut yang diizinkan. Accessor `berkasUrl` membuat atribut `berkas_url` pada respons model agar React dapat menampilkan tautan berkas yang tersimpan.

#### Antarmuka React dan AJAX

`Modules/Pendaftaran/resources/js/pages/index.tsx` membangun halaman daftar pendaftar. React Hooks digunakan untuk mengelola data, status pemuatan, formulir, pesan, dan dialog. `useEffect()` memanggil pengambilan data saat halaman dibuka, sedangkan `useState()` menyimpan perubahan keadaan antarmuka.

Komunikasi dengan server dilakukan melalui helper `http` dan endpoint pada objek `urls`. Formulir dikirim menggunakan `FormData` agar berkas dapat diunggah bersama data teks. Saat mengedit, `_method: PUT` disertakan untuk method spoofing Laravel. Kegagalan validasi dengan status 422 ditampilkan pada formulir, sementara operasi berhasil diikuti pemuatan ulang daftar. Pencarian dilakukan di sisi browser menggunakan `useMemo()`, sehingga tidak perlu mengirim permintaan pencarian baru ke server.

### 3.2 Modul UserManagement

#### Route khusus admin

`Modules/UserManagement/routes/web.php` memakai middleware `auth` dan `role:admin`, lalu mendaftarkan resource route untuk pengelolaan pengguna selain halaman detail. Artinya, pengguna harus sudah login dan memiliki role admin sebelum dapat mengelola akun.

#### Controller pengelolaan pengguna

`UserManagementController.php` menangani daftar, tambah, edit, pembaruan, dan penghapusan pengguna. Pada halaman daftar, controller membaca kata kunci pencarian dan filter role, mengurutkan nama, lalu membatasi hasil menjadi 10 pengguna per halaman. Data yang dikirim ke React dibentuk secara terpilih, termasuk label role dan waktu pembuatan.

Saat memperbarui akun, kata sandi yang kosong tidak disertakan dalam perubahan sehingga kata sandi lama tetap berlaku. Penghapusan akun milik admin yang sedang login juga ditolak agar admin tidak menghapus akunnya sendiri.

#### Halaman daftar pengguna

`Modules/UserManagement/resources/js/pages/index.tsx` menerima daftar, filter, dan pilihan role dari controller. Komponen menampilkan tabel, pencarian nama/email, filter role, pagination, serta tombol untuk menambah, mengedit, dan menghapus pengguna. Navigasi dan pengiriman aksi menggunakan `router` dari Inertia, sehingga halaman bekerja melalui alur Inertia tanpa membuat permintaan AJAX khusus seperti modul Pendaftaran.

#### Role pengguna

Enum `App\Enums\UserRole` mendefinisikan role `admin` dan `user`, beserta label tampilannya. Penggunaan enum membantu menyamakan nilai role yang dipakai dalam aplikasi.

## 4. Alur Kerja Fitur Pendaftaran

1. Pengguna membuka halaman pendaftaran dan React mengambil daftar data dari endpoint JSON.
2. Pengguna mengisi formulir dan, saat pendaftaran baru, memilih berkas yang sesuai.
3. React mengirim data dan berkas memakai `FormData`.
4. Laravel memvalidasi permintaan melalui `DaftarRequest`.
5. Controller menyimpan data ke basis data dan berkas ke penyimpanan publik.
6. React menampilkan pesan hasil dan memuat ulang daftar.
7. Pada penghapusan, pengguna mengonfirmasi tindakan; server menghapus berkas dan data terkait.

## 5. Perbedaan Pendekatan Inertia dan AJAX pada Proyek

Modul UserManagement memakai alur Inertia: controller mengirim data halaman dan React menggunakan `router` untuk navigasi atau aksi. Modul Pendaftaran memakai AJAX: React memanggil endpoint JSON secara langsung, mengelola status pemuatan dan respons, serta mengirim formulir dengan `FormData`. Kedua pendekatan menggunakan Laravel dan React, tetapi cara pertukaran data dan pengelolaan halamannya berbeda.

## 6. Contoh Potongan Kode dan Penjelasannya

Bagian ini menampilkan potongan kode inti yang benar-benar digunakan dalam proyek. Penjelasan diberikan tepat setelah setiap potongan agar hubungan antara kode dan fungsinya mudah dipahami.

### 6.1 Membatasi route dengan middleware

Potongan dari `Modules/Pendaftaran/routes/web.php`:

```php
Route::middleware(['auth', 'role:admin,user'])->group(function () {
    Route::get('pendaftaran', [PendaftaranController::class, 'index']);
    Route::get('pendaftaran/data', [PendaftaranController::class, 'getData']);
    Route::post('pendaftaran', [PendaftaranController::class, 'store']);
});
```

Penjelasan setiap baris:

- `Route::middleware([...])->group(function () {` membuat grup route yang seluruhnya menjalani middleware yang disebutkan.
- `'auth'` meminta pengguna masuk terlebih dahulu.
- `'role:admin,user'` mengizinkan pengguna dengan role admin atau user.
- `Route::get('pendaftaran', ...)` mendaftarkan permintaan GET untuk membuka halaman pendaftaran dan meneruskannya ke method `index`.
- `Route::get('pendaftaran/data', ...)` menyediakan endpoint GET untuk mengambil data pendaftar melalui method `getData`.
- `Route::post('pendaftaran', ...)` mendaftarkan endpoint POST untuk menyimpan pendaftar baru melalui method `store`.
- `});` menutup fungsi grup route dan pemanggilan `group`.

Dengan demikian, middleware berlaku untuk semua route di dalam grup. Di file aslinya, route edit, update, dan hapus juga dimasukkan ke grup tersebut.

### 6.2 Memvalidasi data dan berkas

Potongan dari `Modules/Pendaftaran/app/Http/Requests/DaftarRequest.php`:

```php
'email' => [
    'required',
    'email',
    'max:255',
    Rule::unique('daftars', 'email')->ignore($daftar?->id)
],
'berkas' => [
    $daftar ? 'nullable' : 'required',
    'file',
    'mimes:jpg,jpeg,png,pdf',
    'max:2048'
],
```

Potongan ini merupakan isi dari array aturan validasi pada `rules()`. Penjelasan setiap baris:

- `'email' => [` memulai aturan untuk kolom email.
- `'required'` mewajibkan email diisi.
- `'email'` memeriksa format alamat email.
- `'max:255'` membatasi panjang email sampai 255 karakter.
- `Rule::unique('daftars', 'email')` meminta Laravel memastikan email belum digunakan pada kolom `email` di tabel `daftars`.
- `->ignore($daftar?->id)` mengecualikan ID pendaftar yang sedang diedit dari pemeriksaan unik. Operator `?->` mencegah akses properti jika `$daftar` bernilai null.
- `],` menutup array aturan email.
- `'berkas' => [` memulai aturan untuk unggahan berkas.
- `$daftar ? 'nullable' : 'required'` menjadikan berkas opsional saat mengedit data yang sudah ada, tetapi wajib saat membuat data baru.
- `'file'` memastikan nilai yang dikirim dikenali Laravel sebagai berkas unggahan.
- `'mimes:jpg,jpeg,png,pdf'` hanya menerima ekstensi/jenis berkas yang disebutkan.
- `'max:2048'` membatasi ukuran menjadi 2048 KB atau 2 MB.
- `],` menutup array aturan berkas.

Validasi tetap dilakukan di server meskipun antarmuka React juga memberi petunjuk format berkas.

### 6.3 Menyimpan data dan berkas di controller

Potongan dari `Modules/Pendaftaran/app/Http/Controllers/PendaftaranController.php`:

```php
$data = $request->safe()->except('berkas');
$file = $request->file('berkas');
$data['berkas'] = $file->store('berkas_pendaftaran', 'public');
$data['nama_berkas'] = $file->getClientOriginalName();
Daftar::create($data);
```

Penjelasan setiap baris:

- `$data = $request->safe()->except('berkas');` mengambil data yang lolos validasi, lalu mengeluarkan berkas agar dapat diproses tersendiri.
- `$file = $request->file('berkas');` mengambil objek berkas unggahan dari permintaan.
- `$data['berkas'] = $file->store('berkas_pendaftaran', 'public');` menyimpan berkas di folder `berkas_pendaftaran` pada disk Laravel `public`, lalu mencatat path penyimpanannya ke data.
- `$data['nama_berkas'] = $file->getClientOriginalName();` menyimpan nama asli berkas agar nama tersebut dapat ditampilkan kepada pengguna.
- `Daftar::create($data);` membuat record pendaftar baru menggunakan atribut yang telah disiapkan dan mengizinkan mass assignment melalui `$fillable` pada model.

### 6.4 Mengambil data dan mengirim formulir dengan AJAX

Potongan dari `Modules/Pendaftaran/resources/js/pages/index.tsx`:

```tsx
useEffect(() => {
    loadData();
}, []);

const formData = new FormData();
Object.entries(form).forEach(([key, value]) => formData.append(key, value));
if (file) {
    formData.append('berkas', file);
}
```

Penjelasan setiap baris:

- `useEffect(() => {` mendaftarkan efek samping React yang dijalankan setelah komponen dirender.
- `loadData();` memanggil fungsi yang meminta daftar pendaftar dari server.
- `}, []);` menutup efek dan memberi dependency array kosong, sehingga pemanggilan dilakukan saat komponen pertama kali dimuat.
- `const formData = new FormData();` membuat wadah data formulir yang mendukung teks sekaligus berkas.
- `Object.entries(form).forEach(([key, value]) => formData.append(key, value));` mengubah setiap pasangan nama dan nilai pada objek `form` menjadi entri di `FormData`.
- `if (file) {` memeriksa apakah pengguna telah memilih berkas.
- `formData.append('berkas', file);` menambahkan berkas terpilih dengan nama field `berkas`, sesuai nama yang dibaca Laravel.
- `}` menutup blok kondisi. Setelah itu `FormData` dikirim melalui helper `http` dengan method POST di dalam `handleSubmit()`.

### 6.5 Mengelola state formulir React

Potongan dari halaman React Pendaftaran:

```tsx
const [records, setRecords] = useState<Daftar[]>([]);
const [loading, setLoading] = useState(true);
const [form, setForm] = useState<FormFields>(emptyForm);
```

Penjelasan setiap baris:

- `const [records, setRecords] = useState<Daftar[]>([]);` membuat state daftar pendaftar bertipe array `Daftar`, dengan nilai awal array kosong. `setRecords` digunakan untuk memperbarui daftar.
- `const [loading, setLoading] = useState(true);` membuat state status pemuatan yang dimulai dengan `true`, sehingga halaman dapat menampilkan indikator loading.
- `const [form, setForm] = useState<FormFields>(emptyForm);` membuat state formulir bertipe `FormFields` dan mengisinya dengan nilai awal dari `emptyForm`. `setForm` memperbarui nilai formulir.

### 6.6 Memastikan akses dan validasi pengelolaan user

Potongan dari `Modules/UserManagement/app/Http/Requests/StoreUserRequest.php`:

```php
'role' => ['required', Rule::enum(UserRole::class)],
'password' => ['required', 'confirmed', Password::defaults()],
```

Penjelasan setiap baris:

- `'role' => [...]` menetapkan aturan validasi untuk role akun.
- `'required'` mewajibkan role dipilih.
- `Rule::enum(UserRole::class)` membatasi nilai role ke pilihan yang didefinisikan enum `UserRole`, yaitu `admin` atau `user`.
- `'password' => [...]` menetapkan aturan untuk kata sandi.
- `'required'` mewajibkan kata sandi saat akun baru dibuat.
- `'confirmed'` meminta nilai `password_confirmation` cocok dengan `password`.
- `Password::defaults()` menerapkan aturan kata sandi bawaan Laravel.

Kurung dan koma pada potongan tersebut adalah sintaks array PHP: kurung siku mengelompokkan aturan satu field dan koma memisahkan elemen.

Permintaan juga memeriksa hak akses admin:

```php
public function authorize(): bool
{
    return $this->user()?->isAdmin() ?? false;
}
```

Penjelasan setiap baris:

- `public function authorize(): bool` mendefinisikan method otorisasi yang mengembalikan nilai boolean.
- `{` membuka isi method.
- `$this->user()?->isAdmin()` memeriksa apakah pengguna yang sedang login adalah admin. Operator null-safe `?->` tidak memanggil method jika pengguna tidak tersedia.
- `?? false` menghasilkan `false` jika pemeriksaan sebelumnya bernilai null, sehingga permintaan tanpa pengguna tidak diizinkan.
- `}` menutup method. Jika hasilnya `false`, Form Request menolak permintaan.

### 6.7 Mencegah penghapusan akun sendiri

Potongan dari `Modules/UserManagement/app/Http/Controllers/UserManagementController.php`:

```php
if ($user->is($request->user())) {
    return redirect()->route('admin.users.index')
        ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
}
$user->delete();
```

Penjelasan setiap baris:

- `if ($user->is($request->user())) {` membandingkan model pengguna yang akan dihapus dengan pengguna yang sedang login.
- `return redirect()->route('admin.users.index')` menghentikan proses penghapusan dan mengarahkan kembali ke halaman daftar user.
- `->with('error', '...')` menambahkan pesan error sementara (flash message) untuk ditampilkan setelah redirect.
- `}` menutup kondisi. Baris-baris di dalamnya hanya dijalankan jika admin mencoba menghapus akun sendiri.
- `$user->delete();` menghapus akun dari basis data jika kondisi sebelumnya tidak terpenuhi.

Pemeriksaan ini mencegah admin mengunci dirinya sendiri di luar aplikasi.

## 7. Kesimpulan

Praktikum ini menunjukkan bahwa struktur HMVC dapat menjaga fitur tetap terpisah berdasarkan modul. Laravel menangani routing, pemeriksaan akses, validasi, dan penyimpanan, sementara React mengelola tampilan serta interaksi pengguna. Modul Pendaftaran memperlihatkan CRUD AJAX dan unggah berkas, sedangkan UserManagement memperlihatkan CRUD berbasis Inertia dan pembatasan akses admin. Validasi server dan pemeriksaan role merupakan bagian penting untuk menjaga data serta membatasi akses sesuai kewenangan.

## 8. Referensi

Modul 9: *HMVC, Authorization, dan AJAX pada Laravel React Starter Kit*.
