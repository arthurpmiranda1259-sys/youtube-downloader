# Como Instalar FFmpeg no Windows

## 📥 Método 1: Usando Chocolatey (Mais Fácil)

Se você tem o Chocolatey instalado, execute no PowerShell como Administrador:

```powershell
choco install ffmpeg
```

## 📥 Método 2: Download Manual

### Passo 1: Baixar FFmpeg
1. Acesse: https://github.com/BtbN/FFmpeg-Builds/releases
2. Baixe o arquivo: `ffmpeg-master-latest-win64-gpl.zip`

### Passo 2: Extrair
1. Extraia o arquivo ZIP para `C:\ffmpeg`
2. Dentro deve ter a pasta `bin` com os arquivos `ffmpeg.exe` e `ffprobe.exe`

### Passo 3: Adicionar ao PATH
1. Pressione `Win + X` e selecione "Sistema"
2. Clique em "Configurações avançadas do sistema"
3. Clique em "Variáveis de Ambiente"
4. Na seção "Variáveis do sistema", encontre "Path" e clique em "Editar"
5. Clique em "Novo" e adicione: `C:\ffmpeg\bin`
6. Clique em "OK" em todas as janelas

### Passo 4: Verificar Instalação
Abra um **NOVO** terminal PowerShell e execute:

```powershell
ffmpeg -version
```

Se aparecer a versão do FFmpeg, está instalado corretamente!

## 🎵 Alternativa: Baixar sem Conversão

Se não quiser instalar o FFmpeg agora, você pode:
1. Selecionar "Vídeo" ao invés de "Áudio"
2. O arquivo será baixado em formato `.webm` ou `.m4a`
3. Você pode converter depois usando conversores online ou instalar o FFmpeg quando quiser

## ✅ Testando

Depois de instalar o FFmpeg, tente baixar novamente em MP3!
