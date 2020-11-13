<?php
namespace Moorexa\Framework\Manager\Providers;

use Closure;
use Lightroom\Packager\Moorexa\Interfaces\ViewProviderInterface;
/**
 * @package Counselor View Page Provider
 * @author Moorexa <moorexa.com>
 */

class CounselorProvider implements ViewProviderInterface
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
     * @method CounselorProvider overview
     * @param int $accountid
     * @return mixed
     */
    public function overview($accountid)
    {
        // read info
        $info = $this->model->getAccountInformation($accountid);

        // are we good ??
        if ($info->status == 'error') $this->view->redir('manager/counselors');

        // render view
        $this->view->render('counselors/overview', ['data' => $info, 'info' => $this->model->getCasesAssignedToCounselor($accountid)]);
    }

    /**
     * @method CounselorProvider edit
     * @param int $accountid
     * @return mixed
     */
    public function edit($accountid)
    {
        // read info
        $info = $this->model->getAccountInformation($accountid);

        // are we good ??
        if ($info->status == 'error') $this->view->redir('manager/counselors');

        // render view
        $this->view->render('counselors/edit', ['data' => $info]);
    }
}