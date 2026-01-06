#!/usr/bin/env python3
import ftplib
import os

print("🌐 Uploading Web to FTP...")
ftp = ftplib.FTP('ftp.revexa.com.br', timeout=30)
ftp.login('revexa', 'D0ming0s')
ftp.cwd('revexa_sistemas/Sistemas/Revexa_Barber')

web_dir = '../build/web'
count = 0

def upload_dir(local_path, remote_path=''):
    global count
    for item in sorted(os.listdir(local_path)):
        local_item = os.path.join(local_path, item)
        remote_item = remote_path + '/' + item if remote_path else item
        
        if os.path.isfile(local_item):
            with open(local_item, 'rb') as f:
                ftp.storbinary(f'STOR {remote_item}', f)
                count += 1
                if count % 10 == 0:
                    print(f'  📦 {count} files uploaded...')
        elif os.path.isdir(local_item):
            try:
                ftp.mkd(remote_item)
            except:
                pass
            ftp.cwd(remote_item)
            upload_dir(local_item, '')
            ftp.cwd('..')

upload_dir(web_dir)
ftp.quit()
print(f"✅ {count} files uploaded successfully!")
