<?php
namespace src\Output;

class ErrorNotAllowed extends ErrorOutput {
    public function __construct($msg){
        parent::__construct(403, ['message' => $msg]);
    }
}
