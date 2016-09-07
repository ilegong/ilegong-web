<?php

class WeshareProduct extends AppModel
{

    var $name = 'WeshareProduct';


    public function update_storage_sell($pid, $num)
    {
        if ($num != 0) {
            $tries = 10;
            while ($tries-- > 0) {
                $product = $this->find('first', ['conditions' => ['id' => $pid], 'fields' => ['store', 'sell_num']]);
                if (empty($product)) {
                    return 0;
                }
                $this->updateAll(['sell_num' => 'sell_num + ' . $num], ['id' => $pid, 'sell_num' => $product['WeshareProduct']['sell_num']]);
                if ($this->getAffectedRows() > 0) {
                    return 1;
                }
            }
        }
        return 0;
    }

}