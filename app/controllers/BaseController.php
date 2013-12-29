<?php

class BaseController extends Controller {

    /**
     * Setup the layout used by the controller.
     *
     * @return void
     */
    protected function setupLayout()
    {
        if ( ! is_null($this->layout))
        {
            $this->layout = View::make($this->layout);
        }
    }

    /**
     * This function throw an error 400 and provide error messages
     * from validator $validator in JSON.
     *
     * @param validator : a Laravel validator
     * @return
     */
    protected function _sendValidationErrorMessage($validator)
    {
        $s = array("success" => 0, "errors" => array());
        $messages = $validator->messages();
        foreach ($messages->all() as $message) {
            array_push($s["errors"], array("code" => 400, "type" => "ValidationError", "message" => $message));
        }
        return Response::json($s, 400);
    }

    protected function _sendErrorMessage($code, $type, $message) {
        return Response::json(array(
            "success" => 0,
            "errors" => array(
                array(
                    "code" => $code,
                    "type" => $type,
                    "message" => $message
                )
            )
        ), $code);
    }

}