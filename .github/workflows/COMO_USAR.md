# 🤖 Como Usar o GitHub Actions

## 🎯 O que faz?

Este workflow **compila automaticamente** seu aplicativo Python em `.exe` toda vez que você fizer push, **sem precisar de Windows**!

---

## 🚀 Setup Inicial (5 minutos)

### 1️⃣ Criar repositório no GitHub
```bash
# Na pasta do projeto:
git init
git add .
git commit -m "Initial commit"

# Criar repo no GitHub e conectar:
git remote add origin https://github.com/SEU_USUARIO/youtube-downloader.git
git push -u origin main
```

### 2️⃣ Pronto! 🎉
O GitHub Actions vai:
- ✅ Detectar o workflow automaticamente
- ✅ Compilar no Windows
- ✅ Criar o .exe
- ✅ Disponibilizar para download

---

## 📥 Como Baixar o .exe Compilado

### Método 1: Artifacts (qualquer commit)
1. Vá para: `https://github.com/SEU_USUARIO/SEU_REPO/actions`
2. Clique no último workflow executado (com ✅ verde)
3. Role até "Artifacts" no final da página
4. Baixe:
   - `NeoYT-Downloader-EXE` (só o .exe)
   - `NeoYT-Downloader-Package` (pacote completo ZIP)

### Método 2: Releases (tags/versões)
1. Crie uma tag de versão:
```bash
git tag v1.0.0
git push origin v1.0.0
```
2. GitHub Actions cria automaticamente uma **Release**
3. Acesse: `https://github.com/SEU_USUARIO/SEU_REPO/releases`
4. Baixe o ZIP direto da release!

---

## ⚙️ Executar Manualmente

1. Vá em: `Actions` no seu repositório
2. Clique em "Build Windows EXE" (lado esquerdo)
3. Clique em "Run workflow" (lado direito)
4. Selecione a branch e clique "Run workflow"
5. Aguarde ~5 minutos
6. Baixe os artifacts!

---

## 🔄 Fluxo Automático

```
Você faz push
    ↓
GitHub Actions detecta
    ↓
Roda no Windows Server (grátis!)
    ↓
Instala Python + PyInstaller
    ↓
Compila build_exe.py
    ↓
Cria .exe e pacote ZIP
    ↓
Disponibiliza para download
    ↓
Você baixa e distribui!
```

**Tempo total**: ~3-5 minutos por build

---

## 💰 Custo

**GRÁTIS!** 
- GitHub Actions: 2000 minutos/mês grátis
- Este build: ~3-5 minutos
- **Você pode compilar ~400-600 vezes/mês grátis!**

---

## 🎨 Personalizações

### Compilar apenas em tags:
Remova estas linhas do workflow:
```yaml
on:
  push:
    branches: [ main, master ]  # <- remova isso
```

### Mudar nome do .exe:
Edite `build_exe.py`:
```python
app_name = "MeuApp"  # Nome que você quiser
```

### Adicionar ícone:
1. Coloque `icon.ico` na raiz do projeto
2. Descomente em `build_exe.py`:
```python
"--icon=icon.ico"
```

---

## 🐛 Troubleshooting

### ❌ Workflow falha com erro
1. Clique no workflow que falhou
2. Expanda o step com erro
3. Leia a mensagem de erro
4. Corrija o código e faça novo push

### ❌ Não consigo baixar artifacts
- **Causa**: Precisa estar logado no GitHub
- **Solução**: Faça login antes de acessar Actions

### ❌ Artifacts expiram
- **Padrão**: 30-90 dias
- **Solução**: Use Releases para permanente

---

## 📊 Status do Build

Adicione badge ao README.md:
```markdown
![Build Status](https://github.com/SEU_USUARIO/SEU_REPO/workflows/Build%20Windows%20EXE/badge.svg)
```

Fica assim: ![Build Status](https://img.shields.io/badge/build-passing-brightgreen)

---

## 🎯 Resumo

**SEM GitHub Actions:**
- Precisa de Windows ou VM
- Compilação manual
- Demorado

**COM GitHub Actions:**
- ✅ Compila automaticamente
- ✅ Não precisa de Windows
- ✅ Grátis
- ✅ Rápido (~3-5 min)
- ✅ Disponível para download imediato

**É a melhor solução para você que está no Linux!** 🐧 → 🪟
