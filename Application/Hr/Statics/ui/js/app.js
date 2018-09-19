$(function() {

    autoLeftNav();
    $(window).resize(function() {
        autoLeftNav();
        // console.log($(window).width())
    });

    //    if(storageLoad('SelcetColor')){

    //     }else{
    //       storageSave(saveSelectColor);
    //     }
})



// 风格切换

$('.tpl-skiner-toggle').on('click', function() {
    $('.tpl-skiner').toggleClass('active');
})

$('.tpl-skiner-content-bar').find('span').on('click', function() {
    $('body').attr('class', $(this).attr('data-color'))
    saveSelectColor.Color = $(this).attr('data-color');
    // 保存选择项
    storageSave(saveSelectColor);

})


// 侧边菜单开关


function autoLeftNav() {

    $('.tpl-header-switch-button').on('click', function() {
        if ($('.left-sidebar').is('.active')) {
            if ($(window).width() > 1024) {
                $('.tpl-content-wrapper').removeClass('active');
            }
            $('.left-sidebar').removeClass('active');
        } else {

            $('.left-sidebar').addClass('active');
            if ($(window).width() > 1024) {
                $('.tpl-content-wrapper').addClass('active');
            }
        }
    })

    if ($(window).width() < 1024) {
        $('.left-sidebar').addClass('active');
    } else {
        $('.left-sidebar').removeClass('active');
    }
}


// 侧边菜单
$('.sidebar-nav-sub-title').on('click', function() {

    $('.sidebar-nav-sub-title').not(this).siblings().slideUp(0);
    $('.sidebar-nav-sub-title').find('.sidebar-nav-sub-ico').removeClass('sidebar-nav-sub-ico-rotate');
    $('.sidebar-nav-sub-title').removeClass('active');
    $(this).siblings('.sidebar-nav-sub').slideToggle(80);
    $(this).find('.sidebar-nav-sub-ico').addClass('sidebar-nav-sub-ico-rotate');
    $(this).addClass('active');

})

// 侧边菜单
$('.sidebar-nav-sub .sidebar-nav-link').on('click', function() {
    //alert($(this).html())
    $('.sidebar-nav-sub .sidebar-nav-link').find('a').removeClass('active');
    $(this).find('a').addClass('active');

})