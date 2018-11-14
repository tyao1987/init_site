<?php
namespace Admin\Controller;

use Admin\Model\Action;
use Admin\Model\Controller;
use Admin\Model\Module;
use Admin\Model\Role;
use Admin\Model\RoleAction;
use Admin\Model\Sites;
use Admin\Model\SiteGroup;
use Admin\Model\Job;
use Admin\Model\DealCache ;

use Application\Service\Cache;
use Application\Util\Util;

use Zend\Form\Form;
use Zend\InputFilter\Factory;
use Zend\InputFilter\InputFilter;
use Admin\Model\Auth;

use Zend\View\Model\ViewModel;
use Admin\Util\Post;


class JobController extends AbstractController {

    
	public function indexAction(){
		return $this->redirect()->toRoute('default', array(
						'controller'=> 'recruit',
						'action'    => 'list',
				));
	}

	public function listAction(){
	    $param = $this->params()->fromQuery();
	    $job = new Job();
	    if(!isset($param['is_delete'])){
	        $param['is_delete'] = 0;
	    }
	    $paginator = $job->paginator($param);
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
	    $job = new Job();
	    $errorMsg = "";
	    if ($this->request->isPost()) {
	        $form = $job->getJobForm($_POST);
	        if($form->isValid()){
	            $data = $form->getData();
	            $data['description'] = $_POST['description'];
	            $authIdentity = Auth::getIdentity();
	            $data['user_id'] = $authIdentity['id'];
	            $id = (int) $data['id'];
	            if($data['id'] == 0){
	                $insertId = $job->insertJob($data);
	                $logMessage = "添加招聘 id:".$insertId;
	                $this->saveLog($logMessage,$this->objToArray($job->getJobById($insertId)));
	                return $this->redirect()->toRoute('default', array(
	                    'controller'=> 'job',
	                    'action'    => 'list'
	                ));
	            }else{
	                $row = $job->getJobById($data['id']);
	                if($row && $row['status'] != 1){
	                    $data['status'] = 0;
	                    $job->updateJob($row['id'], $data);
	                    $logMessage = "编辑招聘 id:".$row['id'];
	                    $this->saveLog($logMessage,$this->objToArray($data));
	                    return $this->redirect()->toRoute('default', array(
	                        'controller'=> 'job',
	                        'action'    => 'list'
	                    ));
	                }else{
	                    $errorMsg = "不能修改";
	                }
	            }
	            
	        }
 	    }else{
 	        $jobId = (int)$this->params()->fromRoute("id", 0);
 	        if($jobId > 0){
 	            $row = $job->getJobById($jobId);
 	            if($row && $row['status'] != 1){
 	                $data = $this->objToArray($row);
 	                $form = $job->getJobForm($data);
 	            }else{
 	                return $this->redirect()->toRoute('default', array(
 	                    'controller'=> 'auth',
 	                    'action'    => 'no-auth',
 	                ));
 	            }
 	        }else{
 	            $form = $job->getJobForm();
 	        }
 	    }
	    
	    $viewData = array ();
	    $viewData['form'] = $form;
	    $viewData['error'] = $form->getMessages();
	    if($errorMsg != ""){
	        $viewData['error']['save'] = $errorMsg;
	    }
	    if(isset($viewData['error']['description']['isEmpty'])){
	        $viewData['error']['错误']['工作描述'] = "必须填写";
	    }
	    return new ViewModel($viewData);
	    
	}

	public function resetVerifyAction() {
	    $id = (int)$this->params()->fromRoute("id", 0);
	    $job = new Job();
	    $row = $job->getJobById($id);
	    if($row && $row['status'] == 1){
	        $data = $this->objToArray($row);
	        $data['status'] = 0;
	        $job->updateJob($row['id'], $data);
	        $logMessage = "下架招聘 id:".$row['id'];
	        $this->saveLog($logMessage,$this->objToArray($data));
	    }
	    return $this->redirect()->toRoute('default', array(
	        'controller'=> 'job',
	        'action'    => 'list'
	    ));
	}
	
