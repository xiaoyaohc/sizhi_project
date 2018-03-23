<?php
namespace Home\Controller;
use Home;
class ResumeController extends HomeController{
    public function index(){
    }
    //交互简历
    public function interactive(){
        $file='./Public/Home/assets/other/images/interactive.pdf';

                header('Content-type: application/pdf');
                header('filename='.$file);
                readfile($file);
      /*  header("Content-type:application/pdf");
        readfile("./Public/Home/assets/other/images/交互-袁园.pdf");*/
      $this->display();
    }
    //产品简历
    public function  product(){

    }
    //项目集简历
    public function  items(){

    }
}