<?php

declare(strict_types=1);

function china_in_box_coupon_box(string $title, string $body): array
{
    return [
        'kicker' => 'Cupons China in Box',
        'title' => $title,
        'body' => $body,
        'coupons' => [
            [
                'code' => 'CHINALOMADEE',
                'description' => '20% de desconto em compras acima de R$60,00, conforme disponibilidade da oferta.',
            ],
            [
                'code' => 'CHINALOMADEE12',
                'description' => '12% de desconto em compras acima de R$60,00, conforme disponibilidade da oferta.',
            ],
        ],
    ];
}

function all_guides(): array
{
    return [
        [
            'slug' => 'origem-do-yakisoba',
            'category' => 'Alimentação e Bebidas',
            'title' => 'A origem do yakisoba: como um prato simples virou favorito no delivery',
            'summary' => 'Entenda por que o yakisoba mistura influência chinesa, adaptação japonesa e gosto brasileiro.',
            'intro' => 'Quem procura comida chinesa no Brasil provavelmente ja viu yakisoba no cardapio. Ele aparece em restaurantes orientais, praças de alimentacao, festivais e delivery. Mas a historia desse prato e mais curiosa do que parece: ficou famoso como comida japonesa, mas carrega uma influencia chinesa muito forte desde a sua base.',
            'sections' => [
                [
                    'title' => 'Um prato com nome japones e raiz chinesa',
                    'body' => 'O nome yakisoba vem do japones. "Yaki" indica algo grelhado, frito ou preparado na chapa; "soba" significa macarrao. Em uma traducao simples, yakisoba e o macarrao frito ou salteado. So que a ideia de saltear macarrao com legumes, carnes e molho ja existia na culinaria chinesa em pratos como o chow mein, tambem associado ao macarrao frito. Foi esse tipo de preparo que ajudou a formar o yakisoba que muita gente conhece hoje.',
                ],
                [
                    'title' => 'Como ele ganhou a cara que conhecemos',
                    'body' => 'No Japao, o prato ganhou força especialmente no seculo XX. Em periodos de escassez e adaptacao alimentar, o macarrao se tornou uma alternativa pratica, barata e facil de combinar com ingredientes disponiveis. Repolho, cenoura, cebola, carne suina, frango, frutos do mar e molhos mais encorpados ajudaram a transformar uma receita simples em um prato completo. Com o tempo, o yakisoba deixou de ser apenas uma solucao rapida e passou a ser uma comida de rua muito popular, comum em festas, eventos e barraquinhas.',
                ],
                [
                    'title' => 'Por que o brasileiro gosta tanto',
                    'body' => 'No Brasil, ele ganhou outro caminho. Aqui, muita gente associa yakisoba a uma porcao generosa, com macarrao, legumes, proteina e bastante molho. Essa versao mais molhadinha e encorpada conversa bem com o gosto brasileiro, porque funciona como refeicao completa: mata a fome, tem variedade e geralmente vem em quantidade boa para dividir. Nao e por acaso que virou um dos pratos orientais mais pedidos por quem quer algo diferente da pizza ou do hamburguer, mas ainda quer praticidade.',
                ],
                [
                    'title' => 'Da historia para o pedido de hoje',
                    'body' => 'Uma das partes mais interessantes do yakisoba e justamente sua capacidade de se adaptar. Existe yakisoba de carne, frango, camarao, misto, vegetariano e versoes mais brasileiras, com molho mais doce ou mais intenso. Esse tipo de prato mostra como a comida viaja: nasce de uma tradicao, passa por outro pais, muda de molho, muda de ingredientes e chega a novos publicos com uma cara propria. Para quem gosta de pedir comida chinesa em casa, o yakisoba tambem costuma ser uma boa escolha quando a ideia e economizar. Ele rende bem, combina com entradas como rolinho primavera e pode virar uma refeicao compartilhada. Antes de fechar o pedido, vale conferir se existe cupom ativo, pedido minimo, regra de uso e validade da promocao. Isso evita perder desconto por detalhe pequeno na hora de pagar.',
                ],
            ],
            'coupon_box' => china_in_box_coupon_box(
                'Bateu vontade de comer yakisoba?',
                'Antes de pedir, veja se um dos cupons ativos do China in Box deixa seu jantar mais barato.'
            ),
            'tip' => 'Fontes consultadas: Receitas Nestle, Superinteressante e Discover Nikkei.',
        ],
        [
            'slug' => 'origem-do-rolinho-primavera',
            'category' => 'Alimentação e Bebidas',
            'title' => 'A origem do rolinho primavera: por que essa entrada virou simbolo de sorte e sabor',
            'summary' => 'Conheça a historia do rolinho primavera e veja como ele pode completar o pedido com desconto.',
            'intro' => 'O rolinho primavera e uma daquelas entradas que muita gente pede quase no automatico. Crocante por fora, recheado por dentro e facil de dividir, ele combina com yakisoba, frango xadrez, arroz chop suey e outros pratos da culinaria chinesa adaptada ao gosto brasileiro.',
            'sections' => [
                [
                    'title' => 'A ligacao com a chegada da primavera',
                    'body' => 'A origem do rolinho primavera esta ligada a tradicoes chinesas do inicio da primavera. Em diferentes regioes da China, era comum celebrar a chegada da estacao com preparos feitos de massa fina e vegetais frescos. A ideia tinha um simbolismo bonito: depois do inverno, os brotos e verduras da nova colheita representavam renovacao, fartura e um novo ciclo. Com o tempo, esses preparos foram evoluindo ate chegar ao formato de pequenos rolos recheados, que podiam ser fritos e servidos como petisco ou entrada.',
                ],
                [
                    'title' => 'Por que ele ficou tao conhecido',
                    'body' => 'O nome tambem entrega essa conexao. Em ingles, ele ficou conhecido como spring roll, literalmente rolinho de primavera. Em chines, a ideia se relaciona aos rolos ou panquecas consumidos no periodo de inicio da primavera. Em algumas referencias historicas, aparece a evolucao do "prato de primavera" para massas recheadas e enroladas. A versao frita, dourada e crocante acabou ganhando um sentido extra: pela cor e pelo formato, passou a ser associada a prosperidade e boa sorte.',
                ],
                [
                    'title' => 'Varios paises, varias versoes',
                    'body' => 'Ao viajar pela Asia, o rolinho primavera ganhou muitas versoes. No Japao, ficou conhecido como harumaki. No Vietna, existem rolinhos frescos com massa de arroz e recheios leves. Em outros lugares, aparecem recheios com carne, legumes, camarao, broto de feijao, repolho, cenoura e diferentes molhos. Isso mostra uma coisa simples: quando uma comida e boa, pratica e facil de adaptar, ela atravessa fronteiras sem pedir licenca.',
                ],
                [
                    'title' => 'Como aproveitar melhor no delivery',
                    'body' => 'No Brasil, o rolinho primavera virou uma entrada perfeita para delivery porque resolve varios desejos ao mesmo tempo. E crocante, vem em porcoes faceis de compartilhar, tem sabor conhecido e funciona bem antes do prato principal. Tambem e uma escolha interessante quando a pessoa quer montar um pedido mais completo sem gastar tanto: um prato principal para dividir, uma entrada e, se houver cupom, um desconto aplicado no fim. Na hora de pedir, vale olhar com carinho para os combos. Muitas vezes, o desconto fica mais vantajoso quando o pedido passa de um valor minimo. Se o cupom exige compras acima de R$60,00, por exemplo, adicionar uma entrada como rolinho primavera pode ajudar a bater o minimo e transformar uma compra comum em uma promocao melhor. E claro: isso so faz sentido se voce realmente for consumir. Economia boa nao e comprar mais por impulso; e pagar menos pelo que ja fazia sentido pedir.',
                ],
            ],
            'coupon_box' => china_in_box_coupon_box(
                'Rolinho primavera combina com desconto',
                'Se a ideia e completar o pedido com uma entrada crocante, aproveite para copiar o cupom e resgatar a oferta.'
            ),
            'tip' => 'Fontes consultadas: China Today, Oxford Companion to Food e South China Morning Post.',
        ],
        [
            'slug' => 'comida-chinesa-pelo-mundo',
            'category' => 'Alimentação e Bebidas',
            'title' => 'Onde a comida chinesa faz mais sucesso fora da China e do Japão?',
            'summary' => 'Veja por que a culinaria chinesa se espalhou pelo mundo e virou escolha comum no delivery.',
            'intro' => 'Comida chinesa e uma das culinarias mais reconhecidas do mundo. Mesmo quem nunca estudou gastronomia sabe identificar alguns classicos: rolinho primavera, frango xadrez, yakisoba, arroz chop suey, carne com legumes, porco agridoce e pratos feitos no wok.',
            'sections' => [
                [
                    'title' => 'Um sucesso dificil de medir com uma lista unica',
                    'body' => 'Antes de listar paises, vale fazer uma observacao importante: nao existe um ranking unico e perfeito de quem mais consome comida chinesa no mundo. O consumo pode ser medido por numero de restaurantes, tamanho da comunidade chinesa, pedidos em aplicativos, buscas na internet, faturamento ou presenca cultural. Cada metodo muda um pouco a lista. Ainda assim, alguns paises aparecem com frequencia quando o assunto e alcance global da comida chinesa fora da propria China. Como este guia olha fora do Japao, a lista considera mercados onde a culinaria chinesa tem presenca forte sem usar o Japao como exemplo principal.',
                ],
                [
                    'title' => 'Estados Unidos, Canada e Australia',
                    'body' => 'Os Estados Unidos provavelmente sao o caso mais famoso. A comida chinesa faz parte da rotina americana ha muitas decadas, com restaurantes espalhados por grandes cidades, bairros pequenos e praças de alimentacao. O interessante e que a culinaria chinesa nos EUA tambem ganhou versoes locais, adaptadas ao gosto do publico. Pratos como orange chicken, egg roll e chop suey ficaram conhecidos justamente por essa mistura entre tradicao chinesa, imigracao e paladar local. O Canada tambem tem uma relacao forte com comida chinesa, especialmente em cidades como Vancouver e Toronto, que contam com comunidades asiaticas relevantes e muitos restaurantes chineses tradicionais e modernos. Na Australia, grandes cidades como Sydney e Melbourne tem bairros, mercados e restaurantes de forte influencia asiatica. A proximidade com a Asia e a diversidade migratoria ajudaram a transformar pratos chineses em opcoes comuns para almoço, jantar e delivery.',
                ],
                [
                    'title' => 'Europa e Sudeste Asiatico',
                    'body' => 'No Reino Unido, restaurantes chineses e lojas de takeaway se tornaram parte da paisagem urbana. Para muita gente, pedir comida chinesa no fim de semana e tao normal quanto pedir pizza. O mesmo acontece em diferentes paises da Europa, onde a comida chinesa se espalhou em formatos variados: restaurantes familiares, buffets, delivery e casas mais sofisticadas. No Sudeste Asiatico, paises como Singapura, Malasia, Tailandia, Indonesia e Filipinas tambem tem forte presenca de pratos chineses ou de influencia chinesa. Ali, a historia e diferente da ocidental: em muitos casos, comunidades chinesas vivem nesses paises ha geracoes, e a culinaria se misturou profundamente aos ingredientes locais. Por isso, nem sempre da para separar com facilidade o que e comida chinesa e o que ja virou comida local com raiz chinesa.',
                ],
                [
                    'title' => 'E o Brasil nessa historia?',
                    'body' => 'Aqui, a comida chinesa ganhou espaco principalmente pela praticidade, pelo delivery e por pratos que combinam bem com o paladar popular. Muita gente gosta porque e uma opcao diferente sem ser complicada. Tem molho, tem legumes, tem porcao generosa, tem entrada crocante e costuma funcionar bem para dividir. Isso ajuda a explicar por que marcas como China in Box conseguem conversar com quem quer variar o jantar sem gastar demais. Se voce esta pensando em pedir comida chinesa hoje, a dica e simples: escolha o prato, confira se sua regiao participa, veja se o pedido bate o minimo e teste o cupom antes de pagar. Assim, uma comida que viajou o mundo inteiro pode chegar na sua casa com um desconto melhor.',
                ],
            ],
            'coupon_box' => china_in_box_coupon_box(
                'Hoje pode ser dia de comida chinesa',
                'Escolha seu prato favorito, copie um cupom ativo e siga para o China in Box para resgatar a oferta.'
            ),
            'tip' => 'Fontes consultadas: Xinhua, The World Economy/Wiley e Foodstar.',
        ],
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
