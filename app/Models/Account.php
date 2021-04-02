<?php
namespace Moorexa\Framework\Models;

use Closure;
use HttpClient\Http;
use Lightroom\Packager\Moorexa\{
    MVC\Model, Interfaces\ModelInterface
};
use function Lightroom\Requests\Functions\{session, file, post};
use function Lightroom\Templates\Functions\{redirect};

/**
 * @package Account Model
 * @author Amadi Ifeanyi <wekiwork.com> <amadiify.com>
 */

class Account extends Model
{
    /**
     * @method ModelInterface onModelInit
     * @param ModelInterface $model
     * @param Closure $next
     * @return void
     */
    public function onModelInit(ModelInterface $model, Closure $next) : void 
    {
        // call closure
        $next();
    }

    // authenticate user
    public function authenticateUser()
    {
        // @var mixed $input
        $input = filter('POST', [
            'username' => 'string|notag|required|min:4',
            'password' => 'required|min:4'
        ]);

        // are we good ??
        if (!$input->isOk()) return event('ev')->emit('alert', 'You have an invalid form field. Please check and try again');

        // try pushing
        $auth = Http::body([
            'platformid' => 1,
            'username'   => $input->username,
            'password'   => $input->password
        ])->post('service/auth/login');

        // get the response object
        $response = $auth->json;

        // are we good ??
        if ($response->status == 'error') return event('ev')->emit('alert', $response->message);

        // set session
        session()->set('auth_token', $response->token);
        session()->set('auth_user',  $response->account);
        session()->set('auth_nav',   $response->navigations);

        // load configuration settiings
        $configuration = Http::get('service/config');

        // store configuration
        session()->set('configuration', $configuration->json->config);
        
        // redirect user
        redirect('manager');
    }

    // get statistics overview
    public function getStatisticsOverview() : object
    {
        // set the token
        Http::setHeader([
            'x-auth-token'          => session()->get('auth_token'),
            'x-request-platform'    => 'web'
        ]);

        // fetch from gateway
        $overview = Http::get('service/statistics/overview');

        // check for error
        if ($overview->json->status == 'error') :

            func()->redirect('/logout');

        endif;

        // return object
        return $overview->json;
    }

    // get app usuage
    public function getAppUsage() : object 
    {
        // set the token
        Http::setHeader([
            'x-auth-token'          => session()->get('auth_token'),
            'x-request-platform'    => 'web'
        ]);

        // fetch from gateway
        $usage = Http::get('service/statistics/app-usage');

        // return object
        return $usage->json;
    }

    // reset user password
    public function resetPassword()
    {
        // @var mixed $input
        $input = filter('POST', [
            'username'          => 'string|notag|required|min:4',
            'password'          => 'required|min:4',
            'password_again'    => 'required|min:4'
        ]);

        // are we good ??
        if (!$input->isOk()) return event('ev')->emit('alert', 'You have an invalid form field. Please check and try again');


        // make request
        $request = Http::body($input->data())->post('service/auth/reset-password')->json;

        // are we good ??
        if ($request->status == 'error') return event('ev')->emit('alert', $request->message);

        // add username
        $request->username = $input->username;

        // all good
        return $request;
    }

    // retry sending mail
    public function retrySendingMail($data)
    {
        // make request
        $request = Http::get('service/auth/reset-password/' . $data->vaultId)->json;

        // are we good ??
        if ($request->status == 'error') return event('ev')->emit('alert', $request->message);

        // ok
        event('ev')->emit('alert', $request->message, 'success');
    }

    // complete the password reset
    public function completeResetPassword()
    {
        // @var mixed $input
        $input = filter('POST', [
            'reset_code'  => 'number|notag|required|min:4',
        ]);

        // are we good ??
        if (!$input->isOk()) return event('ev')->emit('alert', 'You have provided an invalid authorization code. Please check and try again.');

        // get the tmp data
        $data = session()->get('tmp_storage_password_reset');

        // make request
        $request = Http::body([
            'username'      => $data->username,
            'reset_code'    => $input->reset_code
        ])->post('service/auth/complete-reset-password')->json;

        // are we good ??
        if ($request->status == 'error') return event('ev')->emit('alert', $request->message);

        // drop others
        session()->dropMultiple('tmp_storage_password_reset', 'retry_mail_time');

        // return response
        return $request;

    }

