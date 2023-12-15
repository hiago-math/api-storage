<?php

namespace Application\Console;

use Application\Console\Commands\MakeControllerCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\File;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return array
     */
    protected function commands()
    {
        foreach (get_ddd_domains() as $domain) {
            $dir = base_path('app' . DIRECTORY_SEPARATOR . 'Domain' . DIRECTORY_SEPARATOR . $domain . DIRECTORY_SEPARATOR . 'Commands');
            $this->getFileByDir($dir);
        }

        $dir = base_path('app' . DIRECTORY_SEPARATOR . 'Application' . DIRECTORY_SEPARATOR . 'Console' . DIRECTORY_SEPARATOR . 'Commands');
        $this->getFileByDir($dir);
    }

    private function getFileByDir(string $dir)
    {
        if (File::exists($dir)) {
            foreach (File::files($dir) as $file) {
                $fileName = $file->getFilename();
                if (strtolower($fileName) != 'kernel.php') {
                    $class = str_replace(
                        [base_path('app'),  '.php', '/'],
                        ['', '', '\\'],
                        $file->getPathname()
                    );

                    $this->commands[] = substr($class, 1);
                }
            }
        }
    }
}
