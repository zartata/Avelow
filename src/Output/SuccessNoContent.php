<?php
namespace src\Output;

class SuccessNoContent extends SuccessOutput {
    public function __construct(){
        parent::__construct(204, null);
    }
}
