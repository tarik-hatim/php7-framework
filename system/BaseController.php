<?php
namespace System;
use System\View;
/*
 * controller - base controller
 *
 */
class BaseController
{
    /**
     * view variable to use the view class
     * @var object
     */
    public $view;
    /**
     * url variable to get the current relative url
     * @var string
     */
    public $url;
    /**
     * on run make an instance of the config class and view class
     */
    public function __construct()
    {
        //Initialize the view Object
        $this->view = new View();

        //Get the current relative URL
        $this->url = $this->getUrl();
        
        if(ENVIRONMENT === 'development') {
            $whoops = new \Whoops\Run;
            $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
            $whoops->register();
        }
    }

    protected function getUrl()
    {
        $url = isset($_SERVER['REQUEST_URI']) ? rtrim($_SERVER['REQUEST_URI'], '/') : null;
        $url = filter_var($url, FILTER_SANITIZE_URL);
        return $this->url = $url;
    }
}