/*
Gemaakt door: Avery Vermaas
MIT licentie:
THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, 
INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR 
PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE 
FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR 
OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER 
DEALINGS IN THE SOFTWARE. 
 */

let intervalId; // Store the interval ID globally
 
function logMessage() {
  console.log("Logging every second...");
}
 
    intervalId = setInterval(logMessage, 2000);
    intervalId = setInterval(inkoopz, 2000);
    intervalId = setInterval(inkoopm, 2000);
    intervalId = setInterval(tlkz, 2000);
    intervalId = setInterval(tlkm, 2000);
    intervalId = setInterval(tlvz, 2000);
    intervalId = setInterval(tlvm, 2000);
    intervalId = setInterval(totaalz, 2000);
    intervalId = setInterval(totaalm, 2000);
    intervalId = setInterval(gelijkz, 2000);
    intervalId = setInterval(gelijkm, 2000);
    intervalId = setInterval(versch, 2000);
    intervalId = setInterval(inkoop26, 2000);
    intervalId = setInterval(tlk26, 2000);
    intervalId = setInterval(tlv26, 2000);
    intervalId = setInterval(totaal26, 2000);
    intervalId = setInterval(gelijkt26, 2000);
    intervalId = setInterval(updatepres, 2000);
    console.log("Logging started!");

function gelijkt26(){
    var opw = document.getElementById('opwek').value;
    
    if(opw >0){
        document.getElementById('gemegl26').value = "30 %";
    }else{
        document.getElementById('gemegl26').value = "0 %";
    }
}


function inkoopz(){
    var restverb = document.getElementById('kwhrestz').value;
    var prijs = document.getElementById('energiepr').value;
    var cost;

    cost = restverb * prijs;

    document.getElementById('jaarverbrz').value = cost.toFixed(2);
    console.log(cost);
}

function inkoopm(){
    var restverb = document.getElementById('kwhrestm').value;
    var prijs = document.getElementById('buurtstr').value;
    var cost;

    cost = restverb * prijs;

    document.getElementById('tariefbuurtstr').value = cost.toFixed(2);
    console.log(cost);
}

function inkoop26(){
    var verb = document.getElementById('gemjaarverbr').value;
    var prijs = document.getElementById('energiepr26').value;
    var cost;

    var opw = document.getElementById('opwek').value;
    if(opw >0){
        cost = (verb*1-opw*1) * prijs*1;
    }else{
        cost = verb*1 * prijs*1;
    }

    /* 500 x penegpr */

    document.getElementById('inkoop26').value = cost.toFixed(2);
    console.log(cost);
}

function tlkz(){
    var ongel = document.getElementById('kwhongelijkz').value;
    var tlk = document.getElementById('teruglvrkost').value;
    var cost;

    cost = ongel*1  * tlk*1;

    document.getElementById('terugleverkostz').value = cost.toFixed(2);
    console.log(cost);
}

function tlkm(){
    var ongel = document.getElementById('kwhongelijkm').value;
    var tlk = document.getElementById('teruglvrkostbs').value;
    var cost;

    cost = ongel*1  * tlk*1;

    document.getElementById('terugleverbuurtm').value = cost.toFixed(2);
    console.log(cost);
}

function tlk26(){
    var ongel = document.getElementById('gemjaarverbr').value;
    var tlk = document.getElementById('teruglvrkost26').value;
    var cost;

    var opw = document.getElementById('opwek').value;
    if(opw >0){
        cost = ongel*1 * tlk*1 * 0.7;
    }else{
        cost = 0;
    }

    document.getElementById('tlk26').value = cost.toFixed(2);
    console.log(cost);
}

function tlvz(){
    var ongel = document.getElementById('kwhongelijkz').value;
    var tlv = document.getElementById('teruglvrverg').value;
    var cost;

    cost = ongel*1 * tlv*1;

    document.getElementById('terugleververgz').value = "-" + cost.toFixed(2);
    console.log(cost);
}

function tlvm(){
    var ongel = document.getElementById('kwhongelijkm').value;
    var tlv = document.getElementById('kortingib').value;
    var cost;

    cost = ongel*1 * tlv*1;

    document.getElementById('inkomstbelast').value = "-" + cost.toFixed(2);
    console.log(cost);
}

function tlv26(){
    var ongel = document.getElementById('gemjaarverbr').value;
    var tlv = document.getElementById('teruglvrverg26').value;
    var cost;

    var opw = document.getElementById('opwek').value;
    if(opw >0){
        cost = ongel*1 * tlv*1 * 0.7;
    }else{
        cost = 0;
    }

    document.getElementById('tlv26').value = "-" + cost.toFixed(2);
    console.log(cost);
}

