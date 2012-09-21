var ajaxManager = (function() {
  $jq = jQuery.noConflict();
  var requests = [];
  
  return {
    addReq: function(opt) {
      requests.push(opt);
    },
    removeReq: function(opt) {
      if($jq.inArray(opt, requests) > -1) {
        requests.splice($jq.inArray(opt, requests), 1);
      }
    },
    run: function() {
      var self = this, orgSuc;
      
      if(requests.length) {
        oriSuc = requests[0].complete;
      
        requests[0].complete = function() {
          if(typeof oriSuc === 'function') {
            oriSuc();
          }
          requests.shift();
          self.run.apply(self, []);
        };   

        $jq.ajax(requests[0]);
      } else {
        self.tid = setTimeout(function() {
          self.run.apply(self, []);
        }, 1000);
      }
    },
    stop:  function() {
      requests = [];
      clearTimeout(this.tid);
    }
  };
}());
ajaxManager.run();
(function($){
  $(document).ready(function(){
    $('.modalClose').click(function() {
      $('.Cart66Unavailable, .Cart66Warning, .Cart66Error').fadeOut(800);
    });

    $('#Cart66CancelPayPalSubscription').click(function() {
      return confirm('Are you sure you want to cancel your subscription?\n');
    });
  })
})(jQuery);

function getCartButtonFormData(formId) {
	$jq = jQuery.noConflict();
  var theForm = $jq('#' + formId);
  var str = '';
  $jq('input:not([type=checkbox], :radio), input[type=checkbox]:checked, input:radio:checked, select, textarea', theForm).each(
    function() {
      var name = $jq(this).attr('name');
      var val = $jq(this).val();
      str += name + '=' + encodeURIComponent(val) + '&';
    }
  );

  return str.substring(0, str.length-1);
}

function inventoryCheck(formId, ajaxurl, useAjax, productName, productUrl, addingText) {
  $jq = jQuery.noConflict();
  var mydata = getCartButtonFormData('cartButtonForm_' + formId);
  ajaxManager.addReq({
    type: "POST",
    url: ajaxurl + '=1',
    data: mydata,
    dataType: 'json',
    success: function(response) {
      if(response[0]) {
        $jq('#task_' + formId).val('addToCart');
        if(useAjax == 'no') {
          $jq('#cartButtonForm_' + formId).submit();
        }
        else {
          buttonTransform(formId, ajaxurl, productName, productUrl, addingText);
        }
      }
      else {
        $jq('.modalClose').show();
        $jq('#stock_message_box_' + formId).fadeIn(300);
        $jq('#stock_message_' + formId).html(response[1]);
      }
    },
    error: function(xhr,err){
      alert("readyState: "+xhr.readyState+"\nstatus: "+xhr.status);
    }
  });
}

