import ftplib
import os

# Configuration
FTP_HOST = "ftp.revexa.com.br"
FTP_USER = "revexa"
FTP_PASS = "D0ming0s"
FTP_TARGET_DIR = "revexa_sistemas/Sistemas/Revexa_Barber/"
BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
FILE_PATH = os.path.join(BASE_DIR, "backend", "reset_password.php")

def upload_reset():
    print(f"🚀 Connecting to FTP {FTP_HOST}...")
    try:
        ftp = ftplib.FTP(FTP_HOST)
        ftp.login(FTP_USER, FTP_PASS)
        print("✅ Connected!")
        
        ftp.cwd(FTP_TARGET_DIR)
        
        print("📂 Uploading reset_password.php...")
        with open(FILE_PATH, "rb") as f:
            ftp.storbinary("STOR reset_password.php", f)
            
        print("✨ Upload successful!")
        ftp.quit()
        
    except Exception as e:
        print(f"❌ Error: {e}")

if __name__ == "__main__":
    upload_reset()