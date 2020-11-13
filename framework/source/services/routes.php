<?php

use Lightroom\Packager\Moorexa\Router as Route;
use function Lightroom\Requests\Functions\{session};
/*
 ***************************
 * 
 * @ Route
 * info: Add your GET, POST, DELETE, PUT request handlers here. 
*/
Route::get('/logout', function(){
    Lightroom\Router\Guards::loadGuard(Moorexa\Guards\Authenticated::class, 'endSession');
});

Route::any('volunteer/{id}', ['id' => '[0-9]+'], function($id){
    return 'manager/volunteers/overview/' . $id; 
});

Route::any('feedback/{id}', ['id' => '[0-9]+'], function($id){
    return 'manager/feedbacks/overview/' . $id;
});

Route::any('counselor/{id}', ['id' => '[0-9]+'], function($id){
    return 'manager/counselors/overview/' . $id;
});

Route::any('counselor/edit/{id}', ['id' => '[0-9]+'], function($id){
    return 'manager/counselors/edit/' . $id;
});

Route::any('super-user/{id}', ['id' => '[0-9]+'], function($id){
    return 'manager/administrators/overview/' . $id;
});