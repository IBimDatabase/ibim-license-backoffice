<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use App\Exceptions\RouteNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use App\Exceptions\UnauthenticatedException;
use Illuminate\Auth\AuthenticationException;
use App\Services\ErrorLogService;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        //return parent::render($request, $exception);
        $response = [];
        $response['status'] = false;

        if ( $exception instanceof ErrorException ) {
            $response['code'] = 500;
            $response['message'] = "Something went wrong. Please try again!";
        }

        if ($exception instanceof NotFoundHttpException) {
            $response['code'] = 404;
            $response['message'] = "Not found.";
        }

        if ($exception instanceof MethodNotAllowedHttpException) {

            $response['code'] = 404;
            $response['message'] = "The Method is invalid.";
        }

        if (@$exception->getMessage() == "Route [login] not defined." || @$exception->getMessage() == "Unauthenticated.")
        {
            $response['code'] = 401;
            $response['message'] = "Unauthenticated.";
        } 
        else 
        {
            $errorLogData = [
                'error_code' => 500, 
                'error_type' => 'EXCEPTION_HANDLER',
                'route' => url()->current(), 
                'file_name' => $exception->getFile().' (line: '. $exception->getLine() . ')',
                'error_message' => $exception->getMessage()
            ];            
            ErrorLogService::saveErrorLog($errorLogData);

            
            $error = [
                "error" => [
                    @$errorLogData['error_message'] .' in '. @$errorLogData['file_name']
                ]
            ];
            $response['code'] = 500;
            $response['message'] = "Something went wrong. Please try again!";
            $response['data'] = $error;
        }

        return response()->json($response, $response['code']);
    }
}