function totaalz(){
    var inkk = document.getElementById('jaarverbrz').value;
    var ink = inkk.replace(" €", "");
    tlkk = document.getElementById('terugleverkostz').value;
    var tlk = tlkk.replace(" €","")
    var tlvv = document.getElementById('terugleververgz').value;
    var tlv = tlvv.replace(" €","");
    var tl = tlv.replace("-","")
    

    cost = ink*1+tlk*1-tl*1;

    document.getElementById('totaalz').value = cost.toFixed(2);
    console.log(cost);
}

function totaalm(){
    var inkk = document.getElementById('tariefbuurtstr').value;
    var ink = inkk.replace(" €", "");
    tlkk = document.getElementById('terugleverbuurtm').value;
    var tlk = tlkk.replace(" €","")
    var tlvv = document.getElementById('inkomstbelast').value;
    var tlv = tlvv.replace(" €","");
    var tl = tlv.replace("-","")
    var lid = document.getElementById('participkostm').value
    
    if(document.getElementById('opwek').value >0){
        document.getElementById('participkostm').value = 24;
        cost = ink*1+tlk*1-tl*1+24;
    }else{
        document.getElementById('participkostm').value = 12;
        cost = ink*1+tlk*1-tl*1+12;
    }
    document.getElementById('totaalm').value = cost.toFixed(2);

    if(document.getElementById('opwek').value >0){
        document.getElementById('participkostm').value = 24;
    }else{
        document.getElementById('participkostm').value = 12;
    }
}

function totaal26(){
    var inkk = document.getElementById('inkoop26').value;
    var ink = inkk.replace(" €", "");
    tlkk = document.getElementById('tlk26').value;
    var tlk = tlkk.replace(" €","")
    var tlvv = document.getElementById('tlv26').value;
    var tlv = tlvv.replace(" €","");
    var tl = tlv.replace("-","")
    

    cost = ink*1+tlk*1-tl*1;

    document.getElementById('tot26').value = cost.toFixed(2);
    console.log(cost);
}

function gelijkz(){
    var opw = document.getElementById('opwek').value;
    var gelt;
    var verbr = document.getElementById('gemjaarverbr').value;
    var cost;
    var tsoc;
    var rest;

    if(document.getElementById('opwek').value >0){
        gelt = 30;
    }else{
        gelt = 0;
    }

    cost = opw * gelt / 100;
    tsoc = opw * (100-gelt) / 100;
    rest = verbr - cost;

    document.getElementById('kwhgelijkz').value = cost;
    document.getElementById('kwhongelijkz').value = tsoc;
    document.getElementById('kwhrestz').value = rest;
}

function gelijkm(){
    var opw = document.getElementById('opwek').value;
    var gel = document.getElementById('gelijktm').value;
    var gelt = gel.replace(" %", "");
    var verbr = document.getElementById('gemjaarverbr').value;
    var cost;
    var tsoc;
    var rest;

    cost = opw * gelt / 100;
    tsoc = opw * (100-gelt) / 100;
    rest = verbr - cost;

    document.getElementById('kwhgelijkm').value = cost;
    document.getElementById('kwhongelijkm').value = tsoc;
    document.getElementById('kwhrestm').value = rest;
}

function versch(){
    var zonder = document.getElementById('totaalz').value;
    var z = zonder.replace(" €", "");
    var met = document.getElementById('totaalm').value;
    var m = met.replace(" €","");
    var vers;

    var vers = z*1 - m*1;

    document.getElementById('verschil').value = vers.toFixed(2);

    /* verschil 26 27 */
    var t26 = document.getElementById('tot26').value;
    var tt26 = t26.replace(" €", "");
    var t27 = document.getElementById('totaalz').value;
    var tt27 = t27.replace(" €","");
    var verschil2627 = tt27*1 - tt26*1;

    document.getElementById('verschil2627').value = verschil2627.toFixed(2);
}

/* Presets */
function p1(){
    document.getElementById('gemjaarverbr').value = 1500;
    document.getElementById('opwek').value = 1000;
}

function p2(){
    document.getElementById('gemjaarverbr').value = 2500;
    document.getElementById('opwek').value = 2000;
}

function p3(){
    document.getElementById('gemjaarverbr').value = 3000;
    document.getElementById('opwek').value = 2500;
}

function p4(){
    document.getElementById('gemjaarverbr').value = 3500;
    document.getElementById('opwek').value = 3000;
}

function updatepres(){
    if(document.getElementById('zonp').checked){
        console.log("zonP");
        if(document.getElementById('1').checked){
            document.getElementById('opwek').value = 1000;
        }
        else if(document.getElementById('2').checked){
            document.getElementById('opwek').value = 2000;
        }
        else if(document.getElementById('3').checked){
            document.getElementById('opwek').value = 2500;
        }
        else if(document.getElementById('4').checked){
            document.getElementById('opwek').value = 3000;
        }
    }else{
        if(document.getElementById('cust').checked){
            console.log("not my prob");
        }else{
        document.getElementById('opwek').value = 0;
        }
    }
}