document.addEventListener('DOMContentLoaded', function() {
    let checkboxbutton = document.getElementById("show");
    let inputPass = document.getElementById("pass");

    // Solo adjuntar el evento si los elementos existen en la página actual
    if (checkboxbutton && inputPass) {
        checkboxbutton.addEventListener("click", () => {
            showpassword();
        });
        // Asegúrate de que showpassword() también esté definido en este script o accesible.
        function showpassword() {
            if (inputPass.type === "password") {
                inputPass.type = "text";
            } else {
                inputPass.type = "password";
            }
        }
    }
});