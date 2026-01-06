# NeoDelivery - Sistema de Delivery Completo

Sistema de delivery profissional desenvolvido em PHP, HTML, CSS e SQLite.

## Características

- Sistema completo de pedidos online
- Painel administrativo completo
- Gestão de produtos e categorias
- Gestão de áreas de entrega e taxas
- Sistema de carrinho de compras
- Integração com WhatsApp
- Pagamento PIX
- Múltiplos status de pedidos
- Sistema de senhas para pedidos
- Totalmente responsivo
- Interface moderna e profissional

## Requisitos

- PHP 7.4 ou superior
- Extensão SQLite3 habilitada no PHP
- Servidor web (Apache/Nginx)

## Instalação

1. Faça upload de todos os arquivos para o diretório do seu site.
   ```
   Exemplo: https://seusite.com.br/delivery/
   ```

2. Certifique-se de que o PHP tem permissão de escrita na pasta `data/`:
   ```bash
   chmod 755 data/
   ```

3. O banco de dados SQLite será criado automaticamente na primeira execução.

## Configuração Inicial

### 1. Acesso ao Painel Admin

- URL: `https://seusite.com.br/delivery/admin/`
- Senha padrão: `admin123`

**IMPORTANTE:** Altere a senha padrão editando o arquivo `config/config.php`:
```php
define('ADMIN_PASSWORD_HASH', password_hash('sua_nova_senha', PASSWORD_DEFAULT));
```

### 2. Configurações do Sistema

No painel admin, vá em **Configurações** e preencha:

- Nome do estabelecimento
- Telefone e WhatsApp
- Endereço completo
- Chave PIX e dados do titular
- Tempo estimado de entrega
- Cores do sistema

### 3. Cadastrar Áreas de Entrega

1. Acesse **Áreas de Entrega** no menu admin
2. Cadastre os bairros que atende
3. Defina a taxa de entrega para cada bairro
4. Configure o tempo estimado

### 4. Criar Categorias

1. Acesse **Categorias** no menu admin
2. Crie categorias como: Hambúrgueres, Pizzas, Bebidas, etc.
3. Defina a ordem de exibição

### 5. Cadastrar Produtos

1. Acesse **Produtos** no menu admin
2. Adicione seus produtos com:
   - Nome e descrição
   - Preço
   - Categoria
   - Imagem (opcional)
   - Produto em destaque (para aparecer na home)

## Estrutura de Arquivos

```
NeoDelivery/
├── admin/                  # Painel administrativo
│   ├── index.php          # Dashboard
│   ├── login.php          # Login admin
│   ├── orders.php         # Gestão de pedidos
│   ├── order_detail.php   # Detalhes do pedido
│   ├── products.php       # Gestão de produtos
│   ├── categories.php     # Gestão de categorias
│   ├── delivery_areas.php # Gestão de áreas
│   ├── settings.php       # Configurações
│   └── logout.php         # Sair
├── assets/
│   ├── css/
│   │   ├── style.css      # Estilos principais
│   │   └── admin.css      # Estilos admin
│   ├── js/
│   │   └── main.js        # JavaScript principal
│   └── images/            # Imagens do sistema
├── config/
│   ├── config.php         # Configurações gerais
│   └── database.php       # Classe do banco de dados
├── data/
│   └── neodelivery.db     # Banco SQLite (criado automaticamente)
├── index.php              # Página inicial
├── cardapio.php           # Página de produtos
├── checkout.php           # Finalização do pedido
├── process_order.php      # Processamento do pedido
└── order_confirmation.php # Confirmação e WhatsApp
```

## Fluxo de Pedido

1. **Cliente navega** pelo cardápio e adiciona produtos ao carrinho
2. **Checkout**: Cliente informa dados pessoais e escolhe tipo de entrega
3. **Processamento**: Pedido é salvo no banco com número e senha
4. **Confirmação**: Cliente recebe confirmação com:
   - Senha do pedido
   - Detalhes completos
   - Chave PIX (se escolheu PIX)
   - Link para WhatsApp com mensagem pronta
