<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;

use Illuminate\Http\Response as IlluminateResponse;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        HttpException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        return parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if ($e instanceof \Illuminate\Database\QueryException) {
            // Render unique constraints violation as 400
            $SQLSTATE_CONSTRAINT_VIOLATION = 23000;
            if ($SQLSTATE_CONSTRAINT_VIOLATION == $e->errorInfo[0]) {
                abort(IlluminateResponse::HTTP_CONFLICT);
            }
        }

        return parent::render($request, $e);
    }
}
