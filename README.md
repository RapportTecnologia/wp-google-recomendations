# WP Google Recommendations

Este plugin exibe recomendações do Google na sua página WordPress, com opções de configuração de estrelas, rolagem e integração com Elementor.

## Instalação

1. Faça upload do plugin para a pasta `wp-content/plugins` do seu WordPress.
2. Ative o plugin pelo painel de administração do WordPress.

## Configuração da API do Google Recommendations

Para utilizar o plugin, você precisará de uma chave de API do Google Recommendations AI. Siga os passos abaixo para obter sua chave:

1. Acesse o [Google Cloud Console](https://console.cloud.google.com/).
2. Crie um novo projeto ou selecione um projeto existente.
3. Ative a API "Recommendations AI" para o seu projeto.
4. Vá em "APIs e Serviços" > "Credenciais".
5. Clique em "Criar credencial" e selecione "Chave de API".
6. Salve a chave gerada.
7. Copie o ID do Projeto e a localização (ex: `us-central1`).

No painel de administração do plugin, preencha os seguintes campos:
- **Chave da API do Google**
- **ID do Projeto Google**
- **Localização da API** (ex: `us-central1`)

## Opções de Administração

- **Nível de Estrelas:** Selecione o nível mínimo de estrelas das recomendações exibidas.
- **Orientação da Rolagem:** Escolha entre rolagem horizontal ou vertical para exibição das recomendações.

## Uso com Elementor

O plugin adiciona um widget chamado "Google Recomendações" à lista de widgets do Elementor. Basta arrastar o widget para sua página e configurar as opções desejadas.

## Shortcode

Você pode exibir as recomendações em qualquer lugar usando o shortcode:

```
[wpgr_recommendations]
```

## Suporte

Para dúvidas ou problemas, abra uma issue ou entre em contato com o desenvolvedor.
