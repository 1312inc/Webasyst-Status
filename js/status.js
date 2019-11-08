(function ($) {
    'use strict';
    $.storage = new $.store();
    $.status = {
        $loading: $('<i class="icon16 loading">'),
        $wa: null,
        $status_content: null,
        $core_sidebar: null,
        defaults: {
            isAdmin: false,
            routingOptions: {},
            userId: 0
        },
        options: {},
        routing: {
            options: {
                self: null,
                user_id: 0,
                content_selector: '#status-content'
            },
            init: function (options) {
                var that = this;
                that.options = $.extend({}, that.options, options);
                if (typeof ($.History) != "undefined") {
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
                                if (typeof (this[actionName + 'Action']) == 'function') {
                                    lastValidActionName = actionName;
                                    lastValidAttrMarker = i + 1;
                                }
                            } else {
                                break;
                            }
                        }
                        attrMarker = i;

                        if (typeof (this[actionName + 'Action']) !== 'function' && lastValidActionName) {
                            actionName = lastValidActionName;
                            attrMarker = lastValidAttrMarker;
                        }

                        var attr = hash.slice(attrMarker);
                        if (typeof (this[actionName + 'Action']) == 'function') {
                            this.preExecute(actionName);
                            console.info('dispatch', [actionName + 'Action', attr]);
                            this[actionName + 'Action'].apply(this, attr);

                            $.storage.set('/status/hash/' + this.options.user_id, hash.join('/'));
                            this.postExecute(actionName);
                        } else {
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
            defaultAction: function () {
                this.contactAction(0);
                $.storage.set('/status/hash/' + this.options.user_id, '');
            },
            contactAction: function (id) {
                var that = this;
                $.get('?module=chronology&contact_id=' + id, function (html) {
                    $('#status-content').html(html);
                });
            },
            projectAction: function (id) {
                var that = this;
                $.get('?module=chronology&project_id=' + id, function (html) {
                    $('#status-content').html(html);
                });
            },
            preExecute: function () {
            },
            postExecute: function () {
                this.options.self.highlightSidebar();
            },
        },
        lazyLoad: function (config) {
            $(window).off('scroll.stts');

            if (config) {
                var $loading = config.$loading,//$('#pl-list-content .lazyloading'),
                    is_bottom = false,
                    request_in_action = false,
                    prev_scroll_pos = 0,
                    offset = config.offset || 1 ,
                    this_is_the_end = false,
                    html_selector = config.html_selector;//'#pl-complete-log > .menu-v';

                $(window).on('scroll.stts', function () {
                    if (this_is_the_end) {
                        return;
                    }

                    var scroll_pos = $(document).scrollTop() + $(window).outerHeight(),
                        doc_h = $(document).outerHeight() - 20;

                    if (prev_scroll_pos < scroll_pos) {
                        if (!is_bottom && scroll_pos >= doc_h) {
                            is_bottom = true;
                            if (request_in_action) {
                                return;
                            }
                            $loading.show();
                            request_in_action = true;

                            $.get(config.url, { offset: offset }, function (html) {
                                $loading.hide();
                                html = $(html).find(html_selector).html();
                                if ($.trim(html).length) {
                                    offset++;
                                } else {
                                    this_is_the_end = true;
                                }
                                $(html_selector).append(html);
                                request_in_action = false;
                            });
                        } else {
                            is_bottom = false;
                        }
                    }
                    prev_scroll_pos = scroll_pos;
                });
            }
        },
        skipHighlightSidebar: false,
        highlightSidebar: function ($li, href) {
            if (this.skipHighlightSidebar) {
                return;
            }

            var self = this;

            var $all_li = self.$core_sidebar.find('li');
            if ($li) {
                $all_li.removeClass('selected');
                $li.addClass('selected');
            } else if (href) {
                $all_li.removeClass('selected');
                $li = self.$core_sidebar.find('a[href^="' + href + '"]').first().closest('li');
                $li.addClass('selected');
            } else {
                var hash = self.routing.getHash(),
                    $a = self.$core_sidebar.find('a[href="' + hash + '"]');

                if (hash) {
                    $all_li.removeClass('selected');
                }
                if ($a.length) { // first find full match
                    $a.closest('li').addClass('selected');
                } else { // more complex hash
                    hash = hash.split("/");
                    if (hash[1]) {
                        while(hash.length) {
                            hash.pop();
                            var href = hash.join('/');

                            var $found_li = self.$core_sidebar.find('a[href^="' + href + '"]').first().closest('li');
                            if ($found_li.length) {
                                $found_li.addClass('selected');
                                break;
                            }
                        }
                    } else {
                        $all_li.removeClass('selected')
                            .first().addClass('selected');
                    }
                }
            }
        },
        timeValueToStr: function(hrs) {
            var secs = hrs * 60 * 60,
                divisor_for_minutes = secs % (60 * 60),
                hours = Math.floor(hrs % 24),
                minutes = Math.floor(divisor_for_minutes / 60),
                divisor_for_seconds = divisor_for_minutes % 60,
                seconds = Math.ceil(divisor_for_seconds),
                str = [];

            if (hours) {
                str.push(hours + $_('h'));
            }
            if (minutes) {
                str.push(minutes + $_('m'))
            }

            return str.join(' ');
        },
        day: function () {
            var $editorHtml,
                $dayEl,
                lastSavedData = '';

            function getDataFromCheckin($checkin) {
                return {
                    'start_time': $checkin.data('checkin-min'),
                    'end_time': $checkin.data('checkin-max'),
                    'id': $checkin.data('checkin-id'),
                    'date': $checkin.data('checkin-date'),
                    'break_duration': $checkin.data('checkin-break')
                }
            }

            var brk = function ($wrapper) {
                var $durationInput = $wrapper.find('input.s-duration-input'),
                    $durationLabel = $wrapper.find('.s-duration-label'),
                    $durationCheckbox = $wrapper.find('input:checkbox'),
                    $checkin = $durationInput.closest('[data-checkin]');

                function value() {
                    return parseInt($durationInput.val());
                }

                // var currentTimeStr = timeValueToStr(value());
                // $durationLabel.text(currentTimeStr ? currentTimeStr : 0);

                //любоу duration (перерыва и по проекту в чекине) — по клику открываем мини-инпут для ввода количества часов (можно ввести дробное количество 2.5, и тогда по focusout будет пересчитано в часа-минутых 2h 30m)
                $durationLabel.on('click.stts', function(e){
                    e.preventDefault();

                    $durationLabel.hide();
                    $durationInput.show().select();
                    if (!value()) {
                        $durationInput.val(1);
                    }
                    //$('.s-break-duration-input').select();
                });

                $durationCheckbox.on('change.stts', function () {
                    if ($durationCheckbox.is(':checked') && value() == 0) {
                        $durationInput.val(1);
                    }

                    $durationInput.trigger('breakChanged.stts');
                });

                $durationInput.on('focusout.stts', function(e){
                    var data = getDataFromCheckin($checkin),
                        time = value();

                    $durationLabel.show().text($.status.timeValueToStr(time));
                    $durationInput.hide();
                    $durationCheckbox.prop('checked', !!time);
                    if (data.break_duration != time) {
                        $durationInput.trigger('breakChanged.stts');
                    }
                });

                return {
                    value: value,
                    isOn: function(){
                        return $durationCheckbox.is(':checked') && value();
                    },
                    wrapper: $wrapper,
                    input: $durationInput,
                    checkbox: $durationCheckbox,
                }
            };

            function initCheckin($el) {
                var $slider = $el.find('.s-editor-slider-slider'),
                    $checkinDuration = $el.find('.s-editor-slider-total h2'),
                    checkinBreak = brk($el.find('.s-editor-slider-break')),
                    data = getDataFromCheckin($el),
                    getSliderMinMax = function() {
                        var breakDuration = checkinBreak.value() * 60,
                            min = 0,
                            max = 1440;

                        if (checkinBreak.isOn()) {
                            min = breakDuration / 2;
                            max = 1440 - breakDuration / 2;
                        }

                        return {'min':min,'max':max};
                    },
                    minMax = getSliderMinMax();

                $checkinDuration.text($.status.timeValueToStr($el.data('checkin-duration')/60));

                checkinBreak.input.on('breakChanged.stts', function () {
                    var minMax = getSliderMinMax();

                    $slider.slider('option', 'max', minMax.max);
                    $slider.slider('option', 'min', minMax.min);
                    var values = $slider.slider('option', 'values'),
                        values2 = [];

                    values2.push(values[0] < minMax.min ? minMax.min : values[0]);
                    values2.push(values[1] > minMax.max ? minMax.max : values[1]);
                    $checkinDuration.text($.status.timeValueToStr((values2[1] - values2[0])/60));

                    $slider.slider('option', 'values', values2);
                });

                $slider.slider('destroy');
                $slider.slider({
                    range: true,
                    min: minMax.min,
                    max: minMax.max,
                    values: [ data.start_time, data.end_time ],
                    slide: function( event, ui ) {
                        var duration = ui.values[1] - ui.values[0];

                        //меняем цвет слайдера на s-active, чтобы показать, что данные сохранились
                        $el.find('.ui-slider').addClass('s-active');

                        if (checkinBreak.isOn() && duration > (24 - checkinBreak.value()) * 60) {
                            return false;
                        }

                        $checkinDuration.text($.status.timeValueToStr(duration/60));
                    },
                    change: function( event, ui ) {
                        //показываем опциаональную детализацию по проектам
                        var data = getDataFromCheckin($el);

                        $el.find('.s-editor-slider-projects').slideDown(200);
                        data.start_time = ui.values[0];
                        data.end_time = ui.values[1];
                        if (!checkinBreak.isOn()) {
                            data.break_duration = 0;
                        } else {
                            data.break_duration = checkinBreak.value();
                        }

                        save(data, $el);
                    }
                });

                //при выборе проектов закрашиваем слайдер цветами проектов в заданных пропорциях. делаем это средствами градиентов css.
                // $el
                    // .on('click', '.s-editor-project input[type="checkbox"]', function(){
                    //     $('#s-editor-slider-slider.ui-slider-horizontal .ui-widget-header').addClass('s-colorized-1312-demo-purpose-class-only');
                    //     $(this).closest('.s-editor-project').addClass('selected');
                    // })
                    //перерыв просто уменьшает общее время
            }

            function savedOk($checkin, data) {
                $editorHtml.find('.s-editor-commit-indicator i.icon16').removeClass('loading').addClass('yes-bw');

                if ($checkin) {
                    $checkin
                        .data('checkin', data['id'])
                        .data('checkin-id', data['id'])
                        .data('checkin-min', data['start_time'])
                        .data('checkin-max', data['end_time'])
                        .data('checkin-break', data['break_duration']);
                }
            }

            function save(data, $checkin) {
                if (lastSavedData === JSON.stringify(data)) {
                    return;
                }

                lastSavedData = JSON.stringify(data);

                //индикатор сохранения — показываем крутилку
                $editorHtml.find('.s-editor-commit-indicator').show();
                $editorHtml.find('.s-editor-commit-indicator i.icon16').removeClass('yes-bw').addClass('loading');

                $.post('?module=checkin&action=save', {checkin: data}, function (r) {
                    if (r.status === 'ok') {
                        savedOk($checkin, r.data);
                        $.status.reloadSidebar();
                    }
                });
            }

            function init(editorHtml) {
                $editorHtml = $(editorHtml);
                $dayEl.hide().after($editorHtml);

                //init sliders
                $editorHtml.find('[data-checkin]').each(function () {
                    initCheckin($(this));
                });

                $editorHtml
                    //если вводят текстовый отчет за день, то индикатор заменяется на кнопку сохранения
                    .on('input.stts propertychange.stts', '.s-editor-comment', function(){
                        $editorHtml.find('.s-editor-commit-button').show();
                        $editorHtml.find('.s-editor-commit-indicator').hide();
                    })
                    .on('click.stts', '.s-editor-commit-button', function(){
                        $editorHtml.find('.s-editor-commit-button').hide();
                        $editorHtml.find('.s-editor-commit-indicator').show();
                        var data = getDataFromCheckin($('[data-checkin]:first'));
                        data['comment'] = $editorHtml.find('.s-editor-comment').val();
                        save(data);
                    })
                    //+ напротив слайдера добавляет еще один слайдер за этот день
                    .on('click.stts', '[data-checkin-action="new"]', function(e){
                        e.preventDefault();

                        var $sourceCheckin = $(this).closest('[data-checkin]'),
                            $newCheckin = $sourceCheckin.clone();

                        $newCheckin
                            .data('checkin', 0)
                            .attr('data-checkin', 0)
                            .data('checkin-id', 0)
                            .attr('data-checkin-id', 0)
                            .data('checkin-break', 0)
                            .attr('data-checkin-break', 0)
                                .find('input.s-duration-input').val(1)
                            .end()
                                .find('input:checkbox').prop('checked', false)
                            .end()
                                .find('.s-editor-slider-slider').empty()
                        ;

                        $sourceCheckin.after($newCheckin);

                        initCheckin($newCheckin);
                    })
                    .on('click.stts', '.s-status-custom-status', function(){
                        $('<div>input: <b>[ enter custom status label ]</b><br><br> radio:<br> <b>(*) calendar name</b><br><b>( ) calendar name</b><br><b>( ) calendar name</b></div>').waDialog({
                            'height' : '400px',
                            'width' : '660px',
                            'onClose' : function(f) {
                                $(this).remove;
                            },
                            'esc' : true,
                        });
                    });
            }

            function close() {
                if ($editorHtml) {
                    $editorHtml.remove();
                    $dayEl.show();
                }
            }

            function editor($day) {
                var date = $day.data('status-day-date');
                $.get('?module=day&action=editor', {date: date}, function (html) {
                    close();
                    $dayEl = $day;
                    init(html);
                });
            }

            return function ($day) {
                editor($day);
            }
        },
        dayEditor: null,
        reloadSidebar: function() {
            var self = this;

            $.get('?module=backend&action=sidebar', function (html) {
                self.$core_sidebar.html(html);
            })
        },
        init: function (o) {
            var self = this;
            self.dayEditor = self.day();
            self.options = $.extend({}, self.defaults, o);
            self.$wa = $('#wa-app');
            self.$status_content = $('#status-content');
            self.$core_sidebar = $('#status-left-sidebar');

            self.options.routingOptions.user_id = self.options.userId;
            self.options.routingOptions.self = self;

            self.routing.init(self.options.routingOptions);
        }
    }
}(jQuery));
