/**
 * Created by Seneca on 6/14/14.
 */


jQuery(document).ready(function(){

    String.prototype.rpad = function(padString, length) {
        var str = this;
        while (str.length < length)
            str = str + padString;
        return str;
    }

    jQuery("input#PromoCode").change(function(){
        //console.log("moo" + jQuery(this).value());
        var invalidString = /[^A-Za-z0-9*]+/g;
        //var invalidString = /^\d{5}$/;
        if(this.value.match(invalidString)){
            alert("Promo codes can only contain letters and numbers");
            v = this.value.replace(invalidString,'');
            this.value=v;
        }

        if(this.value.length > 16 || this.value.length < 4){
            alert("Promo codes must be between 4 and 16 characters");
            if(this.value.length < 4){
                this.value = this.value.rpad("0",4);
            }
        }

        //jQuery.("#promocodeFeedback")
    });


});