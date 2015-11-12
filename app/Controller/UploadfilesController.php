<?php
/* 不能改成file，名字与lib/file.php库文件发生冲突 V1.3 */
class UploadfilesController extends AppController {
	var $name = 'Uploadfiles';
	var $helpers = array (
			'Html' 
	);
	var $components = array (
			'Session',
			'SwfUpload' 
	);
	
	function beforeFilter() {
		// print_r($_POST);print_r($_COOKIE);print_r($_FILES);exit;
		// flash传输过来的，只使用session验证
		if (isset ( $_POST ['PHP_SESSION_ID'] ) && ! empty ( $_POST ['PHP_SESSION_ID'] )) {
			$this->Session->id ( $_POST ['PHP_SESSION_ID'] );
			$_COOKIE ['PHPSESSID'] = $_POST ['PHP_SESSION_ID'];
			$_COOKIE ['SAECMS'] ['Auth'] ['Staff'] = $_POST ['SAECMS'] ['Auth'] ['Staff'];
		}
		$this->autoRender = false;
		parent::beforeFilter ();
	}
	
	public function download($id){
		$fileinfo = $this->Uploadfile->findById($id);
		if(!empty($fileinfo)){
			$filename = $fileinfo['Uploadfile']['fspath'];
			header("Content-Type: application/force-download");
			header("Content-Disposition: attachment; filename=".basename($filename));
			echo file_get_contents(UPLOAD_FILE_PATH.$filename);
		}
		else{
			throw new NotFoundException(__('Error url,this url page is not exist.'));
		}
	}

	function delete($id) {
		$file = $this->Uploadfile->findById ( $id );
		$this->log("prevent trying to delete a Upload file $id, values:".var_export($file, true));
		// 删除文件
		//$this->SwfUpload->deletefile ( $file ['Uploadfile'] ['fspath'] );
		// 从数据库删除
		$this->Uploadfile->delete($id);
		$result = array('success'=>true);
		echo json_encode($result);
	}
	
