# Antigravity IDE - Project Guidelines & AI Agent Rules

Dokumen ini berisi standar dan aturan yang **wajib** diikuti oleh setiap AI Agent saat menulis atau mengedit kode di proyek ini.

## 1. Tech Stack & UI Framework
- **Backend:** Laravel (dengan PHP 8.x).
- **Frontend:** Livewire 3 + Alpine.js + Tailwind CSS.
- **Komponen UI:** **Wajib** menggunakan **Flux UI v2** (`<flux:*>`). Jangan gunakan elemen HTML mentah atau library UI lain jika komponen Flux UI tersedia (misal: gunakan `<flux:button>`, `<flux:input>`, `<flux:table>`, `<flux:modal>`, `<flux:dropdown>`, dll). Desain harus terlihat modern, rapi, dan sekelas macOS/Spotlight.

## 2. Role-Based Access Control (RBAC) & Keamanan
- Selalu lindungi metode di Livewire (terutama `delete`, `save`, `update`) dengan `Gate::authorize('permissions:name')`.
- Sembunyikan elemen UI (tombol Tambah, Edit, Hapus, atau Menu) menggunakan `@can('permissions:name')` atau `@canany` jika *user* tidak memiliki izin.
- Pastikan setiap modul baru memiliki permission yang sesuai (misal: `markets:create`, `customers:update`).

## 3. Standar Tabel (Data Grids)
- Gunakan `<flux:table>` untuk semua tabel data.
- **Pencarian (Search):** Wajib ada di atas tabel (sebelah kiri tombol *Create*). Gunakan `<flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" />`.
- **Pengurutan (Sortable):** Kolom harus bisa diurutkan (gunakan `$sortBy` dan `$sortDirection` di Livewire).
- **Pagination:** Gunakan *trait* `WithPagination` dan panggil `->paginate()` di query.
- **Empty State:** Jika menggunakan `@forelse`, pastikan **selalu ada blok `@empty`** yang berisi `<flux:table.cell colspan="...">Tidak ada data.</flux:table.cell>` sebelum penutup `@endforelse`. Jangan sampai terjadi *ParseError*!

## 4. Standar Kode Livewire (PHP)
- Gunakan *Attribute* `#[Computed]` untuk mengambil data (misal: `public function items()`).
- Jika membuat *State* yang perlu bertahan saat halaman di-*refresh*, pertimbangkan menggunakan atribut `#[Session]` untuk properti tersebut (daripada *local storage* mentah), kecuali jika itu murni *UI state* Alpine.js.
- Setelah melakukan aksi berhasil (Create, Update, Delete), gunakan `Flux::toast(heading: 'Judul Toast', text: 'Pesan toast', variant: 'success')` untuk memberi tahu *user*.
- Selalu periksa kolom *database* secara aktual (lewat Model di `app/Models/`) sebelum menulis perintah `->where()` atau `->orWhere()`. Jangan *copy-paste* kondisi pencarian tanpa memastikan kolomnya benar-benar ada di *table* tersebut (jangan sampai *Unknown column*).

## 5. Floating Windows & Modals
- Jika membuat jendela *floating* seperti **Kalkulator**:
  - Jangan gunakan modal dengan *background blur* bawaan jika jendela tersebut dimaksudkan agar *user* bisa tetap mengeklik elemen lain (multitasking).
  - Buat elemen *draggable* menggunakan Alpine.js (`@mousedown`, `@mousemove.window`, `@mouseup.window`).
  - **Wajib dukung layar sentuh (mobile):** Selalu tambahkan `@touchstart`, `@touchmove.window`, `@touchend.window` pada elemen *drag handle*.
  - Simpan posisi koordinat (`x` dan `y`) ke `localStorage` melalui Alpine agar posisinya tidak hilang saat halaman di-*refresh*.

## 6. Navigasi & Command Menu (Spotlight)
- Aplikasi ini menggunakan **Command Menu / Spotlight** global (`Ctrl+K`).
- Jika ada fitur atau modul baru yang bersifat umum, pastikan menu tersebut diregistrasikan ke dalam komponen `command-menu.blade.php` agar dapat diakses secara cepat.
- Gunakan `wire:navigate` untuk setiap perpindahan halaman SPA (Single Page Application).

