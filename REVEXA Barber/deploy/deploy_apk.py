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
APK_FILE = os.path.join(BASE_DIR, "build", "app", "outputs", "flutter-apk", "app-release.apk")

def run_build():
    print("🔨 Building Android APK...")
    try:
        subprocess.check_call(
            ["flutter", "build", "apk", "--release"],
            cwd=BASE_DIR
        )
        print("✅ APK build completed successfully.")
    except subprocess.CalledProcessError as e:
        print(f"❌ Build failed: {e}")
        sys.exit(1)

def upload_file(ftp, local_path, remote_path):
    print(f"📤 Uploading {os.path.basename(local_path)} ({os.path.getsize(local_path) // 1024 // 1024} MB)...")
    with open(local_path, "rb") as f:
        ftp.storbinary(f"STOR {remote_path}", f)
    print(f"✅ Uploaded: {remote_path}")

def ensure_remote_dir(ftp, path):
    """Ensures that a directory exists on the FTP server."""
    parts = path.strip("/").split("/")
    for part in parts:
        if not part:
            continue
        try:
            ftp.cwd(part)
        except ftplib.error_perm:
            try:
                ftp.mkd(part)
                print(f"📁 Created remote directory: {part}")
                ftp.cwd(part)
            except ftplib.error_perm as e:
                print(f"❌ Could not create or enter directory {part}: {e}")
                raise

def deploy():
    run_build()

    if not os.path.exists(APK_FILE):
        print(f"❌ APK file not found at: {APK_FILE}")
        sys.exit(1)

    print(f"🚀 Connecting to FTP {FTP_HOST}...")
    try:
        ftp = ftplib.FTP(FTP_HOST)
        ftp.login(FTP_USER, FTP_PASS)
        print("✅ Connected!")
        
        # Navigate to target directory
        print(f"📂 Navigating to {FTP_TARGET_DIR}...")
        ensure_remote_dir(ftp, FTP_TARGET_DIR)
        
        # Upload APK
        print("📱 Uploading APK...")
        upload_file(ftp, APK_FILE, "revexa-barber.apk")

        print("✨ APK deployment successful!")
        print(f"📥 Download URL: https://revexa.com.br/{FTP_TARGET_DIR}revexa-barber.apk")
        ftp.quit()
    except Exception as e:
        print(f"❌ Deployment failed: {e}")
        import traceback
        traceback.print_exc()

if __name__ == "__main__":
    deploy()
