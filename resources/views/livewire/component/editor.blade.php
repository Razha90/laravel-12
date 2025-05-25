<?php

use Livewire\Volt\Component;
use App\Models\Comment;

new class extends Component {
    public $classroomId;
    public $contentId;
    public $comments;
    public $error;
    public function mount()
    {
        $segments = request()->segments();
        $this->classroomId = $segments[1];
        $this->contentId = $segments[3];
        $this->getComment();
    }

    public function getComment()
    {
        try {
            $comment = Comment::with('user', 'content')->where('content_id', $this->contentId)->get();
            if ($comment->isEmpty()) {
            } else {
                $this->comments = $comment->toArray();
            }
        } catch (\Throwable $th) {
            Log::error('Error fetching comments: ' . $th->getMessage());
            $this->error = 'An error occurred while fetching comments.';
        }
    }

    public function addComment($comment)
    {
        try {
            Comment::create([
                'content_id' => $this->contentId,
                'user_id' => auth()->user()->id,
                'comment' => $comment,
            ]);
            $this->getComment();
        } catch (\Throwable $th) {
            Log::error('Error adding comment: ' . $th->getMessage());
            $this->dispatch('failed', [
                'message' => __('error.server_error'),
            ]);
        }
    }

    public function deleteComment($id)
    {
        try {
            $comment = Comment::find($id);
            if ($comment) {
                $comment->isDeleted = true;
                $comment->save();
                $this->getComment();
            } else {
                $this->dispatch('failed', [
                    'message' => __('error.server_error'),
                ]);
            }
        } catch (\Throwable $th) {
            Log::error('Error deleting comment: ' . $th->getMessage());
            $this->dispatch('failed', [
                'message' => __('error.server_error'),
            ]);
        }
    }
}; ?>

<div x-data="editorComment" class="mt-10" x-init="initComment">
    @vite(['resources/js/moment.js'])

    <template x-if="comments">
        <div class="text-secondary_blue mx-auto max-w-[650px]">
            <h2 class="animate-fade-up mb-5 text-center text-2xl font-bold">{{ __('add-task.kolom_comment') }}</h2>
            <div class="max-h-[500px] overflow-y-auto px-2">
                <template x-for="(item, index) in comments" :key="index">
                    <div class="mb-5 flex max-h-[600px] gap-2.5" x-data="{ dropdown: false }"
                        :class="item.user.id == '{{ auth()->user()->id }}' ? 'flex-row-reverse' : 'flex-row'">
                        <img class="h-8 w-8 rounded-full" :src="item.user.profile_photo_path" :alt="item.user.name">
                        <div
                            class="leading-1.5 flex w-full max-w-[320px] flex-col rounded-e-xl rounded-es-xl border-gray-200 bg-gray-100 p-4">
                            <div class="flex items-center space-x-2 rtl:space-x-reverse">
                                <span class="text-secondary_blue/70 line-clamp-1 text-sm font-semibold"
                                    x-text="item.user.name"></span>
                                <span class="line-clamp-1 text-sm font-normal text-gray-500"
                                    x-text="changeDate(item.created_at)"></span>
                            </div>
                            <p class="py-2.5 text-sm font-normal text-gray-500"
                                x-text="item.isDeleted == '1' ? '[{{ __('add-task.comment_delete') }}]' : item.comment">
                            </p>
                        </div>
                        <button @click="dropdown = !dropdown"
                            class="inline-flex items-center self-center rounded-lg bg-white p-2 text-center text-sm font-medium text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-4 focus:ring-gray-50"
                            type="button">
                            <svg class="h-4 w-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                fill="currentColor" viewBox="0 0 4 15">
                                <path
                                    d="M3.5 1.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm0 6.041a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm0 5.959a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
                            </svg>
                        </button>
                        <div x-show="dropdown" x-transition @click.away="dropdown = false"
                            class="z-10 w-40 divide-y divide-gray-100 rounded-lg bg-white shadow-sm">
                            <ul class="py-2 text-sm text-gray-700" aria-labelledby="dropdownMenuIconButton">
                                <li>
                                    <p @click="deleteComment(item.id); dropdown = false"
                                        class="block !px-4 py-2 text-red-500 hover:bg-red-100">{{ __('admin.delete') }}
                                    </p>
                                </li>
                            </ul>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </template>
    <div class="animate-fade-up mt-10">
        <h2 class="text-secondary_blue text-bold mx-auto max-w-[650px] pl-5 text-left text-2xl">
            {{ __('add-task.comment') }}
        </h2>
        <div class="mx-auto mb-4 max-w-[650px] rounded-lg border border-gray-200 bg-gray-50">
            <div class="rounded-t-lg bg-white px-4 py-2">
                <label for="comment" class="sr-only">Your comment</label>
                <textarea id="comment" rows="4" x-model="comment" @keyup.enter="addComment"
                    class="w-full border-0 bg-white px-0 text-sm text-gray-900 focus:ring-0"
                    placeholder="{{ __('add-task.try_write_comment') }}" required></textarea>
            </div>
            <div class="flex items-center justify-between border-t border-gray-200 px-3 py-2">
                <button type="button" @click="addComment"
                    class="inline-flex items-center rounded-lg bg-blue-700 px-4 py-2.5 text-center text-xs font-medium text-white hover:bg-blue-800 focus:ring-4 focus:ring-blue-200">
                    {{ __('add-task.add_comment') }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function editorComment() {
        return {
            classroomId: @entangle('classroomId'),
            contentId: @entangle('contentId'),
            comments: @entangle('comments').live,
            error: @entangle('error'),
            comment: null,
            initComment() {
                if (this.error) {
                    this.$dispatch('failed', [{
                        message: this.error,
                    }])
                }
            },
            addComment() {
                if (this.comment) {
                    this.$wire.addComment(this.comment).then(() => {
                        this.comment = null;
                        this.$dispatch('success', [{
                            message: '{{ __('add-task.success_comment') }}',
                        }])
                    })
                } else {
                    this.$dispatch('failed', [{
                        message: '{{ __('add-task.please_add_comment') }}',
                    }])
                }
            },
            changeDate(createdAt) {
                const formattedTime = moment(createdAt).fromNow();
                return formattedTime;
            },
            deleteComment(id) {
                this.$wire.deleteComment(id).then(() => {
                    this.$dispatch('success', [{
                        message: '{{ __('add-task.success_delete_comment') }}',
                    }])
                })
            },
        }
    }
</script>
