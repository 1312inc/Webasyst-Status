$(document).ready(function(){

    $( "#s-editor-slider-slider" ).slider({
      range: true,
      min: 0,
      max: 1440,
      values: [ 540, 1080 ],
      slide: function( event, ui ) {
      }
    });

});
