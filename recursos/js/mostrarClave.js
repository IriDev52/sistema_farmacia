

document.addEventListener('DOMContentLoaded', function() {
let checkboxbutton= document.getElementById("show");
let inputPass=document.getElementById("pass");


checkboxbutton.addEventListener("click",()=>{
	showpassword();

});

function showpassword() {
	if (inputPass.type === "password") {
		inputPass.type = "text";
	} else {
		inputPass.type = "password";
	}
}
});