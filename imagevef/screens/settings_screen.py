"""
Settings Screen - System configuration and face management
"""
import customtkinter as ctk
from tkinter import messagebox
from PIL import Image
import cv2
import os
from typing import Optional

import sys
sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from utils.camera import CameraManager


class SettingsScreen(ctk.CTkFrame):
    """Settings screen for system configuration."""
    
    def __init__(self, parent, controller):
        super().__init__(parent, fg_color="transparent")
        self.controller = controller
        
        self.setup_ui()
    
    def setup_ui(self):
        """Setup the settings screen UI."""
        # Configure grid
        self.grid_columnconfigure(0, weight=1)
        self.grid_rowconfigure(1, weight=1)
        
        # Header
        header = ctk.CTkFrame(self, fg_color="transparent")
        header.grid(row=0, column=0, sticky="ew", padx=30, pady=(30, 20))
        
        back_btn = ctk.CTkButton(
            header,
            text="← Voltar",
            width=100,
            height=35,
            fg_color="transparent",
            hover_color=("gray80", "#3d3d5c"),
            text_color=("gray40", "gray60"),
            command=self.go_back
        )
        back_btn.pack(side="left")
        
        title = ctk.CTkLabel(
            header,
            text="⚙️ Configurações",
            font=ctk.CTkFont(family="Segoe UI", size=28, weight="bold"),
            text_color=("#1a1a2e", "#ff6b6b")
        )
        title.pack(side="left", padx=20)
        
        # Main content with scrollable frame
        content = ctk.CTkScrollableFrame(
            self,
            fg_color="transparent"
        )
        content.grid(row=1, column=0, sticky="nsew", padx=30, pady=20)
        content.grid_columnconfigure(0, weight=1)
        
        # Appearance section
        appearance_card = self.create_section_card(
            content,
            "🎨 Aparência",
            "Personalize a interface"
        )
        appearance_card.pack(fill="x", pady=(0, 15))
        
        # Theme toggle
        theme_frame = ctk.CTkFrame(appearance_card, fg_color="transparent")
        theme_frame.pack(fill="x", padx=20, pady=10)
        
        theme_label = ctk.CTkLabel(
            theme_frame,
            text="Tema",
            font=ctk.CTkFont(size=14),
            text_color=("gray30", "gray70")
        )
        theme_label.pack(side="left")
        
        self.theme_switch = ctk.CTkSwitch(
            theme_frame,
            text="Modo Escuro",
            font=ctk.CTkFont(size=13),
            command=self.toggle_theme,
            onvalue=1,
            offvalue=0,
            progress_color=("#00d9ff", "#00d9ff")
        )
        self.theme_switch.pack(side="right")
        
        # Set initial state based on current theme
        if ctk.get_appearance_mode() == "Dark":
            self.theme_switch.select()
        
        # Recognition settings section
        recognition_card = self.create_section_card(
            content,
            "🔍 Reconhecimento",
            "Ajuste a sensibilidade do sistema"
        )
        recognition_card.pack(fill="x", pady=15)
        
        # Tolerance slider
        tolerance_frame = ctk.CTkFrame(recognition_card, fg_color="transparent")
        tolerance_frame.pack(fill="x", padx=20, pady=10)
        
        tolerance_header = ctk.CTkFrame(tolerance_frame, fg_color="transparent")
        tolerance_header.pack(fill="x")
        
        tolerance_label = ctk.CTkLabel(
            tolerance_header,
            text="Sensibilidade",
            font=ctk.CTkFont(size=14),
            text_color=("gray30", "gray70")
        )
        tolerance_label.pack(side="left")
        
        self.tolerance_value_label = ctk.CTkLabel(
            tolerance_header,
            text="60%",
            font=ctk.CTkFont(size=14, weight="bold"),
            text_color=("#00d9ff", "#00d9ff")
        )
        self.tolerance_value_label.pack(side="right")
        
        # Get current tolerance
        current_tolerance = self.controller.face_engine.tolerance
        
        self.tolerance_slider = ctk.CTkSlider(
            tolerance_frame,
            from_=0.3,
            to=0.9,
            number_of_steps=12,
            progress_color=("#00d9ff", "#00d9ff"),
            button_color=("#00d9ff", "#00d9ff"),
            button_hover_color=("#00b8d4", "#00b8d4"),
            command=self.on_tolerance_change
        )
        self.tolerance_slider.pack(fill="x", pady=(10, 5))
        self.tolerance_slider.set(current_tolerance)
        self.update_tolerance_label(current_tolerance)
        
        tolerance_hint = ctk.CTkLabel(
            tolerance_frame,
            text="⬅ Mais rigoroso | Mais tolerante ➡",
            font=ctk.CTkFont(size=11),
            text_color=("gray50", "gray60")
        )
        tolerance_hint.pack()
        
        # Registered faces section
        faces_card = self.create_section_card(
            content,
            "👤 Rostos Registrados",
            "Gerencie os rostos no sistema"
        )
        faces_card.pack(fill="x", pady=15)
        
        # Faces list container
        self.faces_container = ctk.CTkFrame(faces_card, fg_color="transparent")
        self.faces_container.pack(fill="x", padx=20, pady=10)
        
        self.update_faces_list()
        
        # Clear all button
        clear_frame = ctk.CTkFrame(faces_card, fg_color="transparent")
        clear_frame.pack(fill="x", padx=20, pady=(0, 15))
        
        self.clear_all_btn = ctk.CTkButton(
            clear_frame,
            text="🗑️ Limpar Todos",
            height=40,
            font=ctk.CTkFont(size=13),
            fg_color=("#ff4757", "#cc3a47"),
            hover_color=("#cc3a47", "#aa2f3b"),
            corner_radius=10,
            command=self.clear_all_faces
        )
        self.clear_all_btn.pack(side="right")
        
        # About section
        about_card = self.create_section_card(
            content,
            "ℹ️ Sobre",
            "Informações do sistema"
        )
        about_card.pack(fill="x", pady=15)
        
        about_content = ctk.CTkFrame(about_card, fg_color="transparent")
        about_content.pack(fill="x", padx=20, pady=10)
        
        about_text = ctk.CTkLabel(
            about_content,
            text="Face ID v1.0\n\nSistema de Reconhecimento Facial\nDesenvolvido com Python, OpenCV e CustomTkinter",
            font=ctk.CTkFont(size=13),
            text_color=("gray40", "gray60"),
            justify="center"
        )
        about_text.pack(pady=10)
    
    def create_section_card(self, parent, title: str, subtitle: str) -> ctk.CTkFrame:
        """Create a settings section card."""
        card = ctk.CTkFrame(
            parent,
            fg_color=("gray90", "#252542"),
            corner_radius=15
        )
        
        header = ctk.CTkFrame(card, fg_color="transparent")
        header.pack(fill="x", padx=20, pady=(15, 5))
        
        title_label = ctk.CTkLabel(
            header,
            text=title,
            font=ctk.CTkFont(size=18, weight="bold"),
            text_color=("gray20", "gray90")
        )
        title_label.pack(anchor="w")
        
        subtitle_label = ctk.CTkLabel(
            header,
            text=subtitle,
            font=ctk.CTkFont(size=12),
            text_color=("gray50", "gray60")
        )
        subtitle_label.pack(anchor="w")
        
        return card
    
    def toggle_theme(self):
        """Toggle between light and dark theme."""
        if self.theme_switch.get():
            ctk.set_appearance_mode("dark")
        else:
            ctk.set_appearance_mode("light")
    
    def on_tolerance_change(self, value: float):
        """Handle tolerance slider change."""
        self.controller.face_engine.set_tolerance(value)
        self.update_tolerance_label(value)
    
    def update_tolerance_label(self, value: float):
        """Update the tolerance value label."""
        percentage = int((1 - value) * 100)  # Invert for user understanding
        self.tolerance_value_label.configure(text=f"{percentage}%")
    
    def update_faces_list(self):
        """Update the list of registered faces."""
        # Clear existing items
        for widget in self.faces_container.winfo_children():
            widget.destroy()
        
        faces = self.controller.face_engine.get_registered_faces()
        
        if not faces:
            empty_label = ctk.CTkLabel(
                self.faces_container,
                text="Nenhum rosto registrado",
                font=ctk.CTkFont(size=13),
                text_color=("gray50", "gray60")
            )
            empty_label.pack(pady=20)
            return
        
        for name in faces:
            self.create_face_item(name)
    
    def create_face_item(self, name: str):
        """Create a face list item."""
        item = ctk.CTkFrame(
            self.faces_container,
            fg_color=("gray85", "#1a1a2e"),
            corner_radius=10,
            height=50
        )
        item.pack(fill="x", pady=3)
        item.pack_propagate(False)
        
        content = ctk.CTkFrame(item, fg_color="transparent")
        content.pack(fill="both", expand=True, padx=15)
        
        # Face icon or image
        icon_label = ctk.CTkLabel(
            content,
            text="👤",
            font=ctk.CTkFont(size=20)
        )
        icon_label.pack(side="left", padx=(0, 10))
        
        # Try to load thumbnail
        image_path = self.controller.face_engine.get_face_image_path(name)
        if image_path and os.path.exists(image_path):
            try:
                img = cv2.imread(image_path)
                if img is not None:
                    img = CameraManager.resize_frame(img, 35, 35)
                    pil_img = CameraManager.frame_to_pil(img)
                    photo = ctk.CTkImage(light_image=pil_img, dark_image=pil_img,
                                        size=(35, 35))
                    icon_label.configure(image=photo, text="")
                    icon_label.image = photo
            except:
                pass
        
        # Name
        name_label = ctk.CTkLabel(
            content,
            text=name,
            font=ctk.CTkFont(size=14),
            text_color=("gray20", "gray90")
        )
        name_label.pack(side="left")
        
        # Delete button
        delete_btn = ctk.CTkButton(
            content,
            text="🗑️",
            width=35,
            height=35,
            font=ctk.CTkFont(size=14),
            fg_color=("gray80", "#3d3d5c"),
            hover_color=("#ff6b6b", "#ff4757"),
            corner_radius=8,
            command=lambda n=name: self.delete_face(n)
        )
        delete_btn.pack(side="right")
    
    def delete_face(self, name: str):
        """Delete a registered face."""
        # Confirm deletion
        if messagebox.askyesno(
            "Confirmar Exclusão",
            f"Deseja realmente excluir '{name}'?"
        ):
            success, message = self.controller.face_engine.delete_face(name)
            if success:
                self.update_faces_list()
            else:
                messagebox.showerror("Erro", message)
    
    def clear_all_faces(self):
        """Clear all registered faces."""
        faces = self.controller.face_engine.get_registered_faces()
        
        if not faces:
            messagebox.showinfo("Info", "Nenhum rosto para excluir")
            return
        
        if messagebox.askyesno(
            "Confirmar Exclusão",
            f"Deseja realmente excluir todos os {len(faces)} rostos registrados?\n\nEsta ação não pode ser desfeita!"
        ):
            for name in faces.copy():
                self.controller.face_engine.delete_face(name)
            
            self.update_faces_list()
            messagebox.showinfo("Sucesso", "Todos os rostos foram excluídos")
    
    def go_back(self):
        """Go back to home screen."""
        self.controller.show_screen("home")
    
    def on_show(self):
        """Called when screen is shown."""
        self.update_faces_list()
        
        # Update theme switch state
        if ctk.get_appearance_mode() == "Dark":
            self.theme_switch.select()
        else:
            self.theme_switch.deselect()
