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