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
            'label' => '👑 Administrador demo',
            'description' => 'Acceso total al panel admin',
            'email' => 'admin@visitatrack.test',
            'password' => 'password',
        ],
    ];

    public function authenticate(): void
    {
        $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $this->attemptLogin($this->email, $this->password);
    }

    public function loginAsDemo(string $email, string $password): void
    {
        $this->attemptLogin($email, $password);
    }

    protected function attemptLogin(string $email, string $password): void
    {
        if (! Auth::attempt(['email' => $email, 'password' => $password], $this->remember)) {
            $this->loginError = 'Email o contraseña incorrectos.';

            return;
        }

        request()->session()->regenerate();

        if (Auth::user()->role === 'admin') {
            $this->redirect('/admin', navigate: false);

            return;
        }

        $this->redirectRoute('portal.dashboard', navigate: true);
    }

    public function render()
    {
        return view('livewire.portal.login');
    }
}
