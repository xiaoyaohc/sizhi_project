<?php
namespace Admin\Model;
use Think\Model;

class WorkModel extends Model {
	/* 自动验证规则 */
    protected $_validate = array(
        array('name', 'require', '名称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('img', 'require', '请上传图片', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
    );
}