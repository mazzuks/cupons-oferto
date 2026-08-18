# Oferto Cupons

Protótipo estático para uma vitrine de cupons por categoria com a linguagem visual do Oferto Digital.

## Como atualizar cupons

Edite `data/cupons.js` e adicione/remova objetos em `window.OFERTO_COUPONS`.

Campos principais:

- `categoria`: agrupamento do cupom. Exemplo: Ruffles e outros snacks entram em `Alimentação e Bebidas`, não em Educação.
- `loja`: nome da marca.
- `titulo`: chamada do card.
- `descricao`: texto curto.
- `codigo`: código para copiar.
- `url`: link de destino.
- `banner`: URL da imagem do cupom.
- `inicio` e `fim`: datas no formato `AAAA-MM-DD`.
- `status`: use `ativo`, `rascunho` ou `pausado`. Só cupons ativos aparecem no site.
- `destaque`: `true` para aparecer no painel principal.
- `regra`: observação curta.

Também existe `admin-lite.html`, uma tela simples para gerar o bloco de um novo cupom no formato certo.

## Rotina de operação

Para manter simples agora:

- banners ficam em `assets/` quando forem peças próprias, ou como URL pública quando vierem de parceiros;
- links, textos, status e validade ficam em `data/cupons.js`;
- cupons vencidos somem automaticamente quando passam da data `fim`;
- cupons em `rascunho` ou `pausado` não aparecem no site;
- depois de editar, publicar um commit na `main` atualiza o GitHub e uma nova versão pode ser publicada no Sites.

Quando o volume crescer, o próximo passo natural é trocar `data/cupons.js` por uma planilha/Google Sheets como CMS leve, mantendo o mesmo layout do site.

## Planilha como CMS leve

O site já está preparado para ler uma planilha publicada como CSV. Para ativar:

1. Crie uma planilha com as colunas: `categoria`, `loja`, `titulo`, `descricao`, `codigo`, `url`, `banner`, `inicio`, `fim`, `status`, `destaque`, `regra`.
2. Publique a aba como CSV.
3. Cole a URL em `data/config.js`, no campo `sheetCsvUrl`.
4. Faça um novo deploy.

Enquanto `sheetCsvUrl` estiver vazio, o site usa os cupons locais de `data/cupons.js`.