	public function deleteAction() {
	    $id = (int)$this->params()->fromRoute("id", 0);
	    $job = new Job();
	    $row = $job->getJobById($id);
	    if($row && $row['is_delete'] == 0){
	        $data = $this->objToArray($row);
	        $data['is_delete'] = 1;
	        $job->updateJob($row['id'], $data);
	        $logMessage = "删除招聘 id:".$row['id'];
	        $this->saveLog($logMessage,$this->objToArray($data));
	    }
	    return $this->redirect()->toRoute('default', array(
	        'controller'=> 'job',
	        'action'    => 'list'
	    ));
	}
	
	public function reactiveAction() {
	    $id = (int)$this->params()->fromRoute("id", 0);
	    $job = new Job();
	    $row = $job->getJobById($id);
	    if($row && $row['is_delete'] == 1){
	        $data = $this->objToArray($row);
	        $data['is_delete'] = 0;
	        $job->updateJob($row['id'], $data);
	        $logMessage = "取消删除招聘 id:".$row['id'];
	        $this->saveLog($logMessage,$this->objToArray($data));
	    }
	    return $this->redirect()->toRoute('default', array(
	        'controller'=> 'job',
	        'action'    => 'list'
	    ));
	}
	
	public function verifyAction() {
	    $id = (int)$this->params()->fromRoute("id", 0);
	    $job = new Job();
	    $row = $job->getJobById($id);
	    if($row && $row['status'] == 0){
	        $data = $this->objToArray($row);
	        $data['status'] = 1;
	        $job->updateJob($row['id'], $data);
	        $logMessage = "审核通过招聘 id:".$row['id'];
	        $this->saveLog($logMessage,$this->objToArray($data));
	        return $this->redirect()->toRoute('default', array(
	            'controller'=> 'job',
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
	    $job = new Job();
	    $row = $job->getJobById($id);
	    if($row && $row['status'] == 0){
	        $data = $this->objToArray($row);
	        $data['status'] = 2;
	        $job->updateJob($row['id'], $data);
	        $logMessage = "审核未通过招聘 id:".$row['id'];
	        $this->saveLog($logMessage,$this->objToArray($data));
	        return $this->redirect()->toRoute('default', array(
	            'controller'=> 'job',
	            'action'    => 'list'
	        ));
	    }else{
	        return $this->redirect()->toRoute('default', array(
	            'controller'=> 'auth',
	            'action'    => 'no-auth',
	        ));
	    }
	}
	
	public function topAction() {
	    $id = (int)$this->params()->fromRoute("id", 0);
	    $job = new Job();
	    $row = $job->getJobById($id);
	    if($row && $row['is_top'] == 0){
	        $data = $this->objToArray($row);
	        $data['is_top'] = 1;
	        $job->updateJob($row['id'], $data);
	        $logMessage = "置顶招聘 id:".$row['id'];
	        $this->saveLog($logMessage,$this->objToArray($data));
	        return $this->redirect()->toRoute('default', array(
	            'controller'=> 'job',
	            'action'    => 'list'
	        ));
	    }else{
	        return $this->redirect()->toRoute('default', array(
	            'controller'=> 'auth',
	            'action'    => 'no-auth',
	        ));
	    }
	}
	
	public function cannelTopAction() {
	    $id = (int)$this->params()->fromRoute("id", 0);
	    $job = new Job();
	    $row = $job->getJobById($id);
	    if($row && $row['is_top'] == 1){
	        $data = $this->objToArray($row);
	        $data['is_top'] = 0;
	        $job->updateJob($row['id'], $data);
	        $logMessage = "取消置顶招聘 id:".$row['id'];
	        $this->saveLog($logMessage,$this->objToArray($data));
	        return $this->redirect()->toRoute('default', array(
	            'controller'=> 'job',
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