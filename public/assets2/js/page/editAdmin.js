const editForm = document.getElementById("editForm");
const inpNama = document.getElementById("inpNama");
const inpJenisKelamin = document.getElementById("inpJenisKelamin");
const inpRole = document.getElementById("inpRole");
const inpNomerTelepon = document.getElementById("inpNomerTelepon");
const inpEmail = document.getElementById("inpEmail");
const iconPass = document.getElementById("iconPass");
const inpPassword = document.getElementById("inpPassword");
const inpFoto = document.getElementById("inpFoto");
const allowedFormats = ["image/jpeg", "image/png"];
let uploadeFile = null;
var isPasswordShow = false;
function showLoading() {
    document.querySelector("div#preloader").style.display = "block";
}
function closeLoading() {
    document.querySelector("div#preloader").style.display = "none";
}
function showEyePass(){
    if(inpPassword.value == '' || inpPassword.value == null){
        iconPass.style.display = 'none';
    }else{
        iconPass.style.display = 'block';
    }
}
function showPass(){
    if(isPasswordShow){
        inpPassword.type = 'password';
        document.getElementById('passClose').style.display = 'block';
        document.getElementById('passShow').style.display = 'none';
        isPasswordShow = false;
    }else{
        inpPassword.type = 'text';
        document.getElementById('passClose').style.display = 'none';
        document.getElementById('passShow').style.display = 'block';
        isPasswordShow = true;
    }
}
function handleFileClick() {
    inpFoto.click();
}
function handleFileChange(event) {
    const file = event.target.files[0];
    if (file) {
        if (!allowedFormats.includes(file.type)) {
            showRedPopup("Format Foto harus png, jpeg, jpg !");
            return;
        }
        uploadeFile = file;
        const fileReader = new FileReader();
        fileReader.onload = function() {
            document.getElementById('file').src = fileReader.result;
            document.getElementById('file').style.display = 'block';
            document.querySelector('div.img').style.border = 'none';
        };
        fileReader.readAsDataURL(uploadeFile);
    }
}
function handleDragOver(event) {
    event.preventDefault();
}
function handleDrop(event) {
    event.preventDefault();
    const file = event.dataTransfer.files[0];
    if (file) {
        if (!allowedFormats.includes(file.type)) {
            showRedPopup("Format Foto harus png, jpeg, jpg !");
            return;
        }
        uploadeFile = file;
        const reader = new FileReader();
        reader.onload = function(event) {
            document.getElementById('file').src = event.target.result;
        };
        reader.readAsDataURL(file);
        fileImg = file;
    }
}
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}
editForm.onsubmit = function(event){
    event.preventDefault();
    const nama = inpNama.value.trim();
    const nomer = inpNomerTelepon.value.trim();
    const inp_jenis_kelamin = inpJenisKelamin.value.trim();
    const inp_role = inpRole.value.trim();
    const inpEmails = inpEmail.value.trim();
    const password = inpPassword.value.trim();
    if (nama === users.nama_lengkap && nomer === users.no_telpon && inp_jenis_kelamin === users.jenis_kelamin && inp_role === users.role && inpEmails === users.email && password === '' && uploadeFile === null) {
        showRedPopup('Data belum diubah');
        return;
    }
    if(nama === "") {  
        showRedPopup("Nama Lengkap harus diisi !");
        return;
    }
    if(inp_jenis_kelamin === "") {
        showRedPopup("Jenis Kelamin harus diisi !");
        return;
    }
    if(nomer === "") {
        showRedPopup("Nomer Telepon harus diisi !");
        return;
    }else if(isNaN(nomer)) {
        showRedPopup("Nomer Telepon harus angka !");
        return;
    }else if(!/^08\d+$/.test(nomer)) {
        showRedPopup("Nomer Telepon harus dimulai dengan 08 !");
        return;
    }else if(!/^\d{11,13}$/.test(nomer)) {
        showRedPopup("Nomer Telepon harus terdiri dari 11-13 digit angka !");
        return;
    }
    if(inp_role === "") {
        showRedPopup("Role Admin harus diisi !");
        return;
    }
    if(inpEmails === "") {
        showRedPopup("Email harus diisi !");
        return;
    }
    if(!isValidEmail(inpEmails)) {
        showRedPopup('Format Email salah !');
        return;
    }
    if (password !== '') {
        if (password.length < 8) {
            showRedPopup('Password minimal 8 karakter !');
            return;
        }
        if (!/[A-Z]/.test(password)) {
            showRedPopup('Password minimal ada 1 huruf kapital !');
            return;
        }
        if (!/[a-z]/.test(password)) {
            showRedPopup('Password minimal ada 1 huruf kecil !');
            return;
        }
        if (!/\d/.test(password)) {
            showRedPopup('Password minimal ada 1 angka !');
            return;
        }
        if (!/[!@#$%^&*]/.test(password)) {
            showRedPopup('Password minimal ada 1 karakter unik !');
            return;
        }
    }
    if (uploadeFile) {
        if (!allowedFormats.includes(uploadeFile.type)) {
            showRedPopup("Format Foto harus png, jpeg, jpg !");
            return;
        }
    }
    showLoading();
    const formData = new FormData();
    formData.append("_method", 'PUT');
    formData.append("nama_lengkap", nama);
    formData.append("jenis_kelamin", inp_jenis_kelamin);
    formData.append("no_telpon", nomer);
    formData.append("role", inp_role);
    formData.append("email_admin_lama", users.email);
    formData.append("email_admin", inpEmails);
    if (password !== '') {
        formData.append("password", password);
    }
    if (uploadeFile) {
        formData.append("foto", uploadeFile);
    }
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "/admin/update");
    xhr.setRequestHeader("X-CSRF-TOKEN", csrfToken);
    xhr.onload = function () {
        if (xhr.status === 200) {
            closeLoading();
            var response = JSON.parse(xhr.responseText);
            showGreenPopup(response);
            setTimeout(() => {
                window.location.href = '/admin';
            }, 2000);
        } else {
            closeLoading();
            var response = JSON.parse(xhr.responseText);
            showRedPopup(response);
        }
    };
    xhr.onerror = function () {
        closeLoading();
        showRedPopup("Error occurred during the request.");
    };
    xhr.send(formData);
    return false;
};