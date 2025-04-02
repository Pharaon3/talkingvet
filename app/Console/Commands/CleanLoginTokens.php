<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


//Purges logintokens that have been used or are older than 7 days and have not been used.
class CleanLoginTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-login-tokens';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purges logintokens that have been used or are older than 7 days and have not been used.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $deleted = DB::delete("DELETE FROM logintokens 
        WHERE updated_at NOT NULL OR (creation_date < DATE('now', '-7 days'))") ;    
        Log::info("**[DB MAINTENANCE]** - Purged " . $deleted . " stale login tokens");
    }
}
