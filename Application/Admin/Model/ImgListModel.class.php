<?php

namespace Admin\Model;
use Think\Model;

class ImgListModel extends Model {
	/* 自动验证规则 */
    protected $_validate = array(
        array('name', 'require', '名称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
    );
}