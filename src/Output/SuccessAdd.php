<?php
namespace src\Output;

class SuccessAdd extends SuccessOutput {
    public function __construct($data){
        parent::__construct(201, $data);
    }
}
