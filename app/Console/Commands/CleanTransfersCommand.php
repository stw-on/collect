<?php


namespace App\Console\Commands;


use App\Models\Transfer;
use Illuminate\Console\Command;

class CleanTransfersCommand extends Command
{
    protected $signature = 'transfers:clean';

    protected $description = 'Clean expired and incomplete transfers';

    public function handle()
    {
        /** @var Transfer $transfer */
        foreach (Transfer::query()->cursor() as $transfer) {
            if ($transfer->expires_at->isPast() || (!$transfer->completed && $transfer->updated_at->addHour()->isPast())) {
                $this->line('Deleting transfer ' . $transfer->id);
                $transfer->delete();
            }
        }
    }
}
