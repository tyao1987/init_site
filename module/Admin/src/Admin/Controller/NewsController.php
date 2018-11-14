<?php
namespace Admin\Controller;

use Admin\Model\Action;
use Admin\Model\Controller;
use Admin\Model\Module;
use Admin\Model\Role;
use Admin\Model\RoleAction;
use Admin\Model\Sites;
use Admin\Model\SiteGroup;
use Admin\Model\News;
use Admin\Model\DealCache ;
use Test\Data;

use Application\Service\Cache;
use Application\Util\Util;

use Zend\Form\Form;
use Zend\InputFilter\Factory;
use Zend\InputFilter\InputFilter;
use Admin\Model\Auth;

use Zend\View\Model\ViewModel;
use Admin\Util\Post;


class NewsController extends AbstractController {

    protected $_listPath = ROOT_PATH . '/public/images/tmp/';
    
    public function checkImage(){
        
        $result = array();
        $result['error'] = '';
        $uploadconfig = include (__DIR__ . "/../../../config/upload.config.php");
        if (!is_dir($this->_listPath)) {
            @mkdir ($this->_listPath, 0777, true );
        }
        
        $pathinfo = pathinfo ( $_FILES ['file']['name'] );
        //$fileName = $pathinfo ['filename'];
        $ext = strtolower ( $pathinfo ['extension'] );
        $fileNameWithExt = uniqid() . '.' . $ext;
        $allowExt = array_keys ($uploadconfig ['extension']);
        
        if ($_FILES ['file'] ['error'] > 0) {
            $result['error'] = "{$fileNameWithExt} 上传错误. 错误原因 : " . $this->getErr ( $_FILES ['file'] ['error'] );
            return $result;
        }
        
        if (! in_array ( $ext, $allowExt )) {
            $result['error'] = $ext . " 文件类型 {$fileNameWithExt} 错误";
            return $result;
        }
        
        $mime = $uploadconfig ['extension'] [$ext];
        $mimearray = array ();
        if (count ( $mime ['mimetyle'] ) > 1) {
            $mimearray = $mime ['mimetyle'];
        } else {
            $mimearray [] = $mime ['mimetyle'];
        }
        
        if (! is_uploaded_file ( $_FILES ['file'] ['tmp_name'] )) {
            $result['error'] = "文件上次错误 文件名称" . $_FILES ['file'] ['tmp_name'];
            return $result;
        }
        
        $finfo = finfo_open ( 16 );
        $filetype = finfo_file ( $finfo, $_FILES ['file'] ['tmp_name'] );
        
//         if (! in_array ( $filetype, $mimearray )) {
//             $result['error'] = $filetype . ".' 文件类型 {$fileNameWithExt} 错误'";
//             return $result;
//         }
        
        finfo_close($finfo);
        
        move_uploaded_file ( $_FILES ['file'] ['tmp_name'], $this->_listPath .'/'. $fileNameWithExt );
        if(file_exists($this->_listPath .'/'. $fileNameWithExt)){
            $data = Data::getInstance();
            $config = $data->get('config');
            $imageServer = $config['imageServer'];
            if(substr($imageServer, -1) == '/'){
                $imageServer = substr($imageServer, 0,-1);
            }
            $result['path'] = $imageServer . '/tmp/'. $fileNameWithExt;
        }else{
            $result['error'] = '上传图片失败';
        }
        return $result;
        
    }
    
	public function indexAction(){
		return $this->redirect()->toRoute('default', array(
						'controller'=> 'news',
						'action'    => 'list',
				));
	}
	
	public function reactiveAction() {
	    $id = (int)$this->params()->fromRoute("id", 0);
	    $news = new News();
	    $row = $news->getNewsById($id);
	    if($row && $row['is_delete'] == 1){
	        $data = $this->objToArray($row);
	        $data['is_delete'] = 0;
	        $news->updateNews($row['id'], $data);
	        $logMessage = "取消删除新闻 id:".$row['id'];
	        $this->saveLog($logMessage,$this->objToArray($data));
	    }
	    return $this->redirect()->toRoute('default', array(
	        'controller'=> 'news',
	        'action'    => 'list'
	    ));
	}
	
	public function listAction(){
	    $param = $this->params()->fromQuery();
	    $news = new News();
	    if(!isset($param['is_delete'])){
	        $param['is_delete'] = 0;
	    }
	    //$param['is_delete'] = 0;
	    $paginator = $news->paginator($param);
	    $paginator->setCurrentPageNumber ( ( int ) $param ['page'] );
	    if(empty($param['perpage'])){
	        $param['perpage'] = 20;
	    }
	    $paginator->setItemCountPerPage ( $param['perpage'] );
	    
	    
	    $viewData ['paginator'] = $paginator;
	    $viewData ['param'] = $param;
	    $viewData = array_merge ($viewData, $param);
	    
	    return new ViewModel ($viewData);

	}
	
