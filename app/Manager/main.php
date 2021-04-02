<?php
namespace Moorexa\Framework;

use function Lightroom\Database\Functions\{db};
use Moorexa\Framework\Models\{Cases, Account, Library};
use Lightroom\Packager\Moorexa\Helpers\Assets;
use Lightroom\Packager\Moorexa\MVC\Controller;
use Moorexa\Framework\Manager\Providers\{
    LibraryProvider, VolunteerProvider, FeedbackProvider, CounselorProvider,
    SuperUserProvider
};
use function Lightroom\Requests\Functions\{session, get};
use function Lightroom\Templates\Functions\{render, redirect, json, view};
/**
 * Documentation for Manager Page can be found in Manager/readme.txt
 *
 *@package      Manager Page
 *@author       Moorexa <www.moorexa.com>
 *@author       Amadi Ifeanyi <amadiify.com>
 **/

class Manager extends Controller
{
    /**
    * @method Manager home
    *
    * See documentation https://www.moorexa.com/doc/controller
    *
    * You can catch params sent through the $_GET request
    * @return void
    **/

    public function home() : void 
    {
        // include chartjs
        view()->requireJs(
            MANAGER_STATIC . '/chart.js',
            MANAGER_STATIC . '/home.js'
        )
        ->requireCss(
            MANAGER_STATIC . '/chart.css'
        );

        // exports usage
        Assets::exportVars(['usage' => $this->model->getAppUsage()]);

        // render view
        $this->view->render('dashboard');
    }

    /**
    * @method Manager cases
    *
    * See documentation https://www.moorexa.com/doc/controller
    *
    * You can catch params sent through the $_GET request
    * @return void
    **/
    public function cases(Cases $model) : void
    {
        // export variables
        Assets::exportVars([
            'config' => [
                'headers'   => $_ENV['httpClient']['headers'],
                'endpoint'  => $_ENV['httpClient']['endpoint'],
                'images'    => [
                    'play'  => assets_image('play-button.svg'),
                    'view'  => assets_image('view.svg'),
                    'pause' => assets_image('pause.svg'),
                ],
                'storage'   => session()->get('configuration')->storage_url,
                'url'       => func()->url(),
            ]
        ]);

        // load gateway js
        view()->requireJs(MANAGER_STATIC . '/gateway.js');

        // render view
        $this->view->render('cases', [
            'overview' => $model->getOverview(),
        ]);
    }

    /**
    * @method Manager volunteers
    *
    * See documentation https://www.moorexa.com/doc/controller
    *
    * You can catch params sent through the $_GET request
    * @return void
    **/
    public function volunteers(VolunteerProvider $provider) : void
    {
        $this->view->render('volunteers', ['response' => $this->model->getVolunteers()]);
    }

    /**
    * @method Manager reporters
    *
    * See documentation https://www.moorexa.com/doc/controller
    *
    * You can catch params sent through the $_GET request
    * @return void
    **/
    public function reporters() : void
    {
        // get reporters
        $reporters = $this->model->getReporters();

        // render view
        $this->view->render('reporters', ['reporters' => $reporters]);
    }

    /**
    * @method Manager counselors
    *
    * See documentation https://www.moorexa.com/doc/controller
    *
    * You can catch params sent through the $_GET request
    * @return void
    **/
    public function counselors(CounselorProvider $provider) : void
    {
        $this->view->render('counselors', ['data' => $this->model->getCounselors()]);
    }

    /**
    * @method Manager administrators
    *
    * See documentation https://www.moorexa.com/doc/controller
    *
    * You can catch params sent through the $_GET request
    * @return void
    **/
    public function administrators(SuperUserProvider $provider) : void
    {
        $admin = $this->model->getAdministratorsAndModerators();

        $this->view->render('administrators', ['data' => $admin]);

        //var_dump($admin);
    }

    /**
    * @method Manager statistics
    *
    * See documentation https://www.moorexa.com/doc/controller
    *
    * You can catch params sent through the $_GET request
    * @return void
    **/
    public function statistics() : void
    {
        // render view
        $this->view->render('statistics', ['data' => $this->model->getOveralStatitics()]);
    }

    /**
    * @method Manager library
    *
    * See documentation https://www.moorexa.com/doc/controller
    *
    * You can catch params sent through the $_GET request
    * @return void
    **/
    public function library(LibraryProvider $provider, Library $model) : void
    {
        // render view
        $this->view->render('library', [
            'stats'     => $this->model->getLibraryStatistics(),
            'articles'  => $this->model->getAllArticles(),
            'videos'    => $this->model->getAllVideos()
        ]);
    }

