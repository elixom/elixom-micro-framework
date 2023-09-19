<?php

namespace App\Controllers;

class HtmlController extends \Compago\Controller
{
    protected $view = null;
    protected $views_before = [];
    protected $views_after = [];
    
    public function __invoke($REQUEST,$RESPONSE, $matchRoute) {
        $data = array_merge($_GET, $matchRoute->getMatchedData());
        
        if ($this->view){
            $view = app()->view($this->view);
            $view->with($data);
            $contents = $view->render();
        } else {
            $contents = '';
        }
        
        $arr = [];
        foreach($this->views_before as $vw){
            $view = app()->view($vw);
            $view->with($data);
            $arr[] = $view->render();
        }
        $arr[] = $contents;
        foreach($this->views_after as $vw){
            $view = app()->view($vw);
            $view->with($data);
            $arr[] = $view->render();
        }
        $head = app()->view('layouts.head')->render();
        $foot = app()->view('layouts.foot')->render();
        
        return $head.implode('',$arr).$foot;
    }
}