    // update user profile
    public function updateProfile()
    {
        // @var mixed $input
        $input = filter('POST', [
            'firstname'         => 'string|notag|required|min:2',
            'lastname'          => 'string|notag|required|min:2',
        ]);

        // are we good ??
        if (!$input->isOk()) return event('ev')->emit('alert', 'You have an invalid form field. Please check and try again');

        // are we good??
        $request = \HttpClient\Http::attachment('display_image')->body($input->data())->post('service/account/update/' . app('account')->accountid);

        // an error occured
        if ($request->json->status == 'error') return event('ev')->emit('alert', $request->json->message);

        // update the firstname, lastname, display_image
        app('account')->set([
            'firstname'     => $request->json->data->firstname,
            'lastname'      => $request->json->data->lastname,
            'display_image' => $request->json->data->display_image,
        ]);

        // show response
        event('ev')->emit('alert', $request->json->message, 'success');
    }

    // update counselor profile
    public function updateCounselorProfile()
    {
        // @var mixed $input
        $input = filter('POST', [
            'firstname'         => 'string|notag|required|min:2',
            'lastname'          => 'string|notag|required|min:2',
            'email'             => 'string|notag|required|min:5',
            'telephone'         => 'notag|required|min:5',
            'accountid'         => 'number|required|min:1',
            'gender'            => 'string|required|min:2',
        ]);

        // are we good ??
        if (!$input->isOk()) return event('ev')->emit('alert', 'You have an invalid form field. Please check and try again');

        // get accountid
        $accountid = $input->accountid;

        // remove accountid
        $input->pop('accountid');

        // are we good??
        $request = \HttpClient\Http::attachment('display_image')->body($input->data())->post('service/account/update/' . $accountid);

        // an error occured
        if ($request->json->status == 'error') return event('ev')->emit('alert', $request->json->message);

        // show response
        event('ev')->emit('alert', 'Counselor profile updated successfully.', 'success');
    }

    // update super user profile
    public function updateSuperUserProfile()
    {
        // @var mixed $input
        $input = filter('POST', [
            'firstname'         => 'string|notag|required|min:2',
            'lastname'          => 'string|notag|required|min:2',
            'email'             => 'string|notag|required|min:5',
            'telephone'         => 'notag|required|min:5',
            'accountid'         => 'number|required|min:1',
            'accounttypeid'     => 'number|required|min:1',
        ]);

        // are we good ??
        if (!$input->isOk()) return event('ev')->emit('alert', 'You have an invalid form field. Please check and try again');

        // get accountid
        $accountid = $input->accountid;

        // remove accountid
        $input->pop('accountid');

        // are we good??
        $request = \HttpClient\Http::attachment('display_image')->body($input->data())->post('service/account/update/' . $accountid);

        // an error occured
        if ($request->json->status == 'error') return event('ev')->emit('alert', $request->json->message);

        // show response
        event('ev')->emit('alert', 'Super User profile updated successfully.', 'success');
    }

    // get account information
    public function getAccountInformation($accountid)
    {
        // make query
        $request = \HttpClient\Http::get('service/account/' . $accountid);

        // are we good ??
        return $request->json;
    }

    // get counsellors and volunteers
    public function getCounselorsAndVolunteers()
    {
        // set the token
        Http::setHeader([
            'x-account-types'   => 'Counsellor,Volunteer',
        ]);

        // make request
        return Http::get('service/account/read/accounts')->json;
    }

    // get administrators and moderators
    public function getAdministratorsAndModerators()
    {
        // set the token
        Http::setHeader([
            'x-account-types'   => 'Administrator,Moderator',
        ]);

        // make request
        return Http::get('service/account/read/accounts')->json;
    }

