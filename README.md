# Oferto Cupons

Protótipo estático para uma vitrine de cupons por categoria com a linguagem visual do Oferto Digital.

## Como atualizar cupons

Edite `data/cupons.js` e adicione/remova objetos em `window.OFERTO_COUPONS`.

Campos principais:

- `categoria`: agrupamento do cupom.
- `loja`: nome da marca.
- `titulo`: chamada do card.
- `descricao`: texto curto.
- `codigo`: código para copiar.
- `url`: link de destino.
- `banner`: URL da imagem do cupom.
- `inicio` e `fim`: datas no formato `AAAA-MM-DD`.
- `destaque`: `true` para aparecer no painel principal.
- `regra`: observação curta.

Também existe `admin-lite.html`, uma tela simples para gerar o bloco de um novo cupom no formato certo.
