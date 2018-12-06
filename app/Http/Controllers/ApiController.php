<?php
/**
 * Created by PhpStorm.
 * User: kenath
 * Date: 18/12/2017
 * Time: 02:32 PM
 */

namespace App\Http\Controllers;


use Illuminate\Support\Facades\Response;

/**
 * Class ApiController
 *
 * @package App\Http\Controllers
 * @author  Kenath <kenath@ceylonit.com>
 */
class ApiController extends Controller
{
    /**
     * Status Code
     *
     * @var
     */
    protected $statusCode = 200;

    /**
     * Default Size for pagination
     *
     * @var int
     */
    protected $per_page = 25;


    /**
     * Get Status Code
     *
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Set Header Status Code
     *
     * @param integer $statusCode Code
     *
     * @return $this
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }


    /**
     * Not found
     *
     * @param string $message Message
     *
     * @return mixed
     */
    public function respondNotFound($message = 'Not Found!')
    {
        return  $this->setStatusCode(404)->respondWithError($message);
    }

    /**
     * Internal Server Error
     *
     * @param string $message Message
     *
     * @return mixed
     */
    public function respondInternalError($message = 'Internal Error!')
    {
        return  $this->setStatusCode(500)->respondWithError($message);
    }

    /**
     * Respond
     *
     * @param array $data    Data
     * @param array $headers Http Headers
     *
     * @return mixed
     */
    public function respond($data, $headers = [])
    {
        return Response::json($data, $this->getStatusCode(), $headers);
    }


    /**
     * Error Response
     *
     * @param string $message Message
     *
     * @return mixed
     */
    public function respondWithError($message)
    {
        return $this->respond(
            [
                'error' => [
                    'message' => $message,
                    'status_code' => $this->getStatusCode()
                ]
            ]
        );
    }

    /**
     * Genaric Response for success
     *
     * @param string $message Message
     * @param array  $data    Array
     *
     * @return mixed
     */
    public function respondCreated($message = 'Successfully created', $data = array())
    {
        return $this->setStatusCode(201)->respond(
            [
                'success' => [
                    'message' => $message,
                    'data' => $data
                ]
            ]
        );
    }

    /**
     * Genaric Response for success
     *
     * @param string $message Message
     * @param array  $data    Array
     *
     * @return mixed
     */
    public function respondSuccess($message = 'ok', $data = array())
    {
        return $this->setStatusCode(200)->respond(
            [
                'success' => [
                    'message' => $message,
                    'data' => $data
                ]
            ]
        );
    }

    /**
     * Response for invalid data
     *
     * @param string $message Message
     * @param array  $errors  Errors
     *
     * @return mixed
     */
    public function validationFailed($message = 'Validation failed', $errors = array())
    {
        if (!empty($errors)) {
            return $this->setStatusCode(422)->respond(
                [
                    'message' => $message,
                    'errors' => $errors
                ]
            );
        }

        return $this->setStatusCode(422)->respond(
            [
                'message' => $message
            ]
        );
    }

    /**
     * Response for Invalid Arguments
     *
     * @param string $message Message
     *
     * @return mixed
     */
    public function invalidArguments($message = 'Invalid Arguments')
    {
        return $this->setStatusCode(422)->respond(
            [
                'message' => $message
            ]
        );
    }

    /**
     * Bad Request
     *
     * @param string $message Message Text
     *
     * @return mixed
     */
    public function respondForBadRequest($message = 'Not Found!')
    {
        return  $this->setStatusCode(400)->respondWithError($message);
    }

    /**
     * Bad Request
     *
     * @param string $message Message Text
     *
     * @return mixed
     */
    public function respondForUnauthorizedRequest($message = 'Unauthorised!')
    {
        return  $this->setStatusCode(401)->respondWithError($message);
    }
}
