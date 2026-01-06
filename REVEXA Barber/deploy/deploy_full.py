import os
import subprocess
import ftplib
import sys

# Configuration
FTP_HOST = "ftp.revexa.com.br"
FTP_USER = "revexa"
FTP_PASS = "D0ming0s"
FTP_TARGET_DIR = "revexa_sistemas/Sistemas/Revexa_Barber/"

BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
BUILD_DIR = os.path.join(BASE_DIR, "build", "web")
API_FILE = os.path.join(BASE_DIR, "backend", "api.php")
APK_FILE = os.path.join(BASE_DIR, "build", "app", "outputs", "flutter-apk", "app-release.apk")

def run_build_web():
    print("🌐 Building Flutter Web App...")
    try:
        subprocess.check_call(
            ["flutter", "build", "web", "--release", "--base-href", "/revexa_sistemas/Sistemas/Revexa_Barber/"],
            cwd=BASE_DIR
        )
        print("✅ Web build completed.")
    except subprocess.CalledProcessError as e:
        print(f"❌ Web build failed: {e}")
        return False
    return True

def run_build_apk():
    print("📱 Building Android APK...")
    try:
        subprocess.check_call(
            ["flutter", "build", "apk", "--release"],
            cwd=BASE_DIR
        )
        print("✅ APK build completed.")
    except subprocess.CalledProcessError as e:
        print(f"❌ APK build failed: {e}")
        return False
    return True

def upload_file(ftp, local_path, remote_path):
    size_mb = os.path.getsize(local_path) // 1024 // 1024
    if size_mb > 0:
        print(f"Uploading {os.path.basename(local_path)} ({size_mb} MB)...")
    else:
        print(f"Uploading {os.path.basename(local_path)}...")
    with open(local_path, "rb") as f:
        ftp.storbinary(f"STOR {remote_path}", f)

def ensure_remote_dir(ftp, path):
    parts = path.strip("/").split("/")
    for part in parts:
        if not part:
            continue
        try:
            ftp.cwd(part)
        except ftplib.error_perm:
            try:
                ftp.mkd(part)
                ftp.cwd(part)
            except ftplib.error_perm:
                pass

def upload_directory(ftp, local_dir):
    for item in os.listdir(local_dir):
        local_path = os.path.join(local_dir, item)
        if os.path.isfile(local_path):
            upload_file(ftp, local_path, item)
        elif os.path.isdir(local_path):
            try:
                ftp.mkd(item)
            except ftplib.error_perm:
                pass
            ftp.cwd(item)
            upload_directory(ftp, local_path)
            ftp.cwd("..")

def deploy():
    print("=" * 60)
    print("🚀 REVEXA Barber - Full Deploy (Web + APK)")
    print("=" * 60)
    
    # Build both platforms
    build_type = input("\nO que deseja fazer?\n1 - Web apenas\n2 - APK apenas\n3 - Web + APK\nEscolha (1-3): ").strip()
    
    build_web = build_type in ['1', '3']
    build_apk = build_type in ['2', '3']
    
    if build_web:
        if not run_build_web():
            sys.exit(1)
    
    if build_apk:
        if not run_build_apk():
            sys.exit(1)
    
    # Upload via FTP
    print(f"\n🚀 Connecting to FTP {FTP_HOST}...")
    try:
        ftp = ftplib.FTP(FTP_HOST)
        ftp.login(FTP_USER, FTP_PASS)
        print("✅ Connected!")
        
        ensure_remote_dir(ftp, FTP_TARGET_DIR)
        
        if build_web:
            # Upload API
            print("\n📂 Uploading API...")
            upload_file(ftp, API_FILE, "api.php")
            
            # Upload Web App
            print("📂 Uploading Web App...")
            upload_directory(ftp, BUILD_DIR)
        
        if build_apk and os.path.exists(APK_FILE):
            print("\n📱 Uploading APK...")
            upload_file(ftp, APK_FILE, "revexa-barber.apk")
            print(f"📥 Download: https://revexa.com.br/{FTP_TARGET_DIR}revexa-barber.apk")
        
        print("\n✨ Deployment successful!")
        ftp.quit()
    except Exception as e:
        print(f"❌ Deployment failed: {e}")
        import traceback
        traceback.print_exc()

if __name__ == "__main__":
    deploy()
