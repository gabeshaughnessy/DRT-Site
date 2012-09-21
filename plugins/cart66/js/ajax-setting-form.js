(function($){
  $(document).ready(function(){

    /* Mijireh notice dismissal */
    $('#mijireh_dismiss').click(function() {
      $.ajax({
        type: 'POST',
        url: ajaxurl,
        data: { action: 'dismiss_mijireh_notice' },
        dataType: 'json',
        success: function(result) {
          $('#mijireh_notice').hide();
        }
      });
      return false;
    });
    
    /* API method to get paging information */
    $.fn.dataTableExt.oApi.fnPagingInfo = function ( oSettings )
    {
        return {
            "iStart":         oSettings._iDisplayStart,
            "iEnd":           oSettings.fnDisplayEnd(),
            "iLength":        oSettings._iDisplayLength,
            "iTotal":         oSettings.fnRecordsTotal(),
            "iFilteredTotal": oSettings.fnRecordsDisplay(),
            "iPage":          Math.ceil( oSettings._iDisplayStart / oSettings._iDisplayLength ),
            "iTotalPages":    Math.ceil( oSettings.fnRecordsDisplay() / oSettings._iDisplayLength )
        };
    }

    /* Bootstrap style pagination control */
    $.extend( $.fn.dataTableExt.oPagination, {
        "bootstrap": {
            "fnInit": function( oSettings, nPaging, fnDraw ) {
                var oLang = oSettings.oLanguage.oPaginate;
                var fnClickHandler = function ( e ) {
                    e.preventDefault();
                    if ( oSettings.oApi._fnPageChange(oSettings, e.data.action) ) {
                        fnDraw( oSettings );
                    }
                };

                $(nPaging).addClass('pagination').append(
                    '<ul>'+
                        '<li class="prev disabled"><a href="javascript:void(0)">&larr; '+oLang.sPrevious+'</a></li>'+
                        '<li class="next disabled"><a href="javascript:void(0)">'+oLang.sNext+' &rarr; </a></li>'+
                    '</ul>'
                );
                var els = $('a', nPaging);
                $(els[0]).bind( 'click.DT', { action: "previous" }, fnClickHandler );
                $(els[1]).bind( 'click.DT', { action: "next" }, fnClickHandler );
            },

            "fnUpdate": function ( oSettings, fnDraw ) {
                var iListLength = 5;
                var oPaging = oSettings.oInstance.fnPagingInfo();
                var an = oSettings.aanFeatures.p;
                var i, j, sClass, iStart, iEnd, iHalf=Math.floor(iListLength/2);

                if ( oPaging.iTotalPages < iListLength) {
                    iStart = 1;
                    iEnd = oPaging.iTotalPages;
                }
                else if ( oPaging.iPage <= iHalf ) {
                    iStart = 1;
                    iEnd = iListLength;
                } else if ( oPaging.iPage >= (oPaging.iTotalPages-iHalf) ) {
                    iStart = oPaging.iTotalPages - iListLength + 1;
                    iEnd = oPaging.iTotalPages;
                } else {
                    iStart = oPaging.iPage - iHalf + 1;
                    iEnd = iStart + iListLength - 1;
                }

                for ( i=0, iLen=an.length ; i<iLen ; i++ ) {
                    // Remove the middle elements
                    $('li:gt(0)', an[i]).filter(':not(:last)').remove();

                    // Add the new list items and their event handlers
                    for ( j=iStart ; j<=iEnd ; j++ ) {
                        sClass = (j==oPaging.iPage+1) ? 'class="active"' : '';
                        $('<li '+sClass+'><a href="#">'+j+'</a></li>')
                            .insertBefore( $('li:last', an[i])[0] )
                            .bind('click', function (e) {
                                e.preventDefault();
                                oSettings._iDisplayStart = (parseInt($('a', this).text(),10)-1) * oPaging.iLength;
                                fnDraw( oSettings );
                            } );
                    }

                    // Add / remove disabled classes from the static elements
                    if ( oPaging.iPage === 0 ) {
                        $('li:first', an[i]).addClass('disabled');
                    } else {
                        $('li:first', an[i]).removeClass('disabled');
                    }

                    if ( oPaging.iPage === oPaging.iTotalPages-1 || oPaging.iTotalPages === 0 ) {
                        $('li:last', an[i]).addClass('disabled');
                    } else {
                        $('li:last', an[i]).removeClass('disabled');
                    }
                }
            }
        }
    } );

    $('.ajaxSettingForm').submit(function() {
      var data = getFormData($(this).attr('id'));
      $.ajax({
          type: "POST",
          url: ajaxurl,
          data: data,
          dataType: 'json',
          success: function(result) {
            $('#saveResult').html("<div id='saveMessage' class='" + result[0] + "'></div>");
            $('#saveMessage').append("<p>" + result[1] + "</p>").hide().fadeIn(1500).delay(3000).fadeOut(1500);
          }
      });
      return false;
    });
		
		$('#forcePluginUpdate').submit(function() {
      var data = getFormData($(this).attr('id'));
      $.ajax({
          type: "POST",
          url: ajaxurl,
          data: data,
          dataType: 'json',
          success: function(result) {
            window.location.href="plugins.php";
          }
      });
      return false;
    });

  })
})(jQuery);

function getFormData(formId) {
	$jq = jQuery.noConflict();
  var theForm = $jq('#' + formId);
  var str = '';
  $jq('input:not([type=checkbox], :radio), input[type=checkbox]:checked, input:radio:checked, select, textarea', theForm).each(
      function() {
        var name = $jq(this).attr('name');
        var val = encodeURIComponent($jq(this).val());
        str += name + '=' + val + '&';
      }
  );
  return str.substring(0, str.length-1);
}