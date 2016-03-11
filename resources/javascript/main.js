$(document).ready(function(){

  var scoringTabs = $(".scoring-tab");

  scoringTabs.addClass('hidden').first().removeClass('hidden');

});

function getURLParameter(sParam){
  var sPageURL = window.location.search.substring(1);
  var sURLVariables = sPageURL.split('&');
  for (var i = 0; i < sURLVariables.length; i++)
  {
    var sParameterName = sURLVariables[i].split('=');
    if (sParameterName[0] == sParam)
    {
      return sParameterName[1];
    }
  }
}

$(window).on('load', function(e){
  var $widget = $('#fields-seo-scoring-widget')
  var tab = $widget.data('tab');
  var $seoTab = $('#tabs .tab[href="#tab'+tab+'"]');
  var score = $('#fields-final-rating1').attr('class');
  $seoTab.addClass(score);
  var field = getURLParameter('field');
  if (field == 'fields-seo-scoring-widget') {
    var top = $widget.offset().top - 40;
    $("html, body").delay(400).animate({ scrollTop: top });
  }
});
