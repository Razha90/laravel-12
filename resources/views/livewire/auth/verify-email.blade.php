<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    /**
     * Send an email verification notification to the user.
     */

    public function mount()
    {
        auth()->user()->email_verified_at ? $this->redirectIntended(default: route('my-app', absolute: false), navigate: true) : null;
    }
    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('my-app', absolute: false), navigate: true);

            return;
        }

        Auth::user()->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div class="mt-4 flex flex-col gap-6">
    <flux:text class="text-center">
        {{ __('auth.verify_email') }}
        <span class="text-xl">{{ auth()->user()->email }}</span>
    </flux:text>

    @if (session('status') == 'verification-link-sent')
        <flux:text class="text-center font-medium !dark:text-green-400 !text-green-600">
            {{ __('auth.verify_link') }}
        </flux:text>
    @endif

    <div class="flex flex-col items-center justify-between space-y-3">
        <flux:button wire:click="sendVerification" variant="primary" class="w-full">
            {{ __('auth.resend_verification') }}
        </flux:button>

        <!-- <flux:link class="text-sm cursor-pointer" wire:click="logout">
            {{ __('Log out') }}
        </flux:link> -->
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="rounded-lg px-2 py-1 transition-colors hover:cursor-pointer inline-flex w-full text-accent-content underline">
                {{ __('welcome.logout') }}
            </button>
        </form>
    </div>
</div>
