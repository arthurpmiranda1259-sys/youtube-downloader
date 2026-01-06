#!/usr/bin/env python3
"""
Face ID Application - Main Entry Point
A modern face recognition system with GUI
With TkinterDnD2 support for drag and drop
"""
import customtkinter as ctk
import os
import sys

# Ensure the app directory is in path
APP_DIR = os.path.dirname(os.path.abspath(__file__))
sys.path.insert(0, APP_DIR)

# Try to import TkinterDnD2 for drag and drop support
DND_AVAILABLE = False
try:
    from tkinterdnd2 import TkinterDnD
    DND_AVAILABLE = True
    print("✅ TkinterDnD2 disponível - Drag and Drop ativado!")
except ImportError:
    print("⚠️ TkinterDnD2 não instalado - Drag and Drop desativado")
    print("   Para ativar: pip install tkinterdnd2")

from face_engine import FaceEngine
from screens.home_screen import HomeScreen
from screens.register_screen import RegisterScreen
from screens.verify_screen import VerifyScreen
from screens.settings_screen import SettingsScreen


class FaceIDApp(ctk.CTk if not DND_AVAILABLE else TkinterDnD.Tk):
    """Main application window with optional DnD support."""
    
    def __init__(self):
        super().__init__()
        
        # Window configuration
        self.title("🔐 Face ID")
        self.geometry("900x700")
        self.minsize(800, 600)
        
        # Set appearance
        ctk.set_appearance_mode("dark")
        ctk.set_default_color_theme("blue")
        
        # Configure colors
        self.configure(bg="#1a1a2e")
        
        # Initialize face engine
        data_dir = os.path.join(APP_DIR, "data")
        self.face_engine = FaceEngine(data_dir)
        
        # Configure grid
        self.grid_columnconfigure(0, weight=1)
        self.grid_rowconfigure(0, weight=1)
        
        # Container for screens
        self.container = ctk.CTkFrame(self, fg_color="transparent")
        self.container.grid(row=0, column=0, sticky="nsew")
        self.container.grid_columnconfigure(0, weight=1)
        self.container.grid_rowconfigure(0, weight=1)
        
        # Initialize screens
        self.screens = {}
        self.current_screen = None
        
        # Create all screens
        self.create_screens()
        
        # Show home screen
        self.show_screen("home")
        
        # Handle window close
        self.protocol("WM_DELETE_WINDOW", self.on_close)
    
    def create_screens(self):
        """Create all application screens."""
        screen_classes = {
            "home": HomeScreen,
            "register": RegisterScreen,
            "verify": VerifyScreen,
            "settings": SettingsScreen
        }
        
        for name, screen_class in screen_classes.items():
            screen = screen_class(self.container, self)
            screen.grid(row=0, column=0, sticky="nsew")
            self.screens[name] = screen
    
    def show_screen(self, name: str):
        """Show a specific screen."""
        if name not in self.screens:
            return
        
        # Hide current screen
        if self.current_screen and hasattr(self.screens.get(self.current_screen), 'on_hide'):
            self.screens[self.current_screen].on_hide()
        
        # Show new screen
        screen = self.screens[name]
        screen.tkraise()
        self.current_screen = name
        
        # Call on_show if exists
        if hasattr(screen, 'on_show'):
            screen.on_show()
    
    def on_close(self):
        """Handle application close."""
        # Stop any active cameras
        for screen in self.screens.values():
            if hasattr(screen, 'on_hide'):
                screen.on_hide()
        
        self.destroy()


def main():
    """Main entry point."""
    print("🔐 Iniciando Face ID...")
    
    # Check for dependencies
    try:
        import cv2
        import numpy
        from PIL import Image
        # Check for cv2.face (LBPH recognizer)
        if not hasattr(cv2, 'face'):
            print("\n❌ opencv-contrib-python não está instalado")
            print("\nInstale com:")
            print("  pip install opencv-contrib-python")
            return
    except ImportError as e:
        print(f"\n❌ Dependência não encontrada: {e}")
        print("\nInstale as dependências com:")
        print("  pip install -r requirements.txt")
        return
    
    # Create and run app
    app = FaceIDApp()
    app.mainloop()


if __name__ == "__main__":
    main()
