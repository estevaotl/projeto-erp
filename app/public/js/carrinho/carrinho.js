document.addEventListener("DOMContentLoaded", function () {
    const modalFinalizar = new bootstrap.Modal(document.getElementById("modal-finalizar-compra"));
    const formFinalizar = document.querySelector(".form-finalizar");
    const btnFinalizar = document.querySelector(".btn-finalizar-carrinho");
    const resultadoCep = document.getElementById("cep-resultado");

    if (btnFinalizar) {
        btnFinalizar.addEventListener("click", function () {
            modalFinalizar.show();
        });
    }

    btnFinalizar.addEventListener("click", () => modalFinalizar.show());

    formFinalizar.addEventListener("submit", function (e) {
        e.preventDefault();

        const cep = formFinalizar.querySelector('input[name="cep"]').value.trim();
        const email = formFinalizar.querySelector('input[name="email"]').value.trim();

        if (!cep.match(/^\d{5}-?\d{3}$/)) {
            resultadoCep.textContent = "CEP inválido.";
            return;
        }

        fetch(`https://viacep.com.br/ws/${cep}/json`)
            .then(res => res.json())
            .then(endereco => {
                if (endereco.erro) {
                    resultadoCep.textContent = "CEP não encontrado.";
                    return;
                }

                fetch("/carrinho/finalizar", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ cep, email })
                })
                    .then(res => res.json())
                    .then(res => {
                        if (res.sucesso) {
                            window.location.href = "/carrinho/retorno";
                        } else {
                            alert(res.mensagem || "Erro ao finalizar.");
                        }
                    });
            })
            .catch(() => resultadoCep.textContent = "Erro ao consultar CEP.");
    });
});
