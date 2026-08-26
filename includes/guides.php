<?php

declare(strict_types=1);

function china_in_box_coupon_box(string $title, string $body): array
{
    return [
        'kicker' => '',
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

function shopee_coupon_box(string $title, string $body): array
{
    return [
        'store' => 'Shopee',
        'kicker' => '',
        'title' => $title,
        'body' => $body,
        'limit' => 4,
        'coupons' => [],
    ];
}

function all_guides(): array
{
    return [
        [
            'slug' => 'origem-da-shopee',
            'category' => 'Shopee',
            'title' => 'A origem da Shopee: como o app virou parada obrigatoria para garimpar ofertas',
            'summary' => 'Conheca a historia da Shopee e veja como usar cupons antes de fechar a compra.',
            'intro' => 'A Shopee virou parte da rotina de quem gosta de pesquisar preço, comparar vendedores e encontrar aquele produto util que parecia escondido no meio da internet. De capinha de celular a item de cozinha, de presente simples a acessorio gamer, a plataforma cresceu justamente por juntar variedade, preço competitivo e uma experiencia de compra feita para o celular.',
            'sections' => [
                [
                    'title' => 'De marketplace regional a app conhecido no Brasil',
                    'body' => 'A Shopee nasceu no Sudeste Asiatico dentro do grupo Sea, inicialmente como um marketplace pensado para conectar vendedores e compradores em uma experiencia mobile. A proposta era simples: facilitar a venda online para pequenos e grandes lojistas e deixar a compra mais rapida para quem ja fazia quase tudo pelo celular. Essa combinacao ajudou a marca a crescer em paises como Singapura, Indonesia, Tailandia, Malasia, Vietna e Filipinas antes de ganhar força em outros mercados.',
                ],
                [
                    'title' => 'Por que ela pegou tao forte por aqui',
                    'body' => 'No Brasil, a Shopee encontrou um publico que ja gostava de pesquisar, pechinchar e esperar promoçao. O app se encaixou bem nesse comportamento porque oferece muitas opcoes para o mesmo tipo de produto, avaliações de compradores, cupons frequentes e campanhas de frete ou desconto. Para muita gente, comprar na Shopee virou um habito de garimpo: a pessoa abre o app sem pressa, pesquisa varias lojas e salva itens para decidir depois.',
                ],
                [
                    'title' => 'Como comprar melhor sem cair no impulso',
                    'body' => 'O lado bom da variedade tambem exige cuidado. Como existem muitos vendedores e produtos parecidos, o melhor caminho e comparar preço, reputacao da loja, prazo de entrega, avaliações com foto e politica de devolucao. O cupom ajuda, mas nao deve ser o unico criterio. Um desconto pequeno em uma loja confiavel pode valer mais que uma promessa enorme em uma pagina sem avaliações suficientes.',
                ],
                [
                    'title' => 'Cupom entra no final, mas a escolha começa antes',
                    'body' => 'Antes de finalizar uma compra na Shopee, vale fazer uma checagem rapida: o produto realmente atende ao que voce procura, a loja tem bom historico, o prazo faz sentido e o valor final ficou melhor depois do cupom? Quando essas respostas batem, o desconto vira um empurrao positivo, nao uma compra por ansiedade. Por isso, reunir cupons ativos em uma pagina ajuda: voce pesquisa a oferta, copia o codigo disponivel e segue para resgatar sem perder tempo procurando em varios lugares.',
                ],
            ],
            'coupon_box' => shopee_coupon_box(
                'Vai comprar na Shopee? Veja se tem cupom ativo',
                'Escolha um cupom da Shopee, copie o codigo e siga para resgatar a oferta com o tracking correto.'
            ),
            'tip' => 'Dica: compare sempre o valor final com frete, prazo e reputacao do vendedor antes de concluir a compra.',
        ],
        [
            'slug' => 'produtos-mais-vendidos-na-shopee',
            'category' => 'Shopee',
            'title' => 'Produtos mais vendidos na Shopee: o que costuma aparecer nas buscas de quem quer economizar',
            'summary' => 'Veja categorias populares na Shopee e como usar cupons para melhorar o valor final.',
            'intro' => 'Quando alguem fala em Shopee, muita gente pensa logo em achadinhos. A plataforma ficou conhecida por reunir produtos baratos, itens importados, lojas nacionais e vendedores especializados em nichos bem diferentes. Nem sempre o produto mais vendido sera o melhor para voce, mas entender as categorias mais procuradas ajuda a comprar com mais criterio.',
            'sections' => [
                [
                    'title' => 'Acessorios de celular e tecnologia simples',
                    'body' => 'Capinhas, peliculas, carregadores, suportes, cabos, fones simples e adaptadores costumam ter bastante procura porque sao itens de reposicao ou compra recorrente. Muita gente prefere pagar menos nesses produtos, principalmente quando nao precisa de uma marca especifica. Ainda assim, vale olhar comentarios, fotos reais e quantidade de vendas, porque acessorio barato demais pode sair caro se quebrar rapido ou nao funcionar bem.',
                ],
                [
                    'title' => 'Casa, cozinha e organizacao',
                    'body' => 'Outro grupo forte envolve itens para casa: potes, organizadores, utensilios de cozinha, panos, luminarias, suportes, caixas, cabides e pequenos acessorios de decoracao. Esses produtos costumam viralizar porque prometem resolver problemas pequenos do dia a dia. A melhor compra e aquela que melhora a rotina sem virar acumulacao. Se voce ja estava procurando algo para organizar gaveta, pia, armario ou mesa, um cupom pode deixar o carrinho mais interessante.',
                ],
                [
                    'title' => 'Beleza, moda e pequenos presentes',
                    'body' => 'Maquiagem, necessaires, escovas, acessorios de cabelo, bijuterias, camisetas, meias, bolsas pequenas e itens para presente tambem aparecem bastante. Nessa parte, o cuidado com avaliação e ainda mais importante, porque tamanho, textura, cor e acabamento podem variar. Antes de comprar, leia os comentarios negativos tambem. Eles ajudam a entender se o problema e pontual ou se acontece com muita gente.',
                ],
                [
                    'title' => 'Como usar cupom sem comprar besteira',
                    'body' => 'A regra mais honesta e simples: cupom bom e aquele que reduz o preço de algo que voce ja queria comprar. Se o desconto te fez colocar cinco itens extras no carrinho sem necessidade, a economia desapareceu. Para usar melhor os cupons da Shopee, monte o carrinho primeiro, confira frete e prazo, depois aplique o codigo. Assim voce entende o desconto real e decide se vale fechar agora ou esperar uma promocao melhor.',
                ],
            ],
            'coupon_box' => shopee_coupon_box(
                'Cupons da Shopee para testar no carrinho',
                'Copie um cupom ativo e confira o desconto no valor final antes de pagar.'
            ),
            'tip' => 'Produtos populares mudam com campanhas, datas comemorativas e tendencias. O melhor filtro continua sendo preço final, avaliações e prazo.',
        ],
        [
            'slug' => 'produtos-mais-desejados-na-shopee',
            'category' => 'Shopee',
            'title' => 'Produtos mais desejados na Shopee: como transformar vontade em compra inteligente',
            'summary' => 'Entenda como lidar com lista de desejos, achadinhos e cupons sem comprar por impulso.',
            'intro' => 'A lista de desejos da Shopee tem um papel curioso: ela guarda aquilo que chamou sua atencao, mas tambem pode virar armadilha para compras por impulso. O segredo esta em usar essa lista como filtro, nao como desculpa. Quando voce salva um produto e espera um pouco, consegue comparar melhor, olhar avaliações novas e aproveitar cupons com mais calma.',
            'sections' => [
                [
                    'title' => 'Desejado nao quer dizer urgente',
                    'body' => 'Muitos produtos desejados sao itens de conforto, estilo ou curiosidade: organizadores bonitos, luminarias, utensilios diferentes, roupas, acessorios gamer, produtos de beleza, itens para pet e presentes criativos. Eles podem ser legais, mas nem sempre precisam ser comprados na hora. Esperar alguns dias ajuda a separar o desejo real do impulso criado por uma foto bonita ou por uma oferta com contador.',
                ],
                [
                    'title' => 'Use favoritos para comparar vendedores',
                    'body' => 'Salvar o mesmo tipo de produto de lojas diferentes pode ser uma boa estrategia. Depois, compare preço, prazo, nota do vendedor, fotos de clientes, quantidade de pedidos e comentarios recentes. Muitas vezes, dois produtos parecem iguais, mas um tem acabamento melhor, envio mais rapido ou menos reclamacoes. O cupom deve entrar depois dessa primeira peneira.',
                ],
                [
                    'title' => 'Datas promocionais podem ajudar',
                    'body' => 'Marketplaces costumam trabalhar com campanhas de datas duplas, viradas de mes, frete reduzido e cupons por categoria. Quem nao tem pressa pode se beneficiar disso. Se um produto esta na sua lista de desejos, acompanhe por alguns dias e veja se o preço muda. Quando aparecer cupom, confira se ele vale para aquela categoria e se o desconto supera qualquer variacao de frete.',
                ],
                [
                    'title' => 'Como decidir se vale comprar agora',
                    'body' => 'Uma pergunta simples resolve muita coisa: se o cupom nao existisse, voce ainda compraria esse produto? Se a resposta for sim, o desconto ajuda. Se a resposta for nao, talvez o cupom esteja mandando mais que a sua necessidade. Para comprar melhor na Shopee, use cupons como ferramenta de economia, nao como motivo principal da compra. Escolha o produto, valide a loja, aplique o codigo e so depois finalize.',
                ],
            ],
            'coupon_box' => shopee_coupon_box(
                'Achou algo na lista de desejos? Teste um cupom da Shopee',
                'Veja os cupons ativos, copie o codigo e resgate a oferta antes de finalizar.'
            ),
            'tip' => 'Uma boa lista de desejos economiza dinheiro quando ela ajuda voce a esperar, comparar e comprar melhor.',
        ],
        [
            'slug' => 'como-funciona-a-logistica-da-shopee',
            'category' => 'Shopee',
            'title' => 'Como funciona a logistica da Shopee e por que o prazo muda tanto de uma compra para outra',
            'summary' => 'Entenda por que frete, vendedor, origem do produto e cupom influenciam a compra.',
            'intro' => 'Quem compra na Shopee ja percebeu que dois produtos parecidos podem ter prazos bem diferentes. Um chega rapido, outro demora mais; um tem frete interessante, outro muda bastante quando entra no carrinho. Isso acontece porque a plataforma conecta vendedores variados, estoques diferentes e formas de envio que dependem da origem do produto e da estrutura logistica disponivel.',
            'sections' => [
                [
                    'title' => 'Marketplace nao e uma loja unica',
                    'body' => 'A Shopee funciona como marketplace. Isso significa que muitos vendedores anunciam dentro da mesma plataforma. Alguns produtos saem de vendedores nacionais, outros podem ter origem internacional, e isso muda prazo, rastreamento, custo de envio e experiencia de entrega. Por isso, comparar apenas o preço do produto pode enganar. O valor final e o prazo precisam entrar na conta.',
                ],
                [
                    'title' => 'Frete faz parte do desconto real',
                    'body' => 'Um cupom pode parecer bom, mas o frete pode reduzir o beneficio. Tambem pode acontecer o contrario: um produto um pouco mais caro, com frete melhor, sai mais vantajoso no total. O melhor jeito de avaliar e simular no carrinho. Coloque o produto, veja o frete, aplique o cupom e compare o total final com outras opcoes. Esse pequeno cuidado evita aquela sensacao de desconto bonito que some na etapa de pagamento.',
                ],
                [
                    'title' => 'Prazo depende de origem, vendedor e rota',
                    'body' => 'Produtos enviados de mais longe naturalmente podem demorar mais. Vendedores com estoque nacional tendem a entregar mais rapido, mas isso tambem depende da transportadora, do CEP e do periodo da compra. Em grandes datas promocionais, o volume de pedidos pode aumentar e afetar o prazo. Antes de fechar, confira a estimativa de entrega e veja se ela combina com sua necessidade. Presente de ultima hora, por exemplo, pede mais cuidado.',
                ],
                [
                    'title' => 'Como comprar com mais tranquilidade',
                    'body' => 'A compra fica melhor quando voce junta quatro pontos: loja bem avaliada, produto com comentarios reais, prazo aceitavel e cupom funcionando. Se um desses pontos falha, vale repensar. Para compras pequenas e sem urgencia, talvez um prazo maior seja aceitavel. Para item importante, pode valer pagar um pouco mais em uma opçao com entrega melhor. O Oferto ajuda na etapa do cupom, mas a decisao final precisa olhar o conjunto da compra.',
                ],
            ],
            'coupon_box' => shopee_coupon_box(
                'Antes de fechar o carrinho, veja cupons da Shopee',
                'Copie um cupom ativo, abra a oferta e confirme o desconto junto com frete e prazo.'
            ),
            'tip' => 'Frete gratis, prazo curto e cupom nem sempre aparecem juntos. Compare o total antes de decidir.',
        ],
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
                'China in Box com cupom para pedir yakisoba',
                'Copie um cupom ativo do China in Box e resgate a oferta no site parceiro.'
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
                'China in Box com cupom para pedir rolinho primavera',
                'Copie um cupom ativo do China in Box e resgate a oferta no site parceiro.'
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
                'China in Box com cupom para comida chinesa',
                'Copie um cupom ativo do China in Box e resgate a oferta no site parceiro.'
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
