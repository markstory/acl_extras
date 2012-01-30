<?php

App::uses('AppHelper', 'View/Helper');

class AclExtrasHelper extends AppHelper {
	
	public $helpers = array('Session');

    public $rootNode = 'controllers';

    function __construct(View $view, $settings = array()) {
        parent::__construct($view, $settings);
        extract($settings);
        if(isset($rootNode)) {
            $this->rootNode = $rootNode;
        }
    }
	
	function hasPermission($url) {
		if (!is_array($url)) {
			return false;
		}
		
		extract($url);
		
		if(!isset($plugin)) {
            $plugin = $this->request->plugin;
		}
        $plugin = Inflector::camelize($plugin);
		
		if (!isset($controller)) {
			$controller = $this->request->controller;
		}  
		$controller = Inflector::camelize($controller);
		
		if (!isset($action)) {
			$action = $this->request->action;
		}
		
		if(isset($plugin) and !empty($plugin)) {
			$controller = $plugin.'/'.$controller;
		}
		
		$permission = $this->rootNode.'/'.$controller.'/'.$action;
		
		return in_array($permission, $this->Session->read('Auth.Permissions'));
	
	}
	
}
