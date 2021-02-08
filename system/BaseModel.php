<?php 
namespace System;
use App\Config;
use App\Helpers\Database;

class BaseModel 
{
    protected $db;

    public function __construct()
    {
        $config = Config::get();
        $this->db = Database::get($config);
    }
}