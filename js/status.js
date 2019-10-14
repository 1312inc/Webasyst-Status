$(document).ready(function(){

    //init slider
    $( "#s-editor-slider-slider" ).slider({
      range: true,
      min: 0,
      max: 1440,
      values: [ 540, 1080 ],
      slide: function( event, ui ) {
          //меняем цвет слайдера на s-active, чтобы показать, что данные сохранились
          $('#s-editor-slider-slider.ui-slider').addClass('s-active');
      },
      change: function( event, ui ) {
          //показываем опциаональную детализацию по проектам
          $('.s-editor-slider-projects').slideDown(200);
          //индикатор сохранения — показываем крутилку
          $('.s-editor-commit-indicator').show();
          $('.s-editor-commit-indicator i.icon16').removeClass('yes-bw').addClass('loading');
          setTimeout(function(){
              //тут на самом деле просто ждем ответа от сервера и показываем снова галочку "сохранено" (в прототипе интерфейса индикатор привязан только к слайдеру, но вообще стоит его использовать для любых аяксов внутри s-editor)
              $('.s-editor-commit-indicator i.icon16').removeClass('loading').addClass('yes-bw');
          }, 1312);
      }
    });

    //при выборе проектов закрашиваем слайдер цветами проектов в заданных пропорциях. делаем это средствами градиентов css.
    $('.s-editor-project input[type="checkbox"]').click(function(){
        $('#s-editor-slider-slider.ui-slider-horizontal .ui-widget-header').addClass('s-colorized-1312-demo-purpose-class-only');
        $(this).closest('.s-editor-project').addClass('selected');
    });

    //перерыв просто уменьшает общее время
    $('.s-editor-slider-break input[type="checkbox"]').click(function(){
        $('.s-editor-slider-total h2').text('7h 31m');
    });

    //если вводят текстовый отчет за день, то индикатор заменяется на кнопку сохранения
    $('.s-editor-comment').bind('input propertychange',function(){
        $('.s-editor-commit-button').show();
        $('.s-editor-commit-indicator').hide();
    });
    $('.s-editor-commit-button').click(function(){
        $('.s-editor-commit-button').hide();
        $('.s-editor-commit-indicator').show();
    });

    //+ напротив слайдера добавляет еще один слайдер за этот день
    $('.s-editor-slider-more a').click(function(){
        alert('клонирует всю группу .s-editor-slider, позволяя добавить на этот день еще один интервал');
        return false;
    });

    //любоу duration (перерыва и по проекту в чекине) — по клику открываем мини-инпут для ввода количества часов (можно ввести дробное количество 2.5, и тогда по focusout будет пересчитано в часа-минутых 2h 30m)
    $('.s-duration-label').click(function(){
        $(this).hide();
        $(this).closest('.s-duration').children('.s-duration-input').show().select();
        //$('.s-break-duration-input').select();
        return false;
    });
    $('.s-duration-input').focusout(function(){
        $('.s-duration-label').show();
        $('.s-duration-input').hide();
    });

    $('.s-status-custom-status').click(function(){
        $('<div>input: <b>[ enter custom status label ]</b><br><br> radio:<br> <b>(*) calendar name</b><br><b>( ) calendar name</b><br><b>( ) calendar name</b></div>').waDialog({
            'height' : '400px',
            'width' : '660px',
            'onClose' : function(f) {
                    $(this).remove;
            },
            'esc' : true,
        });
    });

});
