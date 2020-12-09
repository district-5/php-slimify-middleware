<?php
namespace SlimifyMiddleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Slimify\SlimifyStatic;
use Throwable;

/**
 * Class ErrorHandlingMiddleware
 * @package SlimifyMiddleware
 */
class ErrorHandlingMiddleware
{
    /**
     * @var string
     */
    private $viewKey = null;

    /**
     * ErrorHandlingMiddleware constructor.
     * @param string $viewKey
     */
    public function __construct($viewKey = 'default')
    {
        $this->viewKey = $viewKey;
    }

    /**
     * @param Request $request
     * @param Throwable $e
     * @param bool $showError
     * @param bool $logError
     * @param bool $logErrorDetails
     * @param LoggerInterface|null $logger
     * @return ResponseInterface|Response
     */
    public function __invoke(Request $request, Throwable $e, bool $showError, bool $logError, bool $logErrorDetails, ?LoggerInterface $logger = null)
    {
        $static = SlimifyStatic::retrieve();
        $app = $static->getApp();
        if ($logger === null) {
            $logger = $app->log();
        }
        $logger->error($e->getMessage());

        $response = $app->getResponseFactory()->createResponse();
        /* @var $response Response */
        $app->setInterfaces($request, $response);
        if ($e instanceof HttpNotFoundException) {
            $errorFile = 'error-not-found.phtml';
            $title = 'Not found';
            $message = 'The resource you requested could not be found.';
            $code = 404;
        } elseif ($e instanceof HttpBadRequestException || $e instanceof HttpMethodNotAllowedException) {
            $errorFile = 'error-bad-request.phtml';
            $title = 'Bad request';
            $message = 'You made an invalid request to the server.';
            $code = 400;
        } elseif ($e instanceof HttpForbiddenException || $e instanceof HttpUnauthorizedException) {
            $errorFile = 'error-access-denied.phtml';
            $title = 'Access denied.';
            $message = 'Access to the resource you requested was denied.';
            $code = 403;
        } else {
            $errorFile = 'error-generic.phtml';
            $title = 'Error';
            $message = 'An unknown error has occurred.';
            $code = 500;
        }


        if ($request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
            $payload = [
                'error' => $message,
                'title' => $title,
                'code' => $code
            ];
            if ($showError === true) {
                $payload['error'] = $e->getMessage();
                $payload['file'] = $e->getFile();
                $payload['line'] = $e->getLine();
                $payload['trace'] = $e->getTraceAsString();
            }
            return $app->response()->json(
                $payload,
                $code
            )->withStatus($code);
        }

        $errorParams = $static->getErrorViewParams();
        $errorParams['e'] = $e;

        return $app->getView(
            $this->viewKey
        )->render(
            $response,
            'error/' . $errorFile,
            $errorParams
        )->withStatus($code);
    }
}
