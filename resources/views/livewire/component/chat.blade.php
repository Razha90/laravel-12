<?php

use Livewire\Volt\Component;
use App\Models\Chat;
use App\Events\Chatting;
use App\Models\Conversation;

new class extends Component {
    public function loadMessage()
    {
        try {
            $userId = auth()->user()->id;
            $conversation = Conversation::with('userOne', 'userTwo', 'chats')->where('user_one', $userId)->orWhere('user_two', $userId)->get();
            return [
                'status' => true,
                'data' => $conversation->toArray(),
            ];
        } catch (\Throwable $th) {
            Log::error('Chat Error' . $th);
            return [
                'status' => false,
            ];
        }
    }

    public function createConversation($receiverId)
    {
        try {
            $user = auth()->user();
            $conversation = Conversation::where(function ($query) use ($user, $receiverId) {
                $query->where('user_one', $user->id)->where('user_two', $receiverId);
            })
                ->orWhere(function ($query) use ($user, $receiverId) {
                    $query->where('user_one', $receiverId)->where('user_two', $user->id);
                })
                ->first();
            if (!$conversation) {
                $conversation = new Conversation();
                $conversation->user_one = $user->id;
                $conversation->user_two = $receiverId;
                $conversation->save();
                $conversation = Conversation::with('userOne', 'userTwo', 'chats')->find($conversation->id);

                return [
                    'status' => true,
                    'conversation' => $conversation->toArray(),
                ];
            }
            return [
                'status' => false,
            ];
        } catch (\Throwable $th) {
            Log::error('Chat Error' . $th);
            return [
                'status' => false,
            ];
        }
    }

    public function sendMessage($receiverId, $conversationId, $message)
    {
        try {
            $user = auth()->user();

            if (!$message) {
                return false;
            }

            $chat = new Chat();
            $chat->sender_id = $user->id;
            $chat->receiver_id = (int) $receiverId; // casting jadi integer
            $chat->message = $message;
            $chat->conversations_id = $conversationId;
            $chat->save();

            return true;
        } catch (\Throwable $th) {
            Log::error('Chat Error: ' . $th);
            return false;
        }
    }
}; ?>

