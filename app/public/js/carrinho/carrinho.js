document.addEventListener("DOMContentLoaded", function () {
    const modalFinalizar = new bootstrap.Modal(document.getElementById("modal-finalizar-compra"));
    const formFinalizar = document.querySelector(".form-finalizar");
    const btnFinalizar = document.querySelector(".btn-finalizar-carrinho");
    const selectCupom = document.querySelector(".cupom-select");
    const descontoCupomDiv = document.getElementById("desconto-cupom");

    if (btnFinalizar) {
        btnFinalizar.addEventListener("click", function () {
            modalFinalizar.show();
        });
    }

    btnFinalizar.addEventListener("click", () => modalFinalizar.show());

    formFinalizar.addEventListener("submit", function (e) {
        e.preventDefault();

        const botaoFinalizar = this.querySelector(".finalizar-pedido");
        botaoFinalizar.disabled = true;

        const cep = formFinalizar.querySelector('input[name="cep"]').value.trim();
        const email = formFinalizar.querySelector('input[name="email"]').value.trim();
        const cupomSelecionado = selectCupom?.value;

        if (!cep.match(/^\d{5}-?\d{3}$/)) {
            alert("CEP inválido.");
            botaoFinalizar.disabled = false;
            return;
        }

        fetch(`https://viacep.com.br/ws/${cep}/json`)
            .then(res => res.json())
            .then(endereco => {
                if (endereco.erro) {
                    alert("CEP não encontrado.");
                    botaoFinalizar.disabled = false;
                    return;
                }

                fetch("/carrinho/finalizar", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ cep, email, cupom: cupomSelecionado })
                })
                    .then(res => res.json())
                    .then(res => {
                        if (res.sucesso) {
                            window.location.href = "/carrinho/retorno";
                        } else {
                            alert(res.mensagem || "Erro ao finalizar.");
                            botaoFinalizar.disabled = false;
                        }
                    });
            })
            .catch(() => {
                alert("Erro ao consultar CEP.");
                botaoFinalizar.disabled = false;
            });
    });

    if (selectCupom) {
        selectCupom.addEventListener("change", function () {
            const selectedOption = this.options[this.selectedIndex];
            const desconto = selectedOption.getAttribute("data-desconto");

            if (desconto) {
                descontoCupomDiv.innerHTML = `<strong>Desconto aplicado:</strong> R$ ${parseFloat(desconto).toFixed(2).replace(".", ",")}`;
            } else {
                descontoCupomDiv.innerHTML = "";
            }
        });
    }
});
