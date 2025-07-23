document.addEventListener("DOMContentLoaded", function () {
    const modalElement = document.getElementById("modal-cadastro-produto");
    const modal = new bootstrap.Modal(modalElement);
    const form = modalElement.querySelector(".formulario-cadastro-produto");
    const areaItens = document.getElementById("area-itens-dinamicos");
    const nomeProduto = document.getElementById("nomeProduto");

    inicializarBotaoAdicionar();
    inicializarBotaoCadastrarNovoProduto();
    inicializarBotaoSalvar();
    inicializarRemocaoDeItens();
    inicializarExclusaoDeProduto();
    inicializarEdicaoDeProduto();

    function criarItemHTML(dados = {}) {
        return `
            <div class="row g-3 mt-2 mb-2">
                <div class="col-md-2">
                    <label class="form-label">Referencia</label>
                    <input type="text" class="form-control" name="referencia[]" value="${dados.referencia || ''}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Preço</label>
                    <input type="number" step="0.01" class="form-control" name="preco[]" value="${dados.preco || ''}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Estoque</label>
                    <input type="number" class="form-control" name="estoque[]" value="${dados.estoque || ''}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tamanho</label>
                    <select class="form-select" name="tamanho[]">
                        ${["P", "M", "G", "GG"].map(t => `<option value="${t}" ${t === dados.tamanho ? "selected" : ""}>${t}</option>`).join("")}
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Cor</label>
                    <select class="form-select" name="cor[]">
                        ${["Azul", "Vermelho", "Branco", "Preto"].map(c => `<option value="${c}" ${c === dados.cor ? "selected" : ""}>${c}</option>`).join("")}
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-danger remover-item" style="margin-top: 32px;">Remover</button>
                </div>
            </div>
        `;
    }

    function inicializarBotaoAdicionar() {
        const btnAdicionar = document.querySelector(".adicionar-mais-itens");
        if (!btnAdicionar) return;

        btnAdicionar.addEventListener("click", function (e) {
            e.preventDefault();
            areaItens.insertAdjacentHTML("beforeend", criarItemHTML());
        });
    }

    function inicializarRemocaoDeItens() {
        form.addEventListener("click", function (event) {
            if (event.target.classList.contains("remover-item")) {
                event.preventDefault();
                event.target.closest(".row").remove();
            }
        });
    }

    function inicializarBotaoCadastrarNovoProduto() {
        const btnCadastrar = document.querySelector('[data-bs-target="#modal-cadastro-produto"]');
        if (!btnCadastrar) return;

        btnCadastrar.addEventListener("click", function () {
            form.reset();
            areaItens.innerHTML = "";
            form.action = "/produtos/salvar";
            form.method = "POST";
        });
    }

    function inicializarBotaoSalvar() {
        const botaoSalvar = document.querySelector(".salvar-produtos");
        if (!botaoSalvar) return;

        botaoSalvar.addEventListener("click", function () {
            const formulario = this.closest(".modal-content").querySelector(".modal-body > form");
            if (formulario) formulario.submit();
        });
    }

    function inicializarExclusaoDeProduto() {
        document.querySelectorAll(".card").forEach(function (card) {
            const botaoExcluir = card.querySelector(".excluir-produto");
            if (!botaoExcluir) return;

            botaoExcluir.addEventListener("click", function () {
                const id = this.closest(".card-header").getAttribute("data-id");
                if (!confirm("Tem certeza que deseja excluir este produto?")) return;

                fetch(`/produtos/excluir/${id}`, { method: "DELETE" })
                    .then(res => res.json())
                    .then(data => {
                        if (data.sucesso) {
                            card.remove();
                        } else {
                            alert(data.mensagem || "Erro ao excluir produto.");
                        }
                    })
                    .catch(err => console.error("Falha:", err));
            });
        });
    }

    function inicializarEdicaoDeProduto() {
        document.querySelectorAll(".editar-produto").forEach(function (botaoEditar) {
            botaoEditar.addEventListener("click", function () {
                const card = this.closest(".card");
                const header = card.querySelector(".card-header");
                const produtoId = header.getAttribute("data-id");
                const nome = card.querySelector(".nome-produto").textContent.trim();

                modal.show();
                form.reset();
                nomeProduto.value = nome;
                areaItens.innerHTML = "";

                const linhas = card.querySelectorAll("tbody tr");
                const idItems = card.querySelectorAll(".idItem");

                linhas.forEach((linha, index) => {
                    const descricaoTexto = linha.querySelector(".descricao-item")?.textContent.trim();
                    const estoque = linha.querySelector(".estoque-item")?.textContent.trim();
                    const preco = linha.querySelector(".preco-item")?.textContent.trim().replace(',', '.');

                    const idItem = idItems[index]?.value;

                    if (descricaoTexto) {
                        const partes = descricaoTexto.split(" - ");
                        const referencia = partes[0] || "";
                        const tamanho = partes[1] || "";
                        const cor = partes[2] || "";

                        const item = {
                            idItem,
                            referencia,
                            tamanho,
                            cor,
                            estoque,
                            preco
                        };

                        areaItens.insertAdjacentHTML("beforeend", criarItemHTML(item));
                    }
                });

                form.action = `/produtos/editar/${produtoId}`;
                form.method = "POST";
            });
        });
    }


    const modalCarrinho = new bootstrap.Modal(document.getElementById("modal-carrinho"));

    document.querySelectorAll(".comprar-produto").forEach(function (botao) {
        botao.addEventListener("click", function () {
            const card = this.closest(".card");
            const paragrafos = card.querySelectorAll("p");
            const itensContainer = document.getElementById("itens-disponiveis-carrinho");

            // Depois, dentro do loop que percorre os blocos de <p> (assumindo cada item ocupa 5 <p> consecutivos), faça:
            // Isso pega o <input class="idItem"> correspondente ao bloco atual de informações (referência, cor, etc).
            const idItems = card.querySelectorAll("input.idItem");

            itensContainer.innerHTML = ""; // Limpa modal anterior

            for (let i = 0; i < paragrafos.length; i += 5) {
                const referencia = paragrafos[i]?.textContent.replace("Referencia:", "").trim();
                const cor = paragrafos[i + 1]?.textContent.replace("Cor:", "").trim();
                const tamanho = paragrafos[i + 2]?.textContent.replace("Tamanho:", "").trim();
                const estoque = paragrafos[i + 3]?.textContent.replace("Estoque:", "").trim();
                const preco = paragrafos[i + 4]?.textContent.replace("Preco:", "").trim();

                const idItem = idItems[Math.floor(i / 5)]?.value;

                const itemHTML = `
                    <div class="card mb-3">
                        <div class="card-body">
                        <p><strong>Referência:</strong> ${referencia}</p>
                        <p><strong>Tamanho:</strong> ${tamanho}</p>
                        <p><strong>Cor:</strong> ${cor}</p>
                        <p><strong>Estoque:</strong> ${estoque}</p>
                        <p><strong>Preço:</strong> R$ ${preco}</p>
                        <div class="input-group mt-3">
                            <input type="number" class="form-control quantidade-input" value="1" min="1">
                            <button class="btn btn-success btn-adicionar-item"
                                    data-idItem="${idItem}"
                                    data-referencia="${referencia}"
                                    data-cor="${cor}"
                                    data-tamanho="${tamanho}"
                                    data-preco="${preco}">
                            Comprar
                            </button>
                        </div>
                        </div>
                    </div>
                `;

                itensContainer.insertAdjacentHTML("beforeend", itemHTML);
            }

            modalCarrinho.show();
        });
    });

    // 🛒 Adiciona o item selecionado ao carrinho
    document.getElementById("itens-disponiveis-carrinho").addEventListener("click", function (e) {
        if (e.target.classList.contains("btn-adicionar-item")) {
            const btn = e.target;
            const itemCard = btn.closest(".card-body");
            const quantidade = itemCard.querySelector(".quantidade-input").value;

            const dados = {
                referencia: btn.getAttribute("data-referencia"),
                cor: btn.getAttribute("data-cor"),
                tamanho: btn.getAttribute("data-tamanho"),
                preco: btn.getAttribute("data-preco"),
                quantidade: quantidade,
                idItem: btn.getAttribute("data-idItem")
            };

            fetch("/carrinho/adicionar", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(dados)
            })
                .then(res => res.json())
                .then(res => {
                    if (res.sucesso) {
                        alert("Item adicionado ao carrinho!");
                        modalCarrinho.hide();
                        location.reload();
                    } else {
                        alert(res.mensagem || "Erro ao adicionar item.");
                    }
                })
                .catch(err => {
                    console.error("Erro:", err);
                    alert("Erro inesperado ao adicionar item.");
                });
        }
    });

});
