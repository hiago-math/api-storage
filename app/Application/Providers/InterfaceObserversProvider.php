<?php

namespace Application\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class InterfaceObserversProvider extends ServiceProvider
{
    private array $folders = [];

    public function boot()
    {
        $this->loadingInterfaces();
    }

    private function loadingObservers()
    {
        //
    }

    /**
     * @return void
     */
    private function loadingInterfaces(): void
    {
        $this->prepareDddApis();
        $this->prepareDddDomains();

        foreach (remove_values_null($this->folders) as $class) {
            $this->app->bind(
                Arr::get($class, 'abstract'),
                Arr::get($class, 'concrete')
            );
        }
    }

    /**
     * @return void
     */
    private function prepareDddApis(): void
    {
        $dirInfrastructure = base_path("app" . DIRECTORY_SEPARATOR . "Infrastructure/Apis" . DIRECTORY_SEPARATOR . 'change_infrastructure' . DIRECTORY_SEPARATOR . "Interfaces" . DIRECTORY_SEPARATOR);

        foreach (get_ddd_infrastructure_apis() as $infrastructure_api) {
            $dirIServices = str_replace('change_infrastructure', $infrastructure_api, $dirInfrastructure);

            $this->carregarArquivos($dirIServices, $infrastructure_api);
        }
    }

    /**
     * @return void
     */
    private function prepareDddDomains(): void
    {
        $dirDomains = base_path("app" . DIRECTORY_SEPARATOR . "Domain" . DIRECTORY_SEPARATOR . 'change_domains' . DIRECTORY_SEPARATOR . "Interfaces" . DIRECTORY_SEPARATOR);

        foreach (get_ddd_domains() as $domain) {

            $dirIServices = str_replace('change_domains', $domain, $dirDomains) . "Services";
            $dirIRepositorios = str_replace('change_domains', $domain, $dirDomains) . "Repositories";

            $this->carregarArquivos($dirIServices, $domain);
            $this->carregarArquivos($dirIRepositorios, $domain);
        }
    }

    /**
     * @param string $dirInterfaces
     * @param string $domain
     * @return void
     */
    private function carregarArquivos(string $dirInterfaces, string $domain): void
    {
        if (File::exists($dirInterfaces)) {
            foreach (File::files($dirInterfaces) as $file) {
                $abstract = str_replace(['.php'], '', $file->getFilename());
                $concrete = str_replace(['.php'], '', substr($file->getFilename(), 1));

                $bind = null;
                switch ($this->identifyTipo($abstract)) {
                    case 'srv':
                        $file = base_path('app' . DIRECTORY_SEPARATOR . 'Domain' . DIRECTORY_SEPARATOR . $domain . DIRECTORY_SEPARATOR . 'Services' . DIRECTORY_SEPARATOR . $concrete . '.php');

                        if (File::isFile($file)) {
                            $bind = "Domain\\$domain\\Services\\$concrete";
                            $this->folders[] = [
                                'abstract' => "Domain\\" . $domain . "\\Interfaces\Services\\" . $abstract,
                                'concrete' => $bind
                            ];
                        }
                        break;

                    case 'repo':
                        $file = base_path('app' . DIRECTORY_SEPARATOR . 'Infrastructure/Repositories' . DIRECTORY_SEPARATOR . $domain . DIRECTORY_SEPARATOR . $concrete . '.php');

                        if (File::isFile($file)) {
                            $bind = "Infrastructure\Repositories\\$domain\\$concrete";
                            $this->folders[] = [
                                'abstract' => "Domain\\" . $domain . "\\Interfaces\Repositories\\" . $abstract,
                                'concrete' => $bind
                            ];
                        }
                        break;
                    case 'api':
                        $file = base_path('app' . DIRECTORY_SEPARATOR . 'Infrastructure/Apis' . DIRECTORY_SEPARATOR . $domain . DIRECTORY_SEPARATOR . 'Services' . DIRECTORY_SEPARATOR . $concrete . '.php');
                        if (File::isFile($file)) {
                            $bind = "Infrastructure\\Apis\\$domain\\Services\\$concrete";
                            $this->folders[] = [
                                'abstract' => "Infrastructure\\Apis\\" . $domain . "\\Interfaces\\" . $abstract,
                                'concrete' => $bind
                            ];
                        }
                        break;
                }

            }
        }
    }

    /**
     * @param string $abstract
     * @return string|null
     */
    private function identifyTipo(string $abstract): ?string
    {
        switch (Str::afterLast(Str::snake($abstract, '\\'), '\\')) {
            case 'repository':
                return 'repo';
            case 'service':
                return 'srv';
            case 'api':
                return 'api';
            default:
                return null;
        }
    }
}

