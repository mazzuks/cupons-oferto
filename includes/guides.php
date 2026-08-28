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
    return brand_coupon_box('Shopee', $title, $body, 4);
}

function brand_coupon_box(string $store, string $title, string $body, int $limit = 3): array
{
    return [
        'store' => $store,
        'kicker' => '',
        'title' => $title,
        'body' => $body,
        'limit' => $limit,
        'coupons' => [],
    ];
}

function all_guides(): array
{
    $guides = [
        [
            'slug' => 'lojas-rede-cosmeticos-que-valem-colocar-no-carrinho',
            'category' => 'Saúde e Beleza',
            'title' => 'Lojas REDE: cosméticos que valem colocar no carrinho quando aparece cupom',
            'summary' => 'Veja como escolher maquiagem, cuidados com cabelo e itens de beleza sem comprar por impulso.',
            'intro' => 'A Lojas REDE aparece bastante para quem procura beleza, perfumaria e cuidados pessoais com preço melhor. Como esse tipo de compra mistura desejo, reposição e novidade, o cupom ajuda muito quando entra no produto certo.',
            'sections' => [
                ['title' => 'Reponha primeiro os produtos de uso contínuo', 'body' => 'Shampoo, condicionador, protetor solar, hidratante, desodorante e itens básicos de banho são compras que voltam sempre. Quando aparece cupom da Lojas REDE, começar por essa lista deixa a economia mais concreta, porque o desconto entra em algo que você já compraria de qualquer forma. Antes de olhar novidades, veja o que está acabando em casa e monte um carrinho curto com produtos de rotina.'],
                ['title' => 'Separe desejo de necessidade na maquiagem', 'body' => 'Maquiagem costuma ser a parte mais tentadora da compra. Uma base nova, uma paleta diferente ou um batom em promoção parecem ótimos no impulso, mas nem sempre entram no uso real. Para comprar melhor, pense nos tons que você já usa, na validade depois de aberto e na frequência de aplicação. O cupom é bem-vindo quando melhora o preço de um item certeiro, não quando cria uma coleção parada na gaveta.'],
                ['title' => 'Em skincare, confira pele, composição e frequência', 'body' => 'Produtos de cuidado com a pele pedem um pouco mais de critério. Antes de colocar sérum, ácido, hidratante ou protetor no carrinho, confira indicação de uso, tipo de pele, composição e quantidade. Um desconto bom não compensa se o produto irritar, vencer antes do fim ou duplicar uma etapa que você já tem em casa. Para skincare, a melhor compra é a que você consegue usar com regularidade.'],
                ['title' => 'Compare kit, embalagem grande e preço por unidade', 'body' => 'Em beleza, o menor preço da prateleira nem sempre é o melhor negócio. Um kit pode sair mais barato por unidade, uma embalagem maior pode durar mais e um combo pode valer a pena se todos os itens forem úteis. Antes de fechar, compare o total com e sem cupom e veja se o desconto vale para o carrinho inteiro ou só para produtos selecionados. Essa conta simples evita promoção bonita com economia pequena.'],
                ['title' => 'Cuidado com validade e estoque parado', 'body' => 'Cosmético também vence, muda textura, oxida, perde cheiro ou simplesmente deixa de combinar com sua rotina. Por isso, comprar três ou quatro unidades só porque o desconto apareceu pode virar desperdício. Estoque faz sentido para itens que acabam rápido e têm validade confortável. Para produtos novos, vale testar uma unidade primeiro e só repetir quando você souber que realmente funciona.'],
                ['title' => 'Frete, retirada e prazo mudam o desconto final', 'body' => 'Depois de aplicar o cupom, olhe o total da compra com entrega ou retirada. Às vezes o desconto melhora bastante o carrinho; em outras, o frete consome boa parte da vantagem. Também vale conferir prazo, disponibilidade da marca desejada e regra da oferta antes de pagar. O melhor cupom da Lojas REDE é aquele que fecha uma compra útil, chega dentro do prazo e reduz o valor final de verdade.'],
            ],
            'coupon_box' => brand_coupon_box(
                'Lojas REDE',
                'Vai renovar seus itens de beleza?',
                'Veja ofertas ativas da Lojas REDE, copie o cupom quando houver e resgate pelo caminho certo.'
            ),
            'tip' => 'Dica: antes de fechar, veja se o cupom vale para a marca desejada e confira prazo, frete e regra do carrinho.',
        ],
        [
            'slug' => 'lojas-rede-produtos-de-higiene-com-desconto',
            'category' => 'Saúde e Beleza',
            'title' => 'Produtos de higiene com desconto: como economizar na Lojas REDE',
            'summary' => 'Uma leitura simples para comprar itens recorrentes de higiene usando cupom sem exagerar no carrinho.',
            'intro' => 'Sabonete, creme dental, escova, absorvente, desodorante e itens de cuidado diário entram na lista de compras o ano inteiro. Por isso, quando aparece cupom na Lojas REDE, a economia pode ser mais útil do que em uma compra feita só por vontade.',
            'sections' => [
                ['title' => 'Monte uma lista de reposição', 'body' => 'A melhor forma de aproveitar desconto em higiene é saber o que falta. Faça uma lista curta com itens de uso frequente e priorize produtos que sua casa consome todo mês. Assim, o cupom reduz um gasto real.'],
                ['title' => 'Olhe combos com atenção', 'body' => 'Combos podem valer a pena, principalmente em itens recorrentes. Mas confira se a quantidade faz sentido e se o valor final com cupom ficou melhor que comprar separado.'],
                ['title' => 'Use o desconto no carrinho certo', 'body' => 'Algumas ofertas exigem valor mínimo ou categorias específicas. Antes de concluir, aplique o cupom, veja se o desconto apareceu e só então finalize a compra.'],
            ],
            'coupon_box' => brand_coupon_box(
                'Lojas REDE',
                'Cupom da Lojas REDE para produtos do dia a dia',
                'Confira as ofertas ativas e economize em higiene, beleza e perfumaria.'
            ),
            'tip' => 'Itens recorrentes são bons para cupom porque você compra de novo. Só cuide para não levar quantidade demais.',
        ],
        [
            'slug' => 'lojas-rede-perfumaria-e-beleza-para-presentear',
            'category' => 'Saúde e Beleza',
            'title' => 'Perfumaria e beleza para presentear: como usar cupom na Lojas REDE',
            'summary' => 'Veja ideias de presente em beleza e como conferir se a oferta realmente compensa.',
            'intro' => 'Perfume, hidratante, kit de cabelo e maquiagem costumam funcionar bem como presente. Quando a Lojas REDE tem cupom ativo, dá para deixar a lembrança mais caprichada sem estourar o orçamento.',
            'sections' => [
                ['title' => 'Pense no perfil de quem vai receber', 'body' => 'Na dúvida, produtos de cuidado pessoal mais neutros costumam ser mais seguros que fragrâncias muito marcantes ou maquiagem de tom específico. Cupom ajuda, mas a escolha ainda precisa combinar com a pessoa.'],
                ['title' => 'Kit pode ser melhor que produto solto', 'body' => 'Em datas comemorativas, kits de beleza podem ter apresentação melhor e preço interessante. Compare o valor do kit com os itens separados e veja se o cupom entra no total.'],
                ['title' => 'Confira prazo de entrega', 'body' => 'Presente tem data. Antes de resgatar a oferta, olhe prazo, disponibilidade e região atendida. Um desconto ótimo perde força se chegar depois do aniversário ou da data especial.'],
            ],
            'coupon_box' => brand_coupon_box(
                'Lojas REDE',
                'Presentes de beleza com oferta ativa',
                'Veja cupons e promoções da Lojas REDE antes de fechar o presente.'
            ),
            'tip' => 'Para presente, preço importa, mas prazo e troca também contam bastante.',
        ],
        [
            'slug' => 'cozinha-planejada-itatiaia-com-cupom',
            'category' => 'Casa e Utensílios',
            'title' => 'Itatiaia: como planejar a compra de cozinha e móveis usando cupom',
            'summary' => 'Veja o que observar antes de comprar armários, balcões e móveis para casa.',
            'intro' => 'Comprar móveis para cozinha pede mais calma do que uma compra comum. Medida, cor, material, prazo e montagem importam muito. Se aparecer cupom da Itatiaia, ele pode ajudar, mas a escolha precisa começar pelo espaço da sua casa.',
            'sections' => [
                ['title' => 'Meça antes de olhar promoção', 'body' => 'Armário bonito não resolve se não couber. Tire medidas de parede, pia, tomadas e circulação. Depois disso, compare modelos e veja qual oferta faz sentido para o seu ambiente.'],
                ['title' => 'Observe módulos e composição', 'body' => 'Muitas cozinhas são vendidas por módulos. Confira se a oferta inclui todos os itens que você espera, como aéreo, balcão, paneleiro ou nichos. O cupom deve entrar no conjunto certo.'],
                ['title' => 'Frete e montagem mudam o valor final', 'body' => 'Móvel costuma ter frete mais sensível. Antes de decidir, simule entrega, prazo e necessidade de montagem. O desconto real é o preço final depois de tudo isso.'],
            ],
            'coupon_box' => brand_coupon_box(
                'Itatiaia',
                'Vai mexer na cozinha?',
                'Confira ofertas da Itatiaia e veja se existe cupom ativo para melhorar o valor final.'
            ),
            'tip' => 'Para móveis, economizar bem é juntar preço, medida correta e entrega viável.',
        ],
        [
            'slug' => 'armario-de-cozinha-itatiaia-como-escolher',
            'category' => 'Casa e Utensílios',
            'title' => 'Armário de cozinha Itatiaia: como escolher sem errar na compra',
            'summary' => 'Uma explicação rápida para comparar tamanho, acabamento, portas e espaço interno.',
            'intro' => 'Armário de cozinha precisa ser bonito, mas também precisa funcionar no dia a dia. Antes de usar uma oferta da Itatiaia, vale pensar no que você guarda, no espaço disponível e na rotina da casa.',
            'sections' => [
                ['title' => 'Pense no que vai dentro', 'body' => 'Panelas grandes, potes, pratos, copos e mantimentos ocupam espaços diferentes. Escolha o armário olhando para sua rotina, não apenas para a foto do produto.'],
                ['title' => 'Confira acabamento e material', 'body' => 'Cozinha tem umidade, gordura e limpeza frequente. Veja informações de material, puxadores, dobradiças e recomendações de conservação antes de fechar a compra.'],
                ['title' => 'Cupom entra depois da escolha certa', 'body' => 'Depois de confirmar modelo, medida e entrega, aplique o cupom. Se o desconto apareceu e o preço final ficou bom, aí sim a oferta ficou interessante.'],
            ],
            'coupon_box' => brand_coupon_box(
                'Itatiaia',
                'Veja ofertas da Itatiaia para cozinha',
                'Use os cupons ativos para comparar melhor antes de comprar armários e móveis.'
            ),
            'tip' => 'Evite comprar móvel só pela pressa da promoção. Medida errada sai caro.',
        ],
        [
            'slug' => 'moveis-para-casa-itatiaia-o-que-comparar',
            'category' => 'Casa e Utensílios',
            'title' => 'Móveis para casa: o que comparar antes de aproveitar cupom da Itatiaia',
            'summary' => 'Entenda como olhar medidas, entrega e necessidade real antes de comprar.',
            'intro' => 'Promoção de móvel chama atenção porque o valor costuma ser mais alto. Justamente por isso, um cupom da Itatiaia pode representar uma boa economia, desde que a compra esteja bem planejada.',
            'sections' => [
                ['title' => 'Compare com o ambiente real', 'body' => 'Veja altura, largura, profundidade e área de abertura das portas. Também confira se o móvel combina com o que já existe na casa.'],
                ['title' => 'Veja avaliações e fotos de compradores', 'body' => 'Comentários ajudam a entender acabamento, cor real e dificuldade de montagem. Eles também mostram se o produto costuma chegar bem embalado.'],
                ['title' => 'Calcule o total com entrega', 'body' => 'Em móveis, frete pode pesar. Teste o cupom no carrinho e compare o total com outras opções antes de finalizar.'],
            ],
            'coupon_box' => brand_coupon_box(
                'Itatiaia',
                'Ofertas da Itatiaia para mobiliar melhor',
                'Confira cupons e oportunidades ativas antes de fechar sua compra.'
            ),
            'tip' => 'Compra grande merece carrinho revisado: medida, entrega, montagem e desconto.',
        ],
        [
            'slug' => 'brinox-utensilios-de-cozinha-que-facilitam-a-rotina',
            'category' => 'Casa e Utensílios',
            'title' => 'Brinox: utensílios de cozinha que facilitam a rotina e podem sair com desconto',
            'summary' => 'Veja como escolher panelas, formas, potes e acessórios usando ofertas da Brinox.',
            'intro' => 'A Brinox aparece em compras de casa e cozinha porque reúne utensílios que ajudam no preparo, na organização e na mesa. Quando tem cupom ativo, é uma boa chance de trocar peças antigas ou completar a cozinha.',
            'sections' => [
                ['title' => 'Priorize o que você usa toda semana', 'body' => 'Panela boa, assadeira, escorredor, talheres e potes úteis aparecem na rotina. Antes de comprar itens diferentes, resolva primeiro o que melhora seu dia a dia de verdade.'],
                ['title' => 'Material importa', 'body' => 'Inox, alumínio, antiaderente e plástico têm usos diferentes. Leia a descrição e veja cuidados de limpeza, compatibilidade com fogão e resistência ao calor.'],
                ['title' => 'Cupom ajuda em kits', 'body' => 'Kits de cozinha podem valer a pena quando você precisa de várias peças. Aplique o cupom e compare o preço unitário para saber se a oferta ficou boa.'],
            ],
            'coupon_box' => brand_coupon_box(
                'Brinox',
                'Vai equipar a cozinha?',
                'Veja ofertas da Brinox e teste os cupons ativos antes de comprar.'
            ),
            'tip' => 'Utensílio bom é aquele que sai do armário. Compre pensando na rotina, não só na vitrine.',
        ],
        [
            'slug' => 'panelas-brinox-como-escolher',
            'category' => 'Casa e Utensílios',
            'title' => 'Panelas Brinox: como escolher melhor antes de usar cupom',
            'summary' => 'Entenda tamanho, material e tipo de uso antes de aproveitar uma oferta.',
            'intro' => 'Comprar panela parece simples, mas muda bastante conforme o tipo de preparo. Uma panela para arroz não resolve a mesma coisa que uma frigideira, uma caçarola ou uma panela de pressão.',
            'sections' => [
                ['title' => 'Olhe o tamanho da sua rotina', 'body' => 'Quem cozinha para uma pessoa precisa de medidas diferentes de uma família grande. Tamanho certo economiza gás, espaço e tempo na limpeza.'],
                ['title' => 'Veja se o material combina com seu fogão', 'body' => 'Alguns produtos têm restrições de uso. Confira se funciona no seu fogão e se exige algum cuidado especial com antiaderente ou inox.'],
                ['title' => 'Aproveite desconto sem duplicar peças', 'body' => 'Cupom é ótimo para trocar uma panela antiga ou completar um conjunto. Evite comprar uma peça quase igual à que você já tem só porque está em promoção.'],
            ],
            'coupon_box' => brand_coupon_box(
                'Brinox',
                'Ofertas Brinox para renovar panelas',
                'Confira cupons ativos e veja se o desconto entra no item que você procura.'
            ),
            'tip' => 'Antes de comprar, pense em onde a panela será guardada. Espaço também faz parte da escolha.',
        ],
        [
            'slug' => 'organizar-a-cozinha-com-produtos-brinox',
            'category' => 'Casa e Utensílios',
            'title' => 'Como organizar a cozinha com produtos Brinox sem gastar além da conta',
            'summary' => 'Ideias simples para usar organizadores, potes e acessórios com mais inteligência.',
            'intro' => 'Cozinha organizada economiza tempo. Você encontra o que precisa, evita comprar item repetido e usa melhor o espaço. A Brinox costuma ter produtos úteis para essa missão, principalmente quando aparecem ofertas.',
            'sections' => [
                ['title' => 'Comece por gavetas e bancada', 'body' => 'Gavetas bagunçadas e bancada cheia atrapalham a rotina. Organizadores, potes e suportes podem ajudar, mas só se resolverem um problema real do espaço.'],
                ['title' => 'Padronizar pode economizar', 'body' => 'Potes do mesmo formato empilham melhor. Utensílios com função clara evitam acúmulo. Na hora do cupom, prefira itens que conversam entre si.'],
                ['title' => 'Veja o custo do conjunto', 'body' => 'Às vezes, comprar duas ou três peças coordenadas sai melhor que comprar vários itens soltos depois. Teste o cupom no carrinho e compare o valor final.'],
            ],
            'coupon_box' => brand_coupon_box(
                'Brinox',
                'Produtos Brinox para deixar a cozinha mais prática',
                'Veja ofertas ativas e use cupom quando estiver disponível.'
            ),
            'tip' => 'Organização boa deixa a cozinha mais fácil de usar, não apenas mais bonita na foto.',
        ],
        [
            'slug' => 'hostinger-vale-a-pena-para-criar-site',
            'category' => 'Serviços',
            'title' => 'Hostinger vale a pena para criar um site? Veja quando usar cupom',
            'summary' => 'Entenda hospedagem, domínio, e-mail e recursos básicos antes de contratar.',
            'intro' => 'Quem quer colocar um site no ar costuma encontrar a Hostinger nas buscas por hospedagem. O cupom pode reduzir o preço inicial, mas a escolha precisa considerar o tipo de projeto que você quer publicar.',
            'sections' => [
                ['title' => 'Site simples pede estrutura simples', 'body' => 'Portfólio, página institucional, blog pequeno e landing page geralmente não precisam de uma estrutura complexa. O importante é ter estabilidade, painel fácil e suporte quando algo travar.'],
                ['title' => 'Confira domínio, e-mail e renovação', 'body' => 'Antes de contratar, veja se o plano inclui domínio, e-mail, SSL e backup. Também olhe o valor de renovação, porque o primeiro ano pode ter desconto maior.'],
                ['title' => 'Use o cupom pensando no total', 'body' => 'Aplique o cupom e compare o valor final do período contratado. Se o plano resolve seu projeto e a renovação cabe no bolso, a oferta faz mais sentido.'],
            ],
            'coupon_box' => brand_coupon_box(
                'Hostinger',
                'Vai criar um site?',
                'Veja ofertas da Hostinger e resgate o desconto pelo link correto.'
            ),
            'tip' => 'Hospedagem barata só é boa se o projeto continuar funcionando bem depois da compra.',
        ],
        [
            'slug' => 'como-escolher-hospedagem-na-hostinger',
            'category' => 'Serviços',
            'title' => 'Como escolher hospedagem na Hostinger sem pagar por recurso que não usa',
            'summary' => 'Veja como comparar planos de hospedagem antes de resgatar uma promoção.',
            'intro' => 'Hospedagem tem nomes parecidos e diferenças importantes. Antes de usar um cupom da Hostinger, vale entender se você precisa de algo básico, intermediário ou mais robusto.',
            'sections' => [
                ['title' => 'Defina o tamanho do projeto', 'body' => 'Um site de apresentação exige menos que uma loja virtual com muitos acessos. Quanto mais simples o projeto, maior a chance de um plano inicial atender bem.'],
                ['title' => 'Pense em crescimento', 'body' => 'Se você pretende criar blog, páginas de SEO ou área de clientes, escolha um plano que não fique apertado rápido demais. Mas cuidado para não comprar estrutura exagerada logo no começo.'],
                ['title' => 'Compare suporte e facilidade', 'body' => 'Painel, instalador, SSL e backup podem poupar tempo. Cupom reduz preço, mas a experiência no dia a dia também importa.'],
            ],
            'coupon_box' => brand_coupon_box(
                'Hostinger',
                'Ofertas da Hostinger para tirar o site do papel',
                'Confira os cupons ativos e veja qual plano combina com seu projeto.'
            ),
            'tip' => 'O melhor plano é o que resolve hoje e permite crescer sem susto amanhã.',
        ],
        [
            'slug' => 'dominio-email-e-site-com-hostinger',
            'category' => 'Serviços',
            'title' => 'Domínio, e-mail e site: como montar o básico com Hostinger e cupom',
            'summary' => 'Um passo a passo leve para quem quer presença online sem complicação.',
            'intro' => 'Para muita gente, colocar um negócio online começa por três coisas: domínio, e-mail profissional e um site que explique o que a empresa faz. A Hostinger costuma aparecer como opção para juntar essas peças em um só lugar.',
            'sections' => [
                ['title' => 'Domínio é o endereço da marca', 'body' => 'Escolha um domínio fácil de falar, escrever e lembrar. Evite nomes longos demais e confira se ele combina com a marca nas redes sociais.'],
                ['title' => 'E-mail passa mais confiança', 'body' => 'Um e-mail com domínio próprio costuma parecer mais profissional que contas genéricas. Veja se o plano inclui caixas de e-mail ou se isso será contratado à parte.'],
                ['title' => 'Site precisa ser claro', 'body' => 'Não precisa começar gigante. Uma página com serviços, contato, localização e prova de confiança já pode ajudar. Use o cupom para reduzir o custo inicial, sem perder de vista a renovação.'],
            ],
            'coupon_box' => brand_coupon_box(
                'Hostinger',
                'Comece seu site gastando menos',
                'Veja ofertas ativas da Hostinger e resgate pelo tracking correto.'
            ),
            'tip' => 'Antes de pagar, anote custo inicial, renovação e o que está incluso no plano.',
        ],
        [
            'slug' => 'malwee-roupas-basicas-com-cupom',
            'category' => 'Moda',
            'title' => 'Malwee: roupas básicas que combinam com cupom e compra inteligente',
            'summary' => 'Veja como escolher camisetas, peças confortáveis e itens de uso recorrente.',
            'intro' => 'A Malwee é uma marca conhecida por peças do dia a dia. Quando aparece cupom, pode ser um bom momento para renovar camisetas, roupas confortáveis e itens básicos que realmente entram na rotina.',
            'sections' => [
                ['title' => 'Básico bom repete muito', 'body' => 'Camiseta, blusa, moletom leve e peças confortáveis são boas candidatas a cupom porque você usa várias vezes. O desconto faz mais sentido quando a peça não fica parada no armário.'],
                ['title' => 'Confira tecido e tamanho', 'body' => 'Antes de comprar online, veja composição, tabela de medidas e avaliações. Em roupa, preço bom não compensa se o tamanho não servir ou o tecido não agradar.'],
                ['title' => 'Monte carrinho por necessidade', 'body' => 'Separe o que você precisa renovar e só depois aplique o cupom. Assim, a promoção ajuda a economizar, sem transformar a compra em impulso.'],
            ],
            'coupon_box' => brand_coupon_box(
                'Malwee',
                'Vai renovar peças básicas?',
                'Confira ofertas da Malwee e veja se tem cupom ativo para o seu carrinho.'
            ),
            'tip' => 'Roupa básica boa é aquela que você usa sem pensar muito. Priorize conforto e combinação.',
        ],
        [
            'slug' => 'moda-infantil-malwee-com-desconto',
            'category' => 'Moda Infantil',
            'title' => 'Moda infantil Malwee: como comprar com desconto sem errar no tamanho',
            'summary' => 'Dicas para escolher roupas infantis olhando conforto, crescimento e uso real.',
            'intro' => 'Roupa infantil entra na lista de compras com frequência porque criança cresce rápido, mancha roupa e muda de fase. Quando tem oferta da Malwee, vale aproveitar com atenção ao tamanho e ao uso.',
            'sections' => [
                ['title' => 'Compre pensando no conforto', 'body' => 'Tecido macio, modelagem fácil e roupa que permite brincar costumam valer mais que peça muito elaborada. Para criança, praticidade pesa bastante.'],
                ['title' => 'Tamanho merece cuidado extra', 'body' => 'Confira tabela de medidas e, se estiver entre dois tamanhos, pense no tempo de uso. Comprar um pouco maior pode fazer sentido, mas exagerar pode deixar a peça parada.'],
                ['title' => 'Cupom ajuda em reposição', 'body' => 'Camisetas, bermudas, calças e roupas para escola ou passeio são compras recorrentes. Use o cupom para reduzir esse gasto que volta várias vezes ao ano.'],
            ],
            'coupon_box' => brand_coupon_box(
                'Malwee',
                'Ofertas Malwee para moda infantil',
                'Veja cupons ativos e resgate a promoção antes de fechar a compra.'
            ),
            'tip' => 'Em moda infantil, pense no uso real: escola, passeio, frio, calor e facilidade de lavar.',
        ],
        [
            'slug' => 'malwee-como-montar-carrinho-melhor',
            'category' => 'Moda',
            'title' => 'Como montar um carrinho melhor na Malwee antes de aplicar cupom',
            'summary' => 'Aprenda a combinar peças, evitar compras repetidas e testar o desconto no fim.',
            'intro' => 'Comprar roupa online fica mais fácil quando o carrinho tem lógica. Em vez de escolher peças soltas só porque estão em promoção, monte combinações que você realmente vai usar.',
            'sections' => [
                ['title' => 'Escolha cores fáceis de combinar', 'body' => 'Peças neutras e cores que conversam com o que você já tem aumentam as chances de uso. O desconto fica melhor quando a roupa entra em vários looks.'],
                ['title' => 'Evite repetir o mesmo papel', 'body' => 'Antes de comprar mais uma camiseta parecida, veja se falta uma calça, uma blusa de frio ou uma peça para trabalho e passeio. Cupom bom também ajuda a equilibrar o guarda-roupa.'],
                ['title' => 'Teste o desconto antes de pagar', 'body' => 'Aplique o cupom, confira valor final, frete, prazo e política de troca. Se tudo encaixar, a compra fica mais tranquila.'],
            ],
            'coupon_box' => brand_coupon_box(
                'Malwee',
                'Cupons Malwee para comprar melhor',
                'Veja ofertas ativas e confira o desconto antes de finalizar.'
            ),
            'tip' => 'Carrinho bom não é o maior. É aquele que resolve melhor o que você precisa vestir.',
        ],
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
                    'body' => 'Antes de listar paises, vale fazer uma observacao importante: nao existe um ranking unico e perfeito de quem mais consome comida chinesa no mundo. O consumo pode ser medido por numero de restaurantes, tamanho da comunidade chinesa, pedidos em aplicativos, buscas na internet, faturamento ou presenca cultural. Cada metodo muda um pouco a lista. Ainda assim, alguns paises aparecem com frequencia quando o assunto e alcance global da comida chinesa fora da propria China. Como este conteúdo olha fora do Japao, a lista considera mercados onde a culinaria chinesa tem presenca forte sem usar o Japao como exemplo principal.',
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
            'summary' => 'Uma explicação rápida para renovar assinatura, comprar créditos e evitar gasto por impulso.',
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

    $guides = array_merge($guides, generated_brand_guides());

    return array_map('ensure_guide_depth', $guides);
}

function generated_brand_guides(): array
{
    $brands = [
        ['store' => 'BALAROTI', 'category' => 'Casa e Utensílios', 'item' => 'materiais para reforma', 'scene' => 'arrumar a casa, trocar acabamento ou resolver uma obra pequena', 'care' => 'medidas, voltagem, garantia, retirada e frete'],
        ['store' => 'Kidy Calçados', 'category' => 'Moda Infantil', 'item' => 'calçados infantis', 'scene' => 'comprar tênis, sandália ou sapato para criança que cresce rápido', 'care' => 'numeração, conforto, solado, troca e rotina da criança'],
        ['store' => 'Iodice', 'category' => 'Moda Feminina', 'item' => 'moda feminina', 'scene' => 'renovar peças de trabalho, passeio ou ocasiões especiais', 'care' => 'tecido, caimento, tabela de medidas e combinações com o armário'],
        ['store' => 'Cirurgica Sinete', 'category' => 'Saúde e Beleza', 'item' => 'produtos de saúde e cuidado', 'scene' => 'comprar itens de cuidado, apoio e bem-estar para a rotina', 'care' => 'indicação correta, especificação, prazo e necessidade real'],
        ['store' => 'Homedock', 'category' => 'Casa e Utensílios', 'item' => 'itens para casa', 'scene' => 'deixar um ambiente mais prático sem encher a casa de coisa inútil', 'care' => 'tamanho, material, uso no dia a dia e facilidade de limpar'],
        ['store' => 'Amakha Paris', 'category' => 'Saúde e Beleza', 'item' => 'beleza e perfumaria', 'scene' => 'escolher fragrâncias, cuidados pessoais ou presentes de beleza', 'care' => 'perfil de uso, validade, sensibilidade e reputação da oferta'],
        ['store' => 'Lojão dos Esportes', 'category' => 'Esportes', 'item' => 'artigos esportivos', 'scene' => 'voltar a treinar ou completar equipamento para uma modalidade', 'care' => 'tamanho, resistência, material, segurança e frequência de uso'],
        ['store' => 'FOM', 'category' => 'Casa e Utensílios', 'item' => 'conforto para casa e viagem', 'scene' => 'melhorar descanso, trabalho ou viagem com acessórios confortáveis', 'care' => 'ergonomia, tecido, lavagem, tamanho e portabilidade'],
        ['store' => 'Electrolux', 'category' => 'Casa e Utensílios', 'item' => 'eletrodomésticos', 'scene' => 'trocar um aparelho importante da casa ou comprar algo que economize tempo', 'care' => 'voltagem, consumo, assistência, dimensão e entrega'],
        ['store' => 'Drogasmil', 'category' => 'Saúde e Beleza', 'item' => 'farmácia e cuidados pessoais', 'scene' => 'repor produtos de farmácia, higiene e autocuidado', 'care' => 'validade, quantidade, concentração, entrega e regra de medicamento'],
        ['store' => 'Casa do Fitness', 'category' => 'Esportes', 'item' => 'equipamentos fitness', 'scene' => 'montar treino em casa sem gastar como academia profissional', 'care' => 'espaço disponível, carga, segurança, montagem e constância'],
        ['store' => 'BioVittare Farmácia de Manipulação', 'category' => 'Saúde e Beleza', 'item' => 'manipulados e bem-estar', 'scene' => 'organizar uma compra de manipulados ou cuidados personalizados', 'care' => 'receita, fórmula, orientação profissional, prazo e conservação'],
        ['store' => 'Drogaria Rosário', 'category' => 'Saúde e Beleza', 'item' => 'farmácia e higiene', 'scene' => 'comprar produtos de saúde, higiene e beleza de uso recorrente', 'care' => 'validade, embalagem, frete, retirada e comparação por unidade'],
        ['store' => 'Maria Valentina', 'category' => 'Moda Feminina', 'item' => 'moda feminina', 'scene' => 'montar looks femininos para trabalho, eventos e dia a dia', 'care' => 'caimento, tecido, ocasião, troca e peças que combinam entre si'],
        ['store' => 'Dona Coelha', 'category' => 'Kids', 'item' => 'produtos infantis', 'scene' => 'comprar presentes, itens de rotina ou produtos para criança', 'care' => 'idade indicada, segurança, material, troca e prazo de entrega'],
        ['store' => 'Ferramentas Kennedy', 'category' => 'Casa e Utensílios', 'item' => 'ferramentas e reforma', 'scene' => 'comprar ferramenta para manutenção, obra ou uso profissional', 'care' => 'potência, compatibilidade, garantia, marca e frequência de uso'],
        ['store' => 'Desconto Aqui', 'category' => 'Compras', 'item' => 'ofertas variadas', 'scene' => 'garimpar oportunidades sem uma loja única em mente', 'care' => 'origem da oferta, regra, preço final e confiabilidade'],
        ['store' => 'DeÔnibus', 'category' => 'Viagem e Transporte', 'item' => 'passagens de ônibus', 'scene' => 'planejar uma viagem pagando menos na passagem', 'care' => 'horário, rodoviária, bagagem, cancelamento e assento'],
        ['store' => 'Coza', 'category' => 'Casa e Utensílios', 'item' => 'organização para casa', 'scene' => 'organizar cozinha, banheiro, lavanderia ou escritório', 'care' => 'medidas, encaixe, material, cor e facilidade de limpar'],
        ['store' => 'Cicatrissim', 'category' => 'Saúde e Beleza', 'item' => 'cuidados com a pele', 'scene' => 'comprar dermocosméticos e produtos de cuidado com a pele', 'care' => 'tipo de pele, composição, orientação, frequência e sensibilidade'],
        ['store' => 'Kappesberg', 'category' => 'Casa e Utensílios', 'item' => 'móveis para casa', 'scene' => 'comprar móvel para quarto, cozinha, sala ou escritório', 'care' => 'medidas, montagem, entrega, material e espaço de circulação'],
        ['store' => 'Casa das Alianças', 'category' => 'Joias e Acessórios', 'item' => 'alianças e joias', 'scene' => 'escolher aliança, presente ou acessório com significado', 'care' => 'medida, material, gravação, garantia e prazo para a data'],
        ['store' => 'Lauri Esporte', 'category' => 'Esportes', 'item' => 'produtos esportivos', 'scene' => 'comprar roupa, acessório ou equipamento para treino', 'care' => 'tamanho, tecido, modalidade, durabilidade e troca'],
        ['store' => 'Livrarias Curitiba', 'category' => 'Educação e Cultura', 'item' => 'livros e papelaria', 'scene' => 'comprar livros, materiais de estudo ou presentes criativos', 'care' => 'edição, autor, prazo, frete e lista de leitura'],
        ['store' => 'Anhanguera Ferramentas', 'category' => 'Casa e Utensílios', 'item' => 'ferramentas', 'scene' => 'resolver reparos em casa ou equipar uma oficina pequena', 'care' => 'tipo de uso, potência, acessórios, segurança e garantia'],
    ];

    $guides = [];

    foreach ($brands as $brand) {
        $guides[] = brand_generated_guide(
            $brand,
            'por-que-vale-olhar',
            $brand['store'] . ': por que vale olhar ofertas de ' . $brand['item'] . ' antes de comprar',
            'Entenda quando uma promoção da ' . $brand['store'] . ' pode ajudar e o que observar antes de fechar.',
            'Tem compra que parece pequena, mas pesa no fim do mês. Também tem compra grande que só fica tranquila quando a gente compara com calma. A ' . $brand['store'] . ' entra nessa conversa porque pode aparecer com oportunidades de ' . $brand['item'] . ', e esse tipo de oferta merece ser olhado com atenção, não com pressa.'
        );

        $guides[] = brand_generated_guide(
            $brand,
            'compra-melhor',
            $brand['store'] . ': como montar uma compra melhor de ' . $brand['item'],
            'Veja uma forma simples de escolher melhor, comparar o total e aproveitar cupons da ' . $brand['store'] . '.',
            'Uma boa compra não começa no checkout. Ela começa quando você entende o que procura, separa o que é necessidade do que é impulso e só depois olha desconto. Em ofertas da ' . $brand['store'] . ', esse cuidado ajuda a transformar promoção em economia real.'
        );
    }

    return $guides;
}

function brand_generated_guide(array $brand, string $angle, string $title, string $summary, string $intro): array
{
    return [
        'slug' => guide_slug($brand['store'] . '-' . $angle),
        'category' => $brand['category'],
        'title' => $title,
        'summary' => $summary,
        'intro' => $intro,
        'sections' => brand_editorial_sections($brand, $angle),
        'coupon_box' => brand_coupon_box(
            $brand['store'],
            'Ofertas da ' . $brand['store'] . ' para conferir agora',
            'Veja cupons e promoções ativas da ' . $brand['store'] . ' e resgate pelo caminho certo.'
        ),
        'tip' => 'Dica: antes de finalizar, confira a regra no parceiro e veja se o desconto entrou no total. O melhor cupom é o que melhora uma compra que já fazia sentido.',
    ];
}

function guide_slug(string $value): string
{
    return trim((string) preg_replace('/[^a-z0-9]+/', '-', normalize_search_text($value)), '-') ?: 'conteudo';
}

function ensure_guide_depth(array $guide): array
{
    if (guide_is_excluded_from_depth($guide)) {
        return $guide;
    }

    $store = $guide['coupon_box']['store'] ?? 'Oferto';
    $category = $guide['category'] ?? 'Compras';
    $item = strtolower($category);
    $slug = $guide['slug'] ?? '';
    $guide['sections'] = guide_unique_sections($guide['sections'] ?? []);

    for ($attempt = 0; $attempt < 3 && guide_word_count($guide) < 600; $attempt++) {
        $before = count($guide['sections']);
        $guide['sections'] = guide_unique_sections(array_merge(
            $guide['sections'],
            article_depth_sections($store, $category, $item, $guide['title'] ?? 'Como economizar melhor', $slug . '-' . $attempt)
        ));

        if (count($guide['sections']) === $before) {
            break;
        }
    }

    return $guide;
}

function guide_unique_sections(array $sections): array
{
    $unique = [];
    $seenTitles = [];
    $seenBodies = [];

    foreach ($sections as $section) {
        $title = trim((string) ($section['title'] ?? ''));
        $body = trim((string) ($section['body'] ?? ''));
        if ($title === '' || $body === '') {
            continue;
        }

        $titleKey = normalize_search_text($title);
        $bodyKey = normalize_search_text(substr($body, 0, 220));
        if (isset($seenTitles[$titleKey]) || isset($seenBodies[$bodyKey])) {
            continue;
        }

        $seenTitles[$titleKey] = true;
        $seenBodies[$bodyKey] = true;
        $unique[] = $section;
    }

    return $unique;
}

function guide_is_excluded_from_depth(array $guide): bool
{
    $store = strtolower($guide['coupon_box']['store'] ?? '');
    $category = strtolower($guide['category'] ?? '');
    $title = strtolower($guide['title'] ?? '');

    return strpos($store, 'shopee') !== false
        || strpos($category, 'shopee') !== false
        || strpos($title, 'china in box') !== false
        || strpos($title, 'yakisoba') !== false
        || strpos($title, 'rolinho primavera') !== false
        || strpos($title, 'comida chinesa') !== false;
}

function guide_word_count(array $guide): int
{
    $text = implode(' ', array_filter([
        $guide['title'] ?? '',
        $guide['summary'] ?? '',
        $guide['intro'] ?? '',
        $guide['tip'] ?? '',
        $guide['coupon_box']['title'] ?? '',
        $guide['coupon_box']['body'] ?? '',
    ]));

    foreach (($guide['sections'] ?? []) as $section) {
        $text .= ' ' . ($section['title'] ?? '') . ' ' . ($section['body'] ?? '');
    }

    preg_match_all('/\b[\p{L}\p{N}]+\b/u', $text, $matches);

    return count($matches[0]);
}

function brand_editorial_sections(array $brand, string $angle): array
{
    if ($angle === 'compra-melhor') {
        return [
            [
                'title' => 'Comece pela situação que você quer resolver',
                'body' => 'O jeito mais simples de comprar melhor na ' . $brand['store'] . ' é começar pela situação real: ' . $brand['scene'] . '. Quando a busca parte de uma necessidade concreta, fica mais fácil separar oferta boa de distração. Em vez de abrir várias opções e colocar tudo no carrinho, pense no que precisa mudar na sua rotina depois da compra. O produto vai resolver um problema? Vai substituir algo que já acabou? Vai ser usado com frequência? Se a resposta for sim, o desconto trabalha a seu favor. Se a resposta for não, talvez seja só uma chamada bonita disputando sua atenção.',
            ],
            [
                'title' => 'Monte o carrinho como se o cupom ainda não existisse',
                'body' => 'Essa é uma regra boa para quase toda promoção: primeiro escolha o que você compraria mesmo sem desconto. Depois, teste o cupom. Isso evita aquela compra inflada, em que a pessoa tenta alcançar valor mínimo colocando itens desnecessários. Na prática, vale montar uma lista curta, comparar modelos e deixar no carrinho só o que tem uso claro. Quando o cupom entra no fim, ele reduz o custo de uma decisão que já estava madura. Esse processo parece menos emocionante que clicar rápido, mas costuma economizar mais dinheiro.',
            ],
            [
                'title' => 'Olhe os detalhes que mudam a experiência',
                'body' => 'Em ' . strtolower($brand['category']) . ', a diferença entre compra boa e compra frustrante quase sempre está nos detalhes. Antes de resgatar uma oferta da ' . $brand['store'] . ', confira ' . $brand['care'] . '. Esses pontos não aparecem com o mesmo destaque que o preço, mas podem mudar tudo. Um item barato com medida errada, prazo ruim ou regra confusa pode sair caro. Já uma oferta um pouco menor, mas com compra mais segura, troca simples e entrega viável, pode ser a melhor escolha.',
            ],
            [
                'title' => 'Use comparação para não depender da promessa da promoção',
                'body' => 'Toda loja quer mostrar a oferta do jeito mais atraente possível. O papel de quem compra é olhar além da chamada. Pesquise produtos parecidos, veja se o preço já esteve mais baixo, confira avaliações recentes e compare o valor final com frete ou retirada. Quando você faz isso, o cupom deixa de ser uma promessa solta e vira uma parte da conta. Se o total final realmente ficou bom, a promoção ganha força. Se o desconto só parece grande porque o preço base estava alto, melhor esperar outra oportunidade.',
            ],
            [
                'title' => 'Transforme a promoção em hábito de economia',
                'body' => 'A melhor parte de acompanhar cupons não é comprar mais; é comprar com mais consciência. Se a ' . $brand['store'] . ' aparece com frequência para você, vale observar quais tipos de oferta se repetem, quais produtos costumam compensar e em que momento o desconto fica mais interessante. Com o tempo, você passa a reconhecer o que é urgência real e o que é só pressão de vitrine. O Oferto ajuda nessa etapa reunindo ofertas ativas por marca, para que você veja o que está disponível sem sair abrindo páginas aleatórias.',
            ],
        ];
    }

    return [
        [
            'title' => 'Por que essa marca pode entrar no seu radar',
            'body' => 'A ' . $brand['store'] . ' pode ser interessante para quem está pensando em ' . $brand['scene'] . '. O ponto não é comprar só porque apareceu uma oferta, e sim perceber quando a marca resolve uma necessidade que já estava no seu caminho. Muitas compras do dia a dia acabam sendo feitas com pressa, no primeiro resultado ou no primeiro anúncio. Quando você para alguns minutos para comparar, o cupom deixa de ser um detalhe e vira uma ferramenta para melhorar o preço final.',
        ],
        [
            'title' => 'Oferta boa tem contexto',
            'body' => 'Uma promoção de ' . $brand['item'] . ' não vale a mesma coisa para todo mundo. Para uma pessoa, pode ser compra urgente. Para outra, pode ser apenas vontade criada pela vitrine. Antes de resgatar qualquer oportunidade da ' . $brand['store'] . ', pense no contexto: você precisa disso agora, está repondo algo, está comprando para uma data específica ou só achou barato? Essa pergunta muda a qualidade da decisão. O desconto certo aparece quando necessidade, preço e momento combinam.',
        ],
        [
            'title' => 'Detalhes pequenos evitam arrependimento',
            'body' => 'O preço chama atenção, mas a compra é decidida nos detalhes. Em ofertas da ' . $brand['store'] . ', observe ' . $brand['care'] . '. Também vale ler a descrição inteira e conferir se existe regra por região, categoria, valor mínimo ou forma de pagamento. Esse cuidado é ainda mais importante quando o produto tem tamanho, especificação técnica, prazo apertado ou uso recorrente. Economizar não é apenas pagar menos; é pagar menos por algo que chega certo, funciona e será usado.',
        ],
        [
            'title' => 'Quando o cupom realmente melhora a compra',
            'body' => 'Cupom bom não é necessariamente o maior desconto. Às vezes, uma porcentagem menor em um item certo vale mais que uma chamada enorme em uma compra cheia de restrições. O ideal é comparar o valor antes e depois, somar frete, observar prazo e confirmar se o benefício entrou no carrinho. Quando a oferta for de resgate direto, use o botão indicado para seguir pelo caminho correto. Assim você evita perder desconto e ainda ajuda a manter a curadoria de ofertas funcionando.',
        ],
        [
            'title' => 'Como aproveitar sem virar compra por impulso',
            'body' => 'Uma boa estratégia é salvar a oferta, respirar e voltar para ela depois de alguns minutos. Se a compra ainda fizer sentido, siga. Se a vontade passou, provavelmente era impulso. Isso vale para ' . $brand['item'] . ' e para qualquer outra categoria. O Oferto existe justamente para facilitar essa escolha: reunir cupons, promoções e oportunidades em um lugar mais organizado, com descrição, validade e botão de resgate. A decisão final continua sendo sua, mas com menos ruído e mais informação.',
        ],
    ];
}

function article_depth_sections(string $store, string $category, string $item, string $title, string $slug): array
{
    $variants = [
        [
            [
                'title' => 'O que torna essa compra interessante',
                'body' => 'Toda boa economia começa com uma pergunta simples: isso melhora alguma coisa na sua rotina ou só parece barato agora? Em ' . strtolower($category) . ', essa diferença é importante porque o desconto pode aparecer junto de muitas opções parecidas. O melhor caminho é olhar para o uso real, comparar o preço final e entender se o cupom combina com o que você estava procurando. Quando essas peças se encaixam, a promoção deixa de ser impulso e vira uma escolha mais inteligente.',
            ],
            [
                'title' => 'Como olhar além da chamada de desconto',
                'body' => 'A chamada de uma oferta costuma destacar o número mais bonito: porcentagem, frete, brinde ou validade curta. Só que a compra acontece no detalhe. Veja se existe valor mínimo, produto participante, região atendida, prazo de entrega e regra de pagamento. Se houver código, aplique antes de pagar. Se for resgate direto, siga pelo botão da oferta. Essa checagem evita frustração e ajuda você a saber se o desconto é real no seu carrinho.',
            ],
            [
                'title' => 'Por que comparar ainda vale a pena',
                'body' => 'Mesmo quando a marca parece ser a escolha óbvia, comparar ajuda. Veja produtos parecidos, leia avaliações recentes e confira se outra condição entrega mais valor. Às vezes, um preço um pouco maior com prazo melhor compensa. Em outros casos, esperar um cupom mais forte faz sentido. O segredo é não deixar que a urgência da promoção decida por você. Quem compara compra com mais calma e costuma errar menos.',
            ],
            [
                'title' => 'O papel do Oferto nessa decisão',
                'body' => 'O Oferto aproxima conteúdo e oferta para reduzir o tempo perdido em busca solta. Você entende melhor o tema, vê oportunidades relacionadas e segue para resgatar quando fizer sentido. Esse modelo é útil porque muita gente chega pela dúvida antes de chegar pela compra. Ao reunir informação, validade e chamada de ação, a página ajuda tanto quem está pesquisando quanto quem já está pronto para aproveitar o desconto.',
            ],
        ],
        [
            [
                'title' => 'A compra começa antes do carrinho',
                'body' => 'Comprar melhor não é apenas encontrar cupom. É entender o que você quer resolver, quanto pretende gastar e qual resultado espera depois da compra. Em ' . strtolower($category) . ', isso evita escolhas feitas no automático. Antes de clicar em resgatar, pense no produto, no uso, na frequência e no preço que faria sentido. Se a oferta aproxima você desse preço, ótimo. Se só empurra uma compra que não estava no plano, talvez seja melhor esperar.',
            ],
            [
                'title' => 'Preço final é mais importante que desconto anunciado',
                'body' => 'Um desconto bonito pode perder força quando entram frete, prazo, taxas ou regras escondidas. Por isso, sempre olhe o total no carrinho. O que importa é quanto você paga no fim, não apenas o destaque da vitrine. Quando possível, teste mais de uma combinação: produto único, kit, retirada, entrega e forma de pagamento. Essa pequena comparação costuma revelar se a promoção é realmente boa.',
            ],
            [
                'title' => 'Use validade como alerta, não como pressão',
                'body' => 'Oferta com prazo curto pode ser útil, mas também pode acelerar uma compra ruim. Validade serve para você saber até quando pode aproveitar, não para decidir sem pensar. Se o item é necessário, a urgência ajuda. Se não é, respire. Em compras maiores ou recorrentes, acompanhar a marca por alguns dias pode mostrar padrões de preço e campanhas que voltam com frequência.',
            ],
            [
                'title' => 'Conteúdo também ajuda a economizar',
                'body' => 'Muita gente procura cupom só no último minuto, mas ler sobre a categoria antes pode evitar gasto desnecessário. Um texto bem feito ajuda a escolher tamanho, modelo, ocasião, regra ou prioridade. Depois disso, o cupom entra como acabamento da decisão. É essa combinação que o Oferto quer construir: informação simples, ofertas organizadas e caminhos claros para aproveitar sem depender de sorte.',
            ],
        ],
        [
            [
                'title' => 'Quando a oferta conversa com a vida real',
                'body' => 'Uma promoção fica mais forte quando conversa com uma situação concreta: repor algo, presentear alguém, resolver um problema em casa, cuidar da saúde, estudar, viajar ou melhorar um hábito. Sem esse contexto, qualquer desconto parece tentador. Com contexto, você filtra melhor. Em ' . strtolower($category) . ', vale perguntar se o produto ou serviço será usado logo e se o valor final está dentro do que você pretendia pagar.',
            ],
            [
                'title' => 'A regra precisa caber no seu pedido',
                'body' => 'Muitos cupons têm condições. Pode haver valor mínimo, categoria específica, loja participante, limite de uso ou validade curta. Isso não é necessariamente ruim; só precisa estar claro. Antes de finalizar, confira se a regra cabe no seu pedido. Se você precisa mudar demais o carrinho para o desconto funcionar, talvez a oferta esteja mandando na compra mais do que deveria.',
            ],
            [
                'title' => 'Promoção boa não precisa ser complicada',
                'body' => 'Quanto mais simples for entender a vantagem, melhor para o usuário. A oferta ideal deixa claro o que está sendo vendido, qual é o benefício e como resgatar. Se a página do parceiro abrir com outra condição, volte e compare. Esse cuidado protege seu tempo e evita a sensação de ter sido levado por uma chamada que parecia melhor do que era.',
            ],
            [
                'title' => 'Voltar depois também é estratégia',
                'body' => 'Nem sempre o melhor clique é o clique imediato. Salvar uma marca, voltar quando precisar e acompanhar novas ofertas pode gerar economia maior no longo prazo. Isso vale principalmente para compras recorrentes ou categorias em que você ainda está pesquisando. O Oferto ajuda a manter essas oportunidades organizadas, para que você não dependa de lembrar onde viu cada cupom.',
            ],
        ],
    ];

    $index = abs((int) crc32($slug . $title)) % count($variants);

    return $variants[$index];
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
