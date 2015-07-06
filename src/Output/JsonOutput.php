<?php
namespace src\Output;

use src\Config\Config;

class JsonOutput
{
    protected $status;
    protected $data;
    protected $error;

    public function __construct($status, $data, $error){
        $this->status = $status;
        $this->data = $data;
        $this->error = $error;
    }

    public function render()
    {
        // Transformation de data en tableau
        $this->data = $this->toArray($this->data);

        if ($this->error != null)
        {
            $json = array('status' => $this->status, 'error' => $this->error);
        }
        else
        {
            $json = array('status' => $this->status, 'data' => $this->data);
        }

        Config::getApp()->response->setStatus($this->status);
        echo json_encode($json);
        exit;
    }

    public function toArray($value){
        $arrayReturn = $value;
        if (is_array($value)){
            foreach ($value as $key => $v) {
                $arrayReturn[$key] = $this->toArray($v);
            }
        }
        if (is_object($value)){
            $arrayReturn = $value->toArray();
        }
        return $arrayReturn;
    }

}
