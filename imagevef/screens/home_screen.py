"""
Home Screen - Main navigation hub
"""
import customtkinter as ctk
from PIL import Image, ImageDraw
import os


class HomeScreen(ctk.CTkFrame):
    """Home screen with navigation to main features."""
    
    def __init__(self, parent, controller):
        super().__init__(parent, fg_color="transparent")
        self.controller = controller
        
        self.setup_ui()
    
    def setup_ui(self):
        """Setup the home screen UI."""
        # Configure grid
        self.grid_columnconfigure(0, weight=1)
        self.grid_rowconfigure(0, weight=1)
        self.grid_rowconfigure(1, weight=2)
        self.grid_rowconfigure(2, weight=1)
        
        # Header section
        header_frame = ctk.CTkFrame(self, fg_color="transparent")
        header_frame.grid(row=0, column=0, sticky="ew", padx=40, pady=(40, 20))
        
        # Logo/Title with animated gradient effect
        title_label = ctk.CTkLabel(
            header_frame,
            text="🔐 FACE ID",
            font=ctk.CTkFont(family="Segoe UI", size=48, weight="bold"),
            text_color=("#1a1a2e", "#00d9ff")
        )
        title_label.pack()
        
        subtitle = ctk.CTkLabel(
            header_frame,
            text="Sistema de Reconhecimento Facial",
            font=ctk.CTkFont(family="Segoe UI", size=16),
            text_color=("gray40", "gray60")
        )
        subtitle.pack(pady=(5, 0))
        
        # Main buttons section
        buttons_frame = ctk.CTkFrame(self, fg_color="transparent")
        buttons_frame.grid(row=1, column=0, sticky="nsew", padx=40)
        buttons_frame.grid_columnconfigure((0, 1), weight=1)
        buttons_frame.grid_rowconfigure((0, 1), weight=1)
        
        # Register button
        self.register_btn = self.create_feature_button(
            buttons_frame,
            "➕",
            "REGISTRAR",
            "Adicionar novo rosto",
            "#7b2cbf",
            lambda: self.controller.show_screen("register")
        )
        self.register_btn.grid(row=0, column=0, padx=20, pady=20, sticky="nsew")
        
        # Verify button  
        self.verify_btn = self.create_feature_button(
            buttons_frame,
            "🔍",
            "VERIFICAR",
            "Identificar rosto",
            "#00d9ff",
            lambda: self.controller.show_screen("verify")
        )
        self.verify_btn.grid(row=0, column=1, padx=20, pady=20, sticky="nsew")
        
        # Settings button
        self.settings_btn = self.create_feature_button(
            buttons_frame,
            "⚙️",
            "CONFIGURAÇÕES",
            "Gerenciar sistema",
            "#ff6b6b",
            lambda: self.controller.show_screen("settings")
        )
        self.settings_btn.grid(row=1, column=0, columnspan=2, padx=20, pady=20, sticky="ew")
        
        # Stats section
        stats_frame = ctk.CTkFrame(
            self,
            fg_color=("gray90", "#2d2d4a"),
            corner_radius=15
        )
        stats_frame.grid(row=2, column=0, sticky="ew", padx=40, pady=(20, 40))
        
        self.stats_label = ctk.CTkLabel(
            stats_frame,
            text="",
            font=ctk.CTkFont(family="Segoe UI", size=14),
            text_color=("gray40", "gray60")
        )
        self.stats_label.pack(pady=20)
        
        self.update_stats()
    
    def create_feature_button(self, parent, icon: str, title: str, 
                              description: str, color: str, command) -> ctk.CTkFrame:
        """Create a styled feature button."""
        frame = ctk.CTkFrame(
            parent,
            fg_color=("gray95", "#252542"),
            corner_radius=20,
            border_width=2,
            border_color=("gray80", "#3d3d5c")
        )
        
        # Make entire frame clickable
        frame.bind("<Button-1>", lambda e: command())
        frame.bind("<Enter>", lambda e: self.on_button_hover(frame, color, True))
        frame.bind("<Leave>", lambda e: self.on_button_hover(frame, color, False))
        
        # Content
        content = ctk.CTkFrame(frame, fg_color="transparent")
        content.pack(expand=True, fill="both", padx=25, pady=25)
        content.bind("<Button-1>", lambda e: command())
        
        # Icon
        icon_label = ctk.CTkLabel(
            content,
            text=icon,
            font=ctk.CTkFont(size=48)
        )
        icon_label.pack(pady=(10, 15))
        icon_label.bind("<Button-1>", lambda e: command())
        
        # Title
        title_label = ctk.CTkLabel(
            content,
            text=title,
            font=ctk.CTkFont(family="Segoe UI", size=20, weight="bold"),
            text_color=color
        )
        title_label.pack()
        title_label.bind("<Button-1>", lambda e: command())
        
        # Description
        desc_label = ctk.CTkLabel(
            content,
            text=description,
            font=ctk.CTkFont(family="Segoe UI", size=12),
            text_color=("gray40", "gray60")
        )
        desc_label.pack(pady=(5, 10))
        desc_label.bind("<Button-1>", lambda e: command())
        
        return frame
    
    def on_button_hover(self, frame: ctk.CTkFrame, color: str, entering: bool):
        """Handle button hover effect."""
        if entering:
            frame.configure(
                border_color=color,
                fg_color=("gray90", "#2d2d4a")
            )
        else:
            frame.configure(
                border_color=("gray80", "#3d3d5c"),
                fg_color=("gray95", "#252542")
            )
    
    def update_stats(self):
        """Update the stats display."""
        try:
            count = len(self.controller.face_engine.get_registered_faces())
            self.stats_label.configure(
                text=f"📊 {count} rosto(s) registrado(s) no sistema"
            )
        except:
            self.stats_label.configure(text="📊 Sistema pronto")
    
    def on_show(self):
        """Called when screen is shown."""
        self.update_stats()
