<?php
namespace Moorexa\Framework\Manager\Providers;

use Closure;
use function Lightroom\Requests\Functions\{get};
use Lightroom\Packager\Moorexa\Interfaces\ViewProviderInterface;
/**
 * @package Volunteer View Page Provider
 * @author Moorexa <moorexa.com>
 */

class VolunteerProvider implements ViewProviderInterface
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

    /**
     * @method VolunteerProvider overview
     * @param int $accountid
     */
    public function overview($accountid)
    {
        // read info
        $info = $this->model->getVolunteerInformation($accountid);

        // are we good ??
        if ($info->status == 'error') $this->view->redir('manager/volunteers');
         
        // render view
        $this->view->render('volunteers/overvew', ['info' => $info->records]);
    }
}