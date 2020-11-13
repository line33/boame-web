<?php
namespace Moorexa\Guards;

use Lightroom\Router\{
    Guards\RouteGuard, Interfaces\GuardInterface,
    Interfaces\RouteGuardInterface
};
use function Lightroom\Requests\Functions\{session};

/**
 * @package Guard Authenticated
 * @author Amadi ifeanyi <amadiify.com>
 */
class Authenticated implements GuardInterface, RouteGuardInterface
{
    use RouteGuard;

    /**
     * @method GuardInterface guardInit
     * @return void
     * 
     * This method would be called when guard has been initialized. 
     */
    public function guardInit() : void
    {
        // check if authenticated
        $this->isAuthenticated();
    }

    /**
     * @method RouteGuardInterface getRedirectPath 
     * @return string
     * 
     * This method returns a redirect path
     **/
    public function getRedirectPath() : string 
    {
        return '';
    }

    /**
     * @method Authenticated isAuthenticated
     */
    private function isAuthenticated()
    {
        // check for session
        if (!session()->has('auth_token') || !session()->has('auth_user')) return $this->redirect('/');

        // load app
        app()->add('account', new class()
        {
            // @var int $accountid
            public $accountid = 0;

            // @var object $account 
            public $account;

            // load contructor
            public function __construct()
            {
                // @var object $auth
                $auth = session()->get('auth_user');

                // set accountid
                $this->accountid = $auth->accountid;

                // set account
                $this->account = $auth;
            }

            // set multiple
            public function set(array $data)
            {
                // set key => val
                foreach($data as $key => $val) $this->account->{$key} = $val;  

                // set globally
                session()->set('auth_user', $this->account);
            }

            // get account types
            public function accountTypes()
            {
                return [
                    1 => 'Administrator',
                    3 => 'Moderator'
                ];
            }
        });
    }

    /**
     * @method Authenticated checkAuthentication
     * Check if logged in already
     */
    public function checkAuthentication()
    {
        if (session()->has('auth_token') || session()->has('auth_user')) return $this->redirect('manager');
    }

    /**
     * @method Authenticated endSession
     * Unauthenticate a logged in user
     */
    public function endSession()
    {
        if (session()->has('auth_token') || session()->has('auth_user')) :

            // end session
            session()->dropMultiple('auth_token', 'auth_user', 'auth_nav');

            // redirect user
            $this->redirect('/');

        endif;
    }
}