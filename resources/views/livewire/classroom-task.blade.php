<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Content;
use Illuminate\Support\Facades\Log;
use App\Models\Task;
use App\Models\Classroom;

new #[Layout('components.layouts.classroom-learn')] class extends Component {
    public $idClassroom;
    public $idTask;
    public $datas;
    public $answers;
    public $teacher = false;
    public function mount($id, $task)
    {
        $this->idClassroom = $id;
        $this->idTask = $task;
        $this->isTeacher();
        $this->loadAnswer($task);
    }

    public function loadContent($id)
    {
        try {
            $this->datas = Content::find($id)->toArray();
            return [
                'status' => true,
            ];
        } catch (\Throwable $th) {
            Log::error('ClassroomLearn Eroor Load Content ' . $th);
            return [
                'status' => false,
            ];
        }
    }

    public function isTeacher()
    {
        try {
            $classroom = Classroom::find($this->idClassroom);
            $user = auth()->user()->id;
            if ($classroom->user_id == $user) {
                $this->teacher = true;
            } else {
                $this->teacher = false;
            }
        } catch (\Throwable $th) {
            Log::error('ClassroomLearn Eroor Load Teacher ' . $th);
        }
    }

    public function loadAnswer($content_id)
    {
        try {
            $user_id = auth()->user()->id;
            $data = Task::where('content_id', $content_id)->where('user_id', $user_id)->first();
            if ($data) {
                $this->answers = $data->toArray();
            } else {
                $this->answers = null;
            }
        } catch (\Throwable $th) {
            Log::error('ClassroomLearn Eroor Load Answer ' . $th);
        }
    }

    public function updateTask($answer)
    {
        try {
            $user_id = auth()->user()->id;
            $data = Task::where('content_id', $this->idTask)->where('user_id', $user_id)->first();
            if ($data) {
                if ($data->deadline !== null) {
                    $deadline = \Carbon\Carbon::parse($data->deadline);
                    $now = \Carbon\Carbon::now();
                    if ($now->greaterThan($deadline)) {
                        $this->dispatch('failed', ['message' => __('add-task.expired_task')]);
                        return false;
                    }
                }
                $data->answer = $answer;
                $data->save();
                $this->dispatch('success', ['message' => __('add-task.success_task')]);
                return true;
            } else {
                $this->dispatch('failed', ['message' => __('add-task.failed_task')]);
                Log::error('gak jumpa');
                return false;
            }
            return true;
        } catch (\Throwable $th) {
            Log::error('ClassroomLearn Error Update Task ' . $th);
            $this->dispatch('failed', ['message' => __('add-task.server_error')]);
            return false;
        }
    }

    public function uploadTask($answer)
    {
        try {
            $user_id = auth()->user()->id;
            $data = Task::create([
                'content_id' => $this->idTask,
                'user_id' => $user_id,
                'answer' => $answer,
            ]);
            if ($data && $data->id) {
                $this->dispatch('success', ['message' => __('add-task.success_task')]);
                return true;
            }
            $this->dispatch('failed', ['message' => __('add-task.failed_task')]);
            return false;
        } catch (\Throwable $th) {
            Log::error('ClassroomLearn Error Update Task ' . $th);
            $this->dispatch('failed', ['message' => __('add-task.server_error')]);
            return false;
        }
    }
}; ?>

