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
    public $material;
    public $teacher = false;
    public function mount($id, $task)
    {
        $this->idClassroom = $id;
        $this->idTask = $task;
        $this->isTeacher();
        $this->loadMaterial();
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

    public function loadMaterial()
    {
        try {
            $user = auth()->user()->id;
            $this->material = Task::where('content_id', $this->idTask)->where('user_id', $user)->first();
            if ($this->material) {
                $this->material = $this->material->toArray();
            } else {
                $this->material = null;
            }
        } catch (\Throwable $th) {
            Log::error('ClassroomLearn Eroor Load Material ' . $th);
        }
    }

    public function setScore($id, $score)
    {
        try {
            if ($score >= 101) {
                $this->dispatch('failed', [
                    'message' => __('add-task.failed_saved'),
                ]);
                return;
            }
            $user = auth()->user()->id;
            $task = Task::where('content_id', $id)->where('user_id', $user)->first();
            if ($task) {
                $task->update([
                    'value' => $score,
                ]);
            } else {
                Task::create([
                    'user_id' => $user,
                    'content_id' => $id,
                    'value' => $score,
                ]);
            }
        } catch (\Throwable $th) {
            Log::error('ClassroomLearn Eroor Set Score ' . $th);
            $this->dispatch('failed', [
                'message' => __('add-task.failed_saved'),
            ]);
        }
    }
}; ?>

<flux:main class="relative bg-white">
    <flux:sidebar.toggle
        class="text-gray-500! cursor-pointer border transition-all hover:border-gray-400/50 hover:shadow-md lg:hidden"
        icon="bars-2" inset="left" />
    @vite(['resources/js/editor.js'])

    <div x-data="classTask" x-init="firstInit" x-ref="editors" @scroll="calculate"
        class="h-screen max-h-screen min-h-[300px] overflow-y-auto">
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
</flux:main>
<script>
    function classTask() {
        return {
            idClassroom: @entangle('idClassroom'),
            idTask: @entangle('idTask'),
            datas: @entangle('datas').live,
            material: @entangle('material'),
            loading: true,
            isTeacher: @entangle('teacher').live,
            token: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            readLoad: false,
            score: 0,
            timeout: null,
            calculate() {
                if (this.timeout) {
                    clearTimeout(this.timeout);
                }
                this.timeout = setTimeout(() => {
                    if (this.score == 100) {
                        return;
                    }
                    const el = this.$refs.editors;
                    const scrollTop = el.scrollTop;
                    const scrollHeight = el.scrollHeight - el.clientHeight;
                    if (scrollHeight === 0) {
                        this.score = 100;
                        return;
                    }
                    const percent = Math.round((scrollTop / scrollHeight) * 100);
                    if (this.score >= percent) {
                        return;
                    }
                    if (percent == 100) {
                        this.$dispatch('success', [{
                            message: '{{ __('add-task.congratulation') }}',
                        }]);
                    }
                    this.score = percent;
                }, 500);
            },
            get urlBack() {
                const path = window.location.pathname;
                const segments = path.split('/');

                if (segments.length >= 3) {
                    return `/classroom/${this.idClassroom}`;
                }

                return '/classroom';
            },
            async firstInit() {
                const data = await this.$wire.loadContent(this.idTask);
                if (data.status) {
                    this.loading = false;
                    this.score = Number(this.material?.value ?? 0);
                    this.$watch('score', (value) => {
                        this.$wire.setScore(this.idTask, value);
                    });
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
        }
    }
</script>
