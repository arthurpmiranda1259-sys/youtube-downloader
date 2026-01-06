# YouTube Downloader 🎬

Um downloader de vídeos do YouTube simples e eficiente, disponível em duas versões: linha de comando e interface gráfica.

## 📋 Recursos

- ✅ Download de vídeos em várias qualidades (best, 1080p, 720p, 480p, 360p)
- ✅ Download de áudio em MP3 com **capa embutida** (thumbnail do vídeo)
- ✅ **Download de playlists completas** em MP3 com capas
- ✅ Visualização de informações do vídeo antes de baixar
- ✅ Barra de progresso e indicador de velocidade
- ✅ Interface gráfica amigável (versão GUI)
- ✅ Interface de linha de comando (versão CLI)
- ✅ Metadados automáticos nos arquivos MP3

## 🚀 Instalação

### 1. Instalar Python
Certifique-se de ter Python 3.7 ou superior instalado. Verifique com:
```bash
python --version
```

### 2. Instalar dependências
```bash
pip install -r requirements.txt
```

Ou instale manualmente:
```bash
pip install yt-dlp
```

### 3. (Opcional) Instalar FFmpeg
Para converter áudio para MP3, você precisa do FFmpeg:

**Windows:**
- Baixe do site oficial: https://ffmpeg.org/download.html
- Extraia e adicione ao PATH do sistema

**Linux:**
```bash
sudo apt install ffmpeg
```

**macOS:**
```bash
brew install ffmpeg
```

## 📖 Como Usar

### Versão com Interface Gráfica (Recomendado)

Execute o arquivo GUI:
```bash
python youtube_downloader_gui.py
```

1. Cole a URL do vídeo ou playlist do YouTube
2. Escolha a pasta de destino
3. Selecione o tipo:
   - **Vídeo**: Baixa apenas o vídeo
   - **Áudio (MP3)**: Baixa um único áudio com capa
   - **Playlist (MP3)**: Baixa playlist inteira com capas
4. Escolha a qualidade (para vídeos)
5. Clique em "Baixar"

### Versão Linha de Comando

Execute o script:
```bash
python youtube_downloader.py
```

Ou passe a URL como argumento:
```bash
python youtube_downloader.py "https://www.youtube.com/watch?v=VIDEO_ID"
```

#### Opções disponíveis:
1. **Baixar vídeo (melhor qualidade)** - Baixa o vídeo na melhor qualidade disponível
2. **Baixar apenas áudio (MP3)** - Extrai e converte o áudio para MP3
3. **Ver informações do vídeo** - Mostra detalhes sem baixar
4. **Baixar vídeo em qualidade específica** - Escolha o formato desejado

#### Exemplos de formatos personalizados:
- `best` - Melhor qualidade disponível
- `worst` - Menor qualidade (menor tamanho)
- `bestvideo[height<=720]+bestaudio` - Máximo 720p
- `bestvideo[height<=480]+bestaudio` - Máximo 480p

## 📁 Estrutura dos Arquivos

```
NeoYT+/
│
├── youtube_downloader.py       # Versão CLI
├── youtube_downloader_gui.py   # Versão GUI
├── requirements.txt            # Dependências
├── README.md                   # Este arquivo
└── downloads/                  # Pasta padrão de downloads (criada automaticamente)
```

## 🛠️ Solução de Problemas

### Erro: "yt-dlp não encontrado"
```bash
pip install --upgrade yt-dlp
```

### Erro ao converter áudio
- Certifique-se de que o FFmpeg está instalado e no PATH

### Erro de permissão
- Execute o terminal como administrador (Windows)
- Use `sudo` no Linux/macOS se necessário

### Vídeo não baixa
- Verifique se a URL está correta
- Alguns vídeos podem ter restrições de região ou idade
- Tente atualizar o yt-dlp: `pip install --upgrade yt-dlp`

## 🎵 Sobre o Download de Áudio (MP3)

### Com FFmpeg (Recomendado)
- Converte automaticamente para MP3
- Melhor qualidade e compatibilidade

### Sem FFmpeg
- Baixa em formato original (WebM ou M4A)
- O programa detecta automaticamente e avisa
- Ainda funciona perfeitamente para áudio

**O arquivo baixado já é um arquivo de áudio, apenas em formato diferente!**

## ⚠️ Aviso Legal

Este software é fornecido apenas para fins educacionais. Certifique-se de respeitar os direitos autorais e os Termos de Serviço do YouTube ao baixar conteúdo. Use apenas para conteúdo que você tem permissão para baixar.

## 📝 Licença

Livre para uso pessoal e educacional.

## 🤝 Contribuições

Sinta-se à vontade para melhorar o código e adicionar novos recursos!

## 📞 Suporte

Se encontrar problemas:
1. Verifique se todas as dependências estão instaladas
2. Atualize o yt-dlp para a versão mais recente
3. Verifique a documentação do yt-dlp: https://github.com/yt-dlp/yt-dlp

---

**Desenvolvido com ❤️ usando Python e yt-dlp**
