document.addEventListener("DOMContentLoaded", function () {
    // const botaoAbrir = document.querySelector("#abrir-modal-cadastro-produto");

    // const modal = new bootstrap.Modal(document.getElementById("modal-cadastro-produto"));

    // botaoAbrir.addEventListener("click", function () {
    //     modal.show();
    // });

    const botaoAdicionarMaisItens = document.querySelectorAll(".adicionar-mais-itens");

    botaoAdicionarMaisItens.forEach(function (botao) {
        botao.addEventListener("click", function (event) {
            event.preventDefault();

            const form = document.querySelector(".formulario-cadastro-produto");
            const btnSalvar = form.querySelector('button[type="submit"]');

            // Cria novo grupo de inputs (div.row)
            const novaDiv = document.createElement("div");
            novaDiv.classList.add("row", "g-3", "mt-2", "mb-2");

            novaDiv.innerHTML = `
                <div class="col-md-2">
                    <label class="form-label">Referencia</label>
                    <input type="text" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Preço</label>
                    <input type="text" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Quantidade</label>
                    <input type="number" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tamanho</label>
                    <select class="form-select">
                    <option value="P">P</option>
                    <option value="M">M</option>
                    <option value="G">G</option>
                    <option value="GG">GG</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Cor</label>
                    <select class="form-select">
                    <option value="Azul">Azul</option>
                    <option value="Vermelho">Vermelho</option>
                    <option value="Branco">Branco</option>
                    <option value="Preto">Preto</option>
                    </select>
                </div>
                <div class="col-md-2">
					<button class="btn btn-warning adicionar-mais-itens" style="margin-top: 32px;">Adicionar</button>
				</div>
            `;

            // Insere novaDiv antes do botão "Salvar"
            form.insertBefore(novaDiv, btnSalvar);
        });
    });
});
