function mult(value){
    var x,y;
    x= 2 * value ;
    y=3*value;

    document.getElementById('out2x').value = x;
}

function gelijk(value){
    var x;
    if(value >= 1){
        x = 40;
    }else{
        x = 30;
    }
    document.getElementById('gelijkt').value = x + " %";
}

function gemzonp(){
    var huizen = document.getElementById('huizen').value;
    var zonp = document.getElementById('zonp').value;
    var gemzp = zonp / huizen;
    document.getElementById('gemzonp').value = gemzp + " panelen";
}

function verbruikkosten(){
    var huis = document.getElementById('huizen').value;
    var verbruik = document.getElementById('gemjaarverbr').value;
    var gelijktijdigheid = document.getElementById('gelijkt').value;
    var energiepr = document.getElementById('energiepr').value;
    var terugleverpr = document.getElementById('teruglvrkost').value;
    var terugleververg = document.getElementById('teruglvrverg').value;
    var zonstr = document.getElementById('zonp').value * 350;
    var cost;

    cost = (verbruik - (zonstr * gelijktijdigheid / 100)) * energiepr * huis;

    document.getElementById('jaarverbr').value = cost + " €";
}

function terugleverkosten(){
    var huis = document.getElementById('huizen').value;
    var verbruik = document.getElementById('gemjaarverbr').value;
    var gelijktijdigheid = document.getElementById('gelijkt').value;
    var energiepr = document.getElementById('energiepr').value;
    var terugleverpr = document.getElementById('teruglvrkost').value;
    var terugleververg = document.getElementById('teruglvrverg').value;
    var zonstr = document.getElementById('zonp').value * 350;
    var cost;

    cost = (zonstr * gelijktijdigheid / 100) * terugleverpr * huis;

    document.getElementById('terugleverkost').value = cost + " €";
}

function terugleververgoeding(){
    var huis = document.getElementById('huizen').value;
    var verbruik = document.getElementById('gemjaarverbr').value;
    var gelijktijdigheid = document.getElementById('gelijkt').value;
    var energiepr = document.getElementById('energiepr').value;
    var terugleverpr = document.getElementById('teruglvrkost').value;
    var terugleververg = document.getElementById('teruglvrverg').value;
    var zonstr = document.getElementById('zonp').value * 350;
    var cost;

    cost = (zonstr * gelijktijdigheid / 100) * terugleververg * huis;

    document.getElementById('terugleververg').value = cost + " €";
}

function participatiekosten(){
    var deelnr = document.getElementById('deelnemer').value;
    var cost = deelnr * 24;

    document.getElementById('participkost').value = cost + "  €";
}

function totaal(){
    var energiekost = document.getElementById('jaarverbr').value;
    var terugleverkost = document.getElementById('terugleverkost').value;
    var terugleververg = document.getElementById('terugleververg').value;
    var participatiekost = document.getElementById('participkost').value;
    var cost = energiekost * 1 + terugleverkost * 1 - terugleververg * 1 + participatiekost * 1;

    document.getElementById('totaal').value = cost + "  €";
}