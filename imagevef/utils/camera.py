"""
Camera utilities for the Face ID application
"""
import cv2
import threading
import numpy as np
from typing import Optional, Callable, Tuple
from PIL import Image


class CameraManager:
    """Manages webcam capture and processing."""
    
    def __init__(self, camera_index: int = 0):
        self.camera_index = camera_index
        self.capture: Optional[cv2.VideoCapture] = None
        self.is_running = False
        self.thread: Optional[threading.Thread] = None
        self.current_frame: Optional[np.ndarray] = None
        self.frame_callback: Optional[Callable] = None
        self.lock = threading.Lock()
    
    def start(self, callback: Optional[Callable] = None) -> bool:
        """
        Start camera capture.
        Callback receives (frame, face_locations) on each frame.
        Returns True if camera started successfully.
        """
        if self.is_running:
            return True
        
        self.capture = cv2.VideoCapture(self.camera_index)
        
        if not self.capture.isOpened():
            return False
        
        # Set camera properties
        self.capture.set(cv2.CAP_PROP_FRAME_WIDTH, 640)
        self.capture.set(cv2.CAP_PROP_FRAME_HEIGHT, 480)
        self.capture.set(cv2.CAP_PROP_FPS, 30)
        
        self.frame_callback = callback
        self.is_running = True
        
        self.thread = threading.Thread(target=self._capture_loop, daemon=True)
        self.thread.start()
        
        return True
    
    def stop(self):
        """Stop camera capture."""
        self.is_running = False
        
        if self.thread:
            self.thread.join(timeout=1.0)
            self.thread = None
        
        if self.capture:
            self.capture.release()
            self.capture = None
        
        self.current_frame = None
    
    def _capture_loop(self):
        """Main capture loop running in separate thread."""
        while self.is_running and self.capture:
            ret, frame = self.capture.read()
            
            if ret:
                # Flip horizontally for mirror effect
                frame = cv2.flip(frame, 1)
                
                with self.lock:
                    self.current_frame = frame.copy()
                
                if self.frame_callback:
                    try:
                        self.frame_callback(frame)
                    except Exception as e:
                        print(f"Frame callback error: {e}")
    
    def get_frame(self) -> Optional[np.ndarray]:
        """Get the current frame."""
        with self.lock:
            if self.current_frame is not None:
                return self.current_frame.copy()
        return None
    
    def capture_photo(self) -> Optional[np.ndarray]:
        """Capture a single photo."""
        return self.get_frame()
    
    @staticmethod
    def frame_to_pil(frame: np.ndarray) -> Image.Image:
        """Convert OpenCV frame to PIL Image."""
        # Convert BGR to RGB
        rgb_frame = cv2.cvtColor(frame, cv2.COLOR_BGR2RGB)
        return Image.fromarray(rgb_frame)
    
    @staticmethod
    def pil_to_frame(image: Image.Image) -> np.ndarray:
        """Convert PIL Image to OpenCV frame."""
        # Convert to numpy array and RGB to BGR
        frame = np.array(image)
        if len(frame.shape) == 3 and frame.shape[2] == 3:
            frame = cv2.cvtColor(frame, cv2.COLOR_RGB2BGR)
        return frame
    
    @staticmethod
    def load_image(path: str) -> Optional[np.ndarray]:
        """Load an image from file."""
        frame = cv2.imread(path)
        return frame
    
    @staticmethod
    def draw_face_box(frame: np.ndarray, face_location: Tuple[int, int, int, int],
                      color: Tuple[int, int, int] = (0, 255, 255),
                      thickness: int = 2, 
                      label: str = "") -> np.ndarray:
        """
        Draw a bounding box around a face.
        face_location is (top, right, bottom, left).
        """
        top, right, bottom, left = face_location
        
        # Draw rectangle
        cv2.rectangle(frame, (left, top), (right, bottom), color, thickness)
        
        # Draw corner accents
        corner_length = 15
        # Top-left
        cv2.line(frame, (left, top), (left + corner_length, top), color, thickness + 1)
        cv2.line(frame, (left, top), (left, top + corner_length), color, thickness + 1)
        # Top-right
        cv2.line(frame, (right, top), (right - corner_length, top), color, thickness + 1)
        cv2.line(frame, (right, top), (right, top + corner_length), color, thickness + 1)
        # Bottom-left
        cv2.line(frame, (left, bottom), (left + corner_length, bottom), color, thickness + 1)
        cv2.line(frame, (left, bottom), (left, bottom - corner_length), color, thickness + 1)
        # Bottom-right
        cv2.line(frame, (right, bottom), (right - corner_length, bottom), color, thickness + 1)
        cv2.line(frame, (right, bottom), (right, bottom - corner_length), color, thickness + 1)
        
        # Draw label if provided
        if label:
            label_size = cv2.getTextSize(label, cv2.FONT_HERSHEY_SIMPLEX, 0.6, 2)[0]
            # Background for label
            cv2.rectangle(
                frame,
                (left, bottom),
                (left + label_size[0] + 10, bottom + label_size[1] + 10),
                color,
                -1
            )
            # Text
            cv2.putText(
                frame, label,
                (left + 5, bottom + label_size[1] + 3),
                cv2.FONT_HERSHEY_SIMPLEX, 0.6,
                (0, 0, 0), 2
            )
        
        return frame
    
    @staticmethod
    def resize_frame(frame: np.ndarray, max_width: int = 640, 
                     max_height: int = 480) -> np.ndarray:
        """Resize frame to fit within max dimensions while maintaining aspect ratio."""
        h, w = frame.shape[:2]
        
        # Calculate scaling factor
        scale = min(max_width / w, max_height / h)
        
        if scale < 1:
            new_w = int(w * scale)
            new_h = int(h * scale)
            frame = cv2.resize(frame, (new_w, new_h), interpolation=cv2.INTER_AREA)
        
        return frame


def is_camera_available(camera_index: int = 0) -> bool:
    """Check if a camera is available."""
    cap = cv2.VideoCapture(camera_index)
    available = cap.isOpened()
    cap.release()
    return available
