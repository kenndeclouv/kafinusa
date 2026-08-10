<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <flux:heading size="xl">Pengguna</flux:heading>
            <flux:subheading>Kelola pengguna sistem dan perannya di sini.</flux:subheading>
        </div>
        <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto mt-4 sm:mt-0">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari nama, email..." icon="magnifying-glass"
                class="w-full sm:w-64" />
            @can('users:create')
                <flux:button wire:click="addUser" variant="primary" icon="plus" class="ms-auto w-full sm:w-auto">Tambah
                    Pengguna</flux:button>
            @endcan
        </div>
    </div>

    <div class="rounded-2xl bg-zinc-50 dark:bg-white/5 border border-zinc-200 dark:border-white/5 overflow-hidden">
        <div class="overflow-x-auto">
            <flux:table
                class="[&_th:first-child]:!ps-6 [&_td:first-child]:!ps-6 [&_th:last-child]:!pe-6 [&_td:last-child]:!pe-6">
                <flux:table.columns>
                    <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection"
                        wire:click="sort('name')">Nama</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'email'" :direction="$sortDirection"
                        wire:click="sort('email')">Email</flux:table.column>
                    <flux:table.column>Peran (Role)</flux:table.column>
                    @canany(['users:update', 'users:delete'])
                        <flux:table.column>Aksi</flux:table.column>
                    @endcanany
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($this->users as $user)
                        <flux:table.row :key="$user->id">
                            <flux:table.cell>
                                <div class="flex items-center gap-3">
                                    <flux:avatar size="sm" :name="$user->name" />
                                    <span>{{ $user->name }}</span>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>{{ $user->email }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex flex-wrap gap-1">
                                    @forelse ($user->roles as $role)
                                        <flux:badge size="sm" color="zinc" class="flex items-center gap-1">
                                            {{ Str::headline($role->name) }}
                                        </flux:badge>
                                    @empty
                                        <span class="text-zinc-400 text-sm italic">Belum ada peran</span>
                                    @endforelse
                                </div>
                            </flux:table.cell>

                            @canany(['users:update', 'users:delete'])
                                <flux:table.cell>
                                    <flux:dropdown>
                                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                        <flux:menu>
                                            @can('users:update')
                                                <flux:menu.item wire:click="editUser({{ $user->id }})" icon="pencil-square">
                                                    Edit</flux:menu.item>
                                            @endcan
                                            @can('users:delete')
                                                <x-delete-modal id="delete-user-{{ $user->id }}"
                                                    action="deleteUser({{ $user->id }})" requireSlide="true"
                                                    title="Hapus Pengguna?"
                                                    description="Anda yakin ingin menghapus pengguna ini? Tindakan ini tidak dapat dibatalkan." />
                                            @endcan
                                        </flux:menu>
                                    </flux:dropdown>
                                </flux:table.cell>
                            @endcanany
                        </flux:table.row>
                    @empty
                        <tr>
                            <td colspan="100%" class="py-12">
                                <div class="flex flex-col items-center justify-center text-center">
                                    <div class="rounded-full bg-zinc-100 dark:bg-zinc-800 p-3 mb-4">
                                        <flux:icon.folder-open class="w-6 h-6 text-zinc-500 dark:text-zinc-400" />
                                    </div>
                                    <flux:heading size="lg">Belum ada data</flux:heading>
                                    <flux:text class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Mulai dengan
                                        menambahkan pegawai baru yang memiliki akun sistem.</flux:text>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>

        @if ($this->users->total() > 0)
            <flux:pagination :paginator="$this->users" class="p-4" />
        @endif
    </div>

    <!-- Create/Edit Modal -->
    <flux:modal :closable="false" scroll="body" name="create-user-modal" class="md:max-w-2xl !rounded-3xl">
        <form wire:submit="save">
            <flux:heading>{{ $editingUserId ? 'Edit Pengguna' : 'Tambah Pengguna' }}</flux:heading>
            <flux:description>
                {{ $editingUserId ? 'Perbarui informasi pengguna di bawah ini.' : 'Tambahkan pengguna baru ke dalam sistem.' }}
            </flux:description>

            <div class="mt-6 space-y-6">
                <div>
                    <flux:heading size="sm" class="mb-3 px-1">Informasi Akun</flux:heading>
                    <div
                        class="bg-white dark:bg-white/5 rounded-3xl border border-zinc-200 dark:border-white/10 shadow-xs flex flex-col relative overflow-hidden">

                        <!-- Nama -->
                        <label
                            class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                            <span
                                class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                                Nama Lengkap
                            </span>
                            <div class="flex-1">
                                <input type="text" wire:model="name" placeholder="Masukkan nama..."
                                    class="w-full bg-transparent border-none outline-none focus:ring-0 text-[15px] text-zinc-700 dark:text-zinc-300 placeholder-zinc-400 px-0 py-2 text-right" />
                            </div>
                            <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                        </label>
                        <x-error-ios name="name" />

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
                                @if ($editingUserId)
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

                        <!-- Peran (Role) -->
                        <label
                            class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                            <span
                                class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                                Peran (Role)
                            </span>
                            <div class="flex-1">
                                <x-searchable-select wire:model="roles" :options="$this->roleOptions" multiple variant="ios"
                                    placeholder="Pilih peran..." />
                            </div>
                        </label>
                        <x-error-ios name="roles" />
                    </div>


                </div>
            </div>

            <div class="mt-8 flex flex-col gap-2">
                <flux:button type="submit" variant="primary" class="!rounded-full">Simpan Pengguna</flux:button>
                <flux:button x-on:click="$flux.modal('create-user-modal').close()" variant="outline" class="!rounded-full">Batal
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
