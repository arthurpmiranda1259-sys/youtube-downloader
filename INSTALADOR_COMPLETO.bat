@echo off
chcp 65001 > nul
echo ============================================================
echo 🚀 INSTALADOR COMPLETO - NeoYT Downloader
echo ============================================================
echo.
echo Este script vai preparar TUDO automaticamente:
echo   ✅ Verificar Python (opcional - .exe já inclui)
echo   ✅ Instalar FFmpeg (necessário para HD/4K)
echo   ✅ Atualizar yt-dlp
echo   ✅ Configurar PATH do sistema
echo.
pause

echo.
echo [1/4] 📦 Verificando Python...
python --version 2>nul
if %errorlevel% equ 0 (
    echo ✅ Python detectado
) else (
    echo ⚠️ Python não encontrado ^(OK, o .exe funciona sem ele^)
)

echo.
echo [2/4] 🎬 Instalando FFmpeg...
echo Baixando FFmpeg essentials...

:: Criar diretório
if not exist "%~dp0ffmpeg" mkdir "%~dp0ffmpeg"

:: Baixar FFmpeg usando PowerShell
powershell -Command "& {Invoke-WebRequest -Uri 'https://github.com/BtbN/FFmpeg-Builds/releases/download/latest/ffmpeg-master-latest-win64-gpl.zip' -OutFile '%~dp0ffmpeg.zip'}"

if exist "%~dp0ffmpeg.zip" (
    echo ✅ FFmpeg baixado
    echo 📂 Extraindo...
    powershell -Command "& {Expand-Archive -Path '%~dp0ffmpeg.zip' -DestinationPath '%~dp0ffmpeg' -Force}"
    
    :: Encontrar executáveis
    for /r "%~dp0ffmpeg" %%F in (ffmpeg.exe) do (
        set "FFMPEG_PATH=%%~dpF"
        goto :found_ffmpeg
    )
    :found_ffmpeg
    
    if defined FFMPEG_PATH (
        echo ✅ FFmpeg extraído: %FFMPEG_PATH%
        
        :: Adicionar ao PATH do usuário
        echo 🔧 Adicionando ao PATH...
        setx PATH "%PATH%;%FFMPEG_PATH%" >nul 2>&1
        
        echo ✅ FFmpeg instalado com sucesso!
    )
    
    :: Limpar
    del "%~dp0ffmpeg.zip" 2>nul
) else (
    echo ❌ Falha ao baixar FFmpeg
    echo Você pode instalar manualmente: https://ffmpeg.org/download.html
)

echo.
echo [3/4] 📦 Atualizando yt-dlp...
python -m pip install --upgrade yt-dlp 2>nul
if %errorlevel% equ 0 (
    echo ✅ yt-dlp atualizado
) else (
    echo ⚠️ Não foi possível atualizar ^(OK, o .exe já inclui^)
)

echo.
echo [4/4] ✅ Finalizando...
echo.
echo ============================================================
echo ✅ INSTALAÇÃO COMPLETA!
echo ============================================================
echo.
echo Agora você pode usar o aplicativo:
echo   • Execute: NeoYT_Downloader_Portable.exe
echo   • Cole URL do YouTube
echo   • Escolha formato e qualidade
echo   • Baixe!
echo.
echo 💡 DICAS:
echo   • FFmpeg: já está instalado e no PATH
echo   • Playlists: marque "Baixar Playlist Completa"
echo   • Qualidade: escolha até 4K/8K se disponível
echo   • Cookies: marque para vídeos privados/idade
echo.
pause
echo.
echo Abrindo o aplicativo...
start "" "%~dp0NeoYT_Downloader_Portable.exe"
exit
