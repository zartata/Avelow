<?php
namespace src\Output;

class ErrorOutput extends JsonOutput {

    public function __construct($status, $error){
        parent::__construct($status, null, $error);
    }
}
