<?php
namespace src\Output;

class ErrorInternal extends ErrorOutput {
    public function __construct($msg){
        parent::__construct(500, ['message' => $msg]);
    }
}
