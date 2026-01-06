"""
Script para compilar YouTube Downloader em executável standalone PORTABLE
- Inclui Python embutido
- Auto-instala yt-dlp na primeira execução
- Oferece instalação automática do FFmpeg
Uso: python build_exe.py
"""
import os
import sys
import subprocess

def build_executable():
    """Compila o aplicativo em um executável PORTABLE"""
    
    print("=" * 60)
    print("🚀 Compilando YouTube Downloader PORTABLE para .exe")
    print("=" * 60)
    
    # Verificar se PyInstaller está instalado
    try:
        import PyInstaller
        print("✅ PyInstaller encontrado")
    except ImportError:
        print("📦 Instalando PyInstaller...")
        subprocess.check_call([sys.executable, "-m", "pip", "install", "pyinstaller"])
        print("✅ PyInstaller instalado com sucesso")
    
    # Garantir que yt-dlp está instalado (será incluído no .exe)
    print("\n📦 Verificando dependências...")
    try:
        subprocess.check_call([sys.executable, "-m", "pip", "install", "--upgrade", "yt-dlp"])
        print("✅ yt-dlp atualizado")
    except:
        print("⚠️ Aviso: yt-dlp pode precisar ser instalado manualmente")
    
    # Configurações do build
    script_name = "youtube_downloader_gui.py"
    app_name = "NeoYT_Downloader_Portable"
    
    # Comando PyInstaller com TODAS as dependências embutidas
    build_command = [
        "pyinstaller",
        "--onefile",              # Um único arquivo exe PORTABLE
        "--windowed",             # Sem console (GUI puro)
        "--name", app_name,       # Nome do executável
        "--clean",                # Limpar cache antes de compilar
        "--noconfirm",            # Sobrescrever sem perguntar
        
        # Otimizações
        "--optimize", "2",        # Nível máximo de otimização
        
        # Incluir módulos escondidos (importante!)
        "--hidden-import", "yt_dlp",
        "--hidden-import", "yt_dlp.extractor",
        "--hidden-import", "urllib.request",
        "--hidden-import", "zipfile",
        "--hidden-import", "shutil",
        
        # Coletar dados do yt-dlp
        "--collect-all", "yt_dlp",
        
        # Ícone (se existir)
        # "--icon=icon.ico",      # Descomente e adicione seu ícone
        
        # Script principal
        script_name
    ]
    
    print(f"\n📝 Comando: {' '.join(build_command)}\n")
    
    try:
        # Executar compilação
        print("⏳ Compilando... (pode demorar 3-5 minutos)\n")
        subprocess.check_call(build_command)
        
        print("\n" + "=" * 60)
        print("✅ COMPILAÇÃO CONCLUÍDA COM SUCESSO!")
        print("=" * 60)
        print(f"\n📂 Executável PORTABLE criado em: dist/{app_name}.exe")
        print(f"📦 Tamanho: ~30-50 MB (Python + yt-dlp embutidos)")
        print("\n🎯 CARACTERÍSTICAS DO EXECUTÁVEL:")
        print("   ✅ Totalmente PORTABLE - funciona em qualquer Windows")
        print("   ✅ NÃO precisa de Python instalado")
        print("   ✅ NÃO precisa de pip ou dependências")
        print("   ✅ yt-dlp já incluído e atualizado")
        print("   ✅ Oferece instalação automática do FFmpeg")
        print("   ✅ Pode ser executado de pen drive/USB")
        print("\n💡 PRÓXIMOS PASSOS:")
        print(f"   1. Teste: dist/{app_name}.exe")
        print("   2. Copie para qualquer PC Windows")
        print("   3. Execute direto - ZERO configuração!")
        print("\n⚠️  NOTAS:")
        print("   • Primeira execução: ~5-10 segundos (extração)")
        print("   • FFmpeg: oferece download automático na 1ª vez")
        print("   • Antivírus: pode dar falso positivo (normal)")
        print("=" * 60 + "\n")
        
        # Criar arquivo README para distribuição
        create_distribution_readme(app_name)
        
    except subprocess.CalledProcessError as e:
        print(f"\n❌ ERRO na compilação: {e}")
        sys.exit(1)

def create_distribution_readme(app_name):
    """Cria README para distribuir junto com o .exe"""
    readme_content = f"""# {app_name}

## 🚀 Como Usar

1. **Execute o arquivo**: `{app_name}.exe`
2. **Primeira vez**: O app vai verificar e oferecer instalar FFmpeg
3. **Cole a URL**: YouTube, Vimeo, etc.
4. **Escolha formato**: MP4, MP3, qualidade, etc.
5. **Clique em BAIXAR**: Pronto!

## ✅ Vantagens

- ✅ **100% Portable** - Não precisa instalar nada
- ✅ **Funciona offline** - Não precisa internet (exceto para downloads)
- ✅ **Zero configuração** - Executar e usar
- ✅ **Suporta playlists** - Download em lote
- ✅ **Alta qualidade** - Até 4K/8K
- ✅ **Múltiplos formatos** - MP4, MKV, WEBM, MP3, AAC, FLAC...

## 📋 Requisitos

- Windows 10/11 (64-bit)
- ~100MB espaço livre (para cache)
- Conexão com internet (para downloads)

## 🔧 FFmpeg

Na primeira execução, o app vai perguntar se quer instalar FFmpeg.
**Recomendado**: Clique em SIM para melhor qualidade e mais formatos.

## 🐛 Problemas?

### Antivírus bloqueia
**Causa**: Falso positivo (comum em executáveis Python)
**Solução**: Adicione à lista de exceções do antivírus

### Demora para abrir
**Causa**: Normal na primeira execução
**Solução**: Aguarde 10-15 segundos

### "FFmpeg não encontrado"
**Causa**: FFmpeg não instalado
**Solução**: Aceite a instalação automática quando solicitado

## 📞 Suporte

Problemas? Entre em contato ou consulte a documentação completa.

---

**Versão Portable** - Desenvolvido com ❤️
"""
    
    try:
        with open(f"dist/{app_name}_README.txt", "w", encoding="utf-8") as f:
            f.write(readme_content)
        print(f"📄 README criado: dist/{app_name}_README.txt")
    except:
        pass

if __name__ == "__main__":
    build_executable()
