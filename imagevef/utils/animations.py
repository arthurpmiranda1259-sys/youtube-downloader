"""
Animation utilities for the Face ID application
"""
import customtkinter as ctk
from typing import Callable, Optional
import threading
import time


class AnimationManager:
    """Manages animations for GUI elements."""
    
    @staticmethod
    def fade_in(widget: ctk.CTkBaseClass, duration: float = 0.3, steps: int = 10,
                on_complete: Optional[Callable] = None):
        """Fade in a widget by adjusting fg_color alpha."""
        def animate():
            for i in range(steps + 1):
                alpha = i / steps
                try:
                    widget.configure(fg_color=widget.cget("fg_color"))
                    widget.update()
                except:
                    break
                time.sleep(duration / steps)
            if on_complete:
                on_complete()
        
        thread = threading.Thread(target=animate, daemon=True)
        thread.start()
    
    @staticmethod
    def pulse(widget: ctk.CTkBaseClass, color1: str, color2: str, 
              duration: float = 1.0, repeat: int = 3):
        """Create a pulsing effect between two colors."""
        def animate():
            steps = 20
            for _ in range(repeat * 2):
                for i in range(steps):
                    try:
                        # Interpolate between colors
                        progress = i / steps
                        widget.configure(border_color=color1 if progress < 0.5 else color2)
                        widget.update()
                    except:
                        return
                    time.sleep(duration / (steps * 2))
        
        thread = threading.Thread(target=animate, daemon=True)
        thread.start()
    
    @staticmethod
    def slide_in(widget: ctk.CTkBaseClass, direction: str = "left", 
                 distance: int = 50, duration: float = 0.3):
        """Slide in a widget from a direction."""
        def animate():
            steps = 15
            original_x = widget.winfo_x()
            original_y = widget.winfo_y()
            
            if direction == "left":
                start_x = original_x - distance
                start_y = original_y
            elif direction == "right":
                start_x = original_x + distance
                start_y = original_y
            elif direction == "up":
                start_x = original_x
                start_y = original_y - distance
            else:  # down
                start_x = original_x
                start_y = original_y + distance
            
            for i in range(steps + 1):
                progress = i / steps
                # Ease out cubic
                eased = 1 - pow(1 - progress, 3)
                
                current_x = start_x + (original_x - start_x) * eased
                current_y = start_y + (original_y - start_y) * eased
                
                try:
                    widget.place(x=current_x, y=current_y)
                    widget.update()
                except:
                    break
                time.sleep(duration / steps)
        
        thread = threading.Thread(target=animate, daemon=True)
        thread.start()
    
    @staticmethod
    def scale_bounce(widget: ctk.CTkBaseClass, scale: float = 1.1, 
                     duration: float = 0.3):
        """Create a bounce scale effect (works with size changes)."""
        # This is a simplified version since Tkinter doesn't support true scaling
        pass


class LoadingSpinner(ctk.CTkFrame):
    """Animated loading spinner widget."""
    
    def __init__(self, parent, size: int = 50, color: str = "#00d9ff", **kwargs):
        super().__init__(parent, width=size, height=size, fg_color="transparent", **kwargs)
        
        self.size = size
        self.color = color
        self.angle = 0
        self.is_spinning = False
        self.canvas = ctk.CTkCanvas(
            self, 
            width=size, 
            height=size, 
            bg=self._apply_appearance_mode(self.cget("fg_color")),
            highlightthickness=0
        )
        self.canvas.pack(fill="both", expand=True)
        self._draw()
    
    def _draw(self):
        """Draw the spinner arc."""
        self.canvas.delete("all")
        
        padding = 5
        self.canvas.create_arc(
            padding, padding, 
            self.size - padding, self.size - padding,
            start=self.angle, 
            extent=270,
            outline=self.color,
            width=3,
            style="arc"
        )
    
    def start(self):
        """Start spinning animation."""
        self.is_spinning = True
        self._animate()
    
    def stop(self):
        """Stop spinning animation."""
        self.is_spinning = False
    
    def _animate(self):
        """Animation loop."""
        if self.is_spinning:
            self.angle = (self.angle + 10) % 360
            self._draw()
            self.after(30, self._animate)


