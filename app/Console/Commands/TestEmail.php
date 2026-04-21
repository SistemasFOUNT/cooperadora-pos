<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {email=admin@cooperadora.edu.ar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía un email de prueba para verificar la configuración';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        $this->info('🧪 Enviando email de prueba...');
        $this->info("📧 Destinatario: {$email}");
        $this->info("📤 Servidor: " . config('mail.mailers.smtp.host'));
        $this->info("🔐 Usuario: " . config('mail.mailers.smtp.username'));

        try {
            Mail::raw('¡Hola! Este es un email de prueba del FOUNT Contable. Si recibes este mensaje, la configuración de email funciona correctamente. 🎉', function ($message) use ($email) {
                $message->to($email)
                        ->subject('✅ Prueba de Configuración Email - FOUNT Contable')
                        ->from(config('mail.from.address'), config('mail.from.name'));
            });

            $this->info('✅ Email enviado correctamente!');
            $this->info('📬 Revisa la bandeja de entrada y spam del destinatario.');

        } catch (\Exception $e) {
            $this->error('❌ Error al enviar email: ' . $e->getMessage());
            $this->warn('🔧 Verifica la configuración de email en .env');
        }
    }
}
