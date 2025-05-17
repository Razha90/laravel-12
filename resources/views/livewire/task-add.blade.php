<?php

use Livewire\Volt\Component;
use App\Models\Content;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.app-task')] class extends Component {
    public $id;
    public $task;
    public $content = [];
    public $isLoading = false;
    protected $listeners = ['savedContent', 'savedPublishing'];
    public $model1;

    public function mount($id, $task)
    {
        $this->id = $id;
        $this->task = $task;
        $this->loadData();
    }

    private function loadData()
    {
        try {
            $data = Content::where('id', $this->task)->get();
            $this->content = $data->toArray();
        } catch (\Throwable $th) {
            $this->dispatch('failed', ['message' => __('add-task.not_found')]);
            Log::error('Load data task, in Add Task' . $th);
        } finally {
            $this->isLoading = true;
        }
    }

    public function savedContent($data)
    {
        try {
            Content::where('id', $this->task)->update([
                'title' => $data['title'],
                'content' => $data['content'],
                'type' => $data['type'],
            ]);
            $this->dispatch('success', ['message' => __('add-task.succes_saved')]);
        } catch (\Throwable $th) {
            $this->dispatch('failed', ['message' => __('add-task.failed_saved')]);
            Log::error('Update data task, in Add Task' . $th);
        } finally {
            $this->dispatch('savedSuccess');
        }
    }

    public function savedPublishing($data)
    {
        try {
            $date = $data['date'];
            $time = $data['time'];
            $schedule = $data['schedule'];
            $deadline = $data['deadline'];
            $isDeadline = $data['isDeadline'];
            $canUpload = $data['canUpload'];
            $type = $data['type'];

            if ($schedule) {
                $validator = Validator::make(
                    $data,
                    [
                        'date' => 'required|date_format:Y-m-d',
                        'time' => 'required|date_format:H:i',
                        'type' => 'required|string',
                    ],
                    [
                        'date.required' => __('add-task.date_required'),
                        'time.required' => __('add-task.time_required'),
                        'date.date_format' => __('add-task.date_required'),
                        'time.date_format' => __('add-task.time_required'),
                        'type.required' => __('add-task.type_required'),
                        'type.string' => __('add-task.type_string'),
                    ],
                );

                if ($validator->fails()) {
                    $this->dispatch('failed', ['message' => $validator->errors()->first()]);
                    Log::error('Validation data task, in Add Task' . $validator->errors()->first());
                    return;
                }
                $release = $date . ' ' . $time . ':00';
            } else {
                $release = $this->content[0]['release'];
                if (empty($release)) {
                    $release = now()->format('Y-m-d H:i:s');
                }
            }

            if ($type == 'task') {
                $validator = Validator::make(
                    $data,
                    [
                        'canUpload' => 'required|boolean',
                        'isDeadline' => 'required|boolean',
                    ],
                    [
                        'canUpload.required' => __('add-task.can_upload_required'),
                        'isDeadline.required' => __('add-task.is_deadline_required'),
                        'canUpload.boolean' => __('add-task.can_upload_boolean'),
                        'isDeadline.boolean' => __('add-task.is_deadline_boolean'),
                    ],
                );

                if ($validator->fails()) {
                    $this->dispatch('failed', ['message' => $validator->errors()->first()]);
                    Log::error('Validation data task, in Add Task' . $validator->errors()->first());
                    return;
                }

                if ($isDeadline) {
                    $validator = Validator::make(
                        $data,
                        [
                            'deadline' => 'required|date_format:Y-m-d H:i',
                        ],
                        [
                            'deadline.required' => __('add-task.deadline_required'),
                            'deadline.date_format' => __('add-task.deadline_format'),
                        ],
                    );

                    if ($validator->fails()) {
                        $this->dispatch('failed', ['message' => $validator->errors()->first()]);
                        Log::error('Validation data task, in Add Task' . $validator->errors()->first());
                        return;
                    }
                }

                if (!empty($deadline)) {
                    $deadline = $deadline . ':00';
                } else {
                    $deadline = null;
                }

                $maxOrder = Content::max('order');

                Content::where('id', $this->task)->update([
                    'visibility' => true,
                    'release' => $release,
                    'deadline' => $deadline,
                    'canUpload' => $canUpload,
                    'isDeadline' => $isDeadline,
                    'order' => ($maxOrder ?? 0) + 1,
                ]);
                $this->dispatch('success', ['message' => __('add-task.succes_published')]);
                $this->dispatch('initRelease', ['STATUS' => 'PUBLISH', 'DATE' => $release]);
                return;
            }
            $maxOrder = Content::max('order');
            Content::where('id', $this->task)->update([
                'visibility' => true,
                'release' => $release,
                'order' => ($maxOrder ?? 0) + 1,
            ]);
            $this->dispatch('success', ['message' => __('add-task.succes_published')]);
            $this->dispatch('initRelease', ['STATUS' => 'PUBLISH', 'DATE' => $release]);
        } catch (\Throwable $th) {
            $this->dispatch('failed', ['message' => __('add-task.failed_published')]);
            Log::error('Update data task, in Add Task' . $th);
        }
    }

    public function savedDraft()
    {
        try {
            Content::where('id', $this->task)->update([
                'visibility' => false,
                'release' => null,
                'order' => null,
                'deadline' => null,
                'isDeadline' => false,
            ]);
            $this->dispatch('success', ['message' => __('add-task.succes_saved')]);
            $this->dispatch('initRelease', ['STATUS' => 'DRAFT']);
            return [
                'status' => true,
            ];
        } catch (\Throwable $th) {
            $this->dispatch('failed', ['message' => __('add-task.failed_saved')]);
            Log::error('Update data task, in Add Task' . $th);
            return [
                'status' => false,
            ];
        }
    }
}; ?>

