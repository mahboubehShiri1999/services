<?php

namespace App\Http\Controllers;

use App\Exceptions\ServicesException;
use App\Exceptions\UnauthorizedUserException;
use App\Helpers\Constant;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\RequestRulesException;
use App\Http\Controllers\Process\SynchronizationController;
use App\Http\Controllers\Task\TaskManagementController;
use App\Http\Controllers\Task\CommentController;
use Illuminate\Validation\Rule;


trait RulesTrait
{


    public static function rules()
    {
        return [
            RouteCRUDController::class => [
                'createItem' => [
                    'uri' => 'required|string',
                    'description' => 'required|string',
                    'fa_name' => 'required|string',
                    'document_link' => 'required|string'
                ],
                'readItem' => [
                    'id' => 'numeric|required',
                ],
                'updateField' => [
                    'id' => 'required|numeric',
                    'uri' => 'string',
                    'description' => 'string',
                    'fa_name' => 'string',
                    'document_link' => 'string',
                ],
                'deleteRecord' => [
                    'id' => 'integer',
                ],

            ],
            GnafController::class => [
                'search' => [
                    'postcode' => [
                        'ClientBatchID' => 'numeric|required',
                        'Postcodes' => 'required|array',
                        'Postcodes.*' => 'required|array',
                        'Postcodes.*.ClientRowID' => 'required|numeric',
                        'Postcodes.*.PostCode' => 'required',
                        'Signature' => 'string'
                    ],
                    'telephone' => [
                        'ClientBatchID' => 'numeric|required',
                        'Telephones' => 'required|array',
                        'Telephones.*' => 'required|array',
                        'Telephones.*.ClientRowID' => 'required|numeric',
                        'Telephones.*.TelephoneNo' => 'required',
                        'Telephones.*.AreaCode' => 'required',
                        'Signature' => 'string'
                    ]
                ]
            ]
        ];
    }

    public static function checkRules($data, $function, $input,$code)
    {
        $controller = __CLASS__;
        if (strpos($controller, 'GnafController') == true) {
            $category = '';
            if (array_key_exists('Postcodes', $data)) {
                $category = 'postcode';
            } elseif (array_key_exists('Telephones', $data)) {
                $category = 'telephone';
            }
            if (is_object($data)) {
                $validation = Validator::make(
                    $data->all(),
                    self::rules()[$controller][$function][$category]
                );
            } else {
                $validation = Validator::make(
                    $data,
                    self::rules()[$controller][$function][$category]
                );
            }
        } else {
            if (is_object($data)) {
                $validation = Validator::make(
                    $data->all(),
                    self::rules()[$controller][$function]
                );
            } else {
                $validation = Validator::make(
                    $data,
                    self::rules()[$controller][$function]
                );
            }
        }

        if ($validation->fails()) {
            if (strpos($controller, 'GnafController') == true) {
                throw new ServicesException(
                    $data,
                    $input,
                    trans('messages.custom.error.2115'),
                    2115);
            } elseif (strpos($controller, 'RouteCRUDController') == true) {
                throw new RequestRulesException($validation->errors()->getMessages(),$code);
            }
        }


        return $validation->validated();
    }
}
