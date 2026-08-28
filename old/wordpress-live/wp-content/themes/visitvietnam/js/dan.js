function vv_formatDateToYMD(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are 0-based
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

/*
function vvFormatCurrency(value){
    if(vvSiteCurrency == 'VND'){
        return formatted = amount.toLocaleString('vi-VN', {
          style: 'currency',
          currency: 'VND'
        });
    }else{
        return new Intl.NumberFormat('nb-NO', {
          style: 'currency',
          currency: 'NOK',

          minimumFractionDigits: 0
        }).format(value);
    }
}
*/

    function vvFormatCurrency(value, showSymbol = true) {
        value = parseFloat(value);
        value = parseFloat(value) * Number(conversionRates[vvSiteCurrency]);
        value = Number(value);
       
        if (vvSiteCurrency === 'VND') {
            return value.toLocaleString('vi-VN', {
                style: showSymbol ? 'currency' : 'decimal',
                currency: 'VND',
                currencyDisplay: 'code',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
        }
        if(vvSiteCurrency == 'EUR'){
            return value.toLocaleString('de-DE', {
                style: showSymbol ? 'currency' : 'decimal',
                currency: 'EUR',
                currencyDisplay: 'code',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
        }
        if(vvSiteCurrency == 'NOK'){
            return new Intl.NumberFormat('nb-NO', {
                style: showSymbol ? 'currency' : 'decimal',
                currency: 'NOK',
                currencyDisplay: 'code',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(value);
        }
        if(vvSiteCurrency == 'USD'){
            return value.toLocaleString('en-US', {
                style: showSymbol ? 'currency' : 'decimal',
                currency: 'USD',
                currencyDisplay: 'code',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });            
        }
    }