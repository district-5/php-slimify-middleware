<?php
namespace SlimifyMiddleware;

use Slim\App;

/**
 * Class AbstractMiddleware
 * @noinspection PhpUnused
 * @package SlimifyMiddleware
 */
abstract class AbstractMiddleware
{
    /**
     * Add the middleware to the app.
     *
     * @param App $app
     */
    abstract public static function add(App $app);
}
