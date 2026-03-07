<?php

namespace App\Console\Commands;

use App\Models\Coffret;
use App\Models\Equipement;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class BackfillQrTokens extends Command
{
    protected $signature = 'qr:backfill';

    protected $description = 'Backfill qr_token UUIDs for existing coffrets and equipements';

    public function handle(): int
    {
        $coffrets = Coffret::whereNull('qr_token')->get();
        $coffrets->each(fn (Coffret $c) => $c->update(['qr_token' => Str::uuid()->toString()]));
        $this->info("Backfilled {$coffrets->count()} coffret(s).");

        $equipements = Equipement::whereNull('qr_token')->get();
        $equipements->each(fn (Equipement $e) => $e->update(['qr_token' => Str::uuid()->toString()]));
        $this->info("Backfilled {$equipements->count()} equipement(s).");

        return self::SUCCESS;
    }
}
