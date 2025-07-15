<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response; // Opcional, para indicar um código HTTP padrão

class UnauthorizedReversalException extends Exception
{
    /**
     * Report the exception.
     *
     * @return void
     */
    public function report()
    {
        // Você pode logar a exceção aqui, se quiser um log específico para ela
        // \Log::warning('Tentativa de reversão não autorizada: ' . $this->getMessage());
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function render($request) {}
}
