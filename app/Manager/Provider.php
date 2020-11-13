<?php
namespace Moorexa\Framework\Manager;

use Closure;
use Happy\Directives;
use function Lightroom\Templates\Functions\{export};
use function Lightroom\Requests\Functions\{session, get};
use Lightroom\Packager\Moorexa\Interfaces\ControllerProviderInterface;
/**
 * Manager Provider. Will be loaded before the Manager controller
 * @package App provider
 */
class Provider implements ControllerProviderInterface
{
    /**
     * @method ControllerProviderInterface boot
     * @param Closure $next
     * @return void 
     *
     * This method would be called before controller renders a view
     */
    public function boot(Closure $next) : void
    {
        // call view! Applies Globally.
        $next();

        // preload functions
        \Moorexa\Framework\Functions\Registry::preload();

        // end panel directive
        Directives::directive('endpanel', function(){
            return '</div>
            </div>';
        });

        // get account information
        Directives::directive('info', function(string $title, string $functionName = ''){
            // get the auth user info from the session
            $account = session()->get('auth_user');
            // check for title
            return object_key_exists($account, $title) ? ($functionName !== '' && function_exists($functionName) ? call_user_func($functionName, $account->{$title}) : $account->{$title}) : '';
        });

        // load statistics
        event()->on('ev','view.load', function($view){
            export(['statistic' => $view['controller']->model->getStatisticsOverview()]);
        });

        // load preloader event
        event()->on('ev', 'partial.ready', function($partial){
            if ($partial['name'] == 'preloader') $partial['data']['default_preloader'] = 'custom';
        });

        // trigger download
        if (get()->has('download')) $this->triggerDownload(get()->download);

        // check for new cases
        socket('checkForNewCases', []);
    }

    /**
     * @method ControllerProviderInterface setArguments
     * @param array $arguments
     * 
     * This method sets the view arguments
     */
    public function setArguments(array $arguments) : void {}

    /**
     * @method ControllerProviderInterface viewWillEnter
     * @param string $view
     * @param array &$arguments
     * 
     * This method would be called before entering the view
     */
    public function viewWillEnter(string $view, array &$arguments)
    {

    }


    /**
     * @method Provider triggerDownload
     * @param string $fileName
     */
    public function triggerDownload(string $fileName)
    {
        // prepend storage url
        $fullPath = fromStorage($fileName);

        // read external content
        $content = file_get_contents($fullPath);

        // save the file in tmp directory
        $localStorage = HOME . '/tmp/' . $fileName;

        // save now
        file_put_contents($localStorage, $content);

        // read file
        $mime = mime_content_type($localStorage);

        // trigger download
        header('Content-Type: '.$mime);
        header('Content-Disposition: attachment; filename='.$fileName);
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('Content-Length: '.filesize($localStorage));
        readfile($localStorage);
        flush();

        // delete file
        unlink($localStorage);
        exit;


    }

}