<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Content;
use Illuminate\Support\Facades\Log;

new #[Layout('components.layouts.classroom-learn')] class extends Component {
    public $idClassroom;
    public $idTask;
    public $datas;
    public function mount($id, $task)
    {
        $this->idClassroom = $id;
        $this->idTask = $task;
    }

    public function loadContent($id)
    {
        try {
            $this->datas = Content::find($id)->toArray();
            return [
                'status' =>true
            ];
        } catch (\Throwable $th) {
            Log::error('ClassroomLearn Eroor Load Content ' . $th);
            return [
                'status' => false,
            ];
        }
    }
}; ?>

<flux:main class="relative bg-white">
    <flux:sidebar.toggle
        class="text-gray-500! cursor-pointer border transition-all hover:border-gray-400/50 hover:shadow-md lg:hidden"
        icon="bars-2" inset="left" />
    <div x-data="classTask" x-init="firstInit">
        <template x-if="loading && !datas">
            <div class="animate-pulse w-full">
                <div class="mb-4 h-10 w-full rounded-full bg-gray-200 dark:bg-gray-700"></div>
                <div class="mb-4 ml-[10%] h-5 w-[75%] rounded-full bg-gray-200 dark:bg-gray-700"></div>
                <div class="mb-4 h-5 w-full rounded-full bg-gray-200 dark:bg-gray-700"></div>
                <div class="mb-4 h-5 w-full rounded-full bg-gray-200 dark:bg-gray-700"></div>
                <div class="mb-4 h-5 w-full rounded-full bg-gray-200 dark:bg-gray-700"></div>
                <div class="mb-4 h-5 w-full rounded-full bg-gray-200 dark:bg-gray-700"></div>
                <div class="mb-4 h-5 w-full rounded-full bg-gray-200 dark:bg-gray-700"></div>
                <div class="mb-4 h-5 w-full rounded-full bg-gray-200 dark:bg-gray-700"></div>
            </div>
        </template>
        <template x-if="!loading">
            <div>
                <h1 x-text="datas.title" class="text-secondary_blue"></h1>
                <h3>Kontol</h3>
            </div>
        </template>
    </div>
</flux:main>
<script>
    function classTask() {
        return {
            idClassroom: @entangle('idClassroom'),
            idTask: @entangle('idTask'),
            datas: @entangle('datas').live,
            loading: true,
            async firstInit() {
                const data = await this.$wire.loadContent(this.idTask);
                if (data.status) {
                    this.loading = false;
                } else {
                    this.$dispatch('failed', [{
                        message: 'Gagal memuat konten'
                    }]);
                }
            }
        }
    }
</script>
