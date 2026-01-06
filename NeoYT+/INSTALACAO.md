# GUIA DE INSTALAÇÃO RÁPIDA - NeoDelivery

## Passo 1: Upload dos Arquivos

Faça upload de todos os arquivos para:
```
https://oticaemfoco.com.br/sistema/NeoDelivery/
```

## Passo 2: Permissões

Configure as permissões da pasta `data/`:
```bash
chmod 755 data/
```

Ou via FTP/cPanel: defina permissões 755 para a pasta `data/`

## Passo 3: Primeiro Acesso

1. Acesse: `https://oticaemfoco.com.br/sistema/NeoDelivery/`
2. O sistema criará automaticamente o banco de dados SQLite

## Passo 4: Login Admin

1. Acesse: `https://oticaemfoco.com.br/sistema/NeoDelivery/admin/`
2. **Senha padrão:** `admin123`

**IMPORTANTE:** Altere a senha imediatamente!

Para alterar a senha, edite o arquivo `config/config.php` na linha:
```php
define('ADMIN_PASSWORD_HASH', password_hash('SUA_NOVA_SENHA', PASSWORD_DEFAULT));
```

## Passo 5: Configuração Inicial

No painel admin, configure:

### 5.1 Configurações (menu lateral)
- Nome do estabelecimento
- Telefone e WhatsApp (formato: 5511999999999)
- Endereço completo
- Chave PIX e tipo (CPF, CNPJ, etc)
- Nome do titular PIX
- Tempo estimado de entrega
- Marque "Loja Aberta" para aceitar pedidos

### 5.2 Categorias
- Crie categorias: Hambúrgueres, Pizzas, Bebidas, etc.
- Defina um slug (URL amigável): hamburgueres, pizzas, bebidas
- Defina ordem de exibição

### 5.3 Áreas de Entrega
- Cadastre os bairros que atende
- Defina taxa de entrega para cada bairro
- Configure tempo estimado

### 5.4 Produtos
- Adicione produtos com nome, descrição e preço
- Vincule à categoria correta
- Marque "Produto em Destaque" para aparecer na home
- Para imagens: coloque a URL relativa (ex: assets/images/produto.jpg)

## Passo 6: Testar o Sistema

1. Acesse o site público
2. Adicione produtos ao carrinho
3. Finalize um pedido teste
4. Verifique no admin se o pedido apareceu
5. Teste o botão WhatsApp

## Estrutura de URLs

- **Site público:** `https://oticaemfoco.com.br/sistema/NeoDelivery/`
- **Cardápio:** `https://oticaemfoco.com.br/sistema/NeoDelivery/cardapio.php`
- **Admin:** `https://oticaemfoco.com.br/sistema/NeoDelivery/admin/`

## Dicas Importantes

### Imagens
Coloque imagens na pasta `assets/images/` e referencie como:
- `assets/images/produto.jpg`
- `assets/images/logo.png`

### WhatsApp
- Formato correto: 5511999999999 (país + DDD + número)
- Sem espaços, parênteses ou traços

### PIX
- Configure a chave PIX completa
- Selecione o tipo correto (CPF, CNPJ, Email, etc)
- Nome do titular aparecerá na confirmação

### Backup
Faça backup regular do arquivo:
```
data/neodelivery.db
```

## Solução de Problemas

### "Erro ao criar pedido"
- Verifique permissões da pasta `data/`
- Certifique-se que SQLite3 está habilitado no PHP

### "Carrinho vazio"
- Verifique se JavaScript está habilitado no navegador
- Limpe o cache do navegador


### "Página em branco"
- Ative display_errors no PHP para ver erros
- Verifique logs de erro do servidor

### WhatsApp não funciona
- Verifique formato do número: 5511999999999
- Certifique-se que está completo (código país + DDD)

## Próximos Passos

1. Configure horários de funcionamento (funcionalidade básica já inclusa)
2. Adicione mais produtos
3. Teste pedidos reais
4. Monitore pedidos pelo admin
5. Personalize cores em Configurações

## Suporte

Sistema desenvolvido para uso imediato. Todas as funcionalidades básicas estão implementadas e funcionais.

**Senha Admin Padrão:** admin123 (ALTERE IMEDIATAMENTE!)

---

Desenvolvido com atenção aos detalhes para proporcionar a melhor experiência de delivery!
