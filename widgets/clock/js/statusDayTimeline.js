export function statusDayTimeline () {

  const WIDTH = 300;
  const HEIGHT = 300;
  const INNER_RADIUS = 110;
  const OUTER_RADIUS = 130;
  const DEFAULT_COLOR = '#ae7dff80';

  let containerElem;
  let svgElem;
  let locale;

  function minutesToRadians (minutes) {
    return 720 / 24 * minutes / 60 * Math.PI / 180;
  }

  function minutesToTime (minutes, inDigits = false) {
    const hours = Math.floor(minutes / 60);
    const mins = Math.floor(minutes % 60);
    return inDigits
      ? `${hours}:${String(mins).padStart(2, '0')}`
      : `${hours > 0 ? `${hours}${locale.h}` : ''} ${mins > 0 ? `${mins}${locale.m}` : ''}`;
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

    let startTime = checkin.min;
    let endTime;
    let diff = checkin.max - checkin.min;

    const colors = checkin.projectDurationCss.split(',');

    for (let color = 1; color < colors.length; color += 2) {
      let c = colors[color].trim().split(' ');

      endTime = checkin.min + diff * Number.parseInt(c[1]) / 100;

      if (endTime !== startTime) {

        const arc = d3.svg.arc()
          .innerRadius(INNER_RADIUS)
          .outerRadius(OUTER_RADIUS)
          .startAngle(minutesToRadians(startTime))
          .endAngle(minutesToRadians(endTime))
          .cornerRadius(4);

        svgElem.append('path')
          .attr('d', arc)
          .style('fill', c[0] === "#f1f2f3" ? DEFAULT_COLOR : c[0])
          .attr('data-start', startTime)
          .attr('data-end', endTime)
          .on("mouseover", function () {
            const text = makeProjectsList(checkin.projectsDuration).find(p => p.color === c[0]);
            showTooltip(this, `${minutesToTime($(this).data('start'), true)}–${minutesToTime($(this).data('end'), true)} / ${text ? `${text.name}: ${minutesToTime(text.duration)}` : `${minutesToTime(checkin.duration)} ${locale.noProject}`}`);
          })
          .on("mouseout", function () {
            removeTooltip(this);
          });

      }

      startTime = endTime;
    }
  }

  /**
   * Draw Traces
   */
  function drawTraceCheckinArc (checkin) {
    const arc = d3.svg.arc()
      .innerRadius(INNER_RADIUS + 5)
      .outerRadius(OUTER_RADIUS - 5)
      .startAngle(minutesToRadians(checkin.min))
      .endAngle(minutesToRadians(checkin.max))
      .cornerRadius(4);

    svgElem.append('path')
      .attr('d', arc)
      .style('fill', 'var(--s-online-trace-color)')
      .on("mouseover", function () {
        showTooltip(this, `${minutesToTime(checkin.min, true)}–${minutesToTime(checkin.max, true)} / ${checkin.max - checkin.min} ${locale.m} ${locale.online}, ${checkin.durationString} ${locale.active}, ${checkin.breakString} ${locale.idle}`);
      })
      .on("mouseout", function () {
        removeTooltip(this);
      });
  }

  /**
 * Draw user actions on the timeline  
 */
  function drawLogsArcs (logs) {
    for (const app in logs) {
      for (const log of logs[app].logs) {

        const start = log.minutes_from_midnight;
        const end = start + 5;

        const arc = d3.svg.arc()
          .innerRadius(INNER_RADIUS - 4)
          .outerRadius(OUTER_RADIUS + 4)
          .startAngle(minutesToRadians(start))
          .endAngle(minutesToRadians(end))
          .cornerRadius(2);

        svgElem.append('path')
          .attr('d', arc)
          .attr("stroke", '#FFF')
          .attr("stroke-width", '2px')
          .style('fill', '#f3c200')
          .on("mouseover", function () {
            showTooltip(this, `${log.app_id} @ ${minutesToTime(start, true)}`);
          })
          .on("mouseout", function () {
            removeTooltip(this);
          });
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
        .startAngle(0)
        .endAngle(2 * Math.PI);

      svgElem.append('path')
        .attr('d', timeline)
        .style('fill', 'var(--s-timeline-color)');

      // Draw checkins
      for (const checkin of data.checkins.filter(c => c.id)) {
        if (!checkin.isTrace) {
          drawProjectCheckinArc(checkin);
        } else {
          drawTraceCheckinArc(checkin);
        }
      }

      // Draw User logs
      drawLogsArcs(data.walogs);

    },

    setLocale (messages) {
      locale = messages;
      return this;
    },

    $el (selector) {
      containerElem = d3.select(selector);
      return this;
    }
  };

};
