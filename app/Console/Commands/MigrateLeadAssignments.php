<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Leads;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MigrateLeadAssignments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leads:migrate-assignments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate lead assignments from user names to user IDs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting lead assignment migration...');

        // Get all leads where 'assigned' is not a numeric ID
        $leads = Leads::all();
        $updatedCount = 0;
        $skippedCount = 0;

        foreach ($leads as $lead) {
            $assigned = $lead->assigned;

            if (empty($assigned)) {
                $skippedCount++;
                continue;
            }

            // If it's already an ID, skip
            if (is_numeric($assigned)) {
                $skippedCount++;
                continue;
            }

            // Try to find the user by name
            $user = User::where('name', $assigned)->first();

            if ($user) {
                $lead->assigned = $user->id;
                $lead->save();
                $updatedCount++;
                $this->line("Updated lead #{$lead->id}: '{$assigned}' -> ID {$user->id}");
            } else {
                $this->warn("User not found for lead #{$lead->id}: '{$assigned}'");
                $skippedCount++;
            }
        }

        $this->info("Migration completed!");
        $this->info("Updated: {$updatedCount}");
        $this->info("Skipped: {$skippedCount}");
    }
}
