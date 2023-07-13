<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.
// $routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/', 'Home::index');

$routes->group('api', ['namespace' => 'App\API\v1'], static function ($routes) {    
    $routes->post('login',[\App\Controllers\CustomerAuthController::class, 'loginMobile']);
    $routes->post('refreshtoken',[\App\Controllers\JWTController::class, 'refreshToken']);
    $routes->post('registercustomer',[\App\Controllers\CustomerAuthController::class, 'registerCustomer']);    
    $routes->post('registerdriver',[\App\Controllers\DriverAuthController::class, 'registerDriver']);    
    $routes->post('driverlogin',[\App\Controllers\DriverAuthController::class, 'loginMobile']);

    $routes->get('page/(:any)',[\App\Controllers\PageController::class, 'page']);

    // customer
    $routes->group('customer', static function ($routes) {
        $routes->post('update/profile',[\App\Controllers\CustomerAuthController::class, 'updateProfile'],['filter' => 'authFilter']);
        $routes->post('update/profile-image',[\App\Controllers\CustomerAuthController::class, 'updateProfilePic'],['filter' => 'authFilter']);
        $routes->get('faq', [\App\Controllers\CustomerAuthController::class, 'getCustomerFaq'],['filter' => 'authFilter']);
         $routes->get('list/complains', [\App\Controllers\CustomerAuthController::class, 'getCustomerComplains'],['filter' => 'authFilter']);
         $routes->post('add/customercancelreason', [\App\Controllers\CustomerAuthController::class, 'addCustomerCancelReason'],['filter' => 'authFilter']);
    });

    // driver operations routes
    $routes->group('driver', static function ($routes) {    
        $routes->post('update/profile',[\App\Controllers\DriverAuthController::class, 'updateProfile'],['filter' => 'authFilter']);
        $routes->post('update/profile-image',[\App\Controllers\DriverAuthController::class, 'updateProfilePic'],['filter' => 'authFilter']);

        $routes->post('update/basicinfo',[\App\Controllers\DriverAuthController::class, 'updateBasicInformation'],['filter' => 'authFilter']);
        $routes->post('update/vehicleinfo',[\App\Controllers\DriverAuthController::class, 'updateVehicleInformation'],['filter' => 'authFilter']);    
        $routes->post('update/referalcode',[\App\Controllers\DriverAuthController::class, 'updateReferalCode'],['filter' => 'authFilter']);    
        $routes->post('update/selfidetails',[\App\Controllers\DriverAuthController::class, 'updateSelfiDetails'],['filter' => 'authFilter']);    
        $routes->post('update/drivinglicense',[\App\Controllers\DriverAuthController::class, 'updateDrivingLicense'],['filter' => 'authFilter']);            
        $routes->post('update/registercertificate',[\App\Controllers\DriverAuthController::class, 'updateRegisterCertificate'],['filter' => 'authFilter']);            
        
        $routes->get('list/vehiclecategory', [\App\Controllers\DriverAuthController::class, 'getAllVehicleCategories'],['filter' => 'authFilter']);
        $routes->get('list/vehiclebrand', [\App\Controllers\DriverAuthController::class, 'getAllVehicleBrand'],['filter' => 'authFilter']);
        $routes->get('list/vehiclemodel/(:any)', [\App\Controllers\DriverAuthController::class, 'getAllVehicleModel'],['filter' => 'authFilter']);
        $routes->get('list/vehiclecolor', [\App\Controllers\DriverAuthController::class, 'getAllVehicleColor'],['filter' => 'authFilter']);
        $routes->get('faq', [\App\Controllers\DriverAuthController::class, 'getDriverFaq'],['filter' => 'authFilter']);
        $routes->get('list/cancelreason', [\App\Controllers\DriverAuthController::class, 'getAllCancelReason'],['filter' => 'authFilter']);
        $routes->get('list/drivercomplains', [\App\Controllers\DriverAuthController::class, 'getAllDriverComplain'],['filter' => 'authFilter']);
        

        $routes->post('add/drivercancelreason', [\App\Controllers\DriverAuthController::class, 'addDriverCancelReason'],['filter' => 'authFilter']);
      
        
    
    });
    
});

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}


