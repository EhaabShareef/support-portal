<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateLatestMessageAt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickets:update-latest-message-at';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update latest_message_at field for all tickets based on their most recent message';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating latest_message_at for all tickets...');
        
        $tickets = Ticket::whereNull('latest_message_at')
            ->orWhereNull('latest_message_at')
            ->get();
        
        $bar = $this->output->createProgressBar($tickets->count());
        $updated = 0;
        
        foreach ($tickets as $ticket) {
            $latestMessage = $ticket->messages()
                ->latest('created_at')
                ->first();
                
            if ($latestMessage) {
                $ticket->update([
                    'latest_message_at' => $latestMessage->created_at
                ]);
                $updated++;
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        
        $this->info("Updated {$updated} tickets with latest message timestamps.");
        
        return Command::SUCCESS;
    }
}
