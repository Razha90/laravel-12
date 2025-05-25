<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Task;
use App\Models\Classroom;

new #[Layout('components.layouts.classroom-learn')] class extends Component {
    public $classId;
    public $contentId;
    public $getTask;
    public $classroom;
    public $error = null;

    public function mount($id, $task)
    {
        $this->classId = $id;
        $this->contentId = $task;
        $this->loadData();
        $this->getClassroom();
    }

    public function loadData()
    {
        try {
            $data = Task::with('user')->where('content_id', $this->contentId)->get();
            $this->getTask = $data->toArray();
        } catch (\Throwable $th) {
            Log::error('Error loading data: ' . $th->getMessage());
        }
    }

    public function getClassroom()
    {
        try {
            $data = Classroom::where('id', $this->classId)->get();
            $this->classroom = $data->toArray();
        } catch (\Throwable $th) {
            $this->error = __('error.not_found');
            Log::error('ClassroomLearn Eroor Load Data' . $th);
        }
    }

    public function addValueReading($id, $value)
    {
        try {
            $task = Task::find($id);
            if ($task) {
                $task->reading = $value;
                $task->save();
                $this->loadData();
                $this->dispatch('success', [
                    'message' => __('add-task.success')
                ]);
                return;
            }
            $this->dispatch('failed', [
                'message' => __('error.server_error')
            ]);
        } catch (\Throwable $th) {
            Log::error('Error adding value: ' . $th->getMessage());
            $this->dispatch('failed', [
                'message' => __('error.server_error')
            ]);
            return;
        }

    }
}; ?>

