document.getElementById("myForm").addEventListener("submit", function(event) {

    event.preventDefault();

    let name = document.getElementById("name").value.trim();
    let password = document.getElementById("pass").value;
    let gmail = document.getElementById("gmail").value.trim();
    let mobile = document.getElementById("mob").value.trim();

    let gender = document.querySelector('input[name="gender"]:checked');
    let course = document.querySelector('input[name="course"]:checked');

   
    if (name === "") {
        alert("Please Enter Your Name");
        return;
    }

    if (!/^[A-Za-z ]+$/.test(name)) {
        alert("Name Should Contain Only Letters");
        return;
    }

   
    if (password.length < 8) {
        alert("Password Must Be At Least 8 Characters");
        return;
    }

    
    if (!/^[a-zA-Z0-9._%+-]+@gmail\.com$/.test(gmail)) {
        alert("Please Enter a Valid Gmail Address");
        return;
    }

    
    if (!/^[0-9]{10}$/.test(mobile)) {
        alert("Mobile Number Must Contain Exactly 10 Digits");
        return;
    }

    if (!gender) {
        alert("Please Select Gender");
        return;
    }

    if (!course) {
        alert("Please Select a Course");
        return;
    }

    alert("Form Submitted Successfully!");

    this.submit();
});