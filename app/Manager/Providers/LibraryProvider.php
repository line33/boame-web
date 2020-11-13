<?php
namespace Moorexa\Framework\Manager\Providers;

use Closure;
use Moorexa\Framework\Models\{Library};
use function Lightroom\Requests\Functions\{post};
use Lightroom\Packager\Moorexa\Interfaces\ViewProviderInterface;
use function Lightroom\Templates\Functions\{view, export};
/**
 * @package Library View Page Provider
 * @author Moorexa <moorexa.com>
 */

class LibraryProvider implements ViewProviderInterface
{
    private $id = 0;
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

    // article view requested
    public function article(string $action = '', $id)
    {
        // set id
        $this->id = $id;

        // open request
        $this->viewControl('article', $action);

        // render default
        $this->controller->view->render('library');
    }

    // video view requested
    public function video(string $action = '', $id)
    {
        // set id
        $this->id = $id;

        // open request
        $this->viewControl('video', $action);

        // render default
        $this->controller->view->render('library');
    }

    // view request controler
    private function viewControl(string $view, string $action)
    {
        // include editor js file
        view()->requireJs(
            MANAGER_STATIC . '/jquery.js',
            MANAGER_STATIC . '/dist/summernote-lite.js',
            MANAGER_STATIC . '/dist/lang/summernote-es-ES.js',
            MANAGER_STATIC . '/editor.js'
        )
        ->requireCss(
            MANAGER_STATIC . '/dist/summernote-lite.css'
        ); 

        // manage actions
        switch (strtolower($action)) :
            case 'add':
            case 'create':
                return $this->controller->view->render('library/'.$view.'/create');

            case 'edit':
                // load processor
                $processor = $view.'Processor';
                $this->{$processor}();

                // render view
                return $this->view->render('library/'.$view.'/edit');
            
        endswitch;
    }

    // load article
    private function articleProcessor()
    {
        // verify id
        if ($this->id == 0) $this->view->redir('manager/library');

        // load model
        $model = new Library();

        // get article
        $data = $model->getOneArticle($this->id);

        // are we good ??
        if ($data->status == 'error') return $this->view->redir('manager/library');

        // load all
        post()->setMultiple([
            'article_title' => $data->article->article_title,
            'text_area'     => $data->article->article_text
        ]);

        // export title to javascript
        app('assets')->exportVars(['article_title' => $data->article->article_title]);

        // export image
        export(['display_image' => fromStorage($data->article->article_cover_image)]);
    }

    // load videos
    private function videoProcessor()
    {
        // verify id
        if ($this->id == 0) $this->view->redir('manager/library');

        // load model
        $model = new Library();

        // get video
        $data = $model->getOneVideo($this->id);

        // are we good ??
        if ($data->status == 'error') return $this->view->redir('manager/library');

        // load all
        post()->setMultiple([
            'video_title'   => $data->videos->video_title,
            'text_area'     => $data->videos->attachment->video_caption
        ]);

        // export title to javascript
        app('assets')->exportVars(['article_title' => $data->videos->video_title]);

        // export video
        export(['video' => $data->videos->attachment]);
    }
}