function addToCartAjax(formId, ajaxurl, productName, productUrl, buttonText) {
  $jq = jQuery.noConflict();
  var options1 = $jq('.cart66Options.options_1',$jq('#cartButtonForm_' + formId)).val();
  var options2 = $jq('.cart66Options.options_2',$jq('#cartButtonForm_' + formId)).val();
  var itemQuantity = $jq('#Cart66UserQuantityInput_' + formId).val();
  var itemUserPrice = $jq('#Cart66UserPriceInput_' + formId).val();
  var cleanProductId = formId.split('_');
  cleanProductId = cleanProductId[0];
  var data = {
	  cart66ItemId: cleanProductId,
	  itemName: productName,
	  options_1: options1,
	  options_2: options2,
	  item_quantity: itemQuantity,
	  item_user_price: itemUserPrice,
	  product_url: productUrl
  };
  
  ajaxManager.addReq({
    type: "POST",
    url: ajaxurl + '=2',
    data: data,
    dataType: 'json',
    success: function(response) {
      $jq('#addToCart_' + formId).removeAttr('disabled');
      $jq('#addToCart_' + formId).removeClass('ajaxPurAddToCart');
      $jq('#addToCart_' + formId).val(buttonText);
      $jq.hookExecute('addToCartAjaxHook', response);
      ajaxUpdateCartWidgets(ajaxurl);
      if($jq('.customAjaxAddToCartMessage').length > 0) {
        $jq('.customAjaxAddToCartMessage').show().html(response.msg);
        $jq.hookExecute('customAjaxAddToCartMessage', response);
      }
      else {
        if((response.msgId) == 0){
          $jq('.success_' + formId).fadeIn(300);
          $jq('.success_message_' + formId).html(response.msg);
          $jq('.success_' + formId).delay(2000).fadeOut(300);
        }
        if((response.msgId) == -1){
          $jq('.warning_' + formId).fadeIn(300);
          $jq('.warning_message_' + formId).html(response.msg);
        }
        if((response.msgId) == -2){
          $jq('.error_' + formId).fadeIn(300);
          $jq('.error_message_' + formId).html(response.msg);
        }
      }
    }
  })
}
function buttonTransform(formId, ajaxurl, productName, productUrl, addingText) {
  $jq = jQuery.noConflict();
  var buttonText = $jq('#addToCart_' + formId).val();
  $jq('#addToCart_' + formId).attr('disabled', 'disabled');
  $jq('#addToCart_' + formId).addClass('ajaxPurAddToCart');
  $jq('#addToCart_' + formId).val(addingText);
  addToCartAjax(formId, ajaxurl, productName, productUrl, buttonText);
}
function ajaxUpdateCartWidgets(ajaxurl) {
  $jq = jQuery.noConflict();
  var widgetId = $jq('.Cart66CartWidget').attr('id');
  var data = {
	  action: "ajax_cart_elements"
  };
  ajaxManager.addReq({
    type: "POST",
    url: ajaxurl + '=3',
    data: data,
    dataType: 'json',
    success: function(response) {
      $jq.hookExecute('cartElementsAjaxHook', response);
      $jq('#Cart66AdvancedSidebarAjax, #Cart66WidgetCartContents').show();
      $jq('.Cart66WidgetViewCartCheckoutEmpty, #Cart66WidgetCartEmpty').hide();
      $jq('#Cart66WidgetCartLink').each(function(){
        widgetContent = "<span id=\"Cart66WidgetCartCount\">" + response.summary.count + "</span>";
        widgetContent += "<span id=\"Cart66WidgetCartCountText\">" + response.summary.items + "</span>";
        widgetContent += "<span id=\"Cart66WidgetCartCountDash\"> – </span>"
        widgetContent += "<span id=\"Cart66WidgetCartPrice\">" + response.summary.currencySymbol + response.summary.amount + "</span>";
        $jq(this).html(widgetContent).fadeIn('slow');
      });
      $jq('.Cart66RequireShipping').each(function(){
        if(response.shipping == 1) {
          $jq(this).show();
        }
      })
      $jq('#Cart66WidgetCartEmptyAdvanced').each(function(){
        widgetContent = "You have " + response.summary.count + " " + response.summary.items + " (" + response.summary.currencySymbol + response.summary.amount + ") in your shopping cart";
        $jq(this).html(widgetContent).fadeIn('slow');
      });
      $jq("#Cart66AdvancedWidgetCartTable .product_items").remove();
      $jq.each(response.products.reverse(), function(index, array){  
        widgetContent = "<tr class=\"product_items\"><td>";
        widgetContent += "<span class=\"Cart66ProductTitle\">" + array.productName + "</span>";
        widgetContent += "<span class=\"Cart66QuanPrice\">";
        widgetContent += "<span class=\"Cart66ProductQuantity\">" + array.productQuantity + "</span>";
        widgetContent += "<span class=\"Cart66MetaSep\"> x </span>"; 
        widgetContent += "<span class=\"Cart66CurSymbol\">" + array.currencySymbol + "</span> ";
        widgetContent += "<span class=\"Cart66ProductPrice\">" + array.productPrice + "</span>";
        widgetContent += "</span>";
        widgetContent += "</td><td class=\"Cart66ProductSubtotalColumn\">";
        widgetContent += "<span class=\"Cart66ProductSubtotal\">" + array.productSubtotal + "</span>";
        widgetContent += "</td></tr>";
        $jq("#Cart66AdvancedWidgetCartTable tbody").prepend(widgetContent).fadeIn("slow");  
      });
      $jq('.Cart66Subtotal').each(function(){
        $jq(this).html(response.subtotal)
      });
      $jq('.Cart66Shipping').each(function(){
        $jq(this).html(response.shippingAmount)
      });
    }
  })
}

jQuery.extend({ 
  hookExecute: function (function_name, response){
    if (typeof window[function_name] == "function"){
      window[function_name](response);
      return true;
    }
    else{
      return false;
    }
  }
});