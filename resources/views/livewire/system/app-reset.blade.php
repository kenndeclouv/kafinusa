<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <flux:badge size="sm" color="red">Sistem & Developer</flux:badge>
                <flux:badge size="sm" color="zinc">Zona Berbahaya</flux:badge>
            </div>
            <flux:heading size="xl" class="font-bold tracking-tight">Reset Aplikasi</flux:heading>
            <flux:subheading class="text-zinc-500 dark:text-zinc-400">
                Kosongkan seluruh data operasional & transaksi aplikasi untuk memulai siklus baru, dengan tetap mempertahankan data master di menu Database.
            </flux:subheading>
        </div>
        <div class="flex items-center gap-2">
            <flux:button wire:click="$refresh" icon="arrow-path" size="sm" variant="subtle">
                Perbarui Data
            </flux:button>
        </div>
    </div>

    {{-- Alert Warning Card --}}
    <div class="p-5 rounded-2xl border border-red-200 dark:border-red-900/50 bg-red-50/70 dark:bg-red-950/20 text-red-900 dark:text-red-200 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-start gap-3.5">
            <div class="p-2.5 rounded-xl bg-red-100 dark:bg-red-900/50 text-red-600 dark:text-red-400 shrink-0">
                <flux:icon.exclamation-triangle class="w-6 h-6" />
            </div>
            <div>
                <h3 class="text-base font-semibold text-red-950 dark:text-red-100">Peringatan: Tindakan Ini Bersifat Permanen</h3>
                <p class="text-sm text-red-800/90 dark:text-red-300/90 mt-1 leading-relaxed">
                    Setelah reset dijalankan, seluruh riwayat order book, pesanan, pengiriman, kartu stok gudang, dan catatan produksi harian akan <strong>dihapus permanen</strong>.
                    Pastikan Anda telah melakukan backup jika masih memerlukan arsip data sebelumnya.
                </p>
            </div>
        </div>
        <flux:button variant="danger" icon="trash" x-on:click="$flux.modal('confirm-reset-modal').show()" class="shrink-0 w-full md:w-auto shadow-sm">
            Mulai Reset Aplikasi
        </flux:button>
    </div>

    {{-- Comparison Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Data yang Dikosongkan --}}
        <flux:card class="border-red-200/60 dark:border-red-900/30 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-zinc-200 dark:border-white/10">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 rounded-xl bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400">
                            <flux:icon.trash class="w-5 h-5" />
                        </div>
                        <div>
                            <flux:heading size="lg" class="text-red-600 dark:text-red-400">Data Operasional Dikosongkan</flux:heading>
                            <flux:text class="text-xs">Data transaksi harian yang akan dihapus bersih</flux:text>
                        </div>
                    </div>
                    <flux:badge color="red" size="sm">
                        {{ number_format($this->totalOperationalRecords) }} Record Terdeteksi
                    </flux:badge>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 rounded-xl bg-zinc-50 dark:bg-white/[0.03] border border-zinc-100 dark:border-white/5">
                        <div class="flex items-center gap-3">
                            <flux:icon.shopping-cart class="w-4 h-4 text-zinc-500" />
                            <div>
                                <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Buku Order & Pesanan Pelanggan</div>
                                <div class="text-xs text-zinc-500">Order Books, Orders, Item Pesanan</div>
                            </div>
                        </div>
                        <span class="font-mono text-sm font-semibold text-red-600 dark:text-red-400">
                            {{ number_format($this->operationalCounts['orders'] + $this->operationalCounts['order_books'] + $this->operationalCounts['order_items']) }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-xl bg-zinc-50 dark:bg-white/[0.03] border border-zinc-100 dark:border-white/5">
                        <div class="flex items-center gap-3">
                            <flux:icon.truck class="w-4 h-4 text-zinc-500" />
                            <div>
                                <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Rencana & Muatan Pengiriman</div>
                                <div class="text-xs text-zinc-500">Shipment Plans, Batches, Item Pengiriman</div>
                            </div>
                        </div>
                        <span class="font-mono text-sm font-semibold text-red-600 dark:text-red-400">
                            {{ number_format($this->operationalCounts['shipments'] + $this->operationalCounts['shipment_items']) }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-xl bg-zinc-50 dark:bg-white/[0.03] border border-zinc-100 dark:border-white/5">
                        <div class="flex items-center gap-3">
                            <flux:icon.arrows-right-left class="w-4 h-4 text-zinc-500" />
                            <div>
                                <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Mutasi & Stok Persediaan Gudang</div>
                                <div class="text-xs text-zinc-500">Riwayat Mutasi & Tabel Stok Gudang (Dikosongkan total)</div>
                            </div>
                        </div>
                        <span class="font-mono text-sm font-semibold text-red-600 dark:text-red-400">
                            {{ number_format($this->operationalCounts['stock_mutations'] + $this->operationalCounts['stock_opnames']) }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-xl bg-zinc-50 dark:bg-white/[0.03] border border-zinc-100 dark:border-white/5">
                        <div class="flex items-center gap-3">
                            <flux:icon.wrench-screwdriver class="w-4 h-4 text-zinc-500" />
                            <div>
                                <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Produksi Harian Pabrik Tahu</div>
                                <div class="text-xs text-zinc-500">Data Produksi & Hasil Cetakan Tahu</div>
                            </div>
                        </div>
                        <span class="font-mono text-sm font-semibold text-red-600 dark:text-red-400">
                            {{ number_format($this->operationalCounts['productions'] + $this->operationalCounts['production_results']) }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-xl bg-zinc-50 dark:bg-white/[0.03] border border-zinc-100 dark:border-white/5">
                        <div class="flex items-center gap-3">
                            <flux:icon.fire class="w-4 h-4 text-zinc-500" />
                            <div>
                                <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Divisi Petis (Produksi & Penjualan)</div>
                                <div class="text-xs text-zinc-500">Masakan, Penjualan Petis, Bahan Baku, Stok Awal</div>
                            </div>
                        </div>
                        <span class="font-mono text-sm font-semibold text-red-600 dark:text-red-400">
                            {{ number_format($this->operationalCounts['petis_productions'] + $this->operationalCounts['petis_sales'] + $this->operationalCounts['petis_opnames'] + $this->operationalCounts['petis_entries']) }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-xl bg-zinc-50 dark:bg-white/[0.03] border border-zinc-100 dark:border-white/5">
                        <div class="flex items-center gap-3">
                            <flux:icon.archive-box class="w-4 h-4 text-zinc-500" />
                            <div>
                                <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Arsip Tutup Buku Bulanan</div>
                                <div class="text-xs text-zinc-500">Daftar kunci bulan tutup buku</div>
                            </div>
                        </div>
                        <span class="font-mono text-sm font-semibold text-red-600 dark:text-red-400">
                            {{ number_format($this->operationalCounts['monthly_backups']) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-zinc-200 dark:border-white/10 text-xs text-zinc-500 flex items-center gap-2">
                <flux:icon.information-circle class="w-4 h-4 text-amber-500 shrink-0" />
                <span>Tabel persediaan stok gudang dikosongkan total (kondisi fresh seperti baru di-migrate).</span>
            </div>
        </flux:card>

        {{-- Data Master yang Aman (Menu Database) --}}
        <flux:card class="border-emerald-200/60 dark:border-emerald-900/30 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-zinc-200 dark:border-white/10">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                            <flux:icon.shield-check class="w-5 h-5" />
                        </div>
                        <div>
                            <flux:heading size="lg" class="text-emerald-600 dark:text-emerald-400">Data Master Tetap Terjaga</flux:heading>
                            <flux:text class="text-xs">Semua data pada menu Database & Akun tetap utuh</flux:text>
                        </div>
                    </div>
                    <flux:badge color="green" size="sm">
                        Aman & Dilindungi
                    </flux:badge>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 rounded-xl bg-zinc-50 dark:bg-white/[0.03] border border-zinc-100 dark:border-white/5">
                        <div class="flex items-center gap-3">
                            <flux:icon.building-storefront class="w-4 h-4 text-emerald-500" />
                            <div>
                                <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Master Pasar & Pelanggan</div>
                                <div class="text-xs text-zinc-500">Pasar, Pelanggan (status piutang dinolkan)</div>
                            </div>
                        </div>
                        <span class="font-mono text-sm font-semibold text-emerald-600 dark:text-emerald-400">
                            {{ number_format($this->masterCounts['markets']) }} Pasar / {{ number_format($this->masterCounts['customers']) }} Cust
                        </span>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-xl bg-zinc-50 dark:bg-white/[0.03] border border-zinc-100 dark:border-white/5">
                        <div class="flex items-center gap-3">
                            <flux:icon.building-office class="w-4 h-4 text-emerald-500" />
                            <div>
                                <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Master Gudang</div>
                                <div class="text-xs text-zinc-500">Daftar lokasi gudang penyimpanan</div>
                            </div>
                        </div>
                        <span class="font-mono text-sm font-semibold text-emerald-600 dark:text-emerald-400">
                            {{ number_format($this->masterCounts['warehouses']) }} Gudang
                        </span>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-xl bg-zinc-50 dark:bg-white/[0.03] border border-zinc-100 dark:border-white/5">
                        <div class="flex items-center gap-3">
                            <flux:icon.user-group class="w-4 h-4 text-emerald-500" />
                            <div>
                                <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Master Tim, Koki & Pegawai</div>
                                <div class="text-xs text-zinc-500">Tim Sales/Armada, Koki Petis, Data Pegawai</div>
                            </div>
                        </div>
                        <span class="font-mono text-sm font-semibold text-emerald-600 dark:text-emerald-400">
                            {{ number_format($this->masterCounts['teams'] + $this->masterCounts['petis_cookers'] + $this->masterCounts['employees']) }} Orang
                        </span>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-xl bg-zinc-50 dark:bg-white/[0.03] border border-zinc-100 dark:border-white/5">
                        <div class="flex items-center gap-3">
                            <flux:icon.cube class="w-4 h-4 text-emerald-500" />
                            <div>
                                <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Master Barang & Kategori</div>
                                <div class="text-xs text-zinc-500">Daftar produk tahu & kategori produk</div>
                            </div>
                        </div>
                        <span class="font-mono text-sm font-semibold text-emerald-600 dark:text-emerald-400">
                            {{ number_format($this->masterCounts['items']) }} Barang / {{ number_format($this->masterCounts['item_categories']) }} Kat
                        </span>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-xl bg-zinc-50 dark:bg-white/[0.03] border border-zinc-100 dark:border-white/5">
                        <div class="flex items-center gap-3">
                            <flux:icon.calendar-days class="w-4 h-4 text-emerald-500" />
                            <div>
                                <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Jadwal Mingguan (Sales Schedules)</div>
                                <div class="text-xs text-zinc-500">Pola rute dan penugasan pasar mingguan</div>
                            </div>
                        </div>
                        <span class="font-mono text-sm font-semibold text-emerald-600 dark:text-emerald-400">
                            {{ number_format($this->masterCounts['sales_schedules']) }} Jadwal
                        </span>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-xl bg-zinc-50 dark:bg-white/[0.03] border border-zinc-100 dark:border-white/5">
                        <div class="flex items-center gap-3">
                            <flux:icon.key class="w-4 h-4 text-emerald-500" />
                            <div>
                                <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Pengguna & Peran Akses</div>
                                <div class="text-xs text-zinc-500">Akun login, password, dan hak akses tetap aktif</div>
                            </div>
                        </div>
                        <span class="font-mono text-sm font-semibold text-emerald-600 dark:text-emerald-400">
                            {{ number_format($this->masterCounts['users']) }} Akun
                        </span>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-zinc-200 dark:border-white/10 text-xs text-zinc-500 flex items-center gap-2">
                <flux:icon.check-circle class="w-4 h-4 text-emerald-500 shrink-0" />
                <span>Seluruh data referensi master di atas <strong>100% aman</strong> dan tidak akan hilang.</span>
            </div>
        </flux:card>
    </div>

    {{-- Confirmation Bottom Sheet / Modal (Rule #7) --}}
    <flux:modal :closable="false" scroll="body" name="confirm-reset-modal" class="md:w-[28rem] !rounded-3xl">
        <form wire:submit="executeReset" x-data="{ conf: '', pass: '' }" class="space-y-6">
            <div class="text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-red-100 dark:bg-red-950/60 text-red-600 dark:text-red-400 mb-4 ring-8 ring-red-50 dark:ring-red-950/30">
                    <flux:icon.trash class="w-7 h-7" />
                </div>
                <flux:heading size="lg" class="font-bold text-zinc-900 dark:text-zinc-100">
                    Konfirmasi Reset Aplikasi
                </flux:heading>
                <flux:subheading class="text-xs text-zinc-500 mt-1 max-w-sm mx-auto">
                    Tindakan ini akan mengosongkan seluruh data transaksi & operasional secara permanen. Data master pada menu Database tetap dipertahankan.
                </flux:subheading>
            </div>

            <div class="p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900/50 text-xs text-amber-800 dark:text-amber-200">
                <p class="font-semibold mb-1">Sebelum melanjutkan, perhatikan:</p>
                <ul class="list-disc pl-4 space-y-1">
                    <li>Semua order book, order, dan mutasi stok akan terhapus.</li>
                    <li>Tabel stok gudang dan riwayat mutasi akan dikosongkan total.</li>
                    <li>Sesi login Anda tidak akan terputus.</li>
                </ul>
            </div>

            <div class="space-y-4">
                <flux:field>
                    <flux:label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                        1. Ketik kata <span class="font-mono font-bold text-red-600 dark:text-red-400">RESET</span> (huruf kapital):
                    </flux:label>
                    <flux:input 
                        x-model="conf"
                        wire:model="confirmation" 
                        placeholder="Ketik RESET" 
                        class="text-center font-mono font-bold tracking-widest text-base"
                        autocomplete="off"
                        autofocus 
                    />
                    <flux:error name="confirmation" />
                </flux:field>

                <flux:field>
                    <flux:label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                        2. Masukkan Password Akun Super Admin:
                    </flux:label>
                    <flux:input 
                        x-model="pass"
                        wire:model="password" 
                        type="password"
                        viewable
                        placeholder="Password login akun Anda"
                        class="text-sm"
                        autocomplete="current-password"
                    />
                    <flux:error name="password" />
                </flux:field>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <flux:modal.close class="w-full">
                    <flux:button type="button" variant="ghost" class="w-full" x-on:click="conf = ''; pass = ''; $wire.resetConfirmationFields()" :disabled="$isResetting">
                        Batal
                    </flux:button>
                </flux:modal.close>

                <flux:button 
                    type="submit"
                    variant="danger" 
                    class="w-full"
                    x-bind:disabled="conf.trim() !== 'RESET' || pass.trim() === '' || $wire.isResetting"
                >
                    <span wire:loading.remove wire:target="executeReset">Ya, Kosongkan Data</span>
                    <span wire:loading wire:target="executeReset" class="flex items-center justify-center gap-1.5">
                        <flux:icon.arrow-path class="w-4 h-4 animate-spin" />
                        Memproses...
                    </span>
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
