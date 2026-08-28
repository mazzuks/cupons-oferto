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

## Proxima etapa

Os placeholders `.inventory-slot` ja estao ligados ao helper `render_ad_slot()`.
Para ativar blocos manuais do AdSense, crie os blocos no painel do AdSense e preencha os IDs em `includes/config.php`, dentro de `adsense.slots`.

Exemplo:

```php
'adsense' => [
    'client_id' => 'ca-pub-1725208559538025',
    'slots' => [
        'v2_topo_responsivo' => '1234567890',
        'guias_artigo_topo_responsivo' => '2345678901',
    ],
],
```

Enquanto um slot estiver vazio, o site mostra apenas o placeholder visual de publicidade.

