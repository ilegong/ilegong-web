<?PHP


App::uses('ExceptionRenderer', 'Error');


class CustomExceptionRender extends ExceptionRenderer {

	public function render() {
		$this->controller->layout = 'errors';
		$this->controller->viewClass = 'Dzstyle';
		parent::render();
	}
}