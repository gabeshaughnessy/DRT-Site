
function Currency(currency){
    this.currency = currency;

    this.toNumber = function(text){
        //converting to a string if a number as passed
        text = text + " ";
        //Removing all non-numeric characters
        var clean_number = "";
        var is_negative = false;
        for(var i=0; i<text.length; i++){
            var digit = text.substr(i,1);
            if( (parseInt(digit) >= 0 && parseInt(digit) <= 9) || digit == "," || digit == "." )
                clean_number += digit;
            else if(digit == '-')
                is_negative = true;
        }

        //Removing thousand separators but keeping decimal point
        var float_number = "";

        for(var i=0; i<clean_number.length; i++)
        {
            var char = clean_number.substr(i,1);
            if (char >= '0' && char <= '9')
                float_number += char;
            else if((char == "." || char == ",") && clean_number.length - i <= 3)
                float_number += ".";
        }

        if(is_negative)
            float_number = "-" + float_number;

        return this.isNumeric(float_number) ? parseFloat(float_number) : false;
    };

    this.toMoney = function(number){
        number = this.toNumber(number);
        if(number === false)
            return "";

        number = number + "";
        negative = "";
        if(number[0] == "-"){
            negative = "-";
            number = parseFloat(number.substr(1));
        }
        money = this.numberFormat(number, this.currency["decimals"], this.currency["decimal_separator"], this.currency["thousand_separator"]);

        var symbol_left = this.currency["symbol_left"] ? this.currency["symbol_left"] + this.currency["symbol_padding"] : "";
        var symbol_right = this.currency["symbol_right"] ? this.currency["symbol_right"] + this.currency["symbol_padding"] : "";
        money =  negative + symbol_left + money + symbol_right;
        return money;
    };

    this.numberFormat = function(number, decimals, dec_point, thousands_sep){
        number = (number+'').replace(',', '').replace(' ', '');
        var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep, dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',

        toFixedFix = function (n, prec) {
            var k = Math.pow(10, prec);
            return '' + Math.round(n * k) / k;
        };

        // Fix for IE parseFloat(0.55).toFixed(0) = 0;
        s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
        if (s[0].length > 3) {
            s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
        }

        if ((s[1] || '').length < prec) {
            s[1] = s[1] || '';
            s[1] += new Array(prec - s[1].length + 1).join('0');
        }

        return s.join(dec);
    }

    this.isNumeric = function(number){
        return !isNaN(parseFloat(number)) && isFinite(number);
    };
}
