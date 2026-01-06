"""
Face Engine - Core module for face recognition using OpenCV DNN
Uses OpenCV's DNN face detector for better accuracy and LBPH recognizer
"""
import os
import json
import cv2
import numpy as np
from pathlib import Path
from typing import Optional, Tuple, List, Dict, Any
import urllib.request


class FaceEngine:
    """Engine for face detection, registration and verification using OpenCV DNN."""
    
    # URLs for model files
    PROTOTXT_URL = "https://raw.githubusercontent.com/opencv/opencv/master/samples/dnn/face_detector/deploy.prototxt"
    CAFFEMODEL_URL = "https://raw.githubusercontent.com/opencv/opencv_3rdparty/dnn_samples_face_detector_20170830/res10_300x300_ssd_iter_140000.caffemodel"
    
    def __init__(self, data_dir: str = "data"):
        self.data_dir = Path(data_dir)
        self.faces_db_path = self.data_dir / "faces.json"
        self.images_dir = self.data_dir / "images"
        self.models_dir = self.data_dir / "models"
        self.tolerance = 80  # LBPH threshold (lower = more strict)
        self.confidence_threshold = 0.7  # DNN detection confidence
        self.database: Dict[str, Any] = {}
        
        # Create directories
        self.data_dir.mkdir(parents=True, exist_ok=True)
        self.images_dir.mkdir(parents=True, exist_ok=True)
        self.models_dir.mkdir(parents=True, exist_ok=True)
        
        # Initialize face detector
        self._init_face_detector()
        
        # Initialize LBPH face recognizer
        self.recognizer = cv2.face.LBPHFaceRecognizer_create(
            radius=2,
            neighbors=16,
            grid_x=8,
            grid_y=8
        )
        self.label_to_name: Dict[int, str] = {}
        self.name_to_label: Dict[str, int] = {}
        
        # Load existing database
        self.load_database()
    
    def _init_face_detector(self):
        """Initialize the DNN face detector, fallback to Haar Cascade."""
        prototxt_path = self.models_dir / "deploy.prototxt"
        caffemodel_path = self.models_dir / "res10_300x300_ssd_iter_140000.caffemodel"
        
        # Try to download models if not exist
        self.use_dnn = False
        
        try:
            if not prototxt_path.exists():
                print("📥 Baixando modelo de detecção facial (prototxt)...")
                urllib.request.urlretrieve(self.PROTOTXT_URL, prototxt_path)
            
            if not caffemodel_path.exists():
                print("📥 Baixando modelo de detecção facial (caffemodel)...")
                urllib.request.urlretrieve(self.CAFFEMODEL_URL, caffemodel_path)
            
            # Load DNN model
            self.face_net = cv2.dnn.readNetFromCaffe(
                str(prototxt_path), 
                str(caffemodel_path)
            )
            self.use_dnn = True
            print("✅ DNN Face Detector carregado com sucesso!")
        except Exception as e:
            print(f"⚠️ Não foi possível carregar DNN: {e}")
            print("📌 Usando Haar Cascade como fallback...")
        
        # Always have Haar Cascade as fallback
        cascade_path = cv2.data.haarcascades + 'haarcascade_frontalface_default.xml'
        self.face_cascade = cv2.CascadeClassifier(cascade_path)
    
    def load_database(self) -> None:
        """Load face data from JSON file and train recognizer if data exists."""
        if self.faces_db_path.exists():
            try:
                with open(self.faces_db_path, 'r') as f:
                    data = json.load(f)
                    self.database = data.get("faces", {})
                    self.label_to_name = {int(k): v for k, v in data.get("label_to_name", {}).items()}
                    self.name_to_label = data.get("name_to_label", {})
            except (json.JSONDecodeError, KeyError):
                self.database = {}
                self.label_to_name = {}
                self.name_to_label = {}
        
        # Train recognizer with existing data
        self._train_recognizer()
    
    def _train_recognizer(self) -> None:
        """Train the LBPH recognizer with all registered faces."""
        if not self.database:
            return
        
        faces = []
        labels = []
        
        for name, info in self.database.items():
            image_path = info.get("image_path", "")
            if image_path and os.path.exists(image_path):
                img = cv2.imread(image_path)
                if img is not None:
                    # Detect face and get face region
                    face_locations = self.detect_faces(img)
                    if len(face_locations) > 0:
                        top, right, bottom, left = face_locations[0]
                        
                        # Convert to grayscale
                        gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
                        face_roi = gray[top:bottom, left:right]
                        
                        # Preprocess face
                        face_roi = self._preprocess_face(face_roi)
                        
                        label = self.name_to_label.get(name, len(self.name_to_label))
                        self.name_to_label[name] = label
                        self.label_to_name[label] = name
                        
                        faces.append(face_roi)
                        labels.append(label)
        
        if faces:
            self.recognizer.train(faces, np.array(labels))
    
    def _preprocess_face(self, face: np.ndarray, size: int = 200) -> np.ndarray:
        """Preprocess face for better recognition."""
        # Resize to standard size
        face = cv2.resize(face, (size, size))
        
        # Apply histogram equalization for better contrast
        face = cv2.equalizeHist(face)
        
        # Apply slight Gaussian blur to reduce noise
        face = cv2.GaussianBlur(face, (3, 3), 0)
        
        return face
    
    def save_database(self) -> None:
        """Save face data to JSON file."""
        data = {
            "faces": self.database,
            "label_to_name": {str(k): v for k, v in self.label_to_name.items()},
            "name_to_label": self.name_to_label
        }
        with open(self.faces_db_path, 'w') as f:
            json.dump(data, f, indent=2)
    
    def detect_faces(self, image: np.ndarray) -> List[Tuple[int, int, int, int]]:
        """
        Detect faces in an image using DNN or Haar Cascade.
        Returns list of face locations as (top, right, bottom, left) tuples.
        """
        h, w = image.shape[:2]
        
        if self.use_dnn:
            return self._detect_faces_dnn(image, w, h)
        else:
            return self._detect_faces_cascade(image)
    
    def _detect_faces_dnn(self, image: np.ndarray, w: int, h: int) -> List[Tuple[int, int, int, int]]:
        """Detect faces using DNN."""
        # Prepare blob
        blob = cv2.dnn.blobFromImage(
            cv2.resize(image, (300, 300)), 
            1.0, 
            (300, 300), 
            (104.0, 177.0, 123.0)
        )
        
        self.face_net.setInput(blob)
        detections = self.face_net.forward()
        
        face_locations = []
        for i in range(detections.shape[2]):
            confidence = detections[0, 0, i, 2]
            
            if confidence > self.confidence_threshold:
                box = detections[0, 0, i, 3:7] * np.array([w, h, w, h])
                (x1, y1, x2, y2) = box.astype("int")
                
                # Ensure coordinates are within image bounds
                x1 = max(0, x1)
                y1 = max(0, y1)
                x2 = min(w, x2)
                y2 = min(h, y2)
                
                # Convert to (top, right, bottom, left) format
                face_locations.append((y1, x2, y2, x1))
        
        return face_locations
    
    def _detect_faces_cascade(self, image: np.ndarray) -> List[Tuple[int, int, int, int]]:
        """Detect faces using Haar Cascade (fallback)."""
        if len(image.shape) == 3:
            gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
        else:
            gray = image
        
        faces = self.face_cascade.detectMultiScale(
            gray, 
            scaleFactor=1.1, 
            minNeighbors=5, 
            minSize=(50, 50)
        )
        
        face_locations = []
        for (x, y, w, h) in faces:
            face_locations.append((y, x + w, y + h, x))
        
        return face_locations
    
    def register_face(self, name: str, image: np.ndarray, save_image: bool = True) -> Tuple[bool, str]:
        """
        Register a new face with the given name.
        Returns (success, message).
        """
        if not name or not name.strip():
            return False, "Nome não pode estar vazio"
        
        name = name.strip()
        
        # Check if name already exists
        if name in self.database:
            return False, f"'{name}' já está registrado"
        
        # Detect face
        face_locations = self.detect_faces(image)
        if not face_locations:
            return False, "Nenhum rosto detectado na imagem"
        
        # Save image
        image_path = ""
        if save_image:
            image_filename = f"{name.replace(' ', '_').lower()}.jpg"
            image_path = str(self.images_dir / image_filename)
            cv2.imwrite(image_path, image)
        
        # Assign label
        label = len(self.name_to_label)
        self.name_to_label[name] = label
        self.label_to_name[label] = name
        
        # Store in database
        self.database[name] = {
            "label": label,
            "image_path": image_path
        }
        self.save_database()
        
        # Retrain recognizer
        self._train_recognizer()
        
        return True, f"'{name}' registrado com sucesso!"
    
    def verify_face(self, image: np.ndarray) -> Tuple[Optional[str], float]:
        """
        Verify a face against registered faces.
        Returns (name, confidence) where name is None if no match.
        Confidence is 0-100% (higher = better match).
        """
        if not self.database:
            return None, 0.0
        
        # Detect face
        face_locations = self.detect_faces(image)
        if not face_locations:
            return None, 0.0
        
        # Convert to grayscale
        if len(image.shape) == 3:
            gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
        else:
            gray = image
        
        # Get first face
        top, right, bottom, left = face_locations[0]
        face_roi = gray[top:bottom, left:right]
        
        # Preprocess face
        face_roi = self._preprocess_face(face_roi)
        
        try:
            # Predict
            label, confidence = self.recognizer.predict(face_roi)
            
            # LBPH confidence: lower is better, 0 is perfect match
            # Convert to percentage (0-100)
            if confidence < self.tolerance:
                percentage = max(0, min(100, 100 - confidence))
                name = self.label_to_name.get(label)
                return name, percentage
        except cv2.error:
            pass
        
        return None, 0.0
    
    def delete_face(self, name: str) -> Tuple[bool, str]:
        """Delete a registered face."""
        if name not in self.database:
            return False, f"'{name}' não encontrado"
        
        # Delete image file if exists
        image_path = self.database[name].get("image_path", "")
        if image_path and os.path.exists(image_path):
            os.remove(image_path)
        
        # Remove from database
        del self.database[name]
        
        # Remove from label mappings
        if name in self.name_to_label:
            label = self.name_to_label[name]
            del self.name_to_label[name]
            if label in self.label_to_name:
                del self.label_to_name[label]
        
        self.save_database()
        
        # Retrain recognizer
        self._train_recognizer()
        
        return True, f"'{name}' removido com sucesso!"
    
    def get_registered_faces(self) -> List[str]:
        """Get list of registered face names."""
        return list(self.database.keys())
    
    def set_tolerance(self, tolerance: float) -> None:
        """Set face matching tolerance (0-1, higher = more tolerant)."""
        # Convert from 0-1 scale to LBPH scale (30-130)
        self.tolerance = 30 + (tolerance * 100)
    
    def get_face_image_path(self, name: str) -> Optional[str]:
        """Get the saved image path for a registered face."""
        if name in self.database:
            return self.database[name].get("image_path", None)
        return None
