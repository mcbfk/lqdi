document.addEventListener('DOMContentLoaded', function() {
    var input = document.getElementById('emailCard2');
    var label = document.querySelector('.card2 .input-wrapper .labelCard2');

    function checkInput() {
        if(input.value !== '') {
            label.style.opacity = 1;
        } else {
            label.style.opacity = 0;
        }
    }

    input.addEventListener('input', checkInput);
});

window.onscroll = function() {
    var header = document.querySelector('.header');
    var textToHide = document.querySelector('.header p'); // Supondo que o texto esteja em um elemento <p>

    if (window.scrollY > 0) {
        header.classList.add('header-scrolled');
        textToHide.classList.add('hide-on-scroll');
    } else {
        header.classList.remove('header-scrolled');
        textToHide.classList.remove('hide-on-scroll');
    }
};

function updateText() {
    var fileInput = document.getElementById('file');
    var fileChosen = document.getElementById('file-chosen');
    fileChosen.textContent = fileInput.files.length > 0 
                             ? fileInput.files[0].name 
                             : 'Nenhum arquivo escolhido';
}

document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('.form .validatable input, .form .validatable textarea');

    inputs.forEach(input => {
        input.addEventListener('input', function() {
            const checkmark = this.nextElementSibling;
            if (this.validity.valid) { // Verifica se o campo é válido
                checkmark.style.display = 'block'; // Mostra a setinha
            } else {
                checkmark.style.display = 'none'; // Esconde a setinha
            }
        });
    });
});

document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('form-inscricao');
    var messageDiv = document.getElementById('message');
    console.log('Passou aqui 1')
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        var formData = new FormData(this);

        fetch('../api.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (response.status === 409) {
                return response.json(); // Email já cadastrado
            } else if (response.ok) {
                return response.json(); // Sucesso
            } else {
                throw new Error('Network response was not ok: ' + response.statusText);
            }
        })
        .then(data => {
            console.log('Passou aqui 2')
            if (data.error) {
                // Use a mensagem de erro do servidor
                console.log('Passou aqui erro')
                messageDiv.textContent = data.error;
                messageDiv.className = 'message error';
            } else {
                // Outras mensagens de sucesso
                console.log('Passou aqui sucesso')
                messageDiv.textContent = data.message || 'Inscrição feita com sucesso!';
                messageDiv.className = 'message success';
            }
            messageDiv.style.display = 'block';

            setTimeout(function(){
                console.log("Some msg");
                messageDiv.style.display = 'none';
            }, 3500);
        })
        .catch(error => {
            messageDiv.textContent = error.message;
            messageDiv.className = 'message error';
            messageDiv.style.display = 'block';
        });
    });
});