<div>
    @vite(['resources/js/editor.js'])

    <header class="bg-secondary_blue fixed z-30 min-h-[70px] w-full px-[10%] py-3">
        <nav class="flex h-full w-full max-w-[1500px] flex-row items-center justify-between">
            <div>
                <button class="text-primary_white flex flex-row items-center gap-x-2 text-2xl font-bold"
                    @click="if (document.referrer.startsWith(window.location.origin)) {
                                history.back();
                            } else {
                                window.location.href = '{{ route('classroom-learn', ['id' => $id]) }}';
                            }
                        ">
                    <svg class="h-9 w-9" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="#F7F7F7">
                        <polyline fill="none" points="7.6 7 2.5 12 7.6 17" stroke="#F7F7F7" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2"></polyline>
                        <line fill="none" stroke="#F7F7F7" stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="2" x1="21.5" x2="4.8" y1="12" y2="12"></line>
                    </svg>
                    <p>{{ __('add-class.back') }}</p>
                </button>
            </div>
            <div class="flex flex-row items-center gap-x-4">

                <a wire:navigate href="{{ route('settings.profile') }}"
                    class="border-secondary_blue h-[45px] w-[45px] overflow-hidden rounded-full border p-1 transition-opacity hover:opacity-50"
                    x-data="{ isClicked: false }"
                    @click.prevent="if (!isClicked) { isClicked = true; window.location.href = '{{ route('settings.profile') }}'; }"
                    :class="{ 'pointer-events-none opacity-50': isClicked }">
                    <img loading="lazy" src="{{ asset(auth()->user()->profile_photo_path) }}" alt="Profile Photo">
                </a>

            </div>
        </nav>
    </header>

    <main x-data="cmsContent()" x-init="init()" class="relative h-[92vh] min-h-[500px] w-full min-w-[500px]">
        <div x-cloak="" x-data="{ alert: false, message: '' }"
            x-on:show-failed.window="(event) => { 
        message = event.detail.message;
        alert = true;
        setTimeout(() => alert = false, 5000);
    }"
            x-show="alert" x-transition
            class="animate-fade-up absolute bottom-0 left-5 z-30 flex flex-row items-start rounded-lg bg-gray-800 p-4 text-sm text-red-500"
            role="alert">

            <svg class="me-3 inline h-4 w-4 shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                fill="currentColor" viewBox="0 0 20 20">
                <path
                    d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
            </svg>
            <span class="sr-only">{{ __('class-learn.info') }}</span>
            <div>
                <span class="font-medium" x-text="message"></span>
            </div>
        </div>

        <!-- Main modal -->
        <div x-cloak x-data="{ alert: false, message: '', condition: '', warning: '' }" x-show="alert"
            x-on:show-modal-failed.window="(event) => { 
        alert = true;
        message = event.detail.message;
        condition = event.detail.condition;
        warning = event.detail.warning;

    }"
            aria-hidden="true"
            class="fixed z-50 flex h-[100%] max-h-full w-full items-center justify-center overflow-y-auto overflow-x-hidden bg-black/40 md:inset-0">
            <div class="relative max-h-full w-full max-w-2xl p-4">
                <!-- Modal content -->
                <div class="relative rounded-lg bg-white shadow-sm">
                    <!-- Modal header -->
                    <div
                        class="flex items-center justify-between rounded-t border-b border-gray-200 p-4 md:p-5 dark:border-gray-600">
                        <h3 class="text-xl font-semibold text-gray-900" x-text="warning">

                        </h3>
                        <button type="button" @click="() => {alert = false;}"
                            class="ms-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-transparent text-sm text-gray-400 hover:bg-gray-200 hover:text-gray-900"
                            data-modal-hide="default-modal">
                            <svg class="h-3 w-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                            </svg>
                            <span class="sr-only">Close modal</span>
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="space-y-4 p-4 md:p-5">
                        <p class="text-base leading-relaxed text-gray-500" x-text="message">
                        </p>
                    </div>
                    <!-- Modal footer -->
                    <div class="flex items-center rounded-b border-t border-gray-200 p-4 md:p-5">
                        <button data-modal-hide="default-modal" type="button" x-show="condition == 'SAVE'"
                            class="rounded-lg bg-blue-700 px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300"
                            @click="() => {clickSave(); rememberSave = false; alert=false;}">{{ __('add-task.button_ok') }}</button>
                        <button data-modal-hide="default-modal" type="button" x-show="condition == 'SAVE'"
                            class="ms-3 rounded-lg border border-red-400 bg-white px-5 py-2.5 text-sm font-medium text-red-500 hover:bg-red-500 hover:text-white focus:z-10 focus:outline-none focus:ring-4 focus:ring-red-400"
                            @click="() => {alert = false; rememberSave=true}">{{ __('add-task.button_cancel') }}</button>
                        <button data-modal-hide="default-modal" type="button" x-show="condition == 'TITLE'"
                            class="rounded-lg bg-blue-700 px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300"
                            @click="() => {alert = false;}">{{ __('add-task.button_ready') }}</button>

                    </div>
                </div>
            </div>
        </div>

        <!-- Title Zero Modal -->
        <div x-cloak x-data="{ alert: false }" x-show="alert" x-on:show-modal-="(event) => { 
        alert = true;
    }"
            aria-hidden="true"
            class="fixed z-50 flex h-[100%] max-h-full w-full items-center justify-center overflow-y-auto overflow-x-hidden bg-black/40 md:inset-0">
            <div class="relative max-h-full w-full max-w-2xl p-4">
                <!-- Modal content -->
                <div class="relative rounded-lg bg-white shadow-sm">
                    <!-- Modal header -->
                    <div
                        class="flex items-center justify-between rounded-t border-b border-gray-200 p-4 md:p-5 dark:border-gray-600">
                        <h3 class="text-xl font-semibold text-gray-900">
                            {{ __('add-task.warning_title_saved') }}
                        </h3>
                        <button type="button" @click="() => {alert = false; rememberSave = false}"
                            class="ms-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-transparent text-sm text-gray-400 hover:bg-gray-200 hover:text-gray-900"
                            data-modal-hide="default-modal">
                            <svg class="h-3 w-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                            </svg>
                            <span class="sr-only">Close modal</span>
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="space-y-4 p-4 md:p-5">
                        <p class="text-base leading-relaxed text-gray-500">
                            {{ __('add-task.warning_saved') }}
                        </p>
                    </div>
                    <!-- Modal footer -->
                    <div class="flex items-center rounded-b border-t border-gray-200 p-4 md:p-5">
                        <button data-modal-hide="default-modal" type="button"
                            class="rounded-lg bg-blue-700 px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300"
                            @click="() => {clickSave(); rememberSave = false; alert=false;}">{{ __('add-task.button_ok') }}</button>
                        <button data-modal-hide="default-modal" type="button"
                            class="ms-3 rounded-lg border border-red-400 bg-white px-5 py-2.5 text-sm font-medium text-red-500 hover:bg-red-500 hover:text-white focus:z-10 focus:outline-none focus:ring-4 focus:ring-red-400"
                            @click="() => {alert = false}">{{ __('add-task.button_cancel') }}</button>
                    </div>
                </div>
            </div>
        </div>

        <template x-if="isLoading && content.length > 0">
            <div class="fixed mt-[70px] h-full w-1/4 rounded-lg bg-gray-100 p-4 shadow-xl">
                <div class="flex w-full justify-end">
                    <button x-bind:disabled="saveNew" @click="clickSave"
                        :class="{
                            'opacity-50 cursor-not-allowed border-gray-400 bg-gray-300 text-gray-500': (saveNew || !
                                saved),
                            'border-white outline outline-secondary_blue bg-secondary_blue text-white': !(saveNew || !
                                saved)
                        }"
                        class="border px-4 py-1 text-xl transition-all"
                        x-text="(saveNew || !
                                saved) ? saved ? '{{ __('add-task.saved') }}': '{{ __('add-task.saving') }}' : '{{ __('add-task.save') }}....' ">
                    </button>
                </div>
                <div class="mt-4 flex flex-col">
                    <h2 class="font-koho mb-2 text-xl font-bold">{{ __('add-task.choose_content_type') }}</h2>
                    <div x-data="{ open: false }" x-cloak class="relative w-full">
                        <select x-model="type"
                            class="border-secondary_blue focus:ring-secondary_blue w-full appearance-none rounded border bg-white p-2 pr-8 focus:outline-none focus:ring-2"
                            @focus="open = true" @blur="open = false"
                            @change="() => {open = false; saveNew = false; checkChangeDeadline();}">
                            <option value="task">{{ __('add-class.task') }}</option>
                            <option value="notification">{{ __('add-class.notification') }}</option>
                            <option value="material">{{ __('add-task.material') }}</option>
                        </select>
                        <div
                            class="text-secondary_blue pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 transform">
                            <span x-show="!open">▼</span>
                            <span x-show="open">...</span>
                        </div>
                    </div>



                </div>

                <!-- Deadline -->
                <div x-show="type == 'task'" class="bg-accent_grey animate-fade mt-3 rounded-2xl p-3">
                    <h2 class="font-koho text-xl font-bold">{{ __('add-task.deadline') }}</h2>
                    <div class="flex flex-row items-center gap-x-2">
                        <input type="checkbox" x-model="borderDeadline" name="border-deadline"
                            @change="checkChangeDeadline">
                        <label class="h-5 text-base opacity-70"
                            for="border-deadline">{{ __('add-task.deadline_title') }}</label>
                        <div x-data="{ showTooltip: false }" class="relative ml-2 inline-block h-5">
                            <span @mouseenter="showTooltip = true" @mouseleave="showTooltip = false"
                                class="cursor-pointer text-blue-500">
                                ℹ️
                            </span>
                            <div x-show="showTooltip" x-transition
                                class="absolute left-0 top-full mt-1 w-48 rounded-md bg-gray-800 p-2 text-xs text-white shadow-lg">
                                {{ __('add-task.deadline_title_detail') }}
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <input type="date" x-model="deadline" name="deadline" x-bind:min="today"
                            :disabled="!borderDeadline" :class="!borderDeadline ? 'opacity-50 cursor-not-allowed' : ''"
                            class="rounded border bg-white p-2 transition-all"
                            @change="() => {validateDeadlineTime();checkChangeDeadline()}">

                        <input type="time" x-model="deadlineTime" name="deadlineTime"
                            x-bind:min="isToday ? currentTime : '00:00'" :disabled="!borderDeadline"
                            :class="!borderDeadline ? 'opacity-50 cursor-not-allowed' : ''"
                            class="rounded border bg-white p-2 transition-all"
                            @change="() => {validateDeadlineTime();checkChangeDeadline()}">

                        <p x-show="deadlineError" class="mt-1 text-sm text-red-500">
                            {{ __('add-task.deadline_timeout') }}
                        </p>
                    </div>
                    <div class="mt-3 flex flex-row items-center gap-x-2">
                        <input type="checkbox" x-model="canUpload" name="canUpload" :disabled="!borderDeadline"
                            :class="!borderDeadline ? 'opacity-50 cursor-not-allowed' : ''"
                            @change="checkChangeDeadline">

                        <label class="h-5 text-base opacity-70"
                            for="canUpload">{{ __('add-task.can_upload') }}</label>
                        <div x-data="{ showTooltip: false }" class="relative ml-2 inline-block h-5">
                            <span @mouseenter="showTooltip = true" @mouseleave="showTooltip = false"
                                class="cursor-pointer text-blue-500">
                                ℹ️
                            </span>

                            <!-- Tooltip -->
                            <div x-show="showTooltip" x-transition
                                class="absolute left-0 top-full mt-1 w-48 rounded-md bg-gray-800 p-2 text-xs text-white shadow-lg">
                                {{ __('add-task.detail_can_upload') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Release Time -->
                <div class="bg-accent_grey mt-6 flex flex-col gap-y-3 rounded-lg p-2">
                    <div class="flex flex-row items-center gap-x-2">
                        <input type="checkbox" x-model="isSchedule" @change="initTimeAndDate" name="isSchedule">
                        <label for="isSchedule">{{ __('add-task.release_time') }}</label>
                        <div x-data="{ showTooltip: false }" class="relative ml-2 inline-block">
                            <span @mouseenter="showTooltip = true" @mouseleave="showTooltip = false"
                                class="cursor-pointer text-blue-500">
                                ℹ️
                            </span>

                            <!-- Tooltip -->
                            <div x-show="showTooltip" x-transition
                                class="absolute left-0 top-full mt-1 w-48 rounded-md bg-gray-800 p-2 text-xs text-white shadow-lg">
                                {{ __('add-task.detail_release') }}
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col">
                        <label for="date">{{ __('add-task.add_date') }}</label>
                        <input x-bind:min="today" type="date" x-model="date" name="date"
                            :disabled="!isSchedule" class="rounded border p-2 transition-all"
                            @change='checkChangeDeadline'
                            :class="isSchedule ? 'bg-white text-black border-gray-300' :
                                'bg-gray-200 text-gray-500 border-gray-400 cursor-not-allowed'">
                    </div>

                    <div class="flex flex-col">
                        <label for="time">{{ __('add-task.add_time') }}</label>
                        <input type="time" x-model="time" name="time" :disabled="!isSchedule"
                            @change='checkChangeDeadline' class="rounded border p-2 transition-all"
                            :class="isSchedule ? 'bg-white text-black border-gray-300' :
                                'bg-gray-200 text-gray-500 border-gray-400 cursor-not-allowed'">
                    </div>
                </div>

                <!-- publish -->
                <div x-data="{ publish: '{{ __('add-task.publish') }}', update_publish: '{{ __('add-task.update_published') }}', draft: '{{ __('add-task.draft') }}' }" class="mt-5 flex justify-center">
                    <button x-show="release == 'DRAFT'" x-on:click="handlePublish" x-text="publish" type="button"
                        class="text-primary_white mb-2 me-2 rounded-lg bg-blue-700 px-5 py-2.5 text-center text-lg font-medium hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300"></button>
                    <button x-show="release == 'PUBLISH'" x-on:click="handleDraft" x-text="draft" type="button"
                        class="text-primary_white mb-2 me-2 rounded-lg bg-red-700 px-6 py-2 text-center text-lg font-medium hover:bg-red-800 focus:outline-none focus:ring-4 focus:ring-red-300"></button>
                    <button x-show="showUpdatePublish" x-on:click="handlePublish" x-text="update_publish"
                        type="button"
                        class="text-primary_white mb-2 me-2 rounded-lg bg-blue-700 px-6 py-2 text-center text-lg font-medium hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-red-300"></button>
                </div>
            </div>

        </template>
        <div class="ml-[25%] flex w-3/4 flex-col items-center px-[5%] pt-[80px]">

            <template x-if="isLoading && content.length == 0">
                <div class="flex h-full w-full flex-col items-center justify-center gap-y-3">
                    <p class="font-koho text-4xl font-bold text-gray-700">
                        {{ __('add-task.not_found') }}
                    </p>
                    <p class="text-secondary_blue underline"
                        @click="if (document.referrer.startsWith(window.location.origin)) {
                                history.back();
                            } else {
                                window.location.href = '{{ route('classroom-learn', ['id' => $id]) }}';
                            }
                        ">
                        {{ __('add-task.back') }}</p>
                </div>
            </template>

            <div x-show="isLoading && content.length > 0"
                class="bg-primary_white mt-5 h-auto w-full rounded-xl px-4 pb-3 pt-3">
                <input type="text" x-init="initTitle" x-model="title" x-on:input="saveNew = false"
                    class="max-w[650px] w-full rounded-xl border-none bg-white p-4 text-2xl"
                    placeholder="{{ __('add-task.title') }}">
                <div class="bg-white" id="editorjs" wire:ignore></div>
            </div>

            <div x-show="(type == 'task') && isLoading && content.length > 0"
                class="bg-primary_white animate-fade mt-4 w-full p-3">
                <div
                    class="border-secondary_blue flex w-full flex-row items-center rounded-xl border border-dashed p-3">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-[40px]">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier">
                            <path
                                d="M12.75 9C12.75 8.58579 12.4142 8.25 12 8.25C11.5858 8.25 11.25 8.58579 11.25 9L11.25 11.25H9C8.58579 11.25 8.25 11.5858 8.25 12C8.25 12.4142 8.58579 12.75 9 12.75H11.25V15C11.25 15.4142 11.5858 15.75 12 15.75C12.4142 15.75 12.75 15.4142 12.75 15L12.75 12.75H15C15.4142 12.75 15.75 12.4142 15.75 12C15.75 11.5858 15.4142 11.25 15 11.25H12.75V9Z"
                                nCar fill="#2867A4"></path>
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M12 1.25C6.06294 1.25 1.25 6.06294 1.25 12C1.25 17.9371 6.06294 22.75 12 22.75C17.9371 22.75 22.75 17.9371 22.75 12C22.75 6.06294 17.9371 1.25 12 1.25ZM2.75 12C2.75 6.89137 6.89137 2.75 12 2.75C17.1086 2.75 21.25 6.89137 21.25 12C21.25 17.1086 17.1086 21.25 12 21.25C6.89137 21.25 2.75 17.1086 2.75 12Z"
                                fill="#2867A4"></path>
                        </g>
                    </svg>
                    <svg viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" fill="#000000"
                        class="w-[40px]">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier">
                            <path
                                d="M864 512a32 32 0 0 0-32 32v96a32 32 0 0 0 64 0v-96a32 32 0 0 0-32-32zM881.92 389.44a23.68 23.68 0 0 0-5.76-2.88 19.84 19.84 0 0 0-6.08-1.92 32 32 0 0 0-28.8 8.64A32 32 0 0 0 832 416a32 32 0 1 0 64 0 33.6 33.6 0 0 0-9.28-22.72z"
                                fill="#2867A4"></path>
                            <path
                                d="M800 128h-32a96 96 0 0 0-96-96H352a96 96 0 0 0-96 96H224a96 96 0 0 0-96 93.44v677.12A96 96 0 0 0 224 992h576a96 96 0 0 0 96-93.44V736a32 32 0 0 0-64 0v162.56a32 32 0 0 1-32 29.44H224a32 32 0 0 1-32-29.44V221.44A32 32 0 0 1 224 192h32a96 96 0 0 0 96 96h320a96 96 0 0 0 96-96h32a32 32 0 0 1 32 29.44V288a32 32 0 0 0 64 0V221.44A96 96 0 0 0 800 128z m-96 64a32 32 0 0 1-32 32H352a32 32 0 0 1-32-32V128a32 32 0 0 1 32-32h320a32 32 0 0 1 32 32z"
                                fill="#2867A4"></path>
                            <path
                                d="M712.32 426.56L448 721.6l-137.28-136.32A32 32 0 0 0 265.6 630.4l160 160a32 32 0 0 0 22.4 9.6 32 32 0 0 0 23.04-10.56l288-320a32 32 0 0 0-47.68-42.88z"
                                fill="#2867A4"></path>
                        </g>
                    </svg>
                    <p class="text-secondary_blue ml-2 text-2xl font-bold">{{ __('add-class.upload') }}</p>
                </div>
            </div>

        </div>

    </main>
    <style>
        .ce-toolbar__actions--opened {
            opacity: 1;
            background: white;
            padding-inline: 10px;
            padding-bottom: 10px;
            border-radius: 10px;
        }

        .ce-block {
            min-height: 30px;
            width: 100%;
            font-size: 16px;
        }

        .ce-header {
            font-size: inherit !important;
            font-weight: bold;
        }

        h1.ce-header {
            font-size: 32px !important;
        }

        h2.ce-header {
            font-size: 28px !important;
        }

        h3.ce-header {
            font-size: 24px !important;
        }

        h4.ce-header {
            font-size: 20px !important;
        }

        h5.ce-header {
            font-size: 18px !important;
        }

        h6.ce-header {
            font-size: 16px !important;
        }
    </style>
    <script>
        function cmsContent() {
            return {
                isLoading: @entangle('isLoading').live,
                content: @entangle('content').live,
                saved: true,
                type: 'task',
                data: "",
                title: "",
                saveNew: true,
                selectionPos: false,
                idContent: @entangle('task'),
                idClass: @entangle('id'),
                date: null,
                time: null,
                lastDate: null,
                lastTime: null,
                release: "",
                visibility: false,
                isSchedule: false,
                rememberSave: false,
                publishPost: false,
                deadline: null,
                canUpload: true,
                deadlineTime: null,
                lastDeadline: null,
                lastCanUpload: true,
                lastDeadlineTime: null,
                deadlineError: false,
                borderDeadline: false,
                lastBorderDeadline: false,
                showUpdatePublish: false,
                init() {
                    if (this.selectionPos) return;
                    this.selectionPos = true;
                    if (this.content.length > 0) {
                        this.initEditor(this.content[0].content, this.idContent, this.idClass);
                        this.initRelease();
                        this.initDeadline();
                        this.initType();
                    } else {
                        this.$nextTick(() => {
                            this.$dispatch('show-failed', {
                                message: '{{ __('add-task.not_found') }}'
                            });
                        });
                    }
                },
                initEditor(initialData, idContent, idClass) {
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    let parsedData;
                    if (!initialData) {
                        parsedData = {};
                    } else if (typeof initialData === 'string') {
                        try {
                            parsedData = JSON.parse(initialData);
                        } catch (error) {
                            parsedData = {
                                "time": 1742033242584,
                                "blocks": [],
                                "version": "2.31.0-rc.7"
                            };
                        }
                    } else {
                        parsedData = initialData;
                    }
                    const editorjsconfig = {};
                    const editor = new EditorJS({
                        holder: "editorjs",
                        data: parsedData,
                        i18n: {
                            direction: 'ltr',
                        },
                        tools: {
                            layout: {
                                class: EditorJSLayout.LayoutBlockTool,
                                config: {
                                    EditorJS,
                                    editorjsconfig,
                                    enableLayoutEditing: false,
                                    enableLayoutSaving: true,
                                    initialData: {
                                        itemContent: {
                                            1: {
                                                blocks: [],
                                            },
                                        },
                                        layout: {
                                            type: "container",
                                            id: "",
                                            className: "",
                                            style: "border: 1px solid #000000; ",
                                            children: [{
                                                type: "item",
                                                id: "",
                                                className: "",
                                                style: "border: 1px solid #000000; display: inline-block; ",
                                                itemContentId: "1",
                                            }, ],
                                        },
                                    },
                                },
                            },
                            twoColumns: {
                                class: EditorJSLayout.LayoutBlockTool,
                                config: {
                                    EditorJS,
                                    editorjsconfig,
                                    enableLayoutEditing: false,
                                    enableLayoutSaving: false,
                                    initialData: {
                                        itemContent: {
                                            1: {
                                                blocks: [],
                                            },
                                            2: {
                                                blocks: [],
                                            }
                                        },
                                        layout: {
                                            type: "container",
                                            id: "",
                                            className: "",
                                            style: "border: 1px solid #000000; display: flex; justify-content: space-around; padding: 16px; ",
                                            children: [{
                                                    type: "item",
                                                    id: "",
                                                    className: "",
                                                    style: "border: 1px solid #000000; padding: 8px; ",
                                                    itemContentId: "1",
                                                },
                                                {
                                                    type: "item",
                                                    id: "",
                                                    className: "",
                                                    style: "border: 1px solid #000000; padding: 8px; ",
                                                    itemContentId: "2",
                                                },
                                            ],
                                        },
                                    },
                                },
                                shortcut: "CMD+2",
                                toolbox: {
                                    icon: `
                                    <svg xmlns='http://www.w3.org/2000/svg' width="16" height="16" viewBox='0 0 512 512'>
                                        <rect x='128' y='128' width='336' height='336' rx='57' ry='57' fill='none' stroke='currentColor' stroke-linejoin='round' stroke-width='32'/>
                                        <path d='M383.5 128l.5-24a56.16 56.16 0 00-56-56H112a64.19 64.19 0 00-64 64v216a56.16 56.16 0 0056 56h24' fill='none' stroke='currentColor' stroke-linecap='round' stroke-linejoin='round' stroke-width='32'/>
                                    </svg>
                                    `,
                                    title: "2 columns",
                                },
                            },
                            style: EditorJSStyle.StyleInlineTool,
                            paragraph: {
                                class: ParagraphAlignment,
                                config: {
                                    inlineToolbar: true,
                                    placeholder: '{{ __('add-task.paragraph') }}'
                                }
                            },
                            header: {
                                class: HeaderAlignment,
                                config: {
                                    placeholder: '{{ __('add-task.header') }}',
                                    levels: [1, 2, 3, 4, 5, 6],
                                    defaultLevel: 3
                                }
                            },
                            list: {
                                class: window.EditorjsList,
                                inlineToolbar: true,
                                placeholder: '{{ __('add-task.list') }}',
                                config: {
                                    defaultStyle: 'unordered'
                                },
                            },
                            image_upload: {
                                class: ImageTool,
                                config: {
                                    types: "image/*",
                                    additionalRequestHeaders: {
                                        "Authorization": `Bearer ${token}`,
                                        "Content-Type": "application/json"
                                    },
                                    captionPlaceholder: "{{ __('add-task.caption') }}",
                                    buttonContent: "{{ __('add-task.choose_image') }}",
                                    features: {
                                        border: true,
                                        background: true,
                                        stretched: true,
                                        caption: 'optional',
                                    },
                                    uploader: {
                                        async uploadByFile(file) {
                                            const formData = new FormData();
                                            formData.append('image', file);
                                            formData.append('content_id', idContent);
                                            formData.append('classroom_id', idClass);
                                            const response = await fetch('{{ route('upload-image') }}', {
                                                method: 'POST',
                                                body: formData,
                                                headers: {
                                                    'X-CSRF-TOKEN': token,
                                                },
                                            });
                                            return response.json();
                                        }

                                    }

                                },
                            },
                            image: {
                                class: InlineImage,
                                inlineToolbar: true,
                                config: {
                                    embed: {
                                        display: true,
                                    },
                                }
                            },
                            raw: {
                                class: RawTool,
                                config: {
                                    placeholder: '{{ __('add-task.code') }}'
                                }
                            },
                            code: {
                                class: CodeTool,
                                config: {
                                    placeholder: '{{ __('add-task.code') }}'
                                }
                            },
                            linkTool: {
                                class: LinkTool,
                                config: {
                                    endpoint: '{{ route('info') }}',
                                    headers: {
                                        'X-CSRF-TOKEN': token,
                                        'Content-Type': 'application/json'
                                    }
                                }
                            },
                            embed: {
                                class: Embed,
                                inlineToolbar: true,
                                config: {
                                    services: {
                                        youtube: true,
                                        facebook: true,
                                        instagram: true,
                                        twitter: true,
                                        twitch: true,
                                        "twitch-channel": true,
                                        miro: true,
                                        vimeo: true,
                                        gfycat: true,
                                        imgur: true,
                                        vine: true,
                                        aparat: true,
                                        "yandex-music-track": true,
                                        "yandex-music-album": true,
                                        "yandex-music-playlist": true,
                                        coub: true,
                                        codepen: true,
                                        pinterest: true,
                                        github: true
                                    }
                                }
                            },
                            ColorPicker: {
                                class: window.ColorPicker,
                            },
                            attaches: {
                                class: AttachesTool,
                                config: {
                                    uploader: {
                                        async uploadByFile(file) {
                                            const formData = new FormData();
                                            formData.append('file', file);
                                            formData.append('content_id', idContent);
                                            formData.append('classroom_id', idClass);
                                            const response = await fetch('{{ route('upload-file') }}', {
                                                method: 'POST',
                                                body: formData,
                                                headers: {
                                                    'X-CSRF-TOKEN': token,
                                                },
                                            });
                                            return response.json();
                                        },
                                    }
                                }
                            },
                            table: {
                                class: Table,
                                inlineToolbar: true,
                                config: {
                                    rows: 2,
                                    cols: 3,
                                    maxRows: 5,
                                    maxCols: 5,
                                },
                            },
                            quote: {
                                class: Quote,
                                config: {
                                    defaultType: "quotationMark",
                                },
                                shortcut: "CMD+SHIFT+O",
                            },
                            underline: Underline,
                            delimiter: Delimiter,
                            inlineCode: {
                                class: InlineCode,
                                shortcut: 'CMD+SHIFT+M',
                            },
                            textVariant: TextVariantTune,
                            Marker: {
                                class: Marker,
                                shortcut: 'CMD+SHIFT+M',
                            },
                            title: Title,
                            AnyButton: {
                                class: AnyButton,
                                inlineToolbar: false,
                                config: {
                                    textValidation: (text) => {
                                        if (text.length <= 0) {
                                            return false;
                                        }
                                        return true;
                                    },
                                    linkValidation: (text) => {
                                        if (text.length <= 0) {
                                            return false;
                                        }
                                        return true;
                                    }
                                },
                            },
                            nestedchecklist: EditorjsNestedChecklist,
                            Math: {
                                class: EJLaTeX,
                                shortcut: 'CMD+SHIFT+M',
                                config: {
                                    css: '.math-input-wrapper { padding: 5px; }'
                                }
                            },
                            mermaid: MermaidTool,
                            telegramPost: TelegramPost,
                            indentTune: {
                                class: IndentTune,
                                version: EditorJS.version,
                            },
                        },
                        i18n: {
                            messages: {
                                tools: {
                                    "AnyButton": {
                                        'Button Text': 'Button',
                                        'Link Url': 'Add Link',
                                        'Set': "Add",
                                        'Default Button': "Button",
                                    }
                                }
                            },
                        },
                        // tunes: ['textVariant'],
                        tunes: ['indentTune'],
                        onChange: async () => {
                            this.data = await editor.save();
                            this.saveNew = false;
                        },
                        onReady: () => {
                            MermaidTool.config({ 'theme': 'neutral' });
                            new Undo({ editor });
                            new DragDrop(editor);
                        }
                    });
                    
                    document.addEventListener('keydown', async (event) => {
                        if (event.ctrlKey && (event.key === 's' || event.key === 'S')) {
                            event.preventDefault();
                            this.saved = false;
                            const content = await editor.save();
                            const data = {
                                title: this.title,
                                content: content,
                                type: this.type,
                            };

                            Livewire.dispatch('savedContent', {
                                data: data
                            });
                        }
                    });
                    Livewire.on('savedSuccess', (event) => {
                        this.saved = true;
                        this.saveNew = true;
                    });
                },
                initTitle() {
                    this.title = this.content[0].title;
                },
                get minAllowedTime() {
                    let now = new Date();
                    now.setMinutes(now.getMinutes() + 10);
                    return now.toTimeString().slice(0, 5);
                },
                handlePublish() {
                    if (!(this.saveNew && this.saved)) {
                        if (!this.rememberSave) {
                            this.$dispatch('show-modal-failed', {
                                message: '{{ __('add-task.warning_saved') }}',
                                condition: 'SAVE',
                                warning: '{{ __('add-task.warning_title_saved') }}'
                            });
                            return;
                        }
                    }
                    if (this.title.length <= 3) {
                        this.$dispatch('show-modal-failed', {
                            message: '{{ __('add-task.failed_title') }}',
                            condition: 'TITLE',
                            warning: '{{ __('add-task.warning_for_title') }}'
                        });
                        return;
                    }

                    let waktu = '';
                    if (this.borderDeadline) {
                        if (!this.deadline || !this.deadlineTime) {
                            this.$dispatch('show-modal-failed', {
                                message: '{{ __('add-task.date_must_detail') }}',
                                condition: 'TITLE',
                                warning: '{{ __('add-task.date_must') }}'
                            });
                            return;
                        }
                        waktu = new Date(`${this.deadline}T${this.deadlineTime}:00`);
                        let now = new Date();
                        now.setMinutes(now.getMinutes() + 10);
                        if (waktu < now) {
                            this.$dispatch('show-modal-failed', {
                                message: '{{ __('add-task.time_min') }}',
                                condition: 'TITLE',
                                warning: '{{ __('add-task.date_must') }}'
                            });
                            return;
                        }
                        waktu = `${this.deadline} ${this.deadlineTime}`;
                    }

                    if (!this.deadline || !this.deadlineTime) {
                        waktu = null;
                    } else {
                        waktu = `${this.deadline} ${this.deadlineTime}`;
                    }

                    let data = {
                        date: this.isSchedule ? this.date : null,
                        time: this.isSchedule ? this.time : null,
                        schedule: this.isSchedule,
                        canUpload: this.canUpload,
                        deadline: waktu,
                        isDeadline: this.borderDeadline,
                        type: this.type,
                    };
                    Livewire.dispatch('savedPublishing', {
                        data: data
                    });
                    return;
                },
                async handleDraft() {
                    // Livewire.dispatch('savedDraft');
                    const data = await this.$wire.savedDraft();
                    if (data.status) {
                        this.deadline = null;
                        this.deadlineTime = null;
                        this.borderDeadline = false;
                    }
                },
                initType() {
                    this.type = this.content[0].type;
                },
                initDeadline() {
                    const deadline = this.content[0].deadline;
                    this.borderDeadline = this.content[0].isDeadline == 0 ? false : true;
                    this.lastBorderDeadline = this.borderDeadline;
                    this.canUpload = this.content[0].canUpload == 0 ? false : true;
                    this.lastCanUpload = this.canUpload;
                    if (deadline && deadline.length > 0) {
                        let [date, time] = deadline.split(" ");
                        let [hours, minutes] = time.split(":");
                        this.deadline = date;

                        this.deadlineTime = `${hours}:${minutes}`;
                        this.lastDeadline = date;
                        this.lastDeadlineTime = `${hours}:${minutes}`;
                    }
                },
                initRelease() {
                    if (this.publishPost) return;
                    this.publishPost = true;
                    Livewire.on('initRelease', (event) => {
                        let STATUS = "DRAFT";
                        if (event[0].STATUS == 'INIT') {
                            this.visibility = this.content[0].visibility == 0 ? false : true;
                            const getRelease = this.content[0].release;
                            if (this.visibility) {
                                if (getRelease == null) {
                                    // this.$nextTick(() => {
                                    //     this.$dispatch('show-failed', {
                                    //         message: '{{ __('add-task.not_found') }}kontol'
                                    //     });
                                    // });
                                } else {
                                    let [date, time] = getRelease.split(" ");
                                    let [hours, minutes] = time.split(":");
                                    this.date = date;
                                    this.time = `${hours}:${minutes}`;
                                    this.lastDate = date;
                                    this.lastTime = `${hours}:${minutes}`;
                                    let releaseDateTime = new Date(`${date}T${time}:00`);
                                    let now = new Date();
                                    if (now < releaseDateTime) {
                                        STATUS = "SCHEDULE";
                                    } else {
                                        STATUS = "PUBLISH";
                                    }
                                }
                            } else {
                                STATUS = "DRAFT";
                            }
                        }

                        if (event[0].STATUS == 'PUBLISH') {
                            const dateTime = event[0].DATE;
                            console.log(dateTime);
                            let [date, time] = dateTime.split(' ');
                            let [hours, minutes] = time.split(':');
                            this.$nextTick(() => {
                                this.release = "PUBLISH"
                                this.isSchedule = false
                                this.date = date;
                                this.time = `${hours}:${minutes}`
                                this.lastDate = date;
                                this.lastTime = `${hours}:${minutes}`


                                if (this.type == 'task') {
                                    this.lastDeadline = this.deadline;
                                    this.lastDeadlineTime = this.deadlineTime;
                                    this.lastBorderDeadline = this.borderDeadline;
                                    this.lastCanUpload = this.canUpload;
                                    this.checkChangeDeadline();
                                }
                            });
                            return;
                        }

                        if (event[0].STATUS == 'DRAFT') {
                            this.$nextTick(() => {
                                this.release = "DRAFT"
                                this.isSchedule = false
                                this.date = null;
                                this.time = null;
                                this.lastDate = null;
                                this.lastTime = null;
                            });
                            return;
                        }
                        this.$nextTick(() => {
                            this.release = STATUS
                        });

                    });
                    Livewire.dispatch('initRelease', [{
                        'STATUS': 'INIT'
                    }]);
                },
                clickSave() {
                    const event = new KeyboardEvent('keydown', {
                        key: 's',
                        ctrlKey: true,
                        bubbles: true,
                        cancelable: true
                    });
                    document.dispatchEvent(event);
                    this.saveNew = true;
                },
                initTimeAndDate() {
                    if (this.date == null) {
                        let now = new Date();
                        this.date = now.toISOString().split('T')[0];
                        let hours = String(now.getHours()).padStart(2, '0');
                        let minutes = String(now.getMinutes()).padStart(2, '0');
                        this.time = `${hours}:${minutes}`;
                    }
                },
                checkChangeDeadline() {
                    if (this.type == 'task') {
                        this.showUpdatePublish = this.release == 'PUBLISH' && (
                            this.deadline != this.lastDeadline ||
                            this.deadlineTime != this.lastDeadlineTime ||
                            this.canUpload != this.lastCanUpload ||
                            this.date != this.lastDate ||
                            this.time != this.lastTime ||
                            this.borderDeadline != this.lastBorderDeadline);
                    } else {
                        this.showUpdatePublish = this.release == 'PUBLISH' && (
                            this.date != this.lastDate || this.time != this.lastTime);
                    }



                },
                get today() {
                    return new Date().toISOString().split('T')[0];
                },
                get currentTime() {
                    return new Date().toTimeString().slice(0, 5);
                },
                get isToday() {
                    return this.deadline === this.today;
                },
                validateDeadlineTime() {
                    if (this.isToday && this.deadlineTime < this.currentTime) {
                        this.deadlineError = true; // Tampilkan error jika waktu kurang dari sekarang
                    } else {
                        this.deadlineError = false; // Sembunyikan error jika valid
                    }
                }

            }
        }
    </script>
</div>
