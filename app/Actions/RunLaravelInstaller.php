<?php

namespace App\Actions;

use App\ConsoleWriter;
use App\Shell;

class RunLaravelInstaller
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
        $this->consoleWriter->logStep('Running the Laravel installer');

        $process = $this->shell->execInRoot('composer create-project laravel/laravel ' . config('lambo.store.project_name'));
        if (!$process->isSuccessful()) {
            dump($process->getErrorOutput());
        }
        $this->abortIf(! $process->isSuccessful(), 'The laravel installer did not complete successfully.', $process);

        $this->consoleWriter->success($this->getFeedback());
    }

    public function extraOptions()
    {
        return sprintf(
            '%s%s',
            config('lambo.store.dev') ? ' --dev' : '',
            config('lambo.store.with_output') ? '' : ' --quiet'
        );
    }

    public function getFeedback(): string
    {
        return sprintf(
            "A new application '%s' has been created from the %s branch.",
            config('lambo.store.project_name'),
            config('lambo.store.dev') ? 'develop' : 'release'
        );
    }
}
