<?php
namespace Moorexa\Framework;

use Lightroom\Packager\Moorexa\MVC\Controller;
use Moorexa\Framework\App\Providers\PasswordProvider;
use function Lightroom\Requests\Functions\{session};
use function Lightroom\Templates\Functions\{render, redirect, json, view};
/**
 * Documentation for App Page can be found in App/readme.txt
 *
 *@package      App Page
 *@author       Moorexa <www.moorexa.com>
 *@author       Amadi Ifeanyi <amadiify.com>
 **/

class App extends Controller
{
    /**
    * @method App home
    *
    * See documentation https://www.moorexa.com/doc/controller
    *
    * You can catch params sent through the $_GET request
    * @return void
    **/

    public function home() : void 
    {
        // get class
        $redirect = redirect(); 

        // check and get message
        if ($redirect->has('data')) event('ev')->emit('alert', $redirect->data->message, $redirect->data->status);

        // render view
        $this->view->render('home');
    }

    /**
    * @method App resetPassword
    *
    * See documentation https://www.moorexa.com/doc/controller
    *
    * You can catch params sent through the $_GET request
    * @return void
    **/
    public function resetPassword() : void
    {
        // read model
        $response = $this->model->get('resetPassword.return');

        // redirect to complete password reset.
        if (is_object($response)) $this->view->redir('complete-password-reset', ['data' => $response]);

        // render view
        $this->view->render('resetpassword');
    }

    /**
    * @method App completePasswordReset
    *
    * See documentation https://www.moorexa.com/doc/controller
    *
    * You can catch params sent through the $_GET request
    * @return void
    **/
    public function completePasswordReset(PasswordProvider $provider) : void
    {
        // get class
        $redirect = redirect(); 

        // check for data
        if ($redirect->has('data')) :

            // set to tmp session
            session()->set('tmp_storage_password_reset', $redirect->data);

            // show the success message
            event()->emit('ev', 'alert', $redirect->data->message, 'success');

        endif;

        // check the model
        $response = $this->model->get('completeResetPassword.return');

        // do we have something??
        if (is_object($response)) $this->view->redir('app', ['data' => $response]);

        // force redirect
        if (!session()->has('tmp_storage_password_reset')) $this->view->redir('reset-password');

        // render view
        $this->view->render('completepasswordreset');
    }

    /**
    * @method App privacy
    *
    * See documentation https://www.moorexa.com/doc/controller
    *
    * You can catch params sent through the $_GET request
    * @return void
    **/
    public function privacy() : void
    {
        $this->view->render('privacy');
    }
}
// END class