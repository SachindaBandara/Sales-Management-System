<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class CleanupInvoiceFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoice:cleanup {--hours=24 : Hours after which files should be deleted}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up temporary invoice files older than specified hours';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $hours = (int) $this->option('hours');
        $tempPath = storage_path('app/temp');

        if (!File::exists($tempPath)) {
            $this->info('Temp directory does not exist. Nothing to clean up.');
            return Command::SUCCESS;
        }

        $cutoffTime = now()->subHours($hours);
        $deletedCount = 0;
        $totalSize = 0;

        try {
            $files = File::files($tempPath);

            foreach ($files as $file) {
                $fileTime = File::lastModified($file);

                if ($fileTime < $cutoffTime->timestamp) {
                    $fileSize = File::size($file);
                    $totalSize += $fileSize;

                    if (File::delete($file)) {
                        $deletedCount++;
                        $this->line("Deleted: {$file->getFilename()} (" . $this->formatBytes($fileSize) . ")");
                    } else {
                        $this->error("Failed to delete: {$file->getFilename()}");
                    }
                }
            }

            if ($deletedCount > 0) {
                $this->info("Cleanup completed successfully!");
                $this->info("Files deleted: {$deletedCount}");
                $this->info("Space freed: " . $this->formatBytes($totalSize));

                Log::info('Invoice files cleanup completed', [
                    'files_deleted' => $deletedCount,
                    'space_freed' => $totalSize,
                    'hours_threshold' => $hours
                ]);
            } else {
                $this->info('No files found that are older than ' . $hours . ' hours.');
            }

        } catch (\Exception $e) {
            $this->error('Error during cleanup: ' . $e->getMessage());

            Log::error('Invoice files cleanup failed', [
                'error' => $e->getMessage(),
                'hours_threshold' => $hours
            ]);

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Format bytes to human readable format
     *
     * @param int $bytes
     * @return string
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.2f %s", $bytes / pow(1024, $factor), $units[$factor]);
    }
}
