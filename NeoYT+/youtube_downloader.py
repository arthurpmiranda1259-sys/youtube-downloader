#!/usr/bin/env python3
"""
YouTube Video Downloader
Baixa vídeos do YouTube em diferentes formatos e qualidades
"""

import yt_dlp
import os
import sys


def download_video(url, output_path='downloads', format_choice='best'):
    """
    Baixa um vídeo do YouTube
    
    Args:
        url: URL do vídeo do YouTube
        output_path: Pasta onde o vídeo será salvo
        format_choice: Qualidade do vídeo ('best', 'worst', ou formato específico)
    """
    
    # Criar pasta de downloads se não existir
    if not os.path.exists(output_path):
        os.makedirs(output_path)
    
    # Configurações do yt-dlp
    ydl_opts = {
        'format': format_choice,
        'outtmpl': os.path.join(output_path, '%(title)s.%(ext)s'),
        'progress_hooks': [progress_hook],
    }
    
    try:
        with yt_dlp.YoutubeDL(ydl_opts) as ydl:
            print(f"\n🎥 Baixando vídeo de: {url}")
            info = ydl.extract_info(url, download=True)
            print(f"\n✅ Download concluído: {info['title']}")
            return True
    except Exception as e:
        print(f"\n❌ Erro ao baixar vídeo: {str(e)}")
        return False


def download_audio_only(url, output_path='downloads'):
    """
    Baixa apenas o áudio do vídeo em formato MP3
    """
    
    if not os.path.exists(output_path):
        os.makedirs(output_path)
    
    ydl_opts = {
        'format': 'bestaudio/best',
        'outtmpl': os.path.join(output_path, '%(title)s.%(ext)s'),
        'postprocessors': [{
            'key': 'FFmpegExtractAudio',
            'preferredcodec': 'mp3',
            'preferredquality': '192',
        }],
        'progress_hooks': [progress_hook],
    }
    
    try:
        with yt_dlp.YoutubeDL(ydl_opts) as ydl:
            print(f"\n🎵 Baixando áudio de: {url}")
            info = ydl.extract_info(url, download=True)
            print(f"\n✅ Download concluído: {info['title']}.mp3")
            return True
    except Exception as e:
        print(f"\n❌ Erro ao baixar áudio: {str(e)}")
        return False


def progress_hook(d):
    """Mostra o progresso do download"""
    if d['status'] == 'downloading':
        percent = d.get('_percent_str', 'N/A')
        speed = d.get('_speed_str', 'N/A')
        eta = d.get('_eta_str', 'N/A')
        print(f"\rProgresso: {percent} | Velocidade: {speed} | Tempo restante: {eta}", end='')
    elif d['status'] == 'finished':
        print(f"\n📦 Download finalizado, processando arquivo...")


def get_video_info(url):
    """Obtém informações sobre o vídeo sem baixá-lo"""
    ydl_opts = {
        'quiet': True,
        'no_warnings': True,
    }
    
    try:
        with yt_dlp.YoutubeDL(ydl_opts) as ydl:
            info = ydl.extract_info(url, download=False)
            
            print("\n📋 Informações do vídeo:")
            print(f"Título: {info.get('title', 'N/A')}")
            print(f"Duração: {info.get('duration', 0)} segundos")
            print(f"Visualizações: {info.get('view_count', 'N/A')}")
            print(f"Canal: {info.get('uploader', 'N/A')}")
            
            print("\n📊 Formatos disponíveis:")
            formats = info.get('formats', [])
            for i, fmt in enumerate(formats[-10:], 1):  # Mostrar últimos 10 formatos
                resolution = fmt.get('resolution', 'audio only')
                ext = fmt.get('ext', 'N/A')
                filesize = fmt.get('filesize', 0)
                size_mb = f"{filesize / (1024*1024):.2f} MB" if filesize else "Tamanho desconhecido"
                print(f"{i}. {resolution} - {ext} - {size_mb}")
            
            return info
    except Exception as e:
        print(f"\n❌ Erro ao obter informações: {str(e)}")
        return None


def main():
    """Função principal - interface de linha de comando"""
    print("=" * 60)
    print("🎬 YouTube Downloader")
    print("=" * 60)
    
    if len(sys.argv) > 1:
        url = sys.argv[1]
    else:
        url = input("\n📎 Digite a URL do vídeo do YouTube: ").strip()
    
    if not url:
        print("❌ URL não pode estar vazia!")
        return
    
    print("\n🔍 Escolha uma opção:")
    print("1. Baixar vídeo (melhor qualidade)")
    print("2. Baixar apenas áudio (MP3)")
    print("3. Ver informações do vídeo")
    print("4. Baixar vídeo em qualidade específica")
    
    choice = input("\nOpção (1-4): ").strip()
    
    if choice == '1':
        download_video(url)
    elif choice == '2':
        download_audio_only(url)
    elif choice == '3':
        get_video_info(url)
    elif choice == '4':
        print("\nFormatos comuns:")
        print("- bestvideo+bestaudio: Melhor qualidade")
        print("- worst: Pior qualidade (menor tamanho)")
        print("- bestvideo[height<=720]+bestaudio: Máximo 720p")
        print("- bestvideo[height<=480]+bestaudio: Máximo 480p")
        format_code = input("\nDigite o formato desejado: ").strip()
        download_video(url, format_choice=format_code)
    else:
        print("❌ Opção inválida!")


if __name__ == "__main__":
    main()
