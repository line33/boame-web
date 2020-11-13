<?php
namespace Moorexa\Framework\Models;

use Closure;
use HttpClient\Http;
use Lightroom\Packager\Moorexa\{
    MVC\Model, Interfaces\ModelInterface
};
use function Lightroom\Requests\Functions\{session};
use function Lightroom\Templates\Functions\{redirect, view};

/**
 * @package Cases Model
 * @author Amadi Ifeanyi <wekiwork.com> <amadiify.com>
 */

class Cases extends Account
{
    /**
     * @method Cases getOverview
     * @return object
     */
    public function getOverview() : object 
    {
        // set the token
        Http::setHeader([
            'x-auth-token'          => session()->get('auth_token'),
            'x-request-platform'    => 'web'
        ]);

        // fetch from gateway
        return Http::get('service/statistics/cases-overview')->json;
    }

    /**
     * @method Cases getSingleCase
     * @param int $caseReportedID
     * @param string $caseType
     * @return object
     */
    public function getSingleCase(int $caseReportedID, string $caseType) : object
    {
        // fetch from gateway
        return Http::get('cases/report/'.$caseType.'/'.$caseReportedID)->json;
    }

    /**
     * @method Cases assignCase
     * @param int $caseReportedID
     */
    public function assignCase(int $caseReportedID)
    {
        // @var mixed $data 
        $data = filter(['id' => $caseReportedID], ['id' => 'number|required|min:1']);

        // are we good ?
        if (!$data->isOk()) return event('ev')->emit('alert', 'Invalid case ID, Please check and try.');

        // make request
        $data = filter('POST', [
            'assign_to' => 'required|number|min:1'
        ]);

        // send request
        $send = Http::body([
            'casesreportedid'   => $caseReportedID,
            'accountid'         => $data->assign_to
        ])->post('cases/assign')->json;

        // check if it was successful
        if ($send->status == 'error') return event('ev')->emit('alert', $send->message);

        // all good
        event('ev')->emit('alert', $send->message, 'success');

        // send notification
        socket('newCaseAssigned', [
            'accountid' => $data->assign_to,
            'caseid'    => $caseReportedID
        ]);
    }
}