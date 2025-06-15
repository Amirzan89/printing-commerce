const modalDelete = document.querySelector('#modalDelete');
const deleteForm = document.getElementById('deleteForm');
const inpID = document.getElementById('inpID');
let isAnimating = false;
deleteForm.addEventListener('click',function(event){
    event.stopPropagation();
});
function showModalDelete(id){
    inpID.value = id;
    modalDelete.style.display = 'block';
    animateModalDelete('20%');
}
function closeModalDelete(){
    animateModalDelete('-20%');
}
function animateModalDelete(finalTop) {
    let currentTop = parseInt(deleteForm.style.top) || 0;
    let increment = currentTop < parseInt(finalTop) ? 1 : -1;
    function frame() {
        currentTop += increment;
        deleteForm.style.top = currentTop + '%';
        if ((increment === 1 && currentTop >= parseInt(finalTop)) || (increment === -1 && currentTop <= parseInt(finalTop))) {
            clearInterval(animationInterval);
            if (finalTop === '20%') {
                isAnimating = false;
            } else {
                modalDelete.style.display = 'none';
            }
        }
    }
    let animationInterval = setInterval(frame, 5);
}
function showLoading() {
    document.querySelector("div#preloader").style.display = "block";
}
function closeLoading() {
    document.querySelector("div#preloader").style.display = "none";
}
deleteForm.onsubmit = function (event) {
    event.preventDefault();
    showLoading();
    var xhr = new XMLHttpRequest();
    var requestBody = {
        uuid: inpID.value.trim(),
    };
    if(reff == '/admin'){
        xhr.open("DELETE", "/admin/delete");
    }else if(reff == '/metode-pembayaran'){
        xhr.open("DELETE", "/metode-pembayaran/delete");
    }else{
        xhr.open("DELETE", reff + "/delete");
    }
    xhr.setRequestHeader("X-CSRF-TOKEN", csrfToken);
    xhr.setRequestHeader("Content-Type", "application/json");
    xhr.send(JSON.stringify(requestBody));
    xhr.onreadystatechange = function () {
        if (xhr.readyState == XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                closeLoading();
                var response = JSON.parse(xhr.responseText);
                showGreenPopup(response.message);
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                closeLoading();
                var response = JSON.parse(xhr.responseText);
                showRedPopup(response.message);
            }
        }
    };
    return false;
};