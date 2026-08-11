<?php

namespace App\Livewire\Portal;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.portal')]
class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public string $loginError = '';

    public function authenticate(): void
    {
        $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            $this->loginError = 'Email o contraseña incorrectos.';

            return;
        }

        request()->session()->regenerate();

        $this->redirectRoute('portal.dashboard', navigate: true);
    }

    public function render()
    {
        return view('livewire.portal.login');
    }
}
