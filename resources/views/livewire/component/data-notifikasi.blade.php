<?php

use Livewire\Volt\Component;
use App\Models\Notification;
use App\Models\AplicationLater;

new class extends Component {
    public function getNotification()
    {
        try {
            $user = auth()->user();
            $data = $user->notifications()->orderBy('created_at', 'desc')->get()->toArray();
            return response()->json(['status' => true, 'data' => $data]);
        } catch (\Throwable $th) {
            Log::error('Data Notifikasi Get Error = ' . $th);
            return response()->json(['status' => false]);
        }
    }

    public function getUpdateNotification()
    {
        try {
            $user = auth()->user();
            $data = $user->notifications()->orderBy('created_at', 'desc')->get()->toArray();
            return response()->json(['status' => true, 'data' => $data]);
        } catch (\Throwable $th) {
            Log::error('Data Notifikasi Get Error = ' . $th);
            return response()->json(['status' => false]);
        }
    }

    public function deleteNotification($id)
    {
        try {
            $user = auth()->user();
            $notification = $user->notifications()->where('id', $id)->first();
            if (!$notification) {
                $this->dispatch('failed', ['message' => __('notifikasi.not_found')]);
                return response()->json(['status' => false, 'message' => __('notifikasi.not_found')]);
            }
            $notification->delete();
            return response()->json(['status' => true]);
        } catch (\Throwable $th) {
            Log::error('Data Notifikasi Delete Error = ' . $th);
            return response()->json(['status' => false]);
        }
    }

    public function readNotification($id)
    {
        try {
            $user = auth()->user();
            $notification = $user->unreadNotifications()->where('id', $id)->first();
            if (!$notification) {
                $this->dispatch('failed', ['message' => __('notifikasi.not_found')]);
                return response()->json(['status' => false, 'message' => __('notifikasi.not_found')]);
            }
            $notification->markAsRead();
            return response()->json(['status' => true]);
        } catch (\Throwable $th) {
            Log::error('Data Notifikasi Read Error = ' . $th);
            return response()->json(['status' => false]);
        }
    }

    public function getAplicationLetter()
    {
        try {
            $user = auth()->user();
            $data = AplicationLater::where('user_id', $user->id)->get();
            return response()->json(['status' => true, 'data' => $data]);
        } catch (\Throwable $th) {
            Log::error('Data Notifikasi Aplication Error = ' . $th);
            return response()->json(['status' => false]);
        }
    }

    public function deleteAplicationLetter($id)
    {
        try {
            $data = AplicationLater::find($id);
            $data->delete();
            return response()->json(['status' => true]);
        } catch (\Throwable $th) {
            Log::error('Data Notifikasi Aplication Delete Error = ' . $th);
            return response()->json(['status' => false]);
        }
    }

    public function deleteAllNotification()
    {
        try {
            $user = auth()->user();
            $user->notifications()->delete();
            return response()->json(['status' => true]);
        } catch (\Throwable $th) {
            Log::error('Data Semua Notifikasi Delete Error = ' . $th);
            return response()->json(['status' => false]);
        }
    }
}; ?>