## 7. Custom Modals & Bottom Sheets (Mobile UX)
- Komponen `<flux:modal>` di proyek ini telah dimodifikasi secara khusus agar otomatis berfungsi sebagai **Bottom Sheet** di layar perangkat *mobile* (menambah kenyamanan UX ala aplikasi *native*).
- **Syarat Wajib:** Saat membuat modal baru, Anda wajib menggunakan format atribut seperti di bawah ini agar efek *bottom sheet* tersebut dapat aktif:
  ```blade
  <flux:modal :closable="false" scroll="body" name="nama-modal" class="md:w-96 !rounded-3xl">
  ```
- **Penjelasan Atribut:**
  - `scroll="body"`: Ini adalah *trigger* yang paling penting. Tanpa atribut ini, modal akan tetap *mentok* di tengah/atas dan tidak menjadi *bottom sheet*.
  - `:closable="false"`: Mematikan perilaku klik-luar bawaan jika tidak diinginkan (Anda bisa menambahkan tombol Batal menggunakan `<flux:modal.close>`).

## 8. Custom UI Components Khusus
- Walaupun kita menggunakan Flux UI, proyek ini memiliki beberapa komponen kustom *(override)* yang dirancang spesifik dengan animasi/estetika khusus. Utamakan penggunaan komponen kustom ini dibandingkan bawaan Flux:
  - `<x-switch>` (`components/switch.blade.php`): Gunakan untuk *toggle switch* (jangan gunakan `<flux:switch>`). Sudah terintegrasi sempurna dengan Alpine `x-model` maupun Livewire `wire:model`.
  - `<x-tabs>` (`components/tabs.blade.php`): Gunakan untuk antarmuka *segmented control* atau bilah tab.
  - `<x-stepper>` (`components/stepper.blade.php`): Gunakan untuk antarmuka *wizard* (langkah-langkah berurutan).

## 9. Push Notifications (OneSignal Service)
- Jangan pernah menulis ulang kode HTTP/CURL ke API OneSignal (DRY).
- Selalu panggil *Service* `PushNotification` yang telah disediakan jika ada proses bisnis yang mengharuskan *trigger* notifikasi (*Job*, *Observer*, *Livewire*, dll).
- Contoh penggunaan:
  ```php
  use App\Services\PushNotification;
  use App\Models\User;

  // 1. Standar (kirim ke semua pengguna yang berlangganan/aktif)
  PushNotification::send('Judul Notif', 'Isi pesan di sini');

  // 2. Kirim ke user spesifik (misal berdasarkan Role tertentu)
  // Catatan: Frontend sudah otomatis memanggil OneSignal.login(auth()->id()), 
  // sehingga ID database kita otomatis tersinkronisasi dengan OneSignal.
  $salesIds = User::role('sales')->pluck('id')->toArray();

  PushNotification::send(
      'Target Penjualan', 
      'Ayo kejar target bulan ini!', 
      userIds: $salesIds // Hanya perangkat dengan ID user ini yang menerima notif
  );

  // 3. Kirim ke satu orang dengan data tambahan (Payload / URL)
  PushNotification::send(
      'Pesanan Baru', 
      'Ada pesanan masuk', 
      userIds: [5], 
      additionalData: ['url' => '/orders/1024']
  );
  ```

