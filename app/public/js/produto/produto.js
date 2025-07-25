document.addEventListener("DOMContentLoaded", function () {
    const modalCadastro = bootstrap.Modal.getOrCreateInstance(document.getElementById("modal-cadastro-produto"));
    const modalCarrinho = bootstrap.Modal.getOrCreateInstance(document.getElementById("modal-carrinho"));
    const modalElement = document.getElementById("modal-cadastro-produto");
    const form = modalElement.querySelector(".formulario-cadastro-produto");
    const areaItens = document.getElementById("area-itens-dinamicos");
    const nomeProduto = document.getElementById("nomeProduto");

    inicializarBotaoAdicionar();
    inicializarBotaoCadastrarNovoProduto();
    inicializarBotaoSalvar();
    inicializarRemocaoDeItens();
    inicializarExclusaoDeProduto();
    inicializarEdicaoDeProduto();
    inicializarCompraProduto();
    inicializarAdicionarAoCarrinho();

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

    function montarDadosDoFormulario(form) {
        const nomeProduto = form.querySelector("#nomeProduto").value.trim();

        const referencias = Array.from(form.querySelectorAll("input[name='referencia[]']")).map(input => input.value);
        const precos = Array.from(form.querySelectorAll("input[name='preco[]']")).map(input => parseFloat(input.value));
        const estoques = Array.from(form.querySelectorAll("input[name='estoque[]']")).map(input => parseInt(input.value));
        const tamanhos = Array.from(form.querySelectorAll("select[name='tamanho[]']")).map(select => select.value);
        const cores = Array.from(form.querySelectorAll("select[name='cor[]']")).map(select => select.value);
        const ids = Array.from(form.querySelectorAll("input[name='idItem[]']")).map(input => input.value || null);

        const itens = referencias.map((_, i) => ({
            idItem: ids[i],
            referencia: referencias[i],
            preco: precos[i],
            estoque: estoques[i],
            tamanho: tamanhos[i],
            cor: cores[i]
        }));

        return {
            nome: nomeProduto,
            itens: itens
        };
    }

    function inicializarBotaoSalvar() {
        const botaoSalvar = document.querySelector(".salvar-produtos");
        if (!botaoSalvar) return;

        botaoSalvar.addEventListener("click", function (e) {
            e.preventDefault();
            const formulario = this.closest(".modal-content").querySelector(".modal-body > form");
            if (!formulario || !validarFormulario(formulario)) return;

            const dados = montarDadosDoFormulario(formulario);
            const url = formulario.getAttribute("action");
            const metodo = formulario.getAttribute("method") || "POST";

            fetch(url, {
                method: metodo,
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(dados)
            })
                .then(res => res.json())
                .then(response => {
                    if (response.sucesso) {
                        alert("Produto salvo com sucesso!");
                        bootstrap.Modal.getInstance(document.getElementById("modal-cadastro-produto")).hide();
                        location.reload();
                    } else {
                        alert(response.mensagem || "Erro ao salvar produto.");
                    }
                })
                .catch(err => {
                    console.error("Erro ao salvar:", err);
                    alert("Erro inesperado ao salvar produto.");
                });
        });
    }

    function validarFormulario(formulario) {
        const nome = formulario.querySelector("#nomeProduto").value.trim();
        const referencias = formulario.querySelectorAll("input[name='referencia[]']");
        if (!nome) {
            alert("O nome do produto é obrigatório.");
            return false;
        }
        if (referencias.length === 0) {
            alert("Adicione pelo menos uma variação do produto.");
            return false;
        }
        return true;
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
        document.querySelectorAll(".editar-produto").forEach(botaoEditar => {
            botaoEditar.addEventListener("click", () => {
                const card = botaoEditar.closest(".card");
                const produtoId = card.querySelector(".card-header").getAttribute("data-id");
                const nome = card.querySelector(".nome-produto").textContent.trim();
                const linhas = card.querySelectorAll("tbody tr");
                const idItems = card.querySelectorAll(".idItem");

                modalCadastro.show();
                form.reset();
                nomeProduto.value = nome;
                areaItens.innerHTML = "";

                linhas.forEach((linha, index) => {
                    const descricao = linha.querySelector(".descricao-item")?.textContent.trim();
                    const estoque = linha.querySelector(".estoque-item")?.textContent.trim();
                    const preco = linha.querySelector(".preco-item")?.textContent.trim().replace(",", ".");
                    const idItem = idItems[index]?.value;

                    if (descricao) {
                        const [referencia, tamanho, cor] = descricao.split(" - ");
                        const item = { referencia, tamanho, cor, estoque, preco };
                        const hiddenIdItem = idItem ? `<input type="hidden" name="idItem[]" value="${idItem}">` : '';
                        areaItens.insertAdjacentHTML("beforeend", hiddenIdItem + criarItemHTML(item));
                    }
                });

                form.action = `/produtos/editar/${produtoId}`;
                form.method = "POST";
            });
        });
    }

    function inicializarCompraProduto() {
        document.querySelectorAll(".comprar-produto").forEach(botao => {
            botao.addEventListener("click", () => {
                const card = botao.closest(".card");
                const itensContainer = document.getElementById("itens-disponiveis-carrinho");
                const linhas = card.querySelectorAll("tbody tr");
                const idItems = card.querySelectorAll("input.idItem");

                itensContainer.innerHTML = "";

                linhas.forEach((linha, index) => {
                    const descricao = linha.querySelector(".descricao-item")?.textContent.trim();
                    const estoque = linha.querySelector(".estoque-item")?.textContent.trim();
                    const preco = linha.querySelector(".preco-item")?.textContent.trim();
                    const idItem = idItems[index]?.value;

                    if (descricao) {
                        const [referencia, tamanho, cor] = descricao.split(" - ");
                        itensContainer.insertAdjacentHTML("beforeend", criarItemCarrinhoHTML({ referencia, tamanho, cor, estoque, preco, idItem }));
                    }
                });

                modalCarrinho.show();
            });
        });
    }

    function criarItemCarrinhoHTML({ referencia, tamanho, cor, estoque, preco, idItem }) {
        return `
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
    }

    function inicializarAdicionarAoCarrinho() {
        const container = document.getElementById("itens-disponiveis-carrinho");

        container.addEventListener("click", function (e) {
            if (!e.target.classList.contains("btn-adicionar-item")) return;

            const btn = e.target;
            const itemCard = btn.closest(".card-body");
            const quantidade = itemCard.querySelector(".quantidade-input").value;
            const precoBruto = btn.getAttribute("data-preco")?.replace(",", ".");

            const dados = {
                idItem: btn.getAttribute("data-idItem"),
                referencia: btn.getAttribute("data-referencia"),
                cor: btn.getAttribute("data-cor"),
                tamanho: btn.getAttribute("data-tamanho"),
                preco: precoBruto,
                quantidade: parseInt(quantidade)
            };

            btn.disabled = true;

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
                        btn.disabled = false;
                    }
                })
                .catch(err => {
                    console.error("Erro:", err);
                    alert("Erro inesperado ao adicionar item.");
                    btn.disabled = false;
                });
        });
    }
});
