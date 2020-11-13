<?php
use function Lightroom\Requests\Functions\{session};
use function Lightroom\Templates\Functions\{view};

/**
 * @method Registry formatName
 * @param string $name
 * @return string
 */
function formatName(string $name) : string 
{
    return ucfirst(substr($name, 0, 8));
}

/**
 * @method Registry fromStorage
 * @param string $fileName
 * @return string
 */
function fromStorage($fileName) : string 
{
    // @var string $filePath
    $filePath = '';

    // check if $fileName is null
    if ($fileName == null) $fileName = 'generic_avatar.png';

    // get config from session
    $config = session()->get('configuration');

    // add storage url
    $filePath = (preg_match('/^(http)/', $fileName) ? $fileName : (rtrim($config->storage_url, '/') . '/' . $fileName));

    // return path
    return $filePath;
}

/**
 * @method Registry getAccountJson
 * @param mixed $account
 * @return string 
 */
function getAccountJson($account) : string 
{
    return json_encode(['accountid' => $account->accountid]);
}

/**
 * @method Registry getFeedbackJson
 * @param mixed $account
 * @return string 
 */
function getFeedbackJson($feedback) : string 
{
    return json_encode(['feedbackid' => $feedback->feedbackid]);
}

/**
 * @method Registry getArticleJson
 * @param mixed $article
 * @return string 
 */
function getArticleJson($article) : string 
{
    return json_encode(['articleid' => $article->articleid]);
}

/**
 * @method Registry getVideoJson
 * @param mixed $video
 * @return string 
 */
function getVideoJson($video) : string 
{
    return json_encode(['videospublishedid' => $video->videospublishedid]);
}

/**
 * @method Registry getServices
 * @param mixed $data
 */
function getServices($data) : array
{
    // @var array $service
    $service = [];

    // make request
    foreach ($data->services as $tagName => $serviceObject) :

        // @var string $serviceName
        $serviceName = '';

        // run switch
        switch ($tagName) :

            // report case
            case 'report-case-tag':
                $serviceName = 'Report a case';
            break;

            // chat with counselor
            case 'chat-with-counselor':
                $serviceName = 'Chat with a counselor';
            break;

            // send an audio
            case 'report-case-tag-audio':
                $serviceName = 'Send an audio';
            break;

            // send an audio
            case 'report-case-tag-video':
                $serviceName = 'Send a video';
            break;

            // rend article
            case 'read-article':
                $serviceName = 'Read Articles';
            break;

            // watch video
            case 'watch-video':
                $serviceName = 'Watch Videos';
            break;

        endswitch;

        // push service
        $service[] = [
            'service'   => $serviceName,
            'male'      => intval($serviceObject->male),
            'female'    => intval($serviceObject->female),
            'other'     => intval($serviceObject->other),
            'total'     => (intval($serviceObject->male) + intval($serviceObject->female) + intval($serviceObject->other)),
        ];

    endforeach;

    // return array
    return $service;
}

/**
 * @method Registry socket
 * @return SocketIOClient
 */
function socket(string $method, array $data)
{
    static $included;

    if ($included == null) :

        // include js files
        view()->requireJs(MANAGER_STATIC . '/socket.io.js', MANAGER_STATIC . '/socket.js');

        // included 
        $included = true;

    endif;

    // export data
    app('assets')->exportVars([
        'socket_address'    => func()->finder('socketAddress'),
        'method'            => $method,
        'data'              => $data
    ]);
}