<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Tareas programadas
|--------------------------------------------------------------------------
| Requieren que el servidor ejecute cada minuto:  php artisan schedule:run
| (cron en Linux; Programador de tareas en Windows). En desarrollo:
| php artisan schedule:work
*/

// Respaldo de la base de datos y de los PDFs privados (de madrugada).
Schedule::command('respaldo:crear')->dailyAt('02:00')->withoutOverlapping()->onOneServer();

// Vence solicitudes sin pago iniciado y borra códigos de verificación expirados.
Schedule::command('tramites:mantenimiento')->dailyAt('03:00')->withoutOverlapping();

// Limpieza de enlaces de recuperación de contraseña vencidos.
Schedule::command('auth:clear-resets')->daily();

// Conserva los trabajos de correo fallidos 30 días para poder revisarlos.
Schedule::command('queue:prune-failed --hours=720')->weekly();
