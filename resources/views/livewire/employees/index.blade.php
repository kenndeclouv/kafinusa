<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <flux:heading size="xl">Pegawai</flux:heading>
            <flux:subheading>Kelola daftar pegawai di sini.</flux:subheading>
        </div>
        <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto mt-4 sm:mt-0">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari NIK, nama, no HP..."
                icon="magnifying-glass" class="w-full sm:w-64" />
            @can('employees:create')
                <flux:button wire:click="addEmployee" variant="primary" icon="plus" class="ms-auto w-full sm:w-auto">Tambah
                    Pegawai</flux:button>
            @endcan
        </div>
    </div>

    <div class="rounded-2xl bg-zinc-50 dark:bg-white/5 border border-zinc-200 dark:border-white/5 overflow-hidden">
        <div class="overflow-x-auto">
            <flux:table
                class="[&_th:first-child]:!ps-6 [&_td:first-child]:!ps-6 [&_th:last-child]:!pe-6 [&_td:last-child]:!pe-6">
                <flux:table.columns>
                    <flux:table.column sortable :sorted="$sortBy === 'employee_id_number'" :direction="$sortDirection"
                        wire:click="sort('employee_id_number')">NIK</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection"
                        wire:click="sort('name')">Nama</flux:table.column>
                    <flux:table.column>No. HP</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'position'" :direction="$sortDirection"
                        wire:click="sort('position')">Posisi</flux:table.column>
                    <flux:table.column>Akun Sistem</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    @canany(['employees:update', 'employees:delete'])
                        <flux:table.column>Aksi</flux:table.column>
                    @endcanany
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($this->employees as $employee)
                        <flux:table.row :key="$employee->id">
                            <flux:table.cell>
                                <flux:badge color="zinc" size="sm">{{ $employee->employee_id_number }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-3">
                                    <flux:avatar size="sm" :name="$employee->name" />
                                    <span>{{ $employee->name }}</span>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>{{ $employee->phone_number }}</flux:table.cell>
                            <flux:table.cell>{{ $employee->position }}</flux:table.cell>
                            <flux:table.cell>
                                @if ($employee->user_id)
                                    <flux:badge color="blue" size="sm" icon="user">{{ $employee->user->name }}
                                    </flux:badge>
                                @else
                                    <flux:badge color="zinc" size="sm">-</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @if ($employee->status)
                                    <flux:badge color="green" size="sm">Aktif</flux:badge>
                                @else
                                    <flux:badge color="zinc" size="sm">Tidak Aktif</flux:badge>
                                @endif
                            </flux:table.cell>
                            @canany(['employees:update', 'employees:delete'])
                                <flux:table.cell>
                                    <flux:dropdown>
                                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                        <flux:menu>
                                            @can('employees:update')
                                                <flux:menu.item wire:click="editEmployee({{ $employee->id }})" icon="pencil">
                                                    Edit</flux:menu.item>
                                            @endcan
                                            @can('employees:delete')
                                                <x-delete-modal id="delete-employee-{{ $employee->id }}"
                                                    action="delete({{ $employee->id }})" requireSlide="true"
                                                    title="Hapus pegawai?"
                                                    description="Anda akan menghapus pegawai ini. Tindakan ini tidak dapat dibatalkan." />
                                            @endcan
                                        </flux:menu>
                                    </flux:dropdown>
                                </flux:table.cell>
                            @endcanany
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="7" class="text-center text-zinc-500 dark:text-zinc-400">
                                Tidak ada pegawai yang ditemukan.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>

        @if ($this->employees->total() > 0)
            <flux:pagination :paginator="$this->employees" class="p-4" />
        @endif
    </div>

    <!-- Create/Edit Modal -->
    <flux:modal :closable="false" scroll="body" name="create-employee-modal" class="md:max-w-2xl !rounded-3xl">
        <form wire:submit="save">
            <flux:heading>{{ $editingEmployeeId ? 'Edit Pegawai' : 'Tambah Pegawai' }}</flux:heading>
            <flux:description>
                {{ $editingEmployeeId ? 'Perbarui data pegawai di bawah ini.' : 'Tambahkan data pegawai baru ke dalam sistem.' }}
            </flux:description>

            <div class="mt-6 space-y-6">
                <!-- Data Pegawai -->
                <div>
                    <flux:heading size="sm" class="mb-3 px-1">Informasi Dasar</flux:heading>
                    <div
                        class="bg-white dark:bg-white/5 rounded-3xl border border-zinc-200 dark:border-white/10 shadow-xs flex flex-col relative overflow-hidden">

                        <!-- NIK -->
                        <label
                            class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                            <span
                                class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                                NIK
                            </span>
                            <div class="flex-1">
                                <input type="text" wire:model="employee_id_number" placeholder="Masukkan NIK..."
                                    class="w-full bg-transparent border-none outline-none focus:ring-0 text-[15px] text-zinc-700 dark:text-zinc-300 placeholder-zinc-400 px-0 py-2 text-right" />
                            </div>
                            <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                        </label>
                        <x-error-ios name="employee_id_number" />

                        <!-- Nama -->
                        <label
                            class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                            <span
                                class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                                Nama
                            </span>
                            <div class="flex-1">
                                <input type="text" wire:model="name" placeholder="Masukkan nama..."
                                    class="w-full bg-transparent border-none outline-none focus:ring-0 text-[15px] text-zinc-700 dark:text-zinc-300 placeholder-zinc-400 px-0 py-2 text-right" />
                            </div>
                            <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                        </label>
                        <x-error-ios name="name" />

                        <!-- No. HP -->
                        <label
                            class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                            <span
                                class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                                No. HP
                            </span>
                            <div class="flex-1">
                                <input type="text" wire:model="phone_number" placeholder="08..."
                                    class="w-full bg-transparent border-none outline-none focus:ring-0 text-[15px] text-zinc-700 dark:text-zinc-300 placeholder-zinc-400 px-0 py-2 text-right" />
                            </div>
                            <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                        </label>
                        <x-error-ios name="phone_number" />

                        <!-- Posisi -->
                        <label
                            class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                            <span
                                class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                                Posisi
                            </span>
                            <div class="flex-1">
                                <input type="text" wire:model="position" placeholder="Jabatan..."
                                    class="w-full bg-transparent border-none outline-none focus:ring-0 text-[15px] text-zinc-700 dark:text-zinc-300 placeholder-zinc-400 px-0 py-2 text-right" />
                            </div>
                            <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                        </label>
                        <x-error-ios name="position" />

                        <!-- Status -->
                        <label
                            class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                            <span
                                class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                                Status
                            </span>
                            <div class="flex-1 flex justify-end">
                                <x-switch wire:model="status" />
                            </div>
                        </label>
                        <x-error-ios name="status" />
                    </div>


                </div>

                <!-- Integrasi Akun Sistem -->
                <div>
                    <flux:heading size="sm" class="mb-3 px-1">Akun Sistem</flux:heading>
                    <div
                        class="bg-white dark:bg-white/5 rounded-3xl border border-zinc-200 dark:border-white/10 shadow-xs flex flex-col relative overflow-hidden">

                        <!-- Toggle Buat Akun -->
                        <label
                            class="flex flex-row items-center px-4 py-2.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                            <span
                                class="text-[15px] font-medium text-zinc-900 dark:text-white flex-1 select-none flex flex-col">
                                <span>Buat Akun Sistem</span>
                                <span class="text-xs text-zinc-500 font-normal">Izinkan pegawai ini untuk login ke
                                    aplikasi</span>
                            </span>
                            <div class="shrink-0 ml-4">
                                <x-switch wire:model.live="create_user" />
                            </div>
                            @if ($create_user)
                                <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                            @endif
                        </label>
                        <x-error-ios name="create_user" />

                        <!-- User Details Form (only visible if create_user is true) -->
                        @if ($create_user)
                            <!-- Email -->
                            <label
                                class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                                <span
                                    class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                                    Email
                                </span>
                                <div class="flex-1">
                                    <input type="email" wire:model="email" placeholder="Email untuk login..."
                                        class="w-full bg-transparent border-none outline-none focus:ring-0 text-[15px] text-zinc-700 dark:text-zinc-300 placeholder-zinc-400 px-0 py-2 text-right" />
                                </div>
                                <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                            </label>
                            <x-error-ios name="email" />

                            <!-- Password -->
                            <label
                                class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                                <span
                                    class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none flex flex-col">
                                    <span>Password</span>
                                    @if ($has_user)
                                        <span class="text-[10px] text-zinc-500 font-normal leading-tight">Kosongkan
                                            jika<br>tidak ingin diubah</span>
                                    @endif
                                </span>
                                <div class="flex-1">
                                    <input type="password" wire:model="password" placeholder="Minimal 8 karakter..."
                                        class="w-full bg-transparent border-none outline-none focus:ring-0 text-[15px] text-zinc-700 dark:text-zinc-300 placeholder-zinc-400 px-0 py-2 text-right" />
                                </div>
                                <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                            </label>
                            <x-error-ios name="password" />

                            <!-- Role -->
                            <label
                                class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                                <span
                                    class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                                    Role
                                </span>
                                <div class="flex-1">
                                    <x-searchable-select wire:model="role" :options="$this->roles" variant="ios"
                                        placeholder="Pilih hak akses..." />
                                </div>
                            </label>
                            <x-error-ios name="role" />
                        @endif
                    </div>


                </div>
            </div>

            <div class="mt-8 flex flex-col gap-2">
                <flux:button type="submit" variant="primary" class="!rounded-full">Simpan Pegawai</flux:button>
                <flux:button x-on:click="$flux.modal('create-employee-modal').close()" variant="outline" class="!rounded-full">
                    Batal
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
