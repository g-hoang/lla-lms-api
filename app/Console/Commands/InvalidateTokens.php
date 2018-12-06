<?php

namespace App\Console\Commands;

use App\Models\Learner;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Tymon\JWTAuth\Facades\JWTAuth;

class InvalidateTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invalidate:tokens';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Invalidate all user and learner tokens';


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Fetching all User tokens...');

        $tokens = User::where('latest_jwt_claims', '<>', null)
            ->select('latest_jwt_claims')
            ->get();

        $this->info('Total ' . count($tokens) . ' file(s) found for processing.');

        $bar = $this->output->createProgressBar(count($tokens));

        $count = 0;
        foreach ($tokens as $token) {
            try {
                JWTAuth::setToken($token->latest_jwt_claims);
                JWTAuth::invalidate();
                $count++;
            } catch (\Exception $e) {
            }
            $bar->advance();
        }

        $this->info('');
        $this->info($count . ' tokens invalidated!');

        $this->info('');
        $this->info('Fetching all Learner tokens...');

        $tokens = Learner::where('latest_jwt_claims', '<>', null)
            ->select('latest_jwt_claims')
            ->get();

        $this->info('Total ' . count($tokens) . ' file(s) found for processing.');

        $bar = $this->output->createProgressBar(count($tokens));

        $count = 0;
        foreach ($tokens as $token) {
            try {
                JWTAuth::setToken($token->latest_jwt_claims);
                JWTAuth::invalidate();
                $count++;
            } catch (\Exception $e) {
            }
            $bar->advance();
        }

        $this->info('');
        $this->info($count . ' tokens invalidated!');
    }
}