	public function manageAction() {
	    $user = Auth::getIdentity();
	    $news = new News();
	    $errorMsg = "";
	    $imagePath = '';
	    if ($this->request->isPost()) {
	        $form = $news->getNewsForm($_POST);
	        if($form->isValid()){
	            $data = $form->getData();
	            $data['content'] = $_POST['content'];
	            $authIdentity = Auth::getIdentity();
	            $data['user_id'] = $authIdentity['id'];
	            $id = (int) $data['id'];
	            if($data['id'] == 0){
	                $result = $this->checkImage();
	                if($result['error'] == ''){
	                    $data['image'] = $result['path'];
	                    $insertId = $news->insertNews($data);
	                    $logMessage = "添加新闻 id:".$insertId;
	                    $this->saveLog($logMessage,$this->objToArray($news->getNewsById($insertId)));
	                    return $this->redirect()->toRoute('default', array(
	                        'controller'=> 'news',
	                        'action'    => 'list'
	                    ));
	                }else{
	                    $errorMsg = $result['error'];
	                }
	            }else{
	                $row = $news->getNewsById($data['id']);
	                if($row && $row['status'] != 1){
	                    $data['status'] = 0;
	                    if($_FILES['file']['name'] != ''){
	                        $result = $this->checkImage();
	                        if($result['error'] == ''){
	                            $data['image'] = $result['path'];
	                            $oldPathArray = explode('/', $row['image']);
	                            $oldImage = end($oldPathArray);
	                            $news->updateNews($row['id'], $data);
	                            @unlink($this->_listPath."/".$oldImage);
	                            $logMessage = "编辑新闻 id:".$row['id'];
	                            $this->saveLog($logMessage,$this->objToArray($data));
	                            return $this->redirect()->toRoute('default', array(
	                                'controller'=> 'news',
	                                'action'    => 'list'
	                            ));
	                        }else{
	                            $errorMsg = $result['error'];
	                        }
	                    }else{
	                        $news->updateNews($row['id'], $data);
	                        $logMessage = "编辑新闻 id:".$row['id'];
	                        $this->saveLog($logMessage,$this->objToArray($data));
	                        return $this->redirect()->toRoute('default', array(
	                            'controller'=> 'news',
	                            'action'    => 'list'
	                        ));
	                    }
	                    
	                    
	                    
	                }else{
	                    $errorMsg = "不能修改";
	                }
	            }
	            
	        }
 	    }else{
 	        $newsId = (int)$this->params()->fromRoute("id", 0);
 	        if($newsId > 0){
 	            $row = $news->getNewsById($newsId);
 	            if($row && $row['status'] != 1){
 	                $data = $this->objToArray($row);
 	                $imagePath = $data['image'];
 	                $form = $news->getNewsForm($data);
 	            }else{
 	                return $this->redirect()->toRoute('default', array(
 	                    'controller'=> 'auth',
 	                    'action'    => 'no-auth',
 	                ));
 	            }
 	        }else{
 	            $form = $news->getNewsForm();
 	        }
 	    }
	    
	    $viewData = array ();
	    $viewData['form'] = $form;
	    $viewData['error'] = $form->getMessages();
	    if($errorMsg != ""){
	        $viewData['error']['save'] = $errorMsg;
	    }
	    if(isset($viewData['error']['content']['isEmpty'])){
	        unset($viewData['error']['content']['isEmpty']);
	        $viewData['error']['错误']['新闻内容'] = "必须填写";
	    }
	    $viewData['imagePath'] = $imagePath;
	    return new ViewModel($viewData);
	    
	}

	public function deleteAction() {
	    $id = (int)$this->params()->fromRoute("id", 0);
	    $news = new News();
	    $id = (int)$this->params()->fromRoute("id", 0);
	    $news = new News();
	    $row = $news->getNewsById($id);
	    if($row && $row['is_delete'] == 0){
	        $data = $this->objToArray($row);
	        $data['is_delete'] = 1;
	        $news->updateNews($row['id'], $data);
	        $logMessage = "删除新闻 id:".$row['id'];
	        $this->saveLog($logMessage,$this->objToArray($data));
	    }
	    return $this->redirect()->toRoute('default', array(
	        'controller'=> 'news',
	        'action'    => 'list'
	    ));
	}
	
	public function resetVerifyAction() {
	    $id = (int)$this->params()->fromRoute("id", 0);
	    $news = new News();
	    $id = (int)$this->params()->fromRoute("id", 0);
	    $news = new News();
	    $row = $news->getNewsById($id);
	    if($row && $row['status'] == 1){
	        $data = $this->objToArray($row);
	        $data['status'] = 0;
	        $news->updateNews($row['id'], $data);
	        $logMessage = "下架新闻 id:".$row['id'];
	        $this->saveLog($logMessage,$this->objToArray($data));
	    }
	    return $this->redirect()->toRoute('default', array(
	        'controller'=> 'news',
	        'action'    => 'list'
	    ));
	}
	
	public function verifyAction() {
	    $id = (int)$this->params()->fromRoute("id", 0);
	    $news = new News();
	    $row = $news->getNewsById($id);
	    if($row && $row['status'] == 0){
	        $data = $this->objToArray($row);
	        $data['status'] = 1;
	        $news->updateNews($row['id'], $data);
	        $logMessage = "审核通过新闻 id:".$row['id'];
	        $this->saveLog($logMessage,$this->objToArray($data));
	        return $this->redirect()->toRoute('default', array(
	            'controller'=> 'news',
	            'action'    => 'list'
	        ));
	    }else{
	        return $this->redirect()->toRoute('default', array(
	            'controller'=> 'auth',
	            'action'    => 'no-auth',
	        ));
	    }
	}
	
	public function notVerifyAction() {
	    $id = (int)$this->params()->fromRoute("id", 0);
	    $news = new News();
	    $row = $news->getNewsById($id);
	    if($row && $row['status'] == 0){
	        $data = $this->objToArray($row);
	        $data['status'] = 2;
	        $news->updateNews($row['id'], $data);
	        $logMessage = "审核未通过新闻 id:".$row['id'];
	        $this->saveLog($logMessage,$this->objToArray($data));
	        return $this->redirect()->toRoute('default', array(
	            'controller'=> 'news',
	            'action'    => 'list'
	        ));
	    }else{
	        return $this->redirect()->toRoute('default', array(
	            'controller'=> 'auth',
	            'action'    => 'no-auth',
	        ));
	    }
	}
}