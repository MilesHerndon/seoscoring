$(document).ready(function(){

  var scoringTabs = $(".scoring-tab");

  scoringTabs.addClass('hidden').first().removeClass('hidden');

  scoringTabs.click(function(e){
    var $thisTab = $(this).href();
    scoringTabs.addClass('hidden');
    $($thisTab).removeClass('hidden');
  });

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
  var tab = getURLParameter('tab');
  if (tab) {
    $('#tabs .tab').removeClass('sel');
    $('#tab-'+(tab-1)).addClass('sel');
    $('div[id^=tab]').addClass('hidden');
    $('#tab'+tab).removeClass('hidden');
    var top = $('#fields-seo-scoring-widget').offset().top;
    $("html, body").delay(400).animate({ scrollTop: top });
  }
});
