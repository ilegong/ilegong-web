<?php

class AppError extends ErrorHandler {
	
	function __construct($method, $messages) {
		App::uses('Sanitize','Utility');
		static $__previousError = null;

		if ($__previousError != array($method, $messages)) {
			$__previousError = array($method, $messages);
			$this->controller =& new CakeErrorController();
			$this->controller->layout = 'errors';
		} else {
			$this->controller =& new Controller();
			$this->controller->viewPath = 'errors';
		}
		$options = array('escape' => false);
		$messages = Sanitize::clean($messages, $options);

		if (!isset($messages[0])) {
			$messages = array($messages);
		}

		if (method_exists($this->controller, 'apperror')) {
			return $this->controller->appError($method, $messages);
		}

		if (!in_array(strtolower($method), array_map('strtolower', get_class_methods($this)))) {
			$method = 'error';
		}
		if ($method !== 'error') {
			if (Configure::read('debug') == 0) {
				$parentClass = get_parent_class($this);
				if (strtolower($parentClass) != 'errorhandler') {
					$method = 'error404';
				}
				$parentMethods = array_map('strtolower', get_class_methods($parentClass));
				if (in_array(strtolower($method), $parentMethods)) {
					$method = 'error404';
				}
				if (isset($code) && $code == 500) {
					$method = 'error500';
				}
			}
		}
		$this->dispatchMethod($method, $messages);
		$this->_stop();
	}
}