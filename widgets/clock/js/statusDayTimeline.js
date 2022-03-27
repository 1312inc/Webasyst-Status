export function statusDayTimeline () {
  const WIDTH = 300;
  const HEIGHT = 300;
  const INNER_RADIUS = 100;
  const OUTER_RADIUS = 110;
  const GRAY_COLOR = '#f1f2f3';

  let $el;

  const timestampToRadians = (timestamp) => {
    const hours = new Date(timestamp * 1000).getHours() + new Date(timestamp * 1000).getMinutes() / 60;
    return 720 / 24 * (hours + new Date().getTimezoneOffset() / 60) * Math.PI / 180;
  };

  const showTooltip = (el, text) => {
    if (typeof tippy !== 'undefined') {
      tippy(el, {
        content: text,
        allowHTML: true,
        arrow: false
      });
    }
  };

  const removeTooltip = (el) => {
    if (el._tippy) {
      el._tippy.destroy();
    }
  };

  const makeProjectsList = (list) => {
    return [...Object.values(list.projectsDuration)].map(p => {
      return {
        duration: p.duration,
        ...p.project
      };
    });
  };

  return {

    render (checkins) {
      const svg = $el.append("svg")
        .attr("width", WIDTH)
        .attr("height", HEIGHT)
        .append("g")
        .attr("transform", "translate(" + WIDTH / 2 + "," + HEIGHT / 2 + ")");

      const timeline = d3.svg.arc()
        .innerRadius(INNER_RADIUS)
        .outerRadius(OUTER_RADIUS)
        .startAngle(Math.PI / 180)
        .endAngle(2 * Math.PI);

      svg.append('path')
        .attr('d', timeline)
        .style('fill', GRAY_COLOR);

      const projectsList = makeProjectsList(checkins.find(c => !c.isTrace));

      for (const checkin of checkins.filter(c => !c.isTrace)) {

        const colors = checkin.projectDurationCss.split(',');

        let startTime = checkin.startTimestamp;
        let endTime;
        let diff = checkin.endTimestamp - checkin.startTimestamp;

        for (let color = 1; color < colors.length; color += 2) {
          let c = colors[color].trim().split(' ');

          if (c[1] !== '0%') {
            endTime = checkin.startTimestamp + diff * Number.parseInt(c[1]) / 100;

            const arc = d3.svg.arc()
              .innerRadius(INNER_RADIUS)
              .outerRadius(OUTER_RADIUS)
              .startAngle(timestampToRadians(startTime))
              .endAngle(timestampToRadians(endTime));

            svg.append('path')
              .attr('d', arc)
              .style('fill', c[0] === GRAY_COLOR ? '#1a9afe' : c[0])
              .style("pointer-events", "all")
              // .style('cursor', 'pointer')
              .on("mouseover", function () {
                const text = projectsList.find(p => p.color === c[0]);
                if (text) {
                  showTooltip(this, `${text.name}<br>${text.duration} мин`);
                }
              })
              .on("mouseout", function () {
                removeTooltip(this);
              });

            startTime = endTime;
          }
        }

      }

    },

    $el (elementSelector) {
      $el = d3.select(elementSelector);
      return this;
    }
  };

};