<flux:main class="relative bg-white">
    <div class="flex mb-10 flex-row items-center justify-between" x-data="{
        idClassroom: @entangle('idClassroom'),
        get urlBack() {
            const path = window.location.pathname;
            const segments = path.split('/');
            if (segments.length >= 3) {
                return `/classroom/${this.idClassroom}`;
            }
            return '/classroom';
        }
    }">
        <div class="flex">
            <a :href="urlBack"
                class="text-secondary_blue animate-fade hover:text-secondary_blue/50 flex cursor-pointer flex-row gap-x-1 transition-colors">
                <svg class="w-[35px]" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                    <g id="SVGRepo_iconCarrier">
                        <path
                            d="M16.1795 3.26875C15.7889 2.87823 15.1558 2.87823 14.7652 3.26875L8.12078 9.91322C6.94952 11.0845 6.94916 12.9833 8.11996 14.155L14.6903 20.7304C15.0808 21.121 15.714 21.121 16.1045 20.7304C16.495 20.3399 16.495 19.7067 16.1045 19.3162L9.53246 12.7442C9.14194 12.3536 9.14194 11.7205 9.53246 11.33L16.1795 4.68297C16.57 4.29244 16.57 3.65928 16.1795 3.26875Z"
                            fill="currentColor"></path>
                    </g>
                </svg>
                <p class="text-2xl">{{ __('login.back') }}</p>
            </a>
        </div>
        <flux:sidebar.toggle
            class="text-gray-500! cursor-pointer border transition-all hover:border-gray-400/50 hover:shadow-md lg:hidden"
            icon="bars-2" inset="left" />
    </div>
    @vite(['resources/js/editor.js'])

    <div x-data="classTask" x-init="firstInit">
        <template x-if="loading && !datas">
            <div class="w-full animate-pulse">
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
                <h1 x-text="datas.title" class="text-secondary_blue mb-10 text-center text-3xl font-bold"></h1>
                <style>
                    .codex-editor__redactor {
                        padding-bottom: 0 !important;
                        margin-right: 0 !important
                    }

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

                    .link-tool__content {
                        color: black !important;
                    }
                </style>
                <div wire:ignore x-init="readEditor('editor', datas.content)" id="editor" class="!text-secondary_black w-full"></div>
                <template x-if="!isTeacher">
                    <div x-data="{
                        editorWidth: 700,
                        syncWidth() {
                            this.$nextTick(() => {
                                const contentEl = document.querySelector('.ce-block__content');
                                if (contentEl) {
                                    this.editorWidth = contentEl.offsetWidth;
                                }
                            });
                    
                            const observer = new ResizeObserver(entries => {
                                for (let entry of entries) {
                                    this.editorWidth = entry.contentRect.width;
                                }
                            });
                    
                            const el = document.querySelector('.ce-block__content');
                            if (el) observer.observe(el);
                        }
                    }" x-init="syncWidth" :style="{ width: editorWidth + 'px' }"
                        class="mx-auto mt-5 flex flex-col">
                        <h2 class="text-secondary_blue mb-5 mt-10 text-center text-xl font-bold">
                            {{ __('add-task.answer') }}
                        </h2>
                        <div class="flex w-[135px] flex-col items-start justify-start">
                            <div class="flex flex-row items-center gap-x-1">
                                <flux:icon.clock class="text-gray-500" />
                                <flux:text class="text-lg text-gray-500">
                                    {{ __('class-learn.deadline') }}
                                </flux:text>
                            </div>
                            <div class="w-full text-center">
                                <p class="text-gray-500" x-text="getRemainingTime(datas.deadline)"></p>
                            </div>
                        </div>
                        <div x-init="answerEditor('answer')" class="" wire:ignore>
                            <div id="answer" class="!text-secondary_black w-full">
                            </div>
                        </div>
                        <button @click="savedButton" x-show="!isDeadlinePassed"
                            class="text-primary_white ml-auto mt-5 w-[120px] px-3 py-2 text-center text-lg transition-all hover:bg-gray-200"
                            x-bind:class="!changed ? 'bg-gray-200 cursor-not-allowed' :
                                'cursor-pointer bg-secondary_blue hover:text-secondary_blue'"
                            x-text="!answers ? '{{ __('add-task.upload') }}' : saving ? '{{ __('add-task.saving') }}..' : '{{ __('add-task.updated') }}'">
                        </button>
                    </div>
                </template>
            </div>
        </template>
        <style>
            #answer .ce-block__content {
                background: #F2F2F2 !important;
                padding-inline: 10px;
                padding-top: 10px;
                padding-bottom: 10px;
            }
        </style>

    </div>
    <livewire:component.editor />