<flux:main class="relative bg-white">
    <div class="flex flex-row items-center justify-between" x-data="{
        idClassroom: @entangle('classId'),
        get urlBack() {
            const path = window.location.pathname;
            const segments = path.split('/');
            if (segments.length >= 3) {
                return `/classroom/${this.idClassroom}`;
            }
            return '/classroom';
        }
    }">
        <div class="mb-3 flex">
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

    <div x-data="initClassReward" x-init="initReward">
        <template x-if="!tasks && !getStudents">
            <div class="animate-fade animate-pulse">
                <div class="mb-4 h-5 w-full rounded-full bg-gray-200 dark:bg-gray-700"></div>
                <div class="mb-4 h-5 w-full rounded-full bg-gray-200 dark:bg-gray-700"></div>
                <div class="mb-4 h-5 w-full rounded-full bg-gray-200 dark:bg-gray-700"></div>
                <div class="mb-4 h-5 w-full rounded-full bg-gray-200 dark:bg-gray-700"></div>
                <div class="mb-4 h-5 w-full rounded-full bg-gray-200 dark:bg-gray-700"></div>
                <div class="mb-4 h-5 w-full rounded-full bg-gray-200 dark:bg-gray-700"></div>
            </div>
        </template>
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
        <template x-if="tasks && getStudents">
            <div class="mt-10 flex flex-col gap-y-2">
                <h1 class="text-secondary_blue line-clamp-1 text-2xl font-bold">{{ __('add-task.title_task') }} : <span
                        x-text="classroom[0].title"></span> </h1>
                <template x-for="(data, index) in sortStudent" :key="data.id">
                    <div x-data="{
                        checker: checkAnswer(data.user.id),
                        showing: false,
                        valueReading: searchValueTas(data.user.id),
                        renderedEditors: {},
                        initValues() {
                            this.$watch('tasks', () => {
                                this.valueReading = searchValueTas(data.user.id);
                            });
                        },
                        showEditor(id, data) {
                            this.showing = !this.showing;
                            if (!this.renderedEditors[id]) {
                                this.$nextTick(() => {
                                    readEditor(id, data);
                                    this.renderedEditors[id] = true;
                                });
                            }
                        }
                    }" class="w-full rounded-xl bg-white p-3 shadow-md transition-all"
                        :class="showing ? 'hover:bg-primary-white' : 'hover:bg-gray-100'" @click.away="showing = false" x-init="initValues">
                        <div class="flex flex-row gap-x-5">
                            <div class="flex w-[150px] max-w-[150px] flex-row items-center gap-x-2">
                                <div class="border-secondary_blue overflow-hidden rounded-full border shadow-md">
                                    <img wire:igonre :src="data.user.profile_photo_path" alt="Avatar"
                                        class="h-10 w-10 rounded-full object-cover">
                                </div>
                                <p class="text-secondary_blue line-clamp-1" x-text="data.user.name"></p>
                            </div>
                            <div class="text-secondary_blue text-center">
                                <p class="text-secondary_blue/70 text-md">{{ __('add-task.status') }}</p>
                                <p x-text="checker ? '{{ __('add-task.ready') }}' : '{{ __('add-task.not_ready') }}'"
                                    :class="checker ? 'text-green-400' : 'text-red-400'"></p>
                            </div>
                            <div class="text-secondary_blue flex flex-col items-center">
                                <p class="text-secondary_blue/70">{{ __('add-task.task_value') }}</p>
                                <p x-text="valueReading ? valueReading : '{{ __('add-task.give_value') }}' "></p>
                            </div>
                            <div class="flex-1 text-right" x-show="checker"
                                @click="showEditor(data.id, searchAnswer(data.user_id))">
                                <flux:button icon="eye">
                                    {{ __('class-learn.detail') }}
                                </flux:button>
                            </div>

                        </div>
                        <div x-transition x-show="showing"
                            class="text-secondary_blue border-secondary_blue/20 mt-5 h-auto w-full border-t pt-3">
                            <div wire:ignore :id="data.id"></div>
                            <div class="mt-5 flex w-full justify-center" x-data="{ value:valueReading, addValueRead(id, value) {
                                const datas = this.searchId(id);
                                if (datas) {
                                    this.$wire.addValueReading(datas, value);
                                } else {
                                    this.$dispatch('failed', [{
                                        'message': '{{ __('error.server_error') }}'
                                    }]);
                                }
                            } }">
                                <div class="w-xl max-w-xl">
                                    <label for="search"
                                        class="sr-only mb-2 text-sm font-medium text-gray-900">{{ __('add-task.give_value') }}</label>
                                    <div class="relative">
                                        <div
                                            class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3">
                                            <svg class="h-6 w-6 text-gray-500" viewBox="0 0 24 24" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                                    stroke-linejoin="round"></g>
                                                <g id="SVGRepo_iconCarrier">
                                                    <path
                                                        d="M6 8V5C6 4.44772 6.44772 4 7 4H17C17.5523 4 18 4.44772 18 5V19C18 19.5523 17.5523 20 17 20H7C6.44772 20 6 19.5523 6 19V16M6 12H13M13 12L10.5 9.77778M13 12L10.5 14.2222"
                                                        stroke="currentColor" stroke-linecap="round"
                                                        stroke-linejoin="round">
                                                    </path>
                                                </g>
                                            </svg>
                                        </div>
                                        <input type="number" x-model.number="value"
                                            @input="value = Math.min(100, Math.max(0, value))" min="0"
                                            max="100" step="5"
                                            class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-4 ps-10 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500"
                                            placeholder="{{ __('add-task.give_value') }}" required />
                                        <button @click="addValueRead(data.user_id, value)"
                                            class="absolute bottom-2.5 end-2.5 rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300">{{ __('add-class.add') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        <template x-if="pagination && pagination.length > 1">
            <ul class="mt-10 inline-flex h-10 w-full justify-center -space-x-px text-center text-base">
                <template x-for="item in pagination" :key="item">
                    <li>
                        <button @click="page = item"
                            :class="page == item ?
                                'flex items-center justify-center px-4 h-10 text-blue-600 border border-gray-300 bg-blue-50 hover:bg-blue-100 hover:text-blue-700' :
                                'flex h-10 items-center justify-center border border-gray-300 bg-white px-4 leading-tight text-gray-500 hover:bg-gray-100 hover:text-gray-700'"
                            x-text="item"></button>
                    </li>
                </template>
            </ul>
        </template>
    </div>
</flux:main>

<script>
    function initClassReward() {
        return {
            idContent: @entangle('contentId'),
            idClassroom: @entangle('classId'),
            tasks: @entangle('getTask').live,
            error: @entangle('error'),
            classroom: @entangle('classroom'),
            token: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            getStudents: [],
            sortStudent: [],
            page: 1,
            per_page: 15,
            pagination: [],
            searchValueTas(userId) {
                const task = this.tasks.find(task => task?.user_id == userId);
                return task && task.reading ? task.reading : null;
            },
            searchId(userId) {
                const task = this.tasks.find(task => task?.user_id == userId);
                return task && task.id ? task.id : null;
            },
            initReward() {
                this.getStudents = this.students.filter(item => item.role === "member");;
                this.sortStudent = this.paginate();
                this.pagination = this.createPagination();
                this.$watch('page', (val) => {
                    this.sortStudent = this.paginate();
                });
                if (this.error) {
                    setTimeout(() => {
                        this.$dispatch('failed', [{
                            'message': this.error
                        }]);
                    }, 500);
                }
            },
            paginate() {
                const start = (this.page - 1) * this.per_page;
                const end = start + this.per_page;
                return this.getStudents.slice(start, end);
            },
            createPagination() {
                const current = this.page;
                const total = this.getStudents.length;
                const itemsPerPage = this.per_page;
                const totalPages = Math.ceil(total / itemsPerPage);
                const delta = 2;
                let start = Math.max(1, current - delta);
                let end = Math.min(totalPages, current + delta);
                if (current <= delta) {
                    end = Math.min(totalPages, end + (delta - current + 1));
                } else if (current + delta > totalPages) {
                    start = Math.max(1, start - (current + delta - totalPages));
                }

                const pagination = [];
                for (let i = start; i <= end; i++) {
                    pagination.push(i);
                }

                return pagination;
            },
            checkAnswer(userId) {
                const task = this.tasks.find(task => task?.user_id == userId);
                return task && task.answer ? true : false;
            },
            searchAnswer(userId) {
                const task = this.tasks.find(task => task?.user_id == userId);
                return task && task.answer ? task.answer : null;
            },
            readEditor(id, data) {
                const parsedData = JSON.parse(data);
                const editorjsconfig = {};
                new EditorJS({
                    holder: String(id),
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
                                        formData.append('content_id', this.idContent);
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
        }
    }
</script>
