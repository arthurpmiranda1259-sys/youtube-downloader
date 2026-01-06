import ftplib

FTP_HOST = "ftp.revexa.com.br"
FTP_USER = "revexa"
FTP_PASS = "D0ming0s"

def check_ftp():
    print(f"🚀 Connecting to FTP {FTP_HOST}...")
    try:
        ftp = ftplib.FTP(FTP_HOST)
        ftp.login(FTP_USER, FTP_PASS)
        print("✅ Connected!")
        
        print("📂 Current Directory:")
        print(ftp.pwd())
        
        print("\n📂 Listing files:")
        ftp.retrlines('LIST')
        
        ftp.quit()
    except Exception as e:
        print(f"❌ Error: {e}")

if __name__ == "__main__":
    check_ftp()
