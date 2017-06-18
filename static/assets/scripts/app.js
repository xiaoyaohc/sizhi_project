!(function ($) {
    $.AMUI.progress.start();

    var COMMON = {
        RUNTIME:{},
        ready: function(cbk){
            if ('addEventListener' in document) {
                document.addEventListener('DOMContentLoaded', cbk, false);
            }
        },
        initElement: function () {
            var R = this.RUNTIME;
            R.loading = $('#loading');
        },
        init: function () {
            var R = this.RUNTIME;
            this.initElement();

            if(!this.browser.versions.mobile){
                $('html').css({
                    'font-size':'12px',
                    'max-width':'640px',
                    'margin':'auto'
                });
            }

            $(document).ready(function(){
                $.AMUI.progress.done();
                R.loading.hide();
            });
        },
        sprintf : function (str) {
            var args = arguments,
                flag = true,
                i = 1;

            str = str.replace(/%s/g, function () {
                var arg = args[i++];

                if (typeof arg === 'undefined') {
                    flag = false;
                    return '';
                }
                return arg;
            });
            return flag ? str : '';
        },
        getSearchParams:function () {
            var match,
                urlParams = {},
                pl        = /\+/g,  // Regex for replacing addition symbol with a space
                search    = /([^&=]+)=?([^&]*)/g,
                decode    = function (s) { return decodeURIComponent(s.replace(pl, " ")); },
                query     = window.location.search.substring(1);
            while (match = search.exec(query))
                urlParams[decode(match[1])] = decode(match[2]);
            return urlParams;
        },
        browser:{
            versions:function(){
                var u = navigator.userAgent, app = navigator.appVersion;
                return {//移动终端浏览器版本信息
                    trident: u.indexOf('Trident') > -1, //IE内核
                    presto: u.indexOf('Presto') > -1, //opera内核
                    webKit: u.indexOf('AppleWebKit') > -1, //苹果、谷歌内核
                    gecko: u.indexOf('Gecko') > -1 && u.indexOf('KHTML') == -1, //火狐内核
                    mobile: !!u.match(/AppleWebKit.*Mobile.*/), //是否为移动终端
                    ios: !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/), //ios终端
                    android: u.indexOf('Android') > -1 || u.indexOf('Linux') > -1, //android终端或者uc浏览器
                    iPhone: u.indexOf('iPhone') > -1 , //是否为iPhone或者QQHD浏览器
                    iPad: u.indexOf('iPad') > -1, //是否iPad
                    webApp: u.indexOf('Safari') == -1 //是否web应该程序，没有头部与底部
                };
            }(),
            language:(navigator.browserLanguage || navigator.language).toLowerCase()
        },
    //获取url参数
        request: function(name) {
            return this.getSearchParams()[name];
        },
        tips:function(content,time,style){
            var opt = {
                content:content,
                shade:false,
                style:style||'text-align:center;background:rgba(32,32,32,1);opacity:0.8;color:#fff;min-width: initial;border:0;'
            };
            if(time) opt['time'] = time;
            return layer.open(opt);
        },
        getPx: function (px) {
            return px*parseFloat(document.querySelector('html').style.fontSize);
        }
    };

    $(function () {
        COMMON.init();
    });

    window.COMMON = COMMON;

    var APP = {
        initEvent: function () {

        },
        bindEvent: function () {
            $('#course-list').delegate('[show-desc]','click', function () {
                $(this).parents('li').toggleClass('am-active');
            });

            $('#tch-desc,.courses-angle').on('click', function () {
                $('#tch-desc').toggleClass('ellipsis3').next('.courses-angle').toggleClass('am-active');
            });
        },
        myReccords:function(){
            var recordCharts = echarts.init($('#recordCharts')[0]);
            var fz12 = COMMON.getPx(1.2), fz14 = COMMON.getPx(1.4);
            option = {
                title : {
                    left:'center',
                    text: '历史成绩',
                    subtext: '最近5个月',
                    textStyle:{
                        fontSize: fz14
                    },
                    subtextStyle:{
                        fontSize:fz12
                    }
                },
                textStyle:{
                    fontSize:fz12
                },
                grid:{
                    top:'15%'
                },
                tooltip : {
                    trigger: 'axis',
                    textStyle:{
                        fontSize:fz12
                    }
                },
                calculable : true,
                xAxis : [
                    {
                        type : 'category',
                        axisLabel:{
                            textStyle:{
                                fontSize:fz12
                            }
                        },
                        nameTextStyle:{
                            fontSize:fz12
                        },
                        name:"月份",
                        data : ['1月','2月','3月','4月','5月','6月']
                    }
                ],
                yAxis : [
                    {
                        axisLabel:{
                            textStyle:{
                                fontSize:fz12
                            }
                        },
                        nameTextStyle:{
                            fontSize:fz12
                        },
                        name:"分数",
                        type : 'value'
                    }
                ],
                series : [
                    {
                        name:'成绩',
                        type:'bar',
                        data:[ 22, 36, 20, 64, 33,80],
                        markLine : {
                            lineStyle:{
                                normal:{
                                    color:'#314656'
                                }
                            },
                            data : [
                                { type : 'average', name: '平均值'}
                            ]
                        }
                    }
                ]
            };

            recordCharts.setOption(option);
        },
        contact: function () {
            // 百度地图API功能
            var map = new BMap.Map("map");    // 创建Map实例
            map.centerAndZoom(new BMap.Point(116.404, 39.915), 20);  // 初始化地图,设置中心点坐标和地图级别
            map.enableScrollWheelZoom(true);
            var myGeo = new BMap.Geocoder();
            // 将地址解析结果显示在地图上,并调整地图视野
            myGeo.getPoint("广州市海珠区赤岗路13号", function(point){
                if (point) {
                    map.centerAndZoom(point, 20);
                    map.addOverlay(new BMap.Marker(point));
                }else{
                    alert("您选择地址没有解析到结果!");
                }
            });
        },
        teacherList: function () {
            var $tchFilterBox = $('#tch-filter-box'),
                $filterBoxOptions = $('#filter-box-options'),
                $mask = $filterBoxOptions.next('.mask');

            var IScroll = $.AMUI.iScroll,filterScroll;

            var currentField,filterItems = {
                'km':["科目1","科目2","科目3","科目5","科目1","科目2","科目3","科目5","科目1","科目2","科目3","科目5"],
                'xq':["校区1","校区2","校区3","校区5"],
                'px':["综合排序","价格排序","评分最高","人气最高"]
            };

            $tchFilterBox.delegate('.am-btn','click', function () {

                if(currentField != $(this).data('field')){
                    currentField = $(this).data('field');
                    var lis = "";
                    $.each(filterItems[currentField], function (i,v) {
                        lis += '<li>'+ v +'</li>';
                    });
                    $filterBoxOptions.find('ul').html(lis);
                }

                $filterBoxOptions.removeClass('am-hide');
                $mask.removeClass('am-hide');

                if(!filterScroll){
                    filterScroll = new IScroll('#filter-box-options',{
                        scrollbars: true,
                        click: true
                    });
                }else{
                    filterScroll.refresh();
                }

            });

            $mask.on('click', function () {
                $filterBoxOptions.addClass('am-hide');
                $mask.addClass('am-hide');
            });

            $filterBoxOptions.delegate('li','click',function(){
                console.log($(this).text());
                $mask.trigger('click');
                $(this).addClass('.am-active').siblings().removeClass('.am-active');
                $('.am-btn[data-field='+currentField+']').find('span').text($(this).text());
            });
        },
        init:function(){
            this.initEvent();
            this.bindEvent();
        }
    };

    APP.rankHandle = function () {
        var $rank_list = $('#rank-list');
        $rank_list.children('li').on('click', function () {
            $(this).toggleClass('am-active');
        });
        $rank_list.find('.comment').on('click', function (e) {
            e.stopPropagation();
        });
    };

    window.app = APP;

    $(document).ready(function () {
        APP.init();
    });

})(jQuery);