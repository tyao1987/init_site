<?php
namespace Admin\Model;

use Admin\Model\Role;

use Application\Model\DbTable;

use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;

use Zend\Form\Form;
use Zend\InputFilter\Factory;
use Zend\InputFilter\InputFilter;

use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

use Zend\Validator\Db\NoRecordExists;

class Job extends DbTable
{
	// use id for role
	const SUPERUSER_ROLE = 1;

	protected $_name = 'job';
	protected $_primary = 'id';

	function __construct(){
		$this->setTableGateway("cmsdb", $this->_name);
		$this->_select = $this->tableGateway->getSql()->select();
	}

	
	public function insertJob($data){
	    
        unset($data['id']);
        unset($data['submit']);
        unset($data['cancel']);
        $this->insert($data);
        return $this->tableGateway->lastInsertValue;
	    
	}
	
	public function updateJob($id,$data){
	    unset($data['id']);
	    unset($data['submit']);
	    unset($data['cancel']);
	    $where[] = $this->quoteInto('id = ?', $id);
	    return $this->tableGateway->update($data, $where);
	}
	
	public function getJobById($id)
	{
	    $result = $this->fetchRow(array('id'=> $id));
	    return $result;
	}
	
	public function paginator($conditions = array()) {
	    unset($conditions['page']);
	    unset($conditions['perpage']);
	    $dbAdapter = $this->tableGateway->getAdapter ();
	    $sql = new Sql ($dbAdapter);
	    if ($conditions) {
	        foreach ($conditions as $key => $val) {
	            $this->_select->where($this->quoteInto("{$key} like ?", '%' .$val. '%'));
	        }
	    }
	    
	    $this->_select->order(array('id DESC'));
	    
	    $adapter = new DbSelect ($this->_select, $sql);
	    $paginator = new Paginator ( $adapter );
	    
	    return $paginator;
	    
	}
	
	
	public function getJobForm($data = array(),$jobId=null){
	    $form = new Form();
	    $form->setAttribute('action', '/job/manage')
	    ->setAttribute('class', 'form-horizontal')
	    ->setAttribute('method', 'post')
	    ->setAttribute('id', 'job_form')
	    ->setAttribute('enctype', 'multipart/form-data');
	    
	    $form->add(array(
	        'name' => 'name',
	        'type' => 'Text',
	        'attributes' => array(
	            'id'    => 'name',
	            'class' => 'form-control',
	            'required'=>'required',
	        ),
	    ));
	    
	    $form->add(array(
	        'name' => 'salary',
	        'type' => 'Text',
	        'attributes' => array(
	            'id'    => 'salary',
	            'class' => 'form-control',
	            'required'=>'required',
	        ),
	    ));
	    
	    $form->add(array(
	        'name' => 'company',
	        'type' => 'Select',
	        'attributes' => array(
	            'id'    => 'company',
	            'class' => 'form-control',
	            'required'=>'required',
	        ),
	        'options' => array(
	            'label' => '公司',
	            'value_options' => array(
	                '上海均瑶科创' => '上海均瑶科创',
	                '上海风寻科技' => '上海风寻科技',
	                '科稷网络' => '科稷网络',
	                '宝镜征信' => '宝镜征信',
	                '广州风寻科技' => '广州风寻科技',
	                '重庆WI-FI' => '重庆WI-FI',
	            ),
	        )
	        
	    ));
	    
	    $form->add(array(
	        'name' => 'address',
	        'type' => 'Select',
	        'attributes' => array(
	            'id'    => 'company',
	            'class' => 'form-control',
	            'required'=>'required',
	        ),
	        'options' => array(
	            'label' => '公司',
	            'value_options' => array(
	                '上海' => '上海',
	                '重庆' => '重庆',
	            ),
	        )
	        
	    ));
	    
// 	    $form->add(array(
// 	        'name' => 'start_date',
// 	        'type' => 'Text',
// 	        'attributes' => array(
// 	            'id'    => 'start_date',
// 	            'class' => 'form-control',
// 	            'required'=>'required',
// 	        ),
// 	    ));
	    
// 	    $form->add(array(
// 	        'name' => 'is_top',
// 	        'type' => 'Select',
// 	        'attributes' => array(
// 	            'id'    => 'is_top',
// 	            'class' => 'form-control',
// 	            'required'=>'required',
// 	        ),
// 	        'options' => array(
// 	            'label' => '置顶',
// 	            'value_options' => array(
// 	                0 => '否',
// 	                1 => '是',
// 	            ),
// 	        )
	        
// 	    ));
	    
	    $form->add(array(
	        'name' => 'description',
	        'type' => 'Textarea',
	        'attributes' => array(
	            'id'    => 'content',
	            'class' => 'form-control',
	            'required'=>'required',
	        ),
	    ));
	    
	    $form->add(array(
	        'name' => 'submit',
	        'type' => 'Submit',
	        'attributes' => array(
	            'value' => '提交',
	            'class' => 'btn btn-primary btn-lg',
	            'style'=>'width: 20%',
	        ),
	    ));
	    
	    $form->add(array(
	        'name' => 'cancel',
	        'type' => 'Button',
	        'options' => array(
	            'label' => '取消',
	            
	        ),
	        'attributes' => array(
	            'value' => 'Cancel',
	            'class' => 'btn btn-primary btn-lg',
	            'style'=>'width: 20%',
	            'onclick' => 'window.location=\'/job/list\';',
	        ),
	    ));
	    
	    $form->add(
	        array(
	            'name' => 'id',
	            'type' => 'Hidden',
	            'attributes' => array(
	                'value' => $jobId,
	            ),
	        ));
	    
	    $inputFilter = new InputFilter();
	    $factory     = new Factory();
	    
	    $inputFilter->add($factory->createInput(array(
	        'name'     => 'name',
	        'required' => true,
	        'allowEmpty' => false,
	        'filters'  => array(
	            array('name' => 'StripTags'),
	            array('name' => 'StringTrim'),
	        ),
	    )));
	    
	    $inputFilter->add($factory->createInput(array(
	        'name'     => 'description',
	        'required' => true,
	        'allowEmpty' => false,
	        'filters'  => array(
	            array('name' => 'StripTags'),
	            array('name' => 'StringTrim'),
	        ),
	    )));
	    
	    $form->setInputFilter($inputFilter);
	    
	    //set data
	    if (is_array ($data)) {
	        $form->setData($data);
	    }
	    
	    return $form;
	    
	}
	
	public function deleteById($id){
	    $ret = $this->tableGateway->delete($this->quoteInto('id=?', $id));
	    return $ret;
	}
	
	public function getList(){
	    $dbAdapter = $this->tableGateway->getAdapter();
	    $this->_select->order("is_top DESC")->order("id DESC");
	    $this->_select->where(array('status' => 1));
	    $this->_select->where(array('is_delete' => 0));
	    return $this->fetchAll($this->_select->getSqlString($dbAdapter->getPlatform()));
	}
}