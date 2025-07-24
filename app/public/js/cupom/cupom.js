document.addEventListener("DOMContentLoaded", function () {
    const modalElement = document.getElementById("modal-cadastro-cupom");
    const modal = new bootstrap.Modal(modalElement);
    const form = modalElement.querySelector(".formulario-cadastro-cupom");
    const referenciaCupom = document.getElementById("referencia");
    const validadeCupom = document.getElementById("validade");
    const valorMinimoCupom = document.getElementById("valorMinimo");

    inicializarBotaoCadastrarNovoCupom();
    inicializarBotaoSalvar();
    inicializarExclusaoDeCupom();
    inicializarEdicaoDeCupom();

    function inicializarBotaoCadastrarNovoCupom() {
        const btnCadastrar = document.querySelector('[data-bs-target="#modal-cadastro-cupom"]');
        if (!btnCadastrar) return;

        btnCadastrar.addEventListener("click", function () {
            form.reset();
            form.action = "/cupons/salvar";
            form.method = "POST";
        });
    }

    function inicializarBotaoSalvar() {
        const botaoSalvar = document.querySelector(".salvar-cupom");
        if (!botaoSalvar) return;

        botaoSalvar.addEventListener("click", function () {
            const formulario = this.closest(".modal-content").querySelector(".modal-body > form");
            if (formulario) formulario.submit();
        });
    }

    function inicializarExclusaoDeCupom() {
        document.querySelectorAll(".card").forEach(function (card) {
            const botaoExcluir = card.querySelector(".excluir-cupom");
            if (!botaoExcluir) return;

            botaoExcluir.addEventListener("click", function () {
                const id = this.closest(".card").getAttribute("data-id");
                if (!confirm("Tem certeza que deseja excluir este cupom?")) return;

                fetch(`/cupons/excluir/${id}`, { method: "DELETE" })
                    .then(res => res.json())
                    .then(data => {
                        if (data.sucesso) {
                            card.remove();
                        } else {
                            alert(data.mensagem || "Erro ao excluir cupom.");
                        }
                    })
                    .catch(err => console.error("Falha:", err));
            });
        });
    }

    function inicializarEdicaoDeCupom() {
        document.querySelectorAll(".editar-cupom").forEach(function (botaoEditar) {
            botaoEditar.addEventListener("click", function () {
                const card = this.closest(".card");
                const cupomdId = card.getAttribute("data-id");

                const referencia = card.querySelector(".referencia-cupom").textContent.trim();
                const validade = card.querySelector(".validade-cupom").textContent.trim();
                const valorMinimo = card.querySelector(".valorMinimo-cupom").textContent.trim().replace(',', '.');

                modal.show();
                form.reset();
                referenciaCupom.value = referencia;
                validadeCupom.value = validade;
                valorMinimoCupom.value = valorMinimo;

                form.action = `/cupons/editar/${cupomdId}`;
                form.method = "POST";
            });
        });
    }
});
