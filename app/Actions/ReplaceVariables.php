<?php

namespace App\Actions;

use App\ConsoleWriter;
use App\Shell;
use Illuminate\Support\Facades\File;

class ReplaceVariables
{
    use AbortsCommands;

    protected $shell;
    protected $consoleWriter;

    public function __construct(Shell $shell, ConsoleWriter $consoleWriter)
    {
        $this->shell = $shell;
        $this->consoleWriter = $consoleWriter;
    }

    public function __invoke()
    {
        $homePath = config('home_dir') . '/.lambo';
        $projectPath = config('lambo.store.project_path');
        $appNamespace = config('lambo.store.APP_NAMESPACE');
        $dbName = config('lambo.store.DATABASE_NAME');

        $paths = ['app', 'bootstrap', 'config'];

        $this->consoleWriter->logStep('Copying Configuration Files');

        $process = $this->shell->execInProject('cp '.$homePath.'/bootstrap/*.php '.$projectPath.'/bootstrap/');
        if (!$process->isSuccessful()) {
            dump($process->getErrorOutput());
        }
        $this->abortIf(! $process->isSuccessful(), 'Replace variables did not complete successfully', $process);

        $process = $this->shell->execInProject('cp '.$homePath.'/config/*.php '.$projectPath.'/config/');
        if (!$process->isSuccessful()) {
            dump($process->getErrorOutput());
        }
        $this->abortIf(! $process->isSuccessful(), 'Replace variables did not complete successfully', $process);

        $this->consoleWriter->logStep('Running replace variables script');

        foreach ($paths as $path) {
            $process = $this->shell->execInProject('find ./'.$path.'/ -type f -name "*.php" -print0 | xargs -0 sed -i "s/App\/'.$appNamespace.'\/g"');
            if (!$process->isSuccessful()) {
                dump($process->getErrorOutput());
            }
            $this->abortIf(! $process->isSuccessful(), 'Replace variables did not complete successfully', $process);

            $process = $this->shell->execInProject('find ./'.$path.'/ -type f -name "*.php" -print0 | xargs -0 sed -i "s/{{ APP_NAMESPACE }}/'.$appNamespace.'/g"');
            if (!$process->isSuccessful()) {
                dump($process->getErrorOutput());
            }
            $this->abortIf(! $process->isSuccessful(), 'Replace variables did not complete successfully', $process);

            $process = $this->shell->execInProject('find ./'.$path.'/ -type f -name "*.php" -print0 | xargs -0 sed -i "s/{{ DATABASE_NAME }}/'.$dbName.'/g"');
            if (!$process->isSuccessful()) {
                dump($process->getErrorOutput());
            }
            $this->abortIf(! $process->isSuccessful(), 'Replace variables did not complete successfully', $process);
        }

        $this->consoleWriter->success('Replace variables has completed.');
    }
}
