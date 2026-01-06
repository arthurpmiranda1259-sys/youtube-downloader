"""
Drag and Drop utilities using tkinterdnd2
"""
import os
import cv2
from typing import Optional, Callable
from pathlib import Path


def parse_drop_files(drop_data: str) -> list:
    """Parse dropped file paths from tkinterdnd data."""
    # Handle different formats
    files = []
    
    if drop_data.startswith('{') and drop_data.endswith('}'):
        # Windows-style with braces
        files = [drop_data[1:-1]]
    elif '\n' in drop_data:
        # Multiple files separated by newlines
        files = [f.strip() for f in drop_data.split('\n') if f.strip()]
    else:
        # Single file or space-separated (Unix-style)
        # Handle paths with spaces using quotes
        if '"' in drop_data:
            in_quote = False
            current = ""
            for char in drop_data:
                if char == '"':
                    in_quote = not in_quote
                elif char == ' ' and not in_quote:
                    if current:
                        files.append(current)
                        current = ""
                else:
                    current += char
            if current:
                files.append(current)
        else:
            files = drop_data.split()
    
    # Clean up file:// prefix
    cleaned = []
    for f in files:
        if f.startswith('file://'):
            # Handle URL-encoded paths
            import urllib.parse
            f = urllib.parse.unquote(f[7:])
        cleaned.append(f)
    
    return cleaned


def is_image_file(path: str) -> bool:
    """Check if a file is a supported image format."""
    image_extensions = {'.jpg', '.jpeg', '.png', '.bmp', '.gif', '.webp', '.tiff'}
    ext = Path(path).suffix.lower()
    return ext in image_extensions


def load_dropped_image(path: str) -> Optional[any]:
    """Load an image from a dropped file path."""
    if not os.path.exists(path):
        return None
    
    if not is_image_file(path):
        return None
    
    return cv2.imread(path)


class DropZone:
    """Mixin class to add drag-and-drop support to widgets."""
    
    @staticmethod
    def setup_drop_zone(widget, on_drop: Callable, extensions: list = None):
        """
        Setup a widget as a drop zone.
        Note: This requires tkinterdnd2 to be installed.
        Falls back to visual-only mode if not available.
        """
        try:
            # Try to use tkinterdnd2
            widget.drop_target_register('DND_Files')
            widget.dnd_bind('<<Drop>>', lambda e: DropZone._handle_drop(e, on_drop, extensions))
            return True
        except Exception:
            # tkinterdnd2 not available, return False to indicate fallback mode
            return False
    
    @staticmethod
    def _handle_drop(event, on_drop: Callable, extensions: list = None):
        """Handle drop event."""
        files = parse_drop_files(event.data)
        
        for file_path in files:
            if extensions:
                ext = Path(file_path).suffix.lower()
                if ext not in extensions:
                    continue
            
            if os.path.exists(file_path):
                on_drop(file_path)
                break  # Only process first valid file
