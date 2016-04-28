<?PHP


App::uses('ExceptionRenderer', 'Error');


class CustomExceptionRender extends ExceptionRenderer {

    public function error500($error) {
        $this->controller->render('/Errors/error500');
        $this->controller->response->send();
    }

    public function error400($error) {
        $this->controller->render('/Errors/error400');
        $this->controller->response->send();
    }

    public function render() {
        if ($this->template) {
            if ($this->template == 'missingAction' || $this->template == 'missingController') {
                $this->controller->redirect('/');
                //$this->error400($this->error);
            } else {
               //call_user_func_array(array($this, $this->method), array($this->error));
                $this->error500($this->error);
            }
        }  else {

            $this->error500($this->error);
        }

    }

}