<?php

namespace App;

use Illuminate\Support\ServiceProvider;

class BackendServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->app->bind(
            'App\repositories\InputBongkaranInterface',
            'App\repositories\InputBongkaranRepository',
        );

        $this->app->bind(
            'App\repositories\BongkaranInterface',
            'App\repositories\BongkaranRepository'
        );

        $this->app->bind(
            'App\repositories\TransferBongkaranInterface',
            'App\repositories\TransferBongkaranRepository'
        );

        $this->app->bind(
            'App\repositories\LaporanInterface',
            'App\repositories\LaporanRepository'
        );

        $this->app->bind(
            'App\repositories\DataLaporanInterface',
            'App\repositories\DataLaporanRepository'
        );

        $this->app->bind(
            'App\repositories\PolInterface',
            'App\repositories\PolRepository'
        );

        $this->app->bind(
            'App\repositories\ProsesInterface',
            'App\repositories\ProsesRepository'
        );

        $this->app->bind(
            'App\repositories\LotInterface',
            'App\repositories\LotRepository'
        );

        $this->app->bind(
            'App\repositories\CounterInterface',
            'App\repositories\CounterRepository'
        );

        $this->app->bind(
            'App\repositories\TransferInterface',
            'App\repositories\TransferRepository'
        );
    }
}
