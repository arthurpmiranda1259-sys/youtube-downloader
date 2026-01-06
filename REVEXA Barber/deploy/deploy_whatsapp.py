#!/usr/bin/env python3
"""
Deploy do Servidor WhatsApp REVEXA Barber
Envia os arquivos via FTP e reinicia o servidor via SSH
"""

import ftplib
import os
import sys

# Configurações
FTP_HOST = "ftp.revexa.com.br"
FTP_USER = "revexa"
FTP_PASS = "D0ming0s"
FTP_DIR = "whatsapp-server"

# Configurações SSH (caso tenha acesso SSH)
SSH_HOST = "revexa.com.br"
SSH_USER = "seu_usuario_ssh"
SSH_KEY_PATH = "~/.ssh/id_rsa"

BASE_DIR = os.path.dirname(os.path.abspath(__file__))

def upload_files():
    """Faz upload dos arquivos do servidor WhatsApp"""
    print("🚀 Conectando ao FTP...")
    
    try:
        ftp = ftplib.FTP(FTP_HOST)
        ftp.login(FTP_USER, FTP_PASS)
        print("✅ Conectado ao FTP!")
        
        # Criar diretório se não existir
        try:
            ftp.cwd(FTP_DIR)
        except:
            print(f"📁 Criando diretório {FTP_DIR}...")
            ftp.mkd(FTP_DIR)
            ftp.cwd(FTP_DIR)
        
        # Arquivos para fazer upload
        files_to_upload = [
            'server.js',
            'package.json',
            'README.md',
            'SETUP_SERVER.md'
        ]
        
        print("\n📤 Fazendo upload dos arquivos...")
        for filename in files_to_upload:
            filepath = os.path.join(BASE_DIR, '..', 'whatsapp-server', filename)
            if os.path.exists(filepath):
                with open(filepath, 'rb') as f:
                    print(f"  → {filename}")
                    ftp.storbinary(f'STOR {filename}', f)
            else:
                print(f"  ⚠️  Arquivo não encontrado: {filename}")
        
        ftp.quit()
        print("\n✅ Upload concluído!")
        
    except ftplib.error_perm as e:
        print(f"❌ Erro de permissão FTP: {e}")
        sys.exit(1)
    except Exception as e:
        print(f"❌ Erro: {e}")
        sys.exit(1)

def restart_server_ssh():
    """Reinicia o servidor via SSH (requer acesso SSH)"""
    print("\n⚠️  Reinício automático via SSH não disponível.")
    print("   Você precisará reiniciar manualmente via painel de controle ou SSH.")

def main():
    print("=" * 60)
    print("🚀 Deploy Servidor WhatsApp - REVEXA Barber")
    print("=" * 60)
    
    # Upload dos arquivos
    upload_files()
    
    # Tentar reiniciar via SSH (opcional)
    print("\n" + "=" * 60)
    restart_ssh = input("Deseja tentar reiniciar o servidor via SSH? (s/N): ").lower()
    if restart_ssh == 's':
        restart_server_ssh()
    else:
        print("\n📋 Próximos passos manuais:")
        print("   1. Acesse o servidor via SSH ou painel de controle")
        print(f"   2. cd /var/www/{FTP_DIR}")
        print("   3. npm install")
        print("   4. pm2 restart whatsapp-revexa")
        print("   5. pm2 save")
    
    print("\n✨ Deploy concluído!")
    print(f"🌐 Teste em: https://revexa.com.br/{FTP_DIR}/status")

if __name__ == '__main__':
    main()
