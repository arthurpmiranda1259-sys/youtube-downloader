"""
Verify Screen - Face verification with camera/image support and drag-drop
"""
import customtkinter as ctk
from tkinter import filedialog
from PIL import Image
import cv2
import threading
import numpy as np
from typing import Optional
import os

import sys
sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from utils.animations import LoadingSpinner, SuccessAnimation, ErrorAnimation, ProgressRing
from utils.camera import CameraManager, is_camera_available
from utils.drop_zone import is_image_file


class VerifyScreen(ctk.CTkFrame):
    """Screen for verifying faces with drag-drop support."""
    
    def __init__(self, parent, controller):
        super().__init__(parent, fg_color="transparent")
        self.controller = controller
        self.camera = CameraManager()
        self.current_frame: Optional[np.ndarray] = None
        self.is_camera_active = False
        self.is_verifying = False
        self.is_drag_over = False
        
        self.setup_ui()
        self._setup_drop_bindings()
    
    def setup_ui(self):
        """Setup the verify screen UI."""
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
            text="🔍 Verificar Rosto",
            font=ctk.CTkFont(family="Segoe UI", size=28, weight="bold"),
            text_color=("#1a1a2e", "#00d9ff")
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
        
        # Image display area
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
            text="📷\n\n🖱️ Clique aqui ou\n\n📂 Arraste uma imagem",
            font=ctk.CTkFont(size=16),
            text_color=("gray50", "gray60")
        )
        self.image_label.pack(expand=True)
        
        # Source buttons below preview
        source_btns = ctk.CTkFrame(self.preview_frame, fg_color="transparent")
        source_btns.pack(fill="x", padx=20, pady=(5, 20))
        source_btns.grid_columnconfigure((0, 1), weight=1)
        
        camera_available = is_camera_available()
        self.camera_btn = ctk.CTkButton(
            source_btns,
            text="📹 Câmera" if camera_available else "📹 Sem Câmera",
            height=40,
            font=ctk.CTkFont(size=13),
            fg_color=("#00d9ff", "#00a8cc") if camera_available else ("gray60", "gray50"),
            hover_color=("#00b8d4", "#008ba3") if camera_available else ("gray60", "gray50"),
            corner_radius=10,
            command=self.toggle_camera,
            state="normal" if camera_available else "disabled"
        )
        self.camera_btn.grid(row=0, column=0, padx=(0, 5), sticky="ew")
        
        self.upload_btn = ctk.CTkButton(
            source_btns,
            text="📁 Imagem",
            height=40,
            font=ctk.CTkFont(size=13),
            fg_color=("#7b2cbf", "#6320a0"),
            hover_color=("#6320a0", "#4a1878"),
            corner_radius=10,
            command=self.upload_image
        )
        self.upload_btn.grid(row=0, column=1, padx=(5, 0), sticky="ew")
        
        # Right side - Results
        results_frame = ctk.CTkFrame(
            content,
            fg_color=("gray90", "#252542"),
            corner_radius=20
        )
        results_frame.grid(row=0, column=1, sticky="nsew", padx=(15, 0))
        
        results_title = ctk.CTkLabel(
            results_frame,
            text="Resultado",
            font=ctk.CTkFont(size=14, weight="bold"),
            text_color=("gray40", "gray60")
        )
        results_title.pack(pady=(20, 15))
        
        # Result display area
        self.result_container = ctk.CTkFrame(
            results_frame,
            fg_color="transparent"
        )
        self.result_container.pack(fill="both", expand=True, padx=25, pady=10)
        
        # Initial state - waiting for image
        self.result_placeholder = ctk.CTkLabel(
            self.result_container,
            text="🔍\n\nSelecione uma imagem\npara verificar",
            font=ctk.CTkFont(size=16),
            text_color=("gray50", "gray60")
        )
        self.result_placeholder.pack(expand=True)
        
        # Hidden result widgets (shown after verification)
        self.result_icon_label = ctk.CTkLabel(
            self.result_container,
            text="",
            font=ctk.CTkFont(size=64)
        )
        
        self.result_name_label = ctk.CTkLabel(
            self.result_container,
            text="",
            font=ctk.CTkFont(size=24, weight="bold")
        )
        
        self.result_status_label = ctk.CTkLabel(
            self.result_container,
            text="",
            font=ctk.CTkFont(size=14)
        )
        
        # Confidence ring
        self.confidence_ring = ProgressRing(
            self.result_container,
            size=120,
            thickness=10,
            bg_color="#2d2d4a"
        )
        
        # Verify button
        self.verify_btn = ctk.CTkButton(
            results_frame,
            text="🔍 VERIFICAR",
            height=55,
            font=ctk.CTkFont(size=16, weight="bold"),
            fg_color=("#00d9ff", "#00a8cc"),
            hover_color=("#00b8d4", "#008ba3"),
            corner_radius=12,
            command=self.verify_face
        )
        self.verify_btn.pack(fill="x", padx=25, pady=(10, 25))
        
        # Status label
        self.status_label = ctk.CTkLabel(
            results_frame,
            text="",
            font=ctk.CTkFont(size=12),
            text_color=("gray50", "gray60")
        )
        self.status_label.pack(pady=(0, 15))
    
    def _setup_drop_bindings(self):
        """Setup drag and drop bindings."""
        for widget in [self.image_frame, self.image_label]:
            widget.bind("<Button-1>", self._on_drop_click)
        
        try:
            self.image_frame.drop_target_register('DND_Files')
            self.image_frame.dnd_bind('<<Drop>>', self._on_file_drop)
            self.image_frame.dnd_bind('<<DragEnter>>', self._on_drag_enter)
            self.image_frame.dnd_bind('<<DragLeave>>', self._on_drag_leave)
        except:
            pass
    
    def _on_drop_click(self, event):
        """Handle click on drop zone."""
        if not self.is_camera_active:
            self.upload_image()
    
    def _on_drag_enter(self, event):
        """Handle drag enter."""
        self.is_drag_over = True
        self.image_frame.configure(border_color=("#00d9ff", "#00d9ff"))
        self.image_label.configure(text="📥\n\nSolte a imagem aqui!")
    
    def _on_drag_leave(self, event):
        """Handle drag leave."""
        self.is_drag_over = False
        self.image_frame.configure(border_color=("gray75", "#2d2d4a"))
        if self.current_frame is None:
            self.image_label.configure(
                text="📷\n\n🖱️ Clique aqui ou\n\n📂 Arraste uma imagem"
            )
    
    def _on_file_drop(self, event):
        """Handle file drop."""
        self.is_drag_over = False
        self.image_frame.configure(border_color=("gray75", "#2d2d4a"))
        
        files = event.data
        if files.startswith('{'):
            files = files[1:-1]
        
        if files.startswith('file://'):
            import urllib.parse
            files = urllib.parse.unquote(files[7:])
        
        if os.path.exists(files) and is_image_file(files):
            self.load_image_from_path(files)
    
    def load_image_from_path(self, file_path: str):
        """Load image from path."""
        if self.is_camera_active:
            self.stop_camera()
        
        frame = cv2.imread(file_path)
        if frame is not None:
            self.current_frame = frame
            
            faces = self.controller.face_engine.detect_faces(frame)
            
            display_frame = frame.copy()
            for face_loc in faces:
                display_frame = CameraManager.draw_face_box(
                    display_frame, face_loc,
                    color=(0, 255, 255),
                    thickness=2
                )
            
            self.update_image_display(display_frame)
            self.reset_results()
            
            if faces:
                self.status_label.configure(text="✓ Rosto detectado - clique em Verificar")
            else:
                self.status_label.configure(text="⚠ Nenhum rosto detectado")
    
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
            self.camera_btn.configure(text="⏹ Parar", fg_color=("#ff6b6b", "#ee5a5a"))
            self.reset_results()
        else:
            self.status_label.configure(text="❌ Erro ao iniciar câmera")
    
    def stop_camera(self):
        """Stop camera capture."""
        self.camera.stop()
        self.is_camera_active = False
        self.camera_btn.configure(text="📹 Câmera", fg_color=("#00d9ff", "#00a8cc"))
    
    def on_camera_frame(self, frame: np.ndarray):
        """Called on each camera frame."""
        if not self.is_camera_active or self.is_verifying:
            return
        
        # Detect faces and draw boxes
        faces = self.controller.face_engine.detect_faces(frame)
        
        display_frame = frame.copy()
        for face_loc in faces:
            display_frame = CameraManager.draw_face_box(
                display_frame, face_loc,
                color=(0, 255, 255),
                thickness=2
            )
        
        self.current_frame = frame.copy()
        self.update_image_display(display_frame)
    
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
        """Update the image display."""
        try:
            frame = CameraManager.resize_frame(frame, 380, 320)
            pil_image = CameraManager.frame_to_pil(frame)
            photo = ctk.CTkImage(light_image=pil_image, dark_image=pil_image,
                                 size=(pil_image.width, pil_image.height))
            
            self.image_label.configure(image=photo, text="")
            self.image_label.image = photo
        except Exception as e:
            print(f"Display update error: {e}")
    
    def reset_results(self):
        """Reset the results display."""
        # Hide result widgets
        self.result_icon_label.pack_forget()
        self.result_name_label.pack_forget()
        self.result_status_label.pack_forget()
        self.confidence_ring.pack_forget()
        
        # Show placeholder
        self.result_placeholder.pack(expand=True)
        self.status_label.configure(text="")
    
    def verify_face(self):
        """Verify the current face."""
        if self.current_frame is None:
            self.status_label.configure(text="❌ Selecione uma imagem primeiro")
            return
        
        if self.is_verifying:
            return
        
        self.is_verifying = True
        self.verify_btn.configure(state="disabled", text="⏳ Verificando...")
        self.status_label.configure(text="")
        
        # Stop camera if active
        if self.is_camera_active:
            self.stop_camera()
        
        # Verify in thread
        def do_verify():
            name, confidence = self.controller.face_engine.verify_face(self.current_frame)
            self.after(0, lambda: self.on_verify_complete(name, confidence))
        
        thread = threading.Thread(target=do_verify, daemon=True)
        thread.start()
    
    def on_verify_complete(self, name: Optional[str], confidence: float):
        """Handle verification completion."""
        self.is_verifying = False
        self.verify_btn.configure(state="normal", text="🔍 VERIFICAR")
        
        # Hide placeholder
        self.result_placeholder.pack_forget()
        
        if name:
            # Match found!
            self.result_icon_label.configure(text="✅")
            self.result_icon_label.pack(pady=(20, 10))
            
            self.result_name_label.configure(
                text=name,
                text_color=("#00ff88", "#00ff88")
            )
            self.result_name_label.pack(pady=5)
            
            self.result_status_label.configure(
                text="Identidade Confirmada",
                text_color=("#00cc6a", "#00ff88")
            )
            self.result_status_label.pack(pady=(0, 20))
            
            # Update confidence ring
            self.confidence_ring.fg_ring_color = "#00ff88"
            self.confidence_ring.pack(pady=10)
            self.confidence_ring.set_progress(confidence, animate=True)
            
            self.status_label.configure(
                text=f"Match com {confidence:.1f}% de confiança",
                text_color=("#00cc6a", "#00ff88")
            )
        else:
            # No match
            self.result_icon_label.configure(text="❌")
            self.result_icon_label.pack(pady=(20, 10))
            
            self.result_name_label.configure(
                text="Desconhecido",
                text_color=("#ff4757", "#ff6b81")
            )
            self.result_name_label.pack(pady=5)
            
            self.result_status_label.configure(
                text="Não registrado no sistema",
                text_color=("#ff4757", "#ff6b81")
            )
            self.result_status_label.pack(pady=(0, 20))
            
            self.status_label.configure(
                text="Rosto não encontrado na base de dados",
                text_color=("#ff4757", "#ff6b81")
            )
    
    def go_back(self):
        """Go back to home screen."""
        self.stop_camera()
        self.controller.show_screen("home")
    
    def on_show(self):
        """Called when screen is shown."""
        self.current_frame = None
        self.reset_results()
        self.image_label.configure(
            image=None,
            text="📷\n\n🖱️ Clique aqui ou\n\n📂 Arraste uma imagem"
        )
    
    def on_hide(self):
        """Called when screen is hidden."""
        self.stop_camera()
