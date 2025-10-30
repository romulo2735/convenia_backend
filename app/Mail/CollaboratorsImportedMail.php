<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CollaboratorsImportedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function build()
    {
        return $this->subject('Importação de colaboradores')
            ->view('emails.collaborators_imported')
            ->with(['messageText' => 'Processamento realizado com sucesso.']);
    }
}
