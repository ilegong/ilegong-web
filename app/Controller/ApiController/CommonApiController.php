<?php

class CommonApiController extends Controller
{

    static $BANK_TYPES = [
        0 => '工商银行',
        1 => '建设银行',
        2 => '农业银行',
        3 => '邮政储蓄',
        4 => '招商银行',
        5 => '北京银行',
        6 => '交通银行'
    ];

    public function beforeFilter()
    {
        $this->autoRender = false;
    }

    public function get_ship_type_list()
    {
        $list = ShipAddress::ship_type_list();
        echo json_encode($list);
        return;
    }

    public function get_bank_types()
    {
        echo json_encode(self::$BANK_TYPES);
        exit();
    }

    public function upload_image()
    {
        if (count($_FILES["user_files"]) > 0) {
            $folderName = "uploads/";
            $counter = 0;
            $msg = '';
            for ($i = 0; $i < count($_FILES["user_files"]["name"]); $i++) {
                if ($_FILES["user_files"]["name"][$i] <> "") {
                    $ext = strtolower(end(explode(".", $_FILES["user_files"]["name"][$i])));
                    $filePath = $folderName . rand(10000, 990000) . '_' . time() . '.' . $ext;
                    if (!move_uploaded_file($_FILES["user_files"]["tmp_name"][$i], $filePath)) {
                        $msg .= "Failed to upload" . $_FILES["user_files"]["name"][$i] . ". <br>";
                        $counter++;
                    }
                }
                $msg = ($counter == 0) ? "Files uploaded Successfully" : "Erros : " . $msg;
            }
        }
    }


}