/**
 * Created by lenovo on 15-8-16.
 */
$(function(){
    // 选框验证验证
    jQuery.validator.addMethod("isSelect", function(value, element) {
        return this.optional(element) || (value!=0);
    }, "请选择内容!");
$('.form-horizontal').validate({
    rules:{
        class_name:{
            required:true
        },
        subject_id:{
            required:true,
            isSelect:true
        },
        teacher_id:{
            required:true,
            isSelect:true
        },
        course_dates:{
            required:true
        },
        time_interval:{
            required:true
        },
        sale_status:{
            required:true
        },
        class_place:{
            required:true
        },
        class_num:{
            required:true,
            digits:true
        },
        class_price:{
            required:true,
            number:true
        },
        original_price:{
            required:true,
            number:true
        },
        proportion:{
            required:true,
            number:true
        },
        class_room:{
            required:true
        },
        class_desc:{
            required:true
        },
    },
    //验证成功后，将属性class变成class right
    success:function(lable){
        lable.html("&nbsp;").addClass("right");
    },

    invalidHandler: function(form, validator) {  //不通过回调
        return false;
    }
   });

});

