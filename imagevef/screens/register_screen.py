"""
Register Screen - Face registration with camera/image support and drag-drop
"""
import customtkinter as ctk
from tkinter import filedialog
from PIL import Image, ImageTk
import cv2
import threading
import numpy as np
from typing import Optional
import os

import sys
sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from utils.animations import LoadingSpinner, SuccessAnimation, ErrorAnimation
from utils.camera import CameraManager, is_camera_available
from utils.drop_zone import is_image_file


class RegisterScreen(ctk.CTkFrame):
    """Screen for registering new faces with drag-drop support."""
    
    def __init__(self, parent, controller):
        super().__init__(parent, fg_color="transparent")
        self.controller = controller
        self.camera = CameraManager()
        self.current_frame: Optional[np.ndarray] = None
        self.is_camera_active = False
        self.face_detected = False
        self.is_drag_over = False
        
        self.setup_ui()
        self._setup_drop_bindings()
    
    def setup_ui(self):
        """Setup the register screen UI."""
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
            text="➕ Registrar Novo Rosto",
            font=ctk.CTkFont(family="Segoe UI", size=28, weight="bold"),
            text_color=("#1a1a2e", "#7b2cbf")
        )
        title.pack(side="left", padx=20)
        
        # Main content
        content = ctk.CTkFrame(self, fg_color="transparent")
        content.grid(row=1, column=0, sticky="nsew", padx=30, pady=20)
        content.grid_columnconfigure((0, 1), weight=1)
        content.grid_rowconfigure(0, weight=1)
        
        # Left side - Image preview (Drop Zone)
        self.preview_frame = ctk.CTkFrame(
            content,
            fg_color=("gray90", "#252542"),
            corner_radius=20,
            border_width=3,
            border_color=("gray80", "#3d3d5c")
        )
        self.preview_frame.grid(row=0, column=0, sticky="nsew", padx=(0, 15))
        
        preview_label = ctk.CTkLabel(
            self.preview_frame,
            text="Preview",
            font=ctk.CTkFont(size=14, weight="bold"),
            text_color=("gray40", "gray60")
        )
        preview_label.pack(pady=(15, 10))
        
        # Image display area (also a drop zone)
        self.image_frame = ctk.CTkFrame(
            self.preview_frame,
            width=400,
            height=350,
            fg_color=("gray85", "#1a1a2e"),
            corner_radius=15,
            border_width=2,
            border_color=("gray75", "#2d2d4a")
        )
        self.image_frame.pack(padx=20, pady=10, fill="both", expand=True)
        self.image_frame.pack_propagate(False)
        
        self.image_label = ctk.CTkLabel(
            self.image_frame,
            text="📷\n\n🖱️ Clique em 'Upload' ou\n\n📂 Arraste uma imagem aqui",
            font=ctk.CTkFont(size=16),
            text_color=("gray50", "gray60")
        )
        self.image_label.pack(expand=True)
        
        # Face detection indicator
        self.face_indicator = ctk.CTkLabel(
            self.preview_frame,
            text="",
            font=ctk.CTkFont(size=12),
            text_color=("gray40", "gray60")
        )
        self.face_indicator.pack(pady=(5, 15))
        
        # Right side - Controls
        controls_frame = ctk.CTkFrame(
            content,
            fg_color=("gray90", "#252542"),
            corner_radius=20
        )
        controls_frame.grid(row=0, column=1, sticky="nsew", padx=(15, 0))
        
        # Name input
        name_section = ctk.CTkFrame(controls_frame, fg_color="transparent")
        name_section.pack(fill="x", padx=25, pady=(25, 15))
        
        name_label = ctk.CTkLabel(
            name_section,
            text="Nome da Pessoa",
            font=ctk.CTkFont(size=14, weight="bold"),
            text_color=("gray30", "gray70")
        )
        name_label.pack(anchor="w")
        
        self.name_entry = ctk.CTkEntry(
            name_section,
            placeholder_text="Digite o nome...",
            height=45,
            font=ctk.CTkFont(size=14),
            corner_radius=10
        )
        self.name_entry.pack(fill="x", pady=(8, 0))
        
        # Source selection
        source_section = ctk.CTkFrame(controls_frame, fg_color="transparent")
        source_section.pack(fill="x", padx=25, pady=15)
        
        source_label = ctk.CTkLabel(
            source_section,
            text="Fonte da Imagem",
            font=ctk.CTkFont(size=14, weight="bold"),
            text_color=("gray30", "gray70")
        )
        source_label.pack(anchor="w", pady=(0, 10))
        
        # Camera button
        camera_available = is_camera_available()
        self.camera_btn = ctk.CTkButton(
            source_section,
            text="📹 Usar Câmera" if camera_available else "📹 Câmera Indisponível",
            height=45,
            font=ctk.CTkFont(size=14),
            fg_color=("#00d9ff", "#00a8cc") if camera_available else ("gray60", "gray50"),
            hover_color=("#00b8d4", "#008ba3") if camera_available else ("gray60", "gray50"),
            corner_radius=10,
            command=self.toggle_camera,
            state="normal" if camera_available else "disabled"
        )
        self.camera_btn.pack(fill="x", pady=(0, 10))
        
        # Upload button
        self.upload_btn = ctk.CTkButton(
            source_section,
            text="📁 Upload de Imagem",
            height=45,
            font=ctk.CTkFont(size=14),
            fg_color=("#7b2cbf", "#6320a0"),
            hover_color=("#6320a0", "#4a1878"),
            corner_radius=10,
            command=self.upload_image
        )
        self.upload_btn.pack(fill="x")
        
        # Capture button (for camera mode)
        self.capture_btn = ctk.CTkButton(
            source_section,
            text="📸 Capturar Foto",
            height=45,
            font=ctk.CTkFont(size=14),
            fg_color=("#ff6b6b", "#ee5a5a"),
            hover_color=("#ee5a5a", "#dd4949"),
            corner_radius=10,
            command=self.capture_photo
        )
        # Hidden initially
        
        # Drag and drop hint
        drop_hint = ctk.CTkLabel(
            source_section,
            text="💡 Dica: Arraste e solte uma imagem na área de preview",
            font=ctk.CTkFont(size=11),
            text_color=("gray50", "gray60")
        )
        drop_hint.pack(pady=(15, 0))
        
        # Status message
        self.status_frame = ctk.CTkFrame(controls_frame, fg_color="transparent")
        self.status_frame.pack(fill="x", padx=25, pady=15)
        
        self.status_label = ctk.CTkLabel(
            self.status_frame,
            text="",
            font=ctk.CTkFont(size=13),
            text_color=("gray40", "gray60")
        )
        self.status_label.pack()
        
        # Register button
        self.register_btn = ctk.CTkButton(
            controls_frame,
            text="✓ REGISTRAR ROSTO",
            height=55,
            font=ctk.CTkFont(size=16, weight="bold"),
            fg_color=("#00ff88", "#00cc6a"),
            hover_color=("#00cc6a", "#00aa55"),
            text_color=("black", "black"),
            corner_radius=12,
            command=self.register_face
        )
        self.register_btn.pack(fill="x", padx=25, pady=(10, 25))
    
    def _setup_drop_bindings(self):
        """Setup drag and drop bindings for the image frame."""
        # Bind to both the image frame and its children
        for widget in [self.image_frame, self.image_label, self.preview_frame]:
            widget.bind("<Button-1>", self._on_drop_click)
            
        # Try to setup native DnD with tkinterdnd2
        try:
            # Check if tkinterdnd2 is available
            self.image_frame.drop_target_register('DND_Files')
            self.image_frame.dnd_bind('<<Drop>>', self._on_file_drop)
            self.image_frame.dnd_bind('<<DragEnter>>', self._on_drag_enter)
            self.image_frame.dnd_bind('<<DragLeave>>', self._on_drag_leave)
        except:
            # Fallback - just use click to upload
            pass
    
    def _on_drop_click(self, event):
        """Handle click on drop zone - opens file dialog."""
        if not self.is_camera_active:
            self.upload_image()
    
    def _on_drag_enter(self, event):
        """Handle drag enter event."""
        self.is_drag_over = True
        self.image_frame.configure(border_color=("#00d9ff", "#00d9ff"))
        self.image_label.configure(text="📥\n\nSolte a imagem aqui!")
    
    def _on_drag_leave(self, event):
        """Handle drag leave event."""
        self.is_drag_over = False
        self.image_frame.configure(border_color=("gray75", "#2d2d4a"))
        if self.current_frame is None:
            self.image_label.configure(
                text="📷\n\n🖱️ Clique em 'Upload' ou\n\n📂 Arraste uma imagem aqui"
            )
    
    def _on_file_drop(self, event):
        """Handle file drop event."""
        self.is_drag_over = False
        self.image_frame.configure(border_color=("gray75", "#2d2d4a"))
        
        # Parse dropped files
        files = event.data
        if files.startswith('{'):
            files = files[1:-1]
        
        # Handle file:// prefix
        if files.startswith('file://'):
            import urllib.parse
            files = urllib.parse.unquote(files[7:])
        
        # Load image
        if os.path.exists(files) and is_image_file(files):
            self.load_image_from_path(files)
    
    def load_image_from_path(self, file_path: str):
        """Load an image from file path."""
        if self.is_camera_active:
            self.stop_camera()
        
        frame = cv2.imread(file_path)
        if frame is not None:
            self.current_frame = frame
            
            # Detect faces
            faces = self.controller.face_engine.detect_faces(frame)
            self.face_detected = len(faces) > 0
            
            # Draw face boxes
            display_frame = frame.copy()
            for face_loc in faces:
                display_frame = CameraManager.draw_face_box(
                    display_frame, face_loc,
                    color=(0, 255, 255),
                    thickness=2
                )
            
            self.update_image_display(display_frame)
            
            if self.face_detected:
                self.face_indicator.configure(
                    text="✓ Rosto detectado",
                    text_color=("#00ff88", "#00ff88")
                )
                self.status_label.configure(text="✓ Imagem carregada!")
            else:
                self.face_indicator.configure(
                    text="⚠ Nenhum rosto detectado",
                    text_color=("#ff6b6b", "#ff6b6b")
                )
                self.status_label.configure(
                    text="⚠ Nenhum rosto encontrado na imagem",
                    text_color=("#ff6b6b", "#ff6b6b")
                )
        else:
            self.status_label.configure(
                text="❌ Erro ao carregar imagem",
                text_color=("#ff4757", "#ff6b81")
            )
    
    def toggle_camera(self):
        """Toggle camera on/off."""
        if self.is_camera_active:
            self.stop_camera()
        else:
            self.start_camera()
    
    def start_camera(self):
        """Start camera capture."""
        if self.camera.start(callback=self.on_camera_frame):
            self.is_camera_active = True
            self.camera_btn.configure(text="⏹ Parar Câmera", fg_color=("#ff6b6b", "#ee5a5a"))
            self.capture_btn.pack(fill="x", pady=(10, 0))
            self.status_label.configure(text="Câmera ativa - posicione o rosto")
        else:
            self.status_label.configure(
                text="❌ Erro ao iniciar câmera",
                text_color=("#ff4757", "#ff6b81")
            )
    
    def stop_camera(self):
        """Stop camera capture."""
        self.camera.stop()
        self.is_camera_active = False
        self.camera_btn.configure(text="📹 Usar Câmera", fg_color=("#00d9ff", "#00a8cc"))
        self.capture_btn.pack_forget()
        self.status_label.configure(text="")
    
    def on_camera_frame(self, frame: np.ndarray):
        """Called on each camera frame."""
        if not self.is_camera_active:
            return
        
        # Detect faces
        faces = self.controller.face_engine.detect_faces(frame)
        self.face_detected = len(faces) > 0
        
        # Draw face boxes
        display_frame = frame.copy()
        for face_loc in faces:
            display_frame = CameraManager.draw_face_box(
                display_frame, face_loc,
                color=(0, 255, 255),  # Cyan
                thickness=2
            )
        
        self.current_frame = frame.copy()
        
        # Update display
        self.update_image_display(display_frame)
        
        # Update face indicator
        if self.face_detected:
            self.face_indicator.configure(
                text="✓ Rosto detectado",
                text_color=("#00ff88", "#00ff88")
            )
        else:
            self.face_indicator.configure(
                text="⚠ Nenhum rosto detectado",
                text_color=("#ff6b6b", "#ff6b6b")
            )
    
    def capture_photo(self):
        """Capture photo from camera."""
        if self.current_frame is not None:
            self.stop_camera()
            self.update_image_display(self.current_frame)
            self.status_label.configure(text="✓ Foto capturada!")
    
    def upload_image(self):
        """Upload image from file."""
        if self.is_camera_active:
            self.stop_camera()
        
        file_path = filedialog.askopenfilename(
            title="Selecionar Imagem",
            filetypes=[
                ("Imagens", "*.jpg *.jpeg *.png *.bmp *.gif *.webp"),
                ("Todos os arquivos", "*.*")
            ]
        )
        
        if file_path:
            self.load_image_from_path(file_path)
    
    def update_image_display(self, frame: np.ndarray):
        """Update the image display with a frame."""
        try:
            # Resize frame to fit display
            frame = CameraManager.resize_frame(frame, 380, 320)
            
            # Convert to PhotoImage
            pil_image = CameraManager.frame_to_pil(frame)
            photo = ctk.CTkImage(light_image=pil_image, dark_image=pil_image,
                                 size=(pil_image.width, pil_image.height))
            
            self.image_label.configure(image=photo, text="")
            self.image_label.image = photo
        except Exception as e:
            print(f"Display update error: {e}")
    
    def register_face(self):
        """Register the current face."""
        name = self.name_entry.get().strip()
        
        if not name:
            self.status_label.configure(
                text="❌ Digite um nome",
                text_color=("#ff4757", "#ff6b81")
            )
            return
        
        if self.current_frame is None:
            self.status_label.configure(
                text="❌ Selecione uma imagem primeiro",
                text_color=("#ff4757", "#ff6b81")
            )
            return
        
        if not self.face_detected:
            self.status_label.configure(
                text="❌ Nenhum rosto detectado na imagem",
                text_color=("#ff4757", "#ff6b81")
            )
            return
        
        # Show loading
        self.status_label.configure(
            text="⏳ Registrando...",
            text_color=("gray40", "gray60")
        )
        self.register_btn.configure(state="disabled")
        self.update()
        
        # Register in thread
        def do_register():
            success, message = self.controller.face_engine.register_face(
                name, self.current_frame
            )
            
            # Update UI in main thread
            self.after(0, lambda: self.on_register_complete(success, message))
        
        thread = threading.Thread(target=do_register, daemon=True)
        thread.start()
    
    def on_register_complete(self, success: bool, message: str):
        """Handle registration completion."""
        self.register_btn.configure(state="normal")
        
        if success:
            self.status_label.configure(
                text=f"✓ {message}",
                text_color=("#00ff88", "#00ff88")
            )
            # Clear form after delay
            self.after(1500, self.clear_form)
        else:
            self.status_label.configure(
                text=f"❌ {message}",
                text_color=("#ff4757", "#ff6b81")
            )
    
    def clear_form(self):
        """Clear the registration form."""
        self.name_entry.delete(0, "end")
        self.current_frame = None
        self.face_detected = False
        self.image_label.configure(
            image=None,
            text="📷\n\n🖱️ Clique em 'Upload' ou\n\n📂 Arraste uma imagem aqui"
        )
        self.face_indicator.configure(text="")
        self.status_label.configure(text="")
    
    def go_back(self):
        """Go back to home screen."""
        self.stop_camera()
        self.controller.show_screen("home")
    
    def on_show(self):
        """Called when screen is shown."""
        self.clear_form()
    
    def on_hide(self):
        """Called when screen is hidden."""
        self.stop_camera()
