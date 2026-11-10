<?php $this->layout('header', ['title' => $title, 'type' => 'Form']) ?>

<main style="padding: 1rem;">
    <?php if (!empty($successMessage)): ?>
        <p style="color: green; font-weight: bold;"><?= htmlspecialchars($successMessage) ?></p>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <ul style="color: red;">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form action="" method="post" style="max-width: 600px; margin: auto;">
        <fieldset style="border: 1px solid #ccc; padding: 1rem; border-radius: 5px;">
            <legend>Benutzerformular</legend>

            <label for="name">Name:</label><br>
            <input type="text" id="name" name="name" required value="<?= htmlspecialchars($formData['name'] ?? '') ?>" style="width: 100%; padding: 0.5rem; margin-bottom: 1rem;"><br>

            <label for="email">E-Mail:</label><br>
            <input type="email" id="email" name="email" required value="<?= htmlspecialchars($formData['email'] ?? '') ?>" style="width: 100%; padding: 0.5rem; margin-bottom: 1rem;"><br>

            <label for="message">Nachricht:</label><br>
            <textarea id="message" name="message" rows="5" style="width: 100%; padding: 0.5rem; margin-bottom: 1rem;"><?= htmlspecialchars($formData['message'] ?? '') ?></textarea><br>

            <button type="submit" style="background-color: #007acc; color: white; padding: 0.5rem 1rem; border: none; border-radius: 3px; cursor: pointer;">
                Absenden
            </button>
        </fieldset>
    </form>
</main>

<?php if ($type === 'Form' || $type === ''): ?>
    </fieldset>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const nameInput = form.querySelector('#name');
    const emailInput = form.querySelector('#email');
    const messageInput = form.querySelector('#message');

    function showError(input, message) {
        let errorElem = input.nextElementSibling;
        if (!errorElem || !errorElem.classList.contains('error-message')) {
            errorElem = document.createElement('div');
            errorElem.className = 'error-message';
            errorElem.style.color = 'red';
            errorElem.style.fontSize = '0.9em';
            input.parentNode.insertBefore(errorElem, input.nextSibling);
        }
        errorElem.textContent = message;
        input.classList.add('input-error');
    }

    function clearError(input) {
        let errorElem = input.nextElementSibling;
        if (errorElem && errorElem.classList.contains('error-message')) {
            errorElem.textContent = '';
        }
        input.classList.remove('input-error');
    }

    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    function validateName() {
        const value = nameInput.value.trim();
        if (value === '') {
            showError(nameInput, 'Name ist erforderlich.');
            return false;
        } else {
            clearError(nameInput);
            return true;
        }
    }

    function validateEmailField() {
        const value = emailInput.value.trim();
        if (value === '') {
            showError(emailInput, 'E-Mail ist erforderlich.');
            return false;
        } else if (!validateEmail(value)) {
            showError(emailInput, 'Ungültige E-Mail-Adresse.');
            return false;
        } else {
            clearError(emailInput);
            return true;
        }
    }

    function validateMessage() {
        const value = messageInput.value;
        if (value.length > 500) {
            showError(messageInput, 'Die Nachricht darf maximal 500 Zeichen lang sein.');
            return false;
        } else {
            clearError(messageInput);
            return true;
        }
    }

    nameInput.addEventListener('input', validateName);
    emailInput.addEventListener('input', validateEmailField);
    messageInput.addEventListener('input', validateMessage);

    form.addEventListener('submit', function(event) {
        const validName = validateName();
        const validEmail = validateEmailField();
        const validMessage = validateMessage();

        if (!(validName && validEmail && validMessage)) {
            event.preventDefault();
        }
    });
});
</script>

<style>
.input-error {
    border-color: red;
    background-color: #ffe6e6;
}
.error-message {
    margin-top: 0.25rem;
}
</style>

</body>
</html>
