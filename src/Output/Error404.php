<?php
namespace src\Output;

class Error404 extends ErrorOutput {
    public function __construct($msg){
        parent::__construct(404, ['message' => $msg]);
    }
}
