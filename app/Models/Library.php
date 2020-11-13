<?php
namespace Moorexa\Framework\Models;

use Closure;
use HttpClient\Http;
use Lightroom\Packager\Moorexa\{
    MVC\Model, Interfaces\ModelInterface
};
use function Lightroom\Requests\Functions\{post, session};
/**
 * Library model class auto generated.
 *
 *@package Manager Library Model
 *@author Amadi Ifeanyi <amadiify.com>
 **/

class Library extends Account
{
    /**
     * @method Library addArticle
     * @return mixed
     */
    public function addArticle()
    {
        // allow tags
        env_set('bootstrap/filter-input', false);

        // @var string $title
        $title = post()->has('article_title') ? post()->article_title : null;

        // can we create??
        $input = filter('POST', [
            'article_title' => 'string|notag|required|min:2',
            'text_area'     => 'required|min:2',
            'article_image' => 'required|file|filetype:jpg,png,jpeg,gif'
        ]);

        // export article title
        if ($input->has('article_title')) app('assets')->exportVars(['article_title' => $title]);

        // are we good ??
        if (!$input->isOk()) return event('ev')->emit('alert', 'Doesn\'t look good here. Please ensure you attached a cover image, added a text to the wysiwyg editor, and did not add html tag to the article title.');

        // prepare HTTP header
        Http::setHeader([
            'x-auth-token'          => session()->get('auth_token'),
            'x-request-platform'    => 'web',
            'x-request-action'      => 'knowledge-tag/create'
        ]);

        // make submission
        $request = Http::attachment('article_image')->body([
            'article_title' => $input->article_title,
            'article_text'  => $input->text_area
        ])->post('library/article/create')->json;

        // are we good ??
        if ($request->status == 'error') return event('ev')->emit('alert', $request->message);

        // clear post
        post()->clear();

        // remove article_title
        app('assets')->exportVars(['article_title' => null]);

        // print message
        event('ev')->emit('alert', $request->message, 'success');
    }

    /**
     * @method Library editArticle
     * @return mixed
     */
    public function editArticle()
    {
        // allow tags
        env_set('bootstrap/filter-input', false);

        // get the article id
        $articleid = array_pop($this->arguments);

        // @var string $title
        $title = post()->has('article_title') ? post()->article_title : null;

        // can we create??
        $input = filter('POST', [
            'article_title' => 'string|notag|required|min:2',
            'text_area'     => 'required|min:2',
        ]);

        // are we good ??
        if (!$input->isOk()) return event('ev')->emit('alert', 'Doesn\'t look good here. Please ensure you have a cover image, and a text to the wysiwyg editor, also ensure that you did not add an html tag to the article title.');

        // prepare HTTP header
        Http::setHeader([
            'x-auth-token'          => session()->get('auth_token'),
            'x-request-platform'    => 'web',
            'x-request-action'      => 'knowledge-tag/edit'
        ]);

        // make submission
        $request = Http::attachment('article_image')->body([
            'article_title' => $input->article_title,
            'article_text'  => $input->text_area,
            'articleid'     => $articleid
        ])->post('library/article/edit/'.$articleid)->json;

        // are we good ??
        if ($request->status == 'error') return event('ev')->emit('alert', $request->message);

        // print message
        event('ev')->emit('alert', $request->message, 'success');
    }

    /**
     * @method Library getOneArticle
     * @param int $articleid
     * @return mixed
     */
    public function getOneArticle($articleid)
    {
        // load article
        return Http::get('library/article/' . $articleid)->json;
    }

    /**
     * @method Library addVideo
     * @return mixed
     */
    public function addVideo()
    {
        // allow tags
        env_set('bootstrap/filter-input', false);

        // @var string $title
        $title = post()->has('video_title') ? post()->video_title : null;

        // can we create??
        $input = filter('POST', [
            'video_title'   => 'string|notag|required|min:2',
            'text_area'     => 'required|min:2',
            'video'         => 'required|file|filetype:mp4,mpeg,3gp,mpv,mp4a'
        ]);

        // export video title
        if ($input->has('video_title')) app('assets')->exportVars(['article_title' => $title]);

        // are we good ??
        if (!$input->isOk()) return event('ev')->emit('alert', 'Doesn\'t look good here. Please ensure you attached a cover image, added a text to the wysiwyg editor, and did not add html tag to the video title.');

        // prepare HTTP header
        Http::setHeader([
            'x-auth-token'          => session()->get('auth_token'),
            'x-request-platform'    => 'web',
            'x-request-action'      => 'knowledge-tag/create'
        ]);

        // make submission
        $request = Http::attachment('video')->body([
            'video_title'    => $input->video_title,
            'video_caption'  => $input->text_area
        ])->post('library/video/create')->json;

        // are we good ??
        if ($request->status == 'error') return event('ev')->emit('alert', $request->message);

        // clear post
        post()->clear();

        // remove article_title
        app('assets')->exportVars(['article_title' => null]);

        // print message
        event('ev')->emit('alert', $request->message, 'success');
    }

    /**
     * @method Library getOneVideo
     * @param int $videoid
     * @return mixed
     */
    public function getOneVideo($videoid)
    {
        // load article
        return Http::get('library/video/' . $videoid)->json;
    }

    /**
     * @method Library editVideo
     * @return mixed
     */
    public function editVideo()
    {
        // allow tags
        env_set('bootstrap/filter-input', false);

        // get the video id
        $videoid = array_pop($this->arguments);

        // @var string $title
        $title = post()->has('video_title') ? post()->video_title : null;

        // can we create??
        $input = filter('POST', [
            'video_title'   => 'string|notag|required|min:2',
            'text_area'     => 'required|min:2'
        ]);

        // are we good ??
        if (!$input->isOk()) return event('ev')->emit('alert', 'Doesn\'t look good here. Please ensure you have a video attached, and a text to the wysiwyg editor, also ensure that you did not add an html tag to the video title.');

        // prepare HTTP header
        Http::setHeader([
            'x-auth-token'          => session()->get('auth_token'),
            'x-request-platform'    => 'web',
            'x-request-action'      => 'knowledge-tag/edit'
        ]);

        // make submission
        $request = Http::attachment('video')->body([
            'video_title'           => $input->video_title,
            'video_caption'         => $input->text_area,
            'videospublishedid'     => $videoid
        ])->post('library/video/edit/'.$videoid)->json;

        // are we good ??
        if ($request->status == 'error') return event('ev')->emit('alert', $request->message);

        // print message
        event('ev')->emit('alert', $request->message, 'success');
    }
}