	function upload() {
		//select image
		$select=$_REQUEST['select'];
		$file_post_name = $_POST['file_post_name']?$_POST['file_post_name']:'upload';
		$file_model_name = $_POST ['file_model_name'];
		$info = array (); // return json data
		$info ['status'] = '0';
		$info ['fieldname'] = $file_post_name;
		if (isset ( $this->params ['form'] [$file_post_name] )) {
			//check file size
			// upload the file
			$this->SwfUpload->file_post_name = $file_post_name;
			if ($fileifo = $this->SwfUpload->upload ($file_model_name)) {
                if($_REQUEST['no_db']){// 不保存到数据库，在ckeditor中上传文件的场景
                    $info ['status'] = '1';
                    $info =array_merge($info,$fileifo);
                    $file_url = UPLOAD_FILE_URL . $fileifo['fspath'];
                    //$file_url = str_replace('//', '/', $file_url);
                    if(is_image($file_url)){
                        $info['message'] = '<a href="'.$file_url.'" title="'.__( 'Preview').'" target="_blank"><img src="'.$file_url.'" style="max-height:120px"/></a>';
                    }
                    else{
                        $info['message'] = '<a href="'.$file_url.'" target="_blank">'.__( 'Preview').'</a>';
                    }
                    $info['fspath'] = $file_url;
                }
                else{
                    //check image size
                    $fspath = $fileifo['fspath'];
                    $modelname = Inflector::classify ( $this->name );
                    // save the file to the db, or do whateve ryou want to do with
                    // the data
                    $this->data [$modelname] ['modelclass'] = $file_model_name;
                    $this->data [$modelname] ['fieldname'] = $file_post_name;
                    $this->data [$modelname] ['name'] = $fileifo['filename'];
                    $this->data [$modelname] ['size'] = $this->params ['form'] [$file_post_name] ['size'];
                    $this->data [$modelname] ['fspath'] = $fspath;
                    $this->data [$modelname] ['type'] = $fileifo['file_type'];
                    if (empty($_REQUEST['no_thumb']) && 'image' == substr ($fileifo['file_type'], 0, 5 )) {
                        $this->data [$modelname] ['thumb'] = $fileifo['thumb'];
                        $this->data [$modelname] ['mid_thumb'] = $fileifo['mid_thumb'];
                    }
                    if($file_model_name == 'Weshare'){
//                        if (substr ( $fspath, 0, 7 ) != 'http://') {
//                            $img_src_url = Router::url (str_replace ( '//', '/', $this->request->webroot . $fspath ));
//                        } else {
//                            $img_src_url = $fspath;
//                        }
                        if (substr ( $fileifo['mid_thumb'], 0, 7 ) != 'http://') {
                            $s_img_url =  UPLOAD_FILE_URL.str_replace ( '//', '/', ($fileifo['mid_thumb']) );;
                        } else {
                            $s_img_url = $fileifo['mid_thumb'];
                        }
                        $info ['status'] = '1';
                        $info ['message'] = '<div class="ui-upload-filelist" style="float:left;"><img src="'.$s_img_url.'" width="100px" height="100px"><br><p><a href="'.$s_img_url.'" target="_blank">预览</a>&nbsp;&nbsp;&nbsp;<a class="upload-file-delete" onclick="deleteImg(this);">删除</a></p></div>';
                    }else{
                        if (! ($file = $this->Uploadfile->save ( $this->data ))) {
                            $this->Session->setFlash ( 'Database save failed' );
                            $info ['message'] = $this->SwfUpload->filename . ' Database save failed'; // 保存记录时失败
                        } else {
                            $info ['status'] = '1';
                            $file_id = $this->Uploadfile->getLastInsertId ();
                            $info ['fspath'] = $this->data [$modelname] ['fspath'];
                            $info ['file_id'] = $file_id;
                            $info ['message'] = '<div class="ui-upload-filelist" style="float:left;">';

                            if (substr ( $this->data [$modelname] ['thumb'], 0, 7 ) != 'http://') {
                                $file_url = UPLOAD_FILE_URL.str_replace ( '//', '/', ($this->data [$modelname] ['thumb']) );
                                $mid_url =  UPLOAD_FILE_URL.str_replace ( '//', '/', ($this->data [$modelname] ['mid_thumb']) );;
                            } else {
                                $file_url = $this->data [$modelname] ['thumb'];
                                $mid_url = $this->data [$modelname] ['mid_thumb'];
                            }

                            $info['fspath'] = $file_url;

                            if ('image' == substr ( $this->data [$modelname] ['type'], 0, 5 )) {
                                if($file_model_name == 'Weshare'){
                                    $info ['message'] .= '<img src="' . $mid_url . '" width="100px" height="100px"/><br/>';
                                }else{
                                    $info ['message'] .= '<img src="' . $file_url . '" width="100px" height="100px"/><br/>';
                                }
                            }
                            else{
                                $info ['message'] .='<a href="' . $file_url . '" target="_blank">'.$this->data [$modelname] ['name'].'</a>';
                            }
                            $info ['message'] .= '<input type="hidden" name="data[Uploadfile][' . $file_id . '][id]" value="' . $file_id . '">';

                            if ('image' == substr ( $this->data [$modelname] ['type'], 0, 5 )) {
                                //check
                                if(strpos($mid_url,'/')==0){
                                    $mid_thumb_url = str_replace ( '\\', '/', $this->data [$modelname] ['mid_thumb'] );
                                }else if(strpos($mid_url,'http')==0){
                                    $mid_thumb_url = $mid_url;
                                } else{
                                    $mid_thumb_url = str_replace ( '\\', '/', $this->request->webroot . $this->data [$modelname] ['mid_thumb'] );
                                }
                                if (substr ( $this->data [$modelname] ['fspath'], 0, 7 ) != 'http://') {
                                    $src_url = Router::url (str_replace ( '//', '/', $this->request->webroot . ($this->data [$modelname] ['fspath']) ));
                                } else {
                                    $src_url = $this->data [$modelname] ['fspath'];
                                }
                            }
                            //商家管理产品可以选择多幅图片
                            if($select){
                                $info ['message'] .= '<p>
							<a href="' . $src_url . '" target="_blank">' . __ ( '预览') . '</a>
			        		<a class="upload-file-delete" onclick="deleteImg(this);" rel="'.$file_id.'" data-url="' . Router::url ( '/uploadfiles/delete/' . $file_id.'.json') . '">' . __ ( '删除') . '</a>';
                                $info['messgae'] .='<a href="javascript:void(0);" onclick="setCoverImg(\'' . $this->data [$modelname] ['modelclass'] . '\',\'' . $mid_thumb_url . '\');">' . __ ( 'Set as title img') . '</a>';
                                $info['messgae'] .='</p>';
                            }
                            $info ['message'] .= '</div>';
                        }
                    }
                }
			} else {
				$info ['message'] = $this->SwfUpload->errorMessage;
				$this->Session->setFlash ( $this->SwfUpload->errorMessage );
			}
		} else {
			$info ['message'] = 'empty field name';
		}
		if($_REQUEST['return']=='ckeditor'){
			if($info ['status']){
				echo '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction(2, "'.(UPLOAD_FILE_URL.$fileifo['fspath']).'", "");</script>';
			}
			else{
				echo '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction(2, "", "'.$info ['message'].'");</script>';
			}
		}
		else{
			$result = json_encode ( $info );
			echo $result;
		}
		exit ();
	}
}
?>