<?php
namespace Moorexa\Framework\Manager\Providers;

use Closure;
use Lightroom\Packager\Moorexa\Interfaces\ViewProviderInterface;
/**
 * @package SuperUser View Page Provider
 * @author Moorexa <moorexa.com>
 */

class SuperUserProvider implements ViewProviderInterface
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
     * @method SuperUserProvider overview
     * @param int $accountid
     */
    public function overview($accountid)
    {
        // read info
        $info = $this->model->getAccountInformation($accountid);

        // are we good ??
        if ($info->status == 'error') $this->view->redir('manager/administrators');

        // render view
        $this->view->render('super-user/overview', ['data' => $info]);
    }
}