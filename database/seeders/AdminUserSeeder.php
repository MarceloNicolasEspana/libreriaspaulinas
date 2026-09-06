<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Cuenta administradora para trabajar en local.
 *
 * La contraseña es deliberadamente corta para no estorbar durante el
 * desarrollo, y por eso el seeder no corre en producción: allí la única vía
 * para crear un administrador es "php artisan admin:create", que exige doce
 * caracteres y pide la contraseña de forma oculta.
 *
 * Usa updateOrCreate, así que repite las veces que haga falta: si la cuenta ya
 * existe le devuelve la contraseña conocida en vez de fallar por correo
 * duplicado. Eso también repara una fila cuya columna "password" haya quedado
 * con un valor que no es un hash.
 */
class AdminUserSeeder extends Seeder
{
    private const EMAIL = 'marcelonicolasespana@gmail.com';

    private const NAME = 'Marcelo España';

    private const PASSWORD = '123456';

    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->warn('AdminUserSeeder omitido: no se siembran credenciales fijas en producción.');

            return;
        }

        /*
         * La contraseña se asigna en claro: el cast "hashed" del modelo la
         * convierte una sola vez. is_admin va aparte porque no es asignable en
         * masa, para que un formulario no pueda concederse permisos.
         */
        $user = User::updateOrCreate(
            ['email' => self::EMAIL],
            ['name' => self::NAME, 'password' => self::PASSWORD],
        );

        $user->is_admin = true;
        $user->save();

        $this->command?->info('Administrador local: '.self::EMAIL.' / '.self::PASSWORD);
    }
}
