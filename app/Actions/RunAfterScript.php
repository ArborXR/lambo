<?php

namespace App\Actions;

use App\Shell;
use App\ConsoleWriter;
use Illuminate\Support\Facades\File;

class RunAfterScript
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
        $afterScriptPath = config('home_dir') . '/.lambo/after';
        if (!File::isFile($afterScriptPath)) {
            return;
        }

        $this->consoleWriter->logStep('Running after script');

        $export = [];
        foreach (config('lambo.store') as $configVariable => $configData) {
            if (in_array($configVariable, ['project_path', 'APP_NAMESPACE', 'database_name', 'project_name', 'project_url'])) {
                $export[] = 'export ' . strtoupper($configVariable) . '=' . $configData;
            }
        }
        $export[] = 'export DOCKER_CONTAINER_PREFIX=' . substr(config('lambo.store.project_name'), 0, strrpos(config('lambo.store.project_name'), '-'));
        $exports = implode(' && ', $export);

        $process = $this->shell->execInProject($exports . ' && sh ' . $afterScriptPath);
        if (!$process->isSuccessful()) {
            dump($process->getErrorOutput());
        }
        $this->abortIf(!$process->isSuccessful(), 'After file did not complete successfully', $process);

        $this->consoleWriter->success('After script has completed.');
    }
}
