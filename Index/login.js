document.getElementById('show-register').addEventListener('click', function() {
    event.preventDefault();
    // Hide login form
    document.getElementById('login-form').style.display = 'none';
    // Show register form
    document.getElementById('register-form').style.display = 'block';
});

document.getElementById('show-login').addEventListener('click', function() {
    event.preventDefault();
    // Hide register form
    document.getElementById('register-form').style.display = 'none';
    // Show login form
    document.getElementById('login-form').style.display = 'block';
});
