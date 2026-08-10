<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <flux:heading size="xl">Peran</flux:heading>
            <flux:subheading>Manage system roles and permissions.</flux:subheading>
        </div>
        @can('roles:create')
            <flux:button wire:click="addRole" variant="primary" icon="plus" class="w-full sm:w-auto">Tambah Peran
            </flux:button>
        @endcan
    </div>

    <div class="rounded-2xl bg-zinc-50 dark:bg-white/5 border border-zinc-200 dark:border-white/5 overflow-hidden">
        <div class="overflow-x-auto">
            <flux:table
                class="[&_th:first-child]:!ps-6 [&_td:first-child]:!ps-6 [&_th:last-child]:!pe-6 [&_td:last-child]:!pe-6">
                <flux:table.columns>
                    <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection"
                        wire:click="sort('name')">Nama</flux:table.column>
                    @canany(['roles:update', 'roles:delete'])
                        <flux:table.column>Aksi</flux:table.column>
                    @endcanany
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($this->roles as $role)
                        <flux:table.row :key="$role->id">
                            <flux:table.cell>{{ $role->name }}</flux:table.cell>

                            @canany(['roles:update', 'roles:delete'])
                                <flux:table.cell>
                                    <flux:dropdown>
                                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                        <flux:menu>
                                            @can('roles:update')
                                                <flux:menu.item wire:click="editRole({{ $role->id }})" icon="pencil-square">
                                                    Edit</flux:menu.item>
                                            @endcan
                                            @can('roles:delete')
                                                <x-delete-modal id="delete-role-{{ $role->id }}"
                                                    action="deleteRole({{ $role->id }})" requireSlide="true"
                                                    title="Delete role?"
                                                    description="You are about to delete this role. This action cannot be reversed." />
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
                                    <flux:text class="mt-2 mb-4 text-sm text-zinc-500 dark:text-zinc-400">Mulai dengan
                                        menambahkan data baru ke dalam sistem.</flux:text>
                                    <div class="mt-2">
                                        @can('roles:create')
                                            <flux:button wire:click="addRole" variant="primary" icon="plus">Tambah Peran
                                            </flux:button>
                                        @endcan
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>

        @if ($this->roles->total() > 0)
            <flux:pagination :paginator="$this->roles" class="p-4" />
        @endif
    </div>

    <!-- Create/Edit Modal -->
    <flux:modal :closable="false" scroll="body" name="create-role-modal" class="md:max-w-4xl !rounded-3xl">
        <form wire:submit="save">
            <flux:heading>{{ $editingRoleId ? 'Edit Peran' : 'Tambah Peran' }}</flux:heading>
            <flux:description>
                {{ $editingRoleId ? 'Perbarui informasi peran di bawah ini.' : 'Buat peran baru dan tetapkan hak akses (permissions) ke berbagai modul.' }}
            </flux:description>

            <div class="mt-6 space-y-6">
                <!-- Data Peran -->
                <div>
                    <flux:heading size="sm" class="mb-3 px-1">Informasi Dasar</flux:heading>
                    <div
                        class="bg-white dark:bg-white/5 rounded-3xl border border-zinc-200 dark:border-white/10 shadow-xs flex flex-col relative overflow-hidden">

                        <!-- Nama Peran -->
                        <label
                            class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-pointer">
                            <span
                                class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                                Nama Peran
                            </span>
                            <div class="flex-1">
                                <input type="text" wire:model="name" placeholder="Misal: admin, kasir, manager..."
                                    class="w-full bg-transparent border-none outline-none focus:ring-0 text-[15px] text-zinc-700 dark:text-zinc-300 placeholder-zinc-400 px-0 py-2 text-right" />
                            </div>
                        </label>
                        <x-error-ios name="name" />
                    </div>

                </div>

                <!-- Permissions -->
                <div>
                    <flux:heading size="sm" class="mb-3 px-1">Atur Hak Akses (Permissions)</flux:heading>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($this->modules as $moduleName => $permissions)
                            @php
                                $permNames = collect($permissions)->pluck('name')->toArray();
                                $isAllSelected =
                                    count(array_intersect($permNames, $selectedPermissions)) === count($permNames) &&
                                    count($permNames) > 0;
                            @endphp

                            <flux:card wire:key="module-{{ $moduleName }}"
                                class="flex flex-col h-full !rounded-3xl border border-zinc-200 dark:border-white/10 shadow-xs">
                                <div
                                    class="flex items-center justify-between mb-4 border-b border-zinc-200 dark:border-zinc-700 pb-2">
                                    <div class="flex items-center gap-2">
                                        <flux:icon.server-stack class="size-4 text-zinc-500" />
                                        <span class="font-semibold text-sm tracking-wide">{{ $moduleName }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-zinc-500">All</span>
                                        <x-switch wire:click="toggleAll('{{ $moduleName }}')" :checked="$isAllSelected"
                                            size="sm" />
                                    </div>
                                </div>

                                <div class="flex flex-col space-y-3 flex-grow">
                                    @foreach ($permissions as $perm)
                                        @php
                                            $action = explode(':', $perm->name)[1] ?? $perm->name;
                                        @endphp
                                        <x-checkbox wire:key="perm-{{ $perm->id }}" wire:model="selectedPermissions"
                                            value="{{ $perm->name }}" label="{{ Str::headline($action) }}" />
                                    @endforeach
                                </div>
                            </flux:card>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mt-8 flex flex-col gap-2">
                <flux:button type="submit" variant="primary" class="!rounded-full">Simpan Peran</flux:button>
                <flux:button x-on:click="$flux.modal('create-role-modal').close()" variant="outline" class="!rounded-full">Batal
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