<div id="draggable-chat" class="fixed bottom-4 right-4 z-50 select-none shadow-xl" x-cloak x-init="initChat"
    x-data="initChatting" @click.away="berangkatActive=false">
    <div class="border-secondary_blue absolute bottom-0 right-0 flex h-[350px] w-[300px] flex-col rounded-xl border"
        x-show="berangkatActive" x-transition>
        <template x-if="!userChat">
            <div
                class="bg-secondary_blue text-primary_white relative flex flex-row items-center gap-x-2 rounded-t-xl px-4 py-2">
                <div>
                    <svg class="h-[25px] w-[25px]" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier">
                            <path
                                d="M17 3.33782C15.5291 2.48697 13.8214 2 12 2C6.47715 2 2 6.47715 2 12C2 13.5997 2.37562 15.1116 3.04346 16.4525C3.22094 16.8088 3.28001 17.2161 3.17712 17.6006L2.58151 19.8267C2.32295 20.793 3.20701 21.677 4.17335 21.4185L6.39939 20.8229C6.78393 20.72 7.19121 20.7791 7.54753 20.9565C8.88837 21.6244 10.4003 22 12 22C17.5228 22 22 17.5228 22 12C22 10.1786 21.513 8.47087 20.6622 7"
                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                        </g>
                    </svg>
                </div>
                <p class="">{{ __('app.chat') }}</p>
                <div @click="berangkatActive=false"
                    class="text-primary_white absolute right-2 top-1/2 -translate-y-1/2 cursor-pointer transition-all hover:text-red-400 active:text-red-400">
                    <svg class="w-[20px]" viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg"
                        fill="currentColor">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier">
                            <path fill="currentColor"
                                d="M195.2 195.2a64 64 0 0 1 90.496 0L512 421.504 738.304 195.2a64 64 0 0 1 90.496 90.496L602.496 512 828.8 738.304a64 64 0 0 1-90.496 90.496L512 602.496 285.696 828.8a64 64 0 0 1-90.496-90.496L421.504 512 195.2 285.696a64 64 0 0 1 0-90.496z">
                            </path>
                        </g>
                    </svg>
                </div>
            </div>
        </template>
        <template x-if="userChat">
            <div x-data="{
                person: {},
                my_person: {},
                initPerson() {
                    const dataMes = messages.find(item => item.id == this.userChat);
                    this.person = dataMes;
                    this.initPersonCon();
                },
                initPersonCon() {
                    const { user_one, user_two } = this.person;
                    if (user_one.id != this.userId) {
                        this.my_person = user_one;
                        return;
                    }
                    if (user_two.id != this.userId) {
                        this.my_person = user_two;
                        return;
                    }
                    this.my_person = user_one
                },
            }" x-init="initPerson"
                class="bg-secondary_blue relative flex flex-row items-start gap-x-2 rounded-t-xl px-4 py-2">
                <div class="text-primary_white flex h-full items-center justify-center" @click="userChat = null">
                    <svg class="h-[20px] w-[20px]" fill="currentColor" viewBox="0 0 1024 1024"
                        xmlns="http://www.w3.org/2000/svg">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier">
                            <path
                                d="M222.927 580.115l301.354 328.512c24.354 28.708 20.825 71.724-7.883 96.078s-71.724 20.825-96.078-7.883L19.576 559.963a67.846 67.846 0 01-13.784-20.022 68.03 68.03 0 01-5.977-29.488l.001-.063a68.343 68.343 0 017.265-29.134 68.28 68.28 0 011.384-2.6 67.59 67.59 0 0110.102-13.687L429.966 21.113c25.592-27.611 68.721-29.247 96.331-3.656s29.247 68.721 3.656 96.331L224.088 443.784h730.46c37.647 0 68.166 30.519 68.166 68.166s-30.519 68.166-68.166 68.166H222.927z">
                            </path>
                        </g>
                    </svg>
                </div>
                <p class="text-primary_white line-clamp-1" x-text="my_person.name"></p>
                <div @click="berangkatActive=false"
                    class="text-primary_white absolute right-2 top-1/2 -translate-y-1/2 cursor-pointer transition-all hover:text-red-400 active:text-red-400">
                    <svg class="w-[20px]" viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg"
                        fill="currentColor">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier">
                            <path fill="currentColor"
                                d="M195.2 195.2a64 64 0 0 1 90.496 0L512 421.504 738.304 195.2a64 64 0 0 1 90.496 90.496L602.496 512 828.8 738.304a64 64 0 0 1-90.496 90.496L512 602.496 285.696 828.8a64 64 0 0 1-90.496-90.496L421.504 512 195.2 285.696a64 64 0 0 1 0-90.496z">
                            </path>
                        </g>
                    </svg>
                </div>
            </div>
        </template>
        <div class="bg-primary_white h-full w-full rounded-b-xl">
            <template x-if="userChat">
                <div x-data="{
                    person: {},
                    person_me: {},
                    person_send: {},
                    initPerson() {
                        const dataMes = messages.find(item => item.id == this.userChat);
                        this.person = dataMes;
                        this.chatting = messages.find(item => item.id == userChat);
                        this.initPersonCon();
                    },
                    initPersonCon() {
                        const { user_one, user_two } = this.person;
                        if (user_one.id != this.userId) {
                            this.person_me = user_one;
                            this.person_send = user_two
                            return;
                        }
                        if (user_two.id != this.userId) {
                            this.person_me = user_two;
                            this.person_send = user_one
                            return;
                        }
                        this.person_me = user_one
                    },
                    chatting: {},
                
                }" x-init="initPerson" class="relative flex h-full flex-col">
                    <template x-if="chatting.chats">
                        <div class="flex h-[260px] flex-col gap-y-2 overflow-y-auto px-2">
                            <template x-for="(item, index) in chatting.chats" :key="index">
                                <div class="max-w-[90%] rounded-md p-2"
                                    x-bind:class="item.sender_id == userId ? 'bg-gray-200 self-end' : 'bg-green-100 self-start'">
                                    <p class="text-secondary_blue" x-text="item.message"></p>
                                </div>
                            </template>
                        </div>
                    </template>
                    <template x-if="!chatting.chats">
                        <p class="text-secondary_blue text-center">{{ __('app.chat_empty') }}</p>
                    </template>
                    <div class="bg-primary_white flex h-[40px] w-full flex-row gap-x-1 rounded-b-xl px-2 py-1">
                        <input type="search" x-model="chatUser"
                            class="w-full border border-gray-300 bg-gray-50 pl-2 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-blue-500" />
                        <button @click="sendChat(person_me.id,chatUser)"
                            class="bg-secondary_blue text-primary_white rounded-md px-3 py-1 text-sm">{{ __('admin.send') }}</button>
                    </div>
                </div>
            </template>
            <template x-if="!userChat && messages">
                <div class="h-[295px] overflow-y-auto">
                    <template x-for="(item, index) in messages" :key="index">
                        <div x-data="{
                            person: {},
                            initPerson(conversation) {
                                const { user_one, user_two } = conversation;
                                if (user_one.id != this.userId) {
                                    this.person = user_one;
                                    return;
                                }
                                if (user_two.id != this.userId) {
                                    this.person = user_two;
                                    return;
                                }
                                this.person = user_one
                            },
                            timeAgo(dateString) {
                                const now = new Date();
                                const past = new Date(dateString);
                                const seconds = Math.floor((now - past) / 1000);
                                const intervals = [
                                    { label: '{{ __('app.year') }}', seconds: 31536000 },
                                    { label: '{{ __('app.month') }}', seconds: 2592000 },
                                    { label: '{{ __('app.day') }}', seconds: 86400 },
                                    { label: '{{ __('app.hour') }}', seconds: 3600 },
                                    { label: '{{ __('app.hour') }}', seconds: 60 },
                                    { label: '{{ __('app.second') }}', seconds: 1 }
                                ];
                                for (const interval of intervals) {
                                    const count = Math.floor(seconds / interval.seconds);
                                    if (count >= 1) {
                                        return `${count} ${interval.label}${count > 1 ? '' : ''} lalu`;
                                    }
                                }
                                return 'baru saja';
                            }
                        }" class="flex flex-row justify-between border-b border-gray-400 p-2"
                            x-init="initPerson(item)" @click="userChat=item.id">
                            <div class="flex flex-row gap-x-2">
                                <div
                                    class="border-secondary_blue relative h-[35px] w-[35px] overflow-hidden rounded-full border">
                                    <img class="h-full w-full" :src="person.profile_photo_path"
                                        :alt="person.name" />
                                    <div class="absolute left-0 top-0 -z-10 h-full w-full animate-pulse bg-gray-300">
                                    </div>
                                </div>
                                <div class="text-secondary_blue flex flex-col gap-y-1">
                                    <p x-text="person.name" class="line-clamp-1 !py-0 text-sm font-bold"></p>
                                    <p x-text="item.last_message"
                                        class="text-secondary_blue/50 line-clamp-1 !py-0 text-sm">
                                    </p>
                                </div>
                            </div>
                            <div x-text="timeAgo(item.updated_at)" class="text-secondary_blue text-sm"></div>
                        </div>
                    </template>
                </div>
            </template>
            <template x-if="!messages">
                <p class="text-secondary_blue mt-10 text-center text-base"></p>
            </template>
        </div>
    </div>
    <div class="animate-fade text-primary_white bg-secondary_blue hover:bg-secondary_blue/40 active:bg-secondary_blue/40 absolute bottom-0 right-0 h-[50] w-[50px] cursor-pointer overflow-hidden rounded-full p-3 transition-all"
        @click="berangkatActive=true" x-show="
        !berangkatActive">
        <svg class="h-[25px] w-[25px]" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
            <g id="SVGRepo_iconCarrier">
                <path
                    d="M17 3.33782C15.5291 2.48697 13.8214 2 12 2C6.47715 2 2 6.47715 2 12C2 13.5997 2.37562 15.1116 3.04346 16.4525C3.22094 16.8088 3.28001 17.2161 3.17712 17.6006L2.58151 19.8267C2.32295 20.793 3.20701 21.677 4.17335 21.4185L6.39939 20.8229C6.78393 20.72 7.19121 20.7791 7.54753 20.9565C8.88837 21.6244 10.4003 22 12 22C17.5228 22 22 17.5228 22 12C22 10.1786 21.513 8.47087 20.6622 7"
                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
            </g>
        </svg>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chatBubble = document.getElementById('draggable-chat');
        let isDragging = false;
        let offsetX = 0,
            offsetY = 0;

        chatBubble.addEventListener('mousedown', (e) => {
            isDragging = true;
            const rect = chatBubble.getBoundingClientRect();
            offsetX = e.clientX - rect.left;
            offsetY = e.clientY - rect.top;
            chatBubble.style.transition = 'none';
        });

        document.addEventListener('mousemove', (e) => {
            if (!isDragging) return;

            const bubbleWidth = chatBubble.offsetWidth;
            const bubbleHeight = chatBubble.offsetHeight;
            const maxX = window.innerWidth - bubbleWidth;
            const maxY = window.innerHeight - bubbleHeight;

            let x = e.clientX - offsetX;
            let y = e.clientY - offsetY;

            x = Math.max(0, Math.min(x, maxX));
            y = Math.max(0, Math.min(y, maxY));

            chatBubble.style.left = `${x}px`;
            chatBubble.style.top = `${y}px`;
            chatBubble.style.right = 'auto';
            chatBubble.style.bottom = 'auto';
            chatBubble.style.position = 'fixed';
        });

        document.addEventListener('mouseup', () => {
            isDragging = false;
        });

    });

    function initChatting() {
        return {
            messages: [],
            isActive: false,
            berangkatActive: false,
            stopInitChat: false,
            stopInitStorage: false,
            userChat: null,
            stopInitEvent: false,
            chatUser: '',
            userId: '{{ auth()->user()->id }}',
            async sendChat(receiver_id, message) {
                try {
                    if (message.length > 0) {
                        this.chatUser = '';
                        const result = await this.$wire.sendMessage(receiver_id, this.userChat, message);
                        if (!result) {
                            this.$dispatch('failed', [{
                                message: '{{ __('app.chat_failed') }}',
                            }]);
                        }
                    }
                } catch (error) {
                    this.$dispatch('failed', [{
                        message: '{{ __('app.chat_failed') }}'
                    }]);
                }
            },
            initChat() {
                if (this.stopInitChat) return;
                this.stopInitChat = true;
                userId = this.userId;
                window.Echo.private(`chat.${userId}`).listen('.chatting', async (chats) => {
                    const conversationId = chats.data.conversations_id;
                    const convIndex = this.messages.findIndex(item => item.id == conversationId);
                    if (convIndex !== -1) {
                        this.messages[convIndex].last_message = chats.data.message;
                        this.messages[convIndex].updated_at = new Date().toISOString();
                        const newChat = {
                            id: chats.data.id,
                            sender_id: chats.data.sender_id,
                            receiver_id: chats.data.receiver_id,
                            message: chats.data.message,
                            read_at: chats.data.read_at,
                            conversations_id: chats.data.conversations_id,
                            created_at: chats.data.created_at,
                            updated_at: chats.data.updated_at
                        };
                        if (!Array.isArray(this.messages[convIndex].chats)) {
                            this.messages[convIndex].chats = [];
                        }
                        this.messages[convIndex].chats.push(newChat);
                    } else {
                        const loadMes = await this.$wire.loadMessage();
                        this.messages = loadMes.data;
                    }
                }).on("pusher:subscription_error", async (status) => {
                    alert('disconnect')
                });

                window.Echo.private(`chat.${userId}`)
                    .listen('.chatting', function(event) {}).on("pusher:subscription_error", async (status) => {
                        alert('disconnect')
                    });

                window.Echo.connector.pusher.connection.bind('connected', async () => {
                    setInterval(() => {
                        const currentTime = new Date().toISOString();
                        localStorage.setItem('times', currentTime);
                    }, 60000);
                });
                // window.Echo.private(`chat.${userId}`)
                //     .subscribed(function() {
                //         console.log('subscribed To Channel')
                //     })
                //     .listenToAll(function() {
                //         console.log('listening to channel')
                //     })
                //     .listen('.chatting', (data) => {
                //         console.log(data);

                //     });
                this.initEvent();
                this.initStorage();
            },
            async initStorage() {
                if (this.stopInitStorage) return;
                this.stopInitStorage = true;
                this.$watch('messages', (val) => {
                    const jsonChats = JSON.stringify(val);
                    const currentTime = new Date().toISOString();
                    localStorage.setItem('chats', jsonChats);
                    localStorage.setItem('times', currentTime);
                });

                const data = localStorage.getItem('chats');
                const times = localStorage.getItem('times');
                const dataParse = JSON.parse(data);

                if (dataParse && times) {
                    const savedTime = new Date(times);
                    const now = new Date();
                    const diffInMs = now - savedTime;
                    const timeLimit = 60 * 1000; // 60 detik = 1 menit
                    if (diffInMs < timeLimit) {
                        this.messages = dataParse;
                    } else {
                        const loadMes = await this.$wire.loadMessage();
                        this.messages = loadMes.data;
                    }
                } else {
                    const resultLoad = await this.$wire.loadMessage();
                    if (resultLoad.status && resultLoad.data) {
                        const newData = JSON.stringify(this.messages.data);
                        const newTime = new Date().toISOString();
                        localStorage.setItem('chats', newData);
                        localStorage.setItem('times', newTime);
                        this.messages = resultLoad.data;
                    }
                }
            },
            searchUserIsFind(user_one, user_two) {
                const data = this.messages.find(obj => {
                    console.log('user_one', user_one, 'user_two', user_two, 'obj', obj);
                    return (
                        (obj.user_one.id == user_one && obj.user_two.id == user_two) ||
                        (obj.user_one.id == user_two && obj.user_two.id == user_one)
                    );
                });
                return data;
            },
            initEvent() {
                if (this.stopInitEvent) return;
                this.stopInitEvent = true;
                document.addEventListener('user-chat', async (e) => {
                    const data = e.detail;
                    if (data.sender_id && data.receiver_id) {
                        const searching = this.searchUserIsFind(data.sender_id, data.receiver_id);
                        console.log('searching', searching);
                        if (searching) {
                            this.berangkatActive = true;
                            this.userChat = searching.id;
                        } else {
                            const con = await this.$wire.createConversation(data.receiver_id);
                            if (con.status && con.conversation) {
                                this.messages.push(con.conversation);
                            } else {
                                const result = await this.$wire.loadMessage();
                                if (result.status && result.data) {
                                    this.messages = result.data;
                                } else {}
                            }
                        }
                    } else {
                        const con = await this.$wire.createConversation(data.receiver_id);
                        if (con.status && con.conversation) {
                            this.messages.push(con.conversation);
                        } else {
                            const result = await this.$wire.loadMessage();
                            this.messages = result.data;
                        }
                    }
                });
            }
        }
    }
</script>
