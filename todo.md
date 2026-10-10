# Rencana Perbaikan Sistem Harga & Nota

## 1. Kembalikan Logika Snapshot Harga (Revert Bypass)
Kita harus membatalkan *bypass* yang sebelumnya dibuat, agar nota kembali menggunakan harga snapshot (`order_items.price`). Ini sangat penting agar **Nota memiliki history** (nota bulan lalu tidak ikut berubah jika harga master hari ini diubah).
- [ ] Revert perubahan di `resources/views/livewire/order-books/print-notas.blade.php` (kembalikan `$harga = $item->price; if ($harga == 0) ...`)
- [ ] Revert perubahan di `resources/views/livewire/order-books/show.blade.php` (kembalikan prioritas `$orderItem->price`)
- [ ] Revert perubahan di `app/Livewire/OrderBooks/Show.php` pada method `summary()` (kembalikan prioritas `$orderItem->price`)

## 2. Buat Fitur "Sinkronisasi Harga" di Buku Order
Untuk menyelesaikan keluhan client yang malas mengedit pesanan satu per satu saat harga master berubah, kita buatkan tombol pintasan.
- [ ] Tambahkan tombol **"Sinkronkan Harga"** di halaman `Show` Buku Order (mungkin di sebelah tombol 'Tambah Pesanan' atau di menu dropdown aksi).
- [ ] Buat method `syncPrices()` di `app/Livewire/OrderBooks/Show.php`.
- [ ] Logika `syncPrices()`: Melakukan perulangan (loop) ke semua `order_items` pada buku tersebut, menarik harga terbaru dari master (`Item`), lalu menyimpannya (update) kembali ke kolom `order_items.price`.
- [ ] Pastikan sinkronisasi ini tidak bisa dilakukan jika buku sudah di-lock (MonthlyBackup).

## 3. Fitur History Perubahan Harga Master (Item Price History)
Agar *price* (master barang) juga memiliki *history* perubahan dari waktu ke waktu sesuai *request* terpendam dari client.
- [ ] Buat migration untuk tabel `item_price_histories`:
  - `id`
  - `item_id` (foreign key)
  - `old_prices` (json)
  - `new_prices` (json)
  - `changed_by` (user_id)
  - `created_at`
- [ ] Buat model `ItemPriceHistory`.
- [ ] Buat Observer untuk model `Item` (atau letakkan di method `updated` pada Livewire). Setiap kali array `prices` pada barang berubah, simpan jejak perubahannya ke tabel `item_price_histories`.
- [ ] (Opsional) Buat UI kecil/modal di halaman Master Barang untuk melihat riwayat perubahan harganya.
