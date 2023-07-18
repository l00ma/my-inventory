import { Html5QrcodeScanner } from "html5-qrcode";

const thisUrl = "/codebar?query="

function onScanSuccess(decodedText, decodedResult) {
    console.log(`Code scanned = ${decodedText}`, decodedResult);
    html5QrcodeScanner.clear();
    httpGetAsync(thisUrl + decodedText);
}

function httpGetAsync(theUrl) {


    fetch(theUrl, {
        method: "GET"
    })
        .then(function (response) {
            if (response.ok) {
                // Request was successful
                return response.text();
            } else {
                // Request failed
                throw new Error('La requête a échouée avec un statut ' + response.status);
            }
        })
        .then(function (datas) {
           
        })
}

const html5QrcodeScanner = new Html5QrcodeScanner("qr-reader", {
    fps: 10,
    qrbox: 600,
    aspectRatio: 2
});
html5QrcodeScanner.render(onScanSuccess);