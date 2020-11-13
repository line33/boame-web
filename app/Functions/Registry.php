<?php
namespace Moorexa\Framework\Functions;
/**
 * @package Functions Registry Handler
 * @author Amadi Ifeanyi <amadiify.com> <wekiwork.com>
 */
class Registry
{
    /**
     * @method Registry preload
     * @return void 
     */
    public static function preload() : void 
    {
        // include functions from within current directory
        include_once __DIR__ . '/custom.func.php';
        include_once __DIR__ . '/helper.func.php';
    }
}