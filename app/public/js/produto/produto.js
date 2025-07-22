document.addEventListener("DOMContentLoaded", function () {
    // const botaoAbrir = document.querySelector("#abrir-modal-cadastro-produto");

    // const modal = new bootstrap.Modal(document.getElementById("modal-cadastro-produto"));

    // botaoAbrir.addEventListener("click", function () {
    //     modal.show();
    // });

    const areaItens = document.getElementById("area-itens-dinamicos");
    const btnAdicionar = document.querySelector(".adicionar-mais-itens");

    btnAdicionar.addEventListener("click", function (e) {
        e.preventDefault();

        const novoItem = document.createElement("div");
        novoItem.classList.add("row", "g-3", "mt-2", "mb-2");

        novoItem.innerHTML = `
            <div class="col-md-2">
                <label class="form-label">Referencia</label>
                <input type="text" class="form-control" name="referencia[]">
            </div>
            <div class="col-md-2">
                <label class="form-label">Preço</label>
                <input type="number" class="form-control" name="preco[]" step="0.01">
            </div>
            <div class="col-md-2">
                <label class="form-label">Estoque</label>
                <input type="number" class="form-control" name="estoque[]">
            </div>
            <div class="col-md-2">
                <label class="form-label">Tamanho</label>
                <select class="form-select" name="tamanho[]">
                    <option value="P">P</option>
                    <option value="M">M</option>
                    <option value="G">G</option>
                    <option value="GG">GG</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Cor</label>
                <select class="form-select" name="cor[]">
                    <option value="Azul">Azul</option>
                    <option value="Vermelho">Vermelho</option>
                    <option value="Branco">Branco</option>
                    <option value="Preto">Preto</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-danger remover-item" style="margin-top: 32px;">Remover</button>
            </div>
        `;

        areaItens.appendChild(novoItem);
    });

    // Remover bloco individual
    document.querySelector(".formulario-cadastro-produto").addEventListener("click", function (e) {
        if (e.target.classList.contains("remover-item")) {
            e.preventDefault();
            e.target.closest(".row").remove();
        }
    });

    const cards = document.querySelectorAll(".card");

    cards.forEach(function (card) {
        const botaoExcluir = card.querySelector(".excluir-produto");

        botaoExcluir.addEventListener("click", function () {
            const id = this.closest(".card").getAttribute("data-id");

            if (!confirm("Tem certeza que deseja excluir este produto?")) return;

            fetch(`/produtos/excluir/${id}`, { method: "DELETE" })
                .then(response => response.json())
                .then(data => {
                    if (data.sucesso) {
                        card.remove(); // remove o card da tela
                    } else {
                        alert(data.mensagem || "Erro ao excluir o produto.");
                    }
                })
                .catch(err => console.error("Falha:", err));
        });
    });

    const botaoSalvarProdutosModal = document.querySelector(".salvar-produtos");
    if (botaoSalvarProdutosModal) {
        botaoSalvarProdutosModal.addEventListener("click", function () {
            const formulario = this.closest(".modal-content").querySelector(".modal-body > form");
            if (formulario) {
                formulario.submit();
            }
        })
    }

    const modalElement = document.getElementById("modal-cadastro-produto");
    const modal = new bootstrap.Modal(modalElement);
    const form = modalElement.querySelector("form.formulario-cadastro-produto");
    const nomeProduto = document.getElementById("nomeProduto");

    document.querySelectorAll(".editar-produto").forEach(function (botaoEditar) {
        botaoEditar.addEventListener("click", function () {
            const card = this.closest(".card");
            const produtoId = card.getAttribute("data-id");
            const nome = card.querySelector("h2").textContent.trim();
            const paragrafos = card.querySelectorAll("p");

            // Abrir modal
            modal.show();

            // Resetar campos
            form.reset();
            nomeProduto.value = nome;
            areaItens.innerHTML = "";

            // Preencher itens (assume blocos de 5 <p> cada)
            for (let i = 0; i < paragrafos.length; i += 5) {
                const referencia = paragrafos[i]?.textContent.replace("Referencia:", "").trim();
                const cor = paragrafos[i + 1]?.textContent.replace("Cor:", "").trim();
                const tamanho = paragrafos[i + 2]?.textContent.replace("Tamanho:", "").trim();
                const estoque = paragrafos[i + 3]?.textContent.replace("Estoque:", "").trim();
                const preco = paragrafos[i + 4]?.textContent.replace("Preco:", "").trim();

                const itemHTML = `
                    <div class="row g-3 mt-2 mb-2">
                        <div class="col-md-2">
                        <label class="form-label">Referencia</label>
                        <input type="text" class="form-control" name="referencia[]" value="${referencia}">
                        </div>
                        <div class="col-md-2">
                        <label class="form-label">Preço</label>
                        <input type="number" step="0.01" class="form-control" name="preco[]" value="${preco}">
                        </div>
                        <div class="col-md-2">
                        <label class="form-label">Estoque</label>
                        <input type="number" class="form-control" name="estoque[]" value="${estoque}">
                        </div>
                        <div class="col-md-2">
                        <label class="form-label">Tamanho</label>
                        <select class="form-select" name="tamanho[]">
                            ${["P", "M", "G", "GG"].map(t => `<option value="${t}" ${t === tamanho ? "selected" : ""}>${t}</option>`).join("")}
                        </select>
                        </div>
                        <div class="col-md-2">
                        <label class="form-label">Cor</label>
                        <select class="form-select" name="cor[]">
                            ${["Azul", "Vermelho", "Branco", "Preto"].map(c => `<option value="${c}" ${c === cor ? "selected" : ""}>${c}</option>`).join("")}
                        </select>
                        </div>
                        <div class="col-md-2">
                        <button class="btn btn-danger remover-item" style="margin-top: 32px;">Remover</button>
                        </div>
                    </div>
                `;

                areaItens.insertAdjacentHTML("beforeend", itemHTML);
            }

            // Altera rota do formulário para edição
            form.action = `/produtos/editar/${produtoId}`;
            form.method = "POST";
        });
    });

    // Remoção de item dinamicamente
    form.addEventListener("click", function (event) {
        if (event.target.classList.contains("remover-item")) {
            event.preventDefault();
            event.target.closest(".row").remove();
        }
    });

    const btnCadastrar = document.querySelector('[data-bs-target="#modal-cadastro-produto"]');

    if (btnCadastrar) {
        btnCadastrar.addEventListener("click", function () {
            form.reset();                // Limpa os campos fixos (ex: nomeProduto)
            areaItens.innerHTML = "";   // Remove itens antigos
            form.action = "/produtos/salvar";
            form.method = "POST";
        });
    }
});
