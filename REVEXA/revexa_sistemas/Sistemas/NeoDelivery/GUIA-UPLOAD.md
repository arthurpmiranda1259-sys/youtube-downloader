# Guia de Teste - Sistema de Upload de Imagens

## Passos para testar o sistema:

### 1. Verificar o ambiente
Acesse: `https://oticaemfoco.com.br/sistema/NeoDelivery/debug-banners.php`

Isso vai mostrar:
- Estrutura da tabela banners
- Banners cadastrados
- Status dos diretórios de upload
- Configurações do sistema

### 2. Testar upload simples
Acesse: `https://oticaemfoco.com.br/sistema/NeoDelivery/test-upload.html`

- Clique ou arraste uma imagem
- Verifique se o upload funciona
- Anote o caminho retornado

### 3. Adicionar banner pelo admin
Acesse: `https://oticaemfoco.com.br/sistema/NeoDelivery/admin/banners.php`

- Clique na área de upload
- Selecione uma imagem (recomendado: 1920x600px)
- Aguarde o upload completar
- Preencha título e descrição (opcional)
- Marque "Banner Ativo"
- Clique em "Cadastrar Banner"

### 4. Verificar na página inicial
Acesse: `https://oticaemfoco.com.br/sistema/NeoDelivery/`

- Deve aparecer o carrossel com a imagem enviada
- Se não aparecer, volte ao debug-banners.php

### 5. Adicionar imagens em Produtos
Acesse: `https://oticaemfoco.com.br/sistema/NeoDelivery/admin/products.php`

- Ao criar/editar produto
- Clique na área "Imagem do Produto"
- Faça upload da foto
- Preencha os demais campos
- Salve

### 6. Adicionar imagens em Categorias
Acesse: `https://oticaemfoco.com.br/sistema/NeoDelivery/admin/categories.php`

- Ao criar/editar categoria
- Clique na área "Imagem da Categoria"
- Faça upload da foto
- Preencha os demais campos
- Salve

## Problemas Comuns:

### Imagem aparece preta/não carrega
**Causa:** Caminho da imagem incorreto ou arquivo não existe
**Solução:** 
1. Verifique no debug-banners.php se o arquivo existe
2. Verifique se o caminho está correto (deve ser: uploads/banners/nome-arquivo.jpg)
3. Tente fazer upload novamente

### Upload não funciona
**Causa:** Permissões de diretório ou tamanho de arquivo
**Solução:**
1. Verifique se os diretórios têm permissão de escrita (755)
2. Reduza o tamanho da imagem (máximo 5MB)
3. Use formatos suportados: JPG, PNG, GIF, WEBP

### Banner não aparece na página
**Causa:** Banner está inativo ou não há banners cadastrados
**Solução:**
1. Verifique se marcou "Banner Ativo"
2. Verifique no debug-banners.php se o banner existe
3. Limpe o cache do navegador (Ctrl+Shift+R)

## Dicas:

- Use imagens otimizadas (comprimidas)
- Para banners: 1920x600px funciona bem
- Para produtos: 800x600px
- Para categorias: 600x400px
- Sempre marque "Ativo" para o item aparecer no site
