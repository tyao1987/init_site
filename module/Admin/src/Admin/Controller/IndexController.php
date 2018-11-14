<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Test\Data;

use Admin\Model\AdminLog;
use Admin\Model\Auth;
use Admin\Model\Bookmark;
use Admin\Model\Message;
use Admin\Model\Note;
use Admin\Model\Sites;
use Admin\Model\User;
use Admin\Util\Post;


class IndexController extends AbstractController
{
    public function indexAction()
    {
    	return new ViewModel ();
    }
    
    public function updateMyPasswordAction()
    {
        if ($this->request->isPost()) {
            $user = new User();
            $data = $this->params()->fromPost();
            $user->updateMyPassword($data);
            $identity = Auth::getIdentity();
            $userInfo = $user->getUserById($identity['id']);
            $logMessage = "修改密码";
            $this->saveLog($logMessage,$this->objToArray($userInfo));
            Auth::destroy();
            return  $this->redirect()->toRoute('default', array('controller'=> 'auth',"action"=>"login"));
            
            //return $this->redirect()->toUrl($url);
        }
    }
    
}
