<?php

namespace App\Http\Controllers;

/**
 * Interface StatusCodes
 *
 * @package App\Http\Controllers
 */
interface StatusCodes
{
    const SUCCESS_STATUS_CODE = 200;
    const BAD_REQUEST_STATUS_CODE = 400;
    const NOT_FOUND_STATUS_CODE = 404;
    const FORBIDDEN_STATUS_CODE = 403;
    const INTERNAL_SERVER_ERROR_STATUS_CODE = 500;
}