## 10. Strategi Caching (Real-time Cache Busting)
- Proyek ini mengutamakan performa tinggi untuk *Dashboard* dan *query* agregat yang berat (seperti *summary* atau laporan).
- **Larangan:** Jangan menggunakan `Cache::remember()` dengan waktu statis (misal batas 300 detik / 5 menit) karena akan menyebabkan *delay* keakuratan data di *production*.
- **Best Practice (Wajib): Gunakan Pendekatan Cache Busting**
  1. Semua model utama (`Order`, `OrderBook`, `Customer`, `Market`, `OrderItem`) harus memiliki *Trait* `\App\Traits\BustsDashboardCache`. *Trait* ini akan otomatis memperbarui nilai `dashboard_cache_version` setiap kali ada proses Simpan / Hapus data.
  2. Pada metode komponen Livewire (terutama `#[Computed]`) yang me-*load* agregat berat, pastikan kamu memanggil versi *cache* terbaru: 
     `$v = Cache::get('dashboard_cache_version', 1);`
  3. Sisipkan variabel versi `$v` tersebut ke dalam nama kunci cache, lalu gunakan metode **`Cache::rememberForever()`**.
  4. **Contoh Implementasi:**
     ```php
     $v = \Illuminate\Support\Facades\Cache::get('dashboard_cache_version', 1);
     return \Illuminate\Support\Facades\Cache::rememberForever("dash_top_items_v{$v}", function () {
         return \App\Models\OrderItem::query()->selectRaw('...')->get();
     });
     ```
  5. **Parent Touching:** Jika membuat *Model* turunan (seperti `OrderItem`), selalu pasang properti `protected $touches = ['order'];` agar setiap perubahan data anak akan merambat ke waktu *update* induknya dan berhasil memicu *cache busting*.

## 11. Integritas Data Stok & Transaksi Pergudangan (Banking-Grade ACID)
- **Prinsip Utama:** Data stok persediaan gudang diperlakukan setara dengan pencatatan saldo uang di rekening bank. Setiap mutasi fisik (barang masuk, muat ke truk, retur, produksi, opname) wajib memiliki audit trail yang lengkap dan tidak boleh ada data stok yang terpotong/bertambah sebagian (*zero partial update*).
- **Aturan Transaksi Database (`DB::transaction`):**
  1. **Wajib Atomik:** Setiap proses yang mengubah persediaan di `WarehouseStock` dan mencatat `StockMutation` **wajib** berada di dalam `DB::transaction(function () { ... })`. Operasi harus berhasil secara utuh atau tidak tersimpan sama sekali (*all-or-nothing* / *auto-rollback* jika terjadi kesalahan).
  2. **Pessimistic Locking (`lockForUpdate`):** Saat membaca record `WarehouseStock` sebelum penambahan atau pengurangan, selalu gunakan `->lockForUpdate()` untuk mencegah *race condition* ketika beberapa user melakukan transaksi bersamaan:
     ```php
     $stock = WarehouseStock::firstOrCreate(
         ['warehouse_id' => $warehouseId, 'item_id' => $itemId],
         ['current_stock' => 0, 'physical_stock' => 0]
     );
     $stock = WarehouseStock::where('id', $stock->id)->lockForUpdate()->first();
     ```
  3. **Try-Catch & Logging:** Bungkus transaksi pada Livewire action dengan blok `try-catch` agar error tersimpan di log server (`\Log::error(...)`) dan antarmuka menampilkan pesan yang ramah pengguna via `Flux::toast(..., variant: 'danger')`.
- **Standar Alur Dua Tahap Pengiriman & Retur (*Two-Stage Tracking*):**
  1. **Tahap 1 (Load-up / Muat Truk):** Saat muatan dinaikkan ke truk dan dikonfirmasi kirim, stok gudang dipotong **penuh** sesuai jumlah muatan fisik (`quantity`) dan dicatat mutasi `out` (`Pengiriman`).
  2. **Tahap 2 (Retur Toko / Pasar):** Saat muatan kembali dan checker/admin menginput barang retur (pada hari H sore atau H+1/H+2), sistem **otomatis menambah kembali** stok gudang (`WarehouseStock`) dan mencatat mutasi `in` (`Retur`) pada tanggal barang kembali masuk.
  3. **Koreksi Retur:** Jika ada kesalahan input retur yang kemudian diperkecil oleh admin, selisih pengurangannya otomatis memotong kembali stok gudang dengan mutasi penyeimbang (`out`, `Koreksi Retur`).
  4. Aturan ini memastikan kartu stok gudang (*Stock Ledger*) dan laporan sirkulasi harian (*Stock Circulation*) selalu akurat sesuai kondisi fisik riil di lapangan.