<?php

namespace App\Listeners;

use App\Events\PasswordResetedEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class PasswordResetedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param  PasswordResetedEvent  $event
     * @return void
     */
    public function handle(PasswordResetedEvent $event)
    {
        $curl = curl_init();
        $body = urlencode('Reseteo de contraseña \"A Tu Casa\" Su nueva contraseña es:');
        $title = urlencode('Cambio de pass');
        curl_setopt($curl, CURLOPT_URL, 'http://idealo.pe/test.php?title='.$title.'&email='.$event->user->email.'&message='.$body.$event->user->password);
        // 
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true );
        
        $result = curl_exec($curl);
        curl_close($curl);
    }
}