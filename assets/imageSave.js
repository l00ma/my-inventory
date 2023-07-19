const aSubmit = document.getElementById('product_save');
const aBrowse = document.getElementById('product_image');
const aTrigger = document.getElementById('imageHeader');

aTrigger.addEventListener('click', function(){
    aBrowse.click();
})

aBrowse.addEventListener('change', function(){
    aSubmit.click();
})
