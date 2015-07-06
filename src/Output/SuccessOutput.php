<?php
namespace src\Output;

class SuccessOutput extends JsonOutput {

    public function __construct($status, $data){
        parent::__construct($status, $data, null);
    }
}
