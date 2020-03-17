<?php
/**
 * API Library for CTOS Version 2.
 * User: Mohd Nazrul Bin Mustaffa
 * Date: 26/04/2018
 * Time: 11:16 AM
 */
namespace MohdNazrul\CTOSV2Laravel;

use Illuminate\Support\Facades\Facade;


class CTOSeKYCApiFacade extends Facade
{
    protected static function getFacadeAccessor() { return 'CTOSeKYCApi'; }
}