<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class VerificarUsuarios extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'usuarios:verificar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verificar usuarios existentes en el sistema';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $usuarios = User::select('id', 'name', 'email', 'username', 'role', 'punto_venta_id')->get();

        $this->info('=== USUARIOS EXISTENTES ===');

        foreach ($usuarios as $usuario) {
            $this->line("ID: {$usuario->id} | {$usuario->name} | {$usuario->email} | Username: {$usuario->username} | Role: " . ($usuario->role ?? 'sin role') . " | Punto Venta: " . ($usuario->punto_venta_id ?? 'ninguno'));
        }

        $this->info("\nTotal de usuarios: " . $usuarios->count());
    }
}