5. **Admin**: Recebe pedido e pode gerenciar status
6. **WhatsApp**: Mensagem formatada é enviada automaticamente

## Gerenciamento de Pedidos

### Status dos Pedidos

- **Pendente**: Pedido recebido, aguardando preparo
- **Preparando**: Pedido em preparo na cozinha
- **Pronto**: Pedido pronto para retirada/entrega
- **Saiu p/ Entrega**: Pedido saiu para entrega
- **Concluído**: Pedido entregue/retirado
- **Cancelado**: Pedido cancelado

### Funcionalidades Admin

- Visualizar todos os pedidos
- Filtrar por status e data
- Ver detalhes completos do pedido
- Atualizar status do pedido
- Imprimir comprovante
- Enviar detalhes via WhatsApp

## Mensagem WhatsApp

O sistema gera automaticamente uma mensagem formatada com:

```
Pedido [Nome Estabelecimento] aceito e irá começar o preparo!

Senha: XX
Pedido: XXXXXXXXX (DD/MM/AAAA HH:MM)
Tipo: Entrega/Retirada
Endereço: [Endereço]
Estimativa: XX-XX minutos
------------------------------
NOME: [Nome Cliente]
Fone: [Telefone]
------------------------------
1x Produto R$XX,XX
------------------------------
Itens: R$XX,XX
Desconto: R$0,00
Entrega: R$X,XX

TOTAL: R$XX,XX
------------------------------
Pagamento: PIX
Chave PIX: XXXXX (CPF) - Nome Titular

Para repetir este pedido:
[URL do site]
```

## Pagamentos

### PIX
- Chave exibida após confirmação do pedido
- Dados do titular incluídos
- Mensagem WhatsApp contém informações PIX

### Dinheiro/Cartão
- Pagamento na entrega
- Informação exibida na confirmação

## Segurança

1. **Senha Admin**: Altere a senha padrão imediatamente
2. **Session**: Sistema usa sessões PHP seguras
3. **Sanitização**: Todos os inputs são sanitizados
4. **SQL Injection**: Prepared statements em todas as queries

## Personalização

### Cores
- Altere as cores no painel admin em **Configurações**
- As cores são aplicadas automaticamente no site

### Logo
- Upload de logo no painel admin (funcionalidade a ser implementada)
- Por enquanto, coloque a imagem em `assets/images/logo.png`
- Atualize o caminho em **Configurações**

### Imagens de Produtos
- Faça upload de imagens ao cadastrar produtos
- Imagens devem estar em `uploads/products/`
- Formatos aceitos: JPG, PNG, GIF

## Suporte e Manutenção

### Backup do Banco de Dados
Faça backup regular do arquivo:
```
data/neodelivery.db
```

### Logs
Erros PHP são registrados no log padrão do servidor.

### Limpeza de Pedidos Antigos
Para manter o banco limpo, periodicamente delete pedidos muito antigos:
```sql
DELETE FROM orders WHERE created_at < date('now', '-90 days') AND status = 'completed';
```

## Próximas Melhorias Sugeridas

- [ ] Upload de imagens pelo painel admin
- [ ] Relatórios de vendas e estatísticas
- [ ] Sistema de cupons de desconto
- [ ] Notificações push
- [ ] Histórico de pedidos do cliente
- [ ] Avaliações e comentários
- [ ] Múltiplos entregadores
- [ ] Rastreamento de entrega em tempo real

## Tecnologias Utilizadas

- **Backend**: PHP 7.4+
- **Database**: SQLite3
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Design**: CSS Grid, Flexbox, Responsivo

## Licença

Sistema desenvolvido para uso comercial.

## Contato e Suporte

Para suporte ou dúvidas, entre em contato através do estabelecimento.

---

**Desenvolvido com cuidado e atenção aos detalhes para proporcionar a melhor experiência de delivery online.**
