<?php

namespace App\Jobs;

use App\Imports\CollaboratorImport;
use App\Mail\CollaboratorsImportedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

class ImportCollaboratorsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $path;
    protected $user;

    public function __construct(string $path, $user)
    {
        $this->path = $path;
        $this->user = $user;
    }

    public function handle(): void
    {
        Excel::import(new CollaboratorImport($this->user->id), storage_path("app/private/imports/{$this->path}"));

        Mail::to($this->user->email)->send(new CollaboratorsImportedMail());
    }
}
