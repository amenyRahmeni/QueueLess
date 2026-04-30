document.addEventListener('DOMContentLoaded', function () {
    var confirmPasswordInput = document.querySelector('#confirm-password');
    var registerPasswordInput = document.querySelector('#register-password');

    if (confirmPasswordInput && registerPasswordInput) {
        var validatePasswordMatch = function () {
            if (confirmPasswordInput.value === '') {
                confirmPasswordInput.setCustomValidity('');
                return;
            }

            if (confirmPasswordInput.value !== registerPasswordInput.value) {
                confirmPasswordInput.setCustomValidity('Les mots de passe ne correspondent pas.');
                return;
            }

            confirmPasswordInput.setCustomValidity('');
        };

        registerPasswordInput.addEventListener('input', validatePasswordMatch);
        confirmPasswordInput.addEventListener('input', validatePasswordMatch);
    }
});