    // get all volunteers
    public function getVolunteers()
    {
        return Http::get('volunteer/')->json;
    } 

    // get one volunteer info
    public function getVolunteerInformation($accountid)
    {
        return Http::get('volunteer/' . $accountid)->json;
    }

    // approve volunteer
    public function approveVolunteer()
    {
        // validate POST data
        $input = filter('POST', [
            'accountid' => 'number|required|notags|min:1',
            'comment' => 'notags|required|min:1'
        ]);

        // are we good ??
        if ($input->isOk()) :

            // prepare HTTP header
            Http::setHeader([
                'x-auth-token'          => session()->get('auth_token'),
                'x-request-platform'    => 'web',
                'x-request-action'      => 'volun-tag/edit'
            ]);

            // make request
            $request = Http::body([
                'comment' => $input->comment
            ])->post('volunteer/approve/'.$input->accountid)->json;

            // are we good ??
            if ($request->status == 'error') return event('ev')->emit('alert', $request->message);

            // ok good
            event('ev')->emit('alert', $request->message, 'success');
 
        endif;
    }

    // delete volunteer
    public function deleteVolunteer()
    {
        // @var mixed $input
        $input = filter('POST', ['accountid' => 'required|number|min:1']); 

        // check if everything is ok
        if ($input->isOk()) :

            // prepare HTTP header
            Http::setHeader([
                'x-auth-token'          => session()->get('auth_token'),
                'x-request-platform'    => 'web',
                'x-request-action'      => 'volun-tag/delete'
            ]);

            // make request
            $request = Http::body([
                'accountid' => $input->accountid
            ])->post('service/account/delete')->json;

            // are we good ??
            if ($request->status == 'error') return event('ev')->emit('alert', $request->message);

            // ok good
            event('ev')->emit('alert', 'Volunter removed from the system successfully', 'success');

        endif;
    }

    // delete administrators or moderators
    public function deleteSuperUser()
    {
        // @var mixed $input
        $input = filter('POST', ['accountid' => 'required|number|min:1']); 

        // check if everything is ok
        if ($input->isOk()) :

            // check self delete
            if ($input->accountid != app('account')->accountid) :

                // prepare HTTP header
                Http::setHeader([
                    'x-auth-token'          => session()->get('auth_token'),
                    'x-request-platform'    => 'web',
                    'x-request-action'      => 'admin-tag/delete'
                ]);

                // make request
                $request = Http::body([
                    'accountid' => $input->accountid
                ])->post('service/account/delete')->json;

                // are we good ??
                if ($request->status == 'error') return event('ev')->emit('alert', $request->message);

                // ok good
                return event('ev')->emit('alert', 'Account removed from the system successfully', 'success');

            endif;

            // can not delete your account
            event('ev')->emit('alert', 'You cannot delete your account. This action is forbidden');

        endif;
    }

    // delete reporter
    public function deleteReporter()
    {
        // @var mixed $input
        $input = filter('POST', ['accountid' => 'required|number|min:1']); 

        // check if everything is ok
        if ($input->isOk()) :

            // prepare HTTP header
            Http::setHeader([
                'x-auth-token'          => session()->get('auth_token'),
                'x-request-platform'    => 'web',
                'x-request-action'      => 'report-tag/delete'
            ]);

            // make request
            $request = Http::body([
                'accountid' => $input->accountid
            ])->post('service/account/delete')->json;

            // are we good ??
            if ($request->status == 'error') return event('ev')->emit('alert', $request->message);

            // ok good
            event('ev')->emit('alert', 'Reporter removed from the system successfully', 'success');

        endif;
    }

    // get reporters
    public function getReporters()
    {
        // set the token
        Http::setHeader([
            'x-account-types'   => 'Reporter',
        ]);

        // make request
        return Http::get('service/account/read/accounts')->json;
    }

