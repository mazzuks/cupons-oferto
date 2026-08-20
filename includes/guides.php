<?php

declare(strict_types=1);

function all_guides(): array
{
    return [
        [
            'slug' => 'pizza-com-cupom',
            'category' => 'Alimentação e Bebidas',
            'title' => 'Como economizar em uma bela pizza usando cupom',
            'summary' => 'Veja como comparar pedido mínimo, taxa de entrega e combos antes de aplicar o desconto.',
            'intro' => 'Pedir pizza pode sair bem mais barato quando o cupom certo entra junto com uma boa leitura das regras da oferta. Antes de finalizar, vale olhar o valor mínimo, a taxa de entrega, os sabores participantes e se o desconto compensa mais que um combo da loja.',
            'sections' => [
                ['title' => 'Compare o desconto com o valor final', 'body' => 'Um cupom de porcentagem alta nem sempre é o melhor. Some pizza, bebidas, taxa de entrega e possíveis adicionais. O que importa é o total final depois do cupom, não só a chamada da promoção.'],
                ['title' => 'Olhe pedido mínimo e lojas participantes', 'body' => 'Algumas ofertas funcionam apenas em lojas selecionadas, horários específicos ou pedidos acima de determinado valor. Conferir isso antes evita perder tempo no checkout.'],
                ['title' => 'Use o cupom no momento certo', 'body' => 'Fim de semana e horário de jantar costumam ter mais procura. Quando encontrar um cupom com validade curta, copie o código e teste antes de montar um pedido muito grande.'],
            ],
            'tip' => 'Se a taxa de entrega estiver alta, compare retirada na loja, combo promocional e cupom separado antes de decidir.',
        ],
        [
            'slug' => 'cupom-bom-nao-e-so-porcentagem',
            'category' => 'Compras',
            'title' => 'Cupom bom não é só porcentagem alta',
            'summary' => 'Aprenda a olhar frete, validade e regra de uso para não cair em oferta fraca.',
            'intro' => 'A melhor oferta é aquela que reduz o valor real da compra. Um cupom de 10% com frete grátis pode ser melhor que 25% com pedido mínimo alto, prazo ruim ou várias restrições escondidas.',
            'sections' => [
                ['title' => 'Veja o preço antes e depois do cupom', 'body' => 'Abra o carrinho, aplique o cupom e compare o total. Se o frete subiu, o desconto não entrou em todos os itens ou o pedido mínimo for alto demais, talvez a oferta não valha a pena.'],
                ['title' => 'Prefira cupons com regra clara', 'body' => 'Cupons bons costumam informar validade, categoria participante, valor mínimo e se funcionam para novos clientes ou todos os usuários. Quanto mais clara a regra, menor a chance de frustração.'],
                ['title' => 'Atenção ao prazo de entrega', 'body' => 'Em compras recorrentes, como mercado e itens da casa, economizar alguns reais pode não compensar se a entrega demorar demais. Considere o custo total e o prazo.'],
            ],
            'tip' => 'Antes de comprar, teste mais de um cupom quando possível. O melhor desconto muitas vezes aparece só no carrinho.',
        ],
        [
            'slug' => 'gift-cards-quando-esperar-promocao',
            'category' => 'Games',
            'title' => 'Gift cards: quando vale esperar uma promoção',
            'summary' => 'Um guia rápido para renovar assinatura, comprar créditos e evitar gasto por impulso.',
            'intro' => 'Gift cards e créditos de jogos têm promoções recorrentes. Quando a compra não é urgente, esperar uma janela de desconto pode ajudar a renovar assinaturas, comprar moedas virtuais ou presentear pagando menos.',
            'sections' => [
                ['title' => 'Compre antes do vencimento da assinatura', 'body' => 'Se a assinatura vence em breve, acompanhe cupons alguns dias antes. Assim você evita renovar no preço cheio por pressa.'],
                ['title' => 'Cuidado com valor mínimo e região', 'body' => 'Alguns gift cards têm regras de país, plataforma ou valor mínimo. Confira se o cupom funciona para a loja e para o tipo de crédito que você quer comprar.'],
                ['title' => 'Evite comprar crédito que não será usado', 'body' => 'Desconto bom não justifica comprar saldo parado. Priorize cupons para jogos, plataformas e assinaturas que você realmente usa.'],
            ],
            'tip' => 'Para assinaturas recorrentes, salve a data de renovação e procure cupons na semana anterior.',
        ],
        [
            'slug' => 'curso-com-desconto-sem-perder-qualidade',
            'category' => 'Educação',
            'title' => 'Como escolher cursos com desconto sem perder qualidade',
            'summary' => 'Critérios simples para avaliar carga horária, reputação e aplicação prática.',
            'intro' => 'Cursos com desconto podem ser uma ótima oportunidade, mas o menor preço não deve ser o único critério. Antes de usar um cupom, avalie conteúdo, professor, duração, avaliações e utilidade prática para o seu objetivo.',
            'sections' => [
                ['title' => 'Confira a grade e o nível do curso', 'body' => 'Veja se o curso é iniciante, intermediário ou avançado. Um desconto só vale se o conteúdo estiver alinhado com o que você precisa aprender agora.'],
                ['title' => 'Procure sinais de reputação', 'body' => 'Avaliações, amostras de aula, certificado, suporte e atualização do conteúdo ajudam a entender se a oferta entrega qualidade.'],
                ['title' => 'Calcule o custo por aplicação prática', 'body' => 'Um curso curto e direto pode valer mais que uma formação longa que você não termina. Pense em como o aprendizado será usado depois da compra.'],
            ],
            'tip' => 'Se estiver em dúvida, prefira cursos com prévia de aulas, garantia ou avaliações recentes.',
        ],
    ];
}

function guide_by_slug(string $slug): ?array
{
    foreach (all_guides() as $guide) {
        if ($guide['slug'] === $slug) {
            return $guide;
        }
    }

    return null;
}
