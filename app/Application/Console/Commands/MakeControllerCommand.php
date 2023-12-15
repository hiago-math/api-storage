<?php

namespace Application\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use function Nette\Utils\invokeMethod;

class MakeControllerCommand extends GeneratorCommand
{
    protected $signature = 'make:controller {domain} {name} {--invoke}';

    protected $description = "[DDD] Create a new domain controller";

    protected $type = 'Controller';

    private $invoke = false;

    private $className;

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        //$stub = $this->invoke ? 'controller.invokable' : 'controller.api';
        $stub = 'controller.invokable';

        return __DIR__ . '/stubs/' . $stub . '.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {

        // Obtenha o nome do domínio a partir do argumento do comando
        $domain = ucfirst($this->argument('domain'));

        // Construa o namespace do controlador com base no domínio
        return $rootNamespace . '\Application\Http\Controllers\\' . $domain;
    }

    protected function replaceNamespace(&$stub, $name)
    {
        $this->className = Str::afterLast($name, '\\');

        $stub = str_replace(
            ['DummyClass'],
            [str_replace($this->type, '', $this->className)],
            $stub
        );

        $name = str_replace('App\\', '', $name);

        return parent::replaceNamespace($stub, $name);
    }

    protected function getArguments()
    {
        // Adicione o argumento 'domain' ao comando
        return [
            ['domain', InputArgument::REQUIRED, 'The name of the domain.'],
            ['name', InputArgument::REQUIRED, 'The name of the class.'],
        ];
    }

    public function handle()
    {
        $this->invoke = $this->option('invoke');
        return parent::handle();
    }

    protected function getNameInput()
    {
        $name = parent::getNameInput();

        if (Str::contains($name, $this->type)) {
            $name = str_replace([$this->type, strtolower($this->type)], ['', ''], $name);
        }

        return str_replace($this->type . $this->type, $this->type, $name . $this->type);
    }
}