    // get counselors
    public function getCounselors()
    {
        // make request
        return Http::get('counselor/')->json;
    }

    // get feedbacks
    public function getFeedbacks()
    {
        // make requests
        return Http::get('service/feedback')->json;
    }

    // delete feedback
    public function deleteFeedback()
    {
        // @var mixed $input
        $input = filter('POST', ['feedbackid' => 'required|number|min:1']); 

        // check if everything is ok
        if ($input->isOk()) :

            // prepare HTTP header
            Http::setHeader([
                'x-auth-token'          => session()->get('auth_token'),
                'x-request-platform'    => 'web',
                'x-request-action'      => 'feedback-tag/delete'
            ]);

            // make request
            $request = Http::body([
                'feedbackid' => $input->feedbackid
            ])->post('service/feedback/delete')->json;

            // are we good ??
            if ($request->status == 'error') return event('ev')->emit('alert', $request->message);

            // ok good
            event('ev')->emit('alert', $request->message, 'success');

        endif;
    }

    // delete counselor
    public function deleteCounselor()
    {
        // @var mixed $input
        $input = filter('POST', ['accountid' => 'required|number|min:1']); 

        // check if everything is ok
        if ($input->isOk()) :

            // prepare HTTP header
            Http::setHeader([
                'x-auth-token'          => session()->get('auth_token'),
                'x-request-platform'    => 'web',
                'x-request-action'      => 'counselor-tag/delete'
            ]);

            // make request
            $request = Http::body([
                'accountid' => $input->accountid
            ])->post('service/account/delete')->json;

            // are we good ??
            if ($request->status == 'error') return event('ev')->emit('alert', $request->message);

            // ok good
            event('ev')->emit('alert', 'Counselor removed from the system successfully', 'success');

        endif;
    }

    // get feedback 
    public function getFeedbackInformation($feedbackid)
    {
        // make requests
        return Http::get('service/feedback/'.$feedbackid)->json;
    }

    // get feedback replies
    public function getFeedbackReplies($feedbackid)
    {
        // make requests
        return Http::get('service/feedback/reply/'.$feedbackid)->json;
    }

    // submit feedback reply
    public function replyFeedback()
    {
        // @var mixed $input
        $input = filter('POST', [
            'feedbackid' => 'required|number|min:1',
            'reply'      => 'required|notag|min:2'
        ]); 

        // check if we are good to go
        if (!$input->isOk()) return event('ev')->emit('alert', 'Your submission could not bee processed. Please check your entry and try again.');

        // make submission
        $request = Http::body([
            'feedback'  => $input->reply,
            'accountid' => app('account')->accountid
        ])->post('service/feedback/reply/' . $input->feedbackid)->json;

        // are we good ??
        if ($request->status == 'error') return event('ev')->emit('alert', $request->message);

        // all good
        event('ev')->emit('alert', $request->message, 'success');
    }

    // get cases assigned to a counselor
    public function getCasesAssignedToCounselor($accountid)
    {
        // make query
        $request = \HttpClient\Http::get('counselor/cases/' . $accountid);

        // are we good ??
        return $request->json;
    }

    // add a counselor
    public function addCounselor()
    {
        // read form input
        $input = filter('POST', [
            'firstname'         => 'required|string|notag|min:1',
            'lastname'          => 'required|string|notag|min:1',
            'telephone'         => 'required|string|notag|min:5',
            'email'             => 'required|email|min:5',
            'password'          => 'min:4|required',
            'password_again'    => 'min:4|required',
            'gender'            => 'string|min:2|required',
            'accountid'         => ['required|number', app('account')->accountid]
        ]);

        // are we good ??
        if (!$input->isOk()) return event('ev')->emit('alert', 'You have an invalid form submission.');

        // compare password
        if ($input->password != $input->password_again) return event('ev')->emit('alert', 'Your password doesn\'t match.');

        // submit request
        $request = \HttpClient\Http::attachment('display_image')->body($input->data())->post('service/auth/register/counsellor')->json;

        // are we good 
        if ($request->status == 'error') return event('ev')->emit('alert', $request->message);

        // cleear post entry
        post()->clear();

        // all good
        event('ev')->emit('alert', 'Counselor has been added successfully.', 'success');
    }

