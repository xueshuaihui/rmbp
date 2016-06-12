<?php
!defined('FRAMEWORK_PATH') && exit('FRAMEWORK_PATH not defined.');

class pay_control extends base_common_control {

    public function on_index() {
        $this->show('pay_index.htm');
    }
}

?>