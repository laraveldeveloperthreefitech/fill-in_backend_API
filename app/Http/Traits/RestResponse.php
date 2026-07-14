<?php

namespace App\Http\Traits;

use response;

trait RestResponse
{
public $successCode = 200;
public $recodeNotFoundCode = 404;
public $somethingWrong = 408;
public $recodeExistCode = 409;
public $validationCode = 422;
public $internalErrorCode = 500;
public $responseMsg = [
        'success' => [
        'Record has been submitted successfully.',
        'Record has been updated successfully.',
        'Record remove successfully.',
        'Record Found.',
        'Email sent successfully.'
        ],
        'error' => [
        'Sorry! Record not found',
        'Oops! something went wrong.',
        'Sorry! Record already exist.'
        ],
    ];

public function recordFoundWithResponse($data)
{
    return response()->json([
        'statusCode'    => $this->successCode,
        'status'        => 'success',
        'message'       => $this->responseMsg['success'][3],
        'data'          => $data,
    ]);
}
public function recordFoundResponseWithOtherFields($data, $others)
{


    $response = [
        'statusCode'    => $this->successCode,
        'status'        => 'success',
        'message'       => $this->responseMsg['success'][3],
        'data'          => $data,
    ];
    $dataReturn = array_merge($response, $others);
    return response()->json($dataReturn);
}

public function recordListFoundWithResponse($data)
{
    return response()->json([
        'statusCode'    => $this->successCode,
        'status'        => 'success',
        'message'       => $this->responseMsg['success'][3],
        'data'          => $data->data ? $data->data : "",
        'meta'          => $data->data ? $data->meta : "",
    ]);
}

public function getExceptionResponse($e)
{
    return response()->json([
        'statusCode'    => $e->getCode(),
        'status'        => 'failed',
        'message'       => $e->getMessage(),
        'line'          => $e->getLine(),
        'file'          => $e->getFile(),
    ],$this->internalErrorCode);
}

public function recordNotFoundResponse()
{
    return response()->json([
        'statusCode'    =>  $this->recodeNotFoundCode,
        'status'        =>  'failed',
        'message'       =>  $this->responseMsg['error'][0],
        'data'          =>  []
    ]);
}

public function newRecordSaveResponse($data)
{
    return response()->json([
        'statusCode'    => $this->successCode,
        'status'        => 'success',
        'message'       => $this->responseMsg['success'][0],
        'data'          => $data
    ]);
}

public function recordExistResponse()
{
    return response()->json([
        'statusCode'    => $this->recodeExistCode,
        'status'        => 'failed',
        'message'       => $this->responseMsg['error'][2],
    ],$this->recodeExistCode);
}

public function recordRemove()
{
    return response()->json([
        'statusCode'    => $this->successCode,
        'status'        => 'success',
        'message'       => $this->responseMsg['success'][2],
    ]);
}

public function somethingWrong()
{
    return response()->json([
        'statusCode'    => $this->somethingWrong,
        'status'        => 'failed',
        'message'       => $this->responseMsg['error'][1],
    ],$this->somethingWrong);
}

public function recordUpdate()
{
    return response()->json([
        'statusCode'    => $this->successCode,
        'status'        => 'success',
        'message'       => $this->responseMsg['success'][1],
    ]);
}

public function customSuccessRes($message)
{
    return response()->json([
        'statusCode'    => $this->successCode,
        'status'        => 'success',
        'message'       => $message,
    ],$this->successCode);
}

public function customErrorRes($message)
{
    return response()->json([
        'statusCode'    => $this->recodeNotFoundCode,
        'status'        => 'failed',
        'message'       => $message,
    ],$this->recodeNotFoundCode);
}

public function emailSendSuccess()
{
    return response()->json([
        'statusCode'    => $this->successCode,
        'status'        => 'success',
        'message'       => $this->responseMsg['success'][4],
    ]);
}

public function customValidationErrorRes($message, $errors)
{
    return response()->json([
        'statusCode'    => $this->validationCode,
        'status'        => 'failed',
        'message'       => $message,
        'errors'        => $errors,
    ],$this->validationCode);
}

public function customValidationres($message)
{
    return response()->json([
        'statusCode'    => $this->validationCode,
        'status'        => 'failed',
        'message'       => $message,
    ],$this->validationCode);
}

public function errorResWithData($message, $data)
{
    return response()->json([
        'statusCode'    => $this->validationCode,
        'status'        => 'failed',
        'message'       => $message,
        'data'          => $data,
    ]);
}

public function successResWithData($message, $data)
{
    return response()->json([
        'statusCode'    => $this->successCode,
        'status'        => 'success',
        'message'       => $message,
        'data'          => $data,
    ]);
}

public function successResWithOtherData($message, $data , $others)
{
    $response = [
        'statusCode'    => $this->successCode,
        'status'        => 'success',
        'message'       => $message,
        'data'          => $data,
    ];
    $dataReturn = array_merge($response, $others);
    return response()->json($dataReturn);
}

}