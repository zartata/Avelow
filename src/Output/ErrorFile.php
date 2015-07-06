<?php
namespace src\Output;

class ErrorFile extends ErrorOutput {
    public function __construct($msg){
        parent::__construct(400, ['message' => $msg]);
    }
}
