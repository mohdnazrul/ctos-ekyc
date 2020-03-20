<?php

namespace MohdNazrul\CTOSEKYCLaravel;

use Illuminate\Support\ServiceProvider;

class CTOSeKYCServiceProvider extends ServiceProvider
{
    protected $defer = true;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/ctos_ekyc.php' => config_path('ctos_ekyc.php'),
        ], 'ctosv2');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/ctos_ekyc.php','ctos_ekyc');

        //$url, $cipher, $api_key, $cipher_text, $package_name
        $this->app->singleton('CTOSeKYCApi', function ($app){
            $config     =   $app->make('config');
            $url   =   $config->get('ctosv2.username');
            $cipher   =   $config->get('ctosv2.password');
            $api_key =   $config->get('ctosv2.serviceUrl');
            $cipher_text =   $config->get('ctosv2.serviceUrl');
            $package_name =   $config->get('ctosv2.serviceUrl');

            return new CTOSeKYCApi($url, $cipher, $api_key, $cipher_text, $package_name);

        });
    }

    public function provides() {
        return ['CTOSeKYCApi'];
    }
}