<div>
    @script
        <script>
            const userId = "{{ auth()->user()->id }}";

            function updateNotification(data) {
                localStorage.setItem('notifications', JSON.stringify(data));
                const event = new Event('notifications');
                window.dispatchEvent(event);
            }

            function updateAplicationLetter(data) {
                localStorage.setItem('aplication-letter', JSON.stringify(data));
                const event = new Event('aplication-letter');
                window.dispatchEvent(event);
            }

            // window.Echo.private("aplications." + userId)
            //     .notification(async (e) => {

            if (!window.hasEventNotification) {
                window.hasEventNotification = true;
                window.Echo.private(`App.Models.User.${userId}`).notification(async (notification) => {
                    try {
                        // const data = localStorage.getItem('notifications');
                        // const now = new Date();
                        // if (data == null) {
                        //     const newData = {
                        //         created_at: now.toISOString(),
                        //         data: [notification.data]
                        //     }
                        //     updateNotification(newData);
                        // } else {
                        //     const newData = {
                        //         created_at: now.toISOString(),
                        //         data: [notification, ...JSON.parse(data).data]
                        //     }
                        //     updateNotification(newData);
                        // }
                        const now = new Date();
                        const getData = await $wire.getNotification();
                        if (getData.original.status) {
                            const newData = {
                                created_at: now.toISOString(),
                                data: getData.original.data
                            }
                            updateNotification(newData);
                        } else {
                            const newData = {
                                created_at: now.toISOString(),
                                data: []
                            }
                            updateNotification(newData);
                        }

                    } catch (error) {
                        console.error(error);
                    }
                }).on("pusher:subscription_error", async (status) => {
                    const getData = await $wire.getNotification();
                    alert('Error: ' + status);

                    if (getData.original.status) {
                        const now = new Date();
                        const newData = {
                            created_at: now.toISOString(),
                            data: getData.original.data
                        }
                        localStorage.setItem('notifications', JSON.stringify(newData));
                        updateNotification(newData);
                    } else {
                        const now = new Date();
                        const newData = {
                            created_at: now.toISOString(),
                            data: []
                        }
                        localStorage.setItem('notifications', JSON.stringify(newData));
                        updateNotification(newData);
                    }
                });

                window.Echo.connector.pusher.connection.bind('connected', async () => {
                    const data = localStorage.getItem('notifications');
                    const now = new Date();
                    if (data === null) {
                        const getData = await $wire.getNotification();
                        if (getData.original.status) {
                            const newData = {
                                created_at: now.toISOString(),
                                data: getData.original.data
                            }
                            updateNotification(newData);
                        } else {
                            const newData = {
                                created_at: now.toISOString(),
                                data: []
                            }
                            updateNotification(newData);
                        }
                    } else {
                        const parsedData = JSON.parse(data);
                        const savedTime = new Date(parsedData.created_at);
                        const differenceInMinutes = (now - savedTime) / 1000 / 60;

                        if (!(differenceInMinutes < 5)) {
                            const getData = await $wire.getNotification();

                            if (getData.original.status) {
                                const newData = {
                                    created_at: now.toISOString(),
                                    data: getData.original.data
                                }
                                localStorage.setItem('notifications', JSON.stringify(newData));
                                updateNotification(newData);
                            } else {
                                const newData = {
                                    created_at: now.toISOString(),
                                    data: []
                                }
                                localStorage.setItem('notifications', JSON.stringify(newData));
                                updateNotification(newData);
                            }
                        }
                    }
                });

                window.addEventListener('notification-delete', async function(event) {
                    const try_delete = await $wire.deleteNotification(event.detail.id);
                    if (!try_delete.original.status) {
                        const data = localStorage.getItem('notifications');
                        const newData = {
                            created_at: new Date().toISOString(),
                            data: JSON.parse(data).data.filter(item => item.id !== event.detail.id)
                        }
                        updateNotification(newData);
                    }
                });

                window.addEventListener('notification-delete-all', async function(event) {
                    const try_delete = await $wire.deleteAllNotification();
                    if (!try_delete.original.status) {
                        const getData = await $wire.getNotification();

                        if (getData.original.status) {
                            const now = new Date();
                            const newData = {
                                created_at: now.toISOString(),
                                data: getData.original.data
                            }
                            localStorage.setItem('notifications', JSON.stringify(newData));
                            updateNotification(newData);
                        } else {
                            const now = new Date();
                            const newData = {
                                created_at: now.toISOString(),
                                data: []
                            }
                            localStorage.setItem('notifications', JSON.stringify(newData));
                            updateNotification(newData);
                        }
                    }
                });

                window.addEventListener('notification-read', async function(event) {
                    const try_read = await $wire.readNotification(event.detail.id);
                    if (!try_read.original.status) {
                        const data = localStorage.getItem('notifications');
                        const newData = {
                            created_at: new Date().toISOString(),
                            data: JSON.parse(data).data.filter(item => item.id !== event.detail.id)
                        }
                        updateNotification(newData);
                    }
                });

            }
            window.Echo.private("aplications." + userId)
                .notification(async (e) => {
                    try {
                        const data = localStorage.getItem('aplication-letter');
                        const now = new Date();

                        if (data == null) {
                            const newData = {
                                created_at: now.toISOString(),
                                data: [e.data]
                            }
                            updateAplicationLetter(newData);
                        } else {
                            const parsed = JSON.parse(data);
                            if (e.data.hasOwnProperty('origin') && e.data.origin) {
                                const existingIndex = parsed.data.findIndex(item => item.id === e.data.id);
                                if (existingIndex !== -1) {
                                    parsed.data[existingIndex] = e.data;
                                } else {
                                    parsed.data.push(e.data);
                                }
                            } else {
                                parsed.data = parsed.data.filter(item => item.id !== e.data.id);

                            }
                            const newData = {
                                created_at: now.toISOString(),
                                data: parsed.data
                            };

                            updateAplicationLetter(newData);
                        }
                    } catch (error) {
                        console.error(error);
                    }
                })
                .on("pusher:subscription_succeeded", async () => {
                    const data = localStorage.getItem('aplication-letter');
                    const now = new Date();
                    const getDatas = await $wire.getAplicationLetter();
                    if (data === null) {
                        const getData = await $wire.getAplicationLetter();
                        if (getData.original.status) {
                            const newData = {
                                created_at: now.toISOString(),
                                data: getData.original.data
                            }
                            updateAplicationLetter(newData);
                        } else {
                            const newData = {
                                created_at: now.toISOString(),
                                data: []
                            }
                            updateAplicationLetter(newData);
                        }
                    } else {
                        const parsedData = JSON.parse(data);
                        const savedTime = new Date(parsedData.created_at);
                        const differenceInMinutes = (now - savedTime) / 1000 / 60;

                        if (!(differenceInMinutes < 5)) {
                            const getData = await $wire.getAplicationLetter();
                            if (getData.original.status) {
                                const newData = {
                                    created_at: now.toISOString(),
                                    data: getData.original.data
                                }
                                localStorage.setItem('aplication-letter', JSON.stringify(newData));
                                updateAplicationLetter(newData);
                            } else {
                                const newData = {
                                    created_at: now.toISOString(),
                                    data: []
                                }
                                localStorage.setItem('aplication-letter', JSON.stringify(newData));
                                updateAplicationLetter(newData);
                            }
                        }
                    }

                }).on("pusher:subscription_error", async (status) => {
                    const getData = await $wire.getAplicationLetter();
                    if (getData.original.status) {
                        const now = new Date();
                        const newData = {
                            created_at: now.toISOString(),
                            data: getData.original.data
                        }
                        localStorage.setItem('aplication-letter', JSON.stringify(newData));
                        updateAplicationLetter(newData);
                    } else {
                        const now = new Date();
                        const newData = {
                            created_at: now.toISOString(),
                            data: []
                        }
                        localStorage.setItem('aplication-letter', JSON.stringify(newData));
                        updateAplicationLetter(newData);
                    }
                });

            window.addEventListener('aplication-letter-delete', async function(event) {
                const try_delete = await $wire.deleteAplicationLetter(event.detail.id);
                if (!try_delete.original.status) {
                    const data = localStorage.getItem('aplication-letter');
                    const newData = {
                        created_at: new Date().toISOString(),
                        data: JSON.parse(data).data.filter(item => item.id !== event.detail.id)
                    }
                    updateAplicationLetter(newData);
                }
            });
        </script>
    @endscript
</div>