    /**
    * @method Manager feedbacks
    *
    * See documentation https://www.moorexa.com/doc/controller
    *
    * You can catch params sent through the $_GET request
    * @return void
    **/
    public function feedbacks(FeedbackProvider $provider) : void
    {
        $this->view->render('feedbacks', ['data' => $this->model->getFeedbacks()]);
    }

    /**
    * @method Manager profile
    *
    * See documentation https://www.moorexa.com/doc/controller
    *
    * You can catch params sent through the $_GET request
    * @return void
    **/
    public function profile() : void
    {
        // update user profile
        
        // render view
        $this->view->render('profile', [
            'accountType' => session()->get('auth_user')->accountType->account_type
        ]);
    }

    /**
    * @method Manager accountOverview
    *
    * See documentation https://www.moorexa.com/doc/controller
    *
    * You can catch params sent through the $_GET request
    * @return void
    **/
    public function accountOverview($accountid) : void
    {
        // do we have something
        $param = filter(['id' => $accountid], ['id' => 'number|required|min:1']);

        // get account info
        $account = $this->model->getAccountInformation($accountid);

        // check accountid 
        if (!$param->isOk() || $account->status == 'error') $this->view->redir('manager');

        // export variables
        Assets::exportVars([
            'config' => [
                'headers'   => array_merge($_ENV['httpClient']['headers'], ['x-requestid' => $accountid]),
                'endpoint'  => $_ENV['httpClient']['endpoint'],
                'images'    => [
                    'play'  => assets_image('play-button.svg'),
                    'view'  => assets_image('view.svg'),
                    'pause' => assets_image('pause.svg'),
                ],
                'storage'   => session()->get('configuration')->storage_url,
                'url'       => func()->url(),
            ]
        ]);

        // load gateway js
        view()->requireJs(MANAGER_STATIC . '/gateway.js');
        
        // render view
        $this->view->render('accountoverview', [
            'account' => $account->account, 
            'history' => (get()->has('history') ? func()->url(get()->history) : func()->url('manager/cases'))
        ]);
    }

    /**
    * @method Manager audioCase
    *
    * See documentation https://www.moorexa.com/doc/controller
    *
    * You can catch params sent through the $_GET request
    * @return void
    **/
    public function audioCase($casesReportedID, Cases $model, Account $account) : void
    {
        $this->loadCaseView('audiocase', $casesReportedID, $model, $account, 'audio');
    }

    /**
    * @method Manager videoCase
    *
    * See documentation https://www.moorexa.com/doc/controller
    *
    * You can catch params sent through the $_GET request
    * @return void
    **/
    public function videoCase($casesReportedID, Cases $model, Account $account) : void
    {
        $this->loadCaseView('video-case', $casesReportedID, $model, $account, 'video');
    }

    /**
    * @method Manager textCase
    *
    * See documentation https://www.moorexa.com/doc/controller
    *
    * You can catch params sent through the $_GET request
    * @return void
    **/
    public function textCase($casesReportedID, Cases $model, Account $account) : void
    {
        $this->loadCaseView('textcase', $casesReportedID, $model, $account, 'text');
    }

    /**
     * @method Manager loadCaseView
     * @param string $render 
     * @param int $casesReportedID
     */
    private function loadCaseView(string $render, $casesReportedID, Cases $model, Account $account, string $caseType)
    {
        // do we have something
        $param = filter(['id' => $casesReportedID], ['id' => 'number|required|min:1']);

        // get case reported info
        $caseReported = $model->getSingleCase($casesReportedID, $caseType);

        // check case reported id 
        if (!$param->isOk() || $caseReported->status == 'error') $this->view->redir('manager/cases');

        // render view
        $this->view->render($render, [
            'case'      => $caseReported->cases,
            'accounts'  => $account->getCounselorsAndVolunteers()
        ]);
    }

    /**
    * @method Manager jobs
    *
    * See documentation https://www.moorexa.com/doc/controller
    *
    * You can catch params sent through the $_GET request
    * @return void
    **/
    public function jobs() : void
    {
        // get last 20 jobs
        $jobs = db('jobs')->get('job_name,job_status,time_queued,time_completed')->orderBy('jobid', 'desc')
        ->limit(0, 20)->go();

        // render view
        $this->view->render('jobs', ['jobs' => $jobs]);
    }
}
// END class