# Mapa de anuncios

Este projeto ja tem espacos preparados para Google Ad Manager, Ad Exchange ou deals vindos de DV360.

## Slots

| Slot | Posicao | Tamanho sugerido |
| --- | --- | --- |
| `cupons_topo_responsivo` | Depois das categorias, antes da lista de cupons | 970x250 desktop, 728x90 tablet, 320x100 mobile |
| `cupons_lateral_300x250` | Lateral desktop, abaixo do bloco de fidelizacao | 300x250 |
| `cupons_entre_lista_e_guias` | Depois da lista de cupons, antes dos guias SEO | 970x250 desktop, 728x90 tablet, 320x100 mobile |
| `guias_artigo_topo_responsivo` | Paginas de guia, entre o hero e o artigo | 970x250 desktop, 728x90 tablet, 320x100 mobile |
| `guias_lateral_300x250` | Lateral das paginas de guia | 300x250 |

## Blocos AdSense criados

Os placeholders `.inventory-slot` ja estao ligados ao helper `render_ad_slot()`.
Os IDs abaixo foram criados no painel do AdSense e configurados como fallback no codigo.

| Slot do site | Bloco AdSense | ID |
| --- | --- | --- |
| `v2_topo_responsivo` | Oferto Home Topo Responsivo | `5284508420` |
| `v2_entre_destaques_e_lista` | Oferto Home Meio Responsivo | `3971426759` |
| `v2_lateral_300x250` | Oferto Home Lateral 300x250 | `9321167801` |
| `v2_antes_dicas` | Oferto Home Meio Responsivo | `3971426759` |
| `blog_topo_responsivo` | Oferto Blog Topo Responsivo | `4796538786` |
| `guias_artigo_topo_responsivo` | Oferto Guia Topo Responsivo | `5834288282` |
| `guias_lateral_300x250` | Oferto Guia Lateral 300x250 | `9112072798` |
| `sorteios_topo_responsivo` | Oferto Sorteios Topo Responsivo | `3358394686` |
| `oferta_topo_responsivo` | Oferto Home Meio Responsivo | `3971426759` |
| `oferta_meio_responsivo` | Oferto Home Meio Responsivo | `3971426759` |

Se precisar sobrescrever algum ID no servidor, preencha `includes/config.php`:

```php
'adsense' => [
    'client_id' => 'ca-pub-1725208559538025',
    'slots' => [
        'v2_topo_responsivo' => '5284508420',
        'guias_artigo_topo_responsivo' => '5834288282',
    ],
],
```

