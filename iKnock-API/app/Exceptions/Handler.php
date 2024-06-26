<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

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
     * @throws \Throwable
     */
    public function report(Throwable $exception)
    {   
        //  // added this section to send the report! TODO
        // if ($this->shouldReport($exception) && env('DEBUGBAR_EMAIL',true) == true) {
        //     $this->sendEmail($exception,'dharmiktank128@gmail.com'); // sends an email
        //     $this->sendEmail($exception,'smit.laravel@gmail.com'); // sends an email
        // }

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
        return parent::render($request, $exception);
    }

    /**
     * Sends an email to the developer about the exception.
     *
     * @return void
     */
    public function sendEmail(Throwable $exception, $emailUser)
    {
        try {
            $input['error_url'] = \Request::fullUrl();
            $input['message'] = 'IKNOCK ERROR PAGE '.$input['error_url'];
            $input['subject'] = 'IKNOCK ERROR PAGE '.$input['error_url'];
            $input['error_msg'] = $exception->getMessage();
            $email = new \SendGrid\Mail\Mail();
            $email->setFrom(env('MAIL_FROM_ADDRESS','support@iknockapp.com'), env('APP_NAME','IKNOCK'));
            $email->setSubject($input['subject']);
            $email->addTo($emailUser, env('APP_NAME','IKNOCK'));

            $dataView = view('emails.errorPage',compact('input'))->render();

            $email->addContent("text/html",$dataView);
            $sendgrid = new \SendGrid(env('MAIL_PASSWORD','Freedomplan2022!'));

            $response = $sendgrid->send($email);

        } catch (Throwable $ex) {
            Log::error($ex);
        }
    }
}
