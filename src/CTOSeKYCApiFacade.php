<?php
namespace MohdNazrul\CTOSEKYCLaravel;

use Illuminate\Support\Facades\Facade;


class CTOSeKYCApiFacade extends Facade
{
    protected static function getFacadeAccessor() { return 'CTOSeKYCApi'; }
}