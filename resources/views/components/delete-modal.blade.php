@props([
    'action',
    'title' => 'Hapus item?',
    'description' => 'Anda akan menghapus item ini. Tindakan ini tidak dapat dibatalkan.',
    'buttonText' => 'Hapus',
    'id',
    'requireSlide' => false,
])

<flux:modal.trigger name="{{ $id }}">
    @if ($slot->isEmpty())
        <flux:menu.item icon="trash" variant="danger">Delete</flux:menu.item>
    @else
        {{ $slot }}
    @endif
</flux:modal.trigger>

@teleport('body')
    <flux:modal :closable="false" scroll="body" name="{{ $id }}" class="md:w-96 !rounded-3xl">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $title }}</flux:heading>

                <flux:text class="mt-2">
                    {{ $description }}
                </flux:text>
            </div>

            <div class="mt-6 flex flex-col gap-2" x-data="{
                startX: 0,
                currentX: 0,
                maxSlide: 0,
                sliding: false,
                success: false,
                needsSlide: {{ $requireSlide ? 'true' : 'false' }},
            
                startSlide(e) {
                    if (!this.needsSlide || this.success) return;
                    this.sliding = true;
                    this.startX = e.type.includes('mouse') ? e.clientX : e.touches[0].clientX;
                    this.maxSlide = this.$refs.track.clientWidth - this.$refs.handle.clientWidth - 8;
                },
            
                moveSlide(e) {
                    if (!this.sliding) return;
                    let clientX = e.type.includes('mouse') ? e.clientX : e.touches[0].clientX;
                    let delta = clientX - this.startX;
                    this.currentX = Math.max(0, Math.min(delta, this.maxSlide));
            
                    if (this.currentX >= this.maxSlide) {
                        this.success = true;
                        this.sliding = false;
                        this.currentX = this.maxSlide;
                        this.execute();
                    }
                },
            
                stopSlide() {
                    if (!this.sliding) return;
                    this.sliding = false;
                    if (!this.success) {
                        this.currentX = 0;
                    }
                },
            
                execute() {
                    this.$refs.actionBtn.click();
                }
            }">
                <!-- Hidden button to trigger livewire action -->
                <button x-ref="actionBtn" wire:click="{{ $action }}" type="button" class="hidden"></button>

                @if ($requireSlide)
                    <div class="relative w-full h-10 bg-red-50 dark:bg-red-500/10 rounded-full overflow-hidden flex items-center justify-center border border-red-200 dark:border-red-500/20 select-none touch-none"
                        x-ref="track">
                        <span class="text-sm font-medium text-red-600 dark:text-red-400 relative z-10 pointer-events-none"
                            x-text="success ? 'Sedang menghapus...' : 'Geser untuk menghapus'">
                        </span>

                        <!-- Red filling background behind the handle -->
                        <div class="absolute inset-y-0 left-0 bg-red-200 dark:bg-red-500/30 z-0 rounded-full"
                            :class="{ 'transition-none': sliding, 'transition-all duration-300 ease-out': !sliding }"
                            :style="'width: ' + (currentX + 40) + 'px'"></div>

                        <!-- The Draggable Handle -->
                        <div class="absolute left-1 top-1 bottom-1 w-8 rounded-full bg-red-600 shadow flex items-center justify-center cursor-grab z-10"
                            :class="{
                                'cursor-grabbing': sliding,
                                'transition-none': sliding,
                                'transition-transform duration-300 ease-out':
                                    !sliding
                            }"
                            :style="'transform: translateX(' + currentX + 'px)'" @mousedown="startSlide"
                            @touchstart.prevent="startSlide" @mousemove.window="moveSlide"
                            @touchmove.window.prevent="moveSlide" @mouseup.window="stopSlide" @touchend.window="stopSlide"
                            x-ref="handle">
                            <flux:icon.chevron-right class="w-4 h-4 text-white" x-show="!success" />
                            <flux:icon.check class="w-4 h-4 text-white" x-show="success" x-cloak />
                        </div>
                    </div>
                @else
                    <flux:button wire:click="{{ $action }}" variant="danger" class="!rounded-full w-full">
                        {{ $buttonText }}
                    </flux:button>
                @endif

                <flux:modal.close class="w-full">
                    <flux:button variant="outline" class="!rounded-full w-full" x-bind:disabled="success">Batal
                    </flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
@endteleport
