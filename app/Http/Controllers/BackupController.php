<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class BackupController extends Controller
{
    public function backupDatabase()
    {
        $dbName = env('DB_DATABASE');
        $dbUser = env('DB_USERNAME');
        $dbPass = env('DB_PASSWORD');
        $dbHost = env('DB_HOST');
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $filePath = storage_path("app/backups/backup_{$timestamp}.sql");
        Storage::makeDirectory('backups');
        $mysqldumpPath = env('MYSQLDUMP_PATH', 'mysqldump'); 
        $command = "{$mysqldumpPath} --user={$dbUser} --password={$dbPass} --host={$dbHost} {$dbName} > {$filePath}";

        $process = Process::fromShellCommandline($command);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return response()->download($filePath)->deleteFileAfterSend(true);
    }
}
