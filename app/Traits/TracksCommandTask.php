<?php

namespace App\Traits;

use App\Modules\System\Models\Task;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait TracksCommandTask
{
    /**
     * Execute the console command.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $task = Task::create([
            'type' => $this->getName(),
            'status' => 'processing',
            'message' => 'Command started manually or via schedule',
            'user_id' => null,
        ]);

        try {
            $exitCode = parent::execute($input, $output);

            $task->update([
                'status' => $exitCode === 0 ? 'completed' : 'failed',
                'message' => $exitCode === 0 ? 'Command executed successfully' : 'Command failed with exit code ' . $exitCode,
                'progress' => 100,
                'completed_at' => now(),
            ]);

            return $exitCode;
        } catch (\Throwable $e) {
            $task->update([
                'status' => 'failed',
                'message' => 'Command failed: ' . $e->getMessage(),
                'completed_at' => now(),
            ]);

            throw $e;
        }
    }
}
