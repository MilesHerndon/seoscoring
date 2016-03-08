$(document).ready(function(){
  var scoringTabs = $(".scoring-tab");

  scoringTabs.addClass('hidden').first().removeClass('hidden');

  scoringTabs.click(function(e){
    var $thisTab = $(this).href();
    scoringTabs.addClass('hidden');
    $($thisTab).removeClass('hidden');
  });

});