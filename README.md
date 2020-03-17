# CTOS eKYC API Package (Version 1)

This Library allows to query the CTOS eKYC  - B2B API for registered users. 

You need the access details that were provided to you to make any calls to the API.
For exact parameters in the data array, refer to your offline documentation.

If you do not know what all this is about, then you probably do not need or want this library.

# Configuration

## .env file

Configuration via the .env file currently allows the following variables to be set:

- CTOS\_EKYC\_URL='http://api.endpoint/url/'
- CTOS\_EKYC\_CIPHER=EncryptionMethod (e.g: aes-256-cbc)
- CTOS\_EKYC\_API\_KEY=Contact Xendity\/CTOS
- CTOS\_EKYC\_CIPHER\_TEXT=Contact Xendity\/CTOS
- CTOS\_EKYC\_PACKAGE\_NAME=Contact Xendity\/CTOS

## Available functions


**FOR LARAVEL SETUP CONFIGURATION:-**

- Do composer require mohdnazrul/laravel-ctos-ekyc
```php
   composer require mohdnazrul/laravel-ctos-ekyc
```
- Add this syntax inside config/app.php
```php
   ....
   'providers'=> [
     .
     MohdNazrul\CTOSEKYCLaravel\CTOSServiceProvider::class,
     .
   ],
   'aliases' => [
      .
      'CTOSV2' => MohdNazrul\CTOSEKYCLaravel\CTOSApiFacade::class,
      '
    ],
``` 
- Do publish as below
```php
php artisan vendor:publish --tag=ctos_ekyc 
```
- You can edit the default configuration CBM inside config/ctosv2.php based your account as below
```php
return [
    'serviceUrl'    =>  env('CTOS_URL','http://localhost'),
    'username'      =>  env('CTOS_USERNAME','username'),
    'password'      =>  env('CTOS_PASSWORD','password')
];
``` 







     
