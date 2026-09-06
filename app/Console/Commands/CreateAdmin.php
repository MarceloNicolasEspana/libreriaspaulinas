<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateAdmin extends Command
{
    protected $signature = 'admin:create {email} {--name=Administrador}';

    protected $description = 'Crear una cuenta administradora con contraseña ingresada de forma oculta';

    public function handle(): int
    {
        if (! $this->input->isInteractive()) {
            $this->error('Ejecuta este comando de forma interactiva para ingresar la contraseña.');

            return self::FAILURE;
        }
        $password = $this->secret('Contraseña (mínimo 12 caracteres)');
        $confirmation = $this->secret('Repite la contraseña');
        $data = ['email' => $this->argument('email'), 'name' => $this->option('name'), 'password' => $password, 'password_confirmation' => $confirmation];
        $validator = Validator::make($data, ['name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'max:255', 'unique:users,email'], 'password' => ['required', 'confirmed', Password::min(12)]]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }
        $user = new User($validator->safe()->only(['name', 'email', 'password']));
        $user->is_admin = true;
        $user->save();
        $this->info('Administrador creado. Ingresa en /admin.');

        return self::SUCCESS;
    }
}
