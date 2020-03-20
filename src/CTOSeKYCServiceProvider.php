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
        ], 'ctos_ekyc');
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
            $url   =   $config->get('ctos_ekyc.CTOS_EKYC_URL');
            $cipher   =   $config->get('ctos_ekyc.CTOS_EKYC_CIPHER');
            $api_key =   $config->get('ctos_ekyc.CTOS_EKYC_API_KEY');
            $cipher_text =   $config->get('ctos_ekyc.CTOS_EKYC_CIPHER_TEXT');
            $package_name =   $config->get('ctos_ekyc.CTOS_EKYC_PACKAGE_NAME');
            $md5_key =   $config->get('ctos_ekyc.CTOS_EKYC_MD5_KEY');

            return new CTOSeKYCApi($url, $cipher, $api_key, $cipher_text, $package_name, $md5_key);

        });
    }

    public function provides() {
        return ['CTOSeKYCApi'];
    }
}
