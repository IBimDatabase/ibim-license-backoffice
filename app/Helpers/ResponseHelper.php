<?php

namespace App\Helpers;

class ResponseHelper
{
	protected  static $default_errors_response = ['status' => 'error', 'message' => "Something went wrong. Please try again!", 'data' => [], 'code' => '500', ];
    protected static $default_success_response = ['status' => 'success', 'message' => "Sucess", 'data' => [], 'code' => '200', ];

    const default_errors_response = [
        'status' => 'error',
        'message' => "Something went wrong. Please try again!",
        'data' => [],
        'code' => '500',
    ];
    const default_success_response = [
        'status' => 'success',
        'message' => "Sucess",
        'data' => [],
        'code' => '200',
    ];


	public static function RenderSuccessResponse($dataList, $message = null)
	{
		$result = [];
		$result = static::$default_success_response;
		if(!empty($dataList) && is_array($dataList)){
			$dataKeys = array_keys($dataList);
			$dataValues = array_values($dataList);

			$dataCount = count($dataList);

			$index = 0;

			while ($index < $dataCount)
			{
				$result['data'][$dataKeys[$index]] = $dataValues[$index];
				$index++;
			}
		} else {
			$result['data']=$dataList;
		}

		if(!empty($message))
			$result['message'] = $message;
		return $result;
	}


	public static function RenderErrorResponse($errors = null, $message, $code = 500)
	{
		$result = [];
		$result = static::$default_errors_response;

		$result['data']['errors'] = $errors;

		if(!empty($message))
			$result['message'] = $message;

		if(!empty($code))
			$result['code'] = $code;

		return $result;
	}

	public static function RenderValidationResponse($validation = null, $message = null, $code = 422)
	{
		$result = [];
		$result = static::$default_errors_response;

		$result['data']['errors'] = $validation->errors()->all();
		$result['data']['error_fields'] = $validation->errors();

		if(!empty($message))
			$result['message'] = $message;
		else
			$result['message'] = $result['data']['errors'][0];
		if(!empty($code))
			$result['code'] = $code;

		return $result;
	}
}
