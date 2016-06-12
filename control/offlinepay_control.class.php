<?php
!defined('FRAMEWORK_PATH') && exit('FRAMEWORK_PATH not defined.');

class offlinepay_control extends base_common_control {

    public function on_index() {
        $this->show('offlinepay_index.htm');
    }

}

?>