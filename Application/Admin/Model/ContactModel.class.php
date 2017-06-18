<?php

namespace Admin\Model;
use Think\Model;

class ContactModel extends Model {

	protected $_validate = array(
		array('name','require','名称标题必须填写'), //默认情况下用正则进行验证
	);
}