<?php
namespace src\Output;

class ErrorDuplicatedFields extends ErrorOutput {
    public function __construct($msg, $dup){
        parent::__construct(403, ['message' => $msg, 'dupField' => $dup]);
    }
}
