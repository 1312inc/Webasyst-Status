(function ($) {
    'use strict';
    $.storage = new $.store();
    $.status = {
        $loading: $('<i class="icon16 loading">'),
        $wa: null,
        defaults: {
            isAdmin: false,
            routingOptions: {},
            userId: 0
        },
        options: {},
        routing: function (options) {
            return {
                options: {
                    user_id: 0,
                    $content: $('#status-content')
                },
                init: function (options) {
                    var that = this;
                    that.options = options;
                    if (typeof($.History) != "undefined") {
                        $.History.bind(function () {
                            that.dispatch();
                        });
                    }

                    var hash = window.location.hash;
                    if (hash === '#/' || !hash) {
                        hash = $.storage.get('/status/hash/' + that.options.user_id);
                        if (hash && hash !== null && hash !== undefined) {
                            $.wa.setHash('#/' + hash);
                        } else {
                            this.dispatch();
                        }
                    } else {
                        $.wa.setHash(hash);
                    }
                },
                // dispatch() ignores the call if prevHash == hash
                prevHash: null,
                skipScrollToTop: false,
                hash: null,
                /** Current hash. No URI decoding is performed. */
                getHash: function () {
                    return this.cleanHash();
                },
                /** Make sure hash has a # in the begining and exactly one / at the end.
                 * For empty hashes (including #, #/, #// etc.) return an empty string.
                 * Otherwise, return the cleaned hash.
                 * When hash is not specified, current hash is used. No URI decoding is performed. */
                cleanHash: function (hash) {
                    if (typeof hash == 'undefined') {
                        // cross-browser way to get current hash as is, with no URI decoding
                        hash = window.location.toString().split('#')[1] || '';
                    }

                    if (!hash) {
                        return '';
                    } else if (!hash.length) {
                        hash = '' + hash;
                    }
                    while (hash.length > 0 && hash[hash.length - 1] === '/') {
                        hash = hash.substr(0, hash.length - 1);
                    }
                    hash += '/';

                    if (hash[0] != '#') {
                        if (hash[0] != '/') {
                            hash = '/' + hash;
                        }
                        hash = '#' + hash;
                    } else if (hash[1] && hash[1] != '/') {
                        hash = '#/' + hash.substr(1);
                    }

                    if (hash == '#/') {
                        return '';
                    }

                    return hash;
                },
                // if this is > 0 then this.dispatch() decrements it and ignores a call
                skipDispatch: 0,
                /** Cancel the next n automatic dispatches when window.location.hash changes */
                stopDispatch: function (n) {
                    this.skipDispatch = n;
                },
                /** Implements #hash-based navigation. Called every time location.hash changes. */
                dispatch: function (hash) {
                    if (this.skipDispatch > 0) {
                        this.skipDispatch--;
                        return false;
                    }
                    if (hash === undefined || hash === null) {
                        hash = window.location.hash;
                    }
                    hash = hash.replace(/(^[^#]*#\/*|\/$)/g, '');
                    /* fix syntax highlight*/
                    if (this.hash !== null) {
                        this.prevHash = this.hash;
                    }
                    this.hash = hash;
                    if (hash) {
                        hash = hash.split('/');
                        if (hash[0]) {
                            var actionName = "";
                            var attrMarker = hash.length;
                            var lastValidActionName = null;
                            var lastValidAttrMarker = null;
                            for (var i = 0; i < hash.length; i++) {
                                var h = hash[i];
                                if (i < 2) {
                                    if (i === 0) {
                                        actionName = h;
                                    } else if (parseInt(h, 10) != h && h.indexOf('=') == -1) {
                                        actionName += h.substr(0, 1).toUpperCase() + h.substr(1);

                                    } else {
                                        break;
                                    }
                                    if (typeof(this[actionName + 'Action']) == 'function') {
                                        lastValidActionName = actionName;
                                        lastValidAttrMarker = i + 1;
                                    }
                                } else {
                                    break;
                                }
                            }
                            attrMarker = i;

                            if (typeof(this[actionName + 'Action']) !== 'function' && lastValidActionName) {
                                actionName = lastValidActionName;
                                attrMarker = lastValidAttrMarker;
                            }

                            var attr = hash.slice(attrMarker);
                            if (typeof(this[actionName + 'Action']) == 'function') {
                                this.preExecute(actionName);
                                console.info('dispatch', [actionName + 'Action', attr]);
                                this[actionName + 'Action'].apply(this, attr);

                                if (actionName !== 'debug') {
                                    $.storage.set('/status/hash/' + this.options.user_id, hash.join('/'));
                                }
                                this.postExecute(actionName);
                            }
                            else {
                                console.info('Invalid action name:', actionName + 'Action');
                            }
                        } else {
                            this.preExecute();
                            this.defaultAction();
                            this.postExecute();
                        }
                    } else {
                        this.preExecute();
                        this.defaultAction();
                        this.postExecute();
                    }
                },
                redispatch: function () {
                    this.prevHash = null;
                    this.dispatch();
                },
                preExecute: function () {},
                postExecute: function () {},
            }
        },

        init: function (o) {
            var self = this;
            self.options = $.extend({}, self.defaults, o);
            self.$wa = $('#wa-app');

            self.options.routingOptions.user_id = self.options.userId;

            this.routing.init(self.options.routingOptions);
        }
    }
}(jQuery));


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
