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

class News extends DbTable
{
	// use id for role
	const SUPERUSER_ROLE = 1;

	protected $_name = 'news';
	protected $_primary = 'id';


	function __construct(){
		$this->setTableGateway("cmsdb", $this->_name);
		$this->_select = $this->tableGateway->getSql()->select();
	}

	
	public function insertNews($data){
	    
	    unset($data['id']);
	    unset($data['submit']);
	    unset($data['cancel']);
	    $this->insert($data);
	    return $this->tableGateway->lastInsertValue;
	}
	
	public function updateNews($id,$data){
	    unset($data['id']);
	    unset($data['submit']);
	    unset($data['cancel']);
	    $where[] = $this->quoteInto('id = ?', $id);
	    return $this->tableGateway->update($data, $where);
	}
	
	public function getNewsById($id)
	{
	    $result = $this->fetchRow(array('id'=> $id));
	    return $result;
	}
	
	public function paginator($conditions = array()) {
	    unset($conditions['page']);
	    unset($conditions['perpage']);
	    $dbAdapter = $this->tableGateway->getAdapter ();
	    $sql = new Sql ( $dbAdapter );
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
	
	
	public function getNewsForm($data = array(),$newsId=null){
	    $form = new Form();
	    $form->setAttribute('action', '/news/manage')
	    ->setAttribute('class', 'form-horizontal')
	    ->setAttribute('method', 'post')
	    ->setAttribute('id', 'news_form')
	    ->setAttribute('enctype', 'multipart/form-data');
	    
	    
// 	    $form->add(array(
// 	        'name' => 'image',
// 	        'type' => 'Text',
// 	        'attributes' => array(
// 	            'id'    => 'image',
// 	            'class' => 'form-control',
// 	            'required'=>'required',
// 	        ),
// 	    ));
	    
// 	    $form->add(array(
// 	        'name' => 'image_big',
// 	        'type' => 'Text',
// 	        'attributes' => array(
// 	            'id'    => 'image_big',
// 	            'class' => 'form-control',
// 	            'required'=>'required',
// 	        ),
// 	    ));
	    
	    $form->add(array(
	        'name' => 'description',
	        'type' => 'Textarea',
	        'attributes' => array(
	            'id'    => 'description',
	            'class' => 'form-control',
	            'required'=>'required',
	        ),
	    ));
	    
	    $form->add(array(
	        'name' => 'title',
	        'type' => 'Text',
	        'attributes' => array(
	            'id'    => 'title',
	            'class' => 'form-control',
	            'required'=>'required',
	        ),
	    ));
	    
	    $form->add(array(
	        'name' => 'start_date',
	        'type' => 'Text',
	        'attributes' => array(
	            'id'    => 'start_date',
	            'class' => 'form-control',
	            'required'=>'required',
	        ),
	    ));
	    
	    $form->add(array(
	        'name' => 'content',
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
	            'onclick' => 'window.location=\'/news/list\';',
	        ),
	    ));
	    
	    $form->add(
	        array(
	            'name' => 'id',
	            'type' => 'Hidden',
	            'attributes' => array(
	                'value' => $newsId,
	            ),
	        ));
	    
	    $inputFilter = new InputFilter();
	    $factory     = new Factory();
	    
	    $inputFilter->add($factory->createInput(array(
	        'name'     => 'title',
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
	    
	    $inputFilter->add($factory->createInput(array(
	        'name'     => 'content',
	        'required' => true,
	        'allowEmpty' => false,
	        'filters'  => array(
	            array('name' => 'StripTags'),
	            array('name' => 'StringTrim'),
	        ),
	    )));
	    
// 	    $inputFilter->add(
// 	        $factory->createInput(array(
// 	            'name'     => 'image',
// 	            'required' => true,
// 	            'allowEmpty' => false,
// 	            'filters'  => array(
// 	                array('name' => 'StripTags'),
// 	                array('name' => 'StringTrim'),
// 	            ),
// 	        ))
// 	    );
	    
// 	    $inputFilter->add(
// 	        $factory->createInput(array(
// 	            'name'     => 'image_big',
// 	            'required' => true,
// 	            'allowEmpty' => false,
// 	            'filters'  => array(
// 	                array('name' => 'StripTags'),
// 	                array('name' => 'StringTrim'),
// 	            ),
// 	        ))
// 	    );
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
	
	public function getList($year,$month){
	    $data = $this->tableGateway->getAdapter();
	    $date = '';
	    if($year){
	        $date = $year;
	    }
	    if($year && $month){
	        $date = $year."-".$month;
	    }
	    if(isset($date)){
	        $this->_select->where($this->quoteInto("start_date like ?", $date. '%'));
	    }
	    $date = date('Y-m-d');
	    $this->_select->where("start_date <= '$date'");
	    $this->_select->where("status = 1");
	    $this->_select->where("is_delete = 0");
	    $this->_select->order("start_date DESC");
	    return $this->fetchAll($this->_select->getSqlString($data->getPlatform()));
	    
	}
	
	public function getIndexList($limit = 5,$offset = 0){
	    $data = $this->tableGateway->getAdapter();
	    $select = new Select();
	    $select->from($this->_name);
	    $date = date('Y-m-d');
	    $this->_select->where("status = 1");
	    $this->_select->where("is_delete = 0");
	    $this->_select->where("start_date <= '$date'");
	    $sql = $this->_select->getSqlString($data->getPlatform());
	    $sql .= " LIMIT ".$limit." OFFSET ".$offset;
	    return $this->fetchAll($sql);
	    
	}
	
	
	public function getNewsDate(){
	    
	    $date = date('Y-m-d');
	    $sql = "SELECT YEAR(news.`start_date`) AS `year`,DATE_FORMAT(news.`start_date`,'%m') AS `month` FROM news where is_delete = 0 and `status` = 1 and start_date <= '$date' GROUP BY `year` asc ,`month` asc";
	    $data =	$this->fetchAll($sql);
	    return  $data;
	}
}