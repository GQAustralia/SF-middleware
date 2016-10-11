<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

abstract class StatusCodes extends BaseController
{
    const SUCCESS_STATUS_CODE = 200;
    const BAD_REQUEST_STATUS_CODE = 400;
    const NOT_FOUND_STATUS_CODE = 404;
    const FORBIDDEN_STATUS_CODE = 403;
    const INTERNAL_SERVER_ERROR_STATUS_CODE = 500;
}