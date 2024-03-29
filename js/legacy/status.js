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
            userId: 0,
            backendUrl: ''
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
                var self = this;

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
                            this[actionName + 'Action'].apply(this, attr).done(function () {
                                self.postExecute(actionName, hash);
                            });
                            // this.postExecute(actionName, hash);
                        } else {
                            console.info('Invalid action name:', actionName + 'Action');
                        }
                    } else {
                        this.preExecute();
                        this.defaultAction().done(function () {
                            self.postExecute('default', hash);
                        });
                        // this.postExecute('default', hash);
                    }
                } else {
                    this.preExecute();
                    this.defaultAction().done(function () {
                        self.postExecute('', hash);
                    });
                    // this.postExecute('', hash);
                }
            },
            redispatch: function () {
                this.prevHash = null;
                this.dispatch();
            },
            yAction: function () {
                this.defaultAction(true);
                return $.post('?module=backend&action=setSeenNoStatusNotification');
            },
            defaultAction: function (skipSaveHash) {
                return this.contactAction(0, function () {
                    $.status.$status_content.trigger('loadEditor.stts');
                });
            },
            contactAction: function (id, callback) {
                var that = this;
                return $.get('?module=chronology&contact_id=' + id, function (html) {
                    $('#status-content').html(html);
                    if ($.isFunction(callback)) {
                        callback.call();
                    }
                });
            },
            projectAction: function (id) {
                var that = this;
                return $.get('?module=chronology&project_id=' + id, function (html) {
                    $('#status-content').html(html);
                });
            },
            reportsAction: function (date1, date2) {
                var that = this;
                return $.get('?module=report', {start: date1, end: date2}, function (html) {
                    $('#status-content').html(html);
                });
            },
            debugAction: function () {
                return $.get('?module=backend&action=debug', function (html) {
                    $('#status-content').html(html);
                });
            },
            preExecute: function () {
                var $h1 = $.status.$status_content.find('h1:first');

                if ($h1.length) {
                    $('html, body').animate({ scrollTop: 0 }, 131.2);
                    $h1.append('<i class="icon16 loading"></i>');
                }
            },
            postExecute: function (actionName, hash) {
                if (actionName !== 'y') {
                    var value = $.isArray(hash) ? hash.join('/') : '';
                    $.storage.set('/status/hash/' + this.options.user_id, value);
                }
                this.options.self.highlightSidebar();
                this.options.self.setTitle();
            }
        },
        lazyLoad: function (config) {
            $(window).off('scroll.stts');

            if (config) {
                var $loading = config.$loading,//$('#pl-list-content .lazyloading'),
                    is_bottom = false,
                    request_in_action = false,
                    prev_scroll_pos = 0,
                    offset = config.offset || 1,
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

                            $.get(config.url, {offset: offset}, function (html) {
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
                        while (hash.length) {
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
        timeValueToStr: function (hrs, format) {
            var secs = hrs * 60 * 60,
                divisor_for_minutes = secs % (60 * 60),
                hours = Math.floor(hrs % 24),
                minutes = Math.floor(divisor_for_minutes / 60),
                divisor_for_seconds = divisor_for_minutes % 60,
                seconds = Math.ceil(divisor_for_seconds),
                str = [];

            if (format === 'time') {
                if (minutes < 10) {
                    minutes = '0' + minutes;
                }
                if (hours < 10) {
                    hours = '0' + hours;
                }
                str.push(hours);
                str.push(minutes);

                return str.join(':');
            }

            if (hours) {
                str.push(hours + $_('h'));
            }
            if (minutes) {
                str.push(minutes + $_('m'))
            }

            return str.join(' ');
        },
        log: function() {
            window.console && console.log('[status log]', Array.prototype.slice.call(arguments));
            // console.log.apply(console, arguments);
        },
        day: function () {
            var $editorHtml,
                $dayEl,
                reloadDayShow = false,
                lastSavedData = '',
                requestInAction = false,
                $loading = $('<i class="icon16 loading"></i>');

            function getDataFromCheckin($form) {
                return {
                    'start_time': $form.find('[name="checkin[start_time]"]').val(),
                    'end_time': $form.find('[name="checkin[end_time]"]').val(),
                    'id': $form.find('[name="checkin[id]"]').val(),
                    'date': $form.find('[name="checkin[date]"]').val(),
                    'break_duration': $form.find('[name="checkin[break_duration]"]').val()
                }
            }

            var checkboxDuration = function ($wrapper, type) {
                var $durationInput = $wrapper.find('input.s-duration-input'),
                    $durationLabel = $wrapper.find('.s-duration-label'),
                    $durationCheckbox = $wrapper.find('input:checkbox'),
                    $checkin = $durationInput.closest('[data-checkin]'),
                    manual = false,
                    id = parseInt($wrapper.data('status-project-id'));

                type = type || 'break';

                function getValue() {
                    var val = $durationInput.val().replace(',','.');

                    return (type === 'break' ? parseFloat(val) : parseInt(val))|| 0;
                }

                // var currentTimeStr = timeValueToStr(value());
                // $durationLabel.text(currentTimeStr ? currentTimeStr : 0);

                //любоу duration (перерыва и по проекту в чекине) — по клику открываем мини-инпут для ввода количества часов (можно ввести дробное количество 2.5, и тогда по focusout будет пересчитано в часа-минутых 2h 30m)
                $durationLabel.on('click.stts', function (e) {
                    e.preventDefault();

                    $durationLabel.hide();
                    $durationInput.show().select();
                    if (!getValue()) {
                        if (type === 'break') {
                            $durationInput.val(1);
                        }
                    }
                    //$('.s-break-duration-input').select();
                });

                $durationCheckbox
                    .on('change.stts', function () {
                        if ($durationCheckbox.is(':checked')) {
                            if (getValue() == 0 && type === 'break') {
                                $durationInput.val(1);
                            }
                        }
                        $checkin.trigger(type + 'Changed.stts');
                    })
                    .on('click.stts', function (e) {
                        $durationCheckbox.closest('.s-editor-project').toggleClass('selected');
                    });

                $durationInput.on('focusout.stts keydown.stts', function (e) {
                    var time = getValue(),
                        keycode = e.keyCode || e.which;

                    if (keycode) {
                       if (keycode === 13) {
                           $durationInput.trigger('focusout.stts');
                       }
                    } else {
                        if (time > 100) {
                            time = 100;
                            setValue(time);
                        }
                        if (time < 0) {
                            time = 0;
                            setValue(time);
                        }

                        manual = !!time;

                        $durationLabel.show();
                        setValue(time);
                        // if (type === 'break') {
                        //     $durationLabel.text($.status.timeValueToStr(time));
                        // } else {
                        //     $durationLabel.text(time + '%');
                        // }
                        $checkin.trigger(type + 'Changed.stts', $durationInput);
                        $durationInput.hide();
                    }
                });

                function setValue(value) {
                    $durationInput.val(value);
                    if (type === 'break') {
                        $durationLabel.text($.status.timeValueToStr(value));
                    } else {
                        $durationLabel.text(value + '%');
                    }
                }

                $wrapper.data('checkboxDuration', this);

                return {
                    value: getValue,
                    isOn: function () {
                        return $durationCheckbox.is(':checked');
                    },
                    isManual: function () {
                        return manual;
                    },
                    setValue: setValue,
                    getId: function () {
                        return id;
                    },
                    wrapper: $wrapper,
                    input: $durationInput,
                    checkbox: $durationCheckbox,
                    label: $durationLabel
                }
            };

            function initCheckin($el) {
                var $slider = $el.find('.s-editor-slider-slider'),
                    $form = $el.find('form'),
                    $checkinDuration = $el.find('.s-editor-slider-total h2'),
                    checkinBreak = checkboxDuration($el.find('.s-editor-slider-break')),
                    projects = [],
                    data = getDataFromCheckin($form),
                    hasProjects = $el.data('checkin-has-projects'),
                    checkinId = $form.find('[name="checkin[id]"]').val(),
                    minMax = {'min': 0, 'max': 1440};

                var getCheckinDuration = function (values) {
                        var checkinDuration = values[1] - values[0];

                        if (checkinBreak.isOn()) {
                            checkinDuration -= (checkinBreak.value() * 60)
                        }
                        if (checkinDuration < 0) {
                            checkinDuration = 0;
                        }

                        return checkinDuration;
                    },
                    updateDayDuration = function (values) {
                        var duration = getCheckinDuration(values),
                            value = $checkinDuration.data('status-checkin-duration-zero');

                        if (duration > 0) {
                            value = $.status.timeValueToStr(duration / 60);
                        }
                        $checkinDuration.text(value);
                    },
                    fillSliderWithColor = function () {
                        var $line = $slider.find('.ui-widget-header'),
                            width = $line.get(0).style.width,
                            left = $line.get(0).style.left,
                            colors = [];

                        var prevPercent = 0;
                        $.each(projects, function (i, project) {
                            if (!project.isOn() && project.value()) {
                                return;
                            }
                            var percent = parseInt(project.value());
                            colors.push(project.wrapper.data('status-project-color') + ' ' + prevPercent + '%');
                            prevPercent += percent;
                            colors.push(project.wrapper.data('status-project-color') + ' ' + prevPercent + '%');
                        });

                        if (prevPercent < 100) {
                            colors.push('#f1f2f3 ' + prevPercent + '%');
                            colors.push('#f1f2f3 100%');
                        }

                        var gradient = colors.join(', '),
                            style = [
                                'background: #ffd60a',
                                'background: -moz-linear-gradient(left, ' + gradient + ')',
                                'background: -webkit-linear-gradient(left, ' + gradient + ')',
                                'background: linear-gradient(to right, ' + gradient + ')',
                                'width: ' + width,
                                'left: ' + left,
                            ];

                        $line.attr('style', style.join(';'));
                    },
                    recalculateProjects = function (project) {
                        var percent = 100,
                            autoProjects = [],
                            manualProjects = [],
                            autoDurationPercent = 0,
                            manualDuration = 0,
                            currentProject = null,
                            logMarker = '[cash app] recalculate projects ' + Math.round(Date.now() / 1000);

                        var projectGetIdsHelper = function (projectsToGetIds) {
                            return $.map(projectsToGetIds, function (p, i ) {
                                return p.getId();
                            }).join(', ');
                        }

                        window.console && console.group(logMarker);

                        if (project) {
                            var projectId = $(project).closest('.s-editor-project').data('status-project-id');
                            $.status.log('project id ' + projectId);

                            $.each(projects, function (i, project) {
                                if (projectId == project.getId()) {
                                    currentProject = project;
                                    manualProjects.push(currentProject);
                                    $.status.log('current project: ' + currentProject.getId());

                                    return;
                                }
                            });
                        }

                        $.status.log('iterating projects: ' + projectGetIdsHelper(projects));
                        $.each(projects, function (i, project) {
                            if (!project.isOn()) {
                                $.status.log('project ' + project.getId() + ' is off');

                                return;
                            }
                            if (currentProject && currentProject.getId() == project.getId()) {
                                $.status.log('skip current project');

                                return;
                            }

                            if (!project.isManual()) {
                                $.status.log('project ' + project.getId() + ' is auto');
                                autoProjects.push(project);
                            } else {
                                manualProjects.push(project);
                                $.status.log('project ' + project.getId() + ' is manual');
                            }
                        });

                        $.status.log('iterating manual projects: ' + projectGetIdsHelper(manualProjects));
                        var lastOnManualProject = null;
                        $.each(manualProjects, function (i, project) {
                            var projectDuration = project.value();
                            $.status.log('project ' + project.getId() + ' value (duration): ' + projectDuration);

                            percent -= projectDuration;
                            $.status.log('percent left: ' + percent);

                            if (percent < 0) {
                                $.status.log('percent below zero, set project value = ' + (projectDuration + percent));
                                project.setValue(projectDuration + percent);
                                percent = 0;
                            }
                            lastOnManualProject = project;
                        });

                        if (autoProjects.length) {
                            autoDurationPercent = Math.round(percent / autoProjects.length);
                            $.status.log('percents per each auto project: ' + autoDurationPercent);
                        }

                        $.status.log('iterating auto projects: ' + projectGetIdsHelper(autoProjects));
                        var lastOnAutoProject = null;
                        $.each(autoProjects, function (i, project) {
                            var autoPercent = percent - autoDurationPercent,
                                value = autoPercent >= 0 ? autoDurationPercent : 0;
                            $.status.log('set auto project ' + project.getId() + ' value = ' +  value);
                            project.setValue(value)
                            lastOnAutoProject = project;
                        });

                        $.status.log('check percent consistent (100%)');
                        percent = 0;
                        $.each(projects, function (i, project) {
                            if (project.isOn()) {
                                percent += project.value();
                                $.status.log('add project ' + project.getId() + ' value = ' + project.value() + ', total = ' + percent);
                            }
                        });

                        if (percent > 100 && projects.length) {
                            var lastProject = lastOnAutoProject || lastOnManualProject,
                                lastProjectValue = lastProject.value(),
                                lastProjectNewValue = lastProjectValue + (100 - percent);
                            $.status.log('healing last project ' + lastProject.getId()
                                + ' with old value ' + lastProjectValue + ', set new value = ' + lastProjectNewValue
                                + '(' + lastProjectValue + ' + (100 - ' + percent + '))');
                            lastProject.setValue(lastProjectNewValue);
                        } else {
                            $.status.log('percent consistent is ok (100 === 100)');
                        }

                        $.status.log('update date duration');
                        updateDayDuration($slider.slider('option', 'values'));

                        $.status.log('colorize slider');
                        fillSliderWithColor();

                        $.status.log('save data', $form.serialize());
                        save($form);

                        window.console && console.groupEnd();
                    };

                // updateDayDuration($slider.slider('values'));
                // $checkinDuration.text($.status.timeValueToStr($form.find('[name="checkin[total_duration]"]').val() / 60));


                $el.find('.s-editor-project').each(function () {
                    projects.push(new checkboxDuration($(this), 'project'));
                });

                $el
                    .on('projectChanged.stts', function (e, data) {
                        recalculateProjects(data);
                        save($form);
                    })
                    .on('breakChanged.stts', function () {
                        /*var minMax = getSliderMinMax();

                        $slider.slider('option', 'max', minMax.max);
                        $slider.slider('option', 'min', minMax.min);
                        var values = $slider.slider('option', 'values'),
                            values2 = [];

                        values2.push(values[0] < minMax.min ? minMax.min : values[0]);
                        values2.push(values[1] > minMax.max ? minMax.max : values[1]);*/

                        updateDayDuration($slider.slider('option', 'values'));
                        save($form);
                    });


                $slider.slider('destroy');
                $slider.slider({
                    range: true,
                    min: minMax.min,
                    max: minMax.max,
                    step: 5,
                    values: [data.start_time, data.end_time],
                    create: function (event, ui) {
                        $el.find('.ui-slider .ui-slider-handle:first').attr('data-slider-time', $.status.timeValueToStr(data.start_time / 60, 'time'));
                        $el.find('.ui-slider .ui-slider-handle:last').attr('data-slider-time', $.status.timeValueToStr(data.end_time / 60, 'time'));

                        if (hasProjects) {
                            fillSliderWithColor();
                        }
                        updateDayDuration($slider.slider('option', 'values'));
                        if (data.id) {
                            //меняем цвет слайдера на s-active, чтобы показать, что данные сохранились
                            $el.find('.ui-slider').addClass('s-active');
                            $el.find('.s-editor-slider-projects').slideDown(200);
                        }
                    },
                    slide: function (event, ui) {
                        updateDayDuration(ui.values);
                        // if (checkinBreak.isOn() && duration > (24 - checkinBreak.value()) * 60) {
                        //     return false;
                        // }

                        $el.find('.ui-slider .ui-slider-handle:first').attr('data-slider-time', $.status.timeValueToStr(ui.values[0] / 60, 'time'));
                        $el.find('.ui-slider .ui-slider-handle:last').attr('data-slider-time', $.status.timeValueToStr(ui.values[1] / 60, 'time'));
                    },
                    change: function (event, ui) {
                        //показываем опциаональную детализацию по проектам
                        $el.find('.s-editor-slider-projects').slideDown(200);

                        $form.find('[name="checkin[start_time]"]').val(ui.values[0]);
                        $form.find('[name="checkin[end_time]"]').val(ui.values[1]);

                        save($form);
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

            function startRequest(request) {
                if (requestInAction) {
                    $.status.log('Request. ABORT.');
                    return;
                }
                $.status.log('Request. START.');
                requestInAction = true;
                $editorHtml.find(':input').prop('disabled', requestInAction);
                $editorHtml.find('.s-editor-slider-slider').each(function () {
                    $(this).slider('disable');
                });
                request.call()
            }

            function endRequest() {
                requestInAction = false;
                $editorHtml.find(':input').prop('disabled', requestInAction);
                $editorHtml.find('.s-editor-slider-slider').each(function () {
                    $(this).slider('enable');
                });
                $.status.log('Request. END.');
            }

            function save($form) {
                var newSaveData = $form.serialize();
                $.status.log('Save day form.', $form, newSaveData);

                if (lastSavedData === newSaveData) {
                    $.status.log('Save day. Same data');
                    return;
                }

                if (!newSaveData) {
                    $.status.log('Save day. No data');
                    return;
                }

                lastSavedData = newSaveData;

                //индикатор сохранения — показываем крутилку
                $editorHtml.find('.s-editor-commit-indicator').show();
                $editorHtml.find('.s-editor-commit-indicator i.icon16').removeClass('yes-bw').addClass('loading');

                startRequest(function () {
                    $.status.log('Save day. Sending post', newSaveData);
                    $.post('?module=checkin&action=save', newSaveData)
                        .done(function (r) {
                            if (r.status === 'ok') {
                                $.status.log('Save day. OK', r);

                                $editorHtml.find('.s-editor-commit-indicator i.icon16').removeClass('loading').addClass('yes-bw');
                                $form.find('[name="checkin[id]"]').val(r.data.id);
                                var weekNum = parseInt($editorHtml.data('status-week-of-day'));
                                $('[data-status-week-donut="' + weekNum + '"]').trigger('reloadDonut.stts');
                                //меняем цвет слайдера на s-active, чтобы показать, что данные сохранились
                                $form.find('.ui-slider').addClass('s-active');
                                reloadDayShow = true;
                                // savedOk(r.data, $form);
                                $.status.reloadSidebar();
                                if ($.status.routing.hash === 'y') {
                                    $.get($.status.options.backendUrl + '?action=count&background_process=1&force=1&_=' + (+(new Date())));
                                    $('#wa-applist #wa-app-status .indicator').remove();
                                }
                            } else {
                                $.status.log('Save day. FAIL', r);
                            }
                        })
                        .always(function (r) {
                            $.status.log('Save day. Received response', r);
                            endRequest();
                        });
                });
            }

            function remove(id) {
                startRequest(function() {
                    $.status.log('Remove day. Request in action');
                    $.post('?module=checkin&action=delete', {id: id}, function (r) {
                        if (r.status === 'ok') {
                            $.status.log('Remove checkin. OK', r);
                            var weekNum = parseInt($editorHtml.data('status-week-of-day'));
                            $('[data-status-week-donut="' + weekNum + '"]').trigger('reloadDonut.stts');
                            $.status.reloadSidebar();
                        } else {
                            $.status.log('Remove checkin. FAIL', r);
                        }
                    }).always(function (r) {
                        $.status.log('Remove day. Received response', r);
                        endRequest();
                    });
                });
            }

            function init(editorHtml) {
                $editorHtml = $(editorHtml);
                $dayEl.hide().after($editorHtml);

                if ($.status.routing.hash === 'y' && !$editorHtml.find('input[name="checkin[id]"]:first').val()) {
                    $editorHtml.find('h1.s-yesterday').append('<span class="indicator red">1</span>');
                    $editorHtml.find('h1.s-yesterday').after('<p class="s-checkin-reminder">'+$_('Hello! This is a friendly reminder to check in your workday yesterday.')+'</p>');
                }

                //init sliders
                $editorHtml.find('[data-checkin]').each(function () {
                    initCheckin($(this));
                });

                var $firstCheckin = $editorHtml.find('[data-checkin]:first');
                $firstCheckin.find('[data-checkin-action="delete"]')
                    .attr('data-checkin-action', 'add')
                    .attr('title', $_('Add another interval for today'))
                    .find('i.delete')
                    .toggleClass('add delete');

                var saveComment = function() {
                    $editorHtml.find('.s-editor-commit-button').hide();
                    $editorHtml.find('.s-editor-commit-indicator').show();
                    var $form = $editorHtml.find('[data-checkin]:first form'),
                        $comment = $editorHtml.find('.s-editor-comment').clone();
                    $form.append($comment.hide());
                    save($form);
                    $comment.remove();
                };

                $editorHtml
                //если вводят текстовый отчет за день, то индикатор заменяется на кнопку сохранения
                    .on('input.stts propertychange.stts', '.s-editor-comment', function () {
                        $editorHtml.find('.s-editor-commit-button').show();
                        $editorHtml.find('.s-editor-commit-indicator').hide();
                    })
                    .on('keydown.stts', '.s-editor-comment', function (e) {
                        // ctrl + enter || ctrl + s
                        if ((e.ctrlKey || e.metaKey) && ((e.keyCode || e.which) === 13 || (e.keyCode || e.which) === 83)) {
                            e.preventDefault();
                            saveComment();
                        }
                    })
                    .on('click.stts', '.s-editor-commit-button', function () {
                        saveComment()
                    })
                    //+ напротив слайдера добавляет еще один слайдер за этот день
                    .on('click.stts', '[data-checkin-action="add"]', function (e) {
                        e.preventDefault();

                        var $sourceCheckin = $(this).closest('[data-checkin]'),
                            $newCheckin = $sourceCheckin.clone(),
                            $logsChecks = $sourceCheckin.find('.s-editor-slider-helpers').clone();

                        $newCheckin.find('[data-checkin-action="add"]')
                            .attr('data-checkin-action', 'delete')
                            .attr('title', $_('Remove interval for today'))
                            .find('i.add')
                            .toggleClass('add delete');

                        $newCheckin
                            .data('checkin', 0)
                            .attr('data-checkin', 0)
                            .data('checkin-id', 0)
                            .attr('data-checkin-id', 0)
                            .data('checkin-break', 0)
                            .attr('data-checkin-break', 0)
                            .data('checkin-has-projects', 0)
                            .attr('data-checkin-has-projects', 0)
                            .find('input.s-duration-input').val(1)
                            .end().find('input:checkbox').prop('checked', false)
                            .end().find('.s-editor-slider-slider').empty().append($logsChecks)
                        ;

                        // projects
                        $newCheckin.find('form [name="checkin[id]"]').val('');
                        $newCheckin.find('.s-editor-project').removeClass('selected')
                            .closest('.s-editor-slider-projects').hide();


                        $editorHtml.find('[data-checkin]:last').after($newCheckin);

                        initCheckin($newCheckin);
                    })
                    //+ напротив слайдера добавляет еще один слайдер за этот день
                    .on('click.stts', '[data-checkin-action="delete"]', function (e) {
                        e.preventDefault();

                        var $sourceCheckin = $(this).closest('[data-checkin]'),
                            checkinId = $sourceCheckin.find('form [name="checkin[id]"]').val();

                        if (checkinId) {
                            remove(checkinId);
                        }

                        $sourceCheckin.remove();
                    })
                    .on('reloadDayShow.stts', function () {
                        reloadDayShow = true;
                    });
            }

            function close() {
                if ($editorHtml) {
                    if (reloadDayShow) {
                        var date = $dayEl.data('status-day-date');
                        $.get('?module=day&action=show', {date: date, contact_id: $.status.options.userId}, function (html) {
                            $.status.$status_content.find('.s-day[data-status-day-date="' + date + '"]').html(html);
                        });
                    }
                    $editorHtml.remove();
                    $dayEl.show();
                    reloadDayShow = false;
                }
            }

            function editor($day) {
                $day.prepend($loading);
                $.get('?module=day&action=editor', {date: $day.data('status-day-date')}, function (html) {
                    $loading.remove();

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
        reloadSidebar: function () {
            var self = this;

            $.get('?module=backend&action=sidebar', function (html) {
                self.$core_sidebar.html(html);
            })
        },
        skipNextTitle: false,
        setTitle: function (title, skipNext) {
            var self = this;

            if (self.skipNextTitle === true) {
                self.skipNextTitle = false;
                return;
            }

            self.skipNextTitle = skipNext || false;
            var $h1 = self.$status_content.find('h1').first();
            if ($h1.length && !title) {
                title = $h1.contents().filter(function () {
                    return this.nodeType == 3 && this.nodeValue.trim().length > 0;
                })[0].nodeValue.trim()
            }
            if (title) {
                title += ' &mdash; ';
            } else {
                title = '';
            }
            $('title').html(title + $_('Status') + ' &mdash; ' + self.options.account_name);
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

            var projectDialog = function(e) {
                e.preventDefault();

                var projectId = $(this).data('status-project-id') || 0;

                $('#stts-project-dialog').waDialog({
                    'height': '250px',
                    'width': '600px',
                    'url': '?module=project&action=dialog&id=' + projectId,
                    onLoad: function () {
                        var d = this,
                            $dialogWrapper = $(d);

                        $dialogWrapper
                            .on('click', '#status-project-color a', function (e) {
                                e.preventDefault();

                                $dialogWrapper.find('#status-project-color input').val($(this).data('status-project-color'));
                                $(this).addClass('selected')
                                    .siblings().removeClass('selected')
                            })
                            .on('click', '[data-status-action="delete-project"]', function (e) {
                                e.preventDefault();

                                $.post('?module=project&action=delete', $dialogWrapper.find('form').serialize(), function (r) {
                                     if (r.status === 'ok') {
                                         $dialogWrapper.trigger('close');
                                         window.location.hash = '#/';
                                         $.status.routing.redispatch();
                                         $.status.reloadSidebar();
                                     }
                                });
                            })
                        ;

                        setTimeout(function () {
                            if (!$dialogWrapper.find('[name="project[id]"]').val() == 0) {
                                $dialogWrapper.find('[name="project[name]"]').trigger('focus');
                            }
                        }, 13.12);
                    },
                    onSubmit: function (d) {
                        d.find('.dialog-buttons input[type="button"]').after($.status.$loading);
                        $.post('?module=project&action=save', d.find('form').serialize(), function (r) {
                            $.status.$loading.remove();
                            if (r.status === 'ok') {
                                d.trigger('close');
                                // if (!pocketId) {
                                window.location.hash = '#/project/' + r.data.id;
                                // }
                                $.status.routing.redispatch();
                                $.status.reloadSidebar();
                            } else {

                            }
                        }, 'json');
                        return false;
                    }
                });
            };

            self.$core_sidebar
                .on('click', '[data-status-project-action="add"]', projectDialog)
                .on('click.stts', '.s-tiny-ad-close', function (e) {
                    e.preventDefault();

                    $(this).closest('.s-tiny-ad').hide();
                    $.post('?module=backend&action=hideTinyAd');
                });

            self.$status_content.on('click', '[data-status-project-action="add"]', projectDialog);
            s-tiny-ad-close
            self.$status_content
                .on('click.stts', '[data-status-walog-app][data-status-walog-date]', function (e) {
                    e.preventDefault();

                    var $this = $(this),
                        date = $this.data('status-walog-date'),
                        contactId = $this.data('status-walog-contact-id');

                    $('#stts-walog-dialog').waDialog({
                        'height': '300px',
                        'width': '600px',
                        'url': '?module=walog&action=dialog&date=' + encodeURIComponent(date) + '&contact_id=' + contactId,
                        onLoad: function () {
                            var d = this,
                                $dialogWrapper = $(d);
                        }
                    });
                })
                .on('click.stts', '[data-status-wrapper="statuses"] [data-status-action="custom-status"]', function (e) {
                    e.preventDefault();

                    var $this = $(this),
                        date = $this
                            .closest('[data-status-wrapper="statuses"]')
                            .data('status-today-status-date') || '',
                        $wrapper = $this.closest('[data-status-wrapper="statuses"]');

                    $('#stts-status-dialog').waDialog({
                        'height': '300px',
                        'width': '600px',
                        'url': '?module=todaystatus&action=dialog&date=' + encodeURIComponent(date),
                        onLoad: function () {
                            var d = this,
                                $dialogWrapper = $(d);

                            setTimeout(function () {
                                $dialogWrapper.find('status[summary]').trigger('focus');
                            }, 13.12);
                        },
                        onSubmit: function (d) {
                            d.find('.dialog-buttons input[type="button"]').after($.status.$loading);
                            $.post('?module=todaystatus&action=save', d.find('form').serialize(), function (r) {
                                $.status.$loading.remove();
                                if (r.status === 'ok') {
                                    $wrapper.replaceWith(r.data);
                                    $.status.$status_content.find('.s-editor').trigger('reloadDayShow.stts');

                                    $.status.reloadSidebar();
                                    d.trigger('close');
                                } else {

                                }
                            }, 'json');
                            return false;
                        }
                    });
                })
                .on('click.stts', '[data-status-wrapper="statuses"] [data-status-calendar-id] a', function (e) {
                    e.preventDefault();
                    var $this = $(this),
                        $li = $this.closest('[data-status-calendar-id]'),
                        calendarId = $li.data('status-calendar-id'),
                        date = $this
                            .closest('[data-status-wrapper="statuses"]')
                            .data('status-today-status-date') || '',
                        $wrapper = $this.closest('[data-status-wrapper="statuses"]');


                    $.post('?module=todaystatus&action=save', {
                        status: {
                            calendar_id: calendarId,
                            // brand_new: 1,
                            date: date,
                            summary: $this.text()
                        }
                    }, function (r) {
                        $.status.$loading.remove();
                        if (r.status === 'ok') {
                            $wrapper.replaceWith(r.data);
                            $.status.$status_content.find('.s-editor').trigger('reloadDayShow.stts');

                            $.status.reloadSidebar();
                        } else {

                        }
                    }, 'json');
                })
            ;
        }
    }
}(jQuery));
