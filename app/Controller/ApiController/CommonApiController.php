<?php

class CommonApiController extends Controller
{

    public function beforeFilter()
    {
        $this->autoRender = false;
    }

    public function get_ship_type_list()
    {
        $list = ShipAddress::ship_type_list();
        echo json_encode($list);
        exit();
    }

    public function get_bank_types()
    {
        echo json_encode(get_bank_types());
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