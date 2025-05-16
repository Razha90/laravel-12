<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>
<div>

</div>
<script>

    document.addEventListener('DOMContentLoaded', function () {
        const editorjsconfig = {};
        console.log('editorjsconfig', window.EditorJSLayout);
        window.tools = {
            layout: {
                class: window.EditorJSLayout.LayoutBlockTool,
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
                            children: [
                                {
                                    type: "item",
                                    id: "",
                                    className: "",
                                    style: "border: 1px solid #000000; display: inline-block; ",
                                    itemContentId: "1",
                                },
                            ],
                        },
                    },
                },
            },
            twoColumns: {
                class: window.EditorJSLayout.LayoutBlockTool,
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
                            style:
                                "border: 1px solid #000000; display: flex; justify-content: space-around; padding: 16px; ",
                            children: [
                                {
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
                class: Paragraph,
                config: {
                    inlineToolbar: true,
                    placeholder: '{{ __('add-task.paragraph') }}'
                }
            },
            header: {
                class: Header,
                config: {
                    placeholder: '{{ __('add-task.header') }}',
                    levels: [1, 2, 3, 4, 5, 6],
                    defaultLevel: 3
                }
            },
            list: {
                class: List,
                placeholder: '{{ __('add-task.list') }}'
            },
            imageGallery: window.ImageGallery,
            image: {
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
                            formData.append('content_id', idContent);
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
            quote: Quote,
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
            }
        }

    });
</script>