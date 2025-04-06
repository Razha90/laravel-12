<?php

use Livewire\Volt\Component;
use App\Models\Notification;
use App\Models\AplicationLater;

new class extends Component {
    public function getNotification()
    {
        try {
            $users = Auth::user();
            $data = Notification::where('user_id', $users->id)->get();
            return response()->json(['status' => true, 'data' => $data]);
        } catch (\Throwable $th) {
            Log::error('Data Notifikasi Get Error = ' . $th);
            return response()->json(['status' => false]);
        }
    }

    public function deleteNotification($id)
    {
        try {
            $data = Notification::find($id);
            Log::info('Data Notifikasi Delete = ' . $data);
            $data->delete();
            return response()->json(['status' => true]);
        } catch (\Throwable $th) {
            Log::error('Data Notifikasi Delete Error = ' . $th);
            return response()->json(['status' => false]);
        }
    }

    public function readNotification($id)
    {
        try {
            $data = Notification::find($id);
            $data->update(['read' => true]);
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
            Notification::where('user_id', $user->id)->delete();
            return response()->json(['status' => true]);
        } catch (\Throwable $th) {
            Log::error('Data Notifikasi Delete All Error = ' . $th);
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

            window.Echo.private("notifications." + userId)
                .listen(".notifications", async (e) => {
                    try {
                        const data = localStorage.getItem('notifications');
                        const now = new Date();
                        if (data == null) {
                            const newData = {
                                created_at: now.toISOString(),
                                data: [e.data]
                            }
                            // localStorage.setItem('notifications', JSON.stringify(newData));
                            updateNotification(newData);
                        } else {
                            const newData = {
                                created_at: now.toISOString(),
                                data: [...JSON.parse(data).data, e.data]
                            }
                            // localStorage.setItem('notifications', JSON.stringify(newData));
                            updateNotification(newData);
                        }
                    } catch (error) {
                        console.log(error);
                    }
                })
                .on("pusher:subscription_succeeded", async () => {
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

                        if (!(differenceInMinutes < 60)) {
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

                }).on("pusher:subscription_error", async (status) => {
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

            async function initAplicationLetter() {
                const data = localStorage.getItem('aplication-letter');
                if (data == null) {
                    const getData = await $wire.getAplicationLetter();
                    if (getData.original.status) {
                        const create_data = {
                            created_at: new Date().toISOString(),
                            data: getData.original.data
                        }
                        // localStorage.setItem('aplication-letter', JSON.stringify(create_data));
                        updateAplicationLetter(create_data);
                    } else {
                        const create_data = {
                            created_at: new Date().toISOString(),
                            data: []
                        }
                        // localStorage.setItem('aplication-letter', JSON.stringify(create_data));
                        updateAplicationLetter(create_data);
                    }
                } else {
                    const parsedData = JSON.parse(data);
                    const now = new Date();
                    const savedTime = new Date(parsedData.created_at);
                    const differenceInMinutes = (now - savedTime) / 1000 / 60;

                    if (!(differenceInMinutes < 60)) {
                        const getData = await $wire.getAplicationLetter();
                        console.log(getData);
                        if (getData.original.status) {
                            const newData = {
                                created_at: now.toISOString(),
                                data: getData.original.data
                            }
                            // localStorage.setItem('aplication-letter', JSON.stringify(newData));
                            updateAplicationLetter(newData);
                        } else {
                            const newData = {
                                created_at: now.toISOString(),
                                data: []
                            }
                            // localStorage.setItem('aplication-letter', JSON.stringify(newData));
                            updateAplicationLetter(newData);
                        }
                    }
                }
            }
            initAplicationLetter();

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