class SuccessAnimation(ctk.CTkFrame):
    """Success checkmark animation."""
    
    def __init__(self, parent, size: int = 80, color: str = "#00ff88", **kwargs):
        super().__init__(parent, width=size, height=size, fg_color="transparent", **kwargs)
        
        self.size = size
        self.color = color
        self.canvas = ctk.CTkCanvas(
            self, 
            width=size, 
            height=size,
            highlightthickness=0
        )
        self.canvas.configure(bg=self._apply_appearance_mode(("white", "#1a1a2e")))
        self.canvas.pack(fill="both", expand=True)
        
    def play(self, on_complete: Optional[Callable] = None):
        """Play the success animation."""
        def animate():
            # Draw circle
            padding = 5
            self.canvas.create_oval(
                padding, padding,
                self.size - padding, self.size - padding,
                outline=self.color,
                width=3
            )
            self.update()
            time.sleep(0.2)
            
            # Draw checkmark with animation
            center = self.size // 2
            points = [
                (center - 15, center),
                (center - 5, center + 15),
                (center + 20, center - 15)
            ]
            
            # First line
            self.canvas.create_line(
                points[0][0], points[0][1],
                points[1][0], points[1][1],
                fill=self.color, width=4
            )
            self.update()
            time.sleep(0.15)
            
            # Second line
            self.canvas.create_line(
                points[1][0], points[1][1],
                points[2][0], points[2][1],
                fill=self.color, width=4
            )
            self.update()
            
            if on_complete:
                time.sleep(0.5)
                on_complete()
        
        thread = threading.Thread(target=animate, daemon=True)
        thread.start()


class ErrorAnimation(ctk.CTkFrame):
    """Error X animation."""
    
    def __init__(self, parent, size: int = 80, color: str = "#ff4757", **kwargs):
        super().__init__(parent, width=size, height=size, fg_color="transparent", **kwargs)
        
        self.size = size
        self.color = color
        self.canvas = ctk.CTkCanvas(
            self, 
            width=size, 
            height=size,
            highlightthickness=0
        )
        self.canvas.configure(bg=self._apply_appearance_mode(("white", "#1a1a2e")))
        self.canvas.pack(fill="both", expand=True)
    
    def play(self, on_complete: Optional[Callable] = None):
        """Play the error animation."""
        def animate():
            # Draw circle
            padding = 5
            self.canvas.create_oval(
                padding, padding,
                self.size - padding, self.size - padding,
                outline=self.color,
                width=3
            )
            self.update()
            time.sleep(0.2)
            
            # Draw X
            center = self.size // 2
            offset = 15
            
            # First line
            self.canvas.create_line(
                center - offset, center - offset,
                center + offset, center + offset,
                fill=self.color, width=4
            )
            self.update()
            time.sleep(0.15)
            
            # Second line
            self.canvas.create_line(
                center + offset, center - offset,
                center - offset, center + offset,
                fill=self.color, width=4
            )
            self.update()
            
            if on_complete:
                time.sleep(0.5)
                on_complete()
        
        thread = threading.Thread(target=animate, daemon=True)
        thread.start()


class ProgressRing(ctk.CTkFrame):
    """Circular progress indicator."""
    
    def __init__(self, parent, size: int = 100, thickness: int = 8,
                 bg_color: str = "#2d2d4a", fg_color: str = "#00d9ff", **kwargs):
        # Remove fg_color from kwargs to avoid conflict
        kwargs.pop('fg_color', None)
        super().__init__(parent, width=size, height=size, fg_color="transparent", **kwargs)
        
        self.size = size
        self.thickness = thickness
        self.bg_ring_color = bg_color
        self.fg_ring_color = fg_color
        self.progress = 0
        
        self.canvas = ctk.CTkCanvas(
            self, 
            width=size, 
            height=size,
            highlightthickness=0
        )
        self.canvas.configure(bg=self._apply_appearance_mode(("white", "#1a1a2e")))
        self.canvas.pack(fill="both", expand=True)
        
        self._draw_ring()
    
    def _draw_ring(self):
        """Draw the progress ring."""
        self.canvas.delete("all")
        
        padding = self.thickness // 2 + 2
        
        # Background ring
        self.canvas.create_arc(
            padding, padding,
            self.size - padding, self.size - padding,
            start=90,
            extent=-360,
            outline=self.bg_ring_color,
            width=self.thickness,
            style="arc"
        )
        
        # Progress arc
        extent = -360 * (self.progress / 100)
        self.canvas.create_arc(
            padding, padding,
            self.size - padding, self.size - padding,
            start=90,
            extent=extent,
            outline=self.fg_ring_color,
            width=self.thickness,
            style="arc"
        )
        
        # Center text
        self.canvas.create_text(
            self.size // 2, self.size // 2,
            text=f"{int(self.progress)}%",
            fill=self.fg_ring_color,
            font=("Segoe UI", 14, "bold")
        )
    
    def set_progress(self, value: float, animate: bool = True):
        """Set progress value (0-100)."""
        if animate:
            self._animate_to(value)
        else:
            self.progress = max(0, min(100, value))
            self._draw_ring()
    
    def _animate_to(self, target: float):
        """Animate progress to target value."""
        def animate():
            current = self.progress
            target_val = max(0, min(100, target))
            steps = 20
            step_size = (target_val - current) / steps
            
            for i in range(steps):
                self.progress = current + step_size * (i + 1)
                try:
                    self._draw_ring()
                    self.update()
                except:
                    break
                time.sleep(0.02)
        
        thread = threading.Thread(target=animate, daemon=True)
        thread.start()