    // add super user
    public function addSuperUser()
    {
        // read form input
        $input = filter('POST', [
            'firstname'         => 'required|string|notag|min:1',
            'lastname'          => 'required|string|notag|min:1',
            'telephone'         => 'required|string|notag|min:5',
            'email'             => 'required|email|min:5',
            'password'          => 'min:4|required',
            'password_again'    => 'min:4|required',
            'gender'            => 'min:1|required|string',
            'accountid'         => ['required|number', app('account')->accountid],
            'position'          => 'required|number|min:1'
        ]);

        // are we good ??
        if (!$input->isOk()) return event('ev')->emit('alert', 'You have an invalid form submission.');

        // compare password
        if ($input->password != $input->password_again) return event('ev')->emit('alert', 'Your password doesn\'t match.');

        // get endpoint
        $accountType = 'administrator';

        // check for moderator
        $accountType = lcfirst(app('account')->accountTypes()[$input->position]);

        // remove position
        $input->pop('position');

        // submit request
        $request = \HttpClient\Http::attachment('display_image')->body($input->data())->post('service/auth/register/' . $accountType)->json;

        // are we good 
        if ($request->status == 'error') return event('ev')->emit('alert', $request->message);

        // cleear post entry
        post()->clear();

        // all good
        event('ev')->emit('alert', ucfirst($accountType) . ' has been added successfully.', 'success');
    }

    // get library statistics
    public function getLibraryStatistics()
    {
        return Http::get('service/statistics/library-overview')->json;
    }

    // get all articles 
    public function getAllArticles()
    {
        return Http::get('library/articles')->json;
    }

    // delete one article
    public function deleteArticle()
    {
        // @var mixed $input
        $input = filter('POST', ['articleid' => 'required|number|min:1']); 

        // check if everything is ok
        if ($input->isOk()) :

            // prepare HTTP header
            Http::setHeader([
                'x-auth-token'          => session()->get('auth_token'),
                'x-request-platform'    => 'web',
                'x-request-action'      => 'knowledge-tag/delete'
            ]);

            // make request
            $request = Http::delete('library/article/' . $input->articleid)->json;

            // are we good ??
            if ($request->status == 'error') return event('ev')->emit('alert', $request->message);

            // ok good
            event('ev')->emit('alert', $request->message, 'success');

        endif;
    }

    // get all videos 
    public function getAllVideos()
    {
        return Http::get('library/videos')->json;
    }

    // delete one video
    public function deleteVideo()
    {
        // @var mixed $input
        $input = filter('POST', ['videospublishedid' => 'required|number|min:1']); 

        // check if everything is ok
        if ($input->isOk()) :

            // prepare HTTP header
            Http::setHeader([
                'x-auth-token'          => session()->get('auth_token'),
                'x-request-platform'    => 'web',
                'x-request-action'      => 'knowledge-tag/delete'
            ]);

            // make request
            $request = Http::delete('library/video/' . $input->videospublishedid)->json;

            // are we good ??
            if ($request->status == 'error') return event('ev')->emit('alert', $request->message);

            // ok good
            event('ev')->emit('alert', $request->message, 'success');

        endif;
    }

    // get overall statitics
    public function getOveralStatitics()
    {
        // get start date and end date
        $input = filter('POST', [
            'start_date' => 'required|string',
            'end_date'   => 'required|string',
        ]);

        // are we good ??
        if ($input->isOk()) :

            Http::setHeader([
                'x-date-range' => date('d/m/Y', strtotime($input->start_date)) . ',' . date('d/m/Y', strtotime($input->end_date))
            ]);

        endif;

        // get data
        return Http::get('service/statistics/general-overview')->json;
    }
}