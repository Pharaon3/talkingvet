<?php

namespace App\Console\Commands;

use App\Models\RefreshToken;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Laravel\Sanctum\PersonalAccessToken;

class CleanupExpiredTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cleanup-expired-tokens';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete expired refresh tokens and Sanctum access tokens';

    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Execute the console command.
     */
    public function handle()
    {
        RefreshToken::where('expires_at', '<', Carbon::now())->delete();

        // Delete expired Sanctum tokens (if using expiration, otherwise they do not delete automatically)
        PersonalAccessToken::where('created_at', '<', Carbon::now()->subMinutes(config('sanctum.expiration')))->delete();

        $this->info('Expired tokens have been cleaned up.');
    }
}
