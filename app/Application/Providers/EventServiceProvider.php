<?php

namespace Application\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\File;


class EventServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadListenersEvents();
        $this->loadSubscribers();
    }

    protected function loadSubscribers(): void
    {
        try {
            $subscribes = get_by_cache('subscribers_list', []);

            if (is_local() || is_console() || empty($subscribes)) {

                $subscribes = [];
                $separ = DIRECTORY_SEPARATOR;

                foreach (get_modules() as $modulo) {
                    foreach (get_ddd_modules_domains($modulo) as $domain) {
                        $dir = base_path("app{$separ}Modules{$separ}{$modulo}{$separ}Domain{$separ}{$domain}{$separ}Subscribers");
                        if (File::exists($dir)) {
                            foreach (File::files($dir) as $f) {
                                $classe = str_replace(
                                    [base_path('app'), '.php', '/'],
                                    ['', '', '\\'],
                                    $f->getPathname()
                                );

                                # removendo o primeiro caracter que sem é '\'
                                $subscribes[] = substr($classe, 1);
                            }
                        }
                    }
                }
            }
            save_in_cache('subscribers_list', $subscribes, 360);

            foreach ($subscribes as $subscriber) {
                $this->app['events']->subscribe($subscriber);
            }

        } catch (\Exception $e) {
            send_log(__METHOD__ . ':' . __LINE__ . ':' . $e->getMessage(), [], 'error', $e);
        }

    }

    protected function loadListenersEvents(): void
    {
        try {
            $events = get_by_cache('listeners_events_list', []);

            if (is_local() || is_console() || empty($events)) {

                $events = [];
                $separ = DIRECTORY_SEPARATOR;

                foreach (get_modules() as $modulo) {
                    foreach (get_ddd_modules_domains($modulo) as $domain) {
                        $dir = base_path("app{$separ}Modules{$separ}{$modulo}{$separ}Domain{$separ}{$domain}{$separ}Events");
                        if (File::exists($dir)) {
                            foreach (File::files($dir) as $f) {
                                $classe = str_replace(
                                    [base_path('app'), '.php', '/'],
                                    ['', '', '\\'],
                                    $f->getPathname()
                                );

                                # removendo o primeiro caracter que sem é '\'
                                $events[] = substr($classe, 1);
                            }
                        }
                    }
                }
            }
            save_in_cache('listeners_events_list', $events, 360);

            foreach ($events as $event) {
                foreach($event::listeners() as $listener) {
                    $this->app['events']->listen($event, $listener);
                }
            }

        } catch (\Exception $e) {
            send_log(__METHOD__ . ':' . __LINE__ . ':' . $e->getMessage(), [], 'error', $e);
        }

    }

}
