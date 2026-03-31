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
 

document.addEventListener('DOMContentLoaded', () => {
  updateAll();
  setInterval(updateAll, 1000);
});

function updateAll() {
  try {
    inkoopz();
    inkoopm();
    inkoopth();
    tlkz();
    tlkm();
    tlkth();
    tlvz();
    tlvm();
    tlvth();
    totaalz();
    totaalm();
    totaalthuis();
    versch();
    inkoop26();
    tlk26();
    tlv26();
    totaal26();
    gelijkt26();
    updatepres();
  } catch (e) {
    console.warn('Update skipped:', e.message);
  }}


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

function inkoopth(){
    var restverb = document.getElementById('kwhrestth').value;
    var prijs = document.getElementById('energiepr').value;
    var cost;

    cost = restverb * prijs;

    document.getElementById('jaarverbth').value = cost.toFixed(2);
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

function tlkth(){
    var ongel = document.getElementById('kwhongelijkth').value;
    var tlk = document.getElementById('teruglvrkost').value;
    var cost;

    cost = ongel*1  * tlk*1;

    document.getElementById('terugleverkostth').value = cost.toFixed(2);
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

function tlvth(){
    var ongel = document.getElementById('kwhongelijkth').value;
    var tlv = document.getElementById('teruglvrverg').value;
    var cost;

    cost = ongel*1 * tlv*1;

    document.getElementById('terugleververgth').value = "-" + cost.toFixed(2);
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
    var tlkk = document.getElementById('terugleverkostz').value;
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
    var tlkk = document.getElementById('terugleverbuurtm').value;
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

function totaalthuis() {
  const ink = Number(document.getElementById('jaarverbth').value || 0);
  const tlk = Number(document.getElementById('terugleverkostth').value || 0);
  const tlv = Number(
    (document.getElementById('terugleververgth').value || '0').replace('-', '')
  );

  const total = ink + tlk - tlv;
  document.getElementById('totaalthuis').value = total.toFixed(2);
}


function totaal26(){
    var inkk = document.getElementById('inkoop26').value;
    var ink = inkk.replace(" €", "");
    var tlkk = document.getElementById('tlk26').value;
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

function gelijkth(){
    var opw = document.getElementById('opwek').value;
    var gelt;
    var verbr = document.getElementById('gemjaarverbr').value;
    var cost;
    var tsoc;
    var rest;

    if(document.getElementById('opwek').value >0){
        gelt = 40;
    }else{
        gelt = 0;
    }

    cost = opw * gelt / 100;
    tsoc = opw * (100-gelt) / 100;
    rest = verbr - cost;

    document.getElementById('kwhgelijkth').value = cost;
    document.getElementById('kwhongelijkth').value = tsoc;
    document.getElementById('kwhrestth').value = rest;
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
    var th = document.getElementById('totaalthuis').value;
    var vers;

    var vers = z*1 - m*1;
    var vers1 = z*1 - th*1;

    document.getElementById('verschil').value = vers.toFixed(2);
    document.getElementById('verschil1').value = vers1.toFixed(2);
     
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
    document.getElementById('defverb').innerHTML = '<i class="fa-solid fa-bolt"></i> 1500 kWh';
    document.getElementById('defgas').innerHTML = '<i class="fa-solid fa-fire-flame-simple"></i> 800 m3';
    document.getElementById('defopw').innerHTML = '<i class="fa-solid fa-solar-panel"></i> 1000 kWh';
}

function p2(){
    document.getElementById('gemjaarverbr').value = 2500;
    document.getElementById('opwek').value = 2000;
    document.getElementById('defverb').innerHTML = '<i class="fa-solid fa-bolt"></i> 2500 kWh';
    document.getElementById('defgas').innerHTML = '<i class="fa-solid fa-fire-flame-simple"></i> 1000 m3';
    document.getElementById('defopw').innerHTML = '<i class="fa-solid fa-solar-panel"></i> 2000 kWh';
}

function p3(){
    document.getElementById('gemjaarverbr').value = 3000;
    document.getElementById('opwek').value = 2500;
    document.getElementById('defverb').innerHTML = '<i class="fa-solid fa-bolt"></i> 3000 kWh';
    document.getElementById('defgas').innerHTML = '<i class="fa-solid fa-fire-flame-simple"></i> 1200 m3';
    document.getElementById('defopw').innerHTML = '<i class="fa-solid fa-solar-panel"></i> 2500 kWh';
}

function p4(){
    document.getElementById('gemjaarverbr').value = 3500;
    document.getElementById('opwek').value = 3000;
    document.getElementById('defverb').innerHTML = '<i class="fa-solid fa-bolt"></i> 3500 kWh';
    document.getElementById('defgas').innerHTML = '<i class="fa-solid fa-fire-flame-simple"></i> 1400 m3';
    document.getElementById('defopw').innerHTML = '<i class="fa-solid fa-solar-panel"></i> 3000 kWh';  
}

function updatepres(){
    if(document.getElementById('zonp').checked){
        console.log("zonP");
        if(document.getElementById('1').checked){
            document.getElementById('opwek').value = 1000;
            document.getElementById('defopw').innerHTML = '<i class="fa-solid fa-solar-panel"></i> 1000 kWh';
        }
        else if(document.getElementById('2').checked){
            document.getElementById('opwek').value = 2000;
            document.getElementById('defopw').innerHTML = '<i class="fa-solid fa-solar-panel"></i> 2000 kWh';
        }
        else if(document.getElementById('3').checked){
            document.getElementById('opwek').value = 2500;
            document.getElementById('defopw').innerHTML = '<i class="fa-solid fa-solar-panel"></i> 2500 kWh';
        }
        else if(document.getElementById('4').checked){
            document.getElementById('opwek').value = 3000;
            document.getElementById('defopw').innerHTML = '<i class="fa-solid fa-solar-panel"></i> 3000 kWh';
        }
    }else{
        if(document.getElementById('cust').checked){
            console.log("not my prob");
        }else{
        document.getElementById('opwek').value = 0;
        document.getElementById('defopw').innerHTML = '<i class="fa-solid fa-solar-panel"></i> 0 kWh';
        }
    }
}