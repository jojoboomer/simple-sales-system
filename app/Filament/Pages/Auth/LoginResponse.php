<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Http\Responses\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->route('filament.admin.resources.orders.index');
        }

        return redirect()->intended(filament()->getUrl());
    }
}
