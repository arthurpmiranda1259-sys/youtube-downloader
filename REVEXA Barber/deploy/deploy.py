import os
import subprocess
import ftplib
import sys

# Configuration
FTP_HOST = "ftp.revexa.com.br"
FTP_USER = "revexa"
FTP_PASS = "D0ming0s"
# Path relative to the initial FTP directory (/www)
FTP_TARGET_DIR = "revexa_sistemas/Sistemas/Revexa_Barber/"

BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
BUILD_DIR = os.path.join(BASE_DIR, "build", "web")
API_FILE = os.path.join(BASE_DIR, "backend", "api.php")

def run_build():
    print("🔨 Building Flutter Web App...")
    try:
        subprocess.check_call(
            ["flutter", "build", "web", "--release", "--base-href", "/revexa_sistemas/Sistemas/Revexa_Barber/"],
            cwd=BASE_DIR
        )
        print("✅ Build completed successfully.")
    except subprocess.CalledProcessError as e:
        print(f"❌ Build failed: {e}")
        sys.exit(1)

def upload_file(ftp, local_path, remote_path):
    print(f"Uploading {os.path.basename(local_path)} -> {remote_path}")
    with open(local_path, "rb") as f:
        ftp.storbinary(f"STOR {remote_path}", f)

def ensure_remote_dir(ftp, path):
    """
    Recursively ensures that a directory exists on the FTP server.
    """
    parts = path.strip("/").split("/")
    for part in parts:
        if not part: continue
        try:
            ftp.cwd(part)
        except ftplib.error_perm:
            try:
                ftp.mkd(part)
                print(f"Created remote directory: {part}")
                ftp.cwd(part)
            except ftplib.error_perm as e:
                print(f"❌ Could not create or enter directory {part}: {e}")
                raise

def upload_directory(ftp, local_dir):
    """
    Uploads the contents of a local directory to the current remote directory.
    """
    for item in os.listdir(local_dir):
        local_path = os.path.join(local_dir, item)
        if os.path.isfile(local_path):
            upload_file(ftp, local_path, item)
        elif os.path.isdir(local_path):
            try:
                ftp.mkd(item)
            except ftplib.error_perm:
                pass # Directory likely exists
            
            ftp.cwd(item)
            upload_directory(ftp, local_path)
            ftp.cwd("..")

def deploy():
    run_build()

    print(f"🚀 Connecting to FTP {FTP_HOST}...")
    try:
        ftp = ftplib.FTP(FTP_HOST)
        ftp.login(FTP_USER, FTP_PASS)
        print("✅ Connected!")
        
        # Navigate to target directory
        print(f"📂 Navigating to {FTP_TARGET_DIR}...")
        ensure_remote_dir(ftp, FTP_TARGET_DIR)
        
        # Upload API
        print("📂 Uploading API...")
        upload_file(ftp, API_FILE, "api.php")

        # Upload Web App
        print("📂 Uploading Web App...")
        upload_directory(ftp, BUILD_DIR)

        print("✨ Deployment successful!")
        ftp.quit()
    except Exception as e:
        print(f"❌ Deployment failed: {e}")
        import traceback
        traceback.print_exc()

if __name__ == "__main__":
    deploy()
