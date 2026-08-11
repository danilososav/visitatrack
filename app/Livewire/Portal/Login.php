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

    /**
     * @var array<int, array{label: string, description: string, email: string, password: string}>
     */
    public array $demoUsers = [
        [
            'label' => '👷 Trabajador demo',
            'description' => 'Lucía Benítez — visitas y máquinas',
            'email' => 'worker1@visitatrack.test',
            'password' => 'password',
        ],
        [
            'label' => '👷 Trabajador demo 2',
            'description' => 'Marcos Ferreira — segundo usuario de prueba',
            'email' => 'worker2@visitatrack.test',
            'password' => 'password',
        ],
    ];

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
