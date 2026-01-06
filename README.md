# 🎬 NeoYT Downloader - Premium Edition

![Build Status](https://github.com/SEU_USUARIO/SEU_REPO/workflows/Build%20Windows%20EXE/badge.svg)

Interface de última geração para download de vídeos do YouTube e outras plataformas. Design premium com glassmorphism, totalmente portable e sem necessidade de instalação.

---

## ✨ Características

- 🎨 **Interface Premium** - Design glassmorphism refinado
- 📦 **100% Portable** - Não precisa instalar Python ou dependências
- ⚡ **Download Paralelo** - Baixe playlists com 20 threads simultâneos
- 🎬 **Múltiplos Formatos** - MP4, MKV, WEBM, MP3, AAC, FLAC e mais
- 🎯 **Qualidade até 8K** - Escolha de 240p até 8K
- 🔄 **Auto-instalação** - Instala FFmpeg automaticamente
- 🎵 **Áudio + Vídeo** - Suporte completo para ambos
- 📋 **Playlists** - Download de playlists completas
- 🍪 **Cookies** - Suporte para vídeos privados/restritos
- 📸 **Metadados** - Thumbnail e informações embutidas

---

## 📥 Download

### [⬇️ Baixar Última Versão](https://github.com/SEU_USUARIO/SEU_REPO/releases/latest)

Ou acesse [Releases](https://github.com/SEU_USUARIO/SEU_REPO/releases) para versões anteriores.

---

## 🚀 Instalação Rápida

1. **Baixe** o arquivo ZIP da release
2. **Extraia** a pasta
3. **Execute** `INSTALADOR_COMPLETO.bat` (instala FFmpeg automaticamente)
4. **Pronto!** Use `NeoYT_Downloader_Portable.exe`

### Ou instalação manual:
1. Execute `NeoYT_Downloader_Portable.exe`
2. Aceite instalar FFmpeg quando perguntado
3. Pronto!

---

## 📖 Como Usar

1. **Cole a URL** do vídeo ou playlist
2. **Escolha o formato**:
   - Vídeo: MP4, MKV, WEBM, AVI, MOV
   - Áudio: MP3, AAC, OPUS, M4A, FLAC, WAV
3. **Selecione a qualidade**:
   - Vídeo: 8K, 4K, 1080p, 720p, 480p, 360p, 240p
   - Áudio: 320kbps, 256kbps, 192kbps, 128kbps, 96kbps
4. **Configure opções**:
   - ☑️ Incluir Thumbnail
   - ☑️ Incluir Metadados
   - ☑️ Baixar Playlist Completa
   - ☑️ Usar cookies do navegador
5. **Clique em BAIXAR**

---

## 🎨 Screenshots

*(Adicione screenshots aqui)*

---

## 📋 Requisitos

- **Sistema**: Windows 10/11 (64-bit)
- **RAM**: 2 GB mínimo
- **Espaço**: 500 MB livre
- **Internet**: Necessário para downloads

**Não precisa de:**
- ❌ Python instalado
- ❌ pip ou dependências
- ❌ Configuração manual

---

## 🔧 Recursos Técnicos

- **Interface**: Tkinter com design glassmorphism premium
- **Download**: yt-dlp (sempre atualizado)
- **Conversão**: FFmpeg (auto-instalação)
- **Threads**: 20 downloads paralelos para playlists
- **Portabilidade**: PyInstaller com Python embutido

---

## 🛠️ Desenvolvimento

### Compilar do código-fonte:

```bash
# 1. Clonar repositório
git clone https://github.com/SEU_USUARIO/SEU_REPO.git
cd SEU_REPO

# 2. Instalar dependências
pip install -r requirements_build.txt

# 3. Compilar (Windows ou VM)
python build_exe.py

# 4. Criar pacote de distribuição
python create_package.py
```

### Ou use GitHub Actions:
- Faça push para `main`
- GitHub compila automaticamente
- Baixe o .exe nos Artifacts

---

## 📦 Estrutura do Projeto

```
youtube-downloader/
├── youtube_downloader_gui.py    # Aplicativo principal
├── build_exe.py                 # Script de compilação
├── create_package.py            # Cria pacote de distribuição
├── INSTALADOR_COMPLETO.bat      # Instalador automático
├── LEIA-ME.txt                  # Manual do usuário
├── requirements_build.txt       # Dependências de build
└── .github/
    └── workflows/
        └── build-exe.yml        # Build automático
```

---

## 🤝 Contribuindo

Contribuições são bem-vindas!

1. Fork o projeto
2. Crie uma branch (`git checkout -b feature/nova-feature`)
3. Commit suas mudanças (`git commit -m 'Add nova feature'`)
4. Push para a branch (`git push origin feature/nova-feature`)
5. Abra um Pull Request

---

## 📝 Licença

Este projeto está sob a licença MIT. Veja [LICENSE](LICENSE) para mais detalhes.

---

## 🙏 Créditos

- **yt-dlp** - https://github.com/yt-dlp/yt-dlp
- **FFmpeg** - https://ffmpeg.org
- **Python** - https://python.org

---

## 📞 Suporte

Encontrou um bug? Tem alguma sugestão?

- [Abrir Issue](https://github.com/SEU_USUARIO/SEU_REPO/issues)
- [Discussões](https://github.com/SEU_USUARIO/SEU_REPO/discussions)

---

## ⭐ Star History

Se este projeto te ajudou, dê uma ⭐!

---

**Desenvolvido com ❤️ | 100% Grátis e Open Source**
