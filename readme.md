"dev": [
    "Composer\\Config::disableProcessTimeout",
    "npx concurrently -c \"#93c5fd,#c4b5fd,#fdba74,#34d399\" \"php artisan serve\" \"php artisan queue:listen --tries=1\" \"php artisan reverb:start\" \"npm run dev\" --names='server,queue,reverb,vite'"
]

"dev": [
            "Composer\\Config::disableProcessTimeout",
            "npx concurrently -c \"#93c5fd,#c4b5fd,#fdba74\" \"php artisan serve\" \"php artisan queue:listen --tries=1\" \"npm run dev\" --names='server,queue,vite'"
        ]