<?php
namespace SlimifyMiddleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\App;
use Slim\Psr7\Request;
use Slim\Psr7\Stream;

/**
 * Class GzipMiddleware
 * @noinspection PhpUnused
 * @package SlimifyMiddleware
 */
class GzipMiddleware
{
    /**
     * Add Gzip middleware to the app.
     *
     * @param App $app
     */
    public static function add(App $app)
    {
        $app->add(function (Request $request, RequestHandlerInterface $handler) {
            if ($request->hasHeader('Accept-Encoding')) {
                if (stristr($request->getHeaderLine('Accept-Encoding'), 'gzip') === false) {
                    return $handler->handle( // No support for gzip.
                        $request
                    );
                }
            }

            /** @var ResponseInterface $response */
            $response = $handler->handle(
                $request
            );

            if ($response->hasHeader('Content-Encoding')) {
                return $handler->handle(
                    $request
                );
            }

            // Compress response data
            $deflateContext = deflate_init(ZLIB_ENCODING_GZIP);
            $compressed = deflate_add($deflateContext, (string)$response->getBody(), ZLIB_FINISH);

            $stream = fopen('php://memory', 'r+');
            fwrite($stream, $compressed);
            rewind($stream);

            return $response->withHeader(
                'Content-Encoding',
                'gzip'
            )->withHeader(
                'Content-Length',
                strlen($compressed)
            )->withBody(
                new Stream($stream)
            );
        });
    }
}
