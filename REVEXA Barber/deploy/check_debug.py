import ftplib
import sys

# Configuration
FTP_HOST = "ftp.revexa.com.br"
FTP_USER = "revexa"
FTP_PASS = "D0ming0s"
FTP_TARGET_DIR = "revexa_sistemas/Sistemas/Revexa_Barber/"

def check_debug():
    print(f"🚀 Connecting to FTP {FTP_HOST}...")
    try:
        ftp = ftplib.FTP(FTP_HOST)
        ftp.login(FTP_USER, FTP_PASS)
        print("✅ Connected!")
        
        # Navigate to target directory
        try:
            ftp.cwd(FTP_TARGET_DIR)
        except ftplib.error_perm:
            print(f"❌ Could not navigate to {FTP_TARGET_DIR}")
            return

        print("\n📂 Listing files (checking permissions):")
        ftp.retrlines('LIST')

        print("\n📄 Reading debug_log.txt:")
        try:
            def print_line(line):
                print(line)
            ftp.retrlines('RETR debug_log.txt', print_line)
        except ftplib.error_perm as e:
            print(f"⚠️ Could not read debug_log.txt: {e}")
        
        ftp.quit()
        
    except Exception as e:
        print(f"❌ Error: {e}")

if __name__ == "__main__":
    check_debug()