<?php
namespace Moorexa\Framework\App\Providers;

use Closure;
use function Lightroom\Requests\Functions\{session};
use Lightroom\Packager\Moorexa\Interfaces\ViewProviderInterface;
/**
 * @package Password View Page Provider
 * @author Moorexa <moorexa.com>
 */

class PasswordProvider implements ViewProviderInterface
{
    /**
     * @method ViewProviderInterface setArguments
     * @param array $arguments
     * 
     * This method sets the view arguments
     */
    public function setArguments(array $arguments) : void {}

    /**
     * @method ViewProviderInterface viewWillEnter
     * @param Closure $next
     * 
     * This method would be called before rendering view
     */
    public function viewWillEnter(Closure $next) : void
    {
        // route passed
        $next();
    }

    // try resending the password reset code.
    public function retry()
    {
        if (!session()->has('retry_mail_time') || 
            (session()->has('retry_mail_time') && (session()->get('retry_mail_time') < time()))) :

            // make request
            $this->controller->model->retrySendingMail(session()->get('tmp_storage_password_reset'));

            // set time
            session()->set('retry_mail_time', strtotime('+1 minute'));

        else:

            // use default
            event()->emit('ev', 'alert', 'Sorry, you can retry after 1 minute. Thank you for your patience.');

        endif;

        // render view
        $this->controller->view->render('completepasswordreset');
    }
}