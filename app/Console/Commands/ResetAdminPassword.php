<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

/**
 * Cambia la contraseña de una cuenta existente.
 *
 * Complementa a admin:create, que solo sirve para altas: su regla "unique"
 * impide reutilizarlo sobre una cuenta ya creada. Sin este comando, recuperar
 * el acceso obliga a escribir el hash a mano, y ahí es fácil terminar guardando
 * en la columna algo que no es un hash: la contraseña en claro, o un valor
 * mutilado por la shell al interpretar los "$" de bcrypt.
 */
class ResetAdminPassword extends Command
{
    protected $signature = 'admin:password {email}';

    protected $description = 'Cambiar la contraseña de una cuenta ingresándola de forma oculta';

    public function handle(): int
    {
        if (! $this->input->isInteractive()) {
            $this->error('Ejecuta este comando de forma interactiva para ingresar la contraseña.');

            return self::FAILURE;
        }

        $user = User::query()->where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error("No existe una cuenta con el correo {$this->argument('email')}.");

            return self::FAILURE;
        }

        $password = $this->secret('Contraseña nueva (mínimo 12 caracteres)');
        $confirmation = $this->secret('Repite la contraseña');

        $validator = Validator::make(
            ['password' => $password, 'password_confirmation' => $confirmation],
            ['password' => ['required', 'confirmed', Password::min(12)]],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        /*
         * Se asigna en claro a propósito: el cast "hashed" del modelo la hashea
         * una sola vez. Hashearla aquí sería redundante.
         */
        $user->password = $password;
        $user->save();

        $this->info("Contraseña actualizada para {$user->email}.");

        return self::SUCCESS;
    }
}