</flux:main>
<script>
    function classTask() {
        return {
            idClassroom: @entangle('idClassroom'),
            idTask: @entangle('idTask'),
            datas: @entangle('datas').live,
            loading: true,
            isTeacher: @entangle('teacher'),
            answers: @entangle('answers').live,
            token: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            isDeadlinePassed: false,
            saving: false,
            readLoad: false,
            editorLoad: false,
            changed: false,
            get urlBack() {
                const path = window.location.pathname;
                const segments = path.split('/');

                if (segments.length >= 3) {
                    return `/classroom/${this.idClassroom}`;
                }

                return '/classroom';
            },
            savedButton() {

                const event = new KeyboardEvent('keydown', {
                    key: 's',
                    ctrlKey: true,
                    bubbles: true,
                    cancelable: true
                });
                document.dispatchEvent(event);

            },
            async firstInit() {
                const data = await this.$wire.loadContent(this.idTask);
                if (data.status) {
                    this.loading = false;
                } else {
                    this.$dispatch('failed', [{
                        message: 'Gagal memuat konten'
                    }]);
                }
            },
            readEditor(id, data) {
                if (this.readLoad) return;
                this.readLoad = true;
                const parsedData = JSON.parse(data);
                const editorjsconfig = {};
                new EditorJS({
                    holder: id,
                    data: parsedData,
                    readOnly: true,
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
                                    "Authorization": `Bearer ${this.token}`,
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
                                        formData.append('content_id', this.idTask);
                                        formData.append('classroom_id', this.idClassroom);
                                        const response = await fetch('{{ route('upload-image') }}', {
                                            method: 'POST',
                                            body: formData,
                                            headers: {
                                                'X-CSRF-TOKEN': this.token,
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
                                }
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
                                    'X-CSRF-TOKEN': this.token,
                                    'Content-Type': 'application/json'
                                }
                            }
                        },
                        embed: {
                            class: Embed,
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
                                        formData.append('content_id', this.idTask);
                                        formData.append('classroom_id', this.idClassroom);
                                        const response = await fetch('{{ route('upload-file') }}', {
                                            method: 'POST',
                                            body: formData,
                                            headers: {
                                                'X-CSRF-TOKEN': this.token,
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
                    onReady: () => {
                        MermaidTool.config({
                            'theme': 'neutral'
                        });
                    },
                    tunes: ['indentTune'],
                });
            },
            answerEditor(id) {
                if (this.editorLoad) return;
                this.editorLoad = true;
                let parsedData = this.answers ? this.answers.answer : null;
                if (parsedData) {
                    parsedData = JSON.parse(parsedData);
                } else {
                    parsedData = null
                }

                let isDeadlinePassed = false;
                if (this.datas.deadline) {
                    const deadline = new Date(this.datas.deadline);
                    const now = new Date();
                    isDeadlinePassed = now > deadline;
                }
                this.isDeadlinePassed = isDeadlinePassed;

                const editorjsconfig = {};
                const content_id = this.idTask;
                const classroom_id = this.idClassroom;
                const token = this.token;
                const editor = new EditorJS({
                    holder: id,
                    data: parsedData,
                    readOnly: isDeadlinePassed,
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
                                        formData.append('content_id', content_id);
                                        formData.append('classroom_id', classroom_id);
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
                                }
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
                                        formData.append('content_id', content_id);
                                        formData.append('classroom_id', classroom_id);
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
                    onReady: () => {
                        MermaidTool.config({
                            'theme': 'neutral'
                        });
                    },
                    onChange: async () => {
                        this.changed = true;
                    },
                    tunes: ['indentTune'],
                });

                document.addEventListener('keydown', async (event) => {
                    if (event.ctrlKey && (event.key === 's' || event.key === 'S')) {
                        event.preventDefault();
                        if (!this.changed) return;
                        if (this.saving) return;
                        this.saving = true;
                        const content = await editor.save();
                        const contentString = JSON.stringify(content);
                        if (this.answers) {
                            const res = await this.$wire.updateTask(contentString);
                        } else {
                            const res = await this.$wire.uploadTask(contentString);
                        }
                        this.changed = false;
                        this.saving = false;
                    }
                });
                window.addEventListener('beforeunload', (e) => {
                    if (!this.changed) return;
                    e.preventDefault();
                    e.returnValue = '';
                });

            },
            getRemainingTime(deadlineString) {
                if (!deadlineString) return '--:--';
                const deadline = new Date(deadlineString);
                const now = new Date();
                const diffMs = deadline - now;

                if (diffMs <= 0) return '{{ __('class-learn.end') }}';

                const diffSeconds = Math.floor(diffMs / 1000);
                const days = Math.floor(diffSeconds / (3600 * 24));
                const hours = Math.floor((diffSeconds % (3600 * 24)) / 3600);
                const minutes = Math.floor((diffSeconds % 3600) / 60);

                let result = '';

                if (days > 0) {
                    result += `${days} {{ __('class-learn.day') }} `;
                    if (minutes > 0) result += `${minutes} {{ __('class-learn.minute') }}`;
                } else {
                    if (hours > 0) result += `${hours} {{ __('class-learn.hour') }} `;
                    if (minutes > 0) result += `${minutes} {{ __('class-learn.minute') }}`;
                }

                if (result === '') result = '1 {{ __('class-learn.minute') }}';

                return result.trim();
            },
        }
    }
</script>
