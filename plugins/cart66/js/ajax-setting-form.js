var $j = jQuery.noConflict();

$j(document).ready(function() {
  
  $j('.ajaxSettingForm').submit(function() {
    var data = getFormData($j(this).attr('id'));
    $j.ajax({
        type: "POST",
        url: ajaxurl,
        data: data,
        dataType: 'json',
        success: function(result) {
          $j('#saveResult').html("<div id='saveMessage' class='" + result[0] + "'></div>");
          $j('#saveMessage').append("<p>" + result[1] + "</p>").hide().fadeIn(1500);
        }
    });
    setTimeout("$j('#saveMessage').hide('slow');", 5000);
    return false;
  });
  
});

function getFormData(formId) {
  var theForm = $j('#' + formId);
  var str = '';
  $j('input:not([type=checkbox], :radio), input[type=checkbox]:checked, input:radio:checked, select, textarea', theForm).each(
      function() {
        var name = $j(this).attr('name');
        var val = encodeURIComponent($j(this).val());
        str += name + '=' + val + '&';
      }
  );
  return str.substring(0, str.length-1);
}