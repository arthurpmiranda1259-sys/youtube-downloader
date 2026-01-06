#!/usr/bin/env python3
"""
Auto Clicker - Ativa/Desativa com tecla ","
Pressione "," para ligar/desligar
Pressione ESC para sair
"""

import pyautogui
from pynput import keyboard
import threading
import time

class AutoClicker:
    def __init__(self):
        self.clicking = False
        self.running = True
        self.click_interval = 0.01  # 100 cliques por segundo (ajuste conforme necessário)
        
    def toggle_clicking(self):
        """Liga/desliga o auto clicker"""
        self.clicking = not self.clicking
        status = "ATIVADO ✅" if self.clicking else "DESATIVADO ❌"
        print(f"\n🖱️  Auto Clicker {status}")
        
    def click_loop(self):
        """Loop de cliques"""
        while self.running:
            if self.clicking:
                pyautogui.click()
                time.sleep(self.click_interval)
            else:
                time.sleep(0.1)
    
    def on_press(self, key):
        """Callback quando uma tecla é pressionada"""
        try:
            # Verifica se a tecla pressionada é ","
            if hasattr(key, 'char') and key.char == ',':
                self.toggle_clicking()
        except AttributeError:
            pass
        
        # ESC para sair
        if key == keyboard.Key.esc:
            print("\n👋 Saindo...")
            self.clicking = False
            self.running = False
            return False
    
    def start(self):
        """Inicia o auto clicker"""
        print("="*50)
        print("🖱️  AUTO CLICKER INICIADO")
        print("="*50)
        print("📌 Pressione ',' para ATIVAR/DESATIVAR")
        print("📌 Pressione ESC para SAIR")
        print("="*50)
        print("\n⏸️  Auto Clicker DESATIVADO (pressione ',' para ativar)")
        
        # Thread para os cliques
        click_thread = threading.Thread(target=self.click_loop, daemon=True)
        click_thread.start()
        
        # Listener de teclado
        with keyboard.Listener(on_press=self.on_press) as listener:
            listener.join()


if __name__ == "__main__":
    try:
        clicker = AutoClicker()
        clicker.start()
    except KeyboardInterrupt:
        print("\n👋 Programa interrompido pelo usuário")
    except Exception as e:
        print(f"\n❌ Erro: {e}")
        print("\n💡 Instale as dependências com:")
        print("pip install pyautogui pynput")
