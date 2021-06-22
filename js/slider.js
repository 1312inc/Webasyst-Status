var TaskSlider = class Slider {
    constructor (selector, options) {
        this.container = selector.find('.s-editor-slider-slider')[0]
        this.options = options
        this.initialData = options.state.filter((e) => e.comment === null).sort((a, b) => {
            if (a.id > b.id) {
                return 1;
              }
              if (a.id < b.id) {
                return -1;
              }
              return 0;
        });

        this.$sliderProjects = selector.find('.s-editor-slider-projects')
        this.$addButton = selector.find('.s-editor-slider-add')
        this.$deleteButton = selector.find('.s-editor-slider-delete')

        this.selectedRangeIndex = null

        this.currentCheckin = {
            checkin: {
                id: '',
                start_time: null,
                end_time: null,
                date: selector.data('day-date')
            },
            projects: []
        }

        this.init()
    }

    set checkinIndex (index) {
        
        this.selectedRangeIndex = index
        this.currentCheckinId = this.initialData[index] ? this.initialData[index].id : '' 

        var connect = this.container.querySelectorAll('.noUi-connect');
        connect.forEach((e) => {
            e.classList.remove('active');
        });
        if(this.selectedRangeIndex !== null) {
            connect[this.selectedRangeIndex].classList.add('active');
        }
        
    }

    set currentCheckinId (id) {
        this.currentCheckin.checkin.id = id
        this.$sliderProjects.hide();
        this.$sliderProjects.filter('[data-checkin-id="'+this.currentCheckin.checkin.id+'"]').show()        
        this.toggleDeleteButton();
    }

    onUpdate (values) {
        this.options.onChange(values)
    }

    initSlider (options) {

        this.slider = noUiSlider.create(this.container, {
            start: options.start,
            connect: options.connect,
            tooltips: options.start.map(function() {return true}),
            behaviour: 'drag',
            step: 5,
            range: {'min': 0, 'max': 1440},
            // format: {
            //     to: function (value) {
            //         return $.status.timeValueToStr(value / 60, 'time');
            //     },
            //     from: function (value) {
            //         return value;
            //     }
            // }
        });

        this.slider.on('start', (values, handle) => {
            handle++;
            this.checkinIndex = Math.ceil(handle / 2) - 1;
        });

        this.slider.on('update', (values) => {
            this.onUpdate(values)            
        });
        
        this.slider.on('change', (values) => {
            this.currentCheckin.checkin.start_time = +values[0 + this.selectedRangeIndex*2]
            this.currentCheckin.checkin.end_time = +values[1 + this.selectedRangeIndex*2]
            this.onChange()
        });

    }

    addRange () {

        let prevStart = this.slider.get()
        let prevStartLast = prevStart[prevStart.length - 1] 

        let updatedOptions = this.slider.options
        updatedOptions.start = [...prevStart, +prevStartLast + 100, +prevStartLast + 200]
        updatedOptions.tooltips = updatedOptions.start.map(() => true)
        updatedOptions.connect = [...updatedOptions.connect, true, false]
        // updatedOptions.range = this.initialOptions.range

        this.slider.destroy()
        this.initSlider(updatedOptions)

        

    }

    deleteRange () {
        if(this.selectedRangeIndex === null) return
    
        let newStart = this.slider.get()
        newStart.splice(this.selectedRangeIndex * 2, 2)

        let updatedOptions = this.slider.options
        updatedOptions.start = newStart
        updatedOptions.tooltips.splice(0, 2)
        updatedOptions.connect.splice(-2, 2)
        // updatedOptions.range = this.initialOptions.range

        this.slider.destroy()
        this.initSlider(updatedOptions)

        $.post('?module=checkin&action=delete', $.param({id: this.currentCheckin.checkin.id}))
            .done(() => {
                const i = this.initialData.findIndex(e => +e.id === +this.currentCheckin.checkin.id)
                if(i > -1) {
                    this.initialData.splice(i, 1)
                    // this.currentCheckinId = ''
                    this.checkinIndex = null
                }
            })

    }

    onChange () {
        this.save();
    }

    initCheckboxes () {
        
        var $durationInput = this.$sliderProjects.find('input.s-duration-input'),
            $durationLabel = this.$sliderProjects.find('.s-duration-label');

            $durationLabel.on('click.stts', function (e) {
                e.preventDefault();

                $durationLabel.hide();
                $durationInput.show().select();
                // if (!getValue()) {
                //     if (type === 'break') {
                //         $durationInput.val(1);
                //     }
                // }
                //$('.s-break-duration-input').select();
            });
            

        this.$sliderProjects.find('[type="checkbox"]').on('click', (event) => {
            $( event.target ).closest('.s-editor-project').toggleClass('selected')
            this.save();
        })
    }

    save () {
        const data = $.param(this.currentCheckin) + '&' + $.param($('.s-editor-slider-projects:visible').find('input').serializeArray());
        $.post('?module=checkin&action=save', data)
            .done((response) => {
                if(this.initialData.findIndex(e => +e.id === +response.data.id) === -1) {
                    this.initialData.push(response.data)
                    this.currentCheckinId = response.data.id
                }
            })
    }

    toggleDeleteButton () {
        if(this.selectedRangeIndex > 0) {
            this.$deleteButton.show();
        } else {
            this.$deleteButton.hide();
        }
    }

    init () {

        const start = this.initialData.reduce((acc, e) => {
            acc.push(e.min, e.max);
            return acc;
        }, []);

        const connect = [];
        for (let index = 1; index < start.length + 2; index++) {
            connect.push(index % 2 == 0);
        }

        this.initSlider({
            start,
            connect
        })

        this.$addButton.on('click', () => {
            this.addRange()
        })

        this.$deleteButton.on('click', () => {
            this.deleteRange()
        })

        this.toggleDeleteButton();

        this.initCheckboxes()
    
    }
}
