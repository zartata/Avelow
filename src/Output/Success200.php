<?php
namespace src\Output;

class Success200 extends SuccessOutput {
    public function __construct($data){
        parent::__construct(200, $data);
    }
}
