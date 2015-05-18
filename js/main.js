$(document).ready(function () { 
  project = $('h2#projectName').text(); 
  detail = 'The file "{{unit}}" contains the following errors: \r\n',
  message = 'PROJECT: ' + project  + '\r\n';
  message +=  'UNIT: ' + $('#nameBanner').text() + '\r\n';
  file = '', 
  errors = $("div#copy ul li").each(function (index) {

    if ($(this).attr('class') !== file) {
      message += '\r\n' + detail.replace("{{unit}}", $(this).attr('class'));
    }
    file = $(this).attr('class');
    message += '-' + $(this).text() + '\r\n';

  });
 
  $("#copy-button").zclip({
    path: 'js/vendor/jquery/ZeroClipboard.swf',
    copy: message,
    afterCopy: function copied() {
      $("p#copied").show("slow", function () {
        $("html, body").animate({scrollTop: $(document).height()}, 1000);
      });
    }
  });
});
