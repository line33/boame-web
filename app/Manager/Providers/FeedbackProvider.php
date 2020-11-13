<?php
namespace Moorexa\Framework\Manager\Providers;

use Closure;
use Lightroom\Packager\Moorexa\Interfaces\ViewProviderInterface;
/**
 * @package Feedback View Page Provider
 * @author Moorexa <moorexa.com>
 */

class FeedbackProvider implements ViewProviderInterface
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
     * @method FeedbackProvider overview
     * @param int $feedbackid
     */
    public function overview($feedbackid)
    {
        // read info
        $info = $this->model->getFeedbackInformation($feedbackid);

        // are we good ??
        if ($info->status == 'error') $this->view->redir('manager/feedbacks');
         
        // render view
        $this->view->render('feedbacks/overvew', ['info' => $info->feedbacks, 'data' => $this->model->getFeedbackReplies($feedbackid)]);
    }
}