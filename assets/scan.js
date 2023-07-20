import { Html5QrcodeScanner } from "html5-qrcode";

const thisUrl = "/codebar?query="

function onScanSuccess(decodedText, decodedResult) {
    html5QrcodeScanner.clear();
    httpGetAsync(thisUrl + decodedText);
}

function httpGetAsync(theUrl) {
    fetch(theUrl, {
        method: "GET"
    })
        .then(function (response) {
            if (response.ok) {
                // Si la requete s'est bien passée
                return response.json();
            } else {
                // Si erreur
                throw new Error('La requête a échouée avec un statut ' + response.status);
            }
        })
        .then(jsonData => {
            const data = JSON.parse(jsonData);
            const results = document.getElementById('results');

            const productCard = document.createElement('div');

            const productPhoto = document.createElement('img');
            productPhoto.setAttribute('src', data.product.image_front_url);
            productPhoto.classList.add('mx-auto');

            const productName = document.createElement('h3');
            productName.textContent = 'Produit: ' + data.product.product_name;
            productName.classList.add('text-center');

            const productBarcode = document.createElement('p');
            productBarcode.textContent = 'Codebar n°: '+ data.code;
            productBarcode.classList.add('text-center');

            const productBrand = document.createElement('p');
            productBrand.textContent = 'Marque: ' + data.product.brands;
            productBrand.classList.add('text-center');

            productCard.appendChild(productPhoto);
            productCard.appendChild(productBarcode);
            productCard.appendChild(productName);
            productCard.appendChild(productBrand);

            results.appendChild(productCard);
        })
}

const html5QrcodeScanner = new Html5QrcodeScanner("qr-reader", {
    fps: 15,
    qrbox: 500,
    aspectRatio: 2
});
html5QrcodeScanner.render(onScanSuccess);