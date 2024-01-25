<?php

namespace App\Jobs;


use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessJsonDataJob implements ShouldQueue
{
    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function handle()
    {
        if ($this->data) {
            $tables = $this->data;
            if(isset($tables['users'])) foreach ($tables['users'] as $data) User::updateOrCreate($data);
        }
    }
}
