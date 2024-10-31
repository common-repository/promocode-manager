jQuery(document).ready(function() {
  var errorMessage = 'Improper character(s) or character length entered',
  pattern = /^[A-Za-z0-9]+$/,
  maxLength = 16,
  promocode_val,
  promocode_input;

  jQuery( ".dkdspinner" ).hide();// hide the loading spinner

  //html in json has slashes, we want to strip these
  String.prototype.stripSlashes = function(){
    return this.replace(/\\(.)/mg, "$1");
  }

  //gets a query string param
  function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
    results = regex.exec(location.search);
    return results == null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
  }

  /**
   * @function validatePromoCode
   * validates a promo code (alphanumeric only)
   */
  function validatePromoCode(pattern, code, input) {
    // check for any input (keyup) on the promo code form field
    jQuery(input).keyup(function() {
      if(this.value.length > maxLength) {
        // code is too long
        showMessage(jQuery('.promosubmitform').siblings('.dkdmsg'), errorMessage, 'error');
      }
      if (pattern.test(this.value) === false) {
        // code is invalid
        showMessage(jQuery('.promosubmitform').siblings('.dkdmsg'), errorMessage, 'error');
      }
    });
  }

    // call the promo code inline validation function
    promocode_val = jQuery('.promosubmitform').children(".promocodetext").val();
    promocode_input = jQuery('.promosubmitform').children(".promocodetext");
    validatePromoCode(pattern, promocode_val, promocode_input);

    //toggle the promosubmit form
    jQuery(document).on("click", ".dkdpromolink", function() {
      jQuery(this).siblings(".promosubmitform").fadeToggle();
    });

    //promo submission
    jQuery(document).on("submit", ".promosubmitform", function(e) {
      e.preventDefault();

      if(jQuery(this).children(".promocodetext").val().length == 0 || jQuery(this).find(".dkdSubmitBtn").hasClass("dkdSubmitBtnUsed")) {
        return;
      }

      var submission = jQuery(this).serializeArray();
      
      promoSubmit(submission, jQuery(this), function(jq, data) {
        if(data.success) {
          showMessage(jQuery(jq).siblings(".dkdmsg"), data.message, "success");
          jQuery(jq).siblings(".dkdundo").show();
          jQuery(jq).siblings(".dkdpromolink").hide();
          jQuery(jq).find(".dkdSubmitBtn").addClass("dkdSubmitBtnUsed");
        } else {
          showMessage(jQuery(jq).siblings(".dkdmsg"), data.message, "error");
        }
      })
    });
    
    jQuery(document).on("click", ".dkdundo", function() {
      showPromoPrice();
      showPromoAttributes();
      jQuery(this).hide();
      jQuery(this).parent().find(".dkdSubmitBtn").removeClass("dkdSubmitBtnUsed");
      jQuery(this).siblings(".dkdpromolink").show();
      jQuery(this).siblings(".dkdmsg").html("&nbsp;");
      jQuery('.promocodetext').val("");
    });

    function showMessage(target,msg,msg_type) {
      //target.show();
      target.removeClass("success error");
      target.addClass(msg_type);
      if(!msg){
          msg="&nbsp;";//empty space to try to keep white space consistent
      }
      target.html(msg);
    }

    function promoSubmit(submission,context,callback) {
      if(submission){
        jQuery(context).siblings(".dkdmsg").html("&nbsp;");
        submission.push( {"name":"PartnerID","value":settings.partnerid});
        jQuery( context ).ajaxStart(function() {
          jQuery( ".dkdspinner" ).show();
        });
        jQuery( context ).ajaxStop(function() {
          jQuery( ".dkdspinner" ).hide();
        });
        jQuery.ajax({
          type: "POST",
          url: settings.api_url,
          dataType: "json",
          context:context,
          data: {
            action: "api",
            data: submission,
            api_action: "promocode_submission"
          },
          success: function(data) {
            //console.log(data); // show response from the php script.
            showPromoPrice(data['data']);
            showPromoAttributes(data['data']);
            callback(context,data)
          }
        });
      }
    }

    function showPromoPrice(rows) {
      var rows_k = Array();
      for(idx in rows){
        rows_k[rows[idx]['ProductID']] = rows[idx];
      }

      jQuery(".dkdproductprice").each(function() {
        var productid = parseInt(jQuery(this).attr("data-productid"));
        
        if(rows_k[productid]) {
          jQuery(this).closest('.idg-compare-product-block').addClass('product--disabled');
          var val ="$"+parseFloat(rows_k[productid]['Price']).toFixed(2);
          var prefix = jQuery(this).attr("data-priceprefix");
          var suffix = jQuery(this).attr("data-pricesuffix");
          val = prefix + val + suffix;
          jQuery(this).siblings(".promoprice").html(val);
          var prod_width = jQuery(this).children(".productprice").width();
          var price_width = jQuery(this).children(".promoprice").width();
          jQuery('.productprice', this).children(".dkdstrike").show();
        } else{
          jQuery(this).siblings(".promoprice").html("");
          jQuery('.productprice', this).children(".dkdstrike").hide();
        }
      });
    }

  function showPromoAttributes(rows) {
    var rows_k=Array();
    
    for(idx in rows){
      rows_k[rows[idx]['ProductID']]=rows[idx];
    }

    jQuery(".dkdproductattr").each(function() {
      var productid = parseInt(jQuery(this).attr("data-productid"));
      var attr = parseInt(jQuery(this).attr("data-attribute"));
      var attrtype = jQuery(this).attr("data-attributetype");
      if(rows_k[productid] && rows_k[productid]['Attributes']&& rows_k[productid]['Attributes'][attr]){
        var newval = rows_k[productid]['Attributes'][attr]["Value"];
        if(attrtype=="href") {
          jQuery(this).attr("data-oldval",jQuery(this).attr("href"));
          jQuery(this).attr("href",newval.stripSlashes());
        } else{
          jQuery(this).attr("data-oldval",jQuery(this).html());
          jQuery(this).html(newval.stripSlashes());
        }
      } else {
          var oldval = jQuery(this).attr("data-oldval");
          if(attrtype=="href"){
            jQuery(this).attr("href",oldval);
          }
          else{
            jQuery(this).html(oldval);
          }
        }
      });
  }
  
  //we get a specific qs param to trigger promo submission on page load
  var promocode_qs = getParameterByName("promocode");

  if(promocode_qs) {
    jQuery('.promocodetext').val(promocode_qs);
    jQuery('.promosubmitform').submit();
  }
});