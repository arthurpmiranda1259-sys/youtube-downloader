#!/bin/bash

echo "═══════════════════════════════════════════════════════════"
echo "🚀 Publicar no GitHub e Compilar Automaticamente"
echo "═══════════════════════════════════════════════════════════"
echo ""

# Verificar se já é um repo git
if [ ! -d ".git" ]; then
    echo "📦 Inicializando repositório Git..."
    git init
    git branch -M main
    echo "✅ Repositório criado"
else
    echo "✅ Repositório Git já existe"
fi

# Adicionar todos os arquivos
echo ""
echo "📋 Adicionando arquivos..."
git add .

# Commit
echo ""
echo "💾 Fazendo commit..."
read -p "📝 Mensagem do commit (Enter para 'Auto build setup'): " commit_msg
commit_msg=${commit_msg:-"Auto build setup - Portable EXE with GitHub Actions"}
git commit -m "$commit_msg"

# Perguntar se já tem remote
if git remote get-url origin > /dev/null 2>&1; then
    echo ""
    echo "✅ Remote 'origin' já configurado:"
    git remote get-url origin
    read -p "🔄 Fazer push? (s/n): " do_push
else
    echo ""
    echo "❌ Remote 'origin' não configurado"
    echo ""
    echo "📋 INSTRUÇÕES:"
    echo "   1. Crie um novo repositório no GitHub:"
    echo "      https://github.com/new"
    echo ""
    echo "   2. NÃO inicialize com README, .gitignore ou licença"
    echo ""
    echo "   3. Copie a URL do repositório (https://github.com/...)"
    echo ""
    read -p "📎 Cole a URL do seu repositório GitHub: " repo_url
    
    if [ -z "$repo_url" ]; then
        echo "❌ URL vazia. Execute novamente quando criar o repositório."
        exit 1
    fi
    
    echo ""
    echo "🔗 Configurando remote..."
    git remote add origin "$repo_url"
    echo "✅ Remote configurado: $repo_url"
    
    do_push="s"
fi

# Push
if [ "$do_push" = "s" ] || [ "$do_push" = "S" ]; then
    echo ""
    echo "📤 Enviando para GitHub..."
    
    # Tentar push
    if git push -u origin main; then
        echo ""
        echo "═══════════════════════════════════════════════════════════"
        echo "✅ SUCESSO! Código enviado para o GitHub!"
        echo "═══════════════════════════════════════════════════════════"
        echo ""
        echo "🤖 GitHub Actions vai compilar automaticamente!"
        echo ""
        echo "📋 PRÓXIMOS PASSOS:"
        echo "   1. Acesse: $(git remote get-url origin | sed 's/.git$//')"
        echo "   2. Clique em 'Actions' (menu superior)"
        echo "   3. Veja o workflow 'Build Windows EXE' executando"
        echo "   4. Aguarde ~3-5 minutos"
        echo "   5. Baixe o .exe nos 'Artifacts'"
        echo ""
        echo "💡 PARA CRIAR RELEASE:"
        echo "   git tag v1.0.0"
        echo "   git push origin v1.0.0"
        echo "   → Cria release automática com download direto!"
        echo ""
        echo "═══════════════════════════════════════════════════════════"
    else
        echo ""
        echo "❌ Erro ao fazer push"
        echo ""
        echo "💡 POSSÍVEIS CAUSAS:"
        echo "   • Repositório não existe no GitHub"
        echo "   • Não tem permissão (configure SSH ou token)"
        echo "   • URL incorreta"
        echo ""
        echo "🔧 SOLUÇÃO:"
        echo "   1. Verifique se o repositório existe no GitHub"
        echo "   2. Configure autenticação:"
        echo "      git config --global credential.helper cache"
        echo "   3. Tente novamente"
    fi
else
    echo ""
    echo "⏸️  Push cancelado"
    echo "💡 Para fazer push depois:"
    echo "   git push -u origin main"
fi

echo ""
