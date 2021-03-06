<?php

namespace App\Exceptions;

use Carbon\Carbon;
use Exception;
use HttpException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
        ApiException::class
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
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        if (app()->bound('sentry') && $this->shouldReport($exception)) {
            app('sentry')->captureException($exception);
        }

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        return $this->handle($request, $exception);
    }

    public function handle($request, Exception $e){
        // 只处理自定义的APIException异常


        if($e instanceof ApiException) {
            $result = [
                "error_code"    => $e->getCode(),
                "error_message" => $e->getMessage(),
                "data"          => null,
            ];
            return response()->json($result);
        }

        if($e instanceof WebException) {
            $result = [
                "error_code"    => $e->getCode(),
                "error_message" => $e->getMessage(),
                "data"          => null,
            ];
            return response()->json($result);
        }

        if(env("APP_ENV") != "dev"){
            Log::info("未知异常：");
            Log::info($e);
            $result = [
                "error_code"    => $e->getCode(),
                "error_message" => "未知错误",
                "data"          => null,
            ];
            return response()->json($result);
        }else{
            $result = [
                "error_code"    => 500,
                "error_message" => $e->getMessage(),
                "data"    => null,
            ];
            return response()->json($result);
        }

    }

}
