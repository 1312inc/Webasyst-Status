export function statusDayTimeline () {

  const WIDTH = 300;
  const HEIGHT = 300;
  const INNER_RADIUS = 110;
  const OUTER_RADIUS = 130;
  const GRAY_COLOR = '#f3f3f3';
  const DEFAULT_COLOR = '#1a9afe';

  let containerElem;
  let svgElem;

  function timestampToRadians (timestamp, isGmt = false) {
    const hours = new Date(timestamp * 1000).getHours() + new Date(timestamp * 1000).getMinutes() / 60;
    return 720 / 24 * (hours + new Date().getTimezoneOffset() * (isGmt ? -1 : 1) / 60) * Math.PI / 180;
  };

  function showTooltip (el, text) {
    if (typeof tippy !== 'undefined') {
      tippy(el, {
        content: text,
        allowHTML: true,
        arrow: false
      });
    }
  };

  function removeTooltip (el) {
    if (el._tippy) {
      el._tippy.destroy();
    }
  };

  const makeProjectsList = (list) => {
    return Object.values(list).map(p => {
      return {
        duration: p.duration,
        ...p.project
      };
    });
  };

  /**
   * Draw User's projects checkins
   */
  function drawProjectCheckinArc (checkin) {

    let startTime = checkin.startTimestamp;
    let endTime;
    let diff = checkin.endTimestamp - checkin.startTimestamp;

    const colors = checkin.projectDurationCss.split(',');

    for (let color = 1; color < colors.length; color += 2) {
      let c = colors[color].trim().split(' ');

      if (c[1] !== '0%') {
        endTime = checkin.startTimestamp + diff * Number.parseInt(c[1]) / 100;

        const arc = d3.svg.arc()
          .innerRadius(INNER_RADIUS)
          .outerRadius(OUTER_RADIUS)
          .startAngle(timestampToRadians(startTime))
          .endAngle(timestampToRadians(endTime));

        svgElem.append('path')
          .attr('d', arc)
          .style('fill', c[0] === GRAY_COLOR ? DEFAULT_COLOR : c[0])
          .on("mouseover", function () {
            const text = makeProjectsList(checkin.projectsDuration).find(p => p.color === c[0]);
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

  /**
   * Draw Traces
   */
  function drawTraceCheckinArc (checkin) {
    const arc = d3.svg.arc()
      .innerRadius(INNER_RADIUS + 5)
      .outerRadius(OUTER_RADIUS - 5)
      .startAngle(timestampToRadians(checkin.startTimestamp))
      .endAngle(timestampToRadians(checkin.endTimestamp));

    svgElem.append('path')
      .attr('d', arc)
      .style('fill', 'rgba(0,0,0,0.12)');
  }

  /**
 * Draw user actions on the timeline  
 */
  function drawLogsArcs (logs) {
    for (const app in logs) {
      for (const log of logs[app].logs) {

        const start = new Date(log.datetime).getTime() / 1000;
        const end = start + 260;

        const arc = d3.svg.arc()
          .innerRadius(INNER_RADIUS - 4)
          .outerRadius(OUTER_RADIUS + 4)
          .startAngle(timestampToRadians(start, true))
          .endAngle(timestampToRadians(end, true));

        svgElem.append('path')
          .attr('d', arc)
          .attr("stroke", '#FFF')
          .attr("stroke-width", '2px')
          .style('fill', '#f3c200');
      }
    }
  }

  return {

    render (data) {
      svgElem = containerElem.append("svg")
        .attr("width", WIDTH)
        .attr("height", HEIGHT)
        .append("g")
        .attr("transform", `translate(${WIDTH / 2},${HEIGHT / 2})`);

      // Draw gray full circle timeline
      const timeline = d3.svg.arc()
        .innerRadius(INNER_RADIUS)
        .outerRadius(OUTER_RADIUS)
        .startAngle(Math.PI / 180)
        .endAngle(2 * Math.PI);

      svgElem.append('path')
        .attr('d', timeline)
        .style('fill', GRAY_COLOR);

      // Draw checkins
      for (const checkin of data.checkins) {
        if (!checkin.isTrace) {
          drawProjectCheckinArc(checkin);
        } else {
          drawTraceCheckinArc(checkin);
        }
      }

      // Draw User logs
      drawLogsArcs(data.walogs);

    },

    $el (selector) {
      containerElem = d3.select(selector);
      return this;
    }
  };

};
