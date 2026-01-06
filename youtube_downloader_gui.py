#!/usr/bin/env python3
"""
YouTube Video Downloader - ULTRA PREMIUM EDITION
Interface de última geração com glassmorphism e animações avançadas
Versão PORTABLE - Auto-instala dependências
"""

import tkinter as tk
from tkinter import ttk, messagebox, filedialog
import threading
import os
import sys
import subprocess
from concurrent.futures import ThreadPoolExecutor
import queue
import math
import time
import urllib.request
import zipfile
import shutil
from pathlib import Path

# Auto-instalação de dependências
def ensure_dependencies():
    """Garante que todas as dependências estão instaladas"""
    missing = []
    
    # Verificar yt-dlp
    try:
        import yt_dlp
    except ImportError:
        missing.append('yt-dlp')
    
    # Instalar dependências faltantes
    if missing:
        try:
            subprocess.check_call([sys.executable, '-m', 'pip', 'install', '--upgrade'] + missing)
            print(f"✅ Instalado: {', '.join(missing)}")
        except Exception as e:
            messagebox.showerror("Erro", f"Falha ao instalar dependências: {e}")
            sys.exit(1)

# Executar verificação
ensure_dependencies()

# Agora importar yt_dlp
import yt_dlp


class YouTubeDownloaderGUI:
    def __init__(self, root):
        self.root = root
        self.root.title("YouTube Downloader Pro")
        self.root.geometry("1200x900")
        self.root.resizable(True, True)
        
        # Paleta de cores PREMIUM REFINADA (Deep Navy to Purple)
        self.colors = {
            'bg_primary': '#0f0f23',           # Deep Navy
            'bg_secondary': '#1a1a3e',         # Navy Blue  
            'bg_card': '#1e1e3c',              # Glass effect base (solid for tkinter)
            'bg_card_solid': '#1e1e3c',        # For non-transparent contexts
            'accent_primary': '#8b5cf6',       # Purple (primary actions)
            'accent_secondary': '#06b6d4',     # Cyan (secondary)
            'accent_gradient_1': '#6366f1',    # Indigo
            'accent_gradient_2': '#8b5cf6',    # Purple
            'accent_gradient_3': '#ec4899',    # Pink
            'text_primary': '#f8fafc',         # Soft white
            'text_secondary': '#94a3b8',       # Blue gray
            'text_muted': '#64748b',           # Muted gray
            'success': '#10b981',              # Emerald
            'warning': '#f59e0b',              # Amber
            'error': '#ef4444',                # Red
            'border_glass': '#2a2a4e',         # Subtle glass border (solid)
            'glow_subtle': '#6b46c1',          # Subtle purple glow (solid)
        }
        
        # Configurar estilo
        self.root.configure(bg=self.colors['bg_primary'])
        self.setup_styles()
        
        # Verificar FFmpeg na inicialização
        self.check_ffmpeg()
        
        # Variáveis expandidas
        self.download_path = tk.StringVar(value=os.path.join(os.path.expanduser("~"), "Downloads"))
        self.url_var = tk.StringVar()
        self.media_type = tk.StringVar(value="video")
        self.video_format = tk.StringVar(value="mp4")
        self.audio_format = tk.StringVar(value="mp3")
        self.video_quality = tk.StringVar(value="1080p (FHD)")
        self.audio_quality = tk.StringVar(value="192 kbps")
        self.embed_thumbnail = tk.BooleanVar(value=True)
        self.embed_metadata = tk.BooleanVar(value=True)
        self.download_playlist = tk.BooleanVar(value=True)
        self.browser_var = tk.StringVar(value="chrome")
        self.use_cookies_var = tk.BooleanVar(value=True)
        self.is_downloading = False
        
        # Estado de animação refinado
        self.glow_offset = 0
        self.pulse_state = 0
        
        self.setup_ui()
        self.start_animations()
    
    def setup_styles(self):
        """Configura estilos PREMIUM REFINADOS"""
        style = ttk.Style()
        style.theme_use('clam')
        
        # Frames com glassmorphism
        style.configure("Glass.TFrame", 
                       background=self.colors['bg_card_solid'],
                       relief="flat")
        
        # Labels com tipografia premium
        style.configure("Title.TLabel",
                       background=self.colors['bg_primary'],
                       foreground=self.colors['text_primary'],
                       font=("Inter", 28, "bold"))
        
        style.configure("Subtitle.TLabel",
                       background=self.colors['bg_primary'],
                       foreground=self.colors['text_secondary'],
                       font=("Inter", 11))
        
        style.configure("SectionTitle.TLabel",
                       background=self.colors['bg_card_solid'],
                       foreground=self.colors['text_primary'],
                       font=("Inter", 12, "bold"))
        
        style.configure("Text.TLabel",
                       background=self.colors['bg_card_solid'],
                       foreground=self.colors['text_secondary'],
                       font=("Inter", 10))
        
        # Progressbar elegante
        style.configure("Premium.Horizontal.TProgressbar",
                       background=self.colors['accent_primary'],
                       troughcolor=self.colors['bg_secondary'],
                       borderwidth=0,
                       thickness=6)
    
    def start_animations(self):
        """Inicia animações sutis"""
        self.animate_glow()
    
    def animate_glow(self):
        """Anima efeito de brilho sutil"""
        self.glow_offset += 0.03
        self.pulse_state = (math.sin(self.glow_offset) + 1) / 2
        self.root.after(50, self.animate_glow)
    
    def create_glass_card(self, parent, padding=25):
        """Cria card com VERDADEIRO glassmorphism effect"""
        # Container com sombra sutil
        container = tk.Frame(parent, bg=self.colors['bg_primary'], bd=0)
        
        # Frame com efeito glass - simula backdrop blur com camadas
        glass_outer = tk.Frame(container, 
                              bg=self.colors['bg_card_solid'], 
                              highlightthickness=1,
                              highlightbackground=self.colors['border_glass'],
                              bd=0)
        glass_outer.pack(fill=tk.BOTH, expand=True, padx=1, pady=1)
        
        # Inner com padding generoso
        inner = tk.Frame(glass_outer, bg=self.colors['bg_card_solid'], bd=0)
        inner.pack(fill=tk.BOTH, expand=True, padx=padding, pady=padding)
        
        return container, inner
    
    def create_premium_button(self, parent, text, command, style='primary', icon=''):
        """Cria botão premium com profundidade e estados"""
        if style == 'primary':
            bg_normal = self.colors['accent_primary']
            bg_hover = self.colors['accent_gradient_2']
            fg = self.colors['text_primary']
        elif style == 'secondary':
            bg_normal = self.colors['bg_secondary']
            bg_hover = self.colors['bg_card_solid']
            fg = self.colors['text_secondary']
        else:
            bg_normal = self.colors['bg_card_solid']
            bg_hover = self.colors['bg_secondary']
            fg = self.colors['text_muted']
        
        # Container com sombra
        btn_container = tk.Frame(parent, bg=self.colors['bg_primary'], bd=0)
        
        # Botão com bordas arredondadas simuladas
        btn = tk.Label(btn_container, 
                      text=f"{icon} {text}" if icon else text,
                      font=("Inter", 11, "bold"),
                      bg=bg_normal, fg=fg,
                      cursor="hand2",
                      padx=24, pady=14)
        btn.pack()
        
        def on_click(e):
            command()
        
        def on_enter(e):
            btn.config(bg=bg_hover)
        
        def on_leave(e):
            btn.config(bg=bg_normal)
        
        btn.bind("<Button-1>", on_click)
        btn.bind("<Enter>", on_enter)
        btn.bind("<Leave>", on_leave)
        
        return btn_container, btn
    
    def create_modern_input(self, parent, textvariable=None, placeholder="", **kwargs):
        """Cria input moderno com bordas arredondadas"""
        input_container = tk.Frame(parent, 
                                   bg=self.colors['border_glass'],
                                   highlightthickness=0, bd=0)
        
        input_field = tk.Entry(input_container, 
                              textvariable=textvariable,
                              font=("Inter", 11),
                              bg=self.colors['bg_secondary'],
                              fg=self.colors['text_primary'],
                              insertbackground=self.colors['accent_primary'],
                              relief=tk.FLAT, bd=0,
                              **kwargs)
        input_field.pack(padx=1, pady=1, fill=tk.BOTH, expand=True, 
                        ipady=10, ipadx=14)
        
        # Focus effects
        def on_focus_in(e):
            input_container.config(bg=self.colors['accent_primary'])
        
        def on_focus_out(e):
            input_container.config(bg=self.colors['border_glass'])
        
        input_field.bind("<FocusIn>", on_focus_in)
        input_field.bind("<FocusOut>", on_focus_out)
        
        return input_container, input_field
    
    def create_modern_text_area(self, parent, height=4):
        """Cria área de texto moderna"""
        text_container = tk.Frame(parent,
                                 bg=self.colors['border_glass'],
                                 highlightthickness=0, bd=0)
        
        text_widget = tk.Text(text_container, 
                             height=height, wrap=tk.WORD,
                             font=("Inter", 10),
                             bg=self.colors['bg_secondary'],
                             fg=self.colors['text_primary'],
                             insertbackground=self.colors['accent_primary'],
                             relief=tk.FLAT, bd=0,
                             spacing1=4, spacing3=4)
        text_widget.pack(side=tk.LEFT, padx=(1, 0), pady=1, fill=tk.BOTH, expand=True)
        
        # Scrollbar minimalista
        scrollbar = tk.Scrollbar(text_container, command=text_widget.yview,
                                bg=self.colors['bg_secondary'],
                                troughcolor=self.colors['bg_secondary'],
                                activebackground=self.colors['accent_primary'],
                                width=10, bd=0,
                                highlightthickness=0)
        scrollbar.pack(side=tk.RIGHT, fill=tk.Y, padx=(0, 1), pady=1)
        text_widget.config(yscrollcommand=scrollbar.set)
        
        return text_container, text_widget
    
    def create_toggle_switch(self, parent, text, variable):
        """Cria toggle switch moderno ao invés de checkbox"""
        container = tk.Frame(parent, bg=self.colors['bg_card_solid'])
        
        # Label do toggle
        label = tk.Label(container, text=text,
                        font=("Inter", 10),
                        bg=self.colors['bg_card_solid'],
                        fg=self.colors['text_secondary'])
        label.pack(side=tk.LEFT, padx=(0, 12))
        
        # Toggle frame
        toggle_frame = tk.Frame(container, bg=self.colors['bg_secondary'],
                               width=48, height=24, cursor="hand2")
        toggle_frame.pack(side=tk.LEFT)
        toggle_frame.pack_propagate(False)
        
        # Toggle circle
        circle = tk.Frame(toggle_frame, bg=self.colors['text_muted'],
                         width=18, height=18)
        circle.place(x=3, y=3)
        
        def toggle():
            current = variable.get()
            variable.set(not current)
            update_visual()
        
        def update_visual():
            if variable.get():
                toggle_frame.config(bg=self.colors['accent_primary'])
                circle.config(bg=self.colors['text_primary'])
                circle.place(x=27, y=3)
            else:
                toggle_frame.config(bg=self.colors['bg_secondary'])
                circle.config(bg=self.colors['text_muted'])
                circle.place(x=3, y=3)
        
        toggle_frame.bind("<Button-1>", lambda e: toggle())
        circle.bind("<Button-1>", lambda e: toggle())
        
        # Initialize
        update_visual()
        
        return container
    
    def setup_ui(self):
        """Configura interface PREMIUM REFINADA de US$ 200+"""
        
        # ═══════════════════════════════════════════════════════════
        # Background com gradiente sutil
        # ═══════════════════════════════════════════════════════════
        self.bg_canvas = tk.Canvas(self.root, bg=self.colors['bg_primary'],
                                   highlightthickness=0)
        self.bg_canvas.place(x=0, y=0, relwidth=1, relheight=1)
        
        # Criar gradiente sutil no fundo
        self.create_gradient_background()
        
        # Container principal centralizado
        main_container = tk.Frame(self.root, bg='', highlightthickness=0)
        main_container.place(relx=0.5, rely=0.5, anchor='center',
                            relwidth=0.85, relheight=0.92)
        
        # Canvas com scroll suave
        canvas = tk.Canvas(main_container, bg=self.colors['bg_primary'],
                          highlightthickness=0)
        scrollbar = tk.Scrollbar(main_container, orient="vertical", 
                                command=canvas.yview,
                                bg=self.colors['bg_secondary'],
                                troughcolor=self.colors['bg_primary'],
                                activebackground=self.colors['accent_primary'],
                                width=8, bd=0, highlightthickness=0)
        
        scrollable = tk.Frame(canvas, bg=self.colors['bg_primary'])
        scrollable.bind("<Configure>", 
                       lambda e: canvas.configure(scrollregion=canvas.bbox("all")))
        
        canvas.create_window((0, 0), window=scrollable, anchor="nw", width=1000)
        canvas.configure(yscrollcommand=scrollbar.set)
        
        canvas.pack(side=tk.LEFT, fill=tk.BOTH, expand=True)
        scrollbar.pack(side=tk.RIGHT, fill=tk.Y)
        
        # ═══════════════════════════════════════════════════════════
        # 🎯 HEADER MINIMALISTA E ELEGANTE
        # ═══════════════════════════════════════════════════════════
        header = tk.Frame(scrollable, bg=self.colors['bg_primary'])
        header.pack(fill=tk.X, pady=(20, 40))
        
        # Título refinado (não agressivo)
        title_label = tk.Label(header, 
                              text="YouTube Downloader Pro",
                              font=("Inter", 32, "bold"),
                              bg=self.colors['bg_primary'],
                              fg=self.colors['text_primary'])
        title_label.pack()
        
        # Subtítulo sutil
        subtitle = tk.Label(header,
                           text="Premium media downloader with glassmorphism design",
                           font=("Inter", 11),
                           bg=self.colors['bg_primary'],
                           fg=self.colors['text_secondary'])
        subtitle.pack(pady=(8, 0))
        
        # ═══════════════════════════════════════════════════════════
        # 📥 URL INPUT - GLASS CARD
        # ═══════════════════════════════════════════════════════════
        url_card_outer, url_card = self.create_glass_card(scrollable, padding=28)
        url_card_outer.pack(fill=tk.X, pady=(0, 24))
        
        # Header da seção
        url_header = tk.Frame(url_card, bg=self.colors['bg_card_solid'])
        url_header.pack(fill=tk.X, pady=(0, 16))
        
        tk.Label(url_header, text="URLs",
                font=("Inter", 14, "bold"),
                bg=self.colors['bg_card_solid'],
                fg=self.colors['text_primary']).pack(anchor='w')
        
        tk.Label(url_header, text="Paste one or more YouTube URLs (one per line)",
                font=("Inter", 9),
                bg=self.colors['bg_card_solid'],
                fg=self.colors['text_muted']).pack(anchor='w', pady=(4, 0))
        
        # Área de texto moderna
        self.url_text_frame, self.url_text = self.create_modern_text_area(url_card, height=4)
        self.url_text_frame.pack(fill=tk.X)
        
        # ═══════════════════════════════════════════════════════════
        # 🎬 SELEÇÃO DE TIPO - BOTÕES MINIMALISTAS
        # ═══════════════════════════════════════════════════════════
        type_card_outer, type_card = self.create_glass_card(scrollable, padding=28)
        type_card_outer.pack(fill=tk.X, pady=(0, 24))
        
        tk.Label(type_card, text="Media Type",
                font=("Inter", 14, "bold"),
                bg=self.colors['bg_card_solid'],
                fg=self.colors['text_primary']).pack(anchor='w', pady=(0, 16))
        
        # Container para botões
        type_buttons = tk.Frame(type_card, bg=self.colors['bg_card_solid'])
        type_buttons.pack(fill=tk.X)
        
        # Botão de vídeo
        self.video_type_btn = tk.Frame(type_buttons,
                                       bg=self.colors['accent_primary'],
                                       cursor="hand2", highlightthickness=0)
        self.video_type_btn.pack(side=tk.LEFT, padx=(0, 12))
        
        video_inner = tk.Frame(self.video_type_btn, bg=self.colors['accent_primary'])
        video_inner.pack(padx=24, pady=14)
        
        tk.Label(video_inner, text="▶",  # Ícone de linha
                font=("Segoe UI Symbol", 14),
                bg=self.colors['accent_primary'],
                fg=self.colors['text_primary']).pack(side=tk.LEFT, padx=(0, 8))
        
        tk.Label(video_inner, text="Video",
                font=("Inter", 11, "bold"),
                bg=self.colors['accent_primary'],
                fg=self.colors['text_primary']).pack(side=tk.LEFT)
        
        # Botão de áudio
        self.audio_type_btn = tk.Frame(type_buttons,
                                       bg=self.colors['bg_secondary'],
                                       cursor="hand2", highlightthickness=0)
        self.audio_type_btn.pack(side=tk.LEFT)
        
        audio_inner = tk.Frame(self.audio_type_btn, bg=self.colors['bg_secondary'])
        audio_inner.pack(padx=24, pady=14)
        
        tk.Label(audio_inner, text="♪",  # Ícone de linha
                font=("Segoe UI Symbol", 14),
                bg=self.colors['bg_secondary'],
                fg=self.colors['text_secondary']).pack(side=tk.LEFT, padx=(0, 8))
        
        tk.Label(audio_inner, text="Audio",
                font=("Inter", 11, "bold"),
                bg=self.colors['bg_secondary'],
                fg=self.colors['text_secondary']).pack(side=tk.LEFT)
        
        def select_video():
            self.media_type.set("video")
            self.video_type_btn.config(bg=self.colors['accent_primary'])
            video_inner.config(bg=self.colors['accent_primary'])
            for child in video_inner.winfo_children():
                child.config(bg=self.colors['accent_primary'],
                           fg=self.colors['text_primary'])
            
            self.audio_type_btn.config(bg=self.colors['bg_secondary'])
            audio_inner.config(bg=self.colors['bg_secondary'])
            for child in audio_inner.winfo_children():
                child.config(bg=self.colors['bg_secondary'],
                           fg=self.colors['text_secondary'])
            self.update_format_options()
        
        def select_audio():
            self.media_type.set("audio")
            self.audio_type_btn.config(bg=self.colors['accent_primary'])
            audio_inner.config(bg=self.colors['accent_primary'])
            for child in audio_inner.winfo_children():
                child.config(bg=self.colors['accent_primary'],
                           fg=self.colors['text_primary'])
            
            self.video_type_btn.config(bg=self.colors['bg_secondary'])
            video_inner.config(bg=self.colors['bg_secondary'])
            for child in video_inner.winfo_children():
                child.config(bg=self.colors['bg_secondary'],
                           fg=self.colors['text_secondary'])
            self.update_format_options()
        
        # Bind events
        for widget in [self.video_type_btn, video_inner] + list(video_inner.winfo_children()):
            widget.bind("<Button-1>", lambda e: select_video())
        
        for widget in [self.audio_type_btn, audio_inner] + list(audio_inner.winfo_children()):
            widget.bind("<Button-1>", lambda e: select_audio())
        
        # ═══════════════════════════════════════════════════════════
        # ⚙️ CONFIGURAÇÕES AVANÇADAS
        # ═══════════════════════════════════════════════════════════
        config_outer, config_card = self.create_glass_card(scrollable)
        config_outer.pack(fill=tk.X, padx=20, pady=(0, 20))
        
        config_header = tk.Frame(config_card, bg=self.colors['bg_card'])
        config_header.pack(fill=tk.X, padx=25, pady=(20, 15))
        
        tk.Label(config_header, text="⚙️", font=("Segoe UI Emoji", 20),
                bg=self.colors['bg_card'], fg=self.colors['accent_primary']).pack(side=tk.LEFT, padx=(0, 10))
        
        tk.Label(config_header, text="CONFIGURAÇÕES AVANÇADAS",
                font=("Poppins", 14, "bold"),
                bg=self.colors['bg_card'], 
                fg=self.colors['accent_secondary']).pack(side=tk.LEFT)
        
        # Container para opções
        options_container = tk.Frame(config_card, bg=self.colors['bg_card'])
        options_container.pack(fill=tk.X, padx=25, pady=(0, 20))
        
        # OPÇÕES DE VÍDEO
        self.video_options = tk.Frame(options_container, bg=self.colors['bg_card'])
        self.video_options.pack(fill=tk.X, pady=5)
        
        video_row1 = tk.Frame(self.video_options, bg=self.colors['bg_card'])
        video_row1.pack(fill=tk.X, pady=5)
        
        # Formato de vídeo
        fmt_frame = tk.Frame(video_row1, bg=self.colors['bg_card'])
        fmt_frame.pack(side=tk.LEFT, padx=(0, 30))
        
        tk.Label(fmt_frame, text="📹 Formato:",
                font=("Poppins", 10, "bold"),
                bg=self.colors['bg_card'], 
                fg=self.colors['text_primary']).pack(anchor='w', pady=(0, 5))
        
        video_fmt_combo = ttk.Combobox(fmt_frame, textvariable=self.video_format,
                                      state="readonly", width=15,
                                      font=("Poppins", 10))
        video_fmt_combo['values'] = ("mp4", "mkv", "webm", "avi", "mov")
        video_fmt_combo.pack()
        
        # Qualidade de vídeo
        qual_frame = tk.Frame(video_row1, bg=self.colors['bg_card'])
        qual_frame.pack(side=tk.LEFT)
        
        tk.Label(qual_frame, text="📊 Qualidade:",
                font=("Poppins", 10, "bold"),
                bg=self.colors['bg_card'], 
                fg=self.colors['text_primary']).pack(anchor='w', pady=(0, 5))
        
        video_qual_combo = ttk.Combobox(qual_frame, textvariable=self.video_quality,
                                       state="readonly", width=18,
                                       font=("Poppins", 10))
        video_qual_combo['values'] = ("2160p (4K)", "1440p (2K)", "1080p (FHD)", 
                                      "720p (HD)", "480p", "360p", "best")
        video_qual_combo.pack()
        
        # OPÇÕES DE ÁUDIO
        self.audio_options = tk.Frame(options_container, bg=self.colors['bg_card'])
        
        audio_row1 = tk.Frame(self.audio_options, bg=self.colors['bg_card'])
        audio_row1.pack(fill=tk.X, pady=5)
        
        # Formato de áudio
        afmt_frame = tk.Frame(audio_row1, bg=self.colors['bg_card'])
        afmt_frame.pack(side=tk.LEFT, padx=(0, 30))
        
        tk.Label(afmt_frame, text="🎵 Formato:",
                font=("Poppins", 10, "bold"),
                bg=self.colors['bg_card'], 
                fg=self.colors['text_primary']).pack(anchor='w', pady=(0, 5))
        
        audio_fmt_combo = ttk.Combobox(afmt_frame, textvariable=self.audio_format,
                                      state="readonly", width=15,
                                      font=("Poppins", 10))
        audio_fmt_combo['values'] = ("mp3", "aac", "opus", "m4a", "flac", "wav")
        audio_fmt_combo.pack()
        
        # Bitrate
        bitrate_frame = tk.Frame(audio_row1, bg=self.colors['bg_card'])
        bitrate_frame.pack(side=tk.LEFT)
        
        tk.Label(bitrate_frame, text="📊 Bitrate:",
                font=("Poppins", 10, "bold"),
                bg=self.colors['bg_card'], 
                fg=self.colors['text_primary']).pack(anchor='w', pady=(0, 5))
        
        audio_qual_combo = ttk.Combobox(bitrate_frame, textvariable=self.audio_quality,
                                       state="readonly", width=18,
                                       font=("Poppins", 10))
        audio_qual_combo['values'] = ("320 kbps", "256 kbps", "192 kbps", "128 kbps", "96 kbps")
        audio_qual_combo.pack()
        
        # OPÇÕES EXTRAS COM CHECKBOXES MODERNOS
        extras = tk.Frame(config_card, bg=self.colors['bg_card'])
        extras.pack(fill=tk.X, padx=25, pady=(10, 20))
        
        # Linha 1 de checkboxes
        extras_row1 = tk.Frame(extras, bg=self.colors['bg_card'])
        extras_row1.pack(fill=tk.X, pady=5)
        
        self.create_toggle_switch(extras_row1, "📸 Incluir Thumbnail", 
                                    self.embed_thumbnail).pack(side=tk.LEFT, padx=(0, 30))
        self.create_toggle_switch(extras_row1, "📝 Incluir Metadados", 
                                    self.embed_metadata).pack(side=tk.LEFT, padx=(0, 30))
        self.create_toggle_switch(extras_row1, "📋 Baixar Playlist Completa", 
                                    self.download_playlist).pack(side=tk.LEFT)
        
        # ═══════════════════════════════════════════════════════════
        # 🔐 AUTENTICAÇÃO
        # ═══════════════════════════════════════════════════════════
        auth_outer, auth_card = self.create_glass_card(scrollable)
        auth_outer.pack(fill=tk.X, padx=20, pady=(0, 20))
        
        auth_header = tk.Frame(auth_card, bg=self.colors['bg_card'])
        auth_header.pack(fill=tk.X, padx=25, pady=(20, 15))
        
        tk.Label(auth_header, text="🔐", font=("Segoe UI Emoji", 20),
                bg=self.colors['bg_card'], fg=self.colors['accent_primary']).pack(side=tk.LEFT, padx=(0, 10))
        
        tk.Label(auth_header, text="AUTENTICAÇÃO",
                font=("Poppins", 14, "bold"),
                bg=self.colors['bg_card'], 
                fg=self.colors['accent_secondary']).pack(side=tk.LEFT)
        
        auth_content = tk.Frame(auth_card, bg=self.colors['bg_card'])
        auth_content.pack(fill=tk.X, padx=25, pady=(0, 20))
        
        self.create_toggle_switch(auth_content, "🍪 Usar cookies do navegador (recomendado)",
                                    self.use_cookies_var).pack(side=tk.LEFT, padx=(0, 20))
        
        browser_frame = tk.Frame(auth_content, bg=self.colors['bg_card'])
        browser_frame.pack(side=tk.LEFT)
        
        tk.Label(browser_frame, text="🌐 Navegador:",
                font=("Poppins", 10, "bold"),
                bg=self.colors['bg_card'], 
                fg=self.colors['text_primary']).pack(side=tk.LEFT, padx=(0, 10))
        
        browser_combo = ttk.Combobox(browser_frame, textvariable=self.browser_var,
                                    state="readonly", width=12,
                                    font=("Poppins", 10))
        browser_combo['values'] = ("chrome", "firefox", "edge", "brave", "opera", "safari")
        browser_combo.pack(side=tk.LEFT)
        
        # ═══════════════════════════════════════════════════════════
        # 📁 PASTA DE DESTINO
        # ═══════════════════════════════════════════════════════════
        path_outer, path_card = self.create_glass_card(scrollable)
        path_outer.pack(fill=tk.X, padx=20, pady=(0, 20))
        
        path_header = tk.Frame(path_card, bg=self.colors['bg_card'])
        path_header.pack(fill=tk.X, padx=25, pady=(20, 15))
        
        tk.Label(path_header, text="📁", font=("Segoe UI Emoji", 20),
                bg=self.colors['bg_card'], fg=self.colors['accent_primary']).pack(side=tk.LEFT, padx=(0, 10))
        
        tk.Label(path_header, text="PASTA DE DESTINO",
                font=("Poppins", 14, "bold"),
                bg=self.colors['bg_card'], 
                fg=self.colors['accent_secondary']).pack(side=tk.LEFT)
        
        path_content = tk.Frame(path_card, bg=self.colors['bg_card'])
        path_content.pack(fill=tk.X, padx=25, pady=(0, 20))
        
        path_entry_frame, path_entry = self.create_modern_input(path_content,
                                                                textvariable=self.download_path)
        path_entry_frame.pack(side=tk.LEFT, fill=tk.X, expand=True, padx=(0, 15))
        
        browse_frame, browse_label = self.create_premium_button(path_content, 
                                                               "📂 PROCURAR", 
                                                               self.browse_folder,
                                                               style='secondary')
        browse_frame.pack(side=tk.LEFT)
        
        # ═══════════════════════════════════════════════════════════
        # 🚀 BOTÕES DE AÇÃO PRINCIPAIS
        # ═══════════════════════════════════════════════════════════
        actions = tk.Frame(scrollable, bg=self.colors['bg_primary'])
        actions.pack(pady=(10, 20))
        
        # Botão principal GIGANTE
        self.main_btn_frame, self.main_btn_label = self.create_premium_button(
            actions, "⚡ BAIXAR AGORA ⚡", self.start_download, 
            style='primary')
        self.main_btn_frame.pack(side=tk.LEFT, padx=10)
        
        # Configurar tamanho maior
        self.main_btn_label.config(font=("Poppins", 14, "bold"), pady=18)
        
        info_frame, _ = self.create_premium_button(actions, "ℹ️ INFO", 
                                                  self.show_info, 
                                                  style='secondary')
        info_frame.pack(side=tk.LEFT, padx=10)
        
        clear_frame, _ = self.create_premium_button(actions, "🗑️ LIMPAR", 
                                                   self.clear_fields, 
                                                   style='secondary')
        clear_frame.pack(side=tk.LEFT, padx=10)
        
        # ═══════════════════════════════════════════════════════════
        # 📊 PROGRESSO COM ANIMAÇÃO
        # ═══════════════════════════════════════════════════════════
        progress_outer, progress_card = self.create_glass_card(scrollable)
        progress_outer.pack(fill=tk.X, padx=20, pady=(0, 20))
        
        progress_header = tk.Frame(progress_card, bg=self.colors['bg_card'])
        progress_header.pack(fill=tk.X, padx=25, pady=(20, 15))
        
        tk.Label(progress_header, text="📊", font=("Segoe UI Emoji", 20),
                bg=self.colors['bg_card'], fg=self.colors['accent_primary']).pack(side=tk.LEFT, padx=(0, 10))
        
        tk.Label(progress_header, text="PROGRESSO",
                font=("Poppins", 14, "bold"),
                bg=self.colors['bg_card'], 
                fg=self.colors['accent_secondary']).pack(side=tk.LEFT)
        
        # Container da barra de progresso
        progress_container = tk.Frame(progress_card, bg=self.colors['accent_primary'])
        progress_container.pack(fill=tk.X, padx=25, pady=(0, 15))
        
        self.progress_var = tk.DoubleVar()
        self.progress_bar = ttk.Progressbar(progress_container,
                                           variable=self.progress_var,
                                           maximum=100,
                                           style="Gradient.Horizontal.TProgressbar")
        self.progress_bar.pack(fill=tk.X, padx=3, pady=3, ipady=10)
        
        # Status label
        self.status_label = tk.Label(progress_card, 
                                     text="🟢 PRONTO PARA INICIAR",
                                     bg=self.colors['bg_card'],
                                     fg=self.colors['success'],
                                     font=("Poppins", 11, "bold"))
        self.status_label.pack(pady=(0, 20))
        
        # ═══════════════════════════════════════════════════════════
        # 💻 CONSOLE CYBERPUNK
        # ═══════════════════════════════════════════════════════════
        console_outer, console_card = self.create_glass_card(scrollable)
        console_outer.pack(fill=tk.BOTH, expand=True, padx=20, pady=(0, 20))
        
        console_header = tk.Frame(console_card, bg=self.colors['bg_card'])
        console_header.pack(fill=tk.X, padx=25, pady=(20, 15))
        
        tk.Label(console_header, text="💻", font=("Segoe UI Emoji", 20),
                bg=self.colors['bg_card'], fg=self.colors['accent_primary']).pack(side=tk.LEFT, padx=(0, 10))
        
        tk.Label(console_header, text="CONSOLE",
                font=("Poppins", 14, "bold"),
                bg=self.colors['bg_card'], 
                fg=self.colors['accent_secondary']).pack(side=tk.LEFT)
        
        tk.Label(console_header, text="· Logs em tempo real",
                font=("Poppins", 9),
                bg=self.colors['bg_card'], 
                fg=self.colors['text_muted']).pack(side=tk.LEFT, padx=(10, 0))
        
        console_text_frame, self.log_text = self.create_modern_text_area(console_card, height=10)
        console_text_frame.pack(fill=tk.BOTH, expand=True, padx=25, pady=(0, 20))
        
        self.log_text.config(bg=self.colors['bg_primary'], 
                            fg=self.colors['success'],
                            font=("Fira Code", 9))
        
        # Inicializar com opções de vídeo
        self.update_format_options()
        
        # Desenhar partículas de fundo (commented for now)
        # self.draw_particles()
    
    def check_ffmpeg(self):
        """Verifica e instala FFmpeg se necessário"""
        # Verificar se FFmpeg está disponível
        try:
            result = subprocess.run(['ffmpeg', '-version'], 
                                  capture_output=True, 
                                  timeout=5)
            if result.returncode == 0:
                self.log("✅ FFmpeg detectado no sistema")
                return True
        except (FileNotFoundError, subprocess.TimeoutExpired):
            pass
        
        # FFmpeg não encontrado - oferecer instalação automática
        response = messagebox.askyesno(
            "FFmpeg não encontrado",
            "FFmpeg é necessário para mesclar vídeo e áudio de alta qualidade.\n\n"
            "Deseja baixar e instalar automaticamente?\n"
            "(~100MB, recomendado para melhor qualidade)"
        )
        
        if response:
            self.install_ffmpeg()
        else:
            self.log("⚠️ FFmpeg não instalado - algumas funções limitadas")
    
    def install_ffmpeg(self):
        """Baixa e instala FFmpeg automaticamente"""
        import platform
        
        self.log("📦 Baixando FFmpeg...")
        
        # Diretório local do app
        if getattr(sys, 'frozen', False):
            # Rodando como .exe
            app_dir = Path(sys.executable).parent
        else:
            # Rodando como script
            app_dir = Path(__file__).parent
        
        ffmpeg_dir = app_dir / "ffmpeg"
        ffmpeg_dir.mkdir(exist_ok=True)
        
        try:
            system = platform.system()
            
            if system == "Windows":
                # URL do FFmpeg para Windows
                url = "https://github.com/BtbN/FFmpeg-Builds/releases/download/latest/ffmpeg-master-latest-win64-gpl.zip"
                zip_path = ffmpeg_dir / "ffmpeg.zip"
                
                # Baixar
                self.log("⏳ Baixando FFmpeg para Windows...")
                urllib.request.urlretrieve(url, zip_path)
                
                # Extrair
                self.log("📂 Extraindo FFmpeg...")
                with zipfile.ZipFile(zip_path, 'r') as zip_ref:
                    zip_ref.extractall(ffmpeg_dir)
                
                # Encontrar executável
                for root, dirs, files in os.walk(ffmpeg_dir):
                    if 'ffmpeg.exe' in files:
                        ffmpeg_exe = Path(root) / 'ffmpeg.exe'
                        # Adicionar ao PATH
                        os.environ['PATH'] = str(ffmpeg_exe.parent) + os.pathsep + os.environ['PATH']
                        self.log(f"✅ FFmpeg instalado: {ffmpeg_exe}")
                        break
                
                # Limpar
                zip_path.unlink()
                
            elif system == "Linux":
                self.log("🐧 Sistema Linux detectado")
                self.log("Execute: sudo apt install ffmpeg")
                messagebox.showinfo(
                    "Instalação Manual",
                    "No Linux, instale via terminal:\n\n"
                    "sudo apt install ffmpeg\n"
                    "ou\n"
                    "sudo pacman -S ffmpeg"
                )
                
            elif system == "Darwin":  # macOS
                self.log("🍎 Sistema macOS detectado")
                self.log("Execute: brew install ffmpeg")
                messagebox.showinfo(
                    "Instalação Manual",
                    "No macOS, instale via Homebrew:\n\n"
                    "brew install ffmpeg"
                )
            
        except Exception as e:
            self.log(f"❌ Erro ao instalar FFmpeg: {e}")
            messagebox.showerror("Erro", f"Falha ao instalar FFmpeg:\n{e}")
    
    def draw_particles(self):
        """Desenha partículas animadas no fundo"""
        self.bg_canvas.delete("particle")
        
        for particle in self.particle_positions:
            alpha = int(self.pulse_state * 100) + 100
            color = f"#{alpha:02x}{alpha//2:02x}{alpha:02x}"
            self.bg_canvas.create_oval(
                particle['x'] - particle['size'],
                particle['y'] - particle['size'],
                particle['x'] + particle['size'],
                particle['y'] + particle['size'],
                fill=color, outline='',
                tags="particle"
            )
        
        self.root.after(50, self.draw_particles)
    
    def create_toggle_switch(self, parent, text, variable):
        """Cria checkbox moderno"""
        frame = tk.Frame(parent, bg=self.colors['bg_card'])
        
        check = tk.Checkbutton(frame, text=text, variable=variable,
                              bg=self.colors['bg_card'],
                              fg=self.colors['text_primary'],
                              selectcolor=self.colors['bg_secondary'],
                              activebackground=self.colors['bg_card'],
                              activeforeground=self.colors['accent_primary'],
                              font=("Poppins", 10),
                              cursor="hand2")
        check.pack()
        
        return frame
    
    def create_gradient_background(self):
        """Cria fundo com gradiente sutil Deep Navy to Purple"""
        height = 900
        width = 1200
        for i in range(0, height, 2):
            ratio = i / height
            # Gradiente de #0f0f23 para #1a1a3e
            r = int(15 + (26 - 15) * ratio)
            g = int(15 + (26 - 15) * ratio)  
            b = int(35 + (62 - 35) * ratio)
            color = f'#{r:02x}{g:02x}{b:02x}'
            self.bg_canvas.create_line(0, i, width, i, fill=color, width=2, tags="gradient")
    
    def update_format_options(self):
        """Atualiza opções baseado no tipo de mídia"""
        if self.media_type.get() == "video":
            self.video_options.pack(fill=tk.X, pady=5)
            self.audio_options.pack_forget()
        else:
            self.video_options.pack_forget()
            self.audio_options.pack(fill=tk.X, pady=5)
    
    def browse_folder(self):
        """Abre diálogo para selecionar pasta"""
        folder = filedialog.askdirectory(initialdir=self.download_path.get(),
                                        title="Escolha a pasta de destino")
        if folder:
            self.download_path.set(folder)
            self.log(f"📁 Pasta selecionada: {folder}")
    
    def log(self, message):
        """Adiciona mensagem ao console com timestamp"""
        timestamp = time.strftime("%H:%M:%S")
        self.log_text.insert(tk.END, f"[{timestamp}] {message}\n")
        self.log_text.see(tk.END)
        self.root.update_idletasks()
    
    def clear_fields(self):
        """Limpa os campos"""
        self.url_text.delete(1.0, tk.END)
        self.log_text.delete(1.0, tk.END)
        self.progress_var.set(0)
        self.status_label.config(text="🟢 PRONTO PARA INICIAR", 
                                fg=self.colors['success'])
        self.log("🧹 Campos limpos")
    
    def progress_hook(self, d):
        """Atualiza progresso do download"""
        if d['status'] == 'downloading':
            try:
                percent_str = d.get('_percent_str', '0%').strip()
                percent = float(percent_str.replace('%', ''))
                self.progress_var.set(percent)
                
                speed = d.get('_speed_str', 'N/A')
                eta = d.get('_eta_str', 'N/A')
                
                status_text = f"⚡ BAIXANDO: {percent_str} · {speed} · ETA: {eta}"
                self.status_label.config(text=status_text, fg=self.colors['accent_secondary'])
            except:
                pass
        elif d['status'] == 'finished':
            self.progress_var.set(100)
            self.status_label.config(text="🔄 PROCESSANDO ARQUIVO...",
                                    fg=self.colors['warning'])
    
    def check_ffmpeg(self):
        """Verifica se o FFmpeg está instalado"""
        try:
            import subprocess
            # Tentar locais comuns do FFmpeg
            ffmpeg_paths = [
                'ffmpeg',
                r'C:\ffmpeg\ffmpeg-master-latest-win64-gpl\bin\ffmpeg.exe',
                r'C:\ffmpeg\bin\ffmpeg.exe',
            ]
            
            for ffmpeg_path in ffmpeg_paths:
                try:
                    result = subprocess.run([ffmpeg_path, '-version'], 
                                           capture_output=True, 
                                           timeout=5,
                                           creationflags=subprocess.CREATE_NO_WINDOW if os.name == 'nt' else 0)
                    if result.returncode == 0:
                        return ffmpeg_path
                except:
                    continue
            return None
        except:
            return None
    
    def start_download(self):
        """Inicia o download em uma thread separada"""
        urls_text = self.url_text.get(1.0, tk.END).strip()
        
        if not urls_text:
            messagebox.showwarning("⚠️ Aviso", "Por favor, insira pelo menos uma URL!")
            return
        
        if self.is_downloading:
            messagebox.showinfo("⚠️ Aviso", "Já existe um download em andamento!")
            return
        
        # Verificar FFmpeg se for áudio
        if self.media_type.get() == "audio" and not self.check_ffmpeg():
            response = messagebox.askyesno(
                "⚠️ FFmpeg não encontrado",
                "O FFmpeg não está instalado. Ele é necessário para converter áudio.\n\n"
                "Deseja baixar o áudio no formato original (WebM/M4A) sem conversão?\n\n"
                "• Sim = Baixar sem converter (WebM/M4A)\n"
                "• Não = Cancelar (instale o FFmpeg primeiro)\n\n"
                "Veja INSTALL_FFMPEG.md para instruções de instalação."
            )
            if not response:
                return
        
        # Desabilitar botão durante download
        self.main_btn_label.config(text="⏳ BAIXANDO...")
        self.main_btn_frame.config(bg=self.colors['warning'])
        self.is_downloading = True
        
        # Parsear URLs (uma por linha)
        urls = [u.strip() for u in urls_text.split('\n') if u.strip()]
        
        self.log(f"\n{'━'*70}")
        self.log(f"🚀 INICIANDO DOWNLOADS: {len(urls)} URL(s)")
        self.log(f"{'━'*70}\n")
        
        # Executar download em thread separada
        thread = threading.Thread(target=self.download_multiple, args=(urls,))
        thread.daemon = True
        thread.start()
    
    def download_single_video(self, video_url, output_path, ydl_opts_base):
        """Baixa um único vídeo/música"""
        try:
            ydl_opts = ydl_opts_base.copy()
            ydl_opts['outtmpl'] = os.path.join(output_path, '%(title)s.%(ext)s')
            ydl_opts['progress_hooks'] = []  # Remover hook para downloads paralelos
            
            with yt_dlp.YoutubeDL(ydl_opts) as ydl:
                info = ydl.extract_info(video_url, download=True)
                return info.get('title', 'Unknown')
        except Exception as e:
            return None
    
    def get_download_options(self):
        """Retorna configurações de download baseado nas opções selecionadas"""
        ydl_opts = {
            'quiet': True,
            'no_warnings': True,
            'concurrent_fragment_downloads': 16,
            'socket_timeout': 30,
        }
        
        # Adicionar cookies do navegador se habilitado
        if self.use_cookies_var.get():
            ydl_opts['cookiesfrombrowser'] = (self.browser_var.get(),)
            self.log(f"🔐 Usando cookies do {self.browser_var.get().title()}")
        
        media_type = self.media_type.get()
        
        if media_type == "video":
            # Configurações de vídeo
            video_format = self.video_format.get()
            quality = self.video_quality.get().split()[0]  # Pega só a parte numérica
            
            if quality == "best":
                format_string = f'bestvideo[ext={video_format}]+bestaudio[ext=m4a]/best[ext={video_format}]/best'
            else:
                height = quality.replace('p', '')
                format_string = f'bestvideo[height<={height}][ext={video_format}]+bestaudio/best[height<={height}]'
            
            ydl_opts['format'] = format_string
            ydl_opts['merge_output_format'] = video_format
            
            self.log(f"🎬 Formato: {video_format.upper()} | Qualidade: {quality}")
            
        else:  # audio
            # Configurações de áudio
            audio_format = self.audio_format.get()
            bitrate = self.audio_quality.get().split()[0]  # Pega só o número
            
            ydl_opts['format'] = 'bestaudio/best'
            
            # Verificar FFmpeg
            ffmpeg_path = self.check_ffmpeg()
            if ffmpeg_path:
                ydl_opts['postprocessors'] = [{
                    'key': 'FFmpegExtractAudio',
                    'preferredcodec': audio_format,
                    'preferredquality': bitrate,
                }]
                
                if os.path.isfile(str(ffmpeg_path)):
                    ydl_opts['ffmpeg_location'] = os.path.dirname(ffmpeg_path)
                
                self.log(f"🎵 Formato: {audio_format.upper()} | Bitrate: {bitrate} kbps")
            else:
                self.log(f"⚠️ FFmpeg não encontrado - usando formato original")
        
        # Adicionar thumbnail se selecionado
        if self.embed_thumbnail.get():
            if 'postprocessors' not in ydl_opts:
                ydl_opts['postprocessors'] = []
            
            ydl_opts['postprocessors'].append({
                'key': 'EmbedThumbnail',
            })
            ydl_opts['writethumbnail'] = True
            self.log(f"📸 Thumbnail será incluída")
        
        # Adicionar metadados se selecionado
        if self.embed_metadata.get():
            if 'postprocessors' not in ydl_opts:
                ydl_opts['postprocessors'] = []
            
            ydl_opts['postprocessors'].append({
                'key': 'FFmpegMetadata',
                'add_metadata': True,
            })
            self.log(f"📝 Metadados serão incluídos")
        
        return ydl_opts
    
    def download_multiple(self, urls):
        """Baixa múltiplas URLs/playlists em paralelo"""
        try:
            total_downloaded = 0
            total_failed = 0
            
            for idx, url in enumerate(urls, 1):
                self.log(f"\n╔{'═'*68}╗")
                self.log(f"║ 📥 ITEM {idx}/{len(urls):<55} ║")
                self.log(f"║ {url[:66]:<66} ║")
                self.log(f"╚{'═'*68}╝\n")
                
                try:
                    completed, failed, item_name = self.download_item(url, idx, len(urls))
                    total_downloaded += completed
                    total_failed += failed
                except Exception as e:
                    self.log(f"❌ ERRO: {str(e)}")
                    total_failed += 1
            
            # Finalização
            self.log(f"\n╔{'═'*68}╗")
            self.log(f"║{'  🎉 PROCESSO CONCLUÍDO!  ':^70}║")
            self.log(f"╠{'═'*68}╣")
            self.log(f"║  ✅ Sucesso: {total_downloaded:>5} arquivos{' '*35}║")
            self.log(f"║  ❌ Falhas:  {total_failed:>5} arquivos{' '*35}║")
            self.log(f"╚{'═'*68}╝\n")
            
            self.status_label.config(text=f"✅ CONCLUÍDO: {total_downloaded} downloads",
                                    fg=self.colors['success'])
            
            messagebox.showinfo("✅ Concluído", 
                              f"Downloads finalizados com sucesso!\n\n"
                              f"✅ {total_downloaded} arquivos baixados\n"
                              f"❌ {total_failed} falharam")
            
        except Exception as e:
            error_msg = str(e)
            self.log(f"❌ ERRO CRÍTICO: {error_msg}")
            self.status_label.config(text="❌ ERRO NO DOWNLOAD",
                                    fg=self.colors['error'])
            messagebox.showerror("❌ Erro", f"Erro ao processar downloads:\n{error_msg}")
        finally:
            self.main_btn_label.config(text="⚡ BAIXAR AGORA ⚡")
            self.main_btn_frame.config(bg=self.colors['accent_primary'])
            self.is_downloading = False
            self.progress_var.set(0)
    
    def download_item(self, url, item_num, total_items):
        """Baixa um vídeo individual ou playlist completa"""
        try:
            output_base = self.download_path.get()
            
            # Obter configurações otimizadas
            ydl_opts_base = self.get_download_options()
            
            # Verificar se deve baixar playlist
            should_download_playlist = self.download_playlist.get()
            
            # Obter informações
            self.log(f"🔍 Analisando URL...")
            self.status_label.config(text="🔍 ANALISANDO URL...",
                                    fg=self.colors['accent_secondary'])
            
            check_opts = {'quiet': True, 'extract_flat': True}
            if self.use_cookies_var.get():
                check_opts['cookiesfrombrowser'] = (self.browser_var.get(),)
            
            with yt_dlp.YoutubeDL(check_opts) as ydl:
                info = ydl.extract_info(url, download=False)
            
            is_playlist = info and '_type' in info and info['_type'] == 'playlist'
            
            # Se for playlist e o usuário quer baixar playlists
            if is_playlist and should_download_playlist:
                return self.download_full_playlist(url, item_num, total_items, info, ydl_opts_base, output_base)
            else:
                # Baixar apenas um vídeo
                self.log(f"📥 Baixando vídeo individual...")
                self.status_label.config(text="📥 BAIXANDO VÍDEO...",
                                        fg=self.colors['accent_secondary'])
                
                ydl_opts_base['outtmpl'] = os.path.join(output_base, '%(title)s.%(ext)s')
                ydl_opts_base['progress_hooks'] = [self.progress_hook]
                
                with yt_dlp.YoutubeDL(ydl_opts_base) as ydl:
                    video_info = ydl.extract_info(url, download=True)
                    title = video_info.get('title', 'Vídeo')
                    self.log(f"✅ CONCLUÍDO: {title}")
                
                return 1, 0, title
                
        except Exception as e:
            self.log(f"❌ ERRO: {str(e)}")
            return 0, 1, "Erro"
    
    def download_full_playlist(self, url, playlist_num, total_playlists, playlist_info, ydl_opts_base, output_base):
        """Baixa playlist completa com paralelização"""
        try:
            # Criar pasta para a playlist
            playlist_title = playlist_info.get('title', f'Playlist_{playlist_num}')
            safe_title = "".join(c for c in playlist_title if c.isalnum() or c in (' ', '-', '_')).strip()
            playlist_folder = os.path.join(output_base, safe_title)
            os.makedirs(playlist_folder, exist_ok=True)
            
            entries = [e for e in playlist_info.get('entries', []) if e]
            total = len(entries)
            
            self.log(f"📋 PLAYLIST: {safe_title}")
            self.log(f"📊 Total de itens: {total}")
            self.log(f"⚡ Modo paralelo: 20 threads\n")
            
            # Contadores thread-safe
            completed = 0
            failed = 0
            lock = threading.Lock()
            
            def download_track(entry):
                nonlocal completed, failed
                try:
                    video_url = entry.get('url') or f"https://www.youtube.com/watch?v={entry.get('id')}"
                    result = self.download_single_video(video_url, playlist_folder, ydl_opts_base)
                    
                    with lock:
                        if result:
                            completed += 1
                            pct = (completed / total) * 100
                            self.progress_var.set(pct)
                            self.status_label.config(
                                text=f"⚡ PROGRESSO: {completed}/{total} ({pct:.0f}%)",
                                fg=self.colors['accent_secondary'])
                            if completed % 5 == 0 or completed == total:
                                self.log(f"⚡ [{completed}/{total}] {pct:.0f}% completo")
                        else:
                            failed += 1
                    return result
                except Exception as ex:
                    with lock:
                        failed += 1
                    return None
            
            # Download paralelo com 20 threads
            self.log(f"🚀 Iniciando downloads paralelos...")
            with ThreadPoolExecutor(max_workers=20) as executor:
                list(executor.map(download_track, entries))
            
            self.log(f"\n✅ PLAYLIST CONCLUÍDA: {safe_title}")
            self.log(f"   💾 {completed} arquivos baixados")
            self.log(f"   ❌ {failed} falharam\n")
            
            return completed, failed, playlist_title
            
        except Exception as e:
            self.log(f"❌ ERRO NA PLAYLIST: {str(e)}")
            return 0, 1, "Erro"
    
    def show_info(self):
        """Mostra informações do vídeo"""
        urls_text = self.url_text.get(1.0, tk.END).strip()
        
        if not urls_text:
            messagebox.showwarning("⚠️ Aviso", "Por favor, insira uma URL válida!")
            return
        
        url = urls_text.split('\n')[0].strip()
        thread = threading.Thread(target=self.get_info, args=(url,))
        thread.daemon = True
        thread.start()
    
    def get_info(self, url):
        """Obtém informações do vídeo"""
        try:
            self.log("🔍 Obtendo informações...")
            self.status_label.config(text="🔍 Analisando vídeo...")
            
            ydl_opts = {
                'quiet': True,
                'no_warnings': True,
            }
            
            if self.use_cookies_var.get():
                ydl_opts['cookiesfrombrowser'] = (self.browser_var.get(),)
            
            with yt_dlp.YoutubeDL(ydl_opts) as ydl:
                info = ydl.extract_info(url, download=False)
                
                title = info.get('title', 'N/A')
                duration = info.get('duration', 0)
                views = info.get('view_count', 'N/A')
                uploader = info.get('uploader', 'N/A')
                upload_date = info.get('upload_date', 'N/A')
                
                minutes, seconds = divmod(duration, 60)
                
                info_msg = f"╔{'═'*50}╗\n"
                info_msg += f"║  🎬 INFORMAÇÕES DO VÍDEO{' '*24}║\n"
                info_msg += f"╠{'═'*50}╣\n"
                info_msg += f"║ 📝 Título: {title[:38]:<38}║\n"
                info_msg += f"║ ⏱️ Duração: {minutes}:{seconds:02d}{' '*38}║\n"
                if isinstance(views, int):
                    info_msg += f"║ 👁️ Views: {views:,}{' '*(42-len(str(views)))}║\n"
                info_msg += f"║ 📺 Canal: {uploader[:39]:<39}║\n"
                info_msg += f"║ 📅 Data: {upload_date}{' '*39}║\n"
                info_msg += f"╚{'═'*50}╝"
                
                self.log("✅ Informações obtidas!")
                self.status_label.config(text="✅ Informações carregadas")
                messagebox.showinfo("ℹ️ Informações do Vídeo", info_msg)
                
        except Exception as e:
            self.log(f"❌ Erro: {str(e)}")
            self.status_label.config(text="❌ Erro ao obter informações")
            messagebox.showerror("❌ Erro", f"Erro ao obter informações:\n{str(e)}")


def main():
    root = tk.Tk()
    app = YouTubeDownloaderGUI(root)
    root.mainloop()


if __name__ == "__main__":
    main